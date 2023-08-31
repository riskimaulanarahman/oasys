<?php


Class Internalhiringmodule extends Application{
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
				case 'apiinternalhiring':
					$this->internalhiring();
					break;
				case 'apiinternalhiringdetail':
					$this->internalhiringDetail();
					break;
				case 'apiinternalhiringmaster':
					$this->internalhiringMaster();
					break;
				case 'uploadlampiran':
					$this->uploadLampiran();
					break;
				default:
					break;
			}
		}
	}
	
	function internalhiring(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{

			switch ($this->post['criteria']){
				case 'byid':
					$id = $this->post['id'];
					$join = "LEFT JOIN vwadvancereport ON tbl_advance.id = vwadvancereport.id";
					$select = "tbl_advance.*,vwadvancereport.apprstatuscode";
					// $Advance = Advance::find($id, array('include' => array('employee'=>array('company','department','designation'))));
					$Advance = Advance::find($id, array('joins'=>$join,'select'=>$select,'include' => array('employee'=>array('company','department','designation'))));

					if ($Advance){
						$fullname = $Advance->employee->fullname;
						$department = $Advance->employee->department->departmentname;
						$data=$Advance->to_array();
						$data['fullname']=$fullname;
						$data['department']=$department;
						echo json_encode($data, JSON_NUMERIC_CHECK);
					}else{
						$Advance = new Advance();
						echo json_encode($Advance);
					}
					break;
				case 'find':
					$query = $this->post['status'];
					$data = $this->post['data'];
					switch ($query){
						case 'checksapid':
							$Employee = Employee::find('first', array('conditions' => array("SAPID=?",$data['sapid']),'include' => array('company','department','designation','location','level')));
							$department = $Employee->department->departmentname;
							$designation = $Employee->designation->designationname;
							$location = $Employee->location->location;
							$level = $Employee->level->level;

							if($Employee>0) {
								$result['status'] = 200;
								$result['data'] = $Employee->to_array();
								$result['data']['department'] = $department;
								$result['data']['designation'] = $designation;
								$result['data']['location'] = $location;
								$result['data']['level'] = $level;
							} else {
								$result['status'] = 404;
								$result['data'] = null;
							}
								
							echo json_encode($result, JSON_NUMERIC_CHECK);
						break;

						case 'checkstatus':
							$checkih = Internalhiringdetail::find('first', array('conditions' => array("SAPID=? and passcode=?",$data['sapid'],$data['passcode']),'order'=>"tbl_internalhiringdetail.status desc")); //check sapid
							if($checkih>0) {
								$internalhiring = Internalhiring::find('first',array('conditions' => array("id=?",$checkih->ih_id)));
								$result['status'] = 200;
								$result['data'] = $checkih->to_array();
								$result['data']['detail'] = $internalhiring->to_array();
							} else {
								$result['status'] = 404;
								$result['data'] = null;
							}
							echo json_encode($result, JSON_NUMERIC_CHECK);

						break;

						case 'checkstatushistory':
							$join = "LEFT JOIN tbl_internalhiring ON tbl_internalhiringdetail.ih_id = tbl_internalhiring.id";
							$select = "tbl_internalhiring.*,tbl_internalhiringdetail.status";
							$internalhiringdetail = Internalhiringdetail::find('all',array('joins'=>$join,'select'=>$select,'conditions' => array("SAPID=?",$data['sapid'])));
							
							if(count($internalhiringdetail)>0) {
								foreach($internalhiringdetail as &$result) {

									$result= $result->to_array();
									
								}
								$data['status'] = 200;
								$data['data'] = $internalhiringdetail;

							} else {
								$data['status'] = 404;
								$data['data'] = null;
							}
							echo json_encode($data, JSON_NUMERIC_CHECK);

						break;

						case 'cancelapplyment':

							$checkih = Internalhiringdetail::find('first', array('conditions' => array("id=?",$data['id']))); //check id

							if($data['id'] !== '') {

								$data['status'] = 200;
								// $ihdetail = new Internalhiringdetail();
								$checkih->status = 0;
								$checkih->save();
							} else {
								$data['status'] = 404;
							}

							echo json_encode($data, JSON_NUMERIC_CHECK);

						break;

						case 'test':
							// $data = (date('Y-m-d') - date('Y-m-d',strtotime($data['joindate'])));
							// $data = date('Y-m-d',strtotime('2021-11-29'));

							// date in Y-m-d format; or it can be in other formats as well
							$birthDate = "1995-11-30";
							$birthDate = explode("-", $birthDate);
							
							$age = (date("md", date("U", mktime(0, 0, 0, $birthDate[2], $birthDate[0], $birthDate[1]))) > date("md")
								? ((date("Y") - $birthDate[2]) - 1)
								: (date("Y") - $birthDate[2]));
							echo "a is:" . $age;
							
							// date in mm/dd/yyyy format; or it can be in other formats as well
							// $birthDate1 = "11/28/2019";
							// $birthDate1 = explode("/", $birthDate1);
							// $age1 = (date("md", date("U", mktime(0, 0, 0, $birthDate1[0], $birthDate1[1], $birthDate1[2]))) > date("md")
							// 	? ((date("Y") - $birthDate1[2]) - 1)
							// 	: (date("Y") - $birthDate1[2]));
							// echo "tahun masuk:" . $age1;
							

							// echo $data;
						break;

						default:
							// $Employee = Employee::find('first', array('conditions' => array("loginName=?",$query['username'])));
							// $Advance = Advance::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
							// foreach ($Advance as &$result) {
							// 	$fullname	= $result->employee->fullname;		
							// 	$result		= $result->to_array();
							// 	$result['fullname']=$fullname;
							// }
							// $data=array("jml"=>count($Advance));
							$internalhiring = Internalhiringdetail::all();
							foreach ($internalhiring as &$result) {
								$result = $result->to_array();
							}
							echo json_encode($internalhiring, JSON_NUMERIC_CHECK);
						break;
					}
					
				break;
				case 'create':		
					$data = $this->post['data'];
					$Employee = Employee::find('first', array('conditions' => array("loginName=?",$data['username']),"include"=>array("location","company","department")));
						unset($data['__KEY__']);
						unset($data['username']);
						$data['employee_id']=$Employee->id;
						$data['RequestStatus']=0;
						$data['isUsed']=0;
						try{
							$code = Advance::find('first',array('select' => "CONCAT('Advance/','".$Employee->companycode."','/',YEAR(CURDATE()),'/',LPAD(MONTH(CURDATE()), 2, '0'),'/',LPAD(CASE when max(substring(advanceno,-4,4)) is null then 1 else max(substring(advanceno,-4,4))+1 end,4,'0')) as advanceno","conditions"=>array("substring(advanceno,9,".strlen($Employee->companycode).")=? and substring(advanceno,".(strlen($Employee->companycode)+10).",4)=YEAR(CURDATE())",$Employee->companycode)));

							$data['advanceno']=$code->advanceno;

							$Advance = Advance::create($data);
							$data['companycode']=$Employee->companycode;
							$data=$Advance->to_array();
							
							$joins   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";

							$ApproverFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=41")));
							if(count($ApproverFC)>0){
								$Advanceapproval = new Advanceapproval();
								$Advanceapproval->advance_id = $Advance->id;
								$Advanceapproval->approver_id = $ApproverFC->id;
								$Advanceapproval->save();
							}

							// $companyBU=( ($Employee->companycode=='KPA') || ($Employee->companycode=='AHL') )?"KPSI":$Employee->companycode;
							// if (($Employee->company->sapcode=='RND') || ($Employee->company->sapcode=='NKF')){
								$ApproverBU = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and CompanyList like '%".$Employee->companycode."%' ")));
							// }else{
							// 	$ApproverBU = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and tbl_employee.companycode=? and not(tbl_employee.id=?)",$companyBU,$Employee->id)));
							// }
							if(count($ApproverBU)>0){
								$Advanceapproval = new Advanceapproval();
								$Advanceapproval->advance_id = $Advance->id;
								$Advanceapproval->approver_id = $ApproverBU->id;
								$Advanceapproval->save();
								$logger = new Datalogger("Advanceapproval","add","Add initial BU Head Approval ",json_encode($Advanceapproval->to_array()));
								$logger->SaveData();
							}

							// $companyFC=(($data['companycode']=='BCL') || ($data['companycode']=='KPA'))?"KPSI":((($data['companycode']=='KPSI'))?"LDU":$Employee->companycode);
							$ApproverBUFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37' and CompanyList like '%".$Employee->companycode."%'")));
							if(count($ApproverBUFC)>0){
								$Advanceapproval = new Advanceapproval();
								$Advanceapproval->advance_id = $Advance->id;
								$Advanceapproval->approver_id = $ApproverBUFC->id;
								$Advanceapproval->save();
								$logger = new Datalogger("Advanceapproval","add","Add initial BU FC Approval",json_encode($Advanceapproval->to_array()));
								$logger->SaveData();
							}

							// if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220")){
							// 	$ApproverBU = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38' and tbl_employee.location_id='8'")));
							// 	if(count($ApproverBU)>0){
							// 		$Advanceapproval = new Advanceapproval();
							// 		$Advanceapproval->advance_id =$Advance->id;
							// 		$Advanceapproval->approver_id = $ApproverBU->id;
							// 		$Advanceapproval->save();
							// 		$logger = new Datalogger("Advanceapproval","add","Add initial BU Approval",json_encode($Advanceapproval->to_array()));
							// 		$logger->SaveData();
							// 	}
								
							// 	$ApproverBUFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37' and tbl_employee.location_id='8'")));
							// 	if(count($ApproverBUFC)>0){
							// 		$Advanceapproval = new Advanceapproval();
							// 		$Advanceapproval->advance_id =$Advance->id;
							// 		$Advanceapproval->approver_id = $ApproverBUFC->id;
							// 		$Advanceapproval->save();
							// 		$logger = new Datalogger("Advanceapproval","add","Add initial BUFC Approval",json_encode($Advanceapproval->to_array()));
							// 		$logger->SaveData();
							// 	}

							// }else{
							// 	$ApproverBU = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='38'  and tbl_employee.company_id=? and not(tbl_employee.location_id='8')",$Employee->company_id)));
							// 	if(count($ApproverBU)>0){
							// 		$Advanceapproval = new Advanceapproval();
							// 		$Advanceapproval->advance_id = $Advance->id;
							// 		$Advanceapproval->approver_id = $ApproverBU->id;
							// 		$Advanceapproval->save();
							// 		$logger = new Datalogger("Advanceapproval","add","Add initial BU Approval",json_encode($Advanceapproval->to_array()));
							// 		$logger->SaveData();
							// 	}

							// 	$ApproverBUFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37'  and tbl_employee.company_id=? and not(tbl_employee.location_id='8')",$Employee->company_id)));
							// 	if(count($ApproverBUFC)>0){
							// 		$Advanceapproval = new Advanceapproval();
							// 		$Advanceapproval->advance_id = $Advance->id;
							// 		$Advanceapproval->approver_id = $ApproverBUFC->id;
							// 		$Advanceapproval->save();
							// 		$logger = new Datalogger("Advanceapproval","add","Add initial BUFC Approval",json_encode($Advanceapproval->to_array()));
							// 		$logger->SaveData();
							// 	}

							// }

							$Advancehistory = new Advancehistory();
							$Advancehistory->date = date("Y-m-d h:i:s");
							$Advancehistory->fullname = $Employee->fullname;
							$Advancehistory->approvaltype = "Originator";
							$Advancehistory->advance_id = $Advance->id;
							$Advancehistory->actiontype = 0;
							$Advancehistory->save();
							
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "CreateAdvance";
							$err->errordate = date("Y-m-d h:i:s");
							$err->errormessage = $e->getMessage();
							$err->user = $this->currentUser->username;
							$err->ip = $this->ip;
							$err->save();
							$data = array("status"=>"error","message"=>$e->getMessage());
						}
						$logger = new Datalogger("Advance","create",null,json_encode($data));
						$logger->SaveData();

					echo json_encode($data);									
					break;
				case 'delete':				
					$id = $this->post['id'];
					$Internalhiringdetail = Internalhiringdetail::find($id);
					if ($Internalhiringdetail->status==0 || $Internalhiringdetail->status==6){
						try {
							$data = $Internalhiringdetail->to_array();
							$Internalhiringdetail->delete();
							$logger = new Datalogger("Internalhiringdetail","delete",json_encode($data),null);
							$logger->SaveData();
							$data = array("status"=>"success","message"=>"Data has been deleted");
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "DeleteInternalhiringdetail";
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
					$Internalhiring = Internalhiringdetail::find($id);
					$olddata = $Internalhiring->to_array();

					// $codenew = Internalhiring::find('first',array('select' => "CONCAT('IH/','".$Internalhiring->bu."/','".$data['positioncode']."','/',YEAR(CURDATE()),'/',LPAD(MONTH(CURDATE()), 2, '0'),'/',LPAD(CASE when max(substring(postno,-4,4)) is null then 1 else max(substring(postno,-4,4))+1 end,4,'0')) as postno","conditions"=>array("substring(postno,9,".strlen($Internalhiring->bu).")=? and substring(postno,".(strlen($Internalhiring->bu)+10).",4)=YEAR(CURDATE())",$Internalhiring->bu)));
					// $codenew = Internalhiring::find('first',array('select' => "CONCAT('IH/','".$Internalhiring->bu."/','".$data['positioncode']."','/',YEAR(CURDATE()),'/',LPAD(MONTH(CURDATE()), 2, '0')) as postno"));

					// echo $codenew->postno;

					// foreach($data as $key=>$val){
					// 	$Internalhiring->$key=$val;
						
					// }
					// if($data['positioncode']) {
					// 	$Internalhiring->postno = $codenew->postno;
					// }
					$Internalhiring->save();
					$Internalhiring->update_attributes($data);
					$logger = new Datalogger("Internalhiringreport","update",json_encode($olddata),json_encode($data));
					$logger->SaveData();
					echo json_encode($Internalhiring);
					
					break;
				default:
					$internalhiring = Internalhiring::find('all',array('conditions' => array("expireddate >= now()")));
					foreach ($internalhiring as &$result) {
						$result = $result->to_array();
					}
					echo json_encode($internalhiring, JSON_NUMERIC_CHECK);
				break;
			}

		}
	}

	function internalhiringDetail(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
		
			switch ($this->post['criteria']){
				case 'byid':
					$id = $this->post['id'];
					if ($id!=""){
						$Advancedetail = Advancedetail::find('all', array('conditions' => array("advance_id=?",$id)));
						foreach ($Advancedetail as &$result) {
							$result	= $result->to_array();
						}

						echo json_encode($Advancedetail, JSON_NUMERIC_CHECK);
					}else{
						$Advancedetail = new Advancedetail();
						echo json_encode($Advancedetail);
					}
					break;
				case 'find':
					$data = $this->post['data'];

					$Employee = Employee::find('first', array('conditions' => array("SAPID=?",$data['sapid'])));
					$result = $Employee->to_array();
					
					echo json_encode($result, JSON_NUMERIC_CHECK);
					break;
				case 'create':	
					$ihid	= $this->post['ih_id'];
					$level	= $this->post['ih_level'];
					$postno	= $this->post['ih_postno'];
					$data = $this->post['data'];
					// unset($data['__KEY__']);
					$data['ih_id'] = $ihid;
					if($data['isdeclaration'] == true) {
						$data['isdeclaration'] = 1;
					} else {
						$data['isdeclaration'] = 0;
					}
					$Internalhiring = Internalhiring::find($ihid);

					$join = "LEFT JOIN tbl_empjoindate ON tbl_employee.SAPID = tbl_empjoindate.SAPID";
					$select = "tbl_employee.*,tbl_empjoindate.joindate as sapidjd";
					$Employee = Employee::find('first', array('joins'=>$join,'select'=>$select,'conditions' => array("tbl_employee.SAPID=?",$data['sapid'])));
					if($Employee->sapid !== null) {
						$data['fullname'] = $Employee->fullname;
						$data['company_id'] = $Employee->company_id;
						$data['department_id'] = $Employee->department_id;
						$data['location_id'] = $Employee->location_id;
						$data['designation_id'] = $Employee->designation_id;
						$data['level_id'] = $Employee->level_id;
						$data['joindate'] = $Employee->sapidjd;
						$data['status'] = 1;

						$codenew = Internalhiringdetail::find('first',array('select' => "CONCAT('IH/','".$Internalhiring->bu."/','".$Internalhiring->positioncode."','/',YEAR(CURDATE()),'/',LPAD(MONTH(CURDATE()), 2, '0'),'/',LPAD(CASE when max(substring(postno,-4,4)) is null then 1 else max(substring(postno,-4,4))+1 end,4,'0')) as postno","conditions"=>array("substring(postno,1,".strlen($Internalhiring->postno).")=? ",$Internalhiring->postno)));
						// $codenew = Internalhiringdetail::find('first',array('select' => "CONCAT('IH/','".$Internalhiring->bu."/','".$Internalhiring->positioncode."','/',YEAR(CURDATE()),'/',LPAD(MONTH(CURDATE()), 2, '0'),'/',LPAD(CASE when max(substring(postno,-4,4)) is null then 1 else max(substring(postno,-4,4))+1 end,4,'0')) as postno","conditions"=>array("substring(postno,4,".strlen($Internalhiring->bu).")=? and substring(postno,".(strlen($Internalhiring->bu)+1).",8)=YEAR(CURDATE())",$Internalhiring->bu)));


						//get age from date or birthdate
						$data['postno'] = $codenew->postno;

						
						
						$findih = Internalhiringdetail::find('all', array('conditions' => array("SAPID=? and status>=0 ",$data['sapid']))); //check sapid and status
						$checkih = Internalhiringdetail::find('all', array('conditions' => array("SAPID=? and (status>0 and status<5) ",$data['sapid']))); //check sapid
						$checkihreject = Internalhiringdetail::find('all', array('conditions' => array("SAPID=? and status=6 ",$data['sapid']))); //check sapid

							if(count($checkih)>0) {
								echo '406'; //already submit
							} else {

								if($Employee->level_id == $level ) {
									
									
									if(count($findih)>=3) {
										echo '500'; //three data cannot apply again
									} else {
										if(count($checkihreject)>0) {
											echo '407'; //cannot apply coz rejected data
										} else {
											$data['age'] = (date('Y') - date('Y',strtotime($data['dob'])));
											$data['los'] = (date('Y') - date('Y',strtotime($data['joindate'])));
											$Internalhiring = Internalhiringdetail::create($data);
											$logger = new Datalogger("Internalhiringdetail","create",null,json_encode($data));
											$logger->SaveData();
											echo '200'; //success
										}
									}
								} else {
									echo '405'; //must same level
								}
							}
						// } else {
						// 	echo '500'; //three data cannot apply again
						// }
						

					} else {
						echo '404'; //if sapid not found
					}

					break;
				case 'delete':				
					$id = $this->post['id'];
					$Advancedetail = Advancedetail::find($id);
					$data=$Advancedetail->to_array();
					$Advancedetail->delete();
					$logger = new Datalogger("Advancedetail","delete",json_encode($data),null);
					$logger->SaveData();
					echo json_encode($Advancedetail);
					break;
				case 'update':				
					$id = $this->post['id'];
					$data = $this->post['data'];
					
					$Advancedetail = Advancedetail::find($id);
					$olddata = $Advancedetail->to_array();
					// foreach($data as $key=>$val){
					// 	$Advancedetail->$key=$val;
					// }
					foreach($data as $key=>$val){
						// $val=($val=='true')?1:0;
						if($val=='true') {
							$val = 1;
						}else if($val=='false') {
							$val = 0;
						}
						$Advancedetail->$key=$val;
						
					}
					// $exprice = $Advancedetail->unitprice * $Advancedetail->qty;
					// $Advancedetail->extendedprice = $exprice;
					$Advancedetail->save();
					$logger = new Datalogger("Advancedetail","update",json_encode($olddata),json_encode($data));
					$logger->SaveData();
					echo json_encode($Advancedetail);
					
					break;
				default:
					$Internalhiringdetail = Internalhiringdetail::all();
					foreach ($Internalhiringdetail as &$result) {
						$result = $result->to_array();
					}
					echo json_encode($Internalhiringdetail, JSON_NUMERIC_CHECK);
					break;
			}

		}
	}

	function internalhiringMaster(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
		
			switch ($this->post['criteria']){
				case 'byid':
					$id = $this->post['id'];
					if ($id!=""){
						$Advancedetail = Advancedetail::find('all', array('conditions' => array("advance_id=?",$id)));
						foreach ($Advancedetail as &$result) {
							$result	= $result->to_array();
						}

						echo json_encode($Advancedetail, JSON_NUMERIC_CHECK);
					}else{
						$Advancedetail = new Advancedetail();
						echo json_encode($Advancedetail);
					}
					break;
				case 'find':
					$data = $this->post['data'];

					$Employee = Employee::find('first', array('conditions' => array("SAPID=?",$data['sapid'])));
					$result = $Employee->to_array();
					
					echo json_encode($result, JSON_NUMERIC_CHECK);
					break;
				case 'create':	
					try{
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$codenew = Internalhiring::find('first',array('select' => "CONCAT('IH/','".$data['bu']."/','".$data['positioncode']."','/',YEAR(CURDATE()),'/',LPAD(MONTH(CURDATE()), 2, '0')) as postno"));
						$data['postno'] = $codenew->postno;
						$Internalhiring = Internalhiring::create($data);
						$logger = new Datalogger("Internalhiring","create",null,json_encode($data));
						$logger->SaveData();
					}catch (Exception $e){
						$err = new Errorlog();
						$err->errortype = "CreateInternalhiring";
						$err->errordate = date("Y-m-d h:i:s");
						$err->errormessage = $e->getMessage();
						$err->user = $this->currentUser->username;
						$err->ip = $this->ip;
						$err->save();
						$data = array("status"=>"error","message"=>$e->getMessage());
					}
					
					break;
				case 'delete':				
					$id = $this->post['id'];
					$Internalhiring = Internalhiring::find($id);
					$data=$Internalhiring->to_array();
					$Internalhiring->delete();
					$logger = new Datalogger("Internalhiring","delete",json_encode($data),null);
					$logger->SaveData();
					echo json_encode($Internalhiring);
					break;
				case 'update':				
					$id = $this->post['id'];
					$data = $this->post['data'];
					
					$Internalhiring = Internalhiring::find($id);
					$olddata = $Internalhiring->to_array();
					// foreach($data as $key=>$val){
					// 	$Internalhiring->$key=$val;
					// }
					// foreach($data as $key=>$val){
					// 	// $val=($val=='true')?1:0;
					// 	if($val=='true') {
					// 		$val = 1;
					// 	}else if($val=='false') {
					// 		$val = 0;
					// 	}
					// 	$Internalhiring->$key=$val;
						
					// }
					// $codenew = Internalhiring::find('first',array('select' => "CONCAT('IH/','".$Internalhiring->bu."/','".$data['positioncode']."','/',YEAR(CURDATE()),'/',LPAD(MONTH(CURDATE()), 2, '0'),'/',LPAD(CASE when max(substring(postno,-4,4)) is null then 1 else max(substring(postno,-4,4))+1 end,4,'0')) as postno","conditions"=>array("substring(postno,9,".strlen($Internalhiring->bu).")=? and substring(postno,".(strlen($Internalhiring->bu)+10).",4)=YEAR(CURDATE())",$Internalhiring->bu)));
					$codenew = Internalhiring::find('first',array('select' => "CONCAT('IH/','".$Internalhiring->bu."/','".$data['positioncode']."','/',YEAR(CURDATE()),'/',LPAD(MONTH(CURDATE()), 2, '0')) as postno"));

					// echo $codenew->postno;

					foreach($data as $key=>$val){
						$Internalhiring->$key=$val;
						
					}
					if($data['positioncode']) {
						$Internalhiring->postno = $codenew->postno;
					}
					$Internalhiring->save();
					// $Internalhiring->update_attributes($data);
					$logger = new Datalogger("Internalhiring","update",json_encode($olddata),json_encode($data));
					$logger->SaveData();
					echo json_encode($Internalhiring);
					
					break;
				default:
					$Internalhiring = Internalhiring::all();
					foreach ($Internalhiring as &$result) {
						$result = $result->to_array();
					}
					echo json_encode($Internalhiring, JSON_NUMERIC_CHECK);
					break;
			}

		}
	}
	
	function uploadLampiran(){
		if(!isset($_FILES['lampiran'])) {
			http_response_code(400);
			echo "There is no file to upload";
			exit;
		}
		$max_image_size = 5242880;
		if(!is_uploaded_file($_FILES['lampiran']['tmp_name'])) {
			http_response_code(400);
			echo "Unable to upload File";
			exit;
		}
		if($_FILES['lampiran']['size'] > $max_image_size) {
			http_response_code(413);
			echo "File Size too Large, Maximum 5MB";
			exit;
		}
		if((strpos($_FILES['lampiran']['type'], "image") === false) && (strpos($_FILES['lampiran']['type'], "pdf") === false) && (strpos($_FILES['lampiran']['type'], "officedocument") === false)  && (strpos($_FILES['lampiran']['type'], "msword") === false) && (strpos($_FILES['lampiran']['type'], "excel") === false)){
			http_response_code(415);
			echo "Only Accept Image File, pdf or Office Document (Excel & Word) ";
			exit;
		}
		$path_to_file = "upload\\internalhiring\\".time()."_".$_FILES['lampiran']['name'];
		$path_to_file = str_replace("%","_",$path_to_file);
		$path_to_file = str_replace(" ","_",$path_to_file);
		echo $path_to_file;
        move_uploaded_file($_FILES['lampiran']['tmp_name'], $path_to_file);

		$this->processcopy($path_to_file);
	}
}