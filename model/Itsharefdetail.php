<?php
class Itsharefdetail extends ActiveRecord\Model
{
	static $table_name = 'tbl_itsharefdetail';
	static $belongs_to = array(
		array('itsharef')
	);

}