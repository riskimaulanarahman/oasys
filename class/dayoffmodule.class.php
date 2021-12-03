<?php
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

Class DayoffModule extends Application{
	
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
		$this->mail = new PHPMailer;
		$this->mail->isSMTP();
		$this->mail->SMTPDebug = 0;
		$this->mail->Host = SMTPSERVER;
		$this->mail->Port = 465;
		$this->mail->SMTPSecure = 'tls';
		$this->mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);

		$this->mail->SMTPAuth = true;
		$this->mail->Username = MAILFROM;
		$this->mail->Password = SMTPAUTH;
		$this->mail->setFrom(MAILFROM,"Online Approval System");
		$this->ip = USER_IP;
		//$this->mail->addReplyTo('Purwanto_ihm@itci-hutani.com', 'Purwanto');
		$this->mailbody = '<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:m="http://schemas.microsoft.com/office/2004/12/omml" xmlns="http://www.w3.org/TR/REC-html40"><head><meta http-equiv=Content-Type content="text/html; charset=us-ascii"><meta name=Generator content="Microsoft Word 15 (filtered medium)"><style><!--
						/* Font Definitions */
						@font-face {font-family:Wingdings; panose-1:5 0 0 0 0 0 0 0 0 0;} @font-face {font-family:"Cambria Math"; panose-1:2 4 5 3 5 4 6 3 2 4;} @font-face {font-family:Calibri; panose-1:2 15 5 2 2 2 4 3 2 4;} @font-face {font-family:"Century Gothic"; panose-1:2 11 5 2 2 2 2 2 2 4;}
						/* Style Definitions */
						p.MsoNormal, li.MsoNormal, div.MsoNormal {margin:0in; margin-bottom:.0001pt; font-size:11.0pt; font-family:"Calibri","sans-serif";} a:link, span.MsoHyperlink {mso-style-priority:99; color:#0563C1; text-decoration:underline;} a:visited, span.MsoHyperlinkFollowed {mso-style-priority:99; color:#954F72; text-decoration:underline;} span.EmailStyle17 {mso-style-type:personal-reply;	font-family:"Calibri","sans-serif";	color:#1F497D;} .MsoChpDefault {mso-style-type:export-only;} @page WordSection1 {size:8.5in 11.0in;margin:1.0in 1.0in 1.0in 1.0in;} div.WordSection1 {page:WordSection1;} --></style><!--[if gte mso 9]><xml><o:shapedefaults v:ext="edit" spidmax="1026" /></xml><![endif]--><!--[if gte mso 9]><xml><o:shapelayout v:ext="edit"><o:idmap v:ext="edit" data="1" /></o:shapelayout></xml><![endif]--></head>';
		if (isset($this->get)){
			switch ($this->get['action']){
				case 'apidayoffbyemp':
					$this->DayoffByEmp();
					break;
				case 'apidayoff':
					$this->DayOff();
					break;
				case 'apidodetail':
					$this->DayOffDetail();
					break;
				case 'apidoapp':
					$this->DayOffApproval();
					break;
				case 'apidohist':
					$this->DayOffHistory();
					break;
				case 'apipdf':				
					 $this->generatePDF();
					break;
				default:
					break;
			}
		}
	}
	function generatePDF(){
		$id = $this->get['id'];
		$Dayoff = Dayoff::find($id);
		$join   = "LEFT JOIN tbl_approver ON (tbl_dayoffapproval.approver_id = tbl_approver.id) ";
		$Dayoffapproval = Dayoffapproval::find('all', array('joins'=>$join,'conditions' => array("dayoff_id=?",$id),'include' => array('approver'=>array('employee','approvaltype'))));
		$supname=$dateSup=$bgname=$datebg="";
		$reqdate =$Dayoff->requestdate;
		foreach ($Dayoffapproval as $data){
			if(($data->approver->approvaltype->id==5) || ($data->approver->employee_id==$Dayoff->superior)){
				$supname = $data->approver->employee->fullname;
				$usr = Addressbook::find('first',array('conditions'=>array("username=?",$data->approver->employee->loginname)));
				$supemail = $usr->email;
				$dateSup = date("d/m/Y",strtotime($data->approvaldate));
			}
			if(($data->approver->approvaltype->id==1) || ($data->approver->employee_id==$Dayoff->depthead)){
				$dhname = $data->approver->employee->fullname;
				$datedh = date("d/m/Y",strtotime($data->approvaldate));
				if (($levelid==4) || ($levelid==6)){
					$dhname = "";
				}
			}
			if($data->approver->approvaltype->id==2){
				$buhname = $data->approver->employee->fullname;
				$datebuh = date("d/m/Y",strtotime($data->approvaldate));
			}
			if($data->approver->approvaltype->id==3){
				$mdname = $data->approver->employee->fullname;
				$datemd = date("d/m/Y",strtotime($data->approvaldate));
			}
			if($data->approver->approvaltype->id==4){
				$bgname = $data->approver->employee->fullname;
				$datebg = date("d/m/Y",strtotime($data->approvaldate));
			}
		}
		$Dayoffdetail=Dayoffdetail::find('all',array('conditions'=>array("dayoff_id=?",$id),'include'=>array('dayoff'=>array('employee'=>array('company','department','designation','grade','location')))));
								
		foreach ($Dayoffdetail as &$result) {
			$dayoff=$result->dayoff->to_array();
			$emp=$result->dayoff->employee->to_array();
			$des=$result->dayoff->employee->designation->designationname;
			$gradeid=$result->dayoff->employee->grade->id;
			$levelid=$result->dayoff->employee->level_id;
			$grade=$result->dayoff->employee->grade->grade;
			$location=$result->dayoff->employee->location->location;
			$comp=$result->dayoff->employee->company->to_array();
			$usr = Addressbook::find('first',array('conditions'=>array("username=?",$result->dayoff->employee->loginname)));
			$email=$usr->email;
			$dept=$result->dayoff->employee->department->to_array();
			$result = $result->to_array();
			$result['Dayoff']=$dayoff;
			$result['Dayoff']['Employee']=$emp;
			$result['Dayoff']['Employee']['Company']=$comp;
			$result['Dayoff']['Employee']['Department']=$dept;
		}
		$pdfContent='<h3 style="width:100%;text-align:center">WEEKEND/PUBLIC HOLIDAY COVERAGE FORM</h3>';
		$pdfContent .= "Dengan ini diperintahkan agar melaksanakan kerja di waktu Weekend/Public Holiday kepada : <br>";
		$pdfContent .= "<i>Herewith instructed to work on weekend/public holiday to ; </i>";
		$pdfContent .= "<small><ol><li>This form is used based on superior's instruction only.</li>";
		$pdfContent .= "<li>Employee should complete this form and submit to BG HR</li>";
		$pdfContent .= "<li>Asteriks (*) indicates a mandatory field</li>";
		$pdfContent .= "<li>Approving Superior/BU Head/Deputy MD is required to initial on every coverage date</li></ol></small>";
		$pdfContent .= '<table border=0 cellspacing=3 cellpadding=3>';
		$pdfContent .= '<tr><td>*Personnel Number (SAP ID)</td><td>:</td><td style="border:solid windowtext 1.0pt;padding:2.5pt 5.4pt 2.5pt 5.4pt;">'.$emp['sapid'].'</td><td style="width=10px;"></td><td>*Superior Name</td><td>:</td><td style="border:solid windowtext 1.0pt;padding:2.5pt 5.4pt 2.5pt 5.4pt;">'.$supname.'</td></tr>
						<tr><td>*Name</td><td>:</td><td style="border:solid windowtext 1.0pt;padding:2.5pt 5.4pt 2.5pt 5.4pt;">'.$emp['fullname'].'</td><td style="width=10px;"></td><td>*Superior Email</td><td>:</td><td style="border:solid windowtext 1.0pt;padding:2.5pt 5.4pt 2.5pt 5.4pt;">'.$supemail.'</td></tr>
						<tr><td>Position</td><td>:</td><td style="border:solid windowtext 1.0pt;padding:2.5pt 5.4pt 2.5pt 5.4pt;">'.$des.'</td><td colspan=4></td></tr>
						<tr><td>*Grade </td><td>:</td><td style="border:solid windowtext 1.0pt;padding:2.5pt 5.4pt 2.5pt 5.4pt;">'.$grade.'</td><td colspan=4></td></tr>
						<tr><td>Business Group </td><td>:</td><td style="border:solid windowtext 1.0pt;padding:2.5pt 5.4pt 2.5pt 5.4pt;">'.$emp['companycode'].'</td><td colspan=4></td></tr>
						<tr><td>Location </td><td>:</td><td style="border:solid windowtext 1.0pt;padding:2.5pt 5.4pt 2.5pt 5.4pt;">'.$location.'</td><td colspan=4></td></tr>
						<tr><td>*Email / Telephone No.</td><td>:</td><td style="border:solid windowtext 1.0pt;padding:2.5pt 5.4pt 2.5pt 5.4pt;">'.$email.'</td><td colspan=4></td></tr>
						<tr><td><u>Coverage Details</u></td><td>:</td><td colspan=5></td></tr>
						<tr><td>*Work Schedule Code</td><td>:</td><td style="border:solid windowtext 1.0pt;padding:2.5pt 5.4pt 2.5pt 5.4pt;"> </td><td colspan=4></td></tr>
					</table><br>
					<table border=0 cellspacing=0 cellpadding=0 width="100%"><tr><td colspan="9" style="border-bottom:solid windowtext 1.0pt;padding-bottom:8px;">
					<table border=0 cellpadding=3 cellspacing=0>
					<tr><td rowspan=5 style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;text-align:center;background:#F2F2F2;">Date (dd/mm/yyyy)</td>
						<td colspan=2 style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;text-align:center;background:#F2F2F2;">Reason</td>
						<td colspan=3 style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;text-align:center;background:#F2F2F2;">Approving Initial</td></tr>
					<tr><td rowspan=4 style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;text-align:center;background:#F2F2F2;min-width:100px;max-width:300px;">Objectives</td>
						<td rowspan=4 style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;text-align:center;background:#F2F2F2;min-width:50px;max-width:100px;">Remarks</td>
						<td colspan=2 style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;text-align:center;background:#F2C200;">C1-D1</td>
						<td rowspan=2 style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;"></td></tr>
					<tr><td style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;text-align:center;background:#F2C200;">Dept. Head</td>
					<td style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;text-align:center;background:#F2C200;">BU Head</td></tr>
					<tr><td rowspan=2 style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;"></td>
					<td colspan=2 style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;text-align:center;background:#C2C2F2;">D2 & above & IS</td></tr>
					<tr><td style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;text-align:center;background:#C2C2F2;">BU Head</td>
					<td style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;text-align:center;background:#C2C2F2;">Deputy MD</td></tr>';
		foreach ($Dayoffdetail as $data){
			if($data['isapproved']){
				$reason = wordwrap($data['reason'], 60, "<br>");
				$pdfContent .= '<tr><td style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;none;border-top:none;">'.date('d/m/Y', strtotime($data['dateworked'])).'</td>
								<td style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;min-width:100px;max-width:300px;">'.wordwrap($data['reason'], 60, "<br>") .'</td>
								<td style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;min-width:50px;max-width:100px;">'.wordwrap($data['remarks'], 50, "<br>").'</td>
								<td style="border:solid windowtext 1.0pt;border-left:none;border-top:none;">'.(($dhname=="")?'':(($data['isapproved']==1)?'<img src="images/approved.png" style="height:12.5pt" alt="Approved from System">':'<img src="images/rejected.png" style="height:12.5pt" alt="Rejected from System">')).'<br><small>'.$dhname.'<br>'.$datedh.'</small></td>
								<td style="border:solid windowtext 1.0pt;border-left:none;border-top:none;">'.(($buhname=="")?'':(($data['isapproved']==1)?'<img src="images/approved.png" style="height:12.5pt" alt="Approved from System">':'<img src="images/rejected.png" style="height:12.5pt" alt="Rejected from System">')).'<br><small>'.$buhname.'<br>'.$datebuh.'</small></td>
								<td style="border:solid windowtext 1.0pt;border-left:none;border-top:none;">'.((($levelid==4) || ($levelid==6))?(($data['isapproved']==1)?'<img src="images/approved.png" style="height:12.5pt" alt="Approved from System">':'<img src="images/rejected.png" style="height:12.5pt" alt="Rejected from System">'):'').'<br><small>'.$mdname.'<br>'.$datemd.'</small></td>
							</tr>';
			}
		}
		$pdfContent .= "</table><small>Note :<br>";
		$pdfContent .= "- The coverage days listed will be forfeited if it is not claimed within the validity period as per SOP.<br>";
		$pdfContent .= "- Level Approval :<div >- C1-D1 National Staff must be approved by Dept. Head & BU Head.<br>- D2 and above National Staff and all level International Staff must be approved by BU Head & Deputy MD.</div></small>";
		
		$pdfContent .= '<br><table border=0 cellspacing=0 cellpadding=0 style="width:511.95pt;margin-left:1.85pt;border-collapse:collapse">
						<tr style="height:12.75pt"><th style="padding:0in 5.4pt 0in 5.4pt;height:12.75pt" colspan=2>Applied by:</th><th></th><th colspan=2>Assigned by:</th><th></th><th colspan=2>Acknowledged by:</th></tr>
						<tr style="height:30pt">
							<td style="padding:0in 5.4pt 0in 5.4pt;height:30pt" colspan=2><img src="images/approved.png" style="height:25pt" alt="Approved from System"></td>
							<td></td>
							<td colspan=2><img src="images/approved.png" style="height:25pt" alt="Approved from System"></td>
							<td></td>
							<td colspan=2><img src="images/approved.png" style="height:25pt" alt="Approved from System"></td>
						</tr>
						<tr style="height:12.75pt">
							<td style="padding-right:0in 5.4px 0in 5.4px;height:12.75pt;border-bottom:solid windowtext 1.0pt"> '.$emp['fullname'].' </td>
							<td style="width:100px;text-align:center;padding-right:0in 5.4px 0in 5.4px;border-bottom:solid windowtext 1.0pt">'.date("d/m/Y",strtotime($reqdate)).'</td>
							<td style="width:50px;"></td>
							<td style="padding-right:0in 5.4px 0in 5.4px;border-bottom:solid windowtext 1.0pt"> '.$supname.' </td>
							<td style="width:100px;text-align:center;padding-right:0in 5.4px 0in 5.4px;border-bottom:solid windowtext 1.0pt">'.$dateSup.'</td>
							<td style="width:50px;"></td>
							<td style="padding-right:0in 5.4px 0in 5.4px;border-bottom:solid windowtext 1.0pt"> '.$bgname.' </td>
							<td  style="width:100px;text-align:center;padding-right:0in 5.4px 0in 5.4px;border-bottom:solid windowtext 1.0pt">'.$datebg.'</td>
						</tr>
						<tr style="height:12.75pt"><td style="padding:0in 5.4pt 0in 5.4pt;height:12.75pt">Employee</td><td style="width:100px;text-align:center;">Date<br>dd/mm/yyyy</td><td></td><td>Superior</td><td style="width:100px;text-align:center;">Date<br>dd/mm/yyyy</td><td></td><td>BG HR</td><td style="width:100px;text-align:center;">Date<br>dd/mm/yyyy</td></tr>
						</table>
						</td></tr>
							<tr style="height:12.75pt"><td colspan="2"><small>Document ID No : IHM-HRD-9002-FM</small></td><td></td><td colspan="2"><small>Issue Date : 01 September 2019</small></td><td ></td><td><small>Revision:1</small></td><td><small>Page 1 of 1 </small></td></tr>
						</table>
						';
						//echo $pdfContent;
		try {
			$html2pdf = new Html2Pdf('L', 'A4', 'fr');
			$html2pdf->writeHTML($pdfContent);
			ob_clean();
			$fileName ='doc'.DS.'dayoff'.DS.'pdf'.DS.'regenerated'.DS.'WPHCF_'.$emp['sapid'].'_'.date("YmdHis").'.pdf';
			$filePath = SITE_PATH.DS.$fileName;
			$html2pdf->output($filePath, 'F');
			$this->mail->addAttachment($filePath);
			$Dayoff->approveddoc=str_replace("\\","/",$fileName);
			$Dayoff->save();
		} catch (Html2PdfException $e) {
			$html2pdf->clean();
			$formatter = new ExceptionFormatter($e);
			$err = new Errorlog();
			$err->errortype = "PDFGenerator";
			$err->errordate = date("Y-m-d h:i:s");
			$err->errormessage = $formatter->getHtmlMessage();
			$err->user = $this->currentUser->username;
			$err->ip = $this->ip;
			$err->save();
			echo $formatter->getHtmlMessage();
		}
		echo $reason;
	}
	function DayOffHistory(){
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
							$Dayoffhistory = Dayoffhistory::find('all', array('conditions' => array("dayoff_id=?",$id),'include' => array('dayoff')));
							foreach ($Dayoffhistory as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Dayoffhistory, JSON_NUMERIC_CHECK);
						}
						break;
					default:
						break;
				}
			}
			// else{
				// $result= array("status"=>"autherror","message"=>"Authentication error or expired, please refresh and re login application");
				// echo json_encode($result);
			// }
		}
	}
	function DayOffApproval(){
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
							$join   = "LEFT JOIN tbl_approver ON (tbl_dayoffapproval.approver_id = tbl_approver.id) ";
							$Dayoffapproval = Dayoffapproval::find('all', array('joins'=>$join,'conditions' => array("dayoff_id=?",$id),'include' => array('approver'=>array('approvaltype')),"order"=>"tbl_approver.sequence"));
							foreach ($Dayoffapproval as &$result) {
								$approvaltype = $result->approver->approvaltype_id;
								$result		= $result->to_array();
								$result['approvaltype']=$approvaltype;
							}
							echo json_encode($Dayoffapproval, JSON_NUMERIC_CHECK);
						}else{
							$Dayoffapproval = new Dayoffapproval();
							echo json_encode($Dayoffapproval);
						}
						break;
					case 'find':
						$query=$this->post['query'];		
						if(isset($query['status'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join   = "LEFT JOIN tbl_approver ON (tbl_dayoffapproval.approver_id = tbl_approver.id) ";
							$dx = Dayoffapproval::find('first', array('joins'=>$join,'conditions' => array("dayoff_id=? and  tbl_approver.employee_id = ?",$query['dayoff_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
							if($dx->approver->isfinal==1){
								$data=array("jml"=>1);
							}else{
								$join   = "LEFT JOIN tbl_approver ON (tbl_dayoffapproval.approver_id = tbl_approver.id) ";
								$Dayoffapproval = Dayoffapproval::find('all', array('joins'=>$join,'conditions' => array("dayoff_id=? and ApprovalStatus<=1 and not tbl_approver.employee_id=?",$query['dayoff_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
								foreach ($Dayoffapproval as &$result) {
									$fullname	= $result->approver->employee->fullname;		
									$result		= $result->to_array();
									$result['fullname']=$fullname;
								}
								$data=array("jml"=>count($Dayoffapproval));
							}						
						} else if(isset($query['pending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
							$Dayoff = Dayoff::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
							foreach ($Dayoff as $result) {
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_dayoffapproval.approver_id = tbl_approver.id) ";					
								$Dayoffapproval = Dayoffapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and dayoff_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								if($Dayoffapproval->approver->employee_id==$emp_id){
									$request[]=$result->id;
								}
							}
							$Dayoff = Dayoff::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee'=>array('company','department'))));
							foreach ($Dayoff as &$result) {
								$fullname	= $result->employee->fullname;	
								$department = $result->employee->department->departmentname;
								$result		= $result->to_array();
								$result['fullname']=$fullname;
								$result['department']=$department;
							}
							$data=$Dayoff;
						} else if(isset($query['mypending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
							$Dayoff = Dayoff::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
							$jml=0;
							foreach ($Dayoff as $result) {
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_dayoffapproval.approver_id = tbl_approver.id) ";					
								$Dayoffapproval = Dayoffapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and dayoff_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								if($Dayoffapproval->approver->employee_id==$emp_id){
									$request[]=$result->id;
								}
							}
							$Dayoff = Dayoff::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
							foreach ($Dayoff as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=array("jml"=>count($Dayoff));
						} else if(isset($query['filter'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$Dayoff = Dayoff::find('all',array('conditions' => array('RequestStatus>0'),'include' => array('employee'=>array('company','department'))));
							// if($Employee->location->sapcode=='0200'){
							// 	$Dayoff = Dayoff::find('all',array('conditions' => array('RequestStatus=3'),'include' => array('employee'=>array('company','department'))));
							// }else if($this->currentUser->isadmin){
							// 	$Dayoff = Dayoff::find('all',array('include' => array('employee'=>array('company','department'))));
							// }else{
								$joinx   = "LEFT JOIN tbl_employee ON (tbl_dayoffreq.employee_id = tbl_employee.id) ";	
								$Dayoff = Dayoff::find('all',array('joins'=>$joinx,'conditions' => array('tbl_dayoffreq.RequestDate between ? and ? ',$query['startDate'],$query['endDate']),'include' => array('employee'=>array('company','department'))));
							// }
							
							foreach ($Dayoff as &$result) {
								$department = $result->employee->department->departmentname;
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
								$result['department']=$department;
							}
							$data=$Dayoff;
						} else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':
						try{
							$data = $this->post['data'];
							unset($data['__KEY__']);
							$Dayoffapproval = Dayoffapproval::create($data);
							$logger = new Datalogger("Dayoffapproval","create",null,json_encode($data));
							$logger->SaveData();
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "CreateDayoffApproval";
							$err->errordate = date("Y-m-d h:i:s");
							$err->errormessage = $e->getMessage();
							$err->user = $this->currentUser->username;
							$err->ip = $this->ip;
							$err->save();
							$data = array("status"=>"error","message"=>$e->getMessage());
						}
						break;
					case 'delete':
						try {
							$id = $this->post['id'];
							$Dayoffapproval = Dayoffapproval::find($id);
							$data=$Dayoffapproval->to_array();
							$Dayoffapproval->delete();
							$logger = new Datalogger("Dayoffapproval","delete",json_encode($data),null);
							$logger->SaveData();
							echo json_encode($Dayoffapproval);
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "DeleteDayoffApproval";
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
							$doid = $this->post['id'];
							$data = $this->post['data'];
							$Dayoffdetail=Dayoffdetail::find('all',array('conditions'=>array("dayoff_id=?",$doid),'include'=>array('dayoff'=>array('employee'=>array('company','department','designation','grade')))));
							$allcheck = 0;
							foreach ($Dayoffdetail as $result) {
								if(is_null($result->isapproved)){
									$allcheck+=1;
								}
							}
							if (($data['approvalstatus']=='1') || ($data['approvalstatus']=='3')){
								$allcheck=0;
							}
							if($allcheck>0){
								$result= array("status"=>"error","message"=>"Need to do approval/reject on each detail dayoff request");
								echo json_encode($result);
							}else{
								$mode= $data['mode'];
								unset($data['id']);
								unset($data['superior']);
								unset($data['createdby']);
								unset($data['depthead']);
								unset($data['fullname']);
								unset($data['department']);
								unset($data['approveddoc']);
								unset($data['requestdate']);
								unset($data['employee']);
								unset($data['mtd']);
								unset($data['ytd']);
								$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
								$join   = "LEFT JOIN tbl_approver ON (tbl_dayoffapproval.approver_id = tbl_approver.id) ";
								if (isset($data['mode'])){
									$Dayoffapproval = Dayoffapproval::find('first', array('joins'=>$join,'conditions' => array("dayoff_id=? and tbl_approver.employee_id=?",$doid,$Employee->id),'include' => array('approver'=>array('employee','approvaltype'))));
									unset($data['mode']);
								}else{
									$Dayoffapproval = Dayoffapproval::find($this->post['id'],array('include' => array('approver'=>array('employee','approvaltype'))));
								}
								$olddata = $Dayoffapproval->to_array();
								foreach($data as $key=>$val){
									$val=($val=='false')?false:(($val=='true')?true:$val);
									$Dayoffapproval->$key=$val;
								}
								$Dayoffapproval->save();
								$logger = new Datalogger("Dayoffapproval","update",json_encode($olddata),json_encode($data));
								$logger->SaveData();
								if (isset($mode) && ($mode=='approve')){
									$Dayoff = Dayoff::find($doid);
									$reqdate =$Dayoff->requestdate;
									$joinx   = "LEFT JOIN tbl_approver ON (tbl_dayoffapproval.approver_id = tbl_approver.id) ";					
									$nDayoffapproval = Dayoffapproval::find('first',array('joins'=>$joinx,'conditions' => array("dayoff_id=? and ApprovalStatus=0",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
									$username = $nDayoffapproval->approver->employee->loginname;
									$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
									$Dayoffdetail=Dayoffdetail::find('all',array('conditions'=>array("dayoff_id=?",$doid),'include'=>array('dayoff'=>array('employee'=>array('company','department','designation','grade','location')))));
									$creator = Employee::find('first', array('conditions' => array("id=?",$result->dayoff->createdby)));
									$usr = Addressbook::find('first',array('conditions'=>array("username=?",$creator->loginname)));
									$email=$usr->email;
									foreach ($Dayoffdetail as &$result) {
										$dayoff=$result->dayoff->to_array();
										$emp=$result->dayoff->employee->to_array();
										$des=$result->dayoff->employee->designation->designationname;
										$gradeid=$result->dayoff->employee->grade->id;
										$levelid=$result->dayoff->employee->level_id;
										$grade=$result->dayoff->employee->grade->grade;
										$location=$result->dayoff->employee->location->location;
										$comp=$result->dayoff->employee->company->to_array();
										
										$dept=$result->dayoff->employee->department->to_array();
										$result = $result->to_array();
										$result['Dayoff']=$dayoff;
										$result['Dayoff']['Employee']=$emp;
										$result['Dayoff']['Employee']['Company']=$comp;
										$result['Dayoff']['Employee']['Department']=$dept;
									}
									$complete = false;
									$Dayoffhistory = new Dayoffhistory();
									$Dayoffhistory->date = date("Y-m-d h:i:s");
									$Dayoffhistory->fullname = $Employee->fullname;
									$Dayoffhistory->approvaltype = $Dayoffapproval->approver->approvaltype->approvaltype;
									$Dayoffhistory->remarks = $data['remarks'];
									$Dayoffhistory->dayoff_id = $doid;
									
									switch ($data['approvalstatus']){
										case '1':
											$Dayoff->requeststatus = 2;
											$emto=$email;$emname=$usr->fullname;
											$this->mail->Subject = "Online Approval System -> Need Rework";
											$red = 'Your Weekend/PH Cov. request require some rework :';
											$Dayoffhistory->actiontype = 3;
											break;
										case '2':
											// if(($levelid==4) || ($levelid==6)){
												// if ($Dayoffapproval->approver->approvaltype->id == 3 ){
													// $Dayoff->requeststatus = 3;
													// $emto=$email;$emname=$emp['fullname'];
													// $this->mail->Subject = "Online Approval System -> Approval Completed";
													// $red = '<p>Your Weekend/PH Cov. request has been approved</p>
													// <p><b><span lang=EN-US style=\'color:#002060\'>Note : Please <u>forward</u> this electronic approval to your respective Human Resource Department.</span></b></p>';
													
													// $Dayoffapproval = Dayoffapproval::find('all', array('joins'=>$join,'conditions' => array("dayoff_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
													// foreach ($Dayoffapproval as $data) {
														// if($data->approvalstatus==0){
															// $logger = new Datalogger("Dayoffapproval","delete",json_encode($data->to_array()),"automatic remove unnecessary approver by system");
															// $logger->SaveData();
															// $data->delete();
														// }
													// }
													// $complete = true;
												// }else{
													// $Dayoff->requeststatus = 1;
													// $emto=$adb->email;$emname=$adb->fullname;
													// $this->mail->Subject = "Online Approval System -> New Dayoff Submission";
													// $red = 'New Weekend/PH Cov. request is awaiting for your approval:';
												// }
											// }else{
												if ($Dayoffapproval->approver->isfinal == 1){
													$Dayoff->requeststatus = 3;
													$emto=$email;$emname=$usr->fullname;
													$this->mail->Subject = "Online Approval System -> Approval Completed";
													$red = '<p>Your Weekend/PH Cov. request has been approved</p>
													<p><b><span lang=EN-US style=\'color:#002060\'>Note : Please <u>forward</u> this electronic approval to your respective Human Resource Department.</span></b></p>';
													//delete unnecessary approver
													$Dayoffapproval = Dayoffapproval::find('all', array('joins'=>$join,'conditions' => array("dayoff_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
													foreach ($Dayoffapproval as $data) {
														if($data->approvalstatus==0){
															$logger = new Datalogger("Dayoffapproval","delete",json_encode($data->to_array()),"automatic remove unnecessary approver by system");
															$logger->SaveData();
															$data->delete();
														}
													}
													$complete =true;
												}else{
													$Dayoff->requeststatus = 1;
													$emto=$adb->email;$emname=$adb->fullname;
													$this->mail->Subject = "Online Approval System -> New Dayoff Submission";
													$red = 'New Dayoff request is awaiting for your approval:';
												}	
											// }
											$Dayoffhistory->actiontype = 4;							
											break;
										case '3':
											$Dayoff->requeststatus = 4;
											$emto=$email;$emname=$usr->fullname;
											$Dayoffhistory->actiontype = 5;
											$this->mail->Subject = "Online Approval System -> Request Rejected";
											$red = 'Your Dayoff request has been rejected';
											$Dayoffapproval = Dayoffapproval::find('all', array('conditions' => array("dayoff_id=? and approvalstatus='0'",$doid)));
											foreach ($Dayoffapproval as $data) {
												$data->approvalstatus=4;
												$data->save();
											}
											$Dayoffdetail = Dayoffdetail::find('all', array('conditions' => array("dayoff_id=?",$doid)));
											foreach ($Dayoffdetail as $data) {
												$data->isapproved=false;
												$data->save();
											}
											break;
										default:
											break;
									}
									
									$Dayoff->save();
									$Dayoffhistory->save();
									echo "email to :".$emto." ->".$emname;
									$this->mail->addAddress($emto, $emname);
									$this->mailbody .='<body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$emname.',</span></p>
														<p class=MsoNormal><span style="color:#1F497D">'.$red.'</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>';
									$this->mailbody .='<table class=MsoNormal border=0 cellspacing=0 cellpadding=0 width=683 style="width:511.95pt;margin-left:1.85pt;border-collapse:collapse">
														<tr style="height:12.75pt"><td width=196 nowrap valign=top style="width:146.65pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="color:#1F497D">*Personnel Number (SAP ID)</span></p></td><td width=19 nowrap valign=top style="width:13.9pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Arial","sans-serif"">:</span></p></td><td width=171 nowrap valign=top style="width:128.25pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><b>'.$emp['sapid'].'</b></p></td><td width=298 nowrap valign=top style="width:223.15pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td></tr>
														<tr style="height:12.75pt"><td width=196 nowrap valign=top style="width:146.65pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="color:#1F497D">*Name</span></p></td><td width=19 nowrap valign=top style="width:13.9pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Arial","sans-serif"">:</span></p></td><td width=171 nowrap valign=top style="width:128.25pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><b>'.$emp['fullname'].'</b></p></td><td width=298 nowrap valign=top style="width:223.15pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td></tr>
														<tr style="height:12.75pt"><td width=196 nowrap valign=top style="width:146.65pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="color:#1F497D">Position</span></p></td><td width=19 nowrap valign=top style="width:13.9pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Arial","sans-serif"">:</span></p></td><td width=469 nowrap colspan=2 valign=top style="width:351.4pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><b>'.$des.'</b></p></td></tr>
														<!--<tr style="height:12.75pt"><td width=196 nowrap valign=top style="width:146.65pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="color:#1F497D">*Grade </span></p></td><td width=19 nowrap valign=top style="width:13.9pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Arial","sans-serif"">:</span></p></td><td width=171 nowrap valign=top style="width:128.25pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><b>'.$grade.'</b></p></td><td width=298 nowrap valign=top style="width:223.15pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td></tr>-->
														<tr style="height:12.75pt"><td width=196 nowrap valign=top style="width:146.65pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="color:#1F497D">Business Group</span></p></td><td width=19 nowrap valign=top style="width:13.9pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Arial","sans-serif"">:</span></p></td><td width=171 nowrap valign=top style="width:128.25pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><b>'.$emp['companycode'].'</b></p></td><td width=298 nowrap valign=top style="width:223.15pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td></tr>
														<tr style="height:12.75pt"><td width=196 nowrap valign=top style="width:146.65pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="color:#1F497D">Location</span></p></td><td width=19 nowrap valign=top style="width:13.9pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Arial","sans-serif"">:</span></p></td><td width=171 nowrap valign=top style="width:128.25pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><b>'.$location.'</b></p></td><td width=298 nowrap valign=top style="width:223.15pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td></tr>
														<tr style="height:12.75pt"><td width=196 nowrap valign=top style="width:146.65pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="color:#1F497D">*Email / Telephone No.</span></p></td><td width=19 nowrap valign=top style="width:13.9pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Arial","sans-serif"">:</span></p></td><td width=469 nowrap colspan=2 valign=top style="width:351.4pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
														<tr style="height:12.75pt"><td width=196 nowrap valign=top style="width:146.65pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td><td width=19 nowrap valign=top style="width:13.9pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td><td width=171 nowrap valign=top style="width:128.25pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td><td width=298 nowrap valign=top style="width:223.15pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td></tr>
														<tr style="height:12.75pt"><td width=196 nowrap valign=top style="width:146.65pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal>Coverage Details</p></td><td width=19 nowrap valign=top style="width:13.9pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td><td width=171 nowrap valign=top style="width:128.25pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td><td width=298 nowrap valign=top style="width:223.15pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td></tr>
														<tr style="height:18.75pt"><td width=196 nowrap rowspan="2" style="width:146.65pt;border:solid windowtext 1.0pt;background:#F2F2F2;padding:0in 5.4pt 0in 5.4pt;height:18.75pt"><p class=MsoNormal align=center style="text-align:center"><b>Date (dd/mm/yyyy)</b></p></td><td width=487 nowrap colspan=3 style="width:365.3pt;border:solid windowtext 1.0pt;border-left:none;background:#F2F2F2;padding:0in 5.4pt 0in 5.4pt;height:18.75pt"><p class=MsoNormal align=center style="text-align:center"><b>Reason</b></p></td></tr>
														<tr style="height:18.75pt"><td width=190 nowrap colspan=2 style="width:140pt;border:solid windowtext 1.0pt;border-left:none;background:#F2F2F2;padding:0in 5.4pt 0in 5.4pt;height:18.75pt"><p class=MsoNormal align=center style="text-align:center"><b>Objectives</b></p></td><td width=298 nowrap valign=top style="width:223.15pt;border:solid windowtext 1.0pt;border-left:none;background:#F2F2F2;"><p class=MsoNormal valign=center align=center style="text-align:center"><b>Remarks</b></p></td></tr>';
									
									foreach ($Dayoffdetail as $data){
										$this->mailbody .='<tr style="height:22.5pt"><td width=196 nowrap style="width:146.65pt;border:solid windowtext 1.0pt;border-top:none;padding:0in 5.4pt 0in 5.4pt;height:22.5pt"><p class=MsoNormal valign=top align=center style="text-align:center">'.date('d/m/Y', strtotime($data['dateworked'])).'</p></td><td width=190 nowrap colspan=2 style="width:140pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:22.5pt"><p valign=top class=MsoNormal>'.$data['reason'].'</p></td><td width=298 nowrap valign=top style="width:223.15pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:22.5pt"><p valign=top class=MsoNormal>'.$data['remarks'].'</p></td></tr>';
									}
									$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.83.18/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.83.18/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
									
									$this->mail->msgHTML($this->mailbody);
									if ($complete){
										$Dayoffapproval = Dayoffapproval::find('all', array('joins'=>$join,'conditions' => array("dayoff_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
										$supname=$dateSup=$bgname=$datebg="";
										
										foreach ($Dayoffapproval as $data){
											if(($data->approver->approvaltype->id==5) || ($data->approver->employee_id==$Dayoff->superior)){
												$supname = $data->approver->employee->fullname;
												$usr = Addressbook::find('first',array('conditions'=>array("username=?",$data->approver->employee->loginname)));
												$supemail = $usr->email;
												$dateSup = date("d/m/Y",strtotime($data->approvaldate));
											}
											if(($data->approver->approvaltype->id==1) || ($data->approver->employee_id==$Dayoff->depthead)){
												$dhname = $data->approver->employee->fullname;
												$datedh = date("d/m/Y",strtotime($data->approvaldate));
												if (($levelid==4) || ($levelid==6)){
													$dhname = "";
												}
											}
											if($data->approver->approvaltype->id==2){
												$buhname = $data->approver->employee->fullname;
												$datebuh = date("d/m/Y",strtotime($data->approvaldate));
											}
											if($data->approver->approvaltype->id==3){
												$mdname = $data->approver->employee->fullname;
												$datemd = date("d/m/Y",strtotime($data->approvaldate));
											}
											if($data->approver->approvaltype->id==4){
												$bgname = $data->approver->employee->fullname;
												$datebg = date("d/m/Y",strtotime($data->approvaldate));
											}
										}
										$pdfContent="<h3 style='width:100%;text-align:center'>WEEKEND/PUBLIC HOLIDAY COVERAGE FORM</h3>";
										$pdfContent .= "Dengan ini diperintahkan agar melaksanakan kerja di waktu Weekend/Public Holiday kepada : <br>";
										$pdfContent .= "<i>Herewith instructed to work on weekend/public holiday to ; </i>";
										$pdfContent .= "<small><ol><li>This form is used based on superior's instruction only.</li>";
										$pdfContent .= "<li>Employee should complete this form and submit to BG HR</li>";
										$pdfContent .= "<li>Asteriks (*) indicates a mandatory field</li>";
										$pdfContent .= "<li>Approving Superior/BU Head/Deputy MD is required to initial on every coverage date</li></ol></small>";
										$pdfContent .= "<table border=0 cellspacing=3 cellpadding=3>";
										$pdfContent .= '<tr><td>*Personnel Number (SAP ID)</td><td>:</td><td style="border:solid windowtext 1.0pt;padding:2.5pt 5.4pt 2.5pt 5.4pt;">'.$emp['sapid'].'</td><td style="width=10px;"></td><td>*Superior Name</td><td>:</td><td style="border:solid windowtext 1.0pt;padding:2.5pt 5.4pt 2.5pt 5.4pt;">'.$supname.'</td></tr>
														<tr><td>*Name</td><td>:</td><td style="border:solid windowtext 1.0pt;padding:2.5pt 5.4pt 2.5pt 5.4pt;">'.$emp['fullname'].'</td><td style="width=10px;"></td><td>*Superior Email</td><td>:</td><td style="border:solid windowtext 1.0pt;padding:2.5pt 5.4pt 2.5pt 5.4pt;">'.$supemail.'</td></tr>
														<tr><td>Position</td><td>:</td><td style="border:solid windowtext 1.0pt;padding:2.5pt 5.4pt 2.5pt 5.4pt;">'.$des.'</td><td colspan=4></td></tr>
														<tr><td>*Grade </td><td>:</td><td style="border:solid windowtext 1.0pt;padding:2.5pt 5.4pt 2.5pt 5.4pt;">'.$grade.'</td><td colspan=4></td></tr>
														<tr><td>Business Group </td><td>:</td><td style="border:solid windowtext 1.0pt;padding:2.5pt 5.4pt 2.5pt 5.4pt;">'.$emp['companycode'].'</td><td colspan=4></td></tr>
														<tr><td>Location </td><td>:</td><td style="border:solid windowtext 1.0pt;padding:2.5pt 5.4pt 2.5pt 5.4pt;">'.$location.'</td><td colspan=4></td></tr>
														<tr><td>*Email / Telephone No.</td><td>:</td><td style="border:solid windowtext 1.0pt;padding:2.5pt 5.4pt 2.5pt 5.4pt;">'.$email.'</td><td colspan=4></td></tr>
														<tr><td><u>Coverage Details</u></td><td>:</td><td colspan=5></td></tr>
														<tr><td>*Work Schedule Code</td><td>:</td><td style="border:solid windowtext 1.0pt;padding:2.5pt 5.4pt 2.5pt 5.4pt;"> </td><td colspan=4></td></tr>
													</table><br>
													<table border=0 cellspacing=0 cellpadding=0 width="100%"><tr><td colspan="9" style="border-bottom:solid windowtext 1.0pt;padding-bottom:8px;">
													<table border=0 cellpadding=3 cellspacing=0>
													<tr><td rowspan=5 style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;text-align:center;background:#F2F2F2;">Date (dd/mm/yyyy)</td>
														<td colspan=2 style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;text-align:center;background:#F2F2F2;">Reason</td>
														<td colspan=3 style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;text-align:center;background:#F2F2F2;">Approving Initial</td></tr>
													<tr><td rowspan=4 style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;text-align:center;background:#F2F2F2;min-width:100px;max-width:300px;">Objectives</td>
														<td rowspan=4 style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;text-align:center;background:#F2F2F2;min-width:50px;max-width:100px;">Remarks</td>
														<td colspan=2 style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;text-align:center;background:#F2C200;">C1-D1</td>
														<td rowspan=2 style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;"></td></tr>
													<tr><td style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;text-align:center;background:#F2C200;">Dept. Head</td>
													<td style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;text-align:center;background:#F2C200;">BU Head</td></tr>
													<tr><td rowspan=2 style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;"></td>
													<td colspan=2 style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;text-align:center;background:#C2C2F2;">D2 & above & IS</td></tr>
													<tr><td style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;text-align:center;background:#C2C2F2;">BU Head</td>
													<td style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;text-align:center;background:#C2C2F2;">Deputy MD</td></tr>';
										foreach ($Dayoffdetail as $data){
											if ($data['isapproved']){
												$pdfContent .= '<tr><td style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;none;border-top:none;">'.date('d/m/Y', strtotime($data['dateworked'])).'</td>
																<td style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;min-width:100px;max-width:300px;">'.wordwrap($data['reason'], 60, "<br>").'</td>
																<td style="padding:0in 5.4pt 0in 5.4pt;border:solid windowtext 1.0pt;border-left:none;border-top:none;min-width:50px;max-width:100px;">'.wordwrap($data['remarks'], 50, "<br>").'</td>
																<td style="border:solid windowtext 1.0pt;border-left:none;border-top:none;">'.(($dhname=="")?'':(($data['isapproved']==1)?'<img src="images/approved.png" style="height:12.5pt" alt="Approved from System">':'<img src="images/rejected.png" style="height:12.5pt" alt="Rejected from System">')).'<br><small>'.$dhname.'<br>'.$datedh.'</small></td>
																<td style="border:solid windowtext 1.0pt;border-left:none;border-top:none;">'.(($buhname=="")?'':(($data['isapproved']==1)?'<img src="images/approved.png" style="height:12.5pt" alt="Approved from System">':'<img src="images/rejected.png" style="height:12.5pt" alt="Rejected from System">')).'<br><small>'.$buhname.'<br>'.$datebuh.'</small></td>
																<td style="border:solid windowtext 1.0pt;border-left:none;border-top:none;">'.((($levelid==4) || ($levelid==6))?(($data['isapproved']==1)?'<img src="images/approved.png" style="height:12.5pt" alt="Approved from System">':'<img src="images/rejected.png" style="height:12.5pt" alt="Rejected from System">'):'').'<br><small>'.$mdname.'<br>'.$datemd.'</small></td>
															</tr>';
											}
										}
										$pdfContent .= "</table><small>Note :<br>";
										$pdfContent .= "- The coverage days listed will be forfeited if it is not claimed within the validity period as per SOP.<br>";
										$pdfContent .= "- Level Approval :<div >- C1-D1 National Staff must be approved by Dept. Head & BU Head.<br>- D2 and above National Staff and all level International Staff must be approved by BU Head & Deputy MD.</div></small>";
										
										$pdfContent .= '<br><table border=0 cellspacing=0 cellpadding=0 style="width:511.95pt;margin-left:1.85pt;border-collapse:collapse">
														<tr style="height:12.75pt"><th style="padding:0in 5.4pt 0in 5.4pt;height:12.75pt" colspan=2>Applied by:</th><th></th><th colspan=2>Assigned by:</th><th></th><th colspan=2>Acknowledged by:</th></tr>
														<tr style="height:30pt">
															<td style="padding:0in 5.4pt 0in 5.4pt;height:30pt" colspan=2><img src="images/approved.png" style="height:25pt" alt="Approved from System"></td>
															<td></td>
															<td colspan=2><img src="images/approved.png" style="height:25pt" alt="Approved from System"></td>
															<td></td>
															<td colspan=2><img src="images/approved.png" style="height:25pt" alt="Approved from System"></td>
														</tr>
														<tr style="height:12.75pt">
															<td style="padding-right:0in 5.4px 0in 5.4px;height:12.75pt;border-bottom:solid windowtext 1.0pt"> '.$emp['fullname'].' </td>
															<td style="width:100px;text-align:center;padding-right:0in 5.4px 0in 5.4px;border-bottom:solid windowtext 1.0pt">'.date("d/m/Y",strtotime($reqdate)).'</td>
															<td style="width:50px;"></td>
															<td style="padding-right:0in 5.4px 0in 5.4px;border-bottom:solid windowtext 1.0pt"> '.$supname.' </td>
															<td style="width:100px;text-align:center;padding-right:0in 5.4px 0in 5.4px;border-bottom:solid windowtext 1.0pt">'.$dateSup.'</td>
															<td style="width:50px;"></td>
															<td style="padding-right:0in 5.4px 0in 5.4px;border-bottom:solid windowtext 1.0pt"> '.$bgname.' </td>
															<td  style="width:100px;text-align:center;padding-right:0in 5.4px 0in 5.4px;border-bottom:solid windowtext 1.0pt">'.$datebg.'</td>
														</tr>
														<tr style="height:12.75pt"><td style="padding:0in 5.4pt 0in 5.4pt;height:12.75pt">Employee</td><td style="width:100px;text-align:center;">Date<br>dd/mm/yyyy</td><td></td><td>Superior</td><td style="width:100px;text-align:center;">Date<br>dd/mm/yyyy</td><td></td><td>BG HR</td><td style="width:100px;text-align:center;">Date<br>dd/mm/yyyy</td></tr>
														</table>
														</td></tr>
															<tr style="height:12.75pt"><td colspan="2"><small>Document ID No : IHM-HRD-9002-FM</small></td><td></td><td colspan="2"><small>Issue Date : 01 September 2019</small></td><td ></td><td><small>Revision:1</small></td><td><small>Page 1 of 1 </small></td></tr>
														</table>';
														//echo $pdfContent;
										try {
											$html2pdf = new Html2Pdf('L', 'A4', 'fr');
											$html2pdf->writeHTML($pdfContent);
											ob_clean();
											$fileName ='doc'.DS.'dayoff'.DS.'pdf'.DS.'WPHCF_'.$emp['sapid'].'_'.date("YmdHis").'.pdf';
											$filePath = SITE_PATH.DS.$fileName;
											$html2pdf->output($filePath, 'F');
											$this->mail->addAttachment($filePath);
											$Dayoff->approveddoc=str_replace("\\","/",$fileName);
											$Dayoff->save();
										} catch (Html2PdfException $e) {
											$html2pdf->clean();
											$formatter = new ExceptionFormatter($e);
											$err = new Errorlog();
											$err->errortype = "PDFGenerator";
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
								}
								echo json_encode($Dayoffapproval);
							}
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "UpdateDayoffApproval";
							$err->errordate = date("Y-m-d h:i:s");
							$err->errormessage = $e->getMessage();
							$err->user = $this->currentUser->username;
							$err->ip = $this->ip;
							$err->save();
							$data = array("status"=>"error","message"=>$e->getMessage());
						}
						break;
					default:
						$Dayoffapproval = Dayoffapproval::all();
						foreach ($Dayoffapproval as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Dayoffapproval, JSON_NUMERIC_CHECK);
						break;
				}
			}
			// else{
				// $result= array("status"=>"autherror","message"=>"Authentication error or expired, please refresh and re login application");
				// echo json_encode($result);
			// }
		}
	}
	function DayoffDetail(){
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
							$Dayoffdetail = Dayoffdetail::find('all', array('conditions' => array("dayoff_id=?",$id)));
							foreach ($Dayoffdetail as &$result) {
								$appText = ($result->isapproved==null)?"":(($result->isapproved)?"Yes":"No");
								$usedText = ($result->isused==null)?"":(($result->isused)?"Yes":"No");
								$result		= $result->to_array();
								$result['isapproved'] = $appText;
								$result['isused'] = $usedText;
							}
							echo json_encode($Dayoffdetail, JSON_NUMERIC_CHECK);
						}else{
							$Dayoff = new Dayoff();
							echo json_encode($Dayoff);
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							$Dayoffdetail = Dayoffdetail::find('all', array('conditions' => array("dayoff_id=?",$query['dayoff_id'])));
							$data=array("jml"=>count($Dayoffdetail));
						}else if (isset($query['detail'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$joinx   = "LEFT JOIN tbl_dayoffreq as r ON (dayoff_id = r.id) left join tbl_employee e on r.employee_id=e.id ";	
							// if(($Employee->location->sapcode=='0200') || ($this->currentUser->isadmin)){
								$Dayoffdetail = Dayoffdetail::find('all', array('joins'=>$joinx,'conditions' => array("isApproved='1' and r.requeststatus='3' and dateworked between ? and ?",$query['startDate'],$query['endDate']),'order'=>"dateworked"));
							// }else{
							// 	$Dayoffdetail = Dayoffdetail::find('all', array('joins'=>$joinx,'conditions' => array("isApproved='1' and r.requeststatus='3' and e.company_id=?  and dateworked between ? and ?",$Employee->company_id,$query['startDate'],$query['endDate']),'order'=>"dateworked"));
							// }
							
							foreach ($Dayoffdetail as &$result) {
								$joine  = "LEFT JOIN tbl_employee s ON (tbl_dayoffreq.superior = s.id) left join tbl_employee d on (tbl_dayoffreq.depthead = d.id)";	
								$sel = 'tbl_dayoffreq.*,s.fullname as Superior, d.fullname as DeptHead';
								$Dayoff = Dayoff::find('first', array('select'=>$sel,'joins'=>$joine,'include'=>array('employee'=>array("department","location","company","designation","department")),'conditions' => array("tbl_dayoffreq.id=?",$result->dayoff_id)));
								$join  = "LEFT JOIN tbl_approver ON (tbl_dayoffapproval.approver_id = tbl_approver.id) ";	
								$Dayoffapproval = DayOffApproval::find('first',array('joins'=>$join,'conditions'=>array("dayoff_id=?",$result->dayoff_id),'order'=>"tbl_approver.sequence desc",'include' => array('approver'=>array('employee'))));
								$appText = ($result->isapproved==null)?"":(($result->isapproved)?"Yes":"No");
								$usedText = ($result->isused==null)?"":(($result->isused)?"Yes":"No");
								$result		= $result->to_array();
								$result['fullapproveddate']=$Dayoffapproval->approvaldate;
								$result['sapid']=$Dayoff->employee->sapid;
								$result['name']=$Dayoff->employee->fullname;
								$result['location']=$Dayoff->employee->location->location;
								$result['department']=$Dayoff->employee->department->departmentname;
								$result['position']=$Dayoff->employee->designation->designationname;
								$result['bu']=$Dayoff->employee->companycode;
								$result['superior']=$Dayoff->superior;
								$result['depthead']=$Dayoff->depthead;
								$result['isapproved'] = $appText;
								$result['isused'] = $usedText;
							}
							$data=$Dayoffdetail;
						}else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':
						try{
							$data = $this->post['data'];
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							unset($data['__KEY__']);
							if(!isset($data['dateworked'])){
								echo json_encode(array("status"=>"error","message"=>"Date worked not selected, please select date first"));
							}else{
								$joinx   = "LEFT JOIN tbl_dayoffreq as r ON (dayoff_id = r.id) ";	
								$dd = Dayoffdetail::find('all', array('joins'=>$joinx,'conditions' => array("dateworked=? and r.employee_id=? and (isapproved='1' or isapproved is null) ",$data['dateworked'],$Employee->id),'include'=>array("dayoff")));
								if (count($dd)>0){
									echo json_encode(array("status"=>"error","message"=>"You have another Request for selected date"));
								}else{
									$joins   = "LEFT JOIN tbl_dayoffreq as r ON (dayoff_id = r.id) ";	
									$do = Dayoffdetail::find('all', array('joins'=>$joins,'conditions' => array("weekday(dateworked)='6' and abs(datediff(dateworked,?))=7 and r.employee_id=? and (isapproved='1' or isapproved is null)",$data['dateworked'],$Employee->id),'include'=>array("dayoff")));
									$wd =date('N',strtotime($data['dateworked']));
									if(($wd==7) && (count($do)>0)){
										echo json_encode(array("status"=>"error","message"=>"Based on SOP employee cannot work more than 14 days"));
									}else{
										$Holiday = Holiday::find('all',array('conditions' => array("month(HolidayDate)=? and not(weekday(HolidayDate))='6'",date("m", strtotime($data['dateworked'])))));
										$sun= total_sun(date("m", strtotime($data['dateworked'])),date("Y", strtotime($data['dateworked'])));
										$hol= count($Holiday)>2?2:count($Holiday);
										$max = ($sun+$hol)-2;
										//covid -19 SOP only 2 days max per month
										//$max = 2;
										$joins   = "LEFT JOIN tbl_dayoffreq as r ON (dayoff_id = r.id) ";	
										$do = Dayoffdetail::find('all', array('joins'=>$joins,'conditions' => array("month(dateworked)=? and year(dateworked)=? and r.employee_id=?",date("m", strtotime($data['dateworked'])),date("Y", strtotime($data['dateworked'])),$Employee->id),'include'=>array("dayoff")));
										if (count($do)>=$max){
											echo json_encode(array("status"=>"error","message"=>"You have reached maximum number of Weekend Coverage Request ( ".$max." days) for the month ".date("F", strtotime($data['dateworked']))));
										}else{
											$Dayoffdetail = Dayoffdetail::create($data);
											$logger = new Datalogger("Dayoffdetail","create",null,json_encode($data));
											$logger->SaveData();
										}
									}
								}
							}
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "CreateDayoffDetail";
							$err->errordate = date("Y-m-d h:i:s");
							$err->errormessage = $e->getMessage();
							$err->user = $this->currentUser->username;
							$err->ip = $this->ip;
							$err->save();
							$data = array("status"=>"error","message"=>$e->getMessage());
						}
						break;
					case 'delete':
						try{
							$id = $this->post['id'];
							$Dayoffdetail = Dayoffdetail::find($id);
							$data=$Dayoffdetail->to_array();
							$Dayoffdetail->delete();
							$logger = new Datalogger("Dayoffdetail","delete",json_encode($data),null);
							$logger->SaveData();
							echo json_encode($Dayoffdetail);
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "DeleteDayoffDetail";
							$err->errordate = date("Y-m-d h:i:s");
							$err->errormessage = $e->getMessage();
							$err->user = $this->currentUser->username;
							$err->ip = $this->ip;
							$err->save();
							$data = array("status"=>"error","message"=>$e->getMessage());
						}
						break;
					case 'update':
						try {
							$id = $this->post['id'];
							$data = $this->post['data'];
							$Dayoffdetail = Dayoffdetail::find($id);
							$Dayoff = Dayoff::find($Dayoffdetail->dayoff_id);
							$Employee = Employee::find('first', array('conditions' => array("id=?",$Dayoff->employee_id)));
							$joinx   = "LEFT JOIN tbl_dayoffreq as r ON (dayoff_id = r.id) ";	
							$dd = Dayoffdetail::find('all', array('joins'=>$joinx,'conditions' => array("dateworked=? and r.employee_id=?  and (isapproved='1' or isapproved is null) ",$data['dateworked'],$Employee->id),'include'=>array("dayoff")));
							if (count($dd)>0){
								echo json_encode(array("status"=>"error","message"=>"You have another Dayoff Request for selected date"));
							}else{
								$joins   = "LEFT JOIN tbl_dayoffreq as r ON (dayoff_id = r.id) ";	
								$do = Dayoffdetail::find('all', array('joins'=>$joins,'conditions' => array("weekday(dateworked)='6' and abs(datediff(dateworked,?))=7 and r.employee_id=? and not(tbl_dayoffdetail.id=?) and (isapproved='1' or isapproved is null)",$data['dateworked'],$Employee->id,$id),'include'=>array("dayoff")));
									
								if((date('N',strtotime($data['dateworked']))==7) && (count($do)>0)){
									echo json_encode(array("status"=>"error","message"=>"Based on SOP employee cannot work more than 14 days"));
								}else{
									$Holiday = Holiday::find('all',array('conditions' => array("month(HolidayDate)=? and not(weekday(HolidayDate)='6')",date("m", strtotime($data['dateworked'])))));
									$sun= total_sun(date("m", strtotime($data['dateworked'])),date("Y", strtotime($data['dateworked'])));
									$hol= count($Holiday)>2?2:count($Holiday);
									$max = ($sun+$hol)-2;
									$joins   = "LEFT JOIN tbl_dayoffreq as r ON (dayoff_id = r.id) ";	
									$do = Dayoffdetail::find('all', array('joins'=>$joins,'conditions' => array("month(dateworked)=? and year(dateworked)=? and r.employee_id=? and not(tbl_dayoffdetail.id=?)",date("m", strtotime($data['dateworked'])),date("Y", strtotime($data['dateworked'])),$Employee->id,$id),'include'=>array("dayoff")));
									if (count($do)>=$max){
										echo json_encode(array("status"=>"error","message"=>"You have reached maximum number of Dayoff Request ( ".$max." days) for the month ".date("F", strtotime($data['dateworked']))));
									}else{
										//$Dayoffdetail = Dayoffdetail::find($id);
										$olddata = $Dayoffdetail->to_array();
										foreach($data as $key=>$val){					
											$val=($val=='No')?false:(($val=='Yes')?true:$val);
											$Dayoffdetail->$key=$val;
										}
										$Dayoffdetail->save();
										$logger = new Datalogger("Dayoffdetail","update",json_encode($olddata),json_encode($data));
										$logger->SaveData();
										echo json_encode($Dayoffdetail);
									}
								}
							}
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "UpdateDayoffDetail";
							$err->errordate = date("Y-m-d h:i:s");
							$err->errormessage = $e->getMessage();
							$err->user = $this->currentUser->username;
							$err->ip = $this->ip;
							$err->save();
							$data = array("status"=>"error","message"=>$e->getMessage());
						}
						break;
					default:
						$Dayoffdetail = Dayoffdetail::all();
						foreach ($Dayoffdetail as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Dayoffdetail, JSON_NUMERIC_CHECK);
						break;
				}
			}
			// else{
				// $result= array("status"=>"autherror","message"=>"Authentication error or expired, please refresh and re login application");
				// echo json_encode($result);
			// }
		}
	}
	function Dayoff(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'byid':
						$id = $this->post['id'];
						$Dayoff = Dayoff::find($id, array('include' => array('employee'=>array('company','department','designation'))));
						if ($Dayoff){
							$joinx   = "LEFT JOIN tbl_dayoffreq as r ON (dayoff_id = r.id) ";	
							$dMtd = Dayoffdetail::find('all', array('joins'=>$joinx,'conditions' => array("r.employee_id=? and r.RequestStatus='3' and isApproved='1' and Year(Dateworked)=year(now())  and month(Dateworked)=month(now()) ",$Dayoff->employee->id),'include'=>array("dayoff")));
							$dYtd = Dayoffdetail::find('all', array('joins'=>$joinx,'conditions' => array("r.employee_id=? and r.RequestStatus='3' and isApproved='1' and Year(Dateworked)=year(now())",$Dayoff->employee->id),'include'=>array("dayoff")));
							$cMtd = count($dMtd);
							$cYtd = count($dYtd);
							$fullname = $Dayoff->employee->fullname;
							$department = $Dayoff->employee->department->departmentname;
							$data=$Dayoff->to_array();
							$data['mtd'] = $cMtd;
							$data['ytd'] = $cYtd;
							$data['fullname']=$fullname;
							$data['department']=$department;
							echo json_encode($data, JSON_NUMERIC_CHECK);
						}else{
							$Dayoff = new Dayoff();
							echo json_encode($Dayoff);
						}
						break;
					case 'find':
						$query=$this->post['query'];					
						if(isset($query['status'])){
							switch ($query['status']){
								case 'chemp':
									$employee_id = $query['employee_id'];
									$id=$query['dayoff_id'];
									$Dayoff = Dayoff::find($id);
									$Dayoff->employee_id = $employee_id;
									$Dayoff->save();
									$Employee = Employee::find('first', array('conditions' => array("id=?",$employee_id),"include"=>array("location","department","company")));
									$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";
									$Dayoffapproval = Dayoffapproval::find('all',array('conditions' => array("dayoff_id=?",$id)));	
									foreach ($Dayoffapproval as &$result) {
										$result->delete();
										$logger = new Datalogger("Dayoffapproval","delete",json_encode($result->to_array()),"delete Approval because employee changed");
									}
									if(($Employee->level_id==4) || ($Employee->level_id==6) ){
										$Approver3 = Approver::find('first',array('conditions'=>array("module='Dayoff' and isactive='1' and approvaltype_id=3")));
										if(count($Approver3)>0){
											$Dayoffapproval = new Dayoffapproval();
											$Dayoffapproval->dayoff_id = $Dayoff->id;
											$Dayoffapproval->approver_id = $Approver3->id;
											$Dayoffapproval->save();
										}
									}
									// if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,3)=="025") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220") || ($Employee->department->sapcode=="13000090") || ($Employee->department->sapcode=="13000121") || ($Employee->company->sapcode=="NKF") || ($Employee->company->sapcode=="RND")){
										
									// 	$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Dayoff' and tbl_approver.isactive='1' and approvaltype_id=4 and tbl_employee.location_id='8'")));
									// 	if(count($Approver2)>0){
									// 		$Dayoffapproval = new Dayoffapproval();
									// 		$Dayoffapproval->dayoff_id = $Dayoff->id;
									// 		$Dayoffapproval->approver_id = $Approver2->id;
									// 		$Dayoffapproval->save();
									// 	}
										
									// 	if(($Employee->department->sapcode!="13000090") && ($Employee->department->sapcode!="13000121") && ($Employee->company->sapcode!="NKF") && ($Employee->company->sapcode!="RND")  && ($Employee->companycode!="BCL")  && ($Employee->companycode!="LDU")){
									// 		if(($Employee->level_id!=4) && ($Employee->level_id!=6) ){
									// 			$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Dayoff' and tbl_approver.isactive='1' and approvaltype_id=2 and tbl_employee.companycode='KPSI' and not(tbl_employee.id=?)",$Employee->id)));
									// 			if(count($Approver)>0){
									// 				$Dayoffapproval = new Dayoffapproval();
									// 				$Dayoffapproval->dayoff_id = $Dayoff->id;
									// 				$Dayoffapproval->approver_id = $Approver->id;
									// 				$Dayoffapproval->save();
									// 			}
									// 		}
									// 	}else{
									// 		$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Dayoff' and tbl_approver.isactive='1' and approvaltype_id=2 and not tbl_employee.companycode='KPSI' and tbl_employee.company_id=? and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
									// 		if(count($Approver)>0){
									// 			$Dayoffapproval = new Dayoffapproval();
									// 			$Dayoffapproval->dayoff_id = $Dayoff->id;
									// 			$Dayoffapproval->approver_id = $Approver->id;
									// 			$Dayoffapproval->save();
									// 		}else{
									// 			$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Dayoff' and tbl_approver.isactive='1' and approvaltype_id=2 and tbl_employee.company_id=? and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
									// 			if(count($Approver)>0){
									// 				$Dayoffapproval = new Dayoffapproval();
									// 				$Dayoffapproval->dayoff_id = $Dayoff->id;
									// 				$Dayoffapproval->approver_id = $Approver->id;
									// 				$Dayoffapproval->save();
									// 			}
									// 		}
									// 	}
										
									// }else{
									// 	if($Employee->companycode == 'LDU') {
									// 		$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Dayoff' and tbl_approver.isactive='1' and approvaltype_id=2 and tbl_employee.companycode='LDU' and not tbl_employee.companycode='KPSI'  and not(tbl_employee.id=?)",$Employee->id)));
									// 	} else {
									// 		$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Dayoff' and tbl_approver.isactive='1' and approvaltype_id=2 and tbl_employee.company_id=? and not tbl_employee.companycode='KPSI'  and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
									// 	}
									// 	if(count($Approver)>0){
									// 		$Dayoffapproval = new Dayoffapproval();
									// 		$Dayoffapproval->dayoff_id = $Dayoff->id;
									// 		$Dayoffapproval->approver_id = $Approver->id;
									// 		$Dayoffapproval->save();
									// 	} else {
									// 		$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Dayoff' and tbl_approver.isactive='1' and approvaltype_id=2 and tbl_employee.company_id=? and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
									// 		if(count($Approver)>0){
									// 			$Dayoffapproval = new Dayoffapproval();
									// 			$Dayoffapproval->dayoff_id = $Dayoff->id;
									// 			$Dayoffapproval->approver_id = $Approver->id;
									// 			$Dayoffapproval->save();
									// 		}
									// 	}
									// 	$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Dayoff' and tbl_approver.isactive='1' and approvaltype_id=4 and tbl_employee.company_id=?",$Employee->company_id)));
									// 	if(count($Approver2)>0){
									// 		$Dayoffapproval = new Dayoffapproval();
									// 		$Dayoffapproval->dayoff_id = $Dayoff->id;
									// 		$Dayoffapproval->approver_id = $Approver2->id;
									// 		$Dayoffapproval->save();
									// 	}
									// }

									$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Dayoff' and tbl_approver.isactive='1' and approvaltype_id=4 and CompanyList like '%".$Employee->companycode."%' ")));
									if(count($Approver2)>0){
										$Dayoffapproval = new Dayoffapproval();
										$Dayoffapproval->dayoff_id = $Dayoff->id;
										$Dayoffapproval->approver_id = $Approver2->id;
										$Dayoffapproval->save();
									}
									if(($Employee->level_id!=4) && ($Employee->level_id!=6) ){
										$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Dayoff' and tbl_approver.isactive='1' and approvaltype_id=2 and CompanyList like '%".$Employee->companycode."%' and not(tbl_employee.id=?)",$Employee->id)));
										if(count($Approver)>0){
											$Dayoffapproval = new Dayoffapproval();
											$Dayoffapproval->dayoff_id = $Dayoff->id;
											$Dayoffapproval->approver_id = $Approver->id;
											$Dayoffapproval->save();
										}
									}
									
									break;
								default:
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$query['username'])));
									$Dayoff = Dayoff::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Dayoff as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Dayoff));
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
							$Dayoff = Dayoff::create($data);
							$data=$Dayoff->to_array();
							$joinx   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";
							$joins   = "LEFT JOIN tbl_approver ON (tbl_dayoffapproval.approver_id = tbl_approver.id) ";	
							// $Approverx = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Dayoff' and tbl_approver.isactive='1' and approvaltype_id=1 and tbl_employee.company_id=? and tbl_employee.department_id=? and tbl_employee.location_id=? and not(tbl_employee.id=?)",$Employee->company_id,$Employee->department_id,$Employee->location_id,$Employee->id)));
							// if(count($Approverx)>0){
								// $Dayoffapproval = new Dayoffapproval();
								// $Dayoffapproval->dayoff_id = $Dayoff->id;
								// $Dayoffapproval->approver_id = $Approverx->id;
								// $Dayoffapproval->save();
							// }
							if(($Employee->level_id==4) || ($Employee->level_id==6) ){
								$Approver3 = Approver::find('first',array('conditions'=>array("module='Dayoff' and isactive='1' and approvaltype_id=3")));
								if(count($Approver3)>0){
									$Dayoffapproval = new Dayoffapproval();
									$Dayoffapproval->dayoff_id = $Dayoff->id;
									$Dayoffapproval->approver_id = $Approver3->id;
									$Dayoffapproval->save();
								}
							}
							// if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,3)=="025") || ($Employee->department->sapcode=="13000090") || ($Employee->department->sapcode=="13000121") || ($Employee->company->sapcode=="NKF") || ($Employee->company->sapcode=="RND"))
							// {
								$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Dayoff' and tbl_approver.isactive='1' and approvaltype_id=4 and CompanyList like '%".$Employee->companycode."%' ")));
								// $Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Dayoff' and tbl_approver.isactive='1' and approvaltype_id=4 and tbl_employee.location_id='8'")));
								if(count($Approver2)>0){
									$Dayoffapproval = new Dayoffapproval();
									$Dayoffapproval->dayoff_id = $Dayoff->id;
									$Dayoffapproval->approver_id = $Approver2->id;
									$Dayoffapproval->save();
								}
								// if(($Employee->department->sapcode!="13000090") && ($Employee->department->sapcode!="13000121") && (substr(strtolower($Employee->location->sapcode),0,3)!="025") && ($Employee->company->sapcode!="NKF") && ($Employee->company->sapcode!="RND")  && ($Employee->company->companycode!="BCL")  && ($Employee->company->companycode!="LDU")){
									if(($Employee->level_id!=4) && ($Employee->level_id!=6) ){
										$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Dayoff' and tbl_approver.isactive='1' and approvaltype_id=2 and CompanyList like '%".$Employee->companycode."%' and not(tbl_employee.id=?)",$Employee->id)));
										// $Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Dayoff' and tbl_approver.isactive='1' and approvaltype_id=2 and tbl_employee.companycode='KPSI' and not(tbl_employee.id=?)",$Employee->id)));
										if(count($Approver)>0){
											$Dayoffapproval = new Dayoffapproval();
											$Dayoffapproval->dayoff_id = $Dayoff->id;
											$Dayoffapproval->approver_id = $Approver->id;
											$Dayoffapproval->save();
										}
									}
								// }else{
								// 	if($Employee->companycode == 'LDU') {
								// 		$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Dayoff' and tbl_approver.isactive='1' and approvaltype_id=2 and tbl_employee.companycode='LDU' and not tbl_employee.companycode='KPSI'  and not(tbl_employee.id=?)",$Employee->id)));
								// 	} else {
								// 		$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Dayoff' and tbl_approver.isactive='1' and approvaltype_id=2 and tbl_employee.company_id=? and not tbl_employee.companycode='KPSI'  and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
								// 	}
								// 	if(count($Approver)>0){
								// 		$Dayoffapproval = new Dayoffapproval();
								// 		$Dayoffapproval->dayoff_id = $Dayoff->id;
								// 		$Dayoffapproval->approver_id = $Approver->id;
								// 		$Dayoffapproval->save();
								// 	}else{
								// 		$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Dayoff' and tbl_approver.isactive='1' and approvaltype_id=2 and tbl_employee.company_id=? and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
								// 		if(count($Approver)>0){
								// 			$Dayoffapproval = Dayoffapproval::find('all',array('joins'=>$joins,'conditions' => array("dayoff_id=? and tbl_approver.employee_id=?",$Dayoff->id,$Approver->employee_id)));	
								// 			foreach ($Dayoffapproval as &$result) {
								// 				$result		= $result->to_array();
								// 				$result['no']=1;
								// 			}			
								// 			if(count($Dayoffapproval)==0){ 
								// 				$Dayoffapproval = new Dayoffapproval();
								// 				$Dayoffapproval->dayoff_id = $Dayoff->id;
								// 				$Dayoffapproval->approver_id = $Approver->id;
								// 				$Dayoffapproval->save();
								// 			}
								// 		}
								// 	}
								// }	
							// }else{
							// 	$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Dayoff' and tbl_approver.isactive='1' and approvaltype_id=2 and not tbl_employee.companycode='KPSI' and tbl_employee.company_id=? and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
							// 	if(count($Approver)>0){
							// 		$Dayoffapproval = new Dayoffapproval();
							// 		$Dayoffapproval->dayoff_id = $Dayoff->id;
							// 		$Dayoffapproval->approver_id = $Approver->id;
							// 		$Dayoffapproval->save();
							// 	}else{
							// 		$Approver = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Dayoff' and tbl_approver.isactive='1' and approvaltype_id=2 and tbl_employee.company_id=? and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
							// 		if(count($Approver)>0){
							// 			$Dayoffapproval = Dayoffapproval::find('all',array('joins'=>$joins,'conditions' => array("dayoff_id=? and tbl_approver.employee_id=?",$Dayoff->id,$Approver->employee_id)));	
							// 			foreach ($Dayoffapproval as &$result) {
							// 				$result		= $result->to_array();
							// 				$result['no']=1;
							// 			}			
							// 			if(count($Dayoffapproval)==0){ 
							// 				$Dayoffapproval = new Dayoffapproval();
							// 				$Dayoffapproval->dayoff_id = $Dayoff->id;
							// 				$Dayoffapproval->approver_id = $Approver->id;
							// 				$Dayoffapproval->save();
							// 			}
							// 		}
							// 	}
							// 	$Approver2 = Approver::find('first',array('joins'=>$joinx,'conditions'=>array("module='Dayoff' and tbl_approver.isactive='1' and approvaltype_id=4 and tbl_employee.company_id=? ",$Employee->company_id)));
							// 	if(count($Approver2)>0){
							// 		$Dayoffapproval = new Dayoffapproval();
							// 		$Dayoffapproval->dayoff_id = $Dayoff->id;
							// 		$Dayoffapproval->approver_id = $Approver2->id;
							// 		$Dayoffapproval->save();
							// 	}
							// }
							
							$Dayoffhistory = new Dayoffhistory();
							$Dayoffhistory->date = date("Y-m-d h:i:s");
							$Dayoffhistory->fullname = $Employee->fullname;
							$Dayoffhistory->approvaltype = "Originator";
							$Dayoffhistory->dayoff_id = $Dayoff->id;
							$Dayoffhistory->actiontype = 0;
							$Dayoffhistory->save();
							
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "CreateDayoff";
							$err->errordate = date("Y-m-d h:i:s");
							$err->errormessage = $e->getMessage();
							$err->user = $this->currentUser->username;
							$err->ip = $this->ip;
							$err->save();
							$data = array("status"=>"error","message"=>$e->getMessage());
						}
						$logger = new Datalogger("Dayoff","create",null,json_encode($data));
						$logger->SaveData();
						echo json_encode($data);									
						break;
					case 'delete':
						try {				
							$id = $this->post['id'];
							$Dayoff = Dayoff::find($id);
							if ($Dayoff->requeststatus==0 || $Dayoff->requeststatus==2){
								$approval = Dayoffapproval::find("all",array('conditions' => array("dayoff_id=?",$id)));
								foreach ($approval as $delr){
									$delr->delete();
								}
								$detail = Dayoffdetail::find("all",array('conditions' => array("dayoff_id=?",$id)));
								foreach ($detail as $delr){
									$delr->delete();
								}
								$hist = Dayoffhistory::find("all",array('conditions' => array("dayoff_id=?",$id)));
								foreach ($hist as $delr){
									$delr->delete();
								}
								$data = $Dayoff->to_array();
								$Dayoff->delete();
								$logger = new Datalogger("Dayoff","delete",json_encode($data),null);
								$logger->SaveData();
								echo json_encode($Dayoff);
							} else {
								$data = array("status"=>"error","message"=>"You can't delete submitted request");
								echo json_encode($data);
							}
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "DeleteDayoff";
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
							$Dayoff = Dayoff::find($id);
							$olddata = $Dayoff->to_array();
							$superior = $data['superior'];
							$depthead = $data['depthead'];
							unset($data['fullname']);
							unset($data['department']);
							unset($data['approvalstatus']);
							unset($data['mtd']);
							unset($data['ytd']);
							//unset($data['employee']);
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							if($superior==$Employee->id){
								$result= array("status"=>"error","message"=>"You cannot select yourself as your Direct superior");
								echo json_encode($result);
							}else{
								foreach($data as $key=>$val){					
									$val=($val=='false')?false:(($val=='true')?true:$val);
									$Dayoff->$key=$val;
								}
								$Dayoff->save();
								if (isset($data['depthead'])){
									if(($Employee->level_id==4) || ($Employee->level_id==6) ){
									}else{
										$joins   = "LEFT JOIN tbl_approver ON (tbl_dayoffapproval.approver_id = tbl_approver.id) ";					
										$dx = Dayoffapproval::find('all',array('joins'=>$joins,'conditions' => array("dayoff_id=? and tbl_approver.approvaltype_id=1 and not(tbl_approver.employee_id=?)",$id,$depthead)));	
										foreach ($dx as $result) {
											//delete same type approver
											$result->delete();
											$logger = new Datalogger("Dayoffapproval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
										}
										$joins   = "LEFT JOIN tbl_approver ON (tbl_dayoffapproval.approver_id = tbl_approver.id) ";					
										$Dayoffapproval = Dayoffapproval::find('all',array('joins'=>$joins,'conditions' => array("dayoff_id=? and tbl_approver.employee_id=?",$id,$depthead)));	
										foreach ($Dayoffapproval as &$result) {
											$result		= $result->to_array();
											$result['no']=1;
										}			
										if(count($Dayoffapproval)==0){ 
											$Approver = Approver::find('first',array('conditions'=>array("module='Dayoff' and employee_id=? and approvaltype_id=1",$depthead)));
											if(count($Approver)>0){
												$Dayoffapproval = new Dayoffapproval();
												$Dayoffapproval->dayoff_id = $Dayoff->id;
												$Dayoffapproval->approver_id = $Approver->id;
												$Dayoffapproval->save();
											}else{
												$approver = new Approver();
												$approver->module = "Dayoff";
												$approver->employee_id=$depthead;
												$approver->sequence=1;
												$approver->approvaltype_id = 1;
												$approver->isfinal = false;
												$approver->save();
												$Dayoffapproval = new Dayoffapproval();
												$Dayoffapproval->dayoff_id = $Dayoff->id;
												$Dayoffapproval->approver_id = $approver->id;
												$Dayoffapproval->save();
											}
										}
									}
								}
								if (isset($data['superior'])){
									$joins   = "LEFT JOIN tbl_approver ON (tbl_dayoffapproval.approver_id = tbl_approver.id) ";					
									$dx = Dayoffapproval::find('all',array('joins'=>$joins,'conditions' => array("dayoff_id=? and tbl_approver.approvaltype_id=5 and not(tbl_approver.employee_id=?)",$id,$superior)));	
									foreach ($dx as $result) {
										//delete same type approver
										$result->delete();
										$logger = new Datalogger("Dayoffapproval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
									}
									$joins   = "LEFT JOIN tbl_approver ON (tbl_dayoffapproval.approver_id = tbl_approver.id) ";					
									$Dayoffapproval = Dayoffapproval::find('all',array('joins'=>$joins,'conditions' => array("dayoff_id=? and tbl_approver.employee_id=?",$id,$superior)));	
									foreach ($Dayoffapproval as &$result) {
										$result		= $result->to_array();
										$result['no']=1;
									}			
									if(count($Dayoffapproval)==0){ 
										$Approver = Approver::find('first',array('conditions'=>array("module='Dayoff' and employee_id=? and approvaltype_id=5",$superior)));
										if(count($Approver)>0){
											$Dayoffapproval = new Dayoffapproval();
											$Dayoffapproval->dayoff_id = $Dayoff->id;
											$Dayoffapproval->approver_id = $Approver->id;
											$Dayoffapproval->save();
										}else{
											$approver = new Approver();
											$approver->module = "Dayoff";
											$approver->employee_id=$superior;
											$approver->sequence=0;
											$approver->approvaltype_id = 5;
											$approver->isfinal = false;
											$approver->save();
											$Dayoffapproval = new Dayoffapproval();
											$Dayoffapproval->dayoff_id = $Dayoff->id;
											$Dayoffapproval->approver_id = $approver->id;
											$Dayoffapproval->save();
										}
									}
								}
								if($data['requeststatus']==1){
									$Dayoffapproval = Dayoffapproval::find('all', array('conditions' => array("dayoff_id=?",$id)));					
									foreach($Dayoffapproval as $data){
										$data->approvalstatus=0;
										$data->save();
									}
									$joinx   = "LEFT JOIN tbl_approver ON (tbl_dayoffapproval.approver_id = tbl_approver.id) ";					
									$Dayoffapproval = Dayoffapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and dayoff_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
									$username = $Dayoffapproval->approver->employee->loginname;
									$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
									$email = $adb->email;
									$Dayoffdetail=Dayoffdetail::find('all',array('conditions'=>array("dayoff_id=?",$id),'include'=>array('dayoff'=>array('employee'=>array('company','department','designation','grade')))));
									foreach ($Dayoffdetail as &$result) {
										$dayoff=$result->dayoff->to_array();
										$emp=$result->dayoff->employee->to_array();
										$des=$result->dayoff->employee->designation->designationname;
										$grade=$result->dayoff->employee->grade->grade;
										$comp=$result->dayoff->employee->company->to_array();
										$usr = Addressbook::find('first',array('conditions'=>array("username=?",$result->dayoff->employee->loginname)));
										$email=$usr->email;
										$dept=$result->dayoff->employee->department->to_array();
										$result = $result->to_array();
										$result['Dayoff']=$dayoff;
										$result['Dayoff']['Employee']=$emp;
										$result['Dayoff']['Employee']['Company']=$comp;
										$result['Dayoff']['Employee']['Department']=$dept;
									}
									$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$adb->fullname.',</span></p>
														<p class=MsoNormal><span style="color:#1F497D">New Weekend /PH Cov. request is awaiting for your approval:</span></p>
														<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
														<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width=683 style="width:511.95pt;margin-left:1.85pt;border-collapse:collapse">
														<tr style="height:12.75pt"><td width=196 nowrap valign=top style="width:146.65pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="color:#1F497D">Personnel Number (SAP ID)</span></p></td><td width=19 nowrap valign=top style="width:13.9pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Arial","sans-serif"">:</span></p></td><td width=171 nowrap valign=top style="width:128.25pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><b>'.$emp['sapid'].'</b></p></td><td width=298 nowrap valign=top style="width:223.15pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td></tr>
														<tr style="height:12.75pt"><td width=196 nowrap valign=top style="width:146.65pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="color:#1F497D">Name</span></p></td><td width=19 nowrap valign=top style="width:13.9pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Arial","sans-serif"">:</span></p></td><td width=171 nowrap valign=top style="width:128.25pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><b>'.$emp['fullname'].'</b></p></td><td width=298 nowrap valign=top style="width:223.15pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td></tr>
														<tr style="height:12.75pt"><td width=196 nowrap valign=top style="width:146.65pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="color:#1F497D">Position</span></p></td><td width=19 nowrap valign=top style="width:13.9pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Arial","sans-serif"">:</span></p></td><td width=469 nowrap colspan=2 valign=top style="width:351.4pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><b>'.$des.'</b></p></td></tr>
														<!-- <tr style="height:12.75pt"><td width=196 nowrap valign=top style="width:146.65pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="color:#1F497D">Grade </span></p></td><td width=19 nowrap valign=top style="width:13.9pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Arial","sans-serif"">:</span></p></td><td width=171 nowrap valign=top style="width:128.25pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><b>'.$grade.'</b></p></td><td width=298 nowrap valign=top style="width:223.15pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td></tr>-->
														<tr style="height:12.75pt"><td width=196 nowrap valign=top style="width:146.65pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="color:#1F497D">Business Group</span></p></td><td width=19 nowrap valign=top style="width:13.9pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Arial","sans-serif"">:</span></p></td><td width=171 nowrap valign=top style="width:128.25pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><b>'.$emp['companycode'].'</b></p></td><td width=298 nowrap valign=top style="width:223.15pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td></tr>
														<tr style="height:12.75pt"><td width=196 nowrap valign=top style="width:146.65pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="color:#1F497D">Location</span></p></td><td width=19 nowrap valign=top style="width:13.9pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Arial","sans-serif"">:</span></p></td><td width=171 nowrap valign=top style="width:128.25pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><b>'.$comp['companylocation'].'</b></p></td><td width=298 nowrap valign=top style="width:223.15pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td></tr>
														<tr style="height:12.75pt"><td width=196 nowrap valign=top style="width:146.65pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="color:#1F497D">Email / Telephone No.</span></p></td><td width=19 nowrap valign=top style="width:13.9pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Arial","sans-serif"">:</span></p></td><td width=469 nowrap colspan=2 valign=top style="width:351.4pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal><b>'.$email.'</b></p></td></tr>
														<tr style="height:12.75pt"><td width=196 nowrap valign=top style="width:146.65pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td><td width=19 nowrap valign=top style="width:13.9pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td><td width=171 nowrap valign=top style="width:128.25pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td><td width=298 nowrap valign=top style="width:223.15pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td></tr>
														<tr style="height:12.75pt"><td width=196 nowrap valign=top style="width:146.65pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"><p class=MsoNormal>Coverage Details</p></td><td width=19 nowrap valign=top style="width:13.9pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td><td width=171 nowrap valign=top style="width:128.25pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td><td width=298 nowrap valign=top style="width:223.15pt;padding:0in 5.4pt 0in 5.4pt;height:12.75pt"></td></tr>
														<tr style="height:18.75pt"><td width=196 nowrap rowspan="2" style="width:146.65pt;border:solid windowtext 1.0pt;background:#F2F2F2;padding:0in 5.4pt 0in 5.4pt;height:18.75pt"><p class=MsoNormal align=center style="text-align:center"><b>Date (dd/mm/yyyy)</b></p></td><td width=487 nowrap colspan=3 style="width:365.3pt;border:solid windowtext 1.0pt;border-left:none;background:#F2F2F2;padding:0in 5.4pt 0in 5.4pt;height:18.75pt"><p class=MsoNormal align=center style="text-align:center"><b>Reason</b></p></td></tr>
														<tr style="height:18.75pt"><td width=190 nowrap colspan=2 style="width:140pt;border:solid windowtext 1.0pt;border-left:none;background:#F2F2F2;padding:0in 5.4pt 0in 5.4pt;height:18.75pt"><p class=MsoNormal valign=center  align=center style="text-align:center"><b>Objectives</b></p></td><td width=298 nowrap valign=top style="width:223.15pt;border:solid windowtext 1.0pt;border-left:none;background:#F2F2F2;"><p class=MsoNormal valign=center  align=center style="text-align:center"><b>Remarks</b></p></td></tr>';
									foreach ($Dayoffdetail as $data){
										$this->mailbody .='<tr style="height:22.5pt"><td width=196 nowrap style="width:146.65pt;border:solid windowtext 1.0pt;border-top:none;padding:0in 5.4pt 0in 5.4pt;height:22.5pt"><p class=MsoNormal valign=top  align=center style="text-align:center">'.date('d/m/Y', strtotime($data['dateworked'])).'</p></td><td width=190 nowrap colspan=2 style="width:140pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:22.5pt"><p  valign=top  class=MsoNormal>'.$data['reason'].'</p></td><td width=298 nowrap valign=top style="width:223.15pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:22.5pt"><p valign=top class=MsoNormal>'.$data['remarks'].'</p></td></tr>';
									}
									$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.83.18/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.83.18/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
									$this->mail->addAddress($adb->email, $adb->fullname);
									$this->mail->Subject = "Online Approval System -> New Dayoff Submission";
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
									$Dayoffhistory = new Dayoffhistory();
									$Dayoffhistory->date = date("Y-m-d h:i:s");
									$Dayoffhistory->fullname = $Employee->fullname;
									$Dayoffhistory->dayoff_id = $id;
									$Dayoffhistory->approvaltype = "Originator";
									$Dayoffhistory->actiontype = 2;
									$Dayoffhistory->save();
								}else{
									$Dayoffhistory = new Dayoffhistory();
									$Dayoffhistory->date = date("Y-m-d h:i:s");
									$Dayoffhistory->fullname = $Employee->fullname;
									$Dayoffhistory->dayoff_id = $id;
									$Dayoffhistory->approvaltype = "Originator";
									$Dayoffhistory->actiontype = 1;
									$Dayoffhistory->save();
								}
								$logger = new Datalogger("Dayoff","update",json_encode($olddata),json_encode($data));
								$logger->SaveData();
								//echo json_encode($Dayoff);
							}
						}catch (Exception $e){
							$err = new Errorlog();
							$err->errortype = "DeleteDayoff";
							$err->errordate = date("Y-m-d h:i:s");
							$err->errormessage = $e->getMessage();
							$err->user = $this->currentUser->username;
							$err->ip = $this->ip;
							$err->save();
							$data = array("status"=>"error","message"=>$e->getMessage());
						}
						break;
					default:
						$Dayoff = Dayoff::all();
						foreach ($Dayoff as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Dayoff, JSON_NUMERIC_CHECK);
						break;
				}
			}
			// else{
				// $result= array("status"=>"autherror","message"=>"Authentication error or expired, please refresh and re login application");
				// echo json_encode($result);
			// }
		}
	}
	function DayoffByEmp(){	
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
							$Dayoff = Dayoff::find('all', array('conditions' => array("createdby=?",$Employee->id),'include' => array('employee')));
							foreach ($Dayoff as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Dayoff, JSON_NUMERIC_CHECK);
						}else{
							echo json_encode(array());
						}
						break;
					case 'find':
						$query=$this->post['query'];					
						if(isset($query['status'])){
							switch ($query['status']){
								case 'pending':
									//$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));

									//$Dayoff = Dayoff::find('all', array('conditions' => array("employee_id=? and RequestStatus<3 ",$Employee->id),'include' => array('employee')));
									$Dayoff = Dayoff::find('all', array('conditions' => array("employee_id=? and RequestStatus<3 and id<>?",$query['username'],$query['id']),'include' => array('employee')));
									foreach ($Dayoff as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Dayoff));
									break;
								case 'dashboard':
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));

									$Dayoff = Dayoff::find('all', array('conditions' => array("employee_id=? and RequestStatus<3 ",$Employee->id),'include' => array('employee')));
									// $Dayoff = Dayoff::find('all', array('conditions' => array("employee_id=? and RequestStatus<3 and id<>?",$query['username'],$query['id']),'include' => array('employee')));
									foreach ($Dayoff as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Dayoff));
								break;
								default:
									//$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->post['username'])));
									$Dayoff = Dayoff::find('all', array('conditions' => array("employee_id=? and RequestStatus>0 and RequestStatus<3 and id<>?",$query['username'],$query['id']),'include' => array('employee')));
									foreach ($Dayoff as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Dayoff));
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
							$Dayoff = Dayoff::find('all', array('conditions' => array("createdby=?",$Employee->id),'include' => array('employee')));
							foreach ($Dayoff as &$result) {
								$fullname=$result->employee->fullname;
								$result = $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Dayoff, JSON_NUMERIC_CHECK);
						}else{
							echo json_encode(array());
						}
						break;
				}
			}
			// else{
				// $result= array("status"=>"autherror","message"=>"Authentication error or expired, please refresh and re login application");
				// echo json_encode($result);
			// }
		}
	}
}