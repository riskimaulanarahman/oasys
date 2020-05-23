<?php
Class SkrateModule extends Application{
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
				case 'apiskrate':
					$this->skrateManager();
					break;
				default:
					break;
			}
		}
	}
	public function skrateManager(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'all':
						try{
							$Skrate = Skrate::all();
							//print_r($Skrate);
							foreach ($Skrate as &$result) {
								$result = $result->to_array();
							}
							echo json_encode($Skrate);
						}catch (Exception $e){
							echo $e->getMessage();
						}
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Skrate = Skrate::create($data);
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Skrate = Skrate::find($id);
						$Skrate->delete();
						echo json_encode($Skrate);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$Skrate = Skrate::find($id);
						foreach($data as $key=>$val){					
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$Skrate->$key=$val;
						}
						$Skrate->save();
						echo json_encode($Skrate);
						break;
					case 'byid':
						$id = $this->post['id'];
						$Skrate = Skrate::find($id);
						echo json_encode($Skrate);
						break;
					default:
						$Skrate = Skrate::all();
						foreach ($Skrate as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Skrate, JSON_NUMERIC_CHECK);
						break;					
				}
			}	
		}
	}
}