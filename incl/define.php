<?php 
defined( 'Po3nX' ) or die( 'No Direct access' );
error_reporting(E_ERROR | E_WARNING | E_PARSE);
define('EXT','.php');
define('ADMIN', SITE_PATH .DS.'admin');
define('MYCLASS', SITE_PATH .DS.'class');
define('IMAGES', SITE_PATH .DS.'images');
define('MYINC', SITE_PATH .DS.'incl');
define('MODEL', SITE_PATH .DS.'model');
define('SCRIPT', SITE_PATH .DS.'js');
define('CSS',SITE_PATH .DS.'css');
define('LANG', SITE_PATH .DS.'lang');
define('PAGE', SITE_PATH.DS.'page');
define('TPATH','theme');
define('TEPATH',TPATH.'/'.TEMPLATE.'/');
define('THEME', SITE_PATH.DS.TPATH.DS.TEMPLATE);
define('ACTIVERECORD',MYCLASS.DS. 'ActiveRecord'.EXT);
define('MAILER', MYCLASS.DS.'lib'.DS.'PHPMailer'.DS.'PHPMailerAutoload'.EXT);
define('VENDOR', SITE_PATH.DS.'vendor'.DS.'autoload'.EXT);
define('JWTPATH',MYCLASS.DS.'lib/php-jwt-master/src');
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('FULLPATH', str_replace(SELF, '', __FILE__));
define('MAINTENANCE',0);
define('_ISO','charset=UTF-8');
define('SKEY', "MYF1rstJWTTri4l");
define('ISS',"http://172.18.80.201"); 
define('AUD', "http://172.18.80.201");
define('IAT', time()); 
define('NBF',IAT); 
define('EXPR', IAT + 3600); 
if (!empty($_SERVER["HTTP_CLIENT_IP"])){
	$ip = $_SERVER["HTTP_CLIENT_IP"];
} elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
	$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
} else {
	$ip = $_SERVER["REMOTE_ADDR"];
}
define('USER_IP',$ip);
?>
