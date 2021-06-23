<?php
class Advexpenseapproval extends ActiveRecord\Model
{
	static $table_name = 'tbl_advexpenseapproval';
	static $belongs_to = array(
		array('advexpense'),
		array("approver")
	);

}