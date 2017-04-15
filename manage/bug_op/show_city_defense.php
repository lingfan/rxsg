<?php
//查询城防建筑卡0秒
//参数列表：无
//返回
//array[]:result
if (! defined("MANAGE_INTERFACE"))
    exit();
    $ret = sql_fetch_rows("select sc.name,cd.name as dname,from_unixtime(state_starttime) as `time` from sys_city_reinforcequeue as scr left join cfg_defence as cd on cd.did = scr.did left join sys_city as sc on sc.cid=scr.cid  where scr.state=1 and scr.id not in (select id from mem_city_reinforce)");
?>