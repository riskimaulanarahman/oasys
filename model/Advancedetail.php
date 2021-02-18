<?php
class Advancedetail extends ActiveRecord\Model
{
	static $table_name = 'tbl_advancedetail';
	static $belongs_to = array(
		array('advance'),
		array('employee'),
	);
}