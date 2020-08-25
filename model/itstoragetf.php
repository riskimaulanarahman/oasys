<?php
class Itstoragetf extends ActiveRecord\Model
{
	static $table_name = 'tbl_itstoragetf';
	static $belongs_to = array(
		array('employee'),
	);
	// static $has_many = array(
	// 	array(''),
	// 	array('')
	// );
}