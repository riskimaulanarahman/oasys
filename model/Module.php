<?php
class Module extends ActiveRecord\Model
{
	static $table_name = 'tbl_module';
	static $has_many = array(
		array('accessuser')
	);
}