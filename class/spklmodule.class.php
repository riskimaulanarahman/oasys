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
				case 'apispkltmsapp':
					$this->spklTMSApproval();
					break;
				case 'apispklhist':
					$this->spklHistory();
					break;
				case 'apispkltmshist':
					$this->spklTMSHistory();
					break;
				case 'apispklpdf':	
					$id = $this->get['id'];
					$this->generatePDF($id);
					break;
				case 'apispkltmspdf':	
					$id = $this->get['id'];
					$this->generateTMSPDF($id);
					break;
				case 'apispkltms':
					$this->SPKLTms();
					break;
				case 'apitestxl2pdf':
					$this->testExcel();
					break;
				default:
					break;
			}
		}
	}
	function testExcel(){
		$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('doc/template/template.xlsx');
		$worksheet = $spreadsheet->getActiveSheet();
		$pageSetup = $worksheet->getPageSetup();
		$margin = $worksheet->getPageMargins();
		$pageSetup->setFitToPage(false);
		$pageSetup->setScale(150);
		$pageSetup->setFitToWidth(0);
		$pageSetup->setFitToHeight(0);
		$margin->setTop(0.75);
		$margin->setRight(0.25);
		$margin->setLeft(0.5);
		$margin->setBottom(0.25);
		//$pageSetup->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
		$pageSetup->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
		
		for ($a=6;$a<15;$a++){
			$worksheet->insertNewRowBefore($a+1, 1);
			$cellValues = $worksheet->rangeToArray('C6:H6');
			$worksheet->fromArray($cellValues, null, 'C'.$a);
			$worksheet->getCell('D'.$a)->setValue('Nama '.($a-5));
			$worksheet->getCell('E'.$a)->setValue('Alamat '.($a-5));
		}
		$worksheet->getRowDimension('3')->setRowHeight(1);
		$pageSetup->setPrintArea('D1:M12');
		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Mpdf');
		$writer->save('doc/test.pdf');
	}
	function generatePDF($doid){
		$Spkl = Spkl::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));
		$Spkldetail=Spkldetail::find('all',array('conditions'=>array("spkl_id=?",$doid),'include'=>array('spkl'=>array('employee'=>array('company','department','designation','grade','location')))));
		$pdfContent ="<style>  td { padding:3px; font-size:8pt;} th { padding:3px; font-size:8pt;font-weight:normal} small {font-size:7pt;}</style>
					<table border=0 cellpadding=2 cellspacing=0 style='width:100%; margin:2px;margin-left:10px;'><tr><td style='border:0.5px solid #212; width:700px;margin-left:20px;padding-left:15px;'><h5 style='width:100%;text-align:center'><b><u>SURAT PERINTAH KERJA LEMBUR (SPKL)</u></b>";
		$pdfContent .="<br><i>Overtime Instruction & Approval Form</i></h5>";
		$hari = array ( 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu');						
		$pdfContent .= "Dengan ini diperintahkan agar melaksanakan kerja lembur kepada/ <i>Herewith instructed to work overtime to</i>;
						<table border=0 cellspacing=0 cellpadding=1>
						<tr><td>Business Unit </td><td>:</td><td>".$Spkl->employee->companycode."</td><td width='50'></td><td>Hari</td><td>:</td><td>".$hari[(date("N",strtotime($Spkl->datework))-1)]."</td></tr>
						<tr><td>Section </td><td>:</td><td></td><td width='50'></td><td>Tanggal</td><td>:</td><td>".date("d/m/Y",strtotime($Spkl->datework))."</td></tr>
						<tr><td>Department  </td><td>:</td><td>".$Spkl->employee->department->departmentname."</td><td width='50'></td><td></td><td></td><td></td></tr>
						</table>";
		$pdfContent .='	<table border=1 cellspacing=0 cellpadding=2 width=650>
						<tr  style="height:12pt">
						<th rowspan="2" align="center">No</th>
						<th rowspan="2" align="center">Nama</th>
						<th rowspan="2" align="center">No. SAP</th>
						<th rowspan="2" align="center">Posisi</th>
						<th colspan="2" align="center">Perkiraan Lama Bekerja</th>
						
						<th rowspan="2" align="center">Pekerjaan yang harus diselesaikan</th>
						<th rowspan="2" align="center">Remarks</th>
						</tr>
						<tr><th align="center">Jam <br>Normal <br><small>( Jam )</small></th>
						<th align="center">Jam <br>Lembur <br><small>( Jam )</small></th></tr>
						';
		$no=1;
		foreach ($Spkldetail as $data){			
			$pdfContent .='<tr style="height:12pt">
						<td> '.$no.'</td>
						<td> '.$data->employee->fullname.'</td>
						<td> '.$data->employee->sapid.'</td>
						<td> '.wordwrap($data->employee->designation->designationname, 20, "<br>").'</td>
						<td> '.$data->estimatenormalhours.'</td>
						<td> '.$data->estimateovertimehours.'</td>
						<td> '.wordwrap($data->target, 40, "<br>").'</td>';
					if ($data->isapproved){
						$pdfContent .='<td> </td>';
					}	else{
						$Reject = Employee::find('first', array('conditions' => array("id=?", $data->rejectspklby)));
						$pdfContent .='<td>'.wordwrap(' Rejected by '.$Reject->fullname,20,"<br>").'</td>';
					}
			$pdfContent .='</tr>';
			$no++;		
		}
		$pdfContent .= "</table>
						<small><b>Catatan : </b>
						<br>- SPKL wajib dikeluarkan oleh Askep/level lebih tinggi sebelum pekerjaan lembur dijalankan.
						<br>- Satu formulir SPKL mewakili rencana kerja lembur di 1 (satu) hari/tanggal. 
						<br>- SPKL wajib dilampirkan pada daftar hadir (timesheet) dan diserahkan kepada departemen SDM dalam waktu 1X24 jam,atau pada hari kerja berikutnya.</small>";
				
		$joinx   = "LEFT JOIN tbl_approver ON (tbl_spklapproval.approver_id = tbl_approver.id) ";					
		$Spklapproval = Spklapproval::find('all',array('joins'=>$joinx,'conditions' => array("spkl_id=?",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee','approvaltype'))));
		foreach ($Spklapproval as $data){
			if(($data->approver->approvaltype->id==20) || ($data->approver->employee_id==$Spkl->depthead)){
				$deptheadname = $data->approver->employee->fullname;
				$datedepthead = $data->approvaldate;
			}
			if($data->approver->approvaltype->id==21) {
				$hrname = $data->approver->employee->fullname;
				$hrdate = $data->approvaldate;
			}
			if($data->approver->approvaltype->id==22) {
				$buheadname = $data->approver->employee->fullname;
				$buheaddate =$data->approvaldate;
			}
		}		
		$pdfContent .= "<br><br><table border=0 cellspacing=4 cellpadding=3>
						<tr><td align='center'>Diperintahkan Oleh,<br>Askep</td><td width='50'></td><td align='center'>Disetujui Oleh,<br>Dept. Head / Sector Manager</td><td width='50'></td><td align='center'>Diperiksa Oleh,<br>HR BU/HO</td>";
					if(($buheadname!="")){
						$pdfContent .= "<td width='50'></td><td align='center'>Disetujui Oleh,<br>BU Head</td>";
					}
		$pdfContent .= "</tr>
						";
		
		$pdfContent .= '<tr><td align="center" style="padding:2pt 2.4pt 0in 2.4pt;"><img src="images/approved.png" style="height:25pt" alt="Approved from System"></td>
					<td width="50"></td>
					<td align="center" style="padding:2pt 2.4pt 0in 2.4pt;">'.(($deptheadname=="")?"":'<img src="images/approved.png" style="height:25pt" alt="Approved from System">').'</td>
					<td width="50"></td>
					<td align="center" style="padding:2pt 2.4pt 0in 2.4pt;">'.(($hrname=="")?"":'<img src="images/approved.png" style="height:25pt" alt="Approved from System">').'</td>';
					if(($buheadname!="")){
						$pdfContent .= '<td width="50"></td>
						<td align="center" style="padding:2pt 2.4pt 0in 2.4pt;">'.(($buheadname=="")?"":'<img src="images/approved.png" style="height:25pt" alt="Approved from System">').'</td>';
					}
					$pdfContent .= '</tr>';
		$pdfContent .= '<tr><td align="center" style="padding:2pt 2.4pt 0in 2.4pt;">'.$Spkl->employee->fullname.'<br><small>'.date("d/m/Y",strtotime($Spkl->createddate)).'</small></td>
					<td width="50"></td>
					<td align="center" style="padding:2pt 2.4pt 0in 2.4pt;">'.$deptheadname.'<br><small>'.(($deptheadname=="")?"":date("d/m/Y",strtotime($datedepthead))).'</small></td>
					<td width="50"></td>
					<td align="center" style="padding:2pt 2.4pt 0in 2.4pt;">'.$hrname.'<br><small>'.(($hrname=="")?"":date("d/m/Y",strtotime($hrdate))).'</small></td>';
					if(($buheadname!="")){
						$pdfContent .= '<td width="50"></td>
						<td align="center" style="padding:2pt 2.4pt 0in 2.4pt;">'.$buheadname.'<br><small>'.(($buheadname=="")?"":date("d/m/Y",strtotime($buheaddate))).'</small></td>';
					}
					$pdfContent .= '</tr>';
		$pdfContent .= "</table></td></tr></table>";
		
		try {
			$html2pdf = new Html2Pdf('P', 'A4', 'fr');
			$html2pdf->writeHTML($pdfContent);
			ob_clean();
			$fileName ='doc'.DS.'spkl'.DS.'pdf'.DS.''.$Spkl->employee->sapid.'_'.date("YmdHis").'.pdf';
			$fileName = str_replace("/","",$fileName);
			$filePath = SITE_PATH.DS.$fileName;
			$html2pdf->output($filePath, 'F');
			
			$Spkl->approveddoc=str_replace("\\","/",$fileName);
			$Spkl->save();
			return $filePath;
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
	
	function generateTMSPDF($doid){
		$Spkl = Spkl::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));
		$Spkldetail=Spkldetail::find('all',array('conditions'=>array("spkl_id=?",$doid),'include'=>array('spkl'=>array('employee'=>array('company','department','designation','grade','location')))));

		$pdfContent ="<style>  td { padding:3px; font-size:8pt;} th { padding:3px; font-size:8pt;font-weight:normal} small {font-size:7pt;}</style>
					<table border=0 cellpadding=2 cellspacing=0 style='width:100%; margin:2px;margin-left:10px;'><tr><td style='border:0.5px solid #212; width:700px;margin-left:20px;padding-left:15px;'><h5 style='width:100%;text-align:center'><b><u>SURAT PERINTAH KERJA LEMBUR (SPKL)</u></b>";
		$pdfContent .="<br><i>Overtime Instruction & Approval Form</i></h5>";
		$hari = array ( 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu');						
		$pdfContent .= "Dengan ini diperintahkan agar melaksanakan kerja lembur kepada/ <i>Herewith instructed to work overtime to</i>;
						<table border=0 cellspacing=0 cellpadding=1>
						<tr><td>Business Unit </td><td>:</td><td>".$Spkl->employee->companycode."</td><td width='50'></td><td>Hari</td><td>:</td><td>".$hari[(date("N",strtotime($Spkl->datework))-1)]."</td></tr>
						<tr><td>Section </td><td>:</td><td></td><td width='50'></td><td>Tanggal</td><td>:</td><td>".date("d/m/Y",strtotime($Spkl->datework))."</td></tr>
						<tr><td>Department  </td><td>:</td><td>".$Spkl->employee->department->departmentname."</td><td width='50'></td><td></td><td></td><td></td></tr>
						</table>";
		$pdfContent .='	<table border=1 cellspacing=0 cellpadding=2 width=650>
						<tr  style="height:12pt">
						<th rowspan="2" align="center">No</th>
						<th rowspan="2" align="center">Nama</th>
						<th rowspan="2" align="center">No. SAP</th>
						<th rowspan="2" align="center">Posisi</th>
						<th colspan="2" align="center">Perkiraan Lama Bekerja</th>
						
						<th rowspan="2" align="center">Pekerjaan yang harus diselesaikan</th>
						<th rowspan="2" align="center">Remarks</th>
						</tr>
						<tr><th align="center">Jam <br>Normal <br><small>( Jam )</small></th>
						<th align="center">Jam <br>Lembur <br><small>( Jam )</small></th></tr>
						';
		$no=1;
		foreach ($Spkldetail as $data){			
			$pdfContent .='<tr style="height:12pt">
						<td> '.$no.'</td>
						<td> '.$data->employee->fullname.'</td>
						<td> '.$data->employee->sapid.'</td>
						<td> '.wordwrap($data->employee->designation->designationname, 20, "<br>").'</td>
						<td> '.$data->estimatenormalhours.'</td>
						<td> '.$data->estimateovertimehours.'</td>
						<td> '.wordwrap($data->target, 40, "<br>").'</td>';
					if ($data->isapproved){
						$pdfContent .='<td> </td>';
					}	else{
						$Reject = Employee::find('first', array('conditions' => array("id=?", $data->rejectspklby)));
						$pdfContent .='<td>'.wordwrap(' Rejected by '.$Reject->fullname,20,"<br>").'</td>';
					}
			$pdfContent .='</tr>';
			$no++;		
		}
		$pdfContent .= "</table>
						<small><b>Catatan : </b>
						<br>- SPKL wajib dikeluarkan oleh Askep/level lebih tinggi sebelum pekerjaan lembur dijalankan.
						<br>- Satu formulir SPKL mewakili rencana kerja lembur di 1 (satu) hari/tanggal. 
						<br>- SPKL wajib dilampirkan pada daftar hadir (timesheet) dan diserahkan kepada departemen SDM dalam waktu 1X24 jam,atau pada hari kerja berikutnya.</small>";
		
		$joinx   = "LEFT JOIN tbl_approver ON (tbl_spklapproval.approver_id = tbl_approver.id) ";					
		$Spklapproval = Spklapproval::find('all',array('joins'=>$joinx,'conditions' => array("spkl_id=?",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee','approvaltype'))));							
		$pdfContent .= "<table border=0 cellspacing=4 cellpadding=3>
						<tr><td align='center'>Diperintahkan Oleh, <br>Askep</td><td width='50'></td><td align='center'>Disetujui Oleh,<br>Dept. Head / Sector Manager</td><td width='50'></td><td align='center'>Diperiksa Oleh,<br>HR BU/HO</td>";
					
		foreach ($Spklapproval as $data){
			if(($data->approver->approvaltype->id==20) || ($data->approver->employee_id==$Spkl->depthead)){
				$deptheadname = $data->approver->employee->fullname;
				$datedepthead =$data->approvaldate;
			}
			if($data->approver->approvaltype->id==21) {
				$hrname = $data->approver->employee->fullname;
				$hrdate = $data->approvaldate;
			}
			if($data->approver->approvaltype->id==22) {
				$buheadname = $data->approver->employee->fullname;
				$buheaddate = $data->approvaldate;
			}
		}
		if(($buheadname!="")){		
						$pdfContent .= "<td width='50'></td><td align='center'>Disetujui Oleh,<br>BU Head</td>";
					}
						$pdfContent .= "</tr>";
		$pdfContent .= '<tr><td align="center" style="padding:2pt 2.4pt 0in 2.4pt;"><img src="images/approved.png" style="height:25pt" alt="Approved from System"></td>
					<td width="50"></td>
					<td align="center" style="padding:2pt 2.4pt 0in 2.4pt;">'.(($deptheadname=="")?"":'<img src="images/approved.png" style="height:25pt" alt="Approved from System">').'</td>
					<td width="50"></td>
					<td align="center" style="padding:2pt 2.4pt 0in 2.4pt;">'.(($hrname=="")?"":'<img src="images/approved.png" style="height:25pt" alt="Approved from System">').'</td>';
				if(($buheadname!="")){	
					$pdfContent .= '<td width="50"></td>
					<td align="center" style="padding:2pt 2.4pt 0in 2.4pt;">'.(($buheadname=="")?"":'<img src="images/approved.png" style="height:25pt" alt="Approved from System">').'</td>';
				}
					$pdfContent .= '</tr>';
		$pdfContent .= '<tr><td align="center" style="padding:2pt 2.4pt 0in 2.4pt;">'.$Spkl->employee->fullname.'<br><small>'.date("d/m/Y",strtotime($Spkl->createddate)).'</small></td>
					<td width="50"></td>
					<td align="center" style="padding:2pt 2.4pt 0in 2.4pt;">'.$deptheadname.'<br><small>'.(($deptheadname=="")?"":date("d/m/Y",strtotime($datedepthead))).'</small></td>
					<td width="50"></td>
					<td align="center" style="padding:2pt 2.4pt 0in 2.4pt;">'.$hrname.'<br><small>'.(($hrname=="")?"":date("d/m/Y",strtotime($hrdate))).'</small></td>';
				if(($buheadname!="")){		
					$pdfContent .= '<td width="50"></td>
					<td align="center" style="padding:2pt 2.4pt 0in 2.4pt;">'.$buheadname.'<br><small>'.(($buheadname=="")?"":date("d/m/Y",strtotime($buheaddate))).'</small></td>';
				}
					$pdfContent .= '</tr>';
		$pdfContent .= "</table></td></tr></table>";
		
		$tmsContent ="<table border=0 cellpadding=2 cellspacing=0 style='width:100%; margin:2px;margin-left:10px;'><tr><td  style='border:0.5px solid #212; width:700px;margin-left:20px;padding-left:15px;'><h5 style='width:100%;text-align:center'><b><u>DAFTAR HADIR KERJA LEMBUR KARYAWAN</u></b>";
		$tmsContent .="<br><i>Overtime Timesheet & Approval Form</i></h5>";
		
		$tmsContent .= "<table border=0 cellspacing=0 cellpadding=3>
						<tr><td>Hari</td><td>:</td><td>".$hari[(date("N",strtotime($Spkl->datework))-1)]."</td></tr>
						<tr><td>Tanggal</td><td>:</td><td>".date("d/m/Y",strtotime($Spkl->datework))."</td></tr>
						</table>";
						
		$tmsContent .='<table border=1 cellspacing=0 cellpadding=2 width=650>
						<tr style="height:12pt;">
							<th rowspan="2" align="center">No</th>
							<th rowspan="2" align="center">Nama</th>
							<th rowspan="2" align="center">No. SAP</th>
							<th rowspan="2" align="center">Posisi</th>
							<th colspan="5" align="center">Aktual Lama Bekerja</th>
							<th rowspan="2" align="center">Achievement/Remarks </th>
						</tr>
						<tr>
							<th align="center">Jam <br>Mulai</th>
							<th align="center">Jam <br>Keluar</th>
							<th align="center">Total <br><small>( Jam )</small></th>
							<th align="center">Normal <br><small>( Jam )</small></th>
							<th align="center">Lembur <br><small>( Jam )</small></th>
						</tr>
						';
		
		$no=1;
		foreach ($Spkldetail as $data){
			if ($data->isotapproved){
				$tmsContent .='<tr style="height:12pt;">
							<td> '.$no.'</td>
							<td> '.$data->employee->fullname.'</td>
							<td> '.$data->employee->sapid.'</td>
							<td> '.wordwrap($data->employee->designation->designationname, 20, "<br>").'</td>
							<td> '.date("H:i",strtotime($data->actualstartwork)).'</td>
							<td> '.date("H:i",strtotime($data->actualendwork)).'</td>
							<td> '.$data->actualtotalhours.'</td>
							<td> '.$data->actualnormalhours.'</td>
							<td> '.$data->actualovertimehours.'</td>
							<td> '.wordwrap($data->descriptionofwork, 40, "<br>").'</td>
				</tr>';
				$no++;
			}
		}
		$tmsContent .= "</table>
						<small><b>Note : </b>
						<br>- 1 formulir Daftar Hadir mewakili 1 hari/tanggal pelaksanaan kerja lembur. 
						<br>- Daftar Hadir beserta SPKL wajib diserahkan kepada departemen SDM dalam waktu 1X24 jam, atau pada hari kerja berikutnya.</small>";
		
		$joinx   = "LEFT JOIN tbl_approver ON (tbl_spklotapproval.approver_id = tbl_approver.id) ";					
		
		$Spkltmsapproval = Spkltmsapproval::find('all',array('joins'=>$joinx,'conditions' => array("spkl_id=?",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee','approvaltype'))));	
		foreach ($Spkltmsapproval as $data){
			if(($data->approver->approvaltype->id==20) || ($data->approver->employee_id==$Spkl->depthead)){
				$deptheadname = $data->approver->employee->fullname;
				$datedepthead = $data->approvaldate;
			}
			if($data->approver->approvaltype->id==21) {
				$hrname = $data->approver->employee->fullname;
				$hrdate = $data->approvaldate;
			}
		}		
		$tmsContent .= "<table border=0 cellspacing=4 cellpadding=3>
						<tr><td align='center'>Dibuat Oleh,<br>Askep</td><td width='50'></td><td align='center'>Disetujui Oleh,<br>Dept. Head / Sector Manager</td>";
		if ($Spkl->isexceedplan && $hrname!==""){
			$tmsContent .= "<td width='50'></td><td align='center'>Diperiksa Oleh,<br>HR BU/HO</td>";
						
		}
		$tmsContent .= "</tr>";
		
		$tmsContent .= '<tr><td align="center" style="padding:2pt 2.4pt 0in 2.4pt;"><img src="images/approved.png" style="height:25pt" alt="Approved from System"></td>
					<td width="50"></td>
					<td align="center" style="padding:2pt 2.4pt 0in 2.4pt;">'.(($deptheadname=="")?"":'<img src="images/approved.png" style="height:25pt" alt="Approved from System">').'</td>';
		if ($Spkl->isexceedplan && $hrname!==""){
			$tmsContent .= '<td width="50"></td><td align="center" style="padding:2pt 2.4pt 0in 2.4pt;">'.(($hrname=="")?"":'<img src="images/approved.png" style="height:25pt" alt="Approved from System">').'</td>';
						
		}
					$tmsContent .= '</tr>';
		$tmsContent .= '<tr><td align="center" style="padding:2pt 2.4pt 0in 2.4pt;">'.$Spkl->employee->fullname.'<br><small>'.date("d/m/Y",strtotime($Spkl->createddate)).'</small></td>
					<td width="50"></td>
					<td align="center" style="padding:2pt 2.4pt 0in 2.4pt;">'.$deptheadname.'<br><small>'.(($deptheadname=="")?"":date("d/m/Y",strtotime($datedepthead))).'</small></td>';
		if ($Spkl->isexceedplan && $hrname!==""){
			$tmsContent .= '<td width="50"></td><td align="center" style="padding:2pt 2.4pt 0in 2.4pt;">'.$hrname.'<br><small>'.(($hrname=="")?"":date("d/m/Y",strtotime($hrdate))).'</small></td>';
						
		}
					$tmsContent .= '</tr>';
		$tmsContent .= "</table></td></tr></table>";
		
		$pdfContent .=$tmsContent;
		//$pdfContent .=$tmsContent;
		try {
			$html2pdf = new Html2Pdf('P', 'A4', 'fr');
			$html2pdf->writeHTML($pdfContent);
			ob_clean();
			$fileName ='doc'.DS.'spkl'.DS.'pdf'.DS.'TMS'.$Spkl->employee->sapid.'_'.date("YmdHis").'.pdf';
			$fileName = str_replace("/","",$fileName);
			$filePath = SITE_PATH.DS.$fileName;
			$html2pdf->output($filePath, 'F');
			$Spkl->approvedtmsdoc=str_replace("\\","/",$fileName);
			$Spkl->save();
			return $filePath;
		} catch (Html2PdfException $e) {
			$html2pdf->clean();
			$formatter = new ExceptionFormatter($e);
			$err = new Errorlog();
			$err->errortype = "SPKLTMSPDFGenerator";
			$err->errordate = date("Y-m-d h:i:s");
			$err->errormessage = $formatter->getHtmlMessage();
			$err->user = $this->currentUser->username;
			$err->ip = $this->ip;
			$err->save();
			echo $formatter->getHtmlMessage();
		}
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
	function spklTMSHistory(){
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
							$Spkltmshistory = Spkltmshistory::find('all', array('conditions' => array("spkl_id=?",$id),'include' => array('spkl')));
							foreach ($Spkltmshistory as &$result) {
								$result		= $result->to_array();
							}
							echo json_encode($Spkltmshistory, JSON_NUMERIC_CHECK);
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
							if($dx->approver->isfinal==1 || ($Spkl->ismorethan2hours==0 && $dx->approver->approvaltype_id=='21')){
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
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join = "LEFT JOIN vwspklreport v on tbl_spkl.id=v.id LEFT JOIN tbl_employee ON (tbl_spkl.employee_id = tbl_employee.id) ";
							$sel = 'tbl_spkl.*, v.spklstatus,v.otstatus,v.personholding ';
							$Spkl = Spkl::find('all',array('joins'=>$join,'select'=>$sel,'include' => array('employee')));
							
							if($Employee->location->sapcode=='0200' || $this->currentUser->isadmin){
								$Spkl = Spkl::find('all',array('joins'=>$join,'select'=>$sel,'include' => array('employee'=>array('company','department'))));
							}else{
								$Spkl = Spkl::find('all',array('joins'=>$join,'select'=>$sel,'conditions' => array('tbl_spkl.RequestStatus=3 and tbl_employee.company_id=?',$Employee->company_id ),'include' => array('employee'=>array('company','department'))));
							}
							
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
						$Spkldetail=Spkldetail::find('all',array('conditions'=>array("spkl_id=?",$doid),'include'=>array('spkl'=>array('employee'=>array('company','department','designation','grade')))));
						$allcheck = 0;
						foreach ($Spkldetail as $result) {
							if(is_null($result->isapproved)){
								$allcheck+=1;
							}
						}
						if (($data['approvalstatus']=='1') || ($data['approvalstatus']=='3')){
							$allcheck=0;
						}
						if($allcheck>0){
							$result= array("status"=>"error","message"=>"Need to do approval/reject on each detail Overtime request");
							echo json_encode($result);
						}else{
							unset($data['id']);
							unset($data['depthead']);
							unset($data['fullname']);
							unset($data['department']);
							unset($data['datework']);
							unset($data['approveddoc']);
							unset($data['isexceedplan']);
							unset($data['approvalstep']);
							unset($data['ismorethan2hours']);
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
								if ($Spkl->datework !== null){
									foreach ($Spkldetail as $row){
										if ($row->isapproved){
											$time = new DateTime($Spkl->datework);
											$time->add(new DateInterval('PT8H'));
											$start = $time->format('Y-m-d H:i');
											$row->actualstartwork = $start;
											$time = new DateTime($start);
											$time->add(new DateInterval('PT' . ($row->estimatenormalhours + $row->estimateovertimehours+1). 'H'));
											$end = $time->format('Y-m-d H:i');
											$row->actualendwork = $end;
											
											$row->actualtotalhours = $row->estimatenormalhours + $row->estimateovertimehours;
											$row->actualnormalhours = $row->estimatenormalhours;
											$row->actualovertimehours = $row->estimateovertimehours;
											
										}else {
											$row->actualstartwork = null;
											$row->actualendwork= null;
											$row->actualtotalhours = 0;
											$row->actualnormalhours = 0;
											$row->actualovertimehours = 0;
											$Reject = Employee::find('first', array('conditions' => array("id=?", $row->rejectspklby)));
											$row->descriptionofwork = "SPKL Rejected by ".$Reject->fullname;
										}
										$row->save();	
									}
								}
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
										$Spkl->approvalstep = 0;
										$emto=$email;$emname=$Spkl->employee->fullname;
										$this->mail->Subject = "Online Approval System -> Need Rework";
										$red = 'Your SPKL/ Overtime request require some rework :';
										$Spklhistory->actiontype = 3;
										break;
									case '2':
										if ($Spklapproval->approver->isfinal == 1 || ($Spklapproval->approver->approvaltype_id=='21' && $Spkl->ismorethan2hours == 0)){
											$Spkl->requeststatus = 3;
											$Spkl->approvalstep = 0;
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
										else{
											$Spkl->requeststatus = 1;
											$Spkl->approvalstep += 1;
											$emto=$adb->email;$emname=$adb->fullname;
											$this->mail->Subject = "Online Approval System -> New SPKL/Overtime Submission";
											$red = 'New SPKL/Overtime request awaiting for your approval:';
										}
										$Spklhistory->actiontype = 4;							
										break;
									case '3':
										$Spkl->requeststatus = 4;
										$Spkl->approvalstep = 4;
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
									$filePath= $this->generatePDF($doid);
									$this->mail->addAttachment($filePath);
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
						}
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
	function spklTMSApproval(){
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
							$join   = "LEFT JOIN tbl_approver ON (tbl_spklotapproval.approver_id = tbl_approver.id) ";
							$Spkltmsapproval = Spkltmsapproval::find('all', array('joins'=>$join,'conditions' => array("spkl_id=?",$id),'include' => array('approver'=>array('approvaltype')),"order"=>"tbl_approver.sequence"));
							foreach ($Spkltmsapproval as &$result) {
								$approvaltype = $result->approver->approvaltype_id;
								$result		= $result->to_array();
								$result['approvaltype']=$approvaltype;
							}
							echo json_encode($Spkltmsapproval, JSON_NUMERIC_CHECK);
						}else{
							$Spkltmsapproval = new Spkltmsapproval();
							echo json_encode($Spkltmsapproval);
						}
						break;
					case 'find':
						$query=$this->post['query'];		
						if(isset($query['status'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join   = "LEFT JOIN tbl_approver ON (tbl_spklotapproval.approver_id = tbl_approver.id) ";
							$dx = Spkltmsapproval::find('first', array('joins'=>$join,'conditions' => array("spkl_id=? and tbl_approver.employee_id = ?",$query['spkl_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
							$Spkl = Spkl::find($query['spkl_id']);
							if($dx->approver->isfinal==1){
								$data=array("jml"=>1);
							}else{
								if($Spkl->isexceedplan && $dx->approver->approvaltype_id=='20'){
									$join   = "LEFT JOIN tbl_approver ON (tbl_spklotapproval.approver_id = tbl_approver.id) ";
									$Spkltmsapproval = Spkltmsapproval::find('all', array('joins'=>$join,'conditions' => array("spkl_id=? and ApprovalStatus<=1 and not tbl_approver.employee_id=?",$query['spkl_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
									foreach ($Spkltmsapproval as &$result) {
										$fullname	= $result->approver->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Spkltmsapproval));
								} else {
									$data=array("jml"=>1);
								}
								
							}						
						} else if(isset($query['pending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
							$Spkl = Spkl::find('all', array('conditions' => array("tmsreqstatus =1"),'include' => array('employee')));
							foreach ($Spkl as $result) {
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_spklotapproval.approver_id = tbl_approver.id) ";					
								$Spkltmsapproval = Spkltmsapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and spkl_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								if($Spkltmsapproval->approver->employee_id==$emp_id){
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
							$Spkl = Spkl::find('all', array('conditions' => array("tmsreqstatus =1"),'include' => array('employee')));
							$jml=0;
							foreach ($Spkl as $result) {
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_spklotapproval.approver_id = tbl_approver.id) ";					
								$Spkltmsapproval = Spkltmsapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and spkl_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								if($Spkltmsapproval->approver->employee_id==$emp_id){
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
						$Spkltmsapproval = Spkltmsapproval::create($data);
						$logger = new Datalogger("Spkltmsapproval","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':
						$id = $this->post['id'];
						$Spkltmsapproval = Spkltmsapproval::find($id);
						$data=$Spkltmsapproval->to_array();
						$Spkltmsapproval->delete();
						$logger = new Datalogger("Spkltmsapproval","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Spkltmsapproval);
						break;
					case 'update':
						$doid = $this->post['id'];
						$data = $this->post['data'];
						$mode= $data['mode'];
						$Spkldetail=Spkldetail::find('all',array('conditions'=>array("spkl_id=?",$doid),'include'=>array('spkl'=>array('employee'=>array('company','department','designation','grade')))));
						$allcheck = 0;
						foreach ($Spkldetail as $result) {
							if($result->isapproved=='1' and is_null($result->isotapproved)){
								$allcheck+=1;
							}
						}
						if (($data['approvalstatus']=='1') || ($data['approvalstatus']=='3')){
							$allcheck=0;
						}
						if($allcheck>0){
							$result= array("status"=>"error","message"=>"Need to do approval/reject on each detail Employee Overtime / timesheet ");
							echo json_encode($result);
						}else{
							unset($data['id']);
							unset($data['datework']);
							unset($data['isexceedplan']);
							unset($data['approvalstep']);
							unset($data['ismorethan2hours']);
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join   = "LEFT JOIN tbl_approver ON (tbl_spklotapproval.approver_id = tbl_approver.id) ";
							if (isset($data['mode'])){
								$Spkltmsapproval = Spkltmsapproval::find('first', array('joins'=>$join,'conditions' => array("spkl_id=? and tbl_approver.employee_id=?",$doid,$Employee->id),'include' => array('approver'=>array('employee','approvaltype'))));
								unset($data['mode']);
							}else{
								$Spkltmsapproval = Spkltmsapproval::find($this->post['id'],array('include' => array('approver'=>array('employee','approvaltype'))));
							}
							$olddata = $Spkltmsapproval->to_array();
							foreach($data as $key=>$val){
								$val=($val=='false')?false:(($val=='true')?true:$val);
								$Spkltmsapproval->$key=$val;
							}
							$Spkltmsapproval->save();
							$logger = new Datalogger("Spkltmsapproval","update",json_encode($olddata),json_encode($data));
							$logger->SaveData();
							if (isset($mode) && ($mode=='approve')){
								$Spkl = Spkl::find($doid,array('include'=>array('employee'=>array('company','department','designation','grade','location'))));
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_spklotapproval.approver_id = tbl_approver.id) ";					
								$nSpklapproval = Spkltmsapproval::find('first',array('joins'=>$joinx,'conditions' => array("spkl_id=? and ApprovalStatus=0",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								$username = $nSpklapproval->approver->employee->loginname;
								$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
								$Spkldetail=Spkldetail::find('all',array('conditions'=>array("spkl_id=?",$doid),'include'=>array('spkl'=>array('employee'=>array('company','department','designation','grade','location')))));
								$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Spkl->employee->loginname)));
								$email=$usr->email;
								
								$complete = false;
								$Spkltmshistory = new Spkltmshistory();
								$Spkltmshistory->date = date("Y-m-d h:i:s");
								$Spkltmshistory->fullname = $Employee->fullname;
								$Spkltmshistory->approvaltype = $Spkltmsapproval->approver->approvaltype->approvaltype;
								$Spkltmshistory->remarks = $data['remarks'];
								$Spkltmshistory->spkl_id = $doid;
								
								switch ($data['approvalstatus']){
									case '1':
										$Spkl->tmsreqstatus = 2;
										$Spkl->approvalstep = 0;
										$emto=$email;$emname=$Spkl->employee->fullname;
										$this->mail->Subject = "Online Approval System -> Need Rework";
										$red = 'Your Overtime Timesheet request require some rework :';
										$Spkltmshistory->actiontype = 3;
										break;
									case '2':
										//if ($Spkltmsapproval->approver->isfinal == 1){
										if (($Spkltmsapproval->approver->isfinal == 1) || ($Spkltmsapproval->approver->approvaltype_id==21) || ($Spkltmsapproval->approver->approvaltype_id==20 && $Spkl->isexceedplan==false)){
											$Spkl->approvalstep =0;
											$Spkl->tmsreqstatus = 3;
											$emto=$email;$emname=$Spkl->employee->fullname;
											$this->mail->Subject = "Online Approval System -> Approval Completed";
											$red = '<p>Your Overtime Timesheet request has been approved</p>
													<p><b><span lang=EN-US style=\'color:#002060\'>Note : Please <u>forward</u> this electronic approval to your respective Human Resource Department.</span></b></p>';
													//delete unnecessary approver
											$Spkltmsapproval = Spkltmsapproval::find('all', array('joins'=>$join,'conditions' => array("spkl_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
											foreach ($Spkltmsapproval as $data) {
												if($data->approvalstatus==0){
													$logger = new Datalogger("Spkltmsapproval","delete",json_encode($data->to_array()),"automatic remove unnecessary approver by system");
													$logger->SaveData();
													$data->delete();
												}
											}
											$complete =true;
										}else{
											$Spkl->tmsreqstatus = 1;
											$Spkl->approvalstep += 1;
											$emto=$adb->email;$emname=$adb->fullname;
											$this->mail->Subject = "Online Approval System -> New Overtime Timesheet Submission";
											$red = 'New Overtime Timesheet request awaiting for your approval:';
										}
										$Spkltmshistory->actiontype = 4;							
										break;
									case '3':
										$Spkl->tmsreqstatus = 4;
										$Spkl->approvalstep = 4;
										$emto=$email;$emname=$Spkl->employee->fullname;
										$Spkltmshistory->actiontype = 5;
										$this->mail->Subject = "Online Approval System -> Request Rejected";
										$red = 'Your Overtime Timesheet request has been rejected';
										$Spklapproval = Spklapproval::find('all', array('conditions' => array("spkl_id=? and approvalstatus='0'",$doid)));
										foreach ($Spklapproval as $data) {
											$data->approvalstatus=4;
											$data->save();
										}
										$Spkldetail = Spkldetail::find('all', array('conditions' => array("spkl_id=?",$doid)));
										foreach ($Spkldetail as $data) {
											$data->isotapproved=false;
											$data->save();
										}
										break;
									default:
										break;
								}
								//print_r($Spkl);
								$Spkl->save();
								$Spkltmshistory->save();
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
												<th colspan="5"><p class=MsoNormal>Actual Time Work</p></th>
												<th rowspan="2"><p class=MsoNormal>Target Work</p></th>
												<th rowspan="2"><p class=MsoNormal>Achievement/Remarks</p></th>
												</tr>
												<th><p class=MsoNormal>Start Work</p></th>
												<th><p class=MsoNormal>End Work</p></th>
												<th><p class=MsoNormal>Total <br>(hrs)</p></th>
												<th><p class=MsoNormal>Normal<br>(hrs)</p></th>
												<th><p class=MsoNormal>Overtime<br>(hrs)</p></th></tr>
												';
								$no=1;
								foreach ($Spkldetail as $data){
									$this->mailbody .='<tr style="height:22.5pt">
												<td><p class=MsoNormal> '.$no.'</p></td>
												<td><p class=MsoNormal> '.$data->employee->fullname.'</p></td>
												<td><p class=MsoNormal> '.$data->employee->sapid.'</p></td>
												<td><p class=MsoNormal> '.$data->employee->designation->designationname.'</p></td>
												<td><p class=MsoNormal> '.date("H:i",strtotime($data->actualstartwork)).'</p></td>
												<td><p class=MsoNormal> '.date("H:i",strtotime($data->actualendwork)).'</p></td>
												<td><p class=MsoNormal> '.$data->actualtotalhours.'</p></td>
												<td><p class=MsoNormal> '.$data->actualnormalhours.'</p></td>
												<td><p class=MsoNormal> '.$data->actualovertimehours.'</p></td>
												<td><p class=MsoNormal> '.$data->target.'</p></td>
												<td><p class=MsoNormal> '.$data->descriptionofwork.'</p></td>
									</tr>';
									$no++;
								}
								$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
								$this->mail->msgHTML($this->mailbody);
								if ($complete){
									$filePath = $this->generateTMSPDF($doid);
									$this->mail->addAttachment($filePath);
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
							echo json_encode($Spkltmsapproval);
						}
						break;
					default:
						$Spkltmsapproval = Spkltmsapproval::all();
						foreach ($Spkltmsapproval as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Spkltmsapproval, JSON_NUMERIC_CHECK);
						break;
				}
			}
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
								$appText = ($result->isapproved==null)?"":(($result->isapproved)?"Yes":"No");
								$usedText = ($result->isotapproved==null)?"":(($result->isotapproved)?"Yes":"No");
								$result		= $result->to_array();
								$result['isapproved'] = $appText;
								$result['isotapproved'] = $usedText;
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
						}else if (isset($query['detail'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$joinx   = "LEFT JOIN tbl_spkl as r ON (spkl_id = r.id) left join tbl_employee e on r.employee_id=e.id";	
							if(($Employee->location->sapcode=='0200') || ($this->currentUser->isadmin)){
								$Spkldetail = Spkldetail::find('all', array('joins'=>$joinx,'include'=>array('employee'=>array("department","location","company","designation","department")),'conditions' => array("isOTApproved='1' and r.TMSReqStatus='3' and r.datework between ? and ?",$query['startDate'],$query['endDate']),'order'=>"datework"));
							}else{
								$Spkldetail = Spkldetail::find('all', array('joins'=>$joinx,'include'=>array('employee'=>array("department","location","company","designation","department")),'conditions' => array("isOTApproved='1' and r.TMSReqStatus='3' and e.company_id=?  and r.datework between ? and ?",$Employee->company_id,$query['startDate'],$query['endDate']),'order'=>"datework"));
							}
							
							foreach ($Spkldetail as &$result) {
								$joine  = "LEFT JOIN tbl_employee d ON (tbl_spkl.depthead = d.id)";	
								$sel = 'tbl_spkl.*,d.fullname as DeptHead';
								$Spkl = Spkl::find('first', array('select'=>$sel,'joins'=>$joine,'include'=>array('employee'=>array("department","location","company","designation","department")),'conditions' => array("tbl_spkl.id=?",$result->spkl_id)));
								$join  = "LEFT JOIN tbl_approver ON (tbl_spklotapproval.approver_id = tbl_approver.id) ";	
								$Spkltmsapproval = Spkltmsapproval::find('first',array('joins'=>$join,'conditions'=>array("spkl_id=?",$result->spkl_id),'order'=>"tbl_approver.sequence desc",'include' => array('approver'=>array('employee'))));
								$appText = ($result->isapproved==null)?"":(($result->isapproved)?"Yes":"No");
								
								$sapid = $result->employee->sapid;
								$fullname = $result->employee->fullname;
								$location = $result->employee->location->location;
								$department = $result->employee->department->departmentname;
								$position = $result->employee->designation->designationname;
								$bu = $result->employee->companycode;
								$result		= $result->to_array();
								$result['fullapproveddate']= $Spkltmsapproval->approvaldate;
								$result['datework'] = $Spkl->datework;
								$result['reqby'] = $Spkl->employee->fullname;
								$result['sapid']= $sapid;
								$result['name']=$fullname;
								$result['location']=$location;
								$result['department']=$department;
								$result['position']=$position;
								$result['bu']=$bu;
								$result['depthead']=$Spkl->depthead;
								$result['isapproved'] = $appText;
								$result['isused'] = $usedText;
							}
							$data=$Spkldetail;
						}else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Spkldetail = Spkldetail::create($data);
						$spkl_id=$Spkldetail->spkl_id;
						$Spkl = Spkl::find($spkl_id);
						$olddata = $Spkldetail->to_array();
						if (isset($data['actualstartwork']) || isset($data['actualendwork'])){
							if(!isset($data['actualstartwork']) && ($Spkldetail->actualstartwork== null)){
								$Spkldetail->actualstartwork = $Spkl->datework;
							}
							if(!isset($data['actualendwork']) && ($Spkldetail->actualendwork== null)){
								$Spkldetail->actualendwork = $Spkl->datework;
							}
							$start= isset($data['actualstartwork'])?$data['actualstartwork']:$Spkldetail->actualstartwork;
							$date1 = new DateTime($start);
							$date2 =  isset($data['actualendwork'])?new DateTime($data['actualendwork']):new DateTime($Spkldetail->actualendwork);
							$diff = $date2->diff($date1);
							$hours = $diff->h + ($diff->days*24)+($diff->i/60);
							$hours =($hours >5)?$hours-1:$hours;
							$Spkldetail->actualtotalhours = round($hours,1);
							$Holiday = Holiday::find('all',array('conditions' => array("HolidayDate=?",date("Y-m-d", strtotime($start)))));
							if(count($Holiday)>0){
								$Spkldetail->actualnormalhours = 0;
								$Spkldetail->actualovertimehours = round($hours,1);
							}else{
								$wd =date('N',strtotime($start));
								if ($wd=='6'){
									$Spkldetail->actualnormalhours = ($hours>5)?5:round($hours,1);
									$Spkldetail->actualovertimehours = ($hours>5)?round($hours,1) - 5:0 ;
								}else if($wd=='7'){
									$Spkldetail->actualnormalhours = 0;
									$Spkldetail->actualovertimehours = round($hours,1);
								}else{
									$Spkldetail->actualnormalhours = ($hours>8)?8:round($hours,1);
									$Spkldetail->actualovertimehours = ($hours>8)?round($hours,1) - 8 :0;
								}
							}
							if($Spkldetail->isapproved==false){
								$Spkldetail->actualtotalhours=0;
								$Spkldetail->actualnormalhours=0;
								$Spkldetail->actualovertimehours =0;
							}
						}
						if (isset($data['actualovertimehours']) && (($Spkldetail->actualnormalhours+$data['actualovertimehours'])>$Spkldetail->actualtotalhours)){
							$resp = array('status'=>'error','message'=>'Total Overtime hours calculation is not valid, please recheck 
							<br>Total hours   :'.$Spkldetail->actualtotalhours.
							'<br>Normal hours :'.$Spkldetail->actualnormalhours.
							'<br>Overtime hours :'.$data['actualovertimehours'].'<br>Normal hours + overtime hours cannot > Total hours');
							echo json_encode($resp);
						}else{
							if (isset($data['isapproved'])  ){
								if ($data['isapproved']=='No'){
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
									$Spkldetail->rejectspklby=$Employee->id;
									$Spkldetail->isotapproved=false;
								}else{
									$Spkldetail->rejectspklby=null;
									$Spkldetail->isotapproved=null;
								}								
							}
							foreach($data as $key=>$val){					
								$val=($val=='No')?false:(($val=='Yes')?true:$val);
								$Spkldetail->$key=$val;
							}
							$Spkldetail->save();
							$logger = new Datalogger("Spkldetail","update",json_encode($olddata),json_encode($data));
							$logger->SaveData();
							echo json_encode($Spkldetail);
						}
						$AllDetail = Spkldetail::find("all",array('conditions'=>array("spkl_id=?",$spkl_id)));
						$isexceed = 0;
						foreach($AllDetail as $data){
							if($data->actualovertimehours>$data->estimateovertimehours){
								$isexceed++;
							}
						}
						$isMoreThan2hours = 1;
						foreach($AllDetail as $data){
							if($data->estimateovertimehours>2){
								$isMoreThan2hours++;
							}
						}
						if ($isexceed>0){
							$joins   = "LEFT JOIN tbl_approver ON (tbl_spklotapproval.approver_id = tbl_approver.id) ";					
							$Spkltmsapproval = Spkltmsapproval::find('all',array('joins'=>$joins,'conditions' => array("spkl_id=? and approvaltype_id='21'",$spkl_id)));	
							foreach ($Spkltmsapproval as &$result) {
								$result		= $result->to_array();
								$result['no']=1;
							}			
							if(count($Spkltmsapproval)==0){
								$joins   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";
								$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username),"include"=>array("location","company","department")));
								if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220")){
									$ApproverHR = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='SPKL' and tbl_approver.isactive='1' and approvaltype_id='21' and tbl_employee.location_id='1'")));
									if(count($ApproverHR)>0){
										$Spkltmsapproval = new Spkltmsapproval();
										$Spkltmsapproval->spkl_id =$spkl_id;
										$Spkltmsapproval->approver_id = $ApproverHR->id;
										$Spkltmsapproval->save();
										$logger = new Datalogger("Spkltmsapproval","add","Add HR Approval for Exceeded actual overtime hours",json_encode($Spkltmsapproval->to_array()));
										$logger->SaveData();
									}
								}else{
									$ApproverHR = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='SPKL' and tbl_approver.isactive='1' and approvaltype_id='21'  and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
									if(count($ApproverHR)>0){
										$Spkltmsapproval = new Spkltmsapproval();
										$Spkltmsapproval->spkl_id =$spkl_id;
										$Spkltmsapproval->approver_id = $ApproverHR->id;
										$Spkltmsapproval->save();
										$logger = new Datalogger("Spkltmsapproval","add","Add HR Approval for Exceeded actual overtime hours",json_encode($Spkltmsapproval->to_array()));
										$logger->SaveData();
									}
								}
							}
						} else {
							//delete unnecessary approver
							$joins   = "LEFT JOIN tbl_approver ON (tbl_spklotapproval.approver_id = tbl_approver.id) ";					
							$dx = Spkltmsapproval::find('all',array('joins'=>$joins,'conditions' => array("spkl_id=? and tbl_approver.approvaltype_id=21",$spkl_id)));	
							foreach ($dx as $result) {
								//delete same type approver
								$result->delete();
								$logger = new Datalogger("Spkltmsapproval","delete",json_encode($result->to_array()),"delete HR Approval for non exceeded actual overtime");
							}
						}
						echo "isMoreThan2hours = ".$isMoreThan2hours;
						if ($isMoreThan2hours>0){
							$joins   = "LEFT JOIN tbl_approver ON (tbl_spklapproval.approver_id = tbl_approver.id) ";					
							$Spklapproval = Spklapproval::find('all',array('joins'=>$joins,'conditions' => array("spkl_id=? and approvaltype_id='22'",$spkl_id)));	
							foreach ($Spklapproval as &$result) {
								$result		= $result->to_array();
								$result['no']=1;
							}			
							if(count($Spklapproval)==0){
								$joins   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";
								$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username),"include"=>array("location","company","department")));
								if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220") || ($Employee->company->companycode=="KPS")){
									if(($Employee->company->sapcode!="NKF") && ($Employee->company->sapcode!="RND")  && ($Employee->company->companycode!="BCL")  && ($Employee->company->companycode!="LDU")){	
										$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='SPKL' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.companycode='KPSI' and not(tbl_employee.id=?)",$Employee->id)));
										if(count($ApproverBUHead)>0){
											$Spklapproval = new Spklapproval();
											$Spklapproval->spkl_id = $Spkl->id;
											$Spklapproval->approver_id = $ApproverBUHead->id;
											$Spklapproval->save();
											$logger = new Datalogger("Spklapproval","add","Add BUHead Approval for SPKL > 2 hours",json_encode($Spklapproval->to_array()));
											$logger->SaveData();
										}else{
											$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='SPKL' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.companycode='KPSI' and not(tbl_employee.id=?)",$Employee->id)));
											if(count($ApproverBUHead)>0){
												$Spklapproval = new Spklapproval();
												$Spklapproval->spkl_id = $Spkl->id;
												$Spklapproval->approver_id = $ApproverBUHead->id;
												$Spklapproval->save();
												$logger = new Datalogger("Spklapproval","add","Add BUHead Approval for SPKL > 2 hours",json_encode($Spklapproval->to_array()));
												$logger->SaveData();
											}
										}
									}else{
										$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='SPKL' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.company_id=? and not tbl_employee.companycode='KPSI'  and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
										if(count($ApproverBUHead)>0){
											$Spklapproval = new Spklapproval();
											$Spklapproval->spkl_id = $Spkl->id;
											$Spklapproval->approver_id = $ApproverBUHead->id;
											$Spklapproval->save();
											$logger = new Datalogger("Spklapproval","add","Add BUHead Approval for SPKL > 2 hours",json_encode($Spklapproval->to_array()));
											$logger->SaveData();
										}else{
											$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='SPKL' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.companycode='KPSI' and not(tbl_employee.id=?)",$Employee->id)));
											if(count($ApproverBUHead)>0){
												$Spklapproval = new Spklapproval();
												$Spklapproval->spkl_id = $Spkl->id;
												$Spklapproval->approver_id = $ApproverBUHead->id;
												$Spklapproval->save();
												$logger = new Datalogger("Spklapproval","add","Add BUHead Approval for SPKL > 2 hours",json_encode($Spklapproval->to_array()));
												$logger->SaveData();
											}
										}
									}

								}else{
									$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='SPKL' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.company_id=? and not tbl_employee.companycode='KPSI'  and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
									if(count($ApproverBUHead)>0){
										$Spklapproval = new Spklapproval();
										$Spklapproval->spkl_id = $Spkl->id;
										$Spklapproval->approver_id = $ApproverBUHead->id;
										$Spklapproval->save();
										$logger = new Datalogger("Spklapproval","add","Add BUHead Approval for SPKL > 2 hours",json_encode($Spklapproval->to_array()));
										$logger->SaveData();
									}else{
										$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='SPKL' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.companycode='KPSI' and not(tbl_employee.id=?)",$Employee->id)));
										if(count($ApproverBUHead)>0){
											$Spklapproval = new Spklapproval();
											$Spklapproval->spkl_id = $Spkl->id;
											$Spklapproval->approver_id = $ApproverBUHead->id;
											$Spklapproval->save();
											$logger = new Datalogger("Spklapproval","add","Add BUHead Approval for SPKL > 2 hours",json_encode($Spklapproval->to_array()));
											$logger->SaveData();
										}
									}
								}
															}
						} else {
							//delete unnecessary approver
							$joins   = "LEFT JOIN tbl_approver ON (tbl_spklapproval.approver_id = tbl_approver.id) ";					
							$dx = Spklapproval::find('all',array('joins'=>$joins,'conditions' => array("spkl_id=? and tbl_approver.approvaltype_id=22",$spkl_id)));	
							foreach ($dx as $result) {
								//delete same type approver
								$result->delete();
								$logger = new Datalogger("Spklapproval","delete",json_encode($result->to_array()),"delete BUHead for SPKL <= 2hours");
							}
						}
						$Spkl->isexceedplan=($isexceed>0);
						$Spkl->ismorethan2hours=($isMoreThan2hours>0);
						$Spkl->save();
						
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
						$spkl_id=$Spkldetail->spkl_id;
						$Spkl = Spkl::find($spkl_id);
						$olddata = $Spkldetail->to_array();
						if (isset($data['actualstartwork']) || isset($data['actualendwork'])){
							if(!isset($data['actualstartwork']) && ($Spkldetail->actualstartwork== null)){
								$Spkldetail->actualstartwork = $Spkl->datework;
							}
							if(!isset($data['actualendwork']) && ($Spkldetail->actualendwork== null)){
								$Spkldetail->actualendwork = $Spkl->datework;
							}
							$start= isset($data['actualstartwork'])?$data['actualstartwork']:$Spkldetail->actualstartwork;
							$date1 = new DateTime($start);
							$date2 =  isset($data['actualendwork'])?new DateTime($data['actualendwork']):new DateTime($Spkldetail->actualendwork);
							$diff = $date2->diff($date1);
							$hours = $diff->h + ($diff->days*24)+($diff->i/60);
							$hours =($hours >5)?$hours-1:$hours;
							$Spkldetail->actualtotalhours = round($hours,1);
							$Holiday = Holiday::find('all',array('conditions' => array("HolidayDate=?",date("Y-m-d", strtotime($start)))));
							if(count($Holiday)>0){
								$Spkldetail->actualnormalhours = 0;
								$Spkldetail->actualovertimehours = round($hours,1);
							}else{
								$wd =date('N',strtotime($start));
								if ($wd=='6'){
									$Spkldetail->actualnormalhours = ($hours>5)?5:round($hours,1);
									$Spkldetail->actualovertimehours = ($hours>5)?round($hours,1) - 5:0 ;
								}else if($wd=='7'){
									$Spkldetail->actualnormalhours = 0;
									$Spkldetail->actualovertimehours = round($hours,1);
								}else{
									$Spkldetail->actualnormalhours = ($hours>8)?8:round($hours,1);
									$Spkldetail->actualovertimehours = ($hours>8)?round($hours,1) - 8 :0;
								}
							}
							if($Spkldetail->isapproved==false){
								$Spkldetail->actualtotalhours=0;
								$Spkldetail->actualnormalhours=0;
								$Spkldetail->actualovertimehours =0;
							}
						}
						if (isset($data['actualovertimehours']) && (($Spkldetail->actualnormalhours+$data['actualovertimehours'])>$Spkldetail->actualtotalhours)){
							$resp = array('status'=>'error','message'=>'Total Overtime hours calculation is not valid, please recheck 
							<br>Total hours   :'.$Spkldetail->actualtotalhours.
							'<br>Normal hours :'.$Spkldetail->actualnormalhours.
							'<br>Overtime hours :'.$data['actualovertimehours'].'<br>Normal hours + overtime hours cannot > Total hours');
							echo json_encode($resp);
						}else{
							if (isset($data['isapproved'])  ){
								if ($data['isapproved']=='No'){
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
									$Spkldetail->rejectspklby=$Employee->id;
									$Spkldetail->isotapproved=false;
								}else{
									$Spkldetail->rejectspklby=null;
									$Spkldetail->isotapproved=null;
								}								
							}
							foreach($data as $key=>$val){					
								$val=($val=='No')?false:(($val=='Yes')?true:$val);
								$Spkldetail->$key=$val;
							}
							$Spkldetail->save();
							$logger = new Datalogger("Spkldetail","update",json_encode($olddata),json_encode($data));
							$logger->SaveData();
							echo json_encode($Spkldetail);
						}
						$AllDetail = Spkldetail::find("all",array('conditions'=>array("spkl_id=?",$spkl_id)));
						$isexceed = 0;
						foreach($AllDetail as $data){
							if($data->actualovertimehours>$data->estimateovertimehours){
								$isexceed++;
							}
						}
						$isMoreThan2hours = 1;
						foreach($AllDetail as $data){
							if($data->estimateovertimehours>2){
								$isMoreThan2hours++;
							}
						}
						if ($isexceed>0){
							$joins   = "LEFT JOIN tbl_approver ON (tbl_spklotapproval.approver_id = tbl_approver.id) ";					
							$Spkltmsapproval = Spkltmsapproval::find('all',array('joins'=>$joins,'conditions' => array("spkl_id=? and approvaltype_id='21'",$spkl_id)));	
							foreach ($Spkltmsapproval as &$result) {
								$result		= $result->to_array();
								$result['no']=1;
							}			
							if(count($Spkltmsapproval)==0){
								$joins   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";
								$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username),"include"=>array("location","company","department")));
								if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220")){
									$ApproverHR = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='SPKL' and tbl_approver.isactive='1' and approvaltype_id='21' and tbl_employee.location_id='1'")));
									if(count($ApproverHR)>0){
										$Spkltmsapproval = new Spkltmsapproval();
										$Spkltmsapproval->spkl_id =$spkl_id;
										$Spkltmsapproval->approver_id = $ApproverHR->id;
										$Spkltmsapproval->save();
										$logger = new Datalogger("Spkltmsapproval","add","Add HR Approval for Exceeded actual overtime hours",json_encode($Spkltmsapproval->to_array()));
										$logger->SaveData();
									}
								}else{
									$ApproverHR = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='SPKL' and tbl_approver.isactive='1' and approvaltype_id='21'  and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
									if(count($ApproverHR)>0){
										$Spkltmsapproval = new Spkltmsapproval();
										$Spkltmsapproval->spkl_id =$spkl_id;
										$Spkltmsapproval->approver_id = $ApproverHR->id;
										$Spkltmsapproval->save();
										$logger = new Datalogger("Spkltmsapproval","add","Add HR Approval for Exceeded actual overtime hours",json_encode($Spkltmsapproval->to_array()));
										$logger->SaveData();
									}
								}
							}
						} else {
							//delete unnecessary approver
							$joins   = "LEFT JOIN tbl_approver ON (tbl_spklotapproval.approver_id = tbl_approver.id) ";					
							$dx = Spkltmsapproval::find('all',array('joins'=>$joins,'conditions' => array("spkl_id=? and tbl_approver.approvaltype_id=21",$spkl_id)));	
							foreach ($dx as $result) {
								//delete same type approver
								$result->delete();
								$logger = new Datalogger("Spkltmsapproval","delete",json_encode($result->to_array()),"delete HR Approval for non exceeded actual overtime");
							}
						}
						echo "isMoreThan2hours = ".$isMoreThan2hours;
						if ($isMoreThan2hours>0){
							$joins   = "LEFT JOIN tbl_approver ON (tbl_spklapproval.approver_id = tbl_approver.id) ";					
							$Spklapproval = Spklapproval::find('all',array('joins'=>$joins,'conditions' => array("spkl_id=? and approvaltype_id='22'",$spkl_id)));	
							foreach ($Spklapproval as &$result) {
								$result		= $result->to_array();
								$result['no']=1;
							}			
							if(count($Spklapproval)==0){
								$joins   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";
								$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username),"include"=>array("location","company","department")));
								if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220") || ($Employee->company->companycode=="KPS")){
									if(($Employee->company->sapcode!="NKF") && ($Employee->company->sapcode!="RND")  && ($Employee->company->companycode!="BCL")  && ($Employee->company->companycode!="LDU")){	
										$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='SPKL' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.companycode='KPSI' and not(tbl_employee.id=?)",$Employee->id)));
										if(count($ApproverBUHead)>0){
											$Spklapproval = new Spklapproval();
											$Spklapproval->spkl_id = $Spkl->id;
											$Spklapproval->approver_id = $ApproverBUHead->id;
											$Spklapproval->save();
											$logger = new Datalogger("Spklapproval","add","Add BUHead Approval for SPKL > 2 hours",json_encode($Spklapproval->to_array()));
											$logger->SaveData();
										}else{
											$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='SPKL' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.companycode='KPSI' and not(tbl_employee.id=?)",$Employee->id)));
											if(count($ApproverBUHead)>0){
												$Spklapproval = new Spklapproval();
												$Spklapproval->spkl_id = $Spkl->id;
												$Spklapproval->approver_id = $ApproverBUHead->id;
												$Spklapproval->save();
												$logger = new Datalogger("Spklapproval","add","Add BUHead Approval for SPKL > 2 hours",json_encode($Spklapproval->to_array()));
												$logger->SaveData();
											}
										}
									}else{
										$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='SPKL' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.company_id=? and not tbl_employee.companycode='KPSI'  and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
										if(count($ApproverBUHead)>0){
											$Spklapproval = new Spklapproval();
											$Spklapproval->spkl_id = $Spkl->id;
											$Spklapproval->approver_id = $ApproverBUHead->id;
											$Spklapproval->save();
											$logger = new Datalogger("Spklapproval","add","Add BUHead Approval for SPKL > 2 hours",json_encode($Spklapproval->to_array()));
											$logger->SaveData();
										}else{
											$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='SPKL' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.companycode='KPSI' and not(tbl_employee.id=?)",$Employee->id)));
											if(count($ApproverBUHead)>0){
												$Spklapproval = new Spklapproval();
												$Spklapproval->spkl_id = $Spkl->id;
												$Spklapproval->approver_id = $ApproverBUHead->id;
												$Spklapproval->save();
												$logger = new Datalogger("Spklapproval","add","Add BUHead Approval for SPKL > 2 hours",json_encode($Spklapproval->to_array()));
												$logger->SaveData();
											}
										}
									}

								}else{
									$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='SPKL' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.company_id=? and not tbl_employee.companycode='KPSI'  and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
									if(count($ApproverBUHead)>0){
										$Spklapproval = new Spklapproval();
										$Spklapproval->spkl_id = $Spkl->id;
										$Spklapproval->approver_id = $ApproverBUHead->id;
										$Spklapproval->save();
										$logger = new Datalogger("Spklapproval","add","Add BUHead Approval for SPKL > 2 hours",json_encode($Spklapproval->to_array()));
										$logger->SaveData();
									}else{
										$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='SPKL' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.companycode='KPSI' and not(tbl_employee.id=?)",$Employee->id)));
										if(count($ApproverBUHead)>0){
											$Spklapproval = new Spklapproval();
											$Spklapproval->spkl_id = $Spkl->id;
											$Spklapproval->approver_id = $ApproverBUHead->id;
											$Spklapproval->save();
											$logger = new Datalogger("Spklapproval","add","Add BUHead Approval for SPKL > 2 hours",json_encode($Spklapproval->to_array()));
											$logger->SaveData();
										}
									}
								}
															}
						} else {
							//delete unnecessary approver
							$joins   = "LEFT JOIN tbl_approver ON (tbl_spklapproval.approver_id = tbl_approver.id) ";					
							$dx = Spklapproval::find('all',array('joins'=>$joins,'conditions' => array("spkl_id=? and tbl_approver.approvaltype_id=22",$spkl_id)));	
							foreach ($dx as $result) {
								//delete same type approver
								$result->delete();
								$logger = new Datalogger("Spklapproval","delete",json_encode($result->to_array()),"delete BUHead for SPKL <= 2hours");
							}
						}
						$Spkl->isexceedplan=($isexceed>0);
						$Spkl->ismorethan2hours=($isMoreThan2hours>0);
						$Spkl->save();
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
						if($Employee->level_id>2 && $Employee->level_id !=5 ){
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
								$joins   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";
								if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220")){
									$ApproverHR = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='SPKL' and tbl_approver.isactive='1' and approvaltype_id='21' and tbl_employee.location_id='1'")));
									if(count($ApproverHR)>0){
										$Spklapproval = new Spklapproval();
										$Spklapproval->spkl_id =$Spkl->id;
										$Spklapproval->approver_id = $ApproverHR->id;
										$Spklapproval->save();
										$logger = new Datalogger("Spklapproval","add","Add initial HR Approval",json_encode($Spklapproval->to_array()));
										$logger->SaveData();
									}
								}else{
									$ApproverHR = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='SPKL' and tbl_approver.isactive='1' and approvaltype_id='21'  and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
									if(count($ApproverHR)>0){
										$Spklapproval = new Spklapproval();
										$Spklapproval->spkl_id = $Spkl->id;
										$Spklapproval->approver_id = $ApproverHR->id;
										$Spklapproval->save();
										$logger = new Datalogger("Spklapproval","add","Add initial HR Approval",json_encode($Spklapproval->to_array()));
										$logger->SaveData();
									}
								}
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
						} else {
							$data = array("status"=>"error","message"=>"You don't have authority to create OT Instruction / SPKL, \r\n Only Superintendent / Askep level can create SPKL");
						}
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
						$Spkl->approvalstep = ($data['requeststatus']==1)?1:0;
						$Spkl->save();
						
						if (isset($data['depthead'])){
							$joins   = "LEFT JOIN tbl_approver ON (tbl_spklapproval.approver_id = tbl_approver.id) ";					
							$dx = Spklapproval::find('all',array('joins'=>$joins,'conditions' => array("spkl_id=? and tbl_approver.approvaltype_id=20 and not(tbl_approver.employee_id=?)",$id,$depthead)));	
							foreach ($dx as $result) {
								//delete same type approver
								$result->delete();
								$logger = new Datalogger("Spklapproval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
							}				
							$Spklapproval = Spklapproval::find('all',array('joins'=>$joins,'conditions' => array("spkl_id=? and tbl_approver.employee_id=?",$id,$depthead)));	
							foreach ($Spklapproval as &$result) {
								$result		= $result->to_array();
								$result['no']=1;
							}			
							if(count($Spklapproval)==0){ 
								$Approver = Approver::find('first',array('conditions'=>array("module='SPKL' and employee_id=? and approvaltype_id=20",$depthead)));
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
									$approver->approvaltype_id = 20;
									$approver->isfinal = false;
									$approver->save();
									$Spklapproval = new Spklapproval();
									$Spklapproval->spkl_id = $Spkl->id;
									$Spklapproval->approver_id = $approver->id;
									$Spklapproval->save();
								}
							}
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
	function SPKLTms(){
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
							$join = "LEFT join tbl_employee on tbl_spkl.employee_id = tbl_employee.id left join tbl_department on tbl_employee.department_id=tbl_department.id";
							$Spkl = Spkl::find('all', array('joins'=>$join,'conditions' => array("tbl_department.id=? and RequestStatus='3'",$Employee->department_id),'include' => array('employee')));
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
									$Spkl = Spkl::find('all', array('conditions' => array("employee_id=? and RequestStatus='3' and TMSReqStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Spkl as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Spkl));
									break;
									break;
							}
						} else{
							$data=array();
						}
						echo json_encode($data);
						break;
					case 'update':
						$id = $this->post['id'];
						$data = $this->post['data'];
						$Spkl = Spkl::find($id,array('include'=>array('employee'=>array('company','department','designation','grade'))));
						$olddata = $Spkl->to_array();
						$superior = $data['superior'];
						unset($data['approvalstatus']);
						unset($data['fullname']);
						unset($data['department']);
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
						foreach($data as $key=>$val){
							$Spkl->$key=$val;
						}
						$Spkl->approvalstep = ($data['tmsreqstatus']==1)?1:0;
						$Spkl->save();
						$joins   = "LEFT JOIN tbl_approver ON (tbl_spklotapproval.approver_id = tbl_approver.id) ";					
						$Spkltmsapproval = Spkltmsapproval::find('all',array('joins'=>$joins,'conditions' => array("spkl_id=? and tbl_approver.employee_id=?",$id,$Spkl->depthead)));	
						foreach ($Spkltmsapproval as &$result) {
							$result		= $result->to_array();
							$result['no']=1;
						}			
						if(count($Spkltmsapproval)==0){
							$Approver = Approver::find('first',array('conditions'=>array("module='SPKL' and employee_id=? and approvaltype_id=20",$Spkl->depthead)));
							if(count($Approver)>0){
								$Spkltmsapproval = new Spkltmsapproval();
								$Spkltmsapproval->spkl_id = $Spkl->id;
								$Spkltmsapproval->approver_id = $Approver->id;
								$Spkltmsapproval->save();
							}
						}
						if (isset($data['superior'])){
							$joins   = "LEFT JOIN tbl_approver ON (tbl_spklotapproval.approver_id = tbl_approver.id) ";					
							$dx = Spkltmsapproval::find('all',array('joins'=>$joins,'conditions' => array("spkl_id=? and tbl_approver.approvaltype_id=20 and not(tbl_approver.employee_id=?)",$id,$superior)));	
							foreach ($dx as $result) {
								//delete same type approver
								$result->delete();
								$logger = new Datalogger("Spkltmsapproval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
							}
							$joins   = "LEFT JOIN tbl_approver ON (tbl_spklotapproval.approver_id = tbl_approver.id) ";					
							$Spkltmsapproval = Spkltmsapproval::find('all',array('joins'=>$joins,'conditions' => array("spkl_id=? and tbl_approver.employee_id=?",$id,$superior)));	
							foreach ($Spkltmsapproval as &$result) {
								$result		= $result->to_array();
								$result['no']=1;
							}			
							if(count($Spkltmsapproval)==0){ 
								$Approver = Approver::find('first',array('conditions'=>array("module='SPKL' and employee_id=? and approvaltype_id=20",$superior)));
								if(count($Approver)>0){
									$Spkltmsapproval = new Spkltmsapproval();
									$Spkltmsapproval->spkl_id = $Spkl->id;
									$Spkltmsapproval->approver_id = $Approver->id;
									$Spkltmsapproval->save();
								}else{
									$approver = new Approver();
									$approver->module = "SPKL";
									$approver->employee_id=$superior;
									$approver->sequence=0;
									$approver->approvaltype_id = 20;
									$approver->isfinal = false;
									$approver->save();
									$Spkltmsapproval = new Spkltmsapproval();
									$Spkltmsapproval->spkl_id = $Spkl->id;
									$Spkltmsapproval->approver_id = $approver->id;
									$Spkltmsapproval->save();
								}
							}
						}
						if($data['tmsreqstatus']==1){
							$Spkltmsapproval = Spkltmsapproval::find('all', array('conditions' => array("spkl_id=?",$id)));					
							foreach($Spkltmsapproval as $data){
								$data->approvalstatus=0;
								$data->save();
							}
							$joinx   = "LEFT JOIN tbl_approver ON (tbl_spklotapproval.approver_id = tbl_approver.id) ";					
							$Spkltmsapproval = Spkltmsapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and spkl_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
							$username = $Spkltmsapproval->approver->employee->loginname;
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
												<th colspan="5"><p class=MsoNormal>Actual Time Work</p></th>
												<th rowspan="2"><p class=MsoNormal>Target Work</p></th>
												<th rowspan="2"><p class=MsoNormal>Achievement/Remarks</p></th>
												</tr>
												<th><p class=MsoNormal>Start Work</p></th>
												<th><p class=MsoNormal>End Work</p></th>
												<th><p class=MsoNormal>Total <br>(hrs)</p></th>
												<th><p class=MsoNormal>Normal<br>(hrs)</p></th>
												<th><p class=MsoNormal>Overtime<br>(hrs)</p></th></tr>
												';
							$no=1;
							foreach ($Spkldetail as $data){
								$this->mailbody .='<tr style="height:22.5pt">
											<td><p class=MsoNormal> '.$no.'</p></td>
											<td><p class=MsoNormal> '.$data->employee->fullname.'</p></td>
											<td><p class=MsoNormal> '.$data->employee->sapid.'</p></td>
											<td><p class=MsoNormal> '.$data->employee->designation->designationname.'</p></td>
											<td><p class=MsoNormal> '.date("H:i",strtotime($data->actualstartwork)).'</p></td>
											<td><p class=MsoNormal> '.date("H:i",strtotime($data->actualendwork)).'</p></td>
											<td><p class=MsoNormal> '.$data->actualtotalhours.'</p></td>
											<td><p class=MsoNormal> '.$data->actualnormalhours.'</p></td>
											<td><p class=MsoNormal> '.$data->actualovertimehours.'</p></td>
											<td><p class=MsoNormal> '.$data->target.'</p></td>
											<td><p class=MsoNormal> '.$data->descriptionofwork.'</p></td>
								</tr>';
								$no++;
							}
							$this->mailbody .='</table><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">Please login to application <a href="http://172.18.80.201/oasys/">here</a> </span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p><p class=MsoNormal><span style="font-size:10.0pt;font-family:"Century Gothic","sans-serif";color:#1F497D">OASys ( Online Approval System ) : http://172.18.80.201/oasys <br><br></span><b><span style="font-size:12.0pt;font-family:"Century Gothic","sans-serif";color:#365F91"><br></span></b></p><p class=MsoNormal><hr><font color="red"><b>This is a computer generated email. Please do not reply to this email</b></font><span lang=IN style="font-size:12.0pt;font-family:"Times New Roman","serif""> </span><span style="font-size:12.0pt;font-family:"Times New Roman","serif""></span></p></div></body></html>';
							$this->mail->addAddress($adb->email, $adb->fullname);
							$this->mail->Subject = "Online Approval System -> New Overtime Timesheet Submission";
							$this->mail->msgHTML($this->mailbody);
							if (!$this->mail->send()) {
								$err = new Errorlog();
								$err->errortype = "SPKL Timesheet Mail";
								$err->errordate = date("Y-m-d h:i:s");
								$err->errormessage = $this->mail->ErrorInfo;
								$err->user = $this->currentUser->username;
								$err->ip = $this->ip;
								$err->save();
								echo "Mailer Error: " . $this->mail->ErrorInfo;
							} else {
								echo "Message sent!";
							}
							$Spkltmshistory = new Spkltmshistory();
							$Spkltmshistory->date = date("Y-m-d h:i:s");
							$Spkltmshistory->fullname = $Employee->fullname;
							$Spkltmshistory->spkl_id = $id;
							$Spkltmshistory->approvaltype = "Originator";
							$Spkltmshistory->actiontype = 2;
							$Spkltmshistory->save();
						}else{
							$Spkltmshistory = new Spkltmshistory();
							$Spkltmshistory->date = date("Y-m-d h:i:s");
							$Spkltmshistory->fullname = $Employee->fullname;
							$Spkltmshistory->spkl_id = $id;
							$Spkltmshistory->approvaltype = "Originator";
							$Spkltmshistory->actiontype = 1;
							$Spkltmshistory->save();
						}
						$logger = new Datalogger("SPKLTMS","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						//echo json_encode($Spkl);
						
						break;
					default:
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
						if ($Employee){
							$join = "LEFT join tbl_employee on tbl_spkl.employee_id = tbl_employee.id left join tbl_department on tbl_employee.department_id=tbl_department.id";
							$Spkl = Spkl::find('all', array('joins'=>$join,'conditions' => array("employee_id=? and RequestStatus='3'",$Employee->id),'include' => array('employee')));
							//print_r($Spkl);
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