<?php
class Approver extends ActiveRecord\Model
{
	static $table_name = 'tbl_approver';
	static $belongs_to = array(
		array('employee'),
		array('approvaltype'),
	);
	static $has_many = array(
		array('dayoffapproval'),
		array('advpaymentapproval'),
	);
}