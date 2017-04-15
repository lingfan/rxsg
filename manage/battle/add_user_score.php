<?php
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($passport)) {
    exit("param_not_exist");
}
if (! isset($score)) {
    exit("param_not_exist");
}
$uid = sql_fetch_one_cell("select uid from sys_user where passport='$passport'",'battlenet');
if (! empty($uid)) {
    sql_query("update sys_user set battle_score = battle_score + '$score' where passport = '$passport' limit 1",'battlenet');
    $ret = "已为" . $passport . "玩家增加" . $score . "的积分";
}
if(empty($ret)){
	$ret = "账号".$passport."不存在";
}
?>
