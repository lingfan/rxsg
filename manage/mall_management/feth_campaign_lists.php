<?php

	if (!defined("MANAGE_INTERFACE")) exit;

    $campaign_lists = sql_fetch_rows("select * from adm_shop_campaign order by id asc");
    $i = 1;
    foreach($campaign_lists as &$row){
    	$start_time = sql_fetch_one_cell("select MIN(FROM_UNIXTIME(start_time)) from adm_shop_sale where `campaign_id`='$row[id]'");               
    	$end_time = sql_fetch_one_cell("select MAX(FROM_UNIXTIME(end_time)) from adm_shop_sale where `campaign_id`='$row[id]'");               
        $row['start_time'] = $start_time;
        $row['end_time'] = $end_time;
        $row['orderid'] = $i++;
	}
	$ret = $campaign_lists;

?>