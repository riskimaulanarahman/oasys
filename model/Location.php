<?php
class Location extends ActiveRecord\Model
{
	static $table_name = 'tbl_location';
	static $has_many = array(
		array('employee')
	);
}