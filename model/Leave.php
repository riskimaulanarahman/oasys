<?php
class Leave extends ActiveRecord\Model
{
	static $table_name = 'tbl_leavereq';
	static $belongs_to = array(
		array('employee'),
	);
	static $has_many = array(
		array('detailleave')
	);
}