<?php
//设置开启的活动战场列表
//参数列表：无
//返回开启的战场
//array[]:result
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($battle_act)) {
    exit("param_not_exist");
}
if (! isset($select)) {
	exit("param_not_exist");
}
if(!empty($select)){
	foreach ($select as $value){
	sql_query("delete from cfg_act_battle where actid=$value[delete]");
}
}
if (! empty($battle_act)) {
    $actid = sql_fetch_one_cell("select max(actid) from  cfg_act_battle");
    $actid = $actid +1;
    sql_query("insert into cfg_act_battle(actid,date,actname,acttime,starthour,endhour,rate,state) values($actid,unix_timestamp('$battle_act[startdate]'),'$battle_act[actname]','$battle_act[acttime]','$battle_act[start_hour]','$battle_act[end_hour]','$battle_act[rate]','$battle_act[state]')");
    if($battle_act[state] != 1){
		$bid = sql_fetch_one_cell("select id from cfg_battle_field where name= '$battle_act[actname]'");
		$id = sql_fetch_one_cell("select max(id) from  cfg_act_battle_details");
		$id=$id+1;
		sql_query("insert into cfg_act_battle_details(id,actid,bid) values ($id,$actid,$bid)");
		
	}
}
?>