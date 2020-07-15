<?php 
error_reporting(E_ALL);
date_default_timezone_set('Asia/Makassar');
define('Po3nX',1);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
define('ROOT_PATH', dirname(__FILE__));
define( 'DS', DIRECTORY_SEPARATOR );
define('SITE_PATH', ROOT_PATH.DS.'..');
include ( SITE_PATH.DS.'incl'.DS.'db_conf.php' );
require_once ( SITE_PATH .DS.'incl'.DS.'define.php' );
require_once ( MYINC.DS.'functions.php' );
ActiveRecord\Config::initialize(function($cfg)
{
	$cfg->set_model_directory(MODEL);
	$cfg->set_connections(array('development' => 'mysql://'.DB_USER.':'.DB_PASSWORD.'@'.DB_HOST.'/'.DB_NAME));
	//$cfg->set_connections(array('production' => 'mysql://root:@localhost/oasys'));
	// you can change the default connection with the below
	$cfg->set_default_connection('development');
});
$Skrate = Skrate::all();
							//print_r($Skrate);
foreach ($Skrate as &$result) {
	$result = $result->to_array();
}
try{
	$data = json_encode($Skrate, JSON_NUMERIC_CHECK);
}catch (Exception $e){
	print_r($e);
}
//echo json_encode($Skrate, JSON_NUMERIC_CHECK);
switch (json_last_error()) {
	case JSON_ERROR_NONE:
		echo ' - No errors';
	break;
	case JSON_ERROR_DEPTH:
		echo ' - Maximum stack depth exceeded';
	break;
	case JSON_ERROR_STATE_MISMATCH:
		echo ' - Underflow or the modes mismatch';
	break;
	case JSON_ERROR_CTRL_CHAR:
		echo ' - Unexpected control character found';
	break;
	case JSON_ERROR_SYNTAX:
		echo ' - Syntax error, malformed JSON';
	break;
	case JSON_ERROR_UTF8:
		echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
	break;
	default:
		echo ' - Unknown error';
	break;
}



$id = 17;
$Spkl = Spkl::find($id,array('include'=>array('employee'=>array('company','department','designation','grade'))));
var_dump($Spkl);
$mailbody ='</o:shapelayout></xml><![endif]--></head><body lang=EN-US link="#0563C1" vlink="#954F72"><div class=WordSection1><p class=MsoNormal><span style="color:#1F497D"">Dear '.$adb->fullname.',</span></p>
												<p class=MsoNormal><span style="color:#1F497D">New SPKL/Overtime request is awaiting for your approval:</span></p>
												<p class=MsoNormal><span style="color:#1F497D">&nbsp;</span></p>
												<table border=1 cellspacing=0 cellpadding=3 width=683>
													<tr><td><p class=MsoNormal>Created By</p></td><td>:</td><td><p class=MsoNormal><b>'.$Spkl->employee->fullname.'</b></p></td></tr>
													<tr><td><p class=MsoNormal>Creation Date</p></td><td>:</td><td><p class=MsoNormal><b>'.date("d/m/Y",strtotime($Spkl->createddate)).'</b></p></td></tr>
													<tr><td><p class=MsoNormal>Date Work</p></td><td>:</td><td><p class=MsoNormal><b>'.date("d/m/Y",strtotime($Spkl->datework)).'</b></p></td></tr>';
													echo $mailbody;
?>