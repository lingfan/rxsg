<?php
if (!defined("MANAGE_INTERFACE"))
	exit();
if (!isset($info)) {exit("param_not_exist");}
if ($info['search_type']=='accuracy'){
	if (!empty($info['name'])){
		$user_list = sql_fetch_rows("select uid,name,passport from sys_user where name='$info[name]'");
	}
	else {
		$user_list = sql_fetch_rows("select uid,name,passport from sys_user where name='$info[passport]'");
	}
}
elseif ($info['search_type']=='blur'){
	if (!empty($info['name'])){
		$user_list = sql_fetch_rows("select uid,name,passport from sys_user where name like '%$info[name]%'");
	}
	else {
		$user_list = sql_fetch_rows("select uid,name,passport from sys_user where name='%$info[passport]%'");
	}
}
$ret =$user_list ;
?>