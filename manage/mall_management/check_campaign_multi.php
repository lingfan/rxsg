<?php
//查看档期
//参数列表：档期ID
//
//正确执行返回服务器档期列表$ret
if (!defined("MANAGE_INTERFACE"))
	exit();
if (!isset($campaign_id)) {
	exit("param_not_exist");
}

$campaign_lists = sql_fetch_one("select * from adm_shop_campaign where id='$campaign_id'");
if (!empty($campaign_lists)) {
	$campaign_lists['start_time'] = sql_fetch_one_cell("select MIN(FROM_UNIXTIME(start_time)) from adm_shop_sale where `campaign_id`='$campaign_id'");
	$campaign_lists['end_time'] = sql_fetch_one_cell("select MAX(FROM_UNIXTIME(end_time)) from adm_shop_sale where `campaign_id`='$campaign_id'");
}
$ret = $campaign_lists;
?> 