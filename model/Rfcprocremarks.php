<?php
class Rfcprocremarks extends ActiveRecord\Model
{
	static $table_name = 'tbl_rfcprocremarks';
	static $belongs_to = array(
		array('rfc'),
	);
}