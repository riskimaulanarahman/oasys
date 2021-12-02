<?php
class Pendingappr extends ActiveRecord\Model
{
	static $table_name = 'vwpendingbyemp';
	static $belongs_to = array(
		array('employee'),
	);
	// static $has_many = array(
	// 	array(''),`
	// 	array('')
	// );
}