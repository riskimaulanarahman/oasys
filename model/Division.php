<?php
class Division extends ActiveRecord\Model
{
	static $table_name = 'tbl_division';
	static $belongs_to = array(
		array('department'),
	);
	static $has_many = array(
		array('designation')
	);
}