<?php
class Contract extends ActiveRecord\Model
{
	static $table_name = 'tbl_contract';
	static $belongs_to = array(
		array('rfc'),
	);
}