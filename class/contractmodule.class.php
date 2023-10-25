<?php
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

Class ContractModule extends Application{
	
	private $mailbody;
	private $mail;
	private $filename;
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
		$this->mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);
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
                case 'apicontract':
					$this->Contract();
					break;
				case 'apicontractreg':
					$this->ContractRegister();
					break;
                case 'apicontractdelete':
                    $this->ContractDelete();
                    break;
                case 'apicontractupdate':
                    $this->ContractUpdate();
                    break;
                case 'apiclosecontract':
                    $this->CloseContract();
                    break;
				case 'uploadcontractfile':
					$this->uploadContractFile();
					break;
                case 'apicontractfile':
                    $this->contractFile();
                    break;
				default:
					break;
			}
		}
	}

	public function uploadContractFile(){
		$id= $this->get['id'];
		if(!isset($_FILES['myFile'])) {
			http_response_code(400);
			echo "There is no file to upload";
			exit;
		}
		$max_image_size = 6242880;
		if(!is_uploaded_file($_FILES['myFile']['tmp_name'])) {
			http_response_code(400);
			echo "Unable to upload File";
			exit;
		}
		if($_FILES['myFile']['size'] > $max_image_size) {
			http_response_code(413);
			echo "File Size too Large, Maximum 6MB";
			exit;
		}
		// var_dump($_FILES['myFile']['type']);
		$ext = pathinfo($_FILES['myFile']['name'], PATHINFO_EXTENSION);
		// echo $ext;
		if((strpos($_FILES['myFile']['type'], "octet-stream") === false || $ext !== 'msg') && (strpos($_FILES['myFile']['type'], "image") === false) && (strpos($_FILES['myFile']['type'], "pdf") === false) && (strpos($_FILES['myFile']['type'], "officedocument") === false)  && (strpos($_FILES['myFile']['type'], "msword") === false) && (strpos($_FILES['myFile']['type'], "excel") === false)){
			http_response_code(415);
			echo "Only Accept Image File, pdf or Office Document (Excel & Word & Outlook) ";
			exit;
		}
        $filename = preg_replace("/[^a-z0-9\_\-\.]/i", '', basename($_FILES['myFile']["name"]));
		$path_to_file = "upload\\contract\\".$id."_".time()."_".$filename;
	
        move_uploaded_file($_FILES['myFile']['tmp_name'], $path_to_file);

		$this->processcopy($path_to_file);
        echo $path_to_file;
		
	}
    function contractFile(){
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
							$Contractfile = Contractfile::find('all', array('conditions' => array("contract_id=?",$id)));
							foreach ($Contractfile as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Contractfile, JSON_NUMERIC_CHECK);
						}else{
							$Contractfile = new Contractfile();
							echo json_encode($Contractfile);
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							$Contractfile = Contractfile::find('all', array('conditions' => array("contract_id=?",$query['contract_id'])));
							$data=array("jml"=>count($Contractfile));
						}else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						if($this->currentUser->username=="admin"){
							$Rfc = Rfc::find($data['contract_id']);
							$data['employee_id']= $Rfc->employee_id;
						}else{
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$data['employee_id']=$Employee->id;
						}
						
						unset($data['__KEY__']);
						
						$Contractfile = Contractfile::create($data);
						$logger = new Datalogger("Contractfile","create",null,json_encode($data));
						$logger->SaveData();
						echo json_encode($data);
						break;
					case 'delete':				
						$id = $this->post['id'];
						$Contractfile = Contractfile::find($id);
						$data=$Contractfile->to_array();
						$Contractfile->delete();
						$logger = new Datalogger("Contractfile","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Contractfile);
						break;
					case 'update':				
						$id = $this->post['id'];
						$data = $this->post['data'];
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
						$data['employee_id']=$Employee->id;
						$Contractfile = Contractfile::find($id);
						$olddata = $Contractfile->to_array();
						foreach($data as $key=>$val){					
							$val=($val=='No')?false:(($val=='Yes')?true:$val);
							$Contractfile->$key=$val;
						}
						$Contractfile->save();
						$logger = new Datalogger("Contractfile","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						echo json_encode($Contractfile);
						
						break;
					default:
						$Contractfile = Contractfile::all();
						foreach ($Contractfile as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Contractfile, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
		
	}
    function Contract(){
        if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
                switch ($this->post['criteria']){
                    case 'byid':
						$id = $this->post['id'];
                        $join = "LEFT JOIN vwcontract v on tbl_contract.id=v.id";
                        $sel = 'tbl_contract.*, v.activitydescr,v.RFCNo,v.ratetype,v.SKNo,v.CompanyCode,v.RFCUser,v.RFCUserEmail ';
						$Contract = Contract::find($id, array('joins'=>$join,'select'=>$sel,'include' => array('rfc')));
						if ($Contract){
                            $date1 = new DateTime(date('Y-m-d'));
                            $date2 = new DateTime($Contract->periodend);
                            $interval = $date1->diff($date2);
                            $diff =$interval->days;
                            $inv = $interval->invert;
                            $status =  ($Contract->isactive==0 ||($Contract->newcontractno!=0 && $Contract->newcontractno!=''))?3:(($inv==1)?2:(($diff<90)?1:0));
                            $Contract->contractstatus = $status;
                            $Contract->save();
							$data=$Contract->to_array();
							echo json_encode($data, JSON_NUMERIC_CHECK);
						}else{
							$Contract = new Contract();
							echo json_encode($Contract);
						}
						break;
                    case 'all':
                        $join = "LEFT JOIN vwcontract v on tbl_contract.id=v.id";
                        $sel = 'tbl_contract.*, v.activitydescr,v.RFCNo,v.ratetype,v.SKNo,v.CompanyCode,v.RFCUser,v.RFCUserEmail ';
                        $Contract = Contract::find('all',array('joins'=>$join,'select'=>$sel));
                        foreach ($Contract as &$result) {
                            $date1 = new DateTime(date('Y-m-d'));
                            $date2 = new DateTime($result->periodend);
                            $interval = $date1->diff($date2);
                            $diff =$interval->days;
                            $inv = $interval->invert;
                            $status =  ($result->isactive==0 || ($result->newcontractno!=0 && $result->newcontractno!=''))?3:(($inv==1)?2:(($diff<90)?1:0));
                            $result->contractstatus = $status;
                            $result->save();
                            $result = $result->to_array();
                            $result['diff']=$interval;
                        }					
                        echo json_encode($Contract);
                        break;
                    case 'find':
                        $query=$this->post['query'];					
                        if(isset($query['status'])){
                            switch ($query['status']){
                                case 'new':	
                                    $joins   = "left join tbl_contract as old on tbl_contract.id = old.oldcontractno  ";
                                    $data= Contract::find('all',array('joins'=>$joins,'conditions' => array("(old.OldContractno IS null or tbl_contract.id=(select old2.oldcontractno from tbl_contract old2 where old2.id=?)) and not(tbl_contract.id=?) ",$query['contract_id'],$query['contract_id'])));
                                    foreach ($data as &$result) {
                                        $result		= $result->to_array();
                                    }
                                    break;
                                case 'allcontract':	
                                    $data= Contract::find('all');
                                    foreach ($data as &$result) {
                                        $result		= $result->to_array();                                        
                                    }
                                    break;
                                case 'rfcactive':	
                                    $joins   = "LEFT JOIN tbl_contract ON (tbl_contract.rfc_id = tbl_rfc.id) ";
                                    $data= Rfc::find('all',array('joins'=>$joins,'conditions' => array("tbl_rfc.requeststatus='3' and ((tbl_contract.contractno is null  and (tbl_rfc.periodstart>=now() or tbl_rfc.periodend>now()) or tbl_contract.id=?))",$query['contract_id'])));
                                    foreach ($data as &$result) {
                                        $result		= $result->to_array();
                                    }
                                    break;
                                case 'allrfc':
                                    
                                    $data= Rfc::find('all',array('conditions'=>array("requeststatus='3'")));
                                    foreach ($data as &$result) {
                                        $result		= $result->to_array();
                                    }
                                    break;
                                case 'chrfc':
                                    $joins   = "left join tbl_employee as e on tbl_rfc.employee_id = e.id left join tbl_rfcactivity a on tbl_rfc.activity_id=a.id ";
                                    $sel = 'tbl_rfc.*, e.fullname as rfcuser,a.activitydescr ';
                                    $data= Rfc::find('first',array('joins'=>$joins,'select'=>$sel,'conditions'=>array('tbl_rfc.id=?', $query['rfc_id'])));
                                    $data = $data->to_array();
                                    break;
                                default:
                                    $data=array();
                                    break;
                            }
                        }
                        echo json_encode($data, JSON_NUMERIC_CHECK);
                        break;
                    default:
                        break;
                }
            }
        }
    }
	function ContractRegister(){
        if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
            $auth = $this->jwt->checkAuth();
            if($auth){
                $data = $this->post['data'];
                $Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username),"include"=>array("location","company","department")));
                $data['CreatedBy']=$Employee->id;
                try{
                    unset($data['__KEY__']);
                    $Contract = Contract::create($data);
                    $data=$Contract->to_array();

                }catch (Exception $e){
                    $err = new Errorlog();
                    $err->errortype = "CreateContract";
                    $err->errordate = date("Y-m-d h:i:s");
                    $err->errormessage = $e->getMessage();
                    $err->user = $this->currentUser->username;
                    $err->ip = $this->ip;
                    $err->save();
                    $data = array("status"=>"error","message"=>$e->getMessage());
                }
                $logger = new Datalogger("Contract","create",null,json_encode($data));
                $logger->SaveData();
                echo json_encode($data);
            }else{
                echo json_encode(array("message" => "You don't have authority to view this module"));
            }
        }
    }
    function ContractUpdate(){
        if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
            $auth = $this->jwt->checkAuth();
            if($auth){
                try{
                    $data = $this->post['data'];
                    $id = $this->post['id'];
                    $Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username),"include"=>array("location","company","department")));
                    $data['modifiedby']=$Employee->id;
                    $data['modifieddate']=date('Y-m-d');
                    unset($data['activitydescr']);
                    unset($data['rfcno']);
                    unset($data['ratetype']);
                    unset($data['skno']);
                    unset($data['companycode']);
                    unset($data['rfcuser']);
                    unset($data['rfcuseremail']);
                    $Contract = Contract::find($id);
                    $olddata = $Contract->to_array();
                    if(isset($data['oldcontractno'])){
                        $oldData = $Contract->oldcontractno;
                        if (($data['oldcontractno']=='')){
                            if ($oldData!=='' && $oldData!==0){
                                $oldContract = Contract::find($oldData);
                                $oldContract->newcontractno = 0;
                                $oldContract->save();
                            }
                        }else{
                            if($data['oldcontractno']!==$oldData){
                                if ($oldData!=0){
                                    $oldContract = Contract::find($oldData);
                                    $oldContract->newcontractno = 0;
                                    $oldContract->save();
                                }
                                $newContract = Contract::find($data['oldcontractno']);
                                $newContract->newcontractno = $id;
                                $newContract->contractstatus = 3;
                                $newContract->save();
                            }
                        }
                    }
                    foreach($data as $key=>$val){					
                        $Contract->$key=$val;
                    }
                    $Contract->save();
                    $logger = new Datalogger("Contract","update",json_encode($olddata),json_encode($data));
                    $logger->SaveData();
                    echo json_encode($data);

                }catch (Exception $e){
                    $err = new Errorlog();
                    $err->errortype = "UpdateContract";
                    $err->errordate = date("Y-m-d h:i:s");
                    $err->errormessage = $e->getMessage();
                    $err->user = $this->currentUser->username;
                    $err->ip = $this->ip;
                    $err->save();
                    $data = array("status"=>"error","message"=>$e->getMessage());
                }
            }else{
                echo json_encode(array("message" => "You don't have authority to view this module"));
            }
        }
    }
    function CloseContract(){
        if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
            $auth = $this->jwt->checkAuth();
            if($auth){
                try{
                    $id = $this->post['id'];
                    $Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username),"include"=>array("location","company","department")));
                    $data['modifiedby']=$Employee->id;
                    $data['modifieddate']=date('Y-m-d');
                    $Contract = Contract::find($id);
                    $Contract->isactive = false;
                    $Contract->save();
                    $olddata = $Contract->to_array();
                    $logger = new Datalogger("Contract","update",json_encode($olddata),json_encode($data));
                    $logger->SaveData();
                    echo json_encode($data);

                }catch (Exception $e){
                    $err = new Errorlog();
                    $err->errortype = "CloseContract";
                    $err->errordate = date("Y-m-d h:i:s");
                    $err->errormessage = $e->getMessage();
                    $err->user = $this->currentUser->username;
                    $err->ip = $this->ip;
                    $err->save();
                    $data = array("status"=>"error","message"=>$e->getMessage());
                }
            }else{
                echo json_encode(array("message" => "You don't have authority to view this module"));
            }
        }
    }
    function ContractDelete(){
        if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
            $auth = $this->jwt->checkAuth();
            if($auth){
                $id = $this->post['id'];
                $Contract = Contract::find($id);
                $oldData = $Contract->oldcontractno;
                if ($oldData!=0 && $oldData !=''){
                    $oldContract = Contract::find($oldData);
                    $oldContract->newcontractno = 0;
                    $oldContract->save();
                }
                $data=$Contract->to_array();
                $Contract->delete();
                $logger = new Datalogger("Contract","delete",json_encode($data),null);
                $logger->SaveData();
                echo json_encode($Contract);
            }else{
                echo json_encode(array("message" => "You don't have authority to view this module"));
            }
        }
    }
}