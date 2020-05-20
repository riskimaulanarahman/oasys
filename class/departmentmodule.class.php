<?php
Class DepartmentModule extends Application{
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
				case 'apidept':
					$this->departmentManager();
					break;
				default:
					break;
			}
		}
	}
	public function departmentManager(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'all':
						$Department = Department::all();
						foreach ($Department as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Department);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Department = Department::create($data);
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Department = Department::find($id);
						$Department->delete();
						echo json_encode($Department);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$Department = Department::find($id);
						foreach($data as $key=>$val){					
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$Department->$key=$val;
						}
						$Department->save();
						echo json_encode($Department);
						break;
					case 'byid':
						$id = $this->post['id'];
						$Department = Department::find($id);
						echo json_encode($Department);
						break;
					default:
						$Department = Department::all();
						foreach ($Department as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Department, JSON_NUMERIC_CHECK);
						break;					
				}
			}	
		}
	}
}