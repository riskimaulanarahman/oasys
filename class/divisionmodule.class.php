<?php
Class DivisionModule extends Application{
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
				case 'apidiv':
					$this->DivisionManager();
					break;				
				default:
					break;
			}
		}
	}
	
	public function DivisionManager(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'all':
						$division = Division::all();
						foreach ($division as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($division);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$division = Division::create($data);
						break;
					case 'delete':				
						$id = $this->post['id'];
						$division = Division::find($id);
						$division->delete();
						echo json_encode($division);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$division = Division::find($id);
						foreach($data as $key=>$val){					
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$division->$key=$val;
						}
						$division->save();
						echo json_encode($division);
						break;
					case 'byid':
						$id = $this->post['id'];
						$division = Division::find($id);
						echo json_encode($division);
						break;
					default:
						$division = Division::all();
						foreach ($division as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($division, JSON_NUMERIC_CHECK);
						break;					
				}
			}	
		}
	}
}