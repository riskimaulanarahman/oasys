<?php
Class ApproverModule extends Application{
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
				case 'apiappr':
					$this->approverManager();
					break;
				default:
					break;
			}
		}
	}
	public function approverManager(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'all':
						$Approver = Approver::all(array('include' => array('employee')));
						foreach ($Approver as &$result) {
							$dept=$result->employee->department->departmentname;
							$des=$result->employee->designation->designationname;
							$fullname=$result->employee->fullname;
							$result = $result->to_array();
							$result['fullname']=$fullname;
							$result['department']=$dept;
							$result['designation']=$des;
						}					
						echo json_encode($Approver, JSON_NUMERIC_CHECK);
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['module'])){
							if(($query['mode']=='report') || ($query['mode']=='view') || ($query['mode']=='approve')){
								$Approver = Approver::find('all', array('conditions' => array("module=? ",$query['module']),'include' => array('employee','approvaltype')));
							}else{
								if($query['type'] == 'buyer') {
									$Approver = Approver::find('all', array('conditions' => array("module=? AND approvaltype_id = 25 ",$query['module']),'include' => array('employee','approvaltype')));
								} else {
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
									$Approver = Approver::find('all', array('conditions' => array("module=? ",$query['module']),'include' => array('employee','approvaltype')));
								}
								// $Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
								// $Approver = Approver::find('all', array('conditions' => array("module=? ",$query['module']),'include' => array('employee','approvaltype')));
							}
							foreach ($Approver as &$result) {
								$dept=$result->employee->department->departmentname;
								$des=$result->employee->designation->designationname;
								$fullname=$result->employee->fullname;
								$apptype=$result->approvaltype->approvaltype;
								$result = $result->to_array();
								$result['fullname']=$fullname;
								$result['department']=$dept;
								$result['designation']=$des;
								$result['approvaltype']=$apptype;
							}
							$data = $Approver;
						}else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':		
						try{
							$data = $this->post['data'];
							unset($data['__KEY__']);
							$Approver = Approver::create($data);
							if ($Approver){
								$data = array("status"=>"success","message"=>"Query Success", "data"=>$Approver);
							} else{
								$data = array("status"=>"warning","message"=>"Query Returned 0 result", "data"=>$Approver);
							}
						}catch (Exception $e){
							$data = array("status"=>"error","message"=>$e->getMessage(), "data"=>array());
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'delete':
						try{
							$id = $this->post['id'];
							$Approver = Approver::find($id);
							$Approver->delete();
							if ($Approver){
								$data = array("status"=>"success","message"=>"Query Success", "data"=>$Approver);
							} else{
								$data = array("status"=>"warning","message"=>"Query Returned 0 result", "data"=>$Approver);
							}
						}catch (Exception $e){
							$data = array("status"=>"error","message"=>$e->getMessage(), "data"=>array());
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'update':
						try{
							$id = $this->post['id'];
							$data = $this->post['data'];
							$Approver = Approver::find($id);
							foreach($data as $key=>$val){
								$val=($val=='false')?false:(($val=='true')?true:$val);
								$Approver->$key=$val;
							}
							$Approver->save();
							if ($Approver){
								$data = array("status"=>"success","message"=>"Query Success", "data"=>$Approver);
							} else{
								$data = array("status"=>"warning","message"=>"Query Returned 0 result", "data"=>$Approver);
							}
						}catch (Exception $e){
							$data = array("status"=>"error","message"=>$e->getMessage(), "data"=>array());
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'byid':
						$id = $this->post['id'];
						$Approver = Approver::find($id);
						echo json_encode($Approver);
						break;
					default:
						$Approver = Approver::all();
						foreach ($Approver as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Approver, JSON_NUMERIC_CHECK);
						break;					
				}
			}	
		}
	}
}