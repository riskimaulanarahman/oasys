<?php
class Detailleave extends ActiveRecord\Model
{
	static $table_name = 'tbl_detailleave';
	static $belongs_to = array(
		array('leave'),
	);
}