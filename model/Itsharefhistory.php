<?php
class Itsharefhistory extends ActiveRecord\Model
{
	static $table_name = 'tbl_itsharefhistory';
	static $belongs_to = array(
		array('itsharef'),
	);
}