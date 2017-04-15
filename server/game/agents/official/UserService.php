<?php

/*
 * 
 * 家园平台接口数据:::用户相关
 * 
 */

require_once 'Verify.php';
require_once 'Data.php';

//获取游戏服务器当前在线人数
function GetOnlines()
{
	$onlines=sql_fetch_one_cell("select count(*) from sys_online where unix_timestamp() - lastupdate<30");
	echo $onlines;
}

//获取指定帐号所在游戏服务器的在线状态
function IsOnline()
{
	global $uid;
	if($uid==-1){
		echo 0;
		return;
	}
	$isOnlie=sql_fetch_one_cell("select count(*) from sys_online where uid='$uid' and unix_timestamp() - lastupdate<30");
	echo $isOnlie>0?1:0;
}

//获取指定服务器帐号的君主信息
function GetUserInfo()
{
	global $uid;
	if($uid==-1){
		echo 0;
		return;
	}
	global $UnionPosList;
	
	//$userInfo=sql_fetch_one("select a.name as KingName,b.name as `Union`,a.union_pos as Job,c.name as Level,d.name as Officer,a.rank as Rank from sys_user a,sys_union b,cfg_nobility c,cfg_office_pos d where uid = '$uid' and a.union_id=b.id and a.nobility=c.id and a.officepos=d.id");
	$userInfo=sql_fetch_one("select a.name as KingName,a.union_pos as Job,c.name as Level,d.name as Officer,a.rank as Rank,b.name as `Union` from sys_user a left join sys_union b on a.union_id=b.id,cfg_nobility c,cfg_office_pos d where uid = '$uid' and a.nobility=c.id and a.officepos=d.id;");
	$cityCount=sql_fetch_one_cell("select count(*) from sys_city where uid='$uid'");
	$userInfo["CityCount"]=$cityCount;
	$userInfo["Job"]=$UnionPosList[$userInfo["Job"]];
	
	if(count($userInfo)==0){
		echo "";
		return;
	}
	echo json_encode($userInfo);
}

//获取指定帐号在游戏服务器上的排名信息
function GetAllRank()
{
	global $uid;
	if($uid==-1){
		echo 0;
		return;
	}
	
	$user=sql_fetch_one("select rank,union_id,name from sys_user where uid='$uid'");
	$rank=$user["rank"];
	$unionRank=sql_fetch_one("select rank as Rank,name as UnionName,leader as Chief,member as MemberNumber,famouscity as CityNumber,prestige as Renown from rank_union where uid='$user[union_id]'");
	if(!$unionRank)
		unset($unionRank);
	$heroRanks=sql_fetch_rows("select a.name as GeneralName,a.rank as LevelRank,b.rank as InteriorRank,c.rank as ForceRank,d.rank as WitRank from rank_hero a,rank_hero_affairs b,rank_hero_bravery c,rank_hero_wisdom d where a.user='$user[name]' and a.hid=b.hid and a.hid=c.hid and a.hid=d.hid;");
	$cityRanks=sql_fetch_rows("select a.name as CityName,CONCAT('[',a.cid%1000,',',floor(a.cid/1000),']') as Location,b.rank as PopulationRank,a.rank as TypeRank from rank_city_type a,rank_city b where a.user='$user[name]' and a.cid=b.cid;");
	$militaryRank=array();
	$militaryRank['ArmyRank']=sql_fetch_one_cell("select rank from rank_military where name='$user[name]'");
	$militaryRank['AttackRank']=sql_fetch_one_cell("select rank from rank_military_attack where name='$user[name]'");
	$militaryRank['DefendRank']=sql_fetch_one_cell("select rank from rank_military_defence where name='$user[name]'");
	foreach($militaryRank as &$item){
		$item=$item?$item:0;
	}
	$battleRank=array();
	$battleRank['ArmyRank']=sql_fetch_one_cell("select rank from rank_battle_total where uid='$uid'");
	$battleRank['AttackRank']=sql_fetch_one_cell("select rank from rank_battle_week where uid='$uid'");
	$battleRank['DefendRank']=sql_fetch_one_cell("select rank from rank_battle_day where uid='$uid'");
	foreach($battleRank as &$item){
		$item=$item?$item:0;
	}
	$ret=array("Rank"=>$rank,"UnionRank"=>$unionRank,"GeneralRank"=>$heroRanks,"CityRank"=>$cityRanks,"MilitaryRank"=>$militaryRank,"WarFieldRank"=>$battleRank);
	
	if(count($ret)==0){
		echo "";
		return;
	}
	echo json_encode($ret);
}

//获取指定帐号在游戏服务器上的好友列表
function GetFriendList()
{
	global $uid;
	if($uid==-1){
		echo 0;
		return;
	}
	$ret=array();
	$list=sql_fetch_rows("select tuid,from_unixtime(time) as Time1 from sys_user_relation where uid='$uid'");
	$myName=sql_fetch_one_cell("select name from sys_user where uid='$uid'");
	foreach($list as $item){
		$row=sql_fetch_one("select u.passport as Passport,u.name as KingName,from_unixtime(time) as Time2 from sys_user_relation r,sys_user u where u.uid=r.uid and r.tuid='$uid' and r.uid='$item[tuid]'");
		if(!empty($row)){
			$row["Time1"]=$item["Time1"];
			//最后一次战斗
			//$battle=sql_fetch_one("select `type` as Type,from_unixtime(starttime) as Time,(select passport from sys_user where uid=attackuid) as Attack,(select passport from sys_user where uid=resistuid) as Resist,state as State,result as Result from sys_battle where (attackuid=$uid and resistuid=$item[tuid]) or (attackuid=$item[tuid] and resistuid=$uid) order by starttime desc limit 1");
			$battle=sql_fetch_one("select `type` as Type,from_unixtime(starttime) as Time,(select name from sys_user where uid=attackuid) as Attack,(select name from sys_user where uid=resistuid) as Resist,state as State,result as Result from sys_battle where (attackuid=$uid and resistuid=$item[tuid]) or (attackuid=$item[tuid] and resistuid=$uid) order by starttime desc limit 1");
			//$row["BattleInfo"]=$battle;
			//所在联盟情况
			$unionid1=sql_fetch_one_cell("select union_id from sys_user where uid='$uid'");
			$unionid2=sql_fetch_one_cell("select union_id from sys_user where uid='$item[tuid]'");
			$relation=sql_fetch_one("select type from sys_union_relation where (unionid='$unionid1' and target='$unionid2') or (unionid='$unionid2' and target='$unionid1') limit 1");
			if(empty($relation)){
				$row["UnionRelation"]="中立";
				if($unionid1==$unionid2){
					$row["UnionRelation"]="同盟";
				}
			}else{
				if($relation["type"]==0){
					$row["UnionRelation"]="友好";
				}else if($relation["type"]==1){
					$row["UnionRelation"]="中立";
				}else if($relation["type"]==2){
					$row["UnionRelation"]="敌对";
				}
			}
			$info=array();
			$info["Passport"]=$row["Passport"];
			$info["KingName"]=$row["KingName"];
			
			$text1="【".$myName."】于 $row[Time1] 将【$row[KingName]】加为好友";
			$text2="【$row[KingName]】于 $row[Time2] 将【".$myName."】加为好友";
			
			if($row[Time1]<$row[Time2]){
				$content=$text1."，".$text2;
			}else{
				$content=$text2."，".$text1;
			}
			
			if(!$battle){
				$battleInfo = "你们最近没有发生战斗";
			}else{
				$BattleType=array("野地战","攻城战","抢掠战");
				$BattleResult=array("胜利","失败","平局","未知");
				$battleInfo="【".$battle["Attack"]."】于".$battle["Time"]."与【".$battle["Resist"]."】发生了".$BattleType[$battle["Type"]]."，结果".$BattleResult[$battle["Result"]];
			}
			$content .= "|".$battleInfo;
			
			$content .= "|你们所在联盟的关系是".$row["UnionRelation"]."关系";
			
			$info["Memo"]=$content;
			$ret[]=$info;
			/*$text1="你于 $item[time] 将【$row[KingName]】加为好友";
			$text2="【$row[KingName]】于 $row[time] 将你加为好友";
			if($item[time]<$row[time]){
				$content=$text1."，".$text2."！";
			}else{
				$content=$text2."，".$text1."！";
			}
			unset($row["time"]);
			$row["Memo"]=$content;
			$ret[]=$row;
			*/
		}
	}
	//print_r($ret);
	if(count($ret)==0){
		echo "";
		return;
	}
	echo json_encode($ret);
}

//获取玩家在游戏服务器上的城池列表
function GetCityList()
{
	global $uid;
	if($uid==-1){
		echo 0;
		return;
	}
	$list=sql_fetch_rows("select name as CityName,concat('[',cid%1000,',',floor(cid/1000),']') as Location from sys_city where uid='$uid'");
	
	if(count($list)==0){
		echo "";
		return;
	}
	echo json_encode($list);
}
/*
$uid=1008;
GetFriendList();
echo "localtest!!"
*/

function error(){echo "error";}
$func=$_GET["func"]?$_GET["func"]:"error";
$func();

?>