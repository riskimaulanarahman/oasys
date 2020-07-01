<?php
class Grade extends ActiveRecord\Model
{
	static $table_name = 'tbl_grade';
	static $has_many = array(
		array('employee')
	);
}