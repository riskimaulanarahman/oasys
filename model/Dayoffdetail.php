<?php
class Dayoffdetail extends ActiveRecord\Model
{
	static $table_name = 'tbl_dayoffdetail';
	static $belongs_to = array(
		array('dayoff'),
	);
}