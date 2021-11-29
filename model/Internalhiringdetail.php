<?php
class Internalhiringdetail extends ActiveRecord\Model
{
	static $table_name = 'tbl_internalhiringdetail';
	static $has_many = array(
		array('internalhiring')
	);
}