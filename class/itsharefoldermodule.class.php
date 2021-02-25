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
				case 'apiitshareffile':
					$this->itsharefAttachment();
					break;
				case 'uploaditshareffile':
					$this->uploadItsharefFile();
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
						// $Itsharef = Itsharef::find('first', array('joins'=>$join,'conditions' => array("tbl_itsharef.id=?",$id),'select'=>$select,'include' => array('employee'=>array('company','department','designation'))));
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
						$data['RequestStatus']=0;
						try{
							$Itsharef = Itsharef::create($data);
							$data=$Itsharef->to_array();
							$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";

								$Approver3 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=32")));
								if(count($Approver3)>0){
									$Itsharefapproval = new Itsharefapproval();
									$Itsharefapproval->itsharef_id = $Itsharef->id;
									$Itsharefapproval->approver_id = $Approver3->id;
									$Itsharefapproval->save();
								}

								if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,3)=="0220") || ($Employee->department->sapcode=="13000090") || ($Employee->department->sapcode=="13000121") || ($Employee->company->sapcode=="NKF") || ($Employee->company->sapcode=="RND"))
								{
									$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=34 and tbl_employee.location_id='1'")));
									if(count($Approver2)>0){
										$Itsharefapproval = new Itsharefapproval();
										$Itsharefapproval->itsharef_id = $Itsharef->id;
										$Itsharefapproval->approver_id = $Approver2->id;
										$Itsharefapproval->save();
									}
									
	
								}else{

									$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=34 and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
									if(count($Approver2)>0){
										$Itsharefapproval = new Itsharefapproval();
										$Itsharefapproval->itsharef_id = $Itsharef->id;
										$Itsharefapproval->approver_id = $Approver2->id;
										$Itsharefapproval->save();
									}
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
							$err->errortype = "CreateITSHAREF";
							$err->errordate = date("Y-m-d h:i:s");
							$err->errormessage = $e->getMessage();
							$err->user = $this->currentUser->username;
							$err->ip = $this->ip;
							$err->save();
							$data = array("status"=>"error","message"=>$e->getMessage());
						}
						$logger = new Datalogger("ITSHAREF","create",null,json_encode($data));
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
								$detail = Itsharefdetail::find("all",array('conditions' => array("itsharef_id=?",$id)));
								foreach ($detail as $delr){
									$delr->delete();
								}
								$att = Itsharefattachment::find("all",array('conditions' => array("itsharef_id=?",$id)));
								foreach ($att as $delr){
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
								$logger = new Datalogger("ITSHAREF","delete",json_encode($data),null);
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

								$title = 'Share Folder';
								
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

									$this->mailbody .='
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr>
											<th><p class=MsoNormal>Valid From</p></th>
											<th><p class=MsoNormal>Valid To</p></th>
											<th><p class=MsoNormal>Reason</p></th>
										</tr>
										<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itsharef->validfrom)).'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itsharef->validto)).'</p></td>
											<td><p class=MsoNormal> '.$Itsharef->reason.'</p></td>
										</tr>
									';

									$Itsharefdetail = Itsharefdetail::find('all',array('conditions'=>array("itsharef_id=?",$id),'include'=>array('itsharef'=>array('employee'=>array('company','department','designation','grade','location')))));	

									$this->mailbody .='</table>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><th><p class=MsoNormal>No</p></th>
											<th><p class=MsoNormal>Folder Name</p></th>
											<th><p class=MsoNormal>Request Type</p></th>
											<th><p class=MsoNormal>Grant Access To</p></th>
											<th><p class=MsoNormal>Permission</p></th>
										</tr>
										';
									$no=1;
									foreach ($Itsharefdetail as $data){
										if($data->requesttype == 1) {
											$rt = 'Create Share Folder';
										} else if($data->requesttype == 2) {
											$rt = 'Grant Access to Existing Folder';
										} else if($data->requesttype == 3) {
											$rt = 'Delete Shared Folder';
										} else if($data->requesttype == 4) {
											$rt = 'Revoke Access from Existing Folder';
										} else if($data->requesttype == 5) {
											$rt = 'Exclude from Archiving Policy';
										}

										if($data->change == 1) {
											$change = 'Change';
										} else {
											$change = 'ReadOnly';
										}
										$this->mailbody .='<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.$no.'</p></td>
											<td><p class=MsoNormal> '.$data->foldername.'</p></td>
											<td><p class=MsoNormal> '.$rt.'</p></td>
											<td><p class=MsoNormal> '.$data->grantaccessto.'</p></td>
											<td><p class=MsoNormal> '.$change.'</p></td>
											</tr>';
										$no++;
									}
									
									$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
									
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
								$logger = new Datalogger("ITSHAREF","update",json_encode($olddata),json_encode($data));
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
								// print_r($result);
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
								$Itsharefapproval = Itsharefapproval::find('all', array('joins'=>$join,'conditions' => array("itsharef_id=? and ApprovalStatus in (0,1,4) and not tbl_approver.employee_id=?",$query['itsharef_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
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
								$Itsharefapproval = Itsharefapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus in (0,4) and itsharef_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
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
						print_r($data);

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
						unset($data['foldername']);

						unset($data['validto']);
						unset($data['validfrom']);

						unset($data['reason']);	
						
						unset($data['requesttype']);
						
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
							$nTrapproval = Itsharefapproval::find('first',array('joins'=>$joinx,'conditions' => array("itsharef_id=? and ApprovalStatus=0 or ApprovalStatus=4",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
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

							$title = 'Share Folder';
							
							switch ($data['approvalstatus']){
								
								case '1':
									$Itsharef->requeststatus = 2;
									$emto=$email;$emname=$Itsharef->employee->fullname;
									$this->mail->Subject = "Online Approval System -> Need Rework";
									$red = 'Your '.$title.' require some rework :
											<br>Remarks from Approver : <font color="red">'.$data['remarks']."</font>";
									$Itsharefhistory->actiontype = 3;
									echo 'case 1';

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
									echo 'case 2';

									break;
								case '3':
									$Itsharef->requeststatus = 4;
									$emto=$email;$emname=$Itsharef->employee->fullname;
									$Itsharefhistory->actiontype = 5;
									$this->mail->Subject = "Online Approval System -> Request Rejected";
									$red = 'Your '.$title.' Request has been rejected
											<br>Remarks from Approver : <font color="red">'.$data['remarks']."</font>";
									echo 'case 3';

									break;
								case '4':
									$Itsharef->requeststatus = 1;
									// $nTrapproval->approvalstatus = 0;
									$emto=$adb->email;$emname=$adb->fullname;
									$this->mail->Subject = 'Online Approval System -> new '.$title.' Request';
									$red = 'new '.$title.' Request awaiting for your approval:';
									$Itsharefhistory->actiontype = 6;	
									
									echo 'case 4';
									break;
								default:
									break;
							}
							$Itsharef->save();
							$Itsharefhistory->save();
							// $nTrapproval->save();
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


							$this->mailbody .='
								<table border=1 cellspacing=0 cellpadding=3 width=683>
								<tr>
									<th><p class=MsoNormal>Valid From</p></th>
									<th><p class=MsoNormal>Valid To</p></th>
									<th><p class=MsoNormal>Reason</p></th>
								</tr>
								<tr style="height:22.5pt">
									<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itsharef->validfrom)).'</p></td>
									<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itsharef->validto)).'</p></td>
									<td><p class=MsoNormal> '.$Itsharef->reason.'</p></td>
								</tr>
								';

								$Itsharefdetail = Itsharefdetail::find('all',array('conditions'=>array("itsharef_id=?",$doid),'include'=>array('itsharef'=>array('employee'=>array('company','department','designation','grade','location')))));	

								$this->mailbody .='</table>
									<table border=1 cellspacing=0 cellpadding=3 width=683>
									<tr><th><p class=MsoNormal>No</p></th>
										<th><p class=MsoNormal>Folder Name</p></th>
										<th><p class=MsoNormal>Request Type</p></th>
										<th><p class=MsoNormal>Grant Access To</p></th>
										<th><p class=MsoNormal>Permission</p></th>
									</tr>
									';
								$no=1;
								foreach ($Itsharefdetail as $data){
									if($data->requesttype == 1) {
										$rt = 'Create Share Folder';
									} else if($data->requesttype == 2) {
										$rt = 'Grant Access to Existing Folder';
									} else if($data->requesttype == 3) {
										$rt = 'Delete Shared Folder';
									} else if($data->requesttype == 4) {
										$rt = 'Revoke Access from Existing Folder';
									} else if($data->requesttype == 5) {
										$rt = 'Exclude from Archiving Policy';
									}


									if($data->change == 1) {
										$change = 'Change';
									} else {
										$change = 'ReadOnly';
									}
									$this->mailbody .='<tr style="height:22.5pt">
										<td><p class=MsoNormal> '.$no.'</p></td>
										<td><p class=MsoNormal> '.$data->foldername.'</p></td>
										<td><p class=MsoNormal> '.$rt.'</p></td>
										<td><p class=MsoNormal> '.$data->grantaccessto.'</p></td>
										<td><p class=MsoNormal> '.$change.'</p></td>
										</tr>';
									$no++;
								}

							$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
							
								$this->mail->msgHTML($this->mailbody);
							if ($complete){
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
								$err->errortype = "ITSHAREF Mail";
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
							$join = "LEFT JOIN vwitsharefreport ON tbl_itsharefdetail.itsharef_id = vwitsharefreport.id";
							$select = "tbl_itsharefdetail.*,vwitsharefreport.apprstatuscode";
							$Itsharefdetail = Itsharefdetail::find('all', array('joins'=>$join,'select'=>$select,'conditions' => array("itsharef_id=?",$id)));
							$getdetail = Itsharefdetail::find('first', array('joins'=>$join,'select'=>$select,'conditions' => array("itsharef_id=?",$id)));
							// $Itsharefdetail = Itsharefdetail::find('all', array('conditions' => array("itsharef_id=?",$id)));
							foreach ($Itsharefdetail as &$result) {
								$result	= $result->to_array();
							}
							// $Itsharefdetail['rolestatus'] = $getdetail->apprstatuscode;

							// if ($Itsharefdetail){
							// 	// $Itsharefdetail['rolestatus']=$Itsharefdetail->apprstatuscode;
								// print_r($Itsharefdetail);
							// }

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
						if($data['change']=='true') {
							$data['change'] = 1;
						}else if($data['change']=='false') {
							$data['change'] = 0;
						}
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
						// foreach($data as $key=>$val){
						// 	$Itsharefdetail->$key=$val;
						// }
						foreach($data as $key=>$val){
							// $val=($val=='true')?1:0;
							if($val=='true') {
								$val = 1;
							}else if($val=='false') {
								$val = 0;
							}
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
	function  itsharefAttachment(){
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
							$Itsharefattachment = Itsharefattachment::find('all', array('conditions' => array("itsharef_id=?",$id)));
							foreach ($Itsharefattachment as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Itsharefattachment, JSON_NUMERIC_CHECK);
						}else{
							$Itsharefattachment = new Itsharefattachment();
							echo json_encode($Itsharefattachment);
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							$Itsharefattachment = Itsharefattachment::find('all', array('conditions' => array("itsharef_id=?",$query['itsharef_id'])));
							$data=array("jml"=>count($Itsharefattachment));
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
						
						$Itsharefattachment = Itsharefattachment::create($data);
						$logger = new Datalogger("Itsharefattachment","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Itsharefattachment = Itsharefattachment::find($id);
						$data=$Itsharefattachment->to_array();
						$Itsharefattachment->delete();
						$logger = new Datalogger("Itsharefattachment","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Itsharefattachment);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
						$data['employee_id']=$Employee->id;
						$Itsharefattachment = Itsharefattachment::find($id);
						$olddata = $Itsharefattachment->to_array();
						foreach($data as $key=>$val){					
							$val=($val=='No')?false:(($val=='Yes')?true:$val);
							$Itsharefattachment->$key=$val;
						}
						$Itsharefattachment->save();
						$logger = new Datalogger("Itsharefattachment","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						echo json_encode($Itsharefattachment);
						
						break;
					default:
						$Itsharefattachment = Itsharefattachment::all();
						foreach ($Itsharefattachment as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Itsharefattachment, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}
	public function uploadItsharefFile(){
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
		$path_to_file = "upload/itsharef/".$id."_".time()."_".$_FILES['myFile']['name'];
		$path_to_file = str_replace("%","_",$path_to_file);
		$path_to_file = str_replace(" ","_",$path_to_file);
		echo $path_to_file;
        move_uploaded_file($_FILES['myFile']['tmp_name'], $path_to_file);
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