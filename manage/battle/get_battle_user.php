<?php
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($startday)) {
    exit("param_not_exist");
}
if (! isset($endday)) {
    exit("param_not_exist");
}
$ret = sql_fetch_rows("select count(distinct uid) as count,from_serverid as server,b.day from sys_user a left join (select distinct uid,left(jointime,11) day from bak_sys_user_battle_state where unix_timestamp(jointime) > unix_timestamp('$startday') and unix_timestamp(jointime) <= unix_timestamp('$endday')+86400) b on a.uid=b.uid group by a.from_serverid",'battlenet');
?>
