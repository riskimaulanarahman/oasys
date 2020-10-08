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
						// $join = "LEFT JOIN vwiteiereport ON tbl_iteie.id = vwiteiereport.id";
						// $select = "tbl_iteie.*,vwiteiereport.apprstatuscode";
						$Itimail = Itimail::find($id, array('include' => array('employee'=>array('company','department','designation'))));
						// $Itimail = Itimail::find($id, array('joins'=>$join,'select'=>$select,'include' => array('employee'=>array('company','department','designation'))));
						if ($Itimail){
							$fullname = $Itimail->employee->fullname;
							$department = $Itimail->employee->department->departmentname;
							$data=$Itimail->to_array();
							$data['fullname']=$fullname;
							$data['department']=$department;
							echo json_encode($data, JSON_NUMERIC_CHECK);
						}else{
							$Itimail = new Iteie();
							echo json_encode($Itimail);
						}
						break;
					case 'find':
						$query=$this->post['query'];					
						if(isset($query['status'])){
							switch ($query['status']){
								case 'chemp':
									break;
								case "reschedule":
									$id = $query['itimail_id'];
									$Itimail = Itimail::find($id,array('include'=>array('employee'=>array('company','department','designation','grade'))));
									$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Itimail->employee->loginname)));
									$email=$usr->email;
									
									$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$usr->fullname.',</span></p>
										<p class=MsoNormal><span style="color:#1F497D">Your Access Directory has been rescheduled by HR to match with your actual travel schedule:</span></p>
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
									$this->mail->Subject = "Online Approval System -> Access Directory Email Request Reschedule";
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

								$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=30")));
								if(count($Approver)>0){
									$Itimailapproval = new Iteieapproval();
									$Itimailapproval->iteie_id = $Itimail->id;
									$Itimailapproval->approver_id = $Approver->id;
									$Itimailapproval->save();
								}
								$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=31")));
								if(count($Approver2)>0){
									$Itimailapproval = new Iteieapproval();
									$Itimailapproval->iteie_id = $Itimail->id;
									$Itimailapproval->approver_id = $Approver2->id;
									$Itimailapproval->save();
								}
								$Approver3 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='IT' and tbl_approver.isactive='1' and approvaltype_id=32")));
								if(count($Approver3)>0){
									$Itimailapproval = new Iteieapproval();
									$Itimailapproval->iteie_id = $Itimail->id;
									$Itimailapproval->approver_id = $Approver3->id;
									$Itimailapproval->save();
								}

							$Iteihistory = new Iteiehistory();
							$Iteihistory->date = date("Y-m-d h:i:s");
							$Iteihistory->fullname = $Employee->fullname;
							$Iteihistory->approvaltype = "Originator";
							$Iteihistory->iteie_id = $Itimail->id;
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
							$Itimail = Itimail::find($id);
							if ($Itimail->requeststatus==0){
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
								$data = $Itimail->to_array();
								$Itimail->delete();
								$logger = new Datalogger("ITEIE","delete",json_encode($data),null);
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
									$joins   = "LEFT JOIN tbl_approver ON (tbl_iteieapproval.approver_id = tbl_approver.id) ";					
									$dx = Iteieapproval::find('all',array('joins'=>$joins,'conditions' => array("iteie_id=? and tbl_approver.approvaltype_id=29 and not(tbl_approver.employee_id=?)",$id,$depthead)));	
									foreach ($dx as $result) {
										//delete same type dept head approver
										$result->delete();
										$logger = new Datalogger("Iteieapproval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
									}
									$joins   = "LEFT JOIN tbl_approver ON (tbl_iteieapproval.approver_id = tbl_approver.id) ";					
									$Itimailapproval = Iteieapproval::find('all',array('joins'=>$joins,'conditions' => array("iteie_id=? and tbl_approver.employee_id=?",$id,$depthead)));	
									foreach ($Itimailapproval as &$result) {
										$result		= $result->to_array();
										$result['no']=1;
									}			
									if(count($Itimailapproval)==0){ 
										$Approver = Approver::find('first',array('conditions'=>array("module='IT' and employee_id=? and approvaltype_id=29",$depthead)));
										if(count($Approver)>0){
											$Itimailapproval = new Iteieapproval();
											$Itimailapproval->iteie_id = $Itimail->id;
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
											$Itimailapproval = new Iteieapproval();
											$Itimailapproval->iteie_id = $Itimail->id;
											$Itimailapproval->approver_id = $approver->id;
											$Itimailapproval->save();
										}
									}
									
								}
								
								if($data['requeststatus']==1){
									$Itimailapproval = Iteieapproval::find('all', array('conditions' => array("iteie_id=?",$id)));					
									foreach($Itimailapproval as $data){
										$data->approvalstatus=0;
										$data->save();
									}
									$joinx   = "LEFT JOIN tbl_approver ON (tbl_iteieapproval.approver_id = tbl_approver.id) ";					
									$Itimailapproval = Iteieapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and iteie_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
									$username = $Itimailapproval->approver->employee->loginname;
									$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
									$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Itimail->employee->loginname)));
									$email=$usr->email;

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

									$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$adb->fullname.',</span></p>
										<p class=MsoNormal><span style="color:#1F497D">new Access Directory Request is awaiting for your approval:</span></p>
										<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itimail->employee->fullname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itimail->employee->sapid.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itimail->employee->designation->designationname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itimail->employee->company->companyname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Itimail->employee->location->location.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
										</table>
										<p class=MsoNormal><b>Access Directory</b></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										
										<tr><th><p class=MsoNormal>Date</small></p></th>
											<th><p class=MsoNormal>Name</p></th>
											<th><p class=MsoNormal>Employee ID</p></th>
											<th><p class=MsoNormal>Designation</p></th>
											<th><p class=MsoNormal>BG/BU</p></th>
											<th><p class=MsoNormal>Office/Location</p></th>
											<th><p class=MsoNormal>Floor</p></th>
											<th><p class=MsoNormal>Phone(Ext)</p></th>
											<th><p class=MsoNormal>Department</p></th>
										</tr>
										<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->createddate)).'</p></td>
											<td><p class=MsoNormal> '.$Itimail->name.'</p></td>
											<td><p class=MsoNormal> '.$Itimail->employeeid.'</p></td>
											<td><p class=MsoNormal> '.$Itimail->designation.'</p></td>
											<td><p class=MsoNormal> '.$Itimail->bgbu.'</p></td>
											<td><p class=MsoNormal> '.$Itimail->officelocation.'</p></td>
											<td><p class=MsoNormal> '.$Itimail->floor.'</p></td>
											<td><p class=MsoNormal> '.$Itimail->phoneext.'</p></td>
											<td><p class=MsoNormal> '.$Itimail->department.'</p></td>
										</tr>
										</table>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr>
											<th><p class=MsoNormal>Access Type</p></th>
											<th><p class=MsoNormal>Account Type</p></th>
											<th><p class=MsoNormal>Valid From</p></th>
											<th><p class=MsoNormal>Valid To</p></th>
											<th><p class=MsoNormal>List Group</p></th>
										</tr>
										<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.$accessT.'</p></td>
											<td><p class=MsoNormal> '.$accountT.'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validfrom)).'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validto)).'</p></td>
											<td><p class=MsoNormal> '.$Itimail->listgroup.'</p></td>
										</tr>
										';

									$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
									$this->mail->addAddress($adb->email, $adb->fullname);
									$this->mail->Subject = "Online Approval System -> new Access Directory Request";
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
									$Itimailhistory = new Iteiehistory();
									$Itimailhistory->date = date("Y-m-d h:i:s");
									$Itimailhistory->fullname = $Employee->fullname;
									$Itimailhistory->iteie_id = $id;
									$Itimailhistory->approvaltype = "Originator";
									$Itimailhistory->actiontype = 2;
									$Itimailhistory->save();
								}else{
									$Itimailhistory = new Iteiehistory();
									$Itimailhistory->date = date("Y-m-d h:i:s");
									$Itimailhistory->fullname = $Employee->fullname;
									$Itimailhistory->iteie_id = $id;
									$Itimailhistory->approvaltype = "Originator";
									$Itimailhistory->actiontype = 1;
									$Itimailhistory->save();
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
							$join   = "LEFT JOIN tbl_approver ON (tbl_iteieapproval.approver_id = tbl_approver.id) ";
							$Itimailapproval = Iteieapproval::find('all', array('joins'=>$join,'conditions' => array("iteie_id=?",$id),'include' => array('approver'=>array('approvaltype')),"order"=>"tbl_approver.sequence"));
							foreach ($Itimailapproval as &$result) {
								$approvaltype = $result->approver->approvaltype_id;
								$result		= $result->to_array();
								$result['approvaltype']=$approvaltype;
							}
							echo json_encode($Itimailapproval, JSON_NUMERIC_CHECK);
						}else{
							$Itimailapproval = new Iteieapproval();
							echo json_encode($Itimailapproval);
						}
						break;
					case 'find':
						$query=$this->post['query'];		
						if(isset($query['status'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join   = "LEFT JOIN tbl_approver ON (tbl_iteieapproval.approver_id = tbl_approver.id) ";
							$dx = Iteieapproval::find('first', array('joins'=>$join,'conditions' => array("iteie_id=? and tbl_approver.employee_id = ?",$query['itimail_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
							$Itimail = Itimail::find($query['itimail_id']);
							// print_r($dx);
							if($dx->approver->isfinal==1){
								$data=array("jml"=>1);
							}else{
								$join   = "LEFT JOIN tbl_approver ON (tbl_iteieapproval.approver_id = tbl_approver.id) ";
								$Itimailapproval = Iteieapproval::find('all', array('joins'=>$join,'conditions' => array("iteie_id=? and ApprovalStatus<=1 and not tbl_approver.employee_id=?",$query['itimail_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
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
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_iteieapproval.approver_id = tbl_approver.id) ";					
								$Itimailapproval = Iteieapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and iteie_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
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
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_iteieapproval.approver_id = tbl_approver.id) ";					
								$Itimailapproval = Iteieapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and iteie_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
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
							$join = "LEFT JOIN vwiteiereport v on tbl_iteie.id=v.id";
							$sel = 'tbl_iteie.*, v.laststatus,v.personholding ';
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
						$Itimailapproval = Iteieapproval::create($data);
						$logger = new Datalogger("Iteieapproval","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':
						$id = $this->post['id'];
						$Itimailapproval = Iteieapproval::find($id);
						$data=$Itimailapproval->to_array();
						$Itimailapproval->delete();
						$logger = new Datalogger("Iteieapproval","delete",json_encode($data),null);
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
						$join   = "LEFT JOIN tbl_approver ON (tbl_iteieapproval.approver_id = tbl_approver.id) ";
						if (isset($data['mode'])){
							$Itimailapproval = Iteieapproval::find('first', array('joins'=>$join,'conditions' => array("iteie_id=? and tbl_approver.employee_id=?",$doid,$Employee->id),'include' => array('approver'=>array('employee','approvaltype'))));
							unset($data['mode']);
						}else{
							$Itimailapproval = Iteieapproval::find($this->post['id'],array('include' => array('approver'=>array('employee','approvaltype'))));
						}
						foreach($data as $key=>$val) {
							if(($key !== 'approvalstatus') && ($key !== 'approvaldate') && ($key !== 'remarks')) {
								// if(($key == 'isrepair') || ($key == 'isscrap')) {
									$value=(($val===0) || ($val==='0') || ($val==='false'))?false:((($val===1) || ($val==='1') || ($val==='true'))?true:$val);
								// }
								$Itimail->$key=$value;
							}
						}
						$Itimail->save();

						// unset($data['materialdispatchno']);
						// unset($data['isrepair']);
						// unset($data['isscrap']);
						// unset($data['estimatecost']);
						// unset($data['pono']);
						// unset($data['materialreturneddate']);
						// unset($data['supplierdodnno']);
						// unset($data['buyer']);
						
						
						$olddata = $Itimailapproval->to_array();
						foreach($data as $key=>$val){
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$Itimailapproval->$key=$val;
						}
						$Itimailapproval->save();
						$logger = new Datalogger("Iteieapproval","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						if (isset($mode) && ($mode=='approve')){
							$Itimail = Itimail::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));
							$joinx   = "LEFT JOIN tbl_approver ON (tbl_iteieapproval.approver_id = tbl_approver.id) ";					
							$nTrapproval = Iteieapproval::find('first',array('joins'=>$joinx,'conditions' => array("iteie_id=? and ApprovalStatus=0",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
							$username = $nTrapproval->approver->employee->loginname;
							$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
							// $Itimailschedule=Trschedule::find('all',array('conditions'=>array("iteie_id=?",$doid),'include'=>array('itimail'=>array('employee'=>array('company','department','designation','grade','location')))));
							// $Itimailticket=Trticket::find('all',array('conditions'=>array("iteie_id=?",$doid),'include'=>array('itimail'=>array('employee'=>array('company','department','designation','grade','location')))));
							$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Itimail->employee->loginname)));
							$email=$usr->email;
							$superiorId=$Itimail->depthead;
							$Superior = Employee::find($superiorId);
							$supAdb = Addressbook::find('first',array('conditions'=>array("username=?",$Superior->loginname)));
							$complete = false;
							$Itimailhistory = new Iteiehistory();
							$Itimailhistory->date = date("Y-m-d h:i:s");
							$Itimailhistory->fullname = $Employee->fullname;
							$Itimailhistory->approvaltype = $Itimailapproval->approver->approvaltype->approvaltype;
							$Itimailhistory->remarks = $data['remarks'];
							$Itimailhistory->iteie_id = $doid;
							
							switch ($data['approvalstatus']){
								case '1':
									$Itimail->requeststatus = 2;
									$emto=$email;$emname=$Itimail->employee->fullname;
									$this->mail->Subject = "Online Approval System -> Need Rework";
									$red = 'Your Access Directory require some rework :
											<br>Remarks from Approver : <font color="red">'.$data['remarks']."</font>";
									$Itimailhistory->actiontype = 3;
									break;
								case '2':
									if ($Itimailapproval->approver->isfinal == 1){
										$Itimail->requeststatus = 3;
										$emto=$email;$emname=$Itimail->employee->fullname;
										$this->mail->Subject = "Online Approval System -> Approval Completed";
										$red = '<p>Your Access Directory. request has been approved</p>';
													// '<p><b><span lang=EN-US style=\'color:#002060\'>Note : Please <u>forward</u> this electronic approval to your respective Human Resource Department.</span></b></p>';
										//delete unnecessary approver
										$Itimailapproval = Iteieapproval::find('all', array('joins'=>$join,'conditions' => array("iteie_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
										foreach ($Itimailapproval as $data) {
											if($data->approvalstatus==0){
												$logger = new Datalogger("Iteieapproval","delete",json_encode($data->to_array()),"automatic remove unnecessary approver by system");
												$logger->SaveData();
												$data->delete();
											}
										}
										$complete =true;
									}
									else{
										$Itimail->requeststatus = 1;
										$emto=$adb->email;$emname=$adb->fullname;
										$this->mail->Subject = "Online Approval System -> new Access Directory Request";
										$red = 'new Access Directory Request awaiting for your approval:';
									}
									$Itimailhistory->actiontype = 4;							
									break;
								case '3':
									$Itimail->requeststatus = 4;
									$emto=$email;$emname=$Itimail->employee->fullname;
									$Itimailhistory->actiontype = 5;
									$this->mail->Subject = "Online Approval System -> Request Rejected";
									$red = 'Your Access Directory Request has been rejected
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
								</table>
								<p class=MsoNormal><b>Access Directory</b></p>
								<table border=1 cellspacing=0 cellpadding=3 width=683>
								
								<tr><th><p class=MsoNormal>Date</small></p></th>
									<th><p class=MsoNormal>Name</p></th>
									<th><p class=MsoNormal>Employee ID</p></th>
									<th><p class=MsoNormal>Designation</p></th>
									<th><p class=MsoNormal>BG/BU</p></th>
									<th><p class=MsoNormal>Office/Location</p></th>
									<th><p class=MsoNormal>Floor</p></th>
									<th><p class=MsoNormal>Phone(Ext)</p></th>
									<th><p class=MsoNormal>Department</p></th>
								</tr>
								<tr style="height:22.5pt">
									<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->createddate)).'</p></td>
									<td><p class=MsoNormal> '.$Itimail->name.'</p></td>
									<td><p class=MsoNormal> '.$Itimail->employeeid.'</p></td>
									<td><p class=MsoNormal> '.$Itimail->designation.'</p></td>
									<td><p class=MsoNormal> '.$Itimail->bgbu.'</p></td>
									<td><p class=MsoNormal> '.$Itimail->officelocation.'</p></td>
									<td><p class=MsoNormal> '.$Itimail->floor.'</p></td>
									<td><p class=MsoNormal> '.$Itimail->phoneext.'</p></td>
									<td><p class=MsoNormal> '.$Itimail->department.'</p></td>
								</tr>
								</table>
								<table border=1 cellspacing=0 cellpadding=3 width=683>
								<tr>
									<th><p class=MsoNormal>Access Type</p></th>
									<th><p class=MsoNormal>Account Type</p></th>
									<th><p class=MsoNormal>Valid From</p></th>
									<th><p class=MsoNormal>Valid To</p></th>
									<th><p class=MsoNormal>List Group</p></th>
								</tr>
								<tr style="height:22.5pt">
									<td><p class=MsoNormal> '.$accessT.'</p></td>
									<td><p class=MsoNormal> '.$accountT.'</p></td>
									<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validfrom)).'</p></td>
									<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Itimail->validto)).'</p></td>
									<td><p class=MsoNormal> '.$Itimail->listgroup.'</p></td>
								</tr>
								';
							$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
							$this->mail->msgHTML($this->mailbody);
							if ($complete){
								$fileName = $this->generatePDFi($doid);
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
						$Itimailapproval = Iteieapproval::all();
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
		$superiorId=$Itimail->depthead;
		$Superior = Employee::find($superiorId);
		$supAdb = Addressbook::find('first',array('conditions'=>array("username=?",$Superior->loginname)));
		$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Itimail->employee->loginname)));
		$email=$usr->email;

		$datefrom = date("d/m/Y",strtotime($Itimail->validfrom));
		$dateto = date("d/m/Y",strtotime($Itimail->validto));

		$joinx   = "LEFT JOIN tbl_approver ON (tbl_iteieapproval.approver_id = tbl_approver.id) ";					
		$Itimailapproval = Iteieapproval::find('all',array('joins'=>$joinx,'conditions' => array("iteie_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee','approvaltype'))));							
		
		//condition
			foreach ($Itimailapproval as $data){
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
			$file="D:/xampp/htdocs/oasys_gogs/doc/it/ad_template.xlsx";
			// $file="D:/xampp/htdocs/oasys_gogs/doc/it/ad_template.xlsx";
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
			$Worksheet->Range("F7")->Value = $Itimail->name;
			$Worksheet->Range("F9")->Value = $Itimail->employeeid;
			$Worksheet->Range("F11")->Value = $Itimail->designation;
			$Worksheet->Range("F13")->Value = $Itimail->bgbu;
			$Worksheet->Range("F15")->Value = $Itimail->officelocation;
			$Worksheet->Range("Y15")->Value = $Itimail->floor;
			$Worksheet->Range("F17")->Value = $Itimail->phoneext;
			$Worksheet->Range("F19")->Value = $Itimail->department;
			//condition

				if($Itimail->accesstype == 1) {
					$Worksheet->Range("F26")->Value = 'x';
				}else if($Itimail->accesstype == 2) {
					$Worksheet->Range("P26")->Value = 'x';
				}else {
					$accessT = '';
				}
	
				if($Itimail->accounttype == 1) {
					$Worksheet->Range("F28")->Value = 'x';
				}else if($Itimail->accounttype == 2) {
					$Worksheet->Range("P28")->Value = 'x';
				}else {
					$accountT = '';
				}

				if($Itimail->isvip == 1) {
					$Worksheet->Range("F24")->Value = 'x';
				}else {
					$Worksheet->Range("P24")->Value = 'x';
				}
	

			//end condition
			$Worksheet->Range("F31")->Value = $datefrom;
			$Worksheet->Range("R31")->Value = $dateto;
			$Worksheet->Range("K37")->Value = $Itimail->listgroup;
			$Worksheet->Range("F39")->Value = $Itimail->reason;
			$Worksheet->Range("B50")->Value = $deptheadname;
			$Worksheet->Range("B51")->Value = $deptheaddate;
			$Worksheet->Range("I50")->Value = $hrdname;
			$Worksheet->Range("I51")->Value = $hrddate;
			$Worksheet->Range("P50")->Value = $buheadname;
			$Worksheet->Range("P51")->Value = $buheaddate;
			$Worksheet->Range("W50")->Value = $itheadname;
			$Worksheet->Range("W51")->Value = $itheaddate;

			$xlTypePDF = 0;
			$xlQualityStandard = 0;
			// $path="D:/xampp/htdocs/oasys_gogs/doc/it/pdf/output.pdf";
			$fileName ='doc'.DS.'it'.DS.'pdf'.DS.'ITIMAIL'.$Itimail->employee->sapid.'_'.date("YmdHis").'.pdf';
			$fileName = str_replace("/","",$fileName);
			$path='D:/xampp/htdocs/oasys_gogs/doc'.DS.'it'.DS.'pdf'.DS.'ITIMAIL'.$Itimail->employee->sapid.'_'.date("YmdHis").'.pdf';
			if (file_exists($path)) {
			   unlink($path);
			}
			$Worksheet->ExportAsFixedFormat($xlTypePDF, $path, $xlQualityStandard);
			$Itimail->approveddoc=str_replace("\\","/",$fileName);
			$Itimail->save();
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
							$Itimailhistory = Iteiehistory::find('all', array('conditions' => array("iteie_id=?",$id)));
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