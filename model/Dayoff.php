<?php
class Dayoff extends ActiveRecord\Model
{
	static $table_name = 'tbl_dayoffreq';
	static $belongs_to = array(
		array('employee'),
	);
	static $has_many = array(
		array('dayoffdetail')
	);
}