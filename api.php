<?php	
date_default_timezone_set('Asia/Makassar');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('Access-Control-Allow-Methods: GET, PUT,DELETE,POST, OPTIONS');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers:  Content-Type, Authorization, X-Requested-With,Cache-Control, Pragma, Origin');
    header('Access-Control-Max-Age: 3600');
	exit;
}
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With,Cache-Control, Pragma, Origin');
define('Po3nX',1);
define('SITE_PATH', dirname(__FILE__));
define( 'DS', DIRECTORY_SEPARATOR );
include ( SITE_PATH .DS.'incl'.DS.'db_conf.php' );
require_once ( SITE_PATH .DS.'incl'.DS.'define.php' );
require_once ( MYINC.DS.'functions.php' );
$page = array("login",
	"usermanager",
	"companymodule",
	"departmentmodule",
	"divisionmodule",
	"modulemanager",
	"employeemodule",
	"accmanager",
	"designationmodule",
	"grademodule",
	"religionmodule",
	"leavemodule",
	"dayoffmodule",
	"approvermodule",
	"approvaltypemodule",
	"locationmodule",
	"levelmodule",
	"holidaymodule",
	"rfcactivitymodule",
	"rfccontractormodule",
	"rfcmodule",
	"skratemodule",
	"spklmodule",
	"trmodule",
	"mmfmodule",
	"mmf30module",
	"unitmodule",
	"currencymodule",
	"iteiemodule",
	"itimailmodule",
	"listmodmodule",
	"itsharefoldermodule",
	"advancemodule",
	"advpaymentmodule",
	"advexpensemodule",
	"expensetypemodule",
	// "dashboardmodule",
);

foreach ($page as $class){
	$obj 	= new $class;
}