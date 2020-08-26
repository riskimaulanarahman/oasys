<?php
class Itinetaccess extends ActiveRecord\Model
{
	static $table_name = 'tbl_itinetaccess';
	static $belongs_to = array(
		array('employee'),
	);
	// static $has_many = array(
	// 	array(''),
	// 	array('')
	// );
}