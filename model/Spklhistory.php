<?php
class Spklhistory extends ActiveRecord\Model
{
	static $table_name = 'tbl_spklhistory';
	static $belongs_to = array(
		array('spkl'),
	);
}