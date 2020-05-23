<?php
Class DesignationModule extends Application{
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
				case 'apides':
					$this->DesignationManager();
					break;			
				default:
					break;
			}
		}
	}
	
	public function DesignationManager(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'all':
						$designation = Designation::all(array("include"=>array('division' => array('department'))));
						foreach ($designation as &$result) {
							$dept=$result->division->department_id;
							$result = $result->to_array();
							$result['department_id']=$dept;
						}					
						echo json_encode($designation, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						unset($data['department_id']);
						$designation = Designation::create($data);
						break;
					case 'delete':				
						$id = $this->post['id'];
						$designation = Designation::find($id);
						$designation->delete();
						echo json_encode($designation);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						unset($data['department_id']);
						$designation = Designation::find($id);
						foreach($data as $key=>$val){					
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$designation->$key=$val;
						}
						$designation->save();
						echo json_encode($designation);
						break;
					case 'byid':
						$id = $this->post['id'];
						$designation = Designation::find($id);
						echo json_encode($designation);
						break;
					default:
						$designation = Designation::all();
						foreach ($designation as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($designation, JSON_NUMERIC_CHECK);
						break;					
				}
			}	
		}
	}
}