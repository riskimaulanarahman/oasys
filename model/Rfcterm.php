<?php
class Rfcterm extends ActiveRecord\Model
{
	static $table_name = 'tbl_rfcterm';
	static $belongs_to = array(
		array('rfc'),
	);
}