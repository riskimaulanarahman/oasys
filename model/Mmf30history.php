<?php
class Mmf30history extends ActiveRecord\Model
{
	static $table_name = 'tbl_mmf30history';
	static $belongs_to = array(
		array('mmf'),
	);
}