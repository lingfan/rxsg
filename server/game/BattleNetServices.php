<?php
function getUserInfo($uid,$param) 
{
    $sessionfile = "./sessions/".$uid;
    $sessionid = @file_get_contents($sessionfile);
    $sid=0;
    if(is_array($param))$sid=array_shift($param);
		else $sid=$param;
	if ($sessionid === FALSE || $sid != $sessionid)
    {
        return array(0=>-1);
    }    
   	$ret=array(0=>1);
   	$userinfo=sql_fetch_one("select passport,passtype,name,rank,flagchar,union_id,prestige,sex,face,union_pos,nobility,officepos from sys_user where uid=$uid");
   	$userinfo["famous_city_no"]=sql_fetch_one_cell("select count(1) from sys_city where uid=$uid and type>0 and type<5");
   	$ret[]=$userinfo;
   	if($userinfo["union_id"]>0){
   		$ret[]=sql_fetch_one("SELECT id,su.rank as rank_prestige,su.name,u1.name as leader,u2.name as creator,su.prestige,su.member,CASE WHEN suc.count IS NULL THEN 0 ELSE suc.count END AS city FROM sys_union su
			JOIN sys_user u1 ON u1.uid=su.leader 
			JOIN sys_user u2 ON u2.uid=su.creator
			LEFT JOIN sys_union_city suc ON su.id=suc.unionid WHERE su.id=$userinfo[union_id]");
	}else{
		$ret[]=false;
	}
   	return $ret; 	
}

function initBattle($uid,$param)
{
	$tasks=explode(",",array_shift($param));
	$bid=array_shift($param);
	
	$currentBid = sql_fetch_one_cell("select bid from sys_user_battle_state where uid=$uid and state=0 limit 1");
	$queueBid = sql_fetch_one_cell("select bid from sys_battlenet_waiting_queue where uid=$uid limit 1");
	if ($currentBid>0 || ($queueBid>0&&$queueBid!=$bid)) {//已经在战场//排队的不是要开的战场
		return;
	}
	
	
	deleteOldBattleTasks($uid);
	foreach($tasks as $task){
		sql_query("insert into sys_user_task (uid,tid,state) values ($uid,'$task',0) on duplicate key update state=0");
	}
	
	
	$battlefieldid=array_shift($param);
	$battlefieldid=0;//不要和本服的战场id混在一起
	$unionid=array_shift($param);
	$level=array_shift($param);
	sql_query("insert into sys_alarm (uid,battle) values ('$uid',1) on duplicate key update battle=1");
	sql_query("delete from sys_battlenet_waiting_queue where uid=$uid and bid='$bid'");//等待队列清理
	sql_query("insert ignore into sys_user_battle_state (uid,bid,battlefieldid,unionid,level,in_cross_battle) values($uid,$bid,$battlefieldid,$unionid,$level,1)");
}

function initChibi($uid,$param)
{
	$bid=array_shift($param);
	$battlefieldid=array_shift($param);
	$unionid=array_shift($param);
	$level=array_shift($param);
	sql_query("delete from sys_user_chibi_state where uid=$uid");
	sql_query("replace into sys_user_chibi_state (uid,bid,battlefieldid,unionid,level) values($uid,$bid,$battlefieldid,$unionid,$level)");
}

function getHeroInfo($uid,$param)
{
	$hid=array_shift($param);
	$setstate=array_shift($param);
	$ret=array(0=>1);
	//$heroInfo=sql_fetch_one("select * from sys_city_hero where hid=$hid and state=0 and hero_health=0");
	$heroInfo=sql_fetch_one("select * from sys_city_hero s left join mem_hero_blood m on s.hid=m.hid where s.hid=$hid and state=0 and hero_health=0");
	if(empty($heroInfo)){
		return array(0=>0);
	}
	$ret[]=$heroInfo;
//	if(sql_check("select 1 from sys_user_battle_state where uid=$uid and in_cross_battle=1")){
//		if(sql_query("update sys_user_battle_state set in_battle_hids=concat(in_battle_hids,',$hid') where uid=$uid and in_cross_battle=1")){
//		  sql_query("update sys_city_hero set state=2 where hid='$hid'");
//		}
//	}
	
//	$passtype = sql_fetch_one_cell("select passtype from sys_user where uid='$uid'");
//	if ($passtype==='uuyx'||$passtype==='tw') {
//		try{
//			if ($passtype==='tw') {
//				$url="http://hot3.wayi.com.tw/SSWarData.asp";
//			}else{
//				$url="http://sg.uuyx.com/kfzcInterface/index.aspx";
//			}
//			$key = "7B7571C8AB044DCC8AA891ABA7EC9938";
//			$sendParam['DataFlag'] = 1;
//			$sendParam['Sign'] = md5($sendParam['DataFlag'].$key);
//			$sendParam['MonarchData'] = getPtUserInfo($uid,$param);		
//			$sendParamtwo['DataFlag'] = 2;
//			$sendParamtwo['Sign'] = md5($sendParamtwo['DataFlag'].$key);
//			$sendParamtwo['FamousData'] = getPtHeroInfo($uid,$hid);
//			$sendParamtwo['EquipData'] = getPtEquipInfo($uid,$hid);
//			$curl=new cURL();
//			$result=$curl->post($url,$sendParam);
//			$resulttwo=$curl->post($url,$sendParamtwo);
//		}catch (Exception $e) {			
//		}
//	}
	return $ret;
}

function getHeroInfo4Chibi($uid,$param)
{
	$hid=array_shift($param);
	$setstate=array_shift($param);
	$ret=array(0=>1);
	//$heroInfo=sql_fetch_one("select * from sys_city_hero where hid=$hid and state=0 and hero_health=0");
	$heroInfo=sql_fetch_one("select * from sys_city_hero s left join mem_hero_blood m on s.hid=m.hid where s.hid=$hid and state=0 and hero_health=0");
	if(empty($heroInfo)){
		return array(0=>0);
	}
	$ret[]=$heroInfo;
	if(sql_check("select 1 from sys_user_chibi_state where uid=$uid")){
		sql_query("update sys_user_chibi_state set in_battle_hids=concat(in_battle_hids,',$hid') where uid=$uid");
	}
	return $ret;
}

function checkChibiHero($uid, $param)
{
	$chibiHids = array_shift($param);
	$in_battle_hids=sql_fetch_one_cell("select in_battle_hids from sys_user_chibi_state where uid='$uid' limit 1");
	if (empty($chibiHids)) {
		$chibiHids="-1";
	}
	if (empty($in_battle_hids)) {
		$in_battle_hids="-1";
	}
	sql_query("update sys_user_chibi_state set in_battle_hids='$chibiHids' where uid='$uid'");
	sql_query("update sys_city_hero set state=0 where state=2 and hid in ($in_battle_hids) and hid not in ($chibiHids)");
}

function sendBattleReport($uid,$param)
{
	$originCid=array_shift($param);
	$originCityName=array_shift($param);
	$happenCid=array_shift($param);
	$targetCityName=array_shift($param);
	$title=array_shift($param);
	$stype=array_shift($param);;
	$batteID=array_shift($param);
	$content=array_shift($param);
	 
	sql_query("insert into sys_report (`uid`,`origincid`,`origincity`,`happencid`,`happencity`,`title`,`type`,`time`,`read`,`battleid`,`content`,`from_battlenet`) values ('$uid','$originCid','$originCityName','$happenCid','$targetCityName','$title','$stype',unix_timestamp(),'0','$batteID','$content',1)");
	sql_query("insert into sys_alarm (uid,report) values ('$uid',1) on duplicate key update report=1");
	return array();
}

function sendCBBattleReport($uid,$param)
{
	$originCid=array_shift($param);
	$originCityName=array_shift($param);
	$happenCid=array_shift($param);
	$targetCityName=array_shift($param);
	$title=array_shift($param);
	$stype=array_shift($param);;
	$batteID=array_shift($param);
	$content=array_shift($param);
	 
	sql_query("insert into sys_report (`uid`,`origincid`,`origincity`,`happencid`,`happencity`,`title`,`type`,`time`,`read`,`battleid`,`content`,`from_battlenet`) values ('$uid','$originCid','$originCityName','$happenCid','$targetCityName','$title','$stype',unix_timestamp(),'0','$batteID','$content',2)");
	sql_query("insert into sys_alarm (uid,report) values ('$uid',1) on duplicate key update report=1");
	return array();
}

function sendCBBattleMail($uid,$param)
{
	$title=array_shift($param);
	$content=array_shift($param);
	
	$mid = sql_insert("insert into sys_mail_sys_content (`content`,`posttime`) values ('$content',unix_timestamp())");
	sql_query("insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$uid','$mid','$title','0',unix_timestamp())");
	sql_query("insert into sys_alarm (`uid`,`Mail`) values('$uid', '1') on duplicate key update `Mail`='1'");
	return array();
}

function addHeroExpService($uid,$param)
{
	$hid=array_shift($param);
	$exp=array_shift($param);
	addHeroExp($hid,$exp);
}
function sendTroopAlarm($uid,$param)
{
	sql_query("insert into sys_alarm (uid,troops) values ('$uid',1) on duplicate key update troops=1");
	return array();
}

function saveHeroToCity($uid,$param)
{
	//先暴力了，直接给丫回去
  $hid=0;
  if(is_array($param))$hid=array_shift($param);
	else $hid=$param;
	sql_query("update sys_city_hero set state=0 where hid='$hid' ");
}

function quitBattleNet($uid,$param)
{
	$battlefieldid=array_shift($param);
	$hidarr=explode(",",array_shift($param));
	$arealevel=array_shift($param);
	$msg=array_shift($param);
	
	//检查有效么
	if(!sql_check("select 1 from sys_user_battle_state where uid=$uid and in_cross_battle=1")){
		return;
	}
	//将领都先扔回去
	foreach($hidarr as $hid) {
		sql_query("update sys_city_hero set state=0 where hid='$hid' and state=2");
	}
	$in_cross_hids=sql_fetch_one_cell("select in_battle_hids from sys_user_battle_state where uid=$uid and in_cross_battle=1 limit 1");
	if(!empty($in_cross_hids)){
		$hidWithTroop = sql_fetch_one_cell("select group_concat(hid) from sys_troops where hid in ($in_cross_hids)");
		if (empty($hidWithTroop)) {
			$hidWithTroop = "-1";
		}
		sql_query("update sys_city_hero set state=0 where hid in ($in_cross_hids) and state=2 and hid not in ($hidWithTroop)");
	}
	sql_query("delete from sys_battlenet_waiting_queue where uid='$uid'");
	sql_query("insert ignore into log_user_battlenet(uid,area_level,battlefieldid,quittime) values($uid,$arealevel,$battlefieldid,unix_timestamp())");
	sql_query("delete from sys_user_battle_state where uid='$uid' and in_cross_battle=1");
	sendReport($uid,0,40,0,0,$msg);
}

function quitChibiNet($uid,$param)
{
	$battlefieldid=array_shift($param);
	//$hidarr=explode(",",array_shift($param));
	$arealevel=array_shift($param);
	$msg=array_shift($param);
	//将领都先扔回去
//	foreach($hidarr as $hid) {
//		sql_query("update sys_city_hero set state=0 where hid='$hid' and state=2");
//	}
//	$in_cross_hids=sql_fetch_one_cell("select in_battle_hids from sys_user_battle_state where uid=$uid and in_cross_battle=1 limit 1");
//	if(!empty($in_cross_hids)){
//		sql_query("update sys_city_hero set state=0 where hid in ($in_cross_hids) and state=2");
//		sql_query("delete from sys_user_battle_state where uid='$uid' and in_cross_battle=1");
//		sql_query("insert ignore into log_user_battlenet(uid,area_level,battlefieldid,quittime) values($uid,$arealevel,$battlefieldid,unix_timestamp())");
//		sendReport($uid,0,40,0,0,$msg);
//	}
	sql_query("delete from sys_user_chibi_state where uid=$uid");
}

function getGoodsCount($uid,$param)
{
	return sql_fetch_one_cell("select `count` from sys_goods where uid='$uid' and gid='$param'");
}

function reduceBattleGoods($uid,$param){
	$gid=array_shift($param);
	$count=array_shift($param);
	$type=array_shift($param);
	reduceGoods($uid,$gid,$count,$type);
}

function sendBattleReport_1($uid,$param){
	$title=array_shift($param);
	$msg=array_shift($param);
	sendReport($uid,0,$title,0,0,$msg);
}

function getUserNobility($uid,$param){
	return sql_fetch_one_cell("select nobility from sys_user where uid=$uid");
}

function stopBattleBlink($uid,$param) {
	sql_query("update sys_alarm set battle=0 where uid=$uid");
}

function getHeroAttriAndBuf($uid,$param) {
	$hid=array_shift($param);
	$cid=array_shift($param);
	$ret=array();
	$ret[]=sql_fetch_rows("select * from sys_hero_attribute where hid=$hid");
	$ret[]=sql_fetch_rows("select * from mem_hero_buffer where hid=$hid");
	$ret[]=sql_fetch_rows("select * from mem_user_buffer where uid=$uid");
	$ret[]=sql_fetch_rows("select * from sys_city_technic where cid=$cid");
	
	return $ret;
}
function reduceHeroArmorHP($uid,$param){
	$hid=array_shift($param);
	$reduce=array_shift($param);
	if ($hid>0 && $uid>NPC_UID_END){
		$armorsid=sql_fetch_one_cell("select sid from sys_hero_armor where hid=$hid order by rand() limit 1");
		if (!empty($armorsid))
		{
			sql_query("update sys_user_armor set hp=GREATEST(0,hp-$reduce) where uid=$uid and sid=$armorsid");
			regenerateHeroAttri($uid,$hid);
		}
	}
}

function isInBattle($uid,$param) {
	$bid = sql_fetch_one_cell("select bid from sys_user_battle_state where uid=$uid and state=0 limit 1");
	$queueBid = sql_fetch_one_cell("select bid from sys_battlenet_waiting_queue where uid=$uid limit 1");
	if ($bid==6001 || $queueBid==6001 || $bid==9001|| $queueBid==9001) {	
		if ($bid==6001 || $queueBid==6001) {
			$battleState = sendRemoteRequest($uid,"getUserBattleState");				
		}else{
			$battleState = sendRemote9001Request($uid,"getUserBattleState");
		}
		$state = array_shift($battleState);//1:在战场 2：在等待队列 0：空闲
		if ($state!=1) {
			$in_cross_hids=sql_fetch_one_cell("select in_battle_hids from sys_user_battle_state where uid=$uid and in_cross_battle=1 limit 1");
			if(!empty($in_cross_hids)){//卡在跨服的将领也仍回去
				$hidWithTroop = sql_fetch_one_cell("select group_concat(hid) from sys_troops where hid in ($in_cross_hids)");
				if (empty($hidWithTroop)) {
					$hidWithTroop = "-1";
				}
				sql_query("update sys_city_hero set state=0 where hid in ($in_cross_hids) and state=2 and hid not in ($hidWithTroop)");
			}
			sql_query("delete from sys_user_battle_state where uid=$uid and state=0");
			$bid = 0;
		}
		if ($state!=2) {
			sql_query("delete from sys_battlenet_waiting_queue where uid=$uid");
			$queueBid = 0;
		}	
	}
	
	if ($bid>0 || $queueBid>0) {
		return true;
	}else{
		return false;
	}
}

function quitBattleNetByUid($uid,$param) {
	sql_query("update sys_city_hero set state=0 where state=2 and hid in (select group_concat(in_battle_hids) from sys_user_battle_state where in_cross_battle=1 and uid='$uid')");
	sql_query("delete from sys_user_battle_state where in_cross_battle=1 and uid = '$uid'");
}

function getPtUserInfo ($uid, $param)
{
    $serverid = THE_SERVER_ID;
    $servername = SERVER_NAME;
    $battleinfo = sendRemoteRequest($uid,"getBattleNetUserInfo");
    $userinfo = sql_fetch_one("select uid,sex,face,name,union_id,nobility,officepos,prestige,achivement_point from sys_user where uid=$uid");
    $union = sql_fetch_one_cell("select name from sys_union where id='" . $userinfo['union_id'] . "'");
    $nobility = sql_fetch_one_cell("select name from cfg_nobility where id='" . $userinfo['nobility'] . "'");
    $officepos = sql_fetch_one_cell("select name from cfg_office_pos where id='" . $userinfo['officepos'] . "'");
    $userinfo['name'] = str_replace('|','$',$userinfo['name']); 
    $userinfo['name'] = str_replace('&','$aaaaaaa$',$userinfo['name']); 
    $achivement = $userinfo['achivement_point'];
    unset($userinfo['achivement_point']);
    unset($userinfo['officepos']);
    unset($userinfo['nobility']);
    unset($userinfo['union_id']);
    array_push($userinfo, $serverid);
    array_push($userinfo, $servername);
    array_push($userinfo, $union);
    array_push($userinfo, $battleinfo['battle_score']);
    array_push($userinfo, $battleinfo['gongxun']);
    array_push($userinfo, $nobility);
    array_push($userinfo, $officepos);
    array_push($userinfo, $achivement);
    array_push($userinfo, $battleinfo['join_times']);
    array_push($userinfo, $battleinfo['kill_times']);
    $result =  implode('|', $userinfo);
    return $result;
}
function getPtHeroInfo ($uid,$param)
{
	$serverid = THE_SERVER_ID;
    $hid = $param;   
    $heroInfo = sql_fetch_one("select hid,uid,sex,face,name,level,(command_base+command_add_on+level) command,(bravery_base+bravery_add+bravery_add_on) bravery,(wisdom_base+wisdom_add+wisdom_add_on) wisdow,(affairs_base+affairs_add+affairs_add_on) affairs,speed_add_on as speed,((bravery_base+bravery_add+bravery_add_on)*10+attack_add_on) as attack,((wisdom_base+wisdom_add+wisdom_add_on)*10+defence_add_on) as defence from sys_city_hero where hid='$hid'");//and hero_health=0 and state=0 
    $blood = sql_fetch_one("select `force`,`energy` from mem_hero_blood where hid='$hid'");
    $heroInfo['name'] = str_replace('|','$',$heroInfo['name']);
    $heroInfo['name'] = str_replace('&','$aaaaaaa$',$heroInfo['name']);
    $blood['serverid'] = $serverid;
   	$result = 0;
    if(!empty($heroInfo) && !empty($blood)){
    	$ret = array_merge($heroInfo, $blood);
    	$result =  implode('|', $ret);
    }
   
    //print_r($result);
    return $result;
}
function getPtEquipInfo ($uid,$param)
{
    $hid = $param;
    $armors = sql_fetch_rows("select ca.tieid,sha.sid,sha.hid,if(ca.image>0,ca.image,ca.id) as image,ca.type,sha.spart,ca.name,'0',sua.strong_level,ca.hero_level,concat(round(sua.hp/10),'/',sua.hp_max) as hp,ca.description,ca.attribute,sua.embed_pearls,sua.embed_holes from sys_hero_armor sha left join sys_user_armor sua on sua.sid=sha.sid left join cfg_armor ca on sha.armorid=ca.id where sha.hid='$hid'");
    $result = array();
    foreach ($armors as &$armor) {
        $tieid = array_shift($armor);
        $tie_attribute = sql_fetch_rows("select * from cfg_tie_attribute where tieid='$tieid'");
        $count = sql_fetch_one_cell("select count(1) `count` from sys_hero_armor sha left join cfg_armor ca on sha.armorid=ca.id where ca.tieid='$tieid' and sha.hid='$hid'");
        $attribute = array();//print_r($count);exit;
        foreach ($tie_attribute as &$values) {
            array_push($values, $count);
            $attribute[] = implode('^', $values);
        }
        $att = implode(';', $attribute);
        $sid = array_shift($armor);
        $deify_attribute = sql_fetch_rows("select * from sys_user_tie_deify_attribute where sid='$sid'");
        $deify = array();
        foreach ($deify_attribute as &$values) {
            $deify[] = implode('^', $values);
        }
        $dei = implode('$', $deify);
        $arm = array();
        if ($armor['attribute']) {
            $arm[] = str_replace(',', '^', $armor['attribute']);
        }
        if ($armor['embed_pearls']) {
            $arm[] = str_replace(',', '^', $armor['embed_pearls']);
        }else{
        	$arm[]='';
        }
        if ($armor['embed_holes']) {
            $arm[] = str_replace(',', '^', $armor['embed_holes']);
        }else{
        	$arm[]='';
        }
        unset($armor['embed_holes']);
        unset($armor['embed_pearls']);
        unset($armor['attribute']);
        $arm[] = $att;
        $arm[] = $dei;
        $armor[] = implode('$', $arm);
        $result[] = implode('|', $armor);
    }
    $ret = implode('$##$', $result);
    //print_r($a);
    return $ret;	
}

function getUserTactics($uid) {
	$tactics = sql_fetch_one ( "select * from sys_user_tactics where uid='$uid'" );
	return $tactics;
}

function sendSysInformAll($uid, $param) {
	$type=array_shift($param);
	$inuse=array_shift($param);
	$starttime=array_shift($param);
	$endtime=array_shift($param);
	$interval=array_shift($param);
	$scrollcount=array_shift($param);
	$color=array_shift($param);
	$msg=array_shift($param);
	sendSysInform($type,$inuse,$starttime,$endtime,$interval,$scrollcount,$color,$msg);;
}

function bakCrossBattleStateLog($uid, $params) {
	$num = count($params);
	if ($num%2!=0) {
		error_log("bakCrossBattleStateLog: ".print_r($params));
		return;//error
	}
	$param = array();
	for ($i=0;$i<$num/2;$i++){
		$param[$params[$i]]=$params[$num/2+$i];
	}
	sql_query("replace into bak_sys_user_battle_state (uid, battlefieldid,bid,unionid,startcid,level,jointime,sent_troop_count,state,attack_win_score,finish_task_score,kill_enemy_no,be_killed_no,quittime,iswinner,gained_score) values($uid, $param[battlefieldid],$param[bid],$param[unionid],$param[startcid],$param[level],'$param[jointime]',$param[sent_troop_count],$param[state],$param[attack_win_score],$param[finish_task_score],$param[kill_enemy_no],$param[be_killed_no],$param[quittime],$param[iswinner],$param[gained_score])");
}

function sendAllSysMailFromNet($uid,$params)
{
	$title=array_shift($params);
	$content=array_shift($params);
	sendAllSysMail($title,$content);
}

	function addGoodsFromNet($uid,$params){
		$gid=array_shift($params);
		$cnt=array_shift($params);
		$type=array_shift($params);
		if ($cnt==0) {
			return;
		}
		if ($gid==0) {
			return addGift($uid,$cnt,$type);
		}
		
		addGoods($uid,$gid,$cnt,$type);
	}
	function addThingsFromNet($uid,$params){
		$tid=array_shift($params);
		$cnt=array_shift($params);
		$type=array_shift($params);
		if ($cnt==0) {
			return;
		}
		addThings($uid,$tid,$cnt,$type);
	}
	function addArmorFromNet($uid,$params){	
		$armorId=array_shift($params);
		$cnt=array_shift($params);
		$log_type=array_shift($params);
		if ($cnt==0) {
			return;
		}
		$armor=sql_fetch_one("select * from cfg_armor where id='$armorId'");
		if (!empty($armor)) {
			addArmor($uid,$armor,$cnt,$log_type);
		}
	}

/*
 * @desc 更新派往赤壁战场的参数，设置将领为出征状态
 * 
 * @param $uid int 用户的uid
 * @param $param array(),传过来的参数，包括英雄的hid。
 */
function updateHeroOut($uid, $param) {
	$uid = intval($uid);
	$hid = intval($param);
	
	if(sql_check("select 1 from sys_user_battle_state where uid=$uid and in_cross_battle=1")){
		if(sql_query("update sys_user_battle_state set in_battle_hids=concat(in_battle_hids,',$hid') where uid=$uid and in_cross_battle=1")){
		  sql_query("update sys_city_hero set state=2 where hid='$hid'");
		  return 1;
		}
	}
	return 0;
}

function updateHeroOut4Chibi($uid, $param) {
	$uid = intval($uid);
	$hid = intval($param);
	if(sql_check("select 1 from sys_user_chibi_state where uid=$uid")){
		if(sql_query("update sys_user_chibi_state set in_battle_hids=concat(in_battle_hids,',$hid') where uid=$uid")){
		  sql_query("update sys_city_hero set state=2 where hid='$hid'");
		  return 1;
		}

	}
	return 0;
}

/*
 * @desc 将领返回，设置状态为空闲
 * 
 * @param $uid int 用户的uid
 * @param $param array(),传过来的参数，包括英雄的hid。
 */
function updateHeroBack($uid, $param) {
	$uid = intval($uid);
	$hid = intval(array_shift($param));
	$force = intval(array_shift($param));
	$sql = "update sys_city_hero set `state`=0 where hid={$hid} and uid={$uid}";
	sql_query($sql);
	$sql = "update mem_hero_blood set `force`=$force where hid={$hid}";
	sql_query($sql);
}

/**
 * 从赤壁掉落的物品直接增加到本服中
 *
 * @param unknown_type $uid
 * @param unknown_type $param
 */
function addUserGoods($uid, $param) {
	$uid = intval($uid);
	$gid = intval(array_shift($param));
	$count = intval(array_shift($param));
	$type = intval(array_shift($param));
	addGoods($uid,$gid,$count,$type);
	//sql_query("insert into sys_goods (uid,gid,`count`) values ('$uid','$gid','$count') on duplicate key update `count`=`count`+$count");
	//sql_query("insert into log_goods (`uid`,`gid`,`count`,`time`,`type`) values ('$uid','$gid','$count',unix_timestamp(),$type)");
	if($type==1)
		sql_query("insert into log_battle_drop (uid,`count`,time) values ($uid,$count,unix_timestamp()) on duplicate key update count=count+$count, time=unix_timestamp()");
}

/**
 * 从赤壁掉落的物品直接增加到本服中
 *
 * @param unknown_type $uid
 * @param unknown_type $param
 */
function addUserThings($uid, $param) {
	$uid = intval($uid);
	$gid = intval(array_shift($param));
	$count = intval(array_shift($param));
	$type = intval(array_shift($param));
	
	sql_query("insert into sys_things (uid,tid,`count`) values ('$uid','$gid','$count') on duplicate key update `count`=`count`+$count");
	sql_query("insert into log_things (`uid`,`tid`,`count`,`time`,`type`) values ('$uid','$gid','$count',unix_timestamp(),$type)");
	if($type==1)
		sql_query("insert into log_battle_drop (uid,`count`,time) values ($uid,$count,unix_timestamp()) on duplicate key update count=count+$count, time=unix_timestamp()");
}

/**
 * @desc 更新玩家在赤壁的信息，记录完成赤壁成就所需要的数据。
 * @param 
 * @param 
 */
function updateChibiChengjiu($uid, $param) {
	$type = array_shift($param);
	$count = array_shift($param);
	$flag = array_shift($param);
	if ($flag == 0) {
		sql_query("insert into sys_user_chibi_achivement(`uid`,$type) values($uid,$count) on duplicate key update $type=$count");
	} else {
		sql_query("insert into sys_user_chibi_achivement(`uid`,$type) values($uid,$count) on duplicate key update $type=$type+$count");
	}
	checkChibiChengjiu($uid);
}
function checkChibiChengjiu($uid) {
	if (empty($uid)) return;
	
	$gongxun = sql_fetch_one_cell("select gongxun from sys_user_chibi_achivement where uid=$uid");
	$achive = sql_fetch_one("select id,target_value from cfg_achivement where `group`=9 and id=80002");
	if ($gongxun >= $achive['target_value']) {
		finishAchivement($uid,$achive['id']);
	}
	$achive = sql_fetch_one("select id,target_value from cfg_achivement where `group`=9 and id=80003");
	if ($gongxun >= $achive['target_value']) {
		finishAchivement($uid,$achive['id']);
	}
	
	$win = sql_fetch_one_cell("select win from sys_user_chibi_achivement where uid=$uid");
	$achive = sql_fetch_one("select id,target_value from cfg_achivement where `group`=9 and id=80004");
	if ($win >= $achive['target_value']) {
		finishAchivement($uid,$achive['id']);
	}
	$achive = sql_fetch_one("select id,target_value from cfg_achivement where `group`=9 and id=80005");
	if ($win >= $achive['target_value']) {
		finishAchivement($uid,$achive['id']);
	}
	$achive = sql_fetch_one("select id,target_value from cfg_achivement where `group`=9 and id=80006");
	if ($win >= $achive['target_value']) {
		finishAchivement($uid,$achive['id']);
	}
	
	$monster = sql_fetch_one_cell("select monster from sys_user_chibi_achivement where uid=$uid");
	$achive = sql_fetch_one("select id,target_value from cfg_achivement where `group`=9 and id=80007");
	if ($monster >= $achive['target_value']) {
		finishAchivement($uid,$achive['id']);
	}
	$achive = sql_fetch_one("select id,target_value from cfg_achivement where `group`=9 and id=80008");
	if ($monster >= $achive['target_value']) {
		finishAchivement($uid,$achive['id']);
	}
}

?>