<?php
class Itsharefattachment extends ActiveRecord\Model
{
	static $table_name = 'tbl_itsharefattachment';
	static $belongs_to = array(
		array('itsharef'),
	);
}