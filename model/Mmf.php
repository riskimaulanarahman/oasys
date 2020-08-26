<?php
class Mmf extends ActiveRecord\Model
{
	static $table_name = 'tbl_mmf28';
	static $belongs_to = array(
		array('employee'),
	);
	// static $has_many = array(
	// 	array(''),
	// 	array('')
	// );
}