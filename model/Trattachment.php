<?php
class Trattachment extends ActiveRecord\Model
{
	static $table_name = 'tbl_trattachment';
	static $belongs_to = array(
		array('tr'),
	);
}