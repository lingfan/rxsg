<?php
	//查看档期内容
	//参数列表：
	//campaign_id档期ID
	//返回档期内容
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($campaign_id))  exit('params not exists');

	$campaign_contents = sql_fetch_rows("select s.id as id,s.enable as enable,s.onsale as onsale,s.price as price,s.rebate as rebate,
		        s.commend as commend,s.hot as hot,s.operate_type as operate_type,FROM_UNIXTIME(s.start_time) as start_time,s.start_time as start_time2,
		        FROM_UNIXTIME(s.end_time) as end_time,s.end_time as end_time2,s.totalCount as totalCount,s.userbuycnt as userbuycnt,
		        s.daybuycnt as daybuycnt,s.description as description,c.name as name,c.oriprice as oriprice 
		        from adm_shop_sale s left join cfg_shop c on (s.operate_sid=c.id) where `campaign_id`='$campaign_id'");

	$campaign_name=sql_fetch_one_cell("select name from adm_shop_campaign where id='$campaign_id'");
	
	$ret[]=$campaign_contents;
	$ret[]=$campaign_name;

?>