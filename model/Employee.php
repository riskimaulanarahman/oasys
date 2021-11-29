<?php
class Employee extends ActiveRecord\Model
{
	static $table_name = 'tbl_employee';
	static $belongs_to = array(
		array('company'),
		array('department'),
		array('designation'),
		array('grade'),
		array('location'),
		array('religion'),
		array('level')
	);
	static $has_many = array(
		array('accessuser'),
		array('leave'),
		array('dayoff'),
		array('dayoffapproval'),
		array('spkldetail')
	);
}