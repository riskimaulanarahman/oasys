<?php
class Iteieapproval extends ActiveRecord\Model
{
	static $table_name = 'tbl_iteieapproval';
	static $belongs_to = array(
		array('iteie'),
		array("approver")
	);

}