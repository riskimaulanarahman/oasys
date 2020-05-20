<?php
class Rfcdetail extends ActiveRecord\Model
{
	static $table_name = 'tbl_rfcdetail';
	static $belongs_to = array(
		array('rfc'),
	);
}