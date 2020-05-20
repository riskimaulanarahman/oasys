<?php
defined( 'Po3nX' ) or die( 'No Direct access' );
require_once ACTIVERECORD;
require_once MAILER;
require_once VENDOR;
function loadmyclass($classname) {
	$name=strtolower($classname).'.class';
	$file = MYCLASS.DS.$name.EXT;
	if (file_exists($file))
		require_once $file;
}
spl_autoload_register('loadmyclass');
function checkAction($action,$menu){
	return ($action==$menu)?"active":"";
}
function checkTable($table,$menu){
	return($table==$menu)?"active":"";
}
function encr($p,$s){
	return md5(sha1($s.$p).$s);
}
function total_sun($month,$year)
{
    $sundays=0;
    $total_days=cal_days_in_month(CAL_GREGORIAN, $month, $year);
    for($i=1;$i<=$total_days;$i++)
    if(date('N',strtotime($year.'-'.$month.'-'.$i))==7)
    $sundays++;
    return $sundays;
}
include_once JWTPATH.DS.'BeforeValidException'.EXT;
include_once JWTPATH.DS.'ExpiredException'.EXT;
include_once JWTPATH.DS.'SignatureInvalidException'.EXT;
include_once JWTPATH.DS.'JWT'.EXT;
?>