<?php
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($officepos))exit("param_not_exist");

    $ret = sql_fetch_one_cell("select name from cfg_office_pos where id='$officepos'");
    if(empty($ret))$ret = 'no data';
?>