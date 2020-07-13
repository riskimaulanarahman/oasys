<?php
class Spkltmshistory extends ActiveRecord\Model
{
	static $table_name = 'tbl_spklothistory';
	static $belongs_to = array(
		array('spkl'),
	);
}