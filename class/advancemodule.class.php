<?php
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

Class Advancemodule extends Application{
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
				case 'apiadvancebyemp':
					$this->advanceByEmp();
					break;
				case 'apiadvance':
					$this->advance();
					break;
				case 'apiadvancedetail':
					$this->advanceDetail();
					break;
				case 'apiadvanceapp':
					$this->advanceApproval();
					break;
				case 'apiadvancetmsapp':
					$this->advanceTMSApproval();
					break;
				case 'apiadvancehist':
					$this->advanceHistory();
					break;
				case 'apiadvancetmshist':
					$this->advanceTMSHistory();
					break;
				case 'apiadvancepdf':	
					$id = $this->get['id'];
					$this->generatePDF($id);
					break;
				case 'apiadvancetmspdf':	
					$id = $this->get['id'];
					$this->generateTMSPDF($id);
					break;
				case 'apiadvancetms':
					$this->SPKLTms();
					break;
				case 'apitestxl2pdf':
					$this->testExcel();
					break;
				default:
					break;
			}
		}
	}
	

	function advanceByEmp(){	
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
							$Advance = Advance::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Advance as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Advance, JSON_NUMERIC_CHECK);
						}else{
							echo json_encode(array());
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							switch ($query['status']){
								default:
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
									$Advance = Advance::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Advance as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Advance));
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
							$Advance = Advance::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Advance as &$result) {
								$fullname=$result->employee->fullname;
								$result = $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Advance, JSON_NUMERIC_CHECK);
						}else{
							echo json_encode(array());
						}
						break;
				}
			}
		}
	}
	
	function advance(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'byid':
						$id = $this->post['id'];
						$Advance = Advance::find($id, array('include' => array('employee'=>array('company','department','designation'))));
						if ($Advance){
							$fullname = $Advance->employee->fullname;
							$department = $Advance->employee->department->departmentname;
							$data=$Advance->to_array();
							$data['fullname']=$fullname;
							$data['department']=$department;
							echo json_encode($data, JSON_NUMERIC_CHECK);
						}else{
							$Advance = new Advance();
							echo json_encode($Advance);
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
										$employee_id = $query['employee_id'];
										$id= $query['advance_id'];

										$Advance = Advance::find($id);

										$Employee = Employee::find('first', array('conditions' => array("id=?",$employee_id),"include"=>array("location","department","company")));

										print_r($valamount);

										$joins   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id)";
										$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";	
										// if (($valamount=='2') || ($valamount=='3')){
										$AdvanceapprovalBU = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id='38' ",$id)));	
										foreach ($AdvanceapprovalBU as &$result) {
											$result		= $result->to_array();
											$result['no']=1;
										}
										$Advanceapprovaldepmd = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id='39' ",$id)));	
										foreach ($Advanceapprovaldepmd as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}
										$Advanceapprovalmd = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id='40' ",$id)));	
										foreach ($Advanceapprovalmd as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}
										

										if (($valamount<=5000000)){

											// if(count($AdvanceapprovalBU)==0){

											// 		if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220")){
											// 			$ApproverBU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and tbl_employee.location_id='1'")));
											// 			print_r($ApproverBU);
											// 			if(count($ApproverBU)>0){
											// 				$Advanceapproval = new Advanceapproval();
											// 				$Advanceapproval->advance_id =$Advance->id;
											// 				$Advanceapproval->approver_id = $ApproverBU->id;
											// 				$Advanceapproval->save();
											// 				$logger = new Datalogger("Advanceapproval","add","Add initial BU Approval",json_encode($Advanceapproval->to_array()));
											// 				$logger->SaveData();
											// 			}
											// 		}else{
											// 			$ApproverBU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38'  and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
											// 			if(count($ApproverBU)>0){
											// 				$Advanceapproval = new Advanceapproval();
											// 				$Advanceapproval->advance_id = $Advance->id;
											// 				$Advanceapproval->approver_id = $ApproverBU->id;
											// 				$Advanceapproval->save();
											// 				$logger = new Datalogger("Advanceapproval","add","Add initial BU Approval",json_encode($Advanceapproval->to_array()));
											// 				$logger->SaveData();
											// 			}
											// 		}
													
											// }

											$dx = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=39",$id)));	
											foreach ($dx as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval Deputy");
												$logger->SaveData();
											}

											$md = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=40",$id)));	
											foreach ($md as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval MD");
												$logger->SaveData();
											}
													
										} else if(($valamount>5000000 && $valamount<10000000)){

											if(count($Advanceapprovaldepmd)==0){

												if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220")){
													$ApproverDEPMD = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='39' and tbl_employee.location_id='1'")));
													print_r($ApproverDEPMD);
													if(count($ApproverDEPMD)>0){
														$Advanceapproval = new Advanceapproval();
														$Advanceapproval->advance_id =$Advance->id;
														$Advanceapproval->approver_id = $ApproverDEPMD->id;
														$Advanceapproval->save();
														$logger = new Datalogger("Advanceapproval","add","Add initial Deputy Approval",json_encode($Advanceapproval->to_array()));
														$logger->SaveData();
													}
												}else{
													$ApproverDEPMD = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='39'  and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
													if(count($ApproverDEPMD)>0){
														$Advanceapproval = new Advanceapproval();
														$Advanceapproval->advance_id = $Advance->id;
														$Advanceapproval->approver_id = $ApproverDEPMD->id;
														$Advanceapproval->save();
														$logger = new Datalogger("Advanceapproval","add","Add initial Deputy Approval",json_encode($Advanceapproval->to_array()));
														$logger->SaveData();
													}
												}
											}

											$md = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=40",$id)));	
											foreach ($md as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval MD");
												$logger->SaveData();
											}

										} else if(($valamount>=10000000)){

											if(count($Advanceapprovaldepmd)==0){

												if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220")){
													$ApproverDEPMD = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='39' and tbl_employee.location_id='1'")));
													print_r($ApproverDEPMD);
													if(count($ApproverDEPMD)>0){
														$Advanceapproval = new Advanceapproval();
														$Advanceapproval->advance_id =$Advance->id;
														$Advanceapproval->approver_id = $ApproverDEPMD->id;
														$Advanceapproval->save();
														$logger = new Datalogger("Advanceapproval","add","Add initial Deputy Approval",json_encode($Advanceapproval->to_array()));
														$logger->SaveData();
													}
												}else{
													$ApproverDEPMD = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='39'  and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
													if(count($ApproverDEPMD)>0){
														$Advanceapproval = new Advanceapproval();
														$Advanceapproval->advance_id = $Advance->id;
														$Advanceapproval->approver_id = $ApproverDEPMD->id;
														$Advanceapproval->save();
														$logger = new Datalogger("Advanceapproval","add","Add initial Deputy Approval",json_encode($Advanceapproval->to_array()));
														$logger->SaveData();
													}
												}
											}

											if(count($Advanceapprovalmd)==0){

													$ApproverMD = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='40' and tbl_employee.location_id='1'")));
													print_r($ApproverMD);
													if(count($ApproverMD)>0){
														$Advanceapproval = new Advanceapproval();
														$Advanceapproval->advance_id =$Advance->id;
														$Advanceapproval->approver_id = $ApproverMD->id;
														$Advanceapproval->save();
														$logger = new Datalogger("Advanceapproval","add","Add initial Deputy Approval",json_encode($Advanceapproval->to_array()));
														$logger->SaveData();
													}
												
											}

										} 
										// else {
										// 	echo "2";

										// 	// if(count($Advanceapproval)>0){
										// 	// 	$dx = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=30",$id)));	
										// 	// 	foreach ($dx as $result) {
										// 	// 		$result->delete();
										// 	// 		$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval other form");
										// 	// 		$logger->SaveData();
										// 	// 	}
										// 	// }

										// 	if(($valamount <= 5000000 )){
										// 		$dx = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=39",$id)));	
										// 		foreach ($dx as $result) {
										// 			$result->delete();
										// 			$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval Deputy");
										// 			$logger->SaveData();
										// 		}
										// 	}
										// }
										
								break;
								default:
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$query['username'])));
									$Advance = Advance::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Advance as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Advance));
									break;
							}
						} else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':		
						$data = $this->post['data'];
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$data['username']),"include"=>array("location","company","department")));
							unset($data['__KEY__']);
							unset($data['username']);
							$data['employee_id']=$Employee->id;
							$data['RequestStatus']=0;
							try{
								$Advance = Advance::create($data);
								$data=$Advance->to_array();
								
								$joins   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";

								$ApproverFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=41")));
								if(count($ApproverFC)>0){
									$Advanceapproval = new Advanceapproval();
									$Advanceapproval->advance_id = $Advance->id;
									$Advanceapproval->approver_id = $ApproverFC->id;
									$Advanceapproval->save();
								}

								if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220")){
									$ApproverBU = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and tbl_employee.location_id='1'")));
									if(count($ApproverBU)>0){
										$Advanceapproval = new Advanceapproval();
										$Advanceapproval->advance_id =$Advance->id;
										$Advanceapproval->approver_id = $ApproverBU->id;
										$Advanceapproval->save();
										$logger = new Datalogger("Advanceapproval","add","Add initial BU Approval",json_encode($Advanceapproval->to_array()));
										$logger->SaveData();
									}
									
									$ApproverBUFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37' and tbl_employee.location_id='1'")));
									if(count($ApproverBUFC)>0){
										$Advanceapproval = new Advanceapproval();
										$Advanceapproval->advance_id =$Advance->id;
										$Advanceapproval->approver_id = $ApproverBUFC->id;
										$Advanceapproval->save();
										$logger = new Datalogger("Advanceapproval","add","Add initial BUFC Approval",json_encode($Advanceapproval->to_array()));
										$logger->SaveData();
									}
									
									$ApproverHR = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='36' and tbl_employee.location_id='1'")));
									if(count($ApproverHR)>0){
										$Advanceapproval = new Advanceapproval();
										$Advanceapproval->advance_id =$Advance->id;
										$Advanceapproval->approver_id = $ApproverHR->id;
										$Advanceapproval->save();
										$logger = new Datalogger("Advanceapproval","add","Add initial HR Approval",json_encode($Advanceapproval->to_array()));
										$logger->SaveData();
									}
								}else{
									$ApproverBU = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38'  and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
									if(count($ApproverBU)>0){
										$Advanceapproval = new Advanceapproval();
										$Advanceapproval->advance_id = $Advance->id;
										$Advanceapproval->approver_id = $ApproverBU->id;
										$Advanceapproval->save();
										$logger = new Datalogger("Advanceapproval","add","Add initial BU Approval",json_encode($Advanceapproval->to_array()));
										$logger->SaveData();
									}

									$ApproverBUFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37'  and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
									if(count($ApproverBUFC)>0){
										$Advanceapproval = new Advanceapproval();
										$Advanceapproval->advance_id = $Advance->id;
										$Advanceapproval->approver_id = $ApproverBUFC->id;
										$Advanceapproval->save();
										$logger = new Datalogger("Advanceapproval","add","Add initial BUFC Approval",json_encode($Advanceapproval->to_array()));
										$logger->SaveData();
									}

									$ApproverHR = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='36'  and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
									if(count($ApproverHR)>0){
										$Advanceapproval = new Advanceapproval();
										$Advanceapproval->advance_id = $Advance->id;
										$Advanceapproval->approver_id = $ApproverHR->id;
										$Advanceapproval->save();
										$logger = new Datalogger("Advanceapproval","add","Add initial HR Approval",json_encode($Advanceapproval->to_array()));
										$logger->SaveData();
									}
								}

								$Advancehistory = new Advancehistory();
								$Advancehistory->date = date("Y-m-d h:i:s");
								$Advancehistory->fullname = $Employee->fullname;
								$Advancehistory->approvaltype = "Originator";
								$Advancehistory->advance_id = $Advance->id;
								$Advancehistory->actiontype = 0;
								$Advancehistory->save();
								
							}catch (Exception $e){
								$err = new Errorlog();
								$err->errortype = "CreateAdvance";
								$err->errordate = date("Y-m-d h:i:s");
								$err->errormessage = $e->getMessage();
								$err->user = $this->currentUser->username;
								$err->ip = $this->ip;
								$err->save();
								$data = array("status"=>"error","message"=>$e->getMessage());
							}
							$logger = new Datalogger("Advance","create",null,json_encode($data));
							$logger->SaveData();

						echo json_encode($data);									
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Advance = Advance::find($id);
						if ($Advance->requeststatus==0){
							try {
								$approval = Advanceapproval::find("all",array('conditions' => array("advance_id=?",$id)));
								foreach ($approval as $delr){
									$delr->delete();
								}
								$detail = Advancedetail::find("all",array('conditions' => array("advance_id=?",$id)));
								foreach ($detail as $delr){
									$delr->delete();
								}
								$hist = Advancehistory::find("all",array('conditions' => array("advance_id=?",$id)));
								foreach ($hist as $delr){
									$delr->delete();
								}
								$data = $Advance->to_array();
								$Advance->delete();
								$logger = new Datalogger("Advance","delete",json_encode($data),null);
								$logger->SaveData();
								$data = array("status"=>"success","message"=>"Data has been deleted");
							}catch (Exception $e){
								$err = new Errorlog();
								$err->errortype = "DeleteAdvance";
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
						$Advance = Advance::find($id,array('include'=>array('employee'=>array('company','department','designation','grade'))));
						$olddata = $Advance->to_array();
						$depthead = $data['depthead'];
						unset($data['approvalstatus']);
						unset($data['fullname']);
						unset($data['department']);
						//unset($data['employee']);
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
						foreach($data as $key=>$val){
							$Advance->$key=$val;
						}
						$Advance->save();
						
						if (isset($data['depthead'])){
							$joins   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";					
							$dx = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=35 and not(tbl_approver.employee_id=?)",$id,$depthead)));	
							foreach ($dx as $result) {
								//delete same type approver
								$result->delete();
								$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
							}				
							$Advanceapproval = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.employee_id=?",$id,$depthead)));	
							foreach ($Advanceapproval as &$result) {
								$result		= $result->to_array();
								$result['no']=1;
							}			
							if(count($Advanceapproval)==0){ 
								$Approver = Approver::find('first',array('conditions'=>array("module='Advance' and employee_id=? and approvaltype_id=35",$depthead)));
								if(count($Approver)>0){
									$Advanceapproval = new Advanceapproval();
									$Advanceapproval->advance_id = $Advance->id;
									$Advanceapproval->approver_id = $Approver->id;
									$Advanceapproval->save();
								}else{
									$approver = new Approver();
									$approver->module = "Advance";
									$approver->employee_id=$depthead;
									$approver->sequence=1;
									$approver->approvaltype_id = 35;
									$approver->isfinal = false;
									$approver->save();
									$Advanceapproval = new Advanceapproval();
									$Advanceapproval->advance_id = $Advance->id;
									$Advanceapproval->approver_id = $approver->id;
									$Advanceapproval->save();
								}
							}
						}
						if($data['requeststatus']==1){
							$Advanceapproval = Advanceapproval::find('all', array('conditions' => array("advance_id=?",$id)));					
							foreach($Advanceapproval as $data){
								$data->approvalstatus=0;
								$data->save();
							}
							$joinx   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";					
							$Advanceapproval = Advanceapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and advance_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
							$username = $Advanceapproval->approver->employee->loginname;
							$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
							$email = $adb->email;
							// $Advancedetail=Advancedetail::find('all',array('conditions'=>array("advance_id=?",$id),'include'=>array('advance','employee'=>array('company','department','designation','grade'))));
							$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$adb->fullname.',</span></p>
										<p class=MsoNormal><span style="color:#1F497D">new '.$title.' Request is awaiting for your approval:</span></p>
										<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advance->employee->fullname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advance->employee->sapid.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advance->employee->designation->designationname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advance->employee->company->companyname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advance->employee->location->location.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
										</table>
										<br>

										';
							if($Advance->advanceform == 0) {
								$form = "HR Related";
							} else {
								$form = "Ops Related";
							}
							$Advancedetail = Advancedetail::find('all',array('conditions'=>array("advance_id=?",$id),'include'=>array('advance'=>array('employee'=>array('company','department','designation','grade','location')))));	

							$this->mailbody .='
								<table border=1 cellspacing=0 cellpadding=3 width=683>
								<tr>
									<th><p class=MsoNormal>Advance Form</p></th>
									<th><p class=MsoNormal>Beneficiary</p></th>
									<th><p class=MsoNormal>Account Name</p></th>
									<th><p class=MsoNormal>Bank</p></th>
									<th><p class=MsoNormal>Account Number</p></th>
									<th><p class=MsoNormal>Due Date</p></th>
									<th><p class=MsoNormal>Expected Date</p></th>
									<th><p class=MsoNormal>Remarks</p></th>
								</tr>
								<tr style="height:22.5pt">
									<td><p class=MsoNormal> '.$form.'</p></td>
									<td><p class=MsoNormal> '.$Advance->beneficiary.'</p></td>
									<td><p class=MsoNormal> '.$Advance->accountname.'</p></td>
									<td><p class=MsoNormal> '.$Advance->bank.'</p></td>
									<td><p class=MsoNormal> '.$Advance->accountnumber.'</p></td>
									<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Advance->duedate)).'</p></td>
									<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Advance->expecteddate)).'</p></td>
									<td><p class=MsoNormal> '.$Advance->remarks.'</p></td>
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
							foreach ($Advancedetail as $data){
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
							<p><b><span>Total Amount : '.number_format($val_tamount).'</span></b></p><br>
							<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
							$this->mail->addAddress($adb->email, $adb->fullname);
							$this->mail->Subject = "Online Approval System -> Advance";
							$this->mail->msgHTML($this->mailbody);
							if (!$this->mail->send()) {
								$err = new Errorlog();
								$err->errortype = "Advance Mail";
								$err->errordate = date("Y-m-d h:i:s");
								$err->errormessage = $this->mail->ErrorInfo;
								$err->user = $this->currentUser->username;
								$err->ip = $this->ip;
								$err->save();
								echo "Mailer Error: " . $this->mail->ErrorInfo;
							} else {
								echo "Message sent!";
							}
							$Advancehistory = new Advancehistory();
							$Advancehistory->date = date("Y-m-d h:i:s");
							$Advancehistory->fullname = $Employee->fullname;
							$Advancehistory->advance_id = $id;
							$Advancehistory->approvaltype = "Originator";
							$Advancehistory->actiontype = 2;
							$Advancehistory->save();
						}else{
							$Advancehistory = new Advancehistory();
							$Advancehistory->date = date("Y-m-d h:i:s");
							$Advancehistory->fullname = $Employee->fullname;
							$Advancehistory->advance_id = $id;
							$Advancehistory->approvaltype = "Originator";
							$Advancehistory->actiontype = 1;
							$Advancehistory->save();
						}
						$logger = new Datalogger("Advance","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						//echo json_encode($Advance);
						
						break;
					default:
						$Advance = Advance::all();
						foreach ($Advance as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Advance, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}

	function advanceApproval(){
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
							$join   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";
							$Advanceapproval = Advanceapproval::find('all', array('joins'=>$join,'conditions' => array("advance_id=?",$id),'include' => array('approver'=>array('approvaltype')),"order"=>"tbl_approver.sequence"));
							foreach ($Advanceapproval as &$result) {
								$approvaltype = $result->approver->approvaltype_id;
								$result		= $result->to_array();
								$result['approvaltype']=$approvaltype;
							}
							echo json_encode($Advanceapproval, JSON_NUMERIC_CHECK);
						}else{
							$Advanceapproval = new Advanceapproval();
							echo json_encode($Advanceapproval);
						}
						break;
					case 'find':
						$query=$this->post['query'];		
						if(isset($query['status'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";
							$dx = Advanceapproval::find('first', array('joins'=>$join,'conditions' => array("advance_id=? and tbl_approver.employee_id = ?",$query['advance_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
							$Advance = Advance::find($query['advance_id']);
							if($dx->approver->isfinal==1){
								$data=array("jml"=>1);
							}else{
								$join   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";
								$Advanceapproval = Advanceapproval::find('all', array('joins'=>$join,'conditions' => array("advance_id=? and ApprovalStatus<=1 and not tbl_approver.employee_id=?",$query['advance_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
								foreach ($Advanceapproval as &$result) {
									$fullname	= $result->approver->employee->fullname;	
									$result		= $result->to_array();
									$result['fullname']=$fullname;
								}
								$data=array("jml"=>count($Advanceapproval));
							}						
						} else if(isset($query['pending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
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
							$data=$Advance;
						} else if(isset($query['mypending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
							$Advance = Advance::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
							$jml=0;
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
							$data=array("jml"=>count($Advance));
						} else if(isset($query['filter'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join = "LEFT JOIN vwadvancereport v on tbl_advance.id=v.id LEFT JOIN tbl_employee ON (tbl_advance.employee_id = tbl_employee.id) ";
							$sel = 'tbl_advance.*, v.advancestatus,v.otstatus,v.personholding ';
							$Advance = Advance::find('all',array('joins'=>$join,'select'=>$sel,'include' => array('employee')));
							
							if($Employee->location->sapcode=='0200' || $this->currentUser->isadmin){
								$Advance = Advance::find('all',array('joins'=>$join,'select'=>$sel,'include' => array('employee'=>array('company','department'))));
							}else{
								$Advance = Advance::find('all',array('joins'=>$join,'select'=>$sel,'conditions' => array('tbl_advance.RequestStatus=3 and tbl_employee.company_id=?',$Employee->company_id ),'include' => array('employee'=>array('company','department'))));
							}
							
							foreach ($Advance as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=$Advance;
						} else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Advanceapproval = Advanceapproval::create($data);
						$logger = new Datalogger("Advanceapproval","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':
						$id = $this->post['id'];
						$Advanceapproval = Advanceapproval::find($id);
						$data=$Advanceapproval->to_array();
						$Advanceapproval->delete();
						$logger = new Datalogger("Advanceapproval","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Advanceapproval);
						break;
					case 'update':
						$doid = $this->post['id'];
						$data = $this->post['data'];
						$mode= $data['mode'];
						$Spkldetail=Spkldetail::find('all',array('conditions'=>array("advance_id=?",$doid),'include'=>array('advance'=>array('employee'=>array('company','department','designation','grade')))));
						$allcheck = 0;
						foreach ($Spkldetail as $result) {
							if(is_null($result->isapproved)){
								$allcheck+=1;
							}
						}
						if (($data['approvalstatus']=='1') || ($data['approvalstatus']=='3')){
							$allcheck=0;
						}
						if($allcheck>0){
							$result= array("status"=>"error","message"=>"Need to do approval/reject on each detail Overtime request");
							echo json_encode($result);
						}else{
							unset($data['id']);
							unset($data['depthead']);
							unset($data['fullname']);
							unset($data['department']);
							unset($data['datework']);
							unset($data['approveddoc']);
							unset($data['isexceedplan']);
							unset($data['approvalstep']);
							unset($data['ismorethan2hours']);
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							
							$join   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";
							if (isset($data['mode'])){
								$Advanceapproval = Advanceapproval::find('first', array('joins'=>$join,'conditions' => array("advance_id=? and tbl_approver.employee_id=?",$doid,$Employee->id),'include' => array('approver'=>array('employee','approvaltype'))));
								unset($data['mode']);
							}else{
								$Advanceapproval = Advanceapproval::find($this->post['id'],array('include' => array('approver'=>array('employee','approvaltype'))));
							}
							$olddata = $Advanceapproval->to_array();
							foreach($data as $key=>$val){
								$val=($val=='false')?false:(($val=='true')?true:$val);
								$Advanceapproval->$key=$val;
							}
							$Advanceapproval->save();
							$logger = new Datalogger("Advanceapproval","update",json_encode($olddata),json_encode($data));
							$logger->SaveData();
							if (isset($mode) && ($mode=='approve')){
								$Advance = Advance::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";					
								$nAdvanceapproval = Advanceapproval::find('first',array('joins'=>$joinx,'conditions' => array("advance_id=? and ApprovalStatus=0",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								$username = $nAdvanceapproval->approver->employee->loginname;
								$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
								$Advancedetail=Advancedetail::find('all',array('conditions'=>array("advance_id=?",$doid),'include'=>array('advance'=>array('employee'=>array('company','department','designation','grade','location')))));
								if ($Advance->datework !== null){
									foreach ($Advancedetail as $row){
										if ($row->isapproved){
											$time = new DateTime($Advance->datework);
											$time->add(new DateInterval('PT8H'));
											$start = $time->format('Y-m-d H:i');
											$row->actualstartwork = $start;
											$time = new DateTime($start);
											$time->add(new DateInterval('PT' . ($row->estimatenormalhours + $row->estimateovertimehours+1). 'H'));
											$end = $time->format('Y-m-d H:i');
											$row->actualendwork = $end;
											
											$row->actualtotalhours = $row->estimatenormalhours + $row->estimateovertimehours;
											$row->actualnormalhours = $row->estimatenormalhours;
											$row->actualovertimehours = $row->estimateovertimehours;
											
										}else {
											$row->actualstartwork = null;
											$row->actualendwork= null;
											$row->actualtotalhours = 0;
											$row->actualnormalhours = 0;
											$row->actualovertimehours = 0;
											$Reject = Employee::find('first', array('conditions' => array("id=?", $row->rejectadvanceby)));
											$row->descriptionofwork = "SPKL Rejected by ".$Reject->fullname;
										}
										$row->save();	
									}
								}
								$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Advance->employee->loginname)));
								$email=$usr->email;
								
								$complete = false;
								$Advancehistory = new Advancehistory();
								$Advancehistory->date = date("Y-m-d h:i:s");
								$Advancehistory->fullname = $Employee->fullname;
								$Advancehistory->approvaltype = $Advanceapproval->approver->approvaltype->approvaltype;
								$Advancehistory->remarks = $data['remarks'];
								$Advancehistory->advance_id = $doid;
								
								switch ($data['approvalstatus']){
									case '1':
										$Advance->requeststatus = 2;
										$Advance->approvalstep = 0;
										$emto=$email;$emname=$Advance->employee->fullname;
										$this->mail->Subject = "Online Approval System -> Need Rework";
										$red = 'Your SPKL/ Overtime request require some rework :';
										$Advancehistory->actiontype = 3;
										break;
									case '2':
										if ($Advanceapproval->approver->isfinal == 1 || ($Advanceapproval->approver->approvaltype_id=='21' && $Advance->ismorethan2hours == 0)){
											$Advance->requeststatus = 3;
											$Advance->approvalstep = 0;
											$emto=$email;$emname=$Advance->employee->fullname;
											$this->mail->Subject = "Online Approval System -> Approval Completed";
											$red = 'Your SPKL/Overtime request has been approved';
											//delete unnecessary approver
											$Advanceapproval = Advanceapproval::find('all', array('joins'=>$join,'conditions' => array("advance_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
											foreach ($Advanceapproval as $data) {
												if($data->approvalstatus==0){
													$logger = new Datalogger("Advanceapproval","delete",json_encode($data->to_array()),"automatic remove unnecessary approver by system");
													$logger->SaveData();
													$data->delete();
												}
											}
											$complete =true;
										}
										else{
											$Advance->requeststatus = 1;
											$Advance->approvalstep += 1;
											$emto=$adb->email;$emname=$adb->fullname;
											$this->mail->Subject = "Online Approval System -> New SPKL/Overtime Submission";
											$red = 'New SPKL/Overtime request awaiting for your approval:';
										}
										$Advancehistory->actiontype = 4;							
										break;
									case '3':
										$Advance->requeststatus = 4;
										$Advance->approvalstep = 4;
										$emto=$email;$emname=$Advance->employee->fullname;
										$Advancehistory->actiontype = 5;
										$this->mail->Subject = "Online Approval System -> Request Rejected";
										$red = 'Your SPKL/Overtime request has been rejected';
										break;
									default:
										break;
								}
								//print_r($Advance);
								$Advance->save();
								$Advancehistory->save();
								echo "email to :".$emto." ->".$emname;
								$this->mail->addAddress($emto, $emname);
								
								$AdvanceJ = Advance::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));
								$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$emname.',</span></p>
													<p class=MsoNormal><span style="color:#1F497D">'.$red.'</span></p>
													<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
													<table border=1 cellspacing=0 cellpadding=3 width=683>
														<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advance->employee->fullname.'</b></p></td></tr>
														<tr><td><p class=MsoNormal>Creation Date</p></td><td>:</td><td><p class=MsoNormal><b>'.date("d/m/Y",strtotime($Advance->createddate)).'</b></p></td></tr>
														<tr><td><p class=MsoNormal>Date Work</p></td><td>:</td><td><p class=MsoNormal><b>'.date("d/m/Y",strtotime($Advance->datework)).'</b></p></td></tr>';
								$this->mailbody .='</table>
													<p class=MsoNormal><b>SPKL Detail :</b></p>
													<table border=1 cellspacing=0 cellpadding=3 width=683><tr><th  rowspan="2"><p class=MsoNormal>No</p></th>
													<th rowspan="2"><p class=MsoNormal>Employee Name</p></th>
													<th rowspan="2"><p class=MsoNormal>SAPID</p></th>
													<th rowspan="2"><p class=MsoNormal>Position</p></th>
													<th colspan="2"><p class=MsoNormal>Estimate Time for Work</p></th>
													
													<th rowspan="2"><p class=MsoNormal>Target Work</p></th>
													<th rowspan="2"><p class=MsoNormal>Remarks</p></th>
													</tr>
													<tr><th><p class=MsoNormal>Normal</p></th>
													<th><p class=MsoNormal>Overtime</p></th></tr>
													';
								$no=1;
								foreach ($Advancedetail as $data){
									$this->mailbody .='<tr style="height:22.5pt">
												<td><p class=MsoNormal> '.$no.'</p></td>
												<td><p class=MsoNormal> '.$data->employee->fullname.'</p></td>
												<td><p class=MsoNormal> '.$data->employee->sapid.'</p></td>
												<td><p class=MsoNormal> '.$data->employee->designation->designationname.'</p></td>
												<td><p class=MsoNormal> '.$data->estimatenormalhours.'</p></td>
												<td><p class=MsoNormal> '.$data->estimateovertimehours.'</p></td>
												<td><p class=MsoNormal> '.$data->target.'</p></td>
												<td><p class=MsoNormal> '.$data->remarks.'</p></td>
									</tr>';
									$no++;
								}
								$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
								$this->mail->msgHTML($this->mailbody);
								if ($complete){
									$filePath= $this->generatePDF($doid);
									$this->mail->addAttachment($filePath);
								}
								if (!$this->mail->send()) {
									$err = new Errorlog();
									$err->errortype = "SPKL Mail";
									$err->errordate = date("Y-m-d h:i:s");
									$err->errormessage = $this->mail->ErrorInfo;
									$err->user = $this->currentUser->username;
									$err->ip = $this->ip;
									$err->save();
									echo "Mailer Error: " . $this->mail->ErrorInfo;
								} else {
									
									echo "Message sent!";
								}
							}
							echo json_encode($Advanceapproval);
						}
						break;
					default:
						$Advanceapproval = Advanceapproval::all();
						foreach ($Advanceapproval as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Advanceapproval, JSON_NUMERIC_CHECK);
						break;
				}
			}
			// else{
				// $result= array("status"=>"error","message"=>"Authentication error or expired, please refresh and re login application");
				// echo json_encode($result);
			// }
		}
	}

	function advanceDetail(){
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
							// $join = "LEFT JOIN vwitsharefreport ON tbl_itsharefdetail.advance_id = vwitsharefreport.id";
							// $select = "tbl_itsharefdetail.*,vwitsharefreport.apprstatuscode";
							// $Advancedetail = Advancedetail::find('all', array('joins'=>$join,'select'=>$select,'conditions' => array("advance_id=?",$id)));
							$Advancedetail = Advancedetail::find('all', array('conditions' => array("advance_id=?",$id)));
							foreach ($Advancedetail as &$result) {
								$result	= $result->to_array();
							}
	
							echo json_encode($Advancedetail, JSON_NUMERIC_CHECK);
						}else{
							$Advancedetail = new Advancedetail();
							echo json_encode($Advancedetail);
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							$Advancedetail = Advancedetail::find('all', array('conditions' => array("advance_id=?",$query['advance_id'])));
							$data=array("jml"=>count($Advancedetail));
						}else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);

						$Advancedetail = Advancedetail::create($data);
						$logger = new Datalogger("Advancedetail","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Advancedetail = Advancedetail::find($id);
						$data=$Advancedetail->to_array();
						$Advancedetail->delete();
						$logger = new Datalogger("Advancedetail","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Advancedetail);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						
						$Advancedetail = Advancedetail::find($id);
						$olddata = $Advancedetail->to_array();
						// foreach($data as $key=>$val){
						// 	$Advancedetail->$key=$val;
						// }
						foreach($data as $key=>$val){
							// $val=($val=='true')?1:0;
							if($val=='true') {
								$val = 1;
							}else if($val=='false') {
								$val = 0;
							}
							$Advancedetail->$key=$val;
							
						}
						// $exprice = $Advancedetail->unitprice * $Advancedetail->qty;
						// $Advancedetail->extendedprice = $exprice;
						$Advancedetail->save();
						$logger = new Datalogger("Advancedetail","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						echo json_encode($Advancedetail);
						
						break;
					default:
						$Advancedetail = Advancedetail::all();
						foreach ($Advancedetail as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Advancedetail, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}

	function advanceHistory(){
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
							$Advancehistory = Advancehistory::find('all', array('conditions' => array("advance_id=?",$id),'include' => array('advance')));
							foreach ($Advancehistory as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Advancehistory, JSON_NUMERIC_CHECK);
						}
						break;
					default:
						break;
				}
			}
		}
	}

	function generatePDFi($id){
		$Itsharef = Itsharef::find($id);
		$Itsharefdetail = Itsharefdetail::find('all',array('conditions'=>array("itsharef_id=?",$id),'include'=>array('itsharef'=>array('employee'=>array('company','department','designation','grade','location')))));	
		
		$superiorId=$Itsharef->depthead;
		$Superior = Employee::find($superiorId);
		$supAdb = Addressbook::find('first',array('conditions'=>array("username=?",$Superior->loginname)));
		$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Itsharef->employee->loginname)));
		$email=$usr->email;
		$fullname=$Itsharef->employee->fullname;

		$datefrom = date("d/m/Y",strtotime($Itsharef->validfrom));
		$dateto = date("d/m/Y",strtotime($Itsharef->validto));

		$joinx   = "LEFT JOIN tbl_approver ON (tbl_itsharefapproval.approver_id = tbl_approver.id) ";					
		$Itsharefapproval = Itsharefapproval::find('all',array('joins'=>$joinx,'conditions' => array("itsharef_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee','approvaltype'))));							
		
		//condition
			
			
		//end condition

		try {
			$excel = new COM("Excel.Application") or die ("ERROR: Unable to instantaniate COM!\r\n");
			$excel->Visible = false;

				$title = 'sharefolder';


				$file= SITE_PATH."/doc/it/template_sharefoldernew1.xls";
				$Workbook = $excel->Workbooks->Open($file) or die("ERROR: Unable to open " . $file . "!\r\n");
				$Worksheet = $Workbook->Worksheets(1);
				$Worksheet->Activate;

				$Worksheet->Range("F7")->Value = $fullname;
				$Worksheet->Range("F9")->Value = $Itsharef->employee_id;
				$Worksheet->Range("F11")->Value = $Itsharef->designation;
				$Worksheet->Range("F13")->Value = $Itsharef->bgbu;
				$Worksheet->Range("F15")->Value = $Itsharef->officelocation;
				$Worksheet->Range("Y15")->Value = $Itsharef->floor;
				$Worksheet->Range("F17")->Value = $Itsharef->phoneext;
				$Worksheet->Range("F19")->Value = $Itsharef->department;

				$Worksheet->Range("F25")->Value = $Itsharef->foldername;

				$Worksheet->Range("H33")->Value = $datefrom;
				$Worksheet->Range("Q33")->Value = $dateto;

				// $Worksheet->Range("F36")->Value = $Itsharef->reason;
				$Worksheet->Range("F36")->Value = strip_tags($Itsharef->reason);

				$arrappr['role'] = array();
				$arrappr['name'] = array();
				$arrappr['date'] = array();

				array_push($arrappr['role'],'Originator');
				array_push($arrappr['name'],$fullname);
				array_push($arrappr['date'],date("d/m/Y",strtotime($Itsharef->createddate)));

				foreach ($Itsharefapproval as $data){
					if(($data->approver->approvaltype->id==29) || ($data->approver->employee_id==$Itsharef->depthead)){
						$deptheadname = $data->approver->employee->fullname;
						$deptheaddate = date("d/m/Y",strtotime($data->approvaldate));

						array_push($arrappr['role'],'Department head');
						array_push($arrappr['name'],$deptheadname);
						array_push($arrappr['date'],$deptheaddate);

					}
				
					if($data->approver->approvaltype->id==31) {
						$buheadname = $data->approver->employee->fullname;
						$buheaddate = date("d/m/Y",strtotime($data->approvaldate));

						array_push($arrappr['role'],'BU head');
						array_push($arrappr['name'],$buheadname);
						array_push($arrappr['date'],$buheaddate);

					}
					
					if($data->approver->approvaltype->id==34) {
						$itsitename = $data->approver->employee->fullname;
						$itsitedate = date("d/m/Y",strtotime($data->approvaldate));

						array_push($arrappr['role'],'IT Site');
						array_push($arrappr['name'],$itsitename);
						array_push($arrappr['date'],$itsitedate);

					}

					if($data->approver->approvaltype->id==32) {
						$itheadname = $data->approver->employee->fullname;
						$itheaddate = date("d/m/Y",strtotime($data->approvaldate));

						array_push($arrappr['role'],'IT Lead');
						array_push($arrappr['name'],$itheadname);
						array_push($arrappr['date'],$itheaddate);

					}
				}

					echo json_encode($arrappr);

				$picpath= SITE_PATH."/images/approved.png";
	
				$xlShiftDown=-4121;

				for ($b=49;$b<49+count($arrappr['name']);$b++) {
					// print_r($arrdephead[$b-49]);
					$Worksheet->Rows($b+1)->Copy();
					$Worksheet->Rows($b+1)->Insert($xlShiftDown);
					$Worksheet->Range("A".$b)->Value = $arrappr['role'][$b-49];
					$Worksheet->Range("I".$b)->Value = $arrappr['name'][$b-49];
					$Worksheet->Range("Q".$b)->Value = $arrappr['date'][$b-49];

					$pic=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
					$pic->Height  = 20;
					$pic->Top = $excel->Cells($b, 23)->Top ;
					$pic->Left = $excel->Cells($b, 23)->Left ;
				}


				for ($a=29;$a<29+count($Itsharefdetail);$a++){
					$Worksheet->Rows($a+1)->Copy();
					$Worksheet->Rows($a+1)->Insert($xlShiftDown);
					$Worksheet->Range("A".$a)->Value = $Itsharefdetail[$a-29]->foldername;
					$Worksheet->Range("J".$a)->Value = $Itsharefdetail[$a-29]->grantaccessto;
					$Worksheet->Range("Q".$a)->Value = ($Itsharefdetail[$a-29]->change == 0)?'x':'';
					$Worksheet->Range("R".$a)->Value = 'Read Only';
					$Worksheet->Range("U".$a)->Value = ($Itsharefdetail[$a-29]->change == 1)?'x':'';
					$Worksheet->Range("V".$a)->Value = 'Change';
					$Worksheet->Range("X".$a)->Value = $Itsharefdetail[$a-29]->requesttype;
					$Worksheet->Range("Y".$a)->Value = 'Type';


					// if($Itsharefdetail[$a-29]->requesttype == 1) {
					// 	$Worksheet->Range("X".$a)->Value = 'Create Share Folder';
					// }else if($Itsharefdetail[$a-29]->requesttype == 2){
					// 	$Worksheet->Range("X".$a)->Value = 'Create Share Folder';

					// }else if($Itsharefdetail[$a-29]->requesttype == 3){
					// 	$Worksheet->Range("X".$a)->Value = 'Create Share Folder';

					// }else if($Itsharefdetail[$a-29]->requesttype == 4){
					// 	$Worksheet->Range("X".$a)->Value = 'Create Share Folder';

					// }else if($Itsharefdetail[$a-29]->requesttype == 5){
					// 	$Worksheet->Range("X".$a)->Value = 'Create Share Folder';

					// }
				}
		

				//end condition


			$xlTypePDF = 0;
			$xlQualityStandard = 0;
			$fileName ='doc'.DS.'it'.DS.'pdf'.DS.$title.'_'.$Itsharef->employee->fullname.'_'.$Itsharef->employee->sapid.'_'.date("YmdHis").'.pdf';
			$fileName = str_replace("/","",$fileName);
			$path= SITE_PATH.'/doc'.DS.'it'.DS.'pdf'.DS.$title.'_'.$Itsharef->employee->fullname.'_'.$Itsharef->employee->sapid.'_'.date("YmdHis").'.pdf';
			if (file_exists($path)) {
			unlink($path);
			}
			$Worksheet->ExportAsFixedFormat($xlTypePDF, $path, $xlQualityStandard);
			$Itsharef->approveddoc=str_replace("\\","/",$fileName);
			$Itsharef->save();

			// $excel->Application->CutCopyMode(false);
			$excel->CutCopyMode = false;
			$Workbook->Close(false);
			unset($Worksheet);
			unset($Workbook);
			$excel->Workbooks->Close();
			$excel->Quit();
			unset($excel);

			return $fileName;

		} catch(com_exception $e) {  
			$err = new Errorlog();
			$err->errortype = "ITSHAREFPDFGenerator";
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

}