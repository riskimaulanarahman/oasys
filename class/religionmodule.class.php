<?php
Class ReligionModule extends Application{
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
				case 'apireligion':
					$this->ReligionManager();
					break;				
				default:
					break;
			}
		}
	}
	
	public function ReligionManager(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'all':
						$Religion = Religion::all();
						foreach ($Religion as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Religion);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Religion = Religion::create($data);
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Religion = Religion::find($id);
						$Religion->delete();
						echo json_encode($Religion);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$Religion = Religion::find($id);
						foreach($data as $key=>$val){					
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$Religion->$key=$val;
						}
						$Religion->save();
						echo json_encode($Religion);
						break;
					case 'byid':
						$id = $this->post['id'];
						$Religion = Religion::find($id);
						echo json_encode($Religion);
						break;
					default:
						$Religion = Religion::all();
						foreach ($Religion as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Religion, JSON_NUMERIC_CHECK);
						break;					
				}
			}	
		}
	}
}