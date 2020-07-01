<?php
Class AccManager extends Application{
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
				case 'apiaccmanager':
					$this->moduleAccManager();
					break;
				default:
					break;
			}
		}
	}
	public function moduleAccManager(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'all':
						$Accessuser = Accessuser::all();
						foreach ($Accessuser as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Accessuser, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);					
						try{
							$Accessuser = Accessuser::create($data);
							echo json_encode($Accessuser);
						}catch (Exception $e){
							$respon = array("status"=>"error","message"=>$e->getMessage());
							echo json_encode($respon);
						}
						break;
					case 'delete':				
						$id = $this->post['id'];				
						try{
							$Accessuser = Accessuser::find($id);
							$Accessuser->delete();
							echo json_encode($Accessuser);
						}catch (Exception $e){
							$respon = array("status"=>"error","message"=>$e->getMessage());
							echo json_encode($respon);
						}
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						try{
							$Accessuser = Accessuser::find($id);
							foreach($data as $key=>$val){
								
								$val=($val=='false')?false:(($val=='true')?true:$val);
								//echo $key."=>".$val;
								$Accessuser->$key=$val;
							}
							$Accessuser->save();
							echo json_encode($Accessuser);
						}catch (Exception $e){
							$respon = array("status"=>"error","message"=>$e->getMessage());
							echo json_encode($respon);
						}						
						break;
					case 'byid':
						$id = $this->post['id'];
						$Accessuser = Accessuser::find($id);
						echo json_encode($Accessuser);
						break;
					default:
						$Accessuser = Accessuser::all();
						foreach ($Accessuser as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Accessuser, JSON_NUMERIC_CHECK);
						break;					
				}
			}	
		}
	}
}