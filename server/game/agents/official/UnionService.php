<?php

/*
 * 
 * 家园平台接口::::联盟相关
 * 
 */

require_once 'Verify.php';

//更新联盟是否已推送标志
function SetUnionFlag()
{
	global $uid;
	if(!isset($_GET["unionID"])||!isset($_GET["groupID"])){
		echo 0;
		return;
	}
	
	$unionID=intval($_GET["unionID"]);
	$flag=intval($_GET["groupID"]);
	
	$isSuccess=0;
	$isSuccess=sql_query("update sys_union set sns_flag='$flag' where id='$unionID'");
	
	echo $isSuccess;
}

//获取联盟战况信息
function GetUnionWarInfo()
{
	global $uid;
	if(!isset($_GET["unionID"])){
		echo 0;
		return;
	}
	$unionID=intval($_GET["unionID"]);
	//$unionID=1;
	$reportList=sql_fetch_rows("select concat(`description`,'  [',from_unixtime(time),']') as content from `sys_union_report` where `unionid`= '$unionID' order by time desc");
	$ret=array();
	foreach($reportList as $item){
		$ret[]=$item["content"];
	}
	$ret=implode($ret,"|");
	echo $ret;
}

//获取前100联盟列表
function GetUnionTop100()
{
	$unionList=sql_fetch_rows("select id from sys_union where rank<=100 and sns_flag<=0");
	$ret=array();
	foreach($unionList as $item){
		$ret[]=$item["id"];
	}
	$ret=implode($ret,"|");
	
	echo $ret;
}

//获取联盟成员列表及基本信息
function GetUnionInfo()
{
	global $uid;
	if(!isset($_GET["unionID"])){
		echo 0;
		return;
	}
	
	$unionID=intval($_GET["unionID"]);

	$unionInfo=sql_fetch_one("select a.`id` as UnionID,a.`name` as UnionName,a.intro as Introduction,b.name as Chief,b.passport as ChiefPassport from sys_union a,sys_user b where a.id=b.union_id and b.uid=a.leader and id='$unionID'");
	$memberList=sql_fetch_rows("select passport as Passport,name as NickName from sys_user where union_id='$unionID'");
	$unionInfo["MemberNumber"]=count($memberList);
	$unionInfo["MemberList"]=$memberList;
	
	if(count($unionInfo)==0){
		echo "";
		return;
	}
	echo json_encode($unionInfo);
}

function error(){echo "error";}
$func=$_GET["func"]?$_GET["func"]:"error";
$func();

?>