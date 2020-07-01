 <?php
Class LeaveModule extends Application{
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
				case 'apileavebyemp':
					$this->LeaveByEmp();
					break;
				default:
					break;
			}
		}
	}
	function LeaveByEmp(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'byid':
						$id = $this->post['id'];
						$Employee = Employee::find($id);
						if ($Employee){
							$Leave = Leave::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Leave as &$result) {
								$fullname=$result->employee->fullname;		
								$result = $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Leave, JSON_NUMERIC_CHECK);
						}else{
							echo json_encode(array());
						}
						break;
					default:
						$Leave = Leave::all(array('include' => array('employee')));
						foreach ($Leave as &$result) {
							$fullname=$result->employee->fullname;		
							$result = $result->to_array();
							$result['fullname']=$fullname;
						}					
						echo json_encode($Leave, JSON_NUMERIC_CHECK);
						break;	
				}
			}
		}
	}
}