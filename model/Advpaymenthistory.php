<?php
class Advpaymenthistory extends ActiveRecord\Model
{
	static $table_name = 'tbl_advpaymenthistory';
	static $belongs_to = array(
		array('advpayment'),
	);
}