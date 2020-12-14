<?php
class Itsharef extends ActiveRecord\Model
{
	static $table_name = 'tbl_itsharef';
	static $belongs_to = array(
		array('employee'),
	);
	// static $has_many = array(
	// 	array(''),
	// 	array('')
	// );
}