<?php
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

Class Mmfmodule extends Application{
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
				case 'apimmfbyemp':
					$this->mmfByEmp();
					break;
				case 'apimmf':
					$this->Mmf();
					break;
				case 'apimmfapp':
					$this->mmfApproval();
					break;
				case 'apimmffile':
					$this->mmfAttachment();
					break;
				case 'uploadmmffile':
					$this->uploadMmfFile();
					break;
				case 'apimmfpdf':
					$id = $this->get['id'];
					$this->generatePDF($id);
					break;
				case 'apimmfhist':
					$this->mmfHistory();
					break;
				
				default:
					break;
			}
		}
	}
	
	function mmfByEmp(){
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
							$Tr = Mmf::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
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
									$Tr = Mmf::find('all', array('conditions' => array("employee_id=? and RequestStatus<3 and id<>?",$query['username'],$query['id']),'include' => array('employee')));
									foreach ($Tr as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Tr));
									break;
								default:
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
									$Tr = Mmf::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
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
							$Tr = Mmf::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
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
	function Mmf(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'byid':
						$id = $this->post['id'];
						$join = "LEFT JOIN vwmmf28report ON tbl_mmf28.id = vwmmf28report.id";
						$select = "tbl_mmf28.*,vwmmf28report.apprstatuscode";
						$Tr = Mmf::find($id, array('joins'=>$join,'select'=>$select,'include' => array('employee'=>array('company','department','designation'))));
						if ($Tr){
							$fullname = $Tr->employee->fullname;
							$department = $Tr->employee->department->departmentname;
							$data=$Tr->to_array();
							$data['fullname']=$fullname;
							$data['department']=$department;
							echo json_encode($data, JSON_NUMERIC_CHECK);
						}else{
							$Tr = new Mmf();
							echo json_encode($Tr);
						}
						break;
					case 'find':
						$query=$this->post['query'];					
						if(isset($query['status'])){
							switch ($query['status']){
								case 'chemp':
									break;
								case 'addbuyer':
										// $data = $this->post['data'];
										$buyer = $query['employee_id'];
										$id=$query['mmf28_id'];
										$joins   = "LEFT JOIN tbl_approver ON (tbl_mmf28approval.approver_id = tbl_approver.id) ";					
										$dx = Mmfapproval::find('all',array('joins'=>$joins,'conditions' => array("mmf28_id=? and tbl_approver.approvaltype_id=25 and not(tbl_approver.employee_id=?)",$id,$buyer)));	
										foreach ($dx as $result) {
											//delete same type dept head approver
											$result->delete();
											$logger = new Datalogger("MMfapproval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
										}
										$joins   = "LEFT JOIN tbl_approver ON (tbl_mmf28approval.approver_id = tbl_approver.id) ";					
										$Trapproval = Mmfapproval::find('all',array('joins'=>$joins,'conditions' => array("mmf28_id=? and tbl_approver.employee_id=?",$id,$buyer)));	
										foreach ($Trapproval as &$result) {
											$result		= $result->to_array();
											$result['no']=1;
										}			
										if(count($Trapproval)==0){ 
											$Approver = Approver::find('first',array('conditions'=>array("module='MMF' and employee_id=? and approvaltype_id=25",$buyer)));
											if(count($Approver)>0){
												$Trapproval = new Mmfapproval();
												$Trapproval->mmf28_id = $id;
												$Trapproval->approver_id = $Approver->id;
												$Trapproval->save();
											}else{
												$approver = new Approver();
												$approver->module = "MMF";
												$approver->employee_id=$buyer;
												$approver->sequence=3;
												$approver->approvaltype_id = 25;
												$approver->isfinal = true;
												$approver->save();
												$Trapproval = new Mmfapproval();
												$Trapproval->mmf28_id = $id;
												$Trapproval->approver_id = $approver->id;
												$Trapproval->save();
											}
										}
										
								break;
								case "reschedule":
									$id = $query['mmf28_id'];
									$Tr = Mmf::find($id,array('include'=>array('employee'=>array('company','department','designation','grade'))));
									$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Tr->employee->loginname)));
									$email=$usr->email;
									$Trschedule=Trschedule::find('all',array('conditions'=>array("mmf28_id=?",$id),'include'=>array('mmf'=>array('employee'=>array('company','department','designation','grade')))));
									$Trticket=Trticket::find('all',array('conditions'=>array("mmf28_id=?",$id),'include'=>array('mmf'=>array('employee'=>array('company','department','designation','grade')))));
									$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$usr->fullname.',</span></p>
										<p class=MsoNormal><span style="color:#1F497D">Your Travel Request has been rescheduled by HR to match with your actual travel schedule:</span></p>
										<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->fullname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->sapid.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->designation->designationname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->company->companyname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->location->location.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
										</table>';
										// <p class=MsoNormal><b>Travel Schedule ( Jadwal Perjalanan)</b></p>
										// <table border=1 cellspacing=0 cellpadding=3 width=683>
										// <tr><th  rowspan="2"><p class=MsoNormal>No</p></th>
										// 	<th colspan="2"><p class=MsoNormal>Departing <br>( Keberangkatan )</p></th>
										// 	<th><p class=MsoNormal>From ( Dari )</p></th>
										// 	<th colspan="2"><p class=MsoNormal>Arriving ( Ketibaan )</p></th>
										// 	<th><p class=MsoNormal>To ( Ke )</p></th>
										// 	<th><p class=MsoNormal>Region</p></th>
										// 	<th rowspan="2"><p class=MsoNormal>Reason (Alasan)<br><small>(e.g. Meeting,<br>Seminar etc. )</small></p></th>
										// </tr>
										// <tr><th><p class=MsoNormal>Date (tgl) <br> <small>(dd/mm/yyyy)</small></p></th>
										// 	<th><p class=MsoNormal>Time (Waktu)</p></th>
										// 	<th><p class=MsoNormal>City/Country<br>(Kota/Negara)</p></th>
										// 	<th><p class=MsoNormal>Date (tgl) <br> <small>(dd/mm/yyyy)</small></p></th>
										// 	<th><p class=MsoNormal>Time (Waktu)</p></th>
										// 	<th><p class=MsoNormal>City/Country<br>(Kota/Negara)</p></th>
										// 	<th><p class=MsoNormal>R1/R2</p></th>
										// </tr>';
									// $no=1;					
									// foreach ($Trschedule as $data){
									// 	$this->mailbody .='<tr style="height:22.5pt">
									// 		<td><p class=MsoNormal> '.$no.'</p></td>
									// 		<td><p class=MsoNormal> '.date("d/m/Y",strtotime($data->departdate)).'</p></td>
									// 		<td><p class=MsoNormal> '.$data->departtime.'</p></td>
									// 		<td><p class=MsoNormal> '.$data->departfrom.'</p></td>
									// 		<td><p class=MsoNormal> '.date("d/m/Y",strtotime($data->arrivingdate)).'</p></td>
									// 		<td><p class=MsoNormal> '.$data->arrivingtime.'</p></td>
									// 		<td><p class=MsoNormal> '.$data->arrivingto.'</p></td>
									// 		<td><p class=MsoNormal> '.$data->region.'</p></td>
									// 		<td><p class=MsoNormal> '.$data->reason.'</p></td>
									// 	</tr>';
									// 	$no++;
									// }
									// $this->mailbody .='</table>
									// 	<table border=1 cellspacing=0 cellpadding=3 width=683>
									// 	<tr><th><p class=MsoNormal>No</p></th>
									// 		<th><p class=MsoNormal>Ticket For (Untuk)</p></th>
									// 		<th><p class=MsoNormal>Name <br>( Nama )</p></th>
									// 		<th><p class=MsoNormal>Date of Birth <br>( Tgl. Lahir) <br> <small>(dd/mm/yyyy)</small></p></th>
									// 		<th><p class=MsoNormal>Phone Number</p></th>
									// 		<th><p class=MsoNormal>Gender</p></th>
									// 		<th><p class=MsoNormal>Remarks /Confirmation from HR <br> ( Konfirmasi dari HR )</p></th>
									// 	</tr>
									// 	';
									// $no=1;
									// foreach ($Trticket as $data){
									// 	$this->mailbody .='<tr style="height:22.5pt">
									// 		<td><p class=MsoNormal> '.$no.'</p></td>
									// 		<td><p class=MsoNormal> '.$data->ticketfor.'</p></td>
									// 		<td><p class=MsoNormal> '.$data->ticketname.'</p></td>
									// 		<td><p class=MsoNormal> '.date("d/m/Y",strtotime($data->dateofbirth)).'</p></td>
									// 		<td><p class=MsoNormal> '.$data->phonenumber.'</p></td>
									// 		<td><p class=MsoNormal> '.$data->gender.'</p></td>
									// 		<td><p class=MsoNormal> '.$data->hrremarks.'</p></td>
									// 		</tr>';
									// 	$no++;
									// }
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
									$Tr = Mmf::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
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
						// $data['createdby']=$Employee->id;
						$data['RequestStatus']=0;
						try{
							$Rfcnew = Mmf::find('first',array('select' => "CONCAT('MMF28/','".$Employee->companycode."','/',YEAR(CURDATE()),'/',LPAD(MONTH(CURDATE()), 2, '0'),'/',LPAD(CASE when max(substring(mmfnumber,-4,4)) is null then 1 else max(substring(mmfnumber,-4,4))+1 end,4,'0')) as mmfnumber","conditions"=>array("substring(mmfnumber,7,".strlen($Employee->companycode).")=? and substring(mmfnumber,".(strlen($Employee->companycode)+8).",4)=YEAR(CURDATE())",$Employee->companycode)));
							$data['mmfnumber']=$Rfcnew->mmfnumber;
							$Tr = Mmf::create($data);
							$data=$Tr->to_array();
							$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";
							// if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,3)=="022") || ($Employee->department->sapcode=="13000090") || ($Employee->department->sapcode=="13000121") || ($Employee->company->sapcode=="NKF") || ($Employee->company->sapcode=="RND")){
								
							// }else{
								$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='MMF' and tbl_approver.isactive='1' and approvaltype_id=24")));
								if(count($Approver)>0){
									$Trapproval = new Mmfapproval();
									$Trapproval->mmf28_id = $Tr->id;
									$Trapproval->approver_id = $Approver->id;
									$Trapproval->save();
								}
							// }
							// $Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='MMF' and tbl_approver.isactive='1' and approvaltype_id=24")));
							// if(count($Approver)>0){
							// 	$Trapproval = new Mmfapproval();
							// 	$Trapproval->mmf28_id = $Tr->id;
							// 	$Trapproval->approver_id = $Approver->id;
							// 	$Trapproval->save();
							// }
							// $Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='MMF' and tbl_approver.isactive='1' and approvaltype_id=25")));
							// if(count($Approver2)>0){
							// 	$Trapproval = new Mmfapproval();
							// 	$Trapproval->mmf28_id = $Tr->id;
							// 	$Trapproval->approver_id = $Approver2->id;
							// 	$Trapproval->save();
							// }
							$Trhistory = new Mmfhistory();
							$Trhistory->date = date("Y-m-d h:i:s");
							$Trhistory->fullname = $Employee->fullname;
							$Trhistory->approvaltype = "Originator";
							$Trhistory->mmf28_id = $Tr->id;
							$Trhistory->actiontype = 0;
							$Trhistory->save();
							
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "CreateMMF";
							$err->errordate = date("Y-m-d h:i:s");
							$err->errormessage = $e->getMessage();
							$err->user = $this->currentUser->username;
							$err->ip = $this->ip;
							$err->save();
							$data = array("status"=>"error","message"=>$e->getMessage());
						}
						$logger = new Datalogger("MMF","create",null,json_encode($data));
						$logger->SaveData();
						echo json_encode($data);									
						break;
					case 'delete':
						try {				
							$id = $this->post['id'];
							$Tr = Mmf::find($id);
							if ($Tr->requeststatus==0){
								$approval = Mmfapproval::find("all",array('conditions' => array("mmf28_id=?",$id)));
								foreach ($approval as $delr){
									$delr->delete();
								}
								$att = Mmfattachment::find("all",array('conditions' => array("mmf28_id=?",$id)));
								foreach ($att as $delr){
									$delr->delete();
								}
								// $detail = Trschedule::find("all",array('conditions' => array("mmf28_id=?",$id)));
								// foreach ($detail as $delr){
								// 	$delr->delete();
								// }
								// $detail = Trticket::find("all",array('conditions' => array("mmf28_id=?",$id)));
								// foreach ($detail as $delr){
								// 	$delr->delete();
								// }
								$hist = Mmfhistory::find("all",array('conditions' => array("mmf28_id=?",$id)));
								foreach ($hist as $delr){
									$delr->delete();
								}
								$data = $Tr->to_array();
								$Tr->delete();
								$logger = new Datalogger("MMF","delete",json_encode($data),null);
								$logger->SaveData();
								echo json_encode($Tr);
							} else {
								$data = array("status"=>"error","message"=>"You can't delete submitted request");
								echo json_encode($data);
							}
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "DeleteMMF";
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
							$Tr = Mmf::find($id,array('include'=>array('employee'=>array('company','department','designation','grade'))));
							$olddata = $Tr->to_array();
							$depthead = $data['depthead'];
							$buyer = $data['buyer'];
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
									$joins   = "LEFT JOIN tbl_approver ON (tbl_mmf28approval.approver_id = tbl_approver.id) ";					
									$dx = Mmfapproval::find('all',array('joins'=>$joins,'conditions' => array("mmf28_id=? and tbl_approver.approvaltype_id=23 and not(tbl_approver.employee_id=?)",$id,$depthead)));	
									foreach ($dx as $result) {
										//delete same type dept head approver
										$result->delete();
										$logger = new Datalogger("MMfapproval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
									}
									$joins   = "LEFT JOIN tbl_approver ON (tbl_mmf28approval.approver_id = tbl_approver.id) ";					
									$Trapproval = Mmfapproval::find('all',array('joins'=>$joins,'conditions' => array("mmf28_id=? and tbl_approver.employee_id=?",$id,$depthead)));	
									foreach ($Trapproval as &$result) {
										$result		= $result->to_array();
										$result['no']=1;
									}			
									if(count($Trapproval)==0){ 
										$Approver = Approver::find('first',array('conditions'=>array("module='MMF' and employee_id=? and approvaltype_id=23",$depthead)));
										if(count($Approver)>0){
											$Trapproval = new Mmfapproval();
											$Trapproval->mmf28_id = $Tr->id;
											$Trapproval->approver_id = $Approver->id;
											$Trapproval->save();
										}else{
											$approver = new Approver();
											$approver->module = "MMF";
											$approver->employee_id=$depthead;
											$approver->sequence=1;
											$approver->approvaltype_id = 23;
											$approver->isfinal = false;
											$approver->save();
											$Trapproval = new Mmfapproval();
											$Trapproval->mmf28_id = $Tr->id;
											$Trapproval->approver_id = $approver->id;
											$Trapproval->save();
										}
									}
									
								}

								if (isset($data['buyer'])){
									$joins   = "LEFT JOIN tbl_approver ON (tbl_mmf28approval.approver_id = tbl_approver.id) ";					
									$dx = Mmfapproval::find('all',array('joins'=>$joins,'conditions' => array("mmf28_id=? and tbl_approver.approvaltype_id=25 and not(tbl_approver.employee_id=?)",$id,$buyer)));	
									foreach ($dx as $result) {
										//delete same type dept head approver
										$result->delete();
										$logger = new Datalogger("MMfapproval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
									}
									$joins   = "LEFT JOIN tbl_approver ON (tbl_mmf28approval.approver_id = tbl_approver.id) ";					
									$Trapproval = Mmfapproval::find('all',array('joins'=>$joins,'conditions' => array("mmf28_id=? and tbl_approver.employee_id=?",$id,$buyer)));	
									foreach ($Trapproval as &$result) {
										$result		= $result->to_array();
										$result['no']=1;
									}			
									if(count($Trapproval)==0){ 
										$Approver = Approver::find('first',array('conditions'=>array("module='MMF' and employee_id=? and approvaltype_id=25",$buyer)));
										if(count($Approver)>0){
											$Trapproval = new Mmfapproval();
											$Trapproval->mmf28_id = $Tr->id;
											$Trapproval->approver_id = $Approver->id;
											$Trapproval->save();
										}else{
											$approver = new Approver();
											$approver->module = "MMF";
											$approver->employee_id=$buyer;
											$approver->sequence=3;
											$approver->approvaltype_id = 25;
											$approver->isfinal = true;
											$approver->save();
											$Trapproval = new Mmfapproval();
											$Trapproval->mmf28_id = $Tr->id;
											$Trapproval->approver_id = $approver->id;
											$Trapproval->save();
										}
									}
									
								}
								
								if($data['requeststatus']==1){
									$Trapproval = Mmfapproval::find('all', array('conditions' => array("mmf28_id=?",$id)));					
									foreach($Trapproval as $data){
										$data->approvalstatus=0;
										$data->save();
									}
									$joinx   = "LEFT JOIN tbl_approver ON (tbl_mmf28approval.approver_id = tbl_approver.id) ";					
									$Trapproval = Mmfapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and mmf28_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
									$username = $Trapproval->approver->employee->loginname;
									$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
									$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Tr->employee->loginname)));
									$email=$usr->email;
									if($Tr->requiredtype == 1) {
										$required = 'Repair';
									}else if($Tr->requiredtype == 2) {
										$required = 'Servicing';
									}else if($Tr->requiredtype == 3) {
										$required = 'Calibration';
									}else if($Tr->requiredtype == 4) {
										$required = 'Others';
									}else {
										$required = '';
									}
									// $Trschedule=Trschedule::find('all',array('conditions'=>array("mmf28_id=?",$id),'include'=>array('tr'=>array('employee'=>array('company','department','designation','grade')))));
									// $Trticket=Trticket::find('all',array('conditions'=>array("mmf28_id=?",$id),'include'=>array('tr'=>array('employee'=>array('company','department','designation','grade')))));
									$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span>Dear '.$adb->fullname.',</span></p>
										<p class=MsoNormal><span >new MMF 28 Request is awaiting for your approval:</span></p>
										<p class=MsoNormal><span >&nbsp;</span></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->fullname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->sapid.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->designation->designationname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->company->companyname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->location->location.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
										</table>
										<p class=MsoNormal><b>Repairable Form</b></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										
										<tr><th><p class=MsoNormal>Date</small></p></th>
											<th><p class=MsoNormal>Requested By</p></th>
											<th><p class=MsoNormal>Tel No</p></th>
											<th><p class=MsoNormal>WO No</p></th>
											<th><p class=MsoNormal>Charger Code</p></th>
											<th><p class=MsoNormal>Material Dispatch No</p></th>
											<th><p class=MsoNormal>Required By (Date)</p></th>
											<th><p class=MsoNormal>Material Code</p></th>
											<th><p class=MsoNormal>Material Description</p></th>
											<th><p class=MsoNormal>Symptoms (Problem)</p></th>
											<th><p class=MsoNormal>Required</p></th>
											<th><p class=MsoNormal>Instsruction</p></th>
											<th><p class=MsoNormal>Estimation Cost</p></th>
										</tr>
										<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Tr->createddate)).'</p></td>
											<td><p class=MsoNormal> '.$Tr->employee->fullname.'</p></td>
											<td><p class=MsoNormal> '.$Tr->telpno.'</p></td>
											<td><p class=MsoNormal> '.$Tr->mmfnumber.'</p></td>
											<td><p class=MsoNormal> '.$Tr->chargecode.'</p></td>
											<td><p class=MsoNormal> '.$Tr->materialdispatch.'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Tr->requireddate)).'</p></td>
											<td><p class=MsoNormal> '.$Tr->materialcode.'</p></td>
											<td><p class=MsoNormal> '.$Tr->materialdescr.'</p></td>
											<td><p class=MsoNormal> '.$Tr->symptomps.'</p></td>
											<td><p class=MsoNormal> '.$required.'</p></td>
											<td><p class=MsoNormal> '.$Tr->instruction.'</p></td>
											<td><p class=MsoNormal> '.number_format($Tr->estimatecost).'</p></td>
										</tr>
										';
									// $no=1;					
									// foreach ($Trschedule as $data){
									// 	$this->mailbody .='<tr style="height:22.5pt">
									// 		<td><p class=MsoNormal> '.$no.'</p></td>
									// 		<td><p class=MsoNormal> '.date("d/m/Y",strtotime($data->departdate)).'</p></td>
									// 		<td><p class=MsoNormal> '.$data->departtime.'</p></td>
									// 		<td><p class=MsoNormal> '.$data->departfrom.'</p></td>
									// 		<td><p class=MsoNormal> '.date("d/m/Y",strtotime($data->arrivingdate)).'</p></td>
									// 		<td><p class=MsoNormal> '.$data->arrivingtime.'</p></td>
									// 		<td><p class=MsoNormal> '.$data->arrivingto.'</p></td>
									// 		<td><p class=MsoNormal> '.$data->region.'</p></td>
									// 		<td><p class=MsoNormal> '.$data->reason.'</p></td>
									// 	</tr>';
									// 	$no++;
									// }
									// $this->mailbody .='</table>
									// 	<table border=1 cellspacing=0 cellpadding=3 width=683>
									// 	<tr><th><p class=MsoNormal>No</p></th>
									// 		<th><p class=MsoNormal>Ticket For (Untuk)</p></th>
									// 		<th><p class=MsoNormal>Name <br>( Nama )</p></th>
									// 		<th><p class=MsoNormal>Date of Birth <br>( Tgl. Lahir) <br> <small>(dd/mm/yyyy)</small></p></th>
									// 		<th><p class=MsoNormal>Phone Number</p></th>
									// 		<th><p class=MsoNormal>Gender</p></th>
									// 		<th><p class=MsoNormal>Remarks /Confirmation from HR <br> ( Konfirmasi dari HR )</p></th>
									// 	</tr>
									// 	';
									// $no=1;
									// foreach ($Trticket as $data){
									// 	$this->mailbody .='<tr style="height:22.5pt">
									// 		<td><p class=MsoNormal> '.$no.'</p></td>
									// 		<td><p class=MsoNormal> '.$data->ticketfor.'</p></td>
									// 		<td><p class=MsoNormal> '.$data->ticketname.'</p></td>
									// 		<td><p class=MsoNormal> '.date("d/m/Y",strtotime($data->dateofbirth)).'</p></td>
									// 		<td><p class=MsoNormal> '.$data->phonenumber.'</p></td>
									// 		<td><p class=MsoNormal> '.$data->gender.'</p></td>
									// 		<td><p class=MsoNormal> '.$data->hrremarks.'</p></td>
									// 		</tr>';
									// 	$no++;
									// }
									$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
									$this->mail->addAddress($adb->email, $adb->fullname);
									$this->mail->Subject = "Online Approval System -> new MMF 28 Request Submission";
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
									$Trhistory = new Mmfhistory();
									$Trhistory->date = date("Y-m-d h:i:s");
									$Trhistory->fullname = $Employee->fullname;
									$Trhistory->mmf28_id = $id;
									$Trhistory->approvaltype = "Originator";
									$Trhistory->actiontype = 2;
									$Trhistory->save();
								}else{
									if($data['action'] !== 'updatereport') {
										$Trhistory = new Mmfhistory();
										$Trhistory->date = date("Y-m-d h:i:s");
										$Trhistory->fullname = $Employee->fullname;
										$Trhistory->mmf28_id = $id;
										$Trhistory->approvaltype = "Originator";
										$Trhistory->actiontype = 1;
										$Trhistory->save();
									} else {
										$this->generatePDF($id);
									}
									
								}
								$logger = new Datalogger("MMF","update",json_encode($olddata),json_encode($data));
								$logger->SaveData();
							}
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "UpdateMMF";
							$err->errordate = date("Y-m-d h:i:s");
							$err->errormessage = $e->getMessage();
							$err->user = $this->currentUser->username;
							$err->ip = $this->ip;
							$err->save();
							$data = array("status"=>"error","message"=>$e->getMessage());
						}
						break;
					default:
						$Tr = Mmf::all();
						foreach ($Tr as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Tr, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}
	function mmfApproval(){
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
							$join   = "LEFT JOIN tbl_approver ON (tbl_mmf28approval.approver_id = tbl_approver.id) ";
							$Trapproval = Mmfapproval::find('all', array('joins'=>$join,'conditions' => array("mmf28_id=?",$id),'include' => array('approver'=>array('approvaltype')),"order"=>"tbl_approver.sequence"));
							foreach ($Trapproval as &$result) {
								$approvaltype = $result->approver->approvaltype_id;
								$result		= $result->to_array();
								$result['approvaltype']=$approvaltype;
							}
							echo json_encode($Trapproval, JSON_NUMERIC_CHECK);
						}else{
							$Trapproval = new Mmfapproval();
							echo json_encode($Trapproval);
						}
						break;
					case 'find':
						$query=$this->post['query'];		
						if(isset($query['status'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join   = "LEFT JOIN tbl_approver ON (tbl_mmf28approval.approver_id = tbl_approver.id) ";
							$dx = Mmfapproval::find('first', array('joins'=>$join,'conditions' => array("mmf28_id=? and tbl_approver.employee_id = ?",$query['mmf28_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
							$Tr = Mmf::find($query['mmf28_id']);
							if($dx->approver->isfinal==1){
								$data=array("jml"=>1);
							}else{
								$join   = "LEFT JOIN tbl_approver ON (tbl_mmf28approval.approver_id = tbl_approver.id) ";
								$Trapproval = Mmfapproval::find('all', array('joins'=>$join,'conditions' => array("mmf28_id=? and ApprovalStatus<=1 and not tbl_approver.employee_id=?",$query['mmf28_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
								// $Trapproval = Mmfapproval::find('all', array('joins'=>$join,'conditions' => array("mmf28_id=? and ApprovalStatus<=1 and tbl_approver.employee_id=?",$query['mmf28_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
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
							$Tr = Mmf::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
							foreach ($Tr as $result) {
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_mmf28approval.approver_id = tbl_approver.id) ";					
								$Trapproval = Mmfapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and mmf28_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								if($Trapproval->approver->employee_id==$emp_id){
									$request[]=$result->id;
								}
							}
							$Tr = Mmf::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
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
							$Tr = Mmf::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
							$jml=0;
							foreach ($Tr as $result) {
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_mmf28approval.approver_id = tbl_approver.id) ";					
								$Trapproval = Mmfapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and mmf28_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								if($Trapproval->approver->employee_id==$emp_id){
									$request[]=$result->id;
								}
							}
							$Tr = Mmf::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
							foreach ($Tr as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=array("jml"=>count($Tr));
						} else if(isset($query['filter'])){
							$join = "LEFT JOIN vwmmf28report v on tbl_mmf28.id=v.id";
							$sel = 'tbl_mmf28.*, v.laststatus,v.personholding , v.apprbuyername, v.apprprocheaddate';
							$Tr = Mmf::find('all',array('joins'=>$join,'select'=>$sel,'include' => array('employee')));
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
						$Trapproval = Mmfapproval::create($data);
						$logger = new Datalogger("MMfapproval","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':
						$id = $this->post['id'];
						$Trapproval = Mmfapproval::find($id);
						$data=$Trapproval->to_array();
						$Trapproval->delete();
						$logger = new Datalogger("MMfapproval","delete",json_encode($data),null);
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
						$mmf = Mmf::find($doid);
						$join   = "LEFT JOIN tbl_approver ON (tbl_mmf28approval.approver_id = tbl_approver.id) ";
						if (isset($data['mode'])){
							$Trapproval = Mmfapproval::find('first', array('joins'=>$join,'conditions' => array("mmf28_id=? and tbl_approver.employee_id=?",$doid,$Employee->id),'include' => array('approver'=>array('employee','approvaltype'))));
							unset($data['mode']);
						}else{
							$Trapproval = Mmfapproval::find($this->post['id'],array('include' => array('approver'=>array('employee','approvaltype'))));
						}
						foreach($data as $key=>$val) {
							if(($key !== 'approvalstatus') && ($key !== 'approvaldate') && ($key !== 'remarks')) {
								// if(($key == 'isrepair') || ($key == 'isscrap')) {
									$value=(($val===0) || ($val==='0') || ($val==='false'))?false:((($val===1) || ($val==='1') || ($val==='true'))?true:$val);
								// }
								$mmf->$key=$value;
							}
						}
						$mmf->save();

						unset($data['materialdispatchno']);
						unset($data['isrepair']);
						unset($data['isscrap']);
						unset($data['estimatecost']);
						unset($data['pono']);
						unset($data['materialreturneddate']);
						unset($data['supplierdodnno']);
						unset($data['buyer']);
						
						
						$olddata = $Trapproval->to_array();
						foreach($data as $key=>$val){
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$Trapproval->$key=$val;
						}
						$Trapproval->save();
						$logger = new Datalogger("Mmfapproval","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						if (isset($mode) && ($mode=='approve')){
							$Tr = Mmf::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));
							$joinx   = "LEFT JOIN tbl_approver ON (tbl_mmf28approval.approver_id = tbl_approver.id) ";					
							$nTrapproval = Mmfapproval::find('first',array('joins'=>$joinx,'conditions' => array("mmf28_id=? and ApprovalStatus=0",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
							$username = $nTrapproval->approver->employee->loginname;
							$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
							// $Trschedule=Trschedule::find('all',array('conditions'=>array("mmf28_id=?",$doid),'include'=>array('mmf'=>array('employee'=>array('company','department','designation','grade','location')))));
							// $Trticket=Trticket::find('all',array('conditions'=>array("mmf28_id=?",$doid),'include'=>array('mmf'=>array('employee'=>array('company','department','designation','grade','location')))));
							$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Tr->employee->loginname)));
							$email=$usr->email;
							$superiorId=$Tr->depthead;
							$Superior = Employee::find($superiorId);
							$supAdb = Addressbook::find('first',array('conditions'=>array("username=?",$Superior->loginname)));
							$complete = false;
							$Trhistory = new Mmfhistory();
							$Trhistory->date = date("Y-m-d h:i:s");
							$Trhistory->fullname = $Employee->fullname;
							$Trhistory->approvaltype = $Trapproval->approver->approvaltype->approvaltype;
							$Trhistory->remarks = $data['remarks'];
							$Trhistory->mmf28_id = $doid;
							
							switch ($data['approvalstatus']){
								case '1':
									$Tr->requeststatus = 2;
									$emto=$email;$emname=$Tr->employee->fullname;
									$this->mail->Subject = "Online Approval System -> Need Rework";
									$red = 'Your MMF 28 require some rework :
											<br>Remarks from Approver : <font color="red">'.$data['remarks']."</font>";
									$Trhistory->actiontype = 3;
									break;
								case '2':
									if ($Trapproval->approver->isfinal == 1){
										$Tr->requeststatus = 3;
										$emto=$email;$emname=$Tr->employee->fullname;
										$this->mail->Subject = "Online Approval System -> Approval Completed";
										$red = '<p>Your MMF 28. request has been approved</p>
													<p><b><span lang=EN-US style=\'color:#002060\'>Note : Please <u>forward</u> this electronic approval to your respective PR Creator.<br>All prices in the MMF are valid for 14 days</span></b></p>';
										//delete unnecessary approver
										$Trapproval = Mmfapproval::find('all', array('joins'=>$join,'conditions' => array("mmf28_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
										foreach ($Trapproval as $data) {
											if($data->approvalstatus==0){
												$logger = new Datalogger("Mmfapproval","delete",json_encode($data->to_array()),"automatic remove unnecessary approver by system");
												$logger->SaveData();
												$data->delete();
											}
										}
										$complete =true;
									}
									else{
										$Tr->requeststatus = 1;
										$emto=$adb->email;$emname=$adb->fullname;
										$this->mail->Subject = "Online Approval System -> new Mmf 28 Request";
										$red = 'new MMF 28 Request awaiting for your approval:';
									}
									$Trhistory->actiontype = 4;							
									break;
								case '3':
									$Tr->requeststatus = 4;
									$emto=$email;$emname=$Tr->employee->fullname;
									$Trhistory->actiontype = 5;
									$this->mail->Subject = "Online Approval System -> Request Rejected";
									$red = 'Your MMF 28 Request has been rejected';
									break;
								default:
									break;
							}
							$Tr->save();
							$Trhistory->save();
							echo "email to :".$emto." ->".$emname;
							$this->mail->addAddress($emto, $emname);
							$TrJ = Mmf::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));
							if($Tr->requiredtype == 1) {
								$required = 'Repair';
							}else if($Tr->requiredtype == 2) {
								$required = 'Servicing';
							}else if($Tr->requiredtype == 3) {
								$required = 'Calibration';
							}else if($Tr->requiredtype == 4) {
								$required = 'Others';
							}else {
								$required = '';
							}
							$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span >Dear '.$emname.',</span></p>
										<p class=MsoNormal><span >'.$red.'</span></p>
										<p class=MsoNormal><span >&nbsp;</span></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->fullname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->sapid.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->designation->designationname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->company->companyname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Tr->employee->location->location.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
										</table>
										<p class=MsoNormal><b>Repairable Form</b></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										
										<tr><th><p class=MsoNormal>Date</small></p></th>
											<th><p class=MsoNormal>Requested By</p></th>
											<th><p class=MsoNormal>Tel No</p></th>
											<th><p class=MsoNormal>WO No</p></th>
											<th><p class=MsoNormal>Charger Code</p></th>
											<th><p class=MsoNormal>Material Dispatch No</p></th>
											<th><p class=MsoNormal>Required By (Date)</p></th>
											<th><p class=MsoNormal>Material Code</p></th>
											<th><p class=MsoNormal>Material Description</p></th>
											<th><p class=MsoNormal>Symptoms (Problem)</p></th>
											<th><p class=MsoNormal>Required</p></th>
											<th><p class=MsoNormal>Instsruction</p></th>
											<th><p class=MsoNormal>Estimation Cost</p></th>
										</tr>
										<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Tr->createddate)).'</p></td>
											<td><p class=MsoNormal> '.$Tr->employee->fullname.'</p></td>
											<td><p class=MsoNormal> '.$Tr->telpno.'</p></td>
											<td><p class=MsoNormal> '.$Tr->mmfnumber.'</p></td>
											<td><p class=MsoNormal> '.$Tr->chargecode.'</p></td>
											<td><p class=MsoNormal> '.$Tr->materialdispatch.'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Tr->requireddate)).'</p></td>
											<td><p class=MsoNormal> '.$Tr->materialcode.'</p></td>
											<td><p class=MsoNormal> '.$Tr->materialdescr.'</p></td>
											<td><p class=MsoNormal> '.$Tr->symptomps.'</p></td>
											<td><p class=MsoNormal> '.$required.'</p></td>
											<td><p class=MsoNormal> '.$Tr->instruction.'</p></td>
											<td><p class=MsoNormal> '.number_format($Tr->estimatecost).'</p></td>
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
								$err->errortype = "MMF Mail";
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
						$Trapproval = Mmfapproval::all();
						foreach ($Trapproval as $result) {
							$result = $result->to_array();
						}
						echo json_encode($Trapproval, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}
	function mmfAttachment(){
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
							$Mmfattachment = Mmfattachment::find('all', array('conditions' => array("mmf28_id=?",$id)));
							foreach ($Mmfattachment as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Mmfattachment, JSON_NUMERIC_CHECK);
						}else{
							$Mmfattachment = new Mmfattachment();
							echo json_encode($Mmfattachment);
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							$Mmfattachment = Mmfattachment::find('all', array('conditions' => array("mmf28_id=?",$query['mmf28_id'])));
							$data=array("jml"=>count($Mmfattachment));
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
						
						$Mmfattachment = Mmfattachment::create($data);
						$logger = new Datalogger("Mmfattachment","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Mmfattachment = Mmfattachment::find($id);
						$data=$Mmfattachment->to_array();
						$Mmfattachment->delete();
						$logger = new Datalogger("Mmfattachment","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Mmfattachment);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
						$data['employee_id']=$Employee->id;
						$Mmfattachment = Mmfattachment::find($id);
						$olddata = $Mmfattachment->to_array();
						foreach($data as $key=>$val){					
							$val=($val=='No')?false:(($val=='Yes')?true:$val);
							$Mmfattachment->$key=$val;
						}
						$Mmfattachment->save();
						$logger = new Datalogger("Mmfattachment","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						echo json_encode($Mmfattachment);
						
						break;
					default:
						$Mmfattachment = Mmfattachment::all();
						foreach ($Mmfattachment as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Mmfattachment, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}
	public function uploadMmfFile(){
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
		$path_to_file = "upload/mmf28/".$id."_".time()."_".$_FILES['myFile']['name'];
		$path_to_file = str_replace("%","_",$path_to_file);
		$path_to_file = str_replace(" ","_",$path_to_file);
		echo $path_to_file;
        move_uploaded_file($_FILES['myFile']['tmp_name'], $path_to_file);
	}
	function generatePDF($id){
		$Tr = Mmf::find($id);
		// $Trschedule=Trschedule::find('all',array('conditions'=>array("mmf28_id=?",$doid),'include'=>array('mmf'=>array('employee'=>array('company','department','designation','grade','location')))));
		// $Trticket=Trticket::find('all',array('conditions'=>array("mmf28_id=?",$doid),'include'=>array('mmf'=>array('employee'=>array('company','department','designation','grade','location')))));					
		$superiorId=$Tr->depthead;
		$Superior = Employee::find($superiorId);
		$supAdb = Addressbook::find('first',array('conditions'=>array("username=?",$Superior->loginname)));
		$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Tr->employee->loginname)));
		$email=$usr->email;
		$v_date = date("d/m/Y",strtotime($Tr->createddate));
		$v_reqdate = date("d/m/Y",strtotime($Tr->requireddate));
		$v_mdate = date("d/m/Y",strtotime($Tr->materialreturneddate));
		$pdfContent = '
			<!DOCTYPE html>
			<html>
			<head>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<link rel="stylesheet" type="text/css" href="responsive.css"/>
			<style type="text/css">
			table tr td { font-size:10px;font-family: arial; padding:5px;}
			
			.red {color: red;}
			.blue {color: blue;}
			.p-5 {padding: 5px;}
			.tg-bi {font: italic bold 10px/30px Arial;}
			img {height: 25pt;}
			</style>
			</head>
			<body>

			<h2 class="tg-7btt" style=" text-align:center;"><b>REPAIRABLE FORM</b></h2>
		
			<div style="border : 1px solid black; padding: 5px;">
	  
						<table style="width: 595pt;" cellspacing="0" border="0"  width="100%">

							
							<tr>
							<td class="tg-border" colspan="7"><b><i>To be completed by End-User</i></b></td>
							</tr>
							<tr>
							<td class="tg-left">Date :</td>
							<td class="tg-value"><u>'.$v_date.'</u></td>
							<td class="">Requested by :</td>
							<td class="tg-value" colspan="1"><u>'.$usr->fullname.'</u></td>
							<td class="">Tel No :</td>
							<td class="tg-right tg-value" colspan="1"><u>'.$Tr->telpno.'</u></td>
							</tr>
							<tr>
							<td class="tg-left" colspan="1">Work Order No :</td>
							<td class="tg-value" colspan="1"><u>'.$Tr->mmfnumber.'</u></td>
							<td class="">Charge Code :</td>
							<td class="tg-right tg-value" colspan="4"><u>'.$Tr->chargecode.'</u></td>
							</tr>
							<tr>
							<td class="tg-left" colspan="1">Material Dispatch No :</td>
							<td class="tg-value" colspan="1"><u>'.$Tr->materialdispatch.'</u></td>
							<td class="">Required By (Date) :</td>
							<td class="tg-right tg-value" colspan="4"><u>'.$v_reqdate.'</u></td>
							</tr>
							<tr>
							<td class="tg-left" colspan="1">Material Code :</td>
							<td class="tg-right tg-value" colspan="6"><u>'.$Tr->materialcode.'</u></td>
							</tr>
							<tr>
							<td class="tg-left">Material Description :</td>
							<td class="tg-right tg-value" colspan="6"><u>'.$Tr->materialdescr.'</u></td>
							</tr>
							<tr>
							<td class="tg-left">Symptoms (problem) :</td>
							<td class="tg-right tg-value" colspan="6"><u>'.$Tr->symptomps.'</u></td>
							</tr></table>';

							$pdfContent .= '<table style="width: 595pt;" cellspacing="0" border="0"  width="100%">
							  	<tr>
									<td class="tg-left">Required :</td>
									<td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle>
									<b>'.(($Tr->requiredtype == 1)?'X':'').'</b></td>
									<td class="p-5">Repair</td>
									<td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle>
									<b>'.(($Tr->requiredtype == 2)?'X':'').'</b></td>
									<td class="p-5">Servicing</td>
									<td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle>
									<b>'.(($Tr->requiredtype == 3)?'X':'').'</b></td>
									<td class="p-5">Calibration</td>
							  	</tr>
							  	<tr>
									<td class="tg-0lax"></td>
									<td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle>
									<b>'.(($Tr->requiredtype == 4)?'X':'').'</b></td>
									<td class="p-5">Others,Pls Specify <u>'.$Tr->requiredother.'</u></td>
									<td class="tg-0lax"></td>
									<td class="tg-0lax"></td>
									<td class="tg-0lax"></td>
									<td class=""></td>
								</tr>
								<tr>
									<td class="tg-left">Instruction :</td>
									<td class="tg-0lax tg-value" colspan="6"><u>'.$Tr->instruction.'</u></td>
								</tr>
							</table>';

							$pdfContent .= '<table style="width: 595pt;" cellspacing="0" border="0"  width="100%">
							  <tr>
								<td class="tg-0pky">Chemical Content :</td>
								<td style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle  >
								<b>'.(($Tr->ishazardouschemical == 1)?'X':'').'</b></td>
								<td class="tg-0pky">Hazardous Chemical, Chemical Name : <u>'.$Tr->hazchemicalname.'</u></td>
								<td class="tg-0pky"></td>
								<td class="tg-0pky"></td>
								<td class="tg-0pky"></td>
								<td class="tg-0pky"></td>
							  </tr>
							  <tr>
								<td class="tg-0pky"></td>
								<td class="tg-0pky" colspan="6"><b><i>Must ensure material has been decontaminated</i></b></td>
							  </tr>
							  <tr>
								<td class="tg-0pky"></td>
								<td style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle  >
								<b>'.(($Tr->isdecontaminated == 1)?'X':'').'</b></td>
								<td class="tg-0pky">Decontaminated</td>
								<td class="tg-0pky"></td>
								<td class="tg-0pky"></td>
								<td class="tg-0pky"></td>
								<td class="tg-0pky"></td>
							  </tr>
							  <tr>
								<td class="tg-0pky"></td>
								<td style="width:5px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle  >
								<b>'.(($Tr->isnotcontaminated == 1)?'X':'').'</b></td>
								<td class="tg-0pky">Not Contaminated. Reason :  <u>'.$Tr->notcontaminatedreason.'</u></td>
								<td class="tg-0pky"></td>
								<td class="tg-0pky"></td>
								<td class="tg-0pky"></td>
								<td class="tg-0pky"></td>
							  </tr>
							  <tr>
								<td class="tg-0pky"></td>
								<td style="width:5px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle  >
								<b>'.(($Tr->isnonhazardous == 1)?'X':'').'</b></td>
								<td class="tg-0pky">Non-hazardous Chemical. Chemical Name :  <u>'.$Tr->nonhazchemicalname.'</u></td>
								<td class="tg-0pky"></td>
								<td class="tg-0pky"></td>
								<td class="tg-0pky"></td>
								<td class="tg-0pky"></td>
							  </tr>
							  <tr>
								<td class="tg-0pky"></td>
								<td style="width:5px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle  >
								<b>'.(($Tr->isnonchemical == 1)?'X':'').'</b></td>
								<td class="tg-0pky">No Chemical Involved</td>
								<td class="tg-0pky"></td>
								<td class="tg-0pky"></td>
								<td class="tg-0pky"></td>
								<td class="tg-0pky"></td>
							  </tr>
							  </table>';

							$joinx   = "LEFT JOIN tbl_approver ON (tbl_mmf28approval.approver_id = tbl_approver.id) ";					
							$Mmfapproval = Mmfapproval::find('all',array('joins'=>$joinx,'conditions' => array("mmf28_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee','approvaltype'))));							
							foreach ($Mmfapproval as $data){
								if(($data->approver->approvaltype->id==23) || ($data->approver->employee_id==$Tr->depthead)){
									$deptheadname = $data->approver->employee->fullname;
									$datedepthead = date("d/m/Y",strtotime($data->approvaldate));
								}
								if($data->approver->approvaltype->id==24) {
									$procname = $data->approver->employee->fullname;
									$procdate = date("d/m/Y",strtotime($data->approvaldate));
								}
								if($data->approver->approvaltype->id==25) {
									$buyername = $data->approver->employee->fullname;
									$buyerdate = date("d/m/Y",strtotime($data->approvaldate));
								}
							}

							$pdfContent .= '<table style="width: 595pt;" cellspacing="0" border="0"  width="100%">
							<tr>
							<td class="tg-left tg-right" colspan="7"></td>
							</tr>
							<tr>
							<td class="tg-left" colspan="1">Requested by:</td>
							<td colspan="1"></td>
							<td class="tg-right" colspan="4">Approved by:</td>
							</tr>
							<tr>
							<td class="tg-left" colspan="1"><img src="images/approved.png" alt="Approved from System"></td>
							<td colspan="1"></td>
							<td class="tg-right" colspan="4">'.(($deptheadname=="")?"":'<img src="images/approved.png" alt="Approved from System">').'</td>
							</tr>
							<tr>
							<td class="tg-left tg-right" colspan="7"></td>
							</tr>
							<tr>
							<td class="tg-left" colspan="1">('.$usr->fullname.' &amp; '.$v_date.')</td>
							<td colspan="1"></td>
							<td class="tg-right" colspan="4">('.$deptheadname.' &amp; '.$datedepthead.')</td>
							</tr>
							</table></div>';
							

							$pdfContent .= '<div class="opt2" style="border : 1px solid black; padding: 5px;">
							<table style="width: 595pt;" cellspacing="0" border="0"  width="100%">
							<tr>
							<td class="tg-bi tg-border" colspan="9"><b><i>To be completed by Procurement</i></b></td>
							</tr>
							
							<tr>
							<td class="tg-left tg-right" colspan="9">Received by:</td>
							</tr>
							<tr>
							<td class="tg-left" colspan="2"><img src="images/approved.png"></td>
							<td class="tg-right" colspan="7"><img src="images/approved.png"></td>
							</tr>
							<tr>
							<td class="tg-left" colspan="2">Procurement Head</td>
							<td class="tg-right" colspan="7">Buyer</td>
							</tr>
							<tr>
							<td class="tg-left" colspan="2">('.$procname.' &amp; '.$procdate.')</td>
							<td class="tg-right" colspan="7">('.$buyername.' &amp; '.$buyerdate.')</td>
							</tr>
							<tr>
							<td class="tg-left" colspan="1">Material Dispatch No :</td>
							<td class="tg-right" colspan="8"><u>'.$Tr->materialdispatchno.'</u></td>
							</tr>
							</table>';
							$pdfContent .= '<table style="width: 595pt;" cellspacing="0" border="0"  width="100%">
							<tr>
							<td class="tg-left" colspan="1">Repair :</td>
							<td style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle  >
								<b>'.(($Tr->isrepair == 1)?'X':'').'</b></td>
							<td class="p-5">Yes</td>
							<td style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle  >
								<b>'.(($Tr->isrepair == 0)?'X':'').'</b></td>
							<td class="p-5">No</td>
							<td style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=midle  >
								<b>'.(($Tr->isscrap == 1)?'X':'').'</b></td>
							<td class="tg-right p-5" colspan="3">Scrapped</td>
							</tr></table>';
							$pdfContent .= '<table style="width: 595pt;" cellspacing="0" border="0"  width="100%">
							<tr>
							<td class="tg-left" colspan="7">Estimation Cost : <u>'.number_format($Tr->estimatecost).'</u></td>
							</tr>
							<tr>
							<td class="tg-left" colspan="7">PO No : <u>'.$Tr->pono.'</u></td>
							</tr>
							<tr>
							<td class="tg-left" colspan="1">Material Returned Date :</td>
							<td class="" colspan="2"><u>'.$Tr->materialreturneddate.'</u></td>
							<td class="" colspan="1">Supplier DO/DN No :</td>
							<td class="tg-right" colspan="5"><u>'.$Tr->supplierdodnno.'</u></td>
							</tr>
							<tr>
							<td class="tg-left tg-right tg-bottom" colspan="9"></td>
							</tr></table>';

							$pdfContent .= '</div></body>
							</html>';
											
							echo $pdfContent;
											// echo json_encode($Tr->mmfnumber, JSON_NUMERIC_CHECK);
		
		try {
			$html2pdf = new Html2Pdf('P', 'A4', 'en');
			$html2pdf->pdf->SetDisplayMode('fullpage');
			$html2pdf->writeHTML( $pdfContent);
			ob_clean();
			$fileName ='doc'.DS.'mmf'.DS.'pdf'.DS.'MMF28'.$Tr->employee->sapid.'_'.date("YmdHis").'.pdf';
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
			$err->errortype = "MMFPDFGenerator";
			$err->errordate = date("Y-m-d h:i:s");
			$err->errormessage = $formatter->getHtmlMessage();
			$err->user = $this->currentUser->username;
			// $err->user = 'userR';
			$err->ip = $this->ip;
			$err->save();
			echo $formatter->getHtmlMessage();
		}
		
	}
	function mmfHistory(){
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
							$Trhistory = Mmfhistory::find('all', array('conditions' => array("mmf28_id=?",$id)));
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