<?php
class Advpaymentapproval extends ActiveRecord\Model
{
	static $table_name = 'tbl_advpaymentapproval';
	static $belongs_to = array(
		array('advpayment'),
		array("approver")
	);

}