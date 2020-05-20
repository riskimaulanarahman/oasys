<?php
Class ApprovalTypeModule extends Application{
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
				case 'apiapprovaltype':
					$this->approvaltypeManager();
					break;
				default:
					break;
			}
		}
	}
	public function approvaltypeManager(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'all':
						$Approvaltype = Approvaltype::all();
						foreach ($Approvaltype as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Approvaltype, JSON_NUMERIC_CHECK);
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['module'])){
							$Approvaltype = Approvaltype::find('all', array('conditions' => array("module=?",$query['module'])));
							foreach ($Approvaltype as &$result) {
								$result = $result->to_array();
							}
							$data = $Approvaltype;
						}else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Approvaltype = Approvaltype::create($data);
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Approvaltype = Approvaltype::find($id);
						$Approvaltype->delete();
						echo json_encode($Approvaltype);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$Approvaltype = Approvaltype::find($id);
						foreach($data as $key=>$val){					
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$Approvaltype->$key=$val;
						}
						$Approvaltype->save();
						echo json_encode($Approvaltype);
						break;
					case 'byid':
						$id = $this->post['id'];
						$Approvaltype = Approvaltype::find($id);
						echo json_encode($Approvaltype);
						break;
					default:
						$Approvaltype = Approvaltype::all();
						foreach ($Approvaltype as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Approvaltype, JSON_NUMERIC_CHECK);
						break;					
				}
			}	
		}
	}
}