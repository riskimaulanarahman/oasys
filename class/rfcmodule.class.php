<?php
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

Class RfcModule extends Application{
	
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
				case 'apirfcbyemp':
					$this->rfcByEmp();
					break;
				case 'apirfc':
					$this->Rfc();
					break;
				case 'apirfcdetail':
					$this->rfcDetail();
					break;
				case 'apirfcterm':
					$this->rfcTerm();
					break;
				case 'apirfcapp':
					$this->rfcApproval();
					break;
				case 'apirfcfile':
					$this->rfcAttachment();
					break;
				case 'uploadrfcfile':
					$this->uploadRfcFile();
					break;
				case 'apirfchist':
					$this->rfcHistory();
					break;
				default:
					break;
			}
		}
	}
	public function uploadRfcFile(){
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
		$path_to_file = "upload/rfc/".$id."_".time()."_".$_FILES['myFile']['name'];
		$path_to_file = str_replace("%","_",$path_to_file);
		$path_to_file = str_replace(" ","_",$path_to_file);
		echo $path_to_file;
        move_uploaded_file($_FILES['myFile']['tmp_name'], $path_to_file);
	}
	function rfcAttachment(){
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
							$Rfcattachment = Rfcattachment::find('all', array('conditions' => array("rfc_id=?",$id)));
							foreach ($Rfcattachment as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Rfcattachment, JSON_NUMERIC_CHECK);
						}else{
							$Rfcattachment = new Rfcattachment();
							echo json_encode($Rfcattachment);
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							$Rfcattachment = Rfcattachment::find('all', array('conditions' => array("rfc_id=?",$query['rfc_id'])));
							$data=array("jml"=>count($Rfcattachment));
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
						
						$Rfcattachment = Rfcattachment::create($data);
						$logger = new Datalogger("Rfcattachment","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Rfcattachment = Rfcattachment::find($id);
						$data=$Rfcattachment->to_array();
						$Rfcattachment->delete();
						$logger = new Datalogger("Rfcattachment","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Rfcattachment);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
						$data['employee_id']=$Employee->id;
						$Rfcattachment = Rfcattachment::find($id);
						$olddata = $Rfcattachment->to_array();
						foreach($data as $key=>$val){					
							$val=($val=='No')?false:(($val=='Yes')?true:$val);
							$Rfcattachment->$key=$val;
						}
						$Rfcattachment->save();
						$logger = new Datalogger("Rfcattachment","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						echo json_encode($Rfcattachment);
						
						break;
					default:
						$Rfcattachment = Rfcattachment::all();
						foreach ($Rfcattachment as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Rfcattachment, JSON_NUMERIC_CHECK);
						break;
				}
			}
			// else{
				// $result= array("status"=>"error","message"=>"Authentication error or expired, please refresh and re login application");
				// echo json_encode($result);
			// }
		}
		
	}
	function rfcHistory(){
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
							$Rfchistory = Rfchistory::find('all', array('conditions' => array("rfc_id=?",$id),'include' => array('rfc')));
							foreach ($Rfchistory as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Rfchistory, JSON_NUMERIC_CHECK);
						}
						break;
					default:
						break;
				}
			}
			// else{
				// $result= array("status"=>"error","message"=>"Authentication error or expired, please refresh and re login application");
				// echo json_encode($result);
			// }
		}
	}

	function rfcApproval(){
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
							$join   = "LEFT JOIN tbl_approver ON (tbl_rfcapproval.approver_id = tbl_approver.id) ";
							$Rfcapproval = Rfcapproval::find('all', array('joins'=>$join,'conditions' => array("rfc_id=?",$id),'include' => array('approver'=>array('approvaltype')),"order"=>"tbl_approver.sequence"));
							foreach ($Rfcapproval as &$result) {
								$approvaltype = $result->approver->approvaltype_id;
								$result		= $result->to_array();
								$result['approvaltype']=$approvaltype;
							}
							echo json_encode($Rfcapproval, JSON_NUMERIC_CHECK);
						}else{
							$Rfcapproval = new Rfcapproval();
							echo json_encode($Rfcapproval);
						}
						break;
					case 'find':
						$query=$this->post['query'];		
						if(isset($query['status'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join   = "LEFT JOIN tbl_approver ON (tbl_rfcapproval.approver_id = tbl_approver.id) ";
							$dx = Rfcapproval::find('first', array('joins'=>$join,'conditions' => array("rfc_id=? and  tbl_approver.employee_id = ?",$query['rfc_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
							$Rfc = Rfc::find($query['rfc_id']);
							if($dx->approver->isfinal==1){
								$data=array("jml"=>1);
							}else if(($Rfc->ratetype=='SK') && ($dx->approver->approvaltype_id=='11')){
								$data=array("jml"=>1);
							}else{
								$join   = "LEFT JOIN tbl_approver ON (tbl_rfcapproval.approver_id = tbl_approver.id) ";
								$Rfcapproval = Rfcapproval::find('all', array('joins'=>$join,'conditions' => array("rfc_id=? and ApprovalStatus<=1 and not tbl_approver.employee_id=?",$query['rfc_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
								foreach ($Rfcapproval as &$result) {
									$fullname	= $result->approver->employee->fullname;		
									$result		= $result->to_array();
									$result['fullname']=$fullname;
								}
								$data=array("jml"=>count($Rfcapproval));
							}						
						} else if(isset($query['pending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
							$Rfc = Rfc::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
							foreach ($Rfc as $result) {
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_rfcapproval.approver_id = tbl_approver.id) ";					
								$Rfcapproval = Rfcapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and rfc_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								if($Rfcapproval->approver->employee_id==$emp_id){
									$request[]=$result->id;
								}
							}
							$Rfc = Rfc::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
							foreach ($Rfc as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=$Rfc;
						} else if(isset($query['mypending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
							$Rfc = Rfc::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
							$jml=0;
							foreach ($Rfc as $result) {
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_rfcapproval.approver_id = tbl_approver.id) ";					
								$Rfcapproval = Rfcapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and rfc_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								if($Rfcapproval->approver->employee_id==$emp_id){
									$request[]=$result->id;
								}
							}
							$Rfc = Rfc::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
							foreach ($Rfc as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=array("jml"=>count($Rfc));
						} else if(isset($query['filter'])){
							$Rfc = Rfc::find('all',array('conditions' => array('RequestStatus>0'),'include' => array('employee')));
							foreach ($Rfc as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=$Rfc;
						} else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Rfcapproval = Rfcapproval::create($data);
						$logger = new Datalogger("Rfcapproval","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':
						$id = $this->post['id'];
						$Rfcapproval = Rfcapproval::find($id);
						$data=$Rfcapproval->to_array();
						$Rfcapproval->delete();
						$logger = new Datalogger("Rfcapproval","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Rfcapproval);
						break;
					case 'update':
						$doid = $this->post['id'];
						$data = $this->post['data'];
						
						
							$mode= $data['mode'];
							unset($data['id']);
							unset($data['superior']);
							unset($data['depthead']);
							unset($data['approveddoc']);
							unset($data['createddate']);
							unset($data['companycode']);
							unset($data['rfcno']);
							unset($data['activity_id']);
							unset($data['rfctype']);
							unset($data['isprojectcapex']);
							unset($data['oldcontractno']);
							unset($data['periodstart']);
							unset($data['periodend']);
							unset($data['ratetype']);
							unset($data['rate']);
							unset($data['contractor_id']);
							unset($data['contractor_id2']);
							unset($data['paymentterm']);
							unset($data['capexno']);
							unset($data['capexammount']);
							unset($data['capexspent']);
							unset($data['capexbalance']);
							unset($data['rfcammount']);
							unset($data['balance']);
							unset($data['skno']);
							//unset($data['employee']);
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							
							$join   = "LEFT JOIN tbl_approver ON (tbl_rfcapproval.approver_id = tbl_approver.id) ";
							if (isset($data['mode'])){
								$Rfcapproval = Rfcapproval::find('first', array('joins'=>$join,'conditions' => array("rfc_id=? and tbl_approver.employee_id=?",$doid,$Employee->id),'include' => array('approver'=>array('employee','approvaltype'))));
								unset($data['mode']);
							}else{
								$Rfcapproval = Rfcapproval::find($this->post['id'],array('include' => array('approver'=>array('employee','approvaltype'))));
							}
							$olddata = $Rfcapproval->to_array();
							foreach($data as $key=>$val){
								$val=($val=='false')?false:(($val=='true')?true:$val);
								$Rfcapproval->$key=$val;
							}
							$Rfcapproval->save();
							$logger = new Datalogger("Rfcapproval","update",json_encode($olddata),json_encode($data));
							$logger->SaveData();
							if (isset($mode) && ($mode=='approve')){
								$Rfc = Rfc::find($doid);
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_rfcapproval.approver_id = tbl_approver.id) ";					
								$nRfcapproval = Rfcapproval::find('first',array('joins'=>$joinx,'conditions' => array("rfc_id=? and ApprovalStatus=0",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								$username = $nRfcapproval->approver->employee->loginname;
								$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
								$Rfcdetail=Rfcdetail::find('all',array('conditions'=>array("rfc_id=?",$doid),'include'=>array('rfc'=>array('employee'=>array('company','department','designation','grade','location')))));
								
								foreach ($Rfcdetail as &$result) {
									$rfcd=$result->rfc->to_array();
									$emp=$result->rfc->employee->to_array();
									$des=$result->rfc->employee->designation->designationname;
									$gradeid=$result->rfc->employee->grade->id;
									$levelid=$result->rfc->employee->level_id;
									$grade=$result->rfc->employee->grade->grade;
									$location=$result->rfc->employee->location->location;
									$comp=$result->rfc->employee->company->to_array();
									$usr = Addressbook::find('first',array('conditions'=>array("username=?",$result->rfc->employee->loginname)));
									$email=$usr->email;
									$dept=$result->rfc->employee->department->to_array();
									$result = $result->to_array();
									$result['rfc']=$rfcd;
									$result['rfc']['Employee']=$emp;
									$result['rfc']['Employee']['Company']=$comp;
									$result['rfc']['Employee']['Department']=$dept;
								}
								$complete = false;
								$Rfchistory = new Rfchistory();
								$Rfchistory->date = date("Y-m-d h:i:s");
								$Rfchistory->fullname = $Employee->fullname;
								$Rfchistory->approvaltype = $Rfcapproval->approver->approvaltype->approvaltype;
								$Rfchistory->remarks = $data['remarks'];
								$Rfchistory->rfc_id = $doid;
								
								switch ($data['approvalstatus']){
									case '1':
										$Rfc->requeststatus = 2;
										$emto=$email;$emname=$emp['fullname'];
										$this->mail->Subject = "Online Approval System -> Need Rework";
										$red = 'Your RFC request require some rework :';
										$Rfchistory->actiontype = 3;
										break;
									case '2':
										if ($Rfcapproval->approver->isfinal == 1){
											$Rfc->requeststatus = 3;
											$emto=$email;$emname=$emp['fullname'];
											$this->mail->Subject = "Online Approval System -> Approval Completed";
											$red = 'Your RFC request has been approved';
											//delete unnecessary approver
											$Rfcapproval = Rfcapproval::find('all', array('joins'=>$join,'conditions' => array("rfc_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
											foreach ($Rfcapproval as $data) {
												if($data->approvalstatus==0){
													$logger = new Datalogger("Rfcapproval","delete",json_encode($data->to_array()),"automatic remove unnecessary approver by system");
													$logger->SaveData();
													$data->delete();
												}
											}
											$complete =true;
										}else if(($Rfc->ratetype=='SK') && ($Rfcapproval->approver->approvaltype_id=='11' ) ){
											$Rfc->requeststatus = 3;
											$emto=$email;$emname=$emp['fullname'];
											$this->mail->Subject = "Online Approval System -> Approval Completed";
											$red = 'Your RFC request has been approved';
											//delete unnecessary approver
											$Rfcapproval = Rfcapproval::find('all', array('joins'=>$join,'conditions' => array("rfc_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
											foreach ($Rfcapproval as $data) {
												if($data->approvalstatus==0){
													$logger = new Datalogger("Rfcapproval","delete",json_encode($data->to_array()),"automatic remove unnecessary approver by system");
													$logger->SaveData();
													$data->delete();
												}
											}
											$complete =true;
										}else{
											$Rfc->requeststatus = 1;
											$emto=$adb->email;$emname=$adb->fullname;
											$this->mail->Subject = "Online Approval System -> New RFC Submission";
											$red = 'New RFC request awaiting for your approval:';
										}
										$Rfchistory->actiontype = 4;							
										break;
									case '3':
										$Rfc->requeststatus = 4;
										$emto=$email;$emname=$emp['fullname'];
										$Rfchistory->actiontype = 5;
										$this->mail->Subject = "Online Approval System -> Request Rejected";
										$red = 'Your RFC request has been rejected';
										break;
									default:
										break;
								}
								//print_r($Rfc);
								$Rfc->save();
								$Rfchistory->save();
								echo "email to :".$emto." ->".$emname;
								$this->mail->addAddress($emto, $emname);
								$rfctype=array("New","Addendum","Project Capex");
								$joins   = "LEFT JOIN tbl_rfccontractor ON (tbl_rfc.contractor_id = tbl_rfccontractor.id) LEFT JOIN tbl_rfccontractor as c ON (tbl_rfc.contractor_id2 = c.id) LEFT JOIN tbl_rfcactivity ON (tbl_rfc.activity_id = tbl_rfcactivity.id) ";
								$sel = 'tbl_rfc.*, tbl_rfccontractor.contractorname AS contractorname,c.contractorname as contractorname2, tbl_rfcactivity.activitydescr as activitydescr ';
								$RfcJ = Rfc::find($doid,array('joins'=>$joins,'select'=>$sel,'include'=>array('employee'=>array('company','department','designation','grade','location'))));
								$Rfcterm=Rfcterm::find('all',array('conditions'=>array("rfc_id=?",$doid)));
								$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$emname.',</span></p>
													<p class=MsoNormal><span style="color:#1F497D">'.$red.'</span></p>
													<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
													<table border=1 cellspacing=0 cellpadding=3 width=683>
														<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$RfcJ->employee->fullname.'</b></p></td></tr>
														<tr><td><p class=MsoNormal>Date</p></td><td>:</td><td><p class=MsoNormal><b>'.date("d/m/Y",strtotime($RfcJ->createddate)).'</b></p></td></tr>
														<tr><td><p class=MsoNormal>RFC No</p></td><td>:</td><td><p class=MsoNormal><b>'.$RfcJ->rfcno.'</b></p></td></tr>
														<tr><td><p class=MsoNormal>Kind of Contract</p></td><td>:</td><td><p class=MsoNormal><b>'.$RfcJ->activitydescr.'</b></p></td></tr>
														<tr><td><p class=MsoNormal>Rate Type</p></td><td>:</td><td><p class=MsoNormal><b>'.$RfcJ->ratetype.'</b></p></td></tr>
														<tr><td><p class=MsoNormal>Rate / SK No</p></td><td>:</td><td><p class=MsoNormal><b>'.$RfcJ->skno.$RfcJ->rate.'</b></p></td></tr>
														<tr><td><p class=MsoNormal>Period of Contract</p></td><td>:</td><td><p class=MsoNormal><b>'.date("d/m/Y",strtotime($RfcJ->periodstart)).' - '.date("d/m/Y",strtotime($RfcJ->periodend)).'</b></p></td></tr>
														<tr><td><p class=MsoNormal>Payment Term</p></td><td>:</td><td><p class=MsoNormal><b>'.$RfcJ->paymentterm.'</b></p></td></tr>
														<tr><td><p class=MsoNormal>RFC Type</p></td><td>:</td><td><p class=MsoNormal><b>'.$rfctype[$RfcJ->rfctype].'</b></p></td></tr>';
														if($RfcJ->rfctype==1){
															$this->mailbody .='<tr><td><p class=MsoNormal>Old Contract No</td><td>:</td><td><p class=MsoNormal><b>'.$RfcJ->oldcontractno.'</b></p></td></tr>';
														}
														if($RfcJ->isprojectcapex==1){
															$this->mailbody .='<tr><td colspan=3><p class=MsoNormal><b>Capex Information</b></p></td></tr>
																<tr><td><p class=MsoNormal>Capex No</p></td><td>:</td><td><p class=MsoNormal><b>'.$RfcJ->capexno.'</b></p></td></tr>
																<tr><td><p class=MsoNormal>Capex Ammount</p></td><td>:</td><td><p class=MsoNormal><b> Rp.'.number_format($RfcJ->capexammount).'</b></p></td></tr>
																<tr><td><p class=MsoNormal>Capex Spent</p></td><td>:</td><td><p class=MsoNormal><b> Rp.'.number_format($RfcJ->capexspent).'</b></p></td></tr>
																<tr><td><p class=MsoNormal>Capex Balance</p></td><td>:</td><td><p class=MsoNormal><b> Rp.'.number_format($RfcJ->capexbalance).'</b></p></td></tr>
																<tr><td><p class=MsoNormal>RFC Ammount</p></td><td>:</td><td><p class=MsoNormal><b> Rp.'.number_format($RfcJ->rfcammount).'</b></p></td></tr>
																<tr><td><p class=MsoNormal>Balance after this RFC</p></td><td>:</td><td><p class=MsoNormal><b> Rp.'.number_format($RfcJ->balance).'</b></p></td></tr>';
														}
								$this->mailbody .='</table>
													<p class=MsoNormal><b>Contract Detail :</b></p>
													<table border=1 cellspacing=0 cellpadding=3 width=683><tr><th><p class=MsoNormal>Description of Work</p></th></tr>
													';
								$no=1;
								foreach ($Rfcdetail as $data){
									$this->mailbody .='<tr style="height:22.5pt"><td><p class=MsoNormal>'.$no.'. '.$data['description'].'</p></td></tr>';
									$no++;
								}
								$this->mailbody .='<tr><td></td></tr>';
								$this->mailbody .='<tr><th><p class=MsoNormal>Other Term & Condition</p></th></tr>';
								$no="a";
								foreach ($Rfcterm as $data){
										$this->mailbody .= '<tr style="height:22.5pt"><td><p class=MsoNormal>'.$no.'. '.$data->term.'</p></td></tr>';
										$no++;
								}
								$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
								
								$this->mail->msgHTML($this->mailbody);
								if ($complete){
									$compx = Company::find('first',array('conditions'=>array("companycode=?",$RfcJ->companycode)));
									$standard = ($RfcJ->ratetype=="SK")?"Standard Contract":"Non Standard Contract";
									$pdfContent = "<table border=0 cellspacing=0 cellpadding=3 ><tr><td style='width:250px;padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;'>".$standard."</td></tr>";
									if ($RfcJ->isprojectcapex==1){
										$pdfContent .= "<tr><td style='padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-top:none'>Project / CAPEX Related Activities</td></tr>";
									}
									$red = ($RfcJ->rfctype==0)?"We request the following work to be carried out on contract :":"We request the following Amendments to be made for Contract No : ".$RfcJ->oldcontractno;
									$pdfContent .= "</table>";
									$pdfContent .="<p><h2 style='width:100%;text-align:center'>".$compx->companyname."</h2>";
									$neworam = ($RfcJ->rfctype=="0")?"NEW CONTRACT":"CONTRACT AMANDMENTS";
									$pdfContent .="<h4 style='width:100%;text-align:center'><u>REQUEST FOR ".$neworam."</u></h4>";
									$pdfContent .="<h4 style='width:100%;text-align:center'><u>RFC NO: ".$RfcJ->rfcno."</u></h4></p>";
									
									
									$pdfContent .= "<br><br><table border=0 cellspacing=0 cellpadding=3>
													<tr><td>To </td><td>: Legal Department</td></tr>
													<tr><td>From </td><td>: ".$RfcJ->employee->fullname."</td></tr>
													</table><br>
													<p>".$red."</p><br>";
									$pdfContent .= "<table border=0 cellspacing=0 cellpadding=3>";
									$pdfContent .= "<tr><td style='width:250px;padding:0in 5.4pt 0in 5.4pt;'>Kind of Contract</td>
														<td style='width:350px;padding:0in 5.4pt 0in 5.4pt;border-bottom:solid windowtext 1.0pt;'> : ".$RfcJ->activitydescr."</td>
													</tr>
													<tr><td style='width:250px;padding:0in 5.4pt 0in 5.4pt;'>Period</td>
														<td style='padding:0in 5.4pt 0in 5.4pt;border-bottom:solid windowtext 1.0pt;'> : ".date("d/m/Y",strtotime($RfcJ->periodstart))." - ".date("d/m/Y",strtotime($RfcJ->periodend))."</td>
													</tr>
													<tr><td style='width:250px;padding:0in 5.4pt 0in 5.4pt;'>Payment Term</td>
														<td style='padding:0in 5.4pt 0in 5.4pt;border-bottom:solid windowtext 1.0pt;'> : ".$RfcJ->paymentterm."</td>
													</tr>
													<tr><td style='width:250px;padding:0in 5.4pt 0in 5.4pt;'>Scope of Work</td>
														<td style='padding:0in 5.4pt 0in 5.4pt;'> : <b><u> Description of Work</u></b></td>
													</tr>
													";
									$no=1;
									foreach ($Rfcdetail as $data){
										$pdfContent .= '<tr><td></td><td style="padding:0in 5.4pt 0in 5.4pt;border-bottom:solid windowtext 1.0pt;">'.$no.' . '.$data['description'].'</td></tr>';
										$no++;
									}
									$pdfContent .= '<tr><td style="padding:0in 5.4pt 0in 5.4pt;">Rate / SK No</td><td style="padding:0in 5.4pt 0in 5.4pt;border-bottom:solid windowtext 1.0pt;">'.$RfcJ->rate.'</td></tr>';
									
									
									$pdfContent .= "<tr><td style='width:250px;padding:0in 5.4pt 0in 5.4pt;'>Other Term / Conditions</td>
														<td style='padding:0in 5.4pt 0in 5.4pt;'> : </td>
													</tr>
													";
									$no="a";
									foreach ($Rfcterm as $data){
										$pdfContent .= '<tr><td></td><td style="padding:0in 5.4pt 0in 5.4pt;border-bottom:solid windowtext 1.0pt;">'.$no.'. '.$data->term.'</td></tr>';
										$no++;
									}
									$pdfContent .= "<tr><td style='width:250px;padding:0in 5.4pt 0in 5.4pt;'>Contractors Recomended</td>
														<td style='padding:0in 5.4pt 0in 5.4pt;'> : </td>
													</tr>
													";
									$pdfContent .= '<tr><td></td><td style="padding:0in 5.4pt 0in 5.4pt;border-bottom:solid windowtext 1.0pt;">'.$RfcJ->contractorname.'</td></tr>';
									$pdfContent .= '<tr><td></td><td style="padding:0in 5.4pt 0in 5.4pt;border-bottom:solid windowtext 1.0pt;">'.$RfcJ->contractorname2.'</td></tr>';
									
									$pdfContent .= "</table>";
									$joinx   = "LEFT JOIN tbl_approver ON (tbl_rfcapproval.approver_id = tbl_approver.id) ";					
									$Rfcapproval = Rfcapproval::find('all',array('joins'=>$joinx,'conditions' => array("rfc_id=?",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee','approvaltype'))));							
									$pdfContent .= "<br><br><table border=0 cellspacing=4 cellpadding=3>";
									$no=5;
									foreach ($Rfcapproval as $data){
										$col = $no % 4;
										if($col == 1){
											$pdfContent .= "<tr>";
										}
										$pdfContent .= '<td align="center" style="padding:0in 5.4pt 0in 5.4pt;">
										<img src="images/approved.png" style="height:25pt" alt="Approved from System">
										<br><small><i>'. date("d/m/Y",strtotime($data->approvaldate)).'</i>
										<br><u>'.$data->approver->employee->fullname.'</u></small>
										<br>( '.$data->approver->approvaltype->approvaltype.' )
										</td>';
										if($col == 0){
											$pdfContent .= "</tr>";
										}
										$no++;
									}
									if($col !== 0){
										for($a=1;$a<$col;$a++){
											$pdfContent .= '<td style="padding:0in 5.4pt 0in 5.4pt;></td>';
										}
										$pdfContent .= "</tr>";
									}
									$pdfContent .= "</table>";
									if($RfcJ->isprojectcapex==1){
										$pdfContent .= "<br><br><b>Please Attach :</b>
										<br><table border=0 cellspacing=0 cellpadding=3>
										<tr><td style='width:100px;' align='right'>1</td><td>Copy of Approved Capex</td></tr>
										<tr><td style='width:100px;' align='right'>2</td><td>BOQ of Related Project</td></tr>
										</table>
										<br><b>For All Purchase Requisition Items to be Created in SAP System by User before LOI is issued.</b>
										<br><table border=0 cellspacing=0 cellpadding=3>
										<tr><td style='width:250px;padding-left:30px;' >a. Capex / IO No </td><td style='padding:0in 5.4pt 0in 5.4pt;border-bottom:solid windowtext 1.0pt;'>".$RfcJ->capexno."</td></tr>
										<tr><td style='width:250px;padding-left:30px;' >b. Capex Ammount</td><td style='padding:0in 5.4pt 0in 5.4pt;border-bottom:solid windowtext 1.0pt;'>Rp. ".number_format($RfcJ->capexammount)."</td></tr>
										<tr><td style='width:250px;padding-left:30px;' >c. Spent to dated</td><td style='padding:0in 5.4pt 0in 5.4pt;border-bottom:solid windowtext 1.0pt;'>Rp. ".number_format($RfcJ->capexspent)."</td></tr>
										<tr><td style='width:250px;padding-left:30px;' >d. Capex Balance (b-c)</td><td style='padding:0in 5.4pt 0in 5.4pt;border-bottom:solid windowtext 1.0pt;'>Rp. ".number_format($RfcJ->capexbalance)."</td></tr>
										<tr><td style='width:250px;padding-left:30px;' >e. Estimated amount required<br> for this RFC</td><td style='padding:0in 5.4pt 0in 5.4pt;border-bottom:solid windowtext 1.0pt;'>Rp. ".number_format($RfcJ->rfcammount)."</td></tr>
										<tr><td style='width:250px;padding-left:30px;' >f. Balance after this RFC</td><td style='padding:0in 5.4pt 0in 5.4pt;border-bottom:solid windowtext 1.0pt;'>Rp. ".number_format($RfcJ->balance)."</td></tr>
										</table>";
									}else{
										$pdfContent .= "<br><br><b>Please Attach :</b>
										<br><table border=0 cellspacing=0 cellpadding=3>
										<tr><td style='width:100px;' align='right'>1</td><td>Copy of Decision Letter.</td></tr>
										<tr><td style='width:100px;' align='right'>2</td><td>Updated company profile for the existing contractor / Company Profile for New Contractor</td></tr>
										<tr><td style='width:100px;' align='right'>3</td><td>For Non Standard should attached with unit spesification, e.g.; manufacturing year, size, capacity</td></tr>";
										if($RfcJ->rfctype!=="New"){
											$pdfContent .= "<tr><td style='width:100px;' align='right'>4</td><td>To be attached with previous contract</td></tr>";
										}
										$pdfContent .= "</table>";
										$pdfContent .= "<b>SAP/PIMS System :</b>
										<br><table border=0 cellspacing=0 cellpadding=3>
										<tr><td style='width:100px;' align='right'>1</td><td>Acacia Harvesting Related Activity - Work Order to be created in PIMS</td></tr>
										</table>";
									}
									$pdfContent .= "<p><b>RFC must be submitted prior to work commencement.
													<br>Approved RFC need to be submitted to legal to issue contract at least 15 working days prior to work commencement.</b></p>";
									if($RfcJ->isprojectcapex==1){
										$pdfContent .= "<p><b><font color='red'>LOI shall be issued based on approved PR to ensure that the Capex is approved prior to work commencement</font></b></p>";
									}
									try {
										$html2pdf = new Html2Pdf('P', 'A4', 'fr');
										$html2pdf->writeHTML($pdfContent);
										ob_clean();
										$fileName ='rfc'.DS.'pdf'.DS.''.$Rfc->rfcno.'_'.date("YmdHis").'.pdf';
										$fileName = str_replace("/","",$fileName);
										$filePath = SITE_PATH.DS.$fileName;
										$html2pdf->output($filePath, 'F');
										$this->mail->addAttachment($filePath);
										$Rfc->approveddoc=str_replace("\\","/",$fileName);
										$Rfc->save();
									} catch (Html2PdfException $e) {
										$html2pdf->clean();
										$formatter = new ExceptionFormatter($e);
										$err = new Errorlog();
										$err->errortype = "RFCPDFGenerator";
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
									$err->errortype = "RFC Mail";
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
							echo json_encode($Rfcapproval);
						
						break;
					default:
						$Rfcapproval = Rfcapproval::all();
						foreach ($Rfcapproval as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Rfcapproval, JSON_NUMERIC_CHECK);
						break;
				}
			}
			// else{
				// $result= array("status"=>"error","message"=>"Authentication error or expired, please refresh and re login application");
				// echo json_encode($result);
			// }
		}
	}
	function rfcDetail(){
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
							$Rfcdetail = Rfcdetail::find('all', array('conditions' => array("rfc_id=?",$id)));
							foreach ($Rfcdetail as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Rfcdetail, JSON_NUMERIC_CHECK);
						}else{
							$Rfcdetail = new Rfcdetail();
							echo json_encode($Rfcdetail);
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							$Rfcdetail = Rfcdetail::find('all', array('conditions' => array("rfc_id=?",$query['rfc_id'])));
							$data=array("jml"=>count($Rfcdetail));
						}else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Rfcdetail = Rfcdetail::create($data);
						$logger = new Datalogger("Rfcdetail","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Rfcdetail = Rfcdetail::find($id);
						$data=$Rfcdetail->to_array();
						$Rfcdetail->delete();
						$logger = new Datalogger("Rfcdetail","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Rfcdetail);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						
						$Rfcdetail = Rfcdetail::find($id);
						$olddata = $Rfcdetail->to_array();
						foreach($data as $key=>$val){					
							$val=($val=='No')?false:(($val=='Yes')?true:$val);
							$Rfcdetail->$key=$val;
						}
						$Rfcdetail->save();
						$logger = new Datalogger("Rfcdetail","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						echo json_encode($Rfcdetail);
						
						break;
					default:
						$Rfcdetail = Rfcdetail::all();
						foreach ($Rfcdetail as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Rfcdetail, JSON_NUMERIC_CHECK);
						break;
				}
			}
			// else{
				// $result= array("status"=>"error","message"=>"Authentication error or expired, please refresh and re login application");
				// echo json_encode($result);
			// }
		}
	}
	function rfcTerm(){
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
							$Rfcterm = Rfcterm::find('all', array('conditions' => array("rfc_id=?",$id)));
							foreach ($Rfcterm as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Rfcterm, JSON_NUMERIC_CHECK);
						}else{
							$Rfcterm = new Rfcterm();
							echo json_encode($Rfcterm);
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							$Rfcterm = Rfcterm::find('all', array('conditions' => array("rfc_id=?",$query['rfc_id'])));
							$data=array("jml"=>count($Rfcterm));
						}else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Rfcterm = Rfcterm::create($data);
						$logger = new Datalogger("Rfcterm","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Rfcterm = Rfcterm::find($id);
						$data=$Rfcterm->to_array();
						$Rfcterm->delete();
						$logger = new Datalogger("Rfcterm","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Rfcterm);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						
						$Rfcterm = Rfcterm::find($id);
						$olddata = $Rfcterm->to_array();
						foreach($data as $key=>$val){					
							$val=($val=='No')?false:(($val=='Yes')?true:$val);
							$Rfcterm->$key=$val;
						}
						$Rfcterm->save();
						$logger = new Datalogger("Rfcterm","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						echo json_encode($Rfcterm);
						
						break;
					default:
						$Rfcterm = Rfcterm::all();
						foreach ($Rfcterm as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Rfcterm, JSON_NUMERIC_CHECK);
						break;
				}
			}
			// else{
				// $result= array("status"=>"error","message"=>"Authentication error or expired, please refresh and re login application");
				// echo json_encode($result);
			// }
		}
	}
	function Rfc(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'byid':
						$id = $this->post['id'];
						$Rfc = Rfc::find($id, array('include' => array('employee')));
						if ($Rfc){
							//$dataemp = $Rfc->employee->to_array();
							$data=$Rfc->to_array();
							//$data['employee']=$dataemp;
							echo json_encode($data, JSON_NUMERIC_CHECK);
						}else{
							$Rfc = new Rfc();
							echo json_encode($Rfc);
						}
						break;
					case 'find':
						$query=$this->post['query'];					
						if(isset($query['status'])){
							switch ($query['status']){
								case 'last':
									$company = $query['companycode'];
									$id=$query['rfc_id'];
									$Rfc=Rfc::find($id);
									$Rfcactivity=Rfcactivity::find($Rfc->activity_id);
									$joins   = "LEFT JOIN tbl_approver ON (tbl_rfcapproval.approver_id = tbl_approver.id) LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id)";
									$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";	
									$companyBU=( ($company=='KPA') )?"KPSI":$company;
									$Rfcapproval = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id='11' and tbl_employee.companycode=? ",$id,$companyBU)));	
									foreach ($Rfcapproval as &$result) {
										$result		= $result->to_array();
										$result['no']=1;
									}
									if(count($Rfcapproval)==0){
										$dx = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id=11",$id)));	
										foreach ($dx as $result) {
											//delete BU Head Approval because of change Company Code
											$result->delete();
											$logger = new Datalogger("Rfcapproval","delete",json_encode($result->to_array()),"delete BU Head Approval because of change Company Code");
										}
										$ApproverBU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='RFC' and tbl_approver.isactive='1' and approvaltype_id='11' and tbl_employee.companycode=?",$companyBU)));
										if(count($ApproverBU)>0){
											$Rfcapproval = new Rfcapproval();
											$Rfcapproval->rfc_id = $id;
											$Rfcapproval->approver_id = $ApproverBU->id;
											$Rfcapproval->save();
										}
									}
									$companyFC=(($company=="BCL") ||  ($company=='KPA') )?"KPSI":$company;
									$Rfcapproval = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id='10' and tbl_employee.companycode=? ",$id,$companyFC)));	
									foreach ($Rfcapproval as &$result) {
										$result		= $result->to_array();
										$result['no']=1;
									}
									if(count($Rfcapproval)==0){
										$dx = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id=10",$id)));	
										foreach ($dx as $result) {
											//delete BU FC Approval because of change Company Code
											$result->delete();
											$logger = new Datalogger("Rfcapproval","delete",json_encode($result->to_array()),"delete BU FC Approval because of change Company Code");
										}
										$ApproverBUFC = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='RFC' and tbl_approver.isactive='1' and approvaltype_id='10' and tbl_employee.companycode=? ",$companyFC)));
										if(count($ApproverBUFC)>0){
											$Rfcapproval = new Rfcapproval();
											$Rfcapproval->rfc_id = $id;
											$Rfcapproval->approver_id = $ApproverBUFC->id;
											$Rfcapproval->save();
										}
									}
									if($Rfcactivity->ishrrelated=="1"){					
										$Rfcapproval = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id='15' ",$id)));	
										foreach ($Rfcapproval as &$result) {
											$result		= $result->to_array();
											$result['no']=1;
										}
										if(count($Rfcapproval)==0){
											$dx = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id=1",$id)));	
											foreach ($dx as $result) {
												//delete HR KF Approval because of change Company Code
												$result->delete();
												$logger = new Datalogger("Rfcapproval","delete",json_encode($result->to_array()),"delete HR KF Approval because of change Company Code");
											}
											$ApproverHRKF = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='RFC' and tbl_approver.isactive='1' and approvaltype_id='15'")));
											if(count($ApproverHRKF)>0){
												$Rfcapproval = new Rfcapproval();
												$Rfcapproval->rfc_id = $id;
												$Rfcapproval->approver_id = $ApproverHRKF->id;
												$Rfcapproval->save();
											}
										}
										if (($company=='IHM') || ($company=='AHL')  || ($company=='KPS')|| ($company=='KPA')) {
											$Rfcapproval = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id='9' and tbl_employee.companycode=? ",$id,$company)));	
											foreach ($Rfcapproval as &$result) {
												$result		= $result->to_array();
												$result['no']=1;
											}
											if(count($Rfcapproval)==0){
												$dx = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id=9",$id)));	
												foreach ($dx as $result) {
													//delete HR BU Approval because of change Company Code
													$result->delete();
													$logger = new Datalogger("Rfcapproval","delete",json_encode($result->to_array()),"delete HR BU Approval because of change Company Code");
												}
												$ApproverHRFU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='RFC' and tbl_approver.isactive='1' and approvaltype_id='9' and tbl_employee.companycode=?",$company)));
												if(count($ApproverHRFU)>0){
													$Rfcapproval = new Rfcapproval();
													$Rfcapproval->rfc_id = $id;
													$Rfcapproval->approver_id = $ApproverHRFU->id;
													$Rfcapproval->save();
												}
											}
										}else{
											$dx = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id=9",$id)));	
											foreach ($dx as $result) {
												//delete HR BU Approval because of change Company Code
												$result->delete();
												$logger = new Datalogger("Rfcapproval","delete",json_encode($result->to_array()),"delete HR BU Approval because of change Company Code");
											}
										}
										//$data= array("activity"=>"HR Related");
									}else{
										//$joins   = "LEFT JOIN tbl_approver ON (tbl_rfcapproval.approver_id = tbl_approver.id) ";					
										$dx = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id=15",$id)));	
										foreach ($dx as $result) {
											//delete HR KF
											$result->delete();
											$logger = new Datalogger("Rfcapproval","delete",json_encode($result->to_array()),"delete HR of KF Approval for non HR Related Actovity");
										}
										$dx = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id=9",$id)));	
										foreach ($dx as $result) {
											//delete HR BU
											$result->delete();
											$logger = new Datalogger("Rfcapproval","delete",json_encode($result->to_array()),"delete HR BU Approval for non HR Related Actovity");
										}
										//$data= array("activity"=>"Operation Related");
									}
		
									
									$Rfcapproval = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id='8' ",$id)));	
									foreach ($Rfcapproval as &$result) {
										$result		= $result->to_array();
										$result['no']=1;
									}
									if(count($Rfcapproval)==0){
										$dx = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id=8",$id)));	
										foreach ($dx as $result) {
											//delete CAD KF Approval because of change Company Code
											$result->delete();
											$logger = new Datalogger("Rfcapproval","delete",json_encode($result->to_array()),"delete CAD KF Approval because of change Company Code");
										}
										$ApproverCADKF = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='RFC' and tbl_approver.isactive='1' and approvaltype_id='8'")));
										if(count($ApproverCADKF)>0){
											$Rfcapproval = new Rfcapproval();
											$Rfcapproval->rfc_id = $id;
											$Rfcapproval->approver_id = $ApproverCADKF->id;
											$Rfcapproval->save();
										}
									}
									
									$Rfcapproval = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id='7' and tbl_employee.companycode=? ",$id,$company)));	
									foreach ($Rfcapproval as &$result) {
										$result		= $result->to_array();
										$result['no']=1;
									}
									if(count($Rfcapproval)==0){
										$dx = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id=7",$id)));	
										foreach ($dx as $result) {
											//delete CAD BU Approval because of change Company Code
											$result->delete();
											$logger = new Datalogger("Rfcapproval","delete",json_encode($result->to_array()),"delete CAD BU Approval because of change Company Code");
										}
										$ApproverCADFU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='RFC' and tbl_approver.isactive='1' and approvaltype_id='7' and tbl_employee.companycode=?",$company)));
										if(count($ApproverCADFU)>0){
											$Rfcapproval = new Rfcapproval();
											$Rfcapproval->rfc_id = $id;
											$Rfcapproval->approver_id = $ApproverCADFU->id;
											$Rfcapproval->save();
										}
									}
									$Rfcnew = Rfc::find('first',array('select' => "CONCAT('RFC/KF','".$company."','/',YEAR(CURDATE()),'/',LPAD(MONTH(CURDATE()), 2, '0'),'/',LPAD(CASE when max(substring(rfcno,-4,4)) is null then 1 else max(substring(rfcno,-4,4))+1 end,4,'0')) as RfcNo","conditions"=>array("substring(rfcno,7,".strlen($company).")=? and not(id = ?) and substring(rfcno,".(strlen($company)+8).",4)=YEAR(CURDATE()) ",$company,$query['rfc_id'])));
									$Rfc =Rfc::find($id);
									$Rfc->companycode =$company;
									$Rfc->rfcno =$Rfcnew->rfcno;
									$Rfc->save();
									$data=array("rfcno"=>$Rfcnew->rfcno);
									break;
								case 'chrate':
									$ratetype = $query['ratetype'];
									$id=$query['rfc_id'];
									$Rfc = Rfc::find($id);
									if($ratetype=="SK"){
										$joins   = "LEFT JOIN tbl_approver ON (tbl_rfcapproval.approver_id = tbl_approver.id) ";					
										$dx = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id=14",$id)));	
										foreach ($dx as $result) {
											//delete MD of KF for non SK
											$result->delete();
											$logger = new Datalogger("Rfcapproval","delete",json_encode($result->to_array()),"delete MD Approval for non SK RFC");
										}
										$dx = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id=13",$id)));	
										foreach ($dx as $result) {
											//delete KFFC for non SK
											$result->delete();
											$logger = new Datalogger("Rfcapproval","delete",json_encode($result->to_array()),"delete KFFC Approval for non SK RFC");
										}
										$dx = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id=12",$id)));	
										foreach ($dx as $result) {
											//delete CPU for non SK
											$result->delete();
											$logger = new Datalogger("Rfcapproval","delete",json_encode($result->to_array()),"delete CPU Approval for non SK RFC");
										}
										if(($Rfc->companycode=='IHM') || ($Rfc->companycode=='AHL') || ($Rfc->companycode=='KPS')){
											$dx = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id=8",$id)));	
											foreach ($dx as $result) {
												//delete CAD KF for non SK
												$result->delete();
												$logger = new Datalogger("Rfcapproval","delete",json_encode($result->to_array()),"delete CADKF Approval for non SK RFC");
											}
										}
										
										$data= array("trigger"=>"SK");
									}else{
										$joins   = "LEFT JOIN tbl_approver ON (tbl_rfcapproval.approver_id = tbl_approver.id) ";
										$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";										
										$Rfcapproval = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id='14'",$id)));	
										foreach ($Rfcapproval as &$result) {
											$result		= $result->to_array();
											$result['no']=1;
										}
										//$data= array("count"=>count($Rfcapproval));
										if(count($Rfcapproval)==0){
											$ApproverMD = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='RFC' and tbl_approver.isactive='1' and approvaltype_id='14' ")));
											//$data= print_r($ApproverMD,true);
											if(count($ApproverMD)>0){
												$Rfcapproval = new Rfcapproval();
												$Rfcapproval->rfc_id = $id;
												$Rfcapproval->approver_id = $ApproverMD->id;
												$Rfcapproval->save();
												$data= array("appr"=>$Rfcapproval);
											}
										}
										$Rfcapproval = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id='13'",$id)));	
										foreach ($Rfcapproval as &$result) {
											$result		= $result->to_array();
											$result['no']=1;
										}			
										if(count($Rfcapproval)==0){
											$ApproverKDUFC = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='RFC' and tbl_approver.isactive='1' and approvaltype_id='13'")));
											if(count($ApproverKDUFC)>0){
												$Rfcapproval = new Rfcapproval();
												$Rfcapproval->rfc_id = $id;
												$Rfcapproval->approver_id = $ApproverKDUFC->id;
												$Rfcapproval->save();
											}
										}
										$Rfcapproval = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id='12'",$id)));	
										foreach ($Rfcapproval as &$result) {
											$result		= $result->to_array();
											$result['no']=1;
										}			
										if(count($Rfcapproval)==0){
											$ApproverCPU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='RFC' and tbl_approver.isactive='1' and approvaltype_id='12' ")));
											if(count($ApproverCPU)>0){
												$Rfcapproval = new Rfcapproval();
												$Rfcapproval->rfc_id = $id;
												$Rfcapproval->approver_id = $ApproverCPU->id;
												$Rfcapproval->save();
											}
										}
										$Rfcapproval = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id='8'",$id)));	
										foreach ($Rfcapproval as &$result) {
											$result		= $result->to_array();
											$result['no']=1;
										}			
										if(count($Rfcapproval)==0){
											$ApproverCADKF = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='RFC' and tbl_approver.isactive='1' and approvaltype_id='8' ")));
											if(count($ApproverCADKF)>0){
												$Rfcapproval = new Rfcapproval();
												$Rfcapproval->rfc_id = $id;
												$Rfcapproval->approver_id = $ApproverCADKF->id;
												$Rfcapproval->save();
											}
										}
										//$data= array("trigger"=>"Non SK");
									}
									$Rfc =Rfc::find($id);
									$Rfc->ratetype =$ratetype;
									$Rfc->save();
									break;
								case 'chactivity':
									$idActivity = $query['activity'];
									$id=$query['rfc_id'];
									$Rfcactivity = Rfcactivity::find($idActivity);
									$Rfc = Rfc::find($id);
									$joins   = "LEFT JOIN tbl_approver ON (tbl_rfcapproval.approver_id = tbl_approver.id) LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id)";
									$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";
									if($Rfcactivity->ishrrelated=="1"){					
										$Rfcapproval = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id='15' ",$id)));	
										foreach ($Rfcapproval as &$result) {
											$result		= $result->to_array();
											$result['no']=1;
										}
										if(count($Rfcapproval)==0){
											$dx = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id=1",$id)));	
											foreach ($dx as $result) {
												//delete HR KF Approval because of change Company Code
												$result->delete();
												$logger = new Datalogger("Rfcapproval","delete",json_encode($result->to_array()),"delete HR KF Approval because of change Company Code");
											}
											$ApproverHRKF = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='RFC' and tbl_approver.isactive='1' and approvaltype_id='15'")));
											if(count($ApproverHRKF)>0){
												$Rfcapproval = new Rfcapproval();
												$Rfcapproval->rfc_id = $id;
												$Rfcapproval->approver_id = $ApproverHRKF->id;
												$Rfcapproval->save();
											}
										}
										if (($Rfc->companycode=='IHM') || ($Rfc->companycode=='AHL')  || ($Rfc->companycode=='KPS') || ($Rfc->companycode=='KPA')) {
											$Rfcapproval = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id='9' and tbl_employee.companycode=? ",$id,$Rfc->companycode)));	
											foreach ($Rfcapproval as &$result) {
												$result		= $result->to_array();
												$result['no']=1;
											}
											if(count($Rfcapproval)==0){
												$dx = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id=9",$id)));	
												foreach ($dx as $result) {
													//delete HR BU Approval because of change Company Code
													$result->delete();
													$logger = new Datalogger("Rfcapproval","delete",json_encode($result->to_array()),"delete HR BU Approval because of change Company Code");
												}
												$ApproverHRFU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='RFC' and tbl_approver.isactive='1' and approvaltype_id='9' and tbl_employee.companycode=?",$Rfc->companycode)));
												if(count($ApproverHRFU)>0){
													$Rfcapproval = new Rfcapproval();
													$Rfcapproval->rfc_id = $id;
													$Rfcapproval->approver_id = $ApproverHRFU->id;
													$Rfcapproval->save();
												}
											}
										}
										$data= array("activity"=>"HR Related");
									}else{
										$joins   = "LEFT JOIN tbl_approver ON (tbl_rfcapproval.approver_id = tbl_approver.id) ";					
										$dx = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id=15",$id)));	
										foreach ($dx as $result) {
											//delete HR KF
											$result->delete();
											$logger = new Datalogger("Rfcapproval","delete",json_encode($result->to_array()),"delete HR of KF Approval for non HR Related Actovity");
										}
										$dx = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id=9",$id)));	
										foreach ($dx as $result) {
											//delete HR BU
											$result->delete();
											$logger = new Datalogger("Rfcapproval","delete",json_encode($result->to_array()),"delete HR BU Approval for non HR Related Actovity");
										}
										$data= array("activity"=>"Operation Related");
									}
									$data['iscapex'] = $Rfcactivity->iscapexrelated;
									$Rfc =Rfc::find($id);
									$Rfc->activity_id =$idActivity;
									$Rfc->isprojectcapex =($Rfcactivity->iscapexrelated==1)?true:false;
									$Rfc->save();
									break;
								default:
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$query['username'])));
									$Rfc = Rfc::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Rfc as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Rfc));
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
							$Rfcnew = Rfc::find('first',array('select' => "CONCAT('RFC/KF','".$Employee->companycode."','/',YEAR(CURDATE()),'/',LPAD(MONTH(CURDATE()), 2, '0'),'/',LPAD(CASE when max(substring(rfcno,-4,4)) is null then 1 else max(substring(rfcno,-4,4))+1 end,4,'0')) as RfcNo","conditions"=>array("substring(rfcno,7,".strlen($Employee->companycode).")=? and substring(rfcno,".(strlen($Employee->companycode)+8).",4)=YEAR(CURDATE())",$Employee->companycode)));
							$data['rfcno']=$Rfcnew->rfcno;
							$data['companycode']=$Employee->companycode;
							$Rfc = Rfc::create($data);
							$data=$Rfc->to_array();
							$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";
							// if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,3)=="022") || ($Employee->department->sapcode=="13000090") || ($Employee->department->sapcode=="13000121")){
								// $Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='RFC' and tbl_approver.isactive='1' and approvaltype_id=4 and tbl_employee.location_id='1'")));
								// if(count($Approver2)>0){
									// $Rfcapproval = new Rfcapproval();
									// $Rfcapproval->rfc_id = $Rfc->id;
									// $Rfcapproval->approver_id = $Approver2->id;
									// $Rfcapproval->save();
								// }
							// }else{
								// $Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='RFC' and tbl_approver.isactive='1' and approvaltype_id='6' and tbl_employee.companycode=? and not(tbl_employee.id=?)  and tbl_employee.department_id=? ",$Employee->companycode,$Employee->id,$Employee->department_id)));
								// if(count($Approver)>0){
									// $Rfcapproval = new Rfcapproval();
									// $Rfcapproval->rfc_id = $Rfc->id;
									// $Rfcapproval->approver_id = $Approver->id;
									// $Rfcapproval->save();
								// }
								$ApproverBU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='RFC' and tbl_approver.isactive='1' and approvaltype_id='11' and tbl_employee.companycode=? and not(tbl_employee.id=?)",$Employee->companycode,$Employee->id)));
								if(count($ApproverBU)>0){
									$Rfcapproval = new Rfcapproval();
									$Rfcapproval->rfc_id = $Rfc->id;
									$Rfcapproval->approver_id = $ApproverBU->id;
									$Rfcapproval->save();
								}
								$companyFC=(($data['companycode']=='BCL') || ($data['companycode']=='KPA'))?"KPSI":$Employee->companycode;
								$ApproverBUFC = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='RFC' and tbl_approver.isactive='1' and approvaltype_id='10' and tbl_employee.companycode=? and not(tbl_employee.id=?)",$companyFC,$Employee->id)));
								if(count($ApproverBUFC)>0){
									$Rfcapproval = new Rfcapproval();
									$Rfcapproval->rfc_id = $Rfc->id;
									$Rfcapproval->approver_id = $ApproverBUFC->id;
									$Rfcapproval->save();
								}
								if(($data['companycode']=="IHM") || ($data['companycode']=='AHL') || ($data['companycode']=='KPS')|| ($data['companycode']=='KPA')){
									$ApproverHRDFU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='RFC' and tbl_approver.isactive='1' and approvaltype_id='9' and tbl_employee.companycode=?  and not(tbl_employee.id=?)",$Employee->companycode,$Employee->id)));
									if(count($ApproverHRDFU)>0){
										$Rfcapproval = new Rfcapproval();
										$Rfcapproval->rfc_id = $Rfc->id;
										$Rfcapproval->approver_id = $ApproverHRDFU->id;
										$Rfcapproval->save();
									}
								}
								$ApproverHRKF = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='RFC' and tbl_approver.isactive='1' and approvaltype_id='15' ")));
								if(count($ApproverHRKF)>0){
									$Rfcapproval = new Rfcapproval();
									$Rfcapproval->rfc_id = $Rfc->id;
									$Rfcapproval->approver_id = $ApproverHRKF->id;
									$Rfcapproval->save();
								}
								$ApproverCADKF = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='RFC' and tbl_approver.isactive='1' and approvaltype_id='8' ")));
								if(count($ApproverCADKF)>0){
									$Rfcapproval = new Rfcapproval();
									$Rfcapproval->rfc_id = $Rfc->id;
									$Rfcapproval->approver_id = $ApproverCADKF->id;
									$Rfcapproval->save();
								}
							// }
								$ApproverCADFU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='RFC' and tbl_approver.isactive='1' and approvaltype_id='7' and tbl_employee.companycode=?  and not(tbl_employee.id=?)",$Employee->companycode,$Employee->id)));
								if(count($ApproverCADFU)>0){
									$Rfcapproval = new Rfcapproval();
									$Rfcapproval->rfc_id = $Rfc->id;
									$Rfcapproval->approver_id = $ApproverCADFU->id;
									$Rfcapproval->save();
								}
							// }
								
							$Rfchistory = new Rfchistory();
							$Rfchistory->date = date("Y-m-d h:i:s");
							$Rfchistory->fullname = $Employee->fullname;
							$Rfchistory->approvaltype = "Originator";
							$Rfchistory->rfc_id = $Rfc->id;
							$Rfchistory->actiontype = 0;
							$Rfchistory->save();
							
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "CreateRfc";
							$err->errordate = date("Y-m-d h:i:s");
							$err->errormessage = $e->getMessage();
							$err->user = $this->currentUser->username;
							$err->ip = $this->ip;
							$err->save();
							$data = array("status"=>"error","message"=>$e->getMessage());
						}
						$logger = new Datalogger("Rfc","create",null,json_encode($data));
						$logger->SaveData();
						echo json_encode($data);									
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Rfc = Rfc::find($id);
						if ($Rfc->requeststatus==0){
							$approval = Rfcapproval::find("all",array('conditions' => array("rfc_id=?",$id)));
							foreach ($approval as $delr){
								$delr->delete();
							}
							$detail = Rfcdetail::find("all",array('conditions' => array("rfc_id=?",$id)));
							foreach ($detail as $delr){
								$delr->delete();
							}
							$hist = Rfchistory::find("all",array('conditions' => array("rfc_id=?",$id)));
							foreach ($hist as $delr){
								$delr->delete();
							}
							$data = $Rfc->to_array();
							$Rfc->delete();
							$logger = new Datalogger("Rfc","delete",json_encode($data),null);
							$logger->SaveData();
							echo json_encode($Rfc);
						} else {
							$data = array("status"=>"error","message"=>"You can't delete submitted request");
							echo json_encode($data);
						}
						
						break;
					case 'update':
						$id = $this->post['id'];
						$data = $this->post['data'];
						$joins   = "LEFT JOIN tbl_rfccontractor ON (tbl_rfc.contractor_id = tbl_rfccontractor.id) LEFT JOIN tbl_rfcactivity ON (tbl_rfc.activity_id = tbl_rfcactivity.id) ";
						$sel = 'tbl_rfc.*, tbl_rfccontractor.contractorname AS contractorname,tbl_rfcactivity.activitydescr as activitydescr ';
						$Rfc = Rfc::find($id,array('joins'=>$joins,'select'=>$sel,'include'=>array('employee'=>array('company','department','designation','grade'))));
						$olddata = $Rfc->to_array();
						$depthead = $data['depthead'];
						unset($data['approvalstatus']);
						//unset($data['employee']);
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
						if($depthead==$Employee->id){
							$result= array("status"=>"error","message"=>"You cannot select yourself as your Department Head");
							echo json_encode($result);
						}else{
							foreach($data as $key=>$val){
								if($key=='isprojectcapex'){
									$val=($val==0)?false:true;
								}
								$Rfc->$key=$val;
							}
							$Rfc->save();
							if (isset($data['depthead'])){
								// if(($Employee->level_id==4) || ($Employee->level_id==6) ){
								// }else{
									$joins   = "LEFT JOIN tbl_approver ON (tbl_rfcapproval.approver_id = tbl_approver.id) ";					
									$dx = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.approvaltype_id=6 and not(tbl_approver.employee_id=?)",$id,$depthead)));	
									foreach ($dx as $result) {
										//delete same type approver
										$result->delete();
										$logger = new Datalogger("Rfcapproval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
									}
									$joins   = "LEFT JOIN tbl_approver ON (tbl_rfcapproval.approver_id = tbl_approver.id) ";					
									$Rfcapproval = Rfcapproval::find('all',array('joins'=>$joins,'conditions' => array("rfc_id=? and tbl_approver.employee_id=?",$id,$depthead)));	
									foreach ($Rfcapproval as &$result) {
										$result		= $result->to_array();
										$result['no']=1;
									}			
									if(count($Rfcapproval)==0){ 
										$Approver = Approver::find('first',array('conditions'=>array("module='RFC' and employee_id=? and approvaltype_id=6",$depthead)));
										if(count($Approver)>0){
											$Rfcapproval = new Rfcapproval();
											$Rfcapproval->rfc_id = $Rfc->id;
											$Rfcapproval->approver_id = $Approver->id;
											$Rfcapproval->save();
										}else{
											$approver = new Approver();
											$approver->module = "RFC";
											$approver->employee_id=$depthead;
											$approver->sequence=1;
											$approver->approvaltype_id = 6;
											$approver->isfinal = false;
											$approver->save();
											$Rfcapproval = new Rfcapproval();
											$Rfcapproval->rfc_id = $Rfc->id;
											$Rfcapproval->approver_id = $approver->id;
											$Rfcapproval->save();
										}
									}
								// }
							}
							if($data['requeststatus']==1){
								$Rfcapproval = Rfcapproval::find('all', array('conditions' => array("rfc_id=?",$id)));					
								foreach($Rfcapproval as $data){
									$data->approvalstatus=0;
									$data->save();
								}
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_rfcapproval.approver_id = tbl_approver.id) ";					
								$Rfcapproval = Rfcapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and rfc_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								$username = $Rfcapproval->approver->employee->loginname;
								$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
								$email = $adb->email;
								$Rfcdetail=Rfcdetail::find('all',array('conditions'=>array("rfc_id=?",$id),'include'=>array('rfc'=>array('employee'=>array('company','department','designation','grade')))));
								foreach ($Rfcdetail as &$result) {
									$usr = Addressbook::find('first',array('conditions'=>array("username=?",$result->rfc->employee->loginname)));
									$email=$usr->email;
									$result = $result->to_array();
									$result['rfc']=$Rfc->to_array();
								}
								$Rfcterm=Rfcterm::find('all',array('conditions'=>array("rfc_id=?",$id)));
								$rfctype=array("New","Addendum","Project Capex");
								$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$adb->fullname.',</span></p>
													<p class=MsoNormal><span style="color:#1F497D">New RFC request is awaiting for your approval:</span></p>
													<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
													<table border=1 cellspacing=0 cellpadding=3 width=683>
														<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Rfc->employee->fullname.'</b></p></td></tr>
														<tr><td><p class=MsoNormal>Date</p></td><td>:</td><td><p class=MsoNormal><b>'.date("d/m/Y",strtotime($Rfc->createddate)).'</b></p></td></tr>
														<tr><td><p class=MsoNormal>RFC No</p></td><td>:</td><td><p class=MsoNormal><b>'.$Rfc->rfcno.'</b></p></td></tr>
														<tr><td><p class=MsoNormal>Kind of Contract</p></td><td>:</td><td><p class=MsoNormal><b>'.$Rfc->activitydescr.'</b></p></td></tr>
														<tr><td><p class=MsoNormal>Rate Type</p></td><td>:</td><td><p class=MsoNormal><b>'.$Rfc->ratetype.'</b></p></td></tr>
														<tr><td><p class=MsoNormal>Rate / SK No</p></td><td>:</td><td><p class=MsoNormal><b>'.$Rfc->skno.$Rfc->rate.'</b></p></td></tr>
														<tr><td><p class=MsoNormal>Period of Contract</p></td><td>:</td><td><p class=MsoNormal><b>'.date("d/m/Y",strtotime($Rfc->periodstart)).' - '.date("d/m/Y",strtotime($Rfc->periodend)).'</b></p></td></tr>
														<tr><td><p class=MsoNormal>Payment Term</p></td><td>:</td><td><p class=MsoNormal><b>'.$Rfc->paymentterm.'</b></p></td></tr>
														<tr><td><p class=MsoNormal>RFC Type</p></td><td>:</td><td><p class=MsoNormal><b>'.$rfctype[$Rfc->rfctype].'</b></p></td></tr>';
														if($Rfc->rfctype==1){
															$this->mailbody .='<tr><td><p class=MsoNormal>Old Contract No</td><td>:</td><td><p class=MsoNormal><b>'.$Rfc->oldcontractno.'</b></p></td></tr>';
														}
														if($Rfc->rfctype==2){
															$this->mailbody .='<tr><td colspan=3><p class=MsoNormal><b>Capex Information</b></p></td></tr>
																<tr><td><p class=MsoNormal>Capex No</p></td><td>:</td><td><p class=MsoNormal><b>'.$Rfc->capexno.'</b></p></td></tr>
																<tr><td><p class=MsoNormal>Capex Ammount</p></td><td>:</td><td><p class=MsoNormal><b> Rp.'.number_format($Rfc->capexammount).'</b></p></td></tr>
																<tr><td><p class=MsoNormal>Capex Spent</p></td><td>:</td><td><p class=MsoNormal><b> Rp.'.number_format($Rfc->capexspent).'</b></p></td></tr>
																<tr><td><p class=MsoNormal>Capex Balance</p></td><td>:</td><td><p class=MsoNormal><b> Rp.'.number_format($Rfc->capexbalance).'</b></p></td></tr>
																<tr><td><p class=MsoNormal>RFC Ammount</p></td><td>:</td><td><p class=MsoNormal><b> Rp.'.number_format($Rfc->rfcammount).'</b></p></td></tr>
																<tr><td><p class=MsoNormal>Balance after this RFC</p></td><td>:</td><td><p class=MsoNormal><b> Rp.'.number_format($Rfc->balance).'</b></p></td></tr>';
														}
								$this->mailbody .='</table>
													<p class=MsoNormal><b>Contract Detail :</b></p>
													<table border=1 cellspacing=0 cellpadding=3 width=683><tr><th><p class=MsoNormal>Description of Work</p></th></tr>
													';
								$no=1;
								foreach ($Rfcdetail as $data){
									$this->mailbody .='<tr style="height:22.5pt"><td><p class=MsoNormal>'.$no.'. '.$data['description'].'</p></td></tr>';
									$no++;
								}
								$this->mailbody .='<tr><td></td></tr>';
								$this->mailbody .='<tr><th><p class=MsoNormal>Other Term & Condition</p></th></tr>';
								$no="a";
								foreach ($Rfcterm as $data){
										$this->mailbody .= '<tr style="height:22.5pt"><td><p class=MsoNormal>'.$no.'. '.$data->term.'</p></td></tr>';
										$no++;
								}
								$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
								$this->mail->addAddress($adb->email, $adb->fullname);
								$this->mail->Subject = "Online Approval System -> New RFC Submission";
								$this->mail->msgHTML($this->mailbody);
								if (!$this->mail->send()) {
									$err = new Errorlog();
									$err->errortype = "RFC Mail";
									$err->errordate = date("Y-m-d h:i:s");
									$err->errormessage = $this->mail->ErrorInfo;
									$err->user = $this->currentUser->username;
									$err->ip = $this->ip;
									$err->save();
									echo "Mailer Error: " . $this->mail->ErrorInfo;
								} else {
									echo "Message sent!";
								}
								$Rfchistory = new Rfchistory();
								$Rfchistory->date = date("Y-m-d h:i:s");
								$Rfchistory->fullname = $Employee->fullname;
								$Rfchistory->rfc_id = $id;
								$Rfchistory->approvaltype = "Originator";
								$Rfchistory->actiontype = 2;
								$Rfchistory->save();
							}else{
								$Rfchistory = new Rfchistory();
								$Rfchistory->date = date("Y-m-d h:i:s");
								$Rfchistory->fullname = $Employee->fullname;
								$Rfchistory->rfc_id = $id;
								$Rfchistory->approvaltype = "Originator";
								$Rfchistory->actiontype = 1;
								$Rfchistory->save();
							}
							$logger = new Datalogger("RFC","update",json_encode($olddata),json_encode($data));
							$logger->SaveData();
							//echo json_encode($Rfc);
						}
						break;
					default:
						$Rfc = Rfc::all();
						foreach ($Rfc as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Rfc, JSON_NUMERIC_CHECK);
						break;
				}
			}
			// else{
				// $result= array("status"=>"error","message"=>"Authentication error or expired, please refresh and re login application");
				// echo json_encode($result);
			// }
		}
	}
	function rfcByEmp(){	
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
							$Rfc = Rfc::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Rfc as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Rfc, JSON_NUMERIC_CHECK);
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
									$Rfc = Rfc::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Rfc as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Rfc));
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
							$Rfc = Rfc::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Rfc as &$result) {
								$fullname=$result->employee->fullname;
								$result = $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Rfc, JSON_NUMERIC_CHECK);
						}else{
							echo json_encode(array());
						}
						break;
				}
			}
		}
	}
}