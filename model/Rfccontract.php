<?php
class Rfccontract extends ActiveRecord\Model
{
	static $table_name = 'tbl_rfccontract';
	static $has_many = array(
		array('rfc')
	);

}