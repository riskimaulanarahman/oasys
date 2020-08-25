<?php
class Itsharefolder extends ActiveRecord\Model
{
	static $table_name = 'tbl_itsharefolder';
	static $belongs_to = array(
		array('employee'),
	);
	// static $has_many = array(
	// 	array(''),
	// 	array('')
	// );
}