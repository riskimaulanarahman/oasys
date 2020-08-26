<?php
class Mmf30detail extends ActiveRecord\Model
{
	static $table_name = 'tbl_mmf30detail';
	static $belongs_to = array(
		array('mmf30')
	);

}