<?php
require_once("./interface.php");
require_once("./utils.php");
require_once("./HeroFunc.php");
require_once("./HotelFunc.php");

define("TITLE_TRICK",23);
define("TITLE_SHUNTENGMOGUA",30);
function getTrickList($uid,$param)
{
	$ret = array();
	$ret[] = sql_fetch_rows("select * from cfg_trick order by id");
	$ret[] = sql_fetch_one_cell("select sum(count) from sys_goods where uid='$uid' and gid=13");
	return $ret;
}
//草木皆兵  城池显示军队人数为真实的5~10倍。持续wisdom*10分钟。
function trickCaoMuJieBin($uid,$cid,$wisdom)
{
	$rate = mt_rand(5,10);
	$delay = $wisdom * 600;
	$myuid = sql_fetch_one_cell("select uid from sys_city where cid=$cid");
	if ($uid != $myuid) {
		throw Exception($GLOBALS['waigua']['forbidden']);
	}
	sql_query("insert into mem_city_buffer (cid,buftype,bufparam,endtime) values ('$cid',1,$rate,unix_timestamp()+$delay) on duplicate key update endtime=endtime+$delay,bufparam=$rate");

	$endtime = sql_fetch_one_cell("select endtime from mem_city_buffer where cid='$cid' and buftype=1");
	if(!empty($myuid))
		completeTaskGoalBySortandType($uid, 53, 1);

	return sprintf($GLOBALS['trickCaoMuJieBin']['succ'],$rate,MakeEndTime($endtime));
}
//空城计  城池显示军队人数为真实的10%~20%。持续wisdom*10分钟。   
function trickKongCheng($uid,$cid,$wisdom)
{
	$rate = mt_rand(10,20);
	$delay = $wisdom * 600;
	$myuid = sql_fetch_one_cell("select uid from sys_city where cid=$cid");
	if ($uid != $myuid) {
		throw Exception($GLOBALS['waigua']['forbidden']);
	}
	sql_query("insert into mem_city_buffer (cid,buftype,bufparam,endtime) values ('$cid',2,$rate,unix_timestamp()+$delay) on duplicate key update endtime=endtime+$delay,bufparam=$rate");

	$endtime = sql_fetch_one_cell("select endtime from mem_city_buffer where cid='$cid' and buftype=2");
	if(!empty($myuid))
		completeTaskGoalBySortandType($uid, 53, 2);
	return sprintf($GLOBALS['trickKongCheng']['succ'],$rate,MakeEndTime($endtime));

}
//抛砖引玉 城池显示资源为真实的5~10。持续wisdom*10分钟。
function trickPaoZhuangYingYu($uid,$cid,$wisdom)
{
	$rate = mt_rand(5,10);
	$delay = $wisdom * 600;
	
	$myuid = sql_fetch_one_cell("select uid from sys_city where cid=$cid");
	if ($uid != $myuid) {
		throw Exception($GLOBALS['waigua']['forbidden']);
	}
	sql_query("insert into mem_city_buffer (cid,buftype,bufparam,endtime) values ('$cid',3,$rate,unix_timestamp()+$delay) on duplicate key update endtime=endtime+$delay,bufparam=$rate");


	$endtime = sql_fetch_one_cell("select endtime from mem_city_buffer where cid='$cid' and buftype=3");
	if(!empty($myuid))
		completeTaskGoalBySortandType($uid, 53, 3);
	
	return sprintf($GLOBALS['trickPaoZhuangYingYu']['succ'],$rate,MakeEndTime($endtime));

}
//坚壁清野  城池显示资源为真实的10%~20% 持续wisdom*10分钟。  
function trickJinBiQingYe($uid,$cid,$wisdom)
{
	$rate = mt_rand(10,20);
	$delay = $wisdom * 600;
	$myuid = sql_fetch_one_cell("select uid from sys_city where cid=$cid");
	if ($uid != $myuid) {
		throw Exception($GLOBALS['waigua']['forbidden']);
	}
	sql_query("insert into mem_city_buffer (cid,buftype,bufparam,endtime) values ('$cid',4,$rate,unix_timestamp()+$delay) on duplicate key update endtime=endtime+$delay,bufparam=$rate");

	$endtime = sql_fetch_one_cell("select endtime from mem_city_buffer where cid='$cid' and buftype=4");
	if(!empty($myuid))
		completeTaskGoalBySortandType($uid, 53, 4);
	return sprintf($GLOBALS['trickJinBiQingYe']['succ'],$rate,MakeEndTime($endtime));
}
//暗度陈仓 打破敌人封锁，可以从被敌人围困的城池内调动军队出城。
function trickAnDuChenChang($uid,$cid,$wisdom)
{
	$delay = $wisdom * 60;
	$myuid = sql_fetch_one_cell("select uid from sys_city where cid=$cid");
	if ($uid != $myuid) {
		throw Exception($GLOBALS['waigua']['forbidden']);
	}
	sql_query("insert into mem_city_buffer(cid,buftype,bufparam,endtime) values ('$cid','5',0,unix_timestamp()+$delay) on duplicate key update endtime=endtime+$delay");
	$endtime = sql_fetch_one_cell("select endtime from mem_city_buffer where cid='$cid' and buftype=5");
	
	if(!empty($myuid))
		completeTaskGoalBySortandType($uid, 53, 5);
	return sprintf($GLOBALS['trickAnDuChenChang']['succ'],MakeEndTime($endtime));

}
//成功后，立刻降低城池民心。根据敌我双方智谋差，每差10点智谋可降低1点民心，最多降低60点。中计的城池在24小时内不会再次中计。说明：对方会得到警报。任何将领使用，对非盟友城池有效。该计谋只能对敌人的城池使用。
function trickYaoYinHuoZhong($uid,$targetcid,$wisdom)
{
	$user=sql_fetch_one("select name,lastcid from sys_user where uid='$uid'");
	$username = $user['name'];
	$targetcity = getCityNamePosition($targetcid);
	$targetuid = sql_fetch_one_cell("select uid from sys_city where cid='$targetcid'");
	if ($uid == $targetuid) {
		throw new Exception($GLOBALS['waigua']['forbidden']);
	}
	$delta = sql_fetch_one_cell("select unix_timestamp() - last_trick_morale from mem_city_schedule where cid='$targetcid'");
	if ($delta < 86400)
	{
		$caution = sprintf($GLOBALS['trickYaoYinHuoZhong']['fail_caution'],$username,$targetcity);
		sendReport($targetuid,"trick",TITLE_TRICK,$user['lastcid'],$targetcid,$caution);
		return sprintf($GLOBALS['trickYaoYinHuoZhong']['fail'],$targetcity);
	}
	$morale_reduce = ceil($wisdom / 10.0);
	$morale_reduce = min($morale_reduce,60);//最多降低60点民心
	$citymorale = sql_fetch_one_cell("select morale from mem_city_resource where cid='$targetcid'");
	if ($citymorale < $morale_reduce) $morale_reduce = $citymorale;

	sql_query("update mem_city_resource set morale=morale-$morale_reduce where cid='$targetcid'");
	sql_query("update mem_city_resource set `people_stable`=`people_max` * morale * 0.01  where cid='$targetcid'");

	 
	sql_query("update mem_city_schedule set last_trick_morale=unix_timestamp() where cid='$targetcid'");
	 
	$caution = sprintf($GLOBALS['trickYaoYinHuoZhong']['succ_caution'],
	$username,$targetcity,$targetcity,$morale_reduce);
	sendReport($targetuid,"trick",TITLE_TRICK,$user['lastcid'],$targetcid,$caution);

	completeTaskGoalBySortandType($uid, 53, 6);
	return sprintf($GLOBALS['trickYaoYinHuoZhong']['succ'],$targetcity,$morale_reduce);

}
//趁火打劫，降低中计城池内仓库对资源的保护能力，掠夺时可以获得更多的资源。15点智谋降低仓库保护能力1%，15点智谋可持续1分钟。效果可与抢掠技巧叠加。中计的城池在24小时内不会再次中计。
function trickChenHuoDaJie($uid,$targetcid,$wisdom)
{
	$user=sql_fetch_one("select name,lastcid from sys_user where uid='$uid'");
	$username = $user['name'];
	$targetcity = getCityNamePosition($targetcid);
	$targetuid = sql_fetch_one_cell("select uid from sys_city where cid='$targetcid'");
	if ($uid == $targetuid) {
		throw Exception($GLOBALS['waigua']['forbidden']);
	}
	$delta = sql_fetch_one_cell("select unix_timestamp() - last_trick_chenhuodajie from mem_city_schedule where cid='$targetcid'");
	if ($delta < 86400)
	{
		$caution = sprintf($GLOBALS['trickChenHuoDaJie']['fail_caution'],$username,$targetcity);
		sendReport($targetuid,"trick",TITLE_TRICK,$user['lastcid'],$targetcid,$caution);
		return sprintf($GLOBALS['trickChenHuoDaJie']['fail'],$targetcity);
	}

	//15点智谋持续一分钟
	$delay = $wisdom * 4;
	//15点智谋降低百分之一的保护能力
	$param = ceil($wisdom /15.0);
	sql_query("insert into mem_city_buffer (cid,buftype,bufparam,endtime) values ('$targetcid',15,'$param',unix_timestamp()+$delay) on duplicate key update endtime=endtime+$delay,bufparam='$param'");
	sql_query("update mem_city_schedule set last_trick_chenhuodajie=unix_timestamp() where cid='$targetcid'");
	$caution = sprintf($GLOBALS['trickChenHuoDaJie']['succ_caution'],
	$username,$targetcity,$param."%",ceil($delay/60));
	sendReport($targetuid,"trick",TITLE_TRICK,$user['lastcid'],$targetcid,$caution);

	completeTaskGoalBySortandType($uid, 53, 15);
	return sprintf($GLOBALS['trickChenHuoDaJie']['succ'],$targetcity,  $param."%" ,ceil($delay/60));
}
//围魏救赵，从中计城池出发的军队，如果处在前进状态，则立即被强制召回。中计的城池在24小时内不会再次中计。
function trickWeiWeiJiuZhao($uid,$targetcid,$wisdom)
{
	$user=sql_fetch_one("select name,lastcid from sys_user where uid='$uid'");
	$username = $user['name'];
	$targetcity = getCityNamePosition($targetcid);
	$targetuid = sql_fetch_one_cell("select uid from sys_city where cid='$targetcid'");
	if ($uid == $targetuid) {
		throw Exception($GLOBALS['waigua']['forbidden']);
	}
	$delta = sql_fetch_one_cell("select unix_timestamp() - last_trick_weiweijiuzhao from mem_city_schedule where cid='$targetcid'");
	//24小时内不会再中计
	if ($delta < 86400)
	{
		$caution = sprintf($GLOBALS['trickWeiWeiJiuZhao']['fail_caution'],$username,$targetcity);
		sendReport($targetuid,"trick",TITLE_TRICK,$user['lastcid'],$targetcid,$caution);
		return sprintf($GLOBALS['trickWeiWeiJiuZhao']['fail'],$targetcity);
	}
	$troops = sql_fetch_rows("select id from sys_troops where cid='$targetcid' and state=0");

	foreach($troops as $troop)  {
		sql_query("update sys_troops set `state`=1,endtime=unix_timestamp() - starttime + unix_timestamp(),starttime=unix_timestamp() where id='$troop[id]'");
	}
	sql_query("update mem_city_schedule set last_trick_weiweijiuzhao=unix_timestamp() where cid='$targetcid'");

	$caution = sprintf($GLOBALS['trickWeiWeiJiuZhao']['succ_caution'],$username);
	sendReport($targetuid,"trick",TITLE_TRICK,$user['lastcid'],$targetcid,$caution);
	 

	completeTaskGoalBySortandType($uid, 53, 17);
	return $GLOBALS['trickWeiWeiJiuZhao']['succ'];

}

function trickHuDiChouXin($uid,$targetcid,$wisdom){
	$user=sql_fetch_one("select name,lastcid from sys_user where uid='$uid'");
	$username = $user['name'];
	$targetcity = getCityNamePosition($targetcid);
	$targetuid = sql_fetch_one_cell("select uid from sys_city where cid='$targetcid'");
	if ($uid == $targetuid) {
		throw Exception($GLOBALS['waigua']['forbidden']);
	}
	$delta = sql_fetch_one_cell("select unix_timestamp() - last_trick_hudichouxin from mem_city_schedule where cid='$targetcid'");
	//24小时内不会再中计
	if ($delta < 86400)
	{
		$caution = sprintf($GLOBALS['trickHuDiChouXin']['fail_caution'],$username,$targetcity);
		sendReport($targetuid,"trick",TITLE_TRICK,$user['lastcid'],$targetcid,$caution);
		return sprintf($GLOBALS['trickHuDiChouXin']['fail'],$targetcity);
	}
	/*
	>>> sys_troops 
	 `task` tinyint(4) NOT NULL default '0' COMMENT '任务:\r\n0：运输\r\n1：派遣\r\n2：侦察\r\n3：抢掠\r\n4：占领\r\n5：城内士兵反击（被进攻时）\r\n6：义兵起义 7: 前往某个副本  8 派遣到副本中某个据点 9: 攻击副本中某个地方军队',
	  `state` smallint(6) NOT NULL default '0' COMMENT '状态\r\n0：行进\r\n1：返回\r\n2：等待战斗\r\n3：战斗中\r\n4：驻军,10驻军在战场 据点，11 战场据点前进中，12战场据点攻击中',
	>>> sys_city_hero
	   `state` tinyint(3) NOT NULL default '0' COMMENT '状态：0，空闲；1,城守；2,出征，3,战斗,4，驻守,5,俘虏, 6,投奔， 7,主将, 8,军师 9,流亡,10 历练,11 历练返回',
	*/
	$troops = sql_fetch_rows("select id,state,noback from sys_troops where cid='$targetcid' and state in (0,2,4) and hid > 0 and hid<2000");
	foreach($troops as $troop){
		$troopid =$troop["id"];
		$param=array($troopid);		
		if (sql_check("select * from sys_gather where troopid='$troopid'")) //取消采集
			sql_query("delete from sys_gather where troopid='$troopid'");
		if ($troop['state']==0&&$troop['noback'] > 0) //取消关门打狗
			sql_query("update sys_troops set noback=0 where id='$troopid'");			
		callBackTroop($targetuid,$param);
	}
	require_once 'HeroFunc.php';
	$heros=sql_fetch_rows("select hid,state from sys_city_hero where cid='$targetcid' and hid > 0 and hid<2000");
	foreach($heros as $hero)  {
		$state=$hero["state"];
		$hid=$hero["hid"];
		if ($state==10){
			$param=array($hid);
			cancelHeroExpr($targetuid,$targetcid,$param);
		}
		
	}
	
	sql_query("update mem_city_schedule set last_trick_hudichouxin=unix_timestamp() where cid='$targetcid'");

	$caution = sprintf($GLOBALS['trickHuDiChouXin']['succ_caution'],$username);
	sendReport($targetuid,"trick",TITLE_TRICK,$user['lastcid'],$targetcid,$caution);
	 
	
	return $GLOBALS['trickHuDiChouXin']['succ'];	
}
//焚烧粮草。烧毁中计城池内存放的粮食。15点智谋可烧毁粮食1%。中计的城池在6小时内不会再次中计。
function trickFenShaoLiangCao($uid,$targetcid,$wisdom)
{
	$user=sql_fetch_one("select name,lastcid from sys_user where uid='$uid'");
	$username = $user['name'];
	$targetcity = getCityNamePosition($targetcid);
	$targetuid = sql_fetch_one_cell("select uid from sys_city where cid='$targetcid'");
	if ($uid == $targetuid) {
		throw Exception($GLOBALS['waigua']['forbidden']);
	}
	$delta = sql_fetch_one_cell("select unix_timestamp() - last_trick_fenshaoliangcao from mem_city_schedule where cid='$targetcid'");
	//6小时内不会再中计
	if ($delta < 21600)
	{
		$caution = sprintf($GLOBALS['trickFenShaoLiangCao']['fail_caution'],$username,$targetcity);
		sendReport($targetuid,"trick",TITLE_TRICK,$user['lastcid'],$targetcid,$caution);
		return sprintf($GLOBALS['trickFenShaoLiangCao']['fail'],$targetcity);
	}

	//15点智谋烧毁1%
	$food_remove = ($wisdom /15)*0.01;
	//减粮草
	$food = sql_fetch_one_cell("select food from mem_city_resource where cid='$targetcid'");
	
	$remove=ceil($food*$food_remove);
	addCityResources($targetcid,0,0,0,(0-$remove),0);

	sql_query("update mem_city_schedule set last_trick_fenshaoliangcao=unix_timestamp() where cid='$targetcid'");
	$caution = sprintf($GLOBALS['trickFenShaoLiangCao']['succ_caution'],$username,$targetcity,$remove);
	sendReport($targetuid,"trick",TITLE_TRICK,$user['lastcid'],$targetcid,$caution);
	return sprintf($GLOBALS['trickFenShaoLiangCao']['succ'],$remove);

}
//虚张声势。显示出征军队人数为真实的5~10倍。1点智谋可持续10分钟。连续使用时间延长。
function trickXuZhangShengShi($uid,$troop,$wisdom)
{
		
	$delay = $wisdom * 600;
	$rate = mt_rand(5,10);	
	sql_query("insert into mem_troops_buffer (troopid,buftype,bufparam,endtime) values ($troop[id],21,$rate,unix_timestamp()+$delay) on duplicate key update endtime=endtime+$delay,bufparam=$rate");
	$endtime = sql_fetch_one_cell("select endtime from mem_troops_buffer where troopid='$troop[id]' and buftype=21");
	completeTaskGoalBySortandType($uid, 53, 18);
	return sprintf($GLOBALS['trickXuZhangShengShi']['succ'],$rate,MakeEndTime($endtime));
}

//偃旗息鼓。显示出征军队人数为真实的10%~20%。1点智谋可持续10分钟。连续使用时间延长。
function trickYanQiXiGu($uid,$troop,$wisdom)
{
	$delay = $wisdom * 600;
	$rate = mt_rand(10,20);	
	sql_query("insert into mem_troops_buffer (troopid,buftype,bufparam,endtime) values ($troop[id],22,$rate,unix_timestamp()+$delay) on duplicate key update endtime=endtime+$delay,bufparam=$rate");
	$endtime = sql_fetch_one_cell("select endtime from mem_troops_buffer where troopid='$troop[id]' and buftype=22");
	completeTaskGoalBySortandType($uid, 53, 19);
	return sprintf($GLOBALS['trickYanQiXiGu']['succ'],$rate.'%',MakeEndTime($endtime));
}
//诱敌深入。引诱敌军加速冒进，打乱敌人的攻击计划。1点智谋可加快敌军行军速度1%。中计军队在1小时内不会再次中计。
function trickYouDiShenRu($uid,$troop,$wisdom)
{	
	$now = sql_fetch_one_cell("select unix_timestamp()");
	$user = sql_fetch_one("select name,prestige,lastcid from sys_user where uid='$uid'");
	$username=$user['name'];
	
	if ($now - $troop['lastTempt'] < 3600)
	{
		$caution = sprintf($GLOBALS['trickYouDiShenRu']['fail_caution'],$username);
		sendReport($troop['uid'],'trick',23,$user['lastcid'],0,$caution);
		return $GLOBALS['trickYouDiShenRu']['fail'];
	}
	if ($troop['hid'] > 0)
	{
		$hero = sql_fetch_one("select * from sys_city_hero where hid='$troop[hid]'");
		if (!empty($hero))
		{
			if (mt_rand(1,$wisdom + $hero['wisdom_base']+$hero['wisdom_add']) > $wisdom)
			{
				 
				$caution = sprintf($GLOBALS['trickYouDiShenRu']['fail_caution'],$username);
				sendReport($troop['uid'],'trick',23,$user['lastcid'],0,$caution);
				return $GLOBALS['useTrick']['fail_no_wisdom'];
			}
		}
	}
	
	 
	$addrate=(100+$wisdom)/100;
	sql_query("update sys_troops set endtime=unix_timestamp()+(endtime-unix_timestamp())/$addrate,lastTempt=unix_timestamp() where id='$troop[id]'");
	
	$caution = sprintf($GLOBALS['trickYouDiShenRu']['succ_caution'],$username);
	sendReport($troop['uid'],'trick',TITLE_TRICK,$user['lastcid'],0,$caution);
	
	completeTaskGoalBySortandType($uid, 53, 20);
	return sprintf($GLOBALS['trickYouDiShenRu']['succ']);	
}
//顺藤摸瓜，获得对方所有城池的位置
function trickShunTengMoGua($uid,$targetcid,$wisdom)
{
	$user=sql_fetch_one("select name,lastcid from sys_user where uid='$uid'");
	$username = $user['name'];
	$targetcity = getCityNamePosition($targetcid);
	$targetuid = sql_fetch_one_cell("select uid from sys_city where cid='$targetcid'");
	if ($uid == $targetuid) {
		throw Exception($GLOBALS['waigua']['forbidden']);
	}
	//选出所有城池的位置
	$citys =sql_fetch_rows("select cid,name from sys_city where uid='$targetuid'");
	 
	$msg=$GLOBALS['trickShunTengMoGua']['report_first_line'];
	foreach($citys as $city){
		$cid=$city['cid'];
		$y = floor($cid / 1000);
		$x = ($cid % 1000);
		$heroinfos = sql_fetch_rows ( "select name from sys_city_hero where cid='$cid'" );
		$msg.=sprintf($GLOBALS['trickShunTengMoGua']['report_city'] ,$city['name'],$x,$y);
		if(!empty($heroinfos)){
		  foreach($heroinfos as $heroinfo){
			  $msg.='　　　　将领：'.$heroinfo['name'].'<br/>';
		    }	
		}
	}

	sendReport($targetuid,"trick",TITLE_TRICK,$user['lastcid'],$targetcid,sprintf($GLOBALS['trickShunTengMoGua']['alarm'],$username));
	sendReport($uid,"trick",TITLE_SHUNTENGMOGUA,$user['lastcid'],$targetcid,$msg);

	completeTaskGoalBySortandType($uid, 53, 14);
	return $GLOBALS['trickShunTengMoGua']['succ'];

}

//蛛丝马迹，获得对方军队的状况
function trickZhuSiMaji($uid,$targetcid,$wisdom)
{
	$user=sql_fetch_one("select name,lastcid from sys_user where uid='$uid'");
	$username = $user['name'];
	$targetcity = getCityNamePosition($targetcid);
	$targetuid = sql_fetch_one_cell("select uid from sys_city where cid='$targetcid'");
	if ($uid == $targetuid) {
		throw Exception($GLOBALS['waigua']['forbidden']);
	}
	//选出所有城池的位置
	$troops =sql_fetch_rows("select t.task,t.cid,t.targetcid,t.endtime, c.name,c2.name as tocityname from sys_troops t left join sys_city c on t.cid=c.cid left join sys_city c2 on t.targetcid=c2.cid where t.cid='$targetcid' or t.targetcid='$targetcid';");
	 
	$content=$GLOBALS['trickZhuSiMaJi']['table_start'];
	foreach($troops as $troop)
	{
		$content.=$GLOBALS['trickZhuSiMaJi']['tr_start'];
		$cid=$troop['cid'];
		$y = floor($cid / 1000);
		$x = ($cid % 1000);
		if($troop['task']==0){
			$troop['task']=$GLOBALS['StartTroop']['transport'];
		}else if($troop['task']==1){
			$troop['task']=$GLOBALS['StartTroop']['send'];
		}else if($troop['task']==2){
			$troop['task']=$GLOBALS['StartTroop']['detect'];
		}else if($troop['task']==3){
			$troop['task']=$GLOBALS['StartTroop']['harry'];
		}else if($troop['task']==4){
			$troop['task']=$GLOBALS['StartTroop']['occupy'];
		}else if($troop['task']==5){
			$troop['task']=$GLOBALS['StartTroop']['fanji'];
		}else if($troop['task']==6){
			$troop['task']=$GLOBALS['StartTroop']['qiyi'];
		}
		$content.=sprintf($GLOBALS['trickZhuSiMaJi']['td'] , $troop['task']);
		$content.=sprintf($GLOBALS['trickZhuSiMaJi']['td'] , $troop['name']);
		$content.=sprintf($GLOBALS['trickZhuSiMaJi']['td'] ,  "[".$x.",".$y."]");

		$troop_targetcid=$troop['targetcid'];
		$y = floor($troop_targetcid / 1000);
		$x = ($troop_targetcid % 1000);
		if(empty($troop['tocityname'])){
			$troop['tocityname']=$GLOBALS['fileName']['0'];
		}
		$content.=sprintf($GLOBALS['trickZhuSiMaJi']['td'] , $troop['tocityname']);
		$content.=sprintf($GLOBALS['trickZhuSiMaJi']['td'] , "[".$x.",".$y."]");

		$content.=sprintf($GLOBALS['trickZhuSiMaJi']['td'] , MakeEndTime($troop['endtime']));
		$content.=$GLOBALS['trickZhuSiMaJi']['tr_end'];
	}
	$content.=$GLOBALS['trickZhuSiMaJi']['table_end'];
	sendReport($uid,"trick",32,$user['lastcid'],$targetcid,$content);
	sendReport($targetuid,"trick",TITLE_TRICK,$user['lastcid'],$targetcid,sprintf($GLOBALS['trickZhuSiMaJi']['alarm'],$username));
	completeTaskGoalBySortandType($uid, 53, 16);
	return $GLOBALS['trickZhuSiMaJi']['succ'];

}


//挑拨离间：成功后，降低对方城池内一个随机将领的忠诚。 10点智谋可降低1点忠诚
function trickTiaoBoLiJian($uid,$targetcid,$wisdom)
{
	$user=sql_fetch_one("select name,lastcid from sys_user where uid='$uid'");
	$username = $user['name'];
	$targetcity = getCityNamePosition($targetcid);
	$targetuid = sql_fetch_one_cell("select uid from sys_city where cid='$targetcid'");
	if ($uid == $targetuid) {
		throw Exception($GLOBALS['waigua']['forbidden']);
	}
	$ret = "";
	$caution = "";
	$targethero = sql_fetch_one("select * from sys_city_hero where cid='$targetcid' and uid='$targetuid' and (state=0 or state=1 or state=7 or state=8) order by rand() limit 1");        //在城里的将领
	if (empty($targethero)) //无人中计
	{
		$ret = $GLOBALS['trickTiaoBoLiJian']['fail_nohero'];
		$caution = $targetcity.$GLOBALS['trickTiaoBoLiJian']['fail_caution_nohero'];
	}
	else
	{
		$last_trick = sql_fetch_one_cell("select last_trick from mem_hero_schedule where hid='$targethero[hid]'");
		$now = sql_fetch_one_cell("select unix_timestamp()");
		//24小时冷却时间
		if ($now - $last_trick < 86400)
		{
			$ret = $GLOBALS['trickTiaoBoLiJian']['fail'];
			$caution = sprintf($GLOBALS['trickTiaoBoLiJian']['fail_caution'],$username,$targetcity);
		}
		else
		{
			//对比一下智谋      
			if (mt_rand(1,$wisdom + $targethero['wisdom_base']+$targethero['wisdom_add']) > $wisdom)
			{
				$ret = $GLOBALS['useTrick']['fail_no_wisdom'];
				$caution = sprintf($GLOBALS['trickTiaoBoLiJian']['fail_caution'],$username,$targetcity);
			}
			else//中计了
			{
				$loyalty_reduce = ceil($wisdom / 10);
				if ($loyalty_reduce>80) {//最多降低60点忠诚
					$loyalty_reduce=80;
				}
				
				$ret = $GLOBALS['trickTiaoBoLiJian']['succ'];
				$caution = sprintf($GLOBALS['trickTiaoBoLiJian']['succ_caution'],$username,$targetcity);
				sql_query("update sys_city_hero set loyalty=GREATEST(0,loyalty-($loyalty_reduce)) where hid='$targethero[hid]'");


				/*
				 if ($targethero['loyalty'] <= $loyalty_reduce)  //忠诚减到0了,可以招降了
				 {
				 $loyalty_reduce = $targethero['loyalty'];
				 $mycid = sql_fetch_one_cell("select lastcid from sys_user where uid='$uid'");
				 $myofficelevel = sql_fetch_one_cell("select level from sys_building where cid='$mycid' and bid=".ID_BUILDING_OFFICE." limit 1");
				 $mycityheroCount = sql_fetch_one_cell("select count(*) from sys_city_hero where cid='$mycid' and uid='$uid'");
				 if ($myofficelevel - $mycityheroCount > 0)  //有空位
				 {
				 sql_query("update sys_city_hero set uid='$uid',cid='$mycid',state=0,loyalty=10 where hid='$targethero[hid]'");
				 if ($targethero['state'] == 1)  //城守
				 {
				 sql_query("update sys_city set chiefhid=0 where cid='$targetcid'");
				 sql_query("update mem_city_resource set chief_loyalty=0 where cid='$targetcid'");
				 }

				 $ret .= sprintf($GLOBALS['trickTiaoBoLiJian']['succ_surrender'],$targethero['name']);
				 $caution .= sprintf($GLOBALS['trickTiaoBoLiJian']['succ_caution_surrender'],$targethero['name']);
				 }
				 else
				 {
				 $ret .= sprintf($GLOBALS['trickTiaoBoLiJian']['succ_nooffice'],$targethero['name']);
				 $caution .= sprintf($GLOBALS['trickTiaoBoLiJian']['succ_caution_nooffice'],$targethero['name']);
				 }
				 }
				 else
				 */
				{
					$ret .= sprintf($GLOBALS['trickTiaoBoLiJian']['succ_reduceloyalty'],$targethero['name'],$loyalty_reduce);
					$caution .= sprintf($GLOBALS['trickTiaoBoLiJian']['succ_caution_reduceloyalty'],$targethero['name'],$loyalty_reduce);
				}
				$caution .= $GLOBALS['trickTiaoBoLiJian']['succ_caution_tail'];
				sql_query("insert into mem_hero_schedule (hid,last_trick) values ('$targethero[hid]',unix_timestamp()) on duplicate key update last_trick=unix_timestamp()");
			}
		}
	}
	sendReport($targetuid,"trick",TITLE_TRICK,$user['lastcid'],$targetcid,$caution);
	completeTaskGoalBySortandType($uid, 53, 7);
	return $ret;
}
//十面埋伏：中计的敌人城池无法调动军队出城。1点智谋可持续10分钟。连续使用时间延长。
function trickShiMianMaiFu($uid,$targetcid,$wisdom)
{
	$user=sql_fetch_one("select name,lastcid from sys_user where uid='$uid'");
	$username = $user['name'];
	$targetcity = getCityNamePosition($targetcid);
	$targetuid = sql_fetch_one_cell("select uid from sys_city where cid='$targetcid'");
	if ($uid == $targetuid) {
		throw Exception($GLOBALS['waigua']['forbidden']);
	}	 
	$delta = sql_fetch_one_cell("select unix_timestamp() - last_trick_maifu from mem_city_schedule where cid='$targetcid'");
	if ($delta < 3600*24)
	{
		$caution = sprintf($GLOBALS['trickShiMianMaiFu']['fail_caution'],$username,$targetcity);
		sendReport($targetuid,"trick",TITLE_TRICK,$user['lastcid'],$targetcid,$caution);
		return sprintf($GLOBALS['trickShiMianMaiFu']['fail'],$targetcity);
	}

	$delay = $wisdom * 60;

	sql_query("insert into mem_city_buffer (cid,buftype,bufparam,endtime) values ('$targetcid',8,0,unix_timestamp()+'$delay') on duplicate key update endtime=`endtime`+'$delay'");

	$endtime = sql_fetch_one_cell("select endtime from mem_city_buffer where cid='$targetcid' and buftype=8");
	sql_query("update mem_city_schedule set last_trick_maifu=unix_timestamp() where cid='$targetcid'");

	$ret = sprintf($GLOBALS['trickShiMianMaiFu']['succ'],$targetcity,MakeTimeLeft($delay));
	$caution = sprintf($GLOBALS['trickShiMianMaiFu']['succ_caution'],$username,$targetcity,$targetcity,MakeTimeLeft($delay),MakeEndTime($endtime));

	sendReport($targetuid,"trick",TITLE_TRICK,$user['lastcid'],$targetcid,$caution);
	completeTaskGoalBySortandType($uid, 53, 8);
	return $ret;
}

//金蝉脱壳：让军队快速返回，缩短返回时间。1点智谋可缩短时间1分钟
function trickJinChaoTuoQiao($uid,$troop,$wisdom)
{
	if ($troop['state'] == 0)   //前进中的先返回
	{
		sql_query("update sys_troops set `state`=1,endtime=unix_timestamp() - starttime + unix_timestamp(),starttime=unix_timestamp() where id='$troop[id]'");
	}
	$delay = $wisdom * 60;
	sql_query("update sys_troops set endtime=endtime-'$delay',lastrun=unix_timestamp() where id='$troop[id]'");

	completeTaskGoalBySortandType($uid, 53, 10);
	return $GLOBALS['trickJinChaoTuoQiao']['succ'];
}
//八门金锁：将一支敌人军队困住，使其行军时间延长。1点智谋可延长时间1分钟。中计军队在6小时内不会再次中计。
function trickBaMemJinShuo($uid,$troop,$wisdom)
{
	$now = sql_fetch_one_cell("select unix_timestamp()");
	$user = sql_fetch_one("select name,prestige,lastcid from sys_user where uid='$uid'");
	$username=$user['name'];
	if ($now - $troop['lastlock'] < 6 * 3600)
	{
		$caution = sprintf($GLOBALS['trickBaMemJinShuo']['fail_caution'],$username);
		sendReport($troop['uid'],'trick',23,$user['lastcid'],0,$caution);
		return $GLOBALS['trickBaMemJinShuo']['fail'];
	}
	if ($troop['hid'] > 0)
	{
		$hero = sql_fetch_one("select * from sys_city_hero where hid='$troop[hid]'");
		if (!empty($hero))
		{
			if (mt_rand(1,$wisdom + $hero['wisdom_base']+$hero['wisdom_add']) > $wisdom)
			{
				 
				$caution = sprintf($GLOBALS['trickBaMemJinShuo']['fail_caution'],$username);
				sendReport($troop['uid'],'trick',23,$user['lastcid'],0,$caution);
				return $GLOBALS['useTrick']['fail_no_wisdom'];
			}
		}
	}
	 
	$delay = $wisdom * 60;
	sql_query("update sys_troops set endtime=endtime + $delay,lastlock=unix_timestamp() where id='$troop[id]'");
	 
	$caution = sprintf($GLOBALS['trickBaMemJinShuo']['succ_caution'],$username,MakeTimeLeft($delay));
	sendReport($troop['uid'],'trick',23,$user['lastcid'],0,$caution);
	 
	completeTaskGoalBySortandType($uid, 53, 11);
	return sprintf($GLOBALS['trickBaMemJinShuo']['succ'],MakeTimeLeft($delay));
	 
}
//关门打狗
function trickGuanMemDaGou($uid,$troop,$wisdom)
{
	$user=sql_fetch_one("select name,lastcid from sys_user where uid='$uid'");
	$username = $user['name'];
	if ($troop['hid'] > 0)
	{
		$hero = sql_fetch_one("select * from sys_city_hero where hid='$troop[hid]'");
		if (!empty($hero))
		{
			if (mt_rand(1,$wisdom + $hero['wisdom_base']+$hero['wisdom_add']) > $wisdom)
			{
				$caution = sprintf($GLOBALS['trickGuanMemDaGou']['fail_caution'],$username);
				sendReport($troop['uid'],'trick',23,$user['lastcid'],0,$caution);
				return $GLOBALS['useTrick']['fail_no_wisdom'];
			}
		}
	}
	sql_query("update sys_troops set noback=1 where id='$troop[id]'");

	$caution = sprintf($GLOBALS['trickGuanMemDaGou']['succ_caution'],$username);
	sendReport($troop['uid'],'trick',23,$user['lastcid'],0,$caution);
	 
	completeTaskGoalBySortandType($uid, 53, 12);
	return $GLOBALS['trickGuanMemDaGou']['succ'];

}
//千里奔袭
function trickQianLiBenXi($uid,$troop,$wisdom)
{
	$user=sql_fetch_one("select name,lastcid from sys_user where uid='$uid'");
	$username = $user['name'];
	if ($troop['hid'] > 0)
	{
		$addrate=100/(1000+$wisdom);
		if(($troop['state']!=0)&&($troop['state']!=1)) throw new Exception($GLOBALS['trickQianLiBenXi']['wrong_state']);
		sql_query("update sys_troops set endtime=unix_timestamp()+(endtime-unix_timestamp())*$addrate,lastacc=unix_timestamp() where id='$troop[id]'");
	}
	completeTaskGoalBySortandType($uid, 53, 13);
	return $GLOBALS['trickQianLiBenXi']['succ'];
}

//以逸待劳
function trickYiYiDaiLao($uid,$troop,$wisdom)
{
	sql_query("insert into mem_troops_buffer (troopid,buftype,bufparam,endtime) values ($troop[id],26,'',2000000000) on duplicate key update endtime=2000000000");
	return $GLOBALS['trickYiYiDaiLao']['succ'];
}

//调虎离山
function trickDiaoHuLiShan($uid,$targetcid,$wisdom)
{
	$user=sql_fetch_one("select name,lastcid from sys_user where uid='$uid'");
	$username = $user['name'];
	$targetcity = getCityNamePosition($targetcid);
	$targetuid = sql_fetch_one_cell("select uid from sys_city where cid='$targetcid'");
	if ($uid == $targetuid) {
		throw Exception($GLOBALS['waigua']['forbidden']);
	}
	$delta = sql_fetch_one_cell("select buftype from mem_city_buffer where buftype=27 and cid='$targetcid' and endtime>unix_timestamp()");
	if (!empty($delta))
	{
		$caution = sprintf($GLOBALS['trickDiaoHuLiShan']['fail_caution'],$username,$targetcity);
		sendReport($targetuid,"trick",TITLE_TRICK,$user['lastcid'],$targetcid,$caution);
		return sprintf($GLOBALS['trickDiaoHuLiShan']['fail'],$targetcity);
	}
	sql_query("update sys_troops set `state`=1,endtime=unix_timestamp() - starttime + unix_timestamp(),starttime=unix_timestamp() where state in (0,2,4) and cid='$targetcid' and id in (select troopid from mem_troops_buffer where buftype=26)");
	$delay = 3600*24;
	sql_query("insert into mem_city_buffer (cid,buftype,bufparam,endtime) values ('$targetcid',27,'0',unix_timestamp()+$delay) on duplicate key update endtime=endtime+$delay");
	$caution = sprintf($GLOBALS['trickDiaoHuLiShan']['succ_caution'],$username);
	sendReport($targetuid,"trick",TITLE_TRICK,$user['lastcid'],$targetcid,$caution);
	return $GLOBALS['trickDiaoHuLiShan']['succ'];
}


function useTrick($uid,$param)
{
	$hid = intval(array_shift($param));
	$trickid = intval(array_shift($param));
	$tricktype = array_shift($param);
	$tricktarget = array_shift($param);
	
	$trick = sql_fetch_one("select * from cfg_trick where id='$trickid'");
	$temp = empty($trick);
	
	//throw new Exception($hid);
	
	if (empty($trick)||($trick['usetype'] != $tricktype))  throw new Exception($GLOBALS['useTrick']['trick_not_exist']);
	
	

	if(!lockUser($uid)) throw new Exception($GLOBALS['pacifyPeople']['server_busy']);
	$userjinnang = sql_fetch_one_cell("select sum(count) from sys_goods where uid='$uid' and gid=13");
	if ($userjinnang < $trick['cost'])
	{
		$tempnum = $trick['cost'] - $userjinnang;
		throw new Exception("not_enough_goods13#$tempnum");
	}
	$hero = sql_fetch_one("select * from sys_city_hero where hid='$hid'");
	if (empty($hero)) throw new Exception($GLOBALS['useTrick']['hero_not_exist']);
	if($trickid!=13) //不是千里奔袭的话，用城里的智将
	{
		//if ($hero['state'] > 1&&$tricktype!=2) throw new Exception($GLOBALS['useTrick']['hero_not_incity']);
		if (isHeroInCity($hero['state'])==0&&$tricktype!=2) throw new Exception($GLOBALS['useTrick']['hero_not_incity']);
	}

	$wisdom = $hero['wisdom_base'] + $hero['wisdom_add'];
	if (isHeroHasBuffer($hid,4))    //智多星符
	{
		$wisdom = $wisdom * 1.25;
	}
	$wisdom=$wisdom+$hero['wisdom_add_on'];
	if ($tricktype == 0)
	{
		$targetcid = sql_fetch_one_cell("select lastcid from sys_user where uid='$uid'");
	}
	else if ($tricktype == 1) //对敵城用
	{
		$targetcid = $tricktarget;

		$targetuser = sql_fetch_one("select * from sys_user where uid=(select uid from sys_city where cid='$targetcid')");
		if ($targetuser['uid'] == $uid)
		{
			throw new Exception($GLOBALS['useTrick']['target_is_mine']);
		}
		$myunion = sql_fetch_one_cell("select union_id from sys_user where uid='$uid'");
		if (($targetuser['union_id'] == $myunion)&&($myunion > 0))
		{
			throw new Exception($GLOBALS['useTrick']['target_is_union']);
		}
		if(sql_check("select vacend from sys_user_state where uid='$targetuser[uid]' and vacend>unix_timestamp()")) //对方在休假
		{
			throw new Exception($GLOBALS['useTrick']['target_in_vacation']);
		}
		if(sql_check("select forbiend from sys_user_state where uid='$targetuser[uid]' and forbiend>unix_timestamp()"))
		{
			throw new Exception($GLOBALS['useTrick']['target_be_locked']);
		}
	}
	else if ($tricktype == 2)   //对己方军队使用的
	{
		$troopid = $tricktarget;
		$troop = sql_fetch_one("select * from sys_troops where id='$troopid'");
		if (empty($troop)||($troop['uid'] != $uid)) throw new Exception($GLOBALS['useTrick']['target_is_not_my_troop']);
		if ($troop['hid'] == 0) throw new Exception($GLOBALS['useTrick']['target_has_no_hero']);
		if ($trickid == 10)
		{
			if ($troop['state'] > 1) throw new Exception($GLOBALS['useTrick']['target_is_not_on_way']);
			$now = sql_fetch_one_cell("select unix_timestamp()");
			if ($now - $troop['lastrun'] < 3600)
			{
				$msg = sprintf($GLOBALS['useTrick']['target_is_just_run'],MakeTimeLeft(3600-$now + $troop['lastrun']));
				throw new Exception($msg);
			}
		}else if ($trickid == 26)
		{
			if ($troop['state'] != 0) throw new Exception($GLOBALS['useTrick']['target_is_not_on_way']);
		}
	}
	else if ($tricktype == 3)
	{
		$troopid = $tricktarget;
		$troop = sql_fetch_one("select * from sys_troops where id='$troopid'");
		if (empty($troop)||($troop['state'] != 0))
		{
			if ($trickid == 11)
			{
				throw new Exception($GLOBALS['useTrick']['target_not_coming_1']);
			}
			else if ($trickid == 12)
			{
				throw new Exception($GLOBALS['useTrick']['target_not_coming_2']);
			}
		}

	}
	//不宣而战要先判断冷却期
	if ($trickid == 9)
	{
		$targetuid = sql_fetch_one_cell("select uid from sys_city where cid='$targetcid'");
		$now = sql_fetch_one_cell("select unix_timestamp()");
		$last_trick_war = sql_fetch_one_cell("select last_trick_war from mem_user_schedule where uid='$targetuid'");
		if ($now - $last_trick_war < 21600)
		{
			throw new Exception($GLOBALS['trickBuXuanErZhan']['cool_down']);
		}
	}
	else if ($trickid==10) //金蝉脱壳
	{
		$now = sql_fetch_one_cell("select unix_timestamp()");
		if($now-$troop['lastrun']<3600) throw new Exception($GLOBALS['trickJinChaoTuoQiao']['cool_down']);
	}
	else if($trickid==13) //千里奔袭
	{
		$now = sql_fetch_one_cell("select unix_timestamp()");
		if($now-$troop['lastacc']<3600) throw new Exception($GLOBALS['trickQianLiBenXi']['cool_down']);
	}
	else if ($trickid == 5) 
	{//暗度陈仓，需要目标城市已经中了十面埋伏
		$ShiMianManFu = sql_fetch_one("select * from mem_city_buffer where cid='$targetcid' and buftype=8 and endtime > unix_timestamp()");
		if (empty($ShiMianManFu)) {//目标城市没有中"十面埋伏"
			throw new Exception ($GLOBALS['trickAnDuChenChang']['fail_not_shimianmanfu']);
		}
	}
	$curEnergy=sql_fetch_one_cell("select energy from mem_hero_blood where hid='$hid'");
	if($curEnergy<$trick['cost'])
	{
		throw new Exception($GLOBALS['useTrick']['hero_no_energy']);
	}
	sql_query("update mem_hero_blood set `energy`=GREATEST(0,`energy`-'$trick[cost]') where hid='$hid'");
	//先把锦囊扣掉
	addGoods($uid,13,-$trick['cost'],0);
	$trickmsg = "";
	$ret = array();
	$cantrick = true;
	if ($tricktype == 1)    //对敌城
	{
		if ($trickid != 7) //挑拨离间不跟城守比，而是跟其中一个将领比
		{
			$cityhids = sql_fetch_one("select chiefhid,generalid,counsellorid from sys_city where cid='$targetcid'");
    		$chiefhid=0;
    		if(!empty($cityhids)){
    			if($cityhids['counsellorid']>0)
    				$chiefhid=$cityhids['counsellorid'];  
    			else if($cityhids['chiefhid']>0)
    				$chiefhid=$cityhids['chiefhid']; 
    			else if($cityhids['generalid']>0)
    				$chiefhid=$cityhids['generalid']; 	
    		}
			$chiefhero = sql_fetch_one("select * from sys_city_hero where hid='$chiefhid'");
			if (!empty($chiefhero))
			{
				$chiefWisdom=$chiefhero['wisdom_base']+$chiefhero['wisdom_add'];
				if (isHeroHasBuffer($hid,4))    //智多星符
				{
					$chiefWisdom = $chiefWisdom * 1.25;
				}
				$chiefWisdom=$chiefWisdom+$chiefhero['wisdom_add_on'];
				if (mt_rand(1,$chiefWisdom) > $wisdom)
				{
					$trickmsg = $GLOBALS['useTrick']['fail_no_wisdom'];
					$cantrick = false;
				}
			}
		}
	}

	if ($cantrick)
	{
		mt_srand(time());
		
		switch($trickid)
		{
			case 1:
				$trickmsg = trickCaoMuJieBin($uid,$targetcid,$wisdom);
				break;
			case 2:
				$trickmsg = trickKongCheng($uid,$targetcid,$wisdom);
				break;
			case 3:
				$trickmsg = trickPaoZhuangYingYu($uid,$targetcid,$wisdom);
				break;
			case 4:
				$trickmsg = trickJinBiQingYe($uid,$targetcid,$wisdom);
				break;
			case 5:
				$trickmsg = trickAnDuChenChang($uid,$targetcid,$wisdom);
				break;
			case 6:
				$trickmsg = trickYaoYinHuoZhong($uid,$targetcid,$wisdom);
				break;
			case 7:
				$trickmsg = trickTiaoBoLiJian($uid,$targetcid,$wisdom);
				break;
			case 8:
				$trickmsg = trickShiMianMaiFu($uid,$targetcid,$wisdom);
				break;
			//case 9:
				//$trickmsg = trickBuXuanErZhan($uid,$targetcid,$wisdom);
				//break;
			case 10:
				$trickmsg = trickJinChaoTuoQiao($uid,$troop,$wisdom);
				break;
			case 11:
				$trickmsg = trickBaMemJinShuo($uid,$troop,$wisdom);
				break;
			case 12:
				$trickmsg = trickGuanMemDaGou($uid,$troop,$wisdom);
				break;
			case 13:
				$trickmsg =  trickQianLiBenXi($uid,$troop,$wisdom);
				break;
			case 14:
				$trickmsg =  trickShunTengMoGua($uid,$targetcid,$wisdom);
				break;
			case 15:
				$trickmsg =  trickChenHuoDaJie($uid,$targetcid,$wisdom);
				break;
			case 18:
				$trickmsg =  trickZhuSiMaJi($uid,$targetcid,$wisdom);
				break;
			case 19:
				$trickmsg =  trickWeiWeiJiuZhao($uid,$targetcid,$wisdom);
				break;
			case 20:
				$trickmsg =  trickFenShaoLiangCao($uid,$targetcid,$wisdom);
				break;
			case 21:
				$trickmsg =  trickXuZhangShengShi($uid,$troop,$wisdom);
				break;
			case 22:
				$trickmsg =  trickYanQiXiGu($uid,$troop,$wisdom);
				break;
			case 23:
				$trickmsg =  trickYouDiShenRu($uid,$troop,$wisdom);
				break;
			case 25:
				$trickmsg =  trickHuDiChouXin($uid,$targetcid,$wisdom);
				break;
			case 26:
				$trickmsg =  trickYiYiDaiLao($uid,$troop,$wisdom);
				break;
			case 27:
				$trickmsg =  trickDiaoHuLiShan($uid,$targetcid,$wisdom);
				break;
			case 28:
				$trickmsg =  trickShangWuChouTi($uid,$targetcid,$wisdom);
				break;
			default:
				$trickmsg = $GLOBALS['useTrick']['trick_not_exist'];
		}
	}
	unlockUser($uid);
	completeTask($uid,88);

	logUserAction($uid,$trickid+1000);
	$ret[] = $trickmsg;
	$ret[] = sql_fetch_one_cell("select sum(count) from sys_goods where uid='$uid' and gid=13");
	return $ret;
}
//上屋抽梯
function trickShangWuChouTi($uid,$targetcid,$wisdom)
{
	$user=sql_fetch_one("select name,lastcid from sys_user where uid='$uid'");
	$username = $user['name'];
	$targetcity = getCityNamePosition($targetcid);
	$targetuid = sql_fetch_one_cell("select uid from sys_city where cid='$targetcid'");
	if ($uid == $targetuid) {
		throw Exception($GLOBALS['waigua']['forbidden']);
	}
	$delta = sql_fetch_one_cell("select unix_timestamp() - last_trick_shangwuchouti from mem_city_schedule where cid='$targetcid'");
	if($delta==false){
		sql_query("insert into mem_city_schedule(cid,last_trick_shangwuchouti)values($targetcid,0)");
		$delta=1214200000;
	}
	if ($delta < 3600*5)
	{
		$caution = sprintf($GLOBALS['trickSWCT']['fail_caution'],$username,$targetcity);
		sendReport($targetuid,"trick",TITLE_TRICK,$user['lastcid'],$targetcid,$caution);
		return sprintf($GLOBALS['trickSWCT']['fail'],$targetcity);
	}

	$delay = $wisdom * 120;

	sql_query("insert into mem_city_buffer (cid,buftype,bufparam,endtime) values ('$targetcid',28,0,unix_timestamp()+'$delay') on duplicate key update endtime=`endtime`+'$delay'");

	$endtime = sql_fetch_one_cell("select endtime from mem_city_buffer where cid='$targetcid' and buftype=28");
	sql_query("update mem_city_schedule set last_trick_shangwuchouti=unix_timestamp() where cid='$targetcid'");

	$ret = sprintf($GLOBALS['trickSWCT']['succ'],$targetcity,MakeTimeLeft($delay));
	$caution = sprintf($GLOBALS['trickSWCT']['succ_caution'],$username,$targetcity,$targetcity,MakeTimeLeft($delay),MakeEndTime($endtime));

	sendReport($targetuid,"trick",TITLE_TRICK,$user['lastcid'],$targetcid,$caution);
	return $ret;
}

?>