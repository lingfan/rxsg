<?php
	//每日充值
	//参数列表：
	//startday:开始日期
	//endday:结束日期
	//返回
	//array[0]:array{day,money}
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($startday)){exit("param_not_exist");}
	if (!isset($endday)){exit("param_not_exist");}
	
	//$ret[0] = sql_fetch_rows("select from_unixtime(day,'%Y-%m-%d') as day,money from pay_day_money where day >= unix_timestamp($startday) and day < unix_timestamp($endday)+86400");
	//$ret = sql_fetch_rows("select count(passport) as count,day,sum(money) as money from ( select passport,from_unixtime(time,'%Y-%m-%d') as day,sum(money) as money  from pay_log where time >= unix_timestamp($startday) and time < unix_timestamp($endday)+86400 group by passport) as p group by day");
	$ret = sql_fetch_rows("select count(passport) as count, sum(money) as money,day from (select sum(money) as money,passport,day from (select passport, from_unixtime(time,'%Y-%m-%d') as day, money  from pay_log where time >= unix_timestamp($startday) and time < unix_timestamp($endday)+86400 order by day,passport) as q group by q.day,q.passport) as p group by day");
/*	$list = sql_fetch_rows("select passport,from_unixtime(time,'%Y-%m-%d') as day, money  from pay_log where time >= unix_timestamp($startday) and time < unix_timestamp($endday)+86400 order by passport,day");
	$count = 0;
		$money = 0;
		if(!empty($list))
		{
			foreach($list as $_list)
			{
				$day = isset($day)?$day:$_list['day'];//echo $_list['day'];
				$passport = isset($passport)?$passport:$_list['passport'];
				if($day==$_list['day'])
				{
					$count = ($passport==$_list['passport'])?$count:($count+1);
					$passport = $_list['passport'];
					$money += $_list['money'];
				}
				else
				{
					$data[]=array('day'=>$day,'count'=>$count,'money'=>$money);
					$count = 0;
					$day = $_list['day'];
					$money = 0;
				}
			}$data[]=array('day'=>$day,'count'=>$count,'money'=>$money);
		}
		else
		{
			$data = array();
		}
		$ret = $data;
		*/
?>