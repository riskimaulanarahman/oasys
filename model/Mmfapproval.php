<?php
class Mmfapproval extends ActiveRecord\Model
{
	static $table_name = 'tbl_mmf28approval';
	static $belongs_to = array(
		array('mmf'),
		array("approver")
	);

}