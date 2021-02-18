<?php
class Advanceapproval extends ActiveRecord\Model
{
	static $table_name = 'tbl_advanceapproval';
	static $belongs_to = array(
		array('advance'),
		array("approver")
	);

}