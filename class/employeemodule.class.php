<?php
Class EmployeeModule extends Application{
	public function __construct(){
		parent::__construct();
		$this->get = isset($this->get)?$this->get:$_GET;
		$this->post = isset($this->post)?$this->post:$_POST;
		$this->heading = "";
		$this->output = "";
		$this->script = "";
		$this->method = $_SERVER['REQUEST_METHOD'];
		if (isset($this->get)){
			switch ($this->get['action']){
				case 'apiemp':
					$this->employeeManager();
					break;
				default:
					break;
			}
		}
	}
	public function employeeManager(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'all':
						$Employee = Employee::all(array('conditions' => array("loginname <> ''"),'include' => array('department','company', 'designation'),"order"=>"fullname"));
						foreach ($Employee as &$result) {
							$dept=$result->department->departmentname;
							$comp=$result->company->companycode;
							$des=$result->designation->designationname;				
							$result = $result->to_array();
							$result['department']=$dept;
							$result['designation']=$des;
							$result['company']=$comp;
						}					
						echo json_encode($Employee, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Employee = Employee::create($data);
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Employee = Employee::find($id);
						$Employee->delete();
						echo json_encode($Employee);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$Employee = Employee::find($id);
						foreach($data as $key=>$val){					
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$Employee->$key=$val;
						}
						$Employee->save();
						echo json_encode($Employee);
						break;
					case 'byid':
						$id = $this->post['id'];
						$Employee = Employee::find($id);
						if ($Employee){
							echo json_encode($Employee->to_array(), JSON_NUMERIC_CHECK);
						}else{
							$Employee = new Employee();
							echo json_encode($Employee);
						}
						
						break;
					default:
						$Employee = Employee::all(array('include' => array('department', 'designation')));
						foreach ($Employee as &$result) {
							$dept=$result->department->departmentname;
							$des=$result->designation->designationname;				
							$result = $result->to_array();
							$result['department']=$dept;
							$result['designation']=$des;
						}					
						echo json_encode($Employee, JSON_NUMERIC_CHECK);
						break;					
				}
			}	
		}
	}
}