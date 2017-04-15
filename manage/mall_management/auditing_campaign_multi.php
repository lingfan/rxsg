<?php
//同步档期
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

//$step=100000;


$list_no_delete1 = array();
$list_no_delete2 = array();
foreach ($adm_shop_campaign as $campaign_list) {
	//$id = $campaign_list['campaign_id']+$step;
	$id = $campaign_list['campaign_id'];
	$isindb = sql_fetch_one_cell("select id from adm_shop_campaign where id='$id'");
	if (empty($isindb)) {
		sql_query("insert into adm_shop_campaign (`id`, `enable`, `name`) values ('$id' ,'$campaign_list[enable]' ,'$campaign_list[name]')");
	}
	else {
		sql_query("update adm_shop_campaign set `enable`='$campaign_list[enable]',`name`='$campaign_list[name]' where `id`='$id'");
	}
	$list_no_delete1[] = '\'' . $id . '\'';
}
$delete1 = implode(',', $list_no_delete1);
sql_query("delete from adm_shop_campaign where `id` not in ($delete1)");

foreach ($adm_shop_sale as $sale_list1) {
	foreach ($sale_list1 as $sale_list) {
		
		//$id2 = $sale_list['id']+$step;
		//$id1 = $sale_list['campaign_id']+$step;		

		$id2 = $sale_list['id'];
		$id1 = $sale_list['campaign_id'];
		
		$isindb2 = sql_fetch_one_cell("select id from adm_shop_sale where id='$id2'");
		if (empty($isindb2)) {
			sql_query("insert into adm_shop_sale (`id`, `enable`, `operate_type`, `operate_sid`,
					 `start_time`, `end_time`, `campaign_id`, `description`, `position`, `price`, `rebate`, `commend`, 
					 `hot`, `totalCount`, `userbuycnt`, `daybuycnt`, `onsale`) values ('$id2','$sale_list[enable]',
					 '$sale_list[operate_type]','$sale_list[operate_sid]','$sale_list[start_time]',
					 '$sale_list[end_time]','$id1','$sale_list[description]','$sale_list[position]',
					 '$sale_list[price]','$sale_list[rebate]','$sale_list[commend]','$sale_list[hot]',
					 '$sale_list[totalCount]','$sale_list[userbuycnt]','$sale_list[daybuycnt]','$sale_list[onsale]')");
		}
		else {
			sql_query("update adm_shop_sale set `operate_type` = '$sale_list[operate_type]',
				 `operate_sid` = '$sale_list[operate_sid]', `start_time` = '$sale_list[start_time]', `end_time` = '$sale_list[end_time]',
				 `description` = '$sale_list[description]', `price` = '$sale_list[price]', `rebate` = '$sale_list[rebate]',
				 `campaign_id` = '$id1',`commend` = '$sale_list[commend]', `hot` = '$sale_list[hot]', `totalCount` = '$sale_list[totalCount]', 
				 `userbuycnt` = '$sale_list[userbuycnt]', `daybuycnt` = '$sale_list[daybuycnt]', `onsale` = '$sale_list[onsale]' 
				 where `id`='$id2'");
		}
		$list_no_delete2[] = '\'' . $id2 . '\'';
	}
}
$delete2 = implode(',', $list_no_delete2);
sql_query("delete from adm_shop_sale where `id` not in ($delete2)");

$ret = 1;

?>