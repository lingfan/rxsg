<?php
//查询在野名将不存在，用于解决市井传闻查到武将在某个野地，占领该野地却发现武将不存在的问题
//参数列表：武将名
//返回
//array[]:

if (!defined("MANAGE_INTERFACE")) exit;
if (!isset($heroname)) {exit("param_not_exist");}
$ret = sql_fetch_rows("select h.hid,h.uid,h.state,h.name,c.name as cityname,u.name as username from sys_city_hero h left join sys_city c on c.cid=h.cid left join sys_user u on u.uid=h.uid where h.uid<1000 and h.hid<1026 and h.name='$heroname'");


?>