<?php
require_once("interface.php");
require_once("utils.php");
require_once("TechnicFunc.php");
require_once("BuildingFunc.php");
require_once ('ActFunc.php');
//CityMergeTest();
//function CityMergeTest() {
//	$uid=1001;
//	$param=array(116251,117251);
//	mergeCity($uid,$param);
//}

function mergeCity($uid,$param) {
	set_time_limit(3600);
	$mainCid = intval(array_shift($param));//主城cid
	$subCid = intval(array_shift($param)); //分城cid
	checkCityMerge($uid,$mainCid,$subCid);
	subCityBak($uid,$subCid);
	$originalBuildings = sql_fetch_rows("select b.bid,c.name,group_concat(b.level) as levels from sys_building b left join cfg_building c on c.bid=b.bid left join cfg_building_merge 
		m on b.bid=m.bid where b.cid=$mainCid group by b.bid order by seq");
	if (! lockUser ( $uid ))
	   throw new Exception($GLOBALS['pacifyPeople']['server_busy']);
	// 主城官府升1级
	sql_query("update sys_building set level=level+1 where cid=$mainCid and bid=" . ID_BUILDING_GOVERMENT);
	$governLevel = sql_fetch_one_cell("select level from sys_building where cid=$mainCid and bid=" . ID_BUILDING_GOVERMENT);
	completeBuildTask($uid,ID_BUILDING_GOVERMENT,$governLevel);
	doMergeCity($uid,$mainCid, $subCid, 1); //合并城内
	doMergeCity($uid,$mainCid, $subCid, 0); //合并城外
	doMergeResource($mainCid,$subCid);
	//销毁分城
	destorySubCity($subCid);
	//恢复分城的野地，野地中其他玩家驻军都返回。
	clearSubCityWild($subCid);
	//扣除名城契约
	reduceMingchengqiyue($uid,$mainCid);
	//更新城池最大值
	updateCityPeopleMax($mainCid);
	updateCityGoldMax($mainCid);
	updateCityResourceAdd($mainCid);
	updateUserPrestige($uid);
	//生城报告
	$nowBuildings = sql_fetch_rows("select b.bid,c.name,group_concat(b.level) as levels from sys_building b left join cfg_building c on c.bid=b.bid left join cfg_building_merge 
		m on b.bid=m.bid where b.cid=$mainCid group by b.bid order by seq");
	makeMergeReport($uid, $mainCid, $originalBuildings, $nowBuildings);
	unlockuser($uid);
	return array(1);
}

function doMergeCity($uid,$mainCid, $subCid, $inner) {
	// 计算分城现有建筑的资源量
	$subBuildings = sql_fetch_rows("SELECT sys.bid, sys.level FROM sys_building  sys ,cfg_building  cfg  where sys.bid=cfg.bid AND cfg.inner=$inner and sys.cid=$subCid");
	
	$foodRes = 0;
	$woodRes = 0;
	$rockRes = 0;
	$ironRes = 0;
	
	foreach ($subBuildings as $subBuilding) {
		$bid = $subBuilding['bid'];
		$level = $subBuilding['level'];
		// 计算该级别类型建筑需要的资源数
		$needRes = calculateResource($bid, $level,true);//true表示升级前，直接算十级以下的资源。
		// 累加分城的所有建筑资源
		$foodRes += array_shift($needRes);
		$woodRes += array_shift($needRes);
		$rockRes += array_shift($needRes);
		$ironRes += array_shift($needRes);
	}
	//正式升级合并
	upgradeMainCityBuilding($uid,$mainCid, $foodRes, $woodRes, $rockRes, $ironRes, $inner);
}

function getUpgradeResource($bid,$level,$flag)
{
    static $cache=array();
    $key=$bid."_".$level;
    if(array_key_exists($key,$cache)){
        return $cache[$key];
    }
    $needResources = array();
    if ($flag) {
    	$needResources=sql_fetch_rows("SELECT level,upgrade_food, upgrade_wood, upgrade_rock, upgrade_iron FROM cfg_building_level  WHERE bid=$bid and level <= 10 order by level");
    } else {
    	$needResources=sql_fetch_rows("SELECT level,upgrade_food, upgrade_wood, upgrade_rock, upgrade_iron FROM cfg_building_level  WHERE bid=$bid order by level");
    }
    foreach($needResources as $needResource) {
    	$cache[$bid."_".$needResource["level"]]=$needResource;
    }
    return $cache[$key];
}
function calculateResource($bid, $level,$flag) {
	static $cache=array();
	$key=$bid."_".$level;
	if(array_key_exists($key,$cache)){
		return $cache[$key];
	}
	$totalRes = array();
	
	$foodRes = 0;
	$woodRes = 0;
	$rockRes = 0;
	$ironRes = 0;

	for ($i=1; $i <= $level; $i++) {
		$needRes = getUpgradeResource($bid,$i,$flag);
		
		$foodRes += $needRes['upgrade_food'];
		$woodRes += $needRes['upgrade_wood'];
		$rockRes += $needRes['upgrade_rock'];
		$ironRes += $needRes['upgrade_iron'];
	}
	
	$totalRes[] = $foodRes;
	$totalRes[] = $woodRes;
	$totalRes[] = $rockRes;
	$totalRes[] = $ironRes;
	$cache[$key]=$totalRes;
	return $totalRes;
}

function upgradeMainCityBuilding($uid,$mainCid, $foodRes, $woodRes, $rockRes, $ironRes, $inner) {
	$governLevel = sql_fetch_one_cell("select level from sys_building where cid=$mainCid and bid=" . ID_BUILDING_GOVERMENT);
	completeBuildTask($uid,ID_BUILDING_GOVERMENT,$governLevel);
	//取出主城内除官府外的所有建筑
	//$mainBuildings = sql_fetch_rows("SELECT sb.bid, xy, level FROM sys_building sb, cfg_building_merge cbm,cfg_building cb WHERE sb.bid=cbm.bid  AND sb.bid=cb.bid AND sb.cid=$mainCid AND sb.bid !=6 AND cb.inner=$inner ORDER BY seq, level");
	//取出正在升降级的建筑
	$xys=sql_fetch_map("select xy from mem_building_upgrading where cid=$mainCid union select xy from mem_building_destroying where cid=$mainCid","xy");
	while(true){
		//取出主城内除官府外的所有建筑.迁移到这里是为了避免由于一个民房建筑较低，每次都升级这个建筑的 问题。
		$mainBuildings = sql_fetch_rows("SELECT sb.bid, xy, level FROM sys_building sb, cfg_building_merge cbm,cfg_building cb WHERE sb.bid=cbm.bid  AND sb.bid=cb.bid AND sb.cid=$mainCid AND sb.bid !=6 AND cb.inner=$inner ORDER BY seq, level");
	   //一轮下来成功升级的建筑数目，如果该轮下来没建筑升级，则掉出下一轮，返回
		$upgradeCnt = 0;
		
		$houseUpgradeCnt = 0;//一轮下来民房成功升级的建筑数目
		$armyUpgradeCnt = 0;//一轮下来军营成功升级的建筑数目
		$storeUpgradeCnt = 0;//一轮下来仓库成功升级的建筑数目
		foreach ($mainBuildings as &$mainBuilding) {
			$bid = $mainBuilding['bid'];
			$level = $mainBuilding['level'];
			$xy = $mainBuilding['xy'];
			//正在升级或销毁中的建筑不能升级
			//if(sql_check("select 1 from mem_building_upgrading where cid=$mainCid and xy=$xy") || sql_check("select 1 from mem_building_destroying where cid=$mainCid and xy=$xy"))
            if(array_key_exists($xy,$xys))
			     continue;
			
			//民房，军营，仓库每轮最多只升级一个
			if((($bid == ID_BUILDING_HOUSE) && $houseUpgradeCnt > 0) || (($bid == ID_BUILDING_ARMY) && $armyUpgradeCnt > 0) || (($bid == ID_BUILDING_STORE) && $storeUpgradeCnt > 0))
				continue;
			
			$upgradeRes = getUpgradeResource($bid,$level+1,false);
		    if(($level < $governLevel) && ($foodRes >= $upgradeRes['upgrade_food']) && ($woodRes >= $upgradeRes['upgrade_wood']) && ($rockRes >= $upgradeRes['upgrade_rock']) && ($ironRes >= $upgradeRes['upgrade_iron'])) {
				$foodRes -= $upgradeRes['upgrade_food'];
				$woodRes -= $upgradeRes['upgrade_wood'];
				$rockRes -= $upgradeRes['upgrade_rock'];
				$ironRes -= $upgradeRes['upgrade_iron'];
				
				sql_query("update sys_building set level=level+1 where cid=$mainCid and bid=$bid and xy=$xy");
				completeBuildTask($uid,$bid,$level+1);
				$mainBuilding["level"]=$level+1;
				$upgradeCnt++;
				if($bid == ID_BUILDING_HOUSE)
					$houseUpgradeCnt++;
				if($bid == ID_BUILDING_ARMY)
					$armyUpgradeCnt++;
				if($bid == ID_BUILDING_STORE)
					$storeUpgradeCnt++;
			}
			else {
				continue;
			}
		}
		if($upgradeCnt == 0) {
			//将剩余资源放入主城资源里
			sql_query("update mem_city_resource set food=food+$foodRes, wood=wood+$woodRes, rock=rock+$rockRes, iron=iron+$ironRes where cid=$mainCid");
			return;
		}
	}
}

function destorySubCity($subCid) {
	if (empty($subCid)) return;
	sql_query("delete from mem_building_destroying where cid=$subCid");
	sql_query("delete from mem_building_upgrading where cid=$subCid");
	sql_query("delete from mem_city_autotrans where tocid=$subCid");
	sql_query("delete from mem_city_captive where cid=$subCid");
	sql_query("delete from mem_city_draft where cid=$subCid");
	sql_query("delete from mem_city_lamster where cid=$subCid");
	sql_query("delete from mem_city_reinforce where cid=$subCid");
	sql_query("delete from mem_city_resource where cid=$subCid");
	sql_query("delete from mem_city_schedule where cid=$subCid");
	sql_query("delete from mem_city_wounded where cid=$subCid");
	sql_query("delete from mem_technic_upgrading where cid=$subCid");
	sql_query("delete from mem_treasure_map where cid=$subCid");
	sql_query("delete from sys_building where cid=$subCid");
	sql_query("delete from sys_city where cid=$subCid");
	sql_query("delete from sys_city_defence where cid=$subCid");
	sql_query("delete from sys_city_draftqueue where cid=$subCid");
	sql_query("delete from sys_city_reinforcequeue where cid=$subCid");
	sql_query("delete from sys_city_res_add where cid=$subCid");
	sql_query("delete from sys_city_rumor where cid=$subCid");
	sql_query("delete from sys_city_soldier where cid=$subCid");
	sql_query("delete from sys_city_tactics where cid=$subCid");
	sql_query("delete from sys_city_technic where cid=$subCid");
	sql_query("delete from sys_city_trade where cid=$subCid");
	sql_query("delete from sys_building where cid=$subCid");
	sql_query("delete from sys_thing_position where cid=$subCid");
	sql_query("update sys_technic set state=0 where cid=$subCid");
	//城池变成平地
	sql_query("update mem_world set type=1, ownercid=0 where ownercid=$subCid and type=0");
	sql_query("update mem_world set ownercid=0 where ownercid=$subCid");
}

function makeMergeReport($uid, $mainCid, $originalBuildings, $nowBuildings) {
	$content=$GLOBALS['MergeCity']['table_start'];
	//取出各个升级信息
	$buildingCnt = count($originalBuildings);
	for ($i = 0; $i < $buildingCnt; $i++) {
		$originalBuilding = $nowBuilding = array();
		$originalBuilding = $originalBuildings[$i];
		$nowBuilding = $nowBuildings[$i];
		
		$originalLevels = $originalBuilding['levels'];
		$nowLevels = $nowBuilding['levels'];
		$upgradeLevelStr="";
		
		if(($originalBuilding['bid'] == ID_BUILDING_HOUSE) || ($originalBuilding['bid'] == ID_BUILDING_ARMY) || ($originalBuilding['bid'] == ID_BUILDING_STORE) || 
		($originalBuilding['bid'] == ID_BUILDING_FARMLAND) || ($originalBuilding['bid'] == ID_BUILDING_WOOD) || ($originalBuilding['bid'] == ID_BUILDING_ROCK) || ($originalBuilding['bid'] == ID_BUILDING_IRON)) {
			$origLevelArray = $nowLevelArray = array();
			$origLevelArray = explode ( ",", $originalLevels );
			$nowLevelArray = explode ( ",", $nowLevels );
		
			$arrayCnt = count($origLevelArray);
			for($j = 0; $j < $arrayCnt; $j++) {
				$upgradeLevel = $nowLevelArray[$j] - $origLevelArray[$j];
				$upgradeLevelStr .= $upgradeLevel;
				if($j != $arrayCnt - 1)
					$upgradeLevelStr .= ",";
			}
		}
		else {
			$upgradeLevelStr = $nowLevels - $originalLevels;
		}
		$content .= $GLOBALS['MergeCity']['table_item_1'];
		$content .= $originalBuilding['name'];
		$content .= $GLOBALS['MergeCity']['table_item_2'];
		$content .= $upgradeLevelStr;
		$content .= $GLOBALS['MergeCity']['table_item_3'];
	}
	
	$content.=$GLOBALS['MergeCity']['table_end'];
	
	$cityName = sql_fetch_one_cell("select name from sys_city where cid=$mainCid");
	sql_query("insert into sys_report(`uid`,`origincid`,`origincity`,`happencid`,`happencity`,`title`,`type`,`time`,`read`,`battleid`,`content`) 
		values ('$uid', '$mainCid', '$cityName', '$mainCid', '$cityName', '53', '3', unix_timestamp(), '0', '0', '$content')");
	sql_query("insert into sys_alarm (uid,report) values ('$uid',1) on duplicate key update report=1");
}

function checkCityMerge($uid,$mainCid,$subCid) {
	//检查名城契约相关
	$type = sql_fetch_one_cell("select type from sys_city where cid=$mainCid");
	if ($type != 5) {
		if (sql_check("select 1 from log_qiyue where uid=$uid and gid=160052 and count<0")) 
			throw new Exception($GLOBALS['MergeCity']['used']);
		$cnt = sql_fetch_one_cell("select count from sys_goods where gid=160052 and uid=$uid");
		if ($cnt < 1) throw new Exception($GLOBALS['MergeCity']['no_mingchengqiyue']);
	}
	//验证玩家爵位
	checkUserNobility($uid,$mainCid);
	$subCityState = sql_fetch_one_cell("select state from mem_world where type=0 and ownercid=$subCid");
	if ($subCityState == 1) throw new Exception($GLOBALS['MergeCity']['not_in_peace']); 
	
	$subCityType = sql_fetch_one_cell("select type from sys_city where cid=$subCid");
	if ($subCityType != 0) {
		throw new Exception($GLOBALS['MergeCity']['must_be_user_city']);
	}
	//检查城池归属
	if(!sql_check("select 1 from sys_city where cid=$mainCid and uid=$uid") || !sql_check("select 1 from sys_city where cid=$subCid and uid=$uid"))
		throw new Exception($GLOBALS['MergeCity']['not_your_city']);
	$spacialsid=getSpacialSoldierId($mainCid);
	if($spacialsid>0){//活动城池不让当主城合并
		throw new Exception($GLOBALS['MergeCity']['cannot_be_spacial_city']);
	}
	$spacialsid=getSpacialSoldierId($subCid);
	if($spacialsid>0){//活动城池不让当主城合并
		throw new Exception($GLOBALS['MergeCity']['cannot_be_spacial_city']);
	}
	//主城不能为王者之城
	if(sql_check("select 1 from sys_city where cid='$mainCid' and is_special='2'"))throw new Exception($GLOBALS['MergeCity']['cannot_be_isSpecial']);
	$mainCityType = sql_fetch_one_cell("select `type` from sys_city where cid=$mainCid and uid=$uid");
	if($mainCityType != 0 && $mainCityType != 5)  //主城必须为普通城池或者是玩家的主城。
		throw new Exception($GLOBALS['MergeCity']['cannot_be_famous_city']);
	//检查分城将领（军队）
	$heroCnt = sql_fetch_one_cell("select count(*) from sys_city_hero where cid=$subCid and uid=$uid");
	if($heroCnt > 0)
		throw new Exception($GLOBALS['MergeCity']['sub_city_not_empty']);
	$soldierCnt = sql_fetch_one_cell("select sum(`count`) from sys_city_soldier where sid<=12 and cid=$subCid");
	if($soldierCnt > 0)
		throw new Exception($GLOBALS['MergeCity']['sub_city_not_empty']);
	$troopCount = sql_fetch_one_cell("select count(*) from sys_troops where cid=$subCid");
	if($troopCount > 0)
		throw new Exception($GLOBALS['MergeCity']['sub_city_not_empty']);
	//$tradeCount = sql_fetch_one_cell("select count(*) from sys_city_trade where cid=$subCid");
	//if ($tradeCount > 0) {
	//	throw new Exception($GLOBALS['MergeCity']['sub_city_trade_not_empty']);
	//}
	//所有派过来的军队都返回
	sql_query("update sys_troops set state=1 where targetcid=$subCid");
	
	//主城官府必须10级以上
	$governLevel = sql_fetch_one_cell("select level from sys_building where cid=$mainCid and bid=" . ID_BUILDING_GOVERMENT);
	if($governLevel < 10)
		throw new Exception($GLOBALS['MergeCity']['government_not_enough_level']);
		
	if($governLevel >= 15)
		throw new Exception($GLOBALS['MergeCity']['government_max_level']);

}

function clearSubCityWild($subCid) {
	if (empty($subCid)) return;
	$wids = sql_fetch_rows("select wid from mem_world where ownercid=$subCid");
	foreach ($wids as $wid) {
		$cid = wid2cid($wid);
		sql_query("update sys_troops set state=1 where targetcid=$cid");
	}
}

function reduceMingchengqiyue($uid,$mainCid) {
	addGoods($uid,160052,-1,0);//这里type不需要。
	sql_query("update sys_goods set count=0 where uid=$uid and gid=160052");
	sql_query("update sys_city set type=5 where cid=$mainCid");
}

function subCityBak($uid,$cid) {
	if (empty($cid)) throw new Exception($GLOBALS['MergeCity']['bak_error']);
	sql_query("replace into mem_city_resource_bak select * from mem_city_resource where cid=$cid");
	sql_query("replace into mem_city_schedule_bak select * from mem_city_schedule where cid=$cid");
	sql_query("replace into sys_city_bak select * from sys_city where cid=$cid");
	sql_query("replace into sys_city_defence_bak select * from sys_city_defence where cid=$cid");
	sql_query("replace into sys_city_res_add_bak select * from sys_city_res_add where cid=$cid");
	sql_query("replace into sys_city_tactics_bak select * from sys_city_tactics where cid=$cid");
	sql_query("replace into sys_city_technic_bak select * from sys_city_technic where cid=$cid");
	sql_query("replace into sys_building_bak select * from sys_building where cid=$cid");
	sql_query("insert into log_merge_city(uid,cid,time) values('$uid','$cid',unix_timestamp())");
}

function doMergeResource($mainCid,$subCid) {
	$res = sql_fetch_one("select wood,food,iron,rock,gold from mem_city_resource where cid=$subCid");
	if (empty($res)) $res = array('wood'=>0,'rock'=>0,'iron'=>0,'food'=>0,'gold'=>0);
	sql_query("update mem_city_resource set wood=wood+{$res['wood']},rock=rock+{$res['rock']},iron=iron+{$res['iron']},food=food+{$res['food']},gold=gold+{$res['gold']} where cid=$mainCid");
}

function completeBuildTask($uid,$bid,$level) {
	$goalId = sql_fetch_one_cell("select id from cfg_task_goal where sort=6 and  count >10 and type=$bid and count=$level");
	if (!empty($goalId))
		sql_query("replace into sys_user_goal (`uid`,`gid`) values ($uid,$goalId)");
}

function checkUserNobility($uid,$mainCid) {
	$nobility = sql_fetch_one_cell("select nobility from sys_user where uid=$uid");
	$gLevel = sql_fetch_one_cell("select level from sys_building where bid=6 and cid=$mainCid"); 
	$flag = true;
	if (($gLevel == 10) && ($nobility <4)) {
		$flag = false;
		$name = sql_fetch_one_cell("select name from cfg_nobility where id=4");
	} else if (($gLevel == 11) && ($nobility <5)) {
		$flag = false;
		$name = sql_fetch_one_cell("select name from cfg_nobility where id=5");
	} else if (($gLevel == 12) && ($nobility <8)) {
		$flag = false;
		$name = sql_fetch_one_cell("select name from cfg_nobility where id=8");
	}else if (($gLevel == 13) && ($nobility <10)) {
		$flag = false;
		$name = sql_fetch_one_cell("select name from cfg_nobility where id=10");
	}else if (($gLevel == 14) && ($nobility <12)) {
		$flag = false;
		$name = sql_fetch_one_cell("select name from cfg_nobility where id=12");
	}
	if (!$flag) {
		$msg = sprintf($GLOBALS['MergeCity']['nobility'],$gLevel+1,$name);
		throw new Exception($msg);
	}
}
?>