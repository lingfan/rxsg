<?php
require_once ("./utils.php");
define('ASSEMBLE_NET_ENABLE',true);
//define('ASSEMBLE_NET_URL',"http://jz.game.ledu.com/assemble/AssembleNetGatway.php");
define ( 'ASSEMBLE_NET_URL', "http://localhost/server/server_1130/game/AssembleNetGatway.php");

function CrossAssembleSend($uid,$param)
{
	$commandFunc = strval(array_shift($param));	
	$sendParam = array();	
	
	$getDataFromLocal = false;  //判读是在本服操作还是跨服操作
	switch($commandFunc)
	{
		case "initCrossAssembleInfo":
			checkjoinCondition($uid);
			$sendParam = getCheckCrossParam($uid);
			break;
		case "loadCrossBeforeUser10":
			break;
		case "startCrossArenaChallenge":
			$sendParam['targetCrossUid'] = array_shift($param);
			$sendParam['heroInfo']=sql_fetch_one("select group_concat(distinct(hid)) as hids,count(hid) as herocount from sys_city_hero where uid='$uid'");
			doCheckCrossCount($uid,array_shift($param));
			break;
		case "loadUserCrossBattleHero":			
			break;
		case "loadFirstUser":
			break;
		case "refreshCrossAssembleRankInfo":
			$sendParam['selectedIndex']=array_shift($param);
			break;
		case "holdCrossBattleHeroInfo":
			$sendParam = holdCrossBattleHero($uid,$param);
			break;
		case "getAllHeroByUid":
			$getDataFromLocal=true;
			break;
		case "getCrossArenaRankRewardInfo":		
			break;
		case "getCrossArenaRankReward":
			break;
		case "CrossGotoSelectedRank":
			$toRank = array_shift($param);
			$sendParam['selectedRank']=$toRank;
			checkGotoCondition($uid,$toRank);
			break;
		case "getCrossArenaReward":
			$sendParam['crossUid']=array_shift($param);   
			break;
		case "clearCrossArenaCoolTime":
			checkCrossCoolMoney($uid);
			break;
		case "getCrossArenaRankBefore10":
			break;
		default:return;
		
	}		
	$rRet = array();
	if(!$getDataFromLocal){
		$ret = sendRemoteAssembleRequest($uid,$commandFunc,$sendParam);
		$command = array_shift($ret);  //commandFunc
		$rRet[] = $command;
		if($command=="loadUserCrossBattleHero"){   //取下将领的信息
			$tmpHidsArr = array_shift($ret);
			$rRet[] = getUserHeroInfo($tmpHidsArr);
			return $rRet;
		}else if($command=="initCrossAssembleInfo"){
			$crossAssemble = array_shift($ret);	
			doParseSysInform(array_shift($crossAssemble));
			checkAddUserDesignation($uid, array_shift($crossAssemble));			
			$result[] = getUserHeroInfo($crossAssemble[3]);
			array_splice($crossAssemble,3,1,$result);  //把将领的信息从本服取下
			$rRet[] = $crossAssemble;
			return $rRet;
		}else if($command=="getCrossArenaRankReward"){
			$rank = array_shift($ret);
			$rRet[] =addCrossRankReward($uid,$rank);
			return $rRet;
		}else if($command=="getCrossArenaRankRewardInfo"){
			$tmpArenaRank = array_shift($ret);
			$tmpArenaArr[] = array_shift($tmpArenaRank);
			$rankRewardConfArr = sql_fetch_rows("select * from cfg_activity_rank_reward where actid='1000002'");
			$rankRewardArr = parseRewardGood($rankRewardConfArr);
			$tmpArenaArr[] = $rankRewardArr;
			$tmpArenaArr[] = array_shift($tmpArenaRank);
			doParseSysInform(array_shift($tmpArenaRank));	
			checkAddUserDesignation($uid,array_shift($tmpArenaRank));		
			$rRet[] = $tmpArenaArr;
			return $rRet;
		}else if($command=="startCrossArenaChallenge"){
			$battle_result = array_shift($ret);
			$changlleCount = array_shift($battle_result);
			reduceUserMoney($uid,$changlleCount);
			$tmpBattleResult[] = array_shift($battle_result);
			$tmpBattleResult[] = array_shift($battle_result);
			$attacGetkReward = array_shift($battle_result);
			if(count($attacGetkReward)>0){
				$tmpBattleResult[1]['reward']=doGetCorssAssembleBattleReward($uid,array_shift($attacGetkReward));
			}
			$rRet[] = $tmpBattleResult;
			return $rRet;
		}else if($command=="getCrossArenaReward"){
			$userCrossAssembleInfo = array_shift($ret);
			$rRet[] = doGetCorssAssembleReward($uid,array_shift($userCrossAssembleInfo));
			return $rRet;
		}else if($command=="CrossGotoSelectedRank"){
			$gotoResult = array_shift($ret);
			$gotoRank =array_shift($gotoResult);
			addMoney($uid, -$gotoRank, 888);
			$rRet[] = $gotoResult;
			return $rRet;
		}	   
		$rRet[] = $ret;   //返回值
	}else{
		$ret[] = $commandFunc;
		$ret[] = $commandFunc($uid,$param);
		return $ret;
	}
	return $rRet;
}
function getCheckCrossParam($uid)
{
	$param = sql_fetch_one("select a.*,b.force as energy from sys_city_hero a,mem_hero_blood b where a.hid=b.hid and a.uid='$uid' and herotype='1000'");  //君主将信息
	$userLevel = sql_fetch_one_cell("select level from sys_user_level where uid='$uid'");
	if(empty($userLevel))$userLevel=1;
	$param['userLevel'] = $userLevel;   //君主将修为等级
	$passport = sql_fetch_one_cell("select passport from sys_user where uid='$uid'");
	$param['passport']=$passport;	
	return $param;
}
function getUserHeroInfo($hidArr)
{
	$ret = array();	
	if(!empty($hidArr)){
		$hid1 = array_shift($hidArr);
		$hid2 = array_shift($hidArr);
		$hid3 = array_shift($hidArr);
		
		$hero1Info = sql_fetch_one("select a.*,b.force from sys_city_hero a,mem_hero_blood b where a.hid=b.hid and a.hid='$hid1'");
		$hero2Info = sql_fetch_one("select a.*,b.force from sys_city_hero a,mem_hero_blood b where a.hid=b.hid and a.hid='$hid2'");
		$hero3Info = sql_fetch_one("select a.*,b.force from sys_city_hero a,mem_hero_blood b where a.hid=b.hid and a.hid='$hid3'");
		
		if(!empty($hero1Info))$ret[] = $hero1Info;
		if(!empty($hero2Info))$ret[] = $hero2Info;
		if(!empty($hero3Info))$ret[] = $hero3Info;
	}	
	return $ret;
}

function checkjoinCondition($uid)
{
	//爵位需要达到上造
	$nobility=sql_fetch_one_cell("select nobility from sys_user where uid='$uid'");
    $nobility=getBufferNobility($uid,$nobility);
    if(intval($nobility)<2)throw new Exception($GLOBALS['assemble']['nobility_not_enough']);  //爵位没达到上造的不上竞技场
    
    //必须要有君主将
    $kingHero = sql_fetch_one("select * from sys_city_hero where uid='$uid' and herotype='1000'");
	if(empty($kingHero))throw new Exception($GLOBALS['useGoods']['king_cannot_find']);      
}
function holdCrossBattleHero($uid,$param)
{
	$heroArr = array_shift($param);
	$heroCount = intval(count($heroArr));
	if($heroCount!=3)throw new Exception($GLOBALS['assemble']['hero_count_error']);
	
	$hid1 = intval(array_shift($heroArr));
	$hid2 = intval(array_shift($heroArr));
	$hid3 = intval(array_shift($heroArr));
		
	$userKingHid = sql_fetch_one_cell("select hid from sys_city_hero where uid='$uid' and herotype='1000'");
	if(empty($userKingHid)) throw new Exception($GLOBALS['useGoods']['king_cannot_find']);
	$userHidArr = array($hid1,$hid2,$hid3);
	if(!in_array($userKingHid, $userHidArr)){
		throw new Exception($GLOBALS['assemble']['king_not_in']);
	}
	$attackHeroInfo = sql_fetch_rows("select * from sys_city_hero where uid='$uid' and hid in($hid1,$hid2,$hid3)"); 
	if(count($attackHeroInfo)==1||count($attackHeroInfo)==2){
		$userHeroHidArr = sql_fetch_rows("select hid from sys_city_hero where uid='$uid'");
		if(count($userHeroHidArr)>=3)throw new Exception($GLOBALS['assemble']['need_change_hero']);
	}	
	
	$heroInfo['firsthero'] = sql_fetch_one("select a.*,b.force as energy from sys_city_hero a,mem_hero_blood b where a.hid=b.hid and a.hid='$hid1'");
	$heroInfo['secondhero'] = sql_fetch_one("select a.*,b.force as energy from sys_city_hero a,mem_hero_blood b where a.hid=b.hid and a.hid='$hid2'");
	$heroInfo['threehero'] = sql_fetch_one("select a.*,b.force as energy from sys_city_hero a,mem_hero_blood b where a.hid=b.hid and a.hid='$hid3'");
	
	return $heroInfo;
}
function addCrossRankReward($uid,$rank)
{
	$rewardInfo = sql_fetch_one_cell("select reward from cfg_activity_rank_reward where actid='1000002' and minrank<=$rank and maxRank>=$rank");
	if(empty($rewardInfo))throw new Exception($GLOBALS['kingReward']['not_find_data']);
	
	$ret = array();
	$ret[] = $rank;
	$ret[] = parseAndAddReward($uid, $rewardInfo, 888, 888, 888, 888);
	return $ret;
}
function checkGotoCondition($uid,$gotoRank)
{
	$rankArr = array(50,100,200,300,500);
	if(!in_array($gotoRank, $rankArr))throw new Exception($GLOBALS['assemble']['goto_rank_error']);
	if(sql_check("select 1 from sys_user where uid='$uid' and money<$gotoRank"))throw new Exception($GLOBALS['buyGoods']['no_enough_YuanBao']);
}
function doCheckCrossCount($uid,$hasCount)
{
	if(intval($hasCount)>=10)
	{
		$addCount = intval($hasCount)-9;
		$needMoney = $addCount*5;
		if(sql_check("select 1 from sys_user where uid='$uid' and money<'$needMoney'"))throw new Exception($GLOBALS['buyGoods']['no_enough_YuanBao']);
	}
}
function reduceUserMoney($uid,$count)
{
	if($count>=10)
	{
		$addCount = intval($count)-9;
		$needMoney = $addCount*5;
		addMoney($uid, -$needMoney, 888);
	}
}
function doGetCorssAssembleReward($uid,$userCrossAssembleInfo)
{
	$rewardObj = addCrossAttackArenaReward($uid,$userCrossAssembleInfo);	
	if(is_array($rewardObj['info'])){
		$rewardObj['info']['flag'] = $rewardObj['flag'];
		$rewardObj['info']['count'] = $rewardObj['count'];
		$rewardObj['info']['gtype'] = $rewardObj['gtype'];	
		$subRet[] = 1;
		$addArr[] = $rewardObj['info'];   //为了套用原来的物品弹出框，这里再套一层
		$subRet[] = $addArr;
	}else{
		$subRet[] = 0;
		$subRet[] = $rewardObj['info'].'*'.$rewardObj['count'];
	}
	return $subRet;
}
function addCrossAttackArenaReward($attactUid,$resistArenaInfo)
{	
	$lastCid = sql_fetch_one_cell("select lastcid from sys_user where uid='$attactUid'");
	$rewardGid = intval($resistArenaInfo['rewardid']);	
	$rewardType = sql_fetch_one("select type,base from cfg_assemble_reward where `gid`={$resistArenaInfo['rewardid']}");
	if(empty($rewardType))throw new Exception($GLOBALS['hero']['xidian_unvalid']);
	$rewardCount = doCaulRewardCount(false,intval($resistArenaInfo['endtime']),intval($rewardType['base']));
		
	$rewardArray = array();
	if(intval($rewardType['type'])==0){
		addGoods($attactUid,$rewardGid,$rewardCount,888);
		$rewardArray['flag']=0;
		$rewardArray['info']=sql_fetch_one("select * from cfg_goods where gid=$rewardGid");
		$rewardArray['count']=$rewardCount;
		$rewardArray['gtype']=0;
	}else if(intval($rewardType['type'])==1){
		$armor = sql_fetch_one("select * from cfg_armor where id='$rewardGid'");
		addArmor($attactUid, $armor, $rewardCount, 888);
		$rewardArray['flag']=1;
		$rewardArray['info']=$armor;
		$rewardArray['count']=$rewardCount;
		$rewardArray['gtype']=1;
	}else if(intval($rewardType['type'])==2){
		if($rewardGid==-1){
			addCityResources($lastCid, 0, 0, 0, 0, $rewardCount);
		}else if($rewardGid==-2){
			addCityResources($lastCid,0, 0, 0, $rewardCount, 0);
		}else if($rewardGid==-3){
			addCityResources($lastCid,$rewardCount, 0, 0, 0, 0);
		}
		$goodName = sql_fetch_one_cell("select name from cfg_assemble_reward where gid={$resistArenaInfo['rewardid']}");
		$rewardArray['flag']=2;
		$rewardArray['info']=$goodName;
		$rewardArray['count']=$rewardCount;
	}
	return $rewardArray;
}
function checkCrossCoolMoney($uid)
{
	if(!sql_check("select 1 from sys_user where uid='$uid' and money>=10"))throw new Exception($GLOBALS['buyGoods']['no_enough_YuanBao']);
	addMoney($uid, -10, 888);
}
function doGetCorssAssembleBattleReward($uid,$userCrossAssembleInfo)
{
	$rewardObj = addAttackArenaReward($uid,$userCrossAssembleInfo);	
	$ret = array();
	$ret[] = $rewardObj;
	return $ret;
}
function doParseSysInform($crossInform)
{
	if(empty($crossInform))return ;
	$crossInformTime = intval($crossInform['time']);
	if(sql_check("select 1 from mem_state where state='2222' and value>='$crossInformTime'"))return ;
	sql_query("update mem_state set value='$crossInformTime' where state='2222'");
	
	if(sql_check("select unix_timestamp()>$crossInformTime+600"))return ;   //过了10分钟就不公告了
	$msg = $crossInform['msg'];
	sendSysInform(0,1,0,600,50000,1,16247152,$msg);
	
}
function checkAddUserDesignation($uid,$isAddUserDisgnation)
{
	if($isAddUserDisgnation){
		if(sql_check("select 1 from sys_user_designation where did='26' and uid='$uid'"))return;
		//加一个天下第一称号的buff
		$starttime = sql_fetch_one_cell("select value from mem_state where state='2222'");
		if(empty($starttime))throw new Exception($GLOBALS['waigua']['invalid']);
		
		sql_query("insert into mem_user_buffer(`uid`,`buftype`,`bufparam`,`endtime`) values('$uid','2626','26',$starttime+259200) on duplicate key update endtime=$starttime+259200"); //3天buff
		sql_query("insert into sys_user_designation(`uid`,`did`,`ison`,`state`) values('$uid','26','0','1')");		
	}
}

function sendRemoteAssembleRequest($uid,$commandFunc,$param=array()){	
	@include ("../../server_info.php");
	if(defined("ASSEMBLE_NET_ENABLE") && ASSEMBLE_NET_ENABLE){
		global $BATTLE_NET_URL_ASSEMBLE;
		if (empty($BATTLE_NET_URL_ASSEMBLE)) {
			$BATTLE_NET_URL_ASSEMBLE = ASSEMBLE_NET_URL;
		}
	
		$sendParam=array();
		if(!empty($param)){
			foreach($param as $key=>$value)   //目前只支持双层数组
			{
				if(is_array($value)){
					foreach($value as $subkey=>$subvalue)
					{
						$subParam[$subkey] = urlencode($subvalue);
					}
					$param[$key]=$subParam;
				}else{
					$param[$key]=urlencode($value);
				}			
			}
		}		
		$content=json_encode($param);
		$sendParam["commandFunc"]=$commandFunc;
		$sendParam["from_uid"]=$uid;
		$sendParam["from_server"]=$server_guid;
		$sendParam["sign"]=md5($uid.$commandFunc.$server_guid.BATTLE_NET_KEY);
		$sendParam["content"]=$content;
		$sendParam['time']=time();
		$curl=new cURL();
		$result=$curl->post($BATTLE_NET_URL_ASSEMBLE,$sendParam);
		if($result===FALSE){
			throw new Exception($GLOBALS['battlenet']['cannot_connect_server']);
		}
		$ret = json_decode($result,true);
		
		$flag = array_shift($ret);
		if($flag==1){
			throw new Exception(array_shift($ret));
		}
		return $ret;
	}else {
		throw new Exception("参数异常！");
	}
}