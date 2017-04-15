<?php
require_once("./interface.php");
require_once("./utils.php");
require_once("./UnionFunc.php");

function doGetWorldInfo($uid,$cid)
{
	return	$heroes = sql_fetch_rows("select * from sys_city_hero where `cid`='$cid'");
}

function getBlockData($uid,$param)
{
	//删一下过期的标记
	clearMark($uid);
	$blockarray = array_shift($param);
	$ret = array();
	$str = '';
	foreach($blockarray as $block)
	{
		$blockstart = $block*100;
		$blockend = $blockstart + 100;

		$data = array();
		$data[] = $blockstart;
		$data[] = sql_fetch_one_cell("select group_concat((wid - $blockstart),':',type,':',ownercid,':',state,':',level,':',province,':',jun) from mem_world where wid >= $blockstart and wid < $blockend");
		
		$str .= "select group_concat((wid - $blockstart),':',type,':',ownercid,':',state,':',level,':',province,':',jun) from mem_world where wid >= $blockstart and wid < $blockend\n";
		
		$ret[] = $data;
	}
	$unionid = sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
	if(empty($unionid)) $unionid = 0;
	$marks = sql_fetch_rows("select * from sys_union_mark where unionid=$unionid");
	if(empty($marks)) $marks = array();

	$result = array();
	$result[] = $ret;
	$result[] = $marks;
	file_put_contents(dirname(__FILE__).'/getBlockData_sql.log',$str);

file_put_contents(dirname(__FILE__).'/getBlockData.log',var_export($result,true)."\n");

	
	return $result;
}
function getWorldCityInfo($uid,$param)
{
	$now=sql_fetch_one_cell("select unix_timestamp()");
	$cityCount = intval(array_shift($param));
	$citylist = array();
	if ($cityCount > 0)
	{
		$s = "";
		for($i = 0; $i < $cityCount; $i++)
		{
			$cities .= $s.array_shift($param);
			$s = ",";
		}
		$cities =  addslashes($cities);
		$citylist =  sql_fetch_rows("select c.cid,c.type as citytype,c.is_special as is_special,c.province as provinceId,c.name as cityname,u.uid,u.name as username,u.passport,u.flagchar,u.union_id,n.name as unionname,u.prestige,u.state as userstate,u.face as userface,u.sex as usersex from sys_city c,sys_user u left join sys_union n on u.union_id=n.id where c.cid in ($cities) and c.uid=u.uid");
		$user = sql_fetch_one("select * from sys_user where uid='$uid'");
		foreach($citylist as $k => $city)
		{
			if ($city['uid'] == $user['uid'])
			{
				$city['flag'] = 0;
			}
			else if (($city['union_id'] == $user['union_id'])&&($city['union_id'] > 0))
			{
				$city['flag'] = 1;
			}
			else
			{
				$relation = sql_fetch_one("select * from sys_union_relation where unionid='$user[union_id]' and target='$city[union_id]'");
				if (!empty($relation))
				{
					if ($relation['type'] == 0)    //友好联盟
					{
						$city['flag'] = 2;
					}
					else if ($relation['type'] == 1)   //中立联盟
					{
						$city['flag'] = 3;
					}
					else if ($relation['type'] == 2)   //敌对联盟
					{						
						if ($now-$relation['time']>3600*8)
							$city['flag'] = 4;
						else 
							$city['flag'] = 8;
							
							if($city['flag']==8){//联盟宣战等待中
								if(sql_check("select * from mem_user_trickwar where endtime>unix_timestamp() and ((uid='$uid' and targetuid='$city[uid]') or (uid='$city[uid]' and targetuid='$uid'))"))
								{
									$city['flag'] = 6;  //个人宣战状态
								}
								else
								{								
									$inwar=sql_fetch_one("select * from mem_user_inwar where endtime>unix_timestamp() and ((uid='$uid' and targetuid='$city[uid]') or (uid='$city[uid]' and targetuid='$uid'))");
									if(!empty($inwar))
									{
										if($inwar['state']==0)
										{
											$city['flag']=8;
										}
										else $city['flag']=6;
									}
								}
							}
					}
					else{
						if (($city['uid'] < NPC_UID_END)||($city['citytype'] > 0&&$city['citytype'] !=5))  //NPC城和特殊城可以直接占领
						{
							$city['flag'] = 5;
						}
						else
						{
							if(sql_check("select * from mem_user_trickwar where endtime>unix_timestamp() and ((uid='$uid' and targetuid='$city[uid]') or (uid='$city[uid]' and targetuid='$uid'))"))
							{
								$city['flag'] = 6;  //个人宣战状态
							}
							else
							{								
								$inwar=sql_fetch_one("select * from mem_user_inwar where endtime>unix_timestamp() and ((uid='$uid' and targetuid='$city[uid]') or (uid='$city[uid]' and targetuid='$uid'))");
								if(!empty($inwar))
								{
									if($inwar['state']==0)
									{
										$city['flag']=8;
									}
									else $city['flag']=6;
								}
								else
								{
									$city['flag'] = 7; //没有小旗
								}
							}
						}
					}
				}
				else
				{
					if (($city['uid'] < NPC_UID_END)||($city['citytype'] > 0&&$city['citytype']!=5))  //NPC城和特殊城可以直接占领
					{
						$city['flag'] = 5;
					}
					else
					{
						if(sql_check("select * from mem_user_trickwar where endtime>unix_timestamp() and ((uid='$uid' and targetuid='$city[uid]') or (uid='$city[uid]' and targetuid='$uid'))"))
						{
							$city['flag'] = 6;  //个人宣战状态
						}
						else
						{							
							$inwar=sql_fetch_one("select * from mem_user_inwar where endtime>unix_timestamp() and ((uid='$uid' and targetuid='$city[uid]') or (uid='$city[uid]' and targetuid='$uid'))");
							if(!empty($inwar))
							{
								if($inwar['state']==0)
								{
									$city['flag']=8;
								}
								else $city['flag']=6;
							}
							else
							{
								$city['flag'] = 7; //没有小旗
							}
						}
					}
				}
			}
			if(is_null($city['flag']))
			{
				$city['flag']=7;
			}
			if(intval($city['cid'])==215265){
				
				$startTime = getAssingStartTime();
				$luoyangInfo = sql_fetch_one("select * from log_luoyang_belong where time>='$startTime'");
				if(!empty($luoyangInfo)){
					getLuoyangCityInfo($city,$luoyangInfo['uid']);
				}
				
			}
			
			$citylist[$k]['flag'] = $city['flag'];
		}
	}
	
	return $citylist;
}
function getLuoyangCityInfo(&$city,$belongUid)
{
	$userInfo = sql_fetch_one("select uid,name,flagchar,union_id,prestige from sys_user where uid='$belongUid'");
	$city['uid']=$userInfo['uid'];
	$city['username']=$userInfo['name'];
	$city['flagchar']=$userInfo['flagchar'];
	$city['union_id']=$userInfo['union_id'];
	$city['unionname']=sql_fetch_one_cell("select name from sys_union where id={$userInfo['union_id']}");
	$city['prestige']=$userInfo['prestige'];	
}
function getWorldFieldInfo($uid,$param)
{
	$wid = intval(array_shift($param));
	$ret = array();
	$ret[] = sql_fetch_one("select w.wid,w.type,w.ownercid,w.province,w.level,c.uid,c.name as cityname,u.name as username,u.prestige,u.union_id,n.name as unionname from mem_world w left join sys_city c on c.cid=w.ownercid left join sys_user u on u.uid=c.uid left join sys_union n on n.id=u.union_id  where w.wid=$wid");
	return $ret;
}
function startWar($uid,$param)
{
	$targetuid = intval(array_shift($param));
	$targetcid=intval(array_shift($param));
	if (sql_check("select * from mem_user_inwar where (uid='$uid' and targetuid='$targetuid') or (targetuid='$uid' and uid='$targetuid')"))
	{
		throw new Exception($GLOBALS['startWar']['war_is_declared']);
	}
	$user=sql_fetch_one("select name,state,lastcid from sys_user where uid='$uid'");
	$mystate = $user['state'];
    if ($mystate == 1) throw new Exception($GLOBALS['startWar']['new_protect']);
    $targetuser=sql_fetch_one("select name,state,lastcid,union_id from sys_user where uid='$targetuid'");
    $targetstate = $targetuser['state'];
    if ($targetstate == 1) throw new Exception($GLOBALS['startWar']['target_new_protect']);

	$now = sql_fetch_one_cell("select unix_timestamp()");

	sql_query("insert into mem_user_inwar (uid,targetuid,state,endtime) values ('$uid','$targetuid',0,unix_timestamp()+8*3600)");

	$username = $user['name'];
	$targetusername = $targetuser['name'];

	$caution = sprintf($GLOBALS['startWar']['succ_caution'],$username,MakeEndTime($now + 8 * 3600),MakeEndTime($now + 56 * 3600));
	sendReport($targetuid,"startwar",22,$user['lastcid'],$targetcid,$caution);
	$report = sprintf($GLOBALS['startWar']['succ_report'],$targetusername,MakeEndTime($now + 8 * 3600),MakeEndTime($now + 56 * 3600));
	sendReport($uid,"startwar",22,$user['lastcid'],$targetcid,$report);
	
	if($targetuser["union_id"]>0){
		$msg=sprintf($GLOBALS['start_war']['union_msg'],$targetuser["name"],$user["name"]);
		addUnionEvent($targetuser["union_id"],11,$msg);
	}
	
	if(defined("USER_FOR_51") && USER_FOR_51){
    	require_once("51utils.php");
    	add51StartWarEvent($targetuser["name"]);   
    }
	if (defined("PASSTYPE")){
		try{
		    require_once 'game/agents/AgentServiceFactory.php';
			AgentServiceFactory::getInstance($uid)->addStartWarEvent($targetuser["name"]);
		}catch(Exception $e){
			try{
				file_put_contents("./agents/log/interface-error.log",date("Y-m-d H:i:s",time())." || ".$e->getMessage()."\n",FILE_APPEND);
			}catch(Exception $err){
				
			}
		}
    }
	
	$cities = array();
	$cities = sql_fetch_one_cell("select group_concat(cid) from sys_city where uid='$targetuid'");
	$cities = explode(",",$cities);
	$param2=array();
	$cityCount=array();
	$cityCount[]=count($cities);
	$param2=array_merge($cityCount,$cities);
	return getWorldCityInfo($uid,$param2);
}
function createCityFromLand($uid,$param)
{
	$targetwid = intval(array_shift($param));
	$targetcid = wid2cid($targetwid);
	$worldInfo = sql_fetch_one("select * from mem_world where wid=".$targetwid);
	$lastcid = sql_fetch_one_cell("select lastcid from sys_user where uid='$uid'");
	if ($worldInfo['type'] != 1)
	{
		throw new Exception($GLOBALS['createCityFromLand']['only_flatlands_can_build']);
	}
	if ($worldInfo['ownercid'] != $lastcid)
	{
		throw new Exception($GLOBALS['createCityFromLand']['target_flatlands_notYours']);
	}
	if ($worldInfo['state'] != 0)
	{
		throw new Exception($GLOBALS['createCityFromLand']['target_flatlands_in_war']);
	}
	$troops = sql_fetch_rows("select * from sys_troops where uid='$uid' and targetcid='$targetcid' and state=4");
	//新建城池优化，不再需要有军队驻扎
//	if (empty($troops)) throw new Exception($GLOBALS['createCityFromLand']['no_army']);
	$chiefhid = 0;
	$gold = 0;
	$food = 0;
	$wood = 0;
	$rock = 0;
	$iron = 0;
	if(!empty($troops)) 
	{
		foreach($troops as $troop)
		{
			//合一下资源
			$res = explode(',',$troop['resource']);
			$gold += $res[0];
			$food += $res[1];
			$wood += $res[2];
			$rock += $res[3];
			$iron += $res[4];
		}
	}
	//新建城池优化，资源从原来的城池里面减
	$cityresourceinfo = sql_fetch_one("select * from mem_city_resource where cid=$lastcid");
	$goldincity = $cityresourceinfo['gold'];
	$foodincity = $cityresourceinfo['food'];
	$woodincity = $cityresourceinfo['wood'];
	$rockincity = $cityresourceinfo['rock'];
	$ironincity = $cityresourceinfo['iron'];
	if (($goldincity < 10000)||($foodincity < 10000)||($woodincity < 10000)||($rockincity < 10000)||($ironincity < 10000))
	{
		throw new Exception($GLOBALS['createCityFromLand']['no_enough_resource']);
	}
	$nobility = sql_fetch_one_cell("select nobility from sys_user where uid='$uid'");
	//推恩
	$nobility = getBufferNobility($uid,$nobility);

	$nobilityinfo = sql_fetch_one("select name,city_count from cfg_nobility where id='$nobility'");
	$max_city_count=$nobilityinfo['city_count'];
	$current_city_count = sql_fetch_one_cell("select count(*) from sys_city where uid='$uid'");
	if ($current_city_count >= $max_city_count)
	{
		$nextname=sql_fetch_one_cell("select name from cfg_nobility where id=".($nobility+1));
			
		$msg = sprintf($GLOBALS['createCityFromLand']['nobility_not_enough'],$nextname);
		throw new Exception($msg);
	}

	//新建城池优化，资源从原来的城池里面减，扣资源
	addCityResources($lastcid, -10000, -10000, -10000, -10000, -10000);
	
	//新建城池	
	$newcity_name = $GLOBALS['worldfunc']['newcity'];
	//清除伤兵，逃兵，俘虏
	sql_query("delete from mem_city_wounded where cid=$targetcid");
	sql_query("delete from mem_city_lamster where cid=$targetcid");
	sql_query("delete from mem_city_captive where cid=$targetcid");
	
	sql_query("replace into sys_city (`cid`,`uid`,`name`,`type`,`state`,`province`) values ('$targetcid','$uid','$newcity_name','0','0','$worldInfo[province]')");
	//自动建设1级官府
	sql_query("delete from sys_building where `cid`='$targetcid' and `xy`='120'");
	sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ('$targetcid','120','6','1')");
	sql_query("replace into mem_city_resource (`cid`,`people`,`food`,`wood`,`rock`,`iron`,`gold`,`lastupdate`) values ('$targetcid','0','$food','$wood','$rock','$iron','$gold',unix_timestamp())");

	sql_query("replace into sys_city_res_add (`cid`) values ('$targetcid')");
	//修改所在地的属性
	sql_query("update mem_world set ownercid='$targetcid',type='0' where wid=".cid2wid($targetcid));

	//重新计算宝物加成
	resetCityGoodsAdd($uid,$targetcid);

	//军队入住
	if(!empty($troops))
	{
		$hasSetCheif = false;
		foreach($troops as $troop)
		{
			if ($troop['hid'] > 0)
			{
				if (!$hasSetCheif)
				{
					//第一支军队的首领作为城守
					$hasSetCheif = true;
					sql_query("update sys_city_hero set cid='$targetcid',state=1 where hid='$troop[hid]'");
					sql_query("update sys_city set chiefhid='$troop[hid]' where cid='$targetcid'");
				}
				else
				{
					sql_query("update sys_city_hero set cid='$targetcid',state=0 where hid='$troop[hid]'");
				}
			}
			$soldiers = explode(',',$troop['soldiers']);
			if (count($soldiers) > 0)
			{
				for ($i = 0; $i < $soldiers[0]; $i++)
				{
					$sid = $soldiers[$i * 2 + 1];
					$cnt = $soldiers[$i * 2 + 2];
					sql_query("insert into sys_city_soldier (cid,sid,`count`) values ('$targetcid','$sid','$cnt') on duplicate key update `count`=`count`+'$cnt'");
					sql_query("insert into log_city_soldier (cid,sid,`uid`,`count`,`type`) values ('$targetcid','$sid',$uid,'$cnt',10) on duplicate key update `count`=`count`+'$cnt'");
				}
			}
	
			sql_query("delete from sys_troops where id='$troop[id]'");
			sql_query("delete from sys_troop_tactics where troopid='$troop[id]'");
			updateCityResourceAdd($troop['cid']);
		}
	}
	updateCityResourceAdd($targetcid);


	updateCityHeroChange($uid,$lastcid);
	updateCityHeroChange($uid,$targetcid);   
	//完成建立新城任务
	completeTask($uid,169);
	addCityResources($targetcid, 5000, 5000, 5000, 5000, 5000);		
	
	$username=sql_fetch_one_cell("select name from sys_user where uid='$uid'");
	if(defined("USER_FOR_51") && USER_FOR_51){
		require_once("51utils.php");	
    	add51CreateCityEvent($username);   
	}
	if (defined("PASSTYPE")){
		try{
			require_once 'game/agents/AgentServiceFactory.php';
			AgentServiceFactory::getInstance($uid)->addCreateCityEvent($username);
		}catch(Exception $e){
			try{
				file_put_contents("./agents/log/interface-error.log",date("Y-m-d H:i:s",time())." || ".$e->getMessage()."\n",FILE_APPEND);
			}catch(Exception $err){
				
			}
		}
    }
    logUserAction($uid,1);
	//创建君主将
	if (!sql_check("select 1 from sys_city_hero where uid='$uid' and herotype=1000")) {
		sql_query("insert into sys_city_hero(uid,name,sex,face,cid,state,level,command_base,affairs_base,bravery_base,wisdom_base,loyalty,herotype) select uid,name,sex,face,'$cid',0,1,50,1,1,1,100,1000 from sys_user where uid=$uid");
		$hid = sql_fetch_one_cell("select last_insert_id()");
		sql_query("insert into mem_hero_blood(`hid`,`force`,`force_max`,`energy`,`energy_max`) values('$hid','150','150','150','150')");
	}
    //马来定制
	$yysType = sql_fetch_one_cell("select value from mem_state where state=197");
	if ($yysType == 60) {
		checkIsInSili($uid,$targetcid);
	}
	return array();
}

//马来定制：如果玩家在司隶筑城，告知服务器玩家。
function checkIsInSili($uid,$cid) {
	$wid = cid2wid($cid);
	$province = sql_fetch_one_cell("select province from mem_world where wid=$wid");
	if ($province == 1) {
		$userName = sql_fetch_one_cell("select name from sys_user where uid=$uid");
		$cityName = sql_fetch_one_cell("select name from sys_city where cid=$cid");
		$X = floor($cid / 1000);
		$Y = ($cid % 1000);
		$msg=sprintf($GLOBALS['changeCityPosition']['build_city_in_sili'],$userName,$cityName,$Y,$X);
		sendSysInform(0,1,0,300,1800,1,49151,$msg);
	}
}
function addFavourites($uid,$param)//增加野地或是城池目标坐标到收藏
{
	$targetcid = intval(array_shift($param));

	if (sql_check("select * from sys_favourites where uid='$uid' and cid='$targetcid'"))
	{
		throw new Exception($GLOBALS['addFavourites']['already_in_fav']);
	}
	$cnt = sql_fetch_one_cell("select count(*) from sys_favourites where uid='$uid'");
	if ($cnt >= 10)
	{
		throw new Exception($GLOBALS['addFavourites']['fav_is_full']);
	}
	$wid = cid2wid($targetcid);
	$worldInfo = sql_fetch_one("select * from mem_world where wid='$wid'");
	if ($worldInfo['type'] == 0)
	{
		$name = sql_fetch_one_cell("select name from sys_city where cid='$targetcid'");
	}
	else
	{
		$name = sql_fetch_one_cell("select name from cfg_world_type where type='$worldInfo[type]'");
	}
	sql_query("insert into sys_favourites (uid,cid,name,comments) values ('$uid','$targetcid','$name','')");
	throw new Exception($GLOBALS['addFavourites']['succ']);
}
function getFavouritesList($uid,$param)//得到已经有的收藏
{
	$ret = array();
	$ret[] = sql_fetch_rows("select cid,name from sys_city where uid='$uid'");
	$ret[] = sql_fetch_rows("select id,cid,name,comments from sys_favourites where uid='$uid'");
	return $ret;
}
function deleteFavourites($uid,$param)//删除收藏
{
	$id = intval(array_shift($param));
	$fav = sql_fetch_one("select * from sys_favourites where id='$id'");
	if (empty($fav) || ($fav['uid'] != $uid))
	{
		throw new Exception($GLOBALS['deleteFavourites']['error_in_del_fav']);
	}
	sql_query("delete from sys_favourites where id='$id'");
	return getFavouritesList($uid,$param);
}
function setFavouritesComments($uid,$param)//设置目标备注成功
{
	$id = intval(array_shift($param));
	$comments = array_shift($param);
	$comments = addslashes($comments);
	$fav = sql_fetch_one("select * from sys_favourites where id='$id'");
	if (empty($fav) || ($fav['uid'] != $uid))
	{
		throw new Exception($GLOBALS['setFavouritesComments']['already_exist']);
	}
	sql_query("update sys_favourites set comments='$comments' where id='$id'");
	throw new Exception($GLOBALS['setFavouritesComments']['succ']);
	//    return getFavouritesList($uid,$param);

}

function getMaxCountByOfficePos($cityType,$officepos){	
	$maxCount=0;
	if($cityType==1){					
		if ($officepos==6) $maxCount=1;
		else if ($officepos==7) $maxCount=2;
		else if ($officepos>=8) $maxCount=3;	
	}
	else if($cityType==2){				
		if ($officepos==9) $maxCount=4;
		else if ($officepos==10) $maxCount=5;
		else if ($officepos>=11) $maxCount=6;
	}else if($cityType==3) {
		if ($officepos>=12) $maxCount=8;
	}else if($cityType==4) {
		if ($officepos>=13) 
			$maxCount=10;
	}
	return $maxCount;
}
//点击政令按钮时候，取得今天已经下达的次数
function getGovernInfo($uid,$param){
	$cid=intval(array_shift($param));
	$count_and_time=sql_fetch_one("select govern_count,last_govern_time from mem_city_schedule where cid='$cid'");
	
	$officepos =sql_fetch_one_cell("select officepos from sys_user where uid='$uid'");
		
	$officename =sql_fetch_one_cell("select name from cfg_office_pos where id='$officepos'");
	$cityType = sql_fetch_one_cell("select type from sys_city where cid='$cid'");
	//每天最大下达次数
	$maxCount=getMaxCountByOfficePos($cityType,$officepos);	
	
	$ret=array();
	$ret[]=$officename;
	$ret[]=$maxCount;
	
	if(empty($count_and_time)){
		//从来没有下达过政令
		$ret[]=0;		
		return $ret;
	}
	$count=$count_and_time['govern_count'];
	$now = sql_fetch_one_cell("select unix_timestamp()");

	if (floor(($now + 8 * 3600) / 86400 ) > floor(($count_and_time['last_govern_time'] + 8 * 3600) / 86400)){
		$ret[]=0;
		return $ret;
	}
	$ret[]=$count;	
	return $ret;

}

//下达政令
function governOthers($uid,$param){

	//政令类型 0 收税 1 抽丁 2 征粮 3 收编  4裁军
	$type=intval(array_shift($param));
	//目标城池id
	$tcid=intval(array_shift($param));
	//目标用户ID
	$tuid=intval(array_shift($param));
	//当前城池id
	$cid=intval(array_shift($param));
	

	$cityname=array_shift($param);
	$cityname = addslashes($cityname);
	

	if (!sql_check("select 1 from sys_city where uid=$uid and cid=$cid")) {
		throw new Exception($GLOBALS['sendCommand']['command_exception']);
	}
	if(!sql_check("select 1 from sys_city where uid='$tuid' and cid='$tcid'")){
		throw new Exception($GLOBALS['sendCommand']['command_exception']);
	}
	//普通城不能下达政令
	$cityType = sql_fetch_one_cell("select type from sys_city where cid='$cid'");
	if ($cityType == 5) $cityType = 0;
	if($cityType==0) throw new Exception($GLOBALS['governOthers']['city_cannot_govern']);

	//官府没有达到10级，不能下达
	$governLevel = sql_fetch_one_cell("select level from sys_building where cid='$cid' and bid=6" );
	if($governLevel<10) throw new Exception($GLOBALS['governOthers']['not_enouth_government_level']);

	
	//目标城池级别大于自己的级别，也不能下达
	$targetcitytype = sql_fetch_one_cell("select type from sys_city where cid='$tcid'");
	if ($targetcitytype == 5) $targetcitytype = 0; 
	if($cityType<=$targetcitytype) throw new Exception($GLOBALS['governOthers']['not_enough_level']);
	
	//自己盟友的城池不能政令
	$userUnionId = sql_fetch_one_cell("select union_id from sys_user where uid='$uid'");
	if(!empty($userUnionId))
	{
		if(sql_check("select 1 from sys_user where uid='$tuid' and union_id='$userUnionId'")){
			throw new Exception($GLOBALS['luoyang']['target_can_not_union']);
		}
	}
	
	
	//查看封禁、休假状态
	if($targetcitytype==0)
	{
		$myuserstate=sql_fetch_one("select forbiend,vacend,unix_timestamp() as nowtime from sys_user_state where uid='$tuid' and (forbiend>unix_timestamp() or vacend>unix_timestamp())");
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
	
	$cityTypeNameField ="big_city_"+$cityType; 
	$cityTypeName = sql_fetch_one_cell("select value from cfg_name where  name='$cityTypeNameField'");
	$officepos =sql_fetch_one_cell("select officepos from sys_user where uid='$uid'");
	$officename =sql_fetch_one_cell("select name from cfg_office_pos where id='$officepos'");
	
	$wid = cid2wid($cid);
	$twid = cid2wid($tcid);
	
	$world = sql_fetch_one("select province,jun from mem_world where wid = $wid" );
	$tworld = sql_fetch_one("select province,jun from mem_world where wid = $twid" );
	$province = $world["province"];	
	$jun = $world["jun"];	
	$tprovince = $tworld["province"];
	$tjun = $tworld["jun"];
	
	$x=$cid%1000;
	$y=floor($cid/1000);
	

	
	
	//每天最大下达次数
	$maxCount=getMaxCountByOfficePos($cityType,$officepos);	
	if($cityType==1){				
		$pos=floor($cid/10000)*100+floor((floor($cid%1000)/10));
		$tpos=floor($tcid/10000)*100+floor((floor($tcid%1000)/10));			
		//检查是不是在同一个县
		if ($pos != $tpos) 	throw new Exception($GLOBALS['governOthers']['not_enough_level']);		
	}
	else if($cityType==2){
		//检查是不是在同一个郡 
		if ($jun != $tjun || $province != $tprovince) 	throw new Exception($GLOBALS['governOthers']['not_enough_level']);					
	}

	else if($cityType==3) {
		//检查是不是在同一个州
		if ($province != $tprovince) 	throw new Exception($GLOBALS['governOthers']['not_enough_level']);
	}

	$now = sql_fetch_one_cell("select unix_timestamp()");
	
	$lastBeGovernTime =  sql_fetch_one_cell("select last_be_govern_time from mem_city_schedule where cid='$tcid'");
	if (!empty($lastBeGovernTime)){
		//一天以内被下达过政令 则不能下达	 	
		if (!(floor(($now + 8 * 3600) / 86400 ) > floor(($lastBeGovernTime + 8 * 3600) / 86400)))
		throw new Exception($GLOBALS['governOthers']['target_has_been_govern']);
	}

	$timeandcount =  sql_fetch_one("select govern_count,last_govern_time from mem_city_schedule where cid='$cid'");

	$todayFirst=false;
	$todayCount=0;
	
	
	if (empty($timeandcount)){
		$todayFirst = true;
	}
	else{
		$lastTime=$timeandcount["last_govern_time"];
		if (floor(($now + 8 * 3600) / 86400 ) > floor(($lastTime + 8 * 3600) / 86400)){
			$todayFirst = true;
		}else{
			$todayCount =  $timeandcount['govern_count'];
			//下达次数太多
			if($todayCount>=$maxCount) {
				throw new Exception(sprintf($GLOBALS['governOthers']['too_many_time'],$officename,$cityTypeName,$maxCount,$maxCount));
			}

		}
	}

	//今天又增加一次
	$todayCount++;

	$msg="";

	if($type==0){//征税
	
		$totalCount = sql_fetch_one_cell("select gold from mem_city_resource where cid='$tcid'");
		$addCount=	floor($totalCount /10);	
	
		//收税
		//减别人的
		addCityResources($tcid,0,0,0,0,(0-$addCount));
		$report=sprintf($GLOBALS['governOthers']['gold_report'],$cityname,$x,$y,$addCount);
		sendReport($tuid,3,26,$cid,$tcid,$report);
		//加自己的
		addCityResources($cid,0,0,0,0,($addCount));

		$msg=sprintf($GLOBALS['governOthers']['gold_suc'],$addCount);
	}else if($type==1) {//抽丁
		$totalCount = sql_fetch_one_cell("select people from mem_city_resource where cid='$tcid'");
		$addCount=	floor($totalCount/5);
		
		addCityPeople($tcid,(0-$addCount));
	
		addCityPeople($cid,$addCount);
		$report=sprintf($GLOBALS['governOthers']['people_report'],$cityname,$x,$y,$addCount);
		sendReport($tuid,3,28,$cid,$tcid,$report);
		$msg=sprintf($GLOBALS['governOthers']['people_suc'],$addCount);
	}else if($type==2) {//征粮
		$totalCount = sql_fetch_one_cell("select food from mem_city_resource where cid='$tcid'");		
		$addCount=	floor($totalCount /10);
	
		addCityResources($tcid,0,0,0,(0-$addCount),0);
		$report=sprintf($GLOBALS['governOthers']['food_report'],$cityname,$x,$y,$addCount);
		sendReport($tuid,3,42,$cid,$tcid,$report);
		//加自己的
		addCityResources($cid,0,0,0,($addCount),0);
		$msg=sprintf($GLOBALS['governOthers']['food_suc'],$addCount);
		
	}else if($type==3) { //收编 
		$row = sql_fetch_one("select a.sid,a.count from sys_city_soldier a,cfg_soldier b where cid='$tcid' and a.sid=b.sid and b.fromcity=1 order by count desc limit 1");		
		$sid=1;
		$totalCount=0;
		if ($row){
			$sid = $row["sid"];
			$totalCount = $row["count"];
		}
		$sname = sql_fetch_one_cell("select name from cfg_soldier where sid = $sid");
		$addCount=	floor($totalCount/50);		
		addCitySoldier($tcid,$sid,(0-$addCount));
		$report=sprintf($GLOBALS['governOthers']['incorporation_report'],$cityname,$x,$y,$sname,$addCount);
		sendReport($tuid,3,43,$cid,$tcid,$report);
		
		$meaddcount= floor($addCount/2);
		//加自己的
		addCitySoldier($cid,$sid,$meaddcount);
		$msg=sprintf($GLOBALS['governOthers']['incorporation_suc'],$sname,$meaddcount);		
	}else if($type==4) { //裁军
		$row = sql_fetch_one("select sid,count from sys_city_soldier where cid='$tcid' order by count desc  limit 1");
		$sid=1;
		$totalCount=0;
		if ($row){
			$sid = $row["sid"];
			$totalCount = $row["count"];
		}
		$sname = sql_fetch_one_cell("select name from cfg_soldier where sid = $sid");
		$addCount=	floor($totalCount/20);
		addCitySoldier($tcid,$sid,(0-$addCount));
		$report=sprintf($GLOBALS['governOthers']['disarmament_report'],$cityname,$x,$y,$sname,$addCount);	
		sendReport($tuid,3,44,$cid,$tcid,$report);
		$msg=sprintf($GLOBALS['governOthers']['disarmament_suc'],$sname,$addCount);		
	}


	sql_query("insert into mem_city_schedule (cid,last_be_govern_time) values('$tcid',unix_timestamp()) on duplicate key update last_be_govern_time=unix_timestamp()");

	if($todayFirst){
		//重新累计24小时
		sql_query("insert into mem_city_schedule (cid,govern_count,last_be_govern_time) values('$cid','$todayCount',unix_timestamp()) on duplicate key update last_govern_time=unix_timestamp(), govern_count='$todayCount' ");
	}else{
		sql_query("insert into mem_city_schedule (cid,govern_count) values('$cid','$todayCount') on duplicate key update govern_count='$todayCount' ");
	}

	//sql_query("insert into log_reward_city_temp values($uid,1,1) on duplicate key update count=count+1;");
	
	throw new Exception($msg);

}

function getMapCity($uid,$param){
	$cityInfos = sql_fetch_rows("select c.name,c.cid,c.type,u.name as ownername ,un.name as union_name from sys_city c left join sys_user u on c.uid=u.uid left join sys_union un on u.union_id= un.id where c.type>1  and c.type<5");
	
	$ret = array();
	foreach($cityInfos as $cityInfo)
	{
		$type = $cityInfo['type'];
		if(intval($type)==4)  //都城洛阳
		{
			$startTime = getAssingStartTime();
			$belongUid = sql_fetch_one_cell("select uid from log_luoyang_belong where time>='$startTime'");
			if(empty($belongUid))$belongUid=-1;
			$ownInfo = sql_fetch_one("select u.name as username,n.name as unionname from sys_user u,sys_union n where u.union_id=n.id and u.uid='$belongUid'");
			if(!empty($ownInfo)){				
				$cityInfo['ownername']=$ownInfo['username'];
				$cityInfo['union_name']=$ownInfo['unionname'];
			}			
		}
		$ret[] = $cityInfo;
	}	
	return $ret;
}

function checkCanInvade($uid, $param) {//检查是否有侵略者
	$ret = array();
	
	$cid = intval(array_shift($param));
	$type = sql_fetch_one_cell("select type from sys_city where cid=$cid");
	if ($type == 5) $type = 0; 
	
	if($type < 2) {
		$ret[] = true;
		return $ret;
	}
	
	$unionid = sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
	$wid = cid2wid($cid);
	$place = sql_fetch_one("select * from mem_world where wid=$wid limit 1");
	$province = $place['province'];
	$jun = $place['jun'];
	
	if($type == 2) {
		$total = sql_fetch_one_cell("select count(*) from sys_city c left join mem_world w on c.cid=(floor(w.wid / 10000) * 10 + floor(((w.wid % 100) / 10)))*1000 + floor((w.wid % 10000) / 100) * 10 + floor(w.wid % 10) where c.type=1 and w.province=$province and w.jun=$jun");
		$invaded = sql_fetch_one_cell("select count(*) from sys_city c left join mem_world w on c.cid=(floor(w.wid / 10000) * 10 + floor(((w.wid % 100) / 10)))*1000 + floor((w.wid % 10000) / 100) * 10 + floor(w.wid % 10) left join sys_user u on u.uid=c.uid where c.type=1 and w.province=$province and w.jun=$jun and u.union_id=$unionid");
	} else if($type == 3) {
		$total = sql_fetch_one_cell("select count(*) from sys_city c left join mem_world w on c.cid=(floor(w.wid / 10000) * 10 + floor(((w.wid % 100) / 10)))*1000 + floor((w.wid % 10000) / 100) * 10 + floor(w.wid % 10) where c.type=2 and w.province=$province");
		$invaded = sql_fetch_one_cell("select count(*) from sys_city c left join mem_world w on c.cid=(floor(w.wid / 10000) * 10 + floor(((w.wid % 100) / 10)))*1000 + floor((w.wid % 10000) / 100) * 10 + floor(w.wid % 10) left join sys_user u on u.uid=c.uid where c.type=2 and w.province=$province and u.union_id=$unionid");
	} else if($type == 4) {
		$total = sql_fetch_one_cell("select count(*) from sys_city c where c.type=3");
		$invaded = sql_fetch_one_cell("select count(*) from sys_city c left join sys_user u on u.uid=c.uid where c.type=3 and u.union_id=$unionid");
	}
	
	$percent = $invaded / $total;
	if($percent > 0.33333333) {
		$ret[] = true;
		return $ret;
	} 
	
	$ret[] = false;
	$ret[] = $type;
	$ret[] = $total;
	$ret[] = $invaded;
	return $ret;
	
}

function markCity($uid, $param)
{
	$cid = intval(array_shift($param));
	$city = sql_fetch_one("select * from sys_city where cid=$cid limit 1");
	if(empty($city) || $city['type'] == 0|| $city['type'] == 5) {
		throw new Exception($GLOBALS['MarkCity']['only_for_famous_city']);
	}
	
	$unionid = sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
	if(empty($unionid)) {
		throw new Exception($GLOBALS['MarkCity']['No_Union']);
	}
	$union = sql_fetch_one("select * from sys_union where id=$unionid limit 1");
	if(empty($union)) {
		throw new Exception($GLOBALS['MarkCity']['No_Union']);
	}
	
	$unionpos = sql_fetch_one_cell("select union_pos from sys_user where uid=$uid");
	if($unionpos <= 0 || $unionpos <= 3) {
		throw new Exception($GLOBALS['MarkCity']['No_Permission']);
	}
	
	$owneruid = sql_fetch_one_cell("select uid from sys_city where cid=$cid");
	if($owneruid > 1000) {
		$targetunionid = sql_fetch_one_cell("select union_id from sys_user where uid=$owneruid");
		$relation = sql_fetch_rows("select * from sys_union_relation where unionid=$unionid and target=$targetunionid and type=2");
		if(empty($relation)) {
			throw new Exception($GLOBALS['MarkCity']['not_in_war']);
		}
	}
	
	//删一下过期的标记
	clearMark($uid);
	
	$mark = sql_fetch_rows("select * from sys_union_mark where unionid = $unionid and cid=$cid");
	if(!empty($mark)) {
		throw new Exception($GLOBALS['MarkCity']['have_marked']);
	}
	
	$count = sql_fetch_one_cell("select count(*) from sys_union_mark where unionid=$unionid");
	if($count >= 20) {
		$first = sql_fetch_one_cell("select min(endtime) from sys_union_mark where unionid=$unionid");
		$nowtime = sql_fetch_one_cell("select unix_timestamp()");
		$msg = sprintf($GLOBALS['MarkCity']['too_many_marks'], MakeTimeLeft($first - $nowtime));
		throw new Exception($msg);
	}
	$wid = cid2wid($cid);
	sql_query("insert into sys_union_mark(`unionid`, `cid`, `endtime`, `type`,`wid`) values($unionid, $cid, unix_timestamp() + 48*3600, 1, $wid)");

	// 盟主发信通知本方盟内所有成员
	$receivers=sql_fetch_rows("select uid, name from sys_user where union_id='$unionid'");
	$leader = sql_fetch_one("select * from sys_user where union_id=$unionid and union_pos=5");
	$cityname = $city['name'];
	$x = $cid % 1000;
	$y = floor($cid / 1000);
	$content = sprintf($GLOBALS['MarkCity']['mark_mail_content'],$cityname, $x, $y);
	$title = $GLOBALS['MarkCity']['mark_mail_title'];
	foreach($receivers as $receiver)
	{
	    $mid = sql_insert("insert into sys_mail_content (`content`,`posttime`) values ('$content',unix_timestamp())");	    
	    sql_insert("insert into sys_mail_box (`uid`,`name`,`fromuid`,`fromname`,`contentid`,`title`,`read`,`recvstate`,`sendstate`,`posttime`) values ('$receiver[uid]','$receiver[name]','$uid','$leader[name]','$mid','$title','0','0','0',unix_timestamp())");
	}
	
	throw new Exception($GLOBALS['MarkCity']['mark_succ']);
}

function clearMark($uid) {
	$unionid = sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
	$marks = sql_fetch_rows("select * from sys_union_mark where unionid=$unionid");
	if(empty($marks)) return;
	foreach($marks as $mark)
	{
		$cid = $mark['cid'];
		$owneruid = sql_fetch_one_cell("select uid from sys_city where cid=$cid");
		if($owneruid > 1000) {
			$targetunionid = sql_fetch_one_cell("select union_id from sys_user where uid=$owneruid");
			$relation = sql_fetch_rows("select * from sys_union_relation where unionid=$unionid and target=$targetunionid and type=2");
			if(empty($relation)) {
				sql_query("update sys_union_mark set endtime = 0 where id='$mark[id]'");
			}
		}		
	}	
	sql_query("delete from sys_union_mark where endtime < unix_timestamp()");
}

function getActionField($uid,$param)
{
	$type = array_shift($param);   //0为拖地图，1为点击"位置"按钮
	
	$tempArr = array();
	$results = sql_fetch_rows("select cid,count,starttime from cfg_special_act");	
	
	if(empty($results))
	{
		$wid = -1;		
		$tempArr[]=$wid;
	}else 
	{
		foreach ($results as $result)
		{
			$cid = $result["cid"];
			$count = $result["count"];
			$starttime = $result["starttime"];
			$starttimeArr = explode(":",$starttime);
			
			$curtime = sql_fetch_one_cell("select unix_timestamp()");
			$curdaystartSec = sql_fetch_one_cell("select unix_timestamp(curdate())");
			$starttimeSec = intval($starttimeArr[0])*3600+intval($starttimeArr[1])*60+intval($curdaystartSec);
			
			$wid = cid2wid($cid);
			
			if(intval($cid)<1000||intval($count)==0||intval($curtime)<$starttimeSec)
			{
				$wid = -1;
			}
			
			$tempArr[]=$wid;
		}
	}
		
	$ret = array();
	$ret[] = $type;
	$ret[] = $tempArr;

	return $ret;

}
function getUserFields($uid)
{
	$userFields = sql_fetch_rows("select wid from mem_world m,sys_city s where m.ownercid=s.cid and m.type>0 and s.uid='$uid'");
	$ret = array();
	$ret[] = $userFields;

	return $ret;
}
function unionAssit($uid,$param)
{
	$cid = intval(array_shift($param));
	
	$city = sql_fetch_one("select * from sys_city where cid='$cid' limit 1");
	$targetUid = $city['uid'];
	if(empty($city)) {
		throw new Exception($GLOBALS['useGoods']['city_not_exists']);
	}
	$selfUnion = sql_fetch_one("select union_id,union_pos from sys_user where uid='$uid'");
	$memUnionId = sql_fetch_one_cell("select u.union_id from sys_user u,sys_city c where u.uid=c.uid and c.cid='$cid'");
	
	//自己是否为盟主或者副盟主
	if(intval($selfUnion['union_pos'])!=4 && intval($selfUnion['union_pos'])!=5)
	{
		throw new Exception($GLOBALS['world']['union_pos_lower']);
	}
	//被援助对象和自己是否为同一个联盟
	if(intval($selfUnion['union_id']) != intval($memUnionId))
	{
		throw new Exception($GLOBALS['world']['not_in_one_union']);
	}
	//被援助对象是否为休假状态，封禁状态，免战冷却状态
	$targetState1 = sql_fetch_one("select 1 from sys_user_state where uid='$targetUid' and (forbiend>unix_timestamp()||vacend>unix_timestamp())");
	$targetState2 = sql_fetch_one("select 1 from mem_user_buffer where uid='$targetUid' and buftype='8' and endtime>unix_timestamp()");
	if(!empty($targetState1)||!empty($targetState2))
	{
		throw new Exception($GLOBALS['world']['can_not_assit']);
	}
	//被援助对象的所有普通城池是否有一个处于战乱状态
	$mycities = sql_fetch_rows("select cid from sys_city where uid='$targetUid' and type=0");
	foreach($mycities as $city1)
	{
		if (sql_check("select * from mem_world where wid=".cid2wid($city1['cid'])." and state=1"))
		{
			throw new Exception($GLOBALS['changeUserState']['some_city_in_war'].$GLOBALS['changeUserState']['mianzhan']);
		}
	}
	
	$userMoney = sql_fetch_one_cell("select money from sys_user where uid='$uid'");
	if(intval($userMoney)<200)
	{
		throw new Exception($GLOBALS['buyGoods']['no_enough_YuanBao']);
	}
	//先扣元宝
	addMoney($uid,-200,913);
	//给盟友增加2个小时的免战效果
	$now = sql_fetch_one_cell("select unix_timestamp()");
	$persistTime = 7200;
	$finishTime = $now+$persistTime;
	sql_query("insert into mem_user_buffer(`uid`,`buftype`,`endtime`) values('$targetUid','7','$finishTime') on duplicate key update endtime=endtime+$persistTime");
	sql_query ( "update sys_user set state=2 where uid='$targetUid'" );
	
	$ret = array();
	$ret[] = $GLOBALS['world']['assit_succ'];
	
	return $ret;
}
?>