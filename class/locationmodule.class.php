<?php
Class LocationModule extends Application{
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
				case 'apiloc':
					$this->LocationManager();
					break;				
				default:
					break;
			}
		}
	}
	
	public function LocationManager(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'all':
						$Location = Location::all();
						foreach ($Location as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Location);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Location = Location::create($data);
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Location = Location::find($id);
						$Location->delete();
						echo json_encode($Location);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$Location = Location::find($id);
						foreach($data as $key=>$val){					
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$Location->$key=$val;
						}
						$Location->save();
						echo json_encode($Location);
						break;
					case 'byid':
						$id = $this->post['id'];
						$Location = Location::find($id);
						echo json_encode($Location);
						break;
					default:
						$Location = Location::all();
						foreach ($Location as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Location, JSON_NUMERIC_CHECK);
						break;					
				}
			}	
		}
	}
}