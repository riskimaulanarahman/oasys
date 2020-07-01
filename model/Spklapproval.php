<?php
class Spklapproval extends ActiveRecord\Model
{
	static $table_name = 'tbl_spklapproval';
	static $belongs_to = array(
		array('spkl'),
		array("approver")
	);

}