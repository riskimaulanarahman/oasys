<?php
class Spkldetail extends ActiveRecord\Model
{
	static $table_name = 'tbl_spkldetail';
	static $belongs_to = array(
		array('spkl'),
		array('employee'),
	);
}