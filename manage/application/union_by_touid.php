<?php

	if (!defined("MANAGE_INTERFACE")) exit;
	
	if (!isset($touid)){exit("param_not_exist");}
//	if (!isset($union_name)){exit("param_not_exist");}

	$ret = sql_fetch_one("select * from sys_union where id='$touid'");
/*	$u_name = $union['name'];
    $goods_name = sql_fetch_one_cell("select `name` from cfg_goods where `gid`='$gid'");
    if(!empty($union_name)){
    	sql_query("update sys_union set `name`='$union_name' where `id`='$touid'"); 
        $u_name = $union_name; 
    }
    $ret = $u_name;*/
?>