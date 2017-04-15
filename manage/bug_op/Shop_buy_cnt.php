<?php
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($passport)) {
    exit("param_not_exist");
}
if (! isset($shopname)) {
    exit("param_not_exist");
}
if (! isset($count)) {
    exit("param_not_exist");
}
$uid = sql_fetch_one_cell("select uid from sys_user where passport='$passport'");
$shopid = sql_fetch_one_cell("select id from cfg_shop where name='$shopname'");
if (empty($uid) || empty($shopid)) {
    $ret = "用户或商品不存在";
} else {
	sql_query("update log_shop_buy_cnt set count = '$count' where uid='$uid' and sid='$shopid' limit 1");
	$ret = "修复成功！";
}
?>