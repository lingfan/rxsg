<?php
if (!defined("MANAGE_INTERFACE"))
    exit;

if (!isset($enable)) {
    exit('params not exists');
}
if (!isset($name)) {
    exit('params not exists');
}

$campaignid = sql_insert("insert into adm_shop_campaign (`enable`,`name`) values ('$enable','$name')");

$campaign_lists = sql_fetch_rows("select * from adm_shop_campaign order by id asc");
$i = 1;
foreach ($campaign_lists as & $row) {
    $start_time = sql_fetch_one_cell("select MIN(FROM_UNIXTIME(start_time)) from adm_shop_sale where `campaign_id`='$row[id]'");
    $end_time = sql_fetch_one_cell("select MAX(FROM_UNIXTIME(end_time)) from adm_shop_sale where `campaign_id`='$row[id]'");
    $row['start_time'] = $start_time;
    $row['end_time'] = $end_time;
    $row['orderid'] = $i++;
}
$ret['campaignid'] = $campaignid;
$ret['campaign_lists'] = $campaign_lists;

?>