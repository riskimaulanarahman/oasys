<?php
class Spklattachment extends ActiveRecord\Model
{
	static $table_name = 'tbl_spklattachment';
	static $belongs_to = array(
		array('spkl'),
	);
}