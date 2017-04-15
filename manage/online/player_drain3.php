<?php
//充值用户流失
//返回
if (!defined("MANAGE_INTERFACE"))
    exit;

if (!isset($startday)) {
    exit('params_not_exit;');
}
//计算开服起每周的充值人数uid列表

$uids_all = sql_fetch_rows("select from_unixtime(floor(l.time/86400/7)*86400*7,'%Y-%m-%d') as week,u.uid as uid from pay_log as l,sys_user as u where l.passport = u.passport and l.time>floor(unix_timestamp($startday)/86400/7)*86400*7 group by uid,week order by week");

if (empty($uids_all))
    exit;
foreach ($uids_all as $uid_row) {
    $uid_list[$uid_row['week']][] = $uid_row['uid'];

}
foreach ($uid_list as $week => $list) {
    $uids = implode(',', $list);
    $drain_list = sql_fetch_rows("select count(uid) as count,week from (select uid,from_unixtime((floor(time/86400/7)*86400*7),'%y-%m-%d') as week from log_login where uid in ($uids) and time>unix_timestamp($startday) group by week,uid) as p where p.week>=$week group by week");
    $ret[] = $drain_list;
}
?>