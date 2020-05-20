<?php
class Rfcattachment extends ActiveRecord\Model
{
	static $table_name = 'tbl_rfcattachment';
	static $belongs_to = array(
		array('rfc'),
	);
}