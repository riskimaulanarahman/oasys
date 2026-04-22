<?php
class Spkl extends ActiveRecord\Model
{
	static $table_name = 'tbl_spkl';
	static $belongs_to = array(
		array('employee')
	);
	static $has_many = array(
		array('spkldetail'),
		array('spklattachment')
	);
}