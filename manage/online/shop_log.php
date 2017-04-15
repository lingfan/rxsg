<?php
	//道具记录
	//参数列表：
	//startday:开始日期
	//endday:结束日期
	//返回
	//array[0]:array{goodsname,count,price,totalprice,rate}
	if (!defined("MANAGE_INTERFACE")) exit;
	set_time_limit(0);
	if (!isset($startday)){exit("param_not_exist");}
	if (!isset($endday)){exit("param_not_exist");}
	$sum_props = sql_fetch_one_cell("select sum(price*count) as totalprice from log_shop where time >= unix_timestamp($startday) and time < unix_timestamp($endday)+86400");
	
//	$sum = sql_fetch_rows("select sum(totalprice) as totalprice, type, price, count, count(*) as number 
//	from (select sum(count) as totalprice,type,count as price,count(*) as count,uid 
//	from log_money where count<0 and type !=10 and time >= unix_timestamp($startday) and time < unix_timestamp($endday)+86400 group by type,uid) as p group by p.type");
	
	$sum = sql_fetch_rows("select sum(totalprice) as totalprice, type, price,count(uid) as number,sum(counts) as count
	from (select sum(count) as totalprice,type,count as price,count(uid) as counts,uid
	from log_money where count<0 and type !=10 and type!=54 and type!=76 and time >= unix_timestamp($startday) and time < unix_timestamp($endday)+86400 group by type,uid) as p group by p.type");
	
	$type_54 = sql_fetch_one("select sum(totalprice) as totalprice, type, '0' as price,count(uid) as number,sum(counts) as count
	from (select sum(count) as totalprice,type,count as price,count(uid) as counts,uid
	from log_money where count<0 and type=54 and time >= unix_timestamp($startday) and time < unix_timestamp($endday)+86400 group by type,uid) as p group by p.type");
	
	$type_76 = sql_fetch_one_cell("select sum(count) as totalprice  from log_money where type=76 and time >= unix_timestamp($startday) and time < unix_timestamp($endday)+86400 group by type");
	/**************************历练***************************/
	$sum_expr = sql_fetch_one_cell("
 	select sum(cet.hour_money * se.hours + cae.money) as price
	from log_act_hero_expr as lg 
	left join cfg_hero_expr_types as cet on lg.type=cet.type 
	left join sys_hero_expr as se on se.type=lg.type 
	left join cfg_act_hero_expr as cae on lg.type=cae.type
	where lg.time between unix_timestamp('$startday') and unix_timestamp('$endday')+86400");
	$sql = "
	select cet.name as goodsname,count(*) as count,cet.hour_money as price,sum(cet.hour_money * se.hours + cae.money) as totalprice,sum(cet.hour_money * se.hours + cae.money)/'$sum_expr' as rate,
	count(distinct(lg.uid)) as number
	from log_act_hero_expr as lg 
	left join cfg_hero_expr_types as cet on lg.type=cet.type 
	left join sys_hero_expr as se on se.type=lg.type 
	left join cfg_act_hero_expr as cae on lg.type=cae.type 
	where lg.time between unix_timestamp('$startday') and unix_timestamp('$endday')+86400
	group by lg.type
	";
	$result = sql_fetch_rows($sql);
	/**********************************************************/
	if (!empty($type_76)){
		$type_54['totalprice'] = intval(($type_54['totalprice']-$type_76)*0.1);
	}
	else {
		$type_54['totalprice'] = intval($type_54['totalprice']*0.1);
	}
	$sum[] = $type_54;
	$sumall=$sum_props;
	$other_pay = array();
	foreach($sum as $_sum)
	{
		$sumall =$sumall - $_sum['totalprice'];
	}
	foreach($sum as &$s)
	{
		$s['totalprice'] = -$s['totalprice'];
		$s['price'] = -$s['price'];
		$s['rate'] = $s['totalprice']*100.0/$sumall;
	}
	if(!isset($sumall)||empty($sumall)||$sumall<=0){
		$result1 = array();
		$ret[0]=array_merge($result1,$result);
		
	}else{
		$result1 = sql_fetch_rows("select goodsname,sum(count) as count,price,sum(totalprice) as totalprice,count(uid) as number,sum(rate) as rate
		from (select f.name as goodsname,sum(l.count) as count,l.price as price,sum(l.price*l.count) as totalprice,l.uid as uid,(sum(l.price*l.count)*100.0/".$sumall.") as rate 
		from log_shop l left join cfg_shop f on f.id=l.shopid where l.time >= unix_timestamp($startday) and l.time < unix_timestamp($endday)+86400 group by l.shopid,l.uid) as p group by p.goodsname");
		$ret[0]=array_merge($result1,$result);
		$ret[1] = $sum;
	}
	$ret[] = $result;
?>