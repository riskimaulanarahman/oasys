<?php
class Advexpenseattachment extends ActiveRecord\Model
{
	static $table_name = 'tbl_advexpenseattachment';
	static $belongs_to = array(
		array('advexpense'),
	);
}