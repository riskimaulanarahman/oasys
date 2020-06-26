<?php
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

Class SpklModule extends Application{
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
				case 'apispklbyemp':
					$this->spklByEmp();
					break;
				case 'apispkl':
					$this->Spkl();
					break;
				case 'apispkldetail':
					$this->spklDetail();
					break;
				case 'apispklapp':
					$this->spklApproval();
					break;
				case 'apispklhist':
					$this->spklHistory();
					break;
				case 'apispklpdf':				
					 $this->generatePDF();
					break;
				default:
					break;
			}
		}
	}
	function generatePDF(){
	}
	function spklHistory(){
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
							$Spklhistory = Spklhistory::find('all', array('conditions' => array("spkl_id=?",$id),'include' => array('spkl')));
							foreach ($Spklhistory as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Spklhistory, JSON_NUMERIC_CHECK);
						}
						break;
					default:
						break;
				}
			}
		}
	}

	function spklApproval(){
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
							$join   = "LEFT JOIN tbl_approver ON (tbl_spklapproval.approver_id = tbl_approver.id) ";
							$Spklapproval = Spklapproval::find('all', array('joins'=>$join,'conditions' => array("spkl_id=?",$id),'include' => array('approver'=>array('approvaltype')),"order"=>"tbl_approver.sequence"));
							foreach ($Spklapproval as &$result) {
								$approvaltype = $result->approver->approvaltype_id;
								$result		= $result->to_array();
								$result['approvaltype']=$approvaltype;
							}
							echo json_encode($Spklapproval, JSON_NUMERIC_CHECK);
						}else{
							$Spklapproval = new Spklapproval();
							echo json_encode($Spklapproval);
						}
						break;
					case 'find':
						$query=$this->post['query'];		
						if(isset($query['status'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join   = "LEFT JOIN tbl_approver ON (tbl_spklapproval.approver_id = tbl_approver.id) ";
							$dx = Spklapproval::find('first', array('joins'=>$join,'conditions' => array("spkl_id=? and tbl_approver.employee_id = ?",$query['spkl_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
							$Spkl = Spkl::find($query['spkl_id']);
							if($dx->approver->isfinal==1){
								$data=array("jml"=>1);
							}else{
								$join   = "LEFT JOIN tbl_approver ON (tbl_spklapproval.approver_id = tbl_approver.id) ";
								$Spklapproval = Spklapproval::find('all', array('joins'=>$join,'conditions' => array("spkl_id=? and ApprovalStatus<=1 and not tbl_approver.employee_id=?",$query['spkl_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
								foreach ($Spklapproval as &$result) {
									$fullname	= $result->approver->employee->fullname;		
									$result		= $result->to_array();
									$result['fullname']=$fullname;
								}
								$data=array("jml"=>count($Spklapproval));
							}						
						} else if(isset($query['pending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
							$Spkl = Spkl::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
							foreach ($Spkl as $result) {
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_spklapproval.approver_id = tbl_approver.id) ";					
								$Spklapproval = Spklapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and spkl_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								if($Spklapproval->approver->employee_id==$emp_id){
									$request[]=$result->id;
								}
							}
							$Spkl = Spkl::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
							foreach ($Spkl as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=$Spkl;
						} else if(isset($query['mypending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
							$Spkl = Spkl::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
							$jml=0;
							foreach ($Spkl as $result) {
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_spklapproval.approver_id = tbl_approver.id) ";					
								$Spklapproval = Spklapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and spkl_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								if($Spklapproval->approver->employee_id==$emp_id){
									$request[]=$result->id;
								}
							}
							$Spkl = Spkl::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
							foreach ($Spkl as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=array("jml"=>count($Spkl));
						} else if(isset($query['filter'])){
							$join = "LEFT JOIN vwspklreport v on tbl_spkl.id=v.id";
							$sel = 'tbl_spkl.*, v.laststatus,v.personholding ';
							$Spkl = Spkl::find('all',array('joins'=>$join,'select'=>$sel,'include' => array('employee')));
							foreach ($Spkl as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=$Spkl;
						} else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Spklapproval = Spklapproval::create($data);
						$logger = new Datalogger("Spklapproval","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':
						$id = $this->post['id'];
						$Spklapproval = Spklapproval::find($id);
						$data=$Spklapproval->to_array();
						$Spklapproval->delete();
						$logger = new Datalogger("Spklapproval","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Spklapproval);
						break;
					case 'update':
						$doid = $this->post['id'];
						$data = $this->post['data'];
						$mode= $data['mode'];
						unset($data['id']);
						unset($data['depthead']);
						unset($data['fullname']);
						unset($data['department']);
						unset($data['datework']);
						unset($data['approveddoc']);
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
						
						$join   = "LEFT JOIN tbl_approver ON (tbl_spklapproval.approver_id = tbl_approver.id) ";
						if (isset($data['mode'])){
							$Spklapproval = Spklapproval::find('first', array('joins'=>$join,'conditions' => array("spkl_id=? and tbl_approver.employee_id=?",$doid,$Employee->id),'include' => array('approver'=>array('employee','approvaltype'))));
							unset($data['mode']);
						}else{
							$Spklapproval = Spklapproval::find($this->post['id'],array('include' => array('approver'=>array('employee','approvaltype'))));
						}
						$olddata = $Spklapproval->to_array();
						foreach($data as $key=>$val){
							$val=($val=='false')?false:(($val=='true')?true:$val);
							$Spklapproval->$key=$val;
						}
						$Spklapproval->save();
						$logger = new Datalogger("Spklapproval","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						if (isset($mode) && ($mode=='approve')){
							$Spkl = Spkl::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));
							$joinx   = "LEFT JOIN tbl_approver ON (tbl_spklapproval.approver_id = tbl_approver.id) ";					
							$nSpklapproval = Spklapproval::find('first',array('joins'=>$joinx,'conditions' => array("spkl_id=? and ApprovalStatus=0",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
							$username = $nSpklapproval->approver->employee->loginname;
							$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
							$Spkldetail=Spkldetail::find('all',array('conditions'=>array("spkl_id=?",$doid),'include'=>array('spkl'=>array('employee'=>array('company','department','designation','grade','location')))));
							$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Spkl->employee->loginname)));
							$email=$usr->email;
							
							$complete = false;
							$Spklhistory = new Spklhistory();
							$Spklhistory->date = date("Y-m-d h:i:s");
							$Spklhistory->fullname = $Employee->fullname;
							$Spklhistory->approvaltype = $Spklapproval->approver->approvaltype->approvaltype;
							$Spklhistory->remarks = $data['remarks'];
							$Spklhistory->spkl_id = $doid;
							
							switch ($data['approvalstatus']){
								case '1':
									$Spkl->requeststatus = 2;
									$emto=$email;$emname=$Spkl->employee->fullname;
									$this->mail->Subject = "Online Approval System -> Need Rework";
									$red = 'Your SPKL/ Overtime request require some rework :';
									$Spklhistory->actiontype = 3;
									break;
								case '2':
									if ($Spklapproval->approver->isfinal == 1){
										$Spkl->requeststatus = 3;
										$emto=$email;$emname=$Spkl->employee->fullname;
										$this->mail->Subject = "Online Approval System -> Approval Completed";
										$red = 'Your SPKL/Overtime request has been approved';
										//delete unnecessary approver
										$Spklapproval = Spklapproval::find('all', array('joins'=>$join,'conditions' => array("spkl_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
										foreach ($Spklapproval as $data) {
											if($data->approvalstatus==0){
												$logger = new Datalogger("Spklapproval","delete",json_encode($data->to_array()),"automatic remove unnecessary approver by system");
												$logger->SaveData();
												$data->delete();
											}
										}
										$complete =true;
									}
									// else if(($Spkl->ratetype=='SK') && ($Spklapproval->approver->approvaltype_id=='11' ) ){
										// $Spkl->requeststatus = 3;
										// $emto=$email;$emname=$Spkl->employee->fullname;
										// $this->mail->Subject = "Online Approval System -> Approval Completed";
										// $red = 'Your SPKL/Overtime request has been approved';
										// $Spklapproval = Spklapproval::find('all', array('joins'=>$join,'conditions' => array("spkl_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
										// foreach ($Spklapproval as $data) {
											// if($data->approvalstatus==0){
												// $logger = new Datalogger("Spklapproval","delete",json_encode($data->to_array()),"automatic remove unnecessary approver by system");
												// $logger->SaveData();
												// $data->delete();
											// }
										// }
										// $complete =true;
									// }
									else{
										$Spkl->requeststatus = 1;
										$emto=$adb->email;$emname=$adb->fullname;
										$this->mail->Subject = "Online Approval System -> New SPKL/Overtime Submission";
										$red = 'New SPKL/Overtime request awaiting for your approval:';
									}
									$Spklhistory->actiontype = 4;							
									break;
								case '3':
									$Spkl->requeststatus = 4;
									$emto=$email;$emname=$Spkl->employee->fullname;
									$Spklhistory->actiontype = 5;
									$this->mail->Subject = "Online Approval System -> Request Rejected";
									$red = 'Your SPKL/Overtime request has been rejected';
									break;
								default:
									break;
							}
							//print_r($Spkl);
							$Spkl->save();
							$Spklhistory->save();
							echo "email to :".$emto." ->".$emname;
							$this->mail->addAddress($emto, $emname);
							$spkltype=array("New","Addendum","Project Capex");
							$SpklJ = Spkl::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));
							$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$emname.',</span></p>
												<p class=MsoNormal><span style="color:#1F497D">'.$red.'</span></p>
												<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
												<table border=1 cellspacing=0 cellpadding=3 width=683>
													<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Spkl->employee->fullname.'</b></p></td></tr>
													<tr><td><p class=MsoNormal>Creation Date</p></td><td>:</td><td><p class=MsoNormal><b>'.date("d/m/Y",strtotime($Spkl->createddate)).'</b></p></td></tr>
													<tr><td><p class=MsoNormal>Date Work</p></td><td>:</td><td><p class=MsoNormal><b>'.date("d/m/Y",strtotime($Spkl->datework)).'</b></p></td></tr>';
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
							foreach ($Spkldetail as $data){
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
								$pdfContent ="<p><h5 style='width:100%;text-align:center'><b><u>SURAT PERINTAH KERJA LEMBUR (SPKL)</u></b>";
								$pdfContent .="<br><i>Overtime Instruction & Approval Form</i></h5></p>";
								$hari = array ( 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu');						
								$pdfContent .= "<br><br>Dengan ini diperintahkan agar melaksanakan kerja lembur kepada/ <i>Herewith instructed to work overtime to</i>;
												<table border=0 cellspacing=0 cellpadding=3>
												<tr><td>Business Unit </td><td>:</td><td>".$Spkl->employee->companycode."</td><td width='50'></td><td>Hari</td><td>:</td><td>".$hari[(date("N",strtotime($Spkl->datework))-1)]."</td></tr>
												<tr><td>Section </td><td>:</td><td></td><td width='50'></td><td>Tanggal</td><td>:</td><td>".date("d/m/Y",strtotime($Spkl->datework))."</td></tr>
												<tr><td>Department  </td><td>:</td><td>".$Spkl->employee->department->departmentname."</td><td width='50'></td><td></td><td></td><td></td></tr>
												</table><br>";
								$pdfContent .='	<table border=1 cellspacing=0 cellpadding=1 width=650><tr>
												<th rowspan="2" align="center"><p class=MsoNormal>No</p></th>
												<th rowspan="2" align="center"><p class=MsoNormal>Nama</p></th>
												<th rowspan="2" align="center"><p class=MsoNormal>No. SAP</p></th>
												<th rowspan="2" align="center"><p class=MsoNormal>Posisi</p></th>
												<th colspan="2" align="center"><p class=MsoNormal>Perkiraan Lama Bekerja</p></th>
												
												<th rowspan="2" align="center"><p class=MsoNormal>Pekerjaan yang harus diselesaikan</p></th>
												</tr>
												<tr><th align="center"><p class=MsoNormal>Jam Normal <br><small>( Jam )</small></p></th>
												<th align="center"><p class=MsoNormal>Jam Lembur <br><small>( Jam )</small></p></th></tr>
												';
								$no=1;
								foreach ($Spkldetail as $data){
									$pdfContent .='<tr style="height:19pt">
												<td><p class=MsoNormal> '.$no.'</p></td>
												<td><p class=MsoNormal> '.$data->employee->fullname.'</p></td>
												<td><p class=MsoNormal> '.$data->employee->sapid.'</p></td>
												<td><p class=MsoNormal> '.$data->employee->designation->designationname.'</p></td>
												<td><p class=MsoNormal> '.$data->estimatenormalhours.'</p></td>
												<td><p class=MsoNormal> '.$data->estimateovertimehours.'</p></td>
												<td><p class=MsoNormal> '.wordwrap($data->target, 60, "<br>").'</p></td>
									</tr>';
									$no++;
								}
								$pdfContent .= "</table><br>
												<b>Catatan : </b><br>
												 - SPKL wajib dikeluarkan oleh Askep/level lebih tinggi sebelum pekerjaan lembur dijalankan.<br>
												 - Satu formulir SPKL mewakili rencana kerja lembur di 1 (satu) hari/tanggal. <br>
												 - SPKL wajib dilampirkan pada daftar hadir (timesheet) dan diserahkan kepada departemen SDM dalam waktu 1X24 jam, <br>atau pada hari kerja berikutnya.";
								
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_spklapproval.approver_id = tbl_approver.id) ";					
								$Spklapproval = Spklapproval::find('all',array('joins'=>$joinx,'conditions' => array("spkl_id=?",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee','approvaltype'))));							
								$pdfContent .= "<br><br><table border=0 cellspacing=4 cellpadding=3>
												<tr><td align='center'>Diperintahkan Oleh,</td><td width='50'></td><td align='center'>Disetujui Oleh,</td><td width='50'></td><td align='center'>Diperiksa Oleh,</td></tr>
												<tr><td align='center'>Askep</td><td width='50'></td><td align='center'>Dept. Head / Sector Manager</td><td width='50'></td><td align='center'>Askep SDM/CS</td></tr>
												";
								foreach ($Spklapproval as $data){
									if(($data->approver->approvaltype->id==21) || ($data->approver->employee_id==$Spkl->depthead)){
										$deptheadname = $data->approver->employee->fullname;
										$datedepthead = date("d/m/Y",strtotime($data->approvaldate));
									}
									if($data->approver->approvaltype->id==22) {
										$hrname = $data->approver->employee->fullname;
										$hrdate = date("d/m/Y",strtotime($data->approvaldate));
									}
								}
								$pdfContent .= '<tr><td align="center" style="padding:5pt 5.4pt 0in 5.4pt;"><img src="images/approved.png" style="height:25pt" alt="Approved from System"></td>
											<td width="50"></td>
											<td align="center" style="padding:5pt 5.4pt 0in 5.4pt;">'.(($deptheadname=="")?"":'<img src="images/approved.png" style="height:25pt" alt="Approved from System">').'</td>
											<td width="50"></td>
											<td align="center" style="padding:5pt 5.4pt 0in 5.4pt;">'.(($hrname=="")?"":'<img src="images/approved.png" style="height:25pt" alt="Approved from System">').'</td></tr>';
								$pdfContent .= '<tr><td align="center" style="padding:5pt 5.4pt 0in 5.4pt;">'.$Spkl->employee->fullname.'<br><small>'.date("d/m/Y",strtotime($Spkl->createddate)).'</small></td>
											<td width="50"></td>
											<td align="center" style="padding:5pt 5.4pt 0in 5.4pt;">'.$deptheadname.'<br><small>'.(($deptheadname=="")?"":date("d/m/Y",strtotime($datedepthead))).'</small></td>
											<td width="50"></td>
											<td align="center" style="padding:5pt 5.4pt 0in 5.4pt;">'.$hrname.'<br><small>'.(($hrname=="")?"":date("d/m/Y",strtotime($hrdate))).'</small></td></tr>';
								$pdfContent .= "</table>";
								
								try {
									$html2pdf = new Html2Pdf('P', 'A4', 'fr');
									$html2pdf->writeHTML($pdfContent);
									ob_clean();
									$fileName ='doc'.DS.'spkl'.DS.'pdf'.DS.''.$Spkl->employee->sapid.'_'.date("YmdHis").'.pdf';
									$fileName = str_replace("/","",$fileName);
									$filePath = SITE_PATH.DS.$fileName;
									$html2pdf->output($filePath, 'F');
									$this->mail->addAttachment($filePath);
									$Spkl->approveddoc=str_replace("\\","/",$fileName);
									$Spkl->save();
								} catch (Html2PdfException $e) {
									$html2pdf->clean();
									$formatter = new ExceptionFormatter($e);
									$err = new Errorlog();
									$err->errortype = "SPKLPDFGenerator";
									$err->errordate = date("Y-m-d h:i:s");
									$err->errormessage = $formatter->getHtmlMessage();
									$err->user = $this->currentUser->username;
									$err->ip = $this->ip;
									$err->save();
									echo $formatter->getHtmlMessage();
								}
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
						echo json_encode($Spklapproval);
						break;
					default:
						$Spklapproval = Spklapproval::all();
						foreach ($Spklapproval as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Spklapproval, JSON_NUMERIC_CHECK);
						break;
				}
			}
			// else{
				// $result= array("status"=>"error","message"=>"Authentication error or expired, please refresh and re login application");
				// echo json_encode($result);
			// }
		}
	}
	function spklDetail(){
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
							$joinx="left join tbl_employee on tbl_spkldetail.employee_id=tbl_employee.id left join tbl_designation on tbl_employee.designation_id=tbl_designation.id";
							$sel="tbl_spkldetail.*,tbl_employee.fullname,tbl_employee.sapid,tbl_designation.designationname as position";
							$Spkldetail = Spkldetail::find('all', array("joins"=>$joinx,"select"=>$sel,'conditions' => array("spkl_id=?",$id)));
							foreach ($Spkldetail as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Spkldetail, JSON_NUMERIC_CHECK);
						}else{
							$Spkldetail = new Spkldetail();
							echo json_encode($Spkldetail);
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							$Spkldetail = Spkldetail::find('all', array('conditions' => array("spkl_id=?",$query['spkl_id'])));
							$data=array("jml"=>count($Spkldetail));
						}else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Spkldetail = Spkldetail::create($data);
						$logger = new Datalogger("Spkldetail","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Spkldetail = Spkldetail::find($id);
						$data=$Spkldetail->to_array();
						$Spkldetail->delete();
						$logger = new Datalogger("Spkldetail","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Spkldetail);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						
						$Spkldetail = Spkldetail::find($id);
						$olddata = $Spkldetail->to_array();
						foreach($data as $key=>$val){					
							$val=($val=='No')?false:(($val=='Yes')?true:$val);
							$Spkldetail->$key=$val;
						}
						$Spkldetail->save();
						$logger = new Datalogger("Spkldetail","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						echo json_encode($Spkldetail);
						
						break;
					default:
						$Spkldetail = Spkldetail::all();
						foreach ($Spkldetail as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Spkldetail, JSON_NUMERIC_CHECK);
						break;
				}
			}
			// else{
				// $result= array("status"=>"error","message"=>"Authentication error or expired, please refresh and re login application");
				// echo json_encode($result);
			// }
		}
	}
	
	function Spkl(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'byid':
						$id = $this->post['id'];
						$Spkl = Spkl::find($id, array('include' => array('employee'=>array('company','department','designation'))));
						if ($Spkl){
							$fullname = $Spkl->employee->fullname;
							$department = $Spkl->employee->department->departmentname;
							$data=$Spkl->to_array();
							$data['fullname']=$fullname;
							$data['department']=$department;
							echo json_encode($data, JSON_NUMERIC_CHECK);
						}else{
							$Spkl = new Spkl();
							echo json_encode($Spkl);
						}
						break;
					case 'find':
						$query=$this->post['query'];					
						if(isset($query['status'])){
							switch ($query['status']){
								case "last":
									break;
								default:
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$query['username'])));
									$Spkl = Spkl::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Spkl as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Spkl));
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
							$Spkl = Spkl::create($data);
							$data=$Spkl->to_array();
							$Spklhistory = new Spklhistory();
							$Spklhistory->date = date("Y-m-d h:i:s");
							$Spklhistory->fullname = $Employee->fullname;
							$Spklhistory->approvaltype = "Originator";
							$Spklhistory->spkl_id = $Spkl->id;
							$Spklhistory->actiontype = 0;
							$Spklhistory->save();
							
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "CreateSpkl";
							$err->errordate = date("Y-m-d h:i:s");
							$err->errormessage = $e->getMessage();
							$err->user = $this->currentUser->username;
							$err->ip = $this->ip;
							$err->save();
							$data = array("status"=>"error","message"=>$e->getMessage());
						}
						$logger = new Datalogger("Spkl","create",null,json_encode($data));
						$logger->SaveData();
						echo json_encode($data);									
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Spkl = Spkl::find($id);
						if ($Spkl->requeststatus==0){
							try {
								$approval = Spklapproval::find("all",array('conditions' => array("spkl_id=?",$id)));
								foreach ($approval as $delr){
									$delr->delete();
								}
								$detail = Spkldetail::find("all",array('conditions' => array("spkl_id=?",$id)));
								foreach ($detail as $delr){
									$delr->delete();
								}
								$hist = Spklhistory::find("all",array('conditions' => array("spkl_id=?",$id)));
								foreach ($hist as $delr){
									$delr->delete();
								}
								$data = $Spkl->to_array();
								$Spkl->delete();
								$logger = new Datalogger("Spkl","delete",json_encode($data),null);
								$logger->SaveData();
								$data = array("status"=>"success","message"=>"Data has been deleted");
							}catch (Exception $e){
								$err = new Errorlog();
								$err->errortype = "DeleteSpkl";
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
						$Spkl = Spkl::find($id,array('include'=>array('employee'=>array('company','department','designation','grade'))));
						$olddata = $Spkl->to_array();
						$depthead = $data['depthead'];
						unset($data['approvalstatus']);
						unset($data['fullname']);
						unset($data['department']);
						//unset($data['employee']);
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
						foreach($data as $key=>$val){
							$Spkl->$key=$val;
						}
						$Spkl->save();
							if (isset($data['depthead'])){
								// if(($Employee->level_id==4) || ($Employee->level_id==6) ){
								// }else{
									$joins   = "LEFT JOIN tbl_approver ON (tbl_spklapproval.approver_id = tbl_approver.id) ";					
									$dx = Spklapproval::find('all',array('joins'=>$joins,'conditions' => array("spkl_id=? and tbl_approver.approvaltype_id=21 and not(tbl_approver.employee_id=?)",$id,$depthead)));	
									foreach ($dx as $result) {
										//delete same type approver
										$result->delete();
										$logger = new Datalogger("Spklapproval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
									}
									$joins   = "LEFT JOIN tbl_approver ON (tbl_spklapproval.approver_id = tbl_approver.id) ";					
									$Spklapproval = Spklapproval::find('all',array('joins'=>$joins,'conditions' => array("spkl_id=? and tbl_approver.employee_id=?",$id,$depthead)));	
									foreach ($Spklapproval as &$result) {
										$result		= $result->to_array();
										$result['no']=1;
									}			
									if(count($Spklapproval)==0){ 
										$Approver = Approver::find('first',array('conditions'=>array("module='SPKL' and employee_id=? and approvaltype_id=21",$depthead)));
										if(count($Approver)>0){
											$Spklapproval = new Spklapproval();
											$Spklapproval->spkl_id = $Spkl->id;
											$Spklapproval->approver_id = $Approver->id;
											$Spklapproval->save();
										}else{
											$approver = new Approver();
											$approver->module = "SPKL";
											$approver->employee_id=$depthead;
											$approver->sequence=1;
											$approver->approvaltype_id = 21;
											$approver->isfinal = false;
											$approver->save();
											$Spklapproval = new Spklapproval();
											$Spklapproval->spkl_id = $Spkl->id;
											$Spklapproval->approver_id = $approver->id;
											$Spklapproval->save();
										}
									}
								// }
							}
							if($data['requeststatus']==1){
								$Spklapproval = Spklapproval::find('all', array('conditions' => array("spkl_id=?",$id)));					
								foreach($Spklapproval as $data){
									$data->approvalstatus=0;
									$data->save();
								}
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_spklapproval.approver_id = tbl_approver.id) ";					
								$Spklapproval = Spklapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and spkl_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								$username = $Spklapproval->approver->employee->loginname;
								$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
								$email = $adb->email;
								$Spkldetail=Spkldetail::find('all',array('conditions'=>array("spkl_id=?",$id),'include'=>array('spkl','employee'=>array('company','department','designation','grade'))));
								$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$adb->fullname.',</span></p>
													<p class=MsoNormal><span style="color:#1F497D">New SPKL/Overtime request is awaiting for your approval:</span></p>
													<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
													<table border=1 cellspacing=0 cellpadding=3 width=683>
														<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Spkl->employee->fullname.'</b></p></td></tr>
														<tr><td><p class=MsoNormal>Creation Date</p></td><td>:</td><td><p class=MsoNormal><b>'.date("d/m/Y",strtotime($Spkl->createddate)).'</b></p></td></tr>
														<tr><td><p class=MsoNormal>Date Work</p></td><td>:</td><td><p class=MsoNormal><b>'.date("d/m/Y",strtotime($Spkl->datework)).'</b></p></td></tr>';
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
								foreach ($Spkldetail as $data){
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
								$this->mail->addAddress($adb->email, $adb->fullname);
								$this->mail->Subject = "Online Approval System -> New SPKL / Overtime Submission";
								$this->mail->msgHTML($this->mailbody);
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
								$Spklhistory = new Spklhistory();
								$Spklhistory->date = date("Y-m-d h:i:s");
								$Spklhistory->fullname = $Employee->fullname;
								$Spklhistory->spkl_id = $id;
								$Spklhistory->approvaltype = "Originator";
								$Spklhistory->actiontype = 2;
								$Spklhistory->save();
							}else{
								$Spklhistory = new Spklhistory();
								$Spklhistory->date = date("Y-m-d h:i:s");
								$Spklhistory->fullname = $Employee->fullname;
								$Spklhistory->spkl_id = $id;
								$Spklhistory->approvaltype = "Originator";
								$Spklhistory->actiontype = 1;
								$Spklhistory->save();
							}
							$logger = new Datalogger("SPKL","update",json_encode($olddata),json_encode($data));
							$logger->SaveData();
							//echo json_encode($Spkl);
						
						break;
					default:
						$Spkl = Spkl::all();
						foreach ($Spkl as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Spkl, JSON_NUMERIC_CHECK);
						break;
				}
			}
			// else{
				// $result= array("status"=>"error","message"=>"Authentication error or expired, please refresh and re login application");
				// echo json_encode($result);
			// }
		}
	}
	function spklByEmp(){	
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
							$Spkl = Spkl::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Spkl as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Spkl, JSON_NUMERIC_CHECK);
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
									$Spkl = Spkl::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Spkl as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Spkl));
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
							$Spkl = Spkl::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Spkl as &$result) {
								$fullname=$result->employee->fullname;
								$result = $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Spkl, JSON_NUMERIC_CHECK);
						}else{
							echo json_encode(array());
						}
						break;
				}
			}
		}
	}

}