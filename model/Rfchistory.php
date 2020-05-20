<?php
class Rfchistory extends ActiveRecord\Model
{
	static $table_name = 'tbl_rfchistory';
	static $belongs_to = array(
		array('rfc'),
	);
}