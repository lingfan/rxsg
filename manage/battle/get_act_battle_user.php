<?php
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($startday)) {
    exit("param_not_exist");
}
if (! isset($endday)) {
    exit("param_not_exist");
}
if (! isset($streak)) {
    exit("param_not_exist");
}
$sql = "select count(1) as count,a.from_serverid as server from (select count(*) as count,uid from bak_sys_user_battle_state where quittime > unix_timestamp('$startday') and quittime <= unix_timestamp('$endday')+7200 group by uid having count>'$streak') b left join sys_user a on a.uid=b.uid group by a.from_serverid";
$ret = sql_fetch_rows($sql,'battlenet');
?>