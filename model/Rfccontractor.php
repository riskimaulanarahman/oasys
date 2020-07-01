<?php
class Rfccontractor extends ActiveRecord\Model
{
	static $table_name = 'tbl_rfccontractor';
	static $has_many = array(
		array('rfc')
	);
}