<?php

	if (!defined("MANAGE_INTERFACE")) exit;
 	if (!isset($campaign_name)){exit("param_not_exist");}
 	if (!isset($campaign_contents)){exit("param_not_exist");}

    $camp = sql_fetch_one("select * from adm_shop_campaign where `name`='$campaign_name'");
    if(!empty($camp)){
        $camp_id = $camp['id'];
    }else{
        $camp_id = sql_insert("insert into adm_shop_campaign (`name`) values ('$campaign_name')");
    }
    foreach($campaign_contents as &$list){
        sql_query("insert into adm_shop_sale (`operate_sid`,`campaign_id`,`enable`,`onsale`,`operate_type`,`price`,`rebate`,`commend`,`hot`,`totalCount`,`userbuycnt`,`daybuycnt`,`description`,`start_time`,`end_time`) values ('$list[operate_sid]','$camp_id','$list[enable]','$list[onsale]','$list[operate_type]','$list[price]','$list[rebate]','$list[commend]','$list[hot]','$list[totalCount]','$list[userbuycnt]','$list[daybuycnt]','$list[description]','$list[start_time]','$list[end_time]')");
    }
?>