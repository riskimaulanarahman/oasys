<?php
class Advexpense extends ActiveRecord\Model
{
	static $table_name = 'tbl_advexpense';
	static $belongs_to = array(
		array('employee')
	);
	// static $has_many = array(
	// 	array('advancedetail')
	// );
}