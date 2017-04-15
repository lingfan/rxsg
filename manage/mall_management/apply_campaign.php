<?php

	if (!defined("MANAGE_INTERFACE")) exit;
 	if (!isset($data_list)){exit("param_not_exist");}
    $output_str = '';
    $ret[] = array();
    foreach($data_list as $lists){
	    if(!empty($lists['delete'])){
	        sql_query("delete from adm_shop_campaign where `id`='$id'");
	    }else{
	        sql_query("update adm_shop_campaign set enable='$lists[enable]',`name`='$lists[name]' where `id`='$lists[id]'");
	        if(!empty($lists['day']) || !empty($lists['hour'])){
	            $unix_time = $lists['day']*86400 + $lists['hour']*3600;
	            sql_query("update adm_shop_sale set start_time=start_time+$unix_time,end_time=end_time+$unix_time where `campaign_id`='$lists[id]'");
	        }
	    }
        if($lists['choosed']){
            $ret[] = sql_fetch_rows("select * from adm_shop_sale where `campaign_id`='$lists[id]'");
        }else{
        	$ret[] = array();
        }
    }        
?>