<?php
class Itimail extends ActiveRecord\Model
{
	static $table_name = 'tbl_itimail';
	static $belongs_to = array(
		array('employee'),
	);
	// static $has_many = array(
	// 	array(''),
	// 	array('')
	// );
}