<?php
Class ModuleManager extends Application{
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
				case 'apimodule':
					$this->moduleManager();
					break;				
				default:
					break;
			}
		}
	}
	public function moduleManager(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'all':
						$Module = Module::all();
						foreach ($Module as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Module);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Module = Module::create($data);
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Module = Module::find($id);
						$Module->delete();
						echo json_encode($Module);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$Module = Module::find($id);
						foreach($data as $key=>$val){					
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$Module->$key=$val;
						}
						$Module->save();
						echo json_encode($Module);
						break;
					case 'byid':
						$id = $this->post['id'];
						$Module = Module::find($id);
						echo json_encode($Module);
						break;
					default:
						$Module = Module::all();
						foreach ($Module as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Module, JSON_NUMERIC_CHECK);
						break;					
				}
			}	
		}
	}
	
}