<?php
//参数列表：无
//返回
//array[]:result
if (! defined( "MANAGE_INTERFACE" ))
	exit();
if (! isset( $passport )) {
	exit( "param_not_exist" );
}
if (! isset( $name )) {
	exit( "param_not_exist" );
}
$uid = sql_fetch_one_cell( "select uid from sys_user where passport='$passport'" );
if (! empty( $uid )) {
	$hid = sql_fetch_one_cell( "select hid from sys_city_hero where name='$name' and uid='$uid'" );
}
if (! empty( $hid )) {
	sql_query( "delete from sys_hero_armor where hid='$hid' and sid not in (select * from sys_user_armor) limit 16" );
	$ret = "恢复成功 ！";
}else{
	$ret = "不需要修复";
}
?>