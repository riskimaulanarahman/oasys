<?php
class Itmailsize extends ActiveRecord\Model
{
	static $table_name = 'tbl_itmailsize';
	static $belongs_to = array(
		array('employee'),
	);
	// static $has_many = array(
	// 	array(''),
	// 	array('')
	// );
}