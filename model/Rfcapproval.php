<?php
class Rfcapproval extends ActiveRecord\Model
{
	static $table_name = 'tbl_rfcapproval';
	static $belongs_to = array(
		array('rfc'),
		array("approver")
	);

}