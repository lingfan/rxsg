<?php
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($startday)) {
    exit("startday param_not_exist");
}
if (! isset($endday)) {
    exit("endday param_not_exist");
}
if (! isset($times)) {
    exit("times param_not_exist");
}
$sql = "select count(1) count from (select uid,count(*) count from  bak_sys_user_battle_state where unix_timestamp(jointime) < unix_timestamp('$endday') and unix_timestamp(jointime) >= unix_timestamp('$startday') group by uid having count>'$times') a";
$ret = sql_fetch_one_cell($sql,'battlenet');
?>
