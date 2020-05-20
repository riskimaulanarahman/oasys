<?php 
defined( 'Po3nX' ) or die( 'No Direct access' );
define('DB_HOST', "localhost");
define('DB_USER',  "kdu");
define('DB_PASSWORD', "KDUPlanning$3rv3r");
define('DB_NAME', "trial");
define('DSN','mysql:host='.DB_HOST.';dbname='.DB_NAME);
define('LDAP_SERVER', "ldap://172.18.2.3");
define('SMTPSERVER','d1jkts03b.d1.lcl');
define('SMTPAUTH',"AeShDAm1*2020");
define('MAILFROM',"online_system@d1.lcl");
define('DOMAIN',"d1");
define('CHARSET',"AL32UTF8");
//$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'); 
define('INFORMASI',"<span><strong>Lorem Ipsum</strong> is simply dummy text of the printing and typesetting industry.</span>
						<span>Lorem Ipsum has been the <strong>industry's standard</strong> dummy text ever since the 1500s.</span>");
define('BASE','http://172.18.80.201/dev/oasys/');
define('TEMPLATE','hierapolis'); 
?>