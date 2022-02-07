<?php
class Balance extends ActiveRecord\Model
{
	static $table_name = 'tbl_balance';
	// static $belongs_to = array(
	// 	array('dayoff'),
	// 	array("approver")
	// );
	static $has_many = array(
		array('employee')
	);

}