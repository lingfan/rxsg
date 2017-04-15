<?php
require_once("./interface.php");
require_once("./utils.php");
function getDefenceSpeedRate($cid)
{
    $speed_add = 0;
    //建筑技术：每升1级，加快建筑、城防工事的建造速度100%。
    $buildingTechLevel = sql_fetch_one_cell("select level from sys_city_technic where cid=".$cid." and tid=17");
    if (!empty($buildingTechLevel)) 
    {
        $speed_add += 100 * $buildingTechLevel;
    }
    //城守内政：加快城池建筑、城防工事建造速度1%。
    $chiefhid = sql_fetch_one_cell("select chiefhid from sys_city where cid='$cid'");
    if ($chiefhid > 0)
    {
        $chief = sql_fetch_one("select * from sys_city_hero where hid='$chiefhid'");
        if (!empty($chief))
        {
            $bufadd = 1.0;
            if (isHeroHasBuffer($chiefhid,2)) $bufadd = 1.25;   //文曲星符
            $speed_add += ($chief['affairs_base'] + $chief['affairs_add'])*$bufadd;
        }
    }
    
    //城防加固
    $skill_rate=0;
    $chiefHid = sql_fetch_one_cell("select chiefhid from sys_city where cid=$cid");
    if(intval($chiefHid)>0)
    {
	    $attrValue = sql_fetch_one_cell("select b.attr from sys_user_book a,cfg_book b where a.bid=b.id and a.level=b.level and a.bid=17 and a.hid=$chiefHid");
	    if (empty($attrValue)) $attrValue=0;
	    $skill_rate=$attrValue*0.01;
    }
    $finalSpeed = (1.0 / (1.0 + 0.01 * $speed_add)) * (1 - $skill_rate);

    return $finalSpeed;
}
//获得城里的士兵信息
function doGetDefenceInfo($uid,$cid)
{
	checkCityExist($cid,$uid);	
	$cityDefences = sql_fetch_rows("select d.*,c.count from cfg_defence d left join sys_city_defence c on c.cid='$cid' and c.did=d.did order by d.did");
	$ret = array();
    $speedAdd = getDefenceSpeedRate($cid);
	foreach($cityDefences as $defence)
	{
		$state = new WallDefenceState();
		$state->did = $defence['did'];
		$state->dname = $defence['name'];
		$state->count = empty($defence['count'])?0:$defence['count'];
		$state->description = $defence['description'];
		$state->hp = $defence['hp'];
		$state->ap = $defence['ap'];
		$state->dp = $defence['dp'];
		$state->range = $defence['range'];
		$state->speed = $defence['speed'];
		$state->carry = $defence['carry'];
		$state->woodNeed = $defence['wood_need'];
		$state->rockNeed = $defence['rock_need'];
		$state->ironNeed = $defence['iron_need'];
		$state->foodNeed = $defence['food_need'];
		$state->goldNeed = $defence['gold_need'];
		$state->areaNeed=$defence['area_need'];
		$state->reinforce_time = max(1,$defence['time_need'] * $speedAdd);
		$state->can_reinforce = true;
		//判断其它条件是否满足
		$state->conditions = array();
		$conditions = sql_fetch_rows("select * from cfg_defence_condition where did='$defence[did]' order by `pre_type`");	//先建筑后科技
		foreach($conditions as $condition)
		{
			$cond = new UpgradeCondition();
			if ($condition['pre_type'] == 0)	//building
			{
				$cond->type = $GLOBALS['doGetDefenceInfo']['pre_building'];
				$pre_building_id = $condition['pre_id'];
				$curr_building_level = sql_fetch_one_cell("select max(`level`) from sys_building where cid='$cid' and `bid`='$pre_building_id'");
				$cond->canUpgrade = true;
				if (empty($curr_building_level) || $curr_building_level < $condition['pre_level'])
				{
					$cond->canUpgrade = false;
					$state->can_reinforce = false;
				}
				$buildingName = sql_fetch_one_cell("select `name` from cfg_building where bid='$pre_building_id'");
				$cond->upgradeNeed = $buildingName . "(".$GLOBALS['doGetDefenceInfo']['level']. $condition['pre_level'] . ")";
				$cond->currentOwn = $GLOBALS['doGetDefenceInfo']['level'] . (empty($curr_building_level)?0:$curr_building_level);
			}
			else if ($condition['pre_type'] == 1) //technic
			{
				$cond->type = $GLOBALS['doGetDefenceInfo']['pre_technic'];
				$pre_technic_id = $condition['pre_id'];
				$curr_technic_level = sql_fetch_one_cell("select max(`level`) from sys_technic where uid='$uid' and `tid`='$pre_technic_id'");
				$cond->canUpgrade = true;
				if (empty($curr_technic_level) || $curr_technic_level < $condition['pre_level'])
				{
					$cond->canUpgrade = false;
					$state->can_reinforce = false;
				}
				$technicName = sql_fetch_one_cell("select `name` from cfg_technic where tid='$pre_technic_id'");
				$cond->upgradeNeed = $technicName . "(".$GLOBALS['doGetDefenceInfo']['level'] . $condition['pre_level'] . ")";
				$cond->currentOwn = $GLOBALS['doGetDefenceInfo']['level'] . (empty($curr_technic_level)?0:$curr_technic_level);
			}
			$state->conditions[] = $cond;
		}  
		
		$ret[] = $state;
	}
	return $ret;
	
}            
//获得某兵营里的队列信息
function doGetReinforceQueue($uid,$cid)
{
	$queues = sql_fetch_rows("select r.*,d.name as dname,unix_timestamp() as nowtime from sys_city_reinforcequeue r left join cfg_defence d on d.did=r.did where r.`cid`='$cid' order by r.state desc,r.queuetime");
	$ret = array();
	foreach($queues as $queue)
	{
		$state = new WallReinforceState();
		$state->qid = $queue['id'];
		$state->did = $queue['did'];
		$state->dname = $queue['dname'];
		$state->count = $queue['count'];
		$state->state = $queue['state'];
		$state->time_left = $queue['needtime'];
		$state->accmark=$queue['accmark'];
		if ($state->state == 1)
		{
			$state->time_left = $queue['state_starttime'] + $state->time_left - $queue['nowtime'];
			if ($state->time_left == 0)
			{
				$state->time_left = 1;
			}
		}
		$ret[] = $state;
	}
	return $ret;
}
function getWallInfo($uid,$cid)
{
	$wall = sql_fetch_one("select * from sys_building where `cid`='$cid' and `bid`=".ID_BUILDING_WALL);
	if (empty($wall))
	{
		throw new Exception($GLOBALS['getWallInfo']['no_wall']);
	}
	return doGetBuildingInfo($uid,$cid,0,ID_BUILDING_WALL,$wall['level']);
}

//开始建造城防
function startReinforceQueue($uid,$cid,$param)
{
	$did = intval(array_shift($param));
	$defenceCount = intval(array_shift($param));
		
	if ($defenceCount <= 0) throw new Exception($GLOBALS['startReinforceQueue']['build_zero_defence']);
	checkCityExist($cid,$uid);
	if (!sql_check("select * from sys_building where `cid`='$cid' and `bid`=".ID_BUILDING_WALL)) return $GLOBALS['startReinforceQueue']['no_defence_info'];
	$defence_info = sql_fetch_one("select * from cfg_defence where did='$did'");
	if (empty($defence_info)) throw new Exception( $GLOBALS['startReinforceQueue']['no_defence_info']);

	if (!checkCityResource($cid,$defence_info['wood_need']*$defenceCount,
		$defence_info['rock_need']*$defenceCount,$defence_info['iron_need']*$defenceCount,
		$defence_info['food_need']*$defenceCount,$defence_info['gold_need']*$defenceCount))
	{
		throw new Exception($GLOBALS['startReinforceQueue']['no_enough_resource']);
	}
	
	if (getCityArea($cid)-getCityAreaOccupied($cid) < $defence_info['area_need'] * $defenceCount)
	{
		throw new Exception($GLOBALS['startReinforceQueue']['no_free_space']);
	}
	//其它前提条件是否满足
	$conditions = sql_fetch_rows("select * from cfg_defence_condition where did='$did' order by `pre_type`");	//先建筑后科技
	foreach($conditions as $condition)
	{                                     
		if ($condition['pre_type'] == 0)	//building
		{                            
			$pre_building_id = $condition['pre_id'];
			$curr_building_level = sql_fetch_one_cell("select max(`level`) from sys_building where cid='$cid' and `bid`='$pre_building_id'");
			if (empty($curr_building_level) || $curr_building_level < $condition['pre_level'])
			{
				throw new Exception( $GLOBALS['startReinforceQueue']['no_pre_building']);
			}                                                                                     
		}
		else if ($condition['pre_type'] == 1) //technic
		{                           
			$pre_technic_id = $condition['pre_id'];
			$curr_technic_level = sql_fetch_one_cell("select max(`level`) from sys_technic where uid='$uid' and `tid`='$pre_technic_id'");
			if (empty($curr_technic_level) || $curr_technic_level < $condition['pre_level'])
			{                              
				throw new Exception($GLOBALS['startReinforceQueue']['no_pre_technic']);
			}                                                                            
		}
	}
	//城墙已经有level+1个队列正在造的话，就不能开始
	$currentReinforcingCount = sql_fetch_one_cell("select count(*) from sys_city_reinforcequeue where `cid`='$cid'");
	if ($currentReinforcingCount >= sql_fetch_one_cell("select `level` from sys_building where `cid`='$cid' and `bid`=".ID_BUILDING_WALL) + 1)
	{
		throw new Exception($GLOBALS['startReinforceQueue']['queue_reach_limit']);
	}
	
	//扣钱
	addCityResources($cid,-$defence_info['wood_need']*$defenceCount,-$defence_info['rock_need']*$defenceCount,
		-$defence_info['iron_need']*$defenceCount,-$defence_info['food_need']*$defenceCount,-$defence_info['gold_need']*$defenceCount);
	
    $real_time_need = $defence_info['time_need'];  //真实需要的时间    
   
    $real_time_need *= getDefenceSpeedRate($cid);
    
    $real_time_need /= GAME_SPEED_RATE;
    
    $needtime=$real_time_need*$defenceCount;
    
	//开建
	if (sql_check("select * from sys_city_reinforcequeue where `cid`='$cid'"))	//如果墙已经有东东在造了
	{
		sql_query("insert into sys_city_reinforcequeue (`cid`,`did`,`count`,`queuetime`,`state`,`reinforce_interval`,`state_starttime`,`needtime`) values 
		('$cid','$did','$defenceCount',unix_timestamp(),0,'$real_time_need',0,'$needtime')");
		if (!sql_check("select * from sys_city_reinforcequeue where `cid`='$cid' and `state`=1"))	//万一不知道怎么回事没有队在训练，拉个最先的来
		{
			$id = sql_fetch_one_cell("select id from sys_city_reinforcequeue where `cid`='$cid' order by queuetime limit 1");
			if (!empty($id))
			{
				sql_query("update sys_city_reinforcequeue set state=1,state_starttime=unix_timestamp() where `id`='$id'"); 
				sql_query("insert into mem_city_reinforce (select id,cid,did,count,state_starttime+needtime from sys_city_reinforcequeue where `id`='$id')");
			}
		}
	}
	else	//如果还没有兵在造的话，则直接开始造
	{
		sql_query("insert into sys_city_reinforcequeue (`cid`,`did`,`count`,`queuetime`,`state`,`reinforce_interval`,`state_starttime`,`needtime`) values 
		('$cid','$did','$defenceCount',unix_timestamp(),1,'$real_time_need',unix_timestamp(),'$needtime')");
		$lastid = sql_fetch_one_cell("select LAST_INSERT_ID()");
		sql_query("insert into mem_city_reinforce (select id,cid,did,count,state_starttime+needtime from sys_city_reinforcequeue where `id`='$lastid')");
	}
	return getWallInfo($uid,$cid);
}

//停止正在排队的兵种
function stopReinforceQueue($uid,$cid,$param)
{
	$qid = intval(array_shift($param));
	
	checkCityExist($cid,$uid);
	if (!sql_check("select * from sys_building where `cid`='$cid' and `bid`=".ID_BUILDING_WALL)) throw new Exception( $GLOBALS['stopReinforceQueue']['no_barracks_info']);
	
	$queue = sql_fetch_one("select * from sys_city_reinforcequeue where `id`='$qid'");
	if (!empty($queue))
	{
		$defence_info = sql_fetch_one("select * from cfg_defence where did='$queue[did]'");
		if (empty($defence_info)) throw new Exception( $GLOBALS['stopReinforceQueue']['no_reinforcement_info']);
		//删队
		sql_query("delete from sys_city_reinforcequeue where id='$qid'");
		
		//还钱
		addCityResources($cid,	$defence_info['wood_need'] * $queue['count'] * 0.66,
								$defence_info['rock_need'] * $queue['count'] * 0.66,
								$defence_info['iron_need'] * $queue['count'] * 0.66,
								$defence_info['food_need'] * $queue['count'] * 0.66,
								$defence_info['gold_need'] * $queue['count'] * 0.66);
		if ($queue['state'] == 1)	//正在建设中
		{
			sql_query("delete from mem_city_reinforce where id='$qid'");
			//看看有没有其它队在排，有的话开始
			$id = sql_fetch_one_cell("select id from sys_city_reinforcequeue where `cid`='$queue[cid]' order by queuetime limit 1");																 
			if (!empty($id))
			{                                                                       
				sql_query("update sys_city_reinforcequeue set state=1,state_starttime=unix_timestamp() where `id`='$id'"); 
				sql_query("insert into mem_city_reinforce (select id,cid,did,count,state_starttime+needtime from sys_city_reinforcequeue where `id`='$id')");
			} 
		}
	}
	return getWallInfo($uid,$cid);
}
  
function dissolveDefence($uid,$cid,$param)
{                               
	$did = intval(array_shift($param));
	$defenceCount = intval(array_shift($param));
	                                   
	if ($defenceCount <= 0) throw new Exception($GLOBALS['dissolveDefence']['cant_dissolve_zero']);
	checkCityExist($cid,$uid);
	if (!sql_check("select * from sys_building where `cid`='$cid' and `bid`=".ID_BUILDING_WALL)) return $GLOBALS['dissolveDefence']['no_wall_info'];
	$defence_info = sql_fetch_one("select * from cfg_defence where did='$did'");
	if (empty($defence_info)) throw new Exception( $GLOBALS['dissolveDefence']['no_reinforcement_info']);
			  
	$cur_count = sql_fetch_one_cell("select `count` from sys_city_defence where `cid`='$cid' and `did`='$did'");
	if ($cur_count < $defenceCount) throw new Exception($GLOBALS['dissolveDefence']['cant_dissolve_exceed']);  
						                                   
	//拆除城防
	addCityDefence($cid,$did,-$defenceCount);
	//退钱
	addCityResources($cid,$defenceCount * $defence_info['wood_need']*0.33
    ,$defenceCount * $defence_info['rock_need']*0.33
    ,$defenceCount * $defence_info['iron_need']*0.33
    ,$defenceCount * $defence_info['food_need']*0.33
    ,$defenceCount * $defence_info['gold_need']*0.33);      
	return getWallInfo($uid,$cid);
}

function accReinforceQueue($uid,$cid,$param){
	static $MIN_REDUCE_TIME = 1800;//最小缩短30分钟
	$qid = intval(array_shift($param));
	
	checkCityExist($cid,$uid);
	if (!sql_check("select * from sys_building where `cid`='$cid' and `bid`=".ID_BUILDING_WALL)) throw new Exception( $GLOBALS['stopReinforceQueue']['no_barracks_info']);
	
	if(!checkGoods($uid,64)) throw new Exception("not_enough_goods64");
	
	$queue = sql_fetch_one("select * from sys_city_reinforcequeue where `id`='$qid'");
	if(empty($queue))
	{
		throw new Exception($GLOBALS['startReinforceQueue']['no_defence_info']);
	}
	else if ($queue['accmark'] != 0)
	{
		throw new Exception($GLOBALS['accDefence']['only_onece']);
	}
	$oldNeedTime = (int)$queue['needtime'];
	if ($queue['state'] == 1)	//正在建设中
	{
		$state_starttime = (int)$queue['state_starttime'];
		$curTime = $GLOBALS['now'];
		$reduceTime = intval(ceil($oldNeedTime + $state_starttime - $curTime) * 0.3);//缩短30%
		if ($reduceTime < $MIN_REDUCE_TIME) {
			$reduceTime = $MIN_REDUCE_TIME;	
		}
		$newNeedTime = $oldNeedTime - $reduceTime;
		sql_query("update sys_city_reinforcequeue set needtime=$newNeedTime, accmark=1 where id='$queue[id]'");
		
		$state_endtime = sql_fetch_one_cell("select state_endtime from mem_city_reinforce where id='$queue[id]'");
		$reduceTime2 = intval(floor($state_endtime - $curTime) * 0.3);
		if ($reduceTime2 < $MIN_REDUCE_TIME) {
			$reduceTime2 = $MIN_REDUCE_TIME;
		}
		$newNeedTime2 = $state_endtime - $reduceTime2; 
		sql_query("update mem_city_reinforce set state_endtime=$newNeedTime2 where id='$queue[id]'");
	}
	else
	{
		$reduceTime = intval(ceil($oldNeedTime * 0.3));
		if ($reduceTime < $MIN_REDUCE_TIME) {
			$reduceTime = $MIN_REDUCE_TIME;
		}
		$newNeedTime = $oldNeedTime - $reduceTime;
		sql_query("update sys_city_reinforcequeue set needtime=$newNeedTime, accmark=1 where id='$queue[id]'");
	}
	reduceGoods($uid,64,1);
	completeTaskWithTaskid($uid, 326);
	
	return getWallInfo($uid,$cid);
}


?>