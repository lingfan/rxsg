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
$serverid = sql_fetch_one_cell("select from_serverid from sys_servers where server_name='$server'",'battlenet_9001');
$user = sql_fetch_one("select * from sys_user where passport='$passport' and from_serverid='$serverid'",'battlenet_9001');
if (! empty($user) && ! empty($serverid)) {
    sql_query("update sys_user_battle_score set gongxun = gongxun + '$gongxun' where uid = '$user[uid]' limit 1",'battlenet_9001');
    $ret['success'] = $user;
}
else{
	$ret['fail'] = "账号".$passport."不存在或服务器不存在";
}
?>
