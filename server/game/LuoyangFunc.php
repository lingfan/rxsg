<?php
require_once("./interface.php");
require_once("./utils.php");
/**
 * 把兵力派往洛阳
 */
function luoyangTroopStart($uid,$param) {
	$hid = intval(array_shift($param));
	$cid = intval(array_shift($param));
	$unionid = intval(array_shift($param));
	$soldiers = array_shift($param);
	$soldiers = addslashes($soldiers);
	$usegoods=array_shift($param);

	//检查是否有军旗，军旗的id是59
	if(($usegoods)&&!checkGoods($uid,59)) throw new Exception($GLOBALS['StartTroop']['no_flag']);
	//19点后才能开始派兵
	//$day = sql_fetch_one_cell("select DATE_FORMAT(NOW(),'%w')");
	$hour = sql_fetch_one_cell("select DATE_FORMAT(NOW(),'%k')");
	if ($hour!=8 && $hour!=14) {//改成 每天早上10点和下午16点
	//if ($day != 3 || $hour < 16) {
		throw new Exception($GLOBALS['luoyang']['out_of_time']);
	}
	if (sql_check("select 1 from mem_state where state=2000 and value=2")) {
		throw new Exception($GLOBALS['luoyang']['has_close']);
	}
	//check
	if (!sql_check("select 1 from sys_city_hero where uid=$uid and hid=$hid")) {
		throw new Exception($GLOBALS['book']['not_your_hero']);	
	}
	if (!sql_check("select 1 from sys_city where uid=$uid and cid=$cid")) {
		throw new Exception($GLOBALS['searchcityname']['target_union_not_exist']);
	}
	if (!sql_check("select 1 from sys_user where uid=$uid and union_id=$unionid")) {
		throw new Exception($GLOBALS['getUnionRelation']['not_belongTo_union']);
	}
	if(!sql_check("select 1 from sys_city_hero where uid='$uid' and hid='$hid' and state='0'")){
		throw new Exception($GLOBALS['StartTroop']['hero_is_busy']);
	}
	//推恩令有效
	$nobility = sql_fetch_one_cell ( "select nobility from sys_user where uid='$uid'" );
	$nobility = getBufferNobility ( $uid, $nobility );
	if ($nobility <6) {
		throw new Exception($GLOBALS['luoyang']['nobility_is_too_low']);
	}
	$troopNum = sql_fetch_one_cell("select count(*) from sys_luoyang_troops where uid=$uid");
	$second = sql_fetch_one_cell("select second from sys_user_luoyang where uid=$uid");
	if (empty($second) && !empty($troopNum)) {
		throw new Exception($GLOBALS['luoyang']['troop_exists']);
	}
	//检查一下当前城池是否有这么多军队
	$citySoldiers = sql_fetch_map("select * from sys_city_soldier where cid='$cid'","sid");

	$soldierArray = explode(",",$soldiers);
	$numSoldiers = array_shift($soldierArray);
	$takeSoldiers = array();    //真正带出去的军队
	if($numSoldiers>12){
		throw new Exception($GLOBALS['StartTroop']['too_many_sid']);
	}
	$soldierAllCount = 0;
	for ($i = 0; $i < $numSoldiers; $i++)
	{
		$sid = array_shift($soldierArray);
		$cnt = array_shift($soldierArray);
		if ($cnt < 0) $cnt = 0;
		$takeSoldiers[$sid] = $cnt;
		//实际军队人数<客户端传来的人数
		if ($citySoldiers[$sid]['count'] < $cnt)
		{
			throw new Exception($GLOBALS['StartTroop']['no_so_many_army']);
		}
		$soldierAllCount += $cnt;
	}
	if ($soldierAllCount <= 0) throw new Exception($GLOBALS['StartTroop']['no_soldier']);
	//出征人数限制
	$groundLevel = intval(sql_fetch_one_cell("select level from sys_building where cid=$cid and bid='".ID_BUILDING_GROUND."'"));
	$groundLevelLimit = $groundLevel * 10000 * GAME_SPEED_RATE;
	$limitadd=0;
	//使用军旗
	if(!empty($usegoods))
	{
		$limitadd+=25;
	}
	$myCityInfo=sql_fetch_one("select * from sys_city where cid='$cid'");
	//名城出征人数
	if($myCityInfo['type']==0){
		if(getSpacialSoldierId($cid)>0){
			$limitadd+=25;
		}
	}
	if($myCityInfo['type']>0)
	{
		if($myCityInfo['type']==1) $limitadd+=25;
		else if($myCityInfo['type']==2) $limitadd+=50;
		else if($myCityInfo['type']==3) $limitadd+=75;
		else if($myCityInfo['type']==4) $limitadd+=100;
	}
	if($limitadd>0)
	{
		$groundLevelLimit=ceil($groundLevelLimit*(100+$limitadd)/100);
	}

	//是否超过人数
	if ($soldierAllCount > $groundLevelLimit)
	{
		throw new Exception(sprintf($GLOBALS['StartTroop']['no_enough_ground_level'],$groundLevelLimit));
	}
	reduceGoods($uid,59,1);
	
	$pathNeedTime = getTroopTime($hid,$cid,$takeSoldiers);

	//减兵员
	addCitySoldiers($cid,$takeSoldiers,false);
	
	sql_query("insert into sys_luoyang_troops(uid,cid,hid,targetcid,starttime,pathtime,endtime,soldiers,startcid,unionid) values($uid,$cid,$hid,215265,unix_timestamp(),$pathNeedTime,unix_timestamp()+$pathNeedTime,'{$soldiers}',$cid,$unionid)");
	sql_query("update sys_city_hero set state='2' where hid='$hid'");
	
	$ret = array();
	$ret[] = $GLOBALS['luoyang']['start_troop_succ'];
	return $ret;
}

function getTroopTime($hid,$cid,$takeSoldiers) {
	$heroInfo = sql_fetch_one("select * from sys_city_hero where hid=$hid");
	//步兵速度加成
	$speedAddRate1=1+intval(sql_fetch_one_cell("select level from sys_city_technic where cid='$cid' and tid=12"))*0.1;
	//骑兵速度加成
	$speedAddRate2=1+intval(sql_fetch_one_cell("select level from sys_city_technic where cid='$cid' and tid=13"))*0.05;
	//将领速度加成
	$speedAddRate3=1;
	//车轮技术
	$speedAddRate4=intval(sql_fetch_one_cell("select level from sys_city_technic where cid='$cid' and tid=26"))*0.05;
	if($hid!=0)
	{
		$speedAddRate3=1+$heroInfo['speed_add_on']*0.01;
	}
	$currentX = $cid % 1000;
	$currentY = floor($cid / 1000);
	$targetX  = 215265 % 1000;
	$targetY  = floor(215265 / 1000);

	//单程时间 ＝ 每格子距离/最慢兵种速度+宿营时间（每格距离＝60000/game_speed_rate）
	$pathLength = sqrt(($targetX - $currentX)*($targetX - $currentX) + ($targetY - $currentY)*($targetY - $currentY));
	$minSpeed = 999999999;
	$soldierConfig = sql_fetch_rows("select * from cfg_soldier where fromcity=1 order by sid","sid");
	foreach ($soldierConfig as $soldier)        //找到当前军队里最慢的
	{
		$speedAdd = 0;
		//计算将领身上因为装备而得到的该士兵的速度加成
		$sid = $soldier->sid;
		if(($sid >=1 && $sid <=12)||($sid >=45 && $sid <=50)) {
			$attid = 2000 + ($sid - 1) * 100 + 11;  //取得属性id
			$attr = sql_fetch_one("select * from sys_hero_attribute where hid=$hid and attid=$attid");
			if(!empty($attr)) {
				$speedAdd = $attr['value'];
			}
		}
		if (!empty($takeSoldiers[$soldier->sid]))
		{
			//除了斥候外的步兵速度加成
			if($soldier->sid<7&&$soldier->sid!=3)
			{
				$minSpeed = min($soldier->speed*$speedAddRate1*$speedAddRate3 + $speedAdd,$minSpeed);
			}
			//骑兵加成
			else if($soldier->sid==8 || $soldier->sid==7)
			{
				$minSpeed = min($soldier->speed*$speedAddRate2*$speedAddRate3 + $speedAdd,$minSpeed);
			}
			//车轮技术
			else if($soldier->sid>=10 || $soldier->sid<=12)
			{
				$minSpeed = min($soldier->speed*$speedAddRate2*$speedAddRate3+$soldier->speed*$speedAddRate4 + $speedAdd,$minSpeed);
			}
		}
	}
	
	$pathNeedTime = $pathLength * GRID_DISTANCE / $minSpeed;    //需要多少时间
	$beaconAdd = getBeaconAdd($cid,215265);
	if ($beaconAdd) {
		$pathNeedTime = $pathNeedTime*0.5;
	}
	$pathNeedTime=intval(floor($pathNeedTime));
	return $pathNeedTime;
}


function luoyangChangeCity($uid,$param) {
	$troopId = intval(array_shift($param));
	$cid = intval(array_shift($param));
	$targetcid = intval(array_shift($param));
	
	if (!sql_check("select 1 from sys_luoyang_troops where uid=$uid and id=$troopId")) {
		throw new Exception($GLOBALS['luoyang']['not_your_troop']);
	}
	if (!sql_check("select 1 from cfg_luoyang_city where cid=$cid and targetcid=$targetcid")) {
		throw new Exception($GLOBALS['luoyang']['cannot_go_city']);
	}
	if (sql_check("select 1 from sys_luoyang_troops where endtime>unix_timestamp() and id=$troopId")) {
		throw new Exception($GLOBALS['luoyang']['time_not_pass']);
	}
	if(sql_check("select 1 from sys_luoyang_troops where cid='$cid' and uid<'1000' limit 1")){
		throw new Exception($GLOBALS['luoyang']['NPC_troops_exists']);
	}
	sql_query("update sys_luoyang_troops set cid=$targetcid,targetcid=$targetcid,starttime=unix_timestamp(),pathtime=120,endtime=unix_timestamp()+120 where id=$troopId");
		
	$curTroopInfo =sql_fetch_one("select id,hid,cid,targetcid,pathtime from sys_luoyang_troops where id='$troopId'");	
	$ret = array();
	$ret[] = $curTroopInfo;
	$ret[] = $GLOBALS['luoyang']['start_troop_succ'];
	return $ret;
}

function luoyangAttack($uid,$param) {
	$attackHid = intval(array_shift($param));
	$resistHid = intval(array_shift($param));
	$battleCid = intval(array_shift($param));
	
	$value = sql_fetch_one_cell("select value from mem_state where state=2000");
	if ($value == 0) {
		throw new Exception($GLOBALS['luoyang']['not_open']);
	} elseif ($value == 2) {
		throw new Exception($GLOBALS['luoyang']['has_close']);
	}
	$attackTroop = sql_fetch_one("select * from sys_luoyang_troops where hid=$attackHid and cid='$battleCid'");
	$resistTroop = sql_fetch_one("select * from sys_luoyang_troops where hid=$resistHid and cid='$battleCid'");
	if (empty($attackTroop) || empty($resistTroop)) {
		throw new Exception($GLOBALS['luoyang']['troop_not_exists']);
	}
	if ($attackTroop['unionid'] == $resistTroop['unionid']) {
		throw new Exception($GLOBALS['luoyang']['union_is_same']);
	}
	if ($attackTroop['state'] == 1) {
		throw new Exception($GLOBALS['luoyang']['troop_self_battle']);
	}
	if ($resistTroop['state'] == 1) {
		throw new Exception($GLOBALS['luoyang']['troop_other_battle']);
	}
	sql_query("insert into sys_battle (type,starttime,cid,attackuid,resistuid,attacktroop,resisttroops) values(5,unix_timestamp(),{$attackTroop['cid']},{$attackTroop['uid']},{$resistTroop['uid']},{$attackTroop['id']},{$resistTroop['id']})");
	$id = sql_fetch_one_cell("select last_insert_id()");
	$fieldrange = 3354;
	$attackpos = louyangRangePos($attackTroop['soldiers'],3299);
	$resistpos = louyangRangePos($resistTroop['soldiers'],50);
	sql_query("replace into mem_battle (`id`,`type`,`nexttime`,`round`,`attackcid`,`attackhid`,`attacksoldiers`,`attackpos`,`resistcid`,`resisthid`,`resistsoldiers`,`resistpos`,`fieldrange`,`level`,`attackstartcid`,`resiststartcid`) 
	   values ($id,'5',unix_timestamp()+30,'1','{$attackTroop['cid']}','{$attackTroop['hid']}','{$attackTroop['soldiers']}','{$attackpos}','{$resistTroop['cid']}','{$resistTroop['hid']}','{$resistTroop['soldiers']}','{$resistpos}','{$fieldrange}','10','{$attackTroop['startcid']}','{$resistTroop['startcid']}')");
	sql_query("update sys_luoyang_troops set state='1' where hid={$attackTroop['hid']} and cid='$battleCid'");
	sql_query("update sys_luoyang_troops set state='1' where hid={$resistTroop['hid']} and cid='$battleCid'");
	sql_query("update sys_luoyang_troops set battleid='$id' where hid={$resistTroop['hid']} and cid='$battleCid'");
	sql_query("update sys_luoyang_troops set battleid='$id' where hid={$attackTroop['hid']} and cid='$battleCid'");
	//给沙场系统设置默认战术（双方都为遭遇战战术）
	sql_query("replace into mem_battle_tactics (battleid,attack,stype,action,target,action2,target2) (select $id,1,stype,plunder_action,plunder_target,'0','0' from cfg_troop_tactics)");
	sql_query("replace into mem_battle_tactics (battleid,attack,stype,action,target,action2,target2) (select $id,0,stype,plunder_action,plunder_target,'0','0' from cfg_troop_tactics)");
   	$ret = array();
	$ret[] = $id;
	return $ret;
}
function louyangRangePos($soldiers,$fieldrange){
	$posarray=explode(",",$soldiers);
	$posnum=array_shift($posarray);
	$comma='';
	for($i=0;$i<$posnum;$i++){
		$sid=array_shift($posarray);
		$postemp=array_shift($posarray);
		$posrange=$fieldrange;
		$comma.=$sid.','.$posrange.',';
	}
	$newpos=$posnum.",".$comma;
	return $newpos;
}
function luoyangYuanjun($uid) {
	if(!checkMoney($uid,100))throw new Exception($GLOBALS['buyGoods']['no_enough_YuanBao']);
	$unionid = sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
	sql_query("insert into sys_user_luoyang (uid,unionid,second) values($uid,$unionid,1) on duplicate key update second=1");
	addMoney($uid,'-100',91);
	
	$ret = array();
	$ret[] = $GLOBALS['heroexpr']['get_reward_success'];
	return $ret;
}

function useLuoyangTrick($uid,$param) {
	$hid = intval(array_shift($param));
	$type = intval(array_shift($param));
	$usetype = intval(array_shift($param));
	
	//先只开放千里奔袭
	if($type!=13)throw new Exception($GLOBALS['luoyang']['can_not_use_trick']);
	$hero = sql_fetch_one("select * from sys_city_hero where hid='$hid'");
	if (empty($hero)) throw new Exception($GLOBALS['useTrick']['hero_not_exist']);
	$trick = sql_fetch_one("select * from cfg_trick where id='$type'");
	if (empty($trick)||($trick['usetype'] != $usetype))  throw new Exception($GLOBALS['useTrick']['trick_not_exist']);
	$userjinnang = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid=13");
	if ($userjinnang < $trick['cost'])
	{
		$tempnum = $trick['cost'] - $userjinnang;
		throw new Exception("not_enough_goods13#$tempnum");
	}

	$wisdom = $hero['wisdom_base'] + $hero['wisdom_add'];
	if (isHeroHasBuffer($hid,4))    //智多星符
	{
		$wisdom = $wisdom * 1.25;
	}
	$wisdom=$wisdom+$hero['wisdom_add_on'];
	
	$isBack=0;  //-1从洛阳返回，1派遣到洛阳，0出异常了
	$lastcc = 0;
	$troop = sql_fetch_one("select * from sys_luoyang_troops where uid=$uid and hid=$hid");	
	if(empty($troop)){
		$troop = sql_fetch_one("select * from sys_troops where uid=$uid and hid=$hid");
		if(!empty($troop)){
			$isBack = -1;
			$lastcc=$troop['lastacc'];
		}
	}else{
		$isBack=1;
		$lastcc=$troop['lastcc'];
	}
	switch ($type) {
		case 13://千里奔袭
				$addrate=100/(100+$wisdom);
				if($troop['targetcid']!=215265) throw new Exception($GLOBALS['trickQianLiBenXi']['wrong_state']);				
				$now = sql_fetch_one_cell("select unix_timestamp()");
				if($now-$lastcc<3600) throw new Exception($GLOBALS['trickQianLiBenXi']['cool_down']);
				
				if($isBack==-1){
					sql_query("update sys_troops set endtime=unix_timestamp()+(endtime-unix_timestamp())*$addrate,lastacc=unix_timestamp() where id='$troop[id]'");
				}else if($isBack==1){
					sql_query("update sys_luoyang_troops set endtime=unix_timestamp()+(endtime-unix_timestamp())*$addrate,lastcc=unix_timestamp() where id='$troop[id]'");
				}else {
					throw new Exception($GLOBALS['waigua']['invalid']);
				}			
				$msg = $GLOBALS['trickQianLiBenXi']['succ'];
	}
	addGoods($uid,13,-$trick['cost'],0);
	$ret[] = $msg;
	$ret[] = sql_fetch_one_cell("select sum(count) from sys_goods where uid='$uid' and gid=13");
	return $ret;
}

function luoyangCityTroops($uid,$param) {
	$cid = intval(array_shift($param));
	$hid = intval(array_shift($param));
	
	if (!sql_check("select 1 from sys_luoyang_troops where uid=$uid and cid=$cid and hid='$hid'")) {
		throw new Exception($GLOBALS['luoyang']['not_in_city']);
	}
	
	$now = sql_fetch_one_cell("select unix_timestamp()");
	$troopInfos = array();
	$troopInfoTmp = sql_fetch_rows("select id,uid as userId,cid,hid,state,soldiers,battleid,unionid from sys_luoyang_troops where cid='$cid' order by unionid asc");
	foreach($troopInfoTmp as $troopInfo)
	{
		$troopInfo['name']=sql_fetch_one_cell("select name from sys_user where uid={$troopInfo['userId']} limit 1");
		
		$heroInfo = sql_fetch_one("select name,level from sys_city_hero where hid={$troopInfo['hid']} limit 1");		
		$troopInfo['heroName']=$heroInfo['name'];
		$troopInfo['heroLevel']=$heroInfo['level'];
		if(intval($troopInfo['userId'])>1000)
		{
			$troopInfo['unionName']=sql_fetch_one_cell("select name from sys_union where id={$troopInfo['unionid']} limit 1");
		}else 
		{
			$troopInfo['unionName']=" ";
		}
		$troopInfos[] = $troopInfo;
	}
	
	$arr = array();
	$arr[] = $troopInfos;
	return $arr;
}

function luoyangAllocate($uid,$param) {
	$name = array_shift($param);
	$name = addslashes($name);
	
	$luoyangInfo = sql_fetch_one("select * from sys_luoyang_info");
	if(empty($luoyangInfo)||intval($luoyangInfo['uid'])<1000)throw new Exception($GLOBALS['luoyang']['can_not_assign']);
	
	//洛阳城是不是在本盟手上
	if(!sql_check("select 1 from sys_user where uid='$uid' and union_id={$luoyangInfo['unionid']}")){
		throw new Exception($GLOBALS['luoyang']['city_not_in_union']);
	}
	//只有盟主才能才操作
	if (!sql_check("select 1 from sys_union where id={$luoyangInfo['unionid']} and leader=$uid")) {
		throw new Exception($GLOBALS['luoyang']['not_union_leader']);
	}
	
	$preRecord = sql_fetch_one("select * from log_luoyang_belong order by time desc limit 1");		
	$startTime = getAssingStartTime();
	if(!empty($preRecord)){	
		//洛阳城是否已分配	
		if (sql_check("select 1 from log_luoyang_belong where time>=$startTime")) {
			throw new Exception($GLOBALS['luoyang']['has_allocated']);
		}	
	}			
	//成员要存在，并且必须是本盟成员
	$memberUid = sql_fetch_one_cell("select uid from sys_user where union_id={$luoyangInfo['unionid']} and name ='$name'");
	if (empty($memberUid)) {
		throw new Exception($GLOBALS['luoyang']['not_union_member']);
	}
	
	//加分配记录
	if(empty($preRecord)){
		sql_query("insert into log_luoyang_belong(`uid`,`unionid`,`time`,`count`) values('$memberUid','$luoyangInfo[unionid]',unix_timestamp(),'1')");
	}else{
		$isGap = $preRecord<$startTime-604800?true:false;   //是否间断过  604800=7*24*3600
		if(intval($preRecord['uid'])==$memberUid && intval($preRecord['unionid'])==$luoyangInfo['unionid']&&!$isGap){
			$count = $preRecord['count']+1;
			sql_query("insert into log_luoyang_belong(`uid`,`unionid`,`time`,`count`) values('$memberUid','$luoyangInfo[unionid]',unix_timestamp(),'$count')");
		}else{
			sql_query("insert into log_luoyang_belong(`uid`,`unionid`,`time`,`count`) values('$memberUid','$luoyangInfo[unionid]',unix_timestamp(),'1')");
		}
	}
	
	//把洛阳分配给该成员
	sql_query("update sys_luoyang_info set uid='$memberUid',name='$name'");
	sql_query("insert into mem_luoyang_record(time,unionid,type,uid) values(unix_timestamp(),'{$luoyangInfo['unionid']}',1,$memberUid)");
	//给玩家添加王爵位的buffer
	$addLevel = sql_fetch_one_cell("select (21-nobility) as addLevel from sys_user where uid='$memberUid'");
	sql_query("insert into mem_user_buffer(`uid`,`buftype`,`endtime`,`bufparam`) values('$memberUid','130527',unix_timestamp()+864000,$addLevel)");//预留10天，最多7天后由c++给清理掉
	sql_query("update sys_user set nobility='21' where uid='$memberUid'");
	
	$ret = array();
	$ret[] = sprintf($GLOBALS['luoyang']['assign_succ'],$name);
	return $ret;
}

function getLuoyangEvent($uid){
	$eventInfos = sql_fetch_rows("select * from mem_luoyang_record");
	
	$eventInfoArr = array();
	foreach($eventInfos as $eventInfo)
	{
		$eventInfo['unionName'] = sql_fetch_one_cell("select name from sys_union where id={$eventInfo['unionid']}");
		$eventInfo['userName'] = sql_fetch_one_cell("select name from sys_user where uid={$eventInfo['uid']}");
		$eventInfoArr[] = $eventInfo;
	}
	return $eventInfoArr;
}

function getLuoyangInfo() {
	return sql_fetch_one("select * from mem_luoyang_info limit 1");
}

function luoyangCallbackTroop($uid,$param) {
	$troopid = intval(array_shift($param));
	$troopInfo = sql_fetch_one("select * from sys_luoyang_troops where uid=$uid and id=$troopid");
	
	if (empty($troopInfo)) {
		throw new Exception($GLOBALS['luoyang']['not_your_troop']);
	}
	if ($troopInfo['state'] == 1) {
		throw new Exception($GLOBALS['luoyang']['in_luoyang_battle']);
	}
	
	$soldierArray = explode(",",$troopInfo['soldiers']);
	$numSoldiers = array_shift($soldierArray);
	$takeSoldiers = array();    //真正带出去的军队
	if($numSoldiers>12){
		throw new Exception($GLOBALS['StartTroop']['too_many_sid']);
	}
	for ($i = 0; $i < $numSoldiers; $i++)
	{
		$sid = array_shift($soldierArray);
		$cnt = array_shift($soldierArray);
		if ($cnt < 0) $cnt = 0;
		$takeSoldiers[$sid] = $cnt;
	}
	$pathNeedTime = getTroopTime($uid,$troopInfo['startcid'],$takeSoldiers);
	$starttime = sql_fetch_one_cell("select starttime from sys_luoyang_troops where uid=$uid and id=$troopid limit 1");
	if(intval($troopInfo['targetcid'])==215265)  //派遣洛阳的途中进行召回
	{
		$now = sql_fetch_one_cell("select unix_timestamp()");
		$pathNeedTime = $now-$starttime;
		sql_query("insert into sys_troops(uid,cid,hid,targetcid,task,state,starttime,pathtime,endtime,soldiers,resource) values($uid,{$troopInfo['startcid']},{$troopInfo['hid']},215265,1,1,unix_timestamp(),$pathNeedTime,unix_timestamp()+$pathNeedTime,'{$troopInfo['soldiers']}','0,0,0,0,0')");
	}else    //洛阳战场内召回
	{
		sql_query("insert into sys_troops(uid,cid,hid,targetcid,task,state,starttime,pathtime,endtime,soldiers,resource) values($uid,{$troopInfo['startcid']},{$troopInfo['hid']},215265,1,1,unix_timestamp(),$pathNeedTime,unix_timestamp()+$pathNeedTime,'{$troopInfo['soldiers']}','0,0,0,0,0')");
	}		
	sql_query("delete from sys_luoyang_troops where uid=$uid and id=$troopid ");
	$ret = array();
	$ret[] = $GLOBALS['battle']['callback_succ'];
	return $ret;
}
function getLYBattleData($uid)
{
	$isOpen = sql_fetch_one_cell("select value from mem_state where state='2000'");
	if(intval($isOpen) != 1)
	{
		throw new Exception($GLOBALS['luoyang']['out_of_time']);
	}
	$startTime = sql_fetch_one_cell("select value from mem_state where state='2001'");
	if(empty($startTime)||intval($startTime)<=0)
	{
		throw new Exception($GLOBALS['waigua']['invalid']);
	}
	$now = sql_fetch_one_cell("select unix_timestamp()");
	$endTime = $startTime+7200;   //持续2个小时    
	if($endTime<$now){
		throw new Exception($GLOBALS['luoyang']['has_close']);
	}	
	//推恩令有效
	$nobility = sql_fetch_one_cell ( "select nobility from sys_user where uid='$uid'" );
	$nobility = getBufferNobility ( $uid, $nobility );
	if ($nobility <6) {
		throw new Exception($GLOBALS['luoyang']['nobility_is_too_low']);
	}
	//必须在联盟
	$union_id = sql_fetch_one_cell("select union_id from sys_user where uid='$uid'");
	if(intval($union_id)<=0){
		throw new Exception($GLOBALS['luoyang']['not_in_union']);
	}
	$arr = array();
	$cityInfo = sql_fetch_rows("select a.*,group_concat(c.targetcid) as nextcid from cfg_luoyang_city_add a,cfg_luoyang_city c where a.cid=c.cid group by c.cid");
		
	$leavingTime = $endTime-$now;	
	$isOpenSecond = sql_fetch_one_cell("select second from sys_user_luoyang where uid='$uid' limit 1");
	$userTroopInfo = sql_fetch_rows("select s.*,if(s.endtime-unix_timestamp()>0,s.endtime-unix_timestamp(),0) as leavingTime,h.name as heroname,h.level,h.sex,h.face,h.herotype from sys_luoyang_troops s,sys_city_hero h where s.hid=h.hid and s.uid='$uid'");
	if(empty($isOpenSecond))$isOpenSecond=0;
	if(empty($userTroopInfo))$userTroopInfo=array();
	
	$arr[] = $cityInfo;
	$arr[] = $leavingTime;
	$arr[] = $isOpenSecond;
	$arr[] = $userTroopInfo;	
	return $arr;
}
function getUnionRankPoint($uid)
{
	$unionPointRankInfos = sql_fetch_rows("select unionid,sum(score) as totalScore from sys_user_luoyang group by unionid order by totalScore desc");
	$arr = array();
	$rank=0;
	foreach($unionPointRankInfos as $unionPointRankInfo)
	{	$rank++;
		$unionPointRankInfo["rank"]=$rank;
		$unionInfo = sql_fetch_one("select name,(select name from sys_user where uid=leader) as leaderName  from sys_union where id={$unionPointRankInfo['unionid']}");		
		$unionPointRankInfo['unionName']=$unionInfo['name'];
		$unionPointRankInfo['leaderName'] =$unionInfo['leaderName'];
		$arr[] = $unionPointRankInfo;
	}
	return $arr;
}
function getUserRankPoint($uid)
{
	$userPointRankInfos = sql_fetch_rows("select * from sys_user_luoyang order by score desc");
	$arr = array();
	$rank=0;
	foreach($userPointRankInfos as $userPointRankInfo)
	{
		$rank++;
		$userPointRankInfo['rank']=$rank;
		$userPointRankInfo['name'] = sql_fetch_one_cell("select name from sys_user where uid={$userPointRankInfo['uid']}");
		$userPointRankInfo['unionName'] = sql_fetch_one_cell("select name from sys_union where id={$userPointRankInfo['unionid']}");
		$arr[] = $userPointRankInfo;
	}
	return $arr;
}
function getUserInfoPoint($uid)
{
	$userScore = sql_fetch_one_cell("select score from sys_user_luoyang where uid='$uid'");
	$userBattleInfo = sql_fetch_rows("select uid,name,cityname,touid,toname,score,flag from mem_luoyang_event where uid='$uid' or touid='$uid'");
	
	if(empty($userScore))$userScore=0;
	if(empty($userBattleInfo))$userBattleInfo=array();
	
	$arr = array();
	$arr[] = $userScore;
	$arr[] = $userBattleInfo;
	return $arr;
}
function getLYBelongInfo($uid)
{
	$luoyangInfo = sql_fetch_one("select * from sys_luoyang_info");
	if(!empty($luoyangInfo)){
		$luoyangInfo['userName'] = sql_fetch_one_cell("select name from sys_user where uid={$luoyangInfo['uid']}");
		$luoyangInfo['unionName'] = sql_fetch_one_cell("select name from sys_union where id={$luoyangInfo['unionid']}");		
	}
	$startTime = getAssingStartTime();
	$luoyangBattle = sql_fetch_one("select * from cfg_battle_field where id='12001'");
	if(empty($luoyangBattle))$luoyangBattle=array();
	$yearSign = sql_fetch_one("select * from log_luoyang_belong where time>='$startTime' limit 1");
	if(!empty($yearSign))$luoyangInfo['yearCount']=$yearSign['count'];
	$isCanSend = true;
	$sendTime = sql_fetch_one_cell("select last_send_inform from sys_luoyang_info where uid='$uid'");
	if(empty($sendTime)||$sendTime>0){
		$isCanSend=false;
	}
	$arr = array();
	$arr[] = $luoyangInfo;
	$arr[] = $luoyangBattle;
	$arr[] = $isCanSend;
	return $arr;
}
function sendBelongInform($uid)
{
	checkLuoyangOwn($uid);
	$curDate = sql_fetch_one_cell("select unix_timestamp(curdate())");
	if(sql_check("select 1 from sys_luoyang_info where last_send_inform<$curDate")){
		sql_query("update sys_luoyang_info set last_send_inform='0'");
	}
	if(sql_check("select 1 from sys_luoyang_info where last_send_inform>0")){
		throw new Exception($GLOBALS['luoyang']['has_send_inform']);
	}
	$msg= $GLOBALS['luoyang']['inform_content'];
	sql_query("insert into sys_inform (`type`,`inuse`,`starttime`,`endtime`,`interval`,`scrollcount`,`color`,`msg`) values ('1','1',unix_timestamp(),unix_timestamp()+'60',0,2,49151,'$msg')");
	sql_query("update sys_luoyang_info set last_send_inform=unix_timestamp()");
	
	$isCanSend = false;
	$ret = array();
	$ret[] = $isCanSend;
	
	return $ret;	
}
function openUnionZhaoAn($uid){
	checkLuoyangOwn($uid);
	if(sql_check("select 1 from sys_luoyang_info where last_use_buffer>0")){  
		throw new Exception($GLOBALS['luoyang']['has_used']);
	}
	$unionid = sql_fetch_one_cell("select union_id from sys_user where uid='$uid'");
	$unionUids = sql_fetch_rows("select uid from sys_user where union_id='$unionid'");
	$buftime = 259200;   //72小时
	foreach ($unionUids as $unionUid)
	{
		$memberUid = $unionUid['uid'];
		sql_query("insert into mem_user_buffer(`uid`,`buftype`,`endtime`) values('$memberUid','10333',unix_timestamp()+'$buftime') on duplicate key update endtime=endtime+'$buftime'");
	}
	sql_query("update sys_luoyang_info set last_use_buffer=unix_timestamp()");
	$isHasUsed = true;
	$ret = array();
	$ret[] = $isHasUsed;   
	return $ret;   
}
function getLuoyangGovernCount($uid)
{
	checkLuoyangOwn($uid);
	$curDate = sql_fetch_one_cell("select unix_timestamp(curdate())");
	$lastGovernInfo = sql_fetch_one("select * from mem_city_schedule where cid='215265'");
	if(empty($lastGovernInfo)){
		sql_query("insert into mem_city_schedule(`cid`,`last_govern_time`) values('215265','0')");
	}
	if(intval($lastGovernInfo['last_govern_time'])<intval($curDate)){
		sql_query("update mem_city_schedule set last_govern_time='0',govern_count='0' where cid='215265'");
	}
	
	$hasGovernCount = sql_fetch_one_cell("select govern_count from mem_city_schedule where cid='215265'");
	$ret = array();
	$ret[] = $hasGovernCount;
	return $ret;
}
function checkLuoyangOwn($uid)
{
	$startTime = getAssingStartTime();
	if(!sql_check("select 1 from log_luoyang_belong where uid='$uid' and time>=$startTime")){    //本周有把洛阳分配给自己的记录
		throw new Exception($GLOBALS['luoyang']['can_not_send']);
	}
	$luoyangInfo = sql_fetch_one("select * from sys_luoyang_info");
	if(intval($luoyangInfo['uid'])!=$uid){
		throw new Exception($GLOBALS['luoyang']['can_not_send']);
	}
}
function governLuoyangOthers($uid,$param)
{
	//政令类型 0 收税 1 抽丁 2 征粮 3 收编  4裁军
	$type = intval(array_shift($param));
	$xCoord = intval(array_shift($param));
	$yCoord = intval(array_shift($param));
	if($xCoord<0||$yCoord<0||$xCoord>500||$yCoord>500)throw new Exception($GLOBALS['luoyang']['govern_coord_error']);
	
	checkLuoyangOwn($uid);
	$targetCid = $yCoord*1000+$xCoord;	
	$userCid = sql_fetch_one_cell("select lastcid from sys_user where uid='$uid'");
	checkGovernCondition($uid,$targetCid);
	
	$cityname = sql_fetch_one_cell("select name from sys_city where cid='215265'");
	$msg="";
	$x=265;
	$y=215;
	$tuid = sql_fetch_one_cell("select uid from sys_city where cid='$targetCid'");
	if($type==0){//征税
	
		$totalCount = sql_fetch_one_cell("select gold from mem_city_resource where cid='$targetCid'");
		$addCount=	floor($totalCount*0.2);	
		
		addCityResources($targetCid,0,0,0,0,(0-$addCount));
		addCityResources($userCid,0,0,0,0,$addCount);
		
		$report=sprintf($GLOBALS['governOthers']['gold_report'],$cityname,$x,$y,$addCount);
		sendReport($tuid,3,26,$userCid,$targetCid,$report);
		$msg=sprintf($GLOBALS['governOthers']['gold_suc'],$addCount);
	}else if($type==1) {//抽丁
		$totalCount = sql_fetch_one_cell("select people from mem_city_resource where cid='$targetCid'");
		$addCount=	floor($totalCount*0.3);	
			
		addCityPeople($targetCid,(0-$addCount));	
		addCityPeople($userCid,$addCount);
		
		$report=sprintf($GLOBALS['governOthers']['people_report'],$cityname,$x,$y,$addCount);
		sendReport($tuid,3,28,$userCid,$targetCid,$report);
		$msg=sprintf($GLOBALS['governOthers']['people_suc'],$addCount);
	}else if($type==2) {//征粮
		$totalCount = sql_fetch_one_cell("select food from mem_city_resource where cid='$targetCid'");		
		$addCount=	floor($totalCount*0.5);
	
		addCityResources($targetCid,0,0,0,(0-$addCount),0);
		addCityResources($userCid,0,0,0,($addCount),0);
		
		$report=sprintf($GLOBALS['governOthers']['food_report'],$cityname,$x,$y,$addCount);
		sendReport($tuid,3,42,$userCid,$targetCid,$report);
		$msg=sprintf($GLOBALS['governOthers']['food_suc'],$addCount);		
	}else if($type==3) { //收编 
		$row = sql_fetch_one("select a.sid,a.count from sys_city_soldier a,cfg_soldier b where cid='$targetCid' and a.sid=b.sid and b.fromcity=1 order by count desc limit 1");		
		$sid=1;
		$totalCount=0;
		if ($row){
			$sid = $row["sid"];
			$totalCount = $row["count"];
		}
		$sname = sql_fetch_one_cell("select name from cfg_soldier where sid = $sid");
		$addCount=	floor($totalCount*0.05);		
		addCitySoldier($targetCid,$sid,(0-$addCount));
		$report=sprintf($GLOBALS['governOthers']['incorporation_report'],$cityname,$x,$y,$sname,$addCount);
		sendReport($tuid,3,43,$userCid,$targetCid,$report);
		
		$meaddcount= floor($addCount/2);
		addCitySoldier($userCid,$sid,$meaddcount);
		$msg=sprintf($GLOBALS['governOthers']['incorporation_suc'],$sname,$meaddcount);		
	}else if($type==4) { //裁军
		$row = sql_fetch_one("select sid,count from sys_city_soldier where cid='$targetCid' order by count desc  limit 1");
		$sid=1;
		$totalCount=0;
		if ($row){
			$sid = $row["sid"];
			$totalCount = $row["count"];
		}
		$sname = sql_fetch_one_cell("select name from cfg_soldier where sid = $sid");
		$addCount=	floor($totalCount*0.1);
		addCitySoldier($targetCid,$sid,(0-$addCount));
		$report=sprintf($GLOBALS['governOthers']['disarmament_report'],$cityname,$x,$y,$sname,$addCount);	
		sendReport($tuid,3,44,$userCid,$targetCid,$report);
		$msg=sprintf($GLOBALS['governOthers']['disarmament_suc'],$sname,$addCount);		
	}
	sql_query("update mem_city_schedule set govern_count=govern_count+1,last_govern_time=unix_timestamp() where cid='215265'");
	sql_query("update mem_city_schedule set last_be_govern_time=unix_timestamp() where cid='$targetCid'");
	$ret = array();
	$ret[] = $msg;
	return $ret;
}
function checkGovernCondition($uid,$tCid)
{	
	$targetCity = sql_fetch_one("select uid,type,province from sys_city where cid='$tCid'");
	if(empty($targetCity))throw new Exception($GLOBALS['luoyang']['target_is_not_city']);
	if($uid==intval($targetCity['uid']))throw new Exception($GLOBALS['luoyang']['target_can_not_user']);
	if(intval($targetCity['province'])<1||intval($targetCity['province'])>13)throw new Exception($GLOBALS['luoyang']['over_govern_range']);
	
	$targetUnionId = sql_fetch_one_cell("select union_id from sys_user where uid={$targetCity['uid']}");
	$userUnionId = sql_fetch_one_cell("select union_id from sys_user where uid='$uid'");
	if($targetUnionId==$userUnionId)throw new Exception($GLOBALS['luoyang']['target_can_not_union']);
	
	if(sql_check("select 1 from mem_city_schedule where cid='215265' and govern_count>=10"))throw new Exception($GLOBALS['luoyang']['target_over_max']);
	
	$lastBeGovernInfo =  sql_fetch_one("select * from mem_city_schedule where cid='$tCid'");
	if(empty($lastBeGovernInfo)){
		sql_query("insert into mem_city_schedule(`cid`,`last_be_govern_time`) values('$tCid','0')");
		$lastBeGovernTime=0;
	}else{
		$lastBeGovernTime = $lastBeGovernInfo['last_be_govern_time'];
	}
	$curDate = sql_fetch_one_cell("select unix_timestamp(curdate())");
	if(intval($lastBeGovernTime)>=$curDate)throw new Exception($GLOBALS['governOthers']['target_has_been_govern']);
	
	$targetCityType = intval($targetCity['type']);
	if($targetCityType==5)$targetCityType=0;
	if($targetCityType==0)
	{
		$myuserstate=sql_fetch_one("select forbiend,vacend,unix_timestamp() as nowtime from sys_user_state where uid={$targetCity['uid']} and (forbiend>unix_timestamp() or vacend>unix_timestamp())");
		if(!empty($myuserstate))
		{
			if($myuserstate['forbiend']>$myuserstate['nowtime'])
			{
				//封禁
				throw new Exception($GLOBALS['governOthers']['target_not_in_war']);
			}
			else if ($myuserstate['vacend']>$myuserstate['nowtime'])
			{
				//休假
				throw new Exception($GLOBALS['governOthers']['target_not_in_war']);
			}
		}
	}
}
?>