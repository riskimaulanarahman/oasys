<?php
class Advpayment extends ActiveRecord\Model
{
	static $table_name = 'tbl_advpayment';
	// static $belongs_to = array(
	// 	array('employee')
	// );
	static $belongs_to = array(
        array('creator', 'class_name' => 'Employee', 'foreign_key' => 'createdby'),
        array('employee', 'foreign_key' => 'employee_id')
    );
}