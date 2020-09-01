<?php
class Mmfattachment extends ActiveRecord\Model
{
	static $table_name = 'tbl_mmf28attachment';
	static $belongs_to = array(
		array('mmf'),
	);
}