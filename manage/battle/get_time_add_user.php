<?php
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($startday)) {
    exit("param_not_exist");
}
$startday = $startday." 8:00";
$sql = "select hour(jointime) as hourjointime,count(uid) as count from bak_sys_user_battle_state where unix_timestamp(jointime) > unix_timestamp('$startday') and unix_timestamp(jointime) <= unix_timestamp('$startday')+64800 group by hourjointime";
$ret = sql_fetch_rows($sql,'battlenet');
///$ret[]=$sql;
?>