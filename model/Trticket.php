<?php
class Trticket extends ActiveRecord\Model
{
	static $table_name = 'tbl_trticket';
	static $belongs_to = array(
		array('tr'),
	);
}