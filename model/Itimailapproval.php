<?php
class Itimailapproval extends ActiveRecord\Model
{
	static $table_name = 'tbl_itimailapproval';
	static $belongs_to = array(
		array('itimail'),
		array("approver")
	);

}