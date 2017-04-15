<?php
/**
 * @author 张昌彪
 * @模块 查询查看 -- 战场开启场次
 * @功能 当前服务器战场开启场次搜索
 * @参数 
 * 		 startday
 * 		 endday
 * 		 search_name
 * 		 passport
 * @返回 
 * 日期 
 * 黄巾之乱 开启场次
 * 官渡之战 开启场次
 * 
 */
if (!defined("MANAGE_INTERFACE"))
    exit;

if (!isset($startday))
    exit("param_not_exist");
if (!isset($endday))
    exit("param_not_exist");
$ret[] = sql_fetch_rows("select day,count(battlefieldid) as count_battle,battleid from ( select from_unixtime(starttime,'%Y-%m-%d') as day,battleid,battlefieldid from log_battle_honour where starttime>unix_timestamp('$startday') and starttime<unix_timestamp('$endday')+86400 group by day,battlefieldid) as p group by day,battleid");
if(mysql_error())
{
    $ret['mysql_error'] = mysql_error();
}


?>