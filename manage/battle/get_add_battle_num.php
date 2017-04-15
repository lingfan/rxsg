<?php
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($startday)) {
    exit("param_not_exist");
}
if (! isset($endday)) {
    exit("param_not_exist");
}
$startday = $startday." 8:00";
$endday = $endday." 2:00";
$sql = "select count(unew.uid) as `count`,from_unixtime(unew.regtime,'%Y-%m-%d') as day from (select uid,from_serverid,regtime from sys_user where regtime > unix_timestamp('$startday') and regtime <= unix_timestamp('$endday') and state = 0) unew left join (select uid,from_serverid from sys_user where regtime < unix_timestamp('$startday') and state =0) uold on unew.uid=uold.uid where uold.uid is null group by day";
$ret = sql_fetch_rows($sql,'battlenet');
?>
