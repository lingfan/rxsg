<?php
if (!defined("MANAGE_INTERFACE"))
	exit();

$crons = array();
$crons = sql_fetch_rows("select * from adm_shop_sale where enable=1 and inuse=1 and end_time < unix_timestamp()");
if (!empty($crons)) {
		
	foreach ($crons as $cron) {
		sql_query("update cfg_shop set price='$cron[price]',onsale='$cron[onsale]',description='$cron[description]',rebate='$cron[rebate]',commend='$cron[commend]',hot='$cron[hot]',totalCount='$cron[totalCount]',userbuycnt='$cron[userbuycnt]',daybuycnt='$cron[daybuycnt]',position='$cron[position]' where id='$cron[operate_sid]'");
		sql_query("update adm_shop_sale set enable=0 where id='$cron[id]'");
	}
}

$crons = array();	
$crons = sql_fetch_rows("select s.* from adm_shop_sale s,adm_shop_campaign c where s.campaign_id = c.id and s.enable=1 and c.enable=1 and s.inuse=0 and s.start_time < unix_timestamp()");
if (!empty($crons)){
	foreach ($crons as $cron) {
		$oldshop = sql_fetch_one("select * from cfg_shop where id='$cron[operate_sid]'");
		sql_query("update cfg_shop set price='$cron[price]',onsale='$cron[onsale]',description='$cron[description]',rebate='$cron[rebate]',commend='$cron[commend]',hot='$cron[hot]',totalCount='$cron[totalCount]',userbuycnt='$cron[userbuycnt]',daybuycnt='$cron[daybuycnt]',position='$cron[position]' where id='$cron[operate_sid]'");
		sql_query("update adm_shop_sale set inuse=1,price='$oldshop[price]',onsale='$oldshop[onsale]',description='$oldshop[description]',rebate='$oldshop[rebate]',commend='$oldshop[commend]',hot='$oldshop[hot]',totalCount='$oldshop[totalCount]',userbuycnt='$oldshop[userbuycnt]',daybuycnt='$oldshop[daybuycnt]',position='$oldshop[position]' where id='$cron[id]'");
	}
}

$ret = 1;

?>