<?php
//恢复名将卡城池
//参数列表：无
//返回
//array[]:result
if (! defined("MANAGE_INTERFACE"))
    exit();
function cid2wid ($cid)
{
    $y = floor($cid / 1000);
    $x = ($cid % 1000);
    return (floor($y / 10)) * 10000 + (floor($x / 10)) * 100 + ($y % 10) * 10 + ($x % 10);
}
$hero = sql_fetch_rows("select a.hid,a.cid from sys_city_hero a,sys_city b where a.uid = 0 and a.state in(0,4) and a.npcid>0 and a.cid = b.cid and a.uid !=b.uid");

if (! empty($hero)) {
    foreach ($hero as $value) {
    	$wid = cid2wid($value['cid'])-50;
        $city = sql_fetch_one_cell("select cid from (select (floor(wid/10000)*10+floor((wid%100)/10))*1000+floor((wid%10000)/100)*10+floor(wid%10) as cid from mem_world where ownercid=0 and type > 0 and state=0 and wid >=$wid limit 1000) as b where b.cid not in (select cid from sys_city_hero where uid = 0)");
        sql_query("update sys_city_hero set cid ='$city' where hid = $value[hid] limit 1");
        
    }
}
$ret = "名将恢复成功 ！";
?>