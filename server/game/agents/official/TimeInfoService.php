<?php

/*
 * 
 * 家园平台接口::::计时相关
 * 
 */

require_once 'Verify.php';
require_once 'Data.php';


function cid2wid($cid)
{
	$y = floor($cid / 1000);
	$x = ($cid % 1000);
	return (floor($y / 10)) * 10000 + (floor($x / 10)) * 100 + ($y % 10) * 10 + ($x % 10);
}
function wid2cid($wid)
{
	$y = floor($wid / 10000) * 10 + floor((($wid % 100) / 10));
	$x = floor(($wid % 10000) / 100) * 10 + floor($wid % 10);
	return $y * 1000 + $x;
}

function getArmyTroops($uid,$param)
{
	$troops=sql_fetch_rows("select t.*,c.name as fromcity from sys_troops t left join sys_city c on c.cid=t.cid where t.uid='$uid' and t.state<4");
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
	}
	return $troops;
}

function getEnemyTroops($uid,$param)
{
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
	foreach($troops1 as &$troop)
	{
		$troop['wtype']=0;
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
		$viewLevel = sql_fetch_one_cell("select level from sys_building where cid='".$troop['targetownercid']."' and bid=19 limit 1");
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

/*
 * ************************************************
 * 以下是接口调用
 * ************************************************
 */


/*
//获取驻军采集操作游戏计时
 *  * 返回
CityName	string	城池名称
State	string	状态（驻军、采集）
Origin	string	出发地
OriginLocation	string	出发地坐标
Aim	string	目的地
AimLocation	string	目的地坐标
CollectTime	string	持续采集时间[HH:mm:ss]
 */
function GetCollectTimeInfo()
{
	global $uid;
	if($uid==-1){
		echo 0;
		return;
	}
	$ret = array();
	$troops=sql_fetch_rows("select t.state as State,c.name as Origin,t.cid as OriginLocation,t.targetcid as AimLocation,greatest(unix_timestamp()-t.endtime,0) as CollectTime,t.startcid,t.id,g.starttime from sys_troops t left join sys_city c on c.cid=t.cid left join sys_gather g on g.troopid=t.id where t.uid='$uid' and t.state=4");
	foreach($troops as &$troop)
	{
		$troop['CityName']=$troop['Origin'];
		$troop['wtype']=sql_fetch_one_cell("select type from mem_world where `wid`=".cid2wid($troop['AimLocation']));
		if($troop['wtype']==0)
		{
			$troop['Aim'] = sql_fetch_one_cell("select name from sys_city where cid=".$troop['AimLocation']);
		}else{
			$troop['Aim'] = sql_fetch_one_cell("select name from cfg_world_type where type=".$troop['wtype']);
		}
		 
		if($troop[task]==7 || $troop[task]==8 || $troop[task]==9){
			//派遣到某个战场
			$troop['Aim']=sql_fetch_one_cell("select name from sys_battle_city where cid=".$troop['AimLocation']);
			$troop['Origin']=sql_fetch_one_cell("select name from sys_city where cid=".$troop['startcid']);
			$troop['OriginLocation']= $troop['startcid'];
		}
	
		if(!empty($troop['starttime']))
		{
			$troop['State']=5;
			$troop['CollectTime']=sql_fetch_one_cell("select greatest(unix_timestamp()-starttime,0) from sys_gather where troopid='$troop[id]'");
		}
		$tArr=array();
		$tArr['CityName']=$troop['CityName'];
		$tArr['State']=$troop['State']==5?'采集':'驻守';
		$tArr['Origin']=$troop['Origin'];
		$tArr['OriginLocation']="[".($troop['OriginLocation']%1000).",".floor($troop['OriginLocation']/1000)."]";
		$tArr['Aim']=$troop['Aim'];
		$tArr['AimLocation']="[".($troop['AimLocation']%1000).",".floor($troop['AimLocation']/1000)."]";
		$tArr['CollectTime']=floor($troop["CollectTime"]/3600).":".floor($troop["CollectTime"]%3600/60).":".floor($troop["CollectTime"]%60);
		$ret[]=$tArr;
	}
	
	if(count($ret)==0){
		echo "";
		return;
	}
	echo json_encode($ret);
}

/*
 * 获取玩家操作类别计时
 * 
 * 返回
CityName	string	城池名称
Category	string	操作类别（建造、升级、降级、科技）
CurrentLevel	string	当前等级
TargetLevel	string	目标等级
Content	string	具体内容
RemainingTime	string	剩余时间[HH:mm:ss]
EndTime	string	结束时间[yyyy-MM-dd HH:mm:ss]
*/
function GetOperationTimeInfo()
{
	global $uid;
	if($uid==-1){
		echo 0;
		return;
	}
	global $CategoryList;
	$citys=sql_fetch_rows("select cid,name from sys_city where uid='$uid'");
	if(count($citys)>0){
		$arr=array();
		foreach($citys as $city){
			$buildupgradings=sql_fetch_rows("select a.level,b.name,greatest(state_endtime-unix_timestamp(),0) as remainingtime,from_unixtime(state_endtime) as endtime from mem_building_upgrading a,cfg_building b where a.bid=b.bid and cid=$city[cid]");
			$builddestroyings=sql_fetch_rows("select a.level,b.name,greatest(state_endtime-unix_timestamp(),0) as remainingtime,from_unixtime(state_endtime) as endtime from mem_building_destroying a,cfg_building b where a.bid=b.bid and cid=$city[cid]");
			$technicupgrading=sql_fetch_rows("select a.level,b.name,greatest(state_endtime-unix_timestamp(),0) as remainingtime,from_unixtime(state_endtime) as endtime from mem_technic_upgrading a,cfg_technic b where a.tid=b.tid and cid=$city[cid]");
			foreach($buildupgradings as $item){
				$tArr=array();
				$tArr["CityName"]=$city["name"]."[".($city['cid']%1000).",".floor($city['cid']/1000)."]";
				if($item["level"]==1)
					$tArr["Category"]=$CategoryList[0];
				else
					$tArr["Category"]=$CategoryList[1];
				$tArr["CurrentLevel"]=$item["level"]-1;
				$tArr["TargetLevel"]=$item["level"];
				$tArr["Content"]=$item["name"];
				$tArr["RemainingTime"]=floor($item["remainingtime"]/3600).":".floor($item["remainingtime"]%3600/60).":".floor($item["remainingtime"]%60);
				$tArr["EndTime"]=$item["endtime"];
				$arr[]=$tArr;
			}
			foreach($builddestroyings as $item){
				$tArr=array();
				$tArr["CityName"]=$city["name"]."[".($city['cid']%1000).",".floor($city['cid']/1000)."]";
				$tArr["Category"]=$CategoryList[2];
				$tArr["CurrentLevel"]=$item["level"]+1;
				$tArr["TargetLevel"]=$item["level"];
				$tArr["Content"]=$item["name"];
				$tArr["RemainingTime"]=floor($item["remainingtime"]/3600).":".floor($item["remainingtime"]%3600/60).":".floor($item["remainingtime"]%60);
				$tArr["EndTime"]=$item["endtime"];
				$arr[]=$tArr;
			}
			foreach($technicupgrading as $item){
				$tArr=array();
				$tArr["CityName"]=$city["name"]."[".($city['cid']%1000).",".floor($city['cid']/1000)."]";
				$tArr["Category"]=$CategoryList[3];
				$tArr["CurrentLevel"]=$item["level"]-1;
				$tArr["TargetLevel"]=$item["level"];
				$tArr["Content"]=$item["name"];
				$tArr["RemainingTime"]=floor($item["remainingtime"]/3600).":".floor($item["remainingtime"]%3600/60).":".floor($item["remainingtime"]%60);
				$tArr["EndTime"]=$item["endtime"];
				$arr[]=$tArr;
			}
		}
		if(count($arr)==0){
			echo "";
			return;
		}
		echo json_encode($arr);
	}else{
		echo "";
	}
}
/*
 * 返回结果
军对相关操作计时信息:ArmyTaskTimeInfo[]
参数	类型	说明
CityName	string	城池名称
Category	string	操作类别（出征作战、烽火警讯、盟友军队）
S1	string	任务/警报/动态
S2	string	状态/君主
Origin	string	出发地
OriginLocation	string	出发地坐标
Aim	string	目的地
AimLocation	string	目的地坐标
ReachTime	string	到达时间[yyyy-MM-dd HH:mm:ss]
RemainingTime	string	剩余时间[HH:mm:ss]
 */
function GetArmyTimeInfo()
{
	global $uid;
	if($uid==-1){
		echo 0;
		return;
	}
	
	$taskList=array("运输","派遣","侦察","抢掠","占领","防守","义兵","前往战场","战场派遣","战场攻击");
	$stateList=array("前进","返回","等待","战斗","驻守");
	
	$ret=array();
	$armyTroops=getArmyTroops($uid,array());
	$nowTime=sql_fetch_one_cell("select unix_timestamp()");
	foreach($armyTroops as $troop){
		$info=array();
		if($troop["startcid"]){
			$info["CityName"]=sql_fetch_one_cell("select name from sys_city where cid='$troop[startcid]'");
		}else{
			$info["CityName"]=$troop["fromcity"];
		}
		$info["Category"]="出征作战";
		$info["S1"]=$taskList[$troop["task"]];
		$info["S2"]=$stateList[$troop["state"]];
		$info["Origin"]=$troop["fromcity"];
		$info["OriginLocation"]="[".($troop[cid]%1000).",".floor($troop[cid]/1000)."]";
		$info["Aim"]=$troop["targetcity"];
		if($info["Aim"]==""&&$troop["wtype"]>0){
			$info["Aim"] = sql_fetch_one_cell("select name from cfg_world_type where type=".$troop["wtype"]);
		}
		$info["AimLocation"]="[".($troop[targetcid]%1000).",".floor($troop[targetcid]/1000)."]";
		$info["ReachTime"]=sql_fetch_one_cell("select from_unixtime($troop[endtime])");
		$remainingtime=max(0,$troop["endtime"]-$nowTime);
		$info["RemainingTime"]=floor($remainingtime/3600).":".floor($remainingtime%3600/60).":".floor($remainingtime%60);
		$ret[]=$info;
	}
	$enemyTroops=getEnemyTroops($uid,array());
	foreach($enemyTroops as $troop){
		$info=array();
		$info["CityName"]=$troop["targetcity"];
		$info["Category"]="烽火警讯";
		$info["S1"]=$taskList[$troop["task"]];
		$info["S2"]=$troop["enemyuser"];
		$info["Origin"]=$troop["origincity"];
		$info["OriginLocation"]="[".($troop[cid]%1000).",".floor($troop[cid]/1000)."]";
		$info["Aim"]=$troop["targetcity"];
		if($info["Aim"]==""&&$troop["wtype"]>0){
			$info["Aim"] = sql_fetch_one_cell("select name from cfg_world_type where type=".$troop["wtype"]);
		}
		$info["AimLocation"]="[".($troop[targetcid]%1000).",".floor($troop[targetcid]/1000)."]";
		$info["ReachTime"]=sql_fetch_one_cell("select from_unixtime($troop[endtime])");
		$remainingtime=max(0,$troop["endtime"]-$nowTime);
		$info["RemainingTime"]=floor($remainingtime/3600).":".floor($remainingtime%3600/60).":".floor($remainingtime%60);
		$ret[]=$info;
	}
	$unionTroops=getUnionTroops($uid,array());
	foreach($unionTroops as $troop){
		$info=array();
		$info["CityName"]=$troop["targetcity"];
		$info["Category"]="盟友军队";
		$info["S1"]=$taskList[$troop["task"]];
		$info["S2"]=$troop["username"];
		$info["Origin"]=$troop["fromcity"];
		$info["OriginLocation"]="[".($troop[cid]%1000).",".floor($troop[cid]/1000)."]";
		$info["Aim"]=$troop["targetcity"];
		if($info["Aim"]==""&&$troop["wtype"]>0){
			$info["Aim"] = sql_fetch_one_cell("select name from cfg_world_type where type=".$troop["wtype"]);
		}
		$info["AimLocation"]="[".($troop[targetcid]%1000).",".floor($troop[targetcid]/1000)."]";
		$info["ReachTime"]=sql_fetch_one_cell("select from_unixtime($troop[endtime])");
		$remainingtime=max(0,$troop["endtime"]-$nowTime);
		$info["RemainingTime"]=floor($remainingtime/3600).":".floor($remainingtime%3600/60).":".floor($remainingtime%60);
		$ret[]=$info;
	}
	
	if(count($ret)==0){
		echo "";
		return;
	}
	echo json_encode($ret);
	//print_r($enemyTroops);
}



function error(){echo "error";}
$func=$_GET["func"]?$_GET["func"]:"error";
$func();

?>