<?php
class Itrdweb extends ActiveRecord\Model
{
	static $table_name = 'tbl_itrdweb';
	static $belongs_to = array(
		array('employee'),
	);
	// static $has_many = array(
	// 	array(''),
	// 	array('')
	// );
}