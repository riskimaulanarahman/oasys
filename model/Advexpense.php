<?php
class Advexpense extends ActiveRecord\Model
{
	static $table_name = 'tbl_advexpense';
	// static $belongs_to = array(
	// 	array('employee')
	// );
	static $belongs_to = array(
        array('creator', 'class_name' => 'Employee', 'foreign_key' => 'createdby'),
        array('employee', 'foreign_key' => 'employee_id')
    );
	// static $has_many = array(
	// 	array('advancedetail')
	// );
}