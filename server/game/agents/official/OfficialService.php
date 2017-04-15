<?php

if (!defined('PATH_SEPARATOR')) {if (substr(PHP_OS, 0, 3) == 'WIN') define('PATH_SEPARATOR', ';'); else define('PATH_SEPARATOR', ':');}	 
//设置根目录绝对路径到include_path,简化path的使用
ini_set('include_path',ini_get('include_path').PATH_SEPARATOR.realpath("../../../").PATH_SEPARATOR.realpath("../../../lib"));
require_once("config/db.php");
require_once("DB.php");
require_once("mysql.php");
require_once("database.php"); 

require_once 'lib/httpUtils.php';
require_once 'game/agents/BaseService.php';
require_once 'game/utils.php';

require_once 'game/passport/serverid.php';
require_once 'service_config.php';

/**
 * 
 * 官服平台接口
 *
 */
class OfficialService extends BaseService{
	
	function OfficialService($user){
		parent::__construct($user);
	}
	
	protected function sendAllUserAction($user, $title) {
		//echo $title;
	}
	/*
	 *推送系统消息 PushGameMessage.ashx 
	 *Category=1
	 *Passport	string	通行证
		Category	Int	消息分类
		MsgType	Int	消息类型
		Title	string	标题
		Content	string	内容
		Time	string	时间
		Sign	string	签名
	 */
	 function addSysInformEvent($msg)
	 {
	 	$key="M7MDFCR9WRRGRQ3ETBQ6";
	 	$Time = sql_fetch_one_cell("select date(now())");
		$Sign=md5($Time.$key);
	 	$url =ledu_url."PushGameMessage.ashx";
	 	$postString="Time=$Time&Sign=$Sign&ServerID=".server_id;
	 	$Passport=$this->user["passport"];
	 	$Category=1;
	 	$MsgType=101;
	 	$Content=urlencode($msg);
		$postString=$postString."&Passport=$Passport&Category=$Category&MsgType=$MsgType&Content=$Content";
		//echo $postString;
		$result = HttpUtils::httpPost($url,$postString);
	 }
	
	/*推送用户游戏动态信息  接口Url：PushGameMessage.ashx
	 * *Category=2
	 	1．升级信息:具体内容为官职升级，
	 	2.爵位升级，
		格式示例：在{game}的{server}中官职升为XXX，爵位升为XXX
		3．系统提示：开宝箱、
		格式示例：在[游戏名称]的[服务器名]中开宝箱获得了XXX
		格式示例：在[游戏名称]的[服务器名]中加入了XXXX联盟、退出了XXXX联盟，点击联盟名链接到该联盟的群组首页（未建则没有链接）
		4．战斗提示：有其他玩家向该用户发起进攻
			格式示例：在[游戏名称]的[服务器名]中XXX向你发起攻击
	 */
	function addGameMessageEvent($type)
	{
		
		$key="M7MDFCR9WRRGRQ3ETBQ6";
	 	$Time = sql_fetch_one_cell("select date(now())");
		$Sign=md5($Time.$key);
	 	$url =ledu_url."PushGameMessage.ashx";
	 	$postString="Time=$Time&Sign=$Sign&ServerID=".server_id;
	 	$Passport=$this->user["passport"];
		$msg="";
		$MsgType=0;
		switch($type){
			case 1:
				$msg="官职升为".func_get_arg(1);
				$MsgType=201;
				break;
			case 2:
				$msg="爵位升为".func_get_arg(1);
				$MsgType=201;
				break;
			case 3:
				$msg="开宝箱获得了".func_get_arg(1);
				$MsgType=101;
				break;
			case 4:
				$Passport=sql_fetch_one_cell("select passport from sys_user where name='".func_get_arg(1)."'");
				$msg="【".$this->user["name"]."】向你发起攻击";
				$MsgType=102;
				break;
			case 5:
				$msg="将游戏玩家【".func_get_arg(1)."】加为好友";
				$MsgType=203;
				break;
			case 6:
				$Passport=sql_fetch_one_cell("select passport from sys_user where name='".func_get_arg(1)."'");
				$msg="被【".$this->user["name"]."】加为好友";
				$MsgType=203;
				break;
			case 7:
				$msg=func_get_arg(1);
				$MsgType=102;
				break;
			default:
				return;
		}
		$Category=2;
	 	$Content=urlencode($msg);
		$postString=$postString."&Passport=$Passport&Category=$Category&MsgType=$MsgType&Content=$Content";
		//echo $postString;
		$result = HttpUtils::httpPost($url,$postString);
	}
	function addOfficePosEvent($officepos){$this->addGameMessageEvent(1,$officepos);}
	function addNobilityEvent($nobilityname){$this->addGameMessageEvent(2,$nobilityname);}
	function addGoodsEvent($goodsname,$goodsvalue){$this->addGameMessageEvent(3,$goodsname);}
	function addStartWarEvent($enemyname){$this->addGameMessageEvent(4,$enemyname);}
	function addFriendEvent($tname){
		if(sql_fetch_one_cell("select count(*) from sys_user where name='$tname'"))
			$this->addGameMessageEvent(5,$tname);$this->addGameMessageEvent(6,$tname);
	}
	function addAcceptTrickEvent($msg){$this->addGameMessageEvent(7,$msg);}
	
	/*
	 * 游戏联盟开除成员/成员退出联盟/加入成员
	 * 接口Url：UnionMember.ashx
	 * UnionID	Int	联盟ID
	 * GroupID	long/int64	小组ID
		Flag	Int	操作标志（开除成员：0，退出联盟：1，加入成员：2）
		Passport	string	通行证
		UserName	string	用户名
		UnionName	string	联盟名
		Time	string	时间
		Sign	string	签名
	 */
	function addUnionMemberEvent($unionid,$Flag,$groupID)
	{
		
		$key="M7MDFCR9WRRGRQ3ETBQ6";
	 	$Time = sql_fetch_one_cell("select date(now())");
		$Sign=md5($Time.$key);
	 	$url =ledu_url."UnionMember.ashx";
	 	$postString="Time=$Time&Sign=$Sign&ServerID=".server_id;
	 	$Passport=$this->user["passport"];
	 	$UserName=urlencode($this->user["name"]);
	 	$UnionName=urlencode(sql_fetch_one_cell("select name from sys_union where id='$unionid'"));
	 	$postString=$postString."&Passport=$Passport&UserName=$UserName&UnionName=$UnionName&Flag=$Flag&GroupID=$groupID";
	 	$result = HttpUtils::httpPost($url,$postString);
	}
	
	/*
	 * 游戏联盟外交
		接口Url：UnionDiplomacy.ashx
		消息格式：[联盟名]成为友好联盟/中立联盟/敌对联盟
		UnionID	Int	联盟ID
		Method	String	外交方式[友好、中立、敌对]
		UnionName	string	产生外交关系的联盟名称
		Time	string	时间
		Sign	string	签名
	 * 
	 */
	function addUnionDiplomacyEvent($unionid,$targeuname,$relation,$groupID)
	{
		
		$key="M7MDFCR9WRRGRQ3ETBQ6";
	 	$Time = sql_fetch_one_cell("select date(now())");
		$Sign=md5($Time.$key);
	 	$url =ledu_url."UnionDiplomacy.ashx";
	 	$postString="Time=$Time&Sign=$Sign&ServerID=".server_id;
	 	$UnionID=$unionid;
	 	$Method=urlencode($relation);
	 	$UnionName=urlencode($targeuname);//urlencode(sql_fetch_one_cell("select name from sys_union where id='$targetid'"));
	 	$postString=$postString."&UnionID=$UnionID&Method=$Method&UnionName=$UnionName&GroupID=$groupID";
		$result = HttpUtils::httpPost($url,$postString);
	}
	/*
	 * 游戏联盟转让盟主
		接口Url：TransferUnionChief.ashx
		消息格式：[前盟主的用户名]将盟主之位转让给[现盟主的用户名]
		UnionID	Int	联盟ID
		GroupID	long/int64	小组ID
		UserName1	string	前盟主的用户名
		UserName2	string	现盟主的用户名
		Time	string	时间
		Sign	string	签名

	 * 
	 */
	function addTransferUnionChiefEvent($unionid,$name1,$name2,$groupID)
	{
		
		$key="M7MDFCR9WRRGRQ3ETBQ6";
	 	$Time = sql_fetch_one_cell("select date(now())");
		$Sign=md5($Time.$key);
	 	$url =ledu_url."TransferUnionChief.ashx";
	 	$postString="Time=$Time&Sign=$Sign&ServerID=".server_id;
	 	$UnionID=$unionid;
	 	$UserName1=urlencode($name1);
	 	$UserName2=urlencode($name2);
	 	$postString=$postString."&UnionID=$UnionID&UserName1=$UserName1&UserName2=$UserName2&GroupID=$groupID";
		$result = HttpUtils::httpPost($url,$postString);
	}
	
	/*
	 * 游戏联盟官员辞职
		接口Url：UnionOfficialResign.ashx
		消息格式：[用户名]辞去[联盟职务名]一职
		UnionID	Int	联盟ID
		UserName	string	用户名
		JobName	string	联盟职务名
		Time	string	时间
		Sign	string	签名
	 * 
	 */
	function addUnionOfficialResignEvent($unionid,$name,$jobname,$groupID)
	{
		
		$key="M7MDFCR9WRRGRQ3ETBQ6";
	 	$Time = sql_fetch_one_cell("select date(now())");
		$Sign=md5($Time.$key);
	 	$url =ledu_url."UnionOfficialResign.ashx";
	 	$postString="Time=$Time&Sign=$Sign&ServerID=".server_id;
	 	$UnionID=$unionid;
	 	$UserName=urlencode($name);
	 	$JobName=urlencode($jobname);
	 	$postString=$postString."&UnionID=$UnionID&UserName=$UserName&JobName=$JobName&GroupID=$groupID";
		$result = HttpUtils::httpPost($url,$postString);
	}
	
	/*
	 * 游戏联盟宣战
		接口Url：UnionDeclareWar.ashx
		消息格式：[联盟名]向[联盟名]发起宣战
		UnionName1	string	发起宣战的联盟名
		UnionName2	string	接受宣战的联盟名
		Time	string	时间
		Sign	string	签名
	 * 
	 */
	function addUnionDeclareWarEvent($uname1,$uname2,$groupID,$groupID2)
	{
		
		$key="M7MDFCR9WRRGRQ3ETBQ6";
	 	$Time = sql_fetch_one_cell("select date(now())");
		$Sign=md5($Time.$key);
	 	$url =ledu_url."UnionDeclareWar.ashx";
	 	$postString="Time=$Time&Sign=$Sign&ServerID=".server_id;
	 	$UnionName1=urlencode($uname1);
	 	$UnionName2=urlencode($uname2);
	 	$postString=$postString."&UnionName1=$UnionName1&UnionName2=$UnionName2&GroupID1=$groupID&GroupID2=$groupID2";
	 	//echo $postString;
		$result = HttpUtils::httpPost($url,$postString);
	}
	
	/*
	 * 推送军队相关操作游戏计时
		接口Url：PushArmyOperationTime.ashx
		Passport	string	通行证
		Time	string	时间
		Sign	string	签名
		ArmyTaskTimeInfo	string	军对相关操作计时信息列表[JSON格式]
		ArmyTaskTimeInfo
		参数			类型		说明
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
	 * 
	 */
	function addPushArmyOperationTimeEvent($troopid)
	{
		
		$key="M7MDFCR9WRRGRQ3ETBQ6";
	 	$Time = sql_fetch_one_cell("select date(now())");
		$Sign=md5($Time.$key);
	 	$url =ledu_url."PushArmyOperationTime.ashx";
	 	$postString="Time=$Time&Sign=$Sign&ServerID=".server_id;
	 	
		$troop=sql_fetch_one("select *,greatest(endtime-unix_timestamp(),0) as remainingtime,from_unixtime(endtime) as reachtime from sys_troops where id='$troopid'");
		
		//出征作战
		$info=array();
		if($troop["startcid"]){
			$info["CityName"]=sql_fetch_one_cell("select name from sys_city where cid='$troop[startcid]'");
		}else{
			$info["CityName"]=sql_fetch_one_cell("select name from sys_city where cid='$troop[cid]'");
		}
		$info["Category"]="出征作战";
		$taskList=array("运输","派遣","侦察","抢掠","占领","防守","义兵","前往战场","战场派遣","战场攻击");
		$stateList=array("前进","返回","等待","战斗","驻守");
		$info["S1"]=$taskList[$troop["task"]];
		$info["S2"]=$stateList[$troop["state"]];
		
		$info["Origin"]=$info["CityName"];
		if($troop["task"]==7){
			$xy=$troop['targetcid']%1000;
			$bid=$troop['bid'];
			$info['Aim']=sql_fetch_one_cell("select name from cfg_battle_city where bid=$bid and xy=$xy");
		}else if($troop["task"]==8 || $troop["task"]==9){
			$info['Origin']=sql_fetch_one_cell("select name from sys_battle_city where cid=".$troop['targetcid']);
			$info['Aim']=sql_fetch_one_cell("select name from sys_battle_city where cid=".$troop['cid']);
		}else{
			$worldType=sql_fetch_one_cell("select type from mem_world where `wid`=".cid2wid($troop['targetcid']));
			if($troop['wtype']==0)
			{
				$info["Aim"] = sql_fetch_one_cell("select name from sys_city where cid=".$worldType);
			}else{
				$info["Aim"] = sql_fetch_one_cell("select name from cfg_world_type where type=".$worldType);
			}
		}
		$info["OriginLocation"] = "[".($troop[cid]%1000).",".floor($troop[cid]/1000)."]";
		$info["AimLocation"] = "[".($troop[targetcid]%1000).",".floor($troop[targetcid]/1000)."]";
		$info["ReachTime"] = $troop["reachtime"];
		$info["RemainingTime"] = floor($troop["remainingtime"]/3600).":".floor($troop["remainingtime"]%3600/60).":".floor($troop["remainingtime"]%60);
		
		$ArmyTaskTimeInfo=urlencode(json_encode($info));
		$Passport=$this->user["passport"];
		$postString=$postString."&Passport=$Passport&ArmyTaskTimeInfo=$ArmyTaskTimeInfo";
		$result = HttpUtils::httpPost($url,$postString);
		
		//烽火警讯
		if($troop['wtype']==0 && ($troop["task"]==2||$troop["task"]==3||$troop["task"]==4)){
			$viewLevel = sql_fetch_one_cell("select level from sys_building where cid='".$troop['targetcid']."' and bid=19 limit 1");
			$enemyuser=urlencode($this->user["name"]);
			if(empty($viewLevel))
			{
				$viewLevel=0;
			}
			if($viewLevel<4){
				$enemyuser="--";
			}else if($viewLevel<5){
				$info["Origin"]="--";
				$info["OriginLocation"]=0;
			}
			$Passport=urlencode(sql_fetch_one_cell("select passport from sys_user a,sys_city b where a.uid=b.uid and b.cid='$troop[targetcid]'"));
			$info["Category"]="出征作战";
			$info["S2"]=urlencode($this->user["name"]);
			$ArmyTaskTimeInfo=urlencode(json_encode($info));
			$postString=$postString."&Passport=$Passport&ArmyTaskTimeInfo=$ArmyTaskTimeInfo";
			$result = HttpUtils::httpPost($url,$postString);
		}
		//盟友军队
		if($troop["task"]==0||$troop["task"]==1){
			$unionid=sql_fetch_one_cell("select union_id from sys_user where passport='$Passport'");
			if(!empty($unionid)){
				$Passport=sql_fetch_one_cell("select passport from sys_user a,sys_city b where a.uid=b.uid and b.cid='$troop[targetcid]' and union_id='$unionid'");
				if(!empty($Passport)){
					$info["Category"]="盟友军队";
					$info["S2"]=urlencode($this->user["name"]);
					$ArmyTaskTimeInfo=urlencode(json_encode($info));
					$postString=$postString."&Passport=$Passport&ArmyTaskTimeInfo=$ArmyTaskTimeInfo";
					$result = HttpUtils::httpPost($url,$postString);
				}
			}
		}
	}
}


?>