<?php
class Trhistory extends ActiveRecord\Model
{
	static $table_name = 'tbl_trhistory';
	static $belongs_to = array(
		array('tr'),
	);
}