<?php

DEFINE ("CITY_EVENT_51",1);
DEFINE ("DECLARE_WAR_EVENT_51",2);
DEFINE ("GOODS_EVENT_51",3);
DEFINE ("HERO_EVENT_51",4);
DEFINE ("JOIN_EVENT_51",5);
DEFINE ("NOBILITY_EVENT_51",6);
DEFINE ("OFFICE_POS_EVENT_51",7);

DEFINE("FRIEND_JOIN",1);

/**
 * 给添加应用的好友发信息
 *
 * @param unknown_type $param 模板参数
 * @param unknown_type $template 51模板的代号
 */
function send51UserAction($user,$object51,$param,$template){
	$friends51=$object51->api_client->friends_getAppUsers($user);
	$friendArray=array();
	foreach($friends51 as $friend){
		$friendArray[]=$friend[uid];
		if (count($friendArray) ==10)break;
	}
	$sendparam = array(
         "body_data"=>json_encode($param),
         "template_id"=>$template,
         "uids"=>$friendArray,
	);
	$ret=$object51->api_client->call_method("feed.publishTemplatizedAction",$sendparam);
	//throw new Exception($ret["result"]);
}

/**
 * 对添加应用 和 不添加应用的 两种好友 发不同的信息
 *
 * @param unknown_type $user
 * @param unknown_type $object51
 * @param unknown_type $param1
 * @param unknown_type $param2
 * @param unknown_type $template1
 * @param unknown_type $template2
 */
function send51UserActionDiff($user,$object51,$param1,$param2,$template1,$template2){
	$friends51=$object51->api_client->friends_getAppUsers($user);
	$friends51_2=$object51->api_client->friends_getAppUsers($user51);

	$friendArray=array();
	foreach($friends51 as $friend){
		$friendArray[]=$friend[uid];
	}

	$friendArray2=array();
	foreach($friends51_2 as $friend){
		$friendArray2[]=$friend[uid];
	}
	$invites=array_diff($friendArray,$friendArray2);


	$sendparam = array(
         "body_data"=>json_encode($param1),
         "template_id"=>$template1,
         "uids"=>$friendArray,
	);
	$object51->api_client->call_method("feed.publishTemplatizedAction",$sendparam);

	$sendparam2 = array(
         "body_data"=>json_encode($param2),
         "template_id"=>$template2,
         "uids"=>$invites,
	);
	$object51->api_client->call_method("feed.publishTemplatizedAction",$sendparam2);
	//throw new Exception($ret["result"]);
}
/**
 * 给所有的好友发信息
 *
 * @param unknown_type $user
 * @param unknown_type $object51
 * @param unknown_type $param
 * @param unknown_type $template
 */
function sendAll51UserAction($user,$object51,$param,$template){
	$friends51=$object51->api_client->friends_get($user);

	$friendArray=array();
	foreach($friends51 as $friend){
		$friendArray[]=$friend[uid];
	}
	$sendparam = array(
         "body_data"=>json_encode($param),
         "template_id"=>$template,
         "uids"=>$friendArray,
	);
	$ret=$object51->api_client->call_method("feed.publishTemplatizedAction",$sendparam);
	//throw new Exception($ret["result"]);
}

function add51event($uid,$tuid,$type,$time){
	sql_query("insert into log_51_event (uid,tuid,type,time,state,cid,cityname) values('$uid','$tuid','$type','$time',0,0,'') on duplicate key update state=0");
}

function get51FriendsEvent($uid){
	require("51SDK/appinclude.php");
	$ret=array();
	$relations=sql_fetch_rows("select l.id,s.passport,s.name as tname ,l.tuid,s.regtime as time from log_51_event l left join sys_user s on l.tuid=s.uid where l.type=".FRIEND_JOIN." and l.state=0  and l.uid='$uid' order by s.regtime desc");
	$index=0;
	foreach($relations as $relation){
		$userinfo=$OpenApp_51->api_client->users_getInfo(array($relation['passport']), array("username"));
		$relation['tnick'] =$userinfo[0]["username"];
		$ret[0][$index]=$relation;
		$index++;
	}
	sql_query("insert into sys_alarm (uid,friend) values ('$uid',1) on duplicate key update friend=0");
	return  $ret;
}

function remove51FriendsEvent($id){
	sql_query("delete from log_51_event where id='$id'");
}
function add51Friend($uid,$tuid,$id){
	$ret= array();
	$result=sql_query("insert into sys_user_relation (uid,tuid,type,time) values ('$uid','$tuid',0,unix_timestamp()) on duplicate key update type=0");
	if($result){
		remove51FriendsEvent($id);
	}
}
function invite51Friend51($uid){
	$friendsapp51=$OpenApp_51->api_client->friends_getAppUsers($user51);
	$friendsapp51uid=array();
	foreach($friendsapp51 as $one ){
		$friendsapp51uid[]=$one["uid"];
	}

	$friends51=$OpenApp_51->api_client->friends_get($user51);
	$friends51uid=array();
	foreach($friends51 as $one ){
		$friends51uid[]=$one["uid"];
	}

	$invites=array_diff($friends51uid,$friendsapp51uid);

	if(count($invites)>0){
		$ret=array();
		$ret[] = $OpenApp_51->api_client->call_method("users.invite", array("invitees"=>$invites,"reason"=>"aa"));
		return $ret;
	}else{
		$ret=array();
		$ret[]=array("result"=>0);
		return $ret;
	}

}

function add51FriendEvent($uid,$name){
	require("51SDK/appinclude.php");

	$friends=$OpenApp_51->api_client->friends_getAppUsers($user51);
	if(count($friends)==0)return;
	$friendsPassportArray=array();
	foreach($friends as $friend){
		$friendsPassportArray[]="'".$friend["uid"]."'";
	}
	$passPorts = implode(",",$friendsPassportArray);
	$friendsSanguo=sql_fetch_rows("select uid,name,regtime from sys_user where passport in ($passPorts)");
	foreach($friendsSanguo as $friend){
		if($uid!=$friend['uid'])
		add51event($friend['uid'],$uid,FRIEND_JOIN,$friend['regtime']);
		sql_query("update sys_alarm set friend=1 where uid='$friend[uid]'");
	}
	
	$sendparam=array("name"=>base64_encode($user51),"rolename"=>base64_encode($name),"appaddress"=>base64_encode(APPS51_ADDRESS),"serveraddress"=>base64_encode(SEVERS51_ADDRESS));
	//sendAll51UserAction($user51,$OpenApp_51,array("name"=>base64_encode($user51),"appaddress"=>base64_encode(APPS51_ADDRESS),"serveraddress"=>base64_encode(SEVERS51_ADDRESS) ),JOIN_EVENT_51);
	send51UserActionDiff($user51,$OpenApp_51,$sendparam,$sendparam,CITY_EVENT_51,JOIN_EVENT_51);
}

function add51StartWarEvent($enemyname){
	require_once("51SDK/appinclude.php");
	$enemyname=iconv("UTF-8", "GBK", $enemyname);
	sendAll51UserAction($user51,$OpenApp_51,array( "name"=>base64_encode($user51),"enemy"=>base64_encode($enemyname),"appaddress"=>base64_encode(APPS51_ADDRESS),"serveraddress"=>base64_encode(SEVERS51_ADDRESS) ) ,DECLARE_WAR_EVENT_51);
}

function add51GoodsEvent($goodsname,$goodsvalue){
	require_once("51SDK/appinclude.php");
	$goodsname=iconv("UTF-8", "GBK", $goodsname);
	sendAll51UserAction($user51,$OpenApp_51,array("name"=>base64_encode($user51),"goods"=>base64_encode($goodsname),"count"=>base64_encode($goodsvalue),"appaddress"=>base64_encode(APPS51_ADDRESS),"serveraddress"=>base64_encode(SEVERS51_ADDRESS) ),GOODS_EVENT_51);

}

function add51HeroEvent($heroname){
	require_once("51SDK/appinclude.php");
	$heroname=iconv("UTF-8", "GBK", $heroname);
	sendAll51UserAction($user51,$OpenApp_51,array("name"=>base64_encode($user51),"heroname"=>base64_encode($heroname),"appaddress"=>base64_encode(APPS51_ADDRESS),"serveraddress"=>base64_encode(SEVERS51_ADDRESS) ),HERO_EVENT_51);
}

function add51NobilityEvent($nobilityname){
	require_once("51SDK/appinclude.php");
	$nobilityname=iconv("UTF-8", "GBK", $nobilityname);
	sendAll51UserAction($user51,$OpenApp_51,array("name"=>base64_encode($user51),"nobilityname"=>base64_encode($nobilityname),"appaddress"=>base64_encode(APPS51_ADDRESS),"serveraddress"=>base64_encode(SEVERS51_ADDRESS) ),NOBILITY_EVENT_51);
}
function add51OfficePosEvent($officepos){
	require_once("51SDK/appinclude.php");
	$officepos=iconv("UTF-8", "GBK", $officepos);
	sendAll51UserAction($user51,$OpenApp_51,array("name"=>base64_encode($user51),"pos"=>base64_encode($officepos),"appaddress"=>base64_encode(APPS51_ADDRESS),"serveraddress"=>base64_encode(SEVERS51_ADDRESS) ),OFFICE_POS_EVENT_51);
}
function add51CreateCityEvent($name){
	require_once("51SDK/appinclude.php");
	$name=iconv("UTF-8", "GBK", $name);
	$sendparam=array("name"=>base64_encode($user51),"rolename"=>base64_encode($name),"appaddress"=>base64_encode(APPS51_ADDRESS),"serveraddress"=>base64_encode(SEVERS51_ADDRESS));
	send51UserActionDiff($user51,$OpenApp_51,$sendparam,$sendparam,CITY_EVENT_51,JOIN_EVENT_51);
}

function get51passport($uid){
	return sql_query_one_cell ("select passport from sys_user where uid='$uid'");
}
?>