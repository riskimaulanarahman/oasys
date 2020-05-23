<?php
Class GradeModule extends Application{
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
				case 'apigrade':
					$this->GradeManager();
					break;				
				default:
					break;
			}
		}
	}
	
	public function GradeManager(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'all':
						$grade = Grade::all();
						foreach ($grade as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($grade);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$grade = Grade::create($data);
						break;
					case 'delete':				
						$id = $this->post['id'];
						$grade = Grade::find($id);
						$grade->delete();
						echo json_encode($grade);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$grade = Grade::find($id);
						foreach($data as $key=>$val){					
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$grade->$key=$val;
						}
						$grade->save();
						echo json_encode($grade);
						break;
					case 'byid':
						$id = $this->post['id'];
						$grade = Grade::find($id);
						echo json_encode($grade);
						break;
					default:
						$grade = Grade::all();
						foreach ($grade as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($grade, JSON_NUMERIC_CHECK);
						break;					
				}
			}	
		}
	}
}