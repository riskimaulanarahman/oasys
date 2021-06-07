<?php
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

Class TrModule extends Application{
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
		$this->mailbody = '<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:m="http://schemas.microsoft.com/office/2004/12/omml" xmlns="http://www.w3.org/TR/REC-html40"><head><meta http-equiv=Content-Type content="text/html; charset=us-ascii"><meta name=Generator content="Microsoft Word 15 (filtered medium)"><style><!--
						/* Font Definitions */
						@font-face {font-family:Wingdings; panose-1:5 0 0 0 0 0 0 0 0 0;} @font-face {font-family:"Cambria Math"; panose-1:2 4 5 3 5 4 6 3 2 4;} @font-face {font-family:Calibri; panose-1:2 15 5 2 2 2 4 3 2 4;} @font-face {font-family:"Century Gothic"; panose-1:2 11 5 2 2 2 2 2 2 4;}
						/* Style Definitions */
						p.MsoNormal, li.MsoNormal, div.MsoNormal {margin:0in; margin-bottom:.0001pt; font-size:11.0pt; font-family:"Calibri","sans-serif";} a:link, span.MsoHyperlink {mso-style-priority:99; color:#0563C1; text-decoration:underline;} a:visited, span.MsoHyperlinkFollowed {mso-style-priority:99; color:#954F72; text-decoration:underline;} span.EmailStyle17 {mso-style-type:personal-reply;	font-family:"Calibri","sans-serif";	color:#1F497D;} .MsoChpDefault {mso-style-type:export-only;} @page WordSection1 {size:8.5in 11.0in;margin:1.0in 1.0in 1.0in 1.0in;} div.WordSection1 {page:WordSection1;} --></style><!--[if gte mso 9]><xml><o:shapedefaults v:ext="edit" spidmax="1026" /></xml><![endif]--><!--[if gte mso 9]><xml><o:shapelayout v:ext="edit"><o:idmap v:ext="edit" data="1" /></o:shapelayout></xml><![endif]--></head>';
		if (isset($this->get)){
			switch ($this->get['action']){
				case 'apitrbyemp':
					$this->trByEmp();
					break;
				case 'apitr':
					$this->Tr();
					break;
				case 'apitrapp':
					$this->trApproval();
					break;
				case 'apitrpdf':
					$id = $this->get['id'];
					$this->generatePDF($id);
					break;
				case 'apitrschedule':
					$this->trSchedule();
					break;
				case 'apitrticket':
					$this->trTicket();
					break;	
				case 'apitrhist':
					$this->trHistory();
					break;
				
				default:
					break;
			}
		}
	}
	
	function trByEmp(){
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
							$Tr = Tr::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Tr as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Tr, JSON_NUMERIC_CHECK);
						}else{
							echo json_encode(array());
						}
						break;
					case 'find':
						$query=$this->post['query'];					
						if(isset($query['status'])){
							switch ($query['status']){
								case 'waiting':
									$Tr = Tr::find('all', array('conditions' => array("employee_id=? and RequestStatus<3 and id<>?",$query['username'],$query['id']),'include' => array('employee')));
									foreach ($Tr as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Tr));
									break;
								default:
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
									$Tr = Tr::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Tr as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Tr));
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
							$Tr = Tr::find('all', array('conditions' => array("employee_id=? or createdby=?",$Employee->id,$Employee->id),'include' => array('employee')));
							foreach ($Tr as &$result) {
								$fullname=$result->employee->fullname;
								$result = $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Tr, JSON_NUMERIC_CHECK);
						}else{
							echo json_encode(array());
						}
						break;
				}
			}
		}
	}
	function Tr(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'byid':
						$id = $this->post['id'];
						$Tr = Tr::find($id, array('include' => array('employee'=>array('company','department','designation'))));
						if ($Tr){
							$fullname = $Tr->employee->fullname;
							$department = $Tr->employee->department->departmentname;
							$data=$Tr->to_array();
							$data['fullname']=$fullname;
							$data['department']=$department;
							echo json_encode($data, JSON_NUMERIC_CHECK);
						}else{
							$Tr = new Tr();
							echo json_encode($Tr);
						}
						break;
					case 'find':
						$query=$this->post['query'];					
						if(isset($query['status'])){
							switch ($query['status']){
								case 'chemp':
									break;
								case "reschedule":
									$id = $query['tr_id'];
									$Tr = Tr::find($id,array('include'=>array('employee'=>array('company','department','designation','grade'))));
									$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Tr->employee->loginname)));
									$email=$usr->email;
									$Trschedule=Trschedule::find('all',array('conditions'=>array("tr_id=?",$id),'include'=>array('tr'=>array('employee'=>array('company','department','designation','grade')))));
									$Trticket=Trticket::find('all',array('conditions'=>array("tr_id=?",$id),'include'=>array('tr'=>array('employee'=>array('company','department','designation','grade')))));
									$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$usr->fullname.',</span></p>
										<p class=MsoNormal><span style="color:#1F497D">Your Travel Request has been updated or rescheduled by HR to match with your actual travel schedule:</span></p>
										<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->fullname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->sapid.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->designation->designationname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->company->companyname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->location->location.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
										</table>
										<p class=MsoNormal><b>Travel Schedule ( Jadwal Perjalanan)</b></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><th  rowspan="2"><p class=MsoNormal>No</p></th>
											<th colspan="2"><p class=MsoNormal>Departing <br>( Keberangkatan )</p></th>
											<th><p class=MsoNormal>From ( Dari )</p></th>
											<th colspan="2"><p class=MsoNormal>Arriving ( Ketibaan )</p></th>
											<th><p class=MsoNormal>To ( Ke )</p></th>
											<th><p class=MsoNormal>Region</p></th>
											<th rowspan="2"><p class=MsoNormal>Reason (Alasan)<br><small>(e.g. Meeting,<br>Seminar etc. )</small></p></th>
										</tr>
										<tr><th><p class=MsoNormal>Date (tgl) <br> <small>(dd/mm/yyyy)</small></p></th>
											<th><p class=MsoNormal>Time (Waktu)</p></th>
											<th><p class=MsoNormal>City/Country<br>(Kota/Negara)</p></th>
											<th><p class=MsoNormal>Date (tgl) <br> <small>(dd/mm/yyyy)</small></p></th>
											<th><p class=MsoNormal>Time (Waktu)</p></th>
											<th><p class=MsoNormal>City/Country<br>(Kota/Negara)</p></th>
											<th><p class=MsoNormal>R1/R2</p></th>
										</tr>';
									$no=1;					
									foreach ($Trschedule as $data){
										$this->mailbody .='<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.$no.'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($data->departdate)).'</p></td>
											<td><p class=MsoNormal> '.$data->departtime.'</p></td>
											<td><p class=MsoNormal> '.$data->departfrom.'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($data->arrivingdate)).'</p></td>
											<td><p class=MsoNormal> '.$data->arrivingtime.'</p></td>
											<td><p class=MsoNormal> '.$data->arrivingto.'</p></td>
											<td><p class=MsoNormal> '.$data->region.'</p></td>
											<td><p class=MsoNormal> '.$data->reason.'</p></td>
										</tr>';
										$no++;
									}
									$this->mailbody .='</table>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><th><p class=MsoNormal>No</p></th>
											<th><p class=MsoNormal>Ticket For (Untuk)</p></th>
											<th><p class=MsoNormal>Name <br>( Nama )</p></th>
											<th><p class=MsoNormal>Date of Birth <br>( Tgl. Lahir) <br> <small>(dd/mm/yyyy)</small></p></th>
											<th><p class=MsoNormal>Phone Number</p></th>
											<th><p class=MsoNormal>Gender</p></th>
											<th><p class=MsoNormal>Remarks /Confirmation from HR <br> ( Konfirmasi dari HR )</p></th>
										</tr>
										';
									$no=1;
									foreach ($Trticket as $data){
										$this->mailbody .='<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.$no.'</p></td>
											<td><p class=MsoNormal> '.$data->ticketfor.'</p></td>
											<td><p class=MsoNormal> '.$data->ticketname.'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($data->dateofbirth)).'</p></td>
											<td><p class=MsoNormal> '.$data->phonenumber.'</p></td>
											<td><p class=MsoNormal> '.$data->gender.'</p></td>
											<td><p class=MsoNormal> '.$data->hrremarks.'</p></td>
											</tr>';
										$no++;
									}
									$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
									$this->mail->addAddress($usr->email, $usr->fullname);
									$this->mail->Subject = "Online Approval System -> Travel Request Reschedule";
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
									$Tr = Tr::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Tr as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Tr));
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
						$data['createdby']=$Employee->id;
						$data['RequestStatus']=0;
						try{
							$Tr = Tr::create($data);
							$data=$Tr->to_array();
							$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";
							if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220") || ($Employee->company->sapcode=="NKF") || ($Employee->company->sapcode=="RND")){
								if( ($Employee->company->sapcode=="NKF") || ($Employee->company->sapcode=="RND")){
									$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='TR' and tbl_approver.isactive='1' and approvaltype_id=17 and tbl_employee.companycode=?",$Employee->companycode)));
									if(count($Approver)>0){
										$Trapproval = new Trapproval();
										$Trapproval->tr_id = $Tr->id;
										$Trapproval->approver_id = $Approver->id;
										$Trapproval->save();
									}
								}
							}else{
								$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='TR' and tbl_approver.isactive='1' and approvaltype_id=17 and tbl_employee.company_id=?",$Employee->company_id)));
								if(count($Approver)>0){
									$Trapproval = new Trapproval();
									$Trapproval->tr_id = $Tr->id;
									$Trapproval->approver_id = $Approver->id;
									$Trapproval->save();
								}
							}
							$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='TR' and tbl_approver.isactive='1' and approvaltype_id=18")));
							if(count($Approver)>0){
								$Trapproval = new Trapproval();
								$Trapproval->tr_id = $Tr->id;
								$Trapproval->approver_id = $Approver->id;
								$Trapproval->save();
							}
							$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='TR' and tbl_approver.isactive='1' and approvaltype_id=19")));
							if(count($Approver2)>0){
								$Trapproval = new Trapproval();
								$Trapproval->tr_id = $Tr->id;
								$Trapproval->approver_id = $Approver2->id;
								$Trapproval->save();
							}
							$Trhistory = new Trhistory();
							$Trhistory->date = date("Y-m-d h:i:s");
							$Trhistory->fullname = $Employee->fullname;
							$Trhistory->approvaltype = "Originator";
							$Trhistory->tr_id = $Tr->id;
							$Trhistory->actiontype = 0;
							$Trhistory->save();
							
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "CreateTR";
							$err->errordate = date("Y-m-d h:i:s");
							$err->errormessage = $e->getMessage();
							$err->user = $this->currentUser->username;
							$err->ip = $this->ip;
							$err->save();
							$data = array("status"=>"error","message"=>$e->getMessage());
						}
						$logger = new Datalogger("TR","create",null,json_encode($data));
						$logger->SaveData();
						echo json_encode($data);									
						break;
					case 'delete':
						try {				
							$id = $this->post['id'];
							$Tr = Tr::find($id);
							if ($Tr->requeststatus==0){
								$approval = Trapproval::find("all",array('conditions' => array("tr_id=?",$id)));
								foreach ($approval as $delr){
									$delr->delete();
								}
								$detail = Trschedule::find("all",array('conditions' => array("tr_id=?",$id)));
								foreach ($detail as $delr){
									$delr->delete();
								}
								$detail = Trticket::find("all",array('conditions' => array("tr_id=?",$id)));
								foreach ($detail as $delr){
									$delr->delete();
								}
								$hist = Trhistory::find("all",array('conditions' => array("tr_id=?",$id)));
								foreach ($hist as $delr){
									$delr->delete();
								}
								$data = $Tr->to_array();
								$Tr->delete();
								$logger = new Datalogger("TR","delete",json_encode($data),null);
								$logger->SaveData();
								echo json_encode($Tr);
							} else {
								$data = array("status"=>"error","message"=>"You can't delete submitted request");
								echo json_encode($data);
							}
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "DeleteTR";
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
							$Tr = Tr::find($id,array('include'=>array('employee'=>array('company','department','designation','grade'))));
							$olddata = $Tr->to_array();
							$superior = $data['superior'];
							$depthead = $data['depthead'];
							unset($data['fullname']);
							unset($data['department']);
							unset($data['approvalstatus']);
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							if($superior==$Employee->id){
								$result= array("status"=>"error","message"=>"You cannot select yourself as your Direct superior");
								echo json_encode($result);
							}else{
								foreach($data as $key=>$val){
									$value=(($val===0) || ($val==='0') || ($val==='false'))?false:((($val===1) || ($val==='1') || ($val==='true'))?true:$val);
									$Tr->$key=$value;
								}
								$Tr->save();
								
								if (isset($data['depthead'])){
									$joins   = "LEFT JOIN tbl_approver ON (tbl_trapproval.approver_id = tbl_approver.id) ";					
									$dx = Trapproval::find('all',array('joins'=>$joins,'conditions' => array("tr_id=? and tbl_approver.approvaltype_id=16 and not(tbl_approver.employee_id=?)",$id,$depthead)));	
									foreach ($dx as $result) {
										//delete same type dept head approver
										$result->delete();
										$logger = new Datalogger("Trapproval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
									}
									$joins   = "LEFT JOIN tbl_approver ON (tbl_trapproval.approver_id = tbl_approver.id) ";					
									$Trapproval = Trapproval::find('all',array('joins'=>$joins,'conditions' => array("tr_id=? and tbl_approver.employee_id=?",$id,$depthead)));	
									foreach ($Trapproval as &$result) {
										$result		= $result->to_array();
										$result['no']=1;
									}			
									if(count($Trapproval)==0){ 
										$Approver = Approver::find('first',array('conditions'=>array("module='TR' and employee_id=? and approvaltype_id=16",$depthead)));
										if(count($Approver)>0){
											$Trapproval = new Trapproval();
											$Trapproval->tr_id = $Tr->id;
											$Trapproval->approver_id = $Approver->id;
											$Trapproval->save();
										}else{
											$approver = new Approver();
											$approver->module = "TR";
											$approver->employee_id=$depthead;
											$approver->sequence=1;
											$approver->approvaltype_id = 16;
											$approver->isfinal = false;
											$approver->save();
											$Trapproval = new Trapproval();
											$Trapproval->tr_id = $Tr->id;
											$Trapproval->approver_id = $approver->id;
											$Trapproval->save();
										}
									}
									
								}

								if (isset($data['superior'])){
									$joins   = "LEFT JOIN tbl_approver ON (tbl_trapproval.approver_id = tbl_approver.id) ";					
									$dx = Trapproval::find('all',array('joins'=>$joins,'conditions' => array("tr_id=? and tbl_approver.approvaltype_id=43 and not(tbl_approver.employee_id=?)",$id,$superior)));	
									foreach ($dx as $result) {
										//delete same type dept head approver
										$result->delete();
										$logger = new Datalogger("Trapproval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
									}
									$joins   = "LEFT JOIN tbl_approver ON (tbl_trapproval.approver_id = tbl_approver.id) ";					
									$Trapproval = Trapproval::find('all',array('joins'=>$joins,'conditions' => array("tr_id=? and tbl_approver.employee_id=?",$id,$superior)));	
									foreach ($Trapproval as &$result) {
										$result		= $result->to_array();
										$result['no']=1;
									}			
									if(count($Trapproval)==0){ 
										$Approver = Approver::find('first',array('conditions'=>array("module='TR' and employee_id=? and approvaltype_id=43",$superior)));
										if(count($Approver)>0){
											$Trapproval = new Trapproval();
											$Trapproval->tr_id = $Tr->id;
											$Trapproval->approver_id = $Approver->id;
											$Trapproval->save();
										}else{
											$approver = new Approver();
											$approver->module = "TR";
											$approver->employee_id=$superior;
											$approver->sequence=0;
											$approver->approvaltype_id = 43;
											$approver->isfinal = false;
											$approver->save();
											$Trapproval = new Trapproval();
											$Trapproval->tr_id = $Tr->id;
											$Trapproval->approver_id = $approver->id;
											$Trapproval->save();
										}
									}
									
								}
								
								if($data['requeststatus']==1){
									$Trapproval = Trapproval::find('all', array('conditions' => array("tr_id=?",$id)));					
									foreach($Trapproval as $data){
										$data->approvalstatus=0;
										$data->save();
									}
									$joinx   = "LEFT JOIN tbl_approver ON (tbl_trapproval.approver_id = tbl_approver.id) ";					
									$Trapproval = Trapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and tr_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
									$username = $Trapproval->approver->employee->loginname;
									$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
									$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Tr->employee->loginname)));
									$email=$usr->email;
									$Trschedule=Trschedule::find('all',array('conditions'=>array("tr_id=?",$id),'include'=>array('tr'=>array('employee'=>array('company','department','designation','grade')))));
									$Trticket=Trticket::find('all',array('conditions'=>array("tr_id=?",$id),'include'=>array('tr'=>array('employee'=>array('company','department','designation','grade')))));
									$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$adb->fullname.',</span></p>
										<p class=MsoNormal><span style="color:#1F497D">New Travel Request is awaiting for your approval:</span></p>
										<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->fullname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->sapid.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->designation->designationname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->company->companyname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->location->location.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
										</table>
										<p class=MsoNormal><b>Travel Schedule ( Jadwal Perjalanan)</b></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><th  rowspan="2"><p class=MsoNormal>No</p></th>
											<th colspan="2"><p class=MsoNormal>Departing <br>( Keberangkatan )</p></th>
											<th><p class=MsoNormal>From ( Dari )</p></th>
											<th colspan="2"><p class=MsoNormal>Arriving ( Ketibaan )</p></th>
											<th><p class=MsoNormal>To ( Ke )</p></th>
											<th><p class=MsoNormal>Region</p></th>
											<th rowspan="2"><p class=MsoNormal>Reason (Alasan)<br><small>(e.g. Meeting,<br>Seminar etc. )</small></p></th>
										</tr>
										<tr><th><p class=MsoNormal>Date (tgl) <br> <small>(dd/mm/yyyy)</small></p></th>
											<th><p class=MsoNormal>Time (Waktu)</p></th>
											<th><p class=MsoNormal>City/Country<br>(Kota/Negara)</p></th>
											<th><p class=MsoNormal>Date (tgl) <br> <small>(dd/mm/yyyy)</small></p></th>
											<th><p class=MsoNormal>Time (Waktu)</p></th>
											<th><p class=MsoNormal>City/Country<br>(Kota/Negara)</p></th>
											<th><p class=MsoNormal>R1/R2</p></th>
										</tr>';
									$no=1;					
									foreach ($Trschedule as $data){
										$this->mailbody .='<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.$no.'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($data->departdate)).'</p></td>
											<td><p class=MsoNormal> '.$data->departtime.'</p></td>
											<td><p class=MsoNormal> '.$data->departfrom.'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($data->arrivingdate)).'</p></td>
											<td><p class=MsoNormal> '.$data->arrivingtime.'</p></td>
											<td><p class=MsoNormal> '.$data->arrivingto.'</p></td>
											<td><p class=MsoNormal> '.$data->region.'</p></td>
											<td><p class=MsoNormal> '.$data->reason.'</p></td>
										</tr>';
										$no++;
									}
									$this->mailbody .='</table>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><th><p class=MsoNormal>No</p></th>
											<th><p class=MsoNormal>Ticket For (Untuk)</p></th>
											<th><p class=MsoNormal>Name <br>( Nama )</p></th>
											<th><p class=MsoNormal>Date of Birth <br>( Tgl. Lahir) <br> <small>(dd/mm/yyyy)</small></p></th>
											<th><p class=MsoNormal>Phone Number</p></th>
											<th><p class=MsoNormal>Gender</p></th>
											<th><p class=MsoNormal>Remarks /Confirmation from HR <br> ( Konfirmasi dari HR )</p></th>
										</tr>
										';
									$no=1;
									foreach ($Trticket as $data){
										$this->mailbody .='<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.$no.'</p></td>
											<td><p class=MsoNormal> '.$data->ticketfor.'</p></td>
											<td><p class=MsoNormal> '.$data->ticketname.'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($data->dateofbirth)).'</p></td>
											<td><p class=MsoNormal> '.$data->phonenumber.'</p></td>
											<td><p class=MsoNormal> '.$data->gender.'</p></td>
											<td><p class=MsoNormal> '.$data->hrremarks.'</p></td>
											</tr>';
										$no++;
									}
									$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
									$this->mail->addAddress($adb->email, $adb->fullname);
									$this->mail->Subject = "Online Approval System -> New Travel Request Submission";
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
									$Trhistory = new Trhistory();
									$Trhistory->date = date("Y-m-d h:i:s");
									$Trhistory->fullname = $Employee->fullname;
									$Trhistory->tr_id = $id;
									$Trhistory->approvaltype = "Originator";
									$Trhistory->actiontype = 2;
									$Trhistory->save();
								}else{
									$Trhistory = new Trhistory();
									$Trhistory->date = date("Y-m-d h:i:s");
									$Trhistory->fullname = $Employee->fullname;
									$Trhistory->tr_id = $id;
									$Trhistory->approvaltype = "Originator";
									$Trhistory->actiontype = 1;
									$Trhistory->save();
								}
								$logger = new Datalogger("TR","update",json_encode($olddata),json_encode($data));
								$logger->SaveData();
							}
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "UpdateTR";
							$err->errordate = date("Y-m-d h:i:s");
							$err->errormessage = $e->getMessage();
							$err->user = $this->currentUser->username;
							$err->ip = $this->ip;
							$err->save();
							$data = array("status"=>"error","message"=>$e->getMessage());
						}
						break;
					default:
						$Tr = Tr::all();
						foreach ($Tr as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Tr, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}
	function trApproval(){
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
							$join   = "LEFT JOIN tbl_approver ON (tbl_trapproval.approver_id = tbl_approver.id) ";
							$Trapproval = Trapproval::find('all', array('joins'=>$join,'conditions' => array("tr_id=?",$id),'include' => array('approver'=>array('approvaltype')),"order"=>"tbl_approver.sequence"));
							foreach ($Trapproval as &$result) {
								$approvaltype = $result->approver->approvaltype_id;
								$result		= $result->to_array();
								$result['approvaltype']=$approvaltype;
							}
							echo json_encode($Trapproval, JSON_NUMERIC_CHECK);
						}else{
							$Trapproval = new Trapproval();
							echo json_encode($Trapproval);
						}
						break;
					case 'find':
						$query=$this->post['query'];		
						if(isset($query['status'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join   = "LEFT JOIN tbl_approver ON (tbl_trapproval.approver_id = tbl_approver.id) ";
							$dx = Trapproval::find('first', array('joins'=>$join,'conditions' => array("tr_id=? and tbl_approver.employee_id = ?",$query['tr_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
							$Tr = Tr::find($query['tr_id']);
							if($dx->approver->isfinal==1){
								$data=array("jml"=>1);
							}else{
								$join   = "LEFT JOIN tbl_approver ON (tbl_trapproval.approver_id = tbl_approver.id) ";
								$Trapproval = Trapproval::find('all', array('joins'=>$join,'conditions' => array("tr_id=? and ApprovalStatus<=1 and not tbl_approver.employee_id=?",$query['tr_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
								foreach ($Trapproval as &$result) {
									$fullname	= $result->approver->employee->fullname;
									$result		= $result->to_array();
									$result['fullname']=$fullname;
								}
								$data=array("jml"=>count($Trapproval));
							}						
						} else if(isset($query['pending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
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
								$department	= $result->employee->department->departmentname;
								$result		= $result->to_array();
								$result['fullname']=$fullname;
								$result['department']=$department;
							}
							$data=$Tr;
						} else if(isset($query['mypending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
							$Tr = Tr::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
							$jml=0;
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
							$data=array("jml"=>count($Tr));
						} else if(isset($query['filter'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join = "LEFT JOIN vwtrreport v on tbl_tr.id=v.id LEFT JOIN tbl_employee ON (tbl_tr.employee_id = tbl_employee.id) ";
							$sel = 'tbl_tr.*, v.laststatus,v.personholding ';

							if($Employee->location->sapcode=='0200' || $this->currentUser->isadmin){
								$Tr = Tr::find('all',array('joins'=>$join,'select'=>$sel,'include' => array('employee'=>array('company','department'))));
							}else{
								$Tr = Tr::find('all',array('joins'=>$join,'select'=>$sel,'conditions' => array('tbl_tr.RequestStatus=3 and tbl_employee.company_id=?',$Employee->company_id ),'include' => array('employee'=>array('company','department'))));
							}
							
							foreach ($Tr as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=$Tr;
						} else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Trapproval = Trapproval::create($data);
						$logger = new Datalogger("Trapproval","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':
						$id = $this->post['id'];
						$Trapproval = Trapproval::find($id);
						$data=$Trapproval->to_array();
						$Trapproval->delete();
						$logger = new Datalogger("Trapproval","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Trapproval);
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
						
						$join   = "LEFT JOIN tbl_approver ON (tbl_trapproval.approver_id = tbl_approver.id) ";
						if (isset($data['mode'])){
							$Trapproval = Trapproval::find('first', array('joins'=>$join,'conditions' => array("tr_id=? and tbl_approver.employee_id=?",$doid,$Employee->id),'include' => array('approver'=>array('employee','approvaltype'))));
							unset($data['mode']);
						}else{
							$Trapproval = Trapproval::find($this->post['id'],array('include' => array('approver'=>array('employee','approvaltype'))));
						}
						$olddata = $Trapproval->to_array();
						foreach($data as $key=>$val){
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$Trapproval->$key=$val;
						}
						$Trapproval->save();
						$logger = new Datalogger("Trapproval","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						if (isset($mode) && ($mode=='approve')){
							$Tr = Tr::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));
							$joinx   = "LEFT JOIN tbl_approver ON (tbl_trapproval.approver_id = tbl_approver.id) ";					
							$nTrapproval = Trapproval::find('first',array('joins'=>$joinx,'conditions' => array("tr_id=? and ApprovalStatus=0",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
							$username = $nTrapproval->approver->employee->loginname;
							$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
							$Trschedule=Trschedule::find('all',array('conditions'=>array("tr_id=?",$doid),'include'=>array('tr'=>array('employee'=>array('company','department','designation','grade','location')))));
							$Trticket=Trticket::find('all',array('conditions'=>array("tr_id=?",$doid),'include'=>array('tr'=>array('employee'=>array('company','department','designation','grade','location')))));
							$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Tr->employee->loginname)));
							$email=$usr->email;
							$superiorId=$Tr->depthead;
							$Superior = Employee::find($superiorId);
							$supAdb = Addressbook::find('first',array('conditions'=>array("username=?",$Superior->loginname)));
							$complete = false;
							$Trhistory = new Trhistory();
							$Trhistory->date = date("Y-m-d h:i:s");
							$Trhistory->fullname = $Employee->fullname;
							$Trhistory->approvaltype = $Trapproval->approver->approvaltype->approvaltype;
							$Trhistory->remarks = $data['remarks'];
							$Trhistory->tr_id = $doid;
							
							switch ($data['approvalstatus']){
								case '1':
									$Tr->requeststatus = 2;
									$emto=$email;$emname=$Tr->employee->fullname;
									$this->mail->Subject = "Online Approval System -> Need Rework";
									$red = 'Your Travel Request require some rework :
											<br>Remarks from Approver : <font color="red">'.$data['remarks']."</font>";
									$Trhistory->actiontype = 3;
									break;
								case '2':
									if ($Trapproval->approver->isfinal == 1){
										$Tr->requeststatus = 3;
										$emto=$email;$emname=$Tr->employee->fullname;
										$this->mail->Subject = "Online Approval System -> Approval Completed";
										$red = '<p>Your Travel Request request has been approved</p>
													<p><b><span lang=EN-US style=\'color:#002060\'>Note : Please <u>forward</u> this electronic approval to your respective Human Resource Department.</span></b></p>';
										//delete unnecessary approver
										$Trapproval = Trapproval::find('all', array('joins'=>$join,'conditions' => array("tr_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
										foreach ($Trapproval as $data) {
											if($data->approvalstatus==0){
												$logger = new Datalogger("Trapproval","delete",json_encode($data->to_array()),"automatic remove unnecessary approver by system");
												$logger->SaveData();
												$data->delete();
											}
										}
										$complete =true;
									}
									else{
										$Tr->requeststatus = 1;
										$emto=$adb->email;$emname=$adb->fullname;
										$this->mail->Subject = "Online Approval System -> New Travel Request";
										$red = 'New Travel Request awaiting for your approval:';
									}
									$Trhistory->actiontype = 4;							
									break;
								case '3':
									$Tr->requeststatus = 4;
									$emto=$email;$emname=$Tr->employee->fullname;
									$Trhistory->actiontype = 5;
									$this->mail->Subject = "Online Approval System -> Request Rejected";
									$red = 'Your Travel Request has been rejected';
									break;
								default:
									break;
							}
							$Tr->save();
							$Trhistory->save();
							echo "email to :".$emto." ->".$emname;
							$this->mail->addAddress($emto, $emname);
							$TrJ = Tr::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));
							$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$emname.',</span></p>
								<p class=MsoNormal><span style="color:#1F497D">'.$red.'</span></p>
								<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
								<table border=1 cellspacing=0 cellpadding=3 width=683>
								<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->fullname.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->sapid.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->designation->designationname.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->company->companyname.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->location->location.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
								</table>
								<p class=MsoNormal><b>Travel Schedule ( Jadwal Perjalanan)</b></p>
								<table border=1 cellspacing=0 cellpadding=3 width=683>
								<tr><th  rowspan="2"><p class=MsoNormal>No</p></th>
									<th colspan="2"><p class=MsoNormal>Departing <br>( Keberangkatan )</p></th>
									<th><p class=MsoNormal>From ( Dari )</p></th>
									<th colspan="2"><p class=MsoNormal>Arriving ( Ketibaan )</p></th>
									<th><p class=MsoNormal>To ( Ke )</p></th>
									<th><p class=MsoNormal>Region</p></th>
									<th rowspan="2"><p class=MsoNormal>Reason (Alasan)<br><small>(e.g. Meeting,<br>Seminar etc. )</small></p></th>
								</tr>
								<tr><th><p class=MsoNormal>Date (tgl) <br> <small>(dd/mm/yyyy)</small></p></th>
									<th><p class=MsoNormal>Time (Waktu)</p></th>
									<th><p class=MsoNormal>City/Country<br>(Kota/Negara)</p></th>
									<th><p class=MsoNormal>Date (tgl) <br> <small>(dd/mm/yyyy)</small></p></th>
									<th><p class=MsoNormal>Time (Waktu)</p></th>
									<th><p class=MsoNormal>City/Country<br>(Kota/Negara)</p></th>
									<th><p class=MsoNormal>R1/R2</p></th>
								</tr>';
							$no=1;					
							foreach ($Trschedule as $data){
								$this->mailbody .='<tr style="height:22.5pt">
									<td><p class=MsoNormal> '.$no.'</p></td>
									<td><p class=MsoNormal> '.date("d/m/Y",strtotime($data->departdate)).'</p></td>
									<td><p class=MsoNormal> '.$data->departtime.'</p></td>
									<td><p class=MsoNormal> '.$data->departfrom.'</p></td>
									<td><p class=MsoNormal> '.date("d/m/Y",strtotime($data->arrivingdate)).'</p></td>
									<td><p class=MsoNormal> '.$data->arrivingtime.'</p></td>
									<td><p class=MsoNormal> '.$data->arrivingto.'</p></td>
									<td><p class=MsoNormal> '.$data->region.'</p></td>
									<td><p class=MsoNormal> '.$data->reason.'</p></td>
								</tr>';
								$no++;
							}
							$this->mailbody .='</table>
								<table border=1 cellspacing=0 cellpadding=3 width=683>
								<tr><th><p class=MsoNormal>No</p></th>
									<th><p class=MsoNormal>Ticket For (Untuk)</p></th>
									<th><p class=MsoNormal>Name <br>( Nama )</p></th>
									<th><p class=MsoNormal>Date of Birth <br>( Tgl. Lahir) <br> <small>(dd/mm/yyyy)</small></p></th>
									<th><p class=MsoNormal>Phone Number</p></th>
									<th><p class=MsoNormal>Gender</p></th>
									<th><p class=MsoNormal>Remarks /Confirmation from HR <br> ( Konfirmasi dari HR )</p></th>
								</tr>
								';
							$no=1;
							foreach ($Trticket as $data){
								$this->mailbody .='<tr style="height:22.5pt">
									<td><p class=MsoNormal> '.$no.'</p></td>
									<td><p class=MsoNormal> '.$data->ticketfor.'</p></td>
									<td><p class=MsoNormal> '.$data->ticketname.'</p></td>
									<td><p class=MsoNormal> '.date("d/m/Y",strtotime($data->dateofbirth)).'</p></td>
									<td><p class=MsoNormal> '.$data->phonenumber.'</p></td>
									<td><p class=MsoNormal> '.$data->gender.'</p></td>
									<td><p class=MsoNormal> '.$data->hrremarks.'</p></td>
									</tr>';
								$no++;
							}
							$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
							$this->mail->msgHTML($this->mailbody);
							if ($complete){
								$fileName = $this->generatePDF($doid);
								$filePath = SITE_PATH.DS.$fileName;
								$this->mail->addAttachment($filePath);
							}
							if (!$this->mail->send()) {
								$err = new Errorlog();
								$err->errortype = "TR Mail";
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
						$Trapproval = Trapproval::all();
						foreach ($Trapproval as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Trapproval, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}
	function generatePDF($doid){
		
		$Tr = Tr::find($doid);
		$Trschedule=Trschedule::find('all',array('conditions'=>array("tr_id=?",$doid),'include'=>array('tr'=>array('employee'=>array('company','department','designation','grade','location')))));
		$Trticket=Trticket::find('all',array('conditions'=>array("tr_id=?",$doid),'include'=>array('tr'=>array('employee'=>array('company','department','designation','grade','location')))));					
		$superiorId=$Tr->superior;
		$Superior = Employee::find($superiorId);
		$supAdb = Addressbook::find('first',array('conditions'=>array("username=?",$Superior->loginname)));
		$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Tr->employee->loginname)));
		$email=$usr->email;
		$pdfContent = '<style>
					table tr td { font-size:10px;}
					</style><table style="width:800px;max-width:800px" cellspacing="0" border="0"  width="100%">
												<tr>
													<td style="border-top: 1px solid #000000;width:700px;height5px; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=18 align="left" valign=bottom ><small>Form No.: </small></td>
													</tr>
												<tr>
													<td style="border-left: 1px solid #000000; border-right: 1px solid #000000;height:15px;font-size:15pt" colspan=18 align="center" valign=middle ><b>TRAVEL REQUEST FORM</b></td>
													</tr>
												<tr>
													<td style="border-bottom: 1px solid #000000; height5px;border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=18 align="center" valign=bottom >(Permohonan Dinas Luar)</td>
													</tr>
												<tr>
													<td style="border-top: 1px solid #000000;height5px; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=18 align="center" valign=bottom ></td>
													</tr>
												<tr>
													<td style="border-left: 1px solid #000000; width:2px;max-width:2px;height:15px" align="left" valign=bottom></td>
													<td style="width:150px;max-width:150px;" colspan=4 align="left" valign=bottom ><b>*Personnel Number (SAP ID)</b></td>
													<td style="width:5px;max-width:5px;"  align="center" valign=bottom >:</td>
													<td style="width:80px;max-width:800px;border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=3 align="left" valign=bottom >'.$Tr->employee->sapid.'</td>
													<td style="width:5px;max-width:5px;" align="left" valign=bottom ></td>
													<td style="width:80px;max-width:80px;" colspan=3 align="left" valign=bottom >Superior\'s Name </td>
													<td style="width:5px;max-width:5px;" align="center" valign=bottom >:</td>
													<td style="width:80px;max-width:80px;border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=3 align="left" valign=bottom >'.$Superior->fullname.'</td>
													<td style="width:5px;max-width:5px;border-right: 1px solid #000000" align="left" valign=bottom ></td>
												</tr>
												<tr>
													<td style="border-left: 1px solid #000000" colspan=10 height="11" align="center" valign=middle ><br></td>
													<td style="border-right: 1px solid #000000" colspan=8 align="left" valign=middle ><small>(Nama Atasan)</small></td>
													</tr>
												<tr>
													<td style="border-left: 1px solid #000000; width:2px;" height="20" align="left" valign=bottom ></td>
													<td colspan=4 align="left" valign=bottom >Name (Nama)</td>
													<td align="center" valign=bottom >:</td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=3 align="left" valign=bottom >'.$Tr->employee->fullname.'</td>
													<td align="left" valign=bottom ><br></td>
													<td colspan=3 align="left" valign=bottom >Superior\'s e-mail</td>
													<td align="center" valign=bottom >:</td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=3 align="left" valign=bottom ><u><a href="mailto:'.$supAdb->email.'">'.$supAdb->email.'</a></u></td>
													<td style="border-right: 1px solid #000000" align="left" valign=bottom ><br></td>
												</tr>
												<tr>
													<td style="border-left: 1px solid #000000" colspan=10 height="11" align="center" valign=middle ><br></td>
													<td style="border-right: 1px solid #000000" colspan=8 align="left" valign=middle ><small>(E-mail Atasan)</small></td>
													</tr>
												<tr>
													<td style="border-left: 1px solid #000000" height="20" align="left" valign=bottom ></td>
													<td colspan=4 align="left" valign=bottom >E-mail</td>
													<td align="center" valign=bottom >:</td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=3 align="left" valign=bottom ><u><a href="mailto:'.$email.'">'.$email.'</a></u></td>
													<td style="border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=9 align="center" valign=bottom ><br></td>
													</tr>
												<tr>
													<td style="border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=18 height="5" align="center" valign=bottom ></td>
													</tr>
												<tr>
													<td style="border-left: 1px solid #000000" height="20" align="left" valign=bottom ></td>
													<td colspan=4 align="left" valign=bottom >Business Group/Business Unit</td>
													<td align="center" valign=bottom >:</td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=3 align="left" valign=bottom >'.$Tr->employee->company->companyname.'</td>
													<td align="left" valign=bottom ></td>
													<td colspan=3 align="left" valign=bottom >Position (Jabatan)</td>
													<td align="center" valign=bottom >:</td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=3 align="left" valign=bottom >'.$Tr->employee->designation->designationname.'</td>
													<td style="border-right: 1px solid #000000" align="left" valign=bottom ></td>
												</tr>
												<tr>
													<td style="border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=18 height="5" align="center" valign=bottom ></td>
													</tr>
												<tr>
													<td style="border-left: 1px solid #000000" height="20" align="left" valign=bottom ></td>
													<td colspan=4 align="left" valign=bottom >Location (Lokasi)</td>
													<td align="center" valign=bottom >:</td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=3 align="left" valign=bottom >'.$Tr->employee->location->location.'</td>
													<td align="left" valign=bottom ></td>
													<td colspan=3 align="left" valign=bottom >Cost Center</td>
													<td align="center" valign=bottom >:</td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=3 align="left" valign=bottom ><br></td>
													<td style="border-right: 1px solid #000000" align="left" valign=bottom ></td>
												</tr>
												<tr>
													<td style="border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=18 height="5" align="center" valign=bottom ></td>
													</tr>
												<tr>
													<td style="border-left: 1px solid #000000; border-bottom: 1px solid #000000; " height="5" align="left" valign=bottom ></td>
													<td style="border-bottom: 1px solid #000000;  border-right: 1px solid #000000" colspan=17 height="5" align="left" valign=bottom ><small>* Mandatory fields in SAP (Harus Diisi)</small></td>
													</tr>
												<tr>
													<td style="border-top: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=9 height="5" align="center" valign=bottom ></td>
													<td style="border-top: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=9 align="center" valign=bottom ></td>
													</tr>
												<tr>
													<td style="border-left: 1px solid #000000;" height="20" align="left" valign=bottom ></td>
													<td style="width:5px;max-width:5px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle  ><b>'.(($Tr->islandtransport)?'X':'').'</b></td>
													<td style="width:2px" align="left" valign=bottom ></td>
													<td style="width:325px;border-right: 1px solid #000000;" colspan=6 align="left" valign=bottom ><b>VIA LAND TRANSPORTATION (VIA DARAT)</b></td>
													<td style="border-left: 1px solid #000000;max-width:5px" align="left" valign=bottom ></td>
													<td style="width:10px;max-width:10px;border-top: 1px solid #000000; border-bottom: 1px solid #000000;border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle  ><b>'.(($Tr->isairtransport)?'X':'').'</b></td>
													<td style="border-left: 1px solid #000000;max-width:5px" align="left" valign=bottom></td>
													<td style="width:325px;border-right: 1px solid #000000" colspan=6 align="left" valign=botto mwidth="5"  ><b> VIA AIR TRANSPORTATION (VIA UDARA)</b></td>
													</tr>
												<tr>
													<td style="border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=9 height="5" align="center" valign=bottom ></td>
													<td style="border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=9 align="center" valign=bottom ></td>
													</tr>
												<tr>
													<td style="border-top: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=9 height="5" align="center" valign=bottom ></td>
													<td style="border-top: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=9 align="center" valign=bottom ></td>
													</tr>
												<tr>
													<td style="border-left: 1px solid #000000" height="20" align="left" valign=bottom ></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle >'.(($Tr->ispersonalvehicle)?'X':'').'</td>
													<td align="left" valign=bottom ></td>
													<td style="border-right: 1px solid #000000" colspan=6 align="left" valign=bottom >Personal Vehicle (Dengan Mobil Sendiri - BK)</td>
													<td style="border-left: 1px solid #000000" align="left" valign=bottom ></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle  >'.(($Tr->iscommercialairline)?'X':'').'</td>
													<td style="border-left: 1px solid #000000" align="left" valign=bottom ></td>
													<td style="border-right: 1px solid #000000" colspan=6 align="left" valign=bottom >Commercial Airline (Pesawat Komersial)</td>
													</tr>
												<tr>
													<td style="border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=9 height="5" align="center" valign=bottom ></td>
													<td style="border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=9 align="center" valign=bottom ></td>
													</tr>
												<tr>
													<td style="border-left: 1px solid #000000" height="20" align="left" valign=bottom ></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle  >'.(($Tr->ispoolcar)?'X':'').'</td>
													<td align="left" valign=bottom ><br></td>
													<td style="border-right: 1px solid #000000" colspan=6 align="left" valign=bottom >Pool Car (Dengan Mobil Pool)</td>
													<td style="border-left: 1px solid #000000" align="left" valign=bottom ></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle  >'.(($Tr->iscompanyaircraft)?'X':'').'</td>
													<td style="border-left: 1px solid #000000" align="left" valign=bottom ></td>
													<td style="border-right: 1px solid #000000" colspan=6 align="left" valign=bottom >Company Aircraft (Pesawat Perusahaan)</td>
													</tr>
												<tr>
													<td style="border-left: 1px solid #000000" colspan=3 rowspan=3 height="47" align="center" valign=bottom ></td>
													<td style="border-right: 1px solid #000000" colspan=6 align="left" valign=bottom > '.(($Tr->isdropoffonly)?'[X] ':'[ ] ').' Drop Off Only (Drop Saja)</td>
													<td style="border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=9 rowspan=7 align="center" valign=bottom ></td>
													</tr>
												<tr>
													<td style="border-right: 1px solid #000000" colspan=6 align="left" valign=bottom > '.(($Tr->isuntiljobfinish)?'[X] ':'[ ] ').'Until Job Finished (Sampai Tugas Selesai)(Tgl : '.(($Tr->isuntiljobfinish)?date("d/m/Y",strtotime($Tr->jobfinishdate)):'_________').' )</td>
													</tr>
												<tr>
													<td style="border-right: 1px solid #000000" colspan=6 align="left" valign=bottom ></td>
													</tr>
												<tr>
													<td style="border-left: 1px solid #000000" height="20" align="left" valign=bottom ></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="center" valign=midle >'.(($Tr->isbytrain)?'X':'').'</td>
													<td align="left" valign=bottom ></td>
													<td style="border-right: 1px solid #000000" colspan=6 align="left" valign=bottom >By Train (Dengan Kereta Api)</td>
													</tr>
												<tr>
													<td style="border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=9 height="7" align="center" valign=bottom ></td>
													</tr>
												<tr>
													<td style="border-left: 1px solid #000000" height="20" align="left" valign=bottom ></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle  >'.(($Tr->isother)?'X':'').'</td>
													<td align="left" valign=bottom ></td>
													<td style="border-right: 1px solid #000000" colspan=6 align="left" valign=bottom >Other (Please specify):'.(($Tr->isother)?$Tr->otherlandtransportdesc:'_____________').'</td>
													</tr>
													
												<tr>
													<td style="border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=9 height="5" align="center" valign=bottom ></td>
													</tr></table>
												<h5><b>Travel Schedule (Jadwal Perjalanan)</b></h5>
												<table style="width:800px;max-width:800px" cellspacing="0" border="0"  width="100%">
												<tr>
													<td style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" rowspan=2 align="center" valign=middle ><b>No.</b></td>
													<td style="width:120px;max-width:120px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000;" colspan=2 align="center" valign=middle ><b>Departing <br>(Keberangkatan)</b></td>
													<td style="width:90px;max-width:90px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" rowspan=2 align="center" valign=middle ><b>From (Dari) <br> City/Country <br>(Kota/Negara)</b></td>
													<td style="width:130px;max-width:130px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" colspan=2 align="center" valign=middle ><b>Arriving (Ketibaan)</b></td>
													<td style="width:90px;max-width:90px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" rowspan=2 valign=middle ><b>To (Ke)<br>City/Country <br>(Kota/Negara)</b></td>
													<td style="width:30px;max-width:30px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" rowspan=2 align="center" valign=middle><b>Region<br>R1/ R2*</b></td>
													<td style="width:200px;max-width:200px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  rowspan=2 align="center" valign=middle ><b>Reason (Alasan)<br>(e.g. Meeting, Seminar, etc)</b></td>
													</tr>
												<tr>
													<td style="width:70px;max-width:70px;border-bottom: 1px solid #000000; border-left: 1px solid #000000;" align="center" valign=middle><b>Date (Tgl)<br><small>(dd/mm/yyyy)</small></b></td>
													<td style="width:50px;max-width:70px;border-bottom: 1px solid #000000; border-left: 1px solid #000000;" align="center" valign=middle ><b>Time<br> (Waktu)</b></td>
													<td style="width:80px;max-width:80px;border-bottom: 1px solid #000000; border-left: 1px solid #000000;" align="center" valign=middle  ><b>Date (Tgl)<br><small>(dd/mm/yyyy)</small></b></td>
													<td style="width:50px;max-width:70px;border-bottom: 1px solid #000000; border-left: 1px solid #000000;"  align="center" valign=middle  ><b>Time<br> (Waktu)</b></td>
													</tr>
												';
												$no=1;
												foreach ($Trschedule as $data){	
													$pdfContent .='<tr>
														<td style="width:15px;max-width:15px; border-bottom: 1px solid #000000; border-left: 1px solid #000000; height:25px;" align="right"  >'.$no.'</td>
														<td style="width:70px;max-width:70px; border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" >'.date("d/m/Y",strtotime($data->departdate)).'</td>
														<td style="width:50px;max-width:50px; border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" >'.date("H:i",strtotime($data->departtime)).'</td>
														<td style="width:90px;max-width:90px; border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="left" >'.$data->departfrom.'</td>
														<td style="width:70px;max-width:70px; border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="right"  >'.date("d/m/Y",strtotime($data->arrivingdate)).'</td>
														<td style="width:50px;max-width:50px; border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" >'.date("H:i",strtotime($data->arrivingtime)).'</td>
														<td style="width:90px;max-width:90px; border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="left"  >'.$data->arrivingto.'</td>
														<td style="width:30px;max-width:30px; border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="left" >'.$data->region.'</td>
														<td style="width:200px;max-width:200px; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="center" >'.wordwrap($data->reason, 40, "<br>").'</td>
														</tr>';
														$no++;
												}
												$pdfContent .='
												<tr>
												<td colspan=9><br></td>
												</tr><tr>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000;" align="center" valign=middle><b>No.</b></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000;" colspan=2 align="center" valign=middle ><b>Tickets For (Untuk)</b></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000;" align="center" valign=middle ><b>Name (Nama)</b></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000;" align="center" valign=middle ><b>Date of Birth <br>(Tgl. Lahir)<br><small>(dd/mm/yyyy)</small></b></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000;" align="center" valign=middle ><b>Gender</b></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000;" align="center" valign=middle ><b>Phone Number</b></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=2 align="center" valign=middle ><b>Remarks / Confirmation from HR (Konfirmasi dari HR)</b></td>
													</tr>
												<tr>
													</tr>';
												$no=1;
												foreach ($Trticket as $data){	
													$pdfContent .='<tr>
														<td style=" border-bottom: 1px solid #000000; border-left: 1px solid #000000; " height="20" align="center" valign=middle  >'.$no.'</td>
														<td style=" border-bottom: 1px solid #000000; border-left: 1px solid #000000; " colspan=2 align="center" valign=middle >'.$data->ticketfor.'</td>
														<td style=" border-bottom: 1px solid #000000; border-left: 1px solid #000000; " align="center" valign=middle >'.$data->ticketname.'</td>
														<td style=" border-bottom: 1px solid #000000; border-left: 1px solid #000000; " align="center" valign=middle >'.date("d/m/Y",strtotime($data->dateofbirth)).'</td>
														<td style=" border-bottom: 1px solid #000000; border-left: 1px solid #000000; " align="center" valign=middle >'.$data->gender.'</td>
														<td style=" border-bottom: 1px solid #000000; border-left: 1px solid #000000; " align="center" valign=middle >'.$data->phonenumber.'</td>
														<td style=" border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=2 align="left" valign=middle >'.wordwrap($data->hrremarks, 40, "<br>").'</td>
														</tr>';
														$no++;
												}
												/*
												$pdfContent .='<tr>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=18 height="11" align="center" valign=bottom ><br></td>
													</tr>
												<tr>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=18 height="20" align="center" valign=middle ><b>Travel Cash Advance</b></td>
													</tr>
												<tr>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=18 height="20" align="center" valign=middle ><b>Purpose (Keperluan)</b></td>
													</tr>
												<tr>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=18 height="20" align="left" valign=bottom >'.wordwrap($Tr->travelcashadvancepurpose, 100, "<br>").'</td>
													</tr>
												<tr>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=18 height="20" align="left" valign=middle ><b>Total (Jumlah) :  Amount in words (Terbilang) : </b></td>
													</tr>
												<tr>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=18 height="21" align="center" valign=bottom ><br></td>
													</tr>
												<tr>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=12 height="21" align="center" valign=middle ><b>Estimate (Estimasi) :</b></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=6 rowspan=11 align="left" valign=top >I understand that this advance must be settled within 3 days from the date of return from the travel (for travel advance) or within 30 days of date of advance (for other types of advance).  If I fail to settle it within the required time period, I understand that Finance Dept will proceed to make the appropriate deductions from my salary without prior notice.<br>(Saya mengerti bahwa panjar ini harus diselesaikan dalam waktu 30 (tiga puluh) hari kerja dari tanggal kembali dinas (untuk panjar dinas) atau dari tanggal pengambilan (untuk panjar lainnya).  Jika saya gagal untuk menyelesaikannya setelah batas waktu, saya memberikan kewenangan kepada Finance Dept. untuk memproses pemotongan gaji saya tanpa pemberitahuan.)</td>
													</tr>
												<tr>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=7 height="20" align="left" valign=top ><b>SPPD     </b></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="right" valign=top >'.$Tr->sppddays.' days (hari)</td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=4 align="right" valign=bottom  >Rp. '.number_format($Tr->sppdammount,0).'</td>
													</tr>
												<tr>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=7 height="20" align="left" valign=top ><b>Taxi        </b></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="right" valign=top >'.$Tr->taxidays.' days (hari)</td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=4 align="right" valign=bottom  >Rp. '.number_format($Tr->taxiammount,0).'</td>
													</tr>
												<tr>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=7 height="20" align="left" valign=top ><b>Accommodation (Penginapan)</b></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="right" valign=top >'.$Tr->accommodationdays.' days (hari)</td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=4 align="right" valign=bottom  >Rp. '.number_format($Tr->accommodationammount,0).'</td>
													</tr>
												<tr>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=7 height="20" align="left" valign=top ><b>Telephone (Telepon)</b></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="right" valign=top >'.$Tr->telephonedays.' days (hari)</td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=4 align="right" valign=bottom  >Rp. '.number_format($Tr->telephoneammount,0).'</td>
													</tr>
												<tr>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=7 height="20" align="left" valign=top ><b>Others (Lain-lain) :</b></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="right" valign=top >'.$Tr->otheridrdays.' days (hari)</td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=4 align="right" valign=bottom  >Rp. '.number_format($Tr->otheridrammount,0).'</td>
													</tr>
												<tr>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=7 height="20" align="left" valign=bottom ><br></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="center" valign=bottom ><b>Total (IDR)</b></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=4 align="right" valign=bottom  ><b> Rp '.number_format($Tr->totaladvanceidr,0).'</b></td>
													</tr>
												<tr>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=7 height="20" align="left" valign=bottom >Per diem</td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="right" valign=top ><br></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=4 align="left" valign=bottom >USD '.number_format($Tr->perdiemammount,0).'</td>
													</tr>
												<tr>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=7 height="21" align="left" valign=bottom >Others (Lain-lain) :</td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="right" valign=top ><br></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=4 align="left" valign=bottom >USD '.number_format($Tr->otherusdammount,0).'</td>
													</tr>
												<tr>
													<td style="border-top: 1px solid #000000; border-left: 1px solid #000000" colspan=7 height="21" align="center" valign=bottom ><br></td>
													<td style="border-right: 1px solid #000000" align="center" valign=bottom ><b>Total (USD)</b></td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=4 align="left" valign=bottom ><b>USD '.number_format($Tr->totaladvanceusd,0).'</b></td>
													</tr>
												<tr>
													<td style="border-bottom: 1px solid #000000; border-left: 1px solid #000000" colspan=12 height="21" align="center" valign=bottom ><br></td>
													</tr>';
												*/
												$joinx   = "LEFT JOIN tbl_approver ON (tbl_trapproval.approver_id = tbl_approver.id) ";					
												$Trapproval = Trapproval::find('all',array('joins'=>$joinx,'conditions' => array("tr_id=?",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee','approvaltype'))));							
												foreach ($Trapproval as $data){
													if(($data->approver->approvaltype->id==16) || ($data->approver->employee_id==$Tr->depthead)){
														$deptheadname = $data->approver->employee->fullname;
														$datedepthead = date("d/m/Y",strtotime($data->approvaldate));
													}
													if(($data->approver->approvaltype->id==43) || ($data->approver->employee_id==$Tr->superior)){
														$superiorname = $data->approver->employee->fullname;
														$datesuperior = date("d/m/Y",strtotime($data->approvaldate));
													}
													if($data->approver->approvaltype->id==17) {
														$hrbuname = $data->approver->employee->fullname;
														$hrbudate = date("d/m/Y",strtotime($data->approvaldate));
													}
													if($data->approver->approvaltype->id==18) {
														$hrmoname = $data->approver->employee->fullname;
														$hrmodate = date("d/m/Y",strtotime($data->approvaldate));
													}
													if($data->approver->approvaltype->id==19) {
														$mdname = $data->approver->employee->fullname;
														$mddate = date("d/m/Y",strtotime($data->approvaldate));
													}
												}
												$pdfContent .='</table><br>
												<table style="width:800px;max-width:800px" cellspacing="0" border="0"  width="100%"><tr>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000;" height="20" align="center" valign=bottom >Prepared By (Dipersiapkan Oleh)</td>
													<td style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=5 align="center" valign=bottom >Approved By (Disetujui Oleh)</td>
													</tr>
												<tr>
													<td style="width:140px;max-width:160px;border-bottom: 1px solid #000000; border-left: 1px solid #000000; " height="60" align="center" valign=bottom ><br><img src="images/approved.png" style="height:25pt" alt="Approved from System"></td>
													<td style="width:110px;max-width:160px;border-bottom: 1px solid #000000; border-left: 1px solid #000000; " align="center" valign=bottom ><br>'.(($superiorname=="")?"":'<img src="images/approved.png" style="height:25pt" alt="Approved from System">').'</td>
													<td style="width:110px;max-width:160px;border-bottom: 1px solid #000000; border-left: 1px solid #000000; " align="center" valign=bottom ><br>'.(($deptheadname=="")?"":'<img src="images/approved.png" style="height:25pt" alt="Approved from System">').'</td>
													<td style="width:110px;max-width:160px;border-bottom: 1px solid #000000; border-left: 1px solid #000000; " align="center" valign=bottom ><br>'.(($hrbuname=="")?"":'<img src="images/approved.png" style="height:25pt" alt="Approved from System">').'</td>
													<td style="width:110px;max-width:160px;border-bottom: 1px solid #000000; border-left: 1px solid #000000; " align="center" valign=bottom ><br>'.(($hrmoname=="")?"":'<img src="images/approved.png" style="height:25pt" alt="Approved from System">').'</td>
													<td style="width:110px;max-width:160px;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="center" valign=bottom ><br>'.(($mdname=="")?"":'<img src="images/approved.png" style="height:25pt" alt="Approved from System">').'</td>
													</tr>
												<tr>
													</tr>
												<tr>
													</tr>
												<tr>
													<td style="border-bottom: 1px solid #000000; border-left: 1px solid #000000;" align="center" height="25"  valign=bottom>'.$Tr->employee->fullname.'<br><small>'.date("d/m/Y",strtotime($Tr->createddate)).'</small></td>
													<td style="border-bottom: 1px solid #000000; border-left: 1px solid #000000;" align="center" valign=bottom>'.$superiorname.'<br><small>'.(($superiorname=="")?"":$datesuperior).'</small></td>
													<td style="border-bottom: 1px solid #000000; border-left: 1px solid #000000;" align="center" valign=bottom>'.$deptheadname.'<br><small>'.(($deptheadname=="")?"":$datedepthead).'</small></td>
													<td style="border-bottom: 1px solid #000000; border-left: 1px solid #000000;" align="center" valign=bottom>'.$hrbuname.'<br><small>'.(($hrbuname=="")?"":$hrbudate).'</small></td>
													<td style="border-bottom: 1px solid #000000; border-left: 1px solid #000000;" align="center" valign=bottom>'.$hrmoname.'<br><small>'.(($hrmoname=="")?"":$hrmodate).'</small></td>
													<td style="border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="center" valign=bottom>'.$mdname.'<br><small>'.(($mdname=="")?"":$mddate).'</small></td>
													</tr>
												<tr>
													<td style="border-bottom: 1px solid #000000; border-left: 1px solid #000000;" height="15" align="center" valign=bottom >Applicant (Pemohon)</td>
													<td style="border-bottom: 1px solid #000000; border-left: 1px solid #000000;" align="center" valign=bottom>Superior</td>
													<td style="border-bottom: 1px solid #000000; border-left: 1px solid #000000;" align="center" valign=bottom>Department Head</td>
													<td style="border-bottom: 1px solid #000000; border-left: 1px solid #000000;" align="center" valign=bottom>HR BU</td>
													<td style="border-bottom: 1px solid #000000; border-left: 1px solid #000000;" align="center" valign=bottom>HR HO</td>
													<td style="border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" align="center" valign=bottom>Deputy MD</td>
													</tr>
												<tr>
													<td style=" border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000" colspan=6 height="15" align="left" valign=bottom ><small>Copy 1 (Asli) - HRD/KTU    Copy 2 (dua) - AVERIS (Melalui scan)</small></td>
													</tr>
											</table>';
											echo $pdfContent;
		
		try {
			$html2pdf = new Html2Pdf('P', 'A4', 'fr');
			$html2pdf->writeHTML( $pdfContent);
			ob_clean();
			$fileName ='doc'.DS.'tr'.DS.'pdf'.DS.'TR'.$Tr->employee->sapid.'_'.date("YmdHis").'.pdf';
			$fileName = str_replace("/","",$fileName);
			$filePath = SITE_PATH.DS.$fileName;
			$html2pdf->output($filePath, 'F');
			$Tr->approveddoc=str_replace("\\","/",$fileName);
			$Tr->save();
			return $fileName;
		} catch (Html2PdfException $e) {
			$html2pdf->clean();
			$formatter = new ExceptionFormatter($e);
			$err = new Errorlog();
			$err->errortype = "TRPDFGenerator";
			$err->errordate = date("Y-m-d h:i:s");
			$err->errormessage = $formatter->getHtmlMessage();
			$err->user = $this->currentUser->username;
			$err->ip = $this->ip;
			$err->save();
			echo $formatter->getHtmlMessage();
		}
		
	}
	function trSchedule(){
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
							$Trschedule = Trschedule::find('all', array('conditions' => array("tr_id=?",$id)));
							foreach ($Trschedule as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Trschedule, JSON_NUMERIC_CHECK);
						}else{
							$Trschedule = new Trschedule();
							echo json_encode($Trschedule);
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							$Trschedule = Trschedule::find('all', array('conditions' => array("tr_id=?",$query['tr_id'])));
							$data=array("jml"=>count($Trschedule));
						}else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Trschedule = Trschedule::create($data);
						$logger = new Datalogger("Trschedule","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Trschedule = Trschedule::find($id);
						$data=$Trschedule->to_array();
						$Trschedule->delete();
						$logger = new Datalogger("Trschedule","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Trschedule);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						
						$Trschedule = Trschedule::find($id);
						$olddata = $Trschedule->to_array();
						foreach($data as $key=>$val){
							$Trschedule->$key=$val;
						}
						$Trschedule->save();
						$logger = new Datalogger("Trschedule","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						echo json_encode($Trschedule);
						
						break;
					default:
						$Trschedule = Trschedule::all();
						foreach ($Trschedule as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Trschedule, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}
	function trTicket(){
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
							$Trticket = Trticket::find('all', array('conditions' => array("tr_id=?",$id)));
							foreach ($Trticket as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Trticket, JSON_NUMERIC_CHECK);
						}else{
							$Trticket = new Trticket();
							echo json_encode($Trticket);
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							$Trticket = Trticket::find('all', array('conditions' => array("tr_id=?",$query['tr_id'])));
							$data=array("jml"=>count($Trticket));
						}else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Trticket = Trticket::create($data);
						$logger = new Datalogger("Trticket","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Trticket = Trticket::find($id);
						$data=$Trticket->to_array();
						$Trticket->delete();
						$logger = new Datalogger("Trticket","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Trticket);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						
						$Trticket = Trticket::find($id);
						$olddata = $Trticket->to_array();
						foreach($data as $key=>$val){
							$Trticket->$key=$val;
						}
						$Trticket->save();
						$logger = new Datalogger("Trticket","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						echo json_encode($Trticket);
						
						break;
					default:
						$Trticket = Trticket::all();
						foreach ($Trticket as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Trticket, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}
	function trHistory(){
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
							$Trhistory = Trhistory::find('all', array('conditions' => array("tr_id=?",$id)));
							foreach ($Trhistory as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Trhistory, JSON_NUMERIC_CHECK);
						}
						break;
					default:
						break;
				}
			}
		}
	}
}