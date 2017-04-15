<?php
/**
 * @author 张昌彪
 * @模块 查询查看 -- 战场荣誉勋章
 * @功能 按战场获得的荣誉和勋章搜索
 * @参数 
 * 		 startday
 * 		 endday
 * 		 type 搜索条件 array('荣誉','平定黄巾勋章','汉室勋章','曹军官渡勋章','袁军官渡勋章');
 * @返回 
 * 用户 拥有量 从高到低
 * 
 * 
 */
if (!defined("MANAGE_INTERFACE"))
    exit;

if (!isset($startday))
    exit("param_not_exist");
if (!isset($endday))
    exit("param_not_exist");
if (!isset($type))
    exit("param_not_exist");

if($type=='0')
{
    $today = sql_fetch_rows("select * from (select u.uid,u.name,u.passport,l.honour as count,l.starttime from log_battle_honour l,sys_user u where l.starttime>unix_timestamp('$startday') and l.starttime<unix_timestamp('$endday')+86400 and u.uid = l.uid order by l.starttime desc  ) as a group by uid");
    foreach($today as &$row)
    {
        $uid = $row['uid'];
        $last_honour = sql_fetch_one_cell("select honour from log_battle_honour where uid = $uid and starttime<unix_timestamp('$startday') order by starttime desc limit 1");
        $today_honour = sql_fetch_one_cell("select honour from log_battle_honour where uid = $uid and starttime<unix_timestamp('$endday')+86400 order by starttime desc limit 1");
        $row['count'] = $today_honour - $last_honour;
    }
    $ret[] = $today;
}else
{
    $unionid = $type;
    $ret[] = sql_fetch_rows("select u.name,u.passport,sum(l.metal) as count from log_battle_honour l,sys_user u where l.starttime>unix_timestamp('$startday') and l.starttime<unix_timestamp('$endday')+86400 and u.uid = l.uid and l.unionid = '$unionid'  group by l.uid order by count desc");
}

if(mysql_error())
{
    $ret['mysql_error'] = mysql_error();
}

    
    

?>