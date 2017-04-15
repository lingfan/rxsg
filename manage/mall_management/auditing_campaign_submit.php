<?php
//提交时同步档期
//参数列表：
//adm_shop_campaign 档期信息
//adm_shop_sale档期内容
//正确执行返回1
if (!defined("MANAGE_INTERFACE"))
	exit();
if (!isset($adm_shop_campaign)) {
	exit("param_not_exist");
}
if (!isset($adm_shop_sale)) {
	exit("param_not_exist");
}
if (!empty($adm_shop_campaign)) {
	$id = $adm_shop_campaign['id'];
	sql_query("insert into adm_shop_campaign (id,enable,name) values ($id,1,'$adm_shop_campaign[name]') on duplicate key update name='$adm_shop_campaign[name]'");
	$crons = sql_fetch_rows("select * from adm_shop_sale where enable=1 and inuse=1 and campaign_id=$id");
	foreach ($crons as $cron) {
		sql_query("update cfg_shop set price='$cron[price]',onsale='$cron[onsale]',description='$cron[description]',
			rebate='$cron[rebate]',commend='$cron[commend]',hot='$cron[hot]',totalCount='$cron[totalCount]',
			userbuycnt='$cron[userbuycnt]',daybuycnt='$cron[daybuycnt]',position='$cron[position]' where id='$cron[operate_sid]'");
	}
	sql_query("delete from adm_shop_sale where campaign_id=$id");
	if (!empty($adm_shop_sale)) {
		foreach ($adm_shop_sale as $sale_list) {
			$id2 = $sale_list['id'];
			if ($sale_list['end_time'] < time()) {
				$enable = 0;
				$inuse = 1;
			}
			else {
				$enable = 1;
				$inuse = 0;
			}
			sql_query("insert into adm_shop_sale (id,enable,operate_type,operate_sid,start_time,end_time,campaign_id,description,
				`position`,price,rebate,`commend`,hot,totalCount,userbuycnt,daybuycnt,onsale,inuse) values ($id2,$enable,
				'$sale_list[operate_type]',$sale_list[operate_sid],$sale_list[start_time],$sale_list[end_time],$id,
				'$sale_list[description]',$sale_list[position],$sale_list[price],$sale_list[rebate],$sale_list[commend],
				$sale_list[hot],$sale_list[totalCount],$sale_list[userbuycnt],$sale_list[daybuycnt],$sale_list[onsale],$inuse)");
		}
	}
	$crons = array();
	$crons = sql_fetch_rows("select * from adm_shop_sale where enable=1 and inuse=1 and end_time < unix_timestamp()");
	if (!empty($crons)) {
		foreach ($crons as $cron) {
			sql_query("update cfg_shop set price='$cron[price]',onsale='$cron[onsale]',description='$cron[description]',rebate='$cron[rebate]',
				commend='$cron[commend]',hot='$cron[hot]',totalCount='$cron[totalCount]',userbuycnt='$cron[userbuycnt]',daybuycnt='$cron[daybuycnt]',
				position='$cron[position]' where id='$cron[operate_sid]'");
			sql_query("update adm_shop_sale set enable=0 where id='$cron[id]'");
		}
	}
	$crons = array();
	$crons = sql_fetch_rows("select s.* from adm_shop_sale s,adm_shop_campaign c where s.campaign_id = c.id and s.enable=1 and c.enable=1 and s.inuse=0 and s.start_time < unix_timestamp()");
	if (!empty($crons)) {
		foreach ($crons as $cron) {
			$oldshop = sql_fetch_one("select * from cfg_shop where id='$cron[operate_sid]'");
			sql_query("update cfg_shop set price='$cron[price]',onsale='$cron[onsale]',description='$cron[description]',rebate='$cron[rebate]',
				commend='$cron[commend]',hot='$cron[hot]',totalCount='$cron[totalCount]',userbuycnt='$cron[userbuycnt]',daybuycnt='$cron[daybuycnt]',
				`position`='$cron[position]' where id='$cron[operate_sid]'");
			sql_query("update adm_shop_sale set inuse=1,price='$oldshop[price]',onsale='$oldshop[onsale]',description='$oldshop[description]',
				rebate='$oldshop[rebate]',commend='$oldshop[commend]',hot='$oldshop[hot]',totalCount='$oldshop[totalCount]',userbuycnt='$oldshop[userbuycnt]',
				daybuycnt='$oldshop[daybuycnt]',`position`='$oldshop[position]' where id='$cron[id]'");
		}
	}
}
$ret = 1;
?>