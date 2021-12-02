<?php

Class Dashboardmodule extends Application{
	private $mailbody;
	private $mail;
	public function __construct(){
		parent::__construct();
		
		$this->get = isset($this->get)?$this->get:$_GET;
		$this->post = isset($this->post)?$this->post:$_POST;
		$this->heading = "";
		$this->output = "";
		$this->script = "";
		$this->method = $_SERVER['REQUEST_METHOD'];
		$this->currentUser= $this->jwt->getUser();
		$this->ip = USER_IP;

		if (isset($this->get)){
			switch ($this->get['action']){
				case 'apidashboard':
					$this->dashboard();
					break;
				default:
					break;
			}
		}
	}
	

	function dashboard(){	
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							switch ($query['status']){

								case 'pendingapproval':
									
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
									$emp_id = $Employee->id;

									$raw = Pendingappr::find('all',array('conditions' => array('employee_id=?',$emp_id)));
									// $raw = Pendingappr::all();
									foreach ($raw as &$result) {
										$result		= $result->to_array();
									}
								
									echo json_encode($raw, JSON_NUMERIC_CHECK);

								break;

								case 'pendingrequest':
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
									$emp_id = $Employee->id;

									$raw = Pendingreq::find('all',array('conditions' => array('employee_id=?',$emp_id)));
									// $raw = Pendingappr::all();
									foreach ($raw as &$result) {
										$result		= $result->to_array();
									}

									echo json_encode($raw, JSON_NUMERIC_CHECK);


									// $data=array(
									// 	"jml_Dayoff"=>count($Dayoff),
									// 	"jml_Spkl"=>count($Spkl),
									// 	"jml_Spklts"=>count($Spklts),
									// 	"jml_Tr"=>count($Tr),
									// 	"jml_Rfc"=>count($Rfc),
									// 	"jml_Mmf28"=>count($Mmf),
									// 	"jml_Mmf30"=>count($Mmf30),
									// 	"jml_Iteie"=>count($Iteie),
									// 	"jml_Itimail"=>count($Itimail),
									// 	"jml_Itsharef"=>count($Itsharef),
									// 	"jml_Advance"=>count($Advance),
									// 	"jml_Advpayment"=>count($Advpayment),
									// 	"jml_Advexpense"=>count($Advexpense)
									// );
								break;

								
								default:
									// $Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
									// $Advexpense = Advexpense::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									// foreach ($Advexpense as &$result) {
									// 	$fullname	= $result->employee->fullname;		
									// 	$result		= $result->to_array();
									// 	$result['fullname']=$fullname;
									// }
									// $data=array("jml"=>count($Advexpense));
								break;
							}
							
						} else{
							$data=array();
						}
						// echo json_encode($data);
					break;
					default:
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
						if ($Employee){
							$Advexpense = Advexpense::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Advexpense as &$result) {
								$fullname=$result->employee->fullname;
								$result = $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Advexpense, JSON_NUMERIC_CHECK);
						}else{
							echo json_encode(array());
						}
						break;
				}
			}
		}
	}

}