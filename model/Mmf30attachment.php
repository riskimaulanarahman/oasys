<?php
class Mmf30attachment extends ActiveRecord\Model
{
	static $table_name = 'tbl_mmf30attachment';
	static $belongs_to = array(
		array('mmf30'),
	);
}