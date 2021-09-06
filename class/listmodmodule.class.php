<?php
Class ListmodModule extends Application{
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
				case 'apilistmod':
					$this->ModManager();
					break;
				case 'apilistadvance':
					$this->getadvance();
					break;
				default:
					break;
			}
		}
	}

	public function getadvance(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'byid':
						$id = $this->post['id'];
						$Advance = Advance::find('all', array('conditions' => array("employee_id=? and RequestStatus=3 and isused=0",$id),'include' => array('employee')));
						foreach ($Advance as &$result) {
							$result = $result->to_array();
						}
						// print_r($Advance);					
						echo json_encode($Advance, JSON_NUMERIC_CHECK);
						break;
					default:
						$Advance = Advance::all();
						foreach ($Advance as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Advance, JSON_NUMERIC_CHECK);
						break;					
				}
			}	
		}
	}
	
	public function ModManager(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'all':
						$Listmod = Listmod::all();
						foreach ($Listmod as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Listmod, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Listmod = Listmod::create($data);
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Listmod = Listmod::find($id);
						$Listmod->delete();
						echo json_encode($Listmod);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$Listmod = Listmod::find($id);
						foreach($data as $key=>$val){					
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$Listmod->$key=$val;
						}
						$Listmod->save();
						echo json_encode($Listmod);
						break;
					case 'byid':
						$id = $this->post['id'];
						$Listmod = Listmod::find($id);
						echo json_encode($Listmod);
						break;
					default:
						$Listmod = Listmod::all();
						foreach ($Listmod as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Listmod, JSON_NUMERIC_CHECK);
						break;					
				}
			}	
		}
	}
}