<?php
class User extends ActiveRecord\Model
{
	static $table_name = 'tbl_user';
	static $belongs_to = array(
		array('role')
	);
	static $has_many = array(
		array('userlogs','conditions' => array("isActive='1'")),
		array('accessuser')
	);
}