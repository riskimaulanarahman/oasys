<?php
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

Class Advexpensemodule extends Application{
	private $mailbody;
	private $mail;
	private $pathcopy;
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
				case 'apiadvexpensedetailbt':
					$this->advexpenseDetailbt();
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
						$join = "LEFT JOIN vwadvexpensereport ON tbl_advexpense.id = vwadvexpensereport.id";
						$select = "tbl_advexpense.*,vwadvexpensereport.apprstatuscode";
						// $Advexpense = Advexpense::find($id, array('include' => array('employee'=>array('company','department','designation','location'))));
						$Advexpense = Advexpense::find($id, array('joins'=>$join,'select'=>$select,'include' => array('employee'=>array('company','department','designation'))));

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
							$location = $Advexpense->employee->location->location;
							$department = $Advexpense->employee->department->departmentname;
							$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Advexpense->employee->loginname)));

							$data=$Advexpense->to_array();
							$data['name']=$fullname;
							$data['email']=$usr->email;
							$data['costcenter']=$costcenter;
							$data['bg']=$bg;
							$data['location']=$location;

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
								case "bisnistrip":
										$id= $query['advexpense_id'];
										$action = $query['action'];
										$check = $query['check'];
										$valstart = $query['valstart'];
										$valend = $query['valend'];
										$valdays = $query['valdays'];
										$employee_id = $query['employee_id'];

										$Advexpense = Advexpense::find($id);
										$detailbt = Advexpensedetailbt::find("all",array('conditions' => array("advexpense_id=?",$id)));
										
										
										// $data['advexpense_id'] = $id;
										// $data['DepartDate'] = $valstart;
										// $data['ReturnDate'] = $valstart;

										if($action == 'add') {
											$Advexpense->enddate = $valend;

											foreach ($detailbt as $delr){
												$delr->delete();
											}

											for($i=0;$i<$valdays+1;$i++) {
	
												$days_ago = date('Y-m-d', strtotime('+'.$i.' days', strtotime($valstart)));
												// $days_arr = explode(' ',$days_ago);
	
												// for($x=0;$x<$valdays+1;$x++) {
													// print_r($days_arr[0]);
													// echo $days_ago;
													$data['advexpense_id'] = $id;
													$data['DepartDate'] = $days_ago;
													$data['ReturnDate'] = $days_ago;

													// print_r($data);

													$Advexpensedetailbt = Advexpensedetailbt::create($data);
												// }
											}
	
											
	
										} else if($action == 'reset') {
											$Advexpense->startdate = $valstart;
											$Advexpense->enddate = $valend;
											foreach ($detailbt as $delr){
												$delr->delete();
											}

											
										} else {
											$Advexpense->startdate = $valstart;
											$Advexpense->enddate = $valend;
										}
										$Advexpense->save();

										$Advexpensebt = Advexpensedetail::find('first',array('conditions'=> array("advexpense_id=? and expensetype='MNP' ",$Advexpense->id)));
										$Advexpensebt->delete();


										if($check == 'enddate') {
											$Advexpensecheckenddate = Advexpense::find('first',array('conditions' => array('id=? and enddate is null',$id)));
											$datacheck = $Advexpensecheckenddate->to_array();
											// echo json_encode($datacheck, JSON_NUMERIC_CHECK);
											echo count($Advexpensecheckenddate);
											// if(count($Advexpensecheckenddate)==0) {
											// 	$item['message']=200;
											// 	echo json_encode($item, JSON_NUMERIC_CHECK);
											// } else {
											// 	$item['message']=404;
											// 	echo json_encode($item, JSON_NUMERIC_CHECK);
											// }
										}
										

									break;
									case 'savelessadv':
										$id= $query['advexpense_id'];
										$advanceno = $query['advanceno'];
										$paymenttype = $query['paymenttype'];
										$employee_id = $query['employee_id'];

										$Advexpense = Advexpense::find($id, array('include' => array('employee'=>array('company','department','designation'))));

										//check lessadvance
										if($paymenttype == false || $paymenttype == 'false') {
											$valpaymenttype = 0;
										} else if ($paymenttype == true || $paymenttype == 'true') {
											$valpaymenttype = 1;
										}

										if($paymenttype !== null) {
											$Advexpense->paymenttype = $valpaymenttype;
										}


										if($Advexpense->requeststatus == 0) {

												if($advanceno !== null) {
													
													$Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND advanceno=?",$Advexpense->employee_id,$advanceno)));
													
													$AdvanceDetail = AdvanceDetail::find('all',array('conditions'=> array("advance_id=?",$Advance->id)));
													foreach ($AdvanceDetail as $val) {
														$val_tamount += $val->amount;
													}
												
												$Advexpense->advanceno = $advanceno;
												$Advexpense->lessadvance = $val_tamount;
												$Advexpense->save();
												
											} else {
												
												$Advexpense->advanceno = null;
												$Advexpense->lessadvance = null;
												$Advexpense->save();
											}
										}

										


										if($Advexpense) {
											$item['message']=200;
											echo json_encode($item, JSON_NUMERIC_CHECK);
										} else {
											$item['message']=404;
											echo json_encode($item, JSON_NUMERIC_CHECK);
										}
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
											
											// if(($data['companycode']=="IHM" || $Employee->company->sapcode=='RND' || $Employee->company->sapcode=='NKF')  && (substr(strtolower($Employee->location->sapcode),0,4)=="0250")){
											// 	$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and tbl_employee.location_id='8'")));
											// }else if(($data['companycode']=="AHL" || $Employee->company->sapcode=='RND' || $Employee->company->sapcode=='NKF') && (substr(strtolower($Employee->location->sapcode),0,4)=="0210")){
											// 	$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and tbl_employee.location_id='3'")));
											// }else {
											// 	$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and tbl_employee.location_id='8'")));
											// }

											// if(($data['companycode']=="KPS" ||$data['companycode']=="KPSI" || $data['companycode']=="LDU") ){
											// 	$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and companylist = 'KPS,KPSI,LDU'")));
											// }else if(($data['companycode']=="AHL")){
											// 	$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and companylist='AHL'")));
											// }else if(($data['companycode']=="BCL")){
											// 	$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and companylist='BCL'")));
											// }else {
											// 	$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and companylist='IHM'")));
											// }
											$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and FIND_IN_SET(?, CompanyList) > 0 ",$Employee->companycode)));
											
											

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
											// 		$ApproverHRDFU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='36' and tbl_employee.location_id='8' and not(tbl_employee.id=?)",$Employee->id)));
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

											// if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220") || ($Employee->department->sapcode=="13000090") || ($Employee->department->sapcode=="13000121") || ($Employee->company->sapcode=="NKF") || ($Employee->company->sapcode=="RND")){
												
												
												$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=36 and FIND_IN_SET(?, CompanyList) > 0 ",$Employee->companycode)));
												if(count($Approver2)>0){
													$Advexpenseapproval = new Advexpenseapproval();
													$Advexpenseapproval->advexpense_id = $Advexpense->id;
													$Advexpenseapproval->approver_id = $Approver2->id;
													$Advexpenseapproval->save();
													$logger = new Datalogger("Advexpenseapproval","add","Add initial HRD Approval ",json_encode($Advexpenseapproval->to_array()));
													$logger->SaveData();
												}
													
											// }else{
												
											// 	$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=36 and tbl_employee.company_id=? and not(tbl_employee.location_id='8')",$Employee->company_id)));
											// 	if(count($Approver2)>0){
											// 		$Advexpenseapproval = new Advexpenseapproval();
											// 		$Advexpenseapproval->advexpense_id = $Advexpense->id;
											// 		$Advexpenseapproval->approver_id = $Approver2->id;
											// 		$Advexpenseapproval->save();
											// 	}
											// }
											

											

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

											// $companyBU=( ($Employee->companycode=='KPA') || ($Employee->companycode=='AHL') )?"KPSI":$Employee->companycode;
											// if (($Employee->company->sapcode=='RND') || ($Employee->company->sapcode=='NKF')){
												$ApproverBU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and FIND_IN_SET(?, CompanyList) > 0 ",$Employee->companycode)));
											// }else{
											// 	$ApproverBU = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and tbl_employee.companycode=? and not(tbl_employee.id=?)",$companyBU,$Employee->id)));
											// }
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
								$code = Advexpense::find('first',array('select' => "CONCAT('Expense/','".$Employee->companycode."','/',YEAR(CURDATE()),'/',LPAD(MONTH(CURDATE()), 2, '0'),'/',LPAD(CASE when max(substring(expenseno,-4,4)) is null then 1 else max(substring(expenseno,-4,4))+1 end,4,'0')) as expenseno","conditions"=>array("substring(expenseno,9,".strlen($Employee->companycode).")=? and substring(expenseno,".(strlen($Employee->companycode)+10).",4)=YEAR(CURDATE())",$Employee->companycode)));
								$Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND requeststatus=5 AND advanceform=1",$Employee->id)));
								
								$AdvanceDetail = AdvanceDetail::find('all',array('conditions'=> array("advance_id=?",$Advance->id)));
								foreach ($AdvanceDetail as $val) {
									$tamount += $val->amount;
								}
								$data['expenseno']=$code->expenseno;
								$data['lessadvance']=$tamount;
								
								$Advexpense = Advexpense::create($data);
								$data=$Advexpense->to_array();
								
								$joins   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";
								$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";

								// $ApproverFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=41")));
								// if(count($ApproverFC)>0){
								// 	$Advexpenseapproval = new Advexpenseapproval();
								// 	$Advexpenseapproval->advexpense_id = $Advexpense->id;
								// 	$Advexpenseapproval->approver_id = $ApproverFC->id;
								// 	$Advexpenseapproval->save();
								// }

								// if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220") || ($Employee->department->sapcode=="13000090") || ($Employee->department->sapcode=="13000121") || ($Employee->company->sapcode=="NKF") || ($Employee->company->sapcode=="RND")){
												
												
								// 	$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=36 and tbl_employee.location_id='8'")));
								// 	if(count($Approver2)>0){
								// 		$Advexpenseapproval = new Advexpenseapproval();
								// 		$Advexpenseapproval->advexpense_id = $Advexpense->id;
								// 		$Advexpenseapproval->approver_id = $Approver2->id;
								// 		$Advexpenseapproval->save();
								// 	}
										
								// }else{
									
								// 	$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=36 and tbl_employee.company_id=? and not(tbl_employee.location_id='8')",$Employee->company_id)));
								// 	if(count($Approver2)>0){
								// 		$Advexpenseapproval = new Advexpenseapproval();
								// 		$Advexpenseapproval->advexpense_id = $Advexpense->id;
								// 		$Advexpenseapproval->approver_id = $Approver2->id;
								// 		$Advexpenseapproval->save();
								// 	}
								// }

								// if(($Employee->companycode=="KPS" ||$Employee->companycode=="KPSI" || $Employee->companycode=="LDU") ){
								// 	$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and companylist = 'KPS,KPSI,LDU'")));
								// }else if(($Employee->companycode=="AHL")){
								// 	$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and companylist='AHL'")));
								// }else if(($Employee->companycode=="BCL")){
								// 	$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and companylist='BCL'")));
								// }else {
								// 	$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and companylist='IHM'")));
								// }
								$ApproverHRV = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='44' and FIND_IN_SET(?, CompanyList) > 0 ",$Employee->companycode)));


								if(count($ApproverHRV)>0){
									$Advexpenseapproval = new Advexpenseapproval();
									$Advexpenseapproval->advexpense_id = $Advexpense->id;
									$Advexpenseapproval->approver_id = $ApproverHRV->id;
									$Advexpenseapproval->save();
									$logger = new Datalogger("Advexpenseapproval","add","Add initial HR Verifikator Approval ",json_encode($Advexpenseapproval->to_array()));
									$logger->SaveData();
								}

								// if($Employee->companycode == 'BCL') {
									$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='36' and FIND_IN_SET(?, CompanyList) > 0 ",$Employee->companycode)));
								// }else {
								// 	$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='36' and not(tbl_employee.companycode='BCL')")));
								// }
								if(count($Approver2)>0){
									$Advexpenseapproval = new Advexpenseapproval();
									$Advexpenseapproval->advexpense_id = $Advexpense->id;
									$Advexpenseapproval->approver_id = $Approver2->id;
									$Advexpenseapproval->save();
									$logger = new Datalogger("Advexpenseapproval","add","Add initial HRD Approval ",json_encode($Advexpenseapproval->to_array()));
									$logger->SaveData();
								}

								// $companyFC=(($data['companycode']=='BCL') || ($data['companycode']=='KPA'))?"KPSI":((($data['companycode']=='KPSI'))?"LDU":$Employee->companycode);
								// $ApproverBUFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37' and tbl_employee.companycode=? and not(tbl_employee.id=?)",$companyFC,$Employee->id)));
								// if(count($ApproverBUFC)>0){
								// 	$Advexpenseapproval = new Advexpenseapproval();
								// 	$Advexpenseapproval->advexpense_id = $Advexpense->id;
								// 	$Advexpenseapproval->approver_id = $ApproverBUFC->id;
								// 	$Advexpenseapproval->save();
								// 	$logger = new Datalogger("Advexpenseapproval","add","Add initial BU FC Approval",json_encode($Advexpenseapproval->to_array()));
								// 	$logger->SaveData();
								// }

								// if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220")){
								// 	$ApproverBU = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and tbl_employee.location_id='8'")));
								// 	if(count($ApproverBU)>0){
								// 		$Advexpenseapproval = new Advexpenseapproval();
								// 		$Advexpenseapproval->advexpense_id =$Advexpense->id;
								// 		$Advexpenseapproval->approver_id = $ApproverBU->id;
								// 		$Advexpenseapproval->save();
								// 		$logger = new Datalogger("Advexpenseapproval","add","Add initial BU Approval",json_encode($Advexpenseapproval->to_array()));
								// 		$logger->SaveData();
								// 	}
									
								// 	$ApproverBUFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37' and tbl_employee.location_id='8'")));
								// 	if(count($ApproverBUFC)>0){
								// 		$Advexpenseapproval = new Advexpenseapproval();
								// 		$Advexpenseapproval->advexpense_id =$Advexpense->id;
								// 		$Advexpenseapproval->approver_id = $ApproverBUFC->id;
								// 		$Advexpenseapproval->save();
								// 		$logger = new Datalogger("Advexpenseapproval","add","Add initial BUFC Approval",json_encode($Advexpenseapproval->to_array()));
								// 		$logger->SaveData();
								// 	}

								// }else{
								// 	$ApproverBU = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38'  and tbl_employee.company_id=? and not(tbl_employee.location_id='8')",$Employee->company_id)));
								// 	if(count($ApproverBU)>0){
								// 		$Advexpenseapproval = new Advexpenseapproval();
								// 		$Advexpenseapproval->advexpense_id = $Advexpense->id;
								// 		$Advexpenseapproval->approver_id = $ApproverBU->id;
								// 		$Advexpenseapproval->save();
								// 		$logger = new Datalogger("Advexpenseapproval","add","Add initial BU Approval",json_encode($Advexpenseapproval->to_array()));
								// 		$logger->SaveData();
								// 	}

								// 	$ApproverBUFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37'  and tbl_employee.company_id=? and not(tbl_employee.location_id='8')",$Employee->company_id)));
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
								$Advexpensehistory->date = date("Y-m-d H:i:s");
								$Advexpensehistory->fullname = $Employee->fullname;
								$Advexpensehistory->approvaltype = "Originator";
								$Advexpensehistory->advexpense_id = $Advexpense->id;
								$Advexpensehistory->actiontype = 0;
								$Advexpensehistory->save();
								
							}catch (Exception $e){
								$err = new Errorlog();
								$err->errortype = "CreateAdvexpense";
								$err->errordate = date("Y-m-d H:i:s");
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
								$detailbt = Advexpensedetailbt::find("all",array('conditions' => array("advexpense_id=?",$id)));
								foreach ($detailbt as $delr){
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
								$err->errordate = date("Y-m-d H:i:s");
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
						unset($data['expenseno']);
						unset($data['apprstatuscode']);

						//unset($data['employee']);
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
						foreach($data as $key=>$val){
							$value=(($val===0) || ($val==='0') || ($val==='false'))?false:((($val===1) || ($val==='1') || ($val==='true'))?true:$val);
							$Advexpense->$key=$value;
						}
						$Advexpense->save();
						
						if (isset($data['superior'])){
							$joins   = "LEFT JOIN tbl_approver ON (tbl_advexpenseapproval.approver_id = tbl_approver.id) ";					
							$dx = Advexpenseapproval::find('all',array('joins'=>$joins,'conditions' => array("advexpense_id=? and (tbl_approver.approvaltype_id=35 or tbl_approver.approvaltype_id=49) and not(tbl_approver.employee_id=?)",$id,$superior)));	
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
								if($superior == 789) {
									$Approver = Approver::find('first',array('conditions'=>array("module='Advance' and employee_id=? and approvaltype_id=49",$superior)));
								} else {
									$Approver = Approver::find('first',array('conditions'=>array("module='Advance' and employee_id=? and approvaltype_id=35",$superior)));
								}
								// $Approver = Approver::find('first',array('conditions'=>array("module='Advance' and employee_id=? and approvaltype_id=35",$superior)));
								if(count($Approver)>0){
									$Advexpenseapproval = new Advexpenseapproval();
									$Advexpenseapproval->advexpense_id = $Advexpense->id;
									$Advexpenseapproval->approver_id = $Approver->id;
									$Advexpenseapproval->save();
								}else{
									$approver = new Approver();
									$approver->module = "Advance";
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
		
							$Advexpensedetail = Advexpensedetail::find('all',array('conditions'=>array("advexpense_id=?",$id),'include'=>array('advexpense'=>array('employee'=>array('company','department','designation','grade','location')))));	
							$Advexpensedetailbt = Advexpensedetailbt::find('all',array('conditions'=>array("advexpense_id=?",$id),'include'=>array('advexpense'=>array('employee'=>array('company','department','designation','grade','location')))));	
							if($Advexpense->paymenttype == 0) {
								$less = 0;
							} else {
								$less = $Advexpense->lessadvance;
							}
							$this->mailbody .='
								<table border=1 cellspacing=0 cellpadding=3 width=683>
								<tr>
									<th><p class=MsoNormal>Start Date</p></th>
									<th><p class=MsoNormal>End Date</p></th>
									<th><p class=MsoNormal>Remarks</p></th>
								</tr>
								<tr style="height:22.5pt">
									<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Advexpense->startdate)).'</p></td>
									<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Advexpense->enddate)).'</p></td>
									<td><p class=MsoNormal> '.$Advexpense->reason.'</p></td>
								</tr>
								</table>
								<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><th><p class=MsoNormal>No</p></th>
											<th><p class=MsoNormal>Expense Type</p></th>
											<th><p class=MsoNormal>Purpose/Description</p></th>
											<th><p class=MsoNormal>Receipt Date</p></th>
											<th><p class=MsoNormal>Amount</p></th>
											<th><p class=MsoNormal>Currency</p></th>
											<th><p class=MsoNormal>Exchange rate</p></th>
											<th><p class=MsoNormal>Payment Amount in Local Currency</p></th>
											<th><p class=MsoNormal>Cost Centre</p></th>
											<th><p class=MsoNormal>Negara</p></th>
											<th><p class=MsoNormal>Location/City</p></th>
											<th><p class=MsoNormal>Remarks</p></th>
										</tr>
							';
							$no=1;
							foreach ($Advexpensedetail as $data){
								$val_tamount += $data->amount;
								if($data->receiptdate == null || $data->receiptdate == '') {
									$recdate = '';
								} else {
									$recdate = $data->receiptdate;
								}
								$this->mailbody .='<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.$no.'</p></td>
											<td><p class=MsoNormal> '.$data->expensetype.'</p></td>
											<td><p class=MsoNormal> '.$data->purpose.'</p></td>
											<td><p class=MsoNormal> '.$recdate.'</p></td>
											<td><p class=MsoNormal> '.number_format($data->amount).'</p></td>
											<td><p class=MsoNormal> '.$data->currency.'</p></td>
											<td><p class=MsoNormal> '.number_format($data->exchangerate).'</p></td>
											<td><p class=MsoNormal> '.number_format($data->paymentamount).'</p></td>
											<td><p class=MsoNormal> '.$data->costcentre.'</p></td>
											<td><p class=MsoNormal> '.$data->country.'</p></td>
											<td><p class=MsoNormal> '.$data->location.'</p></td>
											<td><p class=MsoNormal> '.$data->remarks.'</p></td>

								</tr>';
								$no++;
							}
							$this->mailbody .= '</table>
							<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><th><p class=MsoNormal>No</p></th>
											<th><p class=MsoNormal>Depart Date</p></th>
											<th><p class=MsoNormal>Depart Time</p></th>
											<th><p class=MsoNormal>Return Date</p></th>
											<th><p class=MsoNormal>Return Time</p></th>
											<th><p class=MsoNormal>Breakfast</p></th>
											<th><p class=MsoNormal>Lunch</p></th>
											<th><p class=MsoNormal>Dinner</p></th>
											<th><p class=MsoNormal>Pocket</p></th>
											<th><p class=MsoNormal>isPapua ?</p></th>
											<th><p class=MsoNormal>Remarks</p></th>
										</tr>
							';
							foreach ($Advexpensedetailbt as $data){
								if($data->ispapua == 0) {
									$sppd = Advexpsppd::find('first',
										array(
											'conditions'=>array("level=? and ispapua=0",$Employee->level_id)
										)
									);

								} else if($data->ispapua == 1) {
									$sppd = Advexpsppd::find('first',
										array(
											'conditions'=>array("level=? and ispapua=1",$Employee->level_id)
										)
									);

									
								} else if($data->ispapua == 2) {
									$sppd = Advexpsppd::find('first',
										array(
											'conditions'=>array("level=? and ispapua=2",$Employee->level_id)
										)
									);

									
								}

								$breakfast = ($data->breakfast == 1) ? 0 : $sppd->breakfast;
								$lunch = ($data->lunch == 1) ? 0 : $sppd->lunch;
								$dinner = ($data->dinner == 1) ? 0 : $sppd->dinner;
								$pocket = ($data->pocket == 1) ? 0 : $sppd->pocket;

								$jml_breakfast += $breakfast;
								$jml_lunch += $lunch;
								$jml_dinner += $dinner;
								$jml_pocket += $pocket;

								$totalbt = $jml_breakfast+$jml_lunch+$jml_dinner+$jml_pocket;

								$papua = ($data->ispapua == 0) ? "TIDAK" : "YA";

								$this->mailbody .='<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.$no.'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($data->departdate)).'</p></td>
											<td><p class=MsoNormal> '.$data->departtime.'</p></td>
											<td><p class=MsoNormal> '.date("d/m/Y",strtotime($data->returndate)).'</p></td>
											<td><p class=MsoNormal> '.$data->returntime.'</p></td>
											<td><p class=MsoNormal> '.number_format($breakfast).'</p></td>
											<td><p class=MsoNormal> '.number_format($lunch).'</p></td>
											<td><p class=MsoNormal> '.number_format($dinner).'</p></td>
											<td><p class=MsoNormal> '.number_format($pocket).'</p></td>
											<td><p class=MsoNormal> '.$papua.'</p></td>
											<td><p class=MsoNormal> '.$data->remarks.'</p></td>

								</tr>';
								$no++;
							}
							$this->mailbody .='</table>
							<ul>
								<li><b><span>Total Amount Detail : '.number_format($val_tamount).'</span></b></li>
								<li><b><span>Total Bisnis Trip : '.number_format($totalbt).'</span></b></li>
								<li><b><span>Less Advance : '.number_format($less).'</span></b></li>
								<li><b><span>Balance To Be Paid : '.number_format(($val_tamount)-$less).'</span></b></li>
							</ul>
							<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.83.35/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.83.35/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
							$this->mail->addAddress($adb->email, $adb->fullname);
							$this->mail->Subject = "Online Approval System -> Advexpense";
							$this->mail->msgHTML($this->mailbody);
							if (!$this->mail->send()) {
								$err = new Errorlog();
								$err->errortype = "Advexpense Mail";
								$err->errordate = date("Y-m-d H:i:s");
								$err->errormessage = $this->mail->ErrorInfo;
								$err->user = $this->currentUser->username;
								$err->ip = $this->ip;
								$err->save();
								echo "Mailer Error: " . $this->mail->ErrorInfo;
							} else {
								echo "Message sent!";
							}

							$Advexpensehistory = new Advexpensehistory();
							$Advexpensehistory->date = date("Y-m-d H:i:s");
							$Advexpensehistory->fullname = $Employee->fullname;
							$Advexpensehistory->advexpense_id = $id;
							$Advexpensehistory->approvaltype = "Originator";
							$Advexpensehistory->actiontype = 2;
							$Advexpensehistory->save();
						}else{
							$Advexpensehistory = new Advexpensehistory();
							$Advexpensehistory->date = date("Y-m-d H:i:s");
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
							if ($dx->approver->approvaltype_id == 36){
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

							// $Advexpense = Advexpense::find('all', array('conditions' => array("RequestStatus=1"),'include' => array('employee')));
							$Advexpense = Advexpense::find('all', array('conditions' => array("RequestStatus >0"),'include' => array('employee')));

							foreach ($Advexpense as $result) {
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_advexpenseapproval.approver_id = tbl_approver.id) ";					
								$Advexpenseapproval = Advexpenseapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and advexpense_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								if($Advexpenseapproval->approver->employee_id==$emp_id){
									$request[]=$result->id;
								}
								$Advexpenseapproval = Advexpenseapproval::find('first',array('joins'=>$joinx,'conditions' => array("advexpense_id=? and tbl_approver.employee_id = ? and approvalstatus!=0",$result->id,$emp_id),'include' => array('approver'=>array('employee'))));							
								if(count($Advexpenseapproval)>0 && ($result->requeststatus==3 || $result->requeststatus==4)){
									$request[]=$result->id;
								}
							}
							$Advexpense = Advexpense::find('all', array('conditions' => array("id in (?)",$request),'order'=>"tbl_advexpense.requeststatus",'include' => array('employee')));
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
							
							// if($Employee->location->sapcode=='0200' || $this->currentUser->isadmin){
								$Advexpense = Advexpense::find('all',array('joins'=>$join,'select'=>$sel,'conditions' => array('tbl_advexpense.CreatedDate between ? and ?',$query['startDate'],$query['endDate'] ),'include' => array('employee'=>array('company','department'))));
							// }else{
							// 	$Advexpense = Advexpense::find('all',array('joins'=>$join,'select'=>$sel,'conditions' => array('tbl_advexpense.RequestStatus=3 or tbl_advexpense.RequestStatus=5 and tbl_employee.company_id=?',$Employee->company_id ),'include' => array('employee'=>array('company','department'))));
							// }
							
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
							// print_r($data);

							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join   = "LEFT JOIN tbl_approver ON (tbl_advexpenseapproval.approver_id = tbl_approver.id) ";
							if (isset($data['mode'])){
								$Advexpenseapproval = Advexpenseapproval::find('first', array('joins'=>$join,'conditions' => array("advexpense_id=? and tbl_approver.employee_id=? and  ApprovalStatus = 0",$doid,$Employee->id),'order' => 'tbl_approver.sequence','include' => array('approver'=>array('employee','approvaltype'))));
								//start for update all duplicate approver
								// $Advexpenseapprovalx = Advexpenseapproval::find('all', array('joins'=>$join,'conditions' => array("advexpense_id=? and tbl_approver.employee_id=?",$doid,$Employee->id),'include' => array('approver'=>array('employee','approvaltype'))));
								//end for update all duplicate approver
								unset($data['mode']);
							}else{
								$Advexpenseapproval = Advexpenseapproval::find($this->post['id'],array('include' => array('approver'=>array('employee','approvaltype'))));
							}

							// $Advexpense = Advexpense::find($doid);
							// foreach($data as $key=>$val) {
							// 	if(($key !== 'approvalstatus') && ($key !== 'approvaldate') && ($key !== 'remarks') ) {
							// 		// if(($key == 'isrepair') || ($key == 'isscrap')) {
							// 			$value=(($val===0) || ($val==='0') || ($val==='false'))?false:((($val===1) || ($val==='1') || ($val==='true'))?true:$val);
							// 		// }
							// 		$Advexpense->$key=$value;
							// 	}
							// }
							
							// $Advexpense->save();
							unset($data['advanceno']);
							unset($data['lessadvance']);
							
							unset($data['startdate']);
							unset($data['enddate']);

							// foreach ($Advexpenseapprovalx as $approval){
								$olddata = $Advexpenseapproval->to_array();
								foreach($data as $key=>$val){
									$val=($val=='false')?false:(($val=='true')?true:$val);
									$Advexpenseapproval->$key=$val;
								}
								
								$Advexpenseapproval->save();
								$logger = new Datalogger("Advexpenseapproval","update",json_encode($olddata),json_encode($data));
								$logger->SaveData();
							// }
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
								$Advexpensehistory->date = date("Y-m-d H:i:s");
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
										if ($Advexpenseapproval->approver->approvaltype_id == 36 || $Advexpenseapproval->approver->isfinal == 1){
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

											// if($Advexpense->lessadvance !== null || $Advexpense->lessadvance !== 0) {
											// 	$Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND requeststatus=5",$Advexpense->employee->id)));
											// 	$Advance->requeststatus=3;
											// 	$Advance->save();
											// }

											if($Advexpense->advanceno == null || $Advexpense->advanceno == '') {
											} else {
												$Advance = Advance::find('first', array('conditions'=> array("employee_id=? AND advanceno=?",$Advexpense->employee->id,$Advexpense->advanceno)));
												$Advance->isused=1;
												$Advance->save();
											}


											$complete =true;
											echo 2;

										}
										else{
											echo 22;

											$Advexpense->requeststatus = 1;
											$emto=$adb->email;$emname=$adb->fullname;
											$this->mail->Subject = "Online Approval System -> New Expense";
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

						$Advexpensedetail = Advexpensedetail::find('all',array('conditions'=>array("advexpense_id=?",$doid),'include'=>array('advexpense'=>array('employee'=>array('company','department','designation','grade','location')))));	
						$Advexpensedetailbt = Advexpensedetailbt::find('all',array('conditions'=>array("advexpense_id=?",$doid),'include'=>array('advexpense'=>array('employee'=>array('company','department','designation','grade','location')))));	
						if($Advexpense->paymenttype == 0) {
							$less = 0;
						} else {
							$less = $Advexpense->lessadvance;
						}
						$this->mailbody .='
						<table border=1 cellspacing=0 cellpadding=3 width=683>
						<tr>
							<th><p class=MsoNormal>Start Date</p></th>
							<th><p class=MsoNormal>End Date</p></th>
							<th><p class=MsoNormal>Remarks</p></th>
						</tr>
						<tr style="height:22.5pt">
							<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Advexpense->startdate)).'</p></td>
							<td><p class=MsoNormal> '.date("d/m/Y",strtotime($Advexpense->enddate)).'</p></td>
							<td><p class=MsoNormal> '.$Advexpense->reason.'</p></td>
						</tr>
						</table>
						<table border=1 cellspacing=0 cellpadding=3 width=683>
								<tr><th><p class=MsoNormal>No</p></th>
									<th><p class=MsoNormal>Expense Type</p></th>
									<th><p class=MsoNormal>Purpose/Description</p></th>
									<th><p class=MsoNormal>Receipt Date</p></th>
									<th><p class=MsoNormal>Amount</p></th>
									<th><p class=MsoNormal>Currency</p></th>
									<th><p class=MsoNormal>Exchange rate</p></th>
									<th><p class=MsoNormal>Payment Amount in Local Currency</p></th>
									<th><p class=MsoNormal>Cost Centre</p></th>
									<th><p class=MsoNormal>Negara</p></th>
									<th><p class=MsoNormal>Location/City</p></th>
									<th><p class=MsoNormal>Remarks</p></th>
								</tr>
						';
						$no=1;
						foreach ($Advexpensedetail as $data){
							$val_tamount += $data->amount;
							$this->mailbody .='<tr style="height:22.5pt">
										<td><p class=MsoNormal> '.$no.'</p></td>
										<td><p class=MsoNormal> '.$data->expensetype.'</p></td>
										<td><p class=MsoNormal> '.$data->purpose.'</p></td>
										<td><p class=MsoNormal> '.date("d/m/Y",strtotime($data->receiptdate)).'</p></td>
										<td><p class=MsoNormal> '.number_format($data->amount).'</p></td>
										<td><p class=MsoNormal> '.$data->currency.'</p></td>
										<td><p class=MsoNormal> '.number_format($data->exchangerate).'</p></td>
										<td><p class=MsoNormal> '.number_format($data->paymentamount).'</p></td>
										<td><p class=MsoNormal> '.$data->costcentre.'</p></td>
										<td><p class=MsoNormal> '.$data->country.'</p></td>
										<td><p class=MsoNormal> '.$data->location.'</p></td>
										<td><p class=MsoNormal> '.$data->remarks.'</p></td>

							</tr>';
							$no++;
						}
						$this->mailbody .= '</table>
							<table border=1 cellspacing=0 cellpadding=3 width=683>
										<tr><th><p class=MsoNormal>No</p></th>
											<th><p class=MsoNormal>Depart Date</p></th>
											<th><p class=MsoNormal>Depart Time</p></th>
											<th><p class=MsoNormal>Return Date</p></th>
											<th><p class=MsoNormal>Return Time</p></th>
											<th><p class=MsoNormal>Breakfast</p></th>
											<th><p class=MsoNormal>Lunch</p></th>
											<th><p class=MsoNormal>Dinner</p></th>
											<th><p class=MsoNormal>Pocket</p></th>
											<th><p class=MsoNormal>isPapua ?</p></th>
											<th><p class=MsoNormal>Remarks</p></th>
										</tr>
						';
						foreach ($Advexpensedetailbt as $data){
							if($data->ispapua == 0) {
								$sppd = Advexpsppd::find('first',
									array(
										'conditions'=>array("level=? and ispapua=0",$Advexpense->employee->level_id)
									)
								);

							} else if($data->ispapua == 1) {
								$sppd = Advexpsppd::find('first',
									array(
										'conditions'=>array("level=? and ispapua=1",$Advexpense->employee->level_id)
									)
								);

								
							} else if($data->ispapua == 2) {
								$sppd = Advexpsppd::find('first',
									array(
										'conditions'=>array("level=? and ispapua=2",$Advexpense->employee->level_id)
									)
								);

								
							} 

							$breakfast = ($data->breakfast == 1) ? 0 : $sppd->breakfast;
							$lunch = ($data->lunch == 1) ? 0 : $sppd->lunch;
							$dinner = ($data->dinner == 1) ? 0 : $sppd->dinner;
							$pocket = ($data->pocket == 1) ? 0 : $sppd->pocket;

							$jml_breakfast += $breakfast;
							$jml_lunch += $lunch;
							$jml_dinner += $dinner;
							$jml_pocket += $pocket;

							$totalbt = $jml_breakfast+$jml_lunch+$jml_dinner+$jml_pocket;

							$papua = ($data->ispapua == 0) ? "TIDAK" : "YA";

							$this->mailbody .='<tr style="height:22.5pt">
										<td><p class=MsoNormal> '.$no.'</p></td>
										<td><p class=MsoNormal> '.date("d/m/Y",strtotime($data->departdate)).'</p></td>
										<td><p class=MsoNormal> '.$data->departtime.'</p></td>
										<td><p class=MsoNormal> '.date("d/m/Y",strtotime($data->returndate)).'</p></td>
										<td><p class=MsoNormal> '.$data->returntime.'</p></td>
										<td><p class=MsoNormal> '.number_format($breakfast).'</p></td>
										<td><p class=MsoNormal> '.number_format($lunch).'</p></td>
										<td><p class=MsoNormal> '.number_format($dinner).'</p></td>
										<td><p class=MsoNormal> '.number_format($pocket).'</p></td>
										<td><p class=MsoNormal> '.$papua.'</p></td>
										<td><p class=MsoNormal> '.$data->remarks.'</p></td>

							</tr>';
							$no++;
						}
						$this->mailbody .='</table>
						<ul>
								<li><b><span>Total Amount Detail : '.number_format($val_tamount).'</span></b></li>
								<li><b><span>Total Bisnis Trip : '.number_format($totalbt).'</span></b></li>
								<li><b><span>Less Advance : '.number_format($less).'</span></b></li>
								<li><b><span>Balance To Be Paid : '.number_format(($val_tamount)-$less).'</span></b></li>
						</ul>
						<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.83.35/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.83.35/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
						
								
								$this->mail->msgHTML($this->mailbody);
								if ($complete){
									$filePath= $this->generatePDFi($doid);
									$Mailrecipient = Mailrecipient::find('all',array('conditions'=>array("module='Advance' and company_list like ? and isActive='1' ","%".$Advexpense->employee->companycode."%")));
									foreach ($Mailrecipient as $data){
										$this->mail->AddCC($data->email);
									}
									$this->mail->addAttachment($filePath);
								}
								if (!$this->mail->send()) {
									$err = new Errorlog();
									$err->errortype = "Advexpense Mail";
									$err->errordate = date("Y-m-d H:i:s");
									$err->errormessage = $this->mail->ErrorInfo;
									$err->user = $this->currentUser->username;
									$err->ip = $this->ip;
									$err->save();
									echo "Mailer Error: " . $this->mail->ErrorInfo;
								} else {
									$this->processcopy($this->pathcopy);
									
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
						} else if(isset($query['checkmnp'])){
							$Advexpensedetailmnp = Advexpensedetail::find('first', array('conditions' => array("advexpense_id=? and expensetype='MNP' ",$query['advexpense_id'])));
							$data=array("jml"=>number_format($Advexpensedetailmnp->amount));
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

	function advexpenseDetailbt(){
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
							// $Advexpensedetailbt = Advexpensedetailbt::find('all', array('joins'=>$join,'select'=>$select,'conditions' => array("advexpense_id=?",$id)));
							$Advexpensedetailbt = Advexpensedetailbt::find('all', array('conditions' => array("advexpense_id=?",$id)));
							foreach ($Advexpensedetailbt as &$result) {
								$result	= $result->to_array();
							}
	
							echo json_encode($Advexpensedetailbt, JSON_NUMERIC_CHECK);
						}else{
							$Advexpensedetailbt = new Advexpensedetailbt();
							echo json_encode($Advexpensedetailbt);
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							if($query['status'] == 'approver') {
								$Advexpensedetailbt = Advexpensedetailbt::find('all', array('conditions' => array("advexpense_id=?",$query['advexpense_id'])));
							} else if($query['status'] == 'checktime') {
								$Advexpensedetailbt = Advexpensedetailbtcheck::all(array('group' => 'advexpense_id', 'having' => 'advexpense_id = '.$query['advexpense_id']));
								// $Advexpensedetailbt = Advexpensedetailbtcheck::find('all', array('having' =>"advexpense_id=?",$query['advexpense_id']));

							}
							$data=array("jml"=>count($Advexpensedetailbt));
						}else if(isset($query['filter'])){
							$join = "LEFT JOIN vwadvexpensereport on tbl_advexpensedetail_bt.advexpense_id=vwadvexpensereport.id";
							$sel = 'tbl_advexpensedetail_bt.*, vwadvexpensereport.CreatedDate ';
							$Advexpensedetailbt = Advexpensedetailbt::find('all',array('joins'=>$join,'select'=>$sel,'conditions' => array('vwadvexpensereport.CreatedDate between ? and ?',$query['startDate'],$query['endDate'] )));
							foreach ($Advexpensedetailbt as &$result) {	
								$result	= $result->to_array();
							}
							$data=$Advexpensedetailbt;
						}else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);

						$Advexpensedetailbt = Advexpensedetailbt::create($data);
						$logger = new Datalogger("Advexpensedetailbt","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Advexpensedetailbt = Advexpensedetailbt::find($id);
						$data=$Advexpensedetailbt->to_array();
						$Advexpensedetailbt->delete();
						$logger = new Datalogger("Advexpensedetailbt","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Advexpensedetailbt);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						
						$Advexpensedetailbt = Advexpensedetailbt::find($id);
						$olddata = $Advexpensedetailbt->to_array();
						// foreach($data as $key=>$val){
						// 	$Advexpensedetailbt->$key=$val;
						// }
						foreach($data as $key=>$val){
							// $val=($val=='true')?1:0;
							if($val=='true') {
								$val = 1;
							}else if($val=='false') {
								$val = 0;
							}
							$Advexpensedetailbt->$key=$val;
							
						}
						// $exprice = $Advexpensedetailbt->unitprice * $Advexpensedetailbt->qty;
						// $Advexpensedetailbt->extendedprice = $exprice;
						$Advexpensedetailbt->save();

						$Advexpense = Advexpense::find($Advexpensedetailbt->advexpense_id);
						$creatorId=$Advexpense->employee_id;
						$Employee = Employee::find($creatorId);
						$Expensebt = Advexpensedetailbt::find('all',array('conditions'=>array("advexpense_id=?",$Advexpensedetailbt->advexpense_id),'include'=>array('advexpense'=>array('employee'=>array('company','department','designation','grade','location')))));	
						
						foreach($Expensebt as $vals) {
							if($vals->ispapua == 0) {
								$sppd = Advexpsppd::find('first',
									array(
										'conditions'=>array("level=? and ispapua=0",$Employee->level_id)
									)
								);
							} else if($vals->ispapua == 1) {
								$sppd = Advexpsppd::find('first',
									array(
										'conditions'=>array("level=? and ispapua=1",$Employee->level_id)
									)
								);
							} else if($vals->ispapua == 2) {
								$sppd = Advexpsppd::find('first',
									array(
										'conditions'=>array("level=? and ispapua=2",$Employee->level_id)
									)
								);
							}

							// $jml_breakfast = 0;
							// $jml_lunch = 0;
							// $jml_dinner = 0;
							// $jml_pocket = 0;

							$breakfast = ($vals->breakfast == 1) ? 0 : $sppd->breakfast;
							$lunch = ($vals->lunch == 1) ? 0 : $sppd->lunch;
							$dinner = ($vals->dinner == 1) ? 0 : $sppd->dinner;
							$pocket = ($vals->pocket == 1) ? 0 : $sppd->pocket;
		
							$jml_breakfast += $breakfast;
							$jml_lunch += $lunch;
							$jml_dinner += $dinner;
							$jml_pocket += $pocket;
		
							$totalbt = $jml_breakfast+$jml_lunch+$jml_dinner+$jml_pocket;

							// print_r($sppd);
							
						}

						// echo $totalbt;
						$Expensedetail = Advexpensedetail::find('first',array('conditions'=>array("advexpense_id=? and purpose='Meals & Pocket'",$Advexpense->id),'include'=>array('advexpense'=>array('employee'=>array('company','department','designation','grade','location')))));	
						// print_r($Expensedetail);
						if(count($Expensedetail)==0) {

							$databt['advexpense_id'] = $Advexpense->id;
							$databt['expensetype'] = 'MNP';
							$databt['purpose'] = 'Meals & Pocket';
							$databt['amount'] = $totalbt;
							$databt['currency'] = 'IDR';
							
							$Advexpensedetail = Advexpensedetail::create($databt);
							$loggers = new Datalogger("Advexpensedetail from bt","create",null,json_encode($data));
							$loggers->SaveData();
						} else {
							$Expensedetail->amount = $totalbt;
							$Expensedetail->save();
						}

						$logger = new Datalogger("Advexpensedetailbt","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						echo json_encode($Advexpensedetailbt);
						
						break;
					default:
						$Advexpensedetailbt = Advexpensedetailbt::all();
						foreach ($Advexpensedetailbt as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Advexpensedetailbt, JSON_NUMERIC_CHECK);
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
		$Advexpensedetailbt = Advexpensedetailbt::find('all',array('conditions'=>array("advexpense_id=?",$id),'include'=>array('advexpense'=>array('employee'=>array('company','department','designation','grade','location')))));	
		
		$creatorId=$Advexpense->employee_id;
		$superiorId=$Advexpense->superior;
		$Employee = Employee::find($creatorId);
		$Superior = Employee::find($superiorId);
		$supAdb = Addressbook::find('first',array('conditions'=>array("username=?",$Superior->loginname)));
		$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Advexpense->employee->loginname)));
		$email=$usr->email;
		$fullname=$Advexpense->employee->fullname;
		$department = $Advexpense->employee->department->departmentname;

		$startdate = $Advexpense->startdate;
		$enddate = $Advexpense->enddate;

		$joinx   = "LEFT JOIN tbl_approver ON (tbl_advexpenseapproval.approver_id = tbl_approver.id) ";					
		$Advexpenseapproval = Advexpenseapproval::find('all',array('joins'=>$joinx,'conditions' => array("advexpense_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee','approvaltype'))));							
		
		try {
			$excel = new COM("Excel.Application") or die ("ERROR: Unable to instantaniate COM!\r\n");
			$excel->Visible = false;

				$title = 'advexpense';
				$file= SITE_PATH."/doc/hr/advexpense.xls";
				
				$Workbook = $excel->Workbooks->Open($file,false,true) or die("ERROR: Unable to open " . $file . "!\r\n");
				$Worksheet = $Workbook->Worksheets(1);
				$Worksheet->Activate;

				$Worksheet->Range("D6")->Value = $Advexpense->employee->sapid;
				$Worksheet->Range("D8")->Value = $Advexpense->name;
				$Worksheet->Range("D10")->Value = $Advexpense->email;
				$Worksheet->Range("D12")->Value = $Advexpense->costcenter;
				$Worksheet->Range("D14")->Value = date("d/m/Y",strtotime($startdate));

				$Worksheet->Range("I10")->Value = $Advexpense->bg;
				$Worksheet->Range("I12")->Value = $Advexpense->location;
				$Worksheet->Range("I14")->Value = date("d/m/Y",strtotime($enddate));


				// $Worksheet->Range("M10")->Value = $expense;
				// $Worksheet->Range("E14")->Value = $Advexpense->beneficiary;
				// $Worksheet->Range("E15")->Value = $Advexpense->accountname;
				// $Worksheet->Range("E16")->Value = $Advexpense->bank;
				// $Worksheet->Range("E17")->Value = $Advexpense->accountnumber;

				foreach ($Advexpenseapproval as $data){
					if(($data->approver->approvaltype->id==35) || ($data->approver->employee_id==$Advexpense->superior)){
						$superiorname = $data->approver->employee->fullname;
						// $superiordate = 'Date : '.date("d/m/Y",strtotime($data->approvaldate));
						$superiordate = date("d/m/Y",strtotime($data->approvaldate));
					}
				
					if($data->approver->approvaltype->id==36) {
						$hrdheadname = $data->approver->employee->fullname;
						$hrdheaddate = date("d/m/Y",strtotime($data->approvaldate));
					}
					
				}
				$picpath= SITE_PATH."/images/approved.png";
				
				$Worksheet->Range("B36")->Value = $Advexpense->name;
				$Worksheet->Range("B45")->Value = 'Name / SAP : '.$Advexpense->name;
				$Worksheet->Range("D36")->Value = date("d/m/Y",strtotime($Advexpense->createddate));
				$Worksheet->Range("B59")->Value = $Advexpense->name;
				$Worksheet->Range("D59")->Value = date("d/m/Y",strtotime($Advexpense->createddate));

				$pic=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
				$pic->Height  = 20;
				$pic->Top = $excel->Cells(36, 3)->Top ;
				$pic->Left = $excel->Cells(36, 3)->Left + 80;
				$picc=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
				$picc->Height  = 20;
				$picc->Top = $excel->Cells(59, 3)->Top ;
				$picc->Left = $excel->Cells(59, 3)->Left + 80 ;

					if(!empty($superiorname)) {
						$Worksheet->Range("F36")->Value = $superiorname;
						$Worksheet->Range("F59")->Value = $superiorname;
						$Worksheet->Range("H36")->Value = $superiordate;
						$Worksheet->Range("H59")->Value = $superiordate;
						$pic1=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic1->Height  = 20;
						$pic1->Top = $excel->Cells(36, 7)->Top ;
						$pic1->Left = $excel->Cells(36, 7)->Left ;
						$pic11=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic11->Height  = 20;
						$pic11->Top = $excel->Cells(59, 7)->Top ;
						$pic11->Left = $excel->Cells(59, 7)->Left ;
					}
	
					if(!empty($hrdheadname)) {
						$Worksheet->Range("K36")->Value = $hrdheadname;
						$Worksheet->Range("K59")->Value = $hrdheadname;
						$Worksheet->Range("L36")->Value = $hrdheaddate;
						$Worksheet->Range("L59")->Value = $hrdheaddate;
						$pic2=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic2->Height  = 20;
						$pic2->Top = $excel->Cells(36, 12)->Top ;
						$pic2->Left = $excel->Cells(36, 12)->Left +20 ;
						$pic22=$Worksheet->Shapes->AddPicture($picpath, False, True, 0, 0, -1, -1);
						$pic22->Height  = 20;
						$pic22->Top = $excel->Cells(59, 12)->Top ;
						$pic22->Left = $excel->Cells(59, 12)->Left +20 ;
					}
	
				foreach ($Advexpensedetail as $data){
					$val_tamount += $data->amount;
				}
				
				if($Advexpense->paymenttype == 0) {
					$lessadvance = 0;
				} else {
					$lessadvance = $Advexpense->lessadvance;
				}
				// $Worksheet->Range("E21")->Value = number_format($val_tamount);
				// $Worksheet->Range("E23")->Value = number_format($lessadvance);
				// $Worksheet->Range("E25")->Value = number_format($val_tamount-$lessadvance);

				for ($b=22;$b<22+count($Advexpensedetailbt);$b++){
					if($Advexpensedetailbt[$b-22]->ispapua == 0) {
						$sppd = Advexpsppd::find('first',
							array(
								'conditions'=>array("level=? and ispapua=0",$Employee->level_id)
							)
						);

					} else if($Advexpensedetailbt[$b-22]->ispapua == 1) {
						$sppd = Advexpsppd::find('first',
							array(
								'conditions'=>array("level=? and ispapua=1",$Employee->level_id)
							)
						);

						
					} else if($Advexpensedetailbt[$b-22]->ispapua == 2) {
						$sppd = Advexpsppd::find('first',
							array(
								'conditions'=>array("level=? and ispapua=2",$Employee->level_id)
							)
						);

						
					}

					$breakfastro = ($Advexpensedetailbt[$b-22]->breakfast == 1) ? 0 : $sppd->breakfast;
					$lunchro = ($Advexpensedetailbt[$b-22]->lunch == 1) ? 0 : $sppd->lunch;
					$dinnerro = ($Advexpensedetailbt[$b-22]->dinner == 1) ? 0 : $sppd->dinner;
					$pocketro = ($Advexpensedetailbt[$b-22]->pocket == 1) ? 0 : $sppd->pocket;

					$jml_breakfastro += $breakfastro;
					$jml_lunchro += $lunchro;
					$jml_dinnerro += $dinnerro;
					$jml_pocketro += $pocketro;

					$totalbtro = $jml_breakfastro+$jml_lunchro+$jml_dinnerro+$jml_pocketro;
				}

				// $Worksheet->Range("E22")->Value = $val_tamount+$totalbtro;
				// return $val_tamount;
				// print_r($val_tamount);
				$Worksheet->Range("E22")->Value = round($val_tamount,2);
				$Worksheet->Range("E24")->Value = $lessadvance;
				$Worksheet->Range("E26")->Value = ($val_tamount)-$lessadvance;
				// $Worksheet->Range("E26")->Value = ($val_tamount+$totalbtro)-$lessadvance;
				
	
				$xlShiftDown=-4121;
				$no = 1;
				$nos = 1;

				for ($a=19;$a<19+count($Advexpensedetail);$a++){
					$Worksheet->Rows($a+1)->Copy();
					$Worksheet->Rows($a+1)->Insert($xlShiftDown);
					// $Worksheet->Range("A".$a)->Value = $no++;
					$Worksheet->Range("B".$a)->Value = $Advexpensedetail[$a-19]->expensetype;
					$Worksheet->Range("C".$a)->Value = $Advexpensedetail[$a-19]->purpose;
					$Worksheet->Range("D".$a)->Value = $Advexpensedetail[$a-19]->receiptdate;
					$Worksheet->Range("E".$a)->Value = $Advexpensedetail[$a-19]->amount;
					$Worksheet->Range("F".$a)->Value = $Advexpensedetail[$a-19]->currency;
					$Worksheet->Range("G".$a)->Value = $Advexpensedetail[$a-19]->exchangerate;
					$Worksheet->Range("H".$a)->Value = $Advexpensedetail[$a-19]->paymentamount;
					$Worksheet->Range("I".$a)->Value = $Advexpensedetail[$a-19]->costcentre;
					$Worksheet->Range("J".$a)->Value = $Advexpensedetail[$a-19]->country;
					$Worksheet->Range("K".$a)->Value = $Advexpensedetail[$a-19]->location;
					$Worksheet->Range("L".$a)->Value = $Advexpensedetail[$a-19]->remarks;
				}

				

			
				$jmldetail = count($Advexpensedetail);

				for ($b=52;$b<52+count($Advexpensedetailbt);$b++){
					if($Advexpensedetailbt[$b-52]->ispapua == 0) {
						$sppd = Advexpsppd::find('first',
							array(
								'conditions'=>array("level=? and ispapua=0",$Employee->level_id)
							)
						);

					} else if($Advexpensedetailbt[$b-52]->ispapua == 1) {
						$sppd = Advexpsppd::find('first',
							array(
								'conditions'=>array("level=? and ispapua=1",$Employee->level_id)
							)
						);

						
					} else if($Advexpensedetailbt[$b-52]->ispapua == 2) {
						$sppd = Advexpsppd::find('first',
							array(
								'conditions'=>array("level=? and ispapua=2",$Employee->level_id)
							)
						);

						
					}

					$breakfast = ($Advexpensedetailbt[$b-52]->breakfast == 1) ? 0 : $sppd->breakfast;
					$lunch = ($Advexpensedetailbt[$b-52]->lunch == 1) ? 0 : $sppd->lunch;
					$dinner = ($Advexpensedetailbt[$b-52]->dinner == 1) ? 0 : $sppd->dinner;
					$pocket = ($Advexpensedetailbt[$b-52]->pocket == 1) ? 0 : $sppd->pocket;

					$jml_breakfast += $breakfast;
					$jml_lunch += $lunch;
					$jml_dinner += $dinner;
					$jml_pocket += $pocket;

					$totalbt = $jml_breakfast+$jml_lunch+$jml_dinner+$jml_pocket;

					$Worksheet->Rows($b+$jmldetail+1)->Copy();
					$Worksheet->Rows($b+$jmldetail+1)->Insert($xlShiftDown);
					$Worksheet->Range("B".($b+$jmldetail))->Value = $nos++;
					$Worksheet->Range("C".($b+$jmldetail))->Value = $Advexpensedetailbt[$b-52]->departdate;
					$Worksheet->Range("D".($b+$jmldetail))->Value = $Advexpensedetailbt[$b-52]->departtime;
					$Worksheet->Range("E".($b+$jmldetail))->Value = $Advexpensedetailbt[$b-52]->returndate;
					$Worksheet->Range("F".($b+$jmldetail))->Value = $Advexpensedetailbt[$b-52]->returntime;
					$Worksheet->Range("G".($b+$jmldetail))->Value = $breakfast;
					// $Worksheet->Range("G".($b+$jmldetail))->Value = $Advexpensedetailbt[$b-52]->breakfast;
					$Worksheet->Range("H".($b+$jmldetail))->Value = $lunch;
					$Worksheet->Range("I".($b+$jmldetail))->Value = $dinner;
					$Worksheet->Range("J".($b+$jmldetail))->Value = $pocket;
				}
				
				// $Worksheet->Range("B19")->Value = '';
				// $Worksheet->Range("C19")->Value = 'Meals & Pocket';
				// $Worksheet->Range("D19")->Value = '';
				// $Worksheet->Range("E19")->Value = $totalbt;
				// $Worksheet->Range("F19")->Value = 'IDR';
				// $Worksheet->Range("G19")->Value = '';
				// $Worksheet->Range("H19")->Value = '';
				// $Worksheet->Range("I19")->Value = '';
				// $Worksheet->Range("J19")->Value = '';
				// $Worksheet->Range("K19")->Value = '';
				// $Worksheet->Range("L19")->Value = '';

				//end condition


			$xlTypePDF = 0;
			$xlQualityStandard = 0;
			$fileName =$title.'_'.$Advexpense->employee->fullname.'_'.$Advexpense->employee->sapid.'_'.date("YmdHis").'.pdf';
			$fileName =  preg_replace("/[^a-z0-9\_\-\.]/i", '', $fileName);
			$filePath = 'doc'.DS.'hr'.DS.'pdf'.DS.$fileName;
			//$path= SITE_PATH.'/doc'.DS.'hr'.DS.'pdf'.DS.$title.'_'.$Advexpense->employee->fullname.'_'.$Advexpense->employee->sapid.'_'.date("YmdHis").'.pdf';
			$path = SITE_PATH.DS.$filePath;
			$pathcopy = 'doc\\hr\\pdf\\' . $title . '_' . $Advexpense->employee->fullname . '_' . $Advexpense->employee->sapid . '_' . date("YmdHis") . '.pdf';
			if (file_exists($path)) {
				unlink($path);
			}	
			
			$Worksheet->ExportAsFixedFormat($xlTypePDF, $path, $xlQualityStandard);
			
			// $excel->Application->CutCopyMode(false);
			$excel->CutCopyMode = false;
			$Workbook->Close(false);
			unset($Worksheet);
			unset($Workbook);
			$excel->Workbooks->Close();
			$excel->Quit();
			unset($excel);
			
			$output = 200;
			echo json_encode($output);
			
			$this->pathcopy = $filePath;
			// $this->processcopy($filePath);
			$Advexpense->approveddoc=str_replace("\\","/",$filePath);
			$Advexpense->save();
			return $filePath;

		} catch(com_exception $e) {  
			$err = new Errorlog();
			$err->errortype = "AdvexpensePDFGenerator";
			$err->errordate = date("Y-m-d H:i:s");
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
		$path_to_file = "upload\\advexpense\\".$id."_".time()."_".$_FILES['myFile']['name'];
		$path_to_file = str_replace("%","_",$path_to_file);
		$path_to_file = str_replace(" ","_",$path_to_file);
		echo $path_to_file;
        move_uploaded_file($_FILES['myFile']['tmp_name'], $path_to_file);

		$this->processcopy($path_to_file);
	}

}