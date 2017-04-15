<?php
	//道具记录
	//参数列表：
	//startday:开始日期
	//endday:结束日期
	//返回
	//array[0]:array{goodsname,count,price,totalprice,rate}
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($startday)){exit("param_not_exist");}
	if (!isset($endday)){exit("param_not_exist");}
	if (!isset($props)){exit("param_not_exist");}
	$shopid = sql_fetch_one_cell("select id from cfg_shop where name = '$props'");
	if(empty($shopid))
	{
		$typeid = sql_fetch_one_cell("select id from log_money_type where name = '$props'");
		if(empty($typeid))
		{
			$ret = array();
			
		}
		else
		{
			$sum = sql_fetch_rows("select day,sum(salenum)*(-1) as totalprice,price*(-1) as price,count(uid) as number,sum(counts) as count 
			from (select from_unixtime((floor(time/86400)*86400),'%Y-%m-%d') as day,uid ,count as price,sum(count) as salenum,count(uid) as counts 
			from log_money where count<0 and type !=10 and time >= unix_timestamp($startday) 
			and time < unix_timestamp($endday)+86400 and type = $typeid group by day,uid) as p group by day");
			
			//$sum = sql_fetch_rows("select day,sum(totalprice) as totalprice,sum(count) as number,price,sum(count) as count from (select sum(count) as totalprice,count as price,count(uid) as count,from_unixtime((floor(time/86400)*86400),'%Y-%m-%d') as day from log_money where count<0 and type !=10 and time >= unix_timestamp($startday) and time < unix_timestamp($endday)+86400 and type = $typeid group by uid,day) as p group by day");
			$sumall = 0;
			foreach($sum as $_sum)
			{
				$sumall += $_sum['totalprice'];
			}
			foreach($sum as &$s)
			{
				//$s['totalprice'] = -$s['totalprice'];
				//$s['price'] = -$s['price'];
				$s['rate'] = $s['totalprice']*100.0/$sumall;
			}
			$ret = $sum;
		}
		
	}
	else
	{
		$sumall = sql_fetch_one_cell("select sum(price*count) as totalprice from log_shop where time >= unix_timestamp($startday) and time < unix_timestamp($endday)+86400 and shopid = $shopid");
		if(!isset($sumall)||empty($sumall)||$sumall<=0){
			$ret = array();
			
		}else{
			$ret = sql_fetch_rows("select day,sum(count) as count,price,sum(totalprice) as totalprice,count(uid) as number,sum(rate) as rate from (select sum(count) as count, price,sum(price*count) as totalprice,uid,(sum(price*count)*100.0/".$sumall.") as rate,from_unixtime((floor(time/86400)*86400),'%Y-%m-%d') as day from log_shop where time >= unix_timestamp($startday) and time < unix_timestamp($endday)+86400 and shopid = $shopid group by day,uid) as p group by p.day");
		}
	}
?>