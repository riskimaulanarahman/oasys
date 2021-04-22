<?php
class Advpaymentdetail extends ActiveRecord\Model
{
	static $table_name = 'tbl_advpaymentdetail';
	static $belongs_to = array(
		array('advpayment'),
		array('employee'),
	);
}