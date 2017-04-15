<?php
/**
 * @author 张昌彪
 * @模块 运营数据 -- 新建号用户流失
 * @功能 获得当前游戏服务器上新建号用户流失
 * @返回 每天注册人数中在一周后的新建号用户流失
 * array(array(day,conversion_rate)...)
 */
if (!defined("MANAGE_INTERFACE"))
    exit;

if (!isset($startday)) {
    exit("param_not_exist");
}
if (!isset($endday)) {
    exit("param_not_exist");
}
//先获取每天的时间表
$day_reg_list = sql_fetch_rows("select from_unixtime(regtime,'%Y-%m-%d') as day from sys_user where regtime>=unix_timestamp('$startday') and regtime<unix_timestamp('$endday')+86400 group by day");
if(empty($day_reg_list))
{
    $ret = array();
}
foreach($day_reg_list as $row)
{
    $day = $row['day'];
    #$reg_user_count = sql_fetch_one_cell("select count(uid) from (select uid from sys_user where from_unixtime(regtime,'%Y%m%d')='$day' group by uid) p");
    $pay_user_count = sql_fetch_one_cell("select count(distinct u.uid) 
    				  from sys_user u 
    				  where from_unixtime(u.regtime,'%Y-%m-%d') = '$day' 
    	     		  and not exists (select * from log_login p 
    								  where p.uid=u.uid and time>=unix_timestamp('$day')+86400*7)");
    #$day_time = sql_fetch_one_cell("select from_unixtime(unix_timestamp('$day'),'%Y-%m-%d')");
    #$rate = round(100*$pay_user_count/$reg_user_count,2);
    $ret[]=array('day'=>$day,'data'=>$pay_user_count);
}


?>