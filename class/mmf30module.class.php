<?php
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

Class Mmf30module extends Application{
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
				case 'apimmf30byemp':
					$this->mmfByEmp();
					break;
				case 'apimmf30':
					$this->Mmf();
					break;
				case 'apimmf30app':
					$this->mmfApproval();
					break;
				case 'apimmf30file':
					$this->mmfAttachment();
					break;
				case 'uploadmmf30file':
					$this->uploadMmf30File();
					break;
				case 'apimmf30pdf':
					$id = $this->get['id'];
					$this->generatePDF($id);
					break;
				case 'apimmf30hist':
					$this->mmfHistory();
					break;
				case 'apimmf30detail':
					$this->mmfDetail();
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
							$Mmf30 = Mmf30::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Mmf30 as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Mmf30, JSON_NUMERIC_CHECK);
						}else{
							echo json_encode(array());
						}
						break;
					case 'find':
						$query=$this->post['query'];					
						if(isset($query['status'])){
							switch ($query['status']){
								case 'waiting':
									$Mmf30 = Mmf30::find('all', array('conditions' => array("employee_id=? and RequestStatus<3 and id<>?",$query['username'],$query['id']),'include' => array('employee')));
									foreach ($Mmf30 as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Mmf30));
									break;
								default:
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
									$Mmf30 = Mmf30::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Mmf30 as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Mmf30));
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
							$Mmf30 = Mmf30::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Mmf30 as &$result) {
								$fullname=$result->employee->fullname;
								$result = $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Mmf30, JSON_NUMERIC_CHECK);
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
						$join = "LEFT JOIN vwmmf30report ON tbl_mmf30.id = vwmmf30report.id";
						$select = "tbl_mmf30.*,vwmmf30report.apprstatuscode";
                        $Mmf30 = Mmf30::find($id, array('joins'=>$join,'select'=>$select,'include' => array('employee'=>array('company','department','designation'))));
                        // $Mmf30 = Mmf30::find($id, array('include' => array('employee'=>array('company','department','designation'))));
						if ($Mmf30){
							$fullname = $Mmf30->employee->fullname;
							$department = $Mmf30->employee->department->departmentname;
							$data=$Mmf30->to_array();
							$data['fullname']=$fullname;
							$data['department']=$department;
							echo json_encode($data, JSON_NUMERIC_CHECK);
						}else{
							$Mmf30 = new Mmf30();
							echo json_encode($Mmf30);
						}
						break;
					case 'find':
						$query=$this->post['query'];					
						if(isset($query['status'])){
							switch ($query['status']){
								case 'chemp':
									break;
								case 'companycode':

									$categorytype = $query['company'];
									$id= $query['mmf30_id'];

									$mmf30 = Mmf30::find($id);
									
									$codenew = Mmf30::find('first',array('select' => "CONCAT('MMF30/','".$categorytype."','/',YEAR(CURDATE()),'/',LPAD(MONTH(CURDATE()), 2, '0'),'/',LPAD(CASE when max(substring(prno,-4,4)) is null then 1 else max(substring(prno,-4,4))+1 end,4,'0')) as prno","conditions"=>array("substring(prno,7,".strlen($categorytype).")=? and not(id = ?) and substring(prno,".(strlen($categorytype)+8).",4)=YEAR(CURDATE())",$categorytype,$query['mmf30_id'])));
									
									$mmf30->prno =$codenew->prno;
									$mmf30->save();
									

									$data=array("prno"=>$codenew->prno);

									break;
								case 'addbuyer':
										// $data = $this->post['data'];
										$buyer = $query['employee_id'];
										$id=$query['mmf30_id'];
										$joins   = "LEFT JOIN tbl_approver ON (tbl_mmf30approval.approver_id = tbl_approver.id) ";					
										$dx = Mmf30approval::find('all',array('joins'=>$joins,'conditions' => array("mmf30_id=? and tbl_approver.approvaltype_id=28 and not(tbl_approver.employee_id=?)",$id,$buyer)));	
										foreach ($dx as $result) {
											//delete same type dept head approver
											$result->delete();
											$logger = new Datalogger("MMf30approval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
										}
										$joins = "LEFT JOIN tbl_approver ON (tbl_mmf30approval.approver_id = tbl_approver.id) ";					
										$Mmf30approval = Mmf30approval::find('all',array('joins'=>$joins,'conditions' => array("mmf30_id=? and tbl_approver.employee_id=?",$id,$buyer)));	
										foreach ($Mmf30approval as &$result) {
											$result	= $result->to_array();
											$result['no']=1;
										}			
										if(count($Mmf30approval)==0){ 
											$Approver = Approver::find('first',array('conditions'=>array("module='MMF30' and employee_id=? and approvaltype_id=28",$buyer)));
											if(count($Approver)>0){
												$Mmf30approval = new Mmf30approval();
												$Mmf30approval->mmf30_id = $id;
												$Mmf30approval->approver_id = $Approver->id;
												$Mmf30approval->save();
											}else{
												$approver = new Approver();
												$approver->module = "MMF30";
												$approver->employee_id=$buyer;
												$approver->sequence=3;
												$approver->approvaltype_id = 28;
												$approver->isfinal = true;
												$approver->save();
												$Mmf30approval = new Mmf30approval();
												$Mmf30approval->mmf30_id = $id;
												$Mmf30approval->approver_id = $approver->id;
												$Mmf30approval->save();
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
									$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.83.18/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.83.18/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
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
							$Mmf30code = Mmf30::find('first',array('select' => "CONCAT('MMF30/','".$Employee->companycode."','/',YEAR(CURDATE()),'/',LPAD(MONTH(CURDATE()), 2, '0'),'/',LPAD(CASE when max(substring(prno,-4,4)) is null then 1 else max(substring(prno,-4,4))+1 end,4,'0')) as prno","conditions"=>array("substring(prno,7,".strlen($Employee->companycode).")=? and substring(prno,".(strlen($Employee->companycode)+8).",4)=YEAR(CURDATE())",$Employee->companycode)));
							$data['prno']=$Mmf30code->prno;
							$Mmf30 = Mmf30::create($data);
							$data=$Mmf30->to_array();
							$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";
							// if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,3)=="022") || ($Employee->department->sapcode=="13000090") || ($Employee->department->sapcode=="13000121") || ($Employee->company->sapcode=="NKF") || ($Employee->company->sapcode=="RND")){
								
							// }else{
								// $Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='MMF30' and tbl_approver.isactive='1' and approvaltype_id=27")));
								// if(count($Approver)>0){
								// 	$Mmf30approval = new Mmf30approval();
								// 	$Mmf30approval->mmf30_id = $Mmf30->id;
								// 	$Mmf30approval->approver_id = $Approver->id;
								// 	$Mmf30approval->save();
								// }
								$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='MMF30' and tbl_approver.isactive='1' and approvaltype_id='27' and FIND_IN_SET(?, CompanyList) > 0 ",$Employee->companycode)));
								if(count($Approver)>0){
									$Mmf30approval = new Mmf30approval();
									$Mmf30approval->mmf30_id = $Mmf30->id;
									$Mmf30approval->approver_id = $Approver->id;
									$Mmf30approval->save();
								}

								// if(($Employee->companycode=="BCL")){
							
								// 	$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='MMF30' and tbl_approver.isactive='1' and approvaltype_id=27 and tbl_employee.companycode='BCL'")));
								// 	if(count($Approver)>0){
								// 		$Mmf30approval = new Mmf30approval();
								// 		$Mmf30approval->mmf30_id = $Mmf30->id;
								// 		$Mmf30approval->approver_id = $Approver->id;
								// 		$Mmf30approval->save();
								// 	}
								// }else{
								// 	$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='MMF30' and tbl_approver.isactive='1' and approvaltype_id=27 and not tbl_employee.companycode='BCL'")));
								// 	if(count($Approver)>0){
								// 		$Mmf30approval = new Mmf30approval();
								// 		$Mmf30approval->mmf30_id = $Mmf30->id;
								// 		$Mmf30approval->approver_id = $Approver->id;
								// 		$Mmf30approval->save();
								// 	}
								// }
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
							$Mmf30history = new Mmf30history();
							$Mmf30history->date = date("Y-m-d h:i:s");
							$Mmf30history->fullname = $Employee->fullname;
							$Mmf30history->approvaltype = "Originator";
							$Mmf30history->mmf30_id = $Mmf30->id;
							$Mmf30history->actiontype = 0;
							$Mmf30history->save();
							
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "CreateMMF30";
							$err->errordate = date("Y-m-d h:i:s");
							$err->errormessage = $e->getMessage();
							$err->user = $this->currentUser->username;
							$err->ip = $this->ip;
							$err->save();
							$data = array("status"=>"error","message"=>$e->getMessage());
						}
						$logger = new Datalogger("MMF30","create",null,json_encode($data));
						$logger->SaveData();
						echo json_encode($data);									
						break;
					case 'delete':
						try {				
							$id = $this->post['id'];
							$Mmf30 = Mmf30::find($id);
							if ($Mmf30->requeststatus==0 || $Mmf30->requeststatus==2){
								$approval = Mmf30approval::find("all",array('conditions' => array("mmf30_id=?",$id)));
								foreach ($approval as $delr){
									$delr->delete();
								}
								$detail = Mmf30detail::find("all",array('conditions' => array("mmf30_id=?",$id)));
								foreach ($detail as $delr){
									$delr->delete();
								}
								$att = Mmf30attachment::find("all",array('conditions' => array("mmf30_id=?",$id)));
								foreach ($att as $delr){
									$delr->delete();
								}
								// $detail = Trticket::find("all",array('conditions' => array("mmf28_id=?",$id)));
								// foreach ($detail as $delr){
								// 	$delr->delete();
								// }
								$hist = Mmf30history::find("all",array('conditions' => array("mmf30_id=?",$id)));
								foreach ($hist as $delr){
									$delr->delete();
								}
								$data = $Mmf30->to_array();
								$Mmf30->delete();
								$logger = new Datalogger("MMF30","delete",json_encode($data),null);
								$logger->SaveData();
								echo json_encode($Mmf30);
							} else {
								$data = array("status"=>"error","message"=>"You can't delete submitted request");
								echo json_encode($data);
							}
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "DeleteMMF30";
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
							$Mmf30 = Mmf30::find($id,array('include'=>array('employee'=>array('company','department','designation','grade'))));
							$olddata = $Mmf30->to_array();
							$depthead = $data['depthead'];
							$buyer = $data['buyer'];
							unset($data['fullname']);
							unset($data['department']);
							unset($data['approvalstatus']);
							unset($data['companycode']);
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							if($superior==$Employee->id){
								$result= array("status"=>"error","message"=>"You cannot select yourself as your Direct superior");
								echo json_encode($result);
							}else{
								foreach($data as $key=>$val){
									$value=(($val===0) || ($val==='0') || ($val==='false'))?false:((($val===1) || ($val==='1') || ($val==='true'))?true:$val);
									$Mmf30->$key=$value;
								}
								$Mmf30->save();
								
								if (isset($data['depthead'])){
									$joins   = "LEFT JOIN tbl_approver ON (tbl_mmf30approval.approver_id = tbl_approver.id) ";					
									$dx = Mmf30approval::find('all',array('joins'=>$joins,'conditions' => array("mmf30_id=? and tbl_approver.approvaltype_id=26 and not(tbl_approver.employee_id=?)",$id,$depthead)));	
									foreach ($dx as $result) {
										//delete same type dept head approver
										$result->delete();
										$logger = new Datalogger("MMf30approval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
									}
									$joins   = "LEFT JOIN tbl_approver ON (tbl_mmf30approval.approver_id = tbl_approver.id) ";					
									$Mmf30approval = Mmf30approval::find('all',array('joins'=>$joins,'conditions' => array("mmf30_id=? and tbl_approver.employee_id=?",$id,$depthead)));	
									foreach ($Mmf30approval as &$result) {
										$result		= $result->to_array();
										$result['no']=1;
									}			
									if(count($Mmf30approval)==0){ 
										$Approver = Approver::find('first',array('conditions'=>array("module='MMF30' and employee_id=? and approvaltype_id=26",$depthead)));
										if(count($Approver)>0){
											$Mmf30approval = new Mmf30approval();
											$Mmf30approval->mmf30_id = $Mmf30->id;
											$Mmf30approval->approver_id = $Approver->id;
											$Mmf30approval->save();
										}else{
											$approver = new Approver();
											$approver->module = "MMF30";
											$approver->employee_id=$depthead;
											$approver->sequence=1;
											$approver->approvaltype_id = 26;
											$approver->isfinal = false;
											$approver->save();
											$Mmf30approval = new Mmf30approval();
											$Mmf30approval->mmf30_id = $Mmf30->id;
											$Mmf30approval->approver_id = $approver->id;
											$Mmf30approval->save();
										}
									}
									
								}

								
								
								if($data['requeststatus']==1){
									$Mmf30approval = Mmf30approval::find('all', array('conditions' => array("mmf30_id=?",$id)));					
									foreach($Mmf30approval as $data){
										$data->approvalstatus=0;
										$data->save();
									}
									$joinx   = "LEFT JOIN tbl_approver ON (tbl_mmf30approval.approver_id = tbl_approver.id) ";					
									$Mmf30approval = Mmf30approval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and mmf30_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
									$username = $Mmf30approval->approver->employee->loginname;
									$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
									$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Mmf30->employee->loginname)));
									$email=$usr->email;
									if($Mmf30->prtype == 1) {
										$prtype = 'Normal PR';
									}else if($Mmf30->prtype == 2) {
										$prtype = 'Urgent PR';
									}else if($Mmf30->prtype == 3) {
										$prtype = 'Minor Purchase';
									}else if($Mmf30->prtype == 4) {
										$prtype = 'Request For Sourcing (RFS) Only';
									}else {
										$prtype = '';
									}

									if($Mmf30->requisitiontype == 1) {
										$requisitiontype = 'Stock Item';
									}else if($Mmf30->requisitiontype == 2) {
										$requisitiontype = 'Services';
									}else if($Mmf30->requisitiontype == 3) {
										$requisitiontype = 'Fixed Asset';
									}else if($Mmf30->requisitiontype == 4) {
										$requisitiontype = 'Raw Material';
									}else if($Mmf30->requisitiontype == 5) {
										$requisitiontype = 'Others';
									}else {
										$requisitiontype = '';
									}
									$Mmf30detail = Mmf30detail::find('all',array('conditions'=>array("mmf30_id=?",$id),'include'=>array('mmf30'=>array('employee'=>array('company','department','designation','grade')))));
									$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span>Dear '.$adb->fullname.',</span></p>
										<p class=MsoNormal><span >new MMF 30 Request is awaiting for your approval:</span></p>
										<p class=MsoNormal><span >&nbsp;</span></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Mmf30->employee->fullname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Mmf30->employee->sapid.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Mmf30->employee->designation->designationname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Mmf30->employee->company->companyname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Mmf30->employee->location->location.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
										</table>
										<p class=MsoNormal><b>PURCHASE REQUISITION (PR) FORM</b></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										
										<tr><th><p class=MsoNormal>PR Type</p></th>
											<th><p class=MsoNormal>Requisition Material</p></th>
											<th><p class=MsoNormal>PR No</p></th>
											<th><p class=MsoNormal>Date</p></th>
											<th><p class=MsoNormal>Required by</p></th>
											<th><p class=MsoNormal>Deliver To</p></th>
											<th><p class=MsoNormal>Cost Code</p></th>
											<th><p class=MsoNormal>Cost Element</p></th>
											<th><p class=MsoNormal>Supplier Name</p></th>
											<th><p class=MsoNormal>Supplier Address</p></th>
											<th><p class=MsoNormal>Supplier Email Fax</p></th>
											<th><p class=MsoNormal>Contract No</p></th>
											<th><p class=MsoNormal>Reason</p></th>
											<th><p class=MsoNormal>Remarks</p></th>
										</tr>
										<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.$prtype.'</p></td>
											<td><p class=MsoNormal> '.$requisitiontype.'</p></td>
											<td><p class=MsoNormal> '.$Mmf30->prno.'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Mmf30->createddate)).'</p></td>
											<td><p class=MsoNormal> '.$Mmf30->employee->fullname.'</p></td>
											<td><p class=MsoNormal> '.$Mmf30->deliverto.'</p></td>
											<td><p class=MsoNormal> '.$Mmf30->costcode.'</p></td>
											<td><p class=MsoNormal> '.$Mmf30->costelement.'</p></td>
											<td><p class=MsoNormal> '.$Mmf30->suppliername.'</p></td>
											<td><p class=MsoNormal> '.$Mmf30->supplieraddress.'</p></td>
											<td><p class=MsoNormal> '.$Mmf30->supplieremailfax.'</p></td>
											<td><p class=MsoNormal> '.$Mmf30->contractno.'</p></td>
											<td><p class=MsoNormal> '.$Mmf30->reason.'</p></td>
											<td><p class=MsoNormal> '.$Mmf30->remarksu.'</p></td>
										</tr>
										';
									$this->mailbody .='</table>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><th><p class=MsoNormal>No</p></th>
											<th><p class=MsoNormal>Material Code</p></th>
											<th><p class=MsoNormal>Description</p></th>
											<th><p class=MsoNormal>Part Number</p></th>
											<th><p class=MsoNormal>Brand/Manufacturer</p></th>
											<th><p class=MsoNormal>Qty</p></th>
											<th><p class=MsoNormal>Unit</p></th>
											<th><p class=MsoNormal>Currency</p></th>
											<th><p class=MsoNormal>Unit Price</p></th>
											<th><p class=MsoNormal>Extended Price</p></th>
											<th><p class=MsoNormal>Remarks</p></th>
										</tr>
										';
									$no=1;
									foreach ($Mmf30detail as $data){
										$this->mailbody .='<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.$no.'</p></td>
											<td><p class=MsoNormal> '.$data->materialcode.'</p></td>
											<td><p class=MsoNormal> '.$data->materialdescr.'</p></td>
											<td><p class=MsoNormal> '.$data->partnumber.'</p></td>
											<td><p class=MsoNormal> '.$data->brandmanufacturer.'</p></td>
											<td><p class=MsoNormal> '.$data->qty.'</p></td>
											<td><p class=MsoNormal> '.$data->unit.'</p></td>
											<td><p class=MsoNormal> '.$data->currency.'</p></td>
											<td><p class=MsoNormal> '.number_format($data->unitprice).'</p></td>
											<td><p class=MsoNormal> '.number_format($data->extendedprice).'</p></td>
											<td><p class=MsoNormal> '.$data->remarks.'</p></td>
											</tr>';
										$no++;
									}
									$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.83.18/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.83.18/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
									$this->mail->addAddress($adb->email, $adb->fullname);
									$this->mail->Subject = "Online Approval System -> new MMF 30 Request Submission";
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

									if (isset($data['buyer'])){
										$joins   = "LEFT JOIN tbl_approver ON (tbl_mmf30approval.approver_id = tbl_approver.id) ";					
										$dx = Mmf30approval::find('all',array('joins'=>$joins,'conditions' => array("mmf30_id=? and tbl_approver.approvaltype_id=28 and not(tbl_approver.employee_id=?)",$id,$buyer)));	
										foreach ($dx as $result) {
											//delete same type dept head approver
											$result->delete();
											$logger = new Datalogger("MMf30approval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
										}
										$joins   = "LEFT JOIN tbl_approver ON (tbl_mmf30approval.approver_id = tbl_approver.id) ";					
										$Mmf30approval = Mmf30approval::find('all',array('joins'=>$joins,'conditions' => array("mmf30_id=? and tbl_approver.employee_id=?",$id,$buyer)));	
										foreach ($Mmf30approval as &$result) {
											$result		= $result->to_array();
											$result['no']=1;
										}			
										if(count($Mmf30approval)==0){ 
											$Approver = Approver::find('first',array('conditions'=>array("module='MMF30' and employee_id=? and approvaltype_id=28",$buyer)));
											if(count($Approver)>0){
												$Mmf30approval = new Mmf30approval();
												$Mmf30approval->mmf30_id = $Mmf30->id;
												$Mmf30approval->approver_id = $Approver->id;
												$Mmf30approval->save();
											}else{
												$approver = new Approver();
												$approver->module = "MMF30";
												$approver->employee_id=$buyer;
												$approver->sequence=3;
												$approver->approvaltype_id = 28;
												$approver->isfinal = true;
												$approver->save();
												$Mmf30approval = new Mmf30approval();
												$Mmf30approval->mmf30_id = $Mmf30->id;
												$Mmf30approval->approver_id = $approver->id;
												$Mmf30approval->save();
											}
										}
										
									}

									$Mmf30history = new Mmf30history();
									$Mmf30history->date = date("Y-m-d h:i:s");
									$Mmf30history->fullname = $Employee->fullname;
									$Mmf30history->mmf30_id = $id;
									$Mmf30history->approvaltype = "Originator";
									$Mmf30history->actiontype = 2;
									$Mmf30history->save();
								}else{
									$Mmf30history = new Mmf30history();
									$Mmf30history->date = date("Y-m-d h:i:s");
									$Mmf30history->fullname = $Employee->fullname;
									$Mmf30history->mmf30_id = $id;
									$Mmf30history->approvaltype = "Originator";
									$Mmf30history->actiontype = 1;
									$Mmf30history->save();
								}

								// if (isset($data['buyer'])){
								// 	$joins   = "LEFT JOIN tbl_approver ON (tbl_mmf30approval.approver_id = tbl_approver.id) ";					
								// 	$dx = Mmf30approval::find('all',array('joins'=>$joins,'conditions' => array("mmf30_id=? and tbl_approver.approvaltype_id=28 and not(tbl_approver.employee_id=?)",$id,$buyer)));	
								// 	foreach ($dx as $result) {
								// 		//delete same type dept head approver
								// 		$result->delete();
								// 		$logger = new Datalogger("MMf30approval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
								// 	}
								// 	$joins   = "LEFT JOIN tbl_approver ON (tbl_mmf30approval.approver_id = tbl_approver.id) ";					
								// 	$Mmf30approval = Mmf30approval::find('all',array('joins'=>$joins,'conditions' => array("mmf30_id=? and tbl_approver.employee_id=?",$id,$buyer)));	
								// 	foreach ($Mmf30approval as &$result) {
								// 		$result		= $result->to_array();
								// 		$result['no']=1;
								// 	}			
								// 	if(count($Mmf30approval)==0){ 
								// 		$Approver = Approver::find('first',array('conditions'=>array("module='MMF30' and employee_id=? and approvaltype_id=28",$buyer)));
								// 		if(count($Approver)>0){
								// 			$Mmf30approval = new Mmf30approval();
								// 			$Mmf30approval->mmf30_id = $Mmf30->id;
								// 			$Mmf30approval->approver_id = $Approver->id;
								// 			$Mmf30approval->save();
								// 		}else{
								// 			$approver = new Approver();
								// 			$approver->module = "MMF30";
								// 			$approver->employee_id=$buyer;
								// 			$approver->sequence=3;
								// 			$approver->approvaltype_id = 28;
								// 			$approver->isfinal = true;
								// 			$approver->save();
								// 			$Mmf30approval = new Mmf30approval();
								// 			$Mmf30approval->mmf30_id = $Mmf30->id;
								// 			$Mmf30approval->approver_id = $approver->id;
								// 			$Mmf30approval->save();
								// 		}
								// 	}
									
								// }

								
								
								$logger = new Datalogger("MMF30","update",json_encode($olddata),json_encode($data));
								$logger->SaveData();
							}
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "UpdateMMF30";
							$err->errordate = date("Y-m-d h:i:s");
							$err->errormessage = $e->getMessage();
							$err->user = $this->currentUser->username;
							$err->ip = $this->ip;
							$err->save();
							$data = array("status"=>"error","message"=>$e->getMessage());
						}
						break;
					default:
						$Mmf30 = Mmf30::all();
						foreach ($Mmf30 as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Mmf30, JSON_NUMERIC_CHECK);
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
							$join   = "LEFT JOIN tbl_approver ON (tbl_mmf30approval.approver_id = tbl_approver.id) ";
							$Mmf30approval = Mmf30approval::find('all', array('joins'=>$join,'conditions' => array("mmf30_id=?",$id),'include' => array('approver'=>array('approvaltype')),"order"=>"tbl_approver.sequence"));
							foreach ($Mmf30approval as &$result) {
								$approvaltype = $result->approver->approvaltype_id;
								$result		= $result->to_array();
								$result['approvaltype']=$approvaltype;
							}
							echo json_encode($Mmf30approval, JSON_NUMERIC_CHECK);
						}else{
							$Mmf30approval = new Mmf30approval();
							echo json_encode($Mmf30approval);
						}
						break;
					case 'find':
						$query=$this->post['query'];		
						if(isset($query['status'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join   = "LEFT JOIN tbl_approver ON (tbl_mmf30approval.approver_id = tbl_approver.id) ";
							$dx = Mmf30approval::find('first', array('joins'=>$join,'conditions' => array("mmf30_id=? and tbl_approver.employee_id = ?",$query['mmf30_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
							$Mmf = Mmf30::find($query['mmf30_id']);
							if($dx->approver->isfinal==1){
								$data=array("jml"=>1);
							}else{
								$join   = "LEFT JOIN tbl_approver ON (tbl_mmf30approval.approver_id = tbl_approver.id) ";
								$Mmfapproval = Mmf30approval::find('all', array('joins'=>$join,'conditions' => array("mmf30_id=? and ApprovalStatus<=1 and not tbl_approver.employee_id=?",$query['mmf30_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
								// $Mmfapproval = Mmf30approval::find('all', array('joins'=>$join,'conditions' => array("mmf30_id=? and ApprovalStatus<=1 and tbl_approver.employee_id=?",$query['mmf30_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
								foreach ($Mmfapproval as &$result) {
									$fullname	= $result->approver->employee->fullname;
									$result		= $result->to_array();
									$result['fullname']=$fullname;
								}
								$data=array("jml"=>count($Mmfapproval));
							}						
						} else if(isset($query['pending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
							$Mmf = Mmf30::find('all', array('conditions' => array("RequestStatus >0"),'include' => array('employee')));
							foreach ($Mmf as $result) {
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_mmf30approval.approver_id = tbl_approver.id) ";					
								$Mmfapproval = Mmf30approval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and mmf30_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								if($Mmfapproval->approver->employee_id==$emp_id){
									$request[]=$result->id;
								}
								$Mmf30approval = Mmf30approval::find('first',array('joins'=>$joinx,'conditions' => array("mmf30_id=? and tbl_approver.employee_id = ? and approvalstatus!=0",$result->id,$emp_id),'include' => array('approver'=>array('employee'))));							
								if(count($Mmf30approval)>0 && ($result->requeststatus==3 || $result->requeststatus==4)){
									$request[]=$result->id;
								}
							}
							$Mmf = Mmf30::find('all', array('conditions' => array("id in (?)",$request),'order'=>"tbl_mmf30.requeststatus",'include' => array('employee')));
							foreach ($Mmf as &$result) {
								$fullname	= $result->employee->fullname;
								$department	= $result->employee->department->departmentname;
								$result		= $result->to_array();
								$result['fullname']=$fullname;
								$result['department']=$department;
							}
							$data=$Mmf;
						} else if(isset($query['mypending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
							$Mmf = Mmf30::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
							$jml=0;
							foreach ($Mmf as $result) {
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_mmf30approval.approver_id = tbl_approver.id) ";					
								$Mmfapproval = Mmf30approval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and mmf30_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								if($Mmfapproval->approver->employee_id==$emp_id){
									$request[]=$result->id;
								}
							}
							$Mmf = Mmf30::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
							foreach ($Mmf as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=array("jml"=>count($Mmf));
						} else if(isset($query['filter'])){
							$join = "LEFT JOIN vwmmf30report v on tbl_mmf30.id=v.id";
							$sel = 'tbl_mmf30.*, v.laststatus,v.personholding, v.apprbuyername, v.apprprocheaddate, v.apprbuyerdate ';
							$Mmf30 = Mmf30::find('all',array('joins'=>$join,'select'=>$sel,'conditions' => array('tbl_mmf30.CreatedDate between ? and ?',$query['startDate'],$query['endDate'] ),'include' => array('employee')));
							foreach ($Mmf30 as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=$Mmf30;
						} else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Mmf30approval = Mmf30approval::create($data);
						$logger = new Datalogger("MMf30approval","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':
						$id = $this->post['id'];
						$Mmf30approval = Mmf30approval::find($id);
						$data=$Mmf30approval->to_array();
						$Mmf30approval->delete();
						$logger = new Datalogger("MMf30approval","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Mmf30approval);
						break;
					case 'update':
						$doid = $this->post['id'];
						$data = $this->post['data'];
						$action = $data['action'];
						$mode= $data['mode'];
						unset($data['id']);
						unset($data['depthead']);
						unset($data['fullname']);
						unset($data['department']);
						unset($data['approveddoc']);
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
						$join   = "LEFT JOIN tbl_approver ON (tbl_mmf30approval.approver_id = tbl_approver.id) ";
						if (isset($data['mode'])){
							$Mmf30approval = Mmf30approval::find('first', array('joins'=>$join,'conditions' => array("mmf30_id=? and tbl_approver.employee_id=?",$doid,$Employee->id),'include' => array('approver'=>array('employee','approvaltype'))));
							unset($data['mode']);
						}else{
							$Mmf30approval = Mmf30approval::find($this->post['id'],array('include' => array('approver'=>array('employee','approvaltype'))));
						}

						if($action == 'form') {
							unset($data['action']);
						
							$mmf30 = Mmf30::find($doid);

							foreach($data as $key=>$val) {
								if(($key !== 'approvalstatus') && ($key !== 'approvaldate') && ($key !== 'remarks')) {
									// if(($key == 'isrepair') || ($key == 'isscrap')) {
										$value=(($val===0) || ($val==='0') || ($val==='false'))?false:((($val===1) || ($val==='1') || ($val==='true'))?true:$val);
									// }
									$mmf30->$key=$value;
								}
							}
							$mmf30->save();
						}

						unset($data['action']);


						// unset($data['materialdispatchno']);
						// unset($data['isrepair']);
						// unset($data['isscrap']);
						// unset($data['estimatecost']);
						// unset($data['pono']);
						// unset($data['materialreturneddate']);
						// unset($data['supplierdodnno']);
						unset($data['buyer']);
						
						
						$olddata = $Mmf30approval->to_array();
						foreach($data as $key=>$val){
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$Mmf30approval->$key=$val;
						}
						$Mmf30approval->save();
						$logger = new Datalogger("Mmf30approval","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						if (isset($mode) && ($mode=='approve')){
							$Mmf30 = Mmf30::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));
							$joinx   = "LEFT JOIN tbl_approver ON (tbl_mmf30approval.approver_id = tbl_approver.id) ";					
							$nMmf30approval = Mmf30approval::find('first',array('joins'=>$joinx,'conditions' => array("mmf30_id=? and ApprovalStatus=0",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
							$username = $nMmf30approval->approver->employee->loginname;
							$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
							// $Mmf30schedule=Trschedule::find('all',array('conditions'=>array("mmf30_id=?",$doid),'include'=>array('mmf'=>array('employee'=>array('company','department','designation','grade','location')))));
							$Mmf30detail = Mmf30detail::find('all',array('conditions'=>array("mmf30_id=?",$doid),'include'=>array('mmf30'=>array('employee'=>array('company','department','designation','grade')))));
							// $Mmf30ticket=Trticket::find('all',array('conditions'=>array("mmf30_id=?",$doid),'include'=>array('mmf'=>array('employee'=>array('company','department','designation','grade','location')))));
							$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Mmf30->employee->loginname)));
							$email=$usr->email;
							$superiorId=$Mmf30->depthead;
							$Superior = Employee::find($superiorId);
							$supAdb = Addressbook::find('first',array('conditions'=>array("username=?",$Superior->loginname)));
							$complete = false;
							$Mmf30history = new Mmf30history();
							$Mmf30history->date = date("Y-m-d h:i:s");
							$Mmf30history->fullname = $Employee->fullname;
							$Mmf30history->approvaltype = $Mmf30approval->approver->approvaltype->approvaltype;
							$Mmf30history->remarks = $data['remarks'];
							$Mmf30history->mmf30_id = $doid;
							
							switch ($data['approvalstatus']){
								case '1':
									$Mmf30->requeststatus = 2;
									$emto=$email;$emname=$Mmf30->employee->fullname;
									$this->mail->Subject = "Online Approval System -> Need Rework";
									$red = 'Your MMF 30 require some rework :
											<br>Remarks from Approver : <font color="red">'.$data['remarks']."</font>";
									$Mmf30history->actiontype = 3;
									break;
								case '2':
									if ($Mmf30approval->approver->isfinal == 1){
										$Mmf30->requeststatus = 3;
										$emto=$email;$emname=$Mmf30->employee->fullname;
										$this->mail->Subject = "Online Approval System -> Approval Completed";
										$red = '<p>Your MMF 30. request has been approved</p>
													<p><b><span lang=EN-US style=\'color:#002060\'>Note : Please <u>forward</u> this electronic approval to your respective PR Creator.<br>All prices in the MMF are valid for 14 days</span></b></p>';
										//delete unnecessary approver
										$Mmf30approval = Mmf30approval::find('all', array('joins'=>$join,'conditions' => array("mmf30_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
										foreach ($Mmf30approval as $data) {
											if($data->approvalstatus==0){
												$logger = new Datalogger("Mmf30approval","delete",json_encode($data->to_array()),"automatic remove unnecessary approver by system");
												$logger->SaveData();
												$data->delete();
											}
										}
										$complete =true;
									}
									else{
										$Mmf30->requeststatus = 1;
										$emto=$adb->email;$emname=$adb->fullname;
										$this->mail->Subject = "Online Approval System -> new Mmf 30 Request";
										$red = 'new MMF 30 Request awaiting for your approval:';
									}
									$Mmf30history->actiontype = 4;							
									break;
								case '3':
									$Mmf30->requeststatus = 4;
									$emto=$email;$emname=$Mmf30->employee->fullname;
									$Mmf30history->actiontype = 5;
									$this->mail->Subject = "Online Approval System -> Request Rejected";
									$red = 'Your MMF 30 Request has been rejected
									<br>Remarks from Approver : <font color="red">'.$data['remarks']."</font>";
									break;
								default:
									break;
							}
							$Mmf30->save();
							$Mmf30history->save();
							echo "email to :".$emto." ->".$emname;
							$this->mail->addAddress($emto, $emname);
							$Mmf30 = Mmf30::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));
							if($Mmf30->prtype == 1) {
								$prtype = 'Normal PR';
							}else if($Mmf30->prtype == 2) {
								$prtype = 'Urgent PR';
							}else if($Mmf30->prtype == 3) {
								$prtype = 'Minor Purchase';
							}else if($Mmf30->prtype == 4) {
								$prtype = 'Request For Sourcing (RFS) Only';
							}else {
								$prtype = '';
							}

							if($Mmf30->requisitiontype == 1) {
								$requisitiontype = 'Stock Item';
							}else if($Mmf30->requisitiontype == 2) {
								$requisitiontype = 'Services';
							}else if($Mmf30->requisitiontype == 3) {
								$requisitiontype = 'Fixed Asset';
							}else if($Mmf30->requisitiontype == 4) {
								$requisitiontype = 'Raw Material';
							}else if($Mmf30->requisitiontype == 5) {
								$requisitiontype = 'Others';
							}else {
								$requisitiontype = '';
							}
							$Mmf30detail = Mmf30detail::find('all',array('conditions'=>array("mmf30_id=?",$doid),'include'=>array('mmf30'=>array('employee'=>array('company','department','designation','grade')))));
							$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span>Dear '.$emname.',</span></p>
								<p class=MsoNormal><span>'.$red.'</span></p>
								<p class=MsoNormal><span>&nbsp;</span></p>
								<table border=1 cellspacing=0 cellpadding=3 width=683>
								<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Mmf30->employee->fullname.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Mmf30->employee->sapid.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Mmf30->employee->designation->designationname.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Mmf30->employee->company->companyname.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Mmf30->employee->location->location.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
								</table>
								<p class=MsoNormal><b>PURCHASE REQUISITION (PR) FORM</b></p>
								<table border=1 cellspacing=0 cellpadding=3 width=683>
								
								<tr><th><p class=MsoNormal>PR Type</p></th>
									<th><p class=MsoNormal>Requisition Material</p></th>
									<th><p class=MsoNormal>PR No</p></th>
									<th><p class=MsoNormal>Date</p></th>
									<th><p class=MsoNormal>Required by</p></th>
									<th><p class=MsoNormal>Deliver To</p></th>
									<th><p class=MsoNormal>Cost Code</p></th>
									<th><p class=MsoNormal>Cost Element</p></th>
									<th><p class=MsoNormal>Supplier Name</p></th>
									<th><p class=MsoNormal>Supplier Address</p></th>
									<th><p class=MsoNormal>Supplier Email Fax</p></th>
									<th><p class=MsoNormal>Contract No</p></th>
									<th><p class=MsoNormal>Reason</p></th>
									<th><p class=MsoNormal>Remarks</p></th>
								</tr>
								<tr style="height:22.5pt">
									<td><p class=MsoNormal> '.$prtype.'</p></td>
									<td><p class=MsoNormal> '.$requisitiontype.'</p></td>
									<td><p class=MsoNormal> '.$Mmf30->prno.'</p></td>
									<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Mmf30->createddate)).'</p></td>
									<td><p class=MsoNormal> '.$Mmf30->employee->fullname.'</p></td>
									<td><p class=MsoNormal> '.$Mmf30->deliverto.'</p></td>
									<td><p class=MsoNormal> '.$Mmf30->costcode.'</p></td>
									<td><p class=MsoNormal> '.$Mmf30->costelement.'</p></td>
									<td><p class=MsoNormal> '.$Mmf30->suppliername.'</p></td>
									<td><p class=MsoNormal> '.$Mmf30->supplieraddress.'</p></td>
									<td><p class=MsoNormal> '.$Mmf30->supplieremailfax.'</p></td>
									<td><p class=MsoNormal> '.$Mmf30->contractno.'</p></td>
									<td><p class=MsoNormal> '.$Mmf30->reason.'</p></td>
									<td><p class=MsoNormal> '.$Mmf30->remarksu.'</p></td>
								</tr>
								';
									$this->mailbody .='</table>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><th><p class=MsoNormal>No</p></th>
											<th><p class=MsoNormal>Material Code</p></th>
											<th><p class=MsoNormal>Description</p></th>
											<th><p class=MsoNormal>Part Number</p></th>
											<th><p class=MsoNormal>Brand/Manufacturer</p></th>
											<th><p class=MsoNormal>Qty</p></th>
											<th><p class=MsoNormal>Unit</p></th>
											<th><p class=MsoNormal>Currency</p></th>
											<th><p class=MsoNormal>Unit Price</p></th>
											<th><p class=MsoNormal>Extended Price</p></th>
											<th><p class=MsoNormal>Remarks</p></th>
										</tr>
										';
									$no=1;
									foreach ($Mmf30detail as $data){
										$val_tprice += $data->extendedprice;
										$this->mailbody .='<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.$no.'</p></td>
											<td><p class=MsoNormal> '.$data->materialcode.'</p></td>
											<td><p class=MsoNormal> '.$data->materialdescr.'</p></td>
											<td><p class=MsoNormal> '.$data->partnumber.'</p></td>
											<td><p class=MsoNormal> '.$data->brandmanufacturer.'</p></td>
											<td><p class=MsoNormal> '.$data->qty.'</p></td>
											<td><p class=MsoNormal> '.$data->unit.'</p></td>
											<td><p class=MsoNormal> '.$data->currency.'</p></td>
											<td><p class=MsoNormal> '.number_format($data->unitprice).'</p></td>
											<td><p class=MsoNormal> '.number_format($data->extendedprice).'</p></td>
											<td><p class=MsoNormal> '.$data->remarks.'</p></td>
											</tr>';
										$no++;
							}
							$this->mailbody .='</table>
							<p><b><span>Total Price : '.$val_tprice.'</span></b></p><br>
				
							<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.83.18/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.83.18/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
							$this->mail->msgHTML($this->mailbody);
							if ($complete){
								$fileName = $this->generatePDF($doid);
								$filePath = SITE_PATH.DS.$fileName;
								$this->mail->addAttachment($filePath);
							}
							if (!$this->mail->send()) {
								$err = new Errorlog();
								$err->errortype = "MMF30 Mail";
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
						echo json_encode($Mmf30approval);
						break;
					default:
						$Mmf30approval = Mmf30approval::all();
						foreach ($Mmf30approval as $result) {
							$result = $result->to_array();
						}
						echo json_encode($Mmf30approval, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}
	function mmfDetail(){
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
							$Mmf30detail = Mmf30detail::find('all', array('conditions' => array("mmf30_id=?",$id)));
							foreach ($Mmf30detail as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Mmf30detail, JSON_NUMERIC_CHECK);
						}else{
							$Mmf30detail = new Mmf30detail();
							echo json_encode($Mmf30detail);
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							$Mmf30detail = Mmf30detail::find('all', array('conditions' => array("mmf30_id=?",$query['mmf30_id'])));
							$data=array("jml"=>count($Mmf30detail));
						}else if(isset($query['filter'])){
							$join = "LEFT JOIN tbl_mmf30 on tbl_mmf30detail.mmf30_id=tbl_mmf30.id";
							$sel = 'tbl_mmf30detail.*, tbl_mmf30.CreatedDate ';
							$Mmf30detail = Mmf30detail::find('all',array('joins'=>$join,'select'=>$sel,'conditions' => array('tbl_mmf30.CreatedDate between ? and ?',$query['startDate'],$query['endDate'] )));
							foreach ($Mmf30detail as &$result) {	
								$result	= $result->to_array();
							}
							$data=$Mmf30detail;
						}else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$exprice = $data['unitprice'] * $data['qty'];
						$data['extendedprice'] = $exprice;
						$Mmf30detail = Mmf30detail::create($data);
						$logger = new Datalogger("Mmf30detail","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Mmf30detail = Mmf30detail::find($id);
						$data=$Mmf30detail->to_array();
						$Mmf30detail->delete();
						$logger = new Datalogger("Mmf30detail","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Mmf30detail);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						
						$Mmf30detail = Mmf30detail::find($id);
						$olddata = $Mmf30detail->to_array();
						foreach($data as $key=>$val){
							$Mmf30detail->$key=$val;
						}
						$exprice = $Mmf30detail->unitprice * $Mmf30detail->qty;
						$Mmf30detail->extendedprice = $exprice;
						$Mmf30detail->save();
						$logger = new Datalogger("Mmf30detail","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						echo json_encode($Mmf30detail);
						
						break;
					default:
						$Mmf30detail = Mmf30detail::all();
						foreach ($Mmf30detail as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Mmf30detail, JSON_NUMERIC_CHECK);
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
							$Mmf30attachment = Mmf30attachment::find('all', array('conditions' => array("mmf30_id=?",$id)));
							foreach ($Mmf30attachment as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Mmf30attachment, JSON_NUMERIC_CHECK);
						}else{
							$Mmf30attachment = new Mmf30attachment();
							echo json_encode($Mmf30attachment);
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							$Mmf30attachment = Mmf30attachment::find('all', array('conditions' => array("mmf30_id=?",$query['mmf30_id'])));
							$data=array("jml"=>count($Mmf30attachment));
						}else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						if($this->currentUser->username=="admin"){
							$Mmf30 = Mmf30::find($data['mmf30_id']);
							$data['employee_id']= $Mmf30->employee_id;
						}else{
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$data['employee_id']=$Employee->id;
						}
						
						unset($data['__KEY__']);
						
						$Mmf30attachment = Mmf30attachment::create($data);
						$logger = new Datalogger("Mmf30attachment","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Mmf30attachment = Mmf30attachment::find($id);
						$data=$Mmf30attachment->to_array();
						$Mmf30attachment->delete();
						$logger = new Datalogger("Mmf30attachment","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Mmf30attachment);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
						$data['employee_id']=$Employee->id;
						$Mmf30attachment = Mmf30attachment::find($id);
						$olddata = $Mmf30attachment->to_array();
						foreach($data as $key=>$val){					
							$val=($val=='No')?false:(($val=='Yes')?true:$val);
							$Mmf30attachment->$key=$val;
						}
						$Mmf30attachment->save();
						$logger = new Datalogger("Mmf30attachment","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						echo json_encode($Mmf30attachment);
						
						break;
					default:
						$Mmf30attachment = Mmf30attachment::all();
						foreach ($Mmf30attachment as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Mmf30attachment, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}
	public function uploadMmf30File(){
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
		$path_to_file = "upload/mmf30/".$id."_".time()."_".$_FILES['myFile']['name'];
		$path_to_file = str_replace("%","_",$path_to_file);
		$path_to_file = str_replace(" ","_",$path_to_file);
		echo $path_to_file;
        move_uploaded_file($_FILES['myFile']['tmp_name'], $path_to_file);
	}
	function generatePDF($id){
		$Mmf30 = Mmf30::find($id);
		// $Mmf30schedule=Trschedule::find('all',array('conditions'=>array("mmf28_id=?",$doid),'include'=>array('mmf'=>array('employee'=>array('company','department','designation','grade','location')))));
		// $Mmf30ticket=Trticket::find('all',array('conditions'=>array("mmf28_id=?",$doid),'include'=>array('mmf'=>array('employee'=>array('company','department','designation','grade','location')))));	
		$Mmf30detail = Mmf30detail::find('all',array('conditions'=>array("mmf30_id=?",$id),'include'=>array('mmf30'=>array('employee'=>array('company','department','designation','grade','location')))));				
		$sumcurrency = $Mmf30detail->currency;
		$superiorId=$Mmf30->depthead;
		$Superior = Employee::find($superiorId);
		$dept = Mmf30::find($id,array('include' => array('employee'=>array('company','department','designation'))));
		$department = $dept->employee->department->departmentname;
		$supAdb = Addressbook::find('first',array('conditions'=>array("username=?",$Superior->loginname)));
		$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Mmf30->employee->loginname)));
		$email=$usr->email;
		$v_date = date("d/m/Y",strtotime($Mmf30->createddate));
		// $v_reqdate = date("d/m/Y",strtotime($Mmf30->requireddate));
		// $v_mdate = date("d/m/Y",strtotime($Mmf30->materialreturneddate));
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

			<h2 class="tg-7btt" style=" text-align:center;"><b>PURCHASE REQUISITION (PR) FORM</b></h2>
		
			<div style="border : 1px solid black; padding: 5px; margin-bottom:2px;">
	  
			<table class="tg" style="width:800px;max-width:800px" cellspacing="0" border="0"  width="100%">

			<tbody>
			<tr>
				<td class="tg-top tg-left" colspan="7"><b class="red tg-value">INSTRUCTIONS:</b> Fill out information below. Underlined fields are required to process transaction.Underlined<br>fields  with  (*/+) are conditional requirements (see notes below) depending on the nature of the requisition</td>
				<td class="tg-top tg-left tg-right" colspan="3"><b class="red tg-value">Requisition Material :</b></td>
			</tr>
			<tr>
				<td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=middle>
				<b>'.(($Mmf30->prtype == 1)?'X':'').'</b></td>
				<td class="p-5" colspan="6"><b>NORMAL PR</b> - PR to go through Normal PR process. To be processed as PO.</td>
				<td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=middle>
				<b>'.(($Mmf30->requisitiontype == 1)?'X':'').'</b></td>
				<td class="tg-right p-5" colspan="3"><b>Stok item</b></td>
			</tr>
			<tr>
				<td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=middle>
				<b>'.(($Mmf30->prtype == 2)?'X':'').'</b></td>
				<td class="p-5" colspan="6"><b>URGENT PR</b> - PR to go through Urgent PR process. To be processed as PO.</td>
				<td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=middle>
				<b>'.(($Mmf30->requisitiontype == 2)?'X':'').'</b></td>
				<td class="tg-right p-5" colspan="3"><b>Services</b></td>
			</tr>
			<tr>
				<td colspan="1" rowspan="2" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=middle>
				<b>'.(($Mmf30->prtype == 3)?'X':'').'</b></td>
				<td class="p-5" colspan="6" rowspan="2" valign="middle"><b>MINOR PURCHASE</b> - PR to go through Minor purchase approval process.<br> For non-production materials below USD500 of part of Finance list of non-PO transactions.</td>
				<td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=middle>
				<b>'.(($Mmf30->requisitiontype == 3)?'X':'').'</b></td>
				<td class="tg-right p-5" colspan="3"><b>Fixed Asset</b></td>
			</tr>
			<tr>
				<td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=middle>
				<b>'.(($Mmf30->requisitiontype == 4)?'X':'').'</b></td>
				<td class="tg-right p-5" colspan="3"><b>Raw Material</b></td>
			</tr>
			<tr>
				<td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=middle>
				<b>'.(($Mmf30->prtype == 4)?'X':'').'</b></td>
				<td class="p-5" colspan="6"><b>REQUEST FOR SOURCING (RFS) ONLY</b> - PR to go through normal approval process.<br> To be processed as RFS only. no PO is required</td>
				<td colspan="1" style="width:15px;max-width:15px;border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000"  align="center" valign=middle>
				<b>'.(($Mmf30->requisitiontype == 5)?'X':'').'</b></td>
				<td class="p-5" colspan="2" valign="middle"><b>Others : <u>'.wordwrap($Mmf30->requisitionother, 40, "<br>").'</u></b></td>
			</tr>
			
		</tbody>
		</table>';

		$pdfContent .= '</div>
		<div style="border : 1px solid black; padding: 5px; margin-bottom:2px;">
	  
			<table class="tg" style="width:900px;max-width:900px" cellspacing="0" border="0"  width="100%">
			<tr>
				<td class="tg-top tg-left tg-bottom"><b class="red tg-value">PR No:</b></td>
				<td class="tg-top tg-right tg-bottom" colspan="2"><u>'.$Mmf30->prno.'</u></td>
				<td class="tg-top tg-left tg-bottom"><b class="red tg-value">Date:</b></td>
				<td class="tg-top tg-right tg-bottom" colspan="2"><u>'.$v_date.'</u></td>
				<td class="tg-top tg-left tg-bottom"><b class="blue tg-value">Cost Code:*</b></td>
				<td class="tg-top tg-right tg-bottom" colspan="2"><u>'.$Mmf30->costcode.'</u></td>
				<td class="tg-top tg-left tg-bottom"><b class="blue tg-value">Cost Element:*</b></td>
				<td class="tg-top tg-right tg-bottom" colspan="3"><u>'.$Mmf30->costelement.'</u></td>
			</tr>
			<tr>
				<td class="tg-top tg-left tg-bottom"><b class="red tg-value">Required by:</b></td>
				<td class="tg-top tg-right tg-bottom" colspan="2"><u>'.$Mmf30->employee->fullname.'</u></td>
				<td class="tg-top tg-left tg-bottom"><b class="red tg-value">Deliver to:</b></td>
				<td class="tg-top tg-right tg-bottom" colspan="2"><u>'.$Mmf30->deliverto.'</u></td>
				<td class="tg-top tg-left tg-bottom"><b class="blue tg-value">Department:*</b></td>
				<td class="tg-top tg-right tg-bottom" colspan="6"><u>'.$department.'</u></td>
			</tr>
			</table>
		</div>
		<div style="border : 1px solid black; padding: 5px; margin-bottom:2px;">	
			<table class="tg" style="width:100%" cellspacing="0" border="0"  width="100%">
			<tr>
				<td colspan="10"><b>Minor Purchase (</b> <i>if required by minor purchase supplier</i> <b>) **</b></td>
			</tr>
			<tr>
				<td class="tg-top tg-left tg-bottom"><b>Supplier Name:</b></td>
				<td class="tg-top tg-right tg-bottom" colspan="2"><u>'.$Mmf30->suppliername.'</u></td>
				<td class="tg-top tg-left tg-bottom"><b>Supplier Address:</b></td>
				<td class="tg-top tg-right tg-bottom" colspan="2"><u>'.$Mmf30->supplieraddress.'</u></td>
				<td class="tg-top tg-left tg-bottom"><b>Email / Fax:</b></td>
				<td class="tg-top tg-right tg-bottom" colspan="2"><u>'.$Mmf30->supplieremailfax.'</u></td>
				<td class="tg-top tg-left tg-bottom"><b>Contract No:**</b></td>
				<td class="tg-top tg-right tg-bottom" colspan="3"><u>'.$Mmf30->contractno.'</u></td>
			</tr>
			</table>
		';

		$pdfContent .= '</div>
		
		<table cellspacing="0" border="0" width="100%" style="width:100%; margin-bottom:2px;">
		<tr>
		<td style="width:15px;max-width:15px; border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle ><b>No.</b></td>
		<td style="width:60px;max-width:60px; border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle ><b>Material<br>Code**</b></td>
		<td style="width:90px;max-width:90px; border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle ><b>Description</b></td>
		<td style="width:55px;max-width:55px; border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle ><b>Part<br>Number</b></td>
		<td style="width:90px;max-width:90px; border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle ><b>Brand/<br>Manufacturer</b></td>
		<td style="width:25px;max-width:25px; border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle><b>Qty</b></td>
		<td style="width:24px;max-width:24px; border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle><b>Unit</b></td>
		<td style="width:60px;max-width:60px; border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle><b>Currency</b></td>
		<td style="width:55px;max-width:55px; border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle ><b>Unit<br>Price</b></td>
		<td style="width:60px;max-width:60px; border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-right: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle><b>Extended<br>Price</b></td>
		<td style="width:60px;max-width:60px; border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-right: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle><b>Remarks<br>Price</b></td>
		</tr>
		';
		$no=1;
		$val_currency = 0;
		$val_qty = 0;
		foreach ($Mmf30detail as $data){	
			$val_currency += $data->currency;
			$val_tprice += $data->extendedprice;
			$pdfContent .='
			<tr>
				<td style="width:15px;max-width:15px; border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle ><b>'.$no.'</b></td>
				<td style="width:60px;max-width:60px; border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle ><b>'.$data->materialcode.'</b></td>
				<td style="width:90px;max-width:90px; border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle ><b>'.wordwrap($data->materialdescr, 40, "<br>").'</b></td>
				<td style="width:55px;max-width:55px; border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle ><b>'.$data->partnumber.'</b></td>
				<td style="width:90px;max-width:90px; border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle ><b>'.$data->brandmanufacturer.'</b></td>
				<td style="width:25px;max-width:25px; border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle><b>'.$data->qty.'</b></td>
				<td style="width:24px;max-width:24px; border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle><b>'.$data->unit.'</b></td>
				<td style="width:60px;max-width:60px; border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle><b>'.$data->currency.'</b></td>
				<td style="width:55px;max-width:55px; border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle ><b>'.number_format($data->unitprice).'</b></td>
				<td style="width:60px;max-width:60px; border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-right: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle><b>'.number_format($data->extendedprice).'</b></td>
				<td style="width:60px;max-width:60px; border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-right: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle><b>'.$data->remarks.'</b></td>
			</tr>
			
				';
				$no++;
		}

		$pdfContent .= '
		<tr>
			<td colspan="5" style="border-top: 1px solid #000000; border-left: 1px solid #000000" >Remarks : </td>
			<td colspan="3" style="border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle></td>
			<td colspan="1" style="border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle>TOTAL PRICE :</td>
			<td colspan="1" style="border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-right: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle><u>'.number_format($val_tprice).'</u></td>

		</tr>
		<tr>
			<td colspan="5" style="border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="left" valign=middle><u>'.wordwrap($Mmf30->remarksu,50,"<br>\n").'</u></td>
			<td colspan="3" style="border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle></td>
			<td colspan="2" style="border-top: 1px solid #000000;border-bottom: 1px solid #000000; border-right: 1px solid #000000; border-left: 1px solid #000000" align="center" valign=middle></td>
		</tr>
		</table>';

		$joinx   = "LEFT JOIN tbl_approver ON (tbl_mmf30approval.approver_id = tbl_approver.id) ";					
		$Mmf30approval = Mmf30approval::find('all',array('joins'=>$joinx,'conditions' => array("mmf30_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee','approvaltype'))));							
		foreach ($Mmf30approval as $data){
			if(($data->approver->approvaltype->id==26) || ($data->approver->employee_id==$Mmf30->depthead)){
				$deptheadname = $data->approver->employee->fullname;
				$datedepthead = date("d/m/Y",strtotime($data->approvaldate));
			}
			if($data->approver->approvaltype->id==27) {
				$procname = $data->approver->employee->fullname;
				$procdate = date("d/m/Y",strtotime($data->approvaldate));
			}
			if($data->approver->approvaltype->id==28) {
				$buyername = $data->approver->employee->fullname;
				$buyerdate = date("d/m/Y",strtotime($data->approvaldate));
				$buyerComment = $data->proccomments;
			}
		}

		$pdfContent .= '
		<table class="tg" style="width:100%" cellspacing="0" border="1"  width="100%">
		<tbody>
		  <tr>
			<td style="width:730px;max-width:730px;" colspan="12"><b>Approval</b></td>
		  </tr>
		  <tr>
			<td class="tg-left tg-top tg-right tg-value red" colspan="5"><b>Requested by:</b></td>
			<td class="tg-left tg-top tg-right tg-value red" colspan="4"><b>Reason for requisition/purchase:</b></td>
			<td class="tg-left tg-top tg-right tg-value blue" colspan="3"><b>Cost Controller Endorsement:*</b></td>
		  </tr>
		  <tr>
			<td class="tg-left tg-bottom red" colspan="2" rowspan="2" valign=top>(End-User)</td>
			<td class="tg-right" colspan="3"><img src="images/approved.png"><br>'.$Mmf30->employee->fullname.' / '.$v_date.'</td>
			<td class="tg-left tg-bottom tg-right" colspan="4" rowspan="2" valign=top>'.wordwrap($Mmf30->reason,50,"<br>\n").'</td>
			<td class="tg-left tg-right" colspan="3"></td>
		  </tr>
		  <tr>
			<td class="tg-bottom tg-right" colspan="3"><b>(Name, Signature &amp; Date)</b></td>
			<td class="tg-left tg-bottom tg-right" colspan="5"><b>(Name, Signature &amp; Date)</b></td>
		  </tr>
		  <tr>
			<td class="tg-left tg-right tg-value red" colspan="5"><b>Approved by:</b></td>
			<td class="tg-left tg-right" colspan="4"><b>Next Higher Level Approval (if required):</b></td>
			<td class="tg-left tg-right tg-value blue" colspan="3"><b>CEO/COO/BU Equivalent:+</b></td>
		  </tr>
		  <tr>
			<td class="tg-left tg-bottom tg-value red" colspan="2" rowspan="2" valign=top>(End-User Mgr)</td>
			<td class="tg-right" colspan="3"><img src="images/approved.png"><br>'.$deptheadname.' / '.$datedepthead.'</td>
			<td class="tg-left tg-bottom" rowspan="2" valign=top>(BU Head)</td>
			<td class="tg-right" colspan="3"></td>
			<td class="tg-right tg-left" colspan="3"></td>
		  </tr>
		  <tr>
			<td class="tg-bottom tg-right" colspan="3"><b>(Name, Signature &amp; Date)</b></td>
			<td class="tg-right tg-bottom" colspan="3"><b>(Name, Signature &amp; Date)</b></td>
			<td class="tg-left tg-bottom tg-right" colspan="3"><b>(Name, Signature &amp; Date)</b></td>
		  </tr>
		</tbody>
		</table>';

		$pdfContent .= '<table class="tg" style="width:100%" cellspacing="0" border="1"  width="100%">
		<tr>
			<td style="width:730px;max-width:730px;" colspan="12"><b>To be completed by Purchasing Personnel</b></td>
		</tr>
		<tr>
			<td class="tg-left tg-top tg-right tg-value red" colspan="2"><b>Received on:</b></td>
			<td class="tg-left tg-top tg-right tg-value red" colspan="4"><b>Procurement Head:</b></td>
			<td class="tg-left tg-top tg-right tg-value red" colspan="4"><b>Buyer:</b></td>
			<td class="tg-left tg-top tg-right" colspan="2"><b>Comments</b></td>
		</tr>
		<tr>
			<td class="tg-left tg-bottom tg-right" colspan="2" rowspan="2" valign=top>'.$procdate.'</td>
			<td class="tg-left tg-right" colspan="4"><img src="images/approved.png"><br>'.$procname.' / '.$procdate.'</td>
			<td class="tg-left tg-right" colspan="4"><img src="images/approved.png"><br>'.$buyername.' / '.$buyerdate.'</td>
			<td class="tg-left tg-bottom tg-right" colspan="2" rowspan="2" valign=top>'.$buyerComment.'</td>
		</tr>
		<tr>
			<td class="tg-left tg-bottom tg-right" colspan="4"><b>(Name, Signature &amp; Date)</b></td>
			<td class="tg-left tg-bottom tg-right" colspan="4"><b>(Name, Signature &amp; Date)</b></td>
		</tr>
		<tr>
    		<td class="tg-left tg-top tg-right" colspan="12"><b>To be completed by Supplier</b> (<i>if required by minor purchase supplier</i>) ++</td>
		</tr>
		<tr>
			<td class="tg-left tg-top tg-right" colspan="2"><b>Received on:</b></td>
			<td class="tg-left tg-top tg-right" colspan="5"><b>Authorized Representative Name:</b></td>
			<td class="tg-left tg-top tg-right" colspan="5"><b>Supplier Contract Number:</b></td>
		</tr>
		<tr>
			<td class="tg-left tg-bottom tg-right" colspan="2"></td>
			<td class="tg-left tg-bottom tg-right" colspan="5"></td>
			<td class="tg-left tg-bottom tg-right" colspan="5"></td>
		</tr>
		<tr>
			<td class="tg-0pky" colspan="12"><b>NOTES:</b>
			<br>*For normal PR (direct, CAPEX) and Minor Purchase only
			<br>**For stock materials only. Non-stock materials do not require Material Code.
			<br>***For Normal PR (direct, CAPEX )and Minor Purchase only. Financial Controller shall act as Cost Controller in business unit where there is no Cost Controller at site.
			<br>+Required for Urgent Purchases only
			<br>++For Minor Purchase Only
			</td>
		</tr>
		<tr>
			<td class="tg-left tg-top tg-bottom" colspan="5">Approved by : Global Material Management</td>
			<td class="tg-left tg-top tg-bottom" colspan="5">Revision 1.0 - 1 Dec 2019</td>
			<td class="tg-left tg-top tg-right tg-bottom" colspan="2">Page 1 of 1</td>
		</tr>
		</table>';

							$pdfContent .= '</body>
							</html>';
											
							echo $pdfContent;
											// echo json_encode($Mmf30->wonumber, JSON_NUMERIC_CHECK);
		
		try {
			$html2pdf = new Html2Pdf('L', 'A4', 'en');
			$html2pdf->pdf->SetDisplayMode('fullpage');
			$html2pdf->writeHTML( $pdfContent);
			ob_clean();
			$fileName ='doc'.DS.'mmf'.DS.'pdf'.DS.'MMF30'.$Mmf30->employee->sapid.'_'.date("YmdHis").'.pdf';
			$fileName = str_replace("/","",$fileName);
			$filePath = SITE_PATH.DS.$fileName;
			$html2pdf->output($filePath, 'F');
			$Mmf30->approveddoc=str_replace("\\","/",$fileName);
			$Mmf30->save();
			return $fileName;
		} catch (Html2PdfException $e) {
			$html2pdf->clean();
			$formatter = new ExceptionFormatter($e);
			$err = new Errorlog();
			$err->errortype = "MMF30PDFGenerator";
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
							$Mmf30history = Mmf30history::find('all', array('conditions' => array("mmf30_id=?",$id)));
							foreach ($Mmf30history as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Mmf30history, JSON_NUMERIC_CHECK);
						}
						break;
					default:
						break;
				}
			}
		}
	}
}