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
				case 'apiadvancehist':
					$this->advanceHistory();
					break;
				case 'apiadvancepdf':	
					$id = $this->get['id'];
					$this->generatePDFi($id);
					break;
				case 'apiadvancefile':
					$this->advanceAttachment();
					break;
				case 'uploadadvancefile':
					$this->uploadadvanceFile();
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
								case 'waiting':
									$Advance = Advance::find('all', array('conditions' => array("employee_id=? and RequestStatus!=3 and id<>?",$query['username'],$query['id']),'include' => array('employee')));
									foreach ($Advance as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Advance));
									break;
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
							// $Advance = Advance::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							//new
							$Advance = Advance::find('all', array('conditions' => array("createdby=?",$Employee->id),'include' => array('employee')));
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
						$join = "LEFT JOIN vwadvancereport ON tbl_advance.id = vwadvancereport.id";
						$select = "tbl_advance.*,vwadvancereport.apprstatuscode";
						// $Advance = Advance::find($id, array('include' => array('employee'=>array('company','department','designation'))));
						$Advance = Advance::find($id, array('joins'=>$join,'select'=>$select,'include' => array('employee'=>array('company','department','designation'))));

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
										$advance_form = $query['formtype'];
										$employee_id = $query['employee_id'];
										$id= $query['advance_id'];

										$Advance = Advance::find($id);
										
										$amountdetail = Advancedetail::find('all', array('conditions' => array("advance_id=?",$Advance->id)));

										foreach($amountdetail as $val) {
											$tdetailamount += $val->amount;
										}

										print_r($tdetailamount);

										$Employee = Employee::find('first', array('conditions' => array("id=?",$employee_id),"include"=>array("location","department","company")));


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

										$Advanceapprovalproc = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id='42' ",$id)));	
										foreach ($Advanceapprovalproc as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}
										

										if (($tdetailamount<5000000)){

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

											$proc = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=42",$id)));	
											foreach ($proc as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval Proc");
												$logger->SaveData();
											}
													
										} else if(($tdetailamount>=5000000 && $tdetailamount<10000000)){

											if(count($Advanceapprovaldepmd)==0){


														$ApproverDEPMD = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='39' and FIND_IN_SET(?, CompanyList) > 0 ",$Employee->companycode)));
														print_r($ApproverDEPMD);
														if(count($ApproverDEPMD)>0){
															$Advanceapproval = new Advanceapproval();
															$Advanceapproval->advance_id =$Advance->id;
															$Advanceapproval->approver_id = $ApproverDEPMD->id;
															$Advanceapproval->save();
															$logger = new Datalogger("Advanceapproval","add","Add initial Deputy Approval",json_encode($Advanceapproval->to_array()));
															$logger->SaveData();
														}

											}

											$md = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=40",$id)));	
											foreach ($md as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval MD");
												$logger->SaveData();
											}

											if($Advance->advanceform == 2 && $Advance->opscategory == 4) {
												if(count($Advanceapprovalproc)==0){
													$ApproverPROC = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='42' ")));
													print_r($ApproverPROC);
													if(count($ApproverPROC)>0){
														$Advanceapproval = new Advanceapproval();
														$Advanceapproval->advance_id =$Advance->id;
														$Advanceapproval->approver_id = $ApproverPROC->id;
														$Advanceapproval->save();
														$logger = new Datalogger("Advanceapproval","add","Add initial PROC",json_encode($Advanceapproval->to_array()));
														$logger->SaveData();
													}
												}
											}

										} else if(($tdetailamount>=10000000)){

											if(count($Advanceapprovaldepmd)==0){

												// if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220")){
													$ApproverDEPMD = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='39' and FIND_IN_SET(?, CompanyList) > 0 ",$Employee->companycode)));
													print_r($ApproverDEPMD);
													// $joins   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";					
													// 	$dx = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.employee_id=? and tbl_approver.approvaltype_id = '38' ",$id,$ApproverDEPMD->employee_id)));	
													// 	foreach ($dx as $result) {
													// 		//delete same type dept head approver
													// 		$result->delete();
													// 		$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
													// 	}
													if(count($ApproverDEPMD)>0){
														$Advanceapproval = new Advanceapproval();
														$Advanceapproval->advance_id =$Advance->id;
														$Advanceapproval->approver_id = $ApproverDEPMD->id;
														$Advanceapproval->save();
														$logger = new Datalogger("Advanceapproval","add","Add initial Deputy Approval",json_encode($Advanceapproval->to_array()));
														$logger->SaveData();
													}
												// }else{
												// 	$ApproverDEPMD = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='39'  and tbl_employee.company_id=? and not(tbl_employee.location_id='8')",$Employee->company_id)));
												// 	if(count($ApproverDEPMD)>0){
												// 		$Advanceapproval = new Advanceapproval();
												// 		$Advanceapproval->advance_id = $Advance->id;
												// 		$Advanceapproval->approver_id = $ApproverDEPMD->id;
												// 		$Advanceapproval->save();
												// 		$logger = new Datalogger("Advanceapproval","add","Add initial Deputy Approval",json_encode($Advanceapproval->to_array()));
												// 		$logger->SaveData();
												// 	}
												// }
											}

											if(count($Advanceapprovalmd)==0){

												// if($Employee->companycode == 'BCL') {
													// $Itimailapprovalmd = Itimailapproval::find('all',array('joins'=>$joins,'conditions' => array("itimail_id=? and tbl_approver.approvaltype_id='33' and tbl_employee.companycode='BCL' ",$id)));	
													$ApproverMD = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='40' and FIND_IN_SET(?, CompanyList) > 0 ",$Employee->companycode)));
												// }else {
												// 	$ApproverMD = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='40' and not(tbl_employee.companycode='BCL')")));

												// 	// $Itimailapprovalmd = Itimailapproval::find('all',array('joins'=>$joins,'conditions' => array("itimail_id=? and tbl_approver.approvaltype_id='33' and not(tbl_employee.companycode='BCL') ",$id)));	
												// }

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

											if($Advance->advanceform == 2 && $Advance->opscategory == 4) {
												if(count($Advanceapprovalproc)==0){
													$ApproverPROC = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='42' ")));
													print_r($ApproverPROC);
													if(count($ApproverPROC)>0){
														$Advanceapproval = new Advanceapproval();
														$Advanceapproval->advance_id =$Advance->id;
														$Advanceapproval->approver_id = $ApproverPROC->id;
														$Advanceapproval->save();
														$logger = new Datalogger("Advanceapproval","add","Add initial PROC",json_encode($Advanceapproval->to_array()));
														$logger->SaveData();
													}
												}
											}

										} 

									break;

									case 'appform':
										$advance_form = $query['formtype'];
										$employee_id = $query['employee_id'];
										$id= $query['advance_id'];

										$Advance = Advance::find($id);
										$Advance->advanceform = $advance_form;
										if($advance_form !== 2) {
											$Advance->opscategory = null;
										}
										$Advance->save();
										
										$amountdetail = Advancedetail::find('all', array('conditions' => array("advance_id=?",$Advance->id)));
										foreach($amountdetail as $val) {
											$tdetailamount += $val->amount;
										}

										$Employee = Employee::find('first', array('conditions' => array("id=?",$employee_id),"include"=>array("location","department","company")));

										// print_r($advance_form);
										$data['companycode']=$Employee->companycode;

										$joins   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id)";
										$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";	

										

										$Advanceapprovalhrd = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id='36' ",$id)));	
										foreach ($Advanceapprovalhrd as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}
										$Advanceapprovalproc = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id='42' ",$id)));	
										foreach ($Advanceapprovalproc as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}

										if($Advance->advanceform == 1) {
											
											$hrverifikator = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=44",$id)));	
											foreach ($hrverifikator as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval HR Verifikator");
												$logger->SaveData();
											}

											$kfssl = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=45",$id)));	
											foreach ($kfssl as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval KF SSL");
												$logger->SaveData();
											}

											$financecomit = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=41",$id)));	
											foreach ($financecomit as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval Finance Commite");
												$logger->SaveData();
											}

											$buhead = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=38",$id)));	
											foreach ($buhead as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval BU Head");
												$logger->SaveData();
											}

											$ApproverFC = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=41")));
											if(count($ApproverFC)>0){
												$Advanceapproval = new Advanceapproval();
												$Advanceapproval->advance_id = $Advance->id;
												$Advanceapproval->approver_id = $ApproverFC->id;
												$Advanceapproval->save();
												$logger = new Datalogger("Advanceapproval","add","Add initial Finance Commite Approval ",json_encode($Advanceapproval->to_array()));
												$logger->SaveData();
											}
											
											// if(($data['companycode']=="KPS" ||$data['companycode']=="KPSI" || $data['companycode']=="LDU") ){
											// 	$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and companylist = 'KPS,KPSI,LDU'")));
											// }else if(($data['companycode']=="AHL")){
											// 	$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and companylist='AHL'")));
											// }else if(($data['companycode']=="BCL")){
											// 	$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and companylist='BCL'")));
											// }else {
											// 	$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and companylist='IHM'")));
											// }
											$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and FIND_IN_SET(?, CompanyList) > 0 ",$Employee->companycode)));

											if(count($ApproverHRV)>0){
												$Advanceapproval = new Advanceapproval();
												$Advanceapproval->advance_id = $Advance->id;
												$Advanceapproval->approver_id = $ApproverHRV->id;
												$Advanceapproval->save();
												$logger = new Datalogger("Advanceapproval","add","Add initial HR Verifikator Approval ",json_encode($Advanceapproval->to_array()));
												$logger->SaveData();
											}

											$hrd = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=36",$id)));	
											foreach ($hrd as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval HRD");
												$logger->SaveData();
											}

											$ApproverBU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and FIND_IN_SET(?, CompanyList) > 0 ",$Employee->companycode)));
											if(count($ApproverBU)>0){
												$Advanceapproval = new Advanceapproval();
												$Advanceapproval->advance_id = $Advance->id;
												$Advanceapproval->approver_id = $ApproverBU->id;
												$Advanceapproval->save();
												$logger = new Datalogger("Advanceapproval","add","Add initial BU Head Approval ",json_encode($Advanceapproval->to_array()));
												$logger->SaveData();
											}

											// if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220") || ($Employee->department->sapcode=="13000090") || ($Employee->department->sapcode=="13000121") || ($Employee->company->sapcode=="NKF") || ($Employee->company->sapcode=="RND")){
												
												
												
													
											// }else{
												
											// 	$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=36 and tbl_employee.company_id=? and not(tbl_employee.location_id='8')",$Employee->company_id)));
											// 	if(count($Approver2)>0){
											// 		$Advanceapproval = new Advanceapproval();
											// 		$Advanceapproval->advance_id = $Advance->id;
											// 		$Advanceapproval->approver_id = $Approver2->id;
											// 		$Advanceapproval->save();
											// 	}
											// }
											

											// if($Employee->companycode == 'BCL') {
											// 	$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='36' and tbl_employee.companycode='BCL'")));
											// }else {
												// $Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='36' and not(tbl_employee.companycode='BCL')")));
												$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='36' and FIND_IN_SET(?, CompanyList) > 0 ",$Employee->companycode)));
											// }
											if(count($Approver2)>0){
												$Advanceapproval = new Advanceapproval();
												$Advanceapproval->advance_id = $Advance->id;
												$Advanceapproval->approver_id = $Approver2->id;
												$Advanceapproval->save();
												$logger = new Datalogger("Advanceapproval","add","Add initial HR Head Approval ",json_encode($Advanceapproval->to_array()));
												$logger->SaveData();
											}

											// $joins   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";					
											// $dx = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.employee_id=? and tbl_approver.approvaltype_id = '39' ",$id,$Approver2->employee_id)));	
											// print_r($ApproverHRV->employee_id);
											// foreach ($dx as $result) {
											// 	//delete same type dept head approver
											// 	$result->delete();
											// 	$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
											// }

											$proc = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=42",$id)));	
											foreach ($proc as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval PROC");
												$logger->SaveData();
											}
											

										} else if($Advance->advanceform == 2) {

											$hrv = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=44",$id)));	
											foreach ($hrv as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval HR Verifikator");
												$logger->SaveData();
											}

											$kfssl = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=45",$id)));	
											foreach ($kfssl as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval KF SSL");
												$logger->SaveData();
											}

											$financecomit = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=41",$id)));	
											foreach ($financecomit as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval Finance Commite");
												$logger->SaveData();
											}

											// if(($data['companycode']=="KPS" ||$data['companycode']=="KPSI" || $data['companycode']=="LDU") ){
											// 	$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and companylist = 'KPS,KPSI,LDU'")));
											// }else if(($data['companycode']=="AHL")){
											// 	$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and companylist='AHL'")));
											// }else if(($data['companycode']=="BCL")){
											// 	$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and companylist='BCL'")));
											// }else {
											// 	$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and companylist='IHM'")));
											// }
											// $ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and FIND_IN_SET(?, CompanyList) > 0 ",$Employee->companycode)));

											// if(count($ApproverHRV)>0){
											// 	$Advanceapproval = new Advanceapproval();
											// 	$Advanceapproval->advance_id = $Advance->id;
											// 	$Advanceapproval->approver_id = $ApproverHRV->id;
											// 	$Advanceapproval->save();
											// 	$logger = new Datalogger("Advanceapproval","add","Add initial HR Verifikator Approval ",json_encode($Advanceapproval->to_array()));
											// 	$logger->SaveData();
											// }

											$hrd = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=36",$id)));	
											foreach ($hrd as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval HRD");
												$logger->SaveData();
											}

											// if(($tdetailamount>=5000000)){

											// 		if(count($Advanceapprovalproc)==0){
											// 			$ApproverPROC = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='42' ")));
											// 			// print_r($ApproverPROC);
											// 			if(count($ApproverPROC)>0){
											// 				$Advanceapproval = new Advanceapproval();
											// 				$Advanceapproval->advance_id =$Advance->id;
											// 				$Advanceapproval->approver_id = $ApproverPROC->id;
											// 				$Advanceapproval->save();
											// 				$logger = new Datalogger("Advanceapproval","add","Add initial PROC",json_encode($Advanceapproval->to_array()));
											// 				$logger->SaveData();
											// 			}
											// 		}
	
											// }
										}


									break;

									case 'opscategory':
										$categorytype = $query['categorytype'];
										$employee_id = $query['employee_id'];
										$id= $query['advance_id'];

										$Advance = Advance::find($id);
										$Advance->opscategory = $categorytype;
										$Advance->save();
										
										$amountdetail = Advancedetail::find('all', array('conditions' => array("advance_id=?",$Advance->id)));
										foreach($amountdetail as $val) {
											$tdetailamount += $val->amount;
										}

										$Employee = Employee::find('first', array('conditions' => array("id=?",$employee_id),"include"=>array("location","department","company")));

										$data['companycode']=$Employee->companycode;

										$joins   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id)";
										$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";	
										

										$Advanceapprovalhrd = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id='36' ",$id)));	
										foreach ($Advanceapprovalhrd as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}
										$Advanceapprovalproc = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id='42' ",$id)));	
										foreach ($Advanceapprovalproc as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}

										if($Advance->opscategory == 1) {

											$financecomit = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=41",$id)));	
											foreach ($financecomit as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval Finance Commite");
												$logger->SaveData();
											}

											$hrv = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=44",$id)));	
											foreach ($hrv as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval HR Verifikator");
												$logger->SaveData();
											}

											$kfssl = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=45",$id)));	
											foreach ($kfssl as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval KF SSL");
												$logger->SaveData();
											}

											$buhead = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=38",$id)));	
											foreach ($buhead as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval BU Head");
												$logger->SaveData();
											}

											$proc = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=42",$id)));	
											foreach ($proc as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval PROC");
												$logger->SaveData();
											}


											$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and FIND_IN_SET(?, CompanyList) > 0 ",$Employee->companycode)));

											if(count($ApproverHRV)>0){
												$Advanceapproval = new Advanceapproval();
												$Advanceapproval->advance_id = $Advance->id;
												$Advanceapproval->approver_id = $ApproverHRV->id;
												$Advanceapproval->save();
												$logger = new Datalogger("Advanceapproval","add","Add initial HR Verifikator Approval ",json_encode($Advanceapproval->to_array()));
												$logger->SaveData();
											}

											$ApproverBU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and FIND_IN_SET(?, CompanyList) > 0 ",$Employee->companycode)));
											if(count($ApproverBU)>0){
												$Advanceapproval = new Advanceapproval();
												$Advanceapproval->advance_id = $Advance->id;
												$Advanceapproval->approver_id = $ApproverBU->id;
												$Advanceapproval->save();
												$logger = new Datalogger("Advanceapproval","add","Add initial BU Head Approval ",json_encode($Advanceapproval->to_array()));
												$logger->SaveData();
											}

											$ApproverFC = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=41")));
											if(count($ApproverFC)>0){
												$Advanceapproval = new Advanceapproval();
												$Advanceapproval->advance_id = $Advance->id;
												$Advanceapproval->approver_id = $ApproverFC->id;
												$Advanceapproval->save();
												$logger = new Datalogger("Advanceapproval","add","Add initial Finance Commite Approval ",json_encode($Advanceapproval->to_array()));
												$logger->SaveData();
											}

										} else if($Advance->opscategory == 2) {

											$hrverifikator = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=44",$id)));	
											foreach ($hrverifikator as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval HR Verifikator");
												$logger->SaveData();
											}

											$financecomit = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=41",$id)));	
											foreach ($financecomit as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval Finance Commite");
												$logger->SaveData();
											}
											$buhead = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=38",$id)));	
											foreach ($buhead as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval BU Head");
												$logger->SaveData();
											}

											$proc = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=42",$id)));	
											foreach ($proc as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval PROC");
												$logger->SaveData();
											}

											$ApproverFC = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=41")));
											if(count($ApproverFC)>0){
												$Advanceapproval = new Advanceapproval();
												$Advanceapproval->advance_id = $Advance->id;
												$Advanceapproval->approver_id = $ApproverFC->id;
												$Advanceapproval->save();
												$logger = new Datalogger("Advanceapproval","add","Add initial Finance Commite Approval ",json_encode($Advanceapproval->to_array()));
												$logger->SaveData();
											}

										} else if($Advance->opscategory == 3) {

											$hrverifikator = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=44",$id)));	
											foreach ($hrverifikator as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval HR Verifikator");
												$logger->SaveData();
											}

											$financecomit = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=41",$id)));	
											foreach ($financecomit as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval Finance Commite");
												$logger->SaveData();
											}
											$buhead = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=38",$id)));	
											foreach ($buhead as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval BU Head");
												$logger->SaveData();
											}

											$proc = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=42",$id)));	
											foreach ($proc as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval PROC");
												$logger->SaveData();
											}

										} else if($Advance->opscategory == 4) {

											$hrverifikator = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=44",$id)));	
											foreach ($hrverifikator as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval HR Verifikator");
												$logger->SaveData();
											}

											$kfssl = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=45",$id)));	
											foreach ($kfssl as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval KF SSL");
												$logger->SaveData();
											}

											$buhead = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=38",$id)));	
											foreach ($buhead as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval BU Head");
												$logger->SaveData();
											}
											$financecomit = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=41",$id)));	
											foreach ($financecomit as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval Finance Commite");
												$logger->SaveData();
											}

											$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and FIND_IN_SET(?, CompanyList) > 0 ",$Employee->companycode)));

											if(count($ApproverHRV)>0){
												$Advanceapproval = new Advanceapproval();
												$Advanceapproval->advance_id = $Advance->id;
												$Advanceapproval->approver_id = $ApproverHRV->id;
												$Advanceapproval->save();
												$logger = new Datalogger("Advanceapproval","add","Add initial HR Verifikator Approval ",json_encode($Advanceapproval->to_array()));
												$logger->SaveData();
											}

											$ApproverBU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and FIND_IN_SET(?, CompanyList) > 0 ",$Employee->companycode)));
											if(count($ApproverBU)>0){
												$Advanceapproval = new Advanceapproval();
												$Advanceapproval->advance_id = $Advance->id;
												$Advanceapproval->approver_id = $ApproverBU->id;
												$Advanceapproval->save();
												$logger = new Datalogger("Advanceapproval","add","Add initial BU Head Approval ",json_encode($Advanceapproval->to_array()));
												$logger->SaveData();
											}

											$ApproverFC = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=41")));
											if(count($ApproverFC)>0){
												$Advanceapproval = new Advanceapproval();
												$Advanceapproval->advance_id = $Advance->id;
												$Advanceapproval->approver_id = $ApproverFC->id;
												$Advanceapproval->save();
												$logger = new Datalogger("Advanceapproval","add","Add initial Finance Commite Approval ",json_encode($Advanceapproval->to_array()));
												$logger->SaveData();
											}

											if(($tdetailamount>=5000000)){

												if(count($Advanceapprovalproc)==0){
													$ApproverPROC = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='42' ")));
													if(count($ApproverPROC)>0){
														$Advanceapproval = new Advanceapproval();
														$Advanceapproval->advance_id =$Advance->id;
														$Advanceapproval->approver_id = $ApproverPROC->id;
														$Advanceapproval->save();
														$logger = new Datalogger("Advanceapproval","add","Add initial PROC",json_encode($Advanceapproval->to_array()));
														$logger->SaveData();
													}
												}
	
											}

										} else if($Advance->opscategory == 5) {
											$hrverifikator = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=44",$id)));	
											foreach ($hrverifikator as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval HR Verifikator");
												$logger->SaveData();
											}

											$kfssl = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=45",$id)));	
											foreach ($kfssl as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval KF SSL");
												$logger->SaveData();
											}

											$buhead = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=38",$id)));	
											foreach ($buhead as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval BU Head");
												$logger->SaveData();
											}
											$financecomit = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=41",$id)));	
											foreach ($financecomit as $result) {
												$result->delete();
												$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval Finance Commite");
												$logger->SaveData();
											}

											// $ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and FIND_IN_SET(?, CompanyList) > 0 ",$Employee->companycode)));

											// if(count($ApproverHRV)>0){
											// 	$Advanceapproval = new Advanceapproval();
											// 	$Advanceapproval->advance_id = $Advance->id;
											// 	$Advanceapproval->approver_id = $ApproverHRV->id;
											// 	$Advanceapproval->save();
											// 	$logger = new Datalogger("Advanceapproval","add","Add initial HR Verifikator Approval ",json_encode($Advanceapproval->to_array()));
											// 	$logger->SaveData();
											// }

											$ApproverBU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and FIND_IN_SET(?, CompanyList) > 0 ",$Employee->companycode)));
											if(count($ApproverBU)>0){
												$Advanceapproval = new Advanceapproval();
												$Advanceapproval->advance_id = $Advance->id;
												$Advanceapproval->approver_id = $ApproverBU->id;
												$Advanceapproval->save();
												$logger = new Datalogger("Advanceapproval","add","Add initial BU Head Approval ",json_encode($Advanceapproval->to_array()));
												$logger->SaveData();
											}

											$ApproverKFSSL = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='45' ")));
											if(count($ApproverKFSSL)>0){
												$Advanceapproval = new Advanceapproval();
												$Advanceapproval->advance_id = $Advance->id;
												$Advanceapproval->approver_id = $ApproverKFSSL->id;
												$Advanceapproval->save();
												$logger = new Datalogger("Advanceapproval","add","Add initial KF SSL Approval ",json_encode($Advanceapproval->to_array()));
												$logger->SaveData();
											}

											$ApproverFC = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=41")));
											if(count($ApproverFC)>0){
												$Advanceapproval = new Advanceapproval();
												$Advanceapproval->advance_id = $Advance->id;
												$Advanceapproval->approver_id = $ApproverFC->id;
												$Advanceapproval->save();
												$logger = new Datalogger("Advanceapproval","add","Add initial Finance Commite Approval ",json_encode($Advanceapproval->to_array()));
												$logger->SaveData();
											}

										}


									break;

									case 'chemp': //new

										$advance_form = $query['formtype'];
										$employee_id = $query['employee_id'];
										$id= $query['advance_id'];
										$mode= $query['mode'];

										$Employee = Employee::find('first', array('conditions' => array("id=?",$employee_id),"include"=>array("location","department","company")));


										$codenew = Advpayment::find('first',array('select' => "CONCAT('Advance/','".$Employee->companycode."','/',YEAR(CURDATE()),'/',LPAD(MONTH(CURDATE()), 2, '0'),'/',LPAD(CASE when max(substring(advanceno,-4,4)) is null then 1 else max(substring(advanceno,-4,4))+1 end,4,'0')) as advanceno","conditions"=>array("substring(advanceno,9,".strlen($Employee->companycode).")=? and not(id = ?) and substring(advanceno,".(strlen($Employee->companycode)+10).",4)=YEAR(CURDATE()) ",$Employee->companycode,$query['advance_id'])));
										$Advance = Advance::find($id);
										if($employee_id) {
											$Advance->employee_id = $employee_id;
										}
										$Advance->advanceno =$codenew->advanceno;
										// $Advance->companycode = $Employee->companycode;
										if($mode == 'edit') {

											$Advance->save();
										}


										$data=array("advanceno"=>$codenew->advanceno);

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
							$data['createdby']=$Employee->id; //new
							$data['RequestStatus']=0;
							$data['isUsed']=0;
							try{
								$code = Advance::find('first',array('select' => "CONCAT('Advance/','".$Employee->companycode."','/',YEAR(CURDATE()),'/',LPAD(MONTH(CURDATE()), 2, '0'),'/',LPAD(CASE when max(substring(advanceno,-4,4)) is null then 1 else max(substring(advanceno,-4,4))+1 end,4,'0')) as advanceno","conditions"=>array("substring(advanceno,9,".strlen($Employee->companycode).")=? and substring(advanceno,".(strlen($Employee->companycode)+10).",4)=YEAR(CURDATE())",$Employee->companycode)));

								$data['advanceno']=$code->advanceno;

								$Advance = Advance::create($data);
								$data['companycode']=$Employee->companycode;
								$data=$Advance->to_array();
								
								$joins   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";

								$ApproverFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=41")));
								if(count($ApproverFC)>0){
									$Advanceapproval = new Advanceapproval();
									$Advanceapproval->advance_id = $Advance->id;
									$Advanceapproval->approver_id = $ApproverFC->id;
									$Advanceapproval->save();
								}

								// $companyBU=( ($Employee->companycode=='KPA') || ($Employee->companycode=='AHL') )?"KPSI":$Employee->companycode;
								// if (($Employee->company->sapcode=='RND') || ($Employee->company->sapcode=='NKF')){
									// }else{
										// 	$ApproverBU = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and tbl_employee.companycode=? and not(tbl_employee.id=?)",$companyBU,$Employee->id)));
										// }
								$ApproverBU = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and FIND_IN_SET(?, CompanyList) > 0 ",$Employee->companycode)));
								if(count($ApproverBU)>0){
									$Advanceapproval = new Advanceapproval();
									$Advanceapproval->advance_id = $Advance->id;
									$Advanceapproval->approver_id = $ApproverBU->id;
									$Advanceapproval->save();
									$logger = new Datalogger("Advanceapproval","add","Add initial BU Head Approval ",json_encode($Advanceapproval->to_array()));
									$logger->SaveData();
								}

								// $companyFC=(($data['companycode']=='BCL') || ($data['companycode']=='KPA'))?"KPSI":((($data['companycode']=='KPSI'))?"LDU":$Employee->companycode);
								$ApproverBUFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37' and CompanyList like '%".$Employee->companycode."%'")));
								if(count($ApproverBUFC)>0){
									$Advanceapproval = new Advanceapproval();
									$Advanceapproval->advance_id = $Advance->id;
									$Advanceapproval->approver_id = $ApproverBUFC->id;
									$Advanceapproval->save();
									$logger = new Datalogger("Advanceapproval","add","Add initial BU FC Approval",json_encode($Advanceapproval->to_array()));
									$logger->SaveData();
								}

								// if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220")){
								// 	$ApproverBU = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and tbl_employee.location_id='8'")));
								// 	if(count($ApproverBU)>0){
								// 		$Advanceapproval = new Advanceapproval();
								// 		$Advanceapproval->advance_id =$Advance->id;
								// 		$Advanceapproval->approver_id = $ApproverBU->id;
								// 		$Advanceapproval->save();
								// 		$logger = new Datalogger("Advanceapproval","add","Add initial BU Approval",json_encode($Advanceapproval->to_array()));
								// 		$logger->SaveData();
								// 	}
									
								// 	$ApproverBUFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37' and tbl_employee.location_id='8'")));
								// 	if(count($ApproverBUFC)>0){
								// 		$Advanceapproval = new Advanceapproval();
								// 		$Advanceapproval->advance_id =$Advance->id;
								// 		$Advanceapproval->approver_id = $ApproverBUFC->id;
								// 		$Advanceapproval->save();
								// 		$logger = new Datalogger("Advanceapproval","add","Add initial BUFC Approval",json_encode($Advanceapproval->to_array()));
								// 		$logger->SaveData();
								// 	}

								// }else{
								// 	$ApproverBU = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38'  and tbl_employee.company_id=? and not(tbl_employee.location_id='8')",$Employee->company_id)));
								// 	if(count($ApproverBU)>0){
								// 		$Advanceapproval = new Advanceapproval();
								// 		$Advanceapproval->advance_id = $Advance->id;
								// 		$Advanceapproval->approver_id = $ApproverBU->id;
								// 		$Advanceapproval->save();
								// 		$logger = new Datalogger("Advanceapproval","add","Add initial BU Approval",json_encode($Advanceapproval->to_array()));
								// 		$logger->SaveData();
								// 	}

								// 	$ApproverBUFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37'  and tbl_employee.company_id=? and not(tbl_employee.location_id='8')",$Employee->company_id)));
								// 	if(count($ApproverBUFC)>0){
								// 		$Advanceapproval = new Advanceapproval();
								// 		$Advanceapproval->advance_id = $Advance->id;
								// 		$Advanceapproval->approver_id = $ApproverBUFC->id;
								// 		$Advanceapproval->save();
								// 		$logger = new Datalogger("Advanceapproval","add","Add initial BUFC Approval",json_encode($Advanceapproval->to_array()));
								// 		$logger->SaveData();
								// 	}

								// }

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
								$approval = Advanceattachment::find("all",array('conditions' => array("advance_id=?",$id)));
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
						unset($data['apprstatuscode']);
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
									$approver->sequence=0;
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
							$title = 'Advance';
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
							if($Advance->advanceform == 1) {
								$form = "HR Related";
							} else if($Advance->advanceform == 2){
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
							<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.83.18/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.83.18/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
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

							// if($Advance->advanceform == 2) {

							// 	$dx = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=36",$id)));	
							// 	foreach ($dx as $result) {
							// 		$result->delete();
							// 		$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete Approval Advance");
							// 		$logger->SaveData();
							// 	}

							// 	if($val_tamount >= 5000000) {

							// 		$ApproverProc = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='42' and tbl_employee.location_id='8'")));
							// 		if(count($ApproverProc)>0){
							// 			$Advanceapproval = new Advanceapproval();
							// 			$Advanceapproval->advance_id =$Advance->id;
							// 			$Advanceapproval->approver_id = $ApproverProc->id;
							// 			$Advanceapproval->save();
							// 			$logger = new Datalogger("Advanceapproval","add","Add initial Proc Approval",json_encode($Advanceapproval->to_array()));
							// 			$logger->SaveData();
							// 		}
									
							// 	}


								
							// }

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
							$dx = Advanceapproval::find('first', array('joins'=>$join,'conditions' => array("advance_id=? and tbl_approver.employee_id = ? and ApprovalStatus = 0",$query['advance_id'],$Employee->id),'order'=>'tbl_approver.sequence','include' => array('approver'=>array('employee'))));
							// print_r($dx);
							$Advance = Advance::find($query['advance_id']);
							$amountdetail = Advancedetail::find('all', array('conditions' => array("advance_id=?",$Advance->id)));
							$tdetailamount = 0;
							foreach($amountdetail as $val) {
								$tdetailamount += $val->amount;
							}
							// print_r($tdetailamount);
							// print_r($dx->approver->approvaltype_id);
							if($dx->approver->isfinal==1){
								$data=array("jml"=>1);
							} else if(($tdetailamount<5000000) && $Advance->advanceform ==1 && $dx->approver->approvaltype_id == 41) {
								$data=array("jml"=>1);
							} else if(($tdetailamount>=5000000 && $tdetailamount<10000000) && $Advance->advanceform ==1 && $dx->approver->approvaltype_id == 39) {
								$data=array("jml"=>1);
								// start advanceform 2
							} else if(($tdetailamount<5000000) && $Advance->advanceform ==2 && $Advance->opscategory==1 && $dx->approver->approvaltype_id == 41) {
								$data=array("jml"=>1);
							} else if(($tdetailamount<5000000) && $Advance->advanceform ==2 && $Advance->opscategory==2 && $dx->approver->approvaltype_id == 41) {
								$data=array("jml"=>1);
							} else if(($tdetailamount<5000000) && $Advance->advanceform ==2 && $Advance->opscategory==3 && $dx->approver->approvaltype_id == 37) {
								$data=array("jml"=>1);
							} else if(($tdetailamount<5000000) && $Advance->advanceform ==2 && $Advance->opscategory==4 && $dx->approver->approvaltype_id == 41) {
								$data=array("jml"=>1);
							} else if(($tdetailamount<5000000) && $Advance->advanceform ==2 && $Advance->opscategory==5 && $dx->approver->approvaltype_id == 41) {
								$data=array("jml"=>1);
							} else if(($tdetailamount>=5000000 && $tdetailamount<10000000) && $Advance->advanceform ==2 && $dx->approver->approvaltype_id == 39) {
								$data=array("jml"=>1);
							} else{
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
							
							$Advance = Advance::find('all', array('conditions' => array("RequestStatus >0"),'include' => array('employee')));
							// $Advance = Advance::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
							foreach ($Advance as $result) {
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";					
								$Advanceapproval = Advanceapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and advance_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								if($Advanceapproval->approver->employee_id==$emp_id){
									$request[]=$result->id;
								}
								$Advanceapproval = Advanceapproval::find('first',array('joins'=>$joinx,'conditions' => array("advance_id=? and tbl_approver.employee_id = ? and approvalstatus!=0",$result->id,$emp_id),'include' => array('approver'=>array('employee'))));							
								if(count($Advanceapproval)>0 && ($result->requeststatus==3 || $result->requeststatus==4)){
									$request[]=$result->id;
								}
							}
							$Advance = Advance::find('all', array('conditions' => array("id in (?)",$request),'order'=>"tbl_advance.requeststatus",'include' => array('employee')));
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
							$sel = 'tbl_advance.*,v.personholding ';
							$Advance = Advance::find('all',array('joins'=>$join,'select'=>$sel,'include' => array('employee')));
							
							// if($Employee->location->sapcode=='0200' || $this->currentUser->isadmin){
								$Advance = Advance::find('all',array('joins'=>$join,'conditions' => array('tbl_advance.CreatedDate between ? and ?',$query['startDate'],$query['endDate'] ),'select'=>$sel,'include' => array('employee'=>array('company','department'))));
							// }else{
							// 	$Advance = Advance::find('all',array('joins'=>$join,'select'=>$sel,'conditions' => array('tbl_advance.RequestStatus=3 or tbl_advance.RequestStatus=5 and tbl_employee.company_id=?',$Employee->company_id ),'include' => array('employee'=>array('company','department'))));
							// }
							
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
							$appstatus = $data['approvalstatus'];
							unset($data['id']);
							unset($data['depthead']);
							unset($data['fullname']);
							unset($data['department']);
							unset($data['approveddoc']);
							// unset($data['advanceform']);

							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";
							if (isset($data['mode'])){
								$Advanceapproval = Advanceapproval::find('first', array('joins'=>$join,'conditions' => array("advance_id=? and tbl_approver.employee_id=? and ApprovalStatus = 0 ",$doid,$Employee->id),'order' => 'tbl_approver.sequence','include' => array('approver'=>array('employee','approvaltype'))));
								//start for update all duplicate approver
								// $Advanceapprovalx = Advanceapproval::find('first', array('joins'=>$join,'conditions' => array("advance_id=? and tbl_approver.employee_id=?",$doid,$Employee->id),'include' => array('approver'=>array('employee','approvaltype'))));
								//end for update all duplicate approver
								unset($data['mode']);
							}else{
								$Advanceapproval = Advanceapproval::find($this->post['id'],array('include' => array('approver'=>array('employee','approvaltype'))));
							}

							// $Advance = Advance::find($doid);
							// foreach($data as $key=>$val) {
							// 	if(($key !== 'approvalstatus') && ($key !== 'approvaldate') && ($key !== 'remarks') ) {
							// 			$value=(($val===0) || ($val==='0') || ($val==='false'))?false:((($val===1) || ($val==='1') || ($val==='true'))?true:$val);
							// 		$Advance->$key=$value;
							// 	}
							// }
							
							// $Advance->save();

							unset($data['advanceform']);
							unset($data['createdby']);
							unset($data['opscategory']);
							unset($data['lessadvance']);
							unset($data['advanceno']);
							
							// foreach ($Advanceapprovalx as $approval){
								$olddata = $Advanceapproval->to_array();
								foreach($data as $key=>$val){
									$val=($val=='false')?false:(($val=='true')?true:$val);
									$Advanceapproval->$key=$val;
								}
								
								$Advanceapproval->save();
								$logger = new Datalogger("Advanceapproval","update",json_encode($olddata),json_encode($data));
								$logger->SaveData();
							// }

						if (isset($mode) && ($mode=='approve')){
								$Advance = Advance::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));
								$joincrb   = "LEFT JOIN tbl_employee ON (tbl_advance.createdby = tbl_employee.id) ";
								$Advancecrb = Advance::find($doid,array('select'=>"tbl_advance.*,tbl_employee.loginname",'joins'=>$joincrb));
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";					
								$nAdvanceapproval = Advanceapproval::find('first',array('joins'=>$joinx,'conditions' => array("advance_id=? and ApprovalStatus=0",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								$username = $nAdvanceapproval->approver->employee->loginname;
								print_r($username);
								$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));

								$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Advance->employee->loginname)));
								$usrcrb = Addressbook::find('first',array('conditions'=>array("username=?",$Advancecrb->loginname)));
								$email=$usr->email;
								$emailcrb=$usrcrb->email;
								
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
										if($Advance->createdby == $Advance->employee_id) {
											$emto=$email;
										} else {
											$emto=$emailcrb;
										}
										$emname=$Advance->employee->fullname;
										$this->mail->Subject = "Online Approval System -> Need Rework";
										$red = 'Your Advance request require some rework : <br>Remarks from Approver : <font color="red">'.$data['remarks']."</font>";
										$Advancehistory->actiontype = 3;
										break;
									case '2':
										if ($Advanceapproval->approver->isfinal == 1){
											$Advance->requeststatus = 3;
											if($Advance->createdby == $Advance->employee_id) {
												$emto=$email;
											} else {
												$emto=$emailcrb;
											}
											$emname=$Advance->employee->fullname;
											$this->mail->Subject = "Online Approval System -> Approval Completed";
											$red = 'Your Advance request has been approved';
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
											$amountdetail = Advancedetail::find('all', array('conditions' => array("advance_id=?",$Advance->id)));
											$tdetailamount = 0;
											foreach($amountdetail as $val) {
												$tdetailamount += $val->amount;
											}
											if($Advance->advanceform == 1) {

												if(($tdetailamount<5000000) && $Advanceapproval->approver->approvaltype_id == 41) {
													$Advance->requeststatus = 3;
													if($Advance->createdby == $Advance->employee_id) {
														$emto=$email;
													} else {
														$emto=$emailcrb;
													}
													$emname=$Advance->employee->fullname;

													$this->mail->Subject = "Online Approval System -> Approval Completed";
													$red = 'Your Advance request has been approved';
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
												} else if(($tdetailamount>=5000000 && $tdetailamount<10000000) && $Advanceapproval->approver->approvaltype_id == 39) {
													$Advance->requeststatus = 3;
													if($Advance->createdby == $Advance->employee_id) {
														$emto=$email;
													} else {
														$emto=$emailcrb;
													}
													$emname=$Advance->employee->fullname;
													$this->mail->Subject = "Online Approval System -> Approval Completed";
													$red = 'Your Advance request has been approved';
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
												} else {
													$Advance->requeststatus = 1;
													$emto=$adb->email;$emname=$adb->fullname;
													$this->mail->Subject = "Online Approval System -> New Advance Submission";
													$red = 'New Advance request awaiting for your approval:';
												}

											} else if($Advance->advanceform == 2) {

												if(($tdetailamount<5000000) && $Advance->opscategory==1 && $Advanceapproval->approver->approvaltype_id == 41) {
													$Advance->requeststatus = 3;
													if($Advance->createdby == $Advance->employee_id) {
														$emto=$email;
													} else {
														$emto=$emailcrb;
													};
													$emname=$Advance->employee->fullname;
													$this->mail->Subject = "Online Approval System -> Approval Completed";
													$red = 'Your Advance request has been approved';
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
												} else if(($tdetailamount<5000000) && $Advance->opscategory==2 && $Advanceapproval->approver->approvaltype_id == 41) {
													$Advance->requeststatus = 3;
													if($Advance->createdby == $Advance->employee_id) {
														$emto=$email;
													} else {
														$emto=$emailcrb;
													}
													$emname=$Advance->employee->fullname;
													$this->mail->Subject = "Online Approval System -> Approval Completed";
													$red = 'Your Advance request has been approved';
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
												} else if(($tdetailamount<5000000) && $Advance->opscategory==3 && $Advanceapproval->approver->approvaltype_id == 37) {
													$Advance->requeststatus = 3;
													if($Advance->createdby == $Advance->employee_id) {
														$emto=$email;
													} else {
														$emto=$emailcrb;
													}
													$emname=$Advance->employee->fullname;
													$this->mail->Subject = "Online Approval System -> Approval Completed";
													$red = 'Your Advance request has been approved';
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
												} else if(($tdetailamount<5000000) && $Advance->opscategory==4 && $Advanceapproval->approver->approvaltype_id == 41) {
													$Advance->requeststatus = 3;
													if($Advance->createdby == $Advance->employee_id) {
														$emto=$email;
													} else {
														$emto=$emailcrb;
													}
													$emname=$Advance->employee->fullname;
													$this->mail->Subject = "Online Approval System -> Approval Completed";
													$red = 'Your Advance request has been approved';
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
												} else if(($tdetailamount<5000000) && $Advance->opscategory==5 && $Advanceapproval->approver->approvaltype_id == 41) {
													$Advance->requeststatus = 3;
													if($Advance->createdby == $Advance->employee_id) {
														$emto=$email;
													} else {
														$emto=$emailcrb;
													}
													$emname=$Advance->employee->fullname;
													$this->mail->Subject = "Online Approval System -> Approval Completed";
													$red = 'Your Advance request has been approved';
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
												} else if(($tdetailamount>=5000000 && $tdetailamount<10000000) && $Advanceapproval->approver->approvaltype_id == 39) {
													$Advance->requeststatus = 3;
													if($Advance->createdby == $Advance->employee_id) {
														$emto=$email;
													} else {
														$emto=$emailcrb;
													}
													$emname=$Advance->employee->fullname;
													$this->mail->Subject = "Online Approval System -> Approval Completed";
													$red = 'Your Advance request has been approved';
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
												} else {
													$Advance->requeststatus = 1;
													$emto=$adb->email;$emname=$adb->fullname;
													$this->mail->Subject = "Online Approval System -> New Advance Submission";
													$red = 'New Advance request awaiting for your approval:';
												}

											}

										}
										$Advancehistory->actiontype = 4;							
										break;
									case '3':
										$Advance->requeststatus = 4;
										if($Advance->createdby == $Advance->employee_id) {
											$emto=$email;
										} else {
											$emto=$emailcrb;
										}
										$emname=$Advance->employee->fullname;
										$Advancehistory->actiontype = 5;
										$this->mail->Subject = "Online Approval System -> Request Rejected";
										$red = 'Your Advance request has been rejected <br>Remarks from Approver : <font color="red">'.$data['remarks']."</font>";
										break;
									default:
										break;
								}
								$Advance->isused = 0;
								$Advance->save();
								$Advancehistory->save();
								echo "email to :".$emto." ->".$emname;
								$this->mail->addAddress($emto, $emname);
								
								
								$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$emname.',</span></p>
								<p class=MsoNormal><span style="color:#1F497D">'.$red.'</span></p>
								<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
								<table border=1 cellspacing=0 cellpadding=3 width=683>
								<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advance->employee->fullname.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advance->employee->sapid.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advance->employee->designation->designationname.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advance->employee->company->companyname.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advance->employee->location->location.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
								</table>';
						if($Advance->advanceform == 1) {
							$form = "HR Related";
						} else if($Advance->advanceform == 2){
							$form = "Ops Related";
						}
						$Advancedetail = Advancedetail::find('all',array('conditions'=>array("advance_id=?",$doid),'include'=>array('advance'=>array('employee'=>array('company','department','designation','grade','location')))));	

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
						<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.83.18/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.83.18/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
						
								
								$this->mail->msgHTML($this->mailbody);
								if ($complete){
									$filePath= $this->generatePDFi($doid);
									$Mailrecipient = Mailrecipient::find('all',array('conditions'=>array("module='Advance' and company_list like ? and isActive='1' ","%".$Advance->employee->companycode."%")));
									foreach ($Mailrecipient as $data){
										$this->mail->AddCC($data->email);
									}
									$this->mail->addAttachment($filePath);
								}
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
							}
							echo json_encode($Advanceapproval);
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
							// $join = "LEFT JOIN vwadvancereport ON tbl_advancedetail.advance_id = vwadvancereport.id";
							// $select = "tbl_advancedetail.*,vwadvancereport.apprstatuscode";
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
		$Advance = Advance::find($id);
		$Advancedetail = Advancedetail::find('all',array('conditions'=>array("advance_id=?",$id),'include'=>array('advance'=>array('employee'=>array('company','department','designation','grade','location')))));	
		
		$superiorId=$Advance->depthead;
		$Superior = Employee::find($superiorId);
		$compx = Company::find('first',array('conditions'=>array("companycode=?",$Advance->employee->companycode)));
		$supAdb = Addressbook::find('first',array('conditions'=>array("username=?",$Superior->loginname)));
		$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Advance->employee->loginname)));
		$email=$usr->email;
		$fullname=$Advance->employee->fullname;
		$department = $Advance->employee->department->departmentname;

		// $duedate = date("d/m/Y",strtotime($Advance->duedate));
		$duedate = $Advance->duedate;
		// $expecteddate = date("d/m/Y",strtotime($Advance->expecteddate));
		$expecteddate = $Advance->expecteddate;

		$joinx   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";					
		$Advanceapproval = Advanceapproval::find('all',array('joins'=>$joinx,'conditions' => array("advance_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee','approvaltype'))));							
		
		//condition
			
			
		//end condition

		try {
			$excel = new COM("Excel.Application") or die ("ERROR: Unable to instantaniate COM!\r\n");
			$excel->Visible = false;

			
			
			if($Advance->advanceform == 1) {
				$title = 'advance_hr';
				$file= SITE_PATH."/doc/hr/advancehr.xlsx";
			} else {
				$title = 'advance_ops';
				$file= SITE_PATH."/doc/hr/advanceops.xlsx";
			}

				
				$Workbook = $excel->Workbooks->Open($file) or die("ERROR: Unable to open " . $file . "!\r\n");
				$Worksheet = $Workbook->Worksheets(1);
				$Worksheet->Activate;

				if($Advance->employee->companycode == 'NKF' || $Advance->employee->companycode == 'RND') {
					$Worksheet->Range("A1")->Value = 'PT. ITCI Hutani Manunggal';
				} else {
					$Worksheet->Range("A1")->Value = $compx->companyname;
				}

				$Worksheet->Range("N6")->Value = date("d/m/Y",strtotime($Advance->createddate));
				$Worksheet->Range("N8")->Value = date("d/m/Y",strtotime($duedate));
				$Worksheet->Range("N7")->Value = date("d/m/Y",strtotime($expecteddate));

			if($Advance->advanceform == 1) {
				$Worksheet->Range("F6")->Value = $fullname;
				$Worksheet->Range("F7")->Value = $department;
				$Worksheet->Range("F8")->Value = $Advance->employee->sapid;

				$Worksheet->Range("F10")->Value = $Advance->beneficiary;
				$Worksheet->Range("F11")->Value = $Advance->accountname;
				$Worksheet->Range("F12")->Value = $Advance->bank;
				$Worksheet->Range("F13")->Value = $Advance->accountnumber;
			} else {
				$Worksheet->Range("E6")->Value = $fullname;
				$Worksheet->Range("E7")->Value = $department;
				$Worksheet->Range("E8")->Value = $Advance->employee->sapid;

				$Worksheet->Range("E10")->Value = $Advance->beneficiary;
				$Worksheet->Range("E11")->Value = $Advance->accountname;
				$Worksheet->Range("E12")->Value = $Advance->bank;
				$Worksheet->Range("E13")->Value = $Advance->accountnumber;
			}




				foreach ($Advanceapproval as $data){
					if(($data->approver->approvaltype->id==35) || ($data->approver->employee_id==$Advance->depthead)){
						$deptheadname = $data->approver->employee->fullname;
						$deptheaddate = 'Date : '.date("d/m/Y",strtotime($data->approvaldate));
					}

					if($data->approver->approvaltype->id==44) {
						$hrdverifname = $data->approver->employee->fullname;
						$hrdverifdate = 'Date : '.date("d/m/Y",strtotime($data->approvaldate));
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
				
				$Worksheet->Range("A32")->Value = $fullname;
				$Worksheet->Range("A33")->Value = 'Date : '.date("d/m/Y",strtotime($Advance->createddate));

				$pic=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
				$pic->Height  = 20;
				$pic->Top = $excel->Cells(29, 1)->Top ;
				$pic->Left = $excel->Cells(29, 1)->Left ;

				if($Advance->advanceform == 1) {
					if(!empty($deptheadname)) {
						$Worksheet->Range("F32")->Value = $deptheadname;
						$Worksheet->Range("F33")->Value = $deptheaddate;
						$pic1=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic1->Height  = 20;
						$pic1->Top = $excel->Cells(29, 6)->Top ;
						$pic1->Left = $excel->Cells(29, 6)->Left ;
					}
	
					if(!empty($hrdheadname)) {
						$Worksheet->Range("I32")->Value = $hrdheadname;
						$Worksheet->Range("I33")->Value = $hrdheaddate;
						$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic2->Height  = 20;
						$pic2->Top = $excel->Cells(29, 9)->Top ;
						$pic2->Left = $excel->Cells(29, 9)->Left ;
					} else {
						$Worksheet->Range("I32")->Value = $hrdverifname;
						$Worksheet->Range("I33")->Value = $hrdverifdate;
						$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic2->Height  = 20;
						$pic2->Top = $excel->Cells(29, 9)->Top ;
						$pic2->Left = $excel->Cells(29, 9)->Left ;
					}
	
					if(!empty($bufcname)) {
						$Worksheet->Range("L32")->Value = $bufcname;
						$Worksheet->Range("L33")->Value = $bufcdate;
						$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic2->Height  = 20;
						$pic2->Top = $excel->Cells(29, 12)->Top ;
						$pic2->Left = $excel->Cells(29, 12)->Left ;
					}
	
					if(!empty($buheadname)) {
						$Worksheet->Range("A45")->Value = $buheadname;
						$Worksheet->Range("A46")->Value = $buheaddate;
						$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic2->Height  = 20;
						$pic2->Top = $excel->Cells(42, 1)->Top ;
						$pic2->Left = $excel->Cells(42, 1)->Left ;
					}
	
					if(!empty($financename)) {
						$Worksheet->Range("F45")->Value = $financename;
						$Worksheet->Range("F46")->Value = $financedate;
						$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic2->Height  = 20;
						$pic2->Top = $excel->Cells(42, 6)->Top ;
						$pic2->Left = $excel->Cells(42, 6)->Left ;
					}
	
					if(!empty($depmdname)) {
						$Worksheet->Range("I45")->Value = $depmdname;
						$Worksheet->Range("I46")->Value = $depmddate;
						$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic2->Height  = 20;
						$pic2->Top = $excel->Cells(42, 9)->Top ;
						$pic2->Left = $excel->Cells(42, 9)->Left ;
					}
	
					if(!empty($mdname)) {
						$Worksheet->Range("L45")->Value = $mdname;
						$Worksheet->Range("L46")->Value = $mddate;
						$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic2->Height  = 20;
						$pic2->Top = $excel->Cells(42, 12)->Top ;
						$pic2->Left = $excel->Cells(42, 12)->Left ;
					}
				} else {
					if(!empty($deptheadname)) {
						$Worksheet->Range("I32")->Value = $deptheadname;
						$Worksheet->Range("I33")->Value = $deptheaddate;
						$pic1=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic1->Height  = 20;
						$pic1->Top = $excel->Cells(29, 9)->Top ;
						$pic1->Left = $excel->Cells(29, 9)->Left ;
					}

					if(!empty($procname)) {
						$Worksheet->Range("L32")->Value = $procname;
						$Worksheet->Range("L33")->Value = $procdate;
						$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic2->Height  = 20;
						$pic2->Top = $excel->Cells(29, 12)->Top ;
						$pic2->Left = $excel->Cells(29, 12)->Left ;
					}

					if(!empty($kfsslname)) {
						$Worksheet->Range("L26")->Value = 'KF SSL';
						$Worksheet->Range("L32")->Value = $kfsslname;
						$Worksheet->Range("L33")->Value = $kfssldate;
						$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic2->Height  = 20;
						$pic2->Top = $excel->Cells(29, 12)->Top ;
						$pic2->Left = $excel->Cells(29, 12)->Left ;
					}
	
					if(!empty($bufcname)) {
						$Worksheet->Range("A45")->Value = $bufcname;
						$Worksheet->Range("A46")->Value = $bufcdate;
						$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic2->Height  = 20;
						$pic2->Top = $excel->Cells(42, 1)->Top ;
						$pic2->Left = $excel->Cells(42, 1)->Left ;
					}
	
					if(!empty($buheadname)) {
						$Worksheet->Range("E45")->Value = $buheadname;
						$Worksheet->Range("E46")->Value = $buheaddate;
						$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic2->Height  = 20;
						$pic2->Top = $excel->Cells(42, 5)->Top ;
						$pic2->Left = $excel->Cells(42, 5)->Left ;
					}
	
					if(!empty($financename)) {
						$Worksheet->Range("H45")->Value = $financename;
						$Worksheet->Range("H46")->Value = $financedate;
						$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic2->Height  = 20;
						$pic2->Top = $excel->Cells(42, 8)->Top ;
						$pic2->Left = $excel->Cells(42, 8)->Left ;
					}

					if(!empty($depmdname)) {
						$Worksheet->Range("K45")->Value = $depmdname;
						$Worksheet->Range("K46")->Value = $depmddate;
						$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic2->Height  = 20;
						$pic2->Top = $excel->Cells(42, 11)->Top ;
						$pic2->Left = $excel->Cells(42, 11)->Left ;
					}
	
					if(!empty($mdname)) {
						$Worksheet->Range("N45")->Value = $mdname;
						$Worksheet->Range("N46")->Value = $mddate;
						$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic2->Height  = 20;
						$pic2->Top = $excel->Cells(42, 14)->Top ;
						$pic2->Left = $excel->Cells(42, 14)->Left ;
					}
				}

				

				foreach ($Advancedetail as $data){
					$val_tamount += $data->amount;
				}

				$Worksheet->Range("L18")->Value = $val_tamount;

	
				$xlShiftDown=-4121;
				$no = 1;
				for ($a=16;$a<16+count($Advancedetail);$a++){
					$Worksheet->Rows($a+1)->Copy();
					$Worksheet->Rows($a+1)->Insert($xlShiftDown);
					$Worksheet->Range("A".$a)->Value = $no++;
					$Worksheet->Range("B".$a)->Value = $Advancedetail[$a-16]->description;
					$Worksheet->Range("J".$a)->Value = $Advancedetail[$a-16]->accountcode;
					$Worksheet->Range("L".$a)->Value = $Advancedetail[$a-16]->amount;
				}
		

				//end condition


			$xlTypePDF = 0;
			$xlQualityStandard = 0;
			$fileName ='doc'.DS.'hr'.DS.'pdf'.DS.$title.'_'.$Advance->employee->fullname.'_'.$Advance->employee->sapid.'_'.date("YmdHis").'.pdf';
			$fileName = str_replace("/","",$fileName);
			$path= SITE_PATH.'/doc'.DS.'hr'.DS.'pdf'.DS.$title.'_'.$Advance->employee->fullname.'_'.$Advance->employee->sapid.'_'.date("YmdHis").'.pdf';
			if (file_exists($path)) {
			unlink($path);
			}
			$Worksheet->ExportAsFixedFormat($xlTypePDF, $path, $xlQualityStandard);
			$Advance->approveddoc=str_replace("\\","/",$fileName);
			$Advance->save();

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

			return $fileName;

		} catch(com_exception $e) {  
			$err = new Errorlog();
			$err->errortype = "AdvanceFPDFGenerator";
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

	function advanceAttachment(){
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
							$Advanceattachment = Advanceattachment::find('all', array('conditions' => array("advance_id=?",$id)));
							foreach ($Advanceattachment as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Advanceattachment, JSON_NUMERIC_CHECK);
						}else{
							$Advanceattachment = new Advanceattachment();
							echo json_encode($Advanceattachment);
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							$Advanceattachment = Advanceattachment::find('all', array('conditions' => array("advance_id=?",$query['advance_id'])));
							$data=array("jml"=>count($Advanceattachment));
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
						
						$Advanceattachment = Advanceattachment::create($data);
						$logger = new Datalogger("Advanceattachment","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Advanceattachment = Advanceattachment::find($id);
						$data=$Advanceattachment->to_array();
						$Advanceattachment->delete();
						$logger = new Datalogger("Advanceattachment","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Advanceattachment);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
						$data['employee_id']=$Employee->id;
						$Advanceattachment = Advanceattachment::find($id);
						$olddata = $Advanceattachment->to_array();
						foreach($data as $key=>$val){					
							$val=($val=='No')?false:(($val=='Yes')?true:$val);
							$Advanceattachment->$key=$val;
						}
						$Advanceattachment->save();
						$logger = new Datalogger("Advanceattachment","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						echo json_encode($Advanceattachment);
						
						break;
					default:
						$Advanceattachment = Advanceattachment::all();
						foreach ($Advanceattachment as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Advanceattachment, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}
	public function uploadAdvanceFile(){
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
		$path_to_file = "upload/advance/".$id."_".time()."_".$_FILES['myFile']['name'];
		$path_to_file = str_replace("%","_",$path_to_file);
		$path_to_file = str_replace(" ","_",$path_to_file);
		echo $path_to_file;
        move_uploaded_file($_FILES['myFile']['tmp_name'], $path_to_file);
	}

}