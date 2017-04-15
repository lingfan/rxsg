<?php
require_once ("./utils.php");
require_once ("./PKFunc.php");

function loadMarryMadam($uid)
{
	$motherInfos = sql_fetch_rows("select c.*,m.*,m.uid as userid from cfg_marry_mother_hero c,mem_marry_hero_favor m where c.hid=m.hid and m.uid='$uid' and m.state<>'0' ");

	$ret = array();
	foreach($motherInfos as $motherInfo)
	{
		$now = sql_fetch_one_cell("select unix_timestamp()");
		$state = intval($motherInfo['state']);
				
		sql_query("insert into sys_user_marry(`uid`) values('$uid') on duplicate key update roomcount=roomcount");
		$roomCount = sql_fetch_one_cell("select roomcount from sys_user_marry where uid='$uid'");
		$childCount = sql_fetch_one_cell("select count(1) from sys_user_child where uid='$uid'");
		if(empty($childCount))$childCount=0;
		$motherInfo['roomIsEnough']=$roomCount>$childCount?true:false;
		$motherInfo['leaveCoolTime']=intval($motherInfo['coolingEndtime'])-$now>0?intval($motherInfo['coolingEndtime'])-$now:0;
		
		$curFavorGid = getGidByFavor($motherInfo['favor']);
		addNextGoodInfo($motherInfo,$curFavorGid);
		$durInfo = sql_fetch_one("select starttime,endtime,isSpeed from mem_marry_during where uid='$uid' and hid={$motherInfo['hid']}");
		if(!empty($durInfo)){
			$motherInfo['isSpeed']= $durInfo['isSpeed'];
			$motherInfo['leaveDuringTime']=intval($durInfo['endtime'])-$now>0?intval($durInfo['endtime'])-$now:0;
			
			if(intval($now)>=intval($durInfo['endtime'])){  //更新到可接生阶段
				sql_query("update mem_marry_hero_favor set state='3' where uid='$uid' and hid={$motherInfo['hid']}");
				$motherInfo['state']=3;
			}
		}else{
			$motherInfo['isSpeed']=0;
			$motherInfo['leaveDuringTime']=0;
		}		
		$ret[] = $motherInfo;
	}
	return $ret;
}
function loadMarryChild($uid)
{
	$ret = array();
	
	sql_query("insert into sys_user_marry(`uid`) values('$uid') on duplicate key update roomcount=roomcount");
	$rCount = sql_fetch_one_cell("select roomcount from sys_user_marry where uid='$uid'");
	$ret[] = $rCount;
	$childInfos = sql_fetch_rows("select s.*,m.mHid as motherHid,m.state,(select name from cfg_marry_mother_hero where hid=m.mHid) as motherName from sys_user_child s,mem_marry_relation m where s.hid=m.shid and s.uid=m.uid and s.uid='$uid' order by s.hid asc");	
	$ret[] = $childInfos;
	return $ret;
}
function loadChildCulture($uid,$param)  //进入培养界面
{
	$roomIndex = intval(array_shift($param));
	$hid = intval(array_shift($param));

	$roomIdArr = array(1,2,3);
	if(!in_array($roomIndex, $roomIdArr))throw new Exception($GLOBALS['marrySystem']['room_num_error']);

	$childInfo = sql_fetch_one("select * from sys_user_child where uid='$uid' and hid='$hid'");
	if(empty($childInfo))throw new Exception($GLOBALS['marrySystem']['child_not_exist']);
	if(intval($childInfo['out_hid'])>0)throw new Exception($GLOBALS['marrySystem']['child_has_finish_culture']);
	
	sql_query("insert into sys_user_marry(`uid`) values('$uid') on duplicate key update roomcount=roomcount");
	$curHasOpenRoom = sql_fetch_one_cell("select roomcount from sys_user_marry where uid='$uid'");
	
	if($roomIndex>$curHasOpenRoom)throw new Exception($GLOBALS['marrySystem']['room_not_open']);

	$ret = array();
	$goodInfo = sql_fetch_one("select *,(select count from sys_goods where gid='12059' and uid='$uid') as goodCnt from cfg_goods where gid='12059'");
	if(empty($goodInfo))$goodInfo=NULL;

	$ret[] = $roomIndex;
	$ret[] = $goodInfo;
	$ret[] = $childInfo;
	return $ret;
}
function deleteSpecialChild($uid,$param)  //周游列国去了
{
	$hid = intval(array_shift($param));

	$childInfo = sql_fetch_one("select * from sys_user_child where uid='$uid' and hid='$hid'");
	if(empty($childInfo)) throw new Exception($GLOBALS['marrySystem']['child_not_exist']);
	if(intval($childInfo['out_hid']>0)) throw new Exception($GLOBALS['marrySystem']['can_not_throw']);
	
	if(!sql_check("select 1 from sys_goods where uid='$uid' and gid='152' and count>=600"))throw new Exception($GLOBALS['marrySystem']['core_not_enough']);

	addGoods($uid, 152, -600, 826);
	sql_query("delete from sys_user_child where uid='$uid' and hid='$hid'");
	sql_query("update mem_marry_relation set state='1' where shid='$hid' and uid='$uid'");
	sql_query("insert into log_child_status(`hid`,`uid`,`out_hid`,`state`,`time`) values('$hid','$uid',{$childInfo['out_hid']},'1',unix_timestamp()) on duplicate key update state='1',time=unix_timestamp()");

	//给玩家增加安慰礼包
	addGoods($uid, 50320, 1, 826);

	$ret = array();
	$ret[] = $GLOBALS['marrySystem']['throw_child_succ'];
	$ret[] = loadMarryChild($uid);
	return $ret;
}
function completeCulture($uid,$param)   //完成培养
{
	$hid = intval(array_shift($param));

	$heroInfo = sql_fetch_one("select * from sys_user_child where uid='$uid' and hid='$hid'");
	if(empty($heroInfo))throw new Exception($GLOBALS['marrySystem']['child_not_exist']);
	resetCaluHeroAttr($heroInfo);

	$lastCid = sql_fetch_one_cell("select lastcid from sys_user where uid='$uid'");
	$heroName = addslashes($heroInfo['name']);
	
	//套用原来将领的头像图片
	$face = (intval($heroInfo['sex'])==0)?mt_rand(1,9):mt_rand(1001,1070);
	
	$sql = "insert into sys_city_hero(`uid`,`name`,`sex`,`face`,`cid`,`level`,`exp`,`command_base`,`affairs_base`,`bravery_base`,`wisdom_base`,`loyalty`,`herotype`)"
			."values('$uid','$heroName',{$heroInfo['sex']},'$face','$lastCid','1','0',{$heroInfo['command']},{$heroInfo['affairs']},{$heroInfo['bravery']},{$heroInfo['wisdom']},'300','10001')";

	sql_query($sql);
	$newHid = sql_fetch_one_cell("select last_insert_id()");
	sql_query("insert into mem_hero_blood(`hid`) values('$newHid')");
	sql_query("update sys_user_child set out_hid='$newHid' where uid='$uid' and hid='$hid'");
	sql_query("update mem_marry_relation set state='4' where uid='$uid' and shid='$hid'");
	sql_query("insert into log_child_status(`hid`,`uid`,`out_hid`,`state`,`time`) values('$hid','$uid','$newHid','4',unix_timestamp()) on duplicate key update out_hid='$newHid',state='4',time=unix_timestamp()");

	$ret = array();
	$ret[] = $GLOBALS['marrySystem']['finish_culture'];
	$ret[] = loadMarryChild($uid);
	return $ret;
}
function resetCaluHeroAttr(&$heroInfo)
{
	$heroInfo["command"] = round($heroInfo["command_base"]+$heroInfo["command_add"]*($heroInfo["upgrade_value"]+$heroInfo["qixin_multip"]+$heroInfo["wenpo_multip"])*0.1);
	$heroInfo["affairs"] = round($heroInfo["affairs_base"]+$heroInfo["affairs_add"]*($heroInfo["upgrade_value"]+$heroInfo["qixin_multip"]+$heroInfo["wenpo_multip"])*0.1);
	$heroInfo["bravery"] = round($heroInfo["bravery_base"]+$heroInfo["bravery_add"]*($heroInfo["upgrade_value"]+$heroInfo["qixin_multip"]+$heroInfo["wenpo_multip"])*0.1);
	$heroInfo["wisdom"] = round($heroInfo["wisdom_base"]+$heroInfo["wisdom_add"]*($heroInfo["upgrade_value"]+$heroInfo["qixin_multip"]+$heroInfo["wenpo_multip"])*0.1);
}
function changeChildName($uid,$param)
{
	$hid = intval(array_shift($param));
	$newName = trim(array_shift($param));

	$heroInfo = sql_fetch_one("select * from sys_user_child where uid='$uid' and hid='$hid'");
	if(empty($heroInfo))throw new Exception($GLOBALS['marrySystem']['child_not_exist']);
	if(intval($heroInfo['is_change'])>0)throw new Exception($GLOBALS['marrySystem']['modify_name_once']);
	if (mb_strlen ( $newName, "utf-8" ) > MAX_HERO_NAME) {
		throw new Exception ( $GLOBALS ['changeHeroName'] ['name_too_long'] );
	} else if ((!(strpos ( $newName, '\'') === false)) || (! (strpos ( $newName, '\\' ) === false))) {
		throw new Exception ($GLOBALS ['changeHeroName'] ['invalid_char']);
	} else if (strlen($newName) == 0) {
		throw new Exception ($GLOBALS ['changeHeroName'] ['input_valid_name']);
	}
	$lowername = strtolower ( $newName );
	if (sql_check ("select * from cfg_baned_name where instr('$lowername',`name`)>0")) {
		throw new Exception ($GLOBALS ['changeHeroName'] ['invalid_char']);
	}
	
	$newName = addslashes($newName);
	sql_query("update sys_user_child set name='$newName',is_change='1' where uid='$uid' and hid='$hid'");
	if(intval($heroInfo['out_hid'])>0){
		sql_query("update sys_city_hero set name='$newName' where hid={$heroInfo['out_hid']} and herotype='10001'");
	}

	$ret = array();
	$ret[] = $GLOBALS['shachange']['change_success'];
	$ret[] = loadMarryChild($uid);
	return $ret;
}
function startCulture($uid,$param)
{
	$hid = intval(array_shift($param));
	$curRoomIndex = intval(array_shift($param));

	$heroInfo = sql_fetch_one("select * from sys_user_child where uid='$uid' and hid='$hid'");
	if(empty($heroInfo))throw new Exception($GLOBALS['marrySystem']['child_not_exist']);
	if(!sql_check("select 1 from sys_goods where uid='$uid' and gid='12059' and count>=1"))throw new Exception($GLOBALS['marrySystem']['chengzhandan_good_not_enough']);

	addGoods($uid, 12059, -1, 826);
	//	计算培养成功的概率
	$succRate = 0;
	$succRand = mt_rand(1, 100);
	$cultureSucc = false;
	$currentTotal = intval($heroInfo['command_add'])+intval($heroInfo['affairs_add'])+intval($heroInfo['bravery_add'])+intval($heroInfo['wisdom_add']);
	if($currentTotal<=500){
		$succRate=90;
	}else if($currentTotal<=900){
		$succRate=70;
	}else if($currentTotal<=1300){
		$succRate=50;
	}else if($currentTotal>2300){
		$succRate=25;
	}
	if($succRand<=$succRate)
	{
		$cultureSucc = true;
	}

	$attrid=0;
	$addValue=0;
	if($cultureSucc)  //计算属性类型和对应增加值
	{
		$attrid = mt_rand(1, 4);
		$addValueRate = mt_rand(1,100);
		if($addValueRate<=70){
			$addValue=1;
		}else if($addValueRate<=95){
			$addValue=2;
		}else{
			$addValue=3;
		}

		if($attrid==1){  //加统帅
			sql_query("update sys_user_child set command_add = command_add+$addValue where uid='$uid' and hid='$hid'");
		}else if($attrid==2){  //加内政
			sql_query("update sys_user_child set affairs_add = affairs_add+$addValue where uid='$uid' and hid='$hid'");
		}else if($attrid==3){   //加勇武
			sql_query("update sys_user_child set bravery_add = bravery_add+$addValue where uid='$uid' and hid='$hid'");
		}else if($attrid==4){   //加智谋
			sql_query("update sys_user_child set wisdom_add = wisdom_add+$addValue where uid='$uid' and hid='$hid'");
		}
	}
	sql_query("insert into log_child_culture(`uid`,`hid`,`attid`,`value`,`time`) values('$uid','$hid','$attrid','$addValue',unix_timestamp())");

	$ret = array();
	$ret[] = $cultureSucc;
	$ret[] = array($attrid,$addValue);
	$ret[] = loadChildCulture($uid,array($curRoomIndex,$hid));
	return $ret;
}
function openChildRoom($uid,$param)
{
	sql_query("insert into sys_user_marry(`uid`) values('$uid') on duplicate key update roomcount=roomcount");
	$curRoomInfo = sql_fetch_one("select * from sys_user_marry where uid='$uid'");
	$userHasOpenCount=intval($curRoomInfo['roomcount']);
	if($userHasOpenCount>=3)throw new Exception($GLOBALS['marrySystem']['all_room_open']);

	$needCost=$userHasOpenCount==1?200:400;

	if(!checkMoney($uid, $needCost))throw new Exception($GLOBALS['buyGoods']['no_enough_YuanBao']);
	addMoney($uid, -$needCost, 826);

	sql_query("update sys_user_marry set roomcount=LEAST(roomcount+1,3) where uid='$uid'");

	return loadMarryChild($uid);
}
function doSpeedFeta($uid,$param)
{
	$motherHid = intval(array_shift($param));

	$now = sql_fetch_one_cell("select unix_timestamp()");
	$heroInfo = sql_fetch_one("select d.* from mem_marry_during d,mem_marry_hero_favor f where d.uid=f.uid and d.hid=f.hid and f.state='2' and d.hid='$motherHid' and f.uid='$uid'");
	if(empty($heroInfo))throw new Exception($GLOBALS['marrySystem']['not_in_during']);
	if(intval($heroInfo['endtime'])<=$now)throw new Exception($GLOBALS['marrySystem']['during_has_finish']);
	if(intval($heroInfo['isSpeed'])>0)throw new Exception($GLOBALS['marrySystem']['has_speed']);

	if(!checkMoney($uid, 500))throw new Exception($GLOBALS['buyGoods']['no_enough_YuanBao']);
	addMoney($uid, -500, 826);

	$reducetime = 77760;   //72*3600*0.3
	sql_query("update mem_marry_during set endtime=endtime-$reducetime,isSpeed='1' where uid='$uid' and hid='$motherHid'");

	$newMotherInfo = sql_fetch_one("select * from mem_marry_during where uid='$uid' and hid='$motherHid'");
	$newMotherInfo['leaveDuringTime']=intval($newMotherInfo['endtime'])-$now>0?intval($newMotherInfo['endtime'])-$now:0;
	$ret = array();
	$ret[] = $newMotherInfo;
	return $ret;
}
function checkRoomCount($uid,$param)
{
	$motherHid = intval(array_shift($param));
	sql_query("insert into sys_user_marry(`uid`) values('$uid') on duplicate key update roomcount=roomcount");
	$roomCount = sql_fetch_one_cell("select roomcount from sys_user_marry where uid='$uid'");
	$childCount = sql_fetch_one_cell("select count(1) from sys_user_child where uid='$uid'");
	if(empty($childCount))$childCount=1;

	sql_query("update mem_marry_hero_favor set state='3' where uid='$uid' and hid='$motherHid'");

	$isEnough=$roomCount>$childCount?true:false;
	$newMotherInfo = sql_fetch_one("select * from mem_marry_hero_favor where uid='$uid' and hid='$motherHid'");

	$ret = array();
	$ret[] = $isEnough;
	$ret[] = $newMotherInfo;
	return $ret;
}
function getMadamChild($uid,$param)  //接生孩子
{
	$type = intval(array_shift($param));
	$motherHid = intval(array_shift($param));

	if($type!=1&&$type!=2) throw new Exception($GLOBALS['marrySystem']['child_type_error']);
	$motherInfo = sql_fetch_one("select c.*,m.* from cfg_marry_mother_hero c,mem_marry_hero_favor m where c.hid=m.hid and m.uid='$uid' and m.hid='$motherHid'");
	if(empty($motherInfo)) throw new Exception($GLOBALS['marrySystem']['madam_not_exist']);

	if(intval($motherInfo['count'])>=2) throw new Exception($GLOBALS['marrySystem']['only_two_child']);

	$durInfo = sql_fetch_one("select * from mem_marry_during where uid='$uid' and hid={$motherInfo['hid']}");
	if(empty($durInfo)) throw new Exception($GLOBALS['marrySystem']['not_in_during_status']);
	$now = sql_fetch_one_cell("select unix_timestamp()");
	if(intval($durInfo['endtime'])>intval($now)) throw new Exception($GLOBALS['marrySystem']['in_during']);

	$curChildCount = sql_fetch_one_cell("select count(1) from sys_user_child where uid='$uid'");
	if(empty($curChildCount))$curChildCount=0;
	sql_query("insert into sys_user_marry(`uid`) values('$uid') on duplicate key update roomcount=roomcount");
	$curRoomCount = sql_fetch_one_cell("select roomcount from sys_user_marry where uid='$uid'");
	if($curChildCount>=$curRoomCount) throw new Exception($GLOBALS['marrySystem']['room_not_enough']);

	if($type==1)   //铜钱
	{
		$count = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='152'");
		if(empty($count)||intval($count)<200) throw new Exception($GLOBALS['blackMarket']['no_enough_copper']);
		addGoods($uid, 152, -200, 826);
	}else{   //元宝
		if(!checkMoney($uid, 180))throw new Exception($GLOBALS['buyGoods']['no_enough_YuanBao']);
		addMoney($uid, -180, 826);
	}

	//准备出生
	//基础属性
	$command = floor(mt_rand(20,30)+intval($motherInfo['command'])*0.2);
	$affaris = floor(mt_rand(20,30)+intval($motherInfo['affairs'])*0.2);
	$bravery = floor(mt_rand(20,30)+intval($motherInfo['bravery'])*0.2);
	$wisdom = floor(mt_rand(20,30)+intval($motherInfo['wisdom'])*0.2);
	$upgrade = mt_rand(2, 5);  //基础成长值

	$sex = mt_rand(0,1);   //0女1男
	$face = $sex==0?mt_rand(6,10):mt_rand(1, 5);
	//生成随机名字
	$name = "";
	$xing = sql_fetch_one_cell("select name from mem_cfg_firstname order by rand() limit 1");
	if($sex==0){
		$manName = sql_fetch_one_cell("select name from mem_cfg_boyname order by rand() limit 1");
		$name=$xing.$manName;
	}else{
		$womanName = sql_fetch_one_cell("select name from mem_cfg_girlname order by rand() limit 1");
		$name=$xing.$womanName;
	}
	$name = addslashes($name);
	$qixin=0;
	$wenpo=0;
	if(intval($durInfo['isSpeed'])>0)$qixin=2;
	if($type==2)$wenpo=1;

	//出生
	$sql = "insert into sys_user_child(`uid`,`name`,`sex`,`face`,`command_base`,`affairs_base`,`bravery_base`,`wisdom_base`,`qixin_multip`,`wenpo_multip`,`upgrade_value`) "
			."values('$uid','$name','$sex','$face','$command','$affaris','$bravery','$wisdom','$qixin','$wenpo','$upgrade')";
	sql_query($sql);
	$childHid = sql_fetch_one_cell("select last_insert_id()");
	sql_query("update mem_marry_hero_favor set count=LEAST(count+1,2),state='1' where uid='$uid' and hid='$motherHid'");
	sql_query("insert into mem_marry_relation(`uid`,`mHid`,`shid`) values('$uid','$motherHid','$childHid')");
	sql_query("insert into log_child_status(`hid`,`uid`,`out_hid`,`state`,`time`) values('$childHid','$uid','0','0',unix_timestamp()) on duplicate key update state='0',time=unix_timestamp()");
	//增加冷却时间  8小时
	sql_query("update mem_marry_hero_favor set coolingEndtime=unix_timestamp()+28800,state='1' where uid='$uid' and hid='$motherHid'");
	sql_query("delete from mem_marry_during where uid='$uid' and hid='$motherHid'");

	$userName = sql_fetch_one_cell("select name from sys_user where uid='$uid'");
	$informMsg = sprintf($GLOBALS['marrySystem']['child_out_inform'],$userName);
	sendSysInform(0,1,0,300,1800,1,16247152,$informMsg);

	$msg = $GLOBALS['marrySystem']['child_out_msg'];
	$motherFavorInfo = sql_fetch_one("select * from mem_marry_hero_favor where uid='$uid' and hid='$motherHid'");

	$ret = array();
	$ret[] = $msg;
	$ret[] = $motherFavorInfo;
	return $ret;
}
function getMadamLeavingTime($uid,$param)
{
	$motherHid = intval(array_shift($param));
	$motherInfo = sql_fetch_one("select * from mem_marry_during where uid='$uid' and hid='$motherHid'");
	if(empty($motherInfo)) throw new Exception($GLOBALS['marrySystem']['madam_not_exist']);

	$now = sql_fetch_one_cell("select unix_timestamp()");

	$ret = array();
	$motherInfo['leaveDuringTime']=intval($motherInfo['endtime'])-$now>0?intval($motherInfo['endtime'])-$now:0;

	if(intval($now)>=intval($motherInfo['endtime'])){  //更新到可接生阶段
		sql_query("update mem_marry_hero_favor set state='3' where uid='$uid' and hid={$motherInfo['hid']}");
	}

	$ret[] = $motherInfo;
	return $ret;
}
function addMadamFavor($uid,$param)
{
	$type = intval(array_shift($param));
	$madamHid = intval(array_shift($param));
	$madamInfo = sql_fetch_one("select * from mem_marry_hero_favor where uid='$uid' and hid='$madamHid'");
	$now = sql_fetch_one_cell("select unix_timestamp()");
	if($type!=1&&$type!=2) throw new Exception($GLOBALS['marrySystem']['child_type_error']);
	if(empty($madamInfo)) throw new Exception($GLOBALS['marrySystem']['madam_not_exist']);
	if(intval($madamInfo['state'])!=1 && intval($madamInfo['state'])!=0) throw new Exception($GLOBALS['marrySystem']['madam_status_error']);
	if(intval($madamInfo['favor'])>=100) throw new Exception($GLOBALS['marrySystem']['madam_favor_full']);
	if(intval($madamInfo['coolingEndtime'])>$now) throw new Exception($GLOBALS['marrySystem']['madam_in_cooling']);
	$curFavor = intval($madamInfo['favor']);
	$gid = getGidByFavor($curFavor);
	$goodCnt = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='$gid'");
	$goodName=sql_fetch_one_cell("select name from cfg_goods where gid='$gid'");
	if(empty($goodCnt)||intval($goodCnt)<1)
	{
		$msg=sprintf($GLOBALS['marry']['lack_gift'],$goodName);
		throw new Exception($msg);
	} 
	if($curFavor==80 && intval($madamInfo['state'])==0)
	{
		throw new Exception($GLOBALS['marry']['can_not_send_gift']);
	}
	if($curFavor==88||$curFavor==98)  //怀孕
	{
		if(intval($madamInfo['state'])!=1)
		{
			throw new Exception($GLOBALS['waigua']['invalid']);
		}
		sql_query("update mem_marry_hero_favor set state='2' where uid='$uid' and hid='$madamHid'");
		sql_query("insert into mem_marry_during(`uid`,`hid`,`starttime`,`endtime`) values('$uid','$madamHid',unix_timestamp(),unix_timestamp()+259200)");
	}

	addGoods($uid, $gid, -1, 826);
	sql_query("update mem_marry_hero_favor set favor=LEAST(favor+2,100) where uid='$uid' and hid='$madamHid'");

	$newMadamInfo = sql_fetch_one("select * from mem_marry_hero_favor where uid='$uid' and hid='$madamHid'");
	$nextGid = getGidByFavor($curFavor+2);
	addNextGoodInfo($newMadamInfo,$nextGid);

	$ret = array();
	$ret[] = $type;
	$ret[] = $GLOBALS['marrySystem']['add_good_succ'];
	$ret[] = $newMadamInfo;
	return $ret;
}
function getGidByFavor($curFavor)
{
	$gid=-1;
	if($curFavor>=100)$curFavor=99;
	if($curFavor<20)
	{
		$gid=12066;
	}else if($curFavor<50)
	{
		$gid=12067;
	}else if($curFavor<80)
	{
		$gid=12068;
	}else if($curFavor<100)
	{
		$gid=12069;
	}
	return $gid;
}
function addNextGoodInfo(&$madamInfo,$gid)
{
	$uid = $madamInfo['uid'];
	$curGood = sql_fetch_one("select c.name,ifnull(s.count,0) as count from cfg_goods c left join sys_goods s on c.gid=s.gid and s.uid='$uid' where c.gid='$gid'");
	$madamInfo['nextGidName']=$curGood['name'];
	$madamInfo['nextGidCount']=$curGood['count'];
}
function getMadamFavorInfo($uid,$param)
{
	$madamHid = intval(array_shift($param));
	$madamInfo = sql_fetch_one("select * from mem_marry_hero_favor where uid='$uid' and hid='$madamHid'");
	$now = sql_fetch_one_cell("select unix_timestamp()");
	if(empty($madamInfo)) throw new Exception($GLOBALS['marrySystem']['madam_not_exist']);

	$leaveTime = intval($madamInfo['coolingEndtime'])-$now>0?intval($madamInfo['coolingEndtime'])-$now:0;
	$madamInfo['leaveCoolTime'] = $leaveTime;

	$curGid = getGidByFavor($madamInfo['favor']);
	addNextGoodInfo($madamInfo,$curGid);

	$ret = array();
	$ret[] = $madamInfo;
	return $ret;
}
/*
 * 加载入口界面信息--py
 */
function loadMarryInfo($uid)
{
	$ret=array();
	$exprInfo=sql_fetch_one("select a.state,unix_timestamp() as nowtime,b.starttime,b.endtime from sys_city_hero a left join sys_king_expr b on a.hid=b.hid where a.uid='$uid' and a.herotype='1000' ");
	$girlFriendInfo=sql_fetch_rows("select *,unix_timestamp() as nowtime from mem_marry_hero_favor a left join cfg_marry_mother_hero b on a.hid=b.hid where uid='$uid' and a.state='0' ");
	
	$ret[]=$exprInfo;
	$ret[]=$girlFriendInfo;
	return $ret;
}
/*
 * 加载历练信息--py
 */
function loadMarryKingExpr($uid)
{
	$ret=array();
	$exprInfo=sql_fetch_one("select a.state,unix_timestamp() as nowtime,b.starttime,b.endtime from sys_city_hero a left join sys_king_expr b on a.hid=b.hid where a.uid='$uid' and a.herotype='1000' ");

	$ret[]=$exprInfo;
	return $ret;
	
}
/*
 * 君主开始历练函数--py
 */
function beginKingExpr($uid,$param)
{
	$hours=intval(array_shift($param));
	if($hours<1 || $hours>8) //检测时间
	{
		throw new Exception($GLOBALS['waigua']['invalid']);
	}
	$nowtime=sql_fetch_one_cell("select unix_timestamp()");
	$vacendTime = sql_fetch_one_cell("select vacend from sys_user_state where uid='$uid'");
	if(!empty($vacendTime) && intval($vacendTime)>$nowtime)   //如果玩家当前处于休假状态，就不让将领进行修炼
	{
		throw new Exception($GLOBALS['marry']['vacend_can_not_expr']);
	}
	$hero=sql_fetch_one("select state,hid,cid from sys_city_hero where uid='$uid' and herotype='1000' ");
	if (empty($hero)) 
	{
		throw new Exception($GLOBALS['useGoods']['king_cannot_find']);
	}
	if ($hero['state']!=0) 
	{
		throw new Exception($GLOBALS['marry']['king_hero_not_free']);
	}
	logUserAction($uid,21);
	sql_query ( "insert into sys_king_expr(`uid`,`cid`,`hid`,`type`,`starttime`,`endtime`,`hours`,`accTimes`,`state`) values ('$uid',{$hero['cid']},{$hero['hid']},0,unix_timestamp(),unix_timestamp()+3600*$hours,$hours,0,0)" );
	sql_query ( "update sys_city_hero set state = 10 where hid = {$hero['hid']}" );
	$ret=array();
	$ret[]=$GLOBALS['marry']['start_expr'];
	$ret[]=loadMarryKingExpr($uid);
	return $ret;
}
/*
 * 加速历练--py
 */
function fasterKingExpr($uid)
{
	$uid=intval($uid);
	$heroExprInfo=sql_fetch_one("select * from sys_king_expr where uid='$uid' ");
	if(empty($heroExprInfo))
	{
		throw new Exception($GLOBALS['marry']['not_in_expr']);
	}
	$now=sql_fetch_one_cell("select unix_timestamp()");
	if($heroExprInfo['state']==0) //历练过程中
	{
		if(intval($heroExprInfo['endtime'])<=$now) throw new Exception($GLOBALS['marry']['not_in_expr']);
		$gid=12060;//君王拜帖
		if (!checkGoods($uid, $gid)) 
		{
			throw new Exception("not_enough_goods".$gid);
		} 
		$CFG_REDUCE=30*60;
		$reduceTime=intval(floor(($heroExprInfo['endtime']-$now)*0.3));			
		$reduceTime=$reduceTime>$CFG_REDUCE?$reduceTime:$CFG_REDUCE;
		$newEndTime=$heroExprInfo['endtime']-$reduceTime;
		sql_query("update sys_king_expr set endtime='$newEndTime',accTimes=accTimes+1 where hid={$heroExprInfo['hid']}");
		reduceGoods($uid, $gid, 1);
	}
	else if ($heroExprInfo['state']==1) //返回途中
	{
		if(intval($heroExprInfo['endtime'])<=$now) throw new Exception($GLOBALS['marry']['king_has_back']);
		$gid=12061;//鸿雁飞书
		if (!checkGoods($uid, $gid))
		{
			throw new Exception("not_enough_goods".$gid);
		}
		sql_query("delete from sys_king_expr where hid={$heroExprInfo['hid']}");
		sql_query("update sys_city_hero set state='0' where hid={$heroExprInfo['hid']}");
		reduceGoods($uid, $gid, 1);
	}
	return loadMarryKingExpr($uid);
}
/*
 * 取消历练--py
 */
function cancelKingExpr($uid)
{
	$uid=intval($uid);
	$heroExprInfo=sql_fetch_one("select * from sys_king_expr where uid='$uid' ");
	if(empty($heroExprInfo) || $heroExprInfo['state']!=0)
	{
		throw new Exception($GLOBALS['marry']['not_in_expr']);
	}
	logActionCountback($uid,21);//将领历练次数
	sql_query ( "update sys_king_expr set state = 1,endtime= if(unix_timestamp()-starttime>hours*1800,endtime,2*unix_timestamp()-starttime ) where hid = {$heroExprInfo['hid']}" );
	sql_query ( "update sys_city_hero set state = 11 where hid ={$heroExprInfo['hid']} and state = 10" );
	return loadMarryKingExpr($uid);
}
/*
 * 接收战报里传来的参数，赠送红玫瑰，路人变红颜--py
 */
function finishMeetWife($uid,$param)
{
	$id=intval(array_shift($param));
	$rewardinfo=sql_fetch_one("select * from sys_king_expr_reward where uid='$uid' and id='$id' ");
	if(empty($rewardinfo))
		throw new Exception($GLOBALS['marry']['reward_not_exist']);
	if($rewardinfo['getFlag']==1)
		throw new Exception($GLOBALS['marry']['reward_over']);
	$needCount = sql_fetch_one_cell("select count from cfg_marry_mother_hero where hid={$rewardinfo['hid']}");
	if(empty($needCount))$needCount=1;   //默认值给1
	//奖励存在且没领取，送玫瑰啦
	$gid=12065;
	if (!checkGoodsCount($uid, $gid,$needCount))
	{
		throw new Exception($GLOBALS['good']['not_enough_goods83']);
	}
	//送女朋友，把奖励置成已领取
	sql_query("insert into mem_marry_hero_favor(`uid`,`hid`,`favor`,`state`,`count`) values('$uid','$rewardinfo[hid]','0','0','0') ");
	sql_query("update sys_king_expr_reward set getFlag='1' where id='$id'");
	reduceGoods($uid, $gid, $needCount);
	$username=sql_fetch_one_cell("select name from sys_user where uid='$uid'");
	$girlfriendName=sql_fetch_one_cell("select name from cfg_marry_mother_hero where hid='$rewardinfo[hid]'");
	$msg="玩家:".$username."送上".$needCount."朵红玫瑰以表爱慕！".$girlfriendName."经不住引诱成为其女朋友！！！";
	//$msg=sprintf($GLOBALS['marry']['become_girlfriend_sys'],$username,$girlfriendName);
	sendSysInform(0,1,0,600,50000,1,16247152,$msg);
	$msg=sprintf($GLOBALS['marry']['become_girlfriend'],$girlfriendName);
	throw new Exception("$msg");
}
/*
 * 分手--py
 */
function byebye($uid,$param)
{
	$hid=intval(array_shift($param));
	$girlfriendInfo=sql_fetch_one("select a.*,b.name from mem_marry_hero_favor a left join cfg_marry_mother_hero b on a.hid=b.hid where a.uid='$uid' and a.hid='$hid' ");
	if(empty($girlfriendInfo) || $girlfriendInfo['state']!=0)
		throw  new Exception($GLOBALS['marry']['girlfriend_none']);
	 $msg=sprintf($GLOBALS['marry']['girlfriend_broke_up'],$girlfriendInfo['name']);
	 //开删
	 sql_query("delete from sys_king_expr_reward where uid='$uid' and hid='$hid'");
	 sql_query("delete from mem_marry_hero_favor where uid='$uid' and hid='$hid'");
	 $ret=array();
	 $ret[]=$msg;
	 return $ret;
}
/*
 * 提亲--py
 */
function proposeMarry($uid,$param)
{
	if(count($param)!=2)
		throw new Exception($GLOBALS['waigua']['invalid']);
	$hid=intval(array_shift($param));
	$giftType=intval(array_shift($param));
	$girlfriendInfo=sql_fetch_one("select a.*,b.name from mem_marry_hero_favor a left join cfg_marry_mother_hero b on a.hid=b.hid where a.uid='$uid' and a.hid='$hid' ");
	if(empty($girlfriendInfo))
		throw  new Exception($GLOBALS['marry']['girlfriend_none']);
	if($girlfriendInfo['favor']<50 || $girlfriendInfo['state']!=0)
		throw new Exception($GLOBALS['waigua']['invalid']);
	$now=sql_fetch_one_cell("select unix_timestamp()");
	if($girlfriendInfo['proposeCoolTime']>$now)
	{
		$msg=sprintf($GLOBALS['marrySystem']['propose_cool'],$girlfriendInfo['name']);
		$leftTime=$girlfriendInfo['proposeCoolTime']-$now;
		throw new Exception($msg.MakeTimeLeft($leftTime));
	}
	//高等聘礼，666元宝；中等聘礼，333元宝；低等聘礼，222铜钱；空手
	if($giftType>4 || $giftType<1)
		throw new Exception($GLOBALS['waigua']['invalid']);
	checkGoodAndReduce($uid,$giftType);
	
	$rateArr=array(1=>5,2=>30,3=>90,4=>100);
	$rand=mt_rand(1, 100);
	$ret=array();
	if($rand<=$rateArr[$giftType]) 
	{
		$npcid=sql_fetch_one_cell("select npcid from cfg_marry_mother_hero where hid='$hid' "); //守护大哥
		$kingHid=sql_fetch_one_cell("select hid from sys_city_hero where uid='$uid' and herotype='1000'");
		$attackHeroPartInfo = sql_fetch_one ( "select a.hid,a.sex,`force` as energy,11 as standIndex,command_base+command_add_on as command,affairs_base+affairs_add+affairs_add_on as affair,bravery_base+bravery_add+bravery_add_on as bravery,wisdom_base+wisdom_add+wisdom_add_on as wisdom,speed_add_on as speed from sys_city_hero a,mem_hero_blood b where a.hid=b.hid and a.hid='$kingHid' " );
		$resistHeroPartInfo = sql_fetch_one ( "select hid,sex,energy+energy_add as energy,11 as standIndex,command_base+command_add as command,affair_base+affair_add as affair,bravery_base+bravery_add as bravery,wisdom_base+wisdom_add as wisdom,speed_base+speed_add as speed from cfg_pk_hero where hid='$npcid' " );
		if(empty($resistHeroPartInfo))
			throw new Exception($GLOBALS['waigua']['invalid']);

		$kingHero=sql_fetch_one("select h.*,b.* from sys_city_hero h,mem_hero_blood b where h.`hid`=b.`hid` and h.hid='$kingHid' ");
		$npcHero = sql_fetch_one("select *,FLOOR((bravery_base+bravery_add)*5+(energy+energy_add)*10+wisdom_base+wisdom_add) as battle from cfg_pk_hero where hid='$npcid' ");
		$ret [] =0;
		$ret[]=$giftType;
		$ret []=array($kingHero,$npcHero);
		$ret [] = startUserPK ( array ($attackHeroPartInfo), array ($resistHeroPartInfo) );
	}
	else
	{
		$ret [] =1;	//表示玩家没有碰到守护npc，提亲成功
		$ret []=$girlfriendInfo['name'];
	}
	if($ret[3]['winer']==1 || $ret[0]==1)   //发提亲成功公告
	{
		sql_query("update mem_marry_hero_favor set state='1' where uid='$uid' and hid='$hid'");
		$username=sql_fetch_one_cell("select name from sys_user where uid='$uid'");
		$msg=sprintf($GLOBALS['marry']['get_married'],$username,$girlfriendInfo['name']);
		sendSysInform(0,1,0,600,50000,1,16247152,$msg);
	}
	//增加提亲冷却时间3小时
	sql_query("update mem_marry_hero_favor set proposeCoolTime=($now+3*60*60) where  uid='$uid' and hid='$hid'  ");
	return $ret;
}
function checkGoodAndReduce($uid,$type)
{
	$userMoney = sql_fetch_one_cell("select money from sys_user where uid='$uid'");
	$userCopper = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='152'");
	if(empty($userMoney))$userMoney=0;
	if(empty($userCopper))$userCopper=0;
	
	if($type==1)  //666元宝
	{
		if(intval($userMoney)<666) throw new Exception($GLOBALS['buyGoods']['no_enough_YuanBao']);
		addMoney($uid, -666, 826);
	}else if($type==2)  //333元宝
	{
		if(intval($userMoney)<333) throw new Exception($GLOBALS['buyGoods']['no_enough_YuanBao']);
		addMoney($uid, -333, 826);
	}else if($type==3)  //222铜钱
	{
		if(intval($userCopper)<222) throw new Exception($GLOBALS['blackMarket']['no_enough_copper']);
		addGoods($uid, 152, -222, 826);
	}
}