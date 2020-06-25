<?php
class Tr extends ActiveRecord\Model
{
	static $table_name = 'tbl_tr';
	static $belongs_to = array(
		array('employee'),
	);
	static $has_many = array(
		array('trschedule'),
		array('trticket')
	);
}