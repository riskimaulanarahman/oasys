<?php
class Iteie extends ActiveRecord\Model
{
	static $table_name = 'tbl_iteie';
	static $belongs_to = array(
		array('employee'),
	);
	// static $has_many = array(
	// 	array(''),
	// 	array('')
	// );
}