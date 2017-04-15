<?php
//卡指挥的处理
//参数：无
//返回：是否正确执行
if (!defined("MANAGE_INTERFACE"))
	exit();

function cid2wid($cid) {

	$y = floor($cid / 1000);
	$x = ($cid % 1000);
	return (floor($y / 10)) * 10000 + (floor($x / 10)) * 100 + ($y % 10) * 10 + ($x % 10);
}
//先把这些部队置为返回状态
sql_query("update  sys_troops set state=1 where task<=6 and state=3 and battleid not in (select id from mem_battle)");
//把战场攻击的设置为驻守状态
sql_query("update  sys_troops set state=4 where task=9 and state=3 and battleid not in (select id from mem_battle)");
//选出战斗
$result = sql_fetch_rows("select distinct(targetcid) from sys_troops where state=3 and battleid not in (select id from mem_battle)");
//把战斗的野地设置为和平状态	
foreach ($result as $done_result) {
	sql_query("update  mem_world set state=0 where state=1 and wid=" . cid2wid($done_result));
}

$ret = "修复成功";
?>