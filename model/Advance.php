<?php
class Advance extends ActiveRecord\Model
{
	static $table_name = 'tbl_advance';
	static $belongs_to = array(
		array('employee')
	);
	static $has_many = array(
		array('advancedetail')
	);
}