<?php
Class RfccontractorModule extends Application{
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
				case 'apirfccontractor':
					$this->RfccontractorManager();
					break;				
				default:
					break;
			}
		}
	}
	
	public function RfccontractorManager(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'all':
						$Rfccontractor = Rfccontractor::all();
						foreach ($Rfccontractor as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Rfccontractor, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Rfccontractor = Rfccontractor::create($data);
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Rfccontractor = Rfccontractor::find($id);
						$Rfccontractor->delete();
						echo json_encode($Rfccontractor);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						print_r($data);
						$Rfccontractor = Rfccontractor::find($id);
						foreach($data as $key=>$val){					
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$Rfccontractor->$key=$val;
						}
						$Rfccontractor->save();
						echo json_encode($Rfccontractor);
						break;
					case 'byid':
						$id = $this->post['id'];
						$Rfccontractor = Rfccontractor::find($id);
						echo json_encode($Rfccontractor);
						break;
					default:
						$Rfccontractor = Rfccontractor::all();
						foreach ($Rfccontractor as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Rfccontractor, JSON_NUMERIC_CHECK);
						break;					
				}
			}	
		}
	}
}