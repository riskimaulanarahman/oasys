<?php
class Iteiehistory extends ActiveRecord\Model
{
	static $table_name = 'tbl_iteiehistory';
	static $belongs_to = array(
		array('iteie'),
	);
}