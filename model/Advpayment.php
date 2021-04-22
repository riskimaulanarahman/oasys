<?php
class Advpayment extends ActiveRecord\Model
{
	static $table_name = 'tbl_advpayment';
	static $belongs_to = array(
		array('employee')
	);
	// static $has_many = array(
	// 	array('advancedetail')
	// );
}