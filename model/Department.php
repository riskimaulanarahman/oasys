<?php
class Department extends ActiveRecord\Model
{
	static $table_name = 'tbl_department';
	static $has_many = array(
		array('employee'),
		array('division'),
		array('designation', 'through' => 'division')
	);
}