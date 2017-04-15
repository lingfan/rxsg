<?php
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($startday)) {
    exit("param_not_exist");
}
$startday=$startday." 8:00";
$sql = "select count(*) as count,hour(from_unixtime(starttime)) as day from sys_user_battle_field where starttime > unix_timestamp('$startday') and starttime < unix_timestamp('$startday')+64800 group by day";
$ret = sql_fetch_rows($sql,'battlenet');
?>
