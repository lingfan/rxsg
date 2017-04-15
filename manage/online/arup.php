<?php
/**
 * @author 张昌彪
 * @模块 运营数据 -- Arup值
 * @功能 获得当前游戏服务器上Arup值的五个参数
 * array[0] 充值额
 * array[1] 服务器的建号人数
 * array[2] 活跃人数服务器的活跃人数（声望30000以上）
 * array[3] A（A=上线次数超过3次的玩家数）
 * array[4] 付费人数
 */
	if (!defined("MANAGE_INTERFACE")) exit;
if(isset($year)&&isset($month)&&$year>0&&$month>0)
{
    $startday = $year*10000+$month*100+1;
    if($month=='12')
    {
        $endday = ($year+1)*10000+101;
    }
    else
    {
        $endday = $year*10000+$month*100+101;
    }
    $ret['st'] = $startday;
    $ret['en'] = $endday;
    $ret[0] = sql_fetch_one_cell("select sum(money) from pay_log where money>0 and money<500000 and time>unix_timestamp('$startday') and time<unix_timestamp('$endday')");
	$ret[1] = sql_fetch_one_cell("select count(*) from sys_user where uid>1000 and regtime>unix_timestamp('$startday') and regtime<unix_timestamp('$endday')");
	$ret[2] = sql_fetch_one_cell("select count(*) from sys_user where prestige>30000 and uid>1000 and regtime>unix_timestamp('$startday') and regtime<unix_timestamp('$endday')");
	$ret[3] = sql_fetch_one_cell("select count(*) from (select count(uid) as count from log_login  where time>unix_timestamp('$startday') and time<unix_timestamp('$endday') group by uid) as p where count>3");
	$ret[4] = sql_fetch_one_cell("select count(*) from (select passport from pay_log where time>unix_timestamp('$startday') and time<unix_timestamp('$endday') group by passport) as p");
}
else
{
    $ret[0] = sql_fetch_one_cell("select sum(money) from pay_log where money>0 and money<500000");
	$ret[1] = sql_fetch_one_cell("select count(*) from sys_user where uid>1000");
	$ret[2] = sql_fetch_one_cell("select count(*) from sys_user where prestige>30000 and uid>1000");
	$ret[3] = sql_fetch_one_cell("select count(*) from (select count(uid) as count from log_login group by uid) as p where count>3");
	$ret[4] = sql_fetch_one_cell("select count(*) from (select passport from pay_log group by passport) as p");
}
	
?>