<?php
class Dayoffhistory extends ActiveRecord\Model
{
	static $table_name = 'tbl_dayoffhistory';
	static $belongs_to = array(
		array('dayoff'),
	);
}