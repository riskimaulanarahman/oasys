<?php
class Mmf30 extends ActiveRecord\Model
{
	static $table_name = 'tbl_mmf30';
	static $belongs_to = array(
		array('employee'),
	);
	// static $has_many = array(
	// 	array(''),
	// 	array('')
	// );
}