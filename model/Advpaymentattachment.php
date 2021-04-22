<?php
class Advpaymentattachment extends ActiveRecord\Model
{
	static $table_name = 'tbl_advpaymentattachment';
	static $belongs_to = array(
		array('advpayment'),
	);
}