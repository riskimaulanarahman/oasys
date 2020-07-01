<?php
class Trschedule extends ActiveRecord\Model
{
	static $table_name = 'tbl_trschedule';
	static $belongs_to = array(
		array('tr'),
	);
}