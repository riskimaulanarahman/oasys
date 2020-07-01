<?php
class Designation extends ActiveRecord\Model
{
	static $table_name = 'tbl_designation';
	static $has_many = array(
		array('employee')
	);
	static $belongs_to = array(
		array('division'),
	);
}