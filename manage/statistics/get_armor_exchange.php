<?php
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($startday)) {
    exit("param_not_exist");
}
if (! isset($endday)) {
    exit("param_not_exist");
}
$sql = "select sum(count) as count,from_unixtime(time,'%Y-%m-%d') as day from log_goods where gid>31000 and gid<31015 and time>unix_timestamp('$startday') and time<=unix_timestamp('$endday') group by day";
$ret = sql_fetch_rows($sql);
?>