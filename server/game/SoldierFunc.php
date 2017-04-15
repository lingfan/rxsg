<?php                      
require_once("./interface.php");
require_once("./utils.php");

function doGetSimpleCitySoldier($uid,$cid)
{
	return sql_fetch_rows("select s.*,c.count from cfg_soldier s left join sys_city_soldier c on c.cid='$cid' and c.sid=s.sid where s.fromcity=1 order by s.sid");
}
function getSoldierSpeedRate($cid,$sid,$speedfactor = 0)
{
       
    //科技加速
    //制造技术(5)：每升1级，加快器械生产速度10%。包括：辎重车(9)、床弩(10)、冲车(11)、投石车(12)。
    //练兵技巧(8)：每升1级，加快士兵训练速度10%。包括：民夫(1)、义兵(2)、长枪兵(4)、刀盾兵(5)、弓箭兵(6)、斥候(3)、轻骑兵(7)、铁骑兵(8)。
                                                                    
    $speed_add = 0;
    
    if (($sid == 9)||($sid==10)||($sid==11)||($sid==12)) //攻城器械：
    {
        $technic_level = sql_fetch_one_cell("select level from sys_city_technic where cid=".$cid." and tid=5");
        if (!empty($technic_level))   //检查本城是否有有效的制造技术
        {                                                  
            $speed_add += 10 * $technic_level;
        }
    }
    else if ($sid <= 8||($sid>=45&&$sid<=50))
    {
        $technic_level = sql_fetch_one_cell("select level from sys_city_technic where cid=".$cid." and tid=8");
        if (!empty($technic_level))   //检查本城是否有有效的练兵技术
        {                                                    
            $speed_add += 10 * $technic_level;
        }
    }
    
    //勇武：加快城池征兵速度1%。 檢查順序：主将，城守，军师
  	$cityhids = sql_fetch_one("select chiefhid,generalid,counsellorid from sys_city where cid='$cid'");
    $chiefhid=0;
    if(!empty($cityhids)){
    	if($cityhids['generalid']>0)
    		$chiefhid=$cityhids['generalid'];
    	else if($cityhids['chiefhid']>0)
    		$chiefhid=$cityhids['chiefhid'];
    	else if($cityhids['counsellorid']>0)
    		$chiefhid=$cityhids['counsellorid'];    	
    }
    if ($chiefhid > 0)
    {
        $chief = sql_fetch_one("select * from sys_city_hero where hid='$chiefhid'");
        if (!empty($chief))
        {
            $bufadd = 1.0;
            if (isHeroHasBuffer($chiefhid,3))   //武曲星符
            {
                $bufadd = 1.25;
            }
            $speed_add += ($chief['bravery_base'] + $chief['bravery_add']) * $bufadd + $chief['bravery_add_on'];
        }
    }
    
    //招兵买马
    $skill_rate=0;
    $chiefHid = sql_fetch_one_cell("select generalid from sys_city where cid=$cid");
	if (!empty($chiefHid)) {
	    $attrValue = sql_fetch_one_cell("select b.attr from sys_user_book a,cfg_book b where a.bid=b.id and a.level=b.level and a.bid=18 and a.hid=$chiefHid");
	    if (empty($attrValue)) $attrValue=0;
	    $skill_rate=$attrValue*0.01;
	}
    
    $finalSpeed = 1 / (1.0 + 0.01 * $speed_add + $speedfactor);
    $finalSpeed = $finalSpeed * (1-$skill_rate);
    //马来订制  司隶造兵速度-30%;    
    $yysType = sql_fetch_one_cell("select value from mem_state where state=198");//198是司隶开关    
    if($yysType == 1){
    	$wid=cid2wid($cid);
    	$province = sql_fetch_one_cell("select province from mem_world where wid=$wid");
    	if($province==1){
    		$finalSpeed*=0.7;
    	}
    }
    
    
    return $finalSpeed;
}
//获得城里的士兵信息
function doGetSoldierInfo($uid,$cid,$xy)
{
	checkCityExist($cid,$uid);	
	
	$citySoldiers = sql_fetch_rows("select s.*,c.count from cfg_soldier s left join sys_city_soldier c on c.cid='$cid' and c.sid=s.sid where s.fromcity=1 order by s.sid");
	$ret = array();
	foreach($citySoldiers as $soldier)
	{
		$sid=$soldier['sid'];
    	$speedRate = getSoldierSpeedRate($cid,$soldier['sid']);
		$state = new ArmySoldierState();
		$state->sid = $soldier['sid'];
		$state->sname = $soldier['name'];
		$state->count = empty($soldier['count'])?0:$soldier['count'];
		$state->description = $soldier['description'];
		$state->hp = $soldier['hp'];
		$state->ap = $soldier['ap'];
		$state->dp = $soldier['dp'];
		$state->range = $soldier['range'];
		$state->speed = $soldier['speed'];
		$state->carry = $soldier['carry'];
		$state->food_use = $soldier['food_use'];
		$state->woodNeed = $soldier['wood_need'];
		$state->rockNeed = $soldier['rock_need'];
		$state->ironNeed = $soldier['iron_need'];
		$state->foodNeed = $soldier['food_need'];
		$state->goldNeed = $soldier['gold_need'];
        $state->peopleNeed = $soldier['people_need'];
		$state->draft_time = max(1,floor($soldier['time_need'] * $speedRate));
		$state->can_draft = true;
		//判断其它条件是否满足
		$state->conditions = array();
		$conditions = sql_fetch_rows("select * from cfg_soldier_condition where sid='$soldier[sid]' order by `pre_type`");	//先建筑后科技
		foreach($conditions as $condition)
		{
			$cond = new UpgradeCondition();
			if ($condition['pre_type'] == 0)	//building
			{
				$cond->type = $GLOBALS['building']['pre_building'];
				$pre_building_id = $condition['pre_id'];
                
                if ($pre_building_id == ID_BUILDING_ARMY)
                {
                   $curr_building_level = sql_fetch_one_cell("select `level` from sys_building where cid='$cid' and `bid`='$pre_building_id' and `xy`='$xy'");  
                }
                else
                {
                    $curr_building_level = sql_fetch_one_cell("select max(`level`) from sys_building where cid='$cid' and `bid`='$pre_building_id'");
                }           
				$cond->canUpgrade = true;
				if (empty($curr_building_level) || $curr_building_level < $condition['pre_level'])
				{
					$cond->canUpgrade = false;
					$state->can_draft = false;
				}
				$buildingName = sql_fetch_one_cell("select `name` from cfg_building where bid='$pre_building_id'");
				$cond->upgradeNeed = $buildingName . "(".$GLOBALS['doGetSoldierInfo']['level'].$condition['pre_level'] . ")";
				$cond->currentOwn = $GLOBALS['doGetSoldierInfo']['level'] . (empty($curr_building_level)?0:$curr_building_level);
			}
			else if ($condition['pre_type'] == 1) //technic
			{
				$cond->type = $GLOBALS['doGetSoldierInfo']['pre_technic'];
				$pre_technic_id = $condition['pre_id'];
				$curr_technic_level = sql_fetch_one_cell("select max(`level`) from sys_technic where uid='$uid' and `tid`='$pre_technic_id'");
				$cond->canUpgrade = true;
				if (empty($curr_technic_level) || $curr_technic_level < $condition['pre_level'])
				{
					$cond->canUpgrade = false;
					$state->can_draft = false;
				}
				$technicName = sql_fetch_one_cell("select `name` from cfg_technic where tid='$pre_technic_id'");
				$cond->upgradeNeed = $technicName . "(".$GLOBALS['doGetSoldierInfo']['level'] . $condition['pre_level'] . ")";
				$cond->currentOwn = $GLOBALS['doGetSoldierInfo']['level'] . (empty($curr_technic_level)?0:$curr_technic_level);
			}
			$state->conditions[] = $cond;
		}  
		if($sid>=45&&$sid<=50){
			$spacialcitycond=new UpgradeCondition();	
			$spacialcitycond->type=$GLOBALS['building']['pre_city'];
			$spacialcitycond->upgradeNeed = $GLOBALS['building']['pre_city_need'];
			if(isSpacialSoldierCity($cid,$sid)){
				$spacialcitycond->currentOwn =$GLOBALS['building']['pre_city_yes'];
				$spacialcitycond->canUpgrade = true;
			}else{
				$spacialcitycond->currentOwn =$GLOBALS['building']['pre_city_no'];
				$spacialcitycond->canUpgrade = false;
				$state->can_draft = false;
			}
			$state->conditions[] = $spacialcitycond;
		}
		
		$ret[] = $state;     
	}
	return $ret;
	
}          
function isSpacialSoldierCity($cid,$sid){
	  $spacialsoldier=sql_check("select * from cfg_soldier_special_city where cid=$cid and sid=$sid limit 1");
	  if(empty($spacialsoldier)){
	  	return false;
	  }else{
	  	return true;
	  }
}
//获得某兵营里的队列信息
function doGetDraftQueue($uid, $cid, $xy) {
	$queues = sql_fetch_rows ( "select d.*,s.name as sname,unix_timestamp() as nowtime from sys_city_draftqueue d left join cfg_soldier s on s.sid=d.sid where d.`cid`='$cid' and d.`xy`='$xy' order by d.state desc,d.queuetime " );
	$ret = array ();
	foreach ( $queues as $queue ) {
		$state = new ArmyDraftState ();
		$state->qid = $queue ['id'];
		$state->sid = $queue ['sid'];
		$state->sname = $queue ['sname'];
		$state->count = $queue ['count'];
		$state->state = $queue ['state'];
		$state->time_left = $queue ['needtime'];
		$state->accmark = $queue ['accmark'];
		
//	//招兵买马
//	$skill_rate = 0;
//	$chiefHid = sql_fetch_one_cell ( "select generalid from sys_city where cid=$cid" );
//	if (! empty ( $chiefHid )) 
//	{
//		$attrValue = sql_fetch_one_cell ( "select b.attr from sys_user_book a,cfg_book b where a.bid=b.id and a.level=b.level and a.bid=18 and a.hid=$chiefHid" );
//		if (empty ( $attrValue ))
//			$attrValue = 0;
//		$skill_rate = $attrValue * 0.01;
//	}
//		$needTime = $state->time_left;
//		$realTime = $needTime - $needTime*$skill_rate;
//		if ($state->state == 1) {
//			//$state->time_left = $queue ['state_starttime'] + $state->time_left - $queue ['nowtime'];
//			$state->time_left = $queue ['state_starttime'] + $realTime - $queue ['nowtime'];
//			if ($state->time_left == 0) {
//				$state->time_left = 1;
//			}
//		}

	if ($state->state == 1)
		{
			$state->time_left = $queue['state_starttime'] + $state->time_left - $queue['nowtime'];
			if ($state->time_left == 0)
			{
				$state->time_left = 1;
			}
		}
		$ret [] = $state;
	}
	return $ret;
}
function getArmyInfo($uid,$cid,$xy)
{
	$army = sql_fetch_one("select * from sys_building where `cid`='$cid' and `xy`='$xy' and `bid`=".ID_BUILDING_ARMY);
	if (empty($army))
	{
		throw new Exception($GLOBALS['getArmyInfo']['no_barracks_built']);
	}
	return doGetBuildingInfo($uid,$cid,$xy,ID_BUILDING_ARMY,$army['level']);
}

//开始征兵
function startDraftQueue($uid,$cid,$param)
{                                       
	$inner = intval(array_shift($param));
	$x = intval(array_shift($param));
	$y = intval(array_shift($param));
	$xy = encodeBuildingPosition($inner,$x,$y);
	$sid = intval(array_shift($param));
	$soldierCount = intval(array_shift($param));

	$resourcefactor = 1;
	$speedfactor = 0;
	
	$double = array_shift($param);
	if($double == 1) {
		$resourcefactor = 2;
		$speedfactor = 1.5;
	}
	
	if ($soldierCount <= 0) throw new Exception($GLOBALS['startDraftQueue']['cant_recruit_zero']);
	checkCityExist($cid,$uid);
	if (!sql_check("select * from sys_building where `cid`='$cid' and `xy`='$xy' and `bid`=".ID_BUILDING_ARMY)) return $GLOBALS['startDraftQueue']['no_barracks_info'];
	$soldier_info = sql_fetch_one("select * from cfg_soldier where sid='$sid' and fromcity=1");
	if (empty($soldier_info)) throw new Exception( $GLOBALS['startDraftQueue']['no_army_branch_info']);
                                       
	if (!checkCityResource($cid,$soldier_info['wood_need']*$soldierCount * $resourcefactor,
		$soldier_info['rock_need']*$soldierCount * $resourcefactor,$soldier_info['iron_need']*$soldierCount * $resourcefactor,
		$soldier_info['food_need']*$soldierCount * $resourcefactor,$soldier_info['gold_need']*$soldierCount * $resourcefactor))
	{
		throw new Exception($GLOBALS['startDraftQueue']['no_enough_resource']);
	}
	                                    
    $soldierPeopleNeed = sql_fetch_one_cell("select people_need from cfg_soldier where sid=$sid");
         
	if (getCityPeopleFreeCount($cid) < $soldierPeopleNeed * $soldierCount)
	{
		$msg = sprintf($GLOBALS['startDraftQueue']['lack_free_people'],$soldierCount);
		//throw new Exception("空闲人口不足，不能训练".$soldierCount."个士兵。");
		throw new Exception($msg);
	}
                                       
	//其它前提条件是否满足
	$conditions = sql_fetch_rows("select * from cfg_soldier_condition where sid='$sid' order by `pre_type`");	//先建筑后科技
	foreach($conditions as $condition)
	{                                     
		if ($condition['pre_type'] == 0)	//building
		{                            
			$pre_building_id = $condition['pre_id'];
            if ($pre_building_id == ID_BUILDING_ARMY)
            {
               $curr_building_level = sql_fetch_one_cell("select `level` from sys_building where cid='$cid' and `bid`='$pre_building_id' and `xy`='$xy'");  
            }
            else
            {
			    $curr_building_level = sql_fetch_one_cell("select max(`level`) from sys_building where cid='$cid' and `bid`='$pre_building_id'");
            }
			if (empty($curr_building_level) || $curr_building_level < $condition['pre_level'])
			{
				throw new Exception( $GLOBALS['startDraftQueue']['no_pre_building']);
			}                                                                                     
		}
		else if ($condition['pre_type'] == 1) //technic
		{                           
			$pre_technic_id = $condition['pre_id'];
			$curr_technic_level = sql_fetch_one_cell("select max(`level`) from sys_technic where uid='$uid' and `tid`='$pre_technic_id'");
			if (empty($curr_technic_level) || $curr_technic_level < $condition['pre_level'])
			{                              
				throw new Exception($GLOBALS['startDraftQueue']['no_pre_technic']);
			}                                                                            
		}
	}
	
	$is_special = sql_fetch_one_cell("select is_special from sys_city where cid='$cid' limit 1");
	if($sid>=45&&$sid<=50){
		if(intval($is_special)==2)   //王者之城单独处理,是否开启了招募特殊兵种的功能
		{
			$spacialsid= sql_fetch_one_cell("select sid from cfg_soldier_special_city where cid='$cid' and type='4' limit 1");
		}else {
			$spacialsid=getSpacialSoldierId($cid);
		}
		
		
		if(empty($spacialsid)){
			throw new Exception($GLOBALS['conscript']['0']);
		}else if($spacialsid!=$sid){
			throw new Exception("hello ,baby,i'm yingke");
		}
	}
	//本兵营已经有level+1个队列正在造的话，就不能开始
	$currentDraftingCount = sql_fetch_one_cell("select count(*) from sys_city_draftqueue where `cid`='$cid' and `xy`='$xy'");
	if ($currentDraftingCount >= sql_fetch_one_cell("select `level` from sys_building where `cid`='$cid' and `xy`='$xy'") + 1)
	{
		throw new Exception($GLOBALS['startDraftQueue']['reach_queue_limit']);
	}
	                                             
                                                            

    $real_time_need = $soldier_info['time_need'];  //真实需要的时间 
    
    
    $real_time_need *= getSoldierSpeedRate($cid,$sid,$speedfactor);
    
    
    $real_time_need /= GAME_SPEED_RATE;
    $real_time_need=floor($real_time_need);
    if ($real_time_need < 1) $real_time_need = 1;
 
 	$needtime=$real_time_need*$soldierCount;
 	$endtime=sql_fetch_one_cell("select endtime from mem_user_buffer where uid='$uid' and buftype=10321 and endtime>unix_timestamp()");
    if(!empty($endtime)){
        	$needtime=floor($needtime*0.08);
    }
 
    //扣钱
    addCityResources($cid,-$soldier_info['wood_need']*$soldierCount * $resourcefactor,-$soldier_info['rock_need']*$soldierCount * $resourcefactor,
        -$soldier_info['iron_need']*$soldierCount * $resourcefactor,-$soldier_info['food_need']*$soldierCount * $resourcefactor,
        -$soldier_info['gold_need']*$soldierCount * $resourcefactor);
    //扣人
    addCityPeople($cid,-$soldierPeopleNeed * $soldierCount);
    

	//开建
	if (sql_check("select * from sys_city_draftqueue where `cid`='$cid' and `xy`='$xy'"))	//如果该兵营已经有兵在造了
	{
		sql_query("insert into sys_city_draftqueue (`cid`,`xy`,`sid`,`count`,`queuetime`,`state`,`draft_interval`,`state_starttime`,`needtime`) values 
		('$cid','$xy','$sid','$soldierCount',unix_timestamp(),0,'$real_time_need',0,'$needtime')");
		if (!sql_check("select * from sys_city_draftqueue where `cid`='$cid' and `xy`='$xy' and `state`=1"))	//万一不知道怎么回事没有队在训练，拉个最先的来
		{
			$id = sql_fetch_one_cell("select id from sys_city_draftqueue where `cid`='$cid' and `xy`='$xy' order by queuetime limit 1");																 
			if (!empty($id))
			{                                                                       
				sql_query("update sys_city_draftqueue set state=1,state_starttime=unix_timestamp() where `id`='$id'"); 
				sql_query("insert into mem_city_draft (select id,cid,xy,sid,count,state_starttime+needtime from sys_city_draftqueue where `id`='$id')");
			} 
		}
	}
	else	//如果还没有兵在造的话，则直接开始造
	{
		sql_query("insert into sys_city_draftqueue (`cid`,`xy`,`sid`,`count`,`queuetime`,`state`,`draft_interval`,`state_starttime`,`needtime`) values 
		('$cid','$xy','$sid','$soldierCount',unix_timestamp(),1,'$real_time_need',unix_timestamp(),'$needtime')");
		$lastid = sql_fetch_one_cell("select LAST_INSERT_ID()");
		sql_query("insert into mem_city_draft (select id,cid,xy,sid,count,state_starttime+needtime from sys_city_draftqueue where `id`='$lastid')");
	}
	if($double == 1) {
		logUserAction($uid,3);
	}
	return getArmyInfo($uid,$cid,$xy);
}

//停止正在排队的兵种
function stopDraftQueue($uid,$cid,$param)
{
	$inner = intval(array_shift($param));
	$x = intval(array_shift($param));
	$y = intval(array_shift($param));
	$xy = encodeBuildingPosition($inner,$x,$y);
	$qid = intval(array_shift($param));
	$uid=intval($uid);
	$cid=intval($cid);
	
	checkCityExist($cid,$uid);
	if (!sql_check("select * from sys_building where `cid`='$cid' and `xy`='$xy' and `bid`=".ID_BUILDING_ARMY)) throw new Exception( $GLOBALS['stopDraftQueue']['no_barracks_info']);
	
	$queue = sql_fetch_one("select * from sys_city_draftqueue where `id`='$qid'");                             
	if (!empty($queue))
	{
		$soldier_info = sql_fetch_one("select * from cfg_soldier where sid='$queue[sid]' and fromcity=1");
		if (empty($soldier_info)) throw new Exception( $GLOBALS['stopDraftQueue']['no_army_branch_info']);
        
        $soldierPeopleNeed = sql_fetch_one_cell("select people_need from cfg_soldier where sid=$queue[sid]");
                                                         
		//删队
		sql_query("delete from sys_city_draftqueue where id='$qid'");
		
		//还人
		addCityPeople($cid,$queue['count'] * $soldierPeopleNeed);
		//还钱
		addCityResources($cid,	$soldier_info['wood_need'] * $queue['count'] * 0.66,
								$soldier_info['rock_need'] * $queue['count'] * 0.66,
								$soldier_info['iron_need'] * $queue['count'] * 0.66,
								$soldier_info['food_need'] * $queue['count'] * 0.66,
								$soldier_info['gold_need'] * $queue['count'] * 0.66);
		if ($queue['state'] == 1)	//正在建设中
		{
			sql_query("delete from mem_city_draft where id='$qid'");
			//看看有没有其它队在排，有的话开始
			$id = sql_fetch_one_cell("select id from sys_city_draftqueue where `cid`='$queue[cid]' and `xy`='$queue[xy]' order by queuetime limit 1");																 
			if (!empty($id))
			{                                                                       
				sql_query("update sys_city_draftqueue set state=1,state_starttime=unix_timestamp() where `id`='$id'"); 
				sql_query("insert into mem_city_draft (select id,cid,xy,sid,count,state_starttime+needtime from sys_city_draftqueue where `id`='$id')");
			} 
		}
	}
	return getArmyInfo($uid,$cid,$xy);
}
  
function dissolveSoldier($uid,$cid,$param)
{
	$inner = intval(array_shift($param));
	$x = intval(array_shift($param));
	$y = intval(array_shift($param));
	$xy = encodeBuildingPosition($inner,$x,$y);
	$sid = intval(array_shift($param));
	$soldierCount = intval(array_shift($param));
	$uid=intval($uid);
	$cid=intval($cid);
	
	if ($soldierCount <= 0) throw new Exception($GLOBALS['dissolveSoldier']['cant_dismiss_zero']);
	checkCityExist($cid,$uid);
	if (!sql_check("select * from sys_building where `cid`='$cid' and `xy`='$xy' and `bid`=".ID_BUILDING_ARMY)) throw new Exception($GLOBALS['dissolveSoldier']['no_barracks_info']);
	$soldier_info = sql_fetch_one("select * from cfg_soldier where sid='$sid' and fromcity=1");
	if (empty($soldier_info)) throw new Exception( $GLOBALS['dissolveSoldier']['no_army_branch_info'] );
			  
	$cur_count = sql_fetch_one_cell("select `count` from sys_city_soldier where `cid`='$cid' and `sid`='$sid'");
	if ($cur_count < $soldierCount) throw new Exception($GLOBALS['dissolveSoldier']['cant_dismiss_exceed']);
	//还人
    
    $soldierPeopleNeed = sql_fetch_one_cell("select people_need from cfg_soldier where sid='$sid'");
	addCityPeople($cid,$soldierCount * $soldierPeopleNeed);                            
	//解散军队
	addCitySoldier($cid,$sid,-$soldierCount);
	//退钱
	addCityResources($cid,$soldierCount * $soldier_info['wood_need'] * 0.33
        ,$soldierCount * $soldier_info['rock_need'] * 0.33
        ,$soldierCount * $soldier_info['iron_need'] * 0.33
        ,$soldierCount * $soldier_info['food_need'] * 0.33
        ,$soldierCount * $soldier_info['gold_need'] * 0.33 );
	
   	logUserAction($uid,29);
   	$ret = array();
   	$ret[] = getArmyInfo($uid,$cid,$xy);
   	$ret[] = $GLOBALS['dissolveSoldier']['dissolve_succ'];
	return $ret;
}

function useHanXinShangBu($uid,$cid,$param)
{//韩信三篇上部,缩短当前队列2小时
	static $gid = 125;//韩信三篇上部	
	static $REDUCE_TIME = 72000;//缩短20小时
	$inner = intval(array_shift($param));
	$x = intval(array_shift($param));
	$y = intval(array_shift($param));
	$xy = encodeBuildingPosition($inner,$x,$y);
	$qid = intval(array_shift($param));
	$uid = intval($uid);
	$cid = intval($cid);
	
	checkCityExist($cid,$uid);
	if (!sql_check("select * from sys_building where `cid`='$cid' and `bid`=".ID_BUILDING_ARMY)) throw new Exception($GLOBALS['dissolveSoldier']['no_barracks_info']);
	
	if(!checkGoods($uid,$gid)) {
		throw new Exception("not_enough_goods125");
	}
	
	$queue = sql_fetch_one("select * from sys_city_draftqueue where `id`='$qid'");
	if(empty($queue))
	{
		throw new Exception($GLOBALS['dissolveSoldier']['no_army_branch_info']);
	}
    $tec_level= sql_fetch_one_cell("select level from sys_city_technic where cid = '$cid' and tid=8 ");//根据练兵科技增加韩信的时间
	if(empty($tec_level)) $tec_level=0;
	$tec_time = 3600*$tec_level;
	$oldNeedTime = (int)$queue['needtime'];
	$newNeedTime = ($oldNeedTime - $REDUCE_TIME - $tec_time);
	if($newNeedTime<0) $newNeedTime=0;
	if ($queue['state'] == 1)	
	{//正在建设中
		sql_query("update sys_city_draftqueue set needtime=$newNeedTime where id='$queue[id]'");
		$state_endtime=sql_fetch_one_cell("select state_starttime+needtime from sys_city_draftqueue where id = ".$queue["id"]);
		sql_query("update mem_city_draft set state_endtime=$state_endtime where id='$queue[id]'");
	}
	else
	{
		sql_query("update sys_city_draftqueue set needtime=$newNeedTime where id='$queue[id]'");
	}
	reduceGoods($uid,$gid,1);
	return getArmyInfo($uid,$cid,$xy);
}

function useHanXinXiaBu($uid,$cid,$param)
{//韩信三篇下部
	static $MIN_REDUCE_TIME = 36000;//最小缩短10小时
	$inner = intval(array_shift($param));
	$x = intval(array_shift($param));
	$y = intval(array_shift($param));
	$xy = encodeBuildingPosition($inner,$x,$y);
	$qid = intval(array_shift($param));
	$uid = intval($uid);
	$cid = intval($cid);
	
	checkCityExist($cid,$uid);
	if (!sql_check("select * from sys_building where `cid`='$cid' and `bid`=".ID_BUILDING_ARMY)) throw new Exception($GLOBALS['dissolveSoldier']['no_barracks_info']);
	
	if(!checkGoods($uid,63)) {
		throw new Exception("ask_for_copperShop");
	}
	
	$queue = sql_fetch_one("select * from sys_city_draftqueue where `id`='$qid'");
	if(empty($queue))
	{
		throw new Exception($GLOBALS['dissolveSoldier']['no_army_branch_info']);
	}
	else if ($queue['accmark'] != 0)
	{
		throw new Exception($GLOBALS['accSoldier']['only_once']);
	}
	$theXy=$queue['xy'];
	$queues= sql_fetch_rows("select min(id) as id from sys_city_draftqueue where accmark=0 and cid = $cid and xy !=$theXy  group by xy");
	$queues[]=$queue;
	foreach($queues as $queue){
		$queue=sql_fetch_one("select * from sys_city_draftqueue where id = ".$queue["id"]);
		$oldNeedTime = (int)$queue['needtime'];
		if ($queue['state'] == 1)	//正在建设中
		{
			$state_starttime = (int)$queue['state_starttime'];
			$curTime = $GLOBALS['now'];
			$reduceTime = intval(ceil($oldNeedTime + $state_starttime - $curTime) * 0.4);//缩短40%
			if ($reduceTime < $MIN_REDUCE_TIME) {
				$reduceTime = $MIN_REDUCE_TIME;	
			}
			$newNeedTime = ($curTime - $state_starttime) + ($oldNeedTime + $state_starttime - $curTime - $reduceTime);
			sql_query("update sys_city_draftqueue set needtime=$newNeedTime, accmark=1 where id='$queue[id]'");
			$state_endtime=sql_fetch_one_cell("select state_starttime+needtime from sys_city_draftqueue where id = ".$queue["id"]);
			sql_query("update mem_city_draft set state_endtime=$state_endtime where id='$queue[id]'");
		}
		else
		{
			$reduceTime = intval(ceil($oldNeedTime * 0.3));
			if ($reduceTime < $MIN_REDUCE_TIME) {
				$reduceTime = $MIN_REDUCE_TIME;
			}
			$newNeedTime = $oldNeedTime - $reduceTime;
			sql_query("update sys_city_draftqueue set needtime=$newNeedTime, accmark=1 where id='$queue[id]'");
		}
	}
	reduceGoods($uid,63,1);
	completeTaskWithTaskid($uid, 325);
	return getArmyInfo($uid,$cid,$xy);
	
}
//用于双倍资源加速造兵时，得到加速后的士兵信息
function changeSoldierInfo($uid, $param)
{
	$resourcefactor = 1;
	$speedfactor = 0;
	
	$double = array_shift($param);
	if($double == 1) {
		$resourcefactor = 2;
		$speedfactor = 1.5;
	}
	
	$cid = intval(array_shift($param));
	checkCityExist($cid,$uid);
	
	$sid = intval(array_shift($param));
	$soldier = sql_fetch_one("select * from cfg_soldier s left join sys_city_soldier c on c.cid='$cid' and c.sid=s.sid where s.sid='$sid' and s.fromcity=1");
	if (empty($soldier)) throw new Exception( $GLOBALS['startDraftQueue']['no_army_branch_info']);
	
	$ret = array();
    $speedRate = getSoldierSpeedRate($cid,$sid,$speedfactor);
	$state = new ArmySoldierState();
	$state->sid = $sid;
	$state->sname = $soldier['name'];
	$state->count = empty($soldier['count'])?0:$soldier['count'];
	$state->description = $soldier['description'];
	$state->hp = $soldier['hp'];
	$state->ap = $soldier['ap'];
	$state->dp = $soldier['dp'];
	$state->range = $soldier['range'];
	$state->speed = $soldier['speed'];
	$state->carry = $soldier['carry'];
	$state->food_use = $soldier['food_use'];
	$state->woodNeed = $soldier['wood_need'] * $resourcefactor;
	$state->rockNeed = $soldier['rock_need'] * $resourcefactor;
	$state->ironNeed = $soldier['iron_need'] * $resourcefactor;
	$state->foodNeed = $soldier['food_need'] * $resourcefactor;
	$state->goldNeed = $soldier['gold_need'] * $resourcefactor;
    $state->peopleNeed = $soldier['people_need'];
	$state->draft_time = max(1,$soldier['time_need'] * $speedRate);
	$state->can_draft = true;
	
	$ret[] = $state;
	return $ret;
}

function getConvertInfo($cid) {
	$ret = array();
	$convert= sql_fetch_one("select `value`,`convert` from sys_soldier_convert where cid=$cid");
	if (empty($convert)) {
		$convert = array(0,0);
	}
	$ret[] = $convert;
	$ret[] = sql_fetch_rows("select * from cfg_soldier_convert");
	return $ret;
}

function soldierConvert($uid,$cid,$param) {
	$inner = array_shift($param);
	$x = array_shift($param);
	$y = array_shift($param);
	$fromSid = intval(array_shift($param));
	$fromCount = intval(array_shift($param));
	$toSid = intval(array_shift($param));
	$toCount = intval(array_shift($param));
	$fromCount = intval($fromCount);
	$toCount = intval($toCount);
	if(($fromSid>=45&&$fromSid<=50)||($toSid>=45&&$toSid<=50)){
		throw new Exception($GLOBALS['conert']['not_support_sid']);
	}
	if ($fromCount<=0 || $toCount<=0 || $toCount >=200000) {
		throw new Exception($GLOBALS['stopDraftQueue']['choose_soldier_not_enough']);
	}
	
	
	$xy = encodeBuildingPosition($inner,$x,$y);
	
	$value = sql_fetch_one_cell("select $toCount*people_need from cfg_soldier where sid=$toSid");
	
	$convertMap = sql_fetch_rows("select * from cfg_soldier_convert");
	$convertRatio = $convertMap[$fromSid-1][$toSid-1];
	list($fromNum,$toNum) = explode(':',$convertRatio);
	$tmpCount = intval($fromNum*$toCount/$toNum);
	if ($tmpCount < $fromCount) $fromCount=$tmpCount;
	if ($tmpCount > $fromCount+1) {//最大单个转化比例为200
		throw new Exception($GLOBALS['sendCommand']['command_exception']);
	}
	
	$soldierCount = sql_fetch_one_cell("select count from sys_city_soldier where cid=$cid and sid=$fromSid"); 
	if ($fromCount > $soldierCount) {
		throw new Exception($GLOBALS['stopDraftQueue']['choose_soldier_not_enough']);
	}
	$convertInfo = sql_fetch_one("select * from sys_soldier_convert where cid=$cid limit 1");
	if (empty($convertInfo)) {
		throw new Exception($GLOBALS['useMojiaGoods']['invalid_param']);
	}
	if ($convertInfo['convert'] >= $convertInfo['value']) {
		throw new Exception($GLOBALS['hero']['xidian_unvalid']);
	}
	if ($convertInfo['value']-$convertInfo['convert'] < $value) {
		throw new Exception($GLOBALS['hero']['xidian_unvalid']);
	}
	
	//修改转换兵力
	sql_query("update sys_soldier_convert set `convert`=`convert`+$value where cid=$cid");
	//减少转化的兵力
	sql_query("update sys_city_soldier set count=greatest(0,count-$fromCount) where cid=$cid and sid=$fromSid");
	//增加被转换的兵力
	sql_query("insert into sys_city_soldier(cid,sid,count) values('$cid','$toSid','$toCount') on duplicate key update count=count+$toCount");
	//记录日志
	sql_query("insert into log_soldier_convert(uid,cid,fromSid,fromCount,toSid,toCount,time)values($uid,$cid,$fromSid,$fromCount,$toSid,$toCount,unix_timestamp())");
	return getArmyInfo($uid,$cid,$xy);
}

function getSoldierRate($uid,$param) {
	$cid=intval(array_shift($param));
	$sid=intval(array_shift($param));
	$rates = sql_fetch_rows("select * from cfg_soldier_rate");
	$camps = sql_fetch_rows("select level from sys_building where cid=$cid and bid=9");
	$total=0;
	foreach ($camps as $camp) {
		$total += $rates[$camp['level']-1][$sid-1];
	}
	if ($total == 0) $total=1;
	$ret=array();
	$ret[]=$total;
	$ret[]=sizeof($camps);;
	return $ret;
}
//test();
function test() {
	//$param = array(1,1000,8,100);
	//soldierConvert(1001,230469,$param);
	//getSoldierRate(230469,8);
}

?>