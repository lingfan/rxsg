<?php
//用于处理部分玩家战斗结束后部队仍然处于战斗状态，无法回城的情况
//参数列表：无
//返回
//是否正确执行
if (! defined("MANAGE_INTERFACE"))
    exit();
function cid2wid ($cid)
{
    $y = floor($cid / 1000);
    $x = floor($cid % 1000);
    return (floor($y / 10)) * 10000 + (floor($x / 10)) * 100 + floor($y % 10) * 10 + floor($x % 10);
}
$targetcid_list = sql_fetch_rows("select distinct targetcid from sys_troops where state=3 and battleid not in ( select id from mem_battle)");
if (! empty($targetcid_list)) {
    foreach ($targetcid_list as $targetcid) {
        sql_query("update mem_world set state=0 where wid=" . cid2wid($targetcid['targetcid']));
    }
    sql_query("update sys_troops set state=1 where state=3 and battleid not in (select id from mem_battle)");
}
//出城迎战战场丢失
$rows = sql_fetch_rows("select distinct(targetcid) from sys_troops where task = 5 and state = 1 and endtime < unix_timestamp()-86400 and battleid not in (select id from mem_battle )");
if (! empty($rows)) {
    foreach ($rows as $value) {
        sql_query("update mem_world set state=0 where wid=" . cid2wid($value['targetcid']));
    }
    sql_query("update sys_troops set state = 1,task = 1,endtime=unix_timestamp() where state = 1 and task = 5 and endtime < unix_timestamp()-86400 and battleid not in (select id from mem_battle )");
}
$ret['message'] = "卡战斗修复成功！";
?>