<?php
class Rfcactivity extends ActiveRecord\Model
{
	static $table_name = 'tbl_rfcactivity';
	static $has_many = array(
		array('rfc')
	);
}