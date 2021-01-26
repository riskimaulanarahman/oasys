<?php
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

Class Itimailmodule extends Application{
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
				case 'apiitimailbyemp':
					$this->itimailByEmp();
					break;
				case 'apiitimail':
					$this->itimail();
					break;
				case 'apiitimailapp':
					$this->itimailApproval();
					break;
				case 'apiitimailpdf':
					$id = $this->get['id'];
					// $this->generatePDF($id);
					$this->generatePDFi($id);
					break;
				case 'apiitimailhist':
					$this->itimailHistory();
					break;
				
				default:
					break;
			}
		}
	}
	
	function itimailByEmp(){
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
							$Itimail = Itimail::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Itimail as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Itimail, JSON_NUMERIC_CHECK);
						}else{
							echo json_encode(array());
						}
						break;
					case 'find':
						$query=$this->post['query'];					
						if(isset($query['status'])){
							switch ($query['status']){
								case 'waiting':
									$Itimail = Itimail::find('all', array('conditions' => array("employee_id=? and RequestStatus<3 and id<>?",$query['username'],$query['id']),'include' => array('employee')));
									foreach ($Itimail as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Itimail));
									break;
								default:
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
									$Itimail = Itimail::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Itimail as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Itimail));
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
							$Itimail = Itimail::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Itimail as &$result) {
								$fullname=$result->employee->fullname;
								$result = $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Itimail, JSON_NUMERIC_CHECK);
						}else{
							echo json_encode(array());
						}
						break;
				}
			}
		}
	}
	function itimail(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'byid':
						$id = $this->post['id'];
						$join = "LEFT JOIN vwitimailreport ON tbl_itimail.id = vwitimailreport.id";
						$select = "tbl_itimail.*,vwitimailreport.apprstatuscode";
						// $Itimail = Itimail::find($id, array('include' => array('employee'=>array('company','department','designation','location'))));
						$Itimail = Itimail::find($id, array('joins'=>$join,'select'=>$select,'include' => array('employee'=>array('company','department','designation'))));
						if ($Itimail){
							$fullname = $Itimail->employee->fullname;
							$bgbu = $Itimail->employee->companycode;
							$location = $Itimail->employee->location->location;
							$designation = $Itimail->employee->designation->designationname;
							$department = $Itimail->employee->department->departmentname;
							$data=$Itimail->to_array();
							$data['fullname']=$fullname;
							$data['bgbu']=$bgbu;
							// $data['listgroup']=$bgbu;
							$data['department']=$department;
							$data['officelocation']=$location;
							$data['designation']=$designation;
							echo json_encode($data, JSON_NUMERIC_CHECK);
						}else{
							$Itimail = new Itimail();
							echo json_encode($Itimail);
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
										$id= $query['itimail_id'];

										$Itimail = Itimail::find($id);

										$Employee = Employee::find('first', array('conditions' => array("id=?",$employee_id),"include"=>array("location","department","company")));

										print_r($Employee);

										$joins   = "LEFT JOIN tbl_approver ON (tbl_itimailapproval.approver_id = tbl_approver.id) LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id)";
										$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";	
										// if (($formtype=='2') || ($formtype=='3')){
										$Itimailapproval = Itimailapproval::find('all',array('joins'=>$joins,'conditions' => array("itimail_id=? and tbl_approver.approvaltype_id='30' ",$id)));	
										foreach ($Itimailapproval as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}
										$Itimailapprovalmd = Itimailapproval::find('all',array('joins'=>$joins,'conditions' => array("itimail_id=? and tbl_approver.approvaltype_id='33' ",$id)));	
										foreach ($Itimailapprovalmd as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}
										

										if (($formtype=='1')){

												if(count($Itimailapproval)==0){

													if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220") || ($Employee->department->sapcode=="13000090") || ($Employee->department->sapcode=="13000121") || ($Employee->company->sapcode=="NKF") || ($Employee->company->sapcode=="RND")){

														$ApproverCADKF = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id='30' and tbl_employee.location_id='1'")));
														if(count($ApproverCADKF)>0){
																$Itimailapproval = new Itimailapproval();
																$Itimailapproval->itimail_id = $Itimail->id;
																$Itimailapproval->approver_id = $ApproverCADKF->id;
																$Itimailapproval->save();
																$logger = new Datalogger("Itimailapproval","add","Add Approval",json_encode($Itimailapproval->to_array()));
																$logger->SaveData();
														}
													} else {
															$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=30 and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
														if(count($Approver2)>0){
															$Itimailapproval = new Itimailapproval();
															$Itimailapproval->itimail_id = $Itimail->id;
															$Itimailapproval->approver_id = $Approver2->id;
															$Itimailapproval->save();
														}
													}


													if(count($Itimailapprovalmd)>0){
														$dx = Itimailapproval::find('all',array('joins'=>$joins,'conditions' => array("itimail_id=? and tbl_approver.approvaltype_id=33",$id)));	
														foreach ($dx as $result) {
															$result->delete();
															$logger = new Datalogger("Itimailapproval","delete",json_encode($result->to_array()),"delete Approval Internet Email");
															$logger->SaveData();
														}
													}
												}
													
										} else if(($formtype=='4')) {
											if(count($Itimailapproval)>0){
												$dx = Itimailapproval::find('all',array('joins'=>$joins,'conditions' => array("itimail_id=? and tbl_approver.approvaltype_id=30",$id)));	
												foreach ($dx as $result) {
													$result->delete();
													$logger = new Datalogger("Itimailapproval","delete",json_encode($result->to_array()),"delete Approval RD Web");
													$logger->SaveData();
												}
											}

											if(count($Itimailapprovalmd)==0){
												$ApproverCADKF = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id='33'")));
												if(count($ApproverCADKF)>0){
														$Itimailapproval = new Itimailapproval();
														$Itimailapproval->itimail_id = $id;
														$Itimailapproval->approver_id = $ApproverCADKF->id;
														$Itimailapproval->save();
														$logger = new Datalogger("Itimailapproval","add","Add Approval",json_encode($Itimailapproval->to_array()));
														$logger->SaveData();
												}
												
											}
										} else {
											if(count($Itimailapproval)>0){
												$dx = Itimailapproval::find('all',array('joins'=>$joins,'conditions' => array("itimail_id=? and tbl_approver.approvaltype_id=30",$id)));	
												foreach ($dx as $result) {
													$result->delete();
													$logger = new Datalogger("Itimailapproval","delete",json_encode($result->to_array()),"delete Approval other form");
													$logger->SaveData();
												}
											}

											if(count($Itimailapprovalmd)>0){
												$dx = Itimailapproval::find('all',array('joins'=>$joins,'conditions' => array("itimail_id=? and tbl_approver.approvaltype_id=33",$id)));	
												foreach ($dx as $result) {
													$result->delete();
													$logger = new Datalogger("Itimailapproval","delete",json_encode($result->to_array()),"delete Approval other form");
													$logger->SaveData();
												}
											}
										}
										
								break;
								case 'appreq':
									$formtype = $query['formtype'];
									$employee_id = $query['employee_id'];
									$id= $query['itimail_id'];
									$accessreq= $query['accessreq'];

									$Itimail = Itimail::find($id);

									$Employee = Employee::find('first', array('conditions' => array("id=?",$employee_id),"include"=>array("location","department","company")));

									// print_r($Employee);

									$joins   = "LEFT JOIN tbl_approver ON (tbl_itimailapproval.approver_id = tbl_approver.id) LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id)";
									$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";	
									// if (($formtype=='2') || ($formtype=='3')){
									$Itimailapproval = Itimailapproval::find('all',array('joins'=>$joins,'conditions' => array("itimail_id=? and tbl_approver.approvaltype_id='30' ",$id)));	
									foreach ($Itimailapproval as &$result) {
										$result		= $result->to_array();
										$result['no']=1;

									}
									$Itimailapprovalmd = Itimailapproval::find('all',array('joins'=>$joins,'conditions' => array("itimail_id=? and tbl_approver.approvaltype_id='33' ",$id)));	
									foreach ($Itimailapprovalmd as &$result) {
										$result		= $result->to_array();
										$result['no']=1;

									}
									
									if(isset($query['accessreq'])) {

										if ($accessreq == 2){
											$Appl = Itimailapproval::find('all',array('joins'=>$joins,'conditions' => array("itimail_id=? and tbl_approver.approvaltype_id=30 and tbl_employee.location_id ='1' ",$id)));
											//$App1 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id='30' ")));
											if(count($App1)>0) {
												
												
											}else {
												$dx = Itimailapproval::find('all',array('joins'=>$joins,'conditions' => array("itimail_id=? and tbl_approver.approvaltype_id=30",$id)));	
												foreach ($dx as $result) {
													$result->delete();
													$logger = new Datalogger("Itimailapproval","delete",json_encode($result->to_array()),"delete Approval internet email");
													$logger->SaveData();
												}
												
												$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id='30' and tbl_employee.location_id ='1' ")));
												if(count($Approver2)>0){
													$Itimailapproval = new Itimailapproval();
													$Itimailapproval->itimail_id = $Itimail->id;
													$Itimailapproval->approver_id = $Approver2->id;
													$Itimailapproval->save();
												}
											}

												echo "1";

												print_r($Approver2);


												// }
													
										} else {
											echo "2";

											// if(count($Itimailapproval)>0){


												if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220") || ($Employee->department->sapcode=="13000090") || ($Employee->department->sapcode=="13000121") || ($Employee->company->sapcode=="NKF") || ($Employee->company->sapcode=="RND")){
													$Appl = Itimailapproval::find('all',array('joins'=>$joins,'conditions' => array("itimail_id=? and tbl_approver.approvaltype_id=30 and tbl_employee.location_id ='1' ",$id)));
													if (count($Appl)>0){
														
													}else{
														$dx = Itimailapproval::find('all',array('joins'=>$joins,'conditions' => array("itimail_id=? and tbl_approver.approvaltype_id=30",$id)));	
														foreach ($dx as $result) {
															$result->delete();
															$logger = new Datalogger("Itimailapproval","delete",json_encode($result->to_array()),"delete Approval not internet email");
															$logger->SaveData();
														}
												
														$ApproverCADKF = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id='30' and tbl_employee.location_id='1'")));
														if(count($ApproverCADKF)>0){
																$Itimailapproval = new Itimailapproval();
																$Itimailapproval->itimail_id = $Itimail->id;
																$Itimailapproval->approver_id = $ApproverCADKF->id;
																$Itimailapproval->save();
																$logger = new Datalogger("Itimailapproval","add","Add Approval",json_encode($Itimailapproval->to_array()));
																$logger->SaveData();
														}
													}
													
												} else {
													$Appl = Itimailapproval::find('all',array('joins'=>$joins,'conditions' => array("itimail_id=? and tbl_approver.approvaltype_id=30 and tbl_employee.company_id=? and not(tbl_employee.location_id='1') ",$id,$Employee->company_id)));
													if (count($Appl)>0){
													}else{
														$dx = Itimailapproval::find('all',array('joins'=>$joins,'conditions' => array("itimail_id=? and tbl_approver.approvaltype_id=30",$id)));	
														foreach ($dx as $result) {
															$result->delete();
															$logger = new Datalogger("Itimailapproval","delete",json_encode($result->to_array()),"delete Approval not internet email 2");
															$logger->SaveData();
														}
												
														$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=30 and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
														if(count($Approver2)>0){
															$Itimailapproval = new Itimailapproval();
															$Itimailapproval->itimail_id = $Itimail->id;
															$Itimailapproval->approver_id = $Approver2->id;
															$Itimailapproval->save();
														}
													}
													
												}
										}
									}

									
							break;
								case "reschedule":
									$id = $query['itimail_id'];
									$Itimail = Itimail::find($id,array('include'=>array('employee'=>array('company','department','designation','grade'))));
									$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Itimail->employee->loginname)));
									$email=$usr->email;
									
									$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$usr->fullname.',</span></p>
										<p class=MsoNormal><span style="color:#1F497D">Your Request Form has been rescheduled by HR to match with your actual travel schedule:</span></p>
										<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itimail->employee->fullname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itimail->employee->sapid.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itimail->employee->designation->designationname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itimail->employee->company->companyname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itimail->employee->location->location.'</b></p></td></tr>
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
									$Itimail = Itimail::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Itimail as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Itimail));
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
							// $Itimailnew = Itimail::find('first',array('select' => "CONCAT('Itimail/','".$Employee->companycode."','/',YEAR(CURDATE()),'/',LPAD(MONTH(CURDATE()), 2, '0'),'/',LPAD(CASE when max(substring(wonumber,-4,4)) is null then 1 else max(substring(wonumber,-4,4))+1 end,4,'0')) as wonumber","conditions"=>array("substring(wonumber,7,".strlen($Employee->companycode).")=? and substring(wonumber,".(strlen($Employee->companycode)+8).",4)=YEAR(CURDATE())",$Employee->companycode)));
							// $data['wonumber']=$Itimailnew->wonumber;
							$Itimail = Itimail::create($data);
							$data=$Itimail->to_array();
							$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";

								// $Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=30")));
								// if(count($Approver)>0){
								// 	$Itimailapproval = new Itimailapproval();
								// 	$Itimailapproval->itimail_id = $Itimail->id;
								// 	$Itimailapproval->approver_id = $Approver->id;
								// 	$Itimailapproval->save();
								// }
								// $Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=31")));
								// if(count($Approver2)>0){
								// 	$Itimailapproval = new Itimailapproval();
								// 	$Itimailapproval->itimail_id = $Itimail->id;
								// 	$Itimailapproval->approver_id = $Approver2->id;
								// 	$Itimailapproval->save();
								// }
								$Approver3 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=32")));
								if(count($Approver3)>0){
									$Itimailapproval = new Itimailapproval();
									$Itimailapproval->itimail_id = $Itimail->id;
									$Itimailapproval->approver_id = $Approver3->id;
									$Itimailapproval->save();
								}

								if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220") || ($Employee->department->sapcode=="13000090") || ($Employee->department->sapcode=="13000121") || ($Employee->company->sapcode=="NKF") || ($Employee->company->sapcode=="RND")){
									// $Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=30 and tbl_employee.location_id='1'")));
									// if(count($Approver2)>0){
									// 	$Itimailapproval = new Itimailapproval();
									// 	$Itimailapproval->itimail_id = $Itimail->id;
									// 	$Itimailapproval->approver_id = $Approver2->id;
									// 	$Itimailapproval->save();
									// }
									
									if(($Employee->department->sapcode!="13000090") && ($Employee->department->sapcode!="13000121") && ($Employee->company->sapcode!="NKF") && ($Employee->company->sapcode!="RND")  && ($Employee->company->companycode!="BCL")  && ($Employee->company->companycode!="LDU")){
										if(($Employee->level_id!=4) && ($Employee->level_id!=6) ){
											$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=31 and tbl_employee.companycode='KPSI' and not(tbl_employee.id=?)",$Employee->id)));
											if(count($Approver)>0){
												$Itimailapproval = new Itimailapproval();
												$Itimailapproval->itimail_id = $Itimail->id;
												$Itimailapproval->approver_id = $Approver->id;
												$Itimailapproval->save();
											}
										}
									}else{
										$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=31 and tbl_employee.company_id=? and not tbl_employee.companycode='KPSI'  and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
										if(count($Approver)>0){
											$Itimailapproval = new Itimailapproval();
											$Itimailapproval->itimail_id = $Itimail->id;
											$Itimailapproval->approver_id = $Approver->id;
											$Itimailapproval->save();
										}else{
											$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=31 and tbl_employee.company_id=? and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
											if(count($Approver)>0){
												$Itimailapproval = new Itimailapproval();
												$Itimailapproval->itimail_id = $Itimail->id;
												$Itimailapproval->approver_id = $Approver->id;
												$Itimailapproval->save();
											}
										}
									}	
								}else{
									$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=31 and not tbl_employee.companycode='KPSI' and tbl_employee.company_id=? and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
									if(count($Approver)>0){
										$Itimailapproval = new Itimailapproval();
										$Itimailapproval->itimail_id = $Itimail->id;
										$Itimailapproval->approver_id = $Approver->id;
										$Itimailapproval->save();
									}else{
										$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=31 and tbl_employee.company_id=? and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
										if(count($Approver)>0){
											$Itimailapproval = new Itimailapproval();
											$Itimailapproval->itimail_id = $Itimail->id;
											$Itimailapproval->approver_id = $Approver->id;
											$Itimailapproval->save();
										}
									}

									// $Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=30 and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
									// if(count($Approver2)>0){
									// 	$Itimailapproval = new Itimailapproval();
									// 	$Itimailapproval->itimail_id = $Itimail->id;
									// 	$Itimailapproval->approver_id = $Approver2->id;
									// 	$Itimailapproval->save();
									// }
								}

							$Iteihistory = new Itimailhistory();
							$Iteihistory->date = date("Y-m-d h:i:s");
							$Iteihistory->fullname = $Employee->fullname;
							$Iteihistory->approvaltype = "Originator";
							$Iteihistory->itimail_id = $Itimail->id;
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
							$Itimail = Itimail::find($id);
							if ($Itimail->requeststatus==0){
								$approval = Itimailapproval::find("all",array('conditions' => array("itimail_id=?",$id)));
								foreach ($approval as $delr){
									$delr->delete();
								}
								// $detail = Trschedule::find("all",array('conditions' => array("itimail_id=?",$id)));
								// foreach ($detail as $delr){
								// 	$delr->delete();
								// }
								// $detail = Trticket::find("all",array('conditions' => array("itimail_id=?",$id)));
								// foreach ($detail as $delr){
								// 	$delr->delete();
								// }
								$hist = Itimailhistory::find("all",array('conditions' => array("itimail_id=?",$id)));
								foreach ($hist as $delr){
									$delr->delete();
								}
								$data = $Itimail->to_array();
								$Itimail->delete();
								$logger = new Datalogger("ITIMAIL","delete",json_encode($data),null);
								$logger->SaveData();
								echo json_encode($Itimail);
							} else {
								$data = array("status"=>"error","message"=>"You can't delete submitted request");
								echo json_encode($data);
							}
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "DeleteITEIE";
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
							$Itimail = Itimail::find($id,array('include'=>array('employee'=>array('company','department','designation','grade'))));
							$olddata = $Itimail->to_array();
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
									$Itimail->$key=$value;
								}
								$Itimail->save();
								
								if (isset($data['depthead'])){
									$joins   = "LEFT JOIN tbl_approver ON (tbl_itimailapproval.approver_id = tbl_approver.id) ";					
									$dx = Itimailapproval::find('all',array('joins'=>$joins,'conditions' => array("itimail_id=? and tbl_approver.approvaltype_id=29 and not(tbl_approver.employee_id=?)",$id,$depthead)));	
									foreach ($dx as $result) {
										//delete same type dept head approver
										$result->delete();
										$logger = new Datalogger("Itimailapproval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
									}
									$joins   = "LEFT JOIN tbl_approver ON (tbl_itimailapproval.approver_id = tbl_approver.id) ";					
									$Itimailapproval = Itimailapproval::find('all',array('joins'=>$joins,'conditions' => array("itimail_id=? and tbl_approver.employee_id=?",$id,$depthead)));	
									foreach ($Itimailapproval as &$result) {
										$result		= $result->to_array();
										$result['no']=1;
									}			
									if(count($Itimailapproval)==0){ 
										$Approver = Approver::find('first',array('conditions'=>array("module='IT' and employee_id=? and approvaltype_id=29",$depthead)));
										if(count($Approver)>0){
											$Itimailapproval = new Itimailapproval();
											$Itimailapproval->itimail_id = $Itimail->id;
											$Itimailapproval->approver_id = $Approver->id;
											$Itimailapproval->save();
										}else{
											$approver = new Approver();
											$approver->module = "IT";
											$approver->employee_id=$depthead;
											$approver->sequence=1;
											$approver->approvaltype_id = 29;
											$approver->isfinal = false;
											$approver->save();
											$Itimailapproval = new Itimailapproval();
											$Itimailapproval->itimail_id = $Itimail->id;
											$Itimailapproval->approver_id = $approver->id;
											$Itimailapproval->save();
										}
									}
									
								}
								
								if($data['requeststatus']==1){
									$Itimailapproval = Itimailapproval::find('all', array('conditions' => array("itimail_id=?",$id)));					
									foreach($Itimailapproval as $data){
										$data->approvalstatus=0;
										$data->save();
									}
									$joinx   = "LEFT JOIN tbl_approver ON (tbl_itimailapproval.approver_id = tbl_approver.id) ";					
									$Itimailapproval = Itimailapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and itimail_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
									$username = $Itimailapproval->approver->employee->loginname;
									$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
									$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Itimail->employee->loginname)));
									$email=$usr->email;

									if($Itimail->formtype == 1) {
										$title = 'Exchange - Internet Email';
									}else if($Itimail->formtype == 2) {
										$title = 'Internet Access';
									}else if($Itimail->formtype == 3) {
										$title = 'Increase Mailbox Size';
									}else if($Itimail->formtype == 4) {
										$title = 'RD Web Access';
									}else if($Itimail->formtype == 5) {
										$title = 'Email Group';
									}

									$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$adb->fullname.',</span></p>
										<p class=MsoNormal><span style="color:#1F497D">new '.$title.' Request is awaiting for your approval:</span></p>
										<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itimail->employee->fullname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itimail->employee->sapid.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itimail->employee->designation->designationname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itimail->employee->company->companyname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itimail->employee->location->location.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
										</table>
										<br>

										';
									
									if($Itimail->formtype == 1) {

										if($Itimail->accessrequested == 1) {
											$accessR = 'Exchange (non-Internet) Email';
										}else if($Itimail->accessrequested == 2) {
											$accessR = 'Internet Email';
										}else if($Itimail->accessrequested == 3) {
											$accessR = 'Change Domain';
										}else {
											$accessR = '';
										}

										if($Itimail->accesstype == 1) {
											$accessT = 'Terminal Server (TS) User Account';
										}else if($Itimail->accesstype == 2) {
											$accessT = 'Non-TS Account';
										}else {
											$accessT = '';
										}
	
										if($Itimail->accounttype == 1) {
											$accountT = 'Permanent';
										}else if($Itimail->accounttype == 2) {
											$accountT = 'Temporary';
										}else {
											$accountT = '';
										}

										if($Itimail->emailquota == 1) {
											$emailQ = '250MB';
										}else if($Itimail->emailquota == 2) {
											$emailQ = '500MB';
										}else if($Itimail->emailquota == 3) {
											$emailQ = '1000MB';
										}else if($Itimail->emailquota == 4) {
											$emailQ = '1500MB';
										}else if($Itimail->emailquota == 5) {
											$emailQ = '2000MB';
										}else {
											$emailQ = '';
										}

										if($Itimail->emaildomain == 1) {
											$emailD = 'itci-hutani.com';
										}else if($Itimail->emaildomain == 2) {
											$emailD = 'kalimantan-prima.com';
										}else if($Itimail->emaildomain == 3) {
											$emailD = 'balikpapanchip.com';
										}else if($Itimail->emaildomain == 4) {
											$emailD = 'lajudinamika.com';
										}else if($Itimail->emaildomain == 5) {
											$emailD = 'ptadindo.com';
										}else if($Itimail->emaildomain == 6) {
											$emailD = 'D1.LCL';
										}else {
											$emailD = '';
										}

										$listmod = Listmod::find('first',array('conditions'=>array("id=?",$Itimail->listgroupmoderation)));

										$this->mailbody .='	
											
											<table border=1 cellspacing=0 cellpadding=3 width=683>
											<tr>
												<th><p class=MsoNormal>Access Requested</p></th>
												<th><p class=MsoNormal>Access Type</p></th>
												<th><p class=MsoNormal>Account Type</p></th>
												<th><p class=MsoNormal>Email Quota </p></th>
												<th><p class=MsoNormal>Email Domain </p></th>
												<th><p class=MsoNormal>List Group</p></th>
												<th><p class=MsoNormal>List Group Moderation</p></th>
												<th><p class=MsoNormal>Valid From</p></th>
												<th><p class=MsoNormal>Valid To</p></th>
											</tr>
											<tr style="height:22.5pt">
												<td><p class=MsoNormal> '.$accessR.'</p></td>
												<td><p class=MsoNormal> '.$accessT.'</p></td>
												<td><p class=MsoNormal> '.$accountT.'</p></td>
												<td><p class=MsoNormal> '.$emailQ.'</p></td>
												<td><p class=MsoNormal> '.$emailD.'</p></td>
												<td><p class=MsoNormal> '.$Itimail->listgroup.'</p></td>
												<td><p class=MsoNormal> '.$listmod->mod.'</p></td>
												<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validfrom)).'</p></td>
												<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validto)).'</p></td>
											</tr>
											';

										$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
										
									} else if($Itimail->formtype == 2) {
										$this->mailbody .='	
											
											<table border=1 cellspacing=0 cellpadding=3 width=683>
											<tr>
												<th><p class=MsoNormal>http:// (A)</p></th>
												<th><p class=MsoNormal>http:// (B)</p></th>
												<th><p class=MsoNormal>Valid From</p></th>
												<th><p class=MsoNormal>Valid To</p></th>
											</tr>
											<tr style="height:22.5pt">
												<td><p class=MsoNormal> '.$Itimail->web1.'</p></td>
												<td><p class=MsoNormal> '.$Itimail->web2.'</p></td>
												<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validfrom)).'</p></td>
												<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validto)).'</p></td>
											</tr>
											';

										$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
										
									} else if($Itimail->formtype == 3) {
										
										if($Itimail->newmailboxsize == 1) {
											$newmailbox = '256MB';
										}else if($Itimail->newmailboxsize == 2) {
											$newmailbox = '512MB';
										}else if($Itimail->newmailboxsize == 3) {
											$newmailbox = '1GB';
										}else if($Itimail->newmailboxsize == 4) {
											$newmailbox = '1.5GB';
										}else if($Itimail->newmailboxsize == 5) {
											$newmailbox = '2GB';
										}else if($Itimail->newmailboxsize == 6) {
											$newmailbox = '3GB';
										}else if($Itimail->newmailboxsize == 7) {
											$newmailbox = '4GB';
										}else if($Itimail->newmailboxsize == 8) {
											$newmailbox = '5GB';
										}else if($Itimail->newmailboxsize == 9) {
											$newmailbox = '6GB';
										}else if($Itimail->newmailboxsize == 10) {
											$newmailbox = '7GB';
										}else if($Itimail->newmailboxsize == 11) {
											$newmailbox = '8GB';
										}else if($Itimail->newmailboxsize == 12) {
											$newmailbox = '9GB';
										}else if($Itimail->newmailboxsize == 13) {
											$newmailbox = '10GB';
										}else {
											$newmailbox = '';
										}

										if($Itimail->incomingsize == 1) {
											$incoming = '5MB';
										}else if($Itimail->incomingsize == 2) {
											$incoming = '10MB';
										}else if($Itimail->incomingsize == 3) {
											$incoming = '15MB';
										}else if($Itimail->incomingsize == 4) {
											$incoming = '20MB';
										}else if($Itimail->incomingsize == 5) {
											$incoming = '25MB';
										}else if($Itimail->incomingsize == 6) {
											$incoming = '30MB';
										}else if($Itimail->incomingsize == 7) {
											$incoming = '35MB';
										}else if($Itimail->incomingsize == 8) {
											$incoming = '40MB';
										}else if($Itimail->incomingsize == 9) {
											$incoming = '45MB';
										}else if($Itimail->incomingsize == 10) {
											$incoming = '50MB';
										}else if($Itimail->incomingsize == 11) {
											$incoming = '55MB';
										}else if($Itimail->incomingsize == 12) {
											$incoming = '60MB';
										}else if($Itimail->incomingsize == 13) {
											$incoming = '65MB';
										}else if($Itimail->incomingsize == 14) {
											$incoming = '70MB';
										}else if($Itimail->incomingsize == 15) {
											$incoming = '75MB';
										}else if($Itimail->incomingsize == 16) {
											$incoming = '80MB';
										}else if($Itimail->incomingsize == 17) {
											$incoming = '85MB';
										}else if($Itimail->incomingsize == 18) {
											$incoming = '90MB';
										}else if($Itimail->incomingsize == 19) {
											$incoming = '95MB';
										}else if($Itimail->incomingsize == 20) {
											$incoming = '100MB';
										}else {
											$incoming = '';
										}

										if($Itimail->outgoingsize == 1) {
											$outgoing = '5MB';
										}else if($Itimail->outgoingsize == 2) {
											$outgoing = '10MB';
										}else if($Itimail->outgoingsize == 3) {
											$outgoing = '15MB';
										}else if($Itimail->outgoingsize == 4) {
											$outgoing = '20MB';
										}else if($Itimail->outgoingsize == 5) {
											$outgoing = '25MB';
										}else if($Itimail->outgoingsize == 6) {
											$outgoing = '30MB';
										}else if($Itimail->outgoingsize == 7) {
											$outgoing = '35MB';
										}else if($Itimail->outgoingsize == 8) {
											$outgoing = '40MB';
										}else if($Itimail->outgoingsize == 9) {
											$outgoing = '45MB';
										}else if($Itimail->outgoingsize == 10) {
											$outgoing = '50MB';
										}else if($Itimail->outgoingsize == 11) {
											$outgoing = '55MB';
										}else if($Itimail->outgoingsize == 12) {
											$outgoing = '60MB';
										}else if($Itimail->outgoingsize == 13) {
											$outgoing = '65MB';
										}else if($Itimail->outgoingsize == 14) {
											$outgoing = '70MB';
										}else if($Itimail->outgoingsize == 15) {
											$outgoing = '75MB';
										}else if($Itimail->outgoingsize == 16) {
											$outgoing = '80MB';
										}else if($Itimail->outgoingsize == 17) {
											$outgoing = '85MB';
										}else if($Itimail->outgoingsize == 18) {
											$outgoing = '90MB';
										}else if($Itimail->outgoingsize == 19) {
											$outgoing = '95MB';
										}else if($Itimail->outgoingsize == 20) {
											$outgoing = '100MB';
										}else {
											$outgoing = '';
										}

										$this->mailbody .='	
											
											<table border=1 cellspacing=0 cellpadding=3 width=683>
											<tr>
												<th><p class=MsoNormal>New Mailbox Size</p></th>
												<th><p class=MsoNormal>Outgoing Size</p></th>
												<th><p class=MsoNormal>Incoming Size</p></th>
												<th><p class=MsoNormal>Valid From</p></th>
												<th><p class=MsoNormal>Valid To</p></th>
											</tr>
											<tr style="height:22.5pt">
												<td><p class=MsoNormal> '.$newmailbox.'</p></td>
												<td><p class=MsoNormal> '.$incoming.'</p></td>
												<td><p class=MsoNormal> '.$outgoing.'</p></td>
												<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validfrom)).'</p></td>
												<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validto)).'</p></td>
											</tr>
											';

										$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
										
									} else if($Itimail->formtype == 4) {
										$this->mailbody .='	
											
											<table border=1 cellspacing=0 cellpadding=3 width=683>
											<tr>
												<th><p class=MsoNormal>RDP to TS</p></th>
												<th><p class=MsoNormal>Example of usage</p></th>
												<th><p class=MsoNormal>Valid From</p></th>
												<th><p class=MsoNormal>Valid To</p></th>
											</tr>
											<tr style="height:22.5pt">
												<td><p class=MsoNormal> '.$Itimail->typeofaccess.'</p></td>
												<td><p class=MsoNormal> access email, open & edit attachments/ documents, department shared folders, corporate portals, SAP GUI</p></td>
												<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validfrom)).'</p></td>
												<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validto)).'</p></td>
											</tr>
											';

										$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
										
									} else if($Itimail->formtype == 5) {

										$string = $Itimail->membername;

										$expstring = explode(',',$string);
										// $countstring = count($expstring)+29;

										$getname = [];
										foreach($expstring as $p => $key) {
											$dataname = Employee::find($key);
											array_push($getname,$dataname->loginname);
										}

										$getemailname = [];
										foreach($getname as $p => $key) {

											$datamail = Addressbook::find('first',array('select'=> "CONCAT(fullname,' (',email,')' ) as name",'conditions' => array("username=?",$key)));

											array_push($getemailname,$datamail->name);

										}

										$ss = implode(' | ',$getemailname);

										$this->mailbody .='	
											
											<table border=1 cellspacing=0 cellpadding=3 width=683>
											<tr>
												<th><p class=MsoNormal>Email Group Name</p></th>
												<th><p class=MsoNormal>Member Name</p></th>
												<th><p class=MsoNormal>Valid From</p></th>
												<th><p class=MsoNormal>Valid To</p></th>
											</tr>
											<tr style="height:22.5pt">
												<td><p class=MsoNormal> '.$Itimail->emailgroupname.'</p></td>
												<td><p class=MsoNormal> '.$ss.'</p></td>
												<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validfrom)).'</p></td>
												<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validto)).'</p></td>
											</tr>
											';

										$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
										
									}
									
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
									$Itimailhistory = new Itimailhistory();
									$Itimailhistory->date = date("Y-m-d h:i:s");
									$Itimailhistory->fullname = $Employee->fullname;
									$Itimailhistory->itimail_id = $id;
									$Itimailhistory->approvaltype = "Originator";
									$Itimailhistory->actiontype = 2;
									$Itimailhistory->save();
								}else{
									$Itimailhistory = new Itimailhistory();
									$Itimailhistory->date = date("Y-m-d h:i:s");
									$Itimailhistory->fullname = $Employee->fullname;
									$Itimailhistory->itimail_id = $id;
									$Itimailhistory->approvaltype = "Originator";
									$Itimailhistory->actiontype = 1;
									$Itimailhistory->save();
								}
								$logger = new Datalogger("ITIMAIL","update",json_encode($olddata),json_encode($data));
								$logger->SaveData();
							}
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "UpdateITEIE";
							$err->errordate = date("Y-m-d h:i:s");
							$err->errormessage = $e->getMessage();
							$err->user = $this->currentUser->username;
							$err->ip = $this->ip;
							$err->save();
							$data = array("status"=>"error","message"=>$e->getMessage());
						}
						break;
					default:
						$Itimail = Itimail::all();
						foreach ($Itimail as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Itimail, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}
	function itimailApproval(){
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
							$join   = "LEFT JOIN tbl_approver ON (tbl_itimailapproval.approver_id = tbl_approver.id) ";
							$Itimailapproval = Itimailapproval::find('all', array('joins'=>$join,'conditions' => array("itimail_id=?",$id),'include' => array('approver'=>array('approvaltype')),"order"=>"tbl_approver.sequence"));
							foreach ($Itimailapproval as &$result) {
								$approvaltype = $result->approver->approvaltype_id;
								$result		= $result->to_array();
								$result['approvaltype']=$approvaltype;
							}
							echo json_encode($Itimailapproval, JSON_NUMERIC_CHECK);
						}else{
							$Itimailapproval = new Itimailapproval();
							echo json_encode($Itimailapproval);
						}
						break;
					case 'find':
						$query=$this->post['query'];		
						if(isset($query['status'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join   = "LEFT JOIN tbl_approver ON (tbl_itimailapproval.approver_id = tbl_approver.id) ";
							$dx = Itimailapproval::find('first', array('joins'=>$join,'conditions' => array("itimail_id=? and tbl_approver.employee_id = ?",$query['itimail_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
							$Itimail = Itimail::find($query['itimail_id']);
							// print_r($dx);
							if($dx->approver->isfinal==1){
								$data=array("jml"=>1);
							}else{
								$join   = "LEFT JOIN tbl_approver ON (tbl_itimailapproval.approver_id = tbl_approver.id) ";
								$Itimailapproval = Itimailapproval::find('all', array('joins'=>$join,'conditions' => array("itimail_id=? and ApprovalStatus<=1 and not tbl_approver.employee_id=?",$query['itimail_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
								foreach ($Itimailapproval as &$result) {
									$fullname	= $result->approver->employee->fullname;
									$result		= $result->to_array();
									$result['fullname']=$fullname;
								}
								$data=array("jml"=>count($Itimailapproval));
							}						
						} else if(isset($query['pending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
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
								$department	= $result->employee->department->departmentname;
								$result		= $result->to_array();
								$result['fullname']=$fullname;
								$result['department']=$department;
							}
							$data=$Itimail;
						} else if(isset($query['mypending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
							$Itimail = Itimail::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
							$jml=0;
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
							$data=array("jml"=>count($Itimail));
						} else if(isset($query['filter'])){
							$join = "LEFT JOIN vwitimailreport v on tbl_itimail.id=v.id";
							$sel = 'tbl_itimail.*, v.laststatus,v.personholding ';
							$Itimail = Itimail::find('all',array('joins'=>$join,'select'=>$sel,'include' => array('employee')));
							foreach ($Itimail as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=$Itimail;
						} else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Itimailapproval = Itimailapproval::create($data);
						$logger = new Datalogger("Itimailapproval","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':
						$id = $this->post['id'];
						$Itimailapproval = Itimailapproval::find($id);
						$data=$Itimailapproval->to_array();
						$Itimailapproval->delete();
						$logger = new Datalogger("Itimailapproval","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Itimailapproval);
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
						$Itimail = Itimail::find($doid);
						$join   = "LEFT JOIN tbl_approver ON (tbl_itimailapproval.approver_id = tbl_approver.id) ";
						if (isset($data['mode'])){
							$Itimailapproval = Itimailapproval::find('first', array('joins'=>$join,'conditions' => array("itimail_id=? and tbl_approver.employee_id=?",$doid,$Employee->id),'include' => array('approver'=>array('employee','approvaltype'))));
							unset($data['mode']);
						}else{
							$Itimailapproval = Itimailapproval::find($this->post['id'],array('include' => array('approver'=>array('employee','approvaltype'))));
						}
						foreach($data as $key=>$val) {
							if(($key !== 'approvalstatus') && ($key !== 'approvaldate') && ($key !== 'remarks') ) {
								// if(($key == 'isrepair') || ($key == 'isscrap')) {
									$value=(($val===0) || ($val==='0') || ($val==='false'))?false:((($val===1) || ($val==='1') || ($val==='true'))?true:$val);
								// }
								$Itimail->$key=$value;
							}
						}
						$Itimail->save();

						unset($data['formtype']);
						
						unset($data['accessrequested']);
						unset($data['accesstype']);
						unset($data['emailquota']);
						unset($data['emaildomain']);
						unset($data['listgroupmoderation']);
						
						unset($data['web1']);
						unset($data['web2']);

						unset($data['newmailboxsize']);
						unset($data['incomingsize']);
						unset($data['outgoingsize']);

						unset($data['typeofaccess']);

						unset($data['emailgroupname']);
						unset($data['membername']);

						unset($data['validto']);
						unset($data['validfrom']);

						unset($data['reason']);
						
						
						$olddata = $Itimailapproval->to_array();
						foreach($data as $key=>$val){
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$Itimailapproval->$key=$val;
						}
						$Itimailapproval->save();
						$logger = new Datalogger("Itimailapproval","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						if (isset($mode) && ($mode=='approve')){
							$Itimail = Itimail::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));
							$joinx   = "LEFT JOIN tbl_approver ON (tbl_itimailapproval.approver_id = tbl_approver.id) ";					
							$nTrapproval = Itimailapproval::find('first',array('joins'=>$joinx,'conditions' => array("itimail_id=? and ApprovalStatus=0",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
							$username = $nTrapproval->approver->employee->loginname;
							$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
							// $Itimailschedule=Trschedule::find('all',array('conditions'=>array("itimail_id=?",$doid),'include'=>array('itimail'=>array('employee'=>array('company','department','designation','grade','location')))));
							// $Itimailticket=Trticket::find('all',array('conditions'=>array("itimail_id=?",$doid),'include'=>array('itimail'=>array('employee'=>array('company','department','designation','grade','location')))));
							$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Itimail->employee->loginname)));
							$email=$usr->email;
							$superiorId=$Itimail->depthead;
							$Superior = Employee::find($superiorId);
							$supAdb = Addressbook::find('first',array('conditions'=>array("username=?",$Superior->loginname)));
							$complete = false;
							$Itimailhistory = new Itimailhistory();
							$Itimailhistory->date = date("Y-m-d h:i:s");
							$Itimailhistory->fullname = $Employee->fullname;
							$Itimailhistory->approvaltype = $Itimailapproval->approver->approvaltype->approvaltype;
							$Itimailhistory->remarks = $data['remarks'];
							$Itimailhistory->itimail_id = $doid;

							if($Itimail->formtype == 1) {
								$title = 'Exchange - Internet Email';
							}else if($Itimail->formtype == 2) {
								$title = 'Internet Access';
							}else if($Itimail->formtype == 3) {
								$title = 'Increase Mailbox Size';
							}else if($Itimail->formtype == 4) {
								$title = 'RD Web Access';
							}else if($Itimail->formtype == 5) {
								$title = 'Email Group';
							}
							
							switch ($data['approvalstatus']){
								case '1':
									$Itimail->requeststatus = 2;
									$emto=$email;$emname=$Itimail->employee->fullname;
									$this->mail->Subject = "Online Approval System -> Need Rework";
									$red = 'Your '.$title.' require some rework :
											<br>Remarks from Approver : <font color="red">'.$data['remarks']."</font>";
									$Itimailhistory->actiontype = 3;
									break;
								case '2':
									if ($Itimailapproval->approver->isfinal == 1){
										$Itimail->requeststatus = 3;
										$emto=$email;$emname=$Itimail->employee->fullname;
										$this->mail->Subject = "Online Approval System -> Approval Completed";
										$red = '<p>Your '.$title.' request has been approved</p>';
													// '<p><b><span lang=EN-US style=\'color:#002060\'>Note : Please <u>forward</u> this electronic approval to your respective Human Resource Department.</span></b></p>';
										//delete unnecessary approver
										$Itimailapproval = Itimailapproval::find('all', array('joins'=>$join,'conditions' => array("itimail_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
										foreach ($Itimailapproval as $data) {
											if($data->approvalstatus==0){
												$logger = new Datalogger("Itimailapproval","delete",json_encode($data->to_array()),"automatic remove unnecessary approver by system");
												$logger->SaveData();
												$data->delete();
											}
										}
										$complete =true;
									}
									else{
										$Itimail->requeststatus = 1;
										$emto=$adb->email;$emname=$adb->fullname;
										$this->mail->Subject = 'Online Approval System -> new '.$title.' Request';
										$red = 'new '.$title.' Request awaiting for your approval:';
									}
									$Itimailhistory->actiontype = 4;							
									break;
								case '3':
									$Itimail->requeststatus = 4;
									$emto=$email;$emname=$Itimail->employee->fullname;
									$Itimailhistory->actiontype = 5;
									$this->mail->Subject = "Online Approval System -> Request Rejected";
									$red = 'Your '.$title.' Request has been rejected
											<br>Remarks from Approver : <font color="red">'.$data['remarks']."</font>";
									break;
								default:
									break;
							}
							$Itimail->save();
							$Itimailhistory->save();
							echo "email to :".$emto." ->".$emname;
							$this->mail->addAddress($emto, $emname);
							$ItimailJ = Itimail::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));



							$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$emname.',</span></p>
								<p class=MsoNormal><span style="color:#1F497D">'.$red.'</span></p>
								<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
								<table border=1 cellspacing=0 cellpadding=3 width=683>
								<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itimail->employee->fullname.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itimail->employee->sapid.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itimail->employee->designation->designationname.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itimail->employee->company->companyname.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itimail->employee->location->location.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
								</table>';

								if($Itimail->formtype == 1) {

									if($Itimail->accessrequested == 1) {
										$accessR = 'Exchange (non-Internet) Email';
									}else if($Itimail->accessrequested == 2) {
										$accessR = 'Internet Email';
									}else if($Itimail->accessrequested == 3) {
										$accessR = 'Change Domain';
									}else {
										$accessR = '';
									}

									if($Itimail->accesstype == 1) {
										$accessT = 'Terminal Server (TS) User Account';
									}else if($Itimail->accesstype == 2) {
										$accessT = 'Non-TS Account';
									}else {
										$accessT = '';
									}

									if($Itimail->accounttype == 1) {
										$accountT = 'Permanent';
									}else if($Itimail->accounttype == 2) {
										$accountT = 'Temporary';
									}else {
										$accountT = '';
									}

									if($Itimail->emailquota == 1) {
										$emailQ = '250MB';
									}else if($Itimail->emailquota == 2) {
										$emailQ = '500MB';
									}else if($Itimail->emailquota == 3) {
										$emailQ = '1000MB';
									}else if($Itimail->emailquota == 4) {
										$emailQ = '1500MB';
									}else if($Itimail->emailquota == 5) {
										$emailQ = '2000MB';
									}else {
										$emailQ = '';
									}

									if($Itimail->emaildomain == 1) {
										$emailD = 'itci-hutani.com';
									}else if($Itimail->emaildomain == 2) {
										$emailD = 'kalimantan-prima.com';
									}else if($Itimail->emaildomain == 3) {
										$emailD = 'balikpapanchip.com';
									}else if($Itimail->emaildomain == 4) {
										$emailD = 'lajudinamika.com';
									}else if($Itimail->emaildomain == 5) {
										$emailD = 'ptadindo.com';
									}else if($Itimail->emaildomain == 6) {
										$emailD = 'D1.LCL';
									}else {
										$emailD = '';
									}

									$listmod = Listmod::find('first',array('conditions'=>array("id=?",$Itimail->listgroupmoderation)));

									$this->mailbody .='	
										
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr>
											<th><p class=MsoNormal>Access Requested</p></th>
											<th><p class=MsoNormal>Access Type</p></th>
											<th><p class=MsoNormal>Account Type</p></th>
											<th><p class=MsoNormal>Email Quota </p></th>
											<th><p class=MsoNormal>Email Domain </p></th>
											<th><p class=MsoNormal>List Group</p></th>
											<th><p class=MsoNormal>List Group Moderation</p></th>
											<th><p class=MsoNormal>Valid From</p></th>
											<th><p class=MsoNormal>Valid To</p></th>
										</tr>
										<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.$accessR.'</p></td>
											<td><p class=MsoNormal> '.$accessT.'</p></td>
											<td><p class=MsoNormal> '.$accountT.'</p></td>
											<td><p class=MsoNormal> '.$emailQ.'</p></td>
											<td><p class=MsoNormal> '.$emailD.'</p></td>
											<td><p class=MsoNormal> '.$Itimail->listgroup.'</p></td>
											<td><p class=MsoNormal> '.$listmod->mod.'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validfrom)).'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validto)).'</p></td>
										</tr>
										';

									$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
									
								} else if($Itimail->formtype == 2) {
									$this->mailbody .='	
										
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr>
											<th><p class=MsoNormal>http:// (A)</p></th>
											<th><p class=MsoNormal>http:// (B)</p></th>
											<th><p class=MsoNormal>Valid From</p></th>
											<th><p class=MsoNormal>Valid To</p></th>
										</tr>
										<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.$Itimail->web1.'</p></td>
											<td><p class=MsoNormal> '.$Itimail->web2.'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validfrom)).'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validto)).'</p></td>
										</tr>
										';

									$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
									
								} else if($Itimail->formtype == 3) {

									if($Itimail->newmailboxsize == 1) {
										$newmailbox = '256MB';
									}else if($Itimail->newmailboxsize == 2) {
										$newmailbox = '512MB';
									}else if($Itimail->newmailboxsize == 3) {
										$newmailbox = '1GB';
									}else if($Itimail->newmailboxsize == 4) {
										$newmailbox = '1.5GB';
									}else if($Itimail->newmailboxsize == 5) {
										$newmailbox = '2GB';
									}else if($Itimail->newmailboxsize == 6) {
										$newmailbox = '3GB';
									}else if($Itimail->newmailboxsize == 7) {
										$newmailbox = '4GB';
									}else if($Itimail->newmailboxsize == 8) {
										$newmailbox = '5GB';
									}else if($Itimail->newmailboxsize == 9) {
										$newmailbox = '6GB';
									}else if($Itimail->newmailboxsize == 10) {
										$newmailbox = '7GB';
									}else if($Itimail->newmailboxsize == 11) {
										$newmailbox = '8GB';
									}else if($Itimail->newmailboxsize == 12) {
										$newmailbox = '9GB';
									}else if($Itimail->newmailboxsize == 13) {
										$newmailbox = '10GB';
									}else {
										$newmailbox = '';
									}

									if($Itimail->incomingsize == 1) {
										$incoming = '5MB';
									}else if($Itimail->incomingsize == 2) {
										$incoming = '10MB';
									}else if($Itimail->incomingsize == 3) {
										$incoming = '15MB';
									}else if($Itimail->incomingsize == 4) {
										$incoming = '20MB';
									}else if($Itimail->incomingsize == 5) {
										$incoming = '25MB';
									}else if($Itimail->incomingsize == 6) {
										$incoming = '30MB';
									}else if($Itimail->incomingsize == 7) {
										$incoming = '35MB';
									}else if($Itimail->incomingsize == 8) {
										$incoming = '40MB';
									}else if($Itimail->incomingsize == 9) {
										$incoming = '45MB';
									}else if($Itimail->incomingsize == 10) {
										$incoming = '50MB';
									}else if($Itimail->incomingsize == 11) {
										$incoming = '55MB';
									}else if($Itimail->incomingsize == 12) {
										$incoming = '60MB';
									}else if($Itimail->incomingsize == 13) {
										$incoming = '65MB';
									}else if($Itimail->incomingsize == 14) {
										$incoming = '70MB';
									}else if($Itimail->incomingsize == 15) {
										$incoming = '75MB';
									}else if($Itimail->incomingsize == 16) {
										$incoming = '80MB';
									}else if($Itimail->incomingsize == 17) {
										$incoming = '85MB';
									}else if($Itimail->incomingsize == 18) {
										$incoming = '90MB';
									}else if($Itimail->incomingsize == 19) {
										$incoming = '95MB';
									}else if($Itimail->incomingsize == 20) {
										$incoming = '100MB';
									}else {
										$incoming = '';
									}

									if($Itimail->outgoingsize == 1) {
										$outgoing = '5MB';
									}else if($Itimail->outgoingsize == 2) {
										$outgoing = '10MB';
									}else if($Itimail->outgoingsize == 3) {
										$outgoing = '15MB';
									}else if($Itimail->outgoingsize == 4) {
										$outgoing = '20MB';
									}else if($Itimail->outgoingsize == 5) {
										$outgoing = '25MB';
									}else if($Itimail->outgoingsize == 6) {
										$outgoing = '30MB';
									}else if($Itimail->outgoingsize == 7) {
										$outgoing = '35MB';
									}else if($Itimail->outgoingsize == 8) {
										$outgoing = '40MB';
									}else if($Itimail->outgoingsize == 9) {
										$outgoing = '45MB';
									}else if($Itimail->outgoingsize == 10) {
										$outgoing = '50MB';
									}else if($Itimail->outgoingsize == 11) {
										$outgoing = '55MB';
									}else if($Itimail->outgoingsize == 12) {
										$outgoing = '60MB';
									}else if($Itimail->outgoingsize == 13) {
										$outgoing = '65MB';
									}else if($Itimail->outgoingsize == 14) {
										$outgoing = '70MB';
									}else if($Itimail->outgoingsize == 15) {
										$outgoing = '75MB';
									}else if($Itimail->outgoingsize == 16) {
										$outgoing = '80MB';
									}else if($Itimail->outgoingsize == 17) {
										$outgoing = '85MB';
									}else if($Itimail->outgoingsize == 18) {
										$outgoing = '90MB';
									}else if($Itimail->outgoingsize == 19) {
										$outgoing = '95MB';
									}else if($Itimail->outgoingsize == 20) {
										$outgoing = '100MB';
									}else {
										$outgoing = '';
									}

									$this->mailbody .='	
										
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr>
											<th><p class=MsoNormal>New Mailbox Size</p></th>
											<th><p class=MsoNormal>Outgoing Size</p></th>
											<th><p class=MsoNormal>Incoming Size</p></th>
											<th><p class=MsoNormal>Valid From</p></th>
											<th><p class=MsoNormal>Valid To</p></th>
										</tr>
										<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.$newmailbox.'</p></td>
											<td><p class=MsoNormal> '.$incoming.'</p></td>
											<td><p class=MsoNormal> '.$outgoing.'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validfrom)).'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validto)).'</p></td>
										</tr>
										';

									$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
									
								} else if($Itimail->formtype == 4) {
									$this->mailbody .='	
										
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr>
											<th><p class=MsoNormal>RDP to TS</p></th>
											<th><p class=MsoNormal>Example of usage</p></th>
											<th><p class=MsoNormal>Valid From</p></th>
											<th><p class=MsoNormal>Valid To</p></th>
										</tr>
										<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.$Itimail->typeofaccess.'</p></td>
											<td><p class=MsoNormal> access email, open & edit attachments/ documents, department shared folders, corporate portals, SAP GUI</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validfrom)).'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validto)).'</p></td>
										</tr>
										';

									$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
									
								} else if($Itimail->formtype == 5) {

									$string = $Itimail->membername;

									$expstring = explode(',',$string);
									// $countstring = count($expstring)+29;

									$getname = [];
									foreach($expstring as $p => $key) {
										$dataname = Employee::find($key);
										array_push($getname,$dataname->loginname);
									}

									$getemailname = [];
									foreach($getname as $p => $key) {

										$datamail = Addressbook::find('first',array('select'=> "CONCAT(fullname,' (',email,')' ) as name",'conditions' => array("username=?",$key)));

										array_push($getemailname,$datamail->name);

									}

									$ss = implode(' | ',$getemailname);

									$this->mailbody .='	
										
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr>
											<th><p class=MsoNormal>Email Group Name</p></th>
											<th><p class=MsoNormal>Member Name</p></th>
											<th><p class=MsoNormal>Valid From</p></th>
											<th><p class=MsoNormal>Valid To</p></th>
										</tr>
										<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.$Itimail->emailgroupname.'</p></td>
											<td><p class=MsoNormal> '.$ss.'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validfrom)).'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validto)).'</p></td>
										</tr>
										';

									$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
									
								}

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
							// 		<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validfrom)).'</p></td>
							// 		<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validto)).'</p></td>
							// 		<td><p class=MsoNormal> '.$Itimail->listgroup.'</p></td>
							// 	</tr>
							// 	';
							// $this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
							
								$this->mail->msgHTML($this->mailbody);
							if ($complete){
								$form = $Itimail->formtype;
								$fileName = $this->generatePDFi($doid);
								$filePath = SITE_PATH.DS.$fileName;
								$Mailrecipient = Mailrecipient::find('all',array('conditions'=>array("module='IT' and company_list like ?","%".$ItimailJ->employee->companycode."%")));
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
						$Itimailapproval = Itimailapproval::all();
						foreach ($Itimailapproval as $result) {
							$result = $result->to_array();
						}
						echo json_encode($Itimailapproval, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}
	function generatePDFi($id){
		$Itimail = Itimail::find($id);
		$form = $Itimail->formtype;
		$superiorId=$Itimail->depthead;
		$Superior = Employee::find($superiorId);
		$supAdb = Addressbook::find('first',array('conditions'=>array("username=?",$Superior->loginname)));
		$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Itimail->employee->loginname)));
		$email=$usr->email;
		$fullname= $Itimail->employee->fullname;

		$datefrom = date("d/m/Y",strtotime($Itimail->validfrom));
		$dateto = date("d/m/Y",strtotime($Itimail->validto));

		$joinx   = "LEFT JOIN tbl_approver ON (tbl_itimailapproval.approver_id = tbl_approver.id) ";					
		$Itimailapproval = Itimailapproval::find('all',array('joins'=>$joinx,'conditions' => array("itimail_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee','approvaltype'))));							
		
		// $ItimailJ = Itimail::find($id,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));

		// $Mailrecipient = Mailrecipient::find('all',array('conditions'=>array("module='IT' and company_list like ?","%".$ItimailJ->employee->companycode."%")));
		// foreach ($Mailrecipient as $data){
		// 	print_r($data->email);
		// }

		//condition
			foreach ($Itimailapproval as $data){
				if(($data->approver->approvaltype->id==29) || ($data->approver->employee_id==$Itimail->depthead)){
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
			$excel->Visible = false;

			if($form == 1) {
				$title = 'EXINETMAIL';

				$file= SITE_PATH."/doc/it/template_inetmail.xlsx";
				$Workbook = $excel->Workbooks->Open($file) or die("ERROR: Unable to open " . $file . "!\r\n");
				$Worksheet = $Workbook->Worksheets(1);
				$Worksheet->Activate;

				$Worksheet->Range("F7")->Value = $fullname;
				$Worksheet->Range("F9")->Value = $Itimail->employee_id;
				$Worksheet->Range("F11")->Value = $Itimail->designation;
				$Worksheet->Range("F13")->Value = $Itimail->bgbu;
				$Worksheet->Range("F15")->Value = $Itimail->officelocation;
				$Worksheet->Range("Y15")->Value = $Itimail->floor;
				$Worksheet->Range("F17")->Value = $Itimail->phoneext;
				$Worksheet->Range("F19")->Value = $Itimail->department;
				//condition

					if($Itimail->accessrequested == 1) {
						$Worksheet->Range("F21")->Value = 'x';
					}else if($Itimail->accessrequested == 2) {
						$Worksheet->Range("N21")->Value = 'x';
					}else if($Itimail->accessrequested == 3) {
						$Worksheet->Range("T21")->Value = 'x';
					}

					if($Itimail->accesstype == 1) {
						$Worksheet->Range("F23")->Value = 'x';
					}else if($Itimail->accesstype == 2) {
						$Worksheet->Range("P23")->Value = 'x';
					}
		
					if($Itimail->accounttype == 1) {
						$Worksheet->Range("F25")->Value = 'x';
					}else if($Itimail->accounttype == 2) {
						$Worksheet->Range("P25")->Value = 'x';
					}

					if($Itimail->emailquota == 1) {
						$Worksheet->Range("F29")->Value = 'x';
					}else if($Itimail->emailquota == 2) {
						$Worksheet->Range("J29")->Value = 'x';
					}else if($Itimail->emailquota == 3) {
						$Worksheet->Range("N29")->Value = 'x';
					}else if($Itimail->emailquota == 4) {
						$Worksheet->Range("R29")->Value = 'x';
					}else if($Itimail->emailquota == 5) {
						$Worksheet->Range("V29")->Value = 'x';
					}

					if($Itimail->emaildomain == 1) {
						$emailD = 'itci-hutani.com';
					}else if($Itimail->emaildomain == 2) {
						$emailD = 'kalimantan-prima.com';
					}else if($Itimail->emaildomain == 3) {
						$emailD = 'balikpapanchip.com';
					}else if($Itimail->emaildomain == 4) {
						$emailD = 'lajudinamika.com';
					}else if($Itimail->emaildomain == 5) {
						$emailD = 'ptadindo.com';
					}else if($Itimail->emaildomain == 6) {
						$emailD = 'D1.LCL';
					}else {
						$emailD = '';
					}

				$listmod = Listmod::find('first',array('conditions'=>array("id=?",$Itimail->listgroupmoderation)));
		

				//end condition
				$Worksheet->Range("F31")->Value = $datefrom;
				$Worksheet->Range("R31")->Value = $dateto;
				$Worksheet->Range("B37")->Value = $emailD;
				$Worksheet->Range("H37")->Value = $Itimail->listgroup;
				$Worksheet->Range("N37")->Value = $listmod->mod;
				$Worksheet->Range("F41")->Value = strip_tags($Itimail->reason);
				$Worksheet->Range("B52")->Value = $deptheadname;
				$Worksheet->Range("B53")->Value = $deptheaddate;
				$Worksheet->Range("I52")->Value = $hrdname;
				$Worksheet->Range("I53")->Value = $hrddate;
				$Worksheet->Range("P52")->Value = $buheadname;
				$Worksheet->Range("P53")->Value = $buheaddate;
				$Worksheet->Range("W52")->Value = $itheadname;
				$Worksheet->Range("W53")->Value = $itheaddate;

				$Worksheet->Range("A60")->Value = $fullname;
				$Worksheet->Range("S60")->Value = date("d/m/Y",strtotime($Itimail->createddate));

		} else if($form == 2) {
			$title = 'INETACCESS';

			$file= SITE_PATH."/doc/it/template_inetaccess.xls";
				$Workbook = $excel->Workbooks->Open($file) or die("ERROR: Unable to open " . $file . "!\r\n");
				$Worksheet = $Workbook->Worksheets(1);
				$Worksheet->Activate;

				$Worksheet->Range("F7")->Value = $fullname;
				$Worksheet->Range("F9")->Value = $Itimail->employee_id;
				$Worksheet->Range("F11")->Value = $Itimail->designation;
				$Worksheet->Range("F13")->Value = $Itimail->bgbu;
				$Worksheet->Range("F15")->Value = $Itimail->officelocation;
				$Worksheet->Range("Y15")->Value = $Itimail->floor;
				$Worksheet->Range("F17")->Value = $Itimail->phoneext;
				$Worksheet->Range("F19")->Value = $Itimail->department;
				//condition
		

				//end condition
				$Worksheet->Range("F29")->Value = $datefrom;
				$Worksheet->Range("R29")->Value = $dateto;
				$Worksheet->Range("F23")->Value = $Itimail->web1;
				$Worksheet->Range("F25")->Value = $Itimail->web2;
				$Worksheet->Range("F32")->Value = strip_tags($Itimail->reason);
				$Worksheet->Range("C44")->Value = $deptheadname;
				$Worksheet->Range("C45")->Value = $deptheaddate;
				$Worksheet->Range("J44")->Value = $buheadname;
				$Worksheet->Range("J45")->Value = $buheaddate;
				$Worksheet->Range("Q44")->Value = $itheadname;
				$Worksheet->Range("Q45")->Value = $itheaddate;

				$Worksheet->Range("A52")->Value = $fullname;
				$Worksheet->Range("S52")->Value = date("d/m/Y",strtotime($Itimail->createddate));

		} else if($form == 3) {
			$title = 'INCMAILBOX';

			$file= SITE_PATH."/doc/it/template_incmailbox.xlsx";
				$Workbook = $excel->Workbooks->Open($file) or die("ERROR: Unable to open " . $file . "!\r\n");
				$Worksheet = $Workbook->Worksheets(1);
				$Worksheet->Activate;

				$Worksheet->Range("G7")->Value = $fullname;
				$Worksheet->Range("G9")->Value = $Itimail->employee_id;
				$Worksheet->Range("G13")->Value = $Itimail->designation;
				$Worksheet->Range("G15")->Value = $Itimail->bgbu;
				$Worksheet->Range("Z15")->Value = $Itimail->officelocation;
				$Worksheet->Range("G17")->Value = $Itimail->floor;
				$Worksheet->Range("G19")->Value = $Itimail->phoneext;
				$Worksheet->Range("G21")->Value = $Itimail->department;
				//condition

				if($Itimail->newmailboxsize == 1) {
					$newmailbox = '256MB';
				}else if($Itimail->newmailboxsize == 2) {
					$newmailbox = '512MB';
				}else if($Itimail->newmailboxsize == 3) {
					$newmailbox = '1GB';
				}else if($Itimail->newmailboxsize == 4) {
					$newmailbox = '1.5GB';
				}else if($Itimail->newmailboxsize == 5) {
					$newmailbox = '2GB';
				}else if($Itimail->newmailboxsize == 6) {
					$newmailbox = '3GB';
				}else if($Itimail->newmailboxsize == 7) {
					$newmailbox = '4GB';
				}else if($Itimail->newmailboxsize == 8) {
					$newmailbox = '5GB';
				}else if($Itimail->newmailboxsize == 9) {
					$newmailbox = '6GB';
				}else if($Itimail->newmailboxsize == 10) {
					$newmailbox = '7GB';
				}else if($Itimail->newmailboxsize == 11) {
					$newmailbox = '8GB';
				}else if($Itimail->newmailboxsize == 12) {
					$newmailbox = '9GB';
				}else if($Itimail->newmailboxsize == 13) {
					$newmailbox = '10GB';
				}else {
					$newmailbox = '';
				}

				if($Itimail->incomingsize == 1) {
					$incoming = '5MB';
				}else if($Itimail->incomingsize == 2) {
					$incoming = '10MB';
				}else if($Itimail->incomingsize == 3) {
					$incoming = '15MB';
				}else if($Itimail->incomingsize == 4) {
					$incoming = '20MB';
				}else if($Itimail->incomingsize == 5) {
					$incoming = '25MB';
				}else if($Itimail->incomingsize == 6) {
					$incoming = '30MB';
				}else if($Itimail->incomingsize == 7) {
					$incoming = '35MB';
				}else if($Itimail->incomingsize == 8) {
					$incoming = '40MB';
				}else if($Itimail->incomingsize == 9) {
					$incoming = '45MB';
				}else if($Itimail->incomingsize == 10) {
					$incoming = '50MB';
				}else if($Itimail->incomingsize == 11) {
					$incoming = '55MB';
				}else if($Itimail->incomingsize == 12) {
					$incoming = '60MB';
				}else if($Itimail->incomingsize == 13) {
					$incoming = '65MB';
				}else if($Itimail->incomingsize == 14) {
					$incoming = '70MB';
				}else if($Itimail->incomingsize == 15) {
					$incoming = '75MB';
				}else if($Itimail->incomingsize == 16) {
					$incoming = '80MB';
				}else if($Itimail->incomingsize == 17) {
					$incoming = '85MB';
				}else if($Itimail->incomingsize == 18) {
					$incoming = '90MB';
				}else if($Itimail->incomingsize == 19) {
					$incoming = '95MB';
				}else if($Itimail->incomingsize == 20) {
					$incoming = '100MB';
				}else {
					$incoming = '';
				}

				if($Itimail->outgoingsize == 1) {
					$outgoing = '5MB';
				}else if($Itimail->outgoingsize == 2) {
					$outgoing = '10MB';
				}else if($Itimail->outgoingsize == 3) {
					$outgoing = '15MB';
				}else if($Itimail->outgoingsize == 4) {
					$outgoing = '20MB';
				}else if($Itimail->outgoingsize == 5) {
					$outgoing = '25MB';
				}else if($Itimail->outgoingsize == 6) {
					$outgoing = '30MB';
				}else if($Itimail->outgoingsize == 7) {
					$outgoing = '35MB';
				}else if($Itimail->outgoingsize == 8) {
					$outgoing = '40MB';
				}else if($Itimail->outgoingsize == 9) {
					$outgoing = '45MB';
				}else if($Itimail->outgoingsize == 10) {
					$outgoing = '50MB';
				}else if($Itimail->outgoingsize == 11) {
					$outgoing = '55MB';
				}else if($Itimail->outgoingsize == 12) {
					$outgoing = '60MB';
				}else if($Itimail->outgoingsize == 13) {
					$outgoing = '65MB';
				}else if($Itimail->outgoingsize == 14) {
					$outgoing = '70MB';
				}else if($Itimail->outgoingsize == 15) {
					$outgoing = '75MB';
				}else if($Itimail->outgoingsize == 16) {
					$outgoing = '80MB';
				}else if($Itimail->outgoingsize == 17) {
					$outgoing = '85MB';
				}else if($Itimail->outgoingsize == 18) {
					$outgoing = '90MB';
				}else if($Itimail->outgoingsize == 19) {
					$outgoing = '95MB';
				}else if($Itimail->outgoingsize == 20) {
					$outgoing = '100MB';
				}else {
					$outgoing = '';
				}

				//end condition
				$Worksheet->Range("G26")->Value = $datefrom;
				$Worksheet->Range("P26")->Value = $dateto;
				$Worksheet->Range("G23")->Value = $newmailbox;
				$Worksheet->Range("P23")->Value = $incoming;
				$Worksheet->Range("X23")->Value = $outgoing;
				$Worksheet->Range("G29")->Value = strip_tags($Itimail->reason);
				$Worksheet->Range("D43")->Value = $deptheadname;
				$Worksheet->Range("D44")->Value = $deptheaddate;
				$Worksheet->Range("M43")->Value = $buheadname;
				$Worksheet->Range("M44")->Value = $buheaddate;
				$Worksheet->Range("V43")->Value = $itheadname;
				$Worksheet->Range("V44")->Value = $itheaddate;

				$Worksheet->Range("B51")->Value = $fullname;
				$Worksheet->Range("T51")->Value = date("d/m/Y",strtotime($Itimail->createddate));

		} else if($form == 4) {
			$title = 'RDWEB';

			$file= SITE_PATH."/doc/it/template_rdweb.xlsx";
				$Workbook = $excel->Workbooks->Open($file) or die("ERROR: Unable to open " . $file . "!\r\n");
				$Worksheet = $Workbook->Worksheets(1);
				$Worksheet->Activate;

				$Worksheet->Range("F7")->Value = $fullname;
				$Worksheet->Range("F9")->Value = $Itimail->employee_id;
				$Worksheet->Range("F13")->Value = $Itimail->designation;
				$Worksheet->Range("F15")->Value = $Itimail->bgbu;
				$Worksheet->Range("F17")->Value = $Itimail->officelocation;
				$Worksheet->Range("Y17")->Value = $Itimail->floor;
				$Worksheet->Range("F19")->Value = $Itimail->phoneext;
				$Worksheet->Range("F21")->Value = $Itimail->department;
				//condition
		

				//end condition
				$Worksheet->Range("F31")->Value = $datefrom;
				$Worksheet->Range("P31")->Value = $dateto;
				$Worksheet->Range("J27")->Value = $Itimail->typeofaccess;
				$Worksheet->Range("F34")->Value = strip_tags($Itimail->reason);
				$Worksheet->Range("C48")->Value = $deptheadname;
				$Worksheet->Range("C49")->Value = $deptheaddate;
				$Worksheet->Range("I48")->Value = $buheadname;
				$Worksheet->Range("I49")->Value = $buheaddate;
				$Worksheet->Range("P48")->Value = $mdname;
				$Worksheet->Range("P49")->Value = $mddate;
				$Worksheet->Range("W48")->Value = $itheadname;
				$Worksheet->Range("W49")->Value = $itheaddate;

				$Worksheet->Range("A58")->Value = $fullname;
				$Worksheet->Range("S58")->Value = date("d/m/Y",strtotime($Itimail->createddate));

		} else if($form == 5) {
				$title = 'mailgroup';


				$file= SITE_PATH."/doc/it/template_mailgroup.xls";
				$Workbook = $excel->Workbooks->Open($file) or die("ERROR: Unable to open " . $file . "!\r\n");
				$Worksheet = $Workbook->Worksheets(1);
				$Worksheet->Activate;

				$Worksheet->Range("F7")->Value = $fullname;
				$Worksheet->Range("F9")->Value = $Itimail->employee_id;
				$Worksheet->Range("F11")->Value = $Itimail->designation;
				$Worksheet->Range("F13")->Value = $Itimail->bgbu;
				$Worksheet->Range("F15")->Value = $Itimail->officelocation;
				$Worksheet->Range("Y15")->Value = $Itimail->floor;
				$Worksheet->Range("F17")->Value = $Itimail->phoneext;
				$Worksheet->Range("F20")->Value = $Itimail->department;

				$Worksheet->Range("F29")->Value = $Itimail->emailgroupname;
				//condition
				$string = $Itimail->membername;

				$expstring = explode(',',$string);
				// $countstring = count($expstring)+29;

				$getname = [];
				foreach($expstring as $p => $key) {
					$dataname = Employee::find($key);
					array_push($getname,$dataname->loginname);
				}

				$getemailname = [];
				foreach($getname as $p => $key) {

					$datamail = Addressbook::find('first',array('select'=> "CONCAT(fullname,' (',email,')' ) as name",'conditions' => array("username=?",$key)));

					array_push($getemailname,$datamail->name);

				}

				$ss = implode(' | ',$getemailname);
				print_r($ss);


				$Worksheet->Range("F31")->Value = $ss;

				$Worksheet->Range("F41")->Value = strip_tags($Itimail->reason);




				// $xlShiftDown=-4121;
				// for ($a=29;$a<$countstring;$a++){
				// 	$Worksheet->Rows($a+1)->Insert($xlShiftDown);
				// 	$Worksheet->Range("F".$a)->Value = ($a-28).'. '.($getname[$a-29]);
				// }
		

				//end condition
				$Worksheet->Range("F26")->Value = $datefrom;
				$Worksheet->Range("R26")->Value = $dateto;
				// $Worksheet->Range("J27")->Value = $Itimail->typeofaccess;
				// $Worksheet->Range("F34")->Value = $Itimail->reason;
				$Worksheet->Range("I56")->Value = $deptheadname;
				$Worksheet->Range("I57")->Value = $deptheaddate;
				$Worksheet->Range("P56")->Value = $buheadname;
				$Worksheet->Range("P57")->Value = $buheaddate;
				$Worksheet->Range("W56")->Value = $itheadname;
				$Worksheet->Range("W57")->Value = $itheaddate;

				$Worksheet->Range("C56")->Value = $fullname;
				$Worksheet->Range("C57")->Value = date("d/m/Y",strtotime($Itimail->createddate));

		}

			$xlTypePDF = 0;
			$xlQualityStandard = 0;
			$fileName ='doc'.DS.'it'.DS.'pdf'.DS.$title.$Itimail->employee->sapid.'_'.date("YmdHis").'.pdf';
			$fileName = str_replace("/","",$fileName);
			$path= SITE_PATH.'/doc'.DS.'it'.DS.'pdf'.DS.$title.$Itimail->employee->sapid.'_'.date("YmdHis").'.pdf';
			if (file_exists($path)) {
			unlink($path);
			}
			$Worksheet->ExportAsFixedFormat($xlTypePDF, $path, $xlQualityStandard);
			$Itimail->approveddoc=str_replace("\\","/",$fileName);
			$Itimail->save();

			$Workbook->Close(false);
			unset($Worksheet);
			unset($Workbook);
			$excel->Workbooks->Close();
			$excel->Quit();
			unset($excel);

			return $fileName;

		} catch(com_exception $e) {  
			$err = new Errorlog();
			$err->errortype = "IteiePDFGenerator";
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
	function itimailHistory(){
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
							$Itimailhistory = Itimailhistory::find('all', array('conditions' => array("itimail_id=?",$id)));
							foreach ($Itimailhistory as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Itimailhistory, JSON_NUMERIC_CHECK);
						}
						break;
					default:
						break;
				}
			}
		}
	}
}