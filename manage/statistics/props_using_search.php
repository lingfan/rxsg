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
	$shopinfo = sql_fetch_one("select id,price,gid from cfg_shop where name = '$props'");
	
	if(!empty($shopinfo)){
		
		$result = sql_fetch_rows("select daytime,count(countuid) as usernums, sum(sumcount)*(-1) as sumcount 
		from(SELECT from_unixtime(time,'%Y-%m-%d') as daytime ,count(uid) as countuid,sum(`count`) as sumcount  
		from log_goods where `type`=0  and  gid='$shopinfo[gid]' and time >= unix_timestamp($startday) and 
		time < unix_timestamp($endday)+86400 group by daytime,uid) as a group by daytime ");
		if (empty($result)){
			$ret = array();
		}
		else {
			$ret[]=$shopinfo['price'];
			$ret[]=$result;
		}
	}
	else {
		$ret = array();
	}
	
?>