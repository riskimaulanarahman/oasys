<?php
class Pendingreq extends ActiveRecord\Model
{
	static $table_name = 'vwpendingreq';
	static $belongs_to = array(
		array('employee'),
	);
	// static $has_many = array(
	// 	array(''),`
	// 	array('')
	// );
}