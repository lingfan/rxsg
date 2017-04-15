<?php
/**
 * @author 张昌彪
 * @模块 公告管理 -- 浮动公告
 * @功能 获得当前游戏服务器上的浮动公告列表
 * @返回 array 
 *       浮动公告列表
 */
    if (!defined("MANAGE_INTERFACE")) exit;
    
    $ret = sql_fetch_rows("select `id`,`type`,`inuse`,FROM_UNIXTIME(starttime) as starttime,FROM_UNIXTIME(endtime) as endtime,`interval`,`scrollcount`,`color`,`msg` from sys_inform where color <> '49151'order by id desc limit 50");

?>
