<?php
Class Expensetypemodule extends Application{
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
				case 'apiexpensetype':
					$this->expensetypeModule();
					break;				
				default:
					break;
			}
		}
	}
	public function expensetypeModule(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'all':
						$Listexpensetype = Listexpensetype::all();
						foreach ($Listexpensetype as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Listexpensetype);
						break;
					case 'byid':
						$id = $this->post['id'];
						$Listexpensetype = Listexpensetype::find($id);
						echo json_encode($Listexpensetype);
						break;
					default:
						$Listexpensetype = Listexpensetype::all();
						foreach ($Listexpensetype as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Listexpensetype, JSON_NUMERIC_CHECK);
						break;					
				}
			}	
		}
	}
	
}