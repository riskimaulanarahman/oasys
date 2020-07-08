<?php
class Mmfhistory extends ActiveRecord\Model
{
	static $table_name = 'tbl_mmf28history';
	static $belongs_to = array(
		array('mmf'),
	);
}