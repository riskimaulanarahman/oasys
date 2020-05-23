<?php
class Accessuser extends ActiveRecord\Model
{
	static $table_name = 'tbl_useraccess';
	static $belongs_to = array(
		array('employee'),
		array('module')
	);
}