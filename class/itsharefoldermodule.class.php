<?php
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

Class Itsharefoldermodule extends Application{
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
		$this->mail->SMTPOptions = array(
			'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
			)
		);
		$this->mail->Host = SMTPSERVER;
		$this->mail->Port = 465;
		$this->mail->SMTPSecure = 'tls';
		$this->mail->SMTPAuth = true;
		$this->mail->Username = MAILFROM;
		$this->mail->Password = SMTPAUTH;
		$this->mail->setFrom(MAILFROM,"Online Approval System");
		$this->mailbody = '<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:m="http://schemas.microsoft.com/office/2004/12/omml" xmlns="http://www.w3.org/TR/REC-html40"><head><meta http-equiv=Content-Type content="text/html; charset=us-ascii"><meta name=Generator content="Microsoft Word 15 (filtered medium)"><style><!--
						/* Font Definitions */
						@font-face {font-family:Wingdings; panose-1:5 0 0 0 0 0 0 0 0 0;} @font-face {font-family:"Cambria Math"; panose-1:2 4 5 3 5 4 6 3 2 4;} @font-face {font-family:Calibri; panose-1:2 15 5 2 2 2 4 3 2 4;} @font-face {font-family:"Century Gothic"; panose-1:2 11 5 2 2 2 2 2 2 4;}
						/* Style Definitions */
						p.MsoNormal, li.MsoNormal, div.MsoNormal {margin:0in; margin-bottom:.0001pt; font-size:11.0pt; font-family:"Calibri","sans-serif";} a:link, span.MsoHyperlink {mso-style-priority:99; color:#0563C1; text-decoration:underline;} a:visited, span.MsoHyperlinkFollowed {mso-style-priority:99; color:#954F72; text-decoration:underline;} span.EmailStyle17 {mso-style-type:personal-reply;	font-family:"Calibri","sans-serif";	color:#1F497D;} .MsoChpDefault {mso-style-type:export-only;} @page WordSection1 {size:8.5in 11.0in;margin:1.0in 1.0in 1.0in 1.0in;} div.WordSection1 {page:WordSection1;} --></style><!--[if gte mso 9]><xml><o:shapedefaults v:ext="edit" spidmax="1026" /></xml><![endif]--><!--[if gte mso 9]><xml><o:shapelayout v:ext="edit"><o:idmap v:ext="edit" data="1" /></o:shapelayout></xml><![endif]--></head>';
		if (isset($this->get)){
			switch ($this->get['action']){
				case 'apiitsharefbyemp':
					$this->itsharefByEmp();
					break;
				case 'apiitsharef':
					$this->itsharef();
					break;
				case 'apiitsharefdetail':
					$this->itsharefDetail();
					break;
				case 'apiitsharefapp':
					$this->itsharefApproval();
					break;
				case 'apiitsharefpdf':
					$id = $this->get['id'];
					// $this->generatePDF($id);
					$this->generatePDFi($id);
					break;
				case 'apiitsharefhist':
					$this->itsharefHistory();
					break;
				
				default:
					break;
			}
		}
	}
	
	function itsharefByEmp(){
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
							$Itsharef = Itsharef::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Itsharef as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Itsharef, JSON_NUMERIC_CHECK);
						}else{
							echo json_encode(array());
						}
						break;
					case 'find':
						$query=$this->post['query'];					
						if(isset($query['status'])){
							switch ($query['status']){
								case 'waiting':
									$Itsharef = Itsharef::find('all', array('conditions' => array("employee_id=? and RequestStatus<3 and id<>?",$query['username'],$query['id']),'include' => array('employee')));
									foreach ($Itsharef as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Itsharef));
									break;
								default:
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
									$Itsharef = Itsharef::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Itsharef as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Itsharef));
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
							$Itsharef = Itsharef::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Itsharef as &$result) {
								$fullname=$result->employee->fullname;
								$result = $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Itsharef, JSON_NUMERIC_CHECK);
						}else{
							echo json_encode(array());
						}
						break;
				}
			}
		}
	}
	function itsharef(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'byid':
						$id = $this->post['id'];
						$join = "LEFT JOIN vwitsharefreport ON tbl_itsharef.id = vwitsharefreport.id";
						$select = "tbl_itsharef.*,vwitsharefreport.apprstatuscode";
						// $Itsharef = Itsharef::find($id, array('include' => array('employee'=>array('company','department','designation','location'))));
						$Itsharef = Itsharef::find($id, array('joins'=>$join,'select'=>$select,'include' => array('employee'=>array('company','department','designation'))));
						if ($Itsharef){
							$fullname = $Itsharef->employee->fullname;
							$bgbu = $Itsharef->employee->companycode;
							$location = $Itsharef->employee->location->location;
							$designation = $Itsharef->employee->designation->designationname;
							$department = $Itsharef->employee->department->departmentname;
							$data=$Itsharef->to_array();
							$data['fullname']=$fullname;
							$data['bgbu']=$bgbu;
							// $data['listgroup']=$bgbu;
							$data['department']=$department;
							$data['officelocation']=$location;
							$data['designation']=$designation;
							echo json_encode($data, JSON_NUMERIC_CHECK);
						}else{
							$Itsharef = new Itsharef();
							echo json_encode($Itsharef);
						}
						break;
					case 'find':
						$query=$this->post['query'];					
						if(isset($query['status'])){
							switch ($query['status']){
								case 'chemp':
									break;
								case 'appcon':
										$formtype = $query['formtype'];
										$employee_id = $query['employee_id'];
										$id= $query['itshar`efl_id'];

										$Itsharef = Itsharef::find($id);

										$Employee = Employee::find('first', array('conditions' => array("id=?",$employee_id),"include"=>array("location","department","company")));

										print_r($Employee);

										$joins   = "LEFT JOIN tbl_approver ON (tbl_itsharefapproval.approver_id = tbl_approver.id) LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id)";
										$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";	
										// if (($formtype=='2') || ($formtype=='3')){
										$Itsharefapproval = Itsharefapproval::find('all',array('joins'=>$joins,'conditions' => array("itsharef_id=? and tbl_approver.approvaltype_id='30' ",$id)));	
										foreach ($Itsharefapproval as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}
										$Itsharefapprovalmd = Itsharefapproval::find('all',array('joins'=>$joins,'conditions' => array("itsharef_id=? and tbl_approver.approvaltype_id='33' ",$id)));	
										foreach ($Itsharefapprovalmd as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}
										

										if (($formtype=='1')){

												if(count($Itsharefapproval)==0){

													if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,3)=="0220") || ($Employee->department->sapcode=="13000090") || ($Employee->department->sapcode=="13000121") || ($Employee->company->sapcode=="NKF") || ($Employee->company->sapcode=="RND")){

														$ApproverCADKF = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id='30' and tbl_employee.location_id='1'")));
														if(count($ApproverCADKF)>0){
																$Itsharefapproval = new Itsharefapproval();
																$Itsharefapproval->itsharef_id = $Itsharef->id;
																$Itsharefapproval->approver_id = $ApproverCADKF->id;
																$Itsharefapproval->save();
																$logger = new Datalogger("Itsharefapproval","add","Add Approval",json_encode($Itsharefapproval->to_array()));
																$logger->SaveData();
														}
													} else {
															$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=30 and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
														if(count($Approver2)>0){
															$Itsharefapproval = new Itsharefapproval();
															$Itsharefapproval->itsharef_id = $Itsharef->id;
															$Itsharefapproval->approver_id = $Approver2->id;
															$Itsharefapproval->save();
														}
													}


													if(count($Itsharefapprovalmd)>0){
														$dx = Itsharefapproval::find('all',array('joins'=>$joins,'conditions' => array("itsharef_id=? and tbl_approver.approvaltype_id=33",$id)));	
														foreach ($dx as $result) {
															$result->delete();
															$logger = new Datalogger("Itsharefapproval","delete",json_encode($result->to_array()),"delete Approval");
															$logger->SaveData();
														}
													}
												}
													
										} else if(($formtype=='4')) {
											if(count($Itsharefapproval)>0){
												$dx = Itsharefapproval::find('all',array('joins'=>$joins,'conditions' => array("itsharef_id=? and tbl_approver.approvaltype_id=30",$id)));	
												foreach ($dx as $result) {
													$result->delete();
													$logger = new Datalogger("Itsharefapproval","delete",json_encode($result->to_array()),"delete Approval");
													$logger->SaveData();
												}
											}

											if(count($Itsharefapprovalmd)==0){
												$ApproverCADKF = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id='33'")));
												if(count($ApproverCADKF)>0){
														$Itsharefapproval = new Itsharefapproval();
														$Itsharefapproval->itsharef_id = $id;
														$Itsharefapproval->approver_id = $ApproverCADKF->id;
														$Itsharefapproval->save();
														$logger = new Datalogger("Itsharefapproval","add","Add Approval",json_encode($Itsharefapproval->to_array()));
														$logger->SaveData();
												}
												
											}
										} else {
											if(count($Itsharefapproval)>0){
												$dx = Itsharefapproval::find('all',array('joins'=>$joins,'conditions' => array("itsharef_id=? and tbl_approver.approvaltype_id=30",$id)));	
												foreach ($dx as $result) {
													$result->delete();
													$logger = new Datalogger("Itsharefapproval","delete",json_encode($result->to_array()),"delete Approval");
													$logger->SaveData();
												}
											}

											if(count($Itsharefapprovalmd)>0){
												$dx = Itsharefapproval::find('all',array('joins'=>$joins,'conditions' => array("itsharef_id=? and tbl_approver.approvaltype_id=33",$id)));	
												foreach ($dx as $result) {
													$result->delete();
													$logger = new Datalogger("Itsharefapproval","delete",json_encode($result->to_array()),"delete Approval");
													$logger->SaveData();
												}
											}
										}
										
								break;
								case 'appreq':
									$formtype = $query['formtype'];
									$employee_id = $query['employee_id'];
									$id= $query['itsharef_id'];
									$accessreq= $query['accessreq'];

									$Itsharef = Itsharef::find($id);

									$Employee = Employee::find('first', array('conditions' => array("id=?",$employee_id),"include"=>array("location","department","company")));

									// print_r($Employee);

									$joins   = "LEFT JOIN tbl_approver ON (tbl_itsharefapproval.approver_id = tbl_approver.id) LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id)";
									$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";	
									// if (($formtype=='2') || ($formtype=='3')){
									$Itsharefapproval = Itsharefapproval::find('all',array('joins'=>$joins,'conditions' => array("itsharef_id=? and tbl_approver.approvaltype_id='30' ",$id)));	
									foreach ($Itsharefapproval as &$result) {
										$result		= $result->to_array();
										$result['no']=1;

									}
									$Itsharefapprovalmd = Itsharefapproval::find('all',array('joins'=>$joins,'conditions' => array("itsharef_id=? and tbl_approver.approvaltype_id='33' ",$id)));	
									foreach ($Itsharefapprovalmd as &$result) {
										$result		= $result->to_array();
										$result['no']=1;

									}
									

									if ($accessreq != 1){
										if(count($Itsharefapproval)>0){
											$dx = Itsharefapproval::find('all',array('joins'=>$joins,'conditions' => array("itsharef_id=? and tbl_approver.approvaltype_id=30",$id)));	
											foreach ($dx as $result) {
												$result->delete();
												$logger = new Datalogger("Itsharefapproval","delete",json_encode($result->to_array()),"delete Approval");
												$logger->SaveData();
											}
										}

											// if(count($Itsharefapproval)==0){

											// 	if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,3)=="0220") || ($Employee->department->sapcode=="13000090") || ($Employee->department->sapcode=="13000121") || ($Employee->company->sapcode=="NKF") || ($Employee->company->sapcode=="RND")){

											// 		$ApproverCADKF = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id='30' and tbl_employee.location_id='1'")));
											// 		$ApproverCADKF = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id='30' and tbl_employee.location_id='1'")));
											// 		if(count($ApproverCADKF)>0){
											// 				$Itsharefapproval = new Itsharefapproval();
											// 				$Itsharefapproval->itsharef_id = $Itsharef->id;
											// 				$Itsharefapproval->approver_id = $ApproverCADKF->id;
											// 				$Itsharefapproval->save();
											// 				$logger = new Datalogger("Itsharefapproval","add","Add Approval",json_encode($Itsharefapproval->to_array()));
											// 				$logger->SaveData();
											// 		}

											// 	} else {
													// if($accessreq != 1) {
													$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id='30' and tbl_employee.loginname like '%Randie_Tjoe%'")));
													// } else {
														
													// 	$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=30 and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
													// }
													if(count($Approver2)>0){
														$Itsharefapproval = new Itsharefapproval();
														$Itsharefapproval->itsharef_id = $Itsharef->id;
														$Itsharefapproval->approver_id = $Approver2->id;
														$Itsharefapproval->save();
													}

											// 	}
											echo "1";


											// }
												
									} else {

										if(count($Itsharefapproval)>0){
											$dx = Itsharefapproval::find('all',array('joins'=>$joins,'conditions' => array("itsharef_id=? and tbl_approver.approvaltype_id=30",$id)));	
											foreach ($dx as $result) {
												$result->delete();
												$logger = new Datalogger("Itsharefapproval","delete",json_encode($result->to_array()),"delete Approval");
												$logger->SaveData();
											}
										}

										echo "2";

										// if(count($Itsharefapproval)==0){

											if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,3)=="0220") || ($Employee->department->sapcode=="13000090") || ($Employee->department->sapcode=="13000121") || ($Employee->company->sapcode=="NKF") || ($Employee->company->sapcode=="RND")){

												$ApproverCADKF = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id='30' and tbl_employee.location_id='1'")));
												if(count($ApproverCADKF)>0){
														$Itsharefapproval = new Itsharefapproval();
														$Itsharefapproval->itsharef_id = $Itsharef->id;
														$Itsharefapproval->approver_id = $ApproverCADKF->id;
														$Itsharefapproval->save();
														$logger = new Datalogger("Itsharefapproval","add","Add Approval",json_encode($Itsharefapproval->to_array()));
														$logger->SaveData();
												}
											} else {
													$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=30 and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
												if(count($Approver2)>0){
													$Itsharefapproval = new Itsharefapproval();
													$Itsharefapproval->itsharef_id = $Itsharef->id;
													$Itsharefapproval->approver_id = $Approver2->id;
													$Itsharefapproval->save();
												}
											}
										// }

									}
									
							break;
								case "reschedule":
									$id = $query['itsharef_id'];
									$Itsharef = Itsharef::find($id,array('include'=>array('employee'=>array('company','department','designation','grade'))));
									$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Itsharef->employee->loginname)));
									$email=$usr->email;
									
									$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$usr->fullname.',</span></p>
										<p class=MsoNormal><span style="color:#1F497D">Your Request Form has been rescheduled by HR to match with your actual travel schedule:</span></p>
										<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itsharef->employee->fullname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itsharef->employee->sapid.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itsharef->employee->designation->designationname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itsharef->employee->company->companyname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itsharef->employee->location->location.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
										';
									$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
									$this->mail->addAddress($usr->email, $usr->fullname);
									$this->mail->Subject = "Online Approval System -> Request Form Email Request Reschedule";
									$fileName = $this->generatePDF($id);
									$filePath = SITE_PATH.DS.$fileName;
									$this->mail->addAttachment($filePath);
									$this->mail->msgHTML($this->mailbody);
									if (!$this->mail->send()) {
										$err = new Errorlog();
										$err->errortype = "Mail";
										$err->errordate = date("Y-m-d h:i:s");
										$err->errormessage = $this->mail->ErrorInfo;
										$err->user = $this->currentUser->username;
										$err->ip = $this->ip;
										$err->save();
										echo "Mailer Error: " . $this->mail->ErrorInfo;
									} else {
										echo "Message sent!";
									}
									
									
									break;
								default:
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$query['username'])));
									$Itsharef = Itsharef::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Itsharef as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Itsharef));
									break;
							}
						} else{
							$data=array();
						}
						echo json_encode($data);
						break;
					case 'create':
						$data = $this->post['data'];
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$data['username']),"include"=>array("location","department","company")));
						unset($data['__KEY__']);
						unset($data['username']);
						$data['employee_id']=$Employee->id;
						// $data['createdby']=$Employee->id;
						$data['RequestStatus']=0;
						try{
							// $Itsharefnew = Itsharef::find('first',array('select' => "CONCAT('Itsharef/','".$Employee->companycode."','/',YEAR(CURDATE()),'/',LPAD(MONTH(CURDATE()), 2, '0'),'/',LPAD(CASE when max(substring(wonumber,-4,4)) is null then 1 else max(substring(wonumber,-4,4))+1 end,4,'0')) as wonumber","conditions"=>array("substring(wonumber,7,".strlen($Employee->companycode).")=? and substring(wonumber,".(strlen($Employee->companycode)+8).",4)=YEAR(CURDATE())",$Employee->companycode)));
							// $data['wonumber']=$Itsharefnew->wonumber;
							$Itsharef = Itsharef::create($data);
							$data=$Itsharef->to_array();
							$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";

								// $Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=30")));
								// if(count($Approver)>0){
								// 	$Itsharefapproval = new Itsharefapproval();
								// 	$Itsharefapproval->itsharef_id = $Itsharef->id;
								// 	$Itsharefapproval->approver_id = $Approver->id;
								// 	$Itsharefapproval->save();
								// }
								// $Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=31")));
								// if(count($Approver2)>0){
								// 	$Itsharefapproval = new Itsharefapproval();
								// 	$Itsharefapproval->itsharef_id = $Itsharef->id;
								// 	$Itsharefapproval->approver_id = $Approver2->id;
								// 	$Itsharefapproval->save();
								// }
								$Approver3 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=32")));
								if(count($Approver3)>0){
									$Itsharefapproval = new Itsharefapproval();
									$Itsharefapproval->itsharef_id = $Itsharef->id;
									$Itsharefapproval->approver_id = $Approver3->id;
									$Itsharefapproval->save();
								}

								if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,3)=="0220") || ($Employee->department->sapcode=="13000090") || ($Employee->department->sapcode=="13000121") || ($Employee->company->sapcode=="NKF") || ($Employee->company->sapcode=="RND")){
									// $Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=30 and tbl_employee.location_id='1'")));
									// if(count($Approver2)>0){
									// 	$Itsharefapproval = new Itsharefapproval();
									// 	$Itsharefapproval->itsharef_id = $Itsharef->id;
									// 	$Itsharefapproval->approver_id = $Approver2->id;
									// 	$Itsharefapproval->save();
									// }
									
									if(($Employee->department->sapcode!="13000090") && ($Employee->department->sapcode!="13000121") && ($Employee->company->sapcode!="NKF") && ($Employee->company->sapcode!="RND")  && ($Employee->company->companycode!="BCL")  && ($Employee->company->companycode!="LDU")){
										if(($Employee->level_id!=4) && ($Employee->level_id!=6) ){
											$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=31 and tbl_employee.companycode='KPSI' and not(tbl_employee.id=?)",$Employee->id)));
											if(count($Approver)>0){
												$Itsharefapproval = new Itsharefapproval();
												$Itsharefapproval->itsharef_id = $Itsharef->id;
												$Itsharefapproval->approver_id = $Approver->id;
												$Itsharefapproval->save();
											}
										}
									}else{
										$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=31 and tbl_employee.company_id=? and not tbl_employee.companycode='KPSI'  and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
										if(count($Approver)>0){
											$Itsharefapproval = new Itsharefapproval();
											$Itsharefapproval->itsharef_id = $Itsharef->id;
											$Itsharefapproval->approver_id = $Approver->id;
											$Itsharefapproval->save();
										}else{
											$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=31 and tbl_employee.company_id=? and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
											if(count($Approver)>0){
												$Itsharefapproval = new Itsharefapproval();
												$Itsharefapproval->itsharef_id = $Itsharef->id;
												$Itsharefapproval->approver_id = $Approver->id;
												$Itsharefapproval->save();
											}
										}
									}	
								}else{
									$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=31 and not tbl_employee.companycode='KPSI' and tbl_employee.company_id=? and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
									if(count($Approver)>0){
										$Itsharefapproval = new Itsharefapproval();
										$Itsharefapproval->itsharef_id = $Itsharef->id;
										$Itsharefapproval->approver_id = $Approver->id;
										$Itsharefapproval->save();
									}else{
										$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=31 and tbl_employee.company_id=? and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
										if(count($Approver)>0){
											$Itsharefapproval = new Itsharefapproval();
											$Itsharefapproval->itsharef_id = $Itsharef->id;
											$Itsharefapproval->approver_id = $Approver->id;
											$Itsharefapproval->save();
										}
									}

									// $Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=30 and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
									// if(count($Approver2)>0){
									// 	$Itsharefapproval = new Itsharefapproval();
									// 	$Itsharefapproval->itsharef_id = $Itsharef->id;
									// 	$Itsharefapproval->approver_id = $Approver2->id;
									// 	$Itsharefapproval->save();
									// }
								}

							$Iteihistory = new Itsharefhistory();
							$Iteihistory->date = date("Y-m-d h:i:s");
							$Iteihistory->fullname = $Employee->fullname;
							$Iteihistory->approvaltype = "Originator";
							$Iteihistory->itsharef_id = $Itsharef->id;
							$Iteihistory->actiontype = 0;
							$Iteihistory->save();
							
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "CreateITIMAIL";
							$err->errordate = date("Y-m-d h:i:s");
							$err->errormessage = $e->getMessage();
							$err->user = $this->currentUser->username;
							$err->ip = $this->ip;
							$err->save();
							$data = array("status"=>"error","message"=>$e->getMessage());
						}
						$logger = new Datalogger("ITIMAIL","create",null,json_encode($data));
						$logger->SaveData();
						echo json_encode($data);									
						break;
					case 'delete':
						try {				
							$id = $this->post['id'];
							$Itsharef = Itsharef::find($id);
							if ($Itsharef->requeststatus==0){
								$approval = Itsharefapproval::find("all",array('conditions' => array("itsharef_id=?",$id)));
								foreach ($approval as $delr){
									$delr->delete();
								}
								// $detail = Trschedule::find("all",array('conditions' => array("itsharef_id=?",$id)));
								// foreach ($detail as $delr){
								// 	$delr->delete();
								// }
								// $detail = Trticket::find("all",array('conditions' => array("itsharef_id=?",$id)));
								// foreach ($detail as $delr){
								// 	$delr->delete();
								// }
								$hist = Itsharefhistory::find("all",array('conditions' => array("itsharef_id=?",$id)));
								foreach ($hist as $delr){
									$delr->delete();
								}
								$data = $Itsharef->to_array();
								$Itsharef->delete();
								$logger = new Datalogger("ITIMAIL","delete",json_encode($data),null);
								$logger->SaveData();
								echo json_encode($Itsharef);
							} else {
								$data = array("status"=>"error","message"=>"You can't delete submitted request");
								echo json_encode($data);
							}
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "DeleteITSHAREF";
							$err->errordate = date("Y-m-d h:i:s");
							$err->errormessage = $e->getMessage();
							$err->user = $this->currentUser->username;
							$err->ip = $this->ip;
							$err->save();
							$data = array("status"=>"error","message"=>$e->getMessage());
						}
						break;
					case 'update':
						try{
							$id = $this->post['id'];
							$data = $this->post['data'];
							$Itsharef = Itsharef::find($id,array('include'=>array('employee'=>array('company','department','designation','grade'))));
							$olddata = $Itsharef->to_array();
							$depthead = $data['depthead'];
							// $buyer = $data['buyer'];
							unset($data['fullname']);
							// unset($data['department']);
							unset($data['approvalstatus']);
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							if($superior==$Employee->id){
								$result= array("status"=>"error","message"=>"You cannot select yourself as your Direct superior");
								echo json_encode($result);
							}else{
								foreach($data as $key=>$val){
									$value=(($val===0) || ($val==='0') || ($val==='false'))?false:((($val===1) || ($val==='1') || ($val==='true'))?true:$val);
									$Itsharef->$key=$value;
								}
								$Itsharef->save();
								
								if (isset($data['depthead'])){
									$joins   = "LEFT JOIN tbl_approver ON (tbl_itsharefapproval.approver_id = tbl_approver.id) ";					
									$dx = Itsharefapproval::find('all',array('joins'=>$joins,'conditions' => array("itsharef_id=? and tbl_approver.approvaltype_id=29 and not(tbl_approver.employee_id=?)",$id,$depthead)));	
									foreach ($dx as $result) {
										//delete same type dept head approver
										$result->delete();
										$logger = new Datalogger("Itsharefapproval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
									}
									$joins   = "LEFT JOIN tbl_approver ON (tbl_itsharefapproval.approver_id = tbl_approver.id) ";					
									$Itsharefapproval = Itsharefapproval::find('all',array('joins'=>$joins,'conditions' => array("itsharef_id=? and tbl_approver.employee_id=?",$id,$depthead)));	
									foreach ($Itsharefapproval as &$result) {
										$result		= $result->to_array();
										$result['no']=1;
									}			
									if(count($Itsharefapproval)==0){ 
										$Approver = Approver::find('first',array('conditions'=>array("module='IT' and employee_id=? and approvaltype_id=29",$depthead)));
										if(count($Approver)>0){
											$Itsharefapproval = new Itsharefapproval();
											$Itsharefapproval->itsharef_id = $Itsharef->id;
											$Itsharefapproval->approver_id = $Approver->id;
											$Itsharefapproval->save();
										}else{
											$approver = new Approver();
											$approver->module = "IT";
											$approver->employee_id=$depthead;
											$approver->sequence=1;
											$approver->approvaltype_id = 29;
											$approver->isfinal = false;
											$approver->save();
											$Itsharefapproval = new Itsharefapproval();
											$Itsharefapproval->itsharef_id = $Itsharef->id;
											$Itsharefapproval->approver_id = $approver->id;
											$Itsharefapproval->save();
										}
									}
									
								}
								
								if($data['requeststatus']==1){
									$Itsharefapproval = Itsharefapproval::find('all', array('conditions' => array("itsharef_id=?",$id)));					
									foreach($Itsharefapproval as $data){
										$data->approvalstatus=0;
										$data->save();
									}
									$joinx   = "LEFT JOIN tbl_approver ON (tbl_itsharefapproval.approver_id = tbl_approver.id) ";					
									$Itsharefapproval = Itsharefapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and itsharef_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
									$username = $Itsharefapproval->approver->employee->loginname;
									$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
									$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Itsharef->employee->loginname)));
									$email=$usr->email;

									// if($Itsharef->formtype == 1) {
									// 	$title = 'Exchange - Internet Email';
									// }else if($Itsharef->formtype == 2) {
									// 	$title = 'Internet Access';
									// }else if($Itsharef->formtype == 3) {
									// 	$title = 'Increase Mailbox Size';
									// }else if($Itsharef->formtype == 4) {
									// 	$title = 'RD Web Access';
									// }else if($Itsharef->formtype == 5) {
									// 	$title = 'Email Group';
									// }

									$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$adb->fullname.',</span></p>
										<p class=MsoNormal><span style="color:#1F497D">new '.$title.' Request is awaiting for your approval:</span></p>
										<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itsharef->employee->fullname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itsharef->employee->sapid.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itsharef->employee->designation->designationname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itsharef->employee->company->companyname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itsharef->employee->location->location.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
										</table>
										<br>

										';
									
									// if($Itsharef->formtype == 1) {

									// 	if($Itsharef->accessrequested == 1) {
									// 		$accessR = 'Exchange (non-Internet) Email';
									// 	}else if($Itsharef->accessrequested == 2) {
									// 		$accessR = 'Internet Email';
									// 	}else if($Itsharef->accessrequested == 3) {
									// 		$accessR = 'Change Domain';
									// 	}else {
									// 		$accessR = '';
									// 	}

									// 	if($Itsharef->accesstype == 1) {
									// 		$accessT = 'Terminal Server (TS) User Account';
									// 	}else if($Itsharef->accesstype == 2) {
									// 		$accessT = 'Non-TS Account';
									// 	}else {
									// 		$accessT = '';
									// 	}
	
									// 	if($Itsharef->accounttype == 1) {
									// 		$accountT = 'Permanent';
									// 	}else if($Itsharef->accounttype == 2) {
									// 		$accountT = 'Temporary';
									// 	}else {
									// 		$accountT = '';
									// 	}

									// 	if($Itsharef->emailquota == 1) {
									// 		$emailQ = '250MB';
									// 	}else if($Itsharef->emailquota == 2) {
									// 		$emailQ = '500MB';
									// 	}else if($Itsharef->emailquota == 3) {
									// 		$emailQ = '1000MB';
									// 	}else if($Itsharef->emailquota == 4) {
									// 		$emailQ = '1500MB';
									// 	}else if($Itsharef->emailquota == 5) {
									// 		$emailQ = '2000MB';
									// 	}else {
									// 		$emailQ = '';
									// 	}

									// 	if($Itsharef->emaildomain == 1) {
									// 		$emailD = 'itci-hutani.com';
									// 	}else if($Itsharef->emaildomain == 2) {
									// 		$emailD = 'kalimantan-prima.com';
									// 	}else if($Itsharef->emaildomain == 3) {
									// 		$emailD = 'balikpapanchip.com';
									// 	}else if($Itsharef->emaildomain == 4) {
									// 		$emailD = 'lajudinamika.com';
									// 	}else if($Itsharef->emaildomain == 5) {
									// 		$emailD = 'ptadindo.com';
									// 	}else if($Itsharef->emaildomain == 6) {
									// 		$emailD = 'D1.LCL';
									// 	}else {
									// 		$emailD = '';
									// 	}

									// 	$listmod = Listmod::find('first',array('conditions'=>array("id=?",$Itsharef->listgroupmoderation)));

									// 	$this->mailbody .='	
											
									// 		<table border=1 cellspacing=0 cellpadding=3 width=683>
									// 		<tr>
									// 			<th><p class=MsoNormal>Access Requested</p></th>
									// 			<th><p class=MsoNormal>Access Type</p></th>
									// 			<th><p class=MsoNormal>Account Type</p></th>
									// 			<th><p class=MsoNormal>Email Quota </p></th>
									// 			<th><p class=MsoNormal>Email Domain </p></th>
									// 			<th><p class=MsoNormal>List Group</p></th>
									// 			<th><p class=MsoNormal>List Group Moderation</p></th>
									// 			<th><p class=MsoNormal>Valid From</p></th>
									// 			<th><p class=MsoNormal>Valid To</p></th>
									// 		</tr>
									// 		<tr style="height:22.5pt">
									// 			<td><p class=MsoNormal> '.$accessR.'</p></td>
									// 			<td><p class=MsoNormal> '.$accessT.'</p></td>
									// 			<td><p class=MsoNormal> '.$accountT.'</p></td>
									// 			<td><p class=MsoNormal> '.$emailQ.'</p></td>
									// 			<td><p class=MsoNormal> '.$emailD.'</p></td>
									// 			<td><p class=MsoNormal> '.$Itsharef->listgroup.'</p></td>
									// 			<td><p class=MsoNormal> '.$listmod->mod.'</p></td>
									// 			<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itsharef->validfrom)).'</p></td>
									// 			<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itsharef->validto)).'</p></td>
									// 		</tr>
									// 		';

									// 	$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
										
									// } else if($Itsharef->formtype == 2) {
									// 	$this->mailbody .='	
											
									// 		<table border=1 cellspacing=0 cellpadding=3 width=683>
									// 		<tr>
									// 			<th><p class=MsoNormal>http:// (A)</p></th>
									// 			<th><p class=MsoNormal>http:// (B)</p></th>
									// 			<th><p class=MsoNormal>Valid From</p></th>
									// 			<th><p class=MsoNormal>Valid To</p></th>
									// 		</tr>
									// 		<tr style="height:22.5pt">
									// 			<td><p class=MsoNormal> '.$Itsharef->web1.'</p></td>
									// 			<td><p class=MsoNormal> '.$Itsharef->web2.'</p></td>
									// 			<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itsharef->validfrom)).'</p></td>
									// 			<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itsharef->validto)).'</p></td>
									// 		</tr>
									// 		';

									// 	$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
										
									// } else if($Itsharef->formtype == 3) {
										
									// 	if($Itsharef->newmailboxsize == 1) {
									// 		$newmailbox = '256MB';
									// 	}else if($Itsharef->newmailboxsize == 2) {
									// 		$newmailbox = '512MB';
									// 	}else if($Itsharef->newmailboxsize == 3) {
									// 		$newmailbox = '1GB';
									// 	}else if($Itsharef->newmailboxsize == 4) {
									// 		$newmailbox = '1.5GB';
									// 	}else if($Itsharef->newmailboxsize == 5) {
									// 		$newmailbox = '2GB';
									// 	}else if($Itsharef->newmailboxsize == 6) {
									// 		$newmailbox = '3GB';
									// 	}else if($Itsharef->newmailboxsize == 7) {
									// 		$newmailbox = '4GB';
									// 	}else if($Itsharef->newmailboxsize == 8) {
									// 		$newmailbox = '5GB';
									// 	}else if($Itsharef->newmailboxsize == 9) {
									// 		$newmailbox = '6GB';
									// 	}else if($Itsharef->newmailboxsize == 10) {
									// 		$newmailbox = '7GB';
									// 	}else if($Itsharef->newmailboxsize == 11) {
									// 		$newmailbox = '8GB';
									// 	}else if($Itsharef->newmailboxsize == 12) {
									// 		$newmailbox = '9GB';
									// 	}else if($Itsharef->newmailboxsize == 13) {
									// 		$newmailbox = '10GB';
									// 	}else {
									// 		$newmailbox = '';
									// 	}

									// 	if($Itsharef->incomingsize == 1) {
									// 		$incoming = '5MB';
									// 	}else if($Itsharef->incomingsize == 2) {
									// 		$incoming = '10MB';
									// 	}else if($Itsharef->incomingsize == 3) {
									// 		$incoming = '15MB';
									// 	}else if($Itsharef->incomingsize == 4) {
									// 		$incoming = '20MB';
									// 	}else if($Itsharef->incomingsize == 5) {
									// 		$incoming = '25MB';
									// 	}else if($Itsharef->incomingsize == 6) {
									// 		$incoming = '30MB';
									// 	}else if($Itsharef->incomingsize == 7) {
									// 		$incoming = '35MB';
									// 	}else if($Itsharef->incomingsize == 8) {
									// 		$incoming = '40MB';
									// 	}else if($Itsharef->incomingsize == 9) {
									// 		$incoming = '45MB';
									// 	}else if($Itsharef->incomingsize == 10) {
									// 		$incoming = '50MB';
									// 	}else if($Itsharef->incomingsize == 11) {
									// 		$incoming = '55MB';
									// 	}else if($Itsharef->incomingsize == 12) {
									// 		$incoming = '60MB';
									// 	}else if($Itsharef->incomingsize == 13) {
									// 		$incoming = '65MB';
									// 	}else if($Itsharef->incomingsize == 14) {
									// 		$incoming = '70MB';
									// 	}else if($Itsharef->incomingsize == 15) {
									// 		$incoming = '75MB';
									// 	}else if($Itsharef->incomingsize == 16) {
									// 		$incoming = '80MB';
									// 	}else if($Itsharef->incomingsize == 17) {
									// 		$incoming = '85MB';
									// 	}else if($Itsharef->incomingsize == 18) {
									// 		$incoming = '90MB';
									// 	}else if($Itsharef->incomingsize == 19) {
									// 		$incoming = '95MB';
									// 	}else if($Itsharef->incomingsize == 20) {
									// 		$incoming = '100MB';
									// 	}else {
									// 		$incoming = '';
									// 	}

									// 	if($Itsharef->outgoingsize == 1) {
									// 		$outgoing = '5MB';
									// 	}else if($Itsharef->outgoingsize == 2) {
									// 		$outgoing = '10MB';
									// 	}else if($Itsharef->outgoingsize == 3) {
									// 		$outgoing = '15MB';
									// 	}else if($Itsharef->outgoingsize == 4) {
									// 		$outgoing = '20MB';
									// 	}else if($Itsharef->outgoingsize == 5) {
									// 		$outgoing = '25MB';
									// 	}else if($Itsharef->outgoingsize == 6) {
									// 		$outgoing = '30MB';
									// 	}else if($Itsharef->outgoingsize == 7) {
									// 		$outgoing = '35MB';
									// 	}else if($Itsharef->outgoingsize == 8) {
									// 		$outgoing = '40MB';
									// 	}else if($Itsharef->outgoingsize == 9) {
									// 		$outgoing = '45MB';
									// 	}else if($Itsharef->outgoingsize == 10) {
									// 		$outgoing = '50MB';
									// 	}else if($Itsharef->outgoingsize == 11) {
									// 		$outgoing = '55MB';
									// 	}else if($Itsharef->outgoingsize == 12) {
									// 		$outgoing = '60MB';
									// 	}else if($Itsharef->outgoingsize == 13) {
									// 		$outgoing = '65MB';
									// 	}else if($Itsharef->outgoingsize == 14) {
									// 		$outgoing = '70MB';
									// 	}else if($Itsharef->outgoingsize == 15) {
									// 		$outgoing = '75MB';
									// 	}else if($Itsharef->outgoingsize == 16) {
									// 		$outgoing = '80MB';
									// 	}else if($Itsharef->outgoingsize == 17) {
									// 		$outgoing = '85MB';
									// 	}else if($Itsharef->outgoingsize == 18) {
									// 		$outgoing = '90MB';
									// 	}else if($Itsharef->outgoingsize == 19) {
									// 		$outgoing = '95MB';
									// 	}else if($Itsharef->outgoingsize == 20) {
									// 		$outgoing = '100MB';
									// 	}else {
									// 		$outgoing = '';
									// 	}

									// 	$this->mailbody .='	
											
									// 		<table border=1 cellspacing=0 cellpadding=3 width=683>
									// 		<tr>
									// 			<th><p class=MsoNormal>New Mailbox Size</p></th>
									// 			<th><p class=MsoNormal>Outgoing Size</p></th>
									// 			<th><p class=MsoNormal>Incoming Size</p></th>
									// 			<th><p class=MsoNormal>Valid From</p></th>
									// 			<th><p class=MsoNormal>Valid To</p></th>
									// 		</tr>
									// 		<tr style="height:22.5pt">
									// 			<td><p class=MsoNormal> '.$newmailbox.'</p></td>
									// 			<td><p class=MsoNormal> '.$incoming.'</p></td>
									// 			<td><p class=MsoNormal> '.$outgoing.'</p></td>
									// 			<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itsharef->validfrom)).'</p></td>
									// 			<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itsharef->validto)).'</p></td>
									// 		</tr>
									// 		';

									// 	$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
										
									// } else if($Itsharef->formtype == 4) {
									// 	$this->mailbody .='	
											
									// 		<table border=1 cellspacing=0 cellpadding=3 width=683>
									// 		<tr>
									// 			<th><p class=MsoNormal>RDP to TS</p></th>
									// 			<th><p class=MsoNormal>Example of usage</p></th>
									// 			<th><p class=MsoNormal>Valid From</p></th>
									// 			<th><p class=MsoNormal>Valid To</p></th>
									// 		</tr>
									// 		<tr style="height:22.5pt">
									// 			<td><p class=MsoNormal> '.$Itsharef->typeofaccess.'</p></td>
									// 			<td><p class=MsoNormal> access email, open & edit attachments/ documents, department shared folders, corporate portals, SAP GUI</p></td>
									// 			<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itsharef->validfrom)).'</p></td>
									// 			<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itsharef->validto)).'</p></td>
									// 		</tr>
									// 		';

									// 	$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
										
									// } else if($Itsharef->formtype == 5) {

									// 	$string = $Itsharef->membername;

									// 	$expstring = explode(',',$string);
									// 	// $countstring = count($expstring)+29;

									// 	$getname = [];
									// 	foreach($expstring as $p => $key) {
									// 		$dataname = Employee::find($key);
									// 		array_push($getname,$dataname->loginname);
									// 	}

									// 	$getemailname = [];
									// 	foreach($getname as $p => $key) {

									// 		$datamail = Addressbook::find('first',array('select'=> "CONCAT(fullname,' (',email,')' ) as name",'conditions' => array("username=?",$key)));

									// 		array_push($getemailname,$datamail->name);

									// 	}

									// 	$ss = implode(' | ',$getemailname);

									// 	$this->mailbody .='	
											
									// 		<table border=1 cellspacing=0 cellpadding=3 width=683>
									// 		<tr>
									// 			<th><p class=MsoNormal>Email Group Name</p></th>
									// 			<th><p class=MsoNormal>Member Name</p></th>
									// 			<th><p class=MsoNormal>Valid From</p></th>
									// 			<th><p class=MsoNormal>Valid To</p></th>
									// 		</tr>
									// 		<tr style="height:22.5pt">
									// 			<td><p class=MsoNormal> '.$Itsharef->emailgroupname.'</p></td>
									// 			<td><p class=MsoNormal> '.$ss.'</p></td>
									// 			<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itsharef->validfrom)).'</p></td>
									// 			<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itsharef->validto)).'</p></td>
									// 		</tr>
									// 		';

									// 	$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
										
									// }
									
									$this->mail->addAddress($adb->email, $adb->fullname);
									$this->mail->Subject = "Online Approval System -> ".$title;
									$this->mail->msgHTML($this->mailbody);
									if (!$this->mail->send()) {
										$err = new Errorlog();
										$err->errortype = "Mail";
										$err->errordate = date("Y-m-d h:i:s");
										$err->errormessage = $this->mail->ErrorInfo;
										$err->user = $this->currentUser->username;
										$err->ip = $this->ip;
										$err->save();
										echo "Mailer Error: " . $this->mail->ErrorInfo;
									} else {
										echo "Message sent!";
									}
									$Itsharefhistory = new Itsharefhistory();
									$Itsharefhistory->date = date("Y-m-d h:i:s");
									$Itsharefhistory->fullname = $Employee->fullname;
									$Itsharefhistory->itsharef_id = $id;
									$Itsharefhistory->approvaltype = "Originator";
									$Itsharefhistory->actiontype = 2;
									$Itsharefhistory->save();
								}else{
									$Itsharefhistory = new Itsharefhistory();
									$Itsharefhistory->date = date("Y-m-d h:i:s");
									$Itsharefhistory->fullname = $Employee->fullname;
									$Itsharefhistory->itsharef_id = $id;
									$Itsharefhistory->approvaltype = "Originator";
									$Itsharefhistory->actiontype = 1;
									$Itsharefhistory->save();
								}
								$logger = new Datalogger("ITIMAIL","update",json_encode($olddata),json_encode($data));
								$logger->SaveData();
							}
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "UpdateITSHAREF";
							$err->errordate = date("Y-m-d h:i:s");
							$err->errormessage = $e->getMessage();
							$err->user = $this->currentUser->username;
							$err->ip = $this->ip;
							$err->save();
							$data = array("status"=>"error","message"=>$e->getMessage());
						}
						break;
					default:
						$Itsharef = Itsharef::all();
						foreach ($Itsharef as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Itsharef, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}
	function itsharefApproval(){
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
							$join   = "LEFT JOIN tbl_approver ON (tbl_itsharefapproval.approver_id = tbl_approver.id) ";
							$Itsharefapproval = Itsharefapproval::find('all', array('joins'=>$join,'conditions' => array("itsharef_id=?",$id),'include' => array('approver'=>array('approvaltype')),"order"=>"tbl_approver.sequence"));
							foreach ($Itsharefapproval as &$result) {
								$approvaltype = $result->approver->approvaltype_id;
								$result		= $result->to_array();
								$result['approvaltype']=$approvaltype;
							}
							echo json_encode($Itsharefapproval, JSON_NUMERIC_CHECK);
						}else{
							$Itsharefapproval = new Itsharefapproval();
							echo json_encode($Itsharefapproval);
						}
						break;
					case 'find':
						$query=$this->post['query'];		
						if(isset($query['status'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join   = "LEFT JOIN tbl_approver ON (tbl_itsharefapproval.approver_id = tbl_approver.id) ";
							$dx = Itsharefapproval::find('first', array('joins'=>$join,'conditions' => array("itsharef_id=? and tbl_approver.employee_id = ?",$query['itsharef_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
							$Itsharef = Itsharef::find($query['itsharef_id']);
							// print_r($dx);
							if($dx->approver->isfinal==1){
								$data=array("jml"=>1);
							}else{
								$join   = "LEFT JOIN tbl_approver ON (tbl_itsharefapproval.approver_id = tbl_approver.id) ";
								$Itsharefapproval = Itsharefapproval::find('all', array('joins'=>$join,'conditions' => array("itsharef_id=? and ApprovalStatus<=1 and not tbl_approver.employee_id=?",$query['itsharef_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
								foreach ($Itsharefapproval as &$result) {
									$fullname	= $result->approver->employee->fullname;
									$result		= $result->to_array();
									$result['fullname']=$fullname;
								}
								$data=array("jml"=>count($Itsharefapproval));
							}						
						} else if(isset($query['pending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
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
								$department	= $result->employee->department->departmentname;
								$result		= $result->to_array();
								$result['fullname']=$fullname;
								$result['department']=$department;
							}
							$data=$Itsharef;
						} else if(isset($query['mypending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
							$Itsharef = Itsharef::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
							$jml=0;
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
							$data=array("jml"=>count($Itsharef));
						} else if(isset($query['filter'])){
							$join = "LEFT JOIN vwitsharefreport v on tbl_itsharef.id=v.id";
							$sel = 'tbl_itsharef.*, v.laststatus,v.personholding ';
							$Itsharef = Itsharef::find('all',array('joins'=>$join,'select'=>$sel,'include' => array('employee')));
							foreach ($Itsharef as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=$Itsharef;
						} else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Itsharefapproval = Itsharefapproval::create($data);
						$logger = new Datalogger("Itsharefapproval","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':
						$id = $this->post['id'];
						$Itsharefapproval = Itsharefapproval::find($id);
						$data=$Itsharefapproval->to_array();
						$Itsharefapproval->delete();
						$logger = new Datalogger("Itsharefapproval","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Itsharefapproval);
						break;
					case 'update':
						$doid = $this->post['id'];
						$data = $this->post['data'];
						$mode= $data['mode'];
						unset($data['id']);
						unset($data['depthead']);
						unset($data['fullname']);
						unset($data['department']);
						unset($data['approveddoc']);
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
						$Itsharef = Itsharef::find($doid);
						$join   = "LEFT JOIN tbl_approver ON (tbl_itsharefapproval.approver_id = tbl_approver.id) ";
						if (isset($data['mode'])){
							$Itsharefapproval = Itsharefapproval::find('first', array('joins'=>$join,'conditions' => array("itsharef_id=? and tbl_approver.employee_id=?",$doid,$Employee->id),'include' => array('approver'=>array('employee','approvaltype'))));
							unset($data['mode']);
						}else{
							$Itsharefapproval = Itsharefapproval::find($this->post['id'],array('include' => array('approver'=>array('employee','approvaltype'))));
						}
						foreach($data as $key=>$val) {
							if(($key !== 'approvalstatus') && ($key !== 'approvaldate') && ($key !== 'remarks') ) {
								// if(($key == 'isrepair') || ($key == 'isscrap')) {
									$value=(($val===0) || ($val==='0') || ($val==='false'))?false:((($val===1) || ($val==='1') || ($val==='true'))?true:$val);
								// }
								$Itsharef->$key=$value;
							}
						}
						$Itsharef->save();

						// unset($data['formtype']);
						
						// unset($data['accessrequested']);
						// unset($data['accesstype']);
						// unset($data['emailquota']);
						// unset($data['emaildomain']);
						// unset($data['listgroupmoderation']);
						
						// unset($data['web1']);
						// unset($data['web2']);

						// unset($data['newmailboxsize']);
						// unset($data['incomingsize']);
						// unset($data['outgoingsize']);

						// unset($data['typeofaccess']);

						// unset($data['emailgroupname']);
						// unset($data['membername']);

						unset($data['validto']);
						unset($data['validfrom']);

						unset($data['reason']);
						
						
						$olddata = $Itsharefapproval->to_array();
						foreach($data as $key=>$val){
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$Itsharefapproval->$key=$val;
						}
						$Itsharefapproval->save();
						$logger = new Datalogger("Itsharefapproval","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						if (isset($mode) && ($mode=='approve')){
							$Itsharef = Itsharef::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));
							$joinx   = "LEFT JOIN tbl_approver ON (tbl_itsharefapproval.approver_id = tbl_approver.id) ";					
							$nTrapproval = Itsharefapproval::find('first',array('joins'=>$joinx,'conditions' => array("itsharef_id=? and ApprovalStatus=0",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
							$username = $nTrapproval->approver->employee->loginname;
							$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
							// $Itsharefschedule=Trschedule::find('all',array('conditions'=>array("itsharef_id=?",$doid),'include'=>array('itsharef'=>array('employee'=>array('company','department','designation','grade','location')))));
							// $Itsharefticket=Trticket::find('all',array('conditions'=>array("itsharef_id=?",$doid),'include'=>array('itsharef'=>array('employee'=>array('company','department','designation','grade','location')))));
							$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Itsharef->employee->loginname)));
							$email=$usr->email;
							$superiorId=$Itsharef->depthead;
							$Superior = Employee::find($superiorId);
							$supAdb = Addressbook::find('first',array('conditions'=>array("username=?",$Superior->loginname)));
							$complete = false;
							$Itsharefhistory = new Itsharefhistory();
							$Itsharefhistory->date = date("Y-m-d h:i:s");
							$Itsharefhistory->fullname = $Employee->fullname;
							$Itsharefhistory->approvaltype = $Itsharefapproval->approver->approvaltype->approvaltype;
							$Itsharefhistory->remarks = $data['remarks'];
							$Itsharefhistory->itsharef_id = $doid;

							if($Itsharef->formtype == 1) {
								$title = 'Exchange - Internet Email';
							}else if($Itsharef->formtype == 2) {
								$title = 'Internet Access';
							}else if($Itsharef->formtype == 3) {
								$title = 'Increase Mailbox Size';
							}else if($Itsharef->formtype == 4) {
								$title = 'RD Web Access';
							}else if($Itsharef->formtype == 5) {
								$title = 'Email Group';
							}
							
							switch ($data['approvalstatus']){
								case '1':
									$Itsharef->requeststatus = 2;
									$emto=$email;$emname=$Itsharef->employee->fullname;
									$this->mail->Subject = "Online Approval System -> Need Rework";
									$red = 'Your '.$title.' require some rework :
											<br>Remarks from Approver : <font color="red">'.$data['remarks']."</font>";
									$Itsharefhistory->actiontype = 3;
									break;
								case '2':
									if ($Itsharefapproval->approver->isfinal == 1){
										$Itsharef->requeststatus = 3;
										$emto=$email;$emname=$Itsharef->employee->fullname;
										$this->mail->Subject = "Online Approval System -> Approval Completed";
										$red = '<p>Your '.$title.' request has been approved</p>';
													// '<p><b><span lang=EN-US style=\'color:#002060\'>Note : Please <u>forward</u> this electronic approval to your respective Human Resource Department.</span></b></p>';
										//delete unnecessary approver
										$Itsharefapproval = Itsharefapproval::find('all', array('joins'=>$join,'conditions' => array("itsharef_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
										foreach ($Itsharefapproval as $data) {
											if($data->approvalstatus==0){
												$logger = new Datalogger("Itsharefapproval","delete",json_encode($data->to_array()),"automatic remove unnecessary approver by system");
												$logger->SaveData();
												$data->delete();
											}
										}
										$complete =true;
									}
									else{
										$Itsharef->requeststatus = 1;
										$emto=$adb->email;$emname=$adb->fullname;
										$this->mail->Subject = 'Online Approval System -> new '.$title.' Request';
										$red = 'new '.$title.' Request awaiting for your approval:';
									}
									$Itsharefhistory->actiontype = 4;							
									break;
								case '3':
									$Itsharef->requeststatus = 4;
									$emto=$email;$emname=$Itsharef->employee->fullname;
									$Itsharefhistory->actiontype = 5;
									$this->mail->Subject = "Online Approval System -> Request Rejected";
									$red = 'Your '.$title.' Request has been rejected
											<br>Remarks from Approver : <font color="red">'.$data['remarks']."</font>";
									break;
								default:
									break;
							}
							$Itsharef->save();
							$Itsharefhistory->save();
							echo "email to :".$emto." ->".$emname;
							$this->mail->addAddress($emto, $emname);
							$ItsharefJ = Itsharef::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));



							$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$emname.',</span></p>
								<p class=MsoNormal><span style="color:#1F497D">'.$red.'</span></p>
								<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
								<table border=1 cellspacing=0 cellpadding=3 width=683>
								<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itsharef->employee->fullname.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itsharef->employee->sapid.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itsharef->employee->designation->designationname.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itsharef->employee->company->companyname.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itsharef->employee->location->location.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
								</table>';


							// $this->mailbody .='
							// 	<table border=1 cellspacing=0 cellpadding=3 width=683>
							// 	<tr>
							// 		<th><p class=MsoNormal>Access Type</p></th>
							// 		<th><p class=MsoNormal>Account Type</p></th>
							// 		<th><p class=MsoNormal>Valid From</p></th>
							// 		<th><p class=MsoNormal>Valid To</p></th>
							// 		<th><p class=MsoNormal>List Group</p></th>
							// 	</tr>
							// 	<tr style="height:22.5pt">
							// 		<td><p class=MsoNormal> '.$accessT.'</p></td>
							// 		<td><p class=MsoNormal> '.$accountT.'</p></td>
							// 		<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itsharef->validfrom)).'</p></td>
							// 		<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itsharef->validto)).'</p></td>
							// 		<td><p class=MsoNormal> '.$Itsharef->listgroup.'</p></td>
							// 	</tr>
							// 	';
							// $this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
							
								$this->mail->msgHTML($this->mailbody);
							if ($complete){
								$form = $Itsharef->formtype;
								$fileName = $this->generatePDFi($doid);
								$filePath = SITE_PATH.DS.$fileName;
								$Mailrecipient = Mailrecipient::find('all',array('conditions'=>array("module='IT' and company_list like ?","%".$ItsharefJ->employee->companycode."%")));
								foreach ($Mailrecipient as $data){
									$this->mail->AddCC($data->email);
								}
								$this->mail->addAttachment($filePath);
							}
							if (!$this->mail->send()) {
								$err = new Errorlog();
								$err->errortype = "ITMAIL Mail";
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
						echo json_encode($Spklapproval);
						break;
					default:
						$Itsharefapproval = Itsharefapproval::all();
						foreach ($Itsharefapproval as $result) {
							$result = $result->to_array();
						}
						echo json_encode($Itsharefapproval, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}

	function itsharefDetail(){
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
							$Itsharefdetail = Itsharefdetail::find('all', array('conditions' => array("itsharef_id=?",$id)));
							foreach ($Itsharefdetail as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Itsharefdetail, JSON_NUMERIC_CHECK);
						}else{
							$Itsharefdetail = new Itsharefdetail();
							echo json_encode($Itsharefdetail);
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							$Itsharefdetail = Itsharefdetail::find('all', array('conditions' => array("itsharef_id=?",$query['itsharef_id'])));
							$data=array("jml"=>count($Itsharefdetail));
						}else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						// $exprice = $data['unitprice'] * $data['qty'];
						// $data['extendedprice'] = $exprice;
						$Itsharefdetail = Itsharefdetail::create($data);
						$logger = new Datalogger("Itsharefdetail","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Itsharefdetail = Itsharefdetail::find($id);
						$data=$Itsharefdetail->to_array();
						$Itsharefdetail->delete();
						$logger = new Datalogger("Itsharefdetail","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Itsharefdetail);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						
						$Itsharefdetail = Itsharefdetail::find($id);
						$olddata = $Itsharefdetail->to_array();
						foreach($data as $key=>$val){
							$Itsharefdetail->$key=$val;
						}
						// $exprice = $Itsharefdetail->unitprice * $Itsharefdetail->qty;
						// $Itsharefdetail->extendedprice = $exprice;
						$Itsharefdetail->save();
						$logger = new Datalogger("Itsharefdetail","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						echo json_encode($Itsharefdetail);
						
						break;
					default:
						$Itsharefdetail = Itsharefdetail::all();
						foreach ($Itsharefdetail as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Itsharefdetail, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}
	function generatePDFi($id){
		$Itsharef = Itsharef::find($id);
		$Itsharefdetail = Itsharefdetail::find('all',array('conditions'=>array("itsharef_id=?",$id),'include'=>array('itsharef'=>array('employee'=>array('company','department','designation','grade','location')))));	
		
		// print_r($Itsharefdetail);

		// $form = $Itsharef->formtype;
		$superiorId=$Itsharef->depthead;
		$Superior = Employee::find($superiorId);
		$supAdb = Addressbook::find('first',array('conditions'=>array("username=?",$Superior->loginname)));
		$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Itsharef->employee->loginname)));
		$email=$usr->email;
		$fullname=$usr->fullname;

		$datefrom = date("d/m/Y",strtotime($Itsharef->validfrom));
		$dateto = date("d/m/Y",strtotime($Itsharef->validto));

		$joinx   = "LEFT JOIN tbl_approver ON (tbl_itsharefapproval.approver_id = tbl_approver.id) ";					
		$Itsharefapproval = Itsharefapproval::find('all',array('joins'=>$joinx,'conditions' => array("itsharef_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee','approvaltype'))));							
		
		// $ItsharefJ = Itsharef::find($id,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));

		// $Mailrecipient = Mailrecipient::find('all',array('conditions'=>array("module='IT' and company_list like ?","%".$ItsharefJ->employee->companycode."%")));
		// foreach ($Mailrecipient as $data){
		// 	print_r($data->email);
		// }

		//condition
			foreach ($Itsharefapproval as $data){
				if(($data->approver->approvaltype->id==29) || ($data->approver->employee_id==$Itsharef->depthead)){
					$deptheadname = $data->approver->employee->fullname;
					$deptheaddate = date("d/m/Y",strtotime($data->approvaldate));
				}
				if($data->approver->approvaltype->id==30) {
					$hrdname = $data->approver->employee->fullname;
					$hrddate = date("d/m/Y",strtotime($data->approvaldate));
				}
				if($data->approver->approvaltype->id==31) {
					$buheadname = $data->approver->employee->fullname;
					$buheaddate = date("d/m/Y",strtotime($data->approvaldate));
				}
				if($data->approver->approvaltype->id==32) {
					$itheadname = $data->approver->employee->fullname;
					$itheaddate = date("d/m/Y",strtotime($data->approvaldate));
				}
				if($data->approver->approvaltype->id==33) {
					$mdname = $data->approver->employee->fullname;
					$mddate = date("d/m/Y",strtotime($data->approvaldate));
				}
			}
			
		//end condition

		try {
			$excel = new COM("Excel.Application") or die ("ERROR: Unable to instantaniate COM!\r\n");
			$excel->Visible = true;

				$title = 'sharefolder';


				$file= SITE_PATH."/doc/it/template_sharefolder.xls";
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
				//condition
				// $string = $Itsharef->membername;

				// $expstring = explode(',',$string);
				// // $countstring = count($expstring)+29;

				// $getname = [];
				// $xlShiftDown=-4121;

				// foreach($Itsharefdetail as $p) {
				// 	$Worksheet->Rows(30)->Insert($xlShiftDown);
				// 	// echo json_encode($p->grantaccessto);
				// 	$Worksheet->Range("F29")->Value = $p->grantaccessto;

				// 	// $dataname = Employee::find($key);
				// 	// array_push($getname,$dataname->loginname);
				// }

				// $getemailname = [];
				// foreach($getname as $p => $key) {

				// 	$datamail = Addressbook::find('first',array('select'=> "CONCAT(fullname,' (',email,')' ) as name",'conditions' => array("username=?",$key)));

				// 	array_push($getemailname,$datamail->name);

				// }

				// $ss = implode(' | ',$getemailname);
				// print_r($ss);


				// $Worksheet->Range("F31")->Value = $ss;

				$Worksheet->Range("F36")->Value = strip_tags($Itsharef->remarks);

				$Worksheet->Range("H33")->Value = $datefrom;
				$Worksheet->Range("Q33")->Value = $dateto;


				$xlShiftDown=-4121;
				for ($a=29;$a<29+count($Itsharefdetail);$a++){
					$Worksheet->Rows($a+1)->Insert($xlShiftDown);
					$Worksheet->Range("F".$a)->Value = $Itsharefdetail[$a-29]->grantaccessto;
					$Worksheet->Range("Q".$a)->Value = ($Itsharefdetail[$a-29]->readonly == 1)?'x':'';
					$Worksheet->Range("R".$a)->Value = 'Read Only';
					$Worksheet->Range("U".$a)->Value = ($Itsharefdetail[$a-29]->change == 1)?'x':'';
					$Worksheet->Range("V".$a)->Value = 'Change';
					// $Worksheet->Range("F".$a)->Value = ($a-28).'. '.($getname[$a-29]);
				}
		

				//end condition
				
				// $Worksheet->Range("J27")->Value = $Itsharef->typeofaccess;
				// $Worksheet->Range("F34")->Value = $Itsharef->reason;

				// $Worksheet->Range("I56")->Value = $deptheadname;
				// $Worksheet->Range("I57")->Value = $deptheaddate;
				// $Worksheet->Range("P56")->Value = $buheadname;
				// $Worksheet->Range("P57")->Value = $buheaddate;
				// $Worksheet->Range("W56")->Value = $itheadname;
				// $Worksheet->Range("W57")->Value = $itheaddate;

				// $Worksheet->Range("C56")->Value = $fullname;
				// $Worksheet->Range("C57")->Value = date("d/m/Y",strtotime($Itsharef->createddate));


			$xlTypePDF = 0;
			$xlQualityStandard = 0;
			$fileName ='doc'.DS.'it'.DS.'pdf'.DS.$title.$Itsharef->employee->sapid.'_'.date("YmdHis").'.pdf';
			$fileName = str_replace("/","",$fileName);
			$path= SITE_PATH.'/doc'.DS.'it'.DS.'pdf'.DS.$title.$Itsharef->employee->sapid.'_'.date("YmdHis").'.pdf';
			if (file_exists($path)) {
			unlink($path);
			}
			$Worksheet->ExportAsFixedFormat($xlTypePDF, $path, $xlQualityStandard);
			$Itsharef->approveddoc=str_replace("\\","/",$fileName);
			$Itsharef->save();

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
	function itsharefHistory(){
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
							$Itsharefhistory = Itsharefhistory::find('all', array('conditions' => array("itsharef_id=?",$id)));
							foreach ($Itsharefhistory as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Itsharefhistory, JSON_NUMERIC_CHECK);
						}
						break;
					default:
						break;
				}
			}
		}
	}
}