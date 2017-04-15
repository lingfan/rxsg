<?php
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($startday)) {
    exit("param_not_exist");
}
$startday = $startday." 08:00";
$sql = "select count(distinct uid) from bak_sys_user_battle_state where unix_timestamp(jointime) > unix_timestamp('$startday') and unix_timestamp(jointime) <= unix_timestamp('$startday')+64800";
$ret[1] = sql_fetch_one_cell($sql,'battlenet_9001');
$sql = "select count(distinct uid) from bak_sys_user_battle_state where sent_troop_count> 0 and unix_timestamp(jointime) > unix_timestamp('$startday') and unix_timestamp(jointime) <= unix_timestamp('$startday')+64800";
$ret[2] = sql_fetch_one_cell($sql,'battlenet_9001');
$sql = "select hour(jointime) as hourjointime,count(distinct uid) as count from bak_sys_user_battle_state where unix_timestamp(jointime) > unix_timestamp('$startday') and unix_timestamp(jointime) <= unix_timestamp('$startday')+64800 group by hourjointime";
$ret[3] = sql_fetch_rows($sql,'battlenet_9001');
$ret[4]=sql_fetch_one_cell("select left('$startday',11)");
?>