<?php
//修复卡交易0秒
//参数列表：无
//返回
//array[]:result
if (!defined("MANAGE_INTERFACE"))
	exit();
$ret = sql_fetch_rows("select sc.name,sct.restype,from_unixtime(endtime) as `time` from sys_city_trade as sct left join sys_city as sc on sc.cid=sct.buycid where sct.state=1 and sct.id not in (select id from mem_city_trade)");
?>