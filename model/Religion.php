<?php
class Religion extends ActiveRecord\Model
{
	static $table_name = 'tbl_religion';
	static $has_many = array(
		array('employee')
	);

}