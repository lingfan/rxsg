<?php
	//道具消耗统计
	//参数列表：
	//startday:开始日期
	//endday:结束日期
	//返回
	//array[0]:array{goodsname,count,price,totalprice,rate}
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($startday)){exit("param_not_exist");}
	if (!isset($endday)){exit("param_not_exist");}
	$sum_props = sql_fetch_one_cell("select sum(price*count) as totalprice from log_shop where time >= unix_timestamp($startday) and time < unix_timestamp($endday)+86400");
	$sum = sql_fetch_rows("select sum(totalprice) as totalprice, type, price, count, count(*) as number from (select sum(count) as totalprice,type,count as price,count(*) as count,uid from log_money where count<0 and type !=10 and time >= unix_timestamp($startday) and time < unix_timestamp($endday)+86400 group by type,uid) as p group by p.type");
	$sumall=$sum_props;
	foreach($sum as $_sum)
	{
		$sumall =$sumall - $_sum['totalprice'];
	}
	//总共消耗道具的元宝数
	//$sumall = sql_fetch_one_cell("select sum(c.value*g.good) as sumall from (select sum(count) as good,gid from log_goods where type=0 and  time >= unix_timestamp($startday) and time < unix_timestamp($endday)+86400 group by gid) as g,cfg_goods c where c.gid = g.gid ");
	if(!empty($sumall))
	{
		$ret = sql_fetch_rows("select c.name as goodsname,count(uid) as number,c.value as price,sum(c.value*g.good) as totalprice,sum(g.good) as count,(sum(c.value*g.good)/$sumall) as rate  from (select sum(count) as good,gid,uid from log_goods where type=0 and time >= unix_timestamp($startday) and time < unix_timestamp($endday)+86400  group by uid,gid) as g,cfg_goods c where c.gid = g.gid group by g.gid order by count asc");
	}
	else
	{
		$ret = array();
		//$ret = array('goodsname'=>0,'count'=>0,'price'=>0,'totalprice'=>0,'rate'=>0);
	}

?>