<?php
class Listmod extends ActiveRecord\Model
{
	static $table_name = 'tbl_mod';
	static $has_many = array(
		array('itimail')
	);
}