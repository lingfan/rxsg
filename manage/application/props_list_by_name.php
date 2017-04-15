<?php

	if (!defined("MANAGE_INTERFACE")) exit;
	
	if (!isset($name)){exit("param_not_exist");}

	$ret['data'] = sql_fetch_rows("select g.uid as uid,g.count as count,u.name as uname,u.passport as passport,c.name as gname from sys_goods g,sys_user u,cfg_goods c where u.name='$name' and g.gid=c.gid and u.uid=g.uid ");
	if(empty($ret['data']))$ret['message'] = 'no result';
?>