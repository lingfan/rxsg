<?php
if (!defined("MANAGE_INTERFACE"))
	exit();
if (!isset($campaigns)) {
	exit("param_not_exist");
}
if (!isset($sales)) {
	exit("param_not_exist");
}
sql_query("update cfg_shop as s,(select * from adm_shop_sale where enable=1 and inuse=1) as b set s.price=b.price,s.onsale=b.onsale,
	s.totalCount=b.totalCount,s.userbuycnt=b.userbuycnt,s.daybuycnt=b.daybuycnt,s.rebate=b.rebate,s.commend=b.commend,s.hot=b.hot,
	s.description=b.description,s.`position`=b.`position` where s.id=b.operate_sid");
if (empty($campaigns)) {
	sql_query("delete from adm_shop_campaign");
	sql_query("delete from adm_shop_sale");
}
else {
	$campaign_arr = array();
	foreach ($campaigns as $campaign) {
		$campaign_arr[] = $campaign['id'];
		sql_query("insert into adm_shop_campaign (id,enable,name) values ($campaign[id],1,'$campaign[name]') on duplicate key update name='$campaign[name]'");
	}
	$campaign_enable = implode(',', $campaign_arr);
	sql_query("delete from adm_shop_campaign where id not in ($campaign_enable)");
	if (empty($sales)) {
		sql_query("delete from adm_shop_sale");
	}
	else {
		$sale_arr = array();
		foreach ($sales as $sale) {
			$sale_arr[] = $sale['id'];
			if ($sale['end_time'] < time()) {
				$enable = 0;
				$inuse = 1;
			}
			else {
				$enable = 1;
				$inuse = 0;
			}
			sql_query("insert into adm_shop_sale (id,enable,operate_type,operate_sid,start_time,end_time,campaign_id,description,`position`,price,rebate,
				commend,hot,totalCount,userbuycnt,daybuycnt,onsale,inuse) values ($sale[id],$enable,'$sale[operate_type]',$sale[operate_sid],$sale[start_time],
				$sale[end_time],$sale[campaign_id],'$sale[description]',$sale[position],$sale[price],$sale[rebate],$sale[commend],$sale[hot],$sale[totalCount],
				$sale[userbuycnt],$sale[daybuycnt],$sale[onsale],$inuse) on duplicate key update operate_type='$sale[operate_type]',operate_sid=$sale[operate_sid],
				daybuycnt=$sale[daybuycnt],start_time=$sale[start_time],end_time=$sale[end_time],rebate=$sale[rebate],onsale=$sale[onsale],price=$sale[price],
				`position`=$sale[position],commend=$sale[commend],description='$sale[description]',totalCount=$sale[totalCount],userbuycnt=$sale[userbuycnt],
				campaign_id=$sale[campaign_id],hot=$sale[hot],enable=$enable,inuse=$inuse");
		}
		$sale_enable = implode(',', $sale_arr);
		sql_query("delete from adm_shop_sale where id not in ($sale_enable)");
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
}
$ret = 1;
?>