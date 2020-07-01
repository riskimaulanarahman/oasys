<?php
class Approvaltype extends ActiveRecord\Model
{
	static $table_name = 'tbl_approvaltype';
	static $has_many = array(
		array('approver')
	);
}