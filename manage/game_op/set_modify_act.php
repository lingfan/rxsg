<?php
//获得要修改的活动战场数据
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($battle_act)) {
    exit("param_not_exist");
}
//$ret = sql_fetch_rows("select * from cfg_act_battle where actid=$actid limit 1");
sql_query("update cfg_act_battle set date = unix_timestamp('$battle_act[startdate]') ,actname = '$battle_act[actname]',acttime='$battle_act[acttime]',starthour=$battle_act[start_hour],endhour=$battle_act[end_hour],rate=$battle_act[rate],state=$battle_act[state] where actid=$battle_act[actid]");
if($battle_act[state] != 1){
		$bid = sql_fetch_one_cell("select id from cfg_battle_field where name= '$battle_act[actname]'");
		sql_query("update cfg_act_battle_details set bid=$bid where actid=$battle_act[actid]");		
	}
?>