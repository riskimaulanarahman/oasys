<?php
class Advexpensedetailbt extends ActiveRecord\Model
{
	static $table_name = 'tbl_advexpensedetail_bt';
	static $belongs_to = array(
		array('advexpense'),
		array('employee'),
	);
}