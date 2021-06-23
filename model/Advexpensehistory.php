<?php
class Advexpensehistory extends ActiveRecord\Model
{
	static $table_name = 'tbl_advexpensehistory';
	static $belongs_to = array(
		array('advexpense'),
	);
}