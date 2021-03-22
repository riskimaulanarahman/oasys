<?php
class Advanceattachment extends ActiveRecord\Model
{
	static $table_name = 'tbl_advanceattachment';
	static $belongs_to = array(
		array('advance'),
	);
}