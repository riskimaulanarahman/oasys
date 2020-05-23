<?php
class Userlog extends ActiveRecord\Model
{
	static $table_name = 'tbl_userlog';
	static $belongs_to = array(
		array('user')
	);
}