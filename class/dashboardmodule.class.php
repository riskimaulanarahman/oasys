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

									//WPC / DAYOFF
									$Dayoff = Dayoff::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
									foreach ($Dayoff as $result) {
										$joinx   = "LEFT JOIN tbl_approver ON (tbl_dayoffapproval.approver_id = tbl_approver.id) ";					
										$Dayoffapproval = Dayoffapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and dayoff_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
										if($Dayoffapproval->approver->employee_id==$emp_id){
											$request[]=$result->id;
										}
									}
									$Dayoff = Dayoff::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
									foreach ($Dayoff as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									//SPKL
									$Spkl = Spkl::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
									foreach ($Spkl as $result) {
										$joinx   = "LEFT JOIN tbl_approver ON (tbl_spklapproval.approver_id = tbl_approver.id) ";					
										$Spklapproval = Spklapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and spkl_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
										if($Spklapproval->approver->employee_id==$emp_id){
											$request[]=$result->id;
										}
									}
									$Spkl = Spkl::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
									foreach ($Spkl as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									//SPKL TS
									$Spklts = Spkl::find('all', array('conditions' => array("tmsreqstatus =1"),'include' => array('employee')));
									foreach ($Spklts as $result) {
										$joinx   = "LEFT JOIN tbl_approver ON (tbl_spklotapproval.approver_id = tbl_approver.id) ";					
										$Spkltstmsapproval = Spkltmsapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and spkl_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
										if($Spkltstmsapproval->approver->employee_id==$emp_id){
											$request[]=$result->id;
										}
									}
									$Spklts = Spkl::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
									foreach ($Spklts as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									//TR
									$Tr = Tr::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
									foreach ($Tr as $result) {
										$joinx   = "LEFT JOIN tbl_approver ON (tbl_trapproval.approver_id = tbl_approver.id) ";					
										$Trapproval = Trapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and tr_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
										if($Trapproval->approver->employee_id==$emp_id){
											$request[]=$result->id;
										}
									}
									$Tr = Tr::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
									foreach ($Tr as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									//RFC
									$Rfc = Rfc::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
									foreach ($Rfc as $result) {
										$joinx   = "LEFT JOIN tbl_approver ON (tbl_rfcapproval.approver_id = tbl_approver.id) ";					
										$Rfcapproval = Rfcapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and rfc_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
										if($Rfcapproval->approver->employee_id==$emp_id){
											$request[]=$result->id;
										}
									}
									$Rfc = Rfc::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
									foreach ($Rfc as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									//MMF28
									$Mmf28 = Mmf::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
									foreach ($Mmf28 as $result) {
										$joinx   = "LEFT JOIN tbl_approver ON (tbl_mmf28approval.approver_id = tbl_approver.id) ";					
										$Mmf28approval = Mmfapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and mmf28_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
										if($Mmf28approval->approver->employee_id==$emp_id){
											$request[]=$result->id;
										}
									}
									$Mmf28 = Mmf::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
									foreach ($Mmf28 as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									//MMF 30
									$Mmf30 = Mmf30::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
									foreach ($Mmf30 as $result) {
										$joinx   = "LEFT JOIN tbl_approver ON (tbl_mmf30approval.approver_id = tbl_approver.id) ";					
										$Mmf30approval = Mmf30approval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and mmf30_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
										if($Mmf30approval->approver->employee_id==$emp_id){
											$request[]=$result->id;
										}
									}
									$Mmf30 = Mmf30::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
									foreach ($Mmf30 as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									//IT ACTIVE DIRECTORY
									$Iteie = Iteie::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
									foreach ($Iteie as $result) {
										$joinx   = "LEFT JOIN tbl_approver ON (tbl_iteieapproval.approver_id = tbl_approver.id) ";					
										$Iteieapproval = Iteieapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and iteie_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
										if($Iteieapproval->approver->employee_id==$emp_id){
											$request[]=$result->id;
										}
									}
									$Iteie = Iteie::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
									foreach ($Iteie as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									//IT REQUEST FORM
									$Itimail = Itimail::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
									foreach ($Itimail as $result) {
										$joinx   = "LEFT JOIN tbl_approver ON (tbl_itimailapproval.approver_id = tbl_approver.id) ";					
										$Itimailapproval = Itimailapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and itimail_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
										if($Itimailapproval->approver->employee_id==$emp_id){
											$request[]=$result->id;
										}
									}
									$Itimail = Itimail::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
									foreach ($Itimail as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									//IT SHARE FOLDER
									$Itsharef = Itsharef::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
									foreach ($Itsharef as $result) {
										$joinx   = "LEFT JOIN tbl_approver ON (tbl_itsharefapproval.approver_id = tbl_approver.id) ";					
										$Itsharefapproval = Itsharefapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and itsharef_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
										if($Itsharefapproval->approver->employee_id==$emp_id){
											$request[]=$result->id;
										}
									}
									$Itsharef = Itsharef::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
									foreach ($Itsharef as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									//ADVANCE
									$Advance = Advance::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
									foreach ($Advance as $result) {
										$joinx   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";					
										$Advanceapproval = Advanceapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and advance_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
										if($Advanceapproval->approver->employee_id==$emp_id){
											$request[]=$result->id;
										}
									}
									$Advance = Advance::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
									foreach ($Advance as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									//ADVANCE PAYMENT
									$Advpayment = Advpayment::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
									foreach ($Advpayment as $result) {
										$joinx   = "LEFT JOIN tbl_approver ON (tbl_advpaymentapproval.approver_id = tbl_approver.id) ";					
										$Advpaymentapproval = Advpaymentapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and advpayment_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
										if($Advpaymentapproval->approver->employee_id==$emp_id){
											$request[]=$result->id;
										}
									}
									$Advpayment = Advpayment::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
									foreach ($Advpayment as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									//ADVANCE EXPENSE
									$Advexpense = Advexpense::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
									foreach ($Advexpense as $result) {
										$joinx   = "LEFT JOIN tbl_approver ON (tbl_advexpenseapproval.approver_id = tbl_approver.id) ";					
										$Advexpenseapproval = Advexpenseapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and advexpense_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
										if($Advexpenseapproval->approver->employee_id==$emp_id){
											$request[]=$result->id;
										}
									}
									$Advexpense = Advexpense::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
									foreach ($Advexpense as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}

									$data=array(
										"jml_Dayoff"=>count($Dayoff),
										"jml_Spkl"=>count($Spkl),
										"jml_Spklts"=>count($Spklts),
										"jml_Tr"=>count($Tr),
										"jml_Rfc"=>count($Rfc),
										"jml_Mmf28"=>count($Mmf28),
										"jml_Mmf30"=>count($Mmf30),
										"jml_Iteie"=>count($Iteie),
										"jml_Itimail"=>count($Itimail),
										"jml_Itsharef"=>count($Itsharef),
										"jml_Advance"=>count($Advance),
										"jml_Advpayment"=>count($Advpayment),
										"jml_Advexpense"=>count($Advexpense)
									);
								break;

								case 'pendingrequest':
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));

									//WPC / DAYOFF
									$Dayoff = Dayoff::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									//SPKL
									$Spkl = Spkl::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									//SPKL TS
									$Spklts = Spkl::find('all', array('conditions' => array("employee_id=? and RequestStatus='3' and TMSReqStatus<3",$Employee->id),'include' => array('employee')));
									//TR
									$Tr = Tr::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									//RFC
									$Rfc = Rfc::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
									//MMF28
									$Mmf = Mmf::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									//MMF 30
									$Mmf30 = Mmf30::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									//IT ACTIVE DIRECTORY
									$Iteie = Iteie::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									//IT REQUEST FORM
									$Itimail = Itimail::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									//IT SHARE FOLDER
									$Itsharef = Itsharef::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									//ADVANCE
									$Advance = Advance::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									//ADVANCE PAYMENT
									$Advpayment = Advpayment::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									//ADVANCE EXPENSE
									$Advexpense = Advexpense::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));

									$data=array(
										"jml_Dayoff"=>count($Dayoff),
										"jml_Spkl"=>count($Spkl),
										"jml_Spklts"=>count($Spklts),
										"jml_Tr"=>count($Tr),
										"jml_Rfc"=>count($Rfc),
										"jml_Mmf28"=>count($Mmf),
										"jml_Mmf30"=>count($Mmf30),
										"jml_Iteie"=>count($Iteie),
										"jml_Itimail"=>count($Itimail),
										"jml_Itsharef"=>count($Itsharef),
										"jml_Advance"=>count($Advance),
										"jml_Advpayment"=>count($Advpayment),
										"jml_Advexpense"=>count($Advexpense)
									);
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
						echo json_encode($data);
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