<?php
class Dayoffapproval extends ActiveRecord\Model
{
	static $table_name = 'tbl_dayoffapproval';
	static $belongs_to = array(
		array('dayoff'),
		array("approver")
	);

}