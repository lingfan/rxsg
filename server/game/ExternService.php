<?php
require_once ("interface.php");
class ExternService {
function saveEnemyTroopSet($cid,$actions){
	$targetbalefireLevel = sql_fetch_one_cell("select level from sys_building where cid='".$cid."' and bid=".ID_BUILDING_BALEFIRE." limit 1");
	if ((empty($targetbalefireLevel)) || $targetbalefireLevel < 5){
		throw new Exception($GLOBALS['enemyfilter']['firelevelhow']);
	}
	foreach ($actions as $action){
		$act=$action['action'];
		$cnt=$action['count'];
		sql_query("insert into sys_enemy_troop_filter(cid,action,`count`)values('$cid','$act','$cnt') on duplicate key update `count`='$cnt'");
	}
	$uid=sql_fetch_one_cell("select uid from sys_city where cid='$cid'");
	sql_query("replace into sys_user_goal (`uid`,`gid`) values ('$uid',539)");
}
function getEnemyTroopSet($cid){
	return sql_fetch_rows("select * from `sys_enemy_troop_filter` where cid='$cid'");
}
function loadUpdateHistory(){
	return sql_fetch_rows("select id,time,version,title from cfg_update_history order by id desc");
}
function loadUpdateDetail($id){
	return sql_fetch_one("select * from cfg_update_history where id='$id' order by id desc limit 1");
}
function loadSpecialCitys(){
	$citysql="select sys_user.uid, sys_city.cid,sys_city.name,cfg_soldier.name as soldierName,sys_user.name as uname from cfg_soldier_special_city, sys_city,sys_user,cfg_soldier where sys_city.cid=cfg_soldier_special_city.cid and sys_city.uid=sys_user.uid and cfg_soldier.sid=cfg_soldier_special_city.sid limit 100";
	$citys=sql_fetch_rows($citysql);
	$uids="";
	foreach ($citys as $city){
		if($uids==""){
			$uids.=$citys['uid'];
		}else{
			$uids.=",".$citys['uid'];
		}
	}
	$unionsql="select sys_user.uid,sys_union.name from sys_user,sys_union where sys_user.union_id=sys_union.id";
	$unions=sql_fetch_simple_map($unionsql,"uid","name");
	foreach ($citys as &$city){
		$uid=$city['uid'];
		$city['position']=getPosition($city['cid']);
		if(empty($unions[$uid])){
			$city['union']="";
		}else{
			$city['union']=$unions[$uid];
		}
	}
	return $citys;
	//$city=array(name=>"地狱",uname=>"上帝",union=>"联合国",position=>"[2,3]",people=>"123");
	//return array($city,);
}
function getTroopMaxTime($x,$y) {
	//判断一下x，y位置是不是npc，如果是把时间设置最大10分钟
	
	return ExternService::getTroopMaxTime2($x,$y);
}
static function getTroopMaxTime2($x,$y) {
	//判断一下x，y位置是不是npc，如果是把时间设置最大10分钟
	if(ExternService::between('2011-06-11 19:00:00','2011-06-11 23:00:00')||ExternService::between('2011-06-12 19:00:00','2011-06-12 23:00:00')){
		
	}else{
		return 0;
	}
	
	
	
	$wid=(floor($y / 10)) * 10000 + (floor($x / 10)) * 100 + ($y % 10) * 10 + ($x % 10);
	$cid=sql_fetch_one_cell("select ownercid from mem_world where wid='".$wid."'");
	if($cid){
		$uid=sql_fetch_one_cell("select uid from sys_city where cid='".$cid."'");
		if($uid>1000){
			return 900;//10分钟
		}else{
			return 0;
		}
	}else{
		return 0;
	}
}
	static function between($begintime,$endtime){
		  $nowtime=time();
		  $begintimet=sql_fetch_one_cell("select unix_timestamp('$begintime');");//strtotime($begintime,$nowtime);
		  $endtimet=sql_fetch_one_cell("select unix_timestamp('$endtime');");//strtotime($endtime,$nowtime);
		if($nowtime>=$begintimet&&$nowtime<=$endtimet)  
			return true;
		return false;
	}

}

?>