<?php
class Advexpensedetail extends ActiveRecord\Model
{
	static $table_name = 'tbl_advexpensedetail';
	static $belongs_to = array(
		array('advexpense'),
		array('employee'),
	);
}