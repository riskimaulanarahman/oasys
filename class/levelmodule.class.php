<?php
Class LevelModule extends Application{
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
				case 'apilevel':
					$this->LevelManager();
					break;				
				default:
					break;
			}
		}
	}
	
	public function LevelManager(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'all':
						$Level = Level::all();
						foreach ($Level as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Level);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Level = Level::create($data);
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Level = Level::find($id);
						$Level->delete();
						echo json_encode($Level);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$Level = Level::find($id);
						foreach($data as $key=>$val){					
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$Level->$key=$val;
						}
						$Level->save();
						echo json_encode($Level);
						break;
					case 'byid':
						$id = $this->post['id'];
						$Level = Level::find($id);
						echo json_encode($Level);
						break;
					default:
						$Level = Level::all();
						foreach ($Level as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Level, JSON_NUMERIC_CHECK);
						break;					
				}
			}	
		}
	}
}