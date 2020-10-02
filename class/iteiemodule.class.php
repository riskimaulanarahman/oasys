<?php
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

Class Iteiemodule extends Application{
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
				case 'apiiteiebyemp':
					$this->iteieByEmp();
					break;
				case 'apiiteie':
					$this->iteie();
					break;
				case 'apiiteieapp':
					$this->iteieApproval();
					break;
				case 'apiiteiepdf':
					$id = $this->get['id'];
					// $this->generatePDF($id);
					$this->generatePDFi($id);
					break;
				case 'apiiteiehist':
					$this->iteieHistory();
					break;
				
				default:
					break;
			}
		}
	}
	
	function iteieByEmp(){
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
							$Iteie = Iteie::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Iteie as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Iteie, JSON_NUMERIC_CHECK);
						}else{
							echo json_encode(array());
						}
						break;
					case 'find':
						$query=$this->post['query'];					
						if(isset($query['status'])){
							switch ($query['status']){
								case 'waiting':
									$Iteie = Iteie::find('all', array('conditions' => array("employee_id=? and RequestStatus<3 and id<>?",$query['username'],$query['id']),'include' => array('employee')));
									foreach ($Iteie as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Iteie));
									break;
								default:
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
									$Iteie = Iteie::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Iteie as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Iteie));
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
							$Iteie = Iteie::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Iteie as &$result) {
								$fullname=$result->employee->fullname;
								$result = $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Iteie, JSON_NUMERIC_CHECK);
						}else{
							echo json_encode(array());
						}
						break;
				}
			}
		}
	}
	function iteie(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'byid':
						$id = $this->post['id'];
						// $join = "LEFT JOIN vwiteiereport ON tbl_iteie.id = vwiteiereport.id";
						// $select = "tbl_iteie.*,vwiteiereport.apprstatuscode";
						$Iteie = Iteie::find($id, array('include' => array('employee'=>array('company','department','designation'))));
						// $Iteie = Iteie::find($id, array('joins'=>$join,'select'=>$select,'include' => array('employee'=>array('company','department','designation'))));
						if ($Iteie){
							$fullname = $Iteie->employee->fullname;
							$department = $Iteie->employee->department->departmentname;
							$data=$Iteie->to_array();
							$data['fullname']=$fullname;
							$data['department']=$department;
							echo json_encode($data, JSON_NUMERIC_CHECK);
						}else{
							$Iteie = new Iteie();
							echo json_encode($Iteie);
						}
						break;
					case 'find':
						$query=$this->post['query'];					
						if(isset($query['status'])){
							switch ($query['status']){
								case 'chemp':
									break;
								case "reschedule":
									$id = $query['iteie_id'];
									$Iteie = Iteie::find($id,array('include'=>array('employee'=>array('company','department','designation','grade'))));
									$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Iteie->employee->loginname)));
									$email=$usr->email;
									
									$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$usr->fullname.',</span></p>
										<p class=MsoNormal><span style="color:#1F497D">Your Travel Request has been rescheduled by HR to match with your actual travel schedule:</span></p>
										<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Iteie->employee->fullname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Iteie->employee->sapid.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Iteie->employee->designation->designationname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Iteie->employee->company->companyname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Iteie->employee->location->location.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
										';
									$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
									$this->mail->addAddress($usr->email, $usr->fullname);
									$this->mail->Subject = "Online Approval System -> Exchange - Internet Email Request Reschedule";
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
									$Iteie = Iteie::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Iteie as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Iteie));
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
							// $Iteienew = Iteie::find('first',array('select' => "CONCAT('Iteie/','".$Employee->companycode."','/',YEAR(CURDATE()),'/',LPAD(MONTH(CURDATE()), 2, '0'),'/',LPAD(CASE when max(substring(wonumber,-4,4)) is null then 1 else max(substring(wonumber,-4,4))+1 end,4,'0')) as wonumber","conditions"=>array("substring(wonumber,7,".strlen($Employee->companycode).")=? and substring(wonumber,".(strlen($Employee->companycode)+8).",4)=YEAR(CURDATE())",$Employee->companycode)));
							// $data['wonumber']=$Iteienew->wonumber;
							$Iteie = Iteie::create($data);
							$data=$Iteie->to_array();
							$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";

								$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=30")));
								if(count($Approver)>0){
									$Iteieapproval = new Iteieapproval();
									$Iteieapproval->iteie_id = $Iteie->id;
									$Iteieapproval->approver_id = $Approver->id;
									$Iteieapproval->save();
								}
								$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=31")));
								if(count($Approver2)>0){
									$Iteieapproval = new Iteieapproval();
									$Iteieapproval->iteie_id = $Iteie->id;
									$Iteieapproval->approver_id = $Approver2->id;
									$Iteieapproval->save();
								}
								$Approver3 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=32")));
								if(count($Approver3)>0){
									$Iteieapproval = new Iteieapproval();
									$Iteieapproval->iteie_id = $Iteie->id;
									$Iteieapproval->approver_id = $Approver3->id;
									$Iteieapproval->save();
								}

							$Iteihistory = new Iteiehistory();
							$Iteihistory->date = date("Y-m-d h:i:s");
							$Iteihistory->fullname = $Employee->fullname;
							$Iteihistory->approvaltype = "Originator";
							$Iteihistory->iteie_id = $Iteie->id;
							$Iteihistory->actiontype = 0;
							$Iteihistory->save();
							
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "CreateITEIE";
							$err->errordate = date("Y-m-d h:i:s");
							$err->errormessage = $e->getMessage();
							$err->user = $this->currentUser->username;
							$err->ip = $this->ip;
							$err->save();
							$data = array("status"=>"error","message"=>$e->getMessage());
						}
						$logger = new Datalogger("ITEIE","create",null,json_encode($data));
						$logger->SaveData();
						echo json_encode($data);									
						break;
					case 'delete':
						try {				
							$id = $this->post['id'];
							$Iteie = Iteie::find($id);
							if ($Iteie->requeststatus==0){
								$approval = Iteieapproval::find("all",array('conditions' => array("iteie_id=?",$id)));
								foreach ($approval as $delr){
									$delr->delete();
								}
								// $detail = Trschedule::find("all",array('conditions' => array("iteie_id=?",$id)));
								// foreach ($detail as $delr){
								// 	$delr->delete();
								// }
								// $detail = Trticket::find("all",array('conditions' => array("iteie_id=?",$id)));
								// foreach ($detail as $delr){
								// 	$delr->delete();
								// }
								$hist = Iteiehistory::find("all",array('conditions' => array("iteie_id=?",$id)));
								foreach ($hist as $delr){
									$delr->delete();
								}
								$data = $Iteie->to_array();
								$Iteie->delete();
								$logger = new Datalogger("ITEIE","delete",json_encode($data),null);
								$logger->SaveData();
								echo json_encode($Iteie);
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
							$Iteie = Iteie::find($id,array('include'=>array('employee'=>array('company','department','designation','grade'))));
							$olddata = $Iteie->to_array();
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
									$Iteie->$key=$value;
								}
								$Iteie->save();
								
								if (isset($data['depthead'])){
									$joins   = "LEFT JOIN tbl_approver ON (tbl_iteieapproval.approver_id = tbl_approver.id) ";					
									$dx = Iteieapproval::find('all',array('joins'=>$joins,'conditions' => array("iteie_id=? and tbl_approver.approvaltype_id=29 and not(tbl_approver.employee_id=?)",$id,$depthead)));	
									foreach ($dx as $result) {
										//delete same type dept head approver
										$result->delete();
										$logger = new Datalogger("Iteieapproval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
									}
									$joins   = "LEFT JOIN tbl_approver ON (tbl_iteieapproval.approver_id = tbl_approver.id) ";					
									$Iteieapproval = Iteieapproval::find('all',array('joins'=>$joins,'conditions' => array("iteie_id=? and tbl_approver.employee_id=?",$id,$depthead)));	
									foreach ($Iteieapproval as &$result) {
										$result		= $result->to_array();
										$result['no']=1;
									}			
									if(count($Iteieapproval)==0){ 
										$Approver = Approver::find('first',array('conditions'=>array("module='IT' and employee_id=? and approvaltype_id=29",$depthead)));
										if(count($Approver)>0){
											$Iteieapproval = new Iteieapproval();
											$Iteieapproval->iteie_id = $Iteie->id;
											$Iteieapproval->approver_id = $Approver->id;
											$Iteieapproval->save();
										}else{
											$approver = new Approver();
											$approver->module = "IT";
											$approver->employee_id=$depthead;
											$approver->sequence=1;
											$approver->approvaltype_id = 29;
											$approver->isfinal = false;
											$approver->save();
											$Iteieapproval = new Iteieapproval();
											$Iteieapproval->iteie_id = $Iteie->id;
											$Iteieapproval->approver_id = $approver->id;
											$Iteieapproval->save();
										}
									}
									
								}
								
								if($data['requeststatus']==1){
									$Iteieapproval = Iteieapproval::find('all', array('conditions' => array("iteie_id=?",$id)));					
									foreach($Iteieapproval as $data){
										$data->approvalstatus=0;
										$data->save();
									}
									$joinx   = "LEFT JOIN tbl_approver ON (tbl_iteieapproval.approver_id = tbl_approver.id) ";					
									$Iteieapproval = Iteieapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and iteie_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
									$username = $Iteieapproval->approver->employee->loginname;
									$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
									$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Iteie->employee->loginname)));
									$email=$usr->email;

									if($Iteie->accessrequested == 1) {
										$accessR = 'Exchange (non-Internet) Email';
									}else if($Iteie->accessrequested == 2) {
										$accessR = 'Internet Email';
									}else if($Iteie->accessrequested == 3) {
										$accessR = 'Change Domain';
									}else {
										$accessR = '';
									}

									if($Iteie->accesstype == 1) {
										$accessT = 'Terminal Server (TS) User Account';
									}else if($Iteie->accesstype == 2) {
										$accessT = 'Non-TS Account';
									}else {
										$accessT = '';
									}

									if($Iteie->accounttype == 1) {
										$accountT = 'Permanent';
									}else if($Iteie->accounttype == 2) {
										$accountT = 'Temporary';
									}else {
										$accountT = '';
									}

									if($Iteie->requesttype == 1) {
										$requestT = 'Grant Access';
									}else if($Iteie->requesttype == 2) {
										$requestT = 'Revoke Access';
									}else {
										$requestT = '';
									}

									if($Iteie->emailquota == 1) {
										$emailQ = '250 MB';
									}else if($Iteie->emailquota == 2) {
										$emailQ = '500 MB';
									}else if($Iteie->emailquota == 3) {
										$emailQ = '1000 MB';
									}else if($Iteie->emailquota == 4) {
										$emailQ = '1500 MB';
									}else if($Iteie->emailquota == 5) {
										$emailQ = '2000 MB';
									}else {
										$emailQ = '';
									}

									if($Iteie->emaildomain == 1) {
										$emailD = 'itci-hutani.com';
									}else if($Iteie->emaildomain == 2) {
										$emailD = 'kalimantan-prima.com';
									}else if($Iteie->emaildomain == 3) {
										$emailD = 'balikpapanchip.com';
									}else if($Iteie->emaildomain == 4) {
										$emailD = 'lajudinamika.com';
									}else if($Iteie->emaildomain == 5) {
										$emailD = 'ptadindo.com';
									}else if($Iteie->emaildomain == 6) {
										$emailD = 'D1.LCL';
									}else {
										$emailD = '';
									}

									if($Iteie->listgroup == 1) {
										$listG = 'IHM';
									}else if($Iteie->listgroup == 2) {
										$listG = 'KPSI';
									}else if($Iteie->listgroup == 3) {
										$listG = 'BCL';
									}else if($Iteie->listgroup == 4) {
										$listG = 'LDU';
									}else if($Iteie->listgroup == 5) {
										$listG = 'Adindo';
									}else {
										$listG = '';
									}

									if($Iteie->listgroupmoderation == 1) {
										$listGM = 'Mod-IHM';
									}else if($Iteie->listgroupmoderation == 2) {
										$listGM = 'Mod-BCL';
									}else if($Iteie->listgroupmoderation == 3) {
										$listGM = 'Mod-KDU-HRD';
									}else if($Iteie->listgroupmoderation == 4) {
										$listGM = 'Mod-KF-Head';
									}else if($Iteie->listgroupmoderation == 5) {
										$listGM = 'Mod-KF-Head2';
									}else if($Iteie->listgroupmoderation == 6) {
										$listGM = 'Mod-KPSI-Pro';
									}else if($Iteie->listgroupmoderation == 7) {
										$listGM = 'Mod-KDU-FA';
									}else {
										$listGM = '';
									}

									$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$adb->fullname.',</span></p>
										<p class=MsoNormal><span style="color:#1F497D">new Exchange - Internet Email Request is awaiting for your approval:</span></p>
										<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Iteie->employee->fullname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Iteie->employee->sapid.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Iteie->employee->designation->designationname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Iteie->employee->company->companyname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Iteie->employee->location->location.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
										</table>
										<p class=MsoNormal><b>Exchange/Internet Email Request Form</b></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										
										<tr><th><p class=MsoNormal>Date</small></p></th>
											<th><p class=MsoNormal>Name</p></th>
											<th><p class=MsoNormal>Employee ID</p></th>
											<th><p class=MsoNormal>Designation</p></th>
											<th><p class=MsoNormal>Grade</p></th>
											<th><p class=MsoNormal>BG/BU</p></th>
											<th><p class=MsoNormal>Office/Location</p></th>
											<th><p class=MsoNormal>Floor</p></th>
											<th><p class=MsoNormal>Phone(Ext)</p></th>
											<th><p class=MsoNormal>Department</p></th>
										</tr>
										<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Iteie->createddate)).'</p></td>
											<td><p class=MsoNormal> '.$Iteie->name.'</p></td>
											<td><p class=MsoNormal> '.$Iteie->employeeid.'</p></td>
											<td><p class=MsoNormal> '.$Iteie->designation.'</p></td>
											<td><p class=MsoNormal> '.$Iteie->grade.'</p></td>
											<td><p class=MsoNormal> '.$Iteie->bgbu.'</p></td>
											<td><p class=MsoNormal> '.$Iteie->officelocation.'</p></td>
											<td><p class=MsoNormal> '.$Iteie->floor.'</p></td>
											<td><p class=MsoNormal> '.$Iteie->phoneext.'</p></td>
											<td><p class=MsoNormal> '.$Iteie->department.'</p></td>
										</tr>
										</table>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr>
											<th><p class=MsoNormal>Access Requested</p></th>
											<th><p class=MsoNormal>Access Type</p></th>
											<th><p class=MsoNormal>Account Type</p></th>
											<th><p class=MsoNormal>Request Type</p></th>
											<th><p class=MsoNormal>Email Quota</p></th>
											<th><p class=MsoNormal>Valid From</p></th>
											<th><p class=MsoNormal>Valid To</p></th>
											<th><p class=MsoNormal>Email Domain</p></th>
											<th><p class=MsoNormal>List Group</p></th>
											<th><p class=MsoNormal>List Group Moderation</p></th>
											<th><p class=MsoNormal>Iron Port Quarantine</p></th>
										</tr>
										<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.$accessR.'</p></td>
											<td><p class=MsoNormal> '.$accessT.'</p></td>
											<td><p class=MsoNormal> '.$accountT.'</p></td>
											<td><p class=MsoNormal> '.$requestT.'</p></td>
											<td><p class=MsoNormal> '.$emailQ.'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Iteie->validfrom)).'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Iteie->validto)).'</p></td>
											<td><p class=MsoNormal> '.$emailD.'</p></td>
											<td><p class=MsoNormal> '.$listG.'</p></td>
											<td><p class=MsoNormal> '.$listGM.'</p></td>
											<td><p class=MsoNormal> '.$Iteie->ironportquarantinedetail.'</p></td>
										</tr>
										';

									$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
									$this->mail->addAddress($adb->email, $adb->fullname);
									$this->mail->Subject = "Online Approval System -> new Exchange - Internet Email Request";
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
									$Iteiehistory = new Iteiehistory();
									$Iteiehistory->date = date("Y-m-d h:i:s");
									$Iteiehistory->fullname = $Employee->fullname;
									$Iteiehistory->iteie_id = $id;
									$Iteiehistory->approvaltype = "Originator";
									$Iteiehistory->actiontype = 2;
									$Iteiehistory->save();
								}else{
									$Iteiehistory = new Iteiehistory();
									$Iteiehistory->date = date("Y-m-d h:i:s");
									$Iteiehistory->fullname = $Employee->fullname;
									$Iteiehistory->iteie_id = $id;
									$Iteiehistory->approvaltype = "Originator";
									$Iteiehistory->actiontype = 1;
									$Iteiehistory->save();
								}
								$logger = new Datalogger("ITEIE","update",json_encode($olddata),json_encode($data));
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
						$Iteie = Iteie::all();
						foreach ($Iteie as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Iteie, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}
	function iteieApproval(){
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
							$join   = "LEFT JOIN tbl_approver ON (tbl_iteieapproval.approver_id = tbl_approver.id) ";
							$Iteieapproval = Iteieapproval::find('all', array('joins'=>$join,'conditions' => array("iteie_id=?",$id),'include' => array('approver'=>array('approvaltype')),"order"=>"tbl_approver.sequence"));
							foreach ($Iteieapproval as &$result) {
								$approvaltype = $result->approver->approvaltype_id;
								$result		= $result->to_array();
								$result['approvaltype']=$approvaltype;
							}
							echo json_encode($Iteieapproval, JSON_NUMERIC_CHECK);
						}else{
							$Iteieapproval = new Iteieapproval();
							echo json_encode($Iteieapproval);
						}
						break;
					case 'find':
						$query=$this->post['query'];		
						if(isset($query['status'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join   = "LEFT JOIN tbl_approver ON (tbl_iteieapproval.approver_id = tbl_approver.id) ";
							$dx = Iteieapproval::find('first', array('joins'=>$join,'conditions' => array("iteie_id=? and tbl_approver.employee_id = ?",$query['iteie_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
							$Iteie = Iteie::find($query['iteie_id']);
							// print_r($dx);
							if($dx->approver->isfinal==1){
								$data=array("jml"=>1);
							}else{
								$join   = "LEFT JOIN tbl_approver ON (tbl_iteieapproval.approver_id = tbl_approver.id) ";
								$Iteieapproval = Iteieapproval::find('all', array('joins'=>$join,'conditions' => array("iteie_id=? and ApprovalStatus<=1 and not tbl_approver.employee_id=?",$query['iteie_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
								foreach ($Iteieapproval as &$result) {
									$fullname	= $result->approver->employee->fullname;
									$result		= $result->to_array();
									$result['fullname']=$fullname;
								}
								$data=array("jml"=>count($Iteieapproval));
							}						
						} else if(isset($query['pending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
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
								$department	= $result->employee->department->departmentname;
								$result		= $result->to_array();
								$result['fullname']=$fullname;
								$result['department']=$department;
							}
							$data=$Iteie;
						} else if(isset($query['mypending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
							$Iteie = Iteie::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
							$jml=0;
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
							$data=array("jml"=>count($Iteie));
						} else if(isset($query['filter'])){
							$join = "LEFT JOIN vwiteiereport v on tbl_iteie.id=v.id";
							$sel = 'tbl_iteie.*, v.laststatus,v.personholding ';
							$Iteie = Iteie::find('all',array('joins'=>$join,'select'=>$sel,'include' => array('employee')));
							foreach ($Iteie as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=$Iteie;
						} else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Iteieapproval = Iteieapproval::create($data);
						$logger = new Datalogger("Iteieapproval","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':
						$id = $this->post['id'];
						$Iteieapproval = Iteieapproval::find($id);
						$data=$Iteieapproval->to_array();
						$Iteieapproval->delete();
						$logger = new Datalogger("Iteieapproval","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Iteieapproval);
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
						$Iteie = Iteie::find($doid);
						$join   = "LEFT JOIN tbl_approver ON (tbl_iteieapproval.approver_id = tbl_approver.id) ";
						if (isset($data['mode'])){
							$Iteieapproval = Iteieapproval::find('first', array('joins'=>$join,'conditions' => array("iteie_id=? and tbl_approver.employee_id=?",$doid,$Employee->id),'include' => array('approver'=>array('employee','approvaltype'))));
							unset($data['mode']);
						}else{
							$Iteieapproval = Iteieapproval::find($this->post['id'],array('include' => array('approver'=>array('employee','approvaltype'))));
						}
						foreach($data as $key=>$val) {
							if(($key !== 'approvalstatus') && ($key !== 'approvaldate') && ($key !== 'remarks')) {
								// if(($key == 'isrepair') || ($key == 'isscrap')) {
									$value=(($val===0) || ($val==='0') || ($val==='false'))?false:((($val===1) || ($val==='1') || ($val==='true'))?true:$val);
								// }
								$Iteie->$key=$value;
							}
						}
						$Iteie->save();

						// unset($data['materialdispatchno']);
						// unset($data['isrepair']);
						// unset($data['isscrap']);
						// unset($data['estimatecost']);
						// unset($data['pono']);
						// unset($data['materialreturneddate']);
						// unset($data['supplierdodnno']);
						// unset($data['buyer']);
						
						
						$olddata = $Iteieapproval->to_array();
						foreach($data as $key=>$val){
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$Iteieapproval->$key=$val;
						}
						$Iteieapproval->save();
						$logger = new Datalogger("Iteieapproval","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						if (isset($mode) && ($mode=='approve')){
							$Iteie = Iteie::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));
							$joinx   = "LEFT JOIN tbl_approver ON (tbl_iteieapproval.approver_id = tbl_approver.id) ";					
							$nTrapproval = Iteieapproval::find('first',array('joins'=>$joinx,'conditions' => array("iteie_id=? and ApprovalStatus=0",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
							$username = $nTrapproval->approver->employee->loginname;
							$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
							// $Iteieschedule=Trschedule::find('all',array('conditions'=>array("iteie_id=?",$doid),'include'=>array('iteie'=>array('employee'=>array('company','department','designation','grade','location')))));
							// $Iteieticket=Trticket::find('all',array('conditions'=>array("iteie_id=?",$doid),'include'=>array('iteie'=>array('employee'=>array('company','department','designation','grade','location')))));
							$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Iteie->employee->loginname)));
							$email=$usr->email;
							$superiorId=$Iteie->depthead;
							$Superior = Employee::find($superiorId);
							$supAdb = Addressbook::find('first',array('conditions'=>array("username=?",$Superior->loginname)));
							$complete = false;
							$Iteiehistory = new Iteiehistory();
							$Iteiehistory->date = date("Y-m-d h:i:s");
							$Iteiehistory->fullname = $Employee->fullname;
							$Iteiehistory->approvaltype = $Iteieapproval->approver->approvaltype->approvaltype;
							$Iteiehistory->remarks = $data['remarks'];
							$Iteiehistory->iteie_id = $doid;
							
							switch ($data['approvalstatus']){
								case '1':
									$Iteie->requeststatus = 2;
									$emto=$email;$emname=$Iteie->employee->fullname;
									$this->mail->Subject = "Online Approval System -> Need Rework";
									$red = 'Your Exchange-Internet Email require some rework :
											<br>Remarks from Approver : <font color="red">'.$data['remarks']."</font>";
									$Iteiehistory->actiontype = 3;
									break;
								case '2':
									if ($Iteieapproval->approver->isfinal == 1){
										$Iteie->requeststatus = 3;
										$emto=$email;$emname=$Iteie->employee->fullname;
										$this->mail->Subject = "Online Approval System -> Approval Completed";
										$red = '<p>Your Exchange-Internet Email. request has been approved</p>';
													// '<p><b><span lang=EN-US style=\'color:#002060\'>Note : Please <u>forward</u> this electronic approval to your respective Human Resource Department.</span></b></p>';
										//delete unnecessary approver
										$Iteieapproval = Iteieapproval::find('all', array('joins'=>$join,'conditions' => array("iteie_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
										foreach ($Iteieapproval as $data) {
											if($data->approvalstatus==0){
												$logger = new Datalogger("Iteieapproval","delete",json_encode($data->to_array()),"automatic remove unnecessary approver by system");
												$logger->SaveData();
												$data->delete();
											}
										}
										$complete =true;
									}
									else{
										$Iteie->requeststatus = 1;
										$emto=$adb->email;$emname=$adb->fullname;
										$this->mail->Subject = "Online Approval System -> new Exchange-Internet Email Request";
										$red = 'new Exchange-Internet Email Request awaiting for your approval:';
									}
									$Iteiehistory->actiontype = 4;							
									break;
								case '3':
									$Iteie->requeststatus = 4;
									$emto=$email;$emname=$Iteie->employee->fullname;
									$Iteiehistory->actiontype = 5;
									$this->mail->Subject = "Online Approval System -> Request Rejected";
									$red = 'Your Exchange-Internet Email Request has been rejected
											<br>Remarks from Approver : <font color="red">'.$data['remarks']."</font>";
									break;
								default:
									break;
							}
							$Iteie->save();
							$Iteiehistory->save();
							echo "email to :".$emto." ->".$emname;
							$this->mail->addAddress($emto, $emname);
							$IteieJ = Iteie::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));
							if($Iteie->accessrequested == 1) {
								$accessR = 'Exchange (non-Internet) Email';
							}else if($Iteie->accessrequested == 2) {
								$accessR = 'Internet Email';
							}else if($Iteie->accessrequested == 3) {
								$accessR = 'Change Domain';
							}else {
								$accessR = '';
							}

							if($Iteie->accesstype == 1) {
								$accessT = 'Terminal Server (TS) User Account';
							}else if($Iteie->accesstype == 2) {
								$accessT = 'Non-TS Account';
							}else {
								$accessT = '';
							}

							if($Iteie->accounttype == 1) {
								$accountT = 'Permanent';
							}else if($Iteie->accounttype == 2) {
								$accountT = 'Temporary';
							}else {
								$accountT = '';
							}

							if($Iteie->requesttype == 1) {
								$requestT = 'Grant Access';
							}else if($Iteie->requesttype == 2) {
								$requestT = 'Revoke Access';
							}else {
								$requestT = '';
							}

							if($Iteie->emailquota == 1) {
								$emailQ = '250 MB';
							}else if($Iteie->emailquota == 2) {
								$emailQ = '500 MB';
							}else if($Iteie->emailquota == 3) {
								$emailQ = '1000 MB';
							}else if($Iteie->emailquota == 4) {
								$emailQ = '1500 MB';
							}else if($Iteie->emailquota == 5) {
								$emailQ = '2000 MB';
							}else {
								$emailQ = '';
							}

							if($Iteie->emaildomain == 1) {
								$emailD = 'itci-hutani.com';
							}else if($Iteie->emaildomain == 2) {
								$emailD = 'kalimantan-prima.com';
							}else if($Iteie->emaildomain == 3) {
								$emailD = 'balikpapanchip.com';
							}else if($Iteie->emaildomain == 4) {
								$emailD = 'lajudinamika.com';
							}else if($Iteie->emaildomain == 5) {
								$emailD = 'ptadindo.com';
							}else if($Iteie->emaildomain == 6) {
								$emailD = 'D1.LCL';
							}else {
								$emailD = '';
							}

							if($Iteie->listgroup == 1) {
								$listG = 'IHM';
							}else if($Iteie->listgroup == 2) {
								$listG = 'KPSI';
							}else if($Iteie->listgroup == 3) {
								$listG = 'BCL';
							}else if($Iteie->listgroup == 4) {
								$listG = 'LDU';
							}else if($Iteie->listgroup == 5) {
								$listG = 'Adindo';
							}else {
								$listG = '';
							}

							if($Iteie->listgroupmoderation == 1) {
								$listGM = 'Mod-IHM';
							}else if($Iteie->listgroupmoderation == 2) {
								$listGM = 'Mod-BCL';
							}else if($Iteie->listgroupmoderation == 3) {
								$listGM = 'Mod-KDU-HRD';
							}else if($Iteie->listgroupmoderation == 4) {
								$listGM = 'Mod-KF-Head';
							}else if($Iteie->listgroupmoderation == 5) {
								$listGM = 'Mod-KF-Head2';
							}else if($Iteie->listgroupmoderation == 6) {
								$listGM = 'Mod-KPSI-Pro';
							}else if($Iteie->listgroupmoderation == 7) {
								$listGM = 'Mod-KDU-FA';
							}else {
								$listGM = '';
							}
							$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$emname.',</span></p>
										<p class=MsoNormal><span style="color:#1F497D">'.$red.'</span></p>
										<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Iteie->employee->fullname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Iteie->employee->sapid.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Iteie->employee->designation->designationname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Iteie->employee->company->companyname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Iteie->employee->location->location.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
										</table>
										<p class=MsoNormal><b>Exchange/Internet Email Request Form</b></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										
										<tr><th><p class=MsoNormal>Date</small></p></th>
											<th><p class=MsoNormal>Name</p></th>
											<th><p class=MsoNormal>Employee ID</p></th>
											<th><p class=MsoNormal>Designation</p></th>
											<th><p class=MsoNormal>Grade</p></th>
											<th><p class=MsoNormal>BG/BU</p></th>
											<th><p class=MsoNormal>Office/Location</p></th>
											<th><p class=MsoNormal>Floor</p></th>
											<th><p class=MsoNormal>Phone(Ext)</p></th>
											<th><p class=MsoNormal>Department</p></th>
										</tr>
										<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Iteie->createddate)).'</p></td>
											<td><p class=MsoNormal> '.$Iteie->name.'</p></td>
											<td><p class=MsoNormal> '.$Iteie->employeeid.'</p></td>
											<td><p class=MsoNormal> '.$Iteie->designation.'</p></td>
											<td><p class=MsoNormal> '.$Iteie->grade.'</p></td>
											<td><p class=MsoNormal> '.$Iteie->bgbu.'</p></td>
											<td><p class=MsoNormal> '.$Iteie->officelocation.'</p></td>
											<td><p class=MsoNormal> '.$Iteie->floor.'</p></td>
											<td><p class=MsoNormal> '.$Iteie->phoneext.'</p></td>
											<td><p class=MsoNormal> '.$Iteie->department.'</p></td>
										</tr>
										</table>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr>
											<th><p class=MsoNormal>Access Requested</p></th>
											<th><p class=MsoNormal>Access Type</p></th>
											<th><p class=MsoNormal>Account Type</p></th>
											<th><p class=MsoNormal>Request Type</p></th>
											<th><p class=MsoNormal>Email Quota</p></th>
											<th><p class=MsoNormal>Valid From</p></th>
											<th><p class=MsoNormal>Valid To</p></th>
											<th><p class=MsoNormal>Email Domain</p></th>
											<th><p class=MsoNormal>List Group</p></th>
											<th><p class=MsoNormal>List Group Moderation</p></th>
											<th><p class=MsoNormal>Iron Port Quarantine</p></th>
										</tr>
										<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.$accessR.'</p></td>
											<td><p class=MsoNormal> '.$accessT.'</p></td>
											<td><p class=MsoNormal> '.$accountT.'</p></td>
											<td><p class=MsoNormal> '.$requestT.'</p></td>
											<td><p class=MsoNormal> '.$emailQ.'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Iteie->validfrom)).'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Iteie->validto)).'</p></td>
											<td><p class=MsoNormal> '.$emailD.'</p></td>
											<td><p class=MsoNormal> '.$listG.'</p></td>
											<td><p class=MsoNormal> '.$listGM.'</p></td>
											<td><p class=MsoNormal> '.$Iteie->ironportquarantinedetail.'</p></td>
										</tr>
										';
							$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
							$this->mail->msgHTML($this->mailbody);
							if ($complete){
								$fileName = $this->generatePDF($doid);
								$filePath = SITE_PATH.DS.$fileName;
								$this->mail->addAttachment($filePath);
							}
							if (!$this->mail->send()) {
								$err = new Errorlog();
								$err->errortype = "ITEIE Mail";
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
						$Iteieapproval = Iteieapproval::all();
						foreach ($Iteieapproval as $result) {
							$result = $result->to_array();
						}
						echo json_encode($Iteieapproval, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}
	function generatePDF($id){
		$Iteie = Iteie::find($id);
		$superiorId=$Iteie->depthead;
		$Superior = Employee::find($superiorId);
		$supAdb = Addressbook::find('first',array('conditions'=>array("username=?",$Superior->loginname)));
		$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Iteie->employee->loginname)));
		$email=$usr->email;

		$datefrom = date("d/m/Y",strtotime($Iteie->validfrom));
		$dateto = date("d/m/Y",strtotime($Iteie->validto));

		$joinx   = "LEFT JOIN tbl_approver ON (tbl_iteieapproval.approver_id = tbl_approver.id) ";					
		$Iteieapproval = Iteieapproval::find('all',array('joins'=>$joinx,'conditions' => array("iteie_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee','approvaltype'))));							
		foreach ($Iteieapproval as $data){
			if(($data->approver->approvaltype->id==29) || ($data->approver->employee_id==$Mmf30->depthead)){
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
		}

		if($Iteie->accessrequested == 1) {
			$accessR = 'Exchange (non-Internet) Email';
			}else if($Iteie->accessrequested == 2) {
				$accessR = 'Internet Email';
			}else if($Iteie->accessrequested == 3) {
				$accessR = 'Change Domain';
			}else {
				$accessR = '';
		}

		if($Iteie->accesstype == 1) {
			$accessT = 'Terminal Server (TS) User Account';
			}else if($Iteie->accesstype == 2) {
				$accessT = 'Non-TS Account';
			}else {
				$accessT = '';
		}

		if($Iteie->accounttype == 1) {
			$accountT = 'Permanent';
			}else if($Iteie->accounttype == 2) {
				$accountT = 'Temporary';
			}else {
				$accountT = '';
		}

		if($Iteie->requesttype == 1) {
			$requestT = 'Grant Access';
			}else if($Iteie->requesttype == 2) {
				$requestT = 'Revoke Access';
			}else {
				$requestT = '';
		}

		if($Iteie->emailquota == 1) {
			$emailQ = '250 MB';
			}else if($Iteie->emailquota == 2) {
				$emailQ = '500 MB';
			}else if($Iteie->emailquota == 3) {
				$emailQ = '1000 MB';
			}else if($Iteie->emailquota == 4) {
				$emailQ = '1500 MB';
			}else if($Iteie->emailquota == 5) {
				$emailQ = '2000 MB';
			}else {
				$emailQ = '';
		}

		if($Iteie->emaildomain == 1) {
			$emailD = 'itci-hutani.com';
			}else if($Iteie->emaildomain == 2) {
				$emailD = 'kalimantan-prima.com';
			}else if($Iteie->emaildomain == 3) {
				$emailD = 'balikpapanchip.com';
			}else if($Iteie->emaildomain == 4) {
				$emailD = 'lajudinamika.com';
			}else if($Iteie->emaildomain == 5) {
				$emailD = 'ptadindo.com';
			}else if($Iteie->emaildomain == 6) {
				$emailD = 'D1.LCL';
			}else {
				$emailD = '';
		}

		if($Iteie->listgroup == 1) {
			$listG = 'IHM';
			}else if($Iteie->listgroup == 2) {
				$listG = 'KPSI';
			}else if($Iteie->listgroup == 3) {
				$listG = 'BCL';
			}else if($Iteie->listgroup == 4) {
				$listG = 'LDU';
			}else if($Iteie->listgroup == 5) {
				$listG = 'Adindo';
			}else {
				$listG = '';
		}

		if($Iteie->listgroupmoderation == 1) {
			$listGM = 'Mod-IHM';
			}else if($Iteie->listgroupmoderation == 2) {
				$listGM = 'Mod-BCL';
			}else if($Iteie->listgroupmoderation == 3) {
				$listGM = 'Mod-KDU-HRD';
			}else if($Iteie->listgroupmoderation == 4) {
				$listGM = 'Mod-KF-Head';
			}else if($Iteie->listgroupmoderation == 5) {
				$listGM = 'Mod-KF-Head2';
			}else if($Iteie->listgroupmoderation == 6) {
				$listGM = 'Mod-KPSI-Pro';
			}else if($Iteie->listgroupmoderation == 7) {
				$listGM = 'Mod-KDU-FA';
			}else {
				$listGM = '';
		}
		
		$pdfContent = '
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

			<html>
			<head>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<style type="text/css">
				.tbl{
					width: 655pt;
					border: 1px solid #000;
					font-size:10px;
					font-family: freeserif; 
					padding:2px;
				}
				.tbl2{
					border: 1px solid #000;
					// font-size:10px;
					font-family: freeserif; 
					padding:2px;
				}
				</style>
				
			</head>

			<body>
			<table class="tbl" CELLSPACING="0" cellpadding="2">
				<colgroup width="35"></colgroup>
				<colgroup span="6" width="27"></colgroup>
				<colgroup width="32"></colgroup>
				<colgroup span="6" width="27"></colgroup>
				<colgroup width="32"></colgroup>
				<colgroup span="10" width="27"></colgroup>
				<colgroup span="2" width="29"></colgroup>
				<tr>
					<td colspan=3 height="25" align="center" valign=middle bgcolor="#008000"><b><font face="Calibri" size=4 color="#FFFFFF">D1</font></b></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td colspan=17 align="right" valign=bottom><b>Exchange/Internet Email Request Form</b></td>
					<td align="left" valign=bottom><br></td>
				</tr>

				<tr>
					<td colspan=10 height="22" align="center" valign=bottom><i><font face="Arial Narrow">* Please have this form duly signed and returned to  IT</font></i></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>

				<tr>
					<td height="10" align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="18" align="left" valign=bottom><b>Name:</b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=10 align="left" valign=middle>'.$Iteie->name.'</td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="6" align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="18" align="left" valign=bottom><b>Employee ID:</b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=10 align="left" valign=middle>'.$Iteie->employeeid.'</td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="6" align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="18" align="left" valign=bottom><b>Designation:</b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=10 align="left" valign=middle>'.$Iteie->designation.'</td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><b>Grade:</b></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=2 align="center" valign=bottom>'.$Iteie->grade.'</td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="5" align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="18" align="left" valign=bottom><b>BG/BU:</b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=10 align="left" valign=middle>'.$Iteie->bgbu.'</td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="5" align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="18" align="left" valign=bottom><b>Office/Location:</b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=10 align="left" valign=bottom>'.$Iteie->officelocation.'</td>
					<td align="center" valign=middle><br></td>
					<td align="left" valign=bottom><b>Floor:</b></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=2 align="center" valign=bottom>'.$Iteie->floor.'</td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="5" align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="18" align="left" valign=bottom><b>Phone(Ext):</b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=10 align="left" valign=middle>'.$Iteie->phoneext.'</td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="5" align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><font size=4><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="18" align="left" valign=bottom><b>Department</b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=10 align="left" valign=middle>'.$Iteie->department.'</td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="11" align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="center" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="center" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="center" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="center" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="center" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="17" align="left" valign=bottom bgcolor="#FFFFFF"><b>Access Requested:</b></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><b><br></b></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><b><br></b></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom><td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle>
										<b>'.(($Iteie->accessrequested == 1)?'x':'&nbsp;&nbsp;').'</b></td></td>
					<td colspan=6 align="left" valign=bottom><i><font face="Arial Narrow">Exchange (non-Internet) Email</font></i></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle>
										<b>'.(($Iteie->accessrequested == 2)?'x':'&nbsp;&nbsp;').'</b></td></td>
					<td colspan=4 align="left" valign=bottom bgcolor="#FFFFFF"><font face="Arial Narrow">Internet Email</font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle>
										<b>'.(($Iteie->accessrequested == 3)?'x':'&nbsp;&nbsp;').'</b></td></td>
					<td colspan=3 align="left" valign=bottom bgcolor="#FFFFFF"><font face="Arial Narrow">Change Domain</font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><i><font face="Arial Narrow"><br></font></i></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="11" align="left" valign=bottom bgcolor="#FFFFFF"><b><br></b></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><b><br></b></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><b><br></b></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font size=4><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><i><font face="Arial Narrow"><br></font></i></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font size=4><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><i><font face="Arial Narrow"><br></font></i></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><i><font face="Arial Narrow"><br></font></i></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font size=4><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><i><font face="Arial Narrow"><br></font></i></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="17" align="left" valign=bottom bgcolor="#FFFFFF"><b>Access Type:</b></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><b><br></b></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><b><br></b></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle>
										<b>'.(($Iteie->accesstype == 1)?'x':'&nbsp;&nbsp;').'</b></td></td>
					<td colspan=6 align="left" valign=bottom bgcolor="#FFFFFF"><i><font face="Arial Narrow">Terminal Server (TS) User Account</font></i></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle>
										<b>'.(($Iteie->accesstype == 2)?'x':'&nbsp;&nbsp;').'</b></td></td>
					<td colspan=3 align="left" valign=bottom bgcolor="#FFFFFF"><i><font face="Arial Narrow">Non-TS Account</font></i></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font size=4><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><i><font face="Arial Narrow"><br></font></i></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font size=4><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><i><font face="Arial Narrow"><br></font></i></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="11" align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="center" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="center" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="center" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="center" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="center" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="17" align="left" valign=bottom bgcolor="#FFFFFF"><b>Account Type:</b></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><b><br></b></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><b><br></b></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle>
										<b>'.(($Iteie->accounttype == 1)?'x':'&nbsp;&nbsp;').'</b></td></td>
					<td colspan=2 align="left" valign=bottom bgcolor="#FFFFFF"><i><font face="Arial Narrow">Permanent </font></i></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font size=4><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle>
										<b>'.(($Iteie->accounttype == 2)?'x':'&nbsp;&nbsp;').'</b></td></td>
					<td colspan=2 align="left" valign=bottom bgcolor="#FFFFFF"><i><font face="Arial Narrow">Temporary</font></i></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font size=4><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><i><font face="Arial Narrow"><br></font></i></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font size=4><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><i><font face="Arial Narrow"><br></font></i></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="17" align="left" valign=bottom bgcolor="#FFFFFF"><b>Request Type:</b></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><b><br></b></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><b><br></b></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle>
										<b>'.(($Iteie->requesttype == 1)?'x':'&nbsp;&nbsp;').'</b></td></td>
					<td colspan=3 align="left" valign=bottom bgcolor="#FFFFFF"><i><font face="Arial Narrow">Grant Access</font></i></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle>
										<b>'.(($Iteie->requesttype == 2)?'x':'&nbsp;&nbsp;').'</b></td></td>
					<td colspan=3 align="left" valign=bottom><font face="Arial Narrow">Revoke Access</font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><i><font face="Arial Narrow"><br></font></i></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font size=4><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><i><font face="Arial Narrow"><br></font></i></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font size=4><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><i><font face="Arial Narrow"><br></font></i></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="11" align="left" valign=bottom bgcolor="#FFFFFF"><b><br></b></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><b><br></b></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><b><br></b></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font size=4><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><i><font face="Arial Narrow"><br></font></i></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font size=4><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><i><font face="Arial Narrow"><br></font></i></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><i><font face="Arial Narrow"><br></font></i></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font size=4><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><i><font face="Arial Narrow"><br></font></i></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font size=4><br></font></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><i><font face="Arial Narrow"><br></font></i></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="17" align="left" valign=bottom><b>Email Quota:</b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle>
										<b>'.(($Iteie->emailquota == 1)?'x':'&nbsp;&nbsp;').'</b></td></td>
					<td colspan=2 align="left" valign=bottom>250MB</td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle>
										<b>'.(($Iteie->emailquota == 2)?'x':'&nbsp;&nbsp;').'</b></td></td>
					<td colspan=2 align="left" valign=bottom>500MB</td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle>
										<b>'.(($Iteie->emailquota == 3)?'x':'&nbsp;&nbsp;').'</b></td></td>
					<td colspan=2 align="left" valign=bottom>1000MB</td>
					<td align="center" valign=bottom><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle>
										<b>'.(($Iteie->emailquota == 4)?'x':'&nbsp;&nbsp;').'</b></td></td>
					<td colspan=2 align="left" valign=bottom>1500MB</td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle>
										<b>'.(($Iteie->emailquota == 5)?'x':'&nbsp;&nbsp;').'</b></td></td>
					<td colspan=2 align="left" valign=bottom>2000MB</td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="11" align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font size=4><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="center" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font size=4><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font size=4><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="center" valign=bottom><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font size=4><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="center" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom bgcolor="#FFFFFF"><font size=4><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="18" align="left" valign=bottom><b>Valid From:</b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=6 align="left" valign=middle>'.$datefrom.'</td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><b>To:</b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=6 align="left" valign=middle>'.$dateto.'</td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="18" align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="center" valign=bottom><font face="Arial Narrow">dd</font></td>
					<td align="center" valign=bottom><font face="Arial Narrow">dd</font></td>
					<td align="center" valign=bottom><font face="Arial Narrow">mm</font></td>
					<td align="center" valign=bottom><font face="Arial Narrow">mm</font></td>
					<td align="center" valign=bottom><font face="Arial Narrow">yy</font></td>
					<td align="center" valign=bottom><font face="Arial Narrow">yy</font></td>
					<td align="center" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="center" valign=bottom><b><font face="Arial Narrow"><br></font></b></td>
					<td align="center" valign=bottom><b><font face="Arial Narrow"><br></font></b></td>
					<td align="center" valign=bottom><font face="Arial Narrow">dd</font></td>
					<td align="center" valign=bottom><font face="Arial Narrow">dd</font></td>
					<td align="center" valign=bottom><font face="Arial Narrow">mm</font></td>
					<td align="center" valign=bottom><font face="Arial Narrow">mm</font></td>
					<td align="center" valign=bottom><font face="Arial Narrow">yy</font></td>
					<td align="center" valign=bottom><font face="Arial Narrow">yy</font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td colspan=10 height="17" align="center" valign=bottom><b>Email Domain (select one, if applicable)**:</b></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><b><u><font face="Arial Narrow"><br></font></u></b></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><b><u><font face="Arial Narrow"><br></font></u></b></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><b><u><font face="Arial Narrow"><br></font></u></b></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="11" align="left" valign=bottom><b><u><font face="Arial Narrow"><br></font></u></b></td>
					<td align="left" valign=bottom><b><u><font face="Arial Narrow"><br></font></u></b></td>
					<td align="left" valign=bottom><b><u><font face="Arial Narrow"><br></font></u></b></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><b><u><font face="Arial Narrow"><br></font></u></b></td>
					<td align="left" valign=bottom><b><u><font face="Arial Narrow"><br></font></u></b></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><b><u><font face="Arial Narrow"><br></font></u></b></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><b><u><font face="Arial Narrow"><br></font></u></b></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><b><u><font face="Arial Narrow"><br></font></u></b></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=6 height="19" align="center" valign=middle><b>Email Domain</b></td>
					<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=6 align="center" valign=middle><b>List Group</b></td>
					<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000" colspan=8 align="center" valign=middle><b>List Group Moderation</b></td>
					<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=7 align="center" valign=middle><b>Iron Port Quarantine</b></td>
					</tr>
				<tr>
					<td style="border-left: 1px solid #000000" height="19" align="center" valign=middle>'.(($Iteie->emaildomain == 1)?'✓':'<font face="Arial Narrow" size=4>&#9633;</font>').'</td>
					<td align="left" valign=bottom><font face="Arial Narrow">itci-hutani.com</font></td>
					<td align="left" valign=bottom><font size=4><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><b><i><font face="Arial Narrow"><br></font></i></b></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><br></td>
					<td style="border-left: 1px solid #000000" align="center" valign=middle>'.(($Iteie->listgroup == 1)?'✓':'<font face="Arial Narrow" size=4>&#9633;</font>').'</td>
					<td align="left" valign=bottom><font face="Arial Narrow" color="#000000">IHM</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="center" valign=middle>'.(($Iteie->listgroupmoderation == 1)?'✓':'<font face="Arial Narrow" size=4>&#9633;</font>').'</td>
					<td align="left" valign=bottom><font face="Arial Narrow" color="#000000">Mod-IHM</font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><b><font face="Arial Narrow"><br></font></b></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><b><font face="Arial Narrow"><br></font></b></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><b><font face="Arial Narrow"><br></font></b></td>
					<td style="border-left: 1px solid #000000" align="center" valign=middle><font face="Arial Narrow" size=4>&#9633;</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow" color="#000000"><br></font></td>
					<td align="left" valign=bottom><b><font face="Arial Narrow"><br></font></b></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><b><font face="Arial Narrow"><br></font></b></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td style="border-left: 1px solid #000000" height="19" align="center" valign=middle>'.(($Iteie->emaildomain == 2)?'✓':'<font face="Arial Narrow" size=4>&#9633;</font>').'</td>
					<td colspan="2" align="left" valign=bottom><font face="Arial Narrow">kalimantan-prima.com</font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><i><font face="Arial Narrow"><br></font></i></td>
					<td style="border-left: 1px solid #000000" align="center" valign=middle>'.(($Iteie->listgroup == 2)?'✓':'<font face="Arial Narrow" size=4>&#9633;</font>').'</td>
					<td align="left" valign=bottom><font face="Arial Narrow" color="#000000">KPSI</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="center" valign=middle>'.(($Iteie->listgroupmoderation == 2)?'✓':'<font face="Arial Narrow" size=4>&#9633;</font>').'</td>
					<td align="left" valign=bottom><font face="Arial Narrow" color="#000000">Mod-BCL</font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><b><font face="Arial Narrow"><br></font></b></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><b><font face="Arial Narrow"><br></font></b></td>
					<td style="border-left: 1px solid #000000" align="center" valign=middle><font face="Arial Narrow" size=4>&#9633;</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;.</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><i><font face="Arial Narrow"><br></font></i></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td style="border-left: 1px solid #000000" height="19" align="center" valign=middle>'.(($Iteie->emaildomain == 3)?'✓':'<font face="Arial Narrow" size=4>&#9633;</font>').'</td>
					<td align="left" valign=bottom><font face="Arial Narrow">balikpapanchip.com</font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><br></td>
					<td style="border-left: 1px solid #000000" align="center" valign=middle>'.(($Iteie->listgroup == 3)?'✓':'<font face="Arial Narrow" size=4>&#9633;</font>').'</td>
					<td align="left" valign=bottom><font face="Arial Narrow" color="#000000">BCL</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="center" valign=middle>'.(($Iteie->listgroupmoderation == 3)?'✓':'<font face="Arial Narrow" size=4>&#9633;</font>').'</td>
					<td colspan="2" align="left" valign=bottom><font face="Arial Narrow" color="#000000">Mod-KDU-HRD</font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<!-- <td align="left" valign=bottom><font face="Arial Narrow"><br></font></td> -->
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-left: 1px solid #000000" align="center" valign=middle><font face="Arial Narrow" size=4>&#9633;</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;.</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td style="border-left: 1px solid #000000" height="19" align="center" valign=middle>'.(($Iteie->emaildomain == 4)?'✓':'<font face="Arial Narrow" size=4>&#9633;</font>').'</td>
					<td align="left" valign=bottom><font face="Arial Narrow">lajudinamika.com</font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><br></td>
					<td style="border-left: 1px solid #000000" align="center" valign=middle>'.(($Iteie->listgroup == 4)?'✓':'<font face="Arial Narrow" size=4>&#9633;</font>').'</td>
					<td align="left" valign=bottom><font face="Arial Narrow" color="#000000">LDU</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="center" valign=middle>'.(($Iteie->listgroupmoderation == 4)?'✓':'<font face="Arial Narrow" size=4>&#9633;</font>').'</td>
					<td align="left" valign=bottom><font face="Arial Narrow" color="#000000">Mod-KF-Head</font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-left: 1px solid #000000" align="center" valign=middle><font face="Arial Narrow" size=4>&#9633;</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;.</font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td style="border-left: 1px solid #000000" height="19" align="center" valign=middle>'.(($Iteie->emaildomain == 5)?'✓':'<font face="Arial Narrow" size=4>&#9633;</font>').'</td>
					<td align="left" valign=bottom><font face="Arial Narrow">ptadindo.com</font></td>
					<td align="left" valign=middle><b><br></b></td>
					<td align="left" valign=middle><b><br></b></td>
					<td align="left" valign=middle><b><br></b></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><br></td>
					<td style="border-left: 1px solid #000000" align="center" valign=middle>'.(($Iteie->listgroup == 5)?'✓':'<font face="Arial Narrow" size=4>&#9633;</font>').'</td>
					<td align="left" valign=bottom><font face="Arial Narrow" color="#000000">Adindo</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="center" valign=middle>'.(($Iteie->listgroupmoderation == 5)?'✓':'<font face="Arial Narrow" size=4>&#9633;</font>').'</td>
					<td colspan="2" align="left" valign=bottom><font face="Arial Narrow" color="#000000">Mod-KF-Head2</font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-left: 1px solid #000000" align="center" valign=middle><font face="Arial Narrow" size=4>&#9633;</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;.</font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td style="border-left: 1px solid #000000" height="19" align="center" valign=middle>'.(($Iteie->emaildomain == 6)?'✓':'<font face="Arial Narrow" size=4>&#9633;</font>').'</td>
					<td align="left" valign=bottom><font face="Arial Narrow">D1.LCL</font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><br></td>
					<td style="border-left: 1px solid #000000" align="center" valign=middle><font face="Arial Narrow" size=4>&#9633;</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;.</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="center" valign=middle>'.(($Iteie->listgroupmoderation == 6)?'✓':'<font face="Arial Narrow" size=4>&#9633;</font>').'</td>
					<td colspan="2" align="left" valign=bottom><font face="Arial Narrow" color="#000000">Mod-KPSI-Pro</font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<!-- <td align="left" valign=bottom><font face="Arial Narrow"><br></font></td> -->
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-left: 1px solid #000000" align="center" valign=middle><font face="Arial Narrow" size=4>&#9633;</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;.</font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td style="border-left: 1px solid #000000" height="19" align="center" valign=middle><font face="Arial Narrow" size=4>&#9633;</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;.</font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><br></td>
					<td style="border-left: 1px solid #000000" align="center" valign=middle><font face="Arial Narrow" size=4>&#9633;</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;.</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="center" valign=middle>'.(($Iteie->listgroupmoderation == 7)?'✓':'<font face="Arial Narrow" size=4>&#9633;</font>').'</td>
					<td align="left" valign=bottom><font face="Arial Narrow" color="#000000">Mod-KDU-FA</font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-left: 1px solid #000000" align="center" valign=middle><font face="Arial Narrow" size=4>&#9633;</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;.</font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><br></td>
				</tr>
			
				<tr>
					<td style="border-left: 1px solid #000000" height="19" align="center" valign=middle><font face="Arial Narrow" size=4>&#9633;</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;.</font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-right: 1px solid #000000" align="left" valign=bottom><br></td>
					<td style="border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle><font face="Arial Narrow" size=4>&#9633;</font></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;.</font></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle><font face="Arial Narrow" size=4>&#9633;</font></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;.</font></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><br></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle><font face="Arial Narrow" size=4>&#9633;</font></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;.</font></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><br></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><br></td>
					<td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000" align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td style="border-top: 1px solid #000000" height="11" align="left" valign=bottom><br></td>
					<td style="border-top: 1px solid #000000" align="left" valign=bottom><br></td>
					<td style="border-top: 1px solid #000000" align="left" valign=bottom><br></td>
					<td style="border-top: 1px solid #000000" align="left" valign=bottom><br></td>
					<td style="border-top: 1px solid #000000" align="left" valign=bottom><br></td>
					<td style="border-top: 1px solid #000000" align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="11" align="center" valign=middle><b><font face="Arial Narrow" size=4><br></font></b></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="center" valign=middle><b><font face="Arial Narrow" size=4><br></font></b></td>
					<td align="left" valign=bottom><font face="Arial Narrow" color="#000000"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="center" valign=middle><b><font face="Arial Narrow" size=4><br></font></b></td>
					<td align="left" valign=bottom><font face="Arial Narrow" color="#000000"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="center" valign=middle><b><font face="Arial Narrow" size=4><br></font></b></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="18" align="left" valign=bottom><b>Reason for request</b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=10 rowspan=2 align="left" valign=top>'.$Iteie->reason.'</td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="18" align="left" valign=bottom><b>/ Remarks</b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><b><br></b></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="18" align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr></table>';

				$pdfContent .= '<table class="tbl2" CELLSPACING="0" cellpadding="2">
				<tr>
					<td colspan=3 height="17" align="left" valign=bottom><b><u><font face="Arial Narrow">Approved by:</font></u></b></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td colspan=3 align="left" valign=bottom><b><u><font face="Arial Narrow">Approved by:</font></u></b></td>
					<td align="left" valign=bottom><b><u><font face="Arial Narrow"><br></font></u></b></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td colspan=3 align="left" valign=bottom><b><u><font face="Arial Narrow">Approved by:</font></u></b></td>
					<td align="left" valign=bottom><b><u><font face="Arial Narrow"><br></font></u></b></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td colspan=3 align="left" valign=bottom><b><u><font face="Arial Narrow">Approved by:</font></u></b></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td colspan=2 height="18" align="left" valign=bottom><font face="Arial Narrow">Dept Head</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><b><font face="Arial Narrow"><br></font></b></td>
					<td align="left" valign=bottom><br></td>
					<td colspan=2 align="left" valign=bottom><font face="Arial Narrow">HRD Head </font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><b><font face="Arial Narrow"><br></font></b></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td colspan=5 align="left" valign=bottom><font face="Arial Narrow">BU/BG Head Project Director</font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td colspan=5 align="left" valign=bottom><font face="Arial Narrow">Site IT Service Delivery Lead</font></td>
					<td align="left" valign=bottom><b><font face="Arial Narrow"><br></font></b></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="13" align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td colspan=5 height="17" align="center" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td colspan=5 align="center" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td colspan=5 align="center" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td colspan=6 align="center" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td height="17" align="left" valign=bottom><img src="images/approved.png" width="60"></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><img src="images/approved.png" width="60"></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><br></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><br></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><br></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><img src="images/approved.png" width="60"></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><br></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><br></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><br></td>
					<td style="border-bottom: 1px solid #000000" align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><img src="images/approved.png" width="60"></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
				</tr>
				<tr>
					<td style="border-top: 1px solid #000000" colspan=5 height="23" align="center" valign=middle><font face="Arial Narrow">(Signature)</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td colspan=5 align="center" valign=middle><font face="Arial Narrow">(Signature)</font></td>
					<td align="left" valign=middle><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=middle><font face="Arial Narrow"><br></font></td>
					<td colspan=5 align="center" valign=middle><font face="Arial Narrow">(Signature)</font></td>
					<td align="left" valign=top><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td style="border-top: 1px solid #000000" colspan=6 align="center" valign=middle><font face="Arial Narrow">(Signature)</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
				</tr>
				<tr>
					<td height="17" align="left" valign=bottom><font face="Arial Narrow">Name: '.$deptheadname.'</font></td>
					<td colspan=4 align="left" valign=bottom><font face="Arial Narrow">&nbsp;</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow">Name: '.$hrdname.'</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td colspan=3 align="left" valign=bottom><font face="Arial Narrow">&nbsp;</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow">Name: '.$buheadname.'</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td colspan=3 align="left" valign=bottom><font face="Arial Narrow">&nbsp;</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td align="left" valign=bottom><font face="Arial Narrow">Name: '.$itheadname.'</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td colspan=4 align="left" valign=bottom><font face="Arial Narrow">&nbsp;</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
				</tr>
				<tr>
					<td height="15" align="left" valign=bottom><font face="Arial Narrow">Date: '.$deptheaddate.'</font></td>
					<td colspan=4 align="left" valign=bottom>&nbsp;</td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><font face="Arial Narrow">Date: '.$hrddate.'</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td colspan=3 align="left" valign=bottom><font face="Arial Narrow">&nbsp;</font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><font face="Arial Narrow">Date: '.$buheaddate.'</font></td>
					<td align="left" valign=bottom><font face="Arial Narrow"><br></font></td>
					<td colspan=3 align="left" valign=bottom><font face="Arial Narrow">&nbsp;</font></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><br></td>
					<td align="left" valign=bottom><font face="Arial Narrow">Date: '.$itheaddate.'</font></td>
					<td align="left" valign=bottom><br></td>
					<td colspan=4 align="left" valign=bottom><font face="Arial Narrow">&nbsp;</font></td>
					<td align="left" valign=bottom><br></td>
				</tr>
			</table>
			<!-- ************************************************************************** -->
			</body>

			</html>


		';
		
											
							echo $pdfContent;
											// echo json_encode($Iteie->wonumber, JSON_NUMERIC_CHECK);
		
		try {
			$html2pdf = new Html2Pdf('P', 'A4', 'en','true','UTF-8');
			// $html2pdf->setDefaultFont('freeserif');
			$html2pdf->pdf->SetDisplayMode('fullpage');
			$html2pdf->writeHTML( $pdfContent);
			ob_clean();
			$fileName ='doc'.DS.'it'.DS.'pdf'.DS.'ITEIE'.$Iteie->employee->sapid.'_'.date("YmdHis").'.pdf';
			$fileName = str_replace("/","",$fileName);
			$filePath = SITE_PATH.DS.$fileName;
			$html2pdf->output($filePath, 'F');
			$Iteie->approveddoc=str_replace("\\","/",$fileName);
			$Iteie->save();
			return $fileName;
		} catch (Html2PdfException $e) {
			$html2pdf->clean();
			$formatter = new ExceptionFormatter($e);
			$err = new Errorlog();
			$err->errortype = "IteiePDFGenerator";
			$err->errordate = date("Y-m-d h:i:s");
			$err->errormessage = $formatter->getHtmlMessage();
			$err->user = $this->currentUser->username;
			$err->ip = $this->ip;
			$err->save();
			echo $formatter->getHtmlMessage();
		}
		
	}
	function generatePDFi($id){
		$Iteie = Iteie::find($id);
		$superiorId=$Iteie->depthead;
		$Superior = Employee::find($superiorId);
		$supAdb = Addressbook::find('first',array('conditions'=>array("username=?",$Superior->loginname)));
		$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Iteie->employee->loginname)));
		$email=$usr->email;

		$datefrom = date("d/m/Y",strtotime($Iteie->validfrom));
		$dateto = date("d/m/Y",strtotime($Iteie->validto));

		$joinx   = "LEFT JOIN tbl_approver ON (tbl_iteieapproval.approver_id = tbl_approver.id) ";					
		$Iteieapproval = Iteieapproval::find('all',array('joins'=>$joinx,'conditions' => array("iteie_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee','approvaltype'))));							
		
		//condition
			foreach ($Iteieapproval as $data){
				if(($data->approver->approvaltype->id==29) || ($data->approver->employee_id==$Mmf30->depthead)){
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
			}
		//end condition

		try {
			$excel = new COM("Excel.Application") or die ("ERROR: Unable to instantaniate COM!\r\n");
			$excel->Visible = false;
			$file="D:/xampp/htdocs/oasys_gogs/doc/it/exchange_email_template.xlsx";
			$Workbook = $excel->Workbooks->Open($file) or die("ERROR: Unable to open " . $file . "!\r\n");
			$Worksheet = $Workbook->Worksheets(1);
			$Worksheet->Activate;
			// $xlShiftDown=-4121;
			// for ($a=6;$a<15;$a++){
			// 	$Worksheet->Rows($a+1)->Insert($xlShiftDown);
			// 	$Worksheet->Range("C".$a)->Value = 'Nama '.($a-5);
			// 	$Worksheet->Range("D".$a)->Value = 'Alamat '.($a-5);
			// 	$Worksheet->Range("K".$a)->Value = rand(5,15);
			// }
			// $Worksheet->Range("F7")->Value = 'Riski';
			$Worksheet->Range("F7")->Value = $Iteie->name;
			$Worksheet->Range("F9")->Value = $Iteie->employeeid;
			$Worksheet->Range("F11")->Value = $Iteie->designation;
			$Worksheet->Range("Y11")->Value = $Iteie->grade;
			$Worksheet->Range("F13")->Value = $Iteie->bgbu;
			$Worksheet->Range("F15")->Value = $Iteie->officelocation;
			$Worksheet->Range("Y15")->Value = $Iteie->floor;
			$Worksheet->Range("F17")->Value = $Iteie->phoneext;
			$Worksheet->Range("F19")->Value = $Iteie->department;
			//condition
				if($Iteie->accessrequested == 1) {
					$Worksheet->Range("F21")->Value = 'x';
				}else if($Iteie->accessrequested == 2) {
					$Worksheet->Range("N21")->Value = 'x';
				}else if($Iteie->accessrequested == 3) {
					$Worksheet->Range("T21")->Value = 'x';
				}else {
					$accessR = '';
				}

				if($Iteie->accesstype == 1) {
					$Worksheet->Range("F23")->Value = 'x';
				}else if($Iteie->accesstype == 2) {
					$Worksheet->Range("P23")->Value = 'x';
				}else {
					$accessT = '';
				}
	
				if($Iteie->accounttype == 1) {
					$Worksheet->Range("F25")->Value = 'x';
				}else if($Iteie->accounttype == 2) {
					$Worksheet->Range("P25")->Value = 'x';
				}else {
					$accountT = '';
				}
	
				if($Iteie->requesttype == 1) {
					$Worksheet->Range("F27")->Value = 'x';
				}else if($Iteie->requesttype == 2) {
					$Worksheet->Range("N27")->Value = 'x';
				}else {
					$requestT = '';
				}
	
				if($Iteie->emailquota == 1) {
					$Worksheet->Range("F29")->Value = 'x';
				}else if($Iteie->emailquota == 2) {
					$Worksheet->Range("J29")->Value = 'x';
				}else if($Iteie->emailquota == 3) {
					$Worksheet->Range("N29")->Value = 'x';
				}else if($Iteie->emailquota == 4) {
					$Worksheet->Range("R29")->Value = 'x';
				}else if($Iteie->emailquota == 5) {
					$Worksheet->Range("V29")->Value = 'x';
				}else {
					$emailQ = '';
				}
	
				if($Iteie->emaildomain == 1) {
					$Worksheet->Range("A37")->Value = 'x';
				}else if($Iteie->emaildomain == 2) {
					$Worksheet->Range("A38")->Value = 'x';
				}else if($Iteie->emaildomain == 3) {
					$Worksheet->Range("A39")->Value = 'x';
				}else if($Iteie->emaildomain == 4) {
					$Worksheet->Range("A40")->Value = 'x';
				}else if($Iteie->emaildomain == 5) {
					$Worksheet->Range("A41")->Value = 'x';
				}else if($Iteie->emaildomain == 6) {
					$Worksheet->Range("A42")->Value = 'x';
				}else {
					$emailD = '';
				}
	
				if($Iteie->listgroup == 1) {
					$Worksheet->Range("G37")->Value = 'x';
				}else if($Iteie->listgroup == 2) {
					$Worksheet->Range("G38")->Value = 'x';
				}else if($Iteie->listgroup == 3) {
					$Worksheet->Range("G39")->Value = 'x';
				}else if($Iteie->listgroup == 4) {
					$Worksheet->Range("G40")->Value = 'x';
				}else if($Iteie->listgroup == 5) {
					$Worksheet->Range("G41")->Value = 'x';
				}else {
					$listG = '';
				}
	
				if($Iteie->listgroupmoderation == 1) {
					$Worksheet->Range("M37")->Value = 'x';
				}else if($Iteie->listgroupmoderation == 2) {
					$Worksheet->Range("M38")->Value = 'x';
				}else if($Iteie->listgroupmoderation == 3) {
					$Worksheet->Range("M39")->Value = 'x';
				}else if($Iteie->listgroupmoderation == 4) {
					$Worksheet->Range("M40")->Value = 'x';
				}else if($Iteie->listgroupmoderation == 5) {
					$Worksheet->Range("M41")->Value = 'x';
				}else if($Iteie->listgroupmoderation == 6) {
					$Worksheet->Range("M42")->Value = 'x';
				}else if($Iteie->listgroupmoderation == 7) {
					$Worksheet->Range("M43")->Value = 'x';
				}else {
					$listGM = '';
				}

			//end condition
			$Worksheet->Range("F31")->Value = $datefrom;
			$Worksheet->Range("R31")->Value = $dateto;
			$Worksheet->Range("V37")->Value = $Iteie->ironportquarantinedetail;
			$Worksheet->Range("F46")->Value = $Iteie->reason;
			$Worksheet->Range("B57")->Value = $deptheadname;
			$Worksheet->Range("B58")->Value = $deptheaddate;
			$Worksheet->Range("I57")->Value = $hrdname;
			$Worksheet->Range("I58")->Value = $hrddate;
			$Worksheet->Range("P57")->Value = $buheadname;
			$Worksheet->Range("P58")->Value = $buheaddate;
			$Worksheet->Range("W57")->Value = $itheadname;
			$Worksheet->Range("W58")->Value = $itheaddate;

			$xlTypePDF = 0;
			$xlQualityStandard = 0;
			// $path="D:/xampp/htdocs/oasys_gogs/doc/it/pdf/output.pdf";
			$fileName ='doc'.DS.'it'.DS.'pdf'.DS.'ITEIE'.$Iteie->employee->sapid.'_'.date("YmdHis").'.pdf';
			$fileName = str_replace("/","",$fileName);
			$path='D:/xampp/htdocs/oasys_gogs/doc'.DS.'it'.DS.'pdf'.DS.'ITEIE'.$Iteie->employee->sapid.'_'.date("YmdHis").'.pdf';
			if (file_exists($path)) {
			   unlink($path);
			}
			$Worksheet->ExportAsFixedFormat($xlTypePDF, $path, $xlQualityStandard);
			$Iteie->approveddoc=str_replace("\\","/",$fileName);
			$Iteie->save();
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
		$Workbook->Close(true);
		unset($Worksheet);
		unset($Workbook);
		$excel->Workbooks->Close();
		$excel->Quit();
		unset($excel);
		
	}
	function iteieHistory(){
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
							$Iteiehistory = Iteiehistory::find('all', array('conditions' => array("iteie_id=?",$id)));
							foreach ($Iteiehistory as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Iteiehistory, JSON_NUMERIC_CHECK);
						}
						break;
					default:
						break;
				}
			}
		}
	}
}