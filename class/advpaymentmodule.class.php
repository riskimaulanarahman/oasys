<?php
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

Class Advpaymentmodule extends Application{
	private $mailbody;
	private $mail;
	private $pathcopy;
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
		$this->mail = new PHPMailer;
		$this->mail->isSMTP();
		$this->mail->SMTPDebug = 0;
		$this->mail->Host = SMTPSERVER;
		$this->mail->Port = 465;
		$this->mail->SMTPSecure = 'tls';
		$this->mail->SMTPAuth = true;
		$this->mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);

		$this->mail->Username = MAILFROM;
		$this->mail->Password = SMTPAUTH;
		$this->mail->setFrom(MAILFROM,"Online Approval System");
		//$this->mail->addReplyTo('Purwanto_ihm@itci-hutani.com', 'Purwanto');
		$this->mailbody = '<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:m="http://schemas.microsoft.com/office/2004/12/omml" xmlns="http://www.w3.org/TR/REC-html40"><head><meta http-equiv=Content-Type content="text/html; charset=us-ascii"><meta name=Generator content="Microsoft Word 15 (filtered medium)"><style><!--
						/* Font Definitions */
						@font-face {font-family:Wingdings; panose-1:5 0 0 0 0 0 0 0 0 0;} @font-face {font-family:"Cambria Math"; panose-1:2 4 5 3 5 4 6 3 2 4;} @font-face {font-family:Calibri; panose-1:2 15 5 2 2 2 4 3 2 4;} @font-face {font-family:"Century Gothic"; panose-1:2 11 5 2 2 2 2 2 2 4;}
						/* Style Definitions */
						p.MsoNormal, li.MsoNormal, div.MsoNormal {margin:0in; margin-bottom:.0001pt; font-size:11.0pt; font-family:"Calibri","sans-serif";} a:link, span.MsoHyperlink {mso-style-priority:99; color:#0563C1; text-decoration:underline;} a:visited, span.MsoHyperlinkFollowed {mso-style-priority:99; color:#954F72; text-decoration:underline;} span.EmailStyle17 {mso-style-type:personal-reply;	font-family:"Calibri","sans-serif";	color:#1F497D;} .MsoChpDefault {mso-style-type:export-only;} @page WordSection1 {size:8.5in 11.0in;margin:1.0in 1.0in 1.0in 1.0in;} div.WordSection1 {page:WordSection1;} --></style><!--[if gte mso 9]><xml><o:shapedefaults v:ext="edit" spidmax="1026" /></xml><![endif]--><!--[if gte mso 9]><xml><o:shapelayout v:ext="edit"><o:idmap v:ext="edit" data="1" /></o:shapelayout></xml><![endif]--></head>';
		if (isset($this->get)){
			switch ($this->get['action']){
				case 'apiadvpaymentbyemp':
					$this->advpaymentByEmp();
					break;
				case 'apiadvpayment':
					$this->advpayment();
					break;
				case 'apiadvpaymentdetail':
					$this->advpaymentDetail();
					break;
				case 'apiadvpaymentapp':
					$this->advpaymentApproval();
					break;
				case 'apiadvpaymenthist':
					$this->advpaymentHistory();
					break;
				case 'apiadvpaymentpdf':	
					$id = $this->get['id'];
					$this->generatePDFi($id);
					break;
				case 'apiadvpaymentfile':
					$this->advpaymentAttachment();
					break;
				case 'uploadadvpaymentfile':
					$this->uploadadvpaymentFile();
					break;
				default:
					break;
			}
		}
	}
	

	function advpaymentByEmp(){	
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
							$Advpayment = Advpayment::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Advpayment as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Advpayment, JSON_NUMERIC_CHECK);
						}else{
							echo json_encode(array());
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							switch ($query['status']){
								case 'waiting':
									$Advpayment = Advpayment::find('all', array('conditions' => array("employee_id=? and RequestStatus<3 and id<>?",$query['username'],$query['id']),'include' => array('employee')));
									foreach ($Advpayment as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Advpayment));
									break;
								default:
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
									$Advpayment = Advpayment::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Advpayment as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Advpayment));
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
							$Advpayment = Advpayment::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Advpayment as &$result) {
								$fullname=$result->employee->fullname;
								$result = $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Advpayment, JSON_NUMERIC_CHECK);
						}else{
							echo json_encode(array());
						}
						break;
				}
			}
		}
	}
	
	function advpayment(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'byid':
						$id = $this->post['id'];
						$join = "LEFT JOIN vwadvpaymentreport ON tbl_advpayment.id = vwadvpaymentreport.id";
						$select = "tbl_advpayment.*,vwadvpaymentreport.apprstatuscode";
						$Advpayment = Advpayment::find($id, array('joins'=>$join,'select'=>$select,'include' => array('employee'=>array('company','department','designation'))));

						// $Advpayment = Advpayment::find($id, array('include' => array('employee'=>array('company','department','designation'))));

						// $Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND requeststatus=5",$Advpayment->employee->id)));
				
						// $AdvanceDetail = AdvanceDetail::find('all',array('conditions'=> array("advance_id=?",$Advance->id)));
						// foreach ($AdvanceDetail as &$data) {
						// 	$val_tamount += $data->amount;
						// }
						
						// echo number_format($val_tamount);
						// echo json_encode($AdvanceDetail, JSON_NUMERIC_CHECK);
						if ($Advpayment){
							$fullname = $Advpayment->employee->fullname;
							$department = $Advpayment->employee->department->departmentname;
							$data=$Advpayment->to_array();
							$data['fullname']=$fullname;
							$data['paymentform']=$Advpayment->paymentform;
							$data['department']=$department;
							echo json_encode($data, JSON_NUMERIC_CHECK);
						}else{
							$Advpayment = new Advpayment();
							echo json_encode($Advpayment);
						}
						break;
					case 'find':
						$query=$this->post['query'];					
						if(isset($query['status'])){
							switch ($query['status']){
								case "last":
									break;
									case 'appcon':
										$valamount = $query['valamount'];
										$advpayment_form = $query['formtype'];
										$employee_id = $query['employee_id'];
										$id= $query['advpayment_id'];

										$Advpayment = Advpayment::find($id);

										$Employee = Employee::find('first', array('conditions' => array("id=?",$employee_id),"include"=>array("location","department","company")));

										print_r($valamount);

										$joins   = "LEFT JOIN tbl_approver ON (tbl_advpaymentapproval.approver_id = tbl_approver.id) LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id)";
										$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";	
										// if (($valamount=='2') || ($valamount=='3')){
										$AdvpaymentapprovalBU = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id='38' ",$id)));	
										foreach ($AdvpaymentapprovalBU as &$result) {
											$result		= $result->to_array();
											$result['no']=1;
										}
										$Advpaymentapprovaldepmd = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id='39' ",$id)));	
										foreach ($Advpaymentapprovaldepmd as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}
										$Advpaymentapprovalmd = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id='40' ",$id)));	
										foreach ($Advpaymentapprovalmd as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}

										$Advpaymentapprovalproc = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id='42' ",$id)));	
										foreach ($Advpaymentapprovalproc as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}

									break;
									case 'savelessadv':
										$id= $query['advpayment_id'];
										$advanceno = $query['advanceno'];
										$paymenttype = $query['paymenttype'];
										$employee_id = $query['employee_id'];

										$Advpayment = Advpayment::find($id, array('include' => array('employee'=>array('company','department','designation'))));

										//check lessadvance
										if($paymenttype == false || $paymenttype == 'false') {
											$valpaymenttype = 0;
										} else if ($paymenttype == true || $paymenttype == 'true') {
											$valpaymenttype = 1;
										}

										if($paymenttype !== null) {
											$Advpayment->paymenttype = $valpaymenttype;
										}

										if($Advpayment->requeststatus == 0) {
											
												if($advanceno !== null) {

													$Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND advanceno=?",$Advpayment->employee_id,$advanceno)));
													
													$AdvanceDetail = AdvanceDetail::find('all',array('conditions'=> array("advance_id=?",$Advance->id)));
													foreach ($AdvanceDetail as $val) {
														$val_tamount += $val->amount;
													}
													
													$Advpayment->advanceno = $advanceno;
													$Advpayment->lessadvance = $val_tamount;
													$Advpayment->save();
													
												// echo 'adaa';
												
											} else {
												$Advpayment->advanceno = null;
												$Advpayment->lessadvance = null;
												$Advpayment->save();
												
												// echo 'tidaak';
											}
										}

										


										if($Advpayment) {
											$item['message']=200;
											$item['amount']=$Advpayment->lessadvance;
											echo json_encode($item, JSON_NUMERIC_CHECK);
										} else {
											$item['message']=404;
											echo json_encode($item, JSON_NUMERIC_CHECK);
										}
									break;
									case 'checkform':
										$advpayment_form = $query['formtype'];
										$employee_id = $query['employee_id'];
										$id= $query['advpayment_id'];
										
										$Advpayment = Advpayment::find($id, array('include' => array('employee'=>array('company','department','designation'))));
										$Advpayment->paymentform = $advpayment_form;
										$Advpayment->save();
										
										$Employee = Employee::find('first', array('conditions' => array("id=?",$employee_id),"include"=>array("location","department","company")));

										// print_r($advpayment_form);
										$data['companycode']=$Employee->companycode;

										if($Advpayment->requeststatus == 0) {

											
												if($advpayment_form == 1) {

													//check lessadvance
													$Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND requeststatus=3 AND advanceform=1 AND isused=0",$Advpayment->employee_id)));
													
													if($Advance) {
														$item['message']=200;
														echo json_encode($item, JSON_NUMERIC_CHECK);
													} else {
														$Advpayment = Advpayment::find($id, array('include' => array('employee'=>array('company','department','designation'))));
														$Advpayment->paymenttype = 0;
														$Advpayment->advanceno = null;
														$Advpayment->lessadvance = null;
														$Advpayment->save();
														$item['message']=404;
														echo json_encode($item, JSON_NUMERIC_CHECK);
													}
													
												} else if($advpayment_form == 2) {
													
													//check lessadvance
													$Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND requeststatus=3 AND advanceform=2 AND isused=0",$Advpayment->employee_id)));
													
												if($Advance) {
													$item['message']=200;
													// $item['lessadvance']=$val_tamount;
													echo json_encode($item, JSON_NUMERIC_CHECK);
												} else {
													$Advpayment = Advpayment::find($id, array('include' => array('employee'=>array('company','department','designation'))));
													$Advpayment->paymenttype = 0;
													$Advpayment->advanceno = null;
													$Advpayment->lessadvance = null;
													$Advpayment->save();
													$item['message']=404;
													echo json_encode($item, JSON_NUMERIC_CHECK);
												}
											}

										} else if($Advpayment->requeststatus == 3 || $Advpayment->requeststatus == 4) {
											if($advpayment_form == 1) {

												//check lessadvance
												$Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND requeststatus=3 AND advanceform=1 AND isused=1",$Advpayment->employee_id)));
												
												if($Advance) {
													$item['message']=200;
													echo json_encode($item, JSON_NUMERIC_CHECK);
												} else {
													$item['message']=404;
													echo json_encode($item, JSON_NUMERIC_CHECK);
												}
												
											} else if($advpayment_form == 2) {
												
												//check lessadvance
												$Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND requeststatus=3 AND advanceform=2 AND isused=1",$Advpayment->employee_id)));
												
												if($Advance) {
													$item['message']=200;
													// $item['lessadvance']=$val_tamount;
													echo json_encode($item, JSON_NUMERIC_CHECK);
												} else {
													$item['message']=404;
													echo json_encode($item, JSON_NUMERIC_CHECK);
												}
											}
										} else {
											if($advpayment_form == 1) {

												//check lessadvance
												$Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND requeststatus=3 AND advanceform=1 AND isused=0",$Advpayment->employee_id)));
												
												if($Advance) {
													$item['message']=200;
													echo json_encode($item, JSON_NUMERIC_CHECK);
												} else {
													$item['message']=404;
													echo json_encode($item, JSON_NUMERIC_CHECK);
												}
												
											} else if($advpayment_form == 2) {
												
												//check lessadvance
												$Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND requeststatus=3 AND advanceform=2 AND isused=0",$Advpayment->employee_id)));
												
												if($Advance) {
													$item['message']=200;
													// $item['lessadvance']=$val_tamount;
													echo json_encode($item, JSON_NUMERIC_CHECK);
												} else {
													$item['message']=404;
													echo json_encode($item, JSON_NUMERIC_CHECK);
												}
											}
										}
										
									break;
									case 'appform':
										$advpayment_form = $query['formtype'];
										$employee_id = $query['employee_id'];
										$id= $query['advpayment_id'];

										$Employee = Employee::find('first', array('conditions' => array("id=?",$employee_id),"include"=>array("location","department","company")));
										$data['companycode']=$Employee->companycode;
										
										$Advpayment = Advpayment::find($id, array('include' => array('employee'=>array('company','department','designation'))));
										$Advpayment->paymentform = $advpayment_form;
										if($Advpayment->companycode == null) {
											$Advpayment->companycode = $Employee->companycode;
										}
										if($advpayment_form == 1) {
											$Advpayment->opscategory = 0;
										}
										$Advpayment->save();
										

										// print_r($advpayment_form);

										$joins   = "LEFT JOIN tbl_approver ON (tbl_advpaymentapproval.approver_id = tbl_approver.id) LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id)";
										$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";	

										$Advpaymentapprovalhrd = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id='36' ",$id)));	
										foreach ($Advpaymentapprovalhrd as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}
										$Advpaymentapprovalproc = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id='42' ",$id)));	
										foreach ($Advpaymentapprovalproc as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}
										
										if($advpayment_form == 1) {

											

											$Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND requeststatus=3 AND advanceform=1 AND isused=0",$Advpayment->employee->id)));

											if($Advance) {
												$item['message']=200;
												echo json_encode($item, JSON_NUMERIC_CHECK);
											} else {
												$Advpayment = Advpayment::find($id, array('include' => array('employee'=>array('company','department','designation'))));
												$Advpayment->paymenttype = 0;
												$Advpayment->advanceno = null;
												$Advpayment->lessadvance = null;
												$Advpayment->save();
												$item['message']=404;
												echo json_encode($item, JSON_NUMERIC_CHECK);
											}

											$kfssl = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=45",$id)));	
											foreach ($kfssl as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval KF SSL");
												$logger->SaveData();
											}

											$financecomit = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=41",$id)));	
											foreach ($financecomit as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval Finance Commite");
												$logger->SaveData();
											}

											$bufc = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=37",$id)));	
											foreach ($bufc as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval BUFC");
												$logger->SaveData();
											}

											$hrverifikator = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=44",$id)));	
											foreach ($hrverifikator as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval HR Verifikator");
												$logger->SaveData();
											}
											
											$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and FIND_IN_SET(?, CompanyList) > 0 ",$Advpayment->companycode)));
											if(count($ApproverHRV)>0){
												$Advpaymentapproval = new Advpaymentapproval();
												$Advpaymentapproval->advpayment_id = $Advpayment->id;
												$Advpaymentapproval->approver_id = $ApproverHRV->id;
												$Advpaymentapproval->save();
												$logger = new Datalogger("Advpaymentapproval","add","Add initial HR Verifikator Approval ",json_encode($Advpaymentapproval->to_array()));
												$logger->SaveData();
											}

											$buhead = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=38",$id)));	
											foreach ($buhead as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval BU HEAD");
												$logger->SaveData();
											}

											$hrd = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=36",$id)));	
											foreach ($hrd as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval HRD");
												$logger->SaveData();
											}

											$Approverbufc = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37' and FIND_IN_SET(?, CompanyList) > 0 ",$Advpayment->companycode)));
											if(count($Approverbufc)>0){
												$Advpaymentapproval = new Advpaymentapproval();
												$Advpaymentapproval->advpayment_id = $Advpayment->id;
												$Advpaymentapproval->approver_id = $Approverbufc->id;
												$Advpaymentapproval->save();
												$logger = new Datalogger("Advpaymentapproval","add","Add initial BU FC Approval ",json_encode($Advpaymentapproval->to_array()));
												$logger->SaveData();
											}

											$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='36' and FIND_IN_SET(?, CompanyList) > 0 ",$Advpayment->companycode)));
											if(count($Approver2)>0){
												$Advpaymentapproval = new Advpaymentapproval();
												$Advpaymentapproval->advpayment_id = $Advpayment->id;
												$Advpaymentapproval->approver_id = $Approver2->id;
												$Advpaymentapproval->save();
												$logger = new Datalogger("Advpaymentapproval","add","Add initial HRD Approval ",json_encode($Advpaymentapproval->to_array()));
												$logger->SaveData();
											}

											$ApproverBU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and FIND_IN_SET(?, CompanyList) > 0 ",$Advpayment->companycode)));
											
											if(count($ApproverBU)>0){
												$Advpaymentapproval = new Advpaymentapproval();
												$Advpaymentapproval->advpayment_id = $Advpayment->id;
												$Advpaymentapproval->approver_id = $ApproverBU->id;
												$Advpaymentapproval->save();
												$logger = new Datalogger("Advpaymentapproval","add","Add initial BU Head Approval ",json_encode($Advpaymentapproval->to_array()));
												$logger->SaveData();
											}

										} else if($advpayment_form == 2) {
											

											$bufc = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=37",$id)));	
											foreach ($bufc as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval BUFC");
												$logger->SaveData();
											}

											$financecomit = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=41",$id)));	
											foreach ($financecomit as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval Finance Commite");
												$logger->SaveData();
											}

											//check lessadvance
											$Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND requeststatus=3 AND advanceform=2 AND isused=0",$Advpayment->employee->id)));

											if($Advance) {
												$item['message']=200;
												// $item['lessadvance']=$val_tamount;
												echo json_encode($item, JSON_NUMERIC_CHECK);
											} else {
												$Advpayment = Advpayment::find($id, array('include' => array('employee'=>array('company','department','designation'))));
												$Advpayment->paymenttype = 0;
												$Advpayment->advanceno = null;
												$Advpayment->lessadvance = null;
												$Advpayment->save();
												$item['message']=404;
												echo json_encode($item, JSON_NUMERIC_CHECK);
											}

											$hrd = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=36",$id)));	
											foreach ($hrd as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval HRD");
												$logger->SaveData();
											}
											

											$hrv = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=44",$id)));	
											foreach ($hrv as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval HR Verifikator");
												$logger->SaveData();
											}

											$buhead = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=38",$id)));	
											foreach ($buhead as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval BU HEAD");
												$logger->SaveData();
											}

											$Approverbufc = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37' and FIND_IN_SET(?, CompanyList) > 0 ",$Advpayment->companycode)));
											if(count($Approverbufc)>0){
												$Advpaymentapproval = new Advpaymentapproval();
												$Advpaymentapproval->advpayment_id = $Advpayment->id;
												$Advpaymentapproval->approver_id = $Approverbufc->id;
												$Advpaymentapproval->save();
												$logger = new Datalogger("Advpaymentapproval","add","Add initial BU FC Approval ",json_encode($Advpaymentapproval->to_array()));
												$logger->SaveData();
											}

											$ApproverBU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and FIND_IN_SET(?, CompanyList) > 0 ",$Advpayment->companycode)));
										
											if(count($ApproverBU)>0){
												$Advpaymentapproval = new Advpaymentapproval();
												$Advpaymentapproval->advpayment_id = $Advpayment->id;
												$Advpaymentapproval->approver_id = $ApproverBU->id;
												$Advpaymentapproval->save();
												$logger = new Datalogger("Advpaymentapproval","add","Add initial BU Head Approval ",json_encode($Advpaymentapproval->to_array()));
												$logger->SaveData();
											}
										}

									break;
									case 'opscategory':
										$categorytype = $query['categorytype'];
										$employee_id = $query['employee_id'];
										$id= $query['advpayment_id'];
										
										$Employee = Employee::find('first', array('conditions' => array("id=?",$employee_id),"include"=>array("location","department","company")));

										$Advpayment = Advpayment::find($id);
										$Advpayment->opscategory = $categorytype;
										if($Advpayment->companycode == null) {
											$Advpayment->companycode = $Employee->companycode;
										}
										$Advpayment->save();


										// $data['companycode']=$Employee->companycode;

										$joins   = "LEFT JOIN tbl_approver ON (tbl_advpaymentapproval.approver_id = tbl_approver.id) LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id)";
										$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";	
										

										$Advpaymentapprovalhrd = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id='36' ",$id)));	
										foreach ($Advpaymentapprovalhrd as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}
										$Advpaymentapprovalproc = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id='42' ",$id)));	
										foreach ($Advpaymentapprovalproc as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}

										if($Advpayment->opscategory == 1) {

											$kfssl = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=45",$id)));	
											foreach ($kfssl as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval KF SSL");
												$logger->SaveData();
											}

											$financecomit = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=41",$id)));	
											foreach ($financecomit as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval Finance Commite");
												$logger->SaveData();
											}

											$buhead = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=38",$id)));	
											foreach ($buhead as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval BU Head");
												$logger->SaveData();
											}

											$bufc = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=37",$id)));	
											foreach ($bufc as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval BUFC");
												$logger->SaveData();
											}


											$ApproverBU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and FIND_IN_SET(?, CompanyList) > 0 ",$Advpayment->companycode)));
											if(count($ApproverBU)>0){
												$Advpaymentapproval = new Advpaymentapproval();
												$Advpaymentapproval->advpayment_id = $Advpayment->id;
												$Advpaymentapproval->approver_id = $ApproverBU->id;
												$Advpaymentapproval->save();
												$logger = new Datalogger("Advpaymentapproval","add","Add initial BU Head Approval ",json_encode($Advpaymentapproval->to_array()));
												$logger->SaveData();
											}

											$Approverbufc = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37' and FIND_IN_SET(?, CompanyList) > 0 ",$Advpayment->companycode)));
											if(count($Approverbufc)>0){
												$Advpaymentapproval = new Advpaymentapproval();
												$Advpaymentapproval->advpayment_id = $Advpayment->id;
												$Advpaymentapproval->approver_id = $Approverbufc->id;
												$Advpaymentapproval->save();
												$logger = new Datalogger("Advpaymentapproval","add","Add initial BU FC Approval ",json_encode($Advpaymentapproval->to_array()));
												$logger->SaveData();
											}


										} else if($Advpayment->opscategory == 2) {

											$kfssl = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=45",$id)));	
											foreach ($kfssl as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval KF SSL");
												$logger->SaveData();
											}

											$financecomit = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=41",$id)));	
											foreach ($financecomit as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval Finance Commite");
												$logger->SaveData();
											}
											$buhead = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=38",$id)));	
											foreach ($buhead as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval BU Head");
												$logger->SaveData();
											}

											$bufc = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=37",$id)));	
											foreach ($bufc as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval BUFC");
												$logger->SaveData();
											}

											$ApproverFC = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=41")));
											if(count($ApproverFC)>0){
												$Advpaymentapproval = new Advpaymentapproval();
												$Advpaymentapproval->advpayment_id = $Advpayment->id;
												$Advpaymentapproval->approver_id = $ApproverFC->id;
												$Advpaymentapproval->save();
												$logger = new Datalogger("Advpaymentapproval","add","Add initial Finance Commite Approval ",json_encode($Advpaymentapproval->to_array()));
												$logger->SaveData();
											}

											$Approverbufc = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37' and FIND_IN_SET(?, CompanyList) > 0 ",$Advpayment->companycode)));
											if(count($Approverbufc)>0){
												$Advpaymentapproval = new Advpaymentapproval();
												$Advpaymentapproval->advpayment_id = $Advpayment->id;
												$Advpaymentapproval->approver_id = $Approverbufc->id;
												$Advpaymentapproval->save();
												$logger = new Datalogger("Advpaymentapproval","add","Add initial BU FC Approval ",json_encode($Advpaymentapproval->to_array()));
												$logger->SaveData();
											}

										} else if($Advpayment->opscategory == 3) {

											$kfssl = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=45",$id)));	
											foreach ($kfssl as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval KF SSL");
												$logger->SaveData();
											}

											$bufc = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=37",$id)));	
											foreach ($bufc as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval BUFC");
												$logger->SaveData();
											}

											$financecomit = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=41",$id)));	
											foreach ($financecomit as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval Finance Commite");
												$logger->SaveData();
											}
											$buhead = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=38",$id)));	
											foreach ($buhead as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval BU Head");
												$logger->SaveData();
											}

											$Approverbufc = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37' and FIND_IN_SET(?, CompanyList) > 0 ",$Advpayment->companycode)));
											if(count($Approverbufc)>0){
												$Advpaymentapproval = new Advpaymentapproval();
												$Advpaymentapproval->advpayment_id = $Advpayment->id;
												$Advpaymentapproval->approver_id = $Approverbufc->id;
												$Advpaymentapproval->save();
												$logger = new Datalogger("Advpaymentapproval","add","Add initial BU FC Approval ",json_encode($Advpaymentapproval->to_array()));
												$logger->SaveData();
											}

										} else if($Advpayment->opscategory == 4) {

											$financecomit = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=41",$id)));	
											foreach ($financecomit as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval Finance Commite");
												$logger->SaveData();
											}

											$bufc = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=37",$id)));	
											foreach ($bufc as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval BUFC");
												$logger->SaveData();
											}

											$buhead = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=38",$id)));	
											foreach ($buhead as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval BU Head");
												$logger->SaveData();
											}

											$kfssl = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=45",$id)));	
											foreach ($kfssl as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval KF SSL");
												$logger->SaveData();
											}


											$ApproverBU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and FIND_IN_SET(?, CompanyList) > 0 ",$Advpayment->companycode)));
											if(count($ApproverBU)>0){
												$Advpaymentapproval = new Advpaymentapproval();
												$Advpaymentapproval->advpayment_id = $Advpayment->id;
												$Advpaymentapproval->approver_id = $ApproverBU->id;
												$Advpaymentapproval->save();
												$logger = new Datalogger("Advpaymentapproval","add","Add initial BU Head Approval ",json_encode($Advpaymentapproval->to_array()));
												$logger->SaveData();
											}

											$Approverkfssl = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='45' ")));
											if(count($Approverkfssl)>0){
												$Advpaymentapproval = new Advpaymentapproval();
												$Advpaymentapproval->advpayment_id = $Advpayment->id;
												$Advpaymentapproval->approver_id = $Approverkfssl->id;
												$Advpaymentapproval->save();
												$logger = new Datalogger("Advpaymentapproval","add","Add initial KF SSL Approval ",json_encode($Advpaymentapproval->to_array()));
												$logger->SaveData();
											}

											$Approverbufc = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37' and FIND_IN_SET(?, CompanyList) > 0 ",$Advpayment->companycode)));
											if(count($Approverbufc)>0){
												$Advpaymentapproval = new Advpaymentapproval();
												$Advpaymentapproval->advpayment_id = $Advpayment->id;
												$Advpaymentapproval->approver_id = $Approverbufc->id;
												$Advpaymentapproval->save();
												$logger = new Datalogger("Advpaymentapproval","add","Add initial BU FC Approval ",json_encode($Advpaymentapproval->to_array()));
												$logger->SaveData();
											}

										}


									break;
									case 'companycode':
										$categorytype = $query['company'];
										$employee_id = $query['employee_id'];
										$id= $query['advpayment_id'];

										$Advpayment = Advpayment::find($id);
										
										$Advpayment->companycode = $categorytype;
										$codenew = Advpayment::find('first',array('select' => "CONCAT('Payment/','".$categorytype."','/',YEAR(CURDATE()),'/',LPAD(MONTH(CURDATE()), 2, '0'),'/',LPAD(CASE when max(substring(paymentno,-4,4)) is null then 1 else max(substring(paymentno,-4,4))+1 end,4,'0')) as paymentno","conditions"=>array("substring(paymentno,9,".strlen($categorytype).")=? and not(id = ?) and substring(paymentno,".(strlen($categorytype)+10).",4)=YEAR(CURDATE()) ",$categorytype,$query['advpayment_id'])));
										
										$Advpayment->paymentno =$codenew->paymentno;
										$Advpayment->save();
										

										$Employee = Employee::find('first', array('conditions' => array("id=?",$employee_id),"include"=>array("location","department","company")));

										// $data['companycode']=$Employee->companycode;

										$joins   = "LEFT JOIN tbl_approver ON (tbl_advpaymentapproval.approver_id = tbl_approver.id) LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id)";
										$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";	
										

										$Advpaymentapprovalhrd = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id='36' ",$id)));	
										foreach ($Advpaymentapprovalhrd as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}
										$Advpaymentapprovalproc = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id='42' ",$id)));	
										foreach ($Advpaymentapprovalproc as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}
										if($Advpayment->paymentform == 1) {
											$kfssl = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=45",$id)));	
											foreach ($kfssl as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval KF SSL");
												$logger->SaveData();
											}

											$financecomit = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=41",$id)));	
											foreach ($financecomit as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval Finance Commite");
												$logger->SaveData();
											}

											$bufc = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=37",$id)));	
											foreach ($bufc as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval BUFC");
												$logger->SaveData();
											}

											$hrverifikator = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=44",$id)));	
											foreach ($hrverifikator as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval HR Verifikator");
												$logger->SaveData();
											}
											
											$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and FIND_IN_SET(?, CompanyList) > 0 ",$Advpayment->companycode)));
											if(count($ApproverHRV)>0){
												$Advpaymentapproval = new Advpaymentapproval();
												$Advpaymentapproval->advpayment_id = $Advpayment->id;
												$Advpaymentapproval->approver_id = $ApproverHRV->id;
												$Advpaymentapproval->save();
												$logger = new Datalogger("Advpaymentapproval","add","Add initial HR Verifikator Approval ",json_encode($Advpaymentapproval->to_array()));
												$logger->SaveData();
											}

											$buhead = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=38",$id)));	
											foreach ($buhead as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval BU HEAD");
												$logger->SaveData();
											}

											$hrd = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=36",$id)));	
											foreach ($hrd as $result) {
												$result->delete();
												$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval HRD");
												$logger->SaveData();
											}

											$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='36' and FIND_IN_SET(?, CompanyList) > 0 ",$Advpayment->companycode)));
											if(count($Approver2)>0){
												$Advpaymentapproval = new Advpaymentapproval();
												$Advpaymentapproval->advpayment_id = $Advpayment->id;
												$Advpaymentapproval->approver_id = $Approver2->id;
												$Advpaymentapproval->save();
												$logger = new Datalogger("Advpaymentapproval","add","Add initial HRD Approval ",json_encode($Advpaymentapproval->to_array()));
												$logger->SaveData();
											}

											$Approverbufc = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37' and FIND_IN_SET(?, CompanyList) > 0 ",$Advpayment->companycode)));
											if(count($Approverbufc)>0){
												$Advpaymentapproval = new Advpaymentapproval();
												$Advpaymentapproval->advpayment_id = $Advpayment->id;
												$Advpaymentapproval->approver_id = $Approverbufc->id;
												$Advpaymentapproval->save();
												$logger = new Datalogger("Advpaymentapproval","add","Add initial BU FC Approval ",json_encode($Advpaymentapproval->to_array()));
												$logger->SaveData();
											}

											$ApproverBU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and FIND_IN_SET(?, CompanyList) > 0 ",$Advpayment->companycode)));
											if(count($ApproverBU)>0){
												$Advpaymentapproval = new Advpaymentapproval();
												$Advpaymentapproval->advpayment_id = $Advpayment->id;
												$Advpaymentapproval->approver_id = $ApproverBU->id;
												$Advpaymentapproval->save();
												$logger = new Datalogger("Advpaymentapproval","add","Add initial BU Head Approval ",json_encode($Advpaymentapproval->to_array()));
												$logger->SaveData();
											}
											
										} else if($Advpayment->paymentform == 2) {
											if($Advpayment->opscategory == 1) {

												$kfssl = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=45",$id)));	
												foreach ($kfssl as $result) {
													$result->delete();
													$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval KF SSL");
													$logger->SaveData();
												}

												$financecomit = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=41",$id)));	
												foreach ($financecomit as $result) {
													$result->delete();
													$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval Finance Commite");
													$logger->SaveData();
												}

												$bufc = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=37",$id)));	
												foreach ($bufc as $result) {
													$result->delete();
													$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval BUFC");
													$logger->SaveData();
												}

												$buhead = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=38",$id)));	
												foreach ($buhead as $result) {
													$result->delete();
													$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval BU Head");
													$logger->SaveData();
												}


												$ApproverBU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and FIND_IN_SET(?, CompanyList) > 0 ",$Advpayment->companycode)));
												if(count($ApproverBU)>0){
													$Advpaymentapproval = new Advpaymentapproval();
													$Advpaymentapproval->advpayment_id = $Advpayment->id;
													$Advpaymentapproval->approver_id = $ApproverBU->id;
													$Advpaymentapproval->save();
													$logger = new Datalogger("Advpaymentapproval","add","Add initial BU Head Approval ",json_encode($Advpaymentapproval->to_array()));
													$logger->SaveData();
												}

												$Approverbufc = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37' and FIND_IN_SET(?, CompanyList) > 0 ",$Advpayment->companycode)));
												if(count($Approverbufc)>0){
													$Advpaymentapproval = new Advpaymentapproval();
													$Advpaymentapproval->advpayment_id = $Advpayment->id;
													$Advpaymentapproval->approver_id = $Approverbufc->id;
													$Advpaymentapproval->save();
													$logger = new Datalogger("Advpaymentapproval","add","Add initial BU FC Approval ",json_encode($Advpaymentapproval->to_array()));
													$logger->SaveData();
												}

											} else if($Advpayment->opscategory == 2) {

												$kfssl = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=45",$id)));	
												foreach ($kfssl as $result) {
													$result->delete();
													$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval KF SSL");
													$logger->SaveData();
												}

												$bufc = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=37",$id)));	
												foreach ($bufc as $result) {
													$result->delete();
													$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval BUFC");
													$logger->SaveData();
												}

												$financecomit = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=41",$id)));	
												foreach ($financecomit as $result) {
													$result->delete();
													$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval Finance Commite");
													$logger->SaveData();
												}
												$buhead = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=38",$id)));	
												foreach ($buhead as $result) {
													$result->delete();
													$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval BU Head");
													$logger->SaveData();
												}

												$Approverbufc = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37' and FIND_IN_SET(?, CompanyList) > 0 ",$Advpayment->companycode)));
												if(count($Approverbufc)>0){
													$Advpaymentapproval = new Advpaymentapproval();
													$Advpaymentapproval->advpayment_id = $Advpayment->id;
													$Advpaymentapproval->approver_id = $Approverbufc->id;
													$Advpaymentapproval->save();
													$logger = new Datalogger("Advpaymentapproval","add","Add initial BU FC Approval ",json_encode($Advpaymentapproval->to_array()));
													$logger->SaveData();
												}

												$ApproverFC = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=41")));
												if(count($ApproverFC)>0){
													$Advpaymentapproval = new Advpaymentapproval();
													$Advpaymentapproval->advpayment_id = $Advpayment->id;
													$Advpaymentapproval->approver_id = $ApproverFC->id;
													$Advpaymentapproval->save();
													$logger = new Datalogger("Advpaymentapproval","add","Add initial Finance Commite Approval ",json_encode($Advpaymentapproval->to_array()));
													$logger->SaveData();
												}

											} else if($Advpayment->opscategory == 3) {

												$kfssl = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=45",$id)));	
												foreach ($kfssl as $result) {
													$result->delete();
													$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval KF SSL");
													$logger->SaveData();
												}

												$bufc = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=37",$id)));	
												foreach ($bufc as $result) {
													$result->delete();
													$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval BUFC");
													$logger->SaveData();
												}

												$financecomit = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=41",$id)));	
												foreach ($financecomit as $result) {
													$result->delete();
													$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval Finance Commite");
													$logger->SaveData();
												}
												$buhead = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=38",$id)));	
												foreach ($buhead as $result) {
													$result->delete();
													$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval BU Head");
													$logger->SaveData();
												}

												$Approverbufc = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37' and FIND_IN_SET(?, CompanyList) > 0 ",$Advpayment->companycode)));
												if(count($Approverbufc)>0){
													$Advpaymentapproval = new Advpaymentapproval();
													$Advpaymentapproval->advpayment_id = $Advpayment->id;
													$Advpaymentapproval->approver_id = $Approverbufc->id;
													$Advpaymentapproval->save();
													$logger = new Datalogger("Advpaymentapproval","add","Add initial BU FC Approval ",json_encode($Advpaymentapproval->to_array()));
													$logger->SaveData();
												}

											} else if($Advpayment->opscategory == 4) {

												$financecomit = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=41",$id)));	
												foreach ($financecomit as $result) {
													$result->delete();
													$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval Finance Commite");
													$logger->SaveData();
												}

												$bufc = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=37",$id)));	
												foreach ($bufc as $result) {
													$result->delete();
													$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval BUFC");
													$logger->SaveData();
												}

												$buhead = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=38",$id)));	
												foreach ($buhead as $result) {
													$result->delete();
													$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval BU Head");
													$logger->SaveData();
												}

												$kfssl = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=45",$id)));	
												foreach ($kfssl as $result) {
													$result->delete();
													$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval KF SSL");
													$logger->SaveData();
												}


												$ApproverBU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and FIND_IN_SET(?, CompanyList) > 0 ",$Advpayment->companycode)));
												if(count($ApproverBU)>0){
													$Advpaymentapproval = new Advpaymentapproval();
													$Advpaymentapproval->advpayment_id = $Advpayment->id;
													$Advpaymentapproval->approver_id = $ApproverBU->id;
													$Advpaymentapproval->save();
													$logger = new Datalogger("Advpaymentapproval","add","Add initial BU Head Approval ",json_encode($Advpaymentapproval->to_array()));
													$logger->SaveData();
												}

												$Approverkfssl = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='45' ")));
												if(count($Approverkfssl)>0){
													$Advpaymentapproval = new Advpaymentapproval();
													$Advpaymentapproval->advpayment_id = $Advpayment->id;
													$Advpaymentapproval->approver_id = $Approverkfssl->id;
													$Advpaymentapproval->save();
													$logger = new Datalogger("Advpaymentapproval","add","Add initial KF SSL Approval ",json_encode($Advpaymentapproval->to_array()));
													$logger->SaveData();
												}

												$Approverbufc = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37' and FIND_IN_SET(?, CompanyList) > 0 ",$Advpayment->companycode)));
												if(count($Approverbufc)>0){
													$Advpaymentapproval = new Advpaymentapproval();
													$Advpaymentapproval->advpayment_id = $Advpayment->id;
													$Advpaymentapproval->approver_id = $Approverbufc->id;
													$Advpaymentapproval->save();
													$logger = new Datalogger("Advpaymentapproval","add","Add initial BU FC Approval ",json_encode($Advpaymentapproval->to_array()));
													$logger->SaveData();
												}

											}
										}

										$data=array("paymentno"=>$codenew->paymentno);
										echo json_encode($data, JSON_NUMERIC_CHECK);


									break;
								default:
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$query['username'])));
									$Advpayment = Advpayment::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Advpayment as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Advpayment));
									break;
							}
						} else{
							$data=array();
						}
						// echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':		
						$data = $this->post['data'];
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$data['username']),"include"=>array("location","company","department")));
							unset($data['__KEY__']);
							unset($data['username']);
							$data['employee_id']=$Employee->id;
							$data['RequestStatus']=0;
							try{
								$code = Advpayment::find('first',array('select' => "CONCAT('Payment/','".$Employee->companycode."','/',YEAR(CURDATE()),'/',LPAD(MONTH(CURDATE()), 2, '0'),'/',LPAD(CASE when max(substring(paymentno,-4,4)) is null then 1 else max(substring(paymentno,-4,4))+1 end,4,'0')) as paymentno","conditions"=>array("substring(paymentno,9,".strlen($Employee->companycode).")=? and substring(paymentno,".(strlen($Employee->companycode)+10).",4)=YEAR(CURDATE())",$Employee->companycode)));

								$data['paymentno']=$code->paymentno;

								$data['companycode']=$Employee->companycode;
								$Advpayment = Advpayment::create($data);
								$data=$Advpayment->to_array();
								
								$joins   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";

								// $companyFC=(($data['companycode']=='BCL') || ($data['companycode']=='KPA'))?"KPSI":((($data['companycode']=='KPSI'))?"LDU":$Employee->companycode);
								$ApproverBUFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37' and FIND_IN_SET(?, CompanyList) > 0 ",$Employee->companycode)));
								if(count($ApproverBUFC)>0){
									$Advpaymentapproval = new Advpaymentapproval();
									$Advpaymentapproval->advpayment_id = $Advpayment->id;
									$Advpaymentapproval->approver_id = $ApproverBUFC->id;
									$Advpaymentapproval->save();
									$logger = new Datalogger("Advpaymentapproval","add","Add initial BU FC Approval",json_encode($Advpaymentapproval->to_array()));
									$logger->SaveData();
								}

								$Advpaymenthistory = new Advpaymenthistory();
								$Advpaymenthistory->date = date("Y-m-d h:i:s");
								$Advpaymenthistory->fullname = $Employee->fullname;
								$Advpaymenthistory->approvaltype = "Originator";
								$Advpaymenthistory->advpayment_id = $Advpayment->id;
								$Advpaymenthistory->actiontype = 0;
								$Advpaymenthistory->save();
								
							}catch (Exception $e){
								$err = new Errorlog();
								$err->errortype = "CreateAdvpayment";
								$err->errordate = date("Y-m-d h:i:s");
								$err->errormessage = $e->getMessage();
								$err->user = $this->currentUser->username;
								$err->ip = $this->ip;
								$err->save();
								$data = array("status"=>"error","message"=>$e->getMessage());
							}
							$logger = new Datalogger("Advpayment","create",null,json_encode($data));
							$logger->SaveData();

						echo json_encode($data);									
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Advpayment = Advpayment::find($id);
						if ($Advpayment->requeststatus==0){
							try {
								$approval = Advpaymentapproval::find("all",array('conditions' => array("advpayment_id=?",$id)));
								foreach ($approval as $delr){
									$delr->delete();
								}
								$approval = Advpaymentattachment::find("all",array('conditions' => array("advpayment_id=?",$id)));
								foreach ($approval as $delr){
									$delr->delete();
								}
								$detail = Advpaymentdetail::find("all",array('conditions' => array("advpayment_id=?",$id)));
								foreach ($detail as $delr){
									$delr->delete();
								}
								$hist = Advpaymenthistory::find("all",array('conditions' => array("advpayment_id=?",$id)));
								foreach ($hist as $delr){
									$delr->delete();
								}
								$data = $Advpayment->to_array();
								$Advpayment->delete();
								$logger = new Datalogger("Advpayment","delete",json_encode($data),null);
								$logger->SaveData();
								$data = array("status"=>"success","message"=>"Data has been deleted");
							}catch (Exception $e){
								$err = new Errorlog();
								$err->errortype = "DeleteAdvpayment";
								$err->errordate = date("Y-m-d h:i:s");
								$err->errormessage = $e->getMessage();
								$err->user = $this->currentUser->username;
								$err->ip = $this->ip;
								$err->save();
								$data = array("status"=>"error","message"=>$e->getMessage());
							}
							echo json_encode($data);
							
						} else {
							$data = array("status"=>"error","message"=>"You can't delete submitted request");
							echo json_encode($data);
						}
						
						break;
					case 'update':
						$id = $this->post['id'];
						$data = $this->post['data'];
						$Advpayment = Advpayment::find($id,array('include'=>array('employee'=>array('company','department','designation','grade'))));
						$olddata = $Advpayment->to_array();
						$depthead = $data['depthead'];
						unset($data['approvalstatus']);
						unset($data['fullname']);
						unset($data['department']);
						unset($data['apprstatuscode']);

						//unset($data['employee']);
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
						foreach($data as $key=>$val){
							$value=(($val===0) || ($val==='0') || ($val==='false'))?false:((($val===1) || ($val==='1') || ($val==='true'))?true:$val);
							$Advpayment->$key=$value;
						}
						$Advpayment->save();
						
						if (isset($data['depthead'])){
							$joins   = "LEFT JOIN tbl_approver ON (tbl_advpaymentapproval.approver_id = tbl_approver.id) ";		
							// if($depthead == 789) {
							// 	$dx = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=49 and not(tbl_approver.employee_id=?)",$id,$depthead)));	
							// } else {
								$dx = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and (tbl_approver.approvaltype_id=35 or tbl_approver.approvaltype_id=49) and not(tbl_approver.employee_id=?)",$id,$depthead)));	
							// }			
							foreach ($dx as $result) {
								//delete same type approver
								$result->delete();
								$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
							}				
							$Advpaymentapproval = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.employee_id=?",$id,$depthead)));	
							foreach ($Advpaymentapproval as &$result) {
								$result		= $result->to_array();
								$result['no']=1;
							}			
							if(count($Advpaymentapproval)==0){ 
								// if($Advpayment->paymentform == 1) {
									if($depthead == 789) {
										$Approver = Approver::find('first',array('conditions'=>array("module='Advance' and employee_id=? and approvaltype_id=49",$depthead)));
									} else {
										$Approver = Approver::find('first',array('conditions'=>array("module='Advance' and employee_id=? and approvaltype_id=35",$depthead)));
									}
								// }
								if(count($Approver)>0){
									$Advpaymentapproval = new Advpaymentapproval();
									$Advpaymentapproval->advpayment_id = $Advpayment->id;
									$Advpaymentapproval->approver_id = $Approver->id;
									$Advpaymentapproval->save();
								}else{
									$approver = new Approver();
									$approver->module = "Advance";
									$approver->employee_id=$depthead;
									$approver->sequence=0;
									$approver->approvaltype_id = 35;
									$approver->isfinal = false;
									$approver->save();
									$Advpaymentapproval = new Advpaymentapproval();
									$Advpaymentapproval->advpayment_id = $Advpayment->id;
									$Advpaymentapproval->approver_id = $approver->id;
									$Advpaymentapproval->save();
									$logger = new Datalogger("Advpaymentapproval","add","Add Approval Dept Head",json_encode($Advpaymentapproval->to_array()));
									$logger->SaveData();
								}
							}
						}
						if($data['requeststatus']==1){
							$Advpaymentapproval = Advpaymentapproval::find('all', array('conditions' => array("advpayment_id=?",$id)));					
							foreach($Advpaymentapproval as $data){
								$data->approvalstatus=0;
								$data->save();
							}
							$joinx   = "LEFT JOIN tbl_approver ON (tbl_advpaymentapproval.approver_id = tbl_approver.id) ";					
							$Advpaymentapproval = Advpaymentapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and advpayment_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
							$username = $Advpaymentapproval->approver->employee->loginname;
							$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
							$email = $adb->email;
							$title = 'Payment';
							// $Advpaymentdetail=Advpaymentdetail::find('all',array('conditions'=>array("advpayment_id=?",$id),'include'=>array('advpayment','employee'=>array('company','department','designation','grade'))));
							$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$adb->fullname.',</span></p>
										<p class=MsoNormal><span style="color:#1F497D">new '.$title.' Request is awaiting for your approval:</span></p>
										<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advpayment->employee->fullname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advpayment->employee->sapid.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advpayment->employee->designation->designationname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advpayment->employee->company->companyname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advpayment->employee->location->location.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
										</table>
										<br>

										';
							if($Advpayment->paymentform == 1) {
								$form = "Payment Req HR";
							} else if($Advpayment->paymentform == 2){
								$form = "Payment Req OPR";
							}

							if($Advpayment->paymenttype == 0) {
								$less = 0;
							} else {
								$less = $Advpayment->lessadvance;
							}

							if($Advpayment->payment == 1) {
								$paymentM = "Cash";
							} else if($Advpayment->payment == 2) {
								$paymentM = "Bank";
							}

							$Advpaymentdetail = Advpaymentdetail::find('all',array('conditions'=>array("advpayment_id=?",$id),'include'=>array('advpayment'=>array('employee'=>array('company','department','designation','grade','location')))));	

							$this->mailbody .='
								<table border=1 cellspacing=0 cellpadding=3 width=683>
								<tr>
									<th><p class=MsoNormal>Payment Form</p></th>
									<th><p class=MsoNormal>Payment Method</p></th>
									<th><p class=MsoNormal>Beneficiary</p></th>
									<th><p class=MsoNormal>Account Name</p></th>
									<th><p class=MsoNormal>Bank</p></th>
									<th><p class=MsoNormal>Account Number</p></th>
									<th><p class=MsoNormal>Payment Date</p></th>
									<th><p class=MsoNormal>Due Date</p></th>
									<th><p class=MsoNormal>Remarks</p></th>
								</tr>
								<tr style="height:22.5pt">
									<td><p class=MsoNormal> '.$form.'</p></td>
									<td><p class=MsoNormal> '.$paymentM.'</p></td>
									<td><p class=MsoNormal> '.$Advpayment->beneficiary.'</p></td>
									<td><p class=MsoNormal> '.$Advpayment->accountname.'</p></td>
									<td><p class=MsoNormal> '.$Advpayment->bank.'</p></td>
									<td><p class=MsoNormal> '.$Advpayment->accountnumber.'</p></td>
									<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Advpayment->paymentdate)).'</p></td>
									<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Advpayment->duedate)).'</p></td>
									<td><p class=MsoNormal> '.$Advpayment->remarks.'</p></td>
								</tr>
								</table>
								<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><th><p class=MsoNormal>No</p></th>
											<th><p class=MsoNormal>Description</p></th>
											<th><p class=MsoNormal>Account Code</p></th>
											<th><p class=MsoNormal>Amount</p></th>
										</tr>
							';
							$no=1;
							foreach ($Advpaymentdetail as $data){
								$val_tamount += $data->amount;
								$this->mailbody .='<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.$no.'</p></td>
											<td><p class=MsoNormal> '.$data->description.'</p></td>
											<td><p class=MsoNormal> '.$data->accountcode.'</p></td>
											<td><p class=MsoNormal> '.number_format($data->amount).'</p></td>
								</tr>';
								$no++;
							}
							$this->mailbody .='</table>
							<ul>
								<li><b><span>Total Amount : '.number_format($val_tamount).'</span></b></li>
								<li><b><span>Less Advance : '.number_format($less).'</span></b></li>
								<li><b><span>Balance To Be Paid : '.number_format($val_tamount-$less).'</span></b></li>
							</ul>
							<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.83.35/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.83.35/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
							$this->mail->addAddress($adb->email, $adb->fullname);
							$this->mail->Subject = "Online Approval System -> Advpayment";
							$this->mail->msgHTML($this->mailbody);
							if (!$this->mail->send()) {
								$err = new Errorlog();
								$err->errortype = "Advpayment Mail";
								$err->errordate = date("Y-m-d h:i:s");
								$err->errormessage = $this->mail->ErrorInfo;
								$err->user = $this->currentUser->username;
								$err->ip = $this->ip;
								$err->save();
								echo "Mailer Error: " . $this->mail->ErrorInfo;
							} else {
								echo "Message sent!";
							}

							// if($Advpayment->paymentform == 2) {

							// 	$dx = Advpaymentapproval::find('all',array('joins'=>$joins,'conditions' => array("advpayment_id=? and tbl_approver.approvaltype_id=36",$id)));	
							// 	foreach ($dx as $result) {
							// 		$result->delete();
							// 		$logger = new Datalogger("Advpaymentapproval","delete",json_encode($result->to_array()),"delete Approval Advpayment");
							// 		$logger->SaveData();
							// 	}

							// 	if($val_tamount >= 5000000) {

							// 		$ApproverProc = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='42' and tbl_employee.location_id='1'")));
							// 		if(count($ApproverProc)>0){
							// 			$Advpaymentapproval = new Advpaymentapproval();
							// 			$Advpaymentapproval->advpayment_id =$Advpayment->id;
							// 			$Advpaymentapproval->approver_id = $ApproverProc->id;
							// 			$Advpaymentapproval->save();
							// 			$logger = new Datalogger("Advpaymentapproval","add","Add initial Proc Approval",json_encode($Advpaymentapproval->to_array()));
							// 			$logger->SaveData();
							// 		}
									
							// 	}


								
							// }

							$Advpaymenthistory = new Advpaymenthistory();
							$Advpaymenthistory->date = date("Y-m-d h:i:s");
							$Advpaymenthistory->fullname = $Employee->fullname;
							$Advpaymenthistory->advpayment_id = $id;
							$Advpaymenthistory->approvaltype = "Originator";
							$Advpaymenthistory->actiontype = 2;
							$Advpaymenthistory->save();
						}else{
							$Advpaymenthistory = new Advpaymenthistory();
							$Advpaymenthistory->date = date("Y-m-d h:i:s");
							$Advpaymenthistory->fullname = $Employee->fullname;
							$Advpaymenthistory->advpayment_id = $id;
							$Advpaymenthistory->approvaltype = "Originator";
							$Advpaymenthistory->actiontype = 1;
							$Advpaymenthistory->save();
						}
						$logger = new Datalogger("Advpayment","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						//echo json_encode($Advpayment);
						
						break;
					default:
						$Advpayment = Advpayment::all();
						foreach ($Advpayment as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Advpayment, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}

	function advpaymentApproval(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'byid':
						$id = $this->post['id'];
						if ($id!=""){
							$join   = "LEFT JOIN tbl_approver ON (tbl_advpaymentapproval.approver_id = tbl_approver.id) ";
							$Advpaymentapproval = Advpaymentapproval::find('all', array('joins'=>$join,'conditions' => array("advpayment_id=?",$id),'include' => array('approver'=>array('approvaltype')),"order"=>"tbl_approver.sequence"));
							foreach ($Advpaymentapproval as &$result) {
								$approvaltype = $result->approver->approvaltype_id;
								$result		= $result->to_array();
								$result['approvaltype']=$approvaltype;
							}
							echo json_encode($Advpaymentapproval, JSON_NUMERIC_CHECK);
						}else{
							$Advpaymentapproval = new Advpaymentapproval();
							echo json_encode($Advpaymentapproval);
						}
						break;
					case 'find':
						$query=$this->post['query'];		
						if(isset($query['status'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join   = "LEFT JOIN tbl_approver ON (tbl_advpaymentapproval.approver_id = tbl_approver.id) ";
							$dx = Advpaymentapproval::find('first', array('joins'=>$join,'conditions' => array("advpayment_id=? and tbl_approver.employee_id = ?",$query['advpayment_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
							// print_r($dx);
							$Advpayment = Advpayment::find($query['advpayment_id']);
							// if($dx->approver->isfinal==1){
							if (($Advpayment->paymentform == 1) && $dx->approver->approvaltype_id == 38){
								$data=array("jml"=>1);
							} else if (($Advpayment->paymentform == 2 && $Advpayment->opscategory == 1) && $dx->approver->approvaltype_id == 38){
								$data=array("jml"=>1);
							}else if (($Advpayment->paymentform == 2 && $Advpayment->opscategory == 2) && $dx->approver->approvaltype_id == 41){
								$data=array("jml"=>1);
							}else if (($Advpayment->paymentform == 2 && $Advpayment->opscategory == 3) && $dx->approver->approvaltype_id == 37){
								$data=array("jml"=>1);
							}else if (($Advpayment->paymentform == 2 && $Advpayment->opscategory == 4) && $dx->approver->approvaltype_id == 38){
								$data=array("jml"=>1);
							}else{
								$join   = "LEFT JOIN tbl_approver ON (tbl_advpaymentapproval.approver_id = tbl_approver.id) ";
								$Advpaymentapproval = Advpaymentapproval::find('all', array('joins'=>$join,'conditions' => array("advpayment_id=? and ApprovalStatus<=1 and not tbl_approver.employee_id=?",$query['advpayment_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
								foreach ($Advpaymentapproval as &$result) {
									$fullname	= $result->approver->employee->fullname;	
									$result		= $result->to_array();
									$result['fullname']=$fullname;
								}
								$data=array("jml"=>count($Advpaymentapproval));
							}						
						} else if(isset($query['pending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;

							// $Advpayment = Advpayment::find('all', array('conditions' => array("RequestStatus=1"),'include' => array('employee')));
							/*$Advpayment = Advpayment::find('all', array('conditions' => array("RequestStatus >0"),'include' => array('employee')));

							foreach ($Advpayment as $result) {
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_advpaymentapproval.approver_id = tbl_approver.id) ";					
								$Advpaymentapproval = Advpaymentapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and advpayment_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								if($Advpaymentapproval->approver->employee_id==$emp_id){
									$request[]=$result->id;
								}
								$Advpaymentapproval = Advpaymentapproval::find('first',array('joins'=>$joinx,'conditions' => array("advpayment_id=? and tbl_approver.employee_id = ? and approvalstatus!=0",$result->id,$emp_id),'include' => array('approver'=>array('employee'))));							
								if(count($Advpaymentapproval)>0 && ($result->requeststatus==3 || $result->requeststatus==4)){
									$request[]=$result->id;
								}
							}*/
							$joinx = "LEFT JOIN tbl_advpaymentapproval ON (tbl_advpayment.id = tbl_advpaymentapproval.advpayment_id) 
								LEFT JOIN tbl_approver ON (tbl_advpaymentapproval.approver_id = tbl_approver.id)";

							$Rfc = Rfc::find('all', array(
								'select'=>"tbl_advpayment.*,tbl_approver.employee_id as empappr,tbl_advpaymentapproval.approvalstatus as apprstatus, (select case when appr.employee_id='".$emp_id."' then 1 else 0 end as pending from  tbl_advpaymentapproval a left join tbl_approver appr ON (a.approver_id = appr.id) where a.approvalstatus=0 and a.advpayment_id=tbl_advpayment.id order by appr.sequence limit 0,1 ) as pendingonme",
								'conditions' => array("tbl_advpayment.RequestStatus > 0"),
								'joins' => $joinx,
								'order' => "tbl_approver.sequence"
							));

							foreach ($Rfc as $result) {
								if ($result->pendingonme == 1) {
									if (!in_array($result->id, $request))
									{
										$request[] = $result->id; 
									}
								}

								if (($result->requeststatus == 3 || $result->requeststatus == 4) && ($result->apprstatus!=0 && $result->apprstatus!="" ) && $result->empappr == $emp_id) {
									if (!in_array($result->id, $request))
									{
										$request[] = $result->id; 
									}
								}
							}
							$Advpayment = Advpayment::find('all', array('conditions' => array("id in (?)",$request),'order'=>"tbl_advpayment.requeststatus",'include' => array('employee')));
							foreach ($Advpayment as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=$Advpayment;

						} else if(isset($query['mypending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
							$Advpayment = Advpayment::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
							$jml=0;
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
							$data=array("jml"=>count($Advpayment));
						} else if(isset($query['filter'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join = "LEFT JOIN vwadvpaymentreport v on tbl_advpayment.id=v.id LEFT JOIN tbl_employee ON (tbl_advpayment.employee_id = tbl_employee.id) ";
							$sel = 'tbl_advpayment.*,v.personholding ';
							$Advpayment = Advpayment::find('all',array('joins'=>$join,'select'=>$sel,'include' => array('employee')));
							
							// if($Employee->location->sapcode=='0200' || $this->currentUser->isadmin){
								$Advpayment = Advpayment::find('all',array('joins'=>$join,'select'=>$sel,'conditions' => array('tbl_advpayment.CreatedDate between ? and ?',$query['startDate'],$query['endDate'] ),'include' => array('employee'=>array('company','department'))));
							// }else{
							// 	$Advpayment = Advpayment::find('all',array('joins'=>$join,'select'=>$sel,'conditions' => array('tbl_advpayment.RequestStatus=3 or tbl_advpayment.RequestStatus=5 and tbl_employee.company_id=?',$Employee->company_id ),'include' => array('employee'=>array('company','department'))));
							// }
							
							foreach ($Advpayment as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=$Advpayment;
						} else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Advpaymentapproval = Advpaymentapproval::create($data);
						$logger = new Datalogger("Advpaymentapproval","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':
						$id = $this->post['id'];
						$Advpaymentapproval = Advpaymentapproval::find($id);
						$data=$Advpaymentapproval->to_array();
						$Advpaymentapproval->delete();
						$logger = new Datalogger("Advpaymentapproval","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Advpaymentapproval);
						break;
					case 'update':
							$doid = $this->post['id'];
							$data = $this->post['data'];
							$mode= $data['mode'];
							$appstatus = $data['approvalstatus'];
							unset($data['id']);
							unset($data['depthead']);
							unset($data['fullname']);
							unset($data['department']);
							unset($data['approveddoc']);
							// if(isset($data['approvalstatus']) == 4) {
							// }
							// if ($appstatus=='4' || $appstatus==4 ){
							// 	$data['approvalstatus'] == 0;
							// }
							// print_r($data);

							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join   = "LEFT JOIN tbl_approver ON (tbl_advpaymentapproval.approver_id = tbl_approver.id) ";
							if (isset($data['mode'])){
								$Advpaymentapproval = Advpaymentapproval::find('first', array('joins'=>$join,'conditions' => array("advpayment_id=? and tbl_approver.employee_id=? and ApprovalStatus = 0",$doid,$Employee->id),'order' => 'tbl_approver.sequence','include' => array('approver'=>array('employee','approvaltype'))));
								//start for update all duplicate approver
								// $Advpaymentapproval = Advpaymentapproval::find('all', array('joins'=>$join,'conditions' => array("advpayment_id=? and tbl_approver.employee_id=?",$doid,$Employee->id),'include' => array('approver'=>array('employee','approvaltype'))));
								//end for update all duplicate approver
								unset($data['mode']);
							}else{
								$Advpaymentapproval = Advpaymentapproval::find($this->post['id'],array('include' => array('approver'=>array('employee','approvaltype'))));
							}

							// $Advpayment = Advpayment::find($doid);
							// foreach($data as $key=>$val) {
							// 	if(($key !== 'approvalstatus') && ($key !== 'approvaldate') && ($key !== 'remarks') ) {
							// 		// if(($key == 'isrepair') || ($key == 'isscrap')) {
							// 			$value=(($val===0) || ($val==='0') || ($val==='false'))?false:((($val===1) || ($val==='1') || ($val==='true'))?true:$val);
							// 		// }
							// 		$Advpayment->$key=$value;
							// 	}
							// }
							
							// $Advpayment->save();
							
							unset($data['advanceno']);
							unset($data['paymentform']);
							unset($data['opscategory']);
							unset($data['lessadvance']);
							unset($data['beneficiary']);
							unset($data['accountname']);
							unset($data['bank']);
							unset($data['accountnumber']);
							
							unset($data['duedate']);
							unset($data['expecteddate']);

							// foreach ($Advpaymentapproval as $approval){
								$olddata = $Advpaymentapproval->to_array();
								foreach($data as $key=>$val){
									$val=($val=='false')?false:(($val=='true')?true:$val);
									$Advpaymentapproval->$key=$val;
								}
								
								$Advpaymentapproval->save();
								$logger = new Datalogger("Advpaymentapproval","update",json_encode($olddata),json_encode($data));
								$logger->SaveData();
							// }
						if (isset($mode) && ($mode=='approve')){
								$Advpayment = Advpayment::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_advpaymentapproval.approver_id = tbl_approver.id) ";					
								$nAdvpaymentapproval = Advpaymentapproval::find('first',array('joins'=>$joinx,'conditions' => array("advpayment_id=? and ApprovalStatus=0",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								$username = $nAdvpaymentapproval->approver->employee->loginname;
								$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));

								$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Advpayment->employee->loginname)));
								$email=$usr->email;
								
								$complete = false;
								$Advpaymenthistory = new Advpaymenthistory();
								$Advpaymenthistory->date = date("Y-m-d h:i:s");
								$Advpaymenthistory->fullname = $Employee->fullname;
								$Advpaymenthistory->approvaltype = $Advpaymentapproval->approver->approvaltype->approvaltype;
								$Advpaymenthistory->remarks = $data['remarks'];
								$Advpaymenthistory->advpayment_id = $doid;
								
								switch ($data['approvalstatus']){
									case '1':
										echo 1;
										$Advpayment->requeststatus = 2;
										$emto=$email;$emname=$Advpayment->employee->fullname;
										$this->mail->Subject = "Online Approval System -> Need Rework";
										$red = 'Your Payment request require some rework : <br>Remarks from Approver : <font color="red">'.$data['remarks']."</font>";
										$Advpaymenthistory->actiontype = 3;
										break;
									case '2':

										// print_r($Advpayment->paymentform);
										// print_r($Advpaymentapproval->approver->approvaltype_id);

										if (($Advpayment->paymentform == 1) && $Advpaymentapproval->approver->approvaltype_id == 38){
											$Advpayment->requeststatus = 3;
											$emto=$email;$emname=$Advpayment->employee->fullname;
											$this->mail->Subject = "Online Approval System -> Approval Completed";
											$red = 'Your Payment request has been approved';
											//delete unnecessary approver
											$Advpaymentapproval = Advpaymentapproval::find('all', array('joins'=>$join,'conditions' => array("advpayment_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
											foreach ($Advpaymentapproval as $data) {
												if($data->approvalstatus==0){
													$logger = new Datalogger("Advpaymentapproval","delete",json_encode($data->to_array()),"automatic remove unnecessary approver by system");
													$logger->SaveData();
													$data->delete();
												}
											}

											if($Advpayment->advanceno == null || $Advpayment->advanceno == '') {
											} else {
												$Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND advanceno=?",$Advpayment->employee->id,$Advpayment->advanceno)));
												$Advance->isused=1;
												$Advance->save();
											}

											$complete =true;
										} else if (($Advpayment->paymentform == 2 && $Advpayment->opscategory == 1) && $Advpaymentapproval->approver->approvaltype_id == 38){
											$Advpayment->requeststatus = 3;
											$emto=$email;$emname=$Advpayment->employee->fullname;
											$this->mail->Subject = "Online Approval System -> Approval Completed";
											$red = 'Your Payment request has been approved';
											//delete unnecessary approver
											$Advpaymentapproval = Advpaymentapproval::find('all', array('joins'=>$join,'conditions' => array("advpayment_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
											foreach ($Advpaymentapproval as $data) {
												if($data->approvalstatus==0){
													$logger = new Datalogger("Advpaymentapproval","delete",json_encode($data->to_array()),"automatic remove unnecessary approver by system");
													$logger->SaveData();
													$data->delete();
												}
											}

											if($Advpayment->advanceno == null || $Advpayment->advanceno == '') {
											} else {
												$Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND advanceno=?",$Advpayment->employee->id,$Advpayment->advanceno)));
												$Advance->isused=1;
												$Advance->save();
											}

											$complete =true;
										} else if (($Advpayment->paymentform == 2 && $Advpayment->opscategory == 2) && $Advpaymentapproval->approver->approvaltype_id == 41){
											$Advpayment->requeststatus = 3;
											$emto=$email;$emname=$Advpayment->employee->fullname;
											$this->mail->Subject = "Online Approval System -> Approval Completed";
											$red = 'Your Payment request has been approved';
											//delete unnecessary approver
											$Advpaymentapproval = Advpaymentapproval::find('all', array('joins'=>$join,'conditions' => array("advpayment_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
											foreach ($Advpaymentapproval as $data) {
												if($data->approvalstatus==0){
													$logger = new Datalogger("Advpaymentapproval","delete",json_encode($data->to_array()),"automatic remove unnecessary approver by system");
													$logger->SaveData();
													$data->delete();
												}
											}

											if($Advpayment->advanceno == null || $Advpayment->advanceno == '') {
											} else {
												$Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND advanceno=?",$Advpayment->employee->id,$Advpayment->advanceno)));
												$Advance->isused=1;
												$Advance->save();
											}

											$complete =true;
										} else if (($Advpayment->paymentform == 2 && $Advpayment->opscategory == 3) && $Advpaymentapproval->approver->approvaltype_id == 37){
											$Advpayment->requeststatus = 3;
											$emto=$email;$emname=$Advpayment->employee->fullname;
											$this->mail->Subject = "Online Approval System -> Approval Completed";
											$red = 'Your Payment request has been approved';
											//delete unnecessary approver
											$Advpaymentapproval = Advpaymentapproval::find('all', array('joins'=>$join,'conditions' => array("advpayment_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
											foreach ($Advpaymentapproval as $data) {
												if($data->approvalstatus==0){
													$logger = new Datalogger("Advpaymentapproval","delete",json_encode($data->to_array()),"automatic remove unnecessary approver by system");
													$logger->SaveData();
													$data->delete();
												}
											}

											if($Advpayment->advanceno == null || $Advpayment->advanceno == '') {
											} else {
												$Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND advanceno=?",$Advpayment->employee->id,$Advpayment->advanceno)));
												$Advance->isused=1;
												$Advance->save();
											}

											$complete =true;
										} else if (($Advpayment->paymentform == 2 && $Advpayment->opscategory == 4) && $Advpaymentapproval->approver->approvaltype_id == 38){
											$Advpayment->requeststatus = 3;
											$emto=$email;$emname=$Advpayment->employee->fullname;
											$this->mail->Subject = "Online Approval System -> Approval Completed";
											$red = 'Your Payment request has been approved';
											//delete unnecessary approver
											$Advpaymentapproval = Advpaymentapproval::find('all', array('joins'=>$join,'conditions' => array("advpayment_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
											foreach ($Advpaymentapproval as $data) {
												if($data->approvalstatus==0){
													$logger = new Datalogger("Advpaymentapproval","delete",json_encode($data->to_array()),"automatic remove unnecessary approver by system");
													$logger->SaveData();
													$data->delete();
												}
											}

											if($Advpayment->advanceno == null || $Advpayment->advanceno == '') {
											} else {
												$Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND advanceno=?",$Advpayment->employee->id,$Advpayment->advanceno)));
												$Advance->isused=1;
												$Advance->save();
											}

											$complete =true;
										}
										else{

											$Advpayment->requeststatus = 1;
											$emto=$adb->email;$emname=$adb->fullname;
											$this->mail->Subject = "Online Approval System -> New Payment Submission";
											$red = 'New Payment request awaiting for your approval:';
										}
										$Advpaymenthistory->actiontype = 4;							
										break;
									case '3':
										echo 3;

										$Advpayment->requeststatus = 4;
										$emto=$email;$emname=$Advpayment->employee->fullname;
										$Advpaymenthistory->actiontype = 5;
										$this->mail->Subject = "Online Approval System -> Request Rejected";
										$red = 'Your Payment request has been rejected <br>Remarks from Approver : <font color="red">'.$data['remarks']."</font>";
										break;
									default:
										break;
								}
								//print_r($Advpayment);
								$Advpayment->save();
								$Advpaymenthistory->save();
								echo "email to :".$emto." ->".$emname;
								$this->mail->addAddress($emto, $emname);
								
								
								$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$emname.',</span></p>
								<p class=MsoNormal><span style="color:#1F497D">'.$red.'</span></p>
								<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
								<table border=1 cellspacing=0 cellpadding=3 width=683>
								<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advpayment->employee->fullname.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advpayment->employee->sapid.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advpayment->employee->designation->designationname.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advpayment->employee->company->companyname.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advpayment->employee->location->location.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
								</table>';
						if($Advpayment->paymentform == 1) {
							$form = "Payment Req HR";
						} else if($Advpayment->paymentform == 2){
							$form = "Payment Req OPR";
						}

						if($Advpayment->paymenttype == 0) {
							$less = 0;
						} else {
							$less = $Advpayment->lessadvance;
						}

						if($Advpayment->payment == 1) {
							$paymentM = "Cash";
						} else if($Advpayment->payment == 2) {
							$paymentM = "Bank";
						}
						$Advpaymentdetail = Advpaymentdetail::find('all',array('conditions'=>array("advpayment_id=?",$doid),'include'=>array('advpayment'=>array('employee'=>array('company','department','designation','grade','location')))));	

						$this->mailbody .='
							<table border=1 cellspacing=0 cellpadding=3 width=683>
							<tr>
								<th><p class=MsoNormal>Payment Form</p></th>
								<th><p class=MsoNormal>Payment Method</p></th>
								<th><p class=MsoNormal>Beneficiary</p></th>
								<th><p class=MsoNormal>Account Name</p></th>
								<th><p class=MsoNormal>Bank</p></th>
								<th><p class=MsoNormal>Account Number</p></th>
								<th><p class=MsoNormal>Payment Date</p></th>
								<th><p class=MsoNormal>Due Date</p></th>
								<th><p class=MsoNormal>Remarks</p></th>
							</tr>
							<tr style="height:22.5pt">
								<td><p class=MsoNormal> '.$form.'</p></td>
								<td><p class=MsoNormal> '.$paymentM.'</p></td>
								<td><p class=MsoNormal> '.$Advpayment->beneficiary.'</p></td>
								<td><p class=MsoNormal> '.$Advpayment->accountname.'</p></td>
								<td><p class=MsoNormal> '.$Advpayment->bank.'</p></td>
								<td><p class=MsoNormal> '.$Advpayment->accountnumber.'</p></td>
								<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Advpayment->paymentdate)).'</p></td>
								<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Advpayment->duedate)).'</p></td>
								<td><p class=MsoNormal> '.$Advpayment->remarks.'</p></td>
							</tr>
							</table>
							<table border=1 cellspacing=0 cellpadding=3 width=683>
									<tr><th><p class=MsoNormal>No</p></th>
										<th><p class=MsoNormal>Description</p></th>
										<th><p class=MsoNormal>Account Code</p></th>
										<th><p class=MsoNormal>Amount</p></th>
									</tr>
						';
						$no=1;
						foreach ($Advpaymentdetail as $data){
							$val_tamount += $data->amount;
							$this->mailbody .='<tr style="height:22.5pt">
										<td><p class=MsoNormal> '.$no.'</p></td>
										<td><p class=MsoNormal> '.$data->description.'</p></td>
										<td><p class=MsoNormal> '.$data->accountcode.'</p></td>
										<td><p class=MsoNormal> '.number_format($data->amount).'</p></td>
							</tr>';
							$no++;
						}
						$this->mailbody .='</table>
						<ul>
								<li><b><span>Total Amount : '.number_format($val_tamount).'</span></b></li>
								<li><b><span>Less Advance : '.number_format($less).'</span></b></li>
								<li><b><span>Balance To Be Paid : '.number_format($val_tamount-$less).'</span></b></li>
						</ul>
						<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.83.35/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.83.35/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
						
								
								$this->mail->msgHTML($this->mailbody);
								if ($complete){
									$filePath= $this->generatePDFi($doid);
									$Mailrecipient = Mailrecipient::find('all',array('conditions'=>array("module='Advance' and company_list like ? and isActive='1' ","%".$Advpayment->employee->companycode."%")));
									foreach ($Mailrecipient as $data){
										$this->mail->AddCC($data->email);
									}
									$this->mail->addAttachment($filePath);
								}
								if (!$this->mail->send()) {
									$err = new Errorlog();
									$err->errortype = "Advpayment Mail";
									$err->errordate = date("Y-m-d h:i:s");
									$err->errormessage = $this->mail->ErrorInfo;
									$err->user = $this->currentUser->username;
									$err->ip = $this->ip;
									$err->save();
									echo "Mailer Error: " . $this->mail->ErrorInfo;
								} else {
									$this->processcopy($this->pathcopy);
									
									echo "Message sent!";
								}
							}
							echo json_encode($Advpaymentapproval);
						break;
					default:
						$Advpaymentapproval = Advpaymentapproval::all();
						foreach ($Advpaymentapproval as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Advpaymentapproval, JSON_NUMERIC_CHECK);
						break;
				}
			}
			// else{
				// $result= array("status"=>"error","message"=>"Authentication error or expired, please refresh and re login application");
				// echo json_encode($result);
			// }
		}
	}

	function advpaymentDetail(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'byid':
						$id = $this->post['id'];
						if ($id!=""){
							// $join = "LEFT JOIN vwadvpaymentreport ON tbl_advpaymentdetail.advpayment_id = vwadvpaymentreport.id";
							// $select = "tbl_advpaymentdetail.*,vwadvpaymentreport.apprstatuscode";
							// $Advpaymentdetail = Advpaymentdetail::find('all', array('joins'=>$join,'select'=>$select,'conditions' => array("advpayment_id=?",$id)));
							$Advpaymentdetail = Advpaymentdetail::find('all', array('conditions' => array("advpayment_id=?",$id)));
							foreach ($Advpaymentdetail as &$result) {
								$result	= $result->to_array();
							}
	
							echo json_encode($Advpaymentdetail, JSON_NUMERIC_CHECK);
						}else{
							$Advpaymentdetail = new Advpaymentdetail();
							echo json_encode($Advpaymentdetail);
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							$Advpaymentdetail = Advpaymentdetail::find('all', array('conditions' => array("advpayment_id=?",$query['advpayment_id'])));
							$data=array("jml"=>count($Advpaymentdetail));
						}else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);

						$Advpaymentdetail = Advpaymentdetail::create($data);
						$logger = new Datalogger("Advpaymentdetail","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Advpaymentdetail = Advpaymentdetail::find($id);
						$data=$Advpaymentdetail->to_array();
						$Advpaymentdetail->delete();
						$logger = new Datalogger("Advpaymentdetail","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Advpaymentdetail);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						
						$Advpaymentdetail = Advpaymentdetail::find($id);
						$olddata = $Advpaymentdetail->to_array();
						// foreach($data as $key=>$val){
						// 	$Advpaymentdetail->$key=$val;
						// }
						foreach($data as $key=>$val){
							// $val=($val=='true')?1:0;
							if($val=='true') {
								$val = 1;
							}else if($val=='false') {
								$val = 0;
							}
							$Advpaymentdetail->$key=$val;
							
						}
						// $exprice = $Advpaymentdetail->unitprice * $Advpaymentdetail->qty;
						// $Advpaymentdetail->extendedprice = $exprice;
						$Advpaymentdetail->save();
						$logger = new Datalogger("Advpaymentdetail","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						echo json_encode($Advpaymentdetail);
						
						break;
					default:
						$Advpaymentdetail = Advpaymentdetail::all();
						foreach ($Advpaymentdetail as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Advpaymentdetail, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}

	function advpaymentHistory(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'byid':
						$id = $this->post['id'];
						if ($id!=""){
							$Advpaymenthistory = Advpaymenthistory::find('all', array('conditions' => array("advpayment_id=?",$id),'include' => array('advpayment')));
							foreach ($Advpaymenthistory as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Advpaymenthistory, JSON_NUMERIC_CHECK);
						}
						break;
					default:
						break;
				}
			}
		}
	}

	function generatePDFi($id){
		$Advpayment = Advpayment::find($id);
		$Advpaymentdetail = Advpaymentdetail::find('all',array('conditions'=>array("advpayment_id=?",$id),'include'=>array('advpayment'=>array('employee'=>array('company','department','designation','grade','location')))));	
		
		$superiorId=$Advpayment->depthead;
		$Superior = Employee::find($superiorId);
		$compx = Company::find('first',array('conditions'=>array("companycode=?",$Advpayment->companycode)));
		$supAdb = Addressbook::find('first',array('conditions'=>array("username=?",$Superior->loginname)));
		$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Advpayment->employee->loginname)));
		$email=$usr->email;
		$fullname=$Advpayment->employee->fullname;
		if($Advpayment->companycode == $Advpayment->employee->companycode) {
			$department = $Advpayment->employee->department->departmentname;
		} else {
			$department = '';
		}

		// $duedate = date("d/m/Y",strtotime($Advpayment->duedate));
		$duedate = $Advpayment->duedate;
		// $paymentdate = date("d/m/Y",strtotime($Advpayment->paymentdate));
		$paymentdate = $Advpayment->paymentdate;

		$joinx   = "LEFT JOIN tbl_approver ON (tbl_advpaymentapproval.approver_id = tbl_approver.id) ";					
		$Advpaymentapproval = Advpaymentapproval::find('all',array('joins'=>$joinx,'conditions' => array("advpayment_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee','approvaltype'))));							
		
		//condition
			
			
		//end condition

		try {
			$excel = new COM("Excel.Application") or die ("ERROR: Unable to instantaniate COM!\r\n");
			$excel->Visible = false;

			if($Advpayment->paymentform == 1) {
				$title = 'payment_hr';
				$file= SITE_PATH."/doc/hr/paymenthr.xlsx";
			} else {
				if($Advpayment->opscategory == 4) {
					$title = 'payment_ops_ssl';
					$file= SITE_PATH."/doc/hr/paymentopsssl.xlsx";
				} else {
					$title = 'payment_ops';
					$file= SITE_PATH."/doc/hr/paymentops.xlsx";
				}
			}

				
				$Workbook = $excel->Workbooks->Open($file,false,true) or die("ERROR: Unable to open " . $file . "!\r\n");
				$Worksheet = $Workbook->Worksheets(1);
				$Worksheet->Activate;

				if($Advpayment->companycode == 'NKF' || $Advpayment->companycode == 'RND') {
					$Worksheet->Range("A1")->Value = 'PT. ITCI Hutani Manunggal';
				} else {
					$Worksheet->Range("A1")->Value = $compx->companyname;
				}

				if($Advpayment->payment == 1) {
					$payment = 'Cash';
				} else if($Advpayment->payment == 2) {
					$payment = 'Bank';
				}

				// $Worksheet->Range("N6")->Value = date("d/m/Y",strtotime($Advpayment->createddate));
				// $Worksheet->Range("M15")->Value = date("d/m/Y",strtotime($duedate));
				// $Worksheet->Range("M16")->Value = date("d/m/Y",strtotime($paymentdate));
				$Worksheet->Range("M15")->Value = $duedate;
				$Worksheet->Range("M16")->Value = $paymentdate;

			// if($Advpayment->paymentform == 1) {
				$Worksheet->Range("E10")->Value = $fullname;
				$Worksheet->Range("E11")->Value = $department;
				$Worksheet->Range("E12")->Value = $Advpayment->employee->sapid;

				$Worksheet->Range("M10")->Value = $payment;
				$Worksheet->Range("E14")->Value = $Advpayment->beneficiary;
				$Worksheet->Range("E15")->Value = $Advpayment->accountname;
				$Worksheet->Range("E16")->Value = $Advpayment->bank;
				$Worksheet->Range("E17")->Value = $Advpayment->accountnumber;
			// } else {
			// 	$Worksheet->Range("E6")->Value = $fullname;
			// 	$Worksheet->Range("E7")->Value = $department;
			// 	$Worksheet->Range("E8")->Value = $Advpayment->employee_id;

			// 	$Worksheet->Range("E10")->Value = $Advpayment->beneficiary;
			// 	$Worksheet->Range("E11")->Value = $Advpayment->accountname;
			// 	$Worksheet->Range("E12")->Value = $Advpayment->bank;
			// 	$Worksheet->Range("E13")->Value = $Advpayment->accountnumber;
			// }




				foreach ($Advpaymentapproval as $data){
					if(($data->approver->approvaltype->id==35) || ($data->approver->employee_id==$Advpayment->depthead)){
						$deptheadname = $data->approver->employee->fullname;
						$deptheaddate = 'Date : '.date("d/m/Y",strtotime($data->approvaldate));
					}
				
					if($data->approver->approvaltype->id==36) {
						$hrdheadname = $data->approver->employee->fullname;
						$hrdheaddate = 'Date : '.date("d/m/Y",strtotime($data->approvaldate));
					}
					
					if($data->approver->approvaltype->id==37) {
						$bufcname = $data->approver->employee->fullname;
						$bufcdate = 'Date : '.date("d/m/Y",strtotime($data->approvaldate));
					}

					if($data->approver->approvaltype->id==38) {
						$buheadname = $data->approver->employee->fullname;
						$buheaddate = 'Date : '.date("d/m/Y",strtotime($data->approvaldate));
					}

					if($data->approver->approvaltype->id==39) {
						$depmdname = $data->approver->employee->fullname;
						$depmddate = 'Date : '.date("d/m/Y",strtotime($data->approvaldate));
					}

					if($data->approver->approvaltype->id==40) {
						$mdname = $data->approver->employee->fullname;
						$mddate = 'Date : '.date("d/m/Y",strtotime($data->approvaldate));
					}

					if($data->approver->approvaltype->id==41) {
						$financename = $data->approver->employee->fullname;
						$financedate = 'Date : '.date("d/m/Y",strtotime($data->approvaldate));
					}

					if($data->approver->approvaltype->id==42) {
						$procname = $data->approver->employee->fullname;
						$procdate = 'Date : '.date("d/m/Y",strtotime($data->approvaldate));
					}

					if($data->approver->approvaltype->id==45) {
						$kfsslname = $data->approver->employee->fullname;
						$kfssldate = 'Date : '.date("d/m/Y",strtotime($data->approvaldate));
					}
				}
				$picpath= SITE_PATH."/images/approved.png";
				
				$Worksheet->Range("A34")->Value = $fullname;
				$Worksheet->Range("A35")->Value = 'Date : '.date("d/m/Y",strtotime($Advpayment->createddate));

				$pic=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
				$pic->Height  = 20;
				$pic->Top = $excel->Cells(30, 1)->Top ;
				$pic->Left = $excel->Cells(30, 1)->Left ;

				if($Advpayment->paymentform == 1) {
					if(!empty($deptheadname)) {
						$Worksheet->Range("E34")->Value = $deptheadname;
						$Worksheet->Range("E35")->Value = $deptheaddate;
						$pic1=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic1->Height  = 20;
						$pic1->Top = $excel->Cells(30, 5)->Top ;
						$pic1->Left = $excel->Cells(30, 5)->Left ;
					}
	
					if(!empty($hrdheadname)) {
						$Worksheet->Range("G34")->Value = $hrdheadname;
						$Worksheet->Range("G35")->Value = $hrdheaddate;
						$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic2->Height  = 20;
						$pic2->Top = $excel->Cells(30, 7)->Top ;
						$pic2->Left = $excel->Cells(30, 7)->Left ;
					}
	
					if(!empty($bufcname)) {
						$Worksheet->Range("J34")->Value = $bufcname;
						$Worksheet->Range("J35")->Value = $bufcdate;
						$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic2->Height  = 20;
						$pic2->Top = $excel->Cells(30, 10)->Top ;
						$pic2->Left = $excel->Cells(30, 10)->Left ;
					}

					if(!empty($buheadname)) {
						$Worksheet->Range("M34")->Value = $buheadname;
						$Worksheet->Range("M35")->Value = $buheaddate;
						$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic2->Height  = 20;
						$pic2->Top = $excel->Cells(30, 13)->Top ;
						$pic2->Left = $excel->Cells(30, 13)->Left ;
					}
				
				} else {

					if($Advpayment->opscategory == 4) {
						if(!empty($deptheadname)) {
							$Worksheet->Range("D34")->Value = $deptheadname;
							$Worksheet->Range("D35")->Value = $deptheaddate;
							$pic1=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
							$pic1->Height  = 20;
							$pic1->Top = $excel->Cells(30, 4)->Top ;
							$pic1->Left = $excel->Cells(30, 4)->Left ;
						}

						if(!empty($kfsslname)) {
							$Worksheet->Range("F34")->Value = $kfsslname;
							$Worksheet->Range("F35")->Value = $kfssldate;
							$pic1=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
							$pic1->Height  = 20;
							$pic1->Top = $excel->Cells(30, 6)->Top ;
							$pic1->Left = $excel->Cells(30, 6)->Left ;
						}
						
						if(!empty($bufcname)) {
							$Worksheet->Range("I34")->Value = $bufcname;
							$Worksheet->Range("I35")->Value = $bufcdate;
							$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
							$pic2->Height  = 20;
							$pic2->Top = $excel->Cells(30, 9)->Top ;
							$pic2->Left = $excel->Cells(30, 9)->Left ;
						}
						
						if(!empty($buheadname)) {
							$Worksheet->Range("K34")->Value = $buheadname;
							$Worksheet->Range("K35")->Value = $buheaddate;
							$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
							$pic2->Height  = 20;
							$pic2->Top = $excel->Cells(30, 11)->Top ;
							$pic2->Left = $excel->Cells(30, 11)->Left ;
						}
						
					} else {

						if(!empty($deptheadname)) {
							$Worksheet->Range("E34")->Value = $deptheadname;
							$Worksheet->Range("E35")->Value = $deptheaddate;
							$pic1=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
							$pic1->Height  = 20;
							$pic1->Top = $excel->Cells(30, 5)->Top ;
							$pic1->Left = $excel->Cells(30, 5)->Left ;
						}
						
						if(!empty($bufcname)) {
							$Worksheet->Range("H34")->Value = $bufcname;
							$Worksheet->Range("H35")->Value = $bufcdate;
							$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
							$pic2->Height  = 20;
							$pic2->Top = $excel->Cells(30, 8)->Top ;
							$pic2->Left = $excel->Cells(30, 8)->Left ;
						}
						
						if(!empty($buheadname)) {
							$Worksheet->Range("K34")->Value = $buheadname;
							$Worksheet->Range("K35")->Value = $buheaddate;
							$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
							$pic2->Height  = 20;
							$pic2->Top = $excel->Cells(30, 11)->Top ;
							$pic2->Left = $excel->Cells(30, 11)->Left ;
						}
						
						if(!empty($financename)) {
							$Worksheet->Range("K28")->Value = 'BG FC';
							$Worksheet->Range("K34")->Value = $financename;
							$Worksheet->Range("K35")->Value = $financedate;
							$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
							$pic2->Height  = 20;
							$pic2->Top = $excel->Cells(30, 11)->Top ;
							$pic2->Left = $excel->Cells(30, 11)->Left ;
						}
					}
					
				}
				
				foreach ($Advpaymentdetail as $data){
					$val_tamount += $data->amount;
				}
				
				if($Advpayment->paymenttype == 0) {
					$lessadvance = 0;
				} else if($Advpayment->paymenttype == 1) {
					$lessadvance = $Advpayment->lessadvance;
				}
				$Worksheet->Range("K22")->Value = $val_tamount;
				$Worksheet->Range("K23")->Value = $lessadvance;
				$Worksheet->Range("K24")->Value = ($val_tamount-$lessadvance);

	
				$xlShiftDown=-4121;
				$no = 1;
				for ($a=20;$a<20+count($Advpaymentdetail);$a++){
					$Worksheet->Rows($a+1)->Copy();
					$Worksheet->Rows($a+1)->Insert($xlShiftDown);
					$Worksheet->Range("A".$a)->Value = $no++;
					$Worksheet->Range("B".$a)->Value = $Advpaymentdetail[$a-20]->description;
					$Worksheet->Range("I".$a)->Value = $Advpaymentdetail[$a-20]->accountcode;
					$Worksheet->Range("K".$a)->Value = $Advpaymentdetail[$a-20]->amount;
				}
		

				//end condition


			$xlTypePDF = 0;
			$xlQualityStandard = 0;
			$fileName ='doc'.DS.'hr'.DS.'pdf'.DS.$title.'_'.$Advpayment->employee->fullname.'_'.$Advpayment->employee->sapid.'_'.date("YmdHis").'.pdf';
			$fileName = str_replace("/","",$fileName);
			$path= SITE_PATH.'/doc'.DS.'hr'.DS.'pdf'.DS.$title.'_'.$Advpayment->employee->fullname.'_'.$Advpayment->employee->sapid.'_'.date("YmdHis").'.pdf';
			$pathcopy = 'doc\\hr\\pdf\\' . $title . '_' . $Advpayment->employee->fullname . '_' . $Advpayment->employee->sapid . '_' . date("YmdHis") . '.pdf';
			if (file_exists($path)) {
			unlink($path);
			}
			$Worksheet->ExportAsFixedFormat($xlTypePDF, $path, $xlQualityStandard);
			$Advpayment->approveddoc=str_replace("\\","/",$fileName);
			$Advpayment->save();

			// $excel->Application->CutCopyMode(false);
			$excel->CutCopyMode = false;
			$Workbook->Close(false);
			unset($Worksheet);
			unset($Workbook);
			$excel->Workbooks->Close();
			$excel->Quit();
			unset($excel);

			$output = 200;
			echo json_encode($output);

			$this->pathcopy = $pathcopy;
			$this->processcopy($pathcopy);

			return $fileName;

		} catch(com_exception $e) {  
			$err = new Errorlog();
			$err->errortype = "AdvpaymentFPDFGenerator";
			$err->errordate = date("Y-m-d h:i:s");
			$err->errormessage = $e->getMessage();
			$err->user = $this->currentUser->username;
			$err->ip = $this->ip;
			$err->save();
			// echo $formatter->getHtmlMessage();
			echo $e->getMessage()."\n";
			// exit;
		
		}
		
		
	}

	function advpaymentAttachment(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'byid':
						$id = $this->post['id'];
						if ($id!=""){
							$Advpaymentattachment = Advpaymentattachment::find('all', array('conditions' => array("advpayment_id=?",$id)));
							foreach ($Advpaymentattachment as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Advpaymentattachment, JSON_NUMERIC_CHECK);
						}else{
							$Advpaymentattachment = new Advpaymentattachment();
							echo json_encode($Advpaymentattachment);
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							$Advpaymentattachment = Advpaymentattachment::find('all', array('conditions' => array("advpayment_id=?",$query['advpayment_id'])));
							$data=array("jml"=>count($Advpaymentattachment));
						}else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
						$data['employee_id']=$Employee->id;
						unset($data['__KEY__']);
						
						$Advpaymentattachment = Advpaymentattachment::create($data);
						$logger = new Datalogger("Advpaymentattachment","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Advpaymentattachment = Advpaymentattachment::find($id);
						$data=$Advpaymentattachment->to_array();
						$Advpaymentattachment->delete();
						$logger = new Datalogger("Advpaymentattachment","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Advpaymentattachment);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
						$data['employee_id']=$Employee->id;
						$Advpaymentattachment = Advpaymentattachment::find($id);
						$olddata = $Advpaymentattachment->to_array();
						foreach($data as $key=>$val){					
							$val=($val=='No')?false:(($val=='Yes')?true:$val);
							$Advpaymentattachment->$key=$val;
						}
						$Advpaymentattachment->save();
						$logger = new Datalogger("Advpaymentattachment","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						echo json_encode($Advpaymentattachment);
						
						break;
					default:
						$Advpaymentattachment = Advpaymentattachment::all();
						foreach ($Advpaymentattachment as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Advpaymentattachment, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}
	public function uploadAdvpaymentFile(){
		$id= $this->get['id'];
		if(!isset($_FILES['myFile'])) {
			http_response_code(400);
			echo "There is no file to upload";
			exit;
		}
		$max_image_size = 5242880;
		if(!is_uploaded_file($_FILES['myFile']['tmp_name'])) {
			http_response_code(400);
			echo "Unable to upload File";
			exit;
		}
		if($_FILES['myFile']['size'] > $max_image_size) {
			http_response_code(413);
			echo "File Size too Large, Maximum 5MB";
			exit;
		}
		if((strpos($_FILES['myFile']['type'], "image") === false) && (strpos($_FILES['myFile']['type'], "pdf") === false) && (strpos($_FILES['myFile']['type'], "officedocument") === false)  && (strpos($_FILES['myFile']['type'], "msword") === false) && (strpos($_FILES['myFile']['type'], "excel") === false)){
			http_response_code(415);
			echo "Only Accept Image File, pdf or Office Document (Excel & Word) ";
			exit;
		}
		$path_to_file = "upload\\advpayment\\".$id."_".time()."_".$_FILES['myFile']['name'];
		$path_to_file = str_replace("%","_",$path_to_file);
		$path_to_file = str_replace(" ","_",$path_to_file);
		echo $path_to_file;
        move_uploaded_file($_FILES['myFile']['tmp_name'], $path_to_file);

		$this->processcopy($path_to_file);
	}

}