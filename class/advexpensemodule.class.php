<?php
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

Class Advexpensemodule extends Application{
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
		//$this->mail->addReplyTo('Purwanto_ihm@itci-hutani.com', 'Purwanto');
		$this->mailbody = '<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:m="http://schemas.microsoft.com/office/2004/12/omml" xmlns="http://www.w3.org/TR/REC-html40"><head><meta http-equiv=Content-Type content="text/html; charset=us-ascii"><meta name=Generator content="Microsoft Word 15 (filtered medium)"><style><!--
						/* Font Definitions */
						@font-face {font-family:Wingdings; panose-1:5 0 0 0 0 0 0 0 0 0;} @font-face {font-family:"Cambria Math"; panose-1:2 4 5 3 5 4 6 3 2 4;} @font-face {font-family:Calibri; panose-1:2 15 5 2 2 2 4 3 2 4;} @font-face {font-family:"Century Gothic"; panose-1:2 11 5 2 2 2 2 2 2 4;}
						/* Style Definitions */
						p.MsoNormal, li.MsoNormal, div.MsoNormal {margin:0in; margin-bottom:.0001pt; font-size:11.0pt; font-family:"Calibri","sans-serif";} a:link, span.MsoHyperlink {mso-style-priority:99; color:#0563C1; text-decoration:underline;} a:visited, span.MsoHyperlinkFollowed {mso-style-priority:99; color:#954F72; text-decoration:underline;} span.EmailStyle17 {mso-style-type:personal-reply;	font-family:"Calibri","sans-serif";	color:#1F497D;} .MsoChpDefault {mso-style-type:export-only;} @page WordSection1 {size:8.5in 11.0in;margin:1.0in 1.0in 1.0in 1.0in;} div.WordSection1 {page:WordSection1;} --></style><!--[if gte mso 9]><xml><o:shapedefaults v:ext="edit" spidmax="1026" /></xml><![endif]--><!--[if gte mso 9]><xml><o:shapelayout v:ext="edit"><o:idmap v:ext="edit" data="1" /></o:shapelayout></xml><![endif]--></head>';
		if (isset($this->get)){
			switch ($this->get['action']){
				case 'apiadvexpensebyemp':
					$this->advexpenseByEmp();
					break;
				case 'apiadvexpense':
					$this->advexpense();
					break;
				case 'apiadvexpensedetail':
					$this->advexpenseDetail();
					break;
				case 'apiadvexpenseapp':
					$this->advexpenseApproval();
					break;
				case 'apiadvexpensehist':
					$this->advexpenseHistory();
					break;
				case 'apiadvexpensepdf':	
					$id = $this->get['id'];
					$this->generatePDFi($id);
					break;
				case 'apiadvexpensefile':
					$this->advexpenseAttachment();
					break;
				case 'uploadadvexpensefile':
					$this->uploadadvexpenseFile();
					break;
				default:
					break;
			}
		}
	}
	

	function advexpenseByEmp(){	
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
							$Advexpense = Advexpense::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Advexpense as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Advexpense, JSON_NUMERIC_CHECK);
						}else{
							echo json_encode(array());
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							switch ($query['status']){
								case 'waiting':
									$Advexpense = Advexpense::find('all', array('conditions' => array("employee_id=? and RequestStatus<3 and id<>?",$query['username'],$query['id']),'include' => array('employee')));
									foreach ($Advexpense as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Advexpense));
									break;
								default:
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
									$Advexpense = Advexpense::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Advexpense as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Advexpense));
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
							$Advexpense = Advexpense::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Advexpense as &$result) {
								$fullname=$result->employee->fullname;
								$result = $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Advexpense, JSON_NUMERIC_CHECK);
						}else{
							echo json_encode(array());
						}
						break;
				}
			}
		}
	}
	
	function advexpense(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'byid':
						$id = $this->post['id'];
						$Advexpense = Advexpense::find($id, array('include' => array('employee'=>array('company','department','designation'))));

						// $Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND requeststatus=5",$Advexpense->employee->id)));
				
						// $AdvanceDetail = AdvanceDetail::find('all',array('conditions'=> array("advance_id=?",$Advance->id)));
						// foreach ($AdvanceDetail as &$data) {
						// 	$val_tamount += $data->amount;
						// }
						
						// echo number_format($val_tamount);
						// echo json_encode($AdvanceDetail, JSON_NUMERIC_CHECK);
						if ($Advexpense){
							$fullname = $Advexpense->employee->fullname;
							$costcenter = $Advexpense->employee->costcenter;
							$bg = $Advexpense->employee->companycode;
							$department = $Advexpense->employee->department->departmentname;
							$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Advexpense->employee->loginname)));

							$data=$Advexpense->to_array();
							$data['name']=$fullname;
							$data['email']=$usr->email;
							$data['costcenter']=$costcenter;
							$data['bg']=$bg;

							echo json_encode($data, JSON_NUMERIC_CHECK);
						}else{
							$Advexpense = new Advexpense();
							echo json_encode($Advexpense);
						}
						break;
					case 'find':
						$query=$this->post['query'];					
						if(isset($query['status'])){
							switch ($query['status']){
								case "last":
									break;
									case 'appcon':
										$valamount = $query['valamount'];
										$advexpense_form = $query['formtype'];
										$employee_id = $query['employee_id'];
										$id= $query['advexpense_id'];

										$Advexpense = Advexpense::find($id);

										$Employee = Employee::find('first', array('conditions' => array("id=?",$employee_id),"include"=>array("location","department","company")));

										print_r($valamount);

										$joins   = "LEFT JOIN tbl_approver ON (tbl_advexpenseapproval.approver_id = tbl_approver.id) LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id)";
										$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";	
										// if (($valamount=='2') || ($valamount=='3')){
										$AdvexpenseapprovalBU = Advexpenseapproval::find('all',array('joins'=>$joins,'conditions' => array("advexpense_id=? and tbl_approver.approvaltype_id='38' ",$id)));	
										foreach ($AdvexpenseapprovalBU as &$result) {
											$result		= $result->to_array();
											$result['no']=1;
										}
										$Advexpenseapprovaldepmd = Advexpenseapproval::find('all',array('joins'=>$joins,'conditions' => array("advexpense_id=? and tbl_approver.approvaltype_id='39' ",$id)));	
										foreach ($Advexpenseapprovaldepmd as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}
										$Advexpenseapprovalmd = Advexpenseapproval::find('all',array('joins'=>$joins,'conditions' => array("advexpense_id=? and tbl_approver.approvaltype_id='40' ",$id)));	
										foreach ($Advexpenseapprovalmd as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}

										$Advexpenseapprovalproc = Advexpenseapproval::find('all',array('joins'=>$joins,'conditions' => array("advexpense_id=? and tbl_approver.approvaltype_id='42' ",$id)));	
										foreach ($Advexpenseapprovalproc as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}

									break;

									case 'appform':
										$advexpense_form = $query['formtype'];
										$employee_id = $query['employee_id'];
										$id= $query['advexpense_id'];

										
										$Advexpense = Advexpense::find($id, array('include' => array('employee'=>array('company','department','designation'))));

										
										$Employee = Employee::find('first', array('conditions' => array("id=?",$employee_id),"include"=>array("location","department","company")));

										// print_r($advexpense_form);
										$data['companycode']=$Employee->companycode;

										$joins   = "LEFT JOIN tbl_approver ON (tbl_advexpenseapproval.approver_id = tbl_approver.id) LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id)";
										$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";	

										

										$Advexpenseapprovalhrd = Advexpenseapproval::find('all',array('joins'=>$joins,'conditions' => array("advexpense_id=? and tbl_approver.approvaltype_id='36' ",$id)));	
										foreach ($Advexpenseapprovalhrd as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}
										$Advexpenseapprovalproc = Advexpenseapproval::find('all',array('joins'=>$joins,'conditions' => array("advexpense_id=? and tbl_approver.approvaltype_id='42' ",$id)));	
										foreach ($Advexpenseapprovalproc as &$result) {
											$result		= $result->to_array();
											$result['no']=1;

										}

										if($advexpense_form == 1) {

											//check lessadvance
											$Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND requeststatus=5 AND advanceform=1",$Advexpense->employee->id)));

											$AdvanceDetail = AdvanceDetail::find('all',array('conditions'=> array("advance_id=?",$Advance->id)));
											foreach ($AdvanceDetail as $val) {
												$val_tamount += $val->amount;
											}

											if($Advance) {
												$item['message']=200;
												$item['lessadvance']=$val_tamount;
												
												echo json_encode($item, JSON_NUMERIC_CHECK);
											} else {
												$item['message']=404;
												echo json_encode($item, JSON_NUMERIC_CHECK);
											}

											// $companyHRV=( ($Employee->companycode=='KPA') || ($Employee->companycode=='AHL') )?"KPSI":$Employee->companycode;
											// if (($Employee->company->sapcode=='RND') || ($Employee->company->sapcode=='NKF')){
											// if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220") || ($Employee->department->sapcode=="13000090") || ($Employee->department->sapcode=="13000121") || ($Employee->company->sapcode=="NKF") || ($Employee->company->sapcode=="RND")){
											$hrverifikator = Advexpenseapproval::find('all',array('joins'=>$joins,'conditions' => array("advexpense_id=? and tbl_approver.approvaltype_id=44",$id)));	
											foreach ($hrverifikator as $result) {
												$result->delete();
												$logger = new Datalogger("Advexpenseapproval","delete",json_encode($result->to_array()),"delete Approval HR Verifikator");
												$logger->SaveData();
											}
											
											if(($data['companycode']=="IHM" || $Employee->company->sapcode=='RND' || $Employee->company->sapcode=='NKF')  && (substr(strtolower($Employee->location->sapcode),0,4)=="0250")){
												$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and tbl_employee.location_id='8'")));
											}else if(($data['companycode']=="AHL" || $Employee->company->sapcode=='RND' || $Employee->company->sapcode=='NKF') && (substr(strtolower($Employee->location->sapcode),0,4)=="0210")){
												$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and tbl_employee.location_id='3'")));
											}else {
												$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and tbl_employee.location_id='1'")));
											}

											

											if(count($ApproverHRV)>0){
												$Advexpenseapproval = new Advexpenseapproval();
												$Advexpenseapproval->advexpense_id = $Advexpense->id;
												$Advexpenseapproval->approver_id = $ApproverHRV->id;
												$Advexpenseapproval->save();
												$logger = new Datalogger("Advexpenseapproval","add","Add initial HR Verifikator Approval ",json_encode($Advexpenseapproval->to_array()));
												$logger->SaveData();
											}

											//add approver
											// if(($data['companycode']=="IHM") || ($data['companycode']=='AHL') || ($data['companycode']=='KPS') || ($data['companycode']=='KPSI') || ($data['companycode']=='KPA')){

											// 	if((substr(strtolower($Employee->location->sapcode),0,4)=="0200")) {
											// 		$ApproverHRDFU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='36' and tbl_employee.location_id='1' and not(tbl_employee.id=?)",$Employee->id)));
											// 	} else {
											// 		$ApproverHRDFU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='36' and tbl_employee.companycode=?  and not(tbl_employee.id=?)",$Employee->companycode,$Employee->id)));
											// 	}
											// 	$hrd = Advexpenseapproval::find('all',array('joins'=>$joins,'conditions' => array("advexpense_id=? and tbl_approver.approvaltype_id=36",$id)));	
											// 	foreach ($hrd as $result) {
											// 		$result->delete();
											// 		$logger = new Datalogger("Advexpenseapproval","delete",json_encode($result->to_array()),"delete Approval HRD");
											// 		$logger->SaveData();
											// 	}
													
											// 	if(count($ApproverHRDFU)>0){

											// 		$Advexpenseapproval = new Advexpenseapproval();
											// 		$Advexpenseapproval->advexpense_id = $Advexpense->id;
											// 		$Advexpenseapproval->approver_id = $ApproverHRDFU->id;
											// 		$Advexpenseapproval->save();
											// 		$logger = new Datalogger("Advexpenseapproval","add","Add initial HRD Approval ",json_encode($Advexpenseapproval->to_array()));
											// 		$logger->SaveData();
													
											// 	}
											// }
											$buhead = Advexpenseapproval::find('all',array('joins'=>$joins,'conditions' => array("advexpense_id=? and tbl_approver.approvaltype_id=38",$id)));	
											foreach ($buhead as $result) {
												$result->delete();
												$logger = new Datalogger("Advexpenseapproval","delete",json_encode($result->to_array()),"delete Approval BU HEAD");
												$logger->SaveData();
											}

											$hrd = Advexpenseapproval::find('all',array('joins'=>$joins,'conditions' => array("advexpense_id=? and tbl_approver.approvaltype_id=36",$id)));	
											foreach ($hrd as $result) {
												$result->delete();
												$logger = new Datalogger("Advexpenseapproval","delete",json_encode($result->to_array()),"delete Approval HRD");
												$logger->SaveData();
											}

											if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220") || ($Employee->department->sapcode=="13000090") || ($Employee->department->sapcode=="13000121") || ($Employee->company->sapcode=="NKF") || ($Employee->company->sapcode=="RND")){
												
												
												$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=36 and tbl_employee.location_id='1'")));
												if(count($Approver2)>0){
													$Advexpenseapproval = new Advexpenseapproval();
													$Advexpenseapproval->advexpense_id = $Advexpense->id;
													$Advexpenseapproval->approver_id = $Approver2->id;
													$Advexpenseapproval->save();
												}
													
											}else{
												
												$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=36 and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
												if(count($Approver2)>0){
													$Advexpenseapproval = new Advexpenseapproval();
													$Advexpenseapproval->advexpense_id = $Advexpense->id;
													$Advexpenseapproval->approver_id = $Approver2->id;
													$Advexpenseapproval->save();
												}
											}
											

											

										} else if($advexpense_form == 2) {

											//check lessadvance
											$Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND requeststatus=5 AND advanceform=2",$Advexpense->employee->id)));

											$AdvanceDetail = AdvanceDetail::find('all',array('conditions'=> array("advance_id=?",$Advance->id)));
											foreach ($AdvanceDetail as $val) {
												$val_tamount += $val->amount;
											}

											if($Advance) {
												$item['message']=200;
												$item['lessadvance']=$val_tamount;
												echo json_encode($item, JSON_NUMERIC_CHECK);
											} else {
												$item['message']=404;
												echo json_encode($item, JSON_NUMERIC_CHECK);
											}

											$hrd = Advexpenseapproval::find('all',array('joins'=>$joins,'conditions' => array("advexpense_id=? and tbl_approver.approvaltype_id=36",$id)));	
											foreach ($hrd as $result) {
												$result->delete();
												$logger = new Datalogger("Advexpenseapproval","delete",json_encode($result->to_array()),"delete Approval HRD");
												$logger->SaveData();
											}

											$hrv = Advexpenseapproval::find('all',array('joins'=>$joins,'conditions' => array("advexpense_id=? and tbl_approver.approvaltype_id=44",$id)));	
											foreach ($hrv as $result) {
												$result->delete();
												$logger = new Datalogger("Advexpenseapproval","delete",json_encode($result->to_array()),"delete Approval HR Verifikator");
												$logger->SaveData();
											}

											$companyBU=( ($Employee->companycode=='KPA') || ($Employee->companycode=='AHL') )?"KPSI":$Employee->companycode;
											if (($Employee->company->sapcode=='RND') || ($Employee->company->sapcode=='NKF')){
												$ApproverBU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and tbl_employee.company_id=? and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
											}else{
												$ApproverBU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and tbl_employee.companycode=? and not(tbl_employee.id=?)",$companyBU,$Employee->id)));
											}
											if(count($ApproverBU)>0){
												$Advexpenseapproval = new Advexpenseapproval();
												$Advexpenseapproval->advexpense_id = $Advexpense->id;
												$Advexpenseapproval->approver_id = $ApproverBU->id;
												$Advexpenseapproval->save();
												$logger = new Datalogger("Advexpenseapproval","add","Add initial BU Head Approval ",json_encode($Advexpenseapproval->to_array()));
												$logger->SaveData();
											}
										}


									break;
								default:
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$query['username'])));
									$Advexpense = Advexpense::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Advexpense as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Advexpense));
									break;
							}
						} else{
							$data=array();
						}
						// echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':		
						$data = $this->post['data'];
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$data['username']),"include"=>array("location","company","department")));
							unset($data['__KEY__']);
							unset($data['username']);
							$data['employee_id']=$Employee->id;
							$data['RequestStatus']=0;
							try{
								$Advexpense = Advexpense::create($data);
								$data['companycode']=$Employee->companycode;
								$data=$Advexpense->to_array();
								
								$joins   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";

								// $ApproverFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=41")));
								// if(count($ApproverFC)>0){
								// 	$Advexpenseapproval = new Advexpenseapproval();
								// 	$Advexpenseapproval->advexpense_id = $Advexpense->id;
								// 	$Advexpenseapproval->approver_id = $ApproverFC->id;
								// 	$Advexpenseapproval->save();
								// }

								

								$companyFC=(($data['companycode']=='BCL') || ($data['companycode']=='KPA'))?"KPSI":((($data['companycode']=='KPSI'))?"LDU":$Employee->companycode);
								$ApproverBUFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37' and tbl_employee.companycode=? and not(tbl_employee.id=?)",$companyFC,$Employee->id)));
								if(count($ApproverBUFC)>0){
									$Advexpenseapproval = new Advexpenseapproval();
									$Advexpenseapproval->advexpense_id = $Advexpense->id;
									$Advexpenseapproval->approver_id = $ApproverBUFC->id;
									$Advexpenseapproval->save();
									$logger = new Datalogger("Advexpenseapproval","add","Add initial BU FC Approval",json_encode($Advexpenseapproval->to_array()));
									$logger->SaveData();
								}

								// if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220")){
								// 	$ApproverBU = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and tbl_employee.location_id='1'")));
								// 	if(count($ApproverBU)>0){
								// 		$Advexpenseapproval = new Advexpenseapproval();
								// 		$Advexpenseapproval->advexpense_id =$Advexpense->id;
								// 		$Advexpenseapproval->approver_id = $ApproverBU->id;
								// 		$Advexpenseapproval->save();
								// 		$logger = new Datalogger("Advexpenseapproval","add","Add initial BU Approval",json_encode($Advexpenseapproval->to_array()));
								// 		$logger->SaveData();
								// 	}
									
								// 	$ApproverBUFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37' and tbl_employee.location_id='1'")));
								// 	if(count($ApproverBUFC)>0){
								// 		$Advexpenseapproval = new Advexpenseapproval();
								// 		$Advexpenseapproval->advexpense_id =$Advexpense->id;
								// 		$Advexpenseapproval->approver_id = $ApproverBUFC->id;
								// 		$Advexpenseapproval->save();
								// 		$logger = new Datalogger("Advexpenseapproval","add","Add initial BUFC Approval",json_encode($Advexpenseapproval->to_array()));
								// 		$logger->SaveData();
								// 	}

								// }else{
								// 	$ApproverBU = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38'  and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
								// 	if(count($ApproverBU)>0){
								// 		$Advexpenseapproval = new Advexpenseapproval();
								// 		$Advexpenseapproval->advexpense_id = $Advexpense->id;
								// 		$Advexpenseapproval->approver_id = $ApproverBU->id;
								// 		$Advexpenseapproval->save();
								// 		$logger = new Datalogger("Advexpenseapproval","add","Add initial BU Approval",json_encode($Advexpenseapproval->to_array()));
								// 		$logger->SaveData();
								// 	}

								// 	$ApproverBUFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37'  and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
								// 	if(count($ApproverBUFC)>0){
								// 		$Advexpenseapproval = new Advexpenseapproval();
								// 		$Advexpenseapproval->advexpense_id = $Advexpense->id;
								// 		$Advexpenseapproval->approver_id = $ApproverBUFC->id;
								// 		$Advexpenseapproval->save();
								// 		$logger = new Datalogger("Advexpenseapproval","add","Add initial BUFC Approval",json_encode($Advexpenseapproval->to_array()));
								// 		$logger->SaveData();
								// 	}

								// }

								$Advexpensehistory = new Advexpensehistory();
								$Advexpensehistory->date = date("Y-m-d h:i:s");
								$Advexpensehistory->fullname = $Employee->fullname;
								$Advexpensehistory->approvaltype = "Originator";
								$Advexpensehistory->advexpense_id = $Advexpense->id;
								$Advexpensehistory->actiontype = 0;
								$Advexpensehistory->save();
								
							}catch (Exception $e){
								$err = new Errorlog();
								$err->errortype = "CreateAdvexpense";
								$err->errordate = date("Y-m-d h:i:s");
								$err->errormessage = $e->getMessage();
								$err->user = $this->currentUser->username;
								$err->ip = $this->ip;
								$err->save();
								$data = array("status"=>"error","message"=>$e->getMessage());
							}
							$logger = new Datalogger("Advexpense","create",null,json_encode($data));
							$logger->SaveData();

						echo json_encode($data);									
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Advexpense = Advexpense::find($id);
						if ($Advexpense->requeststatus==0){
							try {
								$approval = Advexpenseapproval::find("all",array('conditions' => array("advexpense_id=?",$id)));
								foreach ($approval as $delr){
									$delr->delete();
								}
								$approval = Advexpenseattachment::find("all",array('conditions' => array("advexpense_id=?",$id)));
								foreach ($approval as $delr){
									$delr->delete();
								}
								$detail = Advexpensedetail::find("all",array('conditions' => array("advexpense_id=?",$id)));
								foreach ($detail as $delr){
									$delr->delete();
								}
								$hist = Advexpensehistory::find("all",array('conditions' => array("advexpense_id=?",$id)));
								foreach ($hist as $delr){
									$delr->delete();
								}
								$data = $Advexpense->to_array();
								$Advexpense->delete();
								$logger = new Datalogger("Advexpense","delete",json_encode($data),null);
								$logger->SaveData();
								$data = array("status"=>"success","message"=>"Data has been deleted");
							}catch (Exception $e){
								$err = new Errorlog();
								$err->errortype = "DeleteAdvexpense";
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
						$Advexpense = Advexpense::find($id,array('include'=>array('employee'=>array('company','department','designation','grade'))));
						$olddata = $Advexpense->to_array();
						$superior = $data['superior'];
						unset($data['approvalstatus']);
						unset($data['fullname']);
						unset($data['department']);
						//unset($data['employee']);
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
						foreach($data as $key=>$val){
							$value=(($val===0) || ($val==='0') || ($val==='false'))?false:((($val===1) || ($val==='1') || ($val==='true'))?true:$val);
							$Advexpense->$key=$value;
						}
						$Advexpense->save();
						
						if (isset($data['superior'])){
							$joins   = "LEFT JOIN tbl_approver ON (tbl_advexpenseapproval.approver_id = tbl_approver.id) ";					
							$dx = Advexpenseapproval::find('all',array('joins'=>$joins,'conditions' => array("advexpense_id=? and tbl_approver.approvaltype_id=35 and not(tbl_approver.employee_id=?)",$id,$superior)));	
							foreach ($dx as $result) {
								//delete same type approver
								$result->delete();
								$logger = new Datalogger("Advexpenseapproval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
							}				
							$Advexpenseapproval = Advexpenseapproval::find('all',array('joins'=>$joins,'conditions' => array("advexpense_id=? and tbl_approver.employee_id=?",$id,$superior)));	
							foreach ($Advexpenseapproval as &$result) {
								$result		= $result->to_array();
								$result['no']=1;
							}			
							if(count($Advexpenseapproval)==0){ 
								$Approver = Approver::find('first',array('conditions'=>array("module='Advance' and employee_id=? and approvaltype_id=35",$superior)));
								if(count($Approver)>0){
									$Advexpenseapproval = new Advexpenseapproval();
									$Advexpenseapproval->advexpense_id = $Advexpense->id;
									$Advexpenseapproval->approver_id = $Approver->id;
									$Advexpenseapproval->save();
								}else{
									$approver = new Approver();
									$approver->module = "Advexpense";
									$approver->employee_id=$superior;
									$approver->sequence=0;
									$approver->approvaltype_id = 35;
									$approver->isfinal = false;
									$approver->save();
									$Advexpenseapproval = new Advexpenseapproval();
									$Advexpenseapproval->advexpense_id = $Advexpense->id;
									$Advexpenseapproval->approver_id = $approver->id;
									$Advexpenseapproval->save();
								}
							}
						}
						if($data['requeststatus']==1){
							$Advexpenseapproval = Advexpenseapproval::find('all', array('conditions' => array("advexpense_id=?",$id)));					
							foreach($Advexpenseapproval as $data){
								$data->approvalstatus=0;
								$data->save();
							}
							$joinx   = "LEFT JOIN tbl_approver ON (tbl_advexpenseapproval.approver_id = tbl_approver.id) ";					
							$Advexpenseapproval = Advexpenseapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and advexpense_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
							$username = $Advexpenseapproval->approver->employee->loginname;
							$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
							$email = $adb->email;
							$title = 'Expense';
							// $Advexpensedetail=Advexpensedetail::find('all',array('conditions'=>array("advexpense_id=?",$id),'include'=>array('advexpense','employee'=>array('company','department','designation','grade'))));
							$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$adb->fullname.',</span></p>
										<p class=MsoNormal><span style="color:#1F497D">new '.$title.' Request is awaiting for your approval:</span></p>
										<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
										<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advexpense->employee->fullname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advexpense->employee->sapid.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advexpense->employee->designation->designationname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advexpense->employee->company->companyname.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advexpense->employee->location->location.'</b></p></td></tr>
										<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
										</table>
										<br>

										';
							if($Advexpense->expenseform == 1) {
								$form = "Expense Req HR";
							} else if($Advexpense->expenseform == 2){
								$form = "Expense Req OPR";
							}

							if($Advexpense->expensetype == 0) {
								$less = 0;
							} else {
								$less = $Advexpense->lessadvance;
							}

							if($Advexpense->expense == 1) {
								$expenseM = "Cash";
							} else if($Advexpense->expense == 2) {
								$expenseM = "Bank";
							}

							$Advexpensedetail = Advexpensedetail::find('all',array('conditions'=>array("advexpense_id=?",$id),'include'=>array('advexpense'=>array('employee'=>array('company','department','designation','grade','location')))));	

							$this->mailbody .='
								<table border=1 cellspacing=0 cellpadding=3 width=683>
								<tr>
									<th><p class=MsoNormal>Expense Form</p></th>
									<th><p class=MsoNormal>Expense Method</p></th>
									<th><p class=MsoNormal>Beneficiary</p></th>
									<th><p class=MsoNormal>Account Name</p></th>
									<th><p class=MsoNormal>Bank</p></th>
									<th><p class=MsoNormal>Account Number</p></th>
									<th><p class=MsoNormal>Due Date</p></th>
									<th><p class=MsoNormal>Expense Date</p></th>
									<th><p class=MsoNormal>Remarks</p></th>
								</tr>
								<tr style="height:22.5pt">
									<td><p class=MsoNormal> '.$form.'</p></td>
									<td><p class=MsoNormal> '.$expenseM.'</p></td>
									<td><p class=MsoNormal> '.$Advexpense->beneficiary.'</p></td>
									<td><p class=MsoNormal> '.$Advexpense->accountname.'</p></td>
									<td><p class=MsoNormal> '.$Advexpense->bank.'</p></td>
									<td><p class=MsoNormal> '.$Advexpense->accountnumber.'</p></td>
									<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Advexpense->duedate)).'</p></td>
									<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Advexpense->expensedate)).'</p></td>
									<td><p class=MsoNormal> '.$Advexpense->remarks.'</p></td>
								</tr>
								</table>
								<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><th><p class=MsoNormal>No</p></th>
											<th><p class=MsoNormal>Description</p></th>
											<th><p class=MsoNormal>Account Code</p></th>
											<th><p class=MsoNormal>Amount</p></th>
										</tr>
							';
							$no=1;
							foreach ($Advexpensedetail as $data){
								$val_tamount += $data->amount;
								$this->mailbody .='<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.$no.'</p></td>
											<td><p class=MsoNormal> '.$data->description.'</p></td>
											<td><p class=MsoNormal> '.$data->accountcode.'</p></td>
											<td><p class=MsoNormal> '.number_format($data->amount).'</p></td>
								</tr>';
								$no++;
							}
							$this->mailbody .='</table>
							<ul>
								<li><b><span>Total Amount : '.number_format($val_tamount).'</span></b></li>
								<li><b><span>Less Advance : '.number_format($less).'</span></b></li>
								<li><b><span>Balance To Be Paid : '.number_format($val_tamount-$less).'</span></b></li>
							</ul>
							<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
							$this->mail->addAddress($adb->email, $adb->fullname);
							$this->mail->Subject = "Online Approval System -> Advexpense";
							$this->mail->msgHTML($this->mailbody);
							if (!$this->mail->send()) {
								$err = new Errorlog();
								$err->errortype = "Advexpense Mail";
								$err->errordate = date("Y-m-d h:i:s");
								$err->errormessage = $this->mail->ErrorInfo;
								$err->user = $this->currentUser->username;
								$err->ip = $this->ip;
								$err->save();
								echo "Mailer Error: " . $this->mail->ErrorInfo;
							} else {
								echo "Message sent!";
							}

							// if($Advexpense->expenseform == 2) {

							// 	$dx = Advexpenseapproval::find('all',array('joins'=>$joins,'conditions' => array("advexpense_id=? and tbl_approver.approvaltype_id=36",$id)));	
							// 	foreach ($dx as $result) {
							// 		$result->delete();
							// 		$logger = new Datalogger("Advexpenseapproval","delete",json_encode($result->to_array()),"delete Approval Advexpense");
							// 		$logger->SaveData();
							// 	}

							// 	if($val_tamount >= 5000000) {

							// 		$ApproverProc = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='42' and tbl_employee.location_id='1'")));
							// 		if(count($ApproverProc)>0){
							// 			$Advexpenseapproval = new Advexpenseapproval();
							// 			$Advexpenseapproval->advexpense_id =$Advexpense->id;
							// 			$Advexpenseapproval->approver_id = $ApproverProc->id;
							// 			$Advexpenseapproval->save();
							// 			$logger = new Datalogger("Advexpenseapproval","add","Add initial Proc Approval",json_encode($Advexpenseapproval->to_array()));
							// 			$logger->SaveData();
							// 		}
									
							// 	}


								
							// }

							$Advexpensehistory = new Advexpensehistory();
							$Advexpensehistory->date = date("Y-m-d h:i:s");
							$Advexpensehistory->fullname = $Employee->fullname;
							$Advexpensehistory->advexpense_id = $id;
							$Advexpensehistory->approvaltype = "Originator";
							$Advexpensehistory->actiontype = 2;
							$Advexpensehistory->save();
						}else{
							$Advexpensehistory = new Advexpensehistory();
							$Advexpensehistory->date = date("Y-m-d h:i:s");
							$Advexpensehistory->fullname = $Employee->fullname;
							$Advexpensehistory->advexpense_id = $id;
							$Advexpensehistory->approvaltype = "Originator";
							$Advexpensehistory->actiontype = 1;
							$Advexpensehistory->save();
						}
						$logger = new Datalogger("Advexpense","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						//echo json_encode($Advexpense);
						
						break;
					default:
						$Advexpense = Advexpense::all();
						foreach ($Advexpense as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Advexpense, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}

	function advexpenseApproval(){
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
							$join   = "LEFT JOIN tbl_approver ON (tbl_advexpenseapproval.approver_id = tbl_approver.id) ";
							$Advexpenseapproval = Advexpenseapproval::find('all', array('joins'=>$join,'conditions' => array("advexpense_id=?",$id),'include' => array('approver'=>array('approvaltype')),"order"=>"tbl_approver.sequence"));
							foreach ($Advexpenseapproval as &$result) {
								$approvaltype = $result->approver->approvaltype_id;
								$result		= $result->to_array();
								$result['approvaltype']=$approvaltype;
							}
							echo json_encode($Advexpenseapproval, JSON_NUMERIC_CHECK);
						}else{
							$Advexpenseapproval = new Advexpenseapproval();
							echo json_encode($Advexpenseapproval);
						}
						break;
					case 'find':
						$query=$this->post['query'];		
						if(isset($query['status'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join   = "LEFT JOIN tbl_approver ON (tbl_advexpenseapproval.approver_id = tbl_approver.id) ";
							$dx = Advexpenseapproval::find('first', array('joins'=>$join,'conditions' => array("advexpense_id=? and tbl_approver.employee_id = ?",$query['advexpense_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
							// print_r($dx);
							$Advexpense = Advexpense::find($query['advexpense_id']);
							// if($dx->approver->isfinal==1){
							if (($Advexpense->expenseform == 1 && $dx->approver->approvaltype_id == 37) || ($Advexpense->expenseform == 2 && $dx->approver->approvaltype_id == 38)){
								$data=array("jml"=>1);
							}else{
								$join   = "LEFT JOIN tbl_approver ON (tbl_advexpenseapproval.approver_id = tbl_approver.id) ";
								$Advexpenseapproval = Advexpenseapproval::find('all', array('joins'=>$join,'conditions' => array("advexpense_id=? and ApprovalStatus<=1 and not tbl_approver.employee_id=?",$query['advexpense_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
								foreach ($Advexpenseapproval as &$result) {
									$fullname	= $result->approver->employee->fullname;	
									$result		= $result->to_array();
									$result['fullname']=$fullname;
								}
								$data=array("jml"=>count($Advexpenseapproval));
							}						
						} else if(isset($query['pending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;

							// $Advexpense = Advexpense::find('all', array('conditions' => array("RequestStatus !=0"),'include' => array('employee')));
							$Advexpense = Advexpense::find('all', array('conditions' => array("RequestStatus=1"),'include' => array('employee')));
							// print_r($Advexpense);
							foreach ($Advexpense as $result) {
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_advexpenseapproval.approver_id = tbl_approver.id) ";					
								$Advexpenseapproval = Advexpenseapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and advexpense_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								// echo $Advexpenseapproval;
								if($Advexpenseapproval->approver->employee_id==$emp_id){
									$request[]=$result->id;
								}
							}
							$Advexpense = Advexpense::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
							foreach ($Advexpense as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=$Advexpense;
						} else if(isset($query['mypending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
							$Advexpense = Advexpense::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
							$jml=0;
							foreach ($Advexpense as $result) {
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_advexpenseapproval.approver_id = tbl_approver.id) ";					
								$Advexpenseapproval = Advexpenseapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and advexpense_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								if($Advexpenseapproval->approver->employee_id==$emp_id){
									$request[]=$result->id;
								}
							}
							$Advexpense = Advexpense::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
							foreach ($Advexpense as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=array("jml"=>count($Advexpense));
						} else if(isset($query['filter'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join = "LEFT JOIN vwadvexpensereport v on tbl_advexpense.id=v.id LEFT JOIN tbl_employee ON (tbl_advexpense.employee_id = tbl_employee.id) ";
							$sel = 'tbl_advexpense.*,v.personholding ';
							$Advexpense = Advexpense::find('all',array('joins'=>$join,'select'=>$sel,'include' => array('employee')));
							
							if($Employee->location->sapcode=='0200' || $this->currentUser->isadmin){
								$Advexpense = Advexpense::find('all',array('joins'=>$join,'select'=>$sel,'include' => array('employee'=>array('company','department'))));
							}else{
								$Advexpense = Advexpense::find('all',array('joins'=>$join,'select'=>$sel,'conditions' => array('tbl_advexpense.RequestStatus=3 or tbl_advexpense.RequestStatus=5 and tbl_employee.company_id=?',$Employee->company_id ),'include' => array('employee'=>array('company','department'))));
							}
							
							foreach ($Advexpense as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=$Advexpense;
						} else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Advexpenseapproval = Advexpenseapproval::create($data);
						$logger = new Datalogger("Advexpenseapproval","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':
						$id = $this->post['id'];
						$Advexpenseapproval = Advexpenseapproval::find($id);
						$data=$Advexpenseapproval->to_array();
						$Advexpenseapproval->delete();
						$logger = new Datalogger("Advexpenseapproval","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Advexpenseapproval);
						break;
					case 'update':
							$doid = $this->post['id'];
							$data = $this->post['data'];
							$mode= $data['mode'];
							$appstatus = $data['approvalstatus'];
							unset($data['id']);
							unset($data['superior']);
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
							$Advexpense = Advexpense::find($doid);
							$join   = "LEFT JOIN tbl_approver ON (tbl_advexpenseapproval.approver_id = tbl_approver.id) ";
							if (isset($data['mode'])){
								$Advexpenseapproval = Advexpenseapproval::find('first', array('joins'=>$join,'conditions' => array("advexpense_id=? and tbl_approver.employee_id=?",$doid,$Employee->id),'include' => array('approver'=>array('employee','approvaltype'))));
								unset($data['mode']);
							}else{
								$Advexpenseapproval = Advexpenseapproval::find($this->post['id'],array('include' => array('approver'=>array('employee','approvaltype'))));
							}
							foreach($data as $key=>$val) {
								if(($key !== 'approvalstatus') && ($key !== 'approvaldate') && ($key !== 'remarks') ) {
									// if(($key == 'isrepair') || ($key == 'isscrap')) {
										$value=(($val===0) || ($val==='0') || ($val==='false'))?false:((($val===1) || ($val==='1') || ($val==='true'))?true:$val);
									// }
									$Advexpense->$key=$value;
								}
							}
							
							$Advexpense->save();

							unset($data['expenseform']);
							unset($data['beneficiary']);
							unset($data['accountname']);
							unset($data['bank']);
							unset($data['accountnumber']);

							unset($data['duedate']);
							unset($data['expecteddate']);

							
							$olddata = $Advexpenseapproval->to_array();
							foreach($data as $key=>$val){
								$val=($val=='false')?false:(($val=='true')?true:$val);
								$Advexpenseapproval->$key=$val;
							}
							
							$Advexpenseapproval->save();
							$logger = new Datalogger("Advexpenseapproval","update",json_encode($olddata),json_encode($data));
							$logger->SaveData();
						if (isset($mode) && ($mode=='approve')){
								$Advexpense = Advexpense::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_advexpenseapproval.approver_id = tbl_approver.id) ";					
								$nAdvexpenseapproval = Advexpenseapproval::find('first',array('joins'=>$joinx,'conditions' => array("advexpense_id=? and ApprovalStatus=0",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								$username = $nAdvexpenseapproval->approver->employee->loginname;
								$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));

								$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Advexpense->employee->loginname)));
								$email=$usr->email;
								
								$complete = false;
								$Advexpensehistory = new Advexpensehistory();
								$Advexpensehistory->date = date("Y-m-d h:i:s");
								$Advexpensehistory->fullname = $Employee->fullname;
								$Advexpensehistory->approvaltype = $Advexpenseapproval->approver->approvaltype->approvaltype;
								$Advexpensehistory->remarks = $data['remarks'];
								$Advexpensehistory->advexpense_id = $doid;
								
								switch ($data['approvalstatus']){
									case '1':
										$Advexpense->requeststatus = 2;
										$emto=$email;$emname=$Advexpense->employee->fullname;
										$this->mail->Subject = "Online Approval System -> Need Rework";
										$red = 'Your Expense request require some rework : <br>Remarks from Approver : <font color="red">'.$data['remarks']."</font>";
										$Advexpensehistory->actiontype = 3;
										break;
									case '2':
										if (($Advexpense->expenseform == 1 && $Advexpenseapproval->approver->approvaltype_id == 37) || ($Advexpense->expenseform == 2 && $Advexpenseapproval->approver->approvaltype_id == 38)){
											$Advexpense->requeststatus = 3;
											$emto=$email;$emname=$Advexpense->employee->fullname;
											$this->mail->Subject = "Online Approval System -> Approval Completed";
											$red = 'Your Expense request has been approved';
											//delete unnecessary approver
											$Advexpenseapproval = Advexpenseapproval::find('all', array('joins'=>$join,'conditions' => array("advexpense_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
											foreach ($Advexpenseapproval as $data) {
												if($data->approvalstatus==0){
													$logger = new Datalogger("Advexpenseapproval","delete",json_encode($data->to_array()),"automatic remove unnecessary approver by system");
													$logger->SaveData();
													$data->delete();
												}
											}

											if($Advexpense->lessadvance !== null || $Advexpense->lessadvance !== 0) {
												$Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND requeststatus=5",$Advexpense->employee->id)));
												$Advance->requeststatus=3;
												$Advance->save();
											}


											$complete =true;
										}
										else{
											$Advexpense->requeststatus = 1;
											$emto=$adb->email;$emname=$adb->fullname;
											$this->mail->Subject = "Online Approval System -> New Expense Submission";
											$red = 'New Expense request awaiting for your approval:';
										}
										$Advexpensehistory->actiontype = 4;							
										break;
									case '3':
										$Advexpense->requeststatus = 4;
										$emto=$email;$emname=$Advexpense->employee->fullname;
										$Advexpensehistory->actiontype = 5;
										$this->mail->Subject = "Online Approval System -> Request Rejected";
										$red = 'Your Expense request has been rejected <br>Remarks from Approver : <font color="red">'.$data['remarks']."</font>";
										break;
									default:
										break;
								}
								//print_r($Advexpense);
								$Advexpense->save();
								$Advexpensehistory->save();
								echo "email to :".$emto." ->".$emname;
								$this->mail->addAddress($emto, $emname);
								
								
								$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$emname.',</span></p>
								<p class=MsoNormal><span style="color:#1F497D">'.$red.'</span></p>
								<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
								<table border=1 cellspacing=0 cellpadding=3 width=683>
								<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advexpense->employee->fullname.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>SAP ID</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advexpense->employee->sapid.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Position</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advexpense->employee->designation->designationname.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Business Group / Business Unit</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advexpense->employee->company->companyname.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Location</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advexpense->employee->location->location.'</b></p></td></tr>
								<tr><td><p class=MsoNormal>Email</p></td><td>:</td><td><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
								</table>';
						if($Advexpense->expenseform == 1) {
							$form = "Expense Req HR";
						} else if($Advexpense->expenseform == 2){
							$form = "Expense Req OPR";
						}

						if($Advexpense->expensetype == 0) {
							$less = 0;
						} else {
							$less = $Advexpense->lessadvance;
						}

						if($Advexpense->expense == 1) {
							$expenseM = "Cash";
						} else if($Advexpense->expense == 2) {
							$expenseM = "Bank";
						}
						$Advexpensedetail = Advexpensedetail::find('all',array('conditions'=>array("advexpense_id=?",$doid),'include'=>array('advexpense'=>array('employee'=>array('company','department','designation','grade','location')))));	

						$this->mailbody .='
							<table border=1 cellspacing=0 cellpadding=3 width=683>
							<tr>
								<th><p class=MsoNormal>Expense Form</p></th>
								<th><p class=MsoNormal>Expense Method</p></th>
								<th><p class=MsoNormal>Beneficiary</p></th>
								<th><p class=MsoNormal>Account Name</p></th>
								<th><p class=MsoNormal>Bank</p></th>
								<th><p class=MsoNormal>Account Number</p></th>
								<th><p class=MsoNormal>Due Date</p></th>
								<th><p class=MsoNormal>Expected Date</p></th>
								<th><p class=MsoNormal>Remarks</p></th>
							</tr>
							<tr style="height:22.5pt">
								<td><p class=MsoNormal> '.$form.'</p></td>
								<td><p class=MsoNormal> '.$expenseM.'</p></td>
								<td><p class=MsoNormal> '.$Advexpense->beneficiary.'</p></td>
								<td><p class=MsoNormal> '.$Advexpense->accountname.'</p></td>
								<td><p class=MsoNormal> '.$Advexpense->bank.'</p></td>
								<td><p class=MsoNormal> '.$Advexpense->accountnumber.'</p></td>
								<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Advexpense->duedate)).'</p></td>
								<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Advexpense->expensedate)).'</p></td>
								<td><p class=MsoNormal> '.$Advexpense->remarks.'</p></td>
							</tr>
							</table>
							<table border=1 cellspacing=0 cellpadding=3 width=683>
									<tr><th><p class=MsoNormal>No</p></th>
										<th><p class=MsoNormal>Description</p></th>
										<th><p class=MsoNormal>Account Code</p></th>
										<th><p class=MsoNormal>Amount</p></th>
									</tr>
						';
						$no=1;
						foreach ($Advexpensedetail as $data){
							$val_tamount += $data->amount;
							$this->mailbody .='<tr style="height:22.5pt">
										<td><p class=MsoNormal> '.$no.'</p></td>
										<td><p class=MsoNormal> '.$data->description.'</p></td>
										<td><p class=MsoNormal> '.$data->accountcode.'</p></td>
										<td><p class=MsoNormal> '.number_format($data->amount).'</p></td>
							</tr>';
							$no++;
						}
						$this->mailbody .='</table>
						<ul>
								<li><b><span>Total Amount : '.number_format($val_tamount).'</span></b></li>
								<li><b><span>Less Advance : '.number_format($less).'</span></b></li>
								<li><b><span>Balance To Be Paid : '.number_format($val_tamount-$less).'</span></b></li>
						</ul>
						<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
						
								
								$this->mail->msgHTML($this->mailbody);
								if ($complete){
									$filePath= $this->generatePDFi($doid);
									$this->mail->addAttachment($filePath);
								}
								if (!$this->mail->send()) {
									$err = new Errorlog();
									$err->errortype = "Advexpense Mail";
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
							echo json_encode($Advexpenseapproval);
						break;
					default:
						$Advexpenseapproval = Advexpenseapproval::all();
						foreach ($Advexpenseapproval as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Advexpenseapproval, JSON_NUMERIC_CHECK);
						break;
				}
			}
			// else{
				// $result= array("status"=>"error","message"=>"Authentication error or expired, please refresh and re login application");
				// echo json_encode($result);
			// }
		}
	}

	function advexpenseDetail(){
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
							// $join = "LEFT JOIN vwadvexpensereport ON tbl_advexpensedetail.advexpense_id = vwadvexpensereport.id";
							// $select = "tbl_advexpensedetail.*,vwadvexpensereport.apprstatuscode";
							// $Advexpensedetail = Advexpensedetail::find('all', array('joins'=>$join,'select'=>$select,'conditions' => array("advexpense_id=?",$id)));
							$Advexpensedetail = Advexpensedetail::find('all', array('conditions' => array("advexpense_id=?",$id)));
							foreach ($Advexpensedetail as &$result) {
								$result	= $result->to_array();
							}
	
							echo json_encode($Advexpensedetail, JSON_NUMERIC_CHECK);
						}else{
							$Advexpensedetail = new Advexpensedetail();
							echo json_encode($Advexpensedetail);
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							$Advexpensedetail = Advexpensedetail::find('all', array('conditions' => array("advexpense_id=?",$query['advexpense_id'])));
							$data=array("jml"=>count($Advexpensedetail));
						}else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);

						$Advexpensedetail = Advexpensedetail::create($data);
						$logger = new Datalogger("Advexpensedetail","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Advexpensedetail = Advexpensedetail::find($id);
						$data=$Advexpensedetail->to_array();
						$Advexpensedetail->delete();
						$logger = new Datalogger("Advexpensedetail","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Advexpensedetail);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						
						$Advexpensedetail = Advexpensedetail::find($id);
						$olddata = $Advexpensedetail->to_array();
						// foreach($data as $key=>$val){
						// 	$Advexpensedetail->$key=$val;
						// }
						foreach($data as $key=>$val){
							// $val=($val=='true')?1:0;
							if($val=='true') {
								$val = 1;
							}else if($val=='false') {
								$val = 0;
							}
							$Advexpensedetail->$key=$val;
							
						}
						// $exprice = $Advexpensedetail->unitprice * $Advexpensedetail->qty;
						// $Advexpensedetail->extendedprice = $exprice;
						$Advexpensedetail->save();
						$logger = new Datalogger("Advexpensedetail","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						echo json_encode($Advexpensedetail);
						
						break;
					default:
						$Advexpensedetail = Advexpensedetail::all();
						foreach ($Advexpensedetail as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Advexpensedetail, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}

	function advexpenseHistory(){
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
							$Advexpensehistory = Advexpensehistory::find('all', array('conditions' => array("advexpense_id=?",$id),'include' => array('advexpense')));
							foreach ($Advexpensehistory as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Advexpensehistory, JSON_NUMERIC_CHECK);
						}
						break;
					default:
						break;
				}
			}
		}
	}

	function generatePDFi($id){
		$Advexpense = Advexpense::find($id);
		$Advexpensedetail = Advexpensedetail::find('all',array('conditions'=>array("advexpense_id=?",$id),'include'=>array('advexpense'=>array('employee'=>array('company','department','designation','grade','location')))));	
		
		$superiorId=$Advexpense->superior;
		$Superior = Employee::find($superiorId);
		$supAdb = Addressbook::find('first',array('conditions'=>array("username=?",$Superior->loginname)));
		$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Advexpense->employee->loginname)));
		$email=$usr->email;
		$fullname=$Advexpense->employee->fullname;
		$department = $Advexpense->employee->department->departmentname;

		// $duedate = date("d/m/Y",strtotime($Advexpense->duedate));
		$duedate = $Advexpense->duedate;
		// $expensedate = date("d/m/Y",strtotime($Advexpense->expensedate));
		$expensedate = $Advexpense->expensedate;

		$joinx   = "LEFT JOIN tbl_approver ON (tbl_advexpenseapproval.approver_id = tbl_approver.id) ";					
		$Advexpenseapproval = Advexpenseapproval::find('all',array('joins'=>$joinx,'conditions' => array("advexpense_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee','approvaltype'))));							
		
		//condition
			
			
		//end condition

		try {
			$excel = new COM("Excel.Application") or die ("ERROR: Unable to instantaniate COM!\r\n");
			$excel->Visible = false;

			if($Advexpense->expenseform == 1) {
				$title = 'expense_hr';
				$file= SITE_PATH."/doc/hr/expensehr.xlsx";
			} else {
				$title = 'expense_ops';
				$file= SITE_PATH."/doc/hr/expenseops.xlsx";
			}

				
				$Workbook = $excel->Workbooks->Open($file) or die("ERROR: Unable to open " . $file . "!\r\n");
				$Worksheet = $Workbook->Worksheets(1);
				$Worksheet->Activate;

				if($Advexpense->expense == 1) {
					$expense = 'Cash';
				} else if($Advexpense->expense == 2) {
					$expense = 'Bank';
				}

				// $Worksheet->Range("N6")->Value = date("d/m/Y",strtotime($Advexpense->createddate));
				$Worksheet->Range("M15")->Value = date("d/m/Y",strtotime($duedate));
				$Worksheet->Range("M16")->Value = date("d/m/Y",strtotime($expensedate));

			// if($Advexpense->expenseform == 1) {
				$Worksheet->Range("E10")->Value = $fullname;
				$Worksheet->Range("E11")->Value = $department;
				$Worksheet->Range("E12")->Value = $Advexpense->employee_id;

				$Worksheet->Range("M10")->Value = $expense;
				$Worksheet->Range("E14")->Value = $Advexpense->beneficiary;
				$Worksheet->Range("E15")->Value = $Advexpense->accountname;
				$Worksheet->Range("E16")->Value = $Advexpense->bank;
				$Worksheet->Range("E17")->Value = $Advexpense->accountnumber;
			// } else {
			// 	$Worksheet->Range("E6")->Value = $fullname;
			// 	$Worksheet->Range("E7")->Value = $department;
			// 	$Worksheet->Range("E8")->Value = $Advexpense->employee_id;

			// 	$Worksheet->Range("E10")->Value = $Advexpense->beneficiary;
			// 	$Worksheet->Range("E11")->Value = $Advexpense->accountname;
			// 	$Worksheet->Range("E12")->Value = $Advexpense->bank;
			// 	$Worksheet->Range("E13")->Value = $Advexpense->accountnumber;
			// }




				foreach ($Advexpenseapproval as $data){
					if(($data->approver->approvaltype->id==35) || ($data->approver->employee_id==$Advexpense->superior)){
						$superiorname = $data->approver->employee->fullname;
						$superiordate = 'Date : '.date("d/m/Y",strtotime($data->approvaldate));
					}
				
					if($data->approver->approvaltype->id==36) {
						$hrdheadname = $data->approver->employee->fullname;
						$hrdheaddate = 'Date : '.date("d/m/Y",strtotime($data->approvaldate));
					}
					
					if($data->approver->approvaltype->id==37) {
						$bufcname = $data->approver->employee->fullname;
						$bufcdate = 'Date : '.date("d/m/Y",strtotime($data->approvaldate));
					}

					if($data->approver->approvaltype->id==38) {
						$buheadname = $data->approver->employee->fullname;
						$buheaddate = 'Date : '.date("d/m/Y",strtotime($data->approvaldate));
					}

					if($data->approver->approvaltype->id==39) {
						$depmdname = $data->approver->employee->fullname;
						$depmddate = 'Date : '.date("d/m/Y",strtotime($data->approvaldate));
					}

					if($data->approver->approvaltype->id==40) {
						$mdname = $data->approver->employee->fullname;
						$mddate = 'Date : '.date("d/m/Y",strtotime($data->approvaldate));
					}

					if($data->approver->approvaltype->id==41) {
						$financename = $data->approver->employee->fullname;
						$financedate = 'Date : '.date("d/m/Y",strtotime($data->approvaldate));
					}

					if($data->approver->approvaltype->id==42) {
						$procname = $data->approver->employee->fullname;
						$procdate = 'Date : '.date("d/m/Y",strtotime($data->approvaldate));
					}
				}
				$picpath= SITE_PATH."/images/approved.png";
				
				$Worksheet->Range("A34")->Value = $fullname;
				$Worksheet->Range("A35")->Value = 'Date : '.date("d/m/Y",strtotime($Advexpense->createddate));

				$pic=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
				$pic->Height  = 20;
				$pic->Top = $excel->Cells(30, 1)->Top ;
				$pic->Left = $excel->Cells(30, 1)->Left ;

				if($Advexpense->expenseform == 1) {
					if(!empty($superiorname)) {
						$Worksheet->Range("E34")->Value = $superiorname;
						$Worksheet->Range("E35")->Value = $superiordate;
						$pic1=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic1->Height  = 20;
						$pic1->Top = $excel->Cells(30, 5)->Top ;
						$pic1->Left = $excel->Cells(30, 5)->Left ;
					}
	
					if(!empty($hrdheadname)) {
						$Worksheet->Range("H34")->Value = $hrdheadname;
						$Worksheet->Range("H35")->Value = $hrdheaddate;
						$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic2->Height  = 20;
						$pic2->Top = $excel->Cells(30, 8)->Top ;
						$pic2->Left = $excel->Cells(30, 8)->Left ;
					}
	
					if(!empty($bufcname)) {
						$Worksheet->Range("K34")->Value = $bufcname;
						$Worksheet->Range("K35")->Value = $bufcdate;
						$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic2->Height  = 20;
						$pic2->Top = $excel->Cells(30, 11)->Top ;
						$pic2->Left = $excel->Cells(30, 11)->Left ;
					}
	
					// if(!empty($buheadname)) {
					// 	$Worksheet->Range("A45")->Value = $buheadname;
					// 	$Worksheet->Range("A46")->Value = $buheaddate;
					// 	$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
					// 	$pic2->Height  = 20;
					// 	$pic2->Top = $excel->Cells(42, 1)->Top ;
					// 	$pic2->Left = $excel->Cells(42, 1)->Left ;
					// }
	
					// if(!empty($financename)) {
					// 	$Worksheet->Range("F45")->Value = $financename;
					// 	$Worksheet->Range("F46")->Value = $financedate;
					// 	$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
					// 	$pic2->Height  = 20;
					// 	$pic2->Top = $excel->Cells(42, 6)->Top ;
					// 	$pic2->Left = $excel->Cells(42, 6)->Left ;
					// }
	
					// if(!empty($depmdname)) {
					// 	$Worksheet->Range("I45")->Value = $depmdname;
					// 	$Worksheet->Range("I46")->Value = $depmddate;
					// 	$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
					// 	$pic2->Height  = 20;
					// 	$pic2->Top = $excel->Cells(42, 9)->Top ;
					// 	$pic2->Left = $excel->Cells(42, 9)->Left ;
					// }
	
					// if(!empty($mdname)) {
					// 	$Worksheet->Range("L45")->Value = $mdname;
					// 	$Worksheet->Range("L46")->Value = $mddate;
					// 	$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
					// 	$pic2->Height  = 20;
					// 	$pic2->Top = $excel->Cells(42, 12)->Top ;
					// 	$pic2->Left = $excel->Cells(42, 12)->Left ;
					// }
				} else {
					if(!empty($superiorname)) {
						$Worksheet->Range("E34")->Value = $superiorname;
						$Worksheet->Range("E35")->Value = $superiordate;
						$pic1=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic1->Height  = 20;
						$pic1->Top = $excel->Cells(30, 5)->Top ;
						$pic1->Left = $excel->Cells(30, 5)->Left ;
					}

					// if(!empty($procname)) {
					// 	$Worksheet->Range("L32")->Value = $procname;
					// 	$Worksheet->Range("L33")->Value = $procdate;
					// 	$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
					// 	$pic2->Height  = 20;
					// 	$pic2->Top = $excel->Cells(29, 12)->Top ;
					// 	$pic2->Left = $excel->Cells(29, 12)->Left ;
					// }
	
					if(!empty($bufcname)) {
						$Worksheet->Range("H34")->Value = $bufcname;
						$Worksheet->Range("H35")->Value = $bufcdate;
						$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic2->Height  = 20;
						$pic2->Top = $excel->Cells(30, 8)->Top ;
						$pic2->Left = $excel->Cells(30, 8)->Left ;
					}
	
					if(!empty($buheadname)) {
						$Worksheet->Range("K34")->Value = $buheadname;
						$Worksheet->Range("K35")->Value = $buheaddate;
						$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic2->Height  = 20;
						$pic2->Top = $excel->Cells(30, 11)->Top ;
						$pic2->Left = $excel->Cells(30, 11)->Left ;
					}
	
					// if(!empty($financename)) {
					// 	$Worksheet->Range("H45")->Value = $financename;
					// 	$Worksheet->Range("H46")->Value = $financedate;
					// 	$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
					// 	$pic2->Height  = 20;
					// 	$pic2->Top = $excel->Cells(42, 8)->Top ;
					// 	$pic2->Left = $excel->Cells(42, 8)->Left ;
					// }

					// if(!empty($depmdname)) {
					// 	$Worksheet->Range("K45")->Value = $depmdname;
					// 	$Worksheet->Range("K46")->Value = $depmddate;
					// 	$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
					// 	$pic2->Height  = 20;
					// 	$pic2->Top = $excel->Cells(42, 11)->Top ;
					// 	$pic2->Left = $excel->Cells(42, 11)->Left ;
					// }
	
					// if(!empty($mdname)) {
					// 	$Worksheet->Range("N45")->Value = $mdname;
					// 	$Worksheet->Range("N46")->Value = $mddate;
					// 	$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
					// 	$pic2->Height  = 20;
					// 	$pic2->Top = $excel->Cells(42, 14)->Top ;
					// 	$pic2->Left = $excel->Cells(42, 14)->Left ;
					// }
				}

				foreach ($Advexpensedetail as $data){
					$val_tamount += $data->amount;
				}
				
				if($Advexpense->expensetype == 0) {
					$lessadvance = 0;
				} else if($Advexpense->expensetype == 1) {
					$lessadvance = $Advexpense->lessadvance;
				}
				$Worksheet->Range("K22")->Value = $val_tamount;
				$Worksheet->Range("K23")->Value = $lessadvance;
				$Worksheet->Range("K24")->Value = ($val_tamount-$lessadvance);

	
				$xlShiftDown=-4121;
				$no = 1;
				for ($a=20;$a<20+count($Advexpensedetail);$a++){
					$Worksheet->Rows($a+1)->Copy();
					$Worksheet->Rows($a+1)->Insert($xlShiftDown);
					$Worksheet->Range("A".$a)->Value = $no++;
					$Worksheet->Range("B".$a)->Value = $Advexpensedetail[$a-20]->description;
					$Worksheet->Range("I".$a)->Value = $Advexpensedetail[$a-20]->accountcode;
					$Worksheet->Range("K".$a)->Value = $Advexpensedetail[$a-20]->amount;
				}
		

				//end condition


			$xlTypePDF = 0;
			$xlQualityStandard = 0;
			$fileName ='doc'.DS.'hr'.DS.'pdf'.DS.$title.'_'.$Advexpense->employee->fullname.'_'.$Advexpense->employee->sapid.'_'.date("YmdHis").'.pdf';
			$fileName = str_replace("/","",$fileName);
			$path= SITE_PATH.'/doc'.DS.'hr'.DS.'pdf'.DS.$title.'_'.$Advexpense->employee->fullname.'_'.$Advexpense->employee->sapid.'_'.date("YmdHis").'.pdf';
			if (file_exists($path)) {
			unlink($path);
			}
			$Worksheet->ExportAsFixedFormat($xlTypePDF, $path, $xlQualityStandard);
			$Advexpense->approveddoc=str_replace("\\","/",$fileName);
			$Advexpense->save();

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
			$err->errortype = "AdvexpenseFPDFGenerator";
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

	function advexpenseAttachment(){
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
							$Advexpenseattachment = Advexpenseattachment::find('all', array('conditions' => array("advexpense_id=?",$id)));
							foreach ($Advexpenseattachment as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Advexpenseattachment, JSON_NUMERIC_CHECK);
						}else{
							$Advexpenseattachment = new Advexpenseattachment();
							echo json_encode($Advexpenseattachment);
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							$Advexpenseattachment = Advexpenseattachment::find('all', array('conditions' => array("advexpense_id=?",$query['advexpense_id'])));
							$data=array("jml"=>count($Advexpenseattachment));
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
						
						$Advexpenseattachment = Advexpenseattachment::create($data);
						$logger = new Datalogger("Advexpenseattachment","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Advexpenseattachment = Advexpenseattachment::find($id);
						$data=$Advexpenseattachment->to_array();
						$Advexpenseattachment->delete();
						$logger = new Datalogger("Advexpenseattachment","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Advexpenseattachment);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
						$data['employee_id']=$Employee->id;
						$Advexpenseattachment = Advexpenseattachment::find($id);
						$olddata = $Advexpenseattachment->to_array();
						foreach($data as $key=>$val){					
							$val=($val=='No')?false:(($val=='Yes')?true:$val);
							$Advexpenseattachment->$key=$val;
						}
						$Advexpenseattachment->save();
						$logger = new Datalogger("Advexpenseattachment","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						echo json_encode($Advexpenseattachment);
						
						break;
					default:
						$Advexpenseattachment = Advexpenseattachment::all();
						foreach ($Advexpenseattachment as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Advexpenseattachment, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}
	public function uploadAdvexpenseFile(){
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
		$path_to_file = "upload/advexpense/".$id."_".time()."_".$_FILES['myFile']['name'];
		$path_to_file = str_replace("%","_",$path_to_file);
		$path_to_file = str_replace(" ","_",$path_to_file);
		echo $path_to_file;
        move_uploaded_file($_FILES['myFile']['tmp_name'], $path_to_file);
	}

}