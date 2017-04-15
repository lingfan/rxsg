<?php
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($passport)) {
    exit("param_not_exist");
}
if (! isset($gongxun)) {
    exit("param_not_exist");
}
if (! isset($server)) {
    exit("param_not_exist");
}
$serverid = sql_fetch_one_cell("select from_serverid from sys_servers where server_name='$server'",'battlenet');
$user = sql_fetch_one("select * from sys_user where passport='$passport' and from_serverid='$serverid'",'battlenet');
if (! empty($user) && ! empty($serverid)) {
    sql_query("update sys_user set gongxun = gongxun + '$gongxun' where uid='$user[uid]' limit 1",'battlenet');
    $ret['success'] = $user;
}
else{
	$ret['fail'] = "账号".$passport."不存在或服务器不存在";
}
?>
