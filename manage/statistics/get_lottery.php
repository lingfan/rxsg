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
if (! empty($passport)) {
    $ret = sql_fetch_rows("select su.uid,su.passport,su.name,count(*) as `count` from log_lottery llt left join sys_user su on su.uid=llt.uid where su.passport = '$passport' and llt.`time` between '$startday' and '$endday' group by llt.uid");
}else{
	$ret = sql_fetch_rows("select su.uid,su.passport,su.name,count(*) as `count` from log_lottery llt left join sys_user su on su.uid=llt.uid where llt.`time` between '$startday' and '$endday' group by llt.uid");
}
?>