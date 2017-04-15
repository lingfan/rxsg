<?php
require_once("./interface.php");
require_once("./utils.php");
require_once("BattleNetFunc.php");
require_once("ChibiNetFunc.php");

function doGetUnionTroops($uid,$cid)
{
	$ret = array();
	$ret[] = sql_fetch_one_cell("select n.name from sys_user u left join sys_union n on n.id=u.union_id where u.uid='$uid'");
	$ret[] = sql_fetch_rows("select t.*,t.uid as userid,u.name as username,h.name as hero,h.level as herolevel from sys_user u,sys_troops t left join sys_city_hero h on h.hid=t.hid where u.uid=t.uid and t.targetcid=$cid and t.task=1 and t.state=4");
	return $ret;
}

function kickTroop($uid,$param)
{
	$troopid = intval(array_shift($param));
	$troop=sql_fetch_one("select * from sys_troops where id='$troopid' and task=1 and state=4");
	if (empty($troop))
	{
		throw new Exception($GLOBALS['kickUnionTroop']['army_not_exist']);
	}
	sql_query("update sys_troops set `state`=1,endtime=pathtime+unix_timestamp() where id='$troopid'");
	updateCityResourceAdd($troop['targetcid']);
	return array();
}

function allowUnionTroop($uid,$cid,$param)
{
	$allow=intval(array_shift($param));
	sql_query("insert into sys_allow_union_troop (uid,`allow`) values ('$uid','$allow') on duplicate key update `allow`='$allow'");
	$ret=array();
	$ret[]=getAllowUnionTroop($uid,$cid);
	return $ret;
}
function allowAntiPlunder($uid,$cid,$param)
{
	$allow=intval(array_shift($param));
	sql_query("insert into sys_allow_union_troop (uid,`anti_plunder`) values ('$uid','$allow') on duplicate key update `anti_plunder`='$allow'");
	$ret=array();
	$ret[]=getAllowAntiPlunder($uid,$cid);
	return $ret;
}
function allowAntiInvade($uid,$cid,$param)
{
	$allow=intval(array_shift($param));
	sql_query("insert into sys_allow_union_troop (uid,`anti_invade`) values ('$uid','$allow') on duplicate key update `anti_invade`='$allow'");
	$ret=array();
	$ret[]=getAllowAntiInvade($uid,$cid);
	return $ret;
}
function getBattleNetTroops($uid,$crossBid)
{
	if ($crossBid==6001) {
		return sendRemoteRequest($uid,"getBattleNetTroops");
	}else{
		return sendRemote9001Request($uid,"getBattleNetTroops");
	}
}
function getChibiNetTroops($uid)
{
	return sendChibiRemoteRequest($uid,"getChibiNetTroops");
}
function getArmyTroops($uid,$param)
{
	$sign = array_shift($param);//合法标志
	if (empty($sign)) {//没有合法标志，则判定为外挂用户
		$ip = $GLOBALS['sip'];//外挂用户登录ip
		logWaigUserInfo($uid, $ip);//记录外挂用户信息
	}
	sql_query("update sys_alarm set troops=0 where uid='$uid'");
	$troops=sql_fetch_rows("select t.*,c.name as fromcity from sys_troops t left join sys_city c on c.cid=t.cid where t.uid='$uid' and t.state<4");
	
	 
	$battlenetTroops=array();
	if(defined("BATTLE_NET_ENABLE") && BATTLE_NET_ENABLE){
		$crossBid = sql_fetch_one_cell("select bid from sys_user_battle_state where uid=$uid and in_cross_battle=1");
		if($crossBid>0) {
			//如果在跨服战场中，从远程取出
			$battlenetTroops=getBattleNetTroops($uid,$crossBid);
		}
	}
	$chibinetTroops=array();
	if(defined("CHIBI_NET_ENABLE") && CHIBI_NET_ENABLE){
		if(sql_check("select 1 from sys_user_chibi_state where uid=$uid ")) {
			//如果在跨服战场中，从远程取出
			$chibinetTroops=getChibiNetTroops($uid);
		}
	}
	foreach($troops as &$troop){
		$troop['resource']="";
		$troop['soldier']="";
		if($troop['task']==7){
			//派遣到某个战场
			$xy=$troop['targetcid']%1000;
			$bid=$troop['bid'];

			$troop['targetcity']=sql_fetch_one_cell("select name from cfg_battle_city where bid=$bid and xy=$xy");

		}else if($troop['task']==8){
			//派遣到某个战场

			$troop['targetcity']=sql_fetch_one_cell("select name from sys_battle_city where cid=".$troop['targetcid']);

			$troop['fromcity']=sql_fetch_one_cell("select name from sys_battle_city where cid=".$troop['cid']);
		}else if($troop['task']==9){
			//派遣到某个战场
			$troop['targetcity']=sql_fetch_one_cell("select name from sys_battle_city where cid=".$troop['targetcid']);
			$troop['fromcity']=sql_fetch_one_cell("select name from sys_battle_city where cid=".$troop['cid']);
		}else{
			$troop['wtype']=sql_fetch_one_cell("select type from mem_world where `wid`=".cid2wid($troop['targetcid']));
			if($troop['wtype']==0)
			{
				$troop["targetcity"] = sql_fetch_one_cell("select name from sys_city where cid=".$troop['targetcid']);
			}
		}

		$troop['userid']=$troop['uid'];
		$troop['inBattleNet']=0;
	}
	$shachangtroop=getShaChangeTroop($uid);
	$luoyangTroop=getLuoyangTroop($uid);
	return array_merge($troops,$shachangtroop,$battlenetTroops,$chibinetTroops,$luoyangTroop);
}
function getLuoyangTroop($uid)
{
	$luoYangTroops = sql_fetch_rows("select * from sys_luoyang_troops where uid='$uid' and (targetcid='215265' or state='1')");
	$luoyangTroopInfo = array();
	foreach($luoYangTroops as $luoYangTroop)
	{
		$targetCid = intval($luoYangTroop['targetcid']);
		if($targetCid!=215265){
			$luoYangTroop['fromcity'] = sql_fetch_one_cell("select name from cfg_luoyang_city_add where cid={$luoYangTroop['cid']}");
			$luoYangTroop['targetcity'] = sql_fetch_one_cell("select name from cfg_luoyang_city_add where cid={$luoYangTroop['targetcid']}");
			$luoYangTroop['task']=1;
			$luoYangTroop['state']=3;
			$luoYangTroop['userid']=$luoYangTroop['uid'];
			$luoyangTroopInfo[] = $luoYangTroop;
		}else{
			$luoYangTroop['fromcity'] = sql_fetch_one_cell("select name from sys_city where cid={$luoYangTroop['cid']}");
			$luoYangTroop['targetcity'] = sql_fetch_one_cell("select name from sys_city where cid='215265'");
			$luoYangTroop['task']=1;
			$luoYangTroop['state']=0;
			$luoYangTroop['userid']=$luoYangTroop['uid'];
			$luoyangTroopInfo[] = $luoYangTroop;
		}		
	}
	return $luoyangTroopInfo;
}
function getShaChangeTroop($uid){
	$state=sql_fetch_one_cell("select state from sys_sc_waiting_queue where uid=$uid");
	if($state===false){
		return array();
	}else{
//		$troop=sql_fetch_one("select *  from sys_city  where state<4 limit 1");
		$troop=sql_fetch_one("select t.*,c.name as fromcity from sys_troops t left join sys_city c on c.cid=t.cid where  t.state<4 limit 1");
		$stroop=sql_fetch_one("select * from sys_sc_troops where uid=$uid");
		$nextstarttime=sql_fetch_one_cell("select value from mem_state where state=28");
		$starttime=time();
		$pathtime=$nextstarttime-$starttime;
		if($pathtime<0){
			$pathtime=0;
		}
		$troop['id']=$stroop['id'];
		$troop['uid']=$stroop['uid'];
		$troop['hid']=$stroop['hid'];
		$troop['soldiers']=$stroop['soldiers'];
		$troop['battleid']=$stroop['battleid'];
		$troop['arrive_time']=$nextstarttime;
		$troop['endtime']=$nextstarttime;
		$troop['starttime']=$starttime;
		$troop['pathtime']=$pathtime;
		$troop['state']=0;
		$troop['task']=1;
		$hid=$troop['hid'];
		$city=sql_fetch_one("select name,cid from sys_city where cid in (select cid from sys_city_hero where hid=$hid)");
//		$city=sql_fetch_one("select * from sys_battle_city where cid=(select cid from sys_city_hero where hid='$hid')");
		$troop['targetcity']=$GLOBALS['shachange']['enter_5'];
		$troop['fromcity']=$city['name'];
		$troop['cid']=$city['cid'];
		$troop['targetcid']=$troop['cid'];
		$troop['userid']=$troop['uid'];
		$troop['inBattleNet']=0;
		$troop['battlefieldid']=10001;
		if($state==1){
			$troop['state']=3;
		}
		$troops=array();
		$troops[0]=$troop;
		return $troops;
//		sql_query ( "update sys_troops set cid=$cid, soldiers='$soldiers',hid='$hid',`arrive_time`=$nextstarttime,`endtime`=$nextstarttime, `starttime`=$starttime,`pathtime`=$pathtime,`state`=0,`task`=1  where uid='$uid'" );
	}
}

function getEnemyTroops($uid,$param)
{
	$sign = array_shift($param);//合法标志
	if (empty($sign)) {//没有合法标志，则判定为外挂用户
		$ip = $GLOBALS['sip'];//外挂用户登录ip
		logWaigUserInfo($uid, $ip);//记录外挂用户信息
	}
	sql_query("update sys_alarm set enemy=0 where uid='$uid'");
	$troops1 = sql_fetch_rows("select t.*,t.targetcid as targetownercid,c.name as targetcity from sys_troops t,sys_city c where t.targetcid=c.cid and c.uid='$uid' and t.uid <> '$uid' and t.state<4 and t.task in (2,3,4)");
	$cidStr = sql_fetch_one_cell("select group_concat(cid) from sys_city where uid='$uid'");
	if (empty($cidStr)||$cidStr=="")$ownerfields=array();
	else $ownerfields=sql_fetch_rows("select wid from mem_world where ownercid in (".$cidStr.") and type>0");
	if(!empty($ownerfields))
	{
		$comma="";
		foreach($ownerfields as $mywid)
		{
			$fieldcids.=$comma;
			$fieldcids.=wid2cid($mywid['wid']);
			$comma=",";
		}
		$troops2 = sql_fetch_rows("select * from sys_troops where uid <> '$uid' and state<4 and task in (2,3,4) and targetcid in ($fieldcids)");
		foreach($troops2 as &$troop)
		{
			$worldinfo=sql_fetch_one("select type,ownercid from mem_world where `wid`=".cid2wid($troop['targetcid']));
			$troop['wtype']=$worldinfo['type'];
			$troop['targetownercid']=$worldinfo['ownercid'];
		}
	}
	$allfilters=array();
//	$troopfilter=
	foreach($troops1 as $key=>&$troop)
	{
		$troop['wtype']=0;
		$task=$troop['task'];
		$cid=$troop['targetcid'];
		if(empty($allfilters[$cid])){
			$targetbalefireLevel = sql_fetch_one_cell("select level from sys_building where cid='".$cid."' and bid=".ID_BUILDING_BALEFIRE." limit 1");
			if ((empty($targetbalefireLevel)) && $targetbalefireLevel < 5){
				$allfilters[$cid]=false;
			}else{
				$allfilters[$cid]=sql_fetch_simple_map("select action,`count` from sys_enemy_troop_filter where cid='$cid'",'action','count');
			}
		}
		$troopfilter=$allfilters[$cid];
		if(!empty($troopfilter)&&!empty($troopfilter[$task])){//设置了警报过滤
			$soldiercount=getSoldierCount($troop['soldiers']);
			if($troopfilter[$task]>$soldiercount){//出征兵太少，不显示
				//array_slice($troops1,$index,1);
				unset($troops1[$key]);
			}
		}
	}
	if(!empty($troops2)) $troops = array_merge($troops1,$troops2);
	else $troops=array_merge($troops1);
	if(count($troops)==0)
	{
		return $troops;
	}
	foreach($troops as &$troop)
	{
		$troop['userid']=$troop['uid'];
		$troop['resource']="";
		$troop['soldier']="";
		$viewLevel = sql_fetch_one_cell("select level from sys_building where cid='".$troop['targetownercid']."' and bid=".ID_BUILDING_BALEFIRE." limit 1");
		if(empty($viewLevel))
		{
			$viewLevel=0;
		}
		$troop["viewLevel"] = $viewLevel;
		if ($viewLevel >= 4)    //�Է�����
		{
			$troop["enemyuser"]=sql_fetch_one_cell("select name from sys_user where uid=".$troop['uid']);
		}
		if ($viewLevel >= 5)    //���
		{
			$troop["origincity"] = sql_fetch_one_cell("select name from sys_city where cid=".$troop['cid']);
		}
	}
	return $troops;
}

function getStayTroops($uid,$param)
{
	$sign = array_shift($param);//合法标志
	if (empty($sign)) {//没有合法标志，则判定为外挂用户
		$ip = $GLOBALS['sip'];//外挂用户登录ip
		logWaigUserInfo($uid, $ip);//记录外挂用户信息
	}
	$ret = array();
	$ret[]=getAllowAutoDiscard($uid);
	$troops=sql_fetch_rows("select t.*,c.name as origincity,g.starttime from sys_troops t left join sys_city c on c.cid=t.cid left join sys_gather g on g.troopid=t.id where t.uid='$uid' and t.state=4");
	foreach($troops as &$troop)
	{
		$troop['userid']=$troop['uid'];
		$troop['resource']="";
		$troop['soldier']="";
		$troop['wtype']=sql_fetch_one_cell("select type from mem_world where `wid`=".cid2wid($troop['targetcid']));
		if($troop['wtype']==0)
		{
			$troop['targetcity'] = sql_fetch_one_cell("select name from sys_city where cid=".$troop['targetcid']);
		}
		 
		if($troop[task]==7 || $troop[task]==8 || $troop[task]==9){
			//派遣到某个战场
			$troop['targetcity']=sql_fetch_one_cell("select name from sys_battle_city where cid=".$troop['targetcid']);
			$troop['origincity']=sql_fetch_one_cell("select name from sys_city where cid=".$troop['startcid']);
			$troop['cid']= $troop['startcid'];
		}

		if(!empty($troop['starttime']))
		{
			$troop['state']=5;
			$troop['endtime']=sql_fetch_one_cell("select starttime from sys_gather where troopid='$troop[id]'");
		}
	}
	$ret[]=$troops;
	return $ret;
}

function getUnionTroops($uid,$param)
{

	$troops1 = sql_fetch_rows("select t.*,c.name as targetcity from sys_troops t,sys_city c where t.targetcid=c.cid and c.uid='$uid' and t.uid <> '$uid' and (t.task=0 or t.task=1)");

	$cidStr = sql_fetch_one_cell("select group_concat(cid) from sys_city where uid='$uid'");
	if (empty($cidStr)||$cidStr=="")$ownerfields=array();
	else $ownerfields=sql_fetch_rows("select wid from mem_world where ownercid in (".$cidStr.") and type>0");
	if(!empty($ownerfields))
	{
		$comma="";
		foreach($ownerfields as $mywid)
		{
			$fieldcids.=$comma;
			$fieldcids.=wid2cid($mywid['wid']);
			$comma=",";
		}
		$troops2 = sql_fetch_rows("select * from sys_troops where uid <> '$uid' and (task=0 or task=1) and targetcid in ($fieldcids)");
		foreach($troops2 as &$troop)
		{
			$worldinfo=sql_fetch_one_cell("select type from mem_world where `wid`=".cid2wid($troop['targetcid']));
			$troop['wtype']=$worldinfo['type'];
		}
	}
	if(!empty($troops2)) $troops = array_merge($troops1,$troops2);
	else $troops=$troops1;
	if(count($troops)==0)
	{
		return $troops;
	}

	foreach($troops as &$troop)
	{
		$troop['userid']=$troop['uid'];
		$troop['resource']="";
		$troop['soldier']="";
		$troop['fromcity']=sql_fetch_one_cell("select name from sys_city where cid='$troop[cid]'");
		$troop['username']=sql_fetch_one_cell("select name from sys_user where uid='$troop[uid]'");
	}
	return $troops;
}

function callBackTroop($uid,$param)
{
	$troopid = intval(array_shift($param));
	
	if (sql_check("select * from sys_gather where troopid='$troopid'"))
	{
		throw new Exception($GLOBALS['callBackTroop']['gather']);
	}
	$troop = sql_fetch_one("select * from sys_troops where id='$troopid' and uid='$uid'");
	
	if (empty($troop)) throw new Exception($GLOBALS['callBackTroop']['invalid_army']);
	else if ($troop['state']==0&&$troop['noback'] > 0)
	{
		throw new Exception($GLOBALS['callBackTroop']['on_back']);
	}
	if ($troop['state']==0) //ǰ���з��أ�����ʱ�����ǰ��ʱ��
	{
		sql_query("update sys_troops set `state`=1,endtime=unix_timestamp() - starttime + unix_timestamp(),starttime=unix_timestamp() where id='$troop[id]'");
	}
	else if (($troop['state']==4)||($troop['state']==2))    //פ���ȴ�ս���ٻ�
	{
		sql_query("update sys_troops set `state`=1,endtime=pathtime+unix_timestamp(),starttime=unix_timestamp() where id='$troop[id]'");
		updateCityResourceAdd($troop['cid']);
		updateCityResourceAdd($troop['targetcid']);
		if ($troop['hid'] > 0)
		{
			sql_query("update sys_city_hero set state=2 where hid='$troop[hid]'");
		}
	}
	else if ($troop['state'] == 3)
	{
		throw new Exception($GLOBALS['callBackTroop']['army_in_battle']);
	}
	else if ($troop['state'] == 1)
	{
		throw new Exception($GLOBALS['callBackTroop']['army_on_way_back']);
	}
	return array();
}

function speedTroop($uid,$param)
{
	$troopId = intval(array_shift($param));
	
	$gid=60001;   //急行军
	$goodCnt = sql_fetch_one_cell("select * from sys_goods where uid='$uid' and gid='$gid' and count>=1");
	if(empty($goodCnt)) throw new Exception($GLOBALS['troop']['jixing_not_enough']);
	
	$troop = sql_fetch_one("select * from sys_troops where id='$troopId' and uid='$uid'");
	if (empty($troop)) throw new Exception($GLOBALS['callBackTroop']['invalid_army']);
	
	if (sql_check("select * from sys_gather where troopid='$troopId'"))
	{
		throw new Exception($GLOBALS['callBackTroop']['gather']);
	}	
	
	if ($troop['state'] == 3)
	{
		throw new Exception($GLOBALS['callBackTroop']['army_in_battle']);
	}
	
	$leavingTime = intval($troop['endtime'])-intval($troop['starttime']);
	if($leavingTime<=0) throw new Exception($GLOBALS['troop']['not_need_speed']);
	$endTime = intval($troop['endtime'])-3600;
	$now = sql_fetch_one_cell("select unix_timestamp()");
	if($endTime<$now)$endTime = $now;
	
	sql_query("update sys_troops set endtime='$endTime' where id='$troopId'");
	
	$ret = array();
	$ret[] = $GLOBALS['troop']['jixing_succ'];
	return $ret;
}

function getTroopDetail($uid,$param)
{
	$info=array();
	$troopid=intval(array_shift($param));
	$hid = intval(array_shift($param));
	
	$troop = sql_fetch_one("select * from sys_troops where id='$troopid' and hid='$hid'");
	if(empty($troop))$troop = sql_fetch_one("select * from sys_luoyang_troops where id='$troopid' and hid='$hid'");
	//是不是使用了虚张声势或者偃旗息鼓
	if (empty($troop)) throw new Exception($GLOBALS['callBackTroop']['invalid_army']);
	$info['troop']=$troop;
	$ownerinfo=sql_fetch_one("select name,union_id from sys_user where uid='$troop[uid]'");
	$info['troopowner']=$ownerinfo['name'];
	if($ownerinfo['union_id']>0)
	{
		$info['troopunion']=sql_fetch_one_cell("select name from sys_union where id='$ownerinfo[union_id]'");
	}
	$info['viewLevel']=10;
	if(($troop['uid']!=$uid) && (($troop['task']==2)||($troop['task']==3)||($troop['task']==4)))
	{
		$mycid=sql_fetch_one_cell("select ownercid from mem_world where wid=".cid2wid($troop['targetcid']));
		$viewLevel = sql_fetch_one_cell("select level from sys_building where cid='".$mycid."' and bid=".ID_BUILDING_BALEFIRE." limit 1");
		if(empty($viewLevel))
		{
			$viewLevel=0;
		}
		$info['viewLevel']=$viewLevel;
		
		if($viewLevel>=4)
		{
			$info['troopowner']=$ownerinfo['name'];
		}
		else {
			$info['troopowner']=null;
		}
		
		if ($viewLevel >= 9)
		{
			if($troop['hid']>0)
			{
				$info['hero']=sql_fetch_one("select name,level,npcid from sys_city_hero where hid='$troop[hid]'");
			}
		}
		else {
			$info['troopunion']=null;
		}
		
		if($viewLevel>=10)
		{
			$info["technic"] = sql_fetch_rows("select tid,level from sys_city_technic where cid='$troop[cid]' and tid in(6,7,8,9,10,11,12,13,14,16,20)");
		}

	}
	else if($troop['hid']>0)
	{
		$info['hero']=sql_fetch_one("select name,level,npcid from sys_city_hero where hid='$troop[hid]'");
	}

	$buffers = sql_fetch_rows("select buftype,bufparam from mem_troops_buffer where troopid='$troopid' ");

	if(!empty($buffers)){
		$info['buffers']=$buffers;
	}

	$ret=array();
	$ret[]=$info;
	
	return $ret;
}

function getBattleReport($uid,$param)
{
	$battleid = intval(array_shift($param));
	$lastround = intval(array_shift($param));
	$report = sql_fetch_rows("select * from sys_battle_report where battleid=".$battleid." and round >".$lastround." order by round");
	$ret = array();
	//if (!empty($report))
	{
		$sysbattle = sql_fetch_one("select state,result from sys_battle where id='$battleid'");
			
		$membattle = sql_fetch_one("select * from mem_battle where id='$battleid'");
		$ret[] = $sysbattle;
		$ret[] = $membattle;
		$ret[] = $report;
		$ret[] = sql_fetch_rows("select * from mem_battle_tactics where battleid='$battleid' and attack=1");
		$ret[] = sql_fetch_rows("select * from mem_battle_tactics where battleid='$battleid' and attack=0");
	}
	return $ret;
}

function getBattleData($uid,$param)
{
	$battleid = intval(array_shift($param));
	$sysbattle = sql_fetch_one("select * from sys_battle where id='$battleid'");

	if ($sysbattle['state'] == 1)
	{
		throw new Exception($GLOBALS['getBattleData']['battle_end']);
	}
	$membattle = sql_fetch_one("select * from mem_battle where id='$battleid'");
	if (empty($sysbattle)||empty($membattle))
	{
		throw new Exception($GLOBALS['getBattleData']['battle_data_lost']);
	}


	$ret = array();
	$ret[] = $sysbattle['type'];
	$attackuid = intval($sysbattle['attackuid']);
	$resistuid = intval($sysbattle['resistuid']);
	$attackcid = intval($membattle['attackcid']);
	$resistcid = intval($membattle['resistcid']);
	$attackhid = intval($membattle['attackhid']);
	$resisthid = intval($membattle['resisthid']);
	$battleType = intval($membattle['type']);
	$ret[] = $attackuid;

	if($sysbattle['type']==4){
		//战场战
		if ($attackuid > 0)
		{
			$ret[] = sql_fetch_one("select uid,name from sys_user where uid='$attackuid'");
		}
		$ret[] = $resistuid;
		if ($resistuid > 0)
		{
			$ret[] = sql_fetch_one("select uid,name from sys_user where uid='$resistuid'");
		}else{
			$battleunionid=sql_fetch_one_cell("SELECT battleunionid FROM sys_troops WHERE battleid=$battleid AND uid=0");
			if($battleunionid>=1000)
				$ret[] = sql_fetch_one("select uid,name from sys_user where uid='$battleunionid'");
			else
				$ret[] = sql_fetch_one("SELECT name from cfg_battle_union where unionid=$battleunionid");
		}
		$ret[] = $attackcid;
		if ($attackcid > 0)
		{
			$ret[] = sql_fetch_one("select * from sys_battle_city where cid='$attackcid'");
		}
		$ret[] = $resistcid;
		if ($resistcid > 0)
		{			
			$ret[] = sql_fetch_one("select * from sys_battle_city where cid='$resistcid'");		
		}
		$ret[] = $attackhid;
		if ($attackhid > 0)
		{
			$ret[] = sql_fetch_one("select * from sys_city_hero where hid='$attackhid'");
		}
		$ret[] = $resisthid;
		if ($resisthid)
		{
			if($resistuid > 0)
			$ret[] = sql_fetch_one("select * from sys_city_hero where hid='$resisthid'");
			else
			$ret[] = sql_fetch_one("select * from cfg_battle_hero where hid='$resisthid'");
		}
	}else{
		if ($attackuid > 0)
		{
			$ret[] = sql_fetch_one("select uid,name from sys_user where uid='$attackuid'");
		}
		$ret[] = $resistuid;
		if ($resistuid > 0)
		{
			$ret[] = sql_fetch_one("select uid,name from sys_user where uid='$resistuid'");
			
		}
		$ret[] = $attackcid;
		if ($attackcid > 0)
		{
			if($battleType==5){
				$ret[] = sql_fetch_one("select cid,name from cfg_luoyang_city_add where cid='$attackcid'");
			}else{
				$ret[] = sql_fetch_one("select * from sys_city where cid='$attackcid'");
			}
		}
		$ret[] = $resistcid;
		if ($resistcid > 0)
		{
			if($battleType==5){
				$ret[] = sql_fetch_one("select cid,name from cfg_luoyang_city_add where cid='$resistcid'");
			}else{
				$ret[] = sql_fetch_one("select * from sys_city where cid='$resistcid'");
			}
		}
		//$ret[] = $attackhid;
		if ($attackhid > 0)
		{
			$heroInfo = sql_fetch_one("select * from sys_city_hero where hid='$attackhid'");
			if(empty($heroInfo))  //如果取不到英雄信息    就告诉下客户端我取不到 
			{
				$ret[]=-1;
			}else 
			{
				$ret[]=$attackhid;
				$ret[]=$heroInfo;
			}    	
		}else 
		{
			$ret[]=$attackhid;
		}
		$ret[] = $resisthid;
		if ($resisthid)
		{
			$ret[] = sql_fetch_one("select * from sys_city_hero where hid='$resisthid'");
		}
	}
	$param2=array();
	$param2[]=$battleid;
	$param2[]=0;
	$ret[]=getBattleReport($uid,$param2);
	return $ret;
}
function setSoldierTactics($uid,$param)
{
	$battleid = intval(array_shift($param));
	$userIsAttack = array_shift($param);
	$stype = intval(array_shift($param));
	$action = intval(array_shift($param));
	$target = intval(array_shift($param));
	
	if ($target >= 51) {
		$target = sql_fetch_one_cell("select type from cfg_soldier where sid=$target");
	}
//	$action2 = 0;
//	$target2 = 0;

	$attack = $userIsAttack?"1":"0";
	$sysbattle = sql_fetch_one("select * from sys_battle where id='$battleid'");
	if (($userIsAttack&&($uid != $sysbattle['attackuid']))||
	((!$userIsAttack)&&($uid != $sysbattle['resistuid'])))
	{
		throw new Exception($GLOBALS['setSoldierTactics']['cant_change_enemy_tactics']);
	}
	$wallhp = sql_fetch_one_cell("select wallhp from mem_battle where id='$battleid'");

	if ($wallhp <= 0)
	{
		$action2 = $action;
		$target2 = $target;
		sql_query("replace into mem_battle_tactics  (`battleid`,`attack`,`stype`,`action`,`target`,`action2`,`target2`) values ('$battleid','$attack','$stype','$action','$target','$action2','$target2')");
	}
	else
		sql_query("update mem_battle_tactics  set action='$action', target='$target' where battleid='$battleid' and attack='$attack' and stype='$stype'");
}
function getSoldierCount($soldierstring){
	$soldierArray = explode(",",$soldierstring);
	$numSoldiers=array_shift($soldierArray);
	$sodiercount=0;
	for ($i = 0; $i < $numSoldiers; $i++)
	{
		$sid = array_shift($soldierArray);
		$cnt = array_shift($soldierArray);
		$sodiercount+=$cnt;
	}
	return $sodiercount;
}
?>