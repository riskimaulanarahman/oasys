<?php
class Trapproval extends ActiveRecord\Model
{
	static $table_name = 'tbl_trapproval';
	static $belongs_to = array(
		array('tr'),
		array("approver")
	);

}