<?php
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($startday)) {
    exit("param_not_exist");
}
if (! isset($endday)) {
    exit("param_not_exist");
}
$startday1=$startday." 8:00";
//$sql = "select count(*) as count,'$startday' as day from sys_user_battle_field where starttime > unix_timestamp('$startday1') and starttime <= unix_timestamp('$startday1')+64800 group by day";
$sql = "select count(*) as count,left(from_unixtime(starttime),11) as day from sys_user_battle_field where starttime > unix_timestamp('$startday') and starttime <= unix_timestamp('$endday')+64800 group by day";
$ret = sql_fetch_rows($sql,'battlenet');
?>
