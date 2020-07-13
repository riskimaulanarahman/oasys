<?php
class Spkltmsapproval extends ActiveRecord\Model
{
	static $table_name = 'tbl_spklotapproval';
	static $belongs_to = array(
		array('spkl'),
		array("approver")
	);
}