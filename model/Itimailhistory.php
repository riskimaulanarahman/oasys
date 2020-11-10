<?php
class Itimailhistory extends ActiveRecord\Model
{
	static $table_name = 'tbl_itimailhistory';
	static $belongs_to = array(
		array('itimail'),
	);
}