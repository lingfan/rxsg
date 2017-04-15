<?php
/**
 * @author 张昌彪
 * @模块 查询查看 -- 战场查询 -- 战场玩家搜索
 * @功能 通过玩家游戏名或账号模糊或者精确查询用户详细的战场信息
 * @参数 
 * 		 startday
 * 		 endday
 * 		 search_name
 * 		 passport
 * @返回 
 * 日期 黄巾之乱
 * 参加场次	官渡之战
 * 参加场次	战场荣誉
 * 汉室勋章
 * 平定黄巾勋章
 * 袁军官渡勋章
 * 曹军官渡勋章
 * 
 */
if (!defined("MANAGE_INTERFACE"))
    exit;

if (!isset($startday))
    exit("param_not_exist");
if (!isset($endday))
    exit("param_not_exist");
if (!isset($search_name))
    exit("param_not_exist");
if (!isset($passport))
    exit("param_not_exist");

if (!empty($search_name)) {
    $user = sql_fetch_one("select passport,name,uid from sys_user where name = '$search_name' and uid>1000");
    if (empty($user)) {
        $ret['error'] = '找不到该用户';
    } else {
        $uid = $user['uid'];
        $ret[] = $user;
        $ret[] = sql_fetch_rows("select from_unixtime(starttime,'%Y-%m-%d') as day,battleid,count(battleid) as count,honour as sumhonour, sum(metal) as summetal,unionid from (select * from log_battle_honour where uid = '$uid' and starttime > unix_timestamp('$startday') and starttime < unix_timestamp('$endday')+86400 order by starttime desc) as a group by day,battleid,unionid");
        
        $ret['lasthonour'] = sql_fetch_one_cell("select honour from log_battle_honour where uid = $uid and starttime<unix_timestamp('$startday') order by starttime desc limit 1");
    }
} elseif (!empty($passport)) {
    $user = sql_fetch_one("select passport,name,uid from sys_user where passport = '$passport' and uid>1000");
    if (empty($user)) {
        $ret['error'] = '找不到该用户';
    } else {
        $uid = $user['uid'];
        $ret[]=$user;
        $ret[] = sql_fetch_rows("select from_unixtime(starttime,'%Y-%m-%d') as day,battleid,count(battleid) as count,max(honour) as sumhonour, sum(metal) as summetal,unionid from log_battle_honour where uid = '$uid' and starttime > unix_timestamp($startday) and starttime < unix_timestamp($endday)+86400 group by day,battleid,unionid");
        $ret['lasthonour'] = sql_fetch_one_cell("select honour from log_battle_honour where uid = $uid and starttime<unix_timestamp('$startday') order by starttime desc limit 1");
    }
}
else
{
    $ret['error'] = '没有通行证或玩家名';
}
if(mysql_error())
{
    $ret['mysql_error'] = mysql_error();
}

?>