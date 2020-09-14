<?php
Class Unitmodule extends Application{
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
				case 'apiunit':
					$this->unitModule();
					break;				
				default:
					break;
			}
		}
	}
	public function unitModule(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'all':
						$Unit = Unit::all();
						foreach ($Unit as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Unit);
						break;
					// case 'create':			
					// 	$data = $this->post['data'];
					// 	unset($data['__KEY__']);
					// 	$Unit = Unit::create($data);
					// 	break;
					// case 'delete':				
					// 	$id = $this->post['id'];
					// 	$Unit = Unit::find($id);
					// 	$Unit->delete();
					// 	echo json_encode($Unit);
					// 	break;
					// case 'update':				
					// 	$id = $this->post['id'];
					// 	$data = $this->post['data'];
					// 	$Unit = Unit::find($id);
					// 	foreach($data as $key=>$val){					
					// 		$val=($val=='false')?false:(($val=='true')?true:$val);
					// 		$Unit->$key=$val;
					// 	}
					// 	$Unit->save();
					// 	echo json_encode($Unit);
					// 	break;
					case 'byid':
						$id = $this->post['id'];
						$Unit = Unit::find($id);
						echo json_encode($Unit);
						break;
					default:
						$Unit = Unit::all();
						foreach ($Unit as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Unit, JSON_NUMERIC_CHECK);
						break;					
				}
			}	
		}
	}
	
}