<?php
Class RfcactivityModule extends Application{
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
				case 'apirfcactivity':
					$this->RfcactivityManager();
					break;				
				default:
					break;
			}
		}
	}
	
	public function RfcactivityManager(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'all':
						$Rfcactivity = Rfcactivity::all();
						foreach ($Rfcactivity as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Rfcactivity, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Rfcactivity = Rfcactivity::create($data);
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Rfcactivity = Rfcactivity::find($id);
						$Rfcactivity->delete();
						echo json_encode($Rfcactivity);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$Rfcactivity = Rfcactivity::find($id);
						foreach($data as $key=>$val){					
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$Rfcactivity->$key=$val;
						}
						$Rfcactivity->save();
						echo json_encode($Rfcactivity);
						break;
					case 'byid':
						$id = $this->post['id'];
						$Rfcactivity = Rfcactivity::find($id);
						echo json_encode($Rfcactivity);
						break;
					default:
						$Rfcactivity = Rfcactivity::all();
						foreach ($Rfcactivity as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Rfcactivity, JSON_NUMERIC_CHECK);
						break;					
				}
			}	
		}
	}
}