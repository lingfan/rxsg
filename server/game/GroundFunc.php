<?php
require_once("./interface.php");
require_once("./utils.php");
require_once './ExternService.php';
function getGroundInfo($uid,$cid)
{
	$ground = sql_fetch_one("select * from sys_building where `cid`='$cid' and `bid`=".ID_BUILDING_GROUND." order by level desc limit 1");
	if (empty($ground))
	{
		throw new Exception($GLOBALS['getGroundInfo']['no_ground_built']);
	}
	return doGetBuildingInfo($uid,$cid,$ground['xy'],ID_BUILDING_GROUND,$ground['level']);
}

function getTacticsSetting($uid,$cid,$param)
{
	$hid = array_shift($param);
	$ret =  sql_fetch_one("select * from sys_hero_tactics where `hid`='$hid'");
	if (empty($ret))
	{
		$ret = sql_fetch_one("select * from sys_hero_tactics where `hid`='0'");
	}
	return $ret;
}
function setTacticsSetting($uid,$cid,$param)
{
	$hid = array_shift($param);
	$plunder = array_shift($param);
	$invade = array_shift($param);
	$deplunder = array_shift($param);
	$deinvade = array_shift($param);
	$field = array_shift($param);
	sql_query("insert into sys_user_tactics (`hid`,`plunder`,`invade`,`deplunder`,`deinvade`,`field`) values ('$hid','$plunder','$invade','$deplunder','$deinvade','$field') on duplicate key update `plunder`='$plunder',`invade`='$invade',`deplunder`='$deplunder',`deinvade`='$deinvade',`field`='$field'");
	return getGroundInfo($uid,$cid);
}

function startManeuver($uid,$cid,$param)
{
	$attack = array_shift($param);
	$attack = addslashes($attack);
	$resist = array_shift($param);
	$resist = addslashes($resist);
	$mid = sql_insert("insert into mem_maneuver (`state`,`attacksoldiers`,`resistsoldiers`) values ('0','$attack','$resist')");
	$ret = array();
	$ret[] = $mid;
	return $ret;
}
function getManeuverResult($uid,$cid,$param)
{
	$mid = intval(array_shift($param));
	$maneuver = sql_fetch_one("select * from mem_maneuver where id='$mid' and `state`=1");
	$ret = array();
	if (empty($maneuver))    //还在演习中
	{
		$ret[] = 0;
	}
	else
	{
		$ret[] = 1;
		$ret[] = $maneuver;
		sql_query("delete from mem_maneuver where id='$maneuver[id]'");
	}
	return $ret;
}

function checkOutOpen($cid) {
	$wid = cid2wid($cid);
	$tcid = sql_fetch_one_cell("select ownercid from mem_world where wid=$wid");
	if (!empty($tcid)) {
		$cid = $tcid;
	}
	$uid = sql_fetch_one_cell("select uid from sys_city where cid=$cid");
	if (!empty($uid) && $uid>1000) {
		return ;
	}
	$province = sql_fetch_one_cell("select province from mem_world where wid=".cid2wid($cid));
	if (empty($province)) {
		throw new Exception($GLOBALS['out']['targetcity_not_exists']);
	}
	$value = sql_fetch_one_cell("select value from mem_state where state=111");
	switch ($value) {
		case 1:
			if ($province == 14 || $province == 15 || $province == 17 || $province == 18) {
				throw new Exception($GLOBALS['out']['not_open']);
			}
			break;
		case 2:
			if ($province == 14 || $province == 15 || $province == 18) {
				throw new Exception($GLOBALS['out']['not_open']);
			}
			break;
		case 3:
			if ($province == 14 || $province == 15) {
				throw new Exception($GLOBALS['out']['not_open']);
			}
			break;
		case 4:
			if ($province == 14) {
				throw new Exception($GLOBALS['out']['not_open']);
			}
			break;
		default: break;
	}
}
function checkMianZhanCondition($uid,$cid,$targetcid,$task,$serverType)
{
	$targetWid = cid2wid($targetcid);
	$targetMemWorld = sql_fetch_one("select * from mem_world where wid='$targetWid' ");
	if(empty($targetMemWorld)) throw new Exception($GLOBALS['StartTroop']['some_thing_wrong']);
	
	//向大地图的城池进行出军操作
	if(intval($targetMemWorld['type'])==0)
	{
		$targetCityInfo = sql_fetch_one("select * from sys_city where cid='$targetcid'");
		if(empty($targetCityInfo)) throw new Exception($GLOBALS['waigua']['invalid']);
		if($cid==intval($targetCityInfo['cid'])) throw new Exception($GLOBALS['StartTroop']['target_cant_be_current']);
		//13州之外的属于战乱区  不继续往下判断了
		$targetCityProvince = intval($targetCityInfo['province']);
		if($targetCityProvince>13) return;
		//排除马来的 司隶地区  也属于战乱区
		$malaiType = sql_fetch_one_cell("select value from mem_state where state='198'");
		if(intval($malaiType)==1 && $targetCityProvince==1) return;
		//对其他玩家的城池进行出军操作
		if(intval($targetCityInfo['uid'])>1000 && intval($targetCityInfo['uid'])!=$uid)
		{
			//对其他玩家城池的侦查、掠夺、占领进行控制
			if($task>=2&&$task<=4)  //task=2、3、4
			{
				//排除县、郡、州、都 名城
				if(intval($targetCityInfo['type'])>0&&intval($targetCityInfo['type'])<5) return;
				//排除不落之城特种城
				if(getSpacialSoldierId($targetcid)>0 && $serverType!=1) return ;
				// 普通城池和玩家主城  核实被攻击方是否在免战中
				$isMianZhan = sql_fetch_one_cell("select 1 from mem_user_buffer where uid={$targetCityInfo['uid']} and buftype='7' and endtime>unix_timestamp()");
				if(!empty($isMianZhan)) throw new Exception($GLOBALS['StartTroop']['target_in_peace']);
			}	
		}		
	}
}
function StartTroop($uid,$cid,$param)
{
	$hid = intval(array_shift($param));
	$targetcid = intval(array_shift($param));
	$task = intval(array_shift($param));
	$secondAdd = array_shift($param);
	$soldiers = array_shift($param);
	$soldiers = addslashes($soldiers);
	$resource = array_shift($param);
	$resource = addslashes($resource);
    $serverType = sql_fetch_one_cell("select value from  mem_state where state=197");
    
	$now = sql_fetch_one_cell("select unix_timestamp()");
	$vacendTime = sql_fetch_one_cell("select vacend from sys_user_state where uid='$uid'");
	
	if(!empty($vacendTime) && intval($vacendTime)>$now)  //如果玩家当前处于休假状态，军队就不能出征
	{
		return ;
	}
    
    checkOutOpen($targetcid);//检查蛮夷之地是否开放。
    checkMianZhanCondition($uid,$cid,$targetcid,$task,$serverType);
	//防止作弊
	$resourcearray=explode(",",$resource);
	$newresource="";
	$resourcecount=count($resourcearray);
	if($resourcecount>5) $resourcecount=5;
	for($i=0;$i<$resourcecount;$i++){
		$temp=intval(array_shift($resourcearray));
		$newresource.=$temp.",";
	}
	$resource=$newresource;


	$usegoods=array_shift($param);
	if ($targetcid == $cid)
	{
		throw new Exception($GLOBALS['StartTroop']['target_cant_be_current']);
	}

	//检查是否有军旗，军旗的id是59
	if(($usegoods)&&!checkGoods($uid,59)) throw new Exception($GLOBALS['StartTroop']['no_flag']);

	//检查一下是暗渡陈仓状态，如果没有暗渡陈仓，中了十面埋伏的话就不能出兵了
	$anduTime=sql_fetch_one_cell("select `endtime` from mem_city_buffer where cid='$cid' and buftype=5 and `endtime`>unix_timestamp()");
	if(empty($anduTime))
	{
		//检查一下十面埋伏状态
		$shimianTime=sql_fetch_one_cell("select `endtime`-unix_timestamp() from mem_city_buffer where cid='$cid' and buftype=8 and `endtime`>unix_timestamp()");
		if(!empty($shimianTime))
		{
			$msg = sprintf($GLOBALS['StartTroop']['suffer_ShiMianMaiFu'],MakeTimeLeft($shimianTime));
			throw new Exception($msg);
		}
	}
	
	
	$targetwid = cid2wid($targetcid);
	$worldinfo = sql_fetch_one("select * from mem_world where wid=$targetwid");

	//城池不存在
	if ($worldinfo == false) throw new Exception($GLOBALS['StartTroop']['invalid_target'].$task);

	$targetCityInfo=sql_fetch_one("select * from sys_city where cid='$worldinfo[ownercid]'");


	$targetcitytype = $targetCityInfo['type'];
	if ($serverType == 1) {
		if ($targetcitytype >=0 && $targetcitytype <=5)//玩家主城特殊处理。
	        $canProtected=true;
	     if ($worldinfo["type"]>0)//||$targetcitytype>0) //野地或者名城不受免战和休假保护
		    $canProtected=false;
	              //特殊兵的城池不受免战和休假保护
	     if(getSpacialSoldierId($targetcid)>0){
		    $canProtected=true;
	     }
	}
	else {
	   if ($targetcitytype == 5) $targetcitytype = 0;//玩家主城特殊处理。
		$canProtected=true;
		if ($worldinfo["type"]>0||$targetcitytype>0) //野地或者名城不受免战和休假保护
			$canProtected=false;
		//特殊兵的城池不受免战和休假保护
		if(getSpacialSoldierId($targetcid)>0){
			$canProtected=false;
		}
	}
	//马来订制  司隶不受保护
	$isSili = 0;
	$yysType = sql_fetch_one_cell("select value from mem_state where state=198");//198是司隶开关
	if($yysType==1 && $worldinfo["province"]==1){
		$isSili=1;
	}
		
	//校场的等级不够的话就不能出发,TODO 校场等级不够是最常见的情况，是否应该放在最开始检查
	$groundLevel = intval(sql_fetch_one_cell("select level from sys_building where cid=$cid and bid='".ID_BUILDING_GROUND."'"));

	$troopCount = intval(sql_fetch_one_cell("select count(*) from sys_troops where cid=$cid and uid='$uid'"));
	if ($troopCount >= $groundLevel)
	{
		throw new Exception($GLOBALS['StartTroop']['insufficient_ground_level']);
	}


	/////////////// need test
	$taskname = array($GLOBALS['StartTroop']['transport'],$GLOBALS['StartTroop']['send'],$GLOBALS['StartTroop']['detect'],$GLOBALS['StartTroop']['harry'],$GLOBALS['StartTroop']['occupy']);
	$forceNeed=array(2,1,3,4,5);
	if($task == 3 && $hid == 0) {
		throw new Exception($GLOBALS['StartTroop']['plunder_no_hero']);
	}
	if($task == 4 && $hid == 0) {
		throw new Exception($GLOBALS['StartTroop']['invade_no_hero']);
	}
	
	//检查一下英雄的有效性。
	if ($hid != 0)
	{
		$heroInfo=sql_fetch_one("select * from sys_city_hero h left join mem_hero_blood m on m.hid=h.hid where h.hid='$hid' and h.uid='$uid' and h.cid='$cid'");
		if (empty($heroInfo))
		{
			throw new Exception($GLOBALS['StartTroop']['hero_not_found']);
		}
		else if($heroInfo['state']!=0||$heroInfo['hero_health']!=0)
		{
			throw new Exception($GLOBALS['StartTroop']['hero_is_busy']);
		}
		else
		{
			$force=$heroInfo['force'];
			if($force<$forceNeed[$task])
			{
				throw new Exception(sprintf($GLOBALS['StartTroop']['hero_not_enough_force'],$taskname[$task],$forceNeed[$task]));
			}
		}
	}

	//检查一下目标是否可以执行当前任务
	//运输到自己和同盟的城
	//派遣到自己和同盟归属的城和野地
	//侦察可以到非盟友的城和野地
	//掠夺可以到非盟友的城和野地
	//占领可以到非盟友的城和野地
	$targetIsUnion = false; //非盟友
	$targetuid = 0;
	if ($worldinfo['ownercid'] == $cid)  //自己城池归属的城或野地  ?应该是 出发地的野地或 added by taotao
	{
		$targetIsUnion = true;
		$targetuid = $uid;
	}
	else if ($worldinfo['type'] == 0) //是城池
	{
		$targetuid = $targetCityInfo['uid'];    //对方城所属的玩家
	}
	else if ($worldinfo['ownercid'] != 0)  //非无主的城或野地
	{
		$targetuid = $targetCityInfo['uid'];    //对方城所属的玩家
	}

	//检查下玩家是否中了上屋抽梯，如果中了，盟友军队就排不过来了。
	$swctEndTime = sql_fetch_one_cell("select endtime from mem_city_buffer where cid='$targetcid' and buftype=28 and endtime>unix_timestamp()");
	if (!empty($swctEndTime)) {
		$targetUnionId = sql_fetch_one_cell("select a.union_id from sys_user a,sys_city b where a.uid=b.uid and b.cid=$targetcid");
		$myUnionId = sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
		if ($targetUnionId>0 && $myUnionId>0 && ($targetUnionId ==$myUnionId) && $task<=1 && ($uid != $targetuid)) {
			throw new Exception($GLOBALS['trickSWCT']['in_trick']);
		}
	}

	if(!(($task==0)&&($targetuid==$uid)&&($worldinfo['type']==0)))
	{
		//目标是城池的话，要检查自己是不是刚使用过高级迁城令
		$lastMoveCD=intval(sql_fetch_one_cell("select last_adv_move+43200-unix_timestamp() from mem_city_schedule where cid='$cid'"));
		if($lastMoveCD>0)
		{
			$msg=sprintf($GLOBALS['StartTroop']['adv_move_cooldown'],MakeTimeLeft($lastMoveCD));
			throw new Exception($msg);
		}
	}
	if(($worldinfo['type']==0)&&($targetuid > NPC_UID_END)){//检测全民休战
		$startyear=1327244400;//休战开始
		$endyear=1327453200;//休战结束
		$nowtime=time();
		if($nowtime>$startyear&&$nowtime<$endyear){
			$newhour=date("G",$nowtime);
			if(($newhour>=0&&$newhour<9)||($newhour>=23)){//在休战期间
				if($task==3||$task==4){
					throw new Exception($GLOBALS['year']['bidwar']);
				}
			}
		}
	}

	$myUserInfo=sql_fetch_one("select * from sys_user where uid='$uid'");
	$targetUserInfo=sql_fetch_one("select * from sys_user where uid='$targetuid'");
	if(!empty($targetuid))
	{
			
		$targetunion = $targetUserInfo['union_id'];
		$myunion = $myUserInfo['union_id'];
		if ($uid == $targetuid)
		{
			//自己的城
			$targetIsUnion = true;
		}
		else
		{
			if (!empty($targetunion))
			{
				//是同盟
				if (($myunion == $targetunion)&&$myunion>0)
				{
					$targetIsUnion = true;
				}
			}
		}

		//非同盟
		if (($worldinfo['type'] == 0)&&(!$targetIsUnion))
		{
			//首先看是不是敌对联盟
			$union_relation = sql_fetch_one("select * from sys_union_relation where unionid='$myunion' and target='$targetunion'");
			if ((!empty($union_relation))&&($union_relation['type']==0)&&($task == 2))
			{
				//友好盟不能攻击
				throw new Exception($GLOBALS['StartTroop']['cant_detect_friendly_union']);
			}
			
			//马来订制 友盟不能互打 之前限制了 宣战 所以这边要补充下
			if($isSili==1 && (!empty($union_relation))&& ($union_relation['type']==0) && ($task==3 || $task==4)){
				throw new Exception($GLOBALS['StartTroop']['not_in_battle_condition']);
			}
			
			$user_trickwar  = sql_fetch_rows("select * from mem_user_trickwar where (uid='$uid' and targetuid='$targetuid') or (uid='$targetuid' and targetuid='$uid')");
			$user_inwar = sql_fetch_one("select * from mem_user_inwar where (uid='$uid' and targetuid='$targetuid') or (uid='$targetuid' and targetuid='$uid')");
			if ($isSili==0 && (empty($union_relation)||($union_relation['type'] != 2)))   //没有关系或者不是敌对状态的话，盟之间是不能打的
			{
				//看对方是不是NPC或是郡城以上城，如果是的话，则直接可以出征,type为0的是玩家城
				if (!(($targetuid < NPC_UID_END)||($targetcitytype > 0)))
				{
					if ($task > 2)   //"掠夺","占领"需要宣战或不宣而战       
					{
						if (empty($user_trickwar))      //没有不宣而战的话，看有没有宣战过
						{

							//没有宣战
							if (empty($user_inwar))
							{
								throw new Exception($GLOBALS['StartTroop']['not_in_battle_condition']);
							}
							//还不能开战
							else
							{
								if ($user_inwar['state'] == 0)
								{
									$msg = sprintf($GLOBALS['StartTroop']['wait_to_battle'],MakeEndTime($user_inwar['endtime']));
									throw new Exception($msg);
								}
							}
						}
					}
				}
			}else if($isSili==0 && ($task == 4 || $task == 3)){ //敌对关系，不能占领或掠夺
				$now=sql_fetch_one_cell("select unix_timestamp()");
				if(empty($user_trickwar)&&(empty($user_inwar)||$user_inwar['state']==0)){//个人之间不是战斗状态就看联盟
					if ($now-$union_relation['time']<3600*8 && $targetcitytype == 0){
						$msg = sprintf($GLOBALS['StartTroop']['wait_to_union_battle'],MakeEndTime($union_relation['time']+3600*8));
						throw new Exception($msg);
					}
				}
			}
		}
	}

	//查看封禁、休假状态
	if($isSili==0 && $targetcitytype==0)
	{
		$targetuserstate=sql_fetch_one("select forbiend,vacend,unix_timestamp() as nowtime from sys_user_state where uid='$targetuid' and (forbiend>unix_timestamp() or vacend>unix_timestamp())");
		if(!empty($targetuserstate))
		{
			if($targetuserstate['forbiend']>$targetuserstate['nowtime'])
			{
				//封禁
				throw new Exception($GLOBALS['StartTroop']['target_be_locked']);
			}
			else if ($canProtected && ($targetuserstate['vacend']>$targetuserstate['nowtime']) )
			{
				//休假
				throw new Exception($GLOBALS['StartTroop']['target_in_vacation']);
			}
		}
	}

	//所有名城必须在完成黄巾史诗任务后才能"掠夺","占领"
	if ($task>2)
	{
		$targetcitytype=$targetCityInfo['type'];
		if ($targetcitytype == 5) $targetcitytype = 0;
		$luoyangopen=sql_fetch_one_cell("select value from mem_state where state=8");//看洛阳是不是 开启了
		if($luoyangopen==1){
			if($targetcitytype==4){
				if($task==3){//联盟献帝诏书在使用中
					if(doUserUnionHasXianDiZhaoShuBuffer($uid)){
						
					}else{
						throw new Exception($GLOBALS['StartTroop']['nobufferluoyang']);
						//throw new Exception($GLOBALS['StartTroop']['changan_no_buffer']);
					}
				}else {				
					throw new Exception($GLOBALS['StartTroop']['capital']);
				}
			}
		}else{
			//都城
			if($targetcitytype==4)
			{
				throw new Exception($GLOBALS['StartTroop']['capital']);		
			}
			else if ($targetcid==225185) //长安暂时不能打
			{
				throw new Exception($GLOBALS['StartTroop']['changan']);
			}
		}
		 if($targetcitytype>0)
		{
			//黄巾史诗有没有完成
			$huangjinProgress=sql_fetch_one_cell("select value from mem_state where state=5");
			if($huangjinProgress != 1)
			{
				throw new Exception($GLOBALS['StartTroop']['huangjin_unfinished']);
			}
			$chiefhid=$targetCityInfo['chiefhid'];
			if ($chiefhid > 0) //如果有名将做城守，必须打败所有部将才能攻击
			{
				$chiefhero = sql_fetch_one("select * from sys_city_hero where hid='$chiefhid'");
				if ((!empty($chiefhero))&&($chiefhero['npcid']>0)&&($chiefhero['npcid']==$chiefhero['uid']))
				{
					$alldelete = true;
				
					//容错 删除多余的npc将领					
					$deleteheros =sql_fetch_rows("select * from sys_city_hero where uid='$chiefhero[uid]' and herotype=100");
					foreach($deleteheros as $deletehero){
						$deletehid=$deletehero['hid'];
						if(!sql_check("select * from sys_troops where hid=$deletehid")){
							sql_query("delete from sys_city_hero where hid=$deletehid");
						} else {
							$alldelete = false;
						}						
					}
					
					if(!$alldelete) {
						$msg=sprintf($GLOBALS['StartTroop']['has_temporary_hero'],$chiefhero['name']);
						throw new Exception($msg);
					}
					
					
					$followingCnt=sql_fetch_one_cell("select count(*) from sys_city_hero where uid='$chiefhero[uid]' and herotype<>100");
					if($followingCnt>1)
					{
						$msg=sprintf($GLOBALS['StartTroop']['has_following'],$chiefhero['name'],$followingCnt-1);
						throw new Exception($msg);
					}
					//throw new Exception($GLOBALS['StartTroop']['has_great_hero'] );
				}
			}
		}
	}


	$mystate = $myUserInfo['state'];
	$isOut=0;//在蛮夷之地不免战
	if (sql_check("select 1 from mem_world where province>=14 and wid=$targetwid")) {
		$isOut=1;
	}
	//当目标是一个城池的时候
	if (($worldinfo['type'] == 0)&&($targetuid <> $uid))    //向其它人的城池进发的
	{
		if ($task>1)    //侦察，掠夺或占领。
		{
			//校验我的state 是否应该提到最前面？(恩,路过，顶一下你的想法)
			if ($mystate == 1)
			{
				//新手
				throw new Exception($GLOBALS['StartTroop']['still_in_protection']);
			}
			else if ($isSili==0 && $isOut==0 && $mystate == 2)
			{
				//免战
				throw new Exception($GLOBALS['StartTroop']['in_peace_condition']);
			}
			//校验对手的state
			$targetstate = $targetUserInfo['state'];
			if ($targetstate == 1)
			{
					throw new Exception($GLOBALS['StartTroop']['target_in_protection']);
			}
			else if ($serverType == 1 &&$isSili==0 && $isOut==0 && $canProtected && $targetstate == 2&&$targetcitytype>=0 &&$targetcitytype<=5) {
			 	throw new Exception($GLOBALS['StartTroop']['target_in_peace']);
			}
			else if ($isSili==0 && $canProtected && $isOut==0 && $targetstate == 2&&$targetcitytype==0)
			{
					throw new Exception($GLOBALS['StartTroop']['target_in_peace']);
			}
			//判断休假状态
			$vacend = sql_fetch_one_cell("select vacend from sys_user_state where uid=$uid");
			if (!empty($vacend)) {
				throw new Exception($GLOBALS['book']['vacation_cannot_sendtroop']);
			}
		}
	}
	if ($task == 0)  //运输
	{
		if (!($targetIsUnion && ($worldinfo['type'] == 0)))
		{
			throw new Exception($GLOBALS['StartTroop']['only_transport_to_friendly']);
		}
		else
		{
			//给同盟运输，新手保护状态下不能运输
			if ($targetuid != $uid)
			{
				if (($mystate==2)||($mystate==1))
				throw new Exception($GLOBALS['StartTroop']['transport_in_peace_or_protection']);
			}
		}
	}
	else if ($task == 1) //派遣
	{
		//无法向新手保护状态、免战状态、休假状态、封禁状态的玩家派遣援军。

		if (!$targetIsUnion)
		{
			//只能派遣到自己或同盟的城池和野地
			throw new Exception($GLOBALS['StartTroop']['only_send_to_friendly']);
		}
		else
		{
				
				
			//派遣到同盟
			if ($targetuid != $uid)
			{
				//新手
				if (($mystate==2)||($mystate==1)) throw new Exception($GLOBALS['StartTroop']['send_in_peace_or_protection']);
				$allowUnionTroop=getAllowUnionTroop($targetuid,$targetcid);
				if(empty($allowUnionTroop)) //检查对方是否允许盟友驻军
				{
					throw new Exception($GLOBALS['StartTroop']['not_allow_union_troop']);
				}

				//校验对手的state
				$targetstate = $targetUserInfo['state'];
				if ($targetstate == 1)
				{
					throw new Exception($GLOBALS['StartTroop']['target_in_protection']);
				}
				else if ($targetstate == 2&&$targetcitytype==0 && $isOut==0)
				{
					throw new Exception($GLOBALS['StartTroop']['target_in_peace']);
				}
			}
		}
	}
	else
	{
		//攻击命令 不能针对同盟或者自己
		$msg = sprintf($GLOBALS['StartTroop']['only_towards_enemy'],$taskname[$task]);
		if ($targetIsUnion) throw new Exception($msg);
	}

	//检查一下当前城池是否有这么多军队
	$citySoldiers = sql_fetch_map("select * from sys_city_soldier where cid='$cid'","sid");

	$soldierArray = explode(",",$soldiers);
	$numSoldiers = array_shift($soldierArray);
	$takeSoldiers = array();    //真正带出去的军队
	$soldierAllCount = 0;
	$cihouCount = 0;
	if($numSoldiers>12){
		throw new Exception($GLOBALS['StartTroop']['too_many_sid']);
	}
	//检查野地的兵种，自己派的兵种和野地的兵种加在一起不能超过12种
	if (($task == 1) && ($worldinfo['type']!=0)) {//只检查派遣到野地的兵种
		$allSoliderType = array();
		$myDispatchFlag = false;
		$fieldTroops = sql_fetch_rows("select * from sys_troops where targetcid=$targetcid and state in (0,2,4)");
		if (!empty($fieldTroops)) {
			foreach ($fieldTroops as $fieldTroop) {
				$tmpSoldierArr = array();
				$tmpSoldierArr = explode(",",$fieldTroop['soldiers']);
				$tmpSoliderNum = array_shift($tmpSoldierArr);
				for ($i = 0; $i < $tmpSoliderNum; $i++) {
					$sid = array_shift($tmpSoldierArr);
					$cnt = array_shift($tmpSoldierArr);
					if (!in_array($sid,$allSoliderType)) {
						$allSoliderType[]=$sid;
					}
				}
			}
		}
		$tmpSoliderArray = $soldierArray;
		if (count($allSoliderType) > 12) {//如果原来野地已经有12或者更多的兵种，只有当前部队的兵种在这些兵种内，可以派遣过去
			for ($i = 0; $i < $numSoldiers; $i++) {
				$sid = array_shift($tmpSoliderArray);
				$cnt = array_shift($tmpSoliderArray);
				if (!in_array($sid,$allSoliderType)) {
					$myDispatchFlag = true;//不允许派兵了
				}
			}
		} else {//如果在12个兵种内，那么现在拍的兵加上原来的兵种，数量不能超过12种兵
			$tmpSoldierType = $allSoliderType;
			$tmpSoliderArray = $soldierArray;
			for ($i = 0; $i < $numSoldiers; $i++) {
				$sid = array_shift($tmpSoliderArray);
				$cnt = array_shift($tmpSoliderArray);
				if (!in_array($sid,$allSoliderType)) {
					$tmpSoldierType[]=$sid;//不允许派兵了
				}
			}
			if (count($tmpSoldierType) > 12) {
				$myDispatchFlag = true;
			}
		}
		if ($myDispatchFlag) {
			foreach ($allSoliderType as $mTmpType) {
				$tmpStr .= ','.sql_fetch_one_cell("select name from cfg_soldier where sid=$mTmpType");
			}
			$tmpMsg = sprintf($GLOBALS['StartTroop']['too_many_soldier_type'],$tmpStr);
			throw new Exception($tmpMsg);
		}
	}
	
	
	
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
		if ($sid == 3) $cihouCount+=$cnt;
	}

	if ($soldierAllCount <= 0) throw new Exception($GLOBALS['StartTroop']['no_soldier']);
	//有斥候才能侦查
	if (($task == 2)&&($cihouCount<= 0)) throw new Exception($GLOBALS['StartTroop']['army_with_spy']);
	//不能斥候独立出征
	if (($task > 2)&&($cihouCount >= $soldierAllCount)) throw new Exception($GLOBALS['StartTroop']['spy_cant_alone']);

	//出征人数限制
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

	//////////////////TODO  军队速度的计算，是否应该放在最后

	//行军技巧和驾驭技巧

	//步兵速度加成
	$speedAddRate1=1+intval(sql_fetch_one_cell("select level from sys_city_technic where cid='$cid' and tid=12"))*0.05;
	//骑兵速度加成
	$speedAddRate2=1+intval(sql_fetch_one_cell("select level from sys_city_technic where cid='$cid' and tid=13"))*0.01;
	//将领速度加成
	$speedAddRate3=1;
	//车轮技术
	$speedAddRate4=intval(sql_fetch_one_cell("select level from sys_city_technic where cid='$cid' and tid=26"))*0.02;
	if($hid!=0)
	{
		$speedAddRate3=1+$heroInfo['speed_add_on']*0.01;
	}
	$currentX = $cid % 1000;
	$currentY = floor($cid / 1000);
	$targetX  = $targetcid % 1000;
	$targetY  = floor($targetcid / 1000);

	//单程时间 ＝ 每格子距离/最慢兵种速度+宿营时间（每格距离＝60000/game_speed_rate）
	$pathLength = sqrt(($targetX - $currentX)*($targetX - $currentX) + ($targetY - $currentY)*($targetY - $currentY));
	$minSpeed = 999999999;
	// TODO 可以优化和缓存
	$soldierConfig = sql_fetch_rows("select * from cfg_soldier where fromcity=1 order by sid","sid");
	foreach ($soldierConfig as $soldier)        //找到当前军队里最慢的
	{
		$speedAdd = 0;
		if($hid > 0) {
			//计算将领身上因为装备而得到的该士兵的速度加成
			$sid = $soldier->sid;
			if(($sid >=1 && $sid <=12)||($sid >=45 && $sid <=50)) {
				$attid = 2000 + ($sid - 1) * 100 + 11;  //取得属性id
				$attr = sql_fetch_one("select * from sys_hero_attribute where hid=$hid and attid=$attid");
				if(!empty($attr)) {
					$speedAdd = $attr['value'];
				}
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
	
	if ($targetIsUnion)	//同盟才能用
	{
		//本地有驿站
		$speedAddRate = sql_fetch_one_cell("select (1.0 + level * 0.5) from sys_building where cid='$cid' and bid='".ID_BUILDING_DAK."'");
		if (!empty($speedAddRate))
		{
			$minSpeed *= $speedAddRate;
		}
	}
	$pathNeedTime = $pathLength * GRID_DISTANCE / $minSpeed;    //需要多少时间
	if($task!=0){
		$maxTime=ExternService::getTroopMaxTime2($targetX,$targetY);
		if($maxTime){
			if($maxTime<$pathNeedTime){
				$pathNeedTime=$maxTime;
			}
		}
	}
	if ($secondAdd < 0) $secondAdd = 0;
	$pathNeedTime += $secondAdd;
	$beaconAdd = getBeaconAdd($cid,$targetcid);
	if ($beaconAdd) {
		$pathNeedTime = $pathNeedTime*0.05;
	}
	$pathNeedTime=intval(floor($pathNeedTime));

	//负重技术(11)：每升1级，军队负重增加10%。
	$carryTechLevel = sql_fetch_one_cell("select level from sys_city_technic where cid=$cid and tid=11");
	if ($carryTechLevel < 0)
	{
		$carryTechLevel = 0;
	}
	$carryTechAdd = (1 + $carryTechLevel * 0.1);
	
	//将领负重加成
	$carryHeroAdd = 0;
	//将领负重百分比加成
	$carryPercentAdd = 0;
	if($hid > 0) {
		$attr = sql_fetch_one("select * from sys_hero_attribute where hid=$hid and attid=12");
		if(!empty($attr)) {
			$carryHeroAdd = $attr['value'];
		}
		$attr = sql_fetch_one("select * from sys_hero_attribute where hid=$hid and attid=10012");
		if(!empty($attr)) {
			$carryPercentAdd = $attr['value'];
		}
	}
	
	
	//出征耗粮 ＝ 兵的耗粮/小时*2*单程时间   
	$foodUse = 0;
	$allpeople = 0;
	$allcarry = 0;
	foreach ($soldierConfig as $soldier)        //计算负重等
	{		
		$carryAdd = 0;
		if($hid > 0) {
			//计算将领身上因为装备而得到的该士兵的负重加成
			$sid = $soldier->sid;
			if(($sid >=1 && $sid <=12)||($sid >=45 && $sid <=50)) {
				$attid = 2000 + ($sid - 1) * 100 + 12;  //取得属性id
				$attr = sql_fetch_one("select * from sys_hero_attribute where hid=$hid and attid=$attid");
				if(!empty($attr)) {
					$carryAdd = $attr['value'];
				}
			}
		}
		if (!empty($takeSoldiers[$soldier->sid]))
		{
			$foodUse += $soldier->food_use * $takeSoldiers[$soldier->sid];
			$allpeople += $soldier->people_need * $takeSoldiers[$soldier->sid];
			$allcarry += ($soldier->carry * ($carryTechAdd + $carryPercentAdd * 0.01) + $carryAdd + $carryHeroAdd) * $takeSoldiers[$soldier->sid];
		}
	}
	$hourfooduse = $foodUse * 2;        //每小时总耗粮食量

	$foodRate = 2;
	$foodUse *= $foodRate * $pathNeedTime;
	$foodUse = floor($foodUse/3600);    //军队行程耗粮量
	//检查一下当前城池是否有足够的军粮，直接吃掉
	$cityresource = sql_fetch_one("select * from mem_city_resource where cid='$cid'");
	if ($cityresource['food'] < $foodUse) throw new Exception($GLOBALS['StartTroop']['no_enough_food']);
	//检查一下当前城池是否有这么多资源，并把这些资源砍掉
	$resources = explode(",",$resource);
	$gold = $resources[0];
	$food = $resources[1];
	$wood = $resources[2];
	$rock = $resources[3];
	$iron = $resources[4];

	if (($gold < 0)||($food < 0)||($wood < 0)||($rock < 0)||($iron < 0))
	{
		throw new Exception($GLOBALS['StartTroop']['cant_carry_negative']);
	}
	if (($cityresource['gold'] < $gold)||
	($cityresource['food'] < $food + $foodUse)||
	($cityresource['wood'] < $wood)||
	($cityresource['rock'] < $rock)||
	($cityresource['iron'] < $iron))
	{
		throw new Exception($GLOBALS['StartTroop']['no_enough_resource']);
	}
	if ($allcarry+10 < $gold +$food + $wood + $rock +$iron + $foodUse)
	{
		throw new Exception($GLOBALS['StartTroop']['army_carry_limit']);
	}


	if ($hid != 0)  //让将领置成出征状态
	{
		if (!sql_check("select hid from sys_city_hero where state=0 and hid='$hid'")) {
			throw new Exception($GLOBALS['StartTroop']['hero_is_busy']);//再检查一次 避免同步问题
		}
		sql_query("update sys_city_hero set state=2 where hid='$hid'");
		$forceReduce=$forceNeed[$task];
		sql_query("update mem_hero_blood set `force`=GREATEST(0,`force`-$forceReduce) where hid='$hid'");
	}
	//减资源
	addCityResources($cid,-$wood,-$rock,-$iron,-$foodUse-$food,-$gold);
	//减兵员
	addCitySoldiers($cid,$takeSoldiers,false);

	$sqll="insert into sys_troops (`uid`,`cid`,`hid`,`targetcid`,`task`,`state`,`starttime`,`pathtime`,`endtime`,`soldiers`,`resource`,`people`,`fooduse`) values ('$uid','$cid','$hid','$targetcid','$task','0',unix_timestamp(),'$pathNeedTime',unix_timestamp()+$pathNeedTime,'$soldiers','$resource','$allpeople','$hourfooduse')";
	//添加一条出征记录 
	$troopid = sql_insert($sqll);
	/*
	 * 平台接口
	 */
	if (defined("PASSTYPE")){
		try{
		    require_once 'game/agents/AgentServiceFactory.php';
		    AgentServiceFactory::getInstance($uid)->addPushArmyOperationTimeEvent($troopid);
		}catch(Exception $e){
			try{
				file_put_contents("./agents/log/interface-error.log",date("Y-m-d H:i:s",time())." || ".$e->getMessage()."\n",FILE_APPEND);
			}catch(Exception $err){
				
			}
		}
    }

	//设置当前军队的战术为玩家当前战述
	$tactics = sql_fetch_one("select * from sys_user_tactics where uid='$uid'");
	if ($tactics)
	{
		sql_query("replace into sys_troop_tactics (`troopid`,`plunder`,`invade`,`patrol`,`field`) values ('$troopid','$tactics[plunder]','$tactics[invade]','$tactics[patrol]','$tactics[field]')");
	}

	//对方城池可能可以收到警报

	if ($worldinfo['type'] == WT_CITY)
	{
		$targetbalefireLevel = sql_fetch_one_cell("select level from sys_building where cid='".$targetcid."' and bid=".ID_BUILDING_BALEFIRE." limit 1");
		if ((!empty($targetbalefireLevel)) && $targetbalefireLevel > 0)
		{
			$targetuid = sql_fetch_one_cell("select uid from sys_city where cid='$targetcid'");
			if($targetbalefireLevel<5){
				if($targetuid != $uid&&$targetuid>0)
				{
					sql_query("insert into sys_alarm (uid,enemy) values ('$targetuid',1) on duplicate key update enemy=1");
				}				
			}else{
				$filtercount=getEnemyFilter($targetcid,$task);
				if(empty($filtercount)||$soldierAllCount>$filtercount){
					if($targetuid != $uid&&$targetuid>0)
					{
						sql_query("insert into sys_alarm (uid,enemy) values ('$targetuid',1) on duplicate key update enemy=1");
					}
				}
			}
		}
	}
	else
	{
		if ($worldinfo['ownercid'] > 0)
		{
			$targetbalefireLevel = sql_fetch_one_cell("select level from sys_building where cid='".$worldinfo['ownercid']."' and bid=".ID_BUILDING_BALEFIRE." limit 1");
			if ($targetbalefireLevel > 0)
			{
				if($targetuid != $uid&&$targetuid>0)
				{
					sql_query("insert into sys_alarm (uid,enemy) values ('$targetuid',1) on duplicate key update enemy=1");
				}
			}
		}
	}
	if($usegoods) {
		reduceGoods($uid,59,1);
		completeTaskWithTaskid($uid, 303);
	}
	//如果目标是野地，并且正在活动中，记录一下该次出征
	if (($worldinfo['type'] != WT_CITY)&&($task == 4))
	{
		$combat = sql_fetch_one("select * from fc_combat where starttime <= unix_timestamp() and unix_timestamp() < endtime and open=1 and havefield=1 limit 1");
		if (!empty($combat))
		{
			$fieldinfo = sql_fetch_one("select * from fc_fields where combatid='$combat[id]' and wid='$targetwid'");
			if (!empty($fieldinfo))
			{
				sql_query("insert into fc_log (time,combatid,wid,uid,cid) values (unix_timestamp(),$combat[id],$targetwid,$uid,$cid)");
			}
		}
	}

	$ret=array();
	$ret[]=$GLOBALS['StartTroop']['succ'];
	return $ret;
}
/**
 * 计算玩家主城烽火台对军队的加成效果。exmp:对军队出征速度有效地加成范围为玩家主城为中心的10*10格范围内，在有效范围内出征时间减少50%，范围外的出征速度正常
 *
 * @param unknown_type $cid
 * @param unknown_type $targetcid
 */
function getBeaconAdd($cid,$targetcid) {
	$length=0;
	$cityType = sql_fetch_one_cell("select type from sys_city where cid=$cid");
	if ($cityType != 5 ) return false; 
	
	$beaconLevel = sql_fetch_one_cell("select max(level) from sys_building where bid=19 and cid=$cid");
	$currentX = $cid % 1000;
	$currentY = floor($cid / 1000);
	$targetX  = $targetcid % 1000;
	$targetY  = floor($targetcid / 1000);
	if ($beaconLevel>10) $length = 5;
	$length = $length + ($beaconLevel-11)*2;
	
	if ( (abs($targetX-$currentX) <= $length) && (abs($targetY-$currentY) <= $length) ) {
		return true;
	} else {
		return false;
	}
}
/**
 * 玩家所在的联盟是不是在献帝诏书道具
 *
 * @param unknown_type $uid
 * @return unknown
 */
function doUserUnionHasXianDiZhaoShuBuffer($uid){
	$unionid=sql_fetch_one_cell("select `union_id` from sys_user where uid=$uid");
	$creator=sql_fetch_one_cell("select `leader` from sys_union where id=$unionid");
	if($creator){
		return doUserHasXianDiZhaoShuBuffer($creator);
	}else{
		return false;
	}
}
/**
 * 玩家是不是在献帝诏书道具
 *
 * @param unknown_type $uid
 * @return unknown
 */
function doUserHasXianDiZhaoShuBuffer($uid){
	$remaintime=sql_fetch_one_cell("select endtime-unix_timestamp() from mem_user_buffer where uid=$uid and `buftype`=161501 and endtime>unix_timestamp()");
	if(empty($remaintime)){
		return false;
	}else{
		return true;
	}
}
function getAttackTactics($uid,$cid,$param)
{
	$ret = array();
	$tac = sql_fetch_one("select * from sys_user_tactics where uid='$uid'");
	if (!empty($tac))
	{
		$ret[] = $tac;
	}
	return $ret;
}
function setAttackTactics($uid,$cid,$param)
{
	$plunderCount = array_shift($param);
	$plunderArray = array();
	for ($i = 0; $i < $plunderCount; $i++)
	{
		$stype = array_shift($param);
		$action = array_shift($param);
		$target = array_shift($param);
		$plunderArray[] = $stype.",".$action.",".$target;
	}
	$plunder = implode(";",$plunderArray);

	$invadeCount = array_shift($param);
	$invadeArray = array();
	for ($i = 0; $i < $invadeCount; $i++)
	{
		$stype = array_shift($param);
		$action = array_shift($param);
		$target = array_shift($param);
		$action2 = array_shift($param);
		$target2 = array_shift($param);

		$invadeArray[] = $stype.",".$action.",".$target.",".$action2.",".$target2;
	}
	$invade = implode(";",$invadeArray);

	$fieldCount = array_shift($param);
	$fieldArray = array();
	for ($i = 0; $i < $fieldCount; $i++)
	{
		$stype = array_shift($param);
		$action = array_shift($param);
		$target = array_shift($param);
		$fieldArray[] = $stype.",".$action.",".$target;
	}
	$field = implode(";",$fieldArray);

	$action = array_shift($param);
	$patrol = "3,".$action.",3";

	//$sql="insert into sys_user_tactics (`uid`,`plunder`,`invade`,`field`,`patrol`) values ('$uid','$plunder','$invade','$field','$patrol') on duplicate key update `plunder`='$plunder',`invade`='$invade',`field`='$field',`patrol`='$patrol'";
	//throw new Exception($sql);
	sql_query("insert into sys_user_tactics (`uid`,`plunder`,`invade`,`field`,`patrol`) values ('$uid','$plunder','$invade','$field','$patrol') on duplicate key update `plunder`='$plunder',`invade`='$invade',`field`='$field',`patrol`='$patrol'");

	throw new Exception($GLOBALS['setAttackTactics']['succ']);
}
function getResistTactics($uid,$cid,$param)
{
	$ret = array();
	$tac = sql_fetch_one("select * from sys_city_tactics where cid='$cid'");
	if (!empty($tac))
	{
		$ret[] = $tac;
	}
	completeTask($uid,540);
	return $ret;
}
function conscript($uid,$cid){
	$cid = intval($cid);
	$city=sql_fetch_one("select * from sys_city where uid='$uid' and cid='$cid' and type in(0,1,2,3)");//只有县，郡，州可以募兵
	if(empty($city)){
		throw new Exception($GLOBALS['conscript']['0']);
	}
	$conscriptstate=$city['conscript'];
	$isSpecial = $city['is_special'];
	if ($isSpecial == 2) {//王者城，招特殊兵种
		if ($conscriptstate >2) {
			throw new Exception($GLOBALS['conscript']['0']);
		}
		$soldierType = rand(45,50);
		$soldierNum = rand(2000,10000);
		sql_query("insert into sys_city_soldier(cid,sid,count) values('$cid','$soldierType','$soldierNum') on duplicate key update count=count+$soldierNum");
		sql_query("update sys_city set conscript=unix_timestamp() where cid='$cid' limit 1");
		$soldiername=sql_fetch_one_cell("select name from cfg_soldier where sid='$soldierType'");
		throw new Exception($GLOBALS['conscript']['1'].$soldiername.$soldierNum);
	}
	
	$type=$city['type'];
	$province=$city['province'];
	//蛮夷之地获取道具
	if (($province >=14)&& ($province<=18) && ($type>=1) && ($type<=3)) {
		if ($conscriptstate >2) {
			throw new Exception($GLOBALS['conscript']['4']);
		}
		$level=sql_fetch_one_cell("select level from sys_building where bid=6 and cid=$cid");
		if ($level<10) {
			throw new Exception($GLOBALS['conscript']['5']);
		}
		$gid = 19000+($province-13)*10+$type;
		$goodsName=sql_fetch_one_cell("select name from cfg_goods where gid=$gid");
		addGoods($uid,$gid,1,4);
		sql_query("update sys_city set conscript=unix_timestamp() where cid='$cid' limit 1");
		throw new Exception($GLOBALS['conscript']['3'].$goodsName);
	}
	
	if($type==0){//如果是普通城池则得检测是不是可以招募特殊兵种的普通城池
		$sidtype=getSpacialSoldierId($cid);
		if(empty($sidtype)){
			throw new Exception($GLOBALS['conscript']['0']);
		}
	}
	if(empty($conscriptstate)){
		throw new Exception($GLOBALS['conscript']['0']);
	}else if($conscriptstate==1){
		$zhoustate=sql_fetch_one("select sid,rsid from cfg_soldier_special_city where province='$province' limit 1");
		if(empty($zhoustate)){
			throw new Exception($GLOBALS['conscript']['0']) ;//
		}
		$rsid=$zhoustate['rsid'];
		$sid=$zhoustate['sid'];
		if($rsid==1){
			$sid=rand(45,50);
		}
		$count=0;
		if($type==0){
			$count=rand(100,300);
		}
		else if($type==1){
			$count=rand(100,300);
		}else if($type==2){
			$count=rand(300,600);
		}else if($type==3){
			$count=rand(600,1800);
		}
		sql_query("insert into sys_city_soldier(cid,sid,`count`)values($cid,$sid,$count) on duplicate key update `count`=`count`+$count");
		sql_query("update sys_city set conscript=unix_timestamp() where cid='$cid' limit 1");
		$soldiername=sql_fetch_one_cell("select name from cfg_soldier where sid='$sid'");
		throw new Exception($GLOBALS['conscript']['1'].$soldiername.$count);
	}else if($conscriptstate>=2){
		throw new Exception($GLOBALS['conscript']['2']);
	}
}
function setResistTactics($uid,$cid,$param)
{
	$deplunderCount = array_shift($param);
	$deplunderArray = array();
	$deplunderJoinArray = array();
	$join2count=0;
	for ($i = 0; $i < $deplunderCount; $i++)
	{
		$stype = array_shift($param);
		$join = array_shift($param);
		$action = array_shift($param);
		if($join){
			$join2count+=1;
		}
		$target = array_shift($param);
		if ($join) $deplunderJoinArray[] = $stype;
		$deplunderArray[] = $stype.",".$action.",".$target;
	}
	if($join2count>12){
		throw new Exception($join2count.$GLOBALS['depluder']['too_many_sid']);
	}
	$deplunder = implode(";",$deplunderArray);
	$deplunder_join = implode(",",$deplunderJoinArray);


	$deinvadeCount = array_shift($param);
	$deinvadeArray = array();
	$deinvadeJoinArray = array();
	$join2count=0;
	for ($i = 0; $i < $deinvadeCount; $i++)
	{
		$stype = array_shift($param);
		$join = array_shift($param);
		$action = array_shift($param);
		$target = array_shift($param);
		$action2 = array_shift($param);
		$target2 = array_shift($param);
		if($join){
			$join2count+=1;
		}
		if ($join) $deinvadeJoinArray[] = $stype;
		$deinvadeArray[] = $stype.",".$action.",".$target.",".$action2.",".$target2;
	}
	if($join2count>12){
		throw new Exception($join2count.$GLOBALS['depluder']['too_many_sid']);
	}

	$defenceCount = array_shift($param);
	for ($i = 0; $i < $defenceCount; $i++)
	{
		$stype = array_shift($param);
		$action = array_shift($param);
		$target = array_shift($param);
		$deinvadeArray[] = $stype.",".$action.",".$target;
	}

	$deinvade = implode(";",$deinvadeArray);
	$deinvade_join = implode(",",$deinvadeJoinArray);


	$join = array_shift($param);
	$action = array_shift($param);

	if ($join) $depatrol_join = "3"; else $depatrol_join = "";
	$depatrol = "3,".$action.",3";

	sql_query("insert into sys_city_tactics (`cid`,`deplunder_join`,`deplunder`,`depatrol_join`,`depatrol`,`deinvade_join`,`deinvade`) values ('$cid','$deplunder_join','$deplunder','$depatrol_join','$depatrol','$deinvade_join','$deinvade') on duplicate key update `deplunder_join`='$deplunder_join',`deplunder`='$deplunder',`depatrol_join`='$depatrol_join',`depatrol`='$depatrol',`deinvade_join`='$deinvade_join',`deinvade`='$deinvade'");

	throw new Exception($GLOBALS['setResistTactics']['succ']);
}


function getCaptiveSoldierGoldNeed($cid)
{
	return intval(sql_fetch_one_cell("select 0.1 * sum(s.count * (f.wood_need*".WOOD_VALUE."+f.food_need*".FOOD_VALUE."+f.rock_need*".ROCK_VALUE."+f.iron_need*".IRON_VALUE.")) from mem_city_captive s left join cfg_soldier f on f.sid=s.sid where s.cid='$cid' and s.count>0"));
}
function getSoldierGoldNeedDetail($sid,$count)
{
	return intval(sql_fetch_one_cell("select 0.1 * sum($count * (wood_need*".WOOD_VALUE."+food_need*".FOOD_VALUE."+rock_need*".ROCK_VALUE."+iron_need*".IRON_VALUE.")) from cfg_soldier where sid=$sid"));
}
function getWoundedSoldierGoldNeed($cid)
{
	return intval(sql_fetch_one_cell("select 0.1 * sum(s.count * (f.wood_need*".WOOD_VALUE."+f.food_need*".FOOD_VALUE."+f.rock_need*".ROCK_VALUE."+f.iron_need*".IRON_VALUE.")) from mem_city_wounded s left join cfg_soldier f on f.sid=s.sid where s.cid='$cid' and (s.sid<13 or (s.sid>=45 and s.sid<=50) ) and s.count>0"));
}
//集体劝说和单独劝说不一样
function getLamsterGoldNeed($cid)
{
	return intval(sql_fetch_one_cell("select 0.1 * sum(s.count * (f.wood_need*".WOOD_VALUE."+f.food_need*".FOOD_VALUE."+f.rock_need*".ROCK_VALUE."+f.iron_need*".IRON_VALUE.")) from mem_city_lamster s left join cfg_soldier f on f.sid=s.sid where s.cid='$cid' and (s.sid<13 or (s.sid>=45 and s.sid<=50) ) and s.count>0"));
}

function getCaptiveSoldierPeople($cid)
{
	return intval(sql_fetch_one_cell("select sum(c.people_need*w.`count`) from mem_city_captive w left join cfg_soldier c on c.sid=w.sid where w.cid='$cid'  and w.count>0"));
}

function getWoundedSoldierPeople($cid)
{
	return intval(sql_fetch_one_cell("select sum(c.people_need*w.`count`) from mem_city_wounded w left join cfg_soldier c on c.sid=w.sid where w.cid='$cid' and (w.sid<13 or (w.sid>=45 and w.sid<=50) ) and w.count>0"));
}
function getSoldierPeopleDetail($sid,$count)
{
	return intval(sql_fetch_one_cell("select people_need*$count from cfg_soldier where sid=$sid"));
}
function getLamsterPeople($cid)
{
	return intval(sql_fetch_one_cell("select sum(c.people_need*w.`count`) from mem_city_lamster w left join cfg_soldier c on c.sid=w.sid where w.cid='$cid' and (w.sid<13 or (w.sid>=45 and w.sid<=50) ) and w.count>0"));
}
function getCaptiveSoldier($uid,$cid,$param)
{
	$cid = intval($cid);
	$ret = array();
	$ret[] = sql_fetch_rows("select w.sid,s.name,w.count from mem_city_captive w left join cfg_soldier s on w.sid=s.sid where w.cid='$cid'  and w.count > 0");
	$ret[] = getCaptiveSoldierGoldNeed($cid);
	return $ret;
}

function getWoundedSoldier($uid,$cid,$param)
{
	$ret = array();
	$ret[] = sql_fetch_rows("select w.sid,s.name,w.count from mem_city_wounded w left join cfg_soldier s on w.sid=s.sid where w.cid='$cid' and (w.sid<13 or (w.sid>=45 and w.sid<=50) ) and w.count > 0");
	$ret[] = getWoundedSoldierGoldNeed($cid);
	return $ret;
}

function getLamster($uid,$cid,$param)
{
	$ret = array();
	$ret[] = sql_fetch_rows("select w.sid,s.name,w.count from mem_city_lamster w left join cfg_soldier s on w.sid=s.sid where w.cid='$cid' and (w.sid<13 or (w.sid>=45 and w.sid<=50) ) and w.count > 0");
	$ret[] = getLamsterGoldNeed($cid);
	return $ret;
}
function acceptCaptiveSoldier($uid,$cid,$param)
{
	$cid=intval($cid);
	$goldneed =getCaptiveSoldierGoldNeed($cid);
	$citygold = sql_fetch_one_cell("select gold from mem_city_resource where cid='$cid'");
	if ($citygold < $goldneed) throw new Exception($GLOBALS['acceptCaptiveSoldier']['no_enough_gold']);

	$soldiers = sql_fetch_rows("select * from mem_city_captive where cid='$cid' ");
	if (empty($soldiers)) throw new Exception($GLOBALS['acceptCaptiveSoldier']['no_captive_soldier']);
	$totalCount=0;
	foreach($soldiers as $soldier)
	{
		$totalCount+=$soldier['count'];
		$type=sql_fetch_one_cell("select type from cfg_soldier where sid = ".$soldier['sid']);
		finishTaskMaxNum($uid,$soldier['count'],$soldier['sid']+2300);//随机类型任务：招降俘虏（单次）, 2300+sid 为对应兵种在cfg_task_goal里面的type
		finishTask($uid,$soldier['count'],$soldier['sid']+2300);//随机类型任务：招降俘虏（累计）, 2300+sid 为对应兵种在cfg_task_goal里面的type
		sql_query("insert into sys_city_soldier (cid,sid,count) values ('$cid','$type','$soldier[count]') on duplicate key update count=count+'$soldier[count]'");
		sql_query("insert into log_city_soldier (cid,sid,uid,count,type) values ('$cid','$type',$uid,'$soldier[count]',0) on duplicate key update count=count+'$soldier[count]'");
	}
	finishTaskMaxNum($uid,$totalCount,2300);//随机类型任务：招降俘虏（单次）, 2300不区分
	finishTask($uid,$totalCount,2300);//随机类型任务：招降俘虏（累计）, 2300不区分
	sql_query("delete from mem_city_captive where cid='$cid'");
	updateUserPrestige($uid);
	addCityResources($cid,0,0,0,0,-$goldneed);
	updateCityResourceAdd($cid);
	//completeTask($uid,372);	
	logUserAction($uid,4);
	throw new Exception($GLOBALS['acceptCaptiveSoldier']['succ']);
}
function acceptCaptiveSoldierDetail($uid,$cid,$param)
{
	$cid = intval($cid);
	$sid = intval(array_shift($param));
	$count = intval(array_shift($param));
	
	$goldneed =getSoldierGoldNeedDetail($sid,$count);
	$citygold = sql_fetch_one_cell("select gold from mem_city_resource where cid='$cid'");
	if ($citygold < $goldneed) throw new Exception($GLOBALS['acceptCaptiveSoldier']['no_enough_gold']);

	$soldier = sql_fetch_one("select * from mem_city_captive where cid='$cid' and sid=$sid and count>=$count");
	if (empty($soldier)) throw new Exception($GLOBALS['acceptCaptiveSoldier']['no_captive_soldier']);

	$type=sql_fetch_one_cell("select type from cfg_soldier where sid = $sid");
	finishTaskMaxNum($uid,$count,$sid+2300);//随机类型任务：招降俘虏（单次）, 2300+sid 为对应兵种在cfg_task_goal里面的type
	finishTask($uid,$count,$sid+2300);//随机类型任务：招降俘虏（累计）, 2300+sid 为对应兵种在cfg_task_goal里面的type
	sql_query("insert into sys_city_soldier (cid,sid,count) values ('$cid','$type','$count') on duplicate key update count=count+$count");
	sql_query("insert into log_city_soldier (cid,sid,uid,count,type) values ('$cid','$type',$uid,'$count',1) on duplicate key update count=count+$count");
	finishTaskMaxNum($uid,$count,2300);//随机类型任务：招降俘虏（单次）, 2300不区分
	finishTask($uid,$count,2300);//随机类型任务：招降俘虏（累计）, 2300不区分
	sql_query("update mem_city_captive set count=greatest(0,count-$count) where cid='$cid' and sid=$sid");
	updateUserPrestige($uid);
	addCityResources($cid,0,0,0,0,-$goldneed);
	updateCityResourceAdd($cid);
	//completeTask($uid,372);	
	logUserAction($uid,4);
	throw new Exception($GLOBALS['acceptCaptiveSoldier']['succ']);
}
function cureWoundedSoldier($uid,$cid,$param)
{
	$cid = intval($cid);
	$goldneed =getWoundedSoldierGoldNeed($cid);
	$citygold = sql_fetch_one_cell("select gold from mem_city_resource where cid='$cid'");
	if ($citygold < $goldneed) throw new Exception($GLOBALS['cureWoundedSoldier']['no_enough_gold']);

	$soldiers = sql_fetch_rows("select * from mem_city_wounded where cid='$cid' and (sid<13 or (sid>=45 and sid<=50) )");
	if (empty($soldiers)) throw new Exception($GLOBALS['cureWoundedSoldier']['no_wounded_soldier']);
	$totalCount=0;
	foreach($soldiers as $soldier)
	{
		$totalCount+=$soldier['count'];
		finishTaskMaxNum($uid,$soldier['count'],$soldier['sid']+2200);//随机类型任务：治疗伤兵（单次）, 2200+sid 为对应兵种在cfg_task_goal里面的type
		finishTask($uid,$soldier['count'],$soldier['sid']+2200);//随机类型任务：治疗伤兵（累计）, 2200+sid 为对应兵种在cfg_task_goal里面的type
		sql_query("insert into sys_city_soldier (cid,sid,count) values ('$cid','$soldier[sid]','$soldier[count]') on duplicate key update count=count+'$soldier[count]'");
		sql_query("insert into log_city_soldier (cid,sid,uid,count,type) values ('$cid','$soldier[sid]',$uid,'$soldier[count]',2) on duplicate key update count=count+'$soldier[count]'");
	}
	finishTaskMaxNum($uid,$totalCount,2200);//随机类型任务：治疗伤兵（单次）, 2200 所有兵种在cfg_task_goal里面的type
	finishTask($uid,$totalCount,2200);//随机类型任务：治疗伤兵（累计）, 2200 所有兵种在cfg_task_goal里面的type
	sql_query("delete from mem_city_wounded where cid='$cid'");
	updateUserPrestige($uid);
	addCityResources($cid,0,0,0,0,-$goldneed);
	completeTask($uid,372);
	throw new Exception($GLOBALS['cureWoundedSoldier']['succ']);
}
function cureWoundedSoldierDetail($uid,$cid,$param)
{
	$cid = intval($cid);
	$sid=intval(array_shift($param));
	$count = intval(array_shift($param));
	
	$goldneed =getSoldierGoldNeedDetail($sid,$count);
	$citygold = sql_fetch_one_cell("select gold from mem_city_resource where cid='$cid'");
	if ($citygold < $goldneed) throw new Exception($GLOBALS['cureWoundedSoldier']['no_enough_gold']);

	$soldier = sql_fetch_one("select * from mem_city_wounded where cid='$cid' and sid=$sid and count>=$count");
	if (empty($soldier)) throw new Exception($GLOBALS['cureWoundedSoldier']['no_wounded_soldier']);
	
	finishTaskMaxNum($uid,$count,$sid+2200);//随机类型任务：治疗伤兵（单次）, 2200+sid 为对应兵种在cfg_task_goal里面的type
	finishTask($uid,$count,$sid+2200);//随机类型任务：治疗伤兵（累计）, 2200+sid 为对应兵种在cfg_task_goal里面的type
	sql_query("insert into sys_city_soldier (cid,sid,count) values ('$cid','$sid','$count') on duplicate key update count=count+'$count'");
	sql_query("insert into log_city_soldier (cid,sid,uid,count,type) values ('$cid','$sid',$uid,'$count',3) on duplicate key update count=count+'$count'");
	finishTaskMaxNum($uid,$count,2200);//随机类型任务：治疗伤兵（单次）, 2200 所有兵种在cfg_task_goal里面的type
	finishTask($uid,$count,2200);//随机类型任务：治疗伤兵（累计）, 2200 所有兵种在cfg_task_goal里面的type
	sql_query("update mem_city_wounded set count=greatest(0,count-$count) where cid='$cid' and sid=$sid");
	updateUserPrestige($uid);
	addCityResources($cid,0,0,0,0,-$goldneed);
	completeTask($uid,372);
	throw new Exception($GLOBALS['cureWoundedSoldier']['succ']);
}
function sayToLamster($uid,$cid,$param)
{
	$cid = intval($cid);
	$goldneed =getLamsterGoldNeed($cid);
	$citygold = sql_fetch_one_cell("select gold from mem_city_resource where cid='$cid'");
	if ($citygold < $goldneed) throw new Exception($GLOBALS['sayToLamster']['no_enough_gold']);

	$soldiers = sql_fetch_rows("select * from mem_city_lamster where cid='$cid' and (sid<13 or (sid>=45 and sid<=50) )");
	if (empty($soldiers)) throw new Exception($GLOBALS['sayToLamster']['no_wounded_soldier']);
	foreach($soldiers as $soldier)
	{
		sql_query("insert into sys_city_soldier (cid,sid,count) values ('$cid','$soldier[sid]','$soldier[count]') on duplicate key update count=count+'$soldier[count]'");
		sql_query("insert into log_city_soldier (cid,sid,uid,count,type) values ('$cid','$soldier[sid]',$uid,'$soldier[count]',4) on duplicate key update count=count+'$soldier[count]'");
	}
	sql_query("delete from mem_city_lamster where cid='$cid'");
	updateUserPrestige($uid);
	updateCityResourceAdd($cid);
	addCityResources($cid,0,0,0,0,-$goldneed);
	//completeTask($uid,372);
	throw new Exception($GLOBALS['sayToLamster']['succ']);
}
function sayToLamsterDetail($uid,$cid,$param)
{
	$cid = intval($cid);
	$sid = intval(array_shift($param));
	$count = intval(array_shift($param));
	
	$goldneed =getSoldierGoldNeedDetail($sid,$count);
	$citygold = sql_fetch_one_cell("select gold from mem_city_resource where cid='$cid'");
	if ($citygold < $goldneed) throw new Exception($GLOBALS['sayToLamster']['no_enough_gold']);

	$soldier = sql_fetch_one("select * from mem_city_lamster where cid='$cid' and sid=$sid and count>=$count");
	if (empty($soldier)) throw new Exception($GLOBALS['sayToLamster']['no_wounded_soldier']);
	
	sql_query("insert into sys_city_soldier (cid,sid,count) values ('$cid','$sid','$count') on duplicate key update count=count+'$count'");
	sql_query("insert into log_city_soldier (cid,sid,uid,count,type) values ('$cid','$sid',$uid,'$count',5) on duplicate key update count=count+'$count'");
	sql_query("update mem_city_lamster set count=greatest(0,count-$count) where cid='$cid' and sid=$sid");
	updateUserPrestige($uid);
	updateCityResourceAdd($cid);
	addCityResources($cid,0,0,0,0,-$goldneed);
	//completeTask($uid,372);
	throw new Exception($GLOBALS['sayToLamster']['succ']);
}
function dismissCaptiveSoldier($uid,$cid,$param)
{	
	$cid=intval($cid);
	$totalCount=0;
	$soldiers = sql_fetch_rows("select * from mem_city_captive where cid='$cid'");
	foreach($soldiers as $soldier)
	{
		$totalCount+=$soldier['count'];
		finishTaskMaxNum($uid,$soldier['count'],$soldier['sid']+2100);//随机类型任务：释放俘虏（单次）, 2100+sid 为对应兵种在cfg_task_goal里面的type
		finishTask($uid,$soldier['count'],$soldier['sid']+2100);//随机类型任务：释放俘虏（累计）, 2100+sid 为对应兵种在cfg_task_goal里面的type
	}
	finishTaskMaxNum($uid,$totalCount,2100);//随机类型任务：释放俘虏（单次）, 2100 所有兵种在cfg_task_goal里面的type
	finishTask($uid,$totalCount,2100);//随机类型任务：释放俘虏（累计）, 2100 所有兵种在cfg_task_goal里面的type
	sql_query("delete from mem_city_captive where cid='$cid'");	
	updateCityResourceAdd($cid);
	logUserAction($uid,5);
	throw new Exception($GLOBALS['dismissCaptiveSoldier']['succ']);
}
function dismissCaptiveSoldierDetail($uid,$cid,$param)
{	
	$cid = intval($cid);
	$sid = intval(array_shift($param));
	$count = intval(array_shift($param));
	
	$soldier = sql_fetch_one("select * from mem_city_captive where cid='$cid' and sid=$sid and count>=$count");
	if (empty($soldier)) throw new Exception($GLOBALS['acceptCaptiveSoldier']['no_captive_soldier']);
	
	finishTaskMaxNum($uid,$count,$sid+2100);//随机类型任务：释放俘虏（单次）, 2100+sid 为对应兵种在cfg_task_goal里面的type
	finishTask($uid,$count,$sid+2100);//随机类型任务：释放俘虏（累计）, 2100+sid 为对应兵种在cfg_task_goal里面的type
	finishTaskMaxNum($uid,$count,2100);//随机类型任务：释放俘虏（单次）, 2100 所有兵种在cfg_task_goal里面的type
	finishTask($uid,$count,2100);//随机类型任务：释放俘虏（累计）, 2100 所有兵种在cfg_task_goal里面的type
	sql_query("update mem_city_captive set count=greatest(0,count-$count) where cid='$cid' and sid=$sid");	
	updateCityResourceAdd($cid);
	logUserAction($uid,5);
	throw new Exception($GLOBALS['dismissCaptiveSoldier']['succ']);
}
function dismissWoundedSoldier($uid,$cid,$param)
{
	$cid = intval($cid);
	$people=getWoundedSoldierPeople($cid);
	sql_query("update mem_city_resource set people=people+'$people' where cid='$cid'");
	sql_query("delete from mem_city_wounded where cid='$cid'");
	updateCityResourceAdd($cid);
	throw new Exception($GLOBALS['dismissWoundedSoldier']['succ']);
}
function dismissWoundedSoldierDetail($uid,$cid,$param)
{
	$cid = intval($cid);
	$sid=intval(array_shift($param));
	$count = intval(array_shift($param));
	$curCount = sql_fetch_one_cell("select count from mem_city_wounded where cid=$cid and sid=$sid");
	if ($curCount < $count) {
		throw new Exception($GLOBALS['waigua']['forbidden']);
	}
	$people=getSoldierPeopleDetail($sid,$count);
	sql_query("update mem_city_resource set people=people+'$people' where cid='$cid'");
	sql_query("update mem_city_wounded set count=greatest(0,count-$count) where cid='$cid' and sid=$sid");
	updateCityResourceAdd($cid);
	throw new Exception($GLOBALS['dismissWoundedSoldier']['succ']);
}
function dismissLamster($uid,$cid,$param)
{
	$cid = intval($cid);
	$people=getLamsterPeople($cid);
	sql_query("update mem_city_resource set people=people+'$people' where cid='$cid'");
	sql_query("delete from mem_city_lamster where cid='$cid'");
	updateCityResourceAdd($cid);
	throw new Exception($GLOBALS['dismissLamster']['succ']);
}
function dismissLamsterDetail($uid,$cid,$param)
{
	$cid = intval($cid);
	$sid=intval(array_shift($param));
	$count = intval(array_shift($param));
	$curCount = sql_fetch_one_cell("select count from mem_city_lamster where cid=$cid and sid=$sid");
	if ($curCount < $count) {
		throw new Exception($GLOBALS['waigua']['forbidden']);
	}
	$people=getSoldierPeopleDetail($sid,$count);
	sql_query("update mem_city_resource set people=people+'$people' where cid='$cid'");
	sql_query("update mem_city_lamster set count=greatest(0,count-$count) where cid='$cid' and sid=$sid");
	updateCityResourceAdd($cid);
	throw new Exception($GLOBALS['dismissLamster']['succ']);
}
function getEnemyFilter($cid,$taskid){
	$count=sql_fetch_one_cell("select `count` from sys_enemy_troop_filter where cid='$cid' and action='$taskid'");
	return $count;
}
function getTroopScheme($uid,$cid,$param)
{
	$type = array_shift($param);
	$ret = array();
	if(intval($type)<1 || intval($type)>2)
	{
		throw new Exception($GLOBALS['waigua']['invalid']);
	}
	
	$schemeInfo = sql_fetch_one("select * from sys_user_troopScheme where uid='$uid' and type='$type'");
	
	if(!empty($schemeInfo))
	{
		$ret[] = $schemeInfo;
	}
	
	return $ret;
}

function setTroopScheme($uid,$cid,$param)
{
	$type = intval(array_shift($param));
	$soldiers = array_shift($param);
	$soldiers = addslashes($soldiers);
	$ret = array();
	if(intval($type)<1 || intval($type)>2 || strlen($soldiers)==0)
	{
		throw new Exception($GLOBALS['waigua']['invalid']);
	}
	$schemeInfo = sql_fetch_one("select * from sys_user_troopScheme where uid='$uid' and type='$type'");
	
	if(empty($schemeInfo))
	{
		sql_query("insert into sys_user_troopScheme(`uid`,`type`,`soldiers`) values('$uid','$type','$soldiers')");
		$ret[] = $GLOBALS['ground']['scheme_set_succ'];
	}else 
	{
		sql_query("update sys_user_troopScheme set soldiers='$soldiers' where uid='$uid' and type='$type'");
		$ret[] = $GLOBALS['ground']['scheme_update_succ'];
	} 
	
	return $ret;
}


?>
