<?php
//参数列表：无
//返回
//array[]:result
if (! defined("MANAGE_INTERFACE"))
    exit();
    if (!isset($passport)) {exit("param_not_exist");}
    if (!isset($heroname)) {exit("param_not_exist");}
	$ret = sql_fetch_rows("select sch.hid,su.name username,sch.name heroname,sch.state from sys_user su left join sys_city_hero sch on su.uid=sch.uid where su.passport='$passport' and sch.name='$heroname'");
?>