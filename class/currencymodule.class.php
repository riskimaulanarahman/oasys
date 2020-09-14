<?php
Class Currencymodule extends Application{
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
				case 'apicurrency':
					$this->currencyModule();
					break;				
				default:
					break;
			}
		}
	}
	public function currencyModule(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'all':
						$Currency = Currency::all();
						foreach ($Currency as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Currency);
						break;
					// case 'create':			
					// 	$data = $this->post['data'];
					// 	unset($data['__KEY__']);
					// 	$Currency = Currency::create($data);
					// 	break;
					// case 'delete':				
					// 	$id = $this->post['id'];
					// 	$Currency = Currency::find($id);
					// 	$Currency->delete();
					// 	echo json_encode($Currency);
					// 	break;
					// case 'update':				
					// 	$id = $this->post['id'];
					// 	$data = $this->post['data'];
					// 	$Currency = Currency::find($id);
					// 	foreach($data as $key=>$val){					
					// 		$val=($val=='false')?false:(($val=='true')?true:$val);
					// 		$Currency->$key=$val;
					// 	}
					// 	$Currency->save();
					// 	echo json_encode($Currency);
					// 	break;
					case 'byid':
						$id = $this->post['id'];
						$Currency = Currency::find($id);
						echo json_encode($Currency);
						break;
					default:
						$Currency = Currency::all();
						foreach ($Currency as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Currency, JSON_NUMERIC_CHECK);
						break;					
				}
			}	
		}
	}
	
}