<?php
/**
 * @inform 运营接口 -- 及时数据 -- 在线人数
 * @author 张昌彪
 * @param null
 * @return array(today_online,today_online_max)
 * @example  
 */
if (!defined("MANAGE_INTERFACE"))
    exit;
try {
    //参数判断

    if (isset($day) && !empty($day)) //如果有某一天的值传递，就返回当天的最高在线
    {
        if (!isset($distance) || empty($distance)) {
            $distance = 24;
        }
        $spacetime = 86400 / $distance;
        for ($i = 0; $i < $distance; $i++) {
            $result = sql_fetch_one_cell("select count(0) AS `count(*)` from `sys_online` where ($day + $spacetime*($i+1)- `lastupdate`) < 60 ");
            if (empty($result)){$result=0;}
            $cur_online[] = $result;
        }

        $last_max_online = sql_fetch_one_cell("select max(online) from log_online where time < $day and time >=$day-86400",
            'bloodwarlog');
        if (mysql_error()) {
            throw new Exception(mysql_error());
        }
        if (empty($last_max_online)){$last_max_online=0;}
        $ret['content']['last_max_online'] = $last_max_online;
        $ret['content']['cur_online'] = $cur_online;
    } else { //如果没有就传递当天的在线及时数据
        $day = time();
        $cur_online = sql_fetch_one_cell("select count(0) AS `count(*)` from `sys_online` where ((unix_timestamp() - `lastupdate`) < 60) ");
        $last_max_online = sql_fetch_one_cell("select max(online) from log_online where time < $day and time >$day-86400",
            'bloodwarlog');

        if (mysql_error()) {
            throw new Exception(mysql_error());
        }
        if (empty($cur_online)){$cur_online=0;}
        if (empty($last_max_online)){$last_max_online=0;}
        $ret['content']['cur_online'] = $cur_online;
        $ret['content']['last_max_online'] = $last_max_online;
    }
}
catch (exception $e) {
    $ret['error'] = $e->getMessage();
}






?>