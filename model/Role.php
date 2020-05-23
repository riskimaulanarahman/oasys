<?php
class Role extends ActiveRecord\Model
{
	static $table_name = 'tbl_role';
	static $has_many = array(
		array('user')
	);

}