<?php
//幸运宝盒使用统计
//参数列表：
//startday:开始日期
//endday:结束日期
//返回
//array[0]:array{day,money}
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($startday)) {
    exit("param_not_exist");
}
if (! isset($endday)) {
    exit("param_not_exist");
}
if (! empty($uid)) {
    $ret[0] = sql_fetch_rows("select su.passport,su.name,cg.name as gname,llt.* from log_lottery llt left join sys_user su on su.uid=llt.uid left join cfg_goods cg on cg.gid=llt.gid where llt.uid='$uid' and llt.`time` between '$startday' and '$endday' and llt.type=0");
	$ret[1] = sql_fetch_rows("select su.passport,su.name,cg.name as gname,llt.* from log_lottery llt left join sys_user su on su.uid=llt.uid left join cfg_armor cg on cg.id=llt.gid where llt.uid='$uid' and llt.`time` between '$startday' and '$endday' and llt.type=1");
}
?>