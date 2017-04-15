<?php
/**
 * @author 方鸿鹏
 * @method 跨服战场-玩家赤壁道具信息（赤壁）
 * @param $startday 开始时间 $endday 结束时间  $name 君主名 $passport账号
 * @return 
 */

if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($startday)) {
    exit("param_not_exist");
}
if (! isset($endday)) {
    exit("param_not_exist");
}
if (! isset($server)) {
    exit("param_not_exist");
}
if (! isset($passport)) {
    exit("param_not_exist");
}
$from_serverid = sql_fetch_one_cell("select from_serverid from sys_servers where server_name='$server'",'battlenet_9001');
$sql_error = mysql_error();
if(!empty($from_serverid)&&empty($sql_error)){
	$sql = "select u.name as uname,u.passport,cg.name as gname,lg.count,from_unixtime(lg.time) as time,lg.type 
			from log_goods lg,sys_user u,cfg_goods cg 
			where lg.uid=u.uid and lg.gid=cg.gid and u.passport='$passport' and u.from_serverid='$from_serverid' 
			and lg.time>=unix_timestamp('$startday') and lg.time<unix_timestamp('$endday')+86400 and lg.gid>=161001 and lg.gid<=161009
			order by lg.gid";
	$props_list = sql_fetch_rows($sql,'battlenet_9001');
	$sql_error = mysql_error();
	if(!empty($props_list)&&empty($sql_error)){
		$ret = $props_list;
	}
	else{
		$ret='no data';
	}
}
else{
	$ret='no data';
}
?>