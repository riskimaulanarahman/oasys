<?php
class Contractfile extends ActiveRecord\Model
{
	static $table_name = 'tbl_contractdoc';
	static $belongs_to = array(
		array('contract'),
	);
}