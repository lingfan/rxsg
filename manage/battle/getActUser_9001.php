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
$sql = "select count(distinct uid) from bak_sys_user_battle_state where quittime-unix_timestamp('jointime')>1800 and unix_timestamp(jointime) > unix_timestamp('$startday') and unix_timestamp(jointime) <= unix_timestamp('$endday')+3600+86400";
$ret = sql_fetch_one_cell($sql,'battlenet_9001');
?>