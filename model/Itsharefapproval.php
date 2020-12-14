<?php
class Itsharefapproval extends ActiveRecord\Model
{
	static $table_name = 'tbl_itsharefapproval';
	static $belongs_to = array(
		array('itsharef'),
		array("approver")
	);

}