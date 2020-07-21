<?php
class Mmf30approval extends ActiveRecord\Model
{
	static $table_name = 'tbl_mmf30approval';
	static $belongs_to = array(
		array('mmf'),
		array("approver")
	);

}