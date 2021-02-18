<?php
class Advancehistory extends ActiveRecord\Model
{
	static $table_name = 'tbl_advancehistory';
	static $belongs_to = array(
		array('advance'),
	);
}