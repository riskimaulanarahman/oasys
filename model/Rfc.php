<?php
class Rfc extends ActiveRecord\Model
{
	static $table_name = 'tbl_rfc';
	static $belongs_to = array(
		array('employee'),
		array('rfcactivity'),
		array('rfccontractor'),
		array('rfccontract')
	);
	static $has_many = array(
		array('rfcdetail'),
		array('rfcterm'),
		array('rfcattachment')
	);
}