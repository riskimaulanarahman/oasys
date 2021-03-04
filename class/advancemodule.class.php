<?php
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

Class Advancemodule extends Application{
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
				case 'apiadvancebyemp':
					$this->advanceByEmp();
					break;
				case 'apiadvance':
					$this->advance();
					break;
				case 'apiadvancedetail':
					$this->advanceDetail();
					break;
				case 'apiadvanceapp':
					$this->advanceApproval();
					break;
				case 'apiadvancetmsapp':
					$this->advanceTMSApproval();
					break;
				case 'apiadvancehist':
					$this->advanceHistory();
					break;
				case 'apiadvancetmshist':
					$this->advanceTMSHistory();
					break;
				case 'apiadvancepdf':	
					$id = $this->get['id'];
					$this->generatePDF($id);
					break;
				case 'apiadvancetmspdf':	
					$id = $this->get['id'];
					$this->generateTMSPDF($id);
					break;
				case 'apiadvancetms':
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
		$Spkldetail=Spkldetail::find('all',array('conditions'=>array("advance_id=?",$doid),'include'=>array('advance'=>array('employee'=>array('company','department','designation','grade','location')))));
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
						$Reject = Employee::find('first', array('conditions' => array("id=?", $data->rejectadvanceby)));
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
				
		$joinx   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";					
		$Spklapproval = Spklapproval::find('all',array('joins'=>$joinx,'conditions' => array("advance_id=?",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee','approvaltype'))));
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
			$fileName ='doc'.DS.'advance'.DS.'pdf'.DS.''.$Spkl->employee->sapid.'_'.date("YmdHis").'.pdf';
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
		$Spkldetail=Spkldetail::find('all',array('conditions'=>array("advance_id=?",$doid),'include'=>array('advance'=>array('employee'=>array('company','department','designation','grade','location')))));

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
						$Reject = Employee::find('first', array('conditions' => array("id=?", $data->rejectadvanceby)));
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
		
		$joinx   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";					
		$Spklapproval = Spklapproval::find('all',array('joins'=>$joinx,'conditions' => array("advance_id=?",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee','approvaltype'))));							
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
		
		$joinx   = "LEFT JOIN tbl_approver ON (tbl_advanceotapproval.approver_id = tbl_approver.id) ";					
		
		$Spkltmsapproval = Spkltmsapproval::find('all',array('joins'=>$joinx,'conditions' => array("advance_id=?",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee','approvaltype'))));	
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
			$fileName ='doc'.DS.'advance'.DS.'pdf'.DS.'TMS'.$Spkl->employee->sapid.'_'.date("YmdHis").'.pdf';
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
	function advanceHistory(){
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
							$Spklhistory = Spklhistory::find('all', array('conditions' => array("advance_id=?",$id),'include' => array('advance')));
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
	function advanceTMSHistory(){
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
							$Spkltmshistory = Spkltmshistory::find('all', array('conditions' => array("advance_id=?",$id),'include' => array('advance')));
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
	function advanceApproval(){
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
							$join   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";
							$Advanceapproval = Advanceapproval::find('all', array('joins'=>$join,'conditions' => array("advance_id=?",$id),'include' => array('approver'=>array('approvaltype')),"order"=>"tbl_approver.sequence"));
							foreach ($Advanceapproval as &$result) {
								$approvaltype = $result->approver->approvaltype_id;
								$result		= $result->to_array();
								$result['approvaltype']=$approvaltype;
							}
							echo json_encode($Advanceapproval, JSON_NUMERIC_CHECK);
						}else{
							$Advanceapproval = new Advanceapproval();
							echo json_encode($Advanceapproval);
						}
						break;
					case 'find':
						$query=$this->post['query'];		
						if(isset($query['status'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";
							$dx = Advanceapproval::find('first', array('joins'=>$join,'conditions' => array("advance_id=? and tbl_approver.employee_id = ?",$query['advance_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
							$Advance = Advance::find($query['advance_id']);
							if($dx->approver->isfinal==1){
								$data=array("jml"=>1);
							}else{
								$join   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";
								$Advanceapproval = Advanceapproval::find('all', array('joins'=>$join,'conditions' => array("advance_id=? and ApprovalStatus<=1 and not tbl_approver.employee_id=?",$query['advance_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
								foreach ($Advanceapproval as &$result) {
									$fullname	= $result->approver->employee->fullname;	
									$result		= $result->to_array();
									$result['fullname']=$fullname;
								}
								$data=array("jml"=>count($Advanceapproval));
							}						
						} else if(isset($query['pending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
							$Advance = Advance::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
							foreach ($Advance as $result) {
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";					
								$Advanceapproval = Advanceapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and advance_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								if($Advanceapproval->approver->employee_id==$emp_id){
									$request[]=$result->id;
								}
							}
							$Advance = Advance::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
							foreach ($Advance as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=$Advance;
						} else if(isset($query['mypending'])){						
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$emp_id = $Employee->id;
							$Advance = Advance::find('all', array('conditions' => array("RequestStatus =1"),'include' => array('employee')));
							$jml=0;
							foreach ($Advance as $result) {
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";					
								$Advanceapproval = Advanceapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and advance_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								if($Advanceapproval->approver->employee_id==$emp_id){
									$request[]=$result->id;
								}
							}
							$Advance = Advance::find('all', array('conditions' => array("id in (?)",$request),'include' => array('employee')));
							foreach ($Advance as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=array("jml"=>count($Advance));
						} else if(isset($query['filter'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$join = "LEFT JOIN vwadvancereport v on tbl_advance.id=v.id LEFT JOIN tbl_employee ON (tbl_advance.employee_id = tbl_employee.id) ";
							$sel = 'tbl_advance.*, v.advancestatus,v.otstatus,v.personholding ';
							$Advance = Advance::find('all',array('joins'=>$join,'select'=>$sel,'include' => array('employee')));
							
							if($Employee->location->sapcode=='0200' || $this->currentUser->isadmin){
								$Advance = Advance::find('all',array('joins'=>$join,'select'=>$sel,'include' => array('employee'=>array('company','department'))));
							}else{
								$Advance = Advance::find('all',array('joins'=>$join,'select'=>$sel,'conditions' => array('tbl_advance.RequestStatus=3 and tbl_employee.company_id=?',$Employee->company_id ),'include' => array('employee'=>array('company','department'))));
							}
							
							foreach ($Advance as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							$data=$Advance;
						} else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Advanceapproval = Advanceapproval::create($data);
						$logger = new Datalogger("Advanceapproval","create",null,json_encode($data));
						$logger->SaveData();
						break;
					case 'delete':
						$id = $this->post['id'];
						$Advanceapproval = Advanceapproval::find($id);
						$data=$Advanceapproval->to_array();
						$Advanceapproval->delete();
						$logger = new Datalogger("Advanceapproval","delete",json_encode($data),null);
						$logger->SaveData();
						echo json_encode($Advanceapproval);
						break;
					case 'update':
						$doid = $this->post['id'];
						$data = $this->post['data'];
						$mode= $data['mode'];
						$Spkldetail=Spkldetail::find('all',array('conditions'=>array("advance_id=?",$doid),'include'=>array('advance'=>array('employee'=>array('company','department','designation','grade')))));
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
							
							$join   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";
							if (isset($data['mode'])){
								$Spklapproval = Spklapproval::find('first', array('joins'=>$join,'conditions' => array("advance_id=? and tbl_approver.employee_id=?",$doid,$Employee->id),'include' => array('approver'=>array('employee','approvaltype'))));
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
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";					
								$nSpklapproval = Spklapproval::find('first',array('joins'=>$joinx,'conditions' => array("advance_id=? and ApprovalStatus=0",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								$username = $nSpklapproval->approver->employee->loginname;
								$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
								$Spkldetail=Spkldetail::find('all',array('conditions'=>array("advance_id=?",$doid),'include'=>array('advance'=>array('employee'=>array('company','department','designation','grade','location')))));
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
											$Reject = Employee::find('first', array('conditions' => array("id=?", $row->rejectadvanceby)));
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
								$Spklhistory->advance_id = $doid;
								
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
											$Spklapproval = Spklapproval::find('all', array('joins'=>$join,'conditions' => array("advance_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
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
	function advanceTMSApproval(){
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
							$join   = "LEFT JOIN tbl_approver ON (tbl_advanceotapproval.approver_id = tbl_approver.id) ";
							$Spkltmsapproval = Spkltmsapproval::find('all', array('joins'=>$join,'conditions' => array("advance_id=?",$id),'include' => array('approver'=>array('approvaltype')),"order"=>"tbl_approver.sequence"));
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
							$join   = "LEFT JOIN tbl_approver ON (tbl_advanceotapproval.approver_id = tbl_approver.id) ";
							$dx = Spkltmsapproval::find('first', array('joins'=>$join,'conditions' => array("advance_id=? and tbl_approver.employee_id = ?",$query['advance_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
							$Spkl = Spkl::find($query['advance_id']);
							if($dx->approver->isfinal==1){
								$data=array("jml"=>1);
							}else{
								if($Spkl->isexceedplan && $dx->approver->approvaltype_id=='20'){
									$join   = "LEFT JOIN tbl_approver ON (tbl_advanceotapproval.approver_id = tbl_approver.id) ";
									$Spkltmsapproval = Spkltmsapproval::find('all', array('joins'=>$join,'conditions' => array("advance_id=? and ApprovalStatus<=1 and not tbl_approver.employee_id=?",$query['advance_id'],$Employee->id),'include' => array('approver'=>array('employee'))));
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
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_advanceotapproval.approver_id = tbl_approver.id) ";					
								$Spkltmsapproval = Spkltmsapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and advance_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
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
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_advanceotapproval.approver_id = tbl_approver.id) ";					
								$Spkltmsapproval = Spkltmsapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and advance_id=?",$result->id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
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
							$join = "LEFT JOIN vwadvancereport v on tbl_advance.id=v.id";
							$sel = 'tbl_advance.*, v.laststatus,v.personholding ';
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
						$Spkldetail=Spkldetail::find('all',array('conditions'=>array("advance_id=?",$doid),'include'=>array('advance'=>array('employee'=>array('company','department','designation','grade')))));
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
							$join   = "LEFT JOIN tbl_approver ON (tbl_advanceotapproval.approver_id = tbl_approver.id) ";
							if (isset($data['mode'])){
								$Spkltmsapproval = Spkltmsapproval::find('first', array('joins'=>$join,'conditions' => array("advance_id=? and tbl_approver.employee_id=?",$doid,$Employee->id),'include' => array('approver'=>array('employee','approvaltype'))));
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
								$joinx   = "LEFT JOIN tbl_approver ON (tbl_advanceotapproval.approver_id = tbl_approver.id) ";					
								$nSpklapproval = Spkltmsapproval::find('first',array('joins'=>$joinx,'conditions' => array("advance_id=? and ApprovalStatus=0",$doid),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
								$username = $nSpklapproval->approver->employee->loginname;
								$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
								$Spkldetail=Spkldetail::find('all',array('conditions'=>array("advance_id=?",$doid),'include'=>array('advance'=>array('employee'=>array('company','department','designation','grade','location')))));
								$usr = Addressbook::find('first',array('conditions'=>array("username=?",$Spkl->employee->loginname)));
								$email=$usr->email;
								
								$complete = false;
								$Spkltmshistory = new Spkltmshistory();
								$Spkltmshistory->date = date("Y-m-d h:i:s");
								$Spkltmshistory->fullname = $Employee->fullname;
								$Spkltmshistory->approvaltype = $Spkltmsapproval->approver->approvaltype->approvaltype;
								$Spkltmshistory->remarks = $data['remarks'];
								$Spkltmshistory->advance_id = $doid;
								
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
											$Spkltmsapproval = Spkltmsapproval::find('all', array('joins'=>$join,'conditions' => array("advance_id=?",$doid),'include' => array('approver'=>array('employee','approvaltype'))));
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
										$Spklapproval = Spklapproval::find('all', array('conditions' => array("advance_id=? and approvalstatus='0'",$doid)));
										foreach ($Spklapproval as $data) {
											$data->approvalstatus=4;
											$data->save();
										}
										$Spkldetail = Spkldetail::find('all', array('conditions' => array("advance_id=?",$doid)));
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
								$advancetype=array("New","Addendum","Project Capex");
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

	function advanceDetail(){
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
							// $join = "LEFT JOIN vwitsharefreport ON tbl_itsharefdetail.advance_id = vwitsharefreport.id";
							// $select = "tbl_itsharefdetail.*,vwitsharefreport.apprstatuscode";
							// $Advancedetail = Advancedetail::find('all', array('joins'=>$join,'select'=>$select,'conditions' => array("advance_id=?",$id)));
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
						$query=$this->post['query'];
						if(isset($query['status'])){
							$Advancedetail = Advancedetail::find('all', array('conditions' => array("advance_id=?",$query['advance_id'])));
							$data=array("jml"=>count($Advancedetail));
						}else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						// $exprice = $data['unitprice'] * $data['qty'];
						// $data['extendedprice'] = $exprice;
						if($data['change']=='true') {
							$data['change'] = 1;
						}else if($data['change']=='false') {
							$data['change'] = 0;
						}
						$Advancedetail = Advancedetail::create($data);
						$logger = new Datalogger("Advancedetail","create",null,json_encode($data));
						$logger->SaveData();
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
						$Advancedetail = Advancedetail::all();
						foreach ($Advancedetail as &$result) {
							$result = $result->to_array();
						}
						echo json_encode($Advancedetail, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}

	function advanceDetail2(){
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
							$joinx="left join tbl_employee on tbl_advancedetail.employee_id=tbl_employee.id left join tbl_designation on tbl_employee.designation_id=tbl_designation.id";
							$sel="tbl_advancedetail.*,tbl_employee.fullname,tbl_employee.sapid,tbl_designation.designationname as position";
							$Advancedetail = Advancedetail::find('all', array("joins"=>$joinx,"select"=>$sel,'conditions' => array("advance_id=?",$id)));
							foreach ($Advancedetail as &$result) {
								$appText = ($result->isapproved==null)?"":(($result->isapproved)?"Yes":"No");
								$usedText = ($result->isotapproved==null)?"":(($result->isotapproved)?"Yes":"No");
								$result		= $result->to_array();
								$result['isapproved'] = $appText;
								$result['isotapproved'] = $usedText;
							}
							echo json_encode($Advancedetail, JSON_NUMERIC_CHECK);
						}else{
							$Advancedetail = new Advancedetail();
							echo json_encode($Advancedetail);
						}
						break;
					case 'find':
						$query=$this->post['query'];
						if(isset($query['status'])){
							$Advancedetail = Advancedetail::find('all', array('conditions' => array("advance_id=?",$query['advance_id'])));
							$data=array("jml"=>count($Advancedetail));
						}else if (isset($query['detail'])){
							$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
							$joinx   = "LEFT JOIN tbl_advance as r ON (advance_id = r.id) left join tbl_employee e on r.employee_id=e.id";	
							if(($Employee->location->sapcode=='0200') || ($this->currentUser->isadmin)){
								$Advancedetail = Advancedetail::find('all', array('joins'=>$joinx,'include'=>array('employee'=>array("department","location","company","designation","department")),'conditions' => array("isOTApproved='1' and r.TMSReqStatus='3' and r.datework between ? and ?",$query['startDate'],$query['endDate']),'order'=>"datework"));
							}else{
								$Advancedetail = Advancedetail::find('all', array('joins'=>$joinx,'include'=>array('employee'=>array("department","location","company","designation","department")),'conditions' => array("isOTApproved='1' and r.TMSReqStatus='3' and e.company_id=?  and r.datework between ? and ?",$Employee->company_id,$query['startDate'],$query['endDate']),'order'=>"datework"));
							}
							
							foreach ($Advancedetail as &$result) {
								$joine  = "LEFT JOIN tbl_employee d ON (tbl_advance.depthead = d.id)";	
								$sel = 'tbl_advance.*,d.fullname as DeptHead';
								$Spkl = Spkl::find('first', array('select'=>$sel,'joins'=>$joine,'include'=>array('employee'=>array("department","location","company","designation","department")),'conditions' => array("tbl_advance.id=?",$result->advance_id)));
								$join  = "LEFT JOIN tbl_approver ON (tbl_advanceotapproval.approver_id = tbl_approver.id) ";	
								$Spkltmsapproval = Spkltmsapproval::find('first',array('joins'=>$join,'conditions'=>array("advance_id=?",$result->advance_id),'order'=>"tbl_approver.sequence desc",'include' => array('approver'=>array('employee'))));
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
							$data=$Advancedetail;
						}else{
							$data=array();
						}
						echo json_encode($data, JSON_NUMERIC_CHECK);
						break;
					case 'create':			
						$data = $this->post['data'];
						unset($data['__KEY__']);
						$Advancedetail = Advancedetail::create($data);
						$advance_id=$Advancedetail->advance_id;
						$Spkl = Spkl::find($advance_id);
						$olddata = $Advancedetail->to_array();
						if (isset($data['actualstartwork']) || isset($data['actualendwork'])){
							if(!isset($data['actualstartwork']) && ($Advancedetail->actualstartwork== null)){
								$Advancedetail->actualstartwork = $Spkl->datework;
							}
							if(!isset($data['actualendwork']) && ($Advancedetail->actualendwork== null)){
								$Advancedetail->actualendwork = $Spkl->datework;
							}
							$start= isset($data['actualstartwork'])?$data['actualstartwork']:$Advancedetail->actualstartwork;
							$date1 = new DateTime($start);
							$date2 =  isset($data['actualendwork'])?new DateTime($data['actualendwork']):new DateTime($Advancedetail->actualendwork);
							$diff = $date2->diff($date1);
							$hours = $diff->h + ($diff->days*24)+($diff->i/60);
							$hours =($hours >5)?$hours-1:$hours;
							$Advancedetail->actualtotalhours = round($hours,1);
							$Holiday = Holiday::find('all',array('conditions' => array("HolidayDate=?",date("Y-m-d", strtotime($start)))));
							if(count($Holiday)>0){
								$Advancedetail->actualnormalhours = 0;
								$Advancedetail->actualovertimehours = round($hours,1);
							}else{
								$wd =date('N',strtotime($start));
								if ($wd=='6'){
									$Advancedetail->actualnormalhours = ($hours>5)?5:round($hours,1);
									$Advancedetail->actualovertimehours = ($hours>5)?round($hours,1) - 5:0 ;
								}else if($wd=='7'){
									$Advancedetail->actualnormalhours = 0;
									$Advancedetail->actualovertimehours = round($hours,1);
								}else{
									$Advancedetail->actualnormalhours = ($hours>8)?8:round($hours,1);
									$Advancedetail->actualovertimehours = ($hours>8)?round($hours,1) - 8 :0;
								}
							}
							if($Advancedetail->isapproved==false){
								$Advancedetail->actualtotalhours=0;
								$Advancedetail->actualnormalhours=0;
								$Advancedetail->actualovertimehours =0;
							}
						}
						if (isset($data['actualovertimehours']) && (($Advancedetail->actualnormalhours+$data['actualovertimehours'])>$Advancedetail->actualtotalhours)){
							$resp = array('status'=>'error','message'=>'Total Overtime hours calculation is not valid, please recheck 
							<br>Total hours   :'.$Advancedetail->actualtotalhours.
							'<br>Normal hours :'.$Advancedetail->actualnormalhours.
							'<br>Overtime hours :'.$data['actualovertimehours'].'<br>Normal hours + overtime hours cannot > Total hours');
							echo json_encode($resp);
						}else{
							if (isset($data['isapproved'])  ){
								if ($data['isapproved']=='No'){
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
									$Advancedetail->rejectadvanceby=$Employee->id;
									$Advancedetail->isotapproved=false;
								}else{
									$Advancedetail->rejectadvanceby=null;
									$Advancedetail->isotapproved=null;
								}								
							}
							foreach($data as $key=>$val){					
								$val=($val=='No')?false:(($val=='Yes')?true:$val);
								$Advancedetail->$key=$val;
							}
							$Advancedetail->save();
							$logger = new Datalogger("Advancedetail","update",json_encode($olddata),json_encode($data));
							$logger->SaveData();
							echo json_encode($Advancedetail);
						}
						$AllDetail = Advancedetail::find("all",array('conditions'=>array("advance_id=?",$advance_id)));
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
							$joins   = "LEFT JOIN tbl_approver ON (tbl_advanceotapproval.approver_id = tbl_approver.id) ";					
							$Spkltmsapproval = Spkltmsapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and approvaltype_id='21'",$advance_id)));	
							foreach ($Spkltmsapproval as &$result) {
								$result		= $result->to_array();
								$result['no']=1;
							}			
							if(count($Spkltmsapproval)==0){
								$joins   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";
								$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username),"include"=>array("location","company","department")));
								if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220")){
									$ApproverHR = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='21' and tbl_employee.location_id='1'")));
									if(count($ApproverHR)>0){
										$Spkltmsapproval = new Spkltmsapproval();
										$Spkltmsapproval->advance_id =$advance_id;
										$Spkltmsapproval->approver_id = $ApproverHR->id;
										$Spkltmsapproval->save();
										$logger = new Datalogger("Spkltmsapproval","add","Add HR Approval for Exceeded actual overtime hours",json_encode($Spkltmsapproval->to_array()));
										$logger->SaveData();
									}
								}else{
									$ApproverHR = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='21'  and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
									if(count($ApproverHR)>0){
										$Spkltmsapproval = new Spkltmsapproval();
										$Spkltmsapproval->advance_id =$advance_id;
										$Spkltmsapproval->approver_id = $ApproverHR->id;
										$Spkltmsapproval->save();
										$logger = new Datalogger("Spkltmsapproval","add","Add HR Approval for Exceeded actual overtime hours",json_encode($Spkltmsapproval->to_array()));
										$logger->SaveData();
									}
								}
							}
						} else {
							//delete unnecessary approver
							$joins   = "LEFT JOIN tbl_approver ON (tbl_advanceotapproval.approver_id = tbl_approver.id) ";					
							$dx = Spkltmsapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=21",$advance_id)));	
							foreach ($dx as $result) {
								//delete same type approver
								$result->delete();
								$logger = new Datalogger("Spkltmsapproval","delete",json_encode($result->to_array()),"delete HR Approval for non exceeded actual overtime");
							}
						}
						echo "isMoreThan2hours = ".$isMoreThan2hours;
						if ($isMoreThan2hours>0){
							$joins   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";					
							$Spklapproval = Spklapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and approvaltype_id='22'",$advance_id)));	
							foreach ($Spklapproval as &$result) {
								$result		= $result->to_array();
								$result['no']=1;
							}			
							if(count($Spklapproval)==0){
								$joins   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";
								$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username),"include"=>array("location","company","department")));
								if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220") || ($Employee->company->companycode=="KPS")){
									if(($Employee->company->sapcode!="NKF") && ($Employee->company->sapcode!="RND")  && ($Employee->company->companycode!="BCL")  && ($Employee->company->companycode!="LDU")){	
										$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.companycode='KPSI' and not(tbl_employee.id=?)",$Employee->id)));
										if(count($ApproverBUHead)>0){
											$Spklapproval = new Spklapproval();
											$Spklapproval->advance_id = $Spkl->id;
											$Spklapproval->approver_id = $ApproverBUHead->id;
											$Spklapproval->save();
											$logger = new Datalogger("Spklapproval","add","Add BUHead Approval for SPKL > 2 hours",json_encode($Spklapproval->to_array()));
											$logger->SaveData();
										}else{
											$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.companycode='KPSI' and not(tbl_employee.id=?)",$Employee->id)));
											if(count($ApproverBUHead)>0){
												$Spklapproval = new Spklapproval();
												$Spklapproval->advance_id = $Spkl->id;
												$Spklapproval->approver_id = $ApproverBUHead->id;
												$Spklapproval->save();
												$logger = new Datalogger("Spklapproval","add","Add BUHead Approval for SPKL > 2 hours",json_encode($Spklapproval->to_array()));
												$logger->SaveData();
											}
										}
									}else{
										$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.company_id=? and not tbl_employee.companycode='KPSI'  and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
										if(count($ApproverBUHead)>0){
											$Spklapproval = new Spklapproval();
											$Spklapproval->advance_id = $Spkl->id;
											$Spklapproval->approver_id = $ApproverBUHead->id;
											$Spklapproval->save();
											$logger = new Datalogger("Spklapproval","add","Add BUHead Approval for SPKL > 2 hours",json_encode($Spklapproval->to_array()));
											$logger->SaveData();
										}else{
											$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.companycode='KPSI' and not(tbl_employee.id=?)",$Employee->id)));
											if(count($ApproverBUHead)>0){
												$Spklapproval = new Spklapproval();
												$Spklapproval->advance_id = $Spkl->id;
												$Spklapproval->approver_id = $ApproverBUHead->id;
												$Spklapproval->save();
												$logger = new Datalogger("Spklapproval","add","Add BUHead Approval for SPKL > 2 hours",json_encode($Spklapproval->to_array()));
												$logger->SaveData();
											}
										}
									}

								}else{
									$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.company_id=? and not tbl_employee.companycode='KPSI'  and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
									if(count($ApproverBUHead)>0){
										$Spklapproval = new Spklapproval();
										$Spklapproval->advance_id = $Spkl->id;
										$Spklapproval->approver_id = $ApproverBUHead->id;
										$Spklapproval->save();
										$logger = new Datalogger("Spklapproval","add","Add BUHead Approval for SPKL > 2 hours",json_encode($Spklapproval->to_array()));
										$logger->SaveData();
									}else{
										$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.companycode='KPSI' and not(tbl_employee.id=?)",$Employee->id)));
										if(count($ApproverBUHead)>0){
											$Spklapproval = new Spklapproval();
											$Spklapproval->advance_id = $Spkl->id;
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
							$joins   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";					
							$dx = Spklapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=22",$advance_id)));	
							foreach ($dx as $result) {
								//delete same type approver
								$result->delete();
								$logger = new Datalogger("Spklapproval","delete",json_encode($result->to_array()),"delete BUHead for SPKL <= 2hours");
							}
						}
						$Spkl->isexceedplan=($isexceed>0);
						$Spkl->ismorethan2hours=($isMoreThan2hours>0);
						$Spkl->save();
						
						$logger = new Datalogger("Advancedetail","create",null,json_encode($data));
						$logger->SaveData();
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
						$advance_id=$Advancedetail->advance_id;
						$Spkl = Spkl::find($advance_id);
						$olddata = $Advancedetail->to_array();
						if (isset($data['actualstartwork']) || isset($data['actualendwork'])){
							if(!isset($data['actualstartwork']) && ($Advancedetail->actualstartwork== null)){
								$Advancedetail->actualstartwork = $Spkl->datework;
							}
							if(!isset($data['actualendwork']) && ($Advancedetail->actualendwork== null)){
								$Advancedetail->actualendwork = $Spkl->datework;
							}
							$start= isset($data['actualstartwork'])?$data['actualstartwork']:$Advancedetail->actualstartwork;
							$date1 = new DateTime($start);
							$date2 =  isset($data['actualendwork'])?new DateTime($data['actualendwork']):new DateTime($Advancedetail->actualendwork);
							$diff = $date2->diff($date1);
							$hours = $diff->h + ($diff->days*24)+($diff->i/60);
							$hours =($hours >5)?$hours-1:$hours;
							$Advancedetail->actualtotalhours = round($hours,1);
							$Holiday = Holiday::find('all',array('conditions' => array("HolidayDate=?",date("Y-m-d", strtotime($start)))));
							if(count($Holiday)>0){
								$Advancedetail->actualnormalhours = 0;
								$Advancedetail->actualovertimehours = round($hours,1);
							}else{
								$wd =date('N',strtotime($start));
								if ($wd=='6'){
									$Advancedetail->actualnormalhours = ($hours>5)?5:round($hours,1);
									$Advancedetail->actualovertimehours = ($hours>5)?round($hours,1) - 5:0 ;
								}else if($wd=='7'){
									$Advancedetail->actualnormalhours = 0;
									$Advancedetail->actualovertimehours = round($hours,1);
								}else{
									$Advancedetail->actualnormalhours = ($hours>8)?8:round($hours,1);
									$Advancedetail->actualovertimehours = ($hours>8)?round($hours,1) - 8 :0;
								}
							}
							if($Advancedetail->isapproved==false){
								$Advancedetail->actualtotalhours=0;
								$Advancedetail->actualnormalhours=0;
								$Advancedetail->actualovertimehours =0;
							}
						}
						if (isset($data['actualovertimehours']) && (($Advancedetail->actualnormalhours+$data['actualovertimehours'])>$Advancedetail->actualtotalhours)){
							$resp = array('status'=>'error','message'=>'Total Overtime hours calculation is not valid, please recheck 
							<br>Total hours   :'.$Advancedetail->actualtotalhours.
							'<br>Normal hours :'.$Advancedetail->actualnormalhours.
							'<br>Overtime hours :'.$data['actualovertimehours'].'<br>Normal hours + overtime hours cannot > Total hours');
							echo json_encode($resp);
						}else{
							if (isset($data['isapproved'])  ){
								if ($data['isapproved']=='No'){
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
									$Advancedetail->rejectadvanceby=$Employee->id;
									$Advancedetail->isotapproved=false;
								}else{
									$Advancedetail->rejectadvanceby=null;
									$Advancedetail->isotapproved=null;
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
						$AllDetail = Spkldetail::find("all",array('conditions'=>array("advance_id=?",$advance_id)));
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
							$joins   = "LEFT JOIN tbl_approver ON (tbl_advanceotapproval.approver_id = tbl_approver.id) ";					
							$Spkltmsapproval = Spkltmsapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and approvaltype_id='21'",$advance_id)));	
							foreach ($Spkltmsapproval as &$result) {
								$result		= $result->to_array();
								$result['no']=1;
							}			
							if(count($Spkltmsapproval)==0){
								$joins   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";
								$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username),"include"=>array("location","company","department")));
								if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220")){
									$ApproverHR = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='21' and tbl_employee.location_id='1'")));
									if(count($ApproverHR)>0){
										$Spkltmsapproval = new Spkltmsapproval();
										$Spkltmsapproval->advance_id =$advance_id;
										$Spkltmsapproval->approver_id = $ApproverHR->id;
										$Spkltmsapproval->save();
										$logger = new Datalogger("Spkltmsapproval","add","Add HR Approval for Exceeded actual overtime hours",json_encode($Spkltmsapproval->to_array()));
										$logger->SaveData();
									}
								}else{
									$ApproverHR = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='21'  and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
									if(count($ApproverHR)>0){
										$Spkltmsapproval = new Spkltmsapproval();
										$Spkltmsapproval->advance_id =$advance_id;
										$Spkltmsapproval->approver_id = $ApproverHR->id;
										$Spkltmsapproval->save();
										$logger = new Datalogger("Spkltmsapproval","add","Add HR Approval for Exceeded actual overtime hours",json_encode($Spkltmsapproval->to_array()));
										$logger->SaveData();
									}
								}
							}
						} else {
							//delete unnecessary approver
							$joins   = "LEFT JOIN tbl_approver ON (tbl_advanceotapproval.approver_id = tbl_approver.id) ";					
							$dx = Spkltmsapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=21",$advance_id)));	
							foreach ($dx as $result) {
								//delete same type approver
								$result->delete();
								$logger = new Datalogger("Spkltmsapproval","delete",json_encode($result->to_array()),"delete HR Approval for non exceeded actual overtime");
							}
						}
						echo "isMoreThan2hours = ".$isMoreThan2hours;
						if ($isMoreThan2hours>0){
							$joins   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";					
							$Spklapproval = Spklapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and approvaltype_id='22'",$advance_id)));	
							foreach ($Spklapproval as &$result) {
								$result		= $result->to_array();
								$result['no']=1;
							}			
							if(count($Spklapproval)==0){
								$joins   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";
								$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username),"include"=>array("location","company","department")));
								if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220") || ($Employee->company->companycode=="KPS")){
									if(($Employee->company->sapcode!="NKF") && ($Employee->company->sapcode!="RND")  && ($Employee->company->companycode!="BCL")  && ($Employee->company->companycode!="LDU")){	
										$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.companycode='KPSI' and not(tbl_employee.id=?)",$Employee->id)));
										if(count($ApproverBUHead)>0){
											$Spklapproval = new Spklapproval();
											$Spklapproval->advance_id = $Spkl->id;
											$Spklapproval->approver_id = $ApproverBUHead->id;
											$Spklapproval->save();
											$logger = new Datalogger("Spklapproval","add","Add BUHead Approval for SPKL > 2 hours",json_encode($Spklapproval->to_array()));
											$logger->SaveData();
										}else{
											$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.companycode='KPSI' and not(tbl_employee.id=?)",$Employee->id)));
											if(count($ApproverBUHead)>0){
												$Spklapproval = new Spklapproval();
												$Spklapproval->advance_id = $Spkl->id;
												$Spklapproval->approver_id = $ApproverBUHead->id;
												$Spklapproval->save();
												$logger = new Datalogger("Spklapproval","add","Add BUHead Approval for SPKL > 2 hours",json_encode($Spklapproval->to_array()));
												$logger->SaveData();
											}
										}
									}else{
										$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.company_id=? and not tbl_employee.companycode='KPSI'  and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
										if(count($ApproverBUHead)>0){
											$Spklapproval = new Spklapproval();
											$Spklapproval->advance_id = $Spkl->id;
											$Spklapproval->approver_id = $ApproverBUHead->id;
											$Spklapproval->save();
											$logger = new Datalogger("Spklapproval","add","Add BUHead Approval for SPKL > 2 hours",json_encode($Spklapproval->to_array()));
											$logger->SaveData();
										}else{
											$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.companycode='KPSI' and not(tbl_employee.id=?)",$Employee->id)));
											if(count($ApproverBUHead)>0){
												$Spklapproval = new Spklapproval();
												$Spklapproval->advance_id = $Spkl->id;
												$Spklapproval->approver_id = $ApproverBUHead->id;
												$Spklapproval->save();
												$logger = new Datalogger("Spklapproval","add","Add BUHead Approval for SPKL > 2 hours",json_encode($Spklapproval->to_array()));
												$logger->SaveData();
											}
										}
									}

								}else{
									$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.company_id=? and not tbl_employee.companycode='KPSI'  and not(tbl_employee.id=?)",$Employee->company_id,$Employee->id)));
									if(count($ApproverBUHead)>0){
										$Spklapproval = new Spklapproval();
										$Spklapproval->advance_id = $Spkl->id;
										$Spklapproval->approver_id = $ApproverBUHead->id;
										$Spklapproval->save();
										$logger = new Datalogger("Spklapproval","add","Add BUHead Approval for SPKL > 2 hours",json_encode($Spklapproval->to_array()));
										$logger->SaveData();
									}else{
										$ApproverBUHead = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=22 and tbl_employee.companycode='KPSI' and not(tbl_employee.id=?)",$Employee->id)));
										if(count($ApproverBUHead)>0){
											$Spklapproval = new Spklapproval();
											$Spklapproval->advance_id = $Spkl->id;
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
							$joins   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";					
							$dx = Spklapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=22",$advance_id)));	
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
	
	function advance(){
		if (count($this->post)==0){
			http_response_code(405);
    		echo json_encode(array("message" => "Method not Allowed"));
		}else{
			$auth = $this->jwt->checkAuth();
			if($auth){
				switch ($this->post['criteria']){
					case 'byid':
						$id = $this->post['id'];
						$Advance = Advance::find($id, array('include' => array('employee'=>array('company','department','designation'))));
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
						$query=$this->post['query'];					
						if(isset($query['status'])){
							switch ($query['status']){
								case "last":
									break;
								default:
									$Employee = Employee::find('first', array('conditions' => array("loginName=?",$query['username'])));
									$Advance = Advance::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Advance as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Advance));
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
								$Advance = Advance::create($data);
								$data=$Advance->to_array();
								$Advancehistory = new Advancehistory();
								$Advancehistory->date = date("Y-m-d h:i:s");
								$Advancehistory->fullname = $Employee->fullname;
								$Advancehistory->approvaltype = "Originator";
								$Advancehistory->advance_id = $Advance->id;
								$Advancehistory->actiontype = 0;
								$Advancehistory->save();
								$joins   = "LEFT JOIN tbl_employee ON (tbl_approver.employee_id = tbl_employee.id) ";

								$ApproverFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id=41")));
								if(count($ApproverFC)>0){
									$Advanceapproval = new Advanceapproval();
									$Advanceapproval->advance_id = $Advance->id;
									$Advanceapproval->approver_id = $ApproverFC->id;
									$Advanceapproval->save();
								}

								if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220")){
									$ApproverBUFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37' and tbl_employee.location_id='1'")));
									if(count($ApproverBUFC)>0){
										$Advanceapproval = new Advanceapproval();
										$Advanceapproval->advance_id =$Advance->id;
										$Advanceapproval->approver_id = $ApproverBUFC->id;
										$Advanceapproval->save();
										$logger = new Datalogger("Advanceapproval","add","Add initial BU Approval",json_encode($Advanceapproval->to_array()));
										$logger->SaveData();
									}
								}else{
									$ApproverBUFC = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='37'  and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
									if(count($ApproverBUFC)>0){
										$Advanceapproval = new Advanceapproval();
										$Advanceapproval->advance_id = $Advance->id;
										$Advanceapproval->approver_id = $ApproverBUFC->id;
										$Advanceapproval->save();
										$logger = new Datalogger("Advanceapproval","add","Add initial BU Approval",json_encode($Advanceapproval->to_array()));
										$logger->SaveData();
									}
								}

								if((substr(strtolower($Employee->location->sapcode),0,3)=="020") || (substr(strtolower($Employee->location->sapcode),0,4)=="0220")){
									$ApproverHR = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='36' and tbl_employee.location_id='1'")));
									if(count($ApproverHR)>0){
										$Advanceapproval = new Advanceapproval();
										$Advanceapproval->advance_id =$Advance->id;
										$Advanceapproval->approver_id = $ApproverHR->id;
										$Advanceapproval->save();
										$logger = new Datalogger("Advanceapproval","add","Add initial HR Approval",json_encode($Advanceapproval->to_array()));
										$logger->SaveData();
									}
								}else{
									$ApproverHR = Approver::find('first',array('joins'=>$joins,'conditions'=>array("module='Advance' and tbl_approver.isactive='1' and approvaltype_id='36'  and tbl_employee.company_id=? and not(tbl_employee.location_id='1')",$Employee->company_id)));
									if(count($ApproverHR)>0){
										$Advanceapproval = new Advanceapproval();
										$Advanceapproval->advance_id = $Advance->id;
										$Advanceapproval->approver_id = $ApproverHR->id;
										$Advanceapproval->save();
										$logger = new Datalogger("Advanceapproval","add","Add initial HR Approval",json_encode($Advanceapproval->to_array()));
										$logger->SaveData();
									}
								}
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
						$Advance = Advance::find($id);
						if ($Advance->requeststatus==0){
							try {
								$approval = Advanceapproval::find("all",array('conditions' => array("advance_id=?",$id)));
								foreach ($approval as $delr){
									$delr->delete();
								}
								$detail = Advancedetail::find("all",array('conditions' => array("advance_id=?",$id)));
								foreach ($detail as $delr){
									$delr->delete();
								}
								$hist = Advancehistory::find("all",array('conditions' => array("advance_id=?",$id)));
								foreach ($hist as $delr){
									$delr->delete();
								}
								$data = $Advance->to_array();
								$Advance->delete();
								$logger = new Datalogger("Advance","delete",json_encode($data),null);
								$logger->SaveData();
								$data = array("status"=>"success","message"=>"Data has been deleted");
							}catch (Exception $e){
								$err = new Errorlog();
								$err->errortype = "DeleteAdvance";
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
						$Advance = Advance::find($id,array('include'=>array('employee'=>array('company','department','designation','grade'))));
						$olddata = $Advance->to_array();
						$depthead = $data['depthead'];
						unset($data['approvalstatus']);
						unset($data['fullname']);
						unset($data['department']);
						//unset($data['employee']);
						$Employee = Employee::find('first', array('conditions' => array("loginName=?",$this->currentUser->username)));
						foreach($data as $key=>$val){
							$Advance->$key=$val;
						}
						$Advance->approvalstep = ($data['requeststatus']==1)?1:0;
						$Advance->save();
						
						if (isset($data['depthead'])){
							$joins   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";					
							$dx = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=20 and not(tbl_approver.employee_id=?)",$id,$depthead)));	
							foreach ($dx as $result) {
								//delete same type approver
								$result->delete();
								$logger = new Datalogger("Advanceapproval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
							}				
							$Advanceapproval = Advanceapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.employee_id=?",$id,$depthead)));	
							foreach ($Advanceapproval as &$result) {
								$result		= $result->to_array();
								$result['no']=1;
							}			
							if(count($Advanceapproval)==0){ 
								$Approver = Approver::find('first',array('conditions'=>array("module='Advance' and employee_id=? and approvaltype_id=20",$depthead)));
								if(count($Approver)>0){
									$Advanceapproval = new Advanceapproval();
									$Advanceapproval->advance_id = $Advance->id;
									$Advanceapproval->approver_id = $Approver->id;
									$Advanceapproval->save();
								}else{
									$approver = new Approver();
									$approver->module = "Advance";
									$approver->employee_id=$depthead;
									$approver->sequence=1;
									$approver->approvaltype_id = 20;
									$approver->isfinal = false;
									$approver->save();
									$Advanceapproval = new Advanceapproval();
									$Advanceapproval->advance_id = $Advance->id;
									$Advanceapproval->approver_id = $approver->id;
									$Advanceapproval->save();
								}
							}
						}
						if($data['requeststatus']==1){
							$Advanceapproval = Advanceapproval::find('all', array('conditions' => array("advance_id=?",$id)));					
							foreach($Advanceapproval as $data){
								$data->approvalstatus=0;
								$data->save();
							}
							$joinx   = "LEFT JOIN tbl_approver ON (tbl_advanceapproval.approver_id = tbl_approver.id) ";					
							$Advanceapproval = Advanceapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and advance_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
							$username = $Advanceapproval->approver->employee->loginname;
							$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
							$email = $adb->email;
							$Advancedetail=Advancedetail::find('all',array('conditions'=>array("advance_id=?",$id),'include'=>array('advance','employee'=>array('company','department','designation','grade'))));
							$this->mailbody .='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$adb->fullname.',</span></p>
												<p class=MsoNormal><span style="color:#1F497D">New SPKL/Overtime request is awaiting for your approval:</span></p>
												<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
												<table border=1 cellspacing=0 cellpadding=3 width=683>
													<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Advance->employee->fullname.'</b></p></td></tr>
													<tr><td><p class=MsoNormal>Creation Date</p></td><td>:</td><td><p class=MsoNormal><b>'.date("d/m/Y",strtotime($Advance->createddate)).'</b></p></td></tr>
													<tr><td><p class=MsoNormal>Date Work</p></td><td>:</td><td><p class=MsoNormal><b>'.date("d/m/Y",strtotime($Advance->datework)).'</b></p></td></tr>';
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
							foreach ($Advancedetail as $data){
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
							$Advancehistory = new Advancehistory();
							$Advancehistory->date = date("Y-m-d h:i:s");
							$Advancehistory->fullname = $Employee->fullname;
							$Advancehistory->advance_id = $id;
							$Advancehistory->approvaltype = "Originator";
							$Advancehistory->actiontype = 2;
							$Advancehistory->save();
						}else{
							$Advancehistory = new Advancehistory();
							$Advancehistory->date = date("Y-m-d h:i:s");
							$Advancehistory->fullname = $Employee->fullname;
							$Advancehistory->advance_id = $id;
							$Advancehistory->approvaltype = "Originator";
							$Advancehistory->actiontype = 1;
							$Advancehistory->save();
						}
						$logger = new Datalogger("Advance","update",json_encode($olddata),json_encode($data));
						$logger->SaveData();
						//echo json_encode($Advance);
						
						break;
					default:
						$Advance = Advance::all();
						foreach ($Advance as &$result) {
							$result = $result->to_array();
						}					
						echo json_encode($Advance, JSON_NUMERIC_CHECK);
						break;
				}
			}
		}
	}
	function advanceByEmp(){	
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
							$Advance = Advance::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Advance as &$result) {
								$fullname	= $result->employee->fullname;		
								$result		= $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Advance, JSON_NUMERIC_CHECK);
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
									$Advance = Advance::find('all', array('conditions' => array("employee_id=? and RequestStatus<3",$Employee->id),'include' => array('employee')));
									foreach ($Advance as &$result) {
										$fullname	= $result->employee->fullname;		
										$result		= $result->to_array();
										$result['fullname']=$fullname;
									}
									$data=array("jml"=>count($Advance));
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
							$Advance = Advance::find('all', array('conditions' => array("employee_id=?",$Employee->id),'include' => array('employee')));
							foreach ($Advance as &$result) {
								$fullname=$result->employee->fullname;
								$result = $result->to_array();
								$result['fullname']=$fullname;
							}
							echo json_encode($Advance, JSON_NUMERIC_CHECK);
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
							$join = "LEFT join tbl_employee on tbl_advance.employee_id = tbl_employee.id left join tbl_department on tbl_employee.department_id=tbl_department.id";
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
						$joins   = "LEFT JOIN tbl_approver ON (tbl_advanceotapproval.approver_id = tbl_approver.id) ";					
						$Spkltmsapproval = Spkltmsapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.employee_id=?",$id,$Spkl->depthead)));	
						foreach ($Spkltmsapproval as &$result) {
							$result		= $result->to_array();
							$result['no']=1;
						}			
						if(count($Spkltmsapproval)==0){
							$Approver = Approver::find('first',array('conditions'=>array("module='Advance' and employee_id=? and approvaltype_id=20",$Spkl->depthead)));
							if(count($Approver)>0){
								$Spkltmsapproval = new Spkltmsapproval();
								$Spkltmsapproval->advance_id = $Spkl->id;
								$Spkltmsapproval->approver_id = $Approver->id;
								$Spkltmsapproval->save();
							}
						}
						if (isset($data['superior'])){
							$joins   = "LEFT JOIN tbl_approver ON (tbl_advanceotapproval.approver_id = tbl_approver.id) ";					
							$dx = Spkltmsapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.approvaltype_id=20 and not(tbl_approver.employee_id=?)",$id,$superior)));	
							foreach ($dx as $result) {
								//delete same type approver
								$result->delete();
								$logger = new Datalogger("Spkltmsapproval","delete",json_encode($result->to_array()),"delete approver to prevent duplicate same type approver");
							}
							$joins   = "LEFT JOIN tbl_approver ON (tbl_advanceotapproval.approver_id = tbl_approver.id) ";					
							$Spkltmsapproval = Spkltmsapproval::find('all',array('joins'=>$joins,'conditions' => array("advance_id=? and tbl_approver.employee_id=?",$id,$superior)));	
							foreach ($Spkltmsapproval as &$result) {
								$result		= $result->to_array();
								$result['no']=1;
							}			
							if(count($Spkltmsapproval)==0){ 
								$Approver = Approver::find('first',array('conditions'=>array("module='Advance' and employee_id=? and approvaltype_id=20",$superior)));
								if(count($Approver)>0){
									$Spkltmsapproval = new Spkltmsapproval();
									$Spkltmsapproval->advance_id = $Spkl->id;
									$Spkltmsapproval->approver_id = $Approver->id;
									$Spkltmsapproval->save();
								}else{
									$approver = new Approver();
									$approver->module = "Advance";
									$approver->employee_id=$superior;
									$approver->sequence=0;
									$approver->approvaltype_id = 20;
									$approver->isfinal = false;
									$approver->save();
									$Spkltmsapproval = new Spkltmsapproval();
									$Spkltmsapproval->advance_id = $Spkl->id;
									$Spkltmsapproval->approver_id = $approver->id;
									$Spkltmsapproval->save();
								}
							}
						}
						if($data['tmsreqstatus']==1){
							$Spkltmsapproval = Spkltmsapproval::find('all', array('conditions' => array("advance_id=?",$id)));					
							foreach($Spkltmsapproval as $data){
								$data->approvalstatus=0;
								$data->save();
							}
							$joinx   = "LEFT JOIN tbl_approver ON (tbl_advanceotapproval.approver_id = tbl_approver.id) ";					
							$Spkltmsapproval = Spkltmsapproval::find('first',array('joins'=>$joinx,'conditions' => array("ApprovalStatus=0 and advance_id=?",$id),'order'=>"tbl_approver.sequence",'include' => array('approver'=>array('employee'))));							
							$username = $Spkltmsapproval->approver->employee->loginname;
							$adb = Addressbook::find('first',array('conditions'=>array("username=?",$username)));
							$email = $adb->email;
							$Spkldetail=Spkldetail::find('all',array('conditions'=>array("advance_id=?",$id),'include'=>array('advance','employee'=>array('company','department','designation','grade'))));
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
							$Spkltmshistory->advance_id = $id;
							$Spkltmshistory->approvaltype = "Originator";
							$Spkltmshistory->actiontype = 2;
							$Spkltmshistory->save();
						}else{
							$Spkltmshistory = new Spkltmshistory();
							$Spkltmshistory->date = date("Y-m-d h:i:s");
							$Spkltmshistory->fullname = $Employee->fullname;
							$Spkltmshistory->advance_id = $id;
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
							$join = "LEFT join tbl_employee on tbl_advance.employee_id = tbl_employee.id left join tbl_department on tbl_employee.department_id=tbl_department.id";
							$Spkl = Spkl::find('all', array('joins'=>$join,'conditions' => array("employee_id=? and RequestStatus='3'",$Employee->id),'include' => array('employee')));
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