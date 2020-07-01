<?php
Class HolidayModule extends Application{
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
				case 'apiholiday':
					$this->HolidayManager();
					break;				
				default:
					break;
			}
		}
	}
	
	public function HolidayManager(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'all':
						$Holiday = Holiday::all();
						foreach ($Holiday as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Holiday);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Holiday = Holiday::create($data);
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Holiday = Holiday::find($id);
						$Holiday->delete();
						echo json_encode($Holiday);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$Holiday = Holiday::find($id);
						foreach($data as $key=>$val){					
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$Holiday->$key=$val;
						}
						$Holiday->save();
						echo json_encode($Holiday);
						break;
					case 'byid':
						$id = $this->post['id'];
						$Holiday = Holiday::find($id);
						echo json_encode($Holiday);
						break;
					default:
						$Holiday = Holiday::all();
						foreach ($Holiday as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Holiday, JSON_NUMERIC_CHECK);
						break;					
				}
			}	
		}
	}
}