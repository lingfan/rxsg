<?php
require_once("./interface.php");
require_once("./global.php");
require_once("./AdultFunc.php");
require_once("./UnionFunc.php");

function parseAndAddReward($uid,$reward,$loggoodstype,$logarmortype,$logthingstype,$logmoneytype){
	$goods = explode ( ",", $reward );
	$goodcnt = $goods [0];
	$money = 0;
	$ret = array ();
	$isYuanBao = false;
	for($i = 1; $i < $goodcnt * 3; $i += 3) {
		$type = $goods [$i];
		$gid = $goods [$i + 1];
		$cnt = $goods [$i + 2];
		if ($type == 0) {
			if ($gid == 0) {
				$money += $cnt;
			} else if ($gid == - 100) {
				$money += $cnt;
				$isYuanBao = true;
			} else {
				addGoods ( $uid, $gid, $cnt, $loggoodstype );
			}
			$good = sql_fetch_one ( "select *,$cnt as count,'0' as gype from cfg_goods where gid='$gid'" );
			$good ['count'] = $cnt;
			$good ['gtype'] = 0;
			$ret [] = $good;
		} else if ($type == 1) {
			$armor = sql_fetch_one ( "select * from cfg_armor where id='$gid'" );
			$armor ['count'] = $cnt;
			$armor ['gtype'] = 1;
			$armor ['hp'] = $armor ['ori_hp_max'];
			$armor ['hp_max'] = $armor ['ori_hp_max'];
			$ret [] = $armor;
			addArmor ( $uid, $armor, $cnt, $logarmortype );
		} else if ($type == 2) {
			$thing = sql_fetch_one ( "select * from cfg_things where tid='$gid'" );
			$thing ['count'] = $cnt;
			$thing ['gtype'] = 2;
			$ret [] = $thing;
			addThings ( $uid, $gid, $cnt, $logthingstype );
		}
	}
	if ($money > 0) {
		if ($isYuanBao)
			addMoney ( $uid, $money, $logmoneytype );
		else
			addGift ( $uid, $money, $logmoneytype );
	}
	return $ret;
}

function MakeEndTime($endtime)
{
	//$localnow = time();
	//$remotenow = sql_fetch_one_cell("select unix_timestamp()");
	$str = "%Y".$GLOBALS['MakeEndTime']['year']."%m".$GLOBALS['MakeEndTime']['month']."%d".$GLOBALS['MakeEndTime']['day']." %H:%i:%s";
	return sql_fetch_one_cell("select from_unixtime($endtime,'$str')");
	//return date($str,$endtime-$remotenow+$localnow);
}
function MakeTimeLeft($timeleft)
{
	$hour = floor($timeleft / 3600);
	$minute = floor(($timeleft-$hour * 3600) / 60);
	$second = $timeleft % 60;
	if ($hour > 0)
	{
		$thetime = $hour.$GLOBALS['MakeTimeLeft']['hour'].$minute.$GLOBALS['MakeTimeLeft']['min'].$second.$GLOBALS['MakeTimeLeft']['sec'];
	}
	else if ($minute > 0)
	{
		$thetime = $minute.$GLOBALS['MakeTimeLeft']['min'].$second.$GLOBALS['MakeTimeLeft']['sec'];
	}
	else
	{
		$thetime = $second.$GLOBALS['MakeTimeLeft']['sec'];
	}
	return $thetime;
}

function MakeSelectSql($tableName,$whereArray,$limit="")
{
	$sql = "select * from $tableName where ";
	foreach ($whereArray as $name => $value)
	{
		$sql .= $name . "=" . $value . " and ";
	}
	$sql = substr($sql,0,strlen($sql) - 5);
	if (!empty($limit))
	{
		$sql .= $limit;
	}
	return $sql;
}
function encodeBuildingPosition($inner,$x,$y)
{
	return $inner * 100 + $x * 10 + $y;
}
function decodeBuildingPosition($xy,&$inner,&$x,&$y)
{
	$inner = (int)floor($xy / 100);
	$pos = $xy - $inner * 100;
	$x = (int)floor($pos / 10);
	$y = $pos - ($x * 10);
}

//群发系统信

function sendAllSysMail($title,$content)
{
	$title = addslashes($title);
	$content = addslashes($content);
	$mid = sql_insert("insert into sys_mail_sys_content (`content`,`posttime`) values ('$content',unix_timestamp())");
	$sql="insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) (select `uid`,'$mid','$title','0',unix_timestamp() from `sys_user` where state<=2)";
	sql_insert($sql);
	sql_query("insert into sys_alarm (`uid`,`mail`) (select `uid`,1 from `sys_user` where state<=2) on duplicate key update `mail`=1");
}

//给某个玩家发系统信
function sendSysMail($touid,$title,$content)
{
	$title = addslashes($title);
	$content = addslashes($content);

	$mid = sql_insert("insert into sys_mail_sys_content (`content`,`posttime`) values ('$content',unix_timestamp())");
	$sql="insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$touid','$mid','$title','0',unix_timestamp())";
	sql_insert($sql);
	sql_query("insert into sys_alarm (`uid`,`mail`) values ('$touid',1) on duplicate key update `mail`=1");
}


function sendReport($touid,$type,$title,$origincid,$happencid,$content)
{
	if($origincid>0)
	{
		$origincity=sql_fetch_one_cell("select name from sys_city where cid='$origincid'");
		if (empty($origincity))
		{
			$origincity = sql_fetch_one_cell("select c.name from mem_world m left join cfg_world_type c on c.type=m.type where m.wid=".cid2wid($origincid));
		}
	}
	else $origincity="";

	if($origincid==$happencid)
	{
		$happencity=$origincity;
	}
	else if($happencid>0)
	{
		$happencity=sql_fetch_one_cell("select name from sys_city where cid='$happencid'");
		if (empty($happencity))
		{
			$happencity = sql_fetch_one_cell("select c.name from mem_world m left join cfg_world_type c on c.type=m.type where m.wid=".cid2wid($happencid));
		}
	}
	else $happencity="";

	sendReportDetail($touid,$type,$title,$origincid,$origincity,$happencid,$happencity,$content);
	
	/*
	 *平台接口
	 */
	if (defined("PASSTYPE") && $type=="trick"){
		$uid=$touid;
		if($uid>1000 && $title==23){
			try{
			    require_once 'game/agents/AgentServiceFactory.php';
				AgentServiceFactory::getInstance($uid)->addAcceptTrickEvent($content);
			}catch(Exception $e){
				try{
					file_put_contents("./agents/log/interface-error.log",date("Y-m-d H:i:s",time())." || ".$e->getMessage()."\n",FILE_APPEND);
				}catch(Exception $err){
					
				}
			}
		}
    }
}

function sendReportDetail($touid,$type,$title,$origincid,$origincity,$happencid,$happencity,$content)
{
	$content = addslashes($content);
	if($title<=11) $stype=0;
	else if($title>=12&&$title<=14) $stype=1;
	else if($title==19) $stype=2;
	else $stype=3;
	sql_query("insert into sys_report (`uid`,`origincid`,`origincity`,`happencid`,`happencity`,`title`,`type`,`time`,`read`,`battleid`,`content`) values ('$touid','$origincid','$origincity','$happencid','$happencity','$title','$stype',unix_timestamp(),'0','0','$content')");
	sql_query("insert into sys_alarm (uid,report) values ('$touid',1) on duplicate key update report=1");

}
function addCityResources($cid,$wood,$rock,$iron,$food,$gold)
{
	sql_query("update mem_city_resource set `wood`=`wood`+'$wood',`rock`=`rock`+'$rock',`iron`=`iron`+'$iron',`food`=`food`+'$food',`gold`=`gold`+'$gold' where `cid`='$cid'");
}
function checkCityResource($cid,$wood,$rock,$iron,$food,$gold)
{
	$myres = sql_fetch_one("select * from mem_city_resource where `cid`='$cid'");
	if (empty($myres)) return false;
	if (($myres['wood'] < $wood)||
	($myres['rock'] < $rock)||
	($myres['iron'] < $iron)||
	($myres['food'] < $food)||
	($myres['gold'] < $gold))
	{
		return false;
	}
	return true;
}
function checkCityOwner($cid,$uid)
{
	if (!sql_check("select 1 from sys_city where `cid`='$cid' and `uid`='$uid'")) throw new Exception("not_user_city");
}
function checkCityExist($cid,$uid)
{
	if (!sql_check("select uid from sys_city where `cid`='$cid' and uid='$uid'")) throw new Exception($GLOBALS['checkCityExist']['no_city_info']);
}
function getCityPeopleFreeCount($cid)
{
	return sql_fetch_one_cell("select `people`-`people_working`-`people_building` from mem_city_resource where cid=$cid");
}
function getCityNamePosition($cid)
{
	$cityname = sql_fetch_one_cell("select name from sys_city where cid='$cid'");
	return $cityname ."(".($cid%1000).",".floor($cid/1000).")";
}
function getPosition($cid)
{
	return "[".($cid%1000).",".floor($cid/1000)."]";
}
function getWorldState($cid)
{
	$wid=cid2wid($cid);
	return intval(sql_fetch_one_cell("select state from mem_world where wid='$wid'"));
}
function getCityArea($cid)
{
	$level = sql_fetch_one_cell("select `level` from sys_building where cid=".$cid." and bid=".ID_BUILDING_WALL);
	if(empty($level)) return 0;

	$all = sql_fetch_one_cell("select area from cfg_wall where level='$level'");

	return $all;
}

function getCityAreaOccupied($cid)
{
	$curr = sql_fetch_one_cell("select sum(c.count * d.area_need) from sys_city_defence c,cfg_defence d where c.did=d.did and c.cid=$cid");
	$reinforcing = sql_fetch_one_cell("select sum(c.count * d.area_need) from sys_city_reinforcequeue c,cfg_defence d where c.did=d.did and c.cid=$cid");
	
	$state=getWorldState($cid);
	
	if($state==1)
	{
		$curr+=intval(sql_fetch_one_cell("select area from sys_city_area where cid='$cid'"));
	}
	return ($curr+$reinforcing);
}

function addCityPeople($cid,$count)
{
	sql_query("update mem_city_resource set `people`=`people`+'$count' where cid='$cid'");
}

//批量添加兵员
function addCitySoldiers($cid,$soldiers,$add)
{
	foreach($soldiers as $sid=>$count)
	{
		if ($add)
		{
			sql_query("insert into sys_city_soldier (`cid`,`sid`,`count`) values ('$cid','$sid','$count') on duplicate key update `count`=`count` + '$count'");
		}
		else
		{
			sql_query("insert into sys_city_soldier (`cid`,`sid`,`count`) values ('$cid','$sid','-$count') on duplicate key update `count`=`count` - '$count'");
		}
	}
	updateCityResourceAdd($cid);
}
//增加一个兵种
function addCitySoldier($cid,$sid,$count)
{
	sql_query("insert into sys_city_soldier (`cid`,`sid`,`count`) values ('$cid','$sid','$count') on duplicate key update `count`=`count` + '$count'");
	sql_query("insert into log_city_soldier (`cid`,`sid`,`uid`,`count`,`type`) values ('$cid','$sid',0,'$count',9) on duplicate key update `count`=`count` + '$count'");
	updateCityResourceAdd($cid);
}
function addCityDefence($cid,$did,$count)
{
	sql_query("insert into sys_city_defence (`cid`,`did`,`count`) values ('$cid','$did','$count') on duplicate key update `count`=`count` + '$count'");
}
//=========自己加的
function giveDalibao($uid){//登陆送大礼包,君主将任务归零，刷新活动任务
    $dalibaoid=50186;
    $dalibaotype=mt_rand(0,1);
	if($dalibaotype==1) $dalibaoid=50187;
	$usr_info=sql_fetch_one("select * from hd_dalibao where uid='$uid'");
	if(empty($usr_info)){
	  sql_query("insert into sys_goods (uid,gid,count) values ('$uid','$dalibaoid',1) on duplicate key update `count`=`count`+1");
	   sql_query("insert into sys_user_task_num (uid,type,num,maxnum) values ('$uid',1,0,100) on duplicate key update num=0,maxnum=100");
		sql_query("insert into hd_dalibao (uid,time) values ('$uid',unix_timestamp())"); 
		   myupdateUserDailyTasks($uid);//日常任务
	}else{
	    $lastlogintime =sql_fetch_one_cell("select time from hd_dalibao where uid='$uid' limit 1");
		$nowtime=sql_fetch_one_cell("select unix_timestamp()");
		$old=getDailyDay($lastlogintime);
		$new=getDailyDay($nowtime);
		$sameday=$old==$new;
	    if($sameday){
	      //同一天不做事
	    } else{
		   sql_query("insert into sys_goods (uid,gid,count) values ('$uid','$dalibaoid',1) on duplicate key update `count`=`count`+1");
		   sql_query("insert into sys_user_task_num (uid,type,num,maxnum) values ('$uid',1,0,100) on duplicate key update num=0,maxnum=100");
	       sql_query("update hd_dalibao set time=unix_timestamp() where uid=$uid");
		   myupdateUserDailyTasks($uid);//日常任务
		   sql_query("update mem_user_schedule set  today_war_count=0 where uid=$uid");//清除战场次数
		   $m_citys = sql_fetch_rows("select cid from sys_city where uid='$uid'");
		   foreach ($m_citys as $key => $m_city)
		      sql_query("insert into sys_soldier_convert (`cid`,`value`,`convert`) values ('$m_city[cid]',80000,0 )  on duplicate key update value=value+1000");
		}
	}
}
function getDailyDay($time){
		$tstamp=$time;
		$time=date("Y-m-d",$tstamp);
		return $time;
}
function  myupdateUserDailyTasks($uid){//不限制次数可以重复做的任务
       $nobility = sql_fetch_one_cell("select nobility from sys_user where uid='$uid'");
	   $nobility = getBufferNobility($uid,$nobility);//推恩
       if ($nobility<1) return 0;
	   //sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task, cfg_task where tid=221 and state=1 and uid='$uid' and `group` in (100480,100520,100550,101620,101760,102070,107260)) on duplicate key update state=0");
	   sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task, cfg_task where tid=221 and state=1 and uid='$uid' and `group` in (100480,100520,100550,101760,102070,107260)) on duplicate key update state=0");
	   sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task, cfg_task where tid=243 and state=1 and uid='$uid' and `group` in (80,1000)) on duplicate key update state=0");
	   /*
       //==勇士嘉奖
	   sql_query("delete from sys_user_task where tid between 100331 and 100333");//删除活动任务id
	   sql_query("delete from sys_user_goal where gid between 2060391 and 2060393");//删除玩家得到了的物品，在重做
	   $yongshiid=100311;
	   for($i=0;$i<4;$i++){
		  $taskstate=sql_fetch_one_cell("select state from sys_user_task where uid=$uid and tid=$yongshiid");
		  $goalid=sql_fetch_one_cell("select id from cfg_task_goal where tid=$yongshiid and sort=5 limit 1");
		  if($taskstate==1){
		     sql_query("delete from sys_user_goal where uid=$uid and gid=$goalid");
			 sql_query("update sys_user_task set state=0 where uid=$uid and tid=$yongshiid");
			}else{
			 sql_query("insert into sys_user_task(uid,tid,state)values($uid,$yongshiid,0) on duplicate key update tid=$yongshiid");
			}
		  $yongshiid++;
		}
		//=============君主任务下增加2个任务
		$yongshiid=90000;//侦察黄巾军城池，当成功后，触发90032占领黄巾军城池任务
		sql_query("delete from sys_user_task where uid=$uid and (tid between 90000 and 90018)");//删除活动任务id
	    sql_query("delete from sys_user_goal where uid=$uid and (gid between 102502 and 102520)");//删除玩家得到了的物品
		sql_query("delete from sys_user_task where uid=$uid and (tid between 90032 and 90041)");//删除活动任务id
	    sql_query("delete from sys_user_goal where uid=$uid and (gid between 102534 and 102543)");//删除玩家得到了的物品
		sql_query("insert into sys_user_task(uid,tid,state)values($uid,$yongshiid,0) on duplicate key update tid=$yongshiid");
		//============活动任务
		//====战场犒赏
	    $yongshiid=101181;
		sql_query("delete from sys_user_task where uid=$uid and (tid between 101181 and 101186)");//删除活动任务id
		sql_query("delete from sys_user_goal where uid=$uid and (gid between 3000032 and 3000037)");//删除玩家得到了的物品
		for($i=0;$i<6;$i++){
		  $taskstate=sql_fetch_one_cell("select state from sys_user_task where uid=$uid and tid=$yongshiid");
		  $goalid=sql_fetch_one_cell("select id from cfg_task_goal where tid=$yongshiid and sort=5 limit 1");
		  if($taskstate==1){
		     sql_query("delete from sys_user_goal where uid=$uid and gid=$goalid");
			 sql_query("update sys_user_task set state=0 where uid=$uid and tid=$yongshiid");
			}else{
			 sql_query("insert into sys_user_task(uid,tid,state)values($uid,$yongshiid,0) on duplicate key update tid=$yongshiid");
			}
		  $yongshiid++;
		}
		//=====
		//=======武器强化嵌套任务
		$yongshiid=103801;
        sql_query("delete from sys_user_goal where uid=$uid and (gid between 3101152 and 3101156)");//删除玩家得到了的物品，在重做
        sql_query("delete from sys_user_task where uid=$uid and (tid between 103801 and 103805)");
        sql_query("insert into sys_user_task(uid,tid,state)values($uid,$yongshiid,0) on duplicate key update tid=$yongshiid");
	    //=======
		*/
	}
//===========
function sendPassportMail($uid){
	$user = sql_fetch_one("select * from sys_user where uid='$uid'");
	$mails = sql_fetch_rows("select * from sys_mail_passport where passport='$user[passport]' and passtype='$user[passtype]'");
	foreach($mails as $mail){
		sendSysMail($uid,$mail['title'],$mail['content']);
		sql_query("delete from sys_mail_passport where id='$mail[id]'");
	}
}
function realLogin($uid,$sid){
	$ip = $GLOBALS['ip'];
	$sip = $GLOBALS['sip'];
	if (sql_check("select * from cfg_baned_ip where ip='$ip'")) throw new Exception($GLOBALS['realLogin']['ip_blocked']);
	sql_query("insert into sys_sessions(uid, sid, ip) values('$uid', '$sid', '$ip') on duplicate key update `sid`='$sid',`ip`='$ip'");
	file_put_contents(ROOT_PATH."/sessions/".$uid,$sid);
	$_SESSION['currentLogin_uid'] = $uid; //在session里保存下用户名
	if(isAdultOpen()){
		updateFcmTime($uid);
	}
	sql_query("update sys_online set onlinetime=onlinetime+GREATEST(0,lastupdate-onlineupdate),onlineupdate=unix_timestamp(),`lastupdate`=unix_timestamp() where uid='$uid'");
	$lastlogintime =sql_fetch_one_cell("select unix_timestamp()-time from log_login where uid='$uid' order by time desc limit 1");
	if($lastlogintime>86400*7){// 如果玩家七天没有登录了，则发送信件，并且还有城池	
		if(sql_check("select * from sys_city where uid='$uid'")){
			$temp=$GLOBALS['utils']['7days_not_login'];
			sql_insert("insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$uid','15','$temp','0',unix_timestamp())");
			sql_query("insert into sys_alarm (`uid`,`mail`) values ('$uid',1) on duplicate key update `mail`=1");
		}
	}
	sql_query("insert into log_login (uid,ip,time,sip) values ($uid,'$ip',unix_timestamp(),'$sip')");
	$user = sql_fetch_one("select * from sys_user where uid='$uid'");
	$showRule = ($user['regtime']<1280505600 && !sql_check("select uid from sys_allowpk where uid='$uid' and allow=1"));//1280505600=unix_timestamp('2010-07-31')
   	$rank = sql_fetch_one_cell("select rank from rank_user where uid='$uid'");
	if(empty($rank)){
	   $rank=intval(sql_fetch_one_cell("select count(*) from rank_user"))+1;
	}
	sql_query("update sys_user set rank='$rank' where uid='$uid'");
	giveDalibao($uid);
	if (($user['union_id'] > 0)){   //如果玩家从属于一个联盟，刷新该联盟的声望和排名
	   updateUnionRank($user['union_id']);
	}
	if($user['state']<>3){
	  $playusersname=$user['name'];
	  $msg="欢迎玩家:".$playusersname."来到热血三国!";
	  sendSysInform(0,1,0,600,50000,1,14972979,$msg);//第一个参数0 聊天室，1顶部
	}else if($user['state']==3){//设置新人王和排行触发
	  $content='新人王活动奖励';
	  sql_insert("insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$uid','1001','$content','0',unix_timestamp())");
	  $content='联盟排行活动奖励';
	  sql_insert("insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$uid','1002','$content','0',unix_timestamp())");
	  $content='野地将活动奖励';
	  sql_insert("insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$uid','1003','$content','0',unix_timestamp())");
	  $content='史诗将活动奖励';
	  sql_insert("insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$uid','1004','$content','0',unix_timestamp())");
	  $pandutype=3;
	  startNewRank($pandutype);
	}
	return $showRule;
}
function startNewRank($type){//type=1为重置新人王和联盟排行 type=0 为新人王和联盟排行清0
      switch($type){
	     case 0: sql_query("update sys_activity set inuse=1,content='' where id=1");//公告清0
		 case 1:{
		       sql_query("update sys_user_act set state=0,con=0,time=0 where id=1");
	           sql_query("update sys_hero_act set uid=0,state=0 where hid>0");
			   if($type==0) return;
			}
	    }
      $actstart = sql_fetch_one("select * from sys_user_act where id=1");
	  if($actstart['state']==0 && $actstart['time']==0){//没发公告
	      sql_query("update sys_user_act set state=1,con=0,time=unix_timestamp() where id=1");//新人王，联盟排行开启
		  $tim=time()+86400*7;
	      $uyearm=date('n',$tim);
	      $uyeard=date('j',$tim);
	      $uyear=$uyearm.'月'.$uyeard.'日<br/>下午16时';
	      $content='<font color="#FFFF00">---------------公告--------------</font><br/>1、新人王排行已经开始！将于'.$uyear.'结束!<br/>2、野地将活动正在进行中第一个招降到<br/>指定将领的玩家将会获得丰富奖励！<br/>3、相关奖励请查看系统信件！';
	      sql_query("update sys_activity set inuse=1,content='$content' where id=1");
		}
    }
function wid2cid($wid){
	 $y = floor($wid / 10000) * 10 + floor((($wid % 100) / 10));
	 $x = floor(($wid % 10000) / 100) * 10 + floor($wid % 10);
	 return $y * 1000 + $x;
    }
function check_firsthero($uid,$uname,$hid){	 
     $hidok = sql_fetch_one("select * from sys_hero_act where state=0 and hid='$hid'");
	 if(empty($hidok)) return 0;
	 $msg ="恭喜玩家[:".$uname."],第一个降服名将: [".$hidok ['name']."]获得元宝:";
	 sql_query("update sys_hero_act set state=1 where hid='$hid'");
	 if(empty($hidok['goods']) || $hidok['goods']==''){
		$msg .=$hidok['gifs']; 
	  }else{
		  $msg .=$hidok['gifs'].'获得物品:'; 
		  $goodinfo=explode( "," , $hidok['goods']);
		  for($i=0;$i<$goodinfo[0];$i++){
			  $nus=$i*2+1;
			  $gid=$goodinfo[$nus];
			  $goodname=sql_fetch_one_cell("select name from cfg_goods where gid='$gid'");
			  $gidcnt=$goodinfo[$nus+1];
			  $msg .=$goodname.'*'.$gidcnt;
			  addGoods($uid,$gid,$gidcnt,3);
      		}
	    }
	 addMoney($uid,$hidok['gifs'],3);	
     sendSysInform(0,1,0,600,50000,1,16738740,$msg);	 
	 return 1;
    }
/*
function get_firstherogoods($uid,$hid){
    $hmsg='';
    switch($hid){
	  case 10:{//兀突骨
	      $hmsg.=',获得50000元宝！龙渊装备箱3个!';
		  $money=50000;
		  $gid=8887;
		  $cnt=3;
		  break;
	    }
	  case 107:{//司马懿
	      $hmsg.=',获得180000元宝！龙渊装备箱8个!';
		  $money=180000;
		  $gid=8887;
		  $cnt=8;
		  break;
	    }
	  case 177:{//吕布
	      $hmsg.=',获得15000000元宝！绝世龙渊套装2套,绝世白虎套装1套!';
		  $money=10000000;
		  $gid=8892;
		  $cnt=2;
		  addGoods($uid,$gid,$cnt,3);
		  $gid=8891;
		  $cnt=1;
		  break;
	    }
	  case 255:{//周瑜
	      $hmsg.=',获得400000元宝！龙渊装备箱8个!';
		  $money=400000;
		  $gid=8887;
		  $cnt=8;
		  break;
	    }
	  case 261:{//孟获
	      $hmsg.=',获得80000元宝！龙渊装备箱5个!';
		  $money=80000;
		  $gid=8887;
		  $cnt=5;
		  break;
	    }
	  case 285:{//姜维
	      $hmsg.=',获得200000元宝！龙渊装备箱7个!';
		  $money=200000;
		  $gid=8887;
		  $cnt=7;
		  break;
	    }
	  case 340:{//孙坚
	      $hmsg.=',获得1500000元宝！龙渊装备箱12个！';
		  $money=1500000;
		  $gid=8887;
		  $cnt=12;
		  break;
	    }
	  case 347:{//孙策
	      $hmsg.=',获得2000000元宝！龙渊装备箱12个！';
		  $money=2000000;
		  $gid=8887;
		  $cnt=12;
	      break;
	    }
	  case 456:{//张飞
	      $hmsg.=',获得200000元宝！龙渊装备箱8个！';
		  $money=200000;
		  $gid=8887;
		  $cnt=8;
	      break;
	    }
	  case 518:{//曹操
	      $hmsg.=',获得50000000元宝！绝世龙渊套装1套,绝世白虎套装3套!';
		  $money=50000000;
		  $gid=8892;
		  $cnt=1;
		  addGoods($uid,$gid,$cnt,3);
		  $gid=8891;
		  $cnt=3;
		  break;
	    }
	  case 725:{//刘备
	      $hmsg.=',获得10000000元宝！绝世龙渊套装2套,绝世白虎套装2套!';
		  $money=10000000;
		  $gid=8892;
		  $cnt=2;
		  addGoods($uid,$gid,$cnt,3);
		  $gid=8891;
		  $cnt=2;
		  break;
	    }
	  case 699:{//赵云
	      $hmsg.=',获得210000元宝！龙渊装备箱10个！';
		  $money=210000;
		  $gid=8887;
		  $cnt=10;
	      break;
	    }
	  case 832:{//蹋顿
	      $hmsg.=',获得100000元宝！龙渊装备箱6个！';
		  $money=100000;
		  $gid=8887;
		  $cnt=6;
		  break;
	    }
	  case 870:{//关羽
	      $hmsg.=',获得300000元宝！龙渊装备箱10个！';
		  $money=300000;
		  $gid=8887;
		  $cnt=10;
		  break;
	    }
	  case 1008:{//花鬘
	      $hmsg.=',获得60000元宝！龙渊装备箱3个！';
		  $money=60000;
		  $gid=8887;
		  $cnt=3;
		  break;
	    }
	  case 1014:{//祝融夫人
	      $hmsg.=',获得50000元宝！龙渊装备箱3个！';
		  $money=50000;
		  $gid=8887;
		  $cnt=3;
		  break;
	    }
	  case 1025:{//鲍三娘
	      $hmsg.=',获得35000元宝！龙渊装备箱3个！';
		  $money=35000;
		  $gid=8887;
		  $cnt=3;
		  break;
	    }
	  case 409:{//马超
	      $hmsg.=',获得100000元宝！龙渊装备箱8个！';
		  $money=250000;
		  $gid=8887;
		  $cnt=8;
		  break;
	    }
	  case 186:{//吕蒙
	      $hmsg.=',获得300000元宝！龙渊装备箱10个！';
		  $money=300000;
		  $gid=8887;
		  $cnt=10;
		  break;
	    }
	  case 484:{//张辽
	      $hmsg.=',获得60000元宝！龙渊装备箱10个！';
		  $money=360000;
		  $gid=8887;
		  $cnt=10;
		  break;
	    }
	  case 114:{//甘宁
	      $hmsg.=',获得50000元宝！龙渊装备箱8个！';
		  $money=250000;
		  $gid=8887;
		  $cnt=8;
		  break;
	    }
	  case 580:{//陆逊
	      $hmsg.=',获得35000元宝！龙渊装备箱7个！';
		  $money=210000;
		  $gid=8887;
		  $cnt=7;
		  break;
	    }
	}
  addMoney($uid,$money,3);
  addGoods($uid,$gid,$cnt,3);
  return $hmsg;
}
*/
function cid2wid($cid)
{
	$y = floor($cid / 1000);
	$x = ($cid % 1000);
	return (floor($y / 10)) * 10000 + (floor($x / 10)) * 100 + ($y % 10) * 10 + ($x % 10);
}
function getCityDistance($cid1,$cid2)
{
	$x1 = $cid1 % 1000;
	$y1 = floor($cid1 / 1000);
	$x2 = $cid2 % 1000;
	$y2 = floor($cid2 / 1000);
	return sqrt(($x1-$x2)*($x1-$x2) + ($y1-$y2)*($y1-$y2));
}
function updateCityHeroChange($uid,$cid)
{
	//$hero_fee = sql_fetch_one_cell("select sum(level) * ".HERO_FEE_RATE." from sys_city_hero where cid='$cid' and uid='$uid' and npcid=0");
	//$npc_fee = sql_fetch_one_cell("select sum(level) * ".NPCHERO_FEE_RATE." from sys_city_hero where cid='$cid' and uid='$uid' and npcid > 0");
	//$hero_fee += $npc_fee;
	//主将军师修炼算进俸禄
	$hero_fee=sql_fetch_one_cell("select sum(level*20+(GREATEST(affairs_base+affairs_add-90,0)+GREATEST(bravery_base+bravery_add-90,0)+GREATEST(wisdom_base+wisdom_add-90,0))*50) from sys_city_hero where herotype!=1000 and cid='$cid' and uid='$uid' and state!=5 and state!=6 and state!=9");
	//$hero_fee=sql_fetch_one_cell("select sum(level*20+(GREATEST(affairs_base+affairs_add-90,0)+GREATEST(bravery_base+bravery_add-90,0)+GREATEST(wisdom_base+wisdom_add-90,0))*50) from sys_city_hero where cid='$cid' and uid='$uid' and state<5");
	//$hero_fee+=sql_fetch_one_cell("select sum(level*20+(GREATEST(affairs_base+affairs_add-90,0)+GREATEST(bravery_base+bravery_add-90,0)+GREATEST(wisdom_base+wisdom_add-90,0))*50) from sys_city_hero where cid='$cid' and uid='$uid' and state=10");
	//$hero_fee+=sql_fetch_one_cell("select sum(level*20+(GREATEST(affairs_base+affairs_add-90,0)+GREATEST(bravery_base+bravery_add-90,0)+GREATEST(wisdom_base+wisdom_add-90,0))*50) from sys_city_hero where cid='$cid' and uid='$uid' and state=11");
	//$hero_fee+=sql_fetch_one_cell("select sum(level*20+(GREATEST(affairs_base+affairs_add-90,0)+GREATEST(bravery_base+bravery_add-90,0)+GREATEST(wisdom_base+wisdom_add-90,0))*50) from sys_city_hero where cid='$cid' and uid='$uid' and state=7");
	//$hero_fee+=sql_fetch_one_cell("select sum(level*20+(GREATEST(affairs_base+affairs_add-90,0)+GREATEST(bravery_base+bravery_add-90,0)+GREATEST(wisdom_base+wisdom_add-90,0))*50) from sys_city_hero where cid='$cid' and uid='$uid' and state=8");
	$hero_fee = $hero_fee+1-1;//为防止在解雇最后一个将领时出现null值,这里将null设为0
	sql_query("update mem_city_resource set hero_fee='$hero_fee'  where cid='$cid'");
}
function openTaskWithTaskid($uid,$taskid)
{
	if(sql_check("select * from cfg_task where id='$taskid'")){
		sql_query("insert into sys_user_task (uid,tid,state) values ('$uid','$taskid',0) on duplicate key update state=0");
	}
}
function removeTaskWithTaskid($uid,$taskid)//
{
	sql_query("delete from sys_user_task where uid='$uid' and tid='$taskid'");

}
function completeTask($uid,$goalid)
{
	sql_query("replace into sys_user_goal (`uid`,`gid`) values ('$uid','$goalid')");
}
function completeTaskWithTaskid($uid,$taskid)
{
	$goals = sql_fetch_rows("select * from cfg_task_goal where tid=$taskid");
	foreach($goals as $goal) {
		$goalid = $goal["id"];
		sql_query("replace into sys_user_goal (`uid`,`gid`) values ('$uid','$goalid')");		
	}
}
function completeTaskGoalBySortandType($uid, $sort, $type)
{
	$goalids = sql_fetch_rows("select id from cfg_task_goal where sort=$sort and type=$type");
	foreach ($goalids as $goalid){
		$id = $goalid['id'];
		$taskid = sql_fetch_one_cell("select tid from cfg_task_goal where id=$id");
		if(empty($taskid)) continue;
		$taskrecord = sql_fetch_one("select * from sys_user_task where uid=$uid and tid=$taskid and state=0 limit 1");
		if(empty($taskrecord)) continue;
		completeTask($uid, $id);
	}
}

function updateUnionRank($unionid)
{
	$union_prestige=sql_fetch_one_cell("select sum(prestige) from sys_user  where union_id='$unionid'");
	if ($union_prestige<0) $union_prestige=2147483640;
	sql_query("update sys_union  set prestige=$union_prestige where id='$unionid'");	
	$union_donate=sql_fetch_one_cell("select sum(donate) from sys_user_donate  where unionid='$unionid' and uid in (select uid from sys_user where union_id=$unionid)");
	if (empty($union_donate)) $union_donate=0;
	sql_query("update sys_union  set donate=$union_donate where id='$unionid'");
	$rank = sql_fetch_one_cell("select count(*) + 1 from sys_union where prestige>'$union_prestige'");
	sql_query("update sys_union set rank='$rank' where id='$unionid'");
}

function isHeroHasBuffer($hid,$buftype)
{
	return sql_check("select * from mem_hero_buffer where hid='$hid' and buftype='$buftype'");
}

function updateUserPrestige($uid)
{
	$prestige1 = sql_fetch_one_cell("select sum(r.people_building) from sys_user u,sys_city c,mem_city_resource r where c.cid=r.cid and c.uid=u.uid and u.uid=".$uid);
	$prestige2 = sql_fetch_one_cell("select sum(f.people_need*s.count)  from sys_user u,sys_city c,sys_city_soldier s,cfg_soldier f where s.cid=c.cid and s.sid=f.sid and c.uid=u.uid and u.uid=".$uid);
	$prestige3 = sql_fetch_one_cell("select sum(people) from sys_troops where uid=".$uid);
	$warprestige = sql_fetch_one_cell("select warprestige from sys_user where uid=".$uid);
	$prestige = $prestige1 + $prestige2 + $prestige3 + $warprestige;
	if ($prestige < 0) $prestige = 0;

	sql_query("update sys_user set prestige=$prestige where uid=".$uid);
}

function cityHasHeroPosition($uid,$cid)
{
	$officeLevel = sql_fetch_one_cell("select level from sys_building where cid='$cid' and bid=".ID_BUILDING_OFFICE);
	if (empty($officeLevel)) return false;
	$heroCount = sql_fetch_one_cell("select count(*) from sys_city_hero where cid='$cid' and uid='$uid'");
	return $officeLevel>$heroCount;
}
//得到玩家拥有的所有城市
function getCities($uid)
{
	return sql_fetch_rows("select * from sys_city where `uid`='$uid'");
}

function getCityBuilding($uid,$param)
{
	$cid=intval(array_shift($param));
	return getCityBuildingInfo($uid,$cid);
}

function getCityBuildingInfo($uid,$cid){
	//所有的建筑信息
	//======
	getBuildingTasks($uid,$cid);//完成成长任务，包括建筑卡0的解决,
	sql_query("update `sys_building` set `state` = '0',`level` = (`level` - '1') where `state` = '2' and `cid`='$cid' and `state_endtime` <= unix_timestamp()");//建筑拆除一级
	sql_query("delete from mem_building_destroying where `cid`='$cid' and `state_endtime` <= unix_timestamp()");
	sql_query("delete from sys_building where `cid`='$cid' and `level` ='0' and `state` = '0'");//删除建筑是0的显示
	
	//=====
	$rets = sql_fetch_rows("select b.*,c.name as bname,c.description as buildingDescription,l.description as level_description,l.using_people from cfg_building c,sys_building b left join cfg_building_level l on l.bid=b.bid and l.`level`=b.`level` where b.`cid`='$cid' and c.`bid`=b.`bid`");
	foreach ($rets as &$ret)
	{
		$now = sql_fetch_one_cell("select unix_timestamp()");
		$ret['state_timeleft'] = $ret['state_endtime'] - $now;
	}
	return $rets;
	//return sql_fetch_rows("select b.*,c.name as bname,c.description as buildingDescription,b.state_endtime-unix_timestamp() as state_timeleft,l.description as level_description,l.using_people from cfg_building c,sys_building b left join cfg_building_level l on l.bid=b.bid and l.`level`=b.`level` where b.`cid`='$cid' and c.`bid`=b.`bid`");
}
//资源信息
//军队信息
//城防信息
//将领信息
function doGetCityBaseInfo($uid,$cid){
	$ret = array();
	$user = sql_fetch_one("select * from sys_user where uid='$uid'");
	$openLottery = sql_fetch_one_cell("select value from mem_state where state = 150");
	if(empty($openLottery) || $openLottery == "" || $openLottery == false){
		$openLottery = 0;
	}
	$user['openLottery'] = $openLottery;
	$ret[] = $user;
	$isAdult=1;
	$onLineTime=0;
	if(isAdultOpen()){
		$isAdult = sql_fetch_one_cell("select state from sys_user_fcm where uid = '$uid'");
		$onLineTime = sql_fetch_one_cell("select onlinetime from sys_user_fcm where uid = '$uid'");	
	}
	$ret[0]['isAdult'] = $isAdult;
	$ret[0]['onLineTime'] = $onLineTime;
    $ret[0]['openIndex']=sql_fetch_one_cell("select value from mem_state where state='111'");  //第一次加载获取少数名族地区当前已开启到哪个区域
    $ret[0]['designations']=sql_fetch_rows("select * from cfg_designation a left join sys_user_designation b on a.`did`=b.`did` where b.`uid`='$uid'");
    $ret[0]['curDesigName']=sql_fetch_one_cell("select a.name from cfg_designation a left join sys_user_designation b on a.`did`=b.`did` where b.`uid`='$uid' and b.`ison`='1' and b.`state`='1'");
    $ret[0]['curDesigid']=sql_fetch_one_cell("select did from sys_user_designation where uid='$uid' and ison='1' and state='1'");
	$ret[0]['currentYear']=dogetCurYear();
	//补给技巧的加成效果
//	$food_army_use = sql_fetch_one_cell("select food_army_use from mem_city_resource where cid='$cid'");
//	$bujiLevel = sql_fetch_one_cell("select level from sys_city_technic where cid=$cid and tid=28");
//	if (!empty($bujiLevel) && $bujiLevel >0 ) {
//		$food_army_use = $food_army_use * (1 - $bujiLevel * 0.03);
//	}
//	
     //UpdateUsersCityResource($uid,$cid);
	$cityres = sql_fetch_one("select * from mem_city_resource where `cid`='$cid'");
	$ret[] = $cityres;
    //$ret[] = sql_fetch_one("select * from mem_city_resource where `cid`='$cid'");
	//所有属于本城的将领列表
	$ret[] = sql_fetch_rows("select * from sys_city_hero h left join mem_hero_blood m on m.hid=h.hid where h.`cid`='$cid' and h.uid='$uid'");
	//本城拥有的军队
	$ret[] = sql_fetch_rows("select * from sys_city_soldier where `cid`='$cid' order by sid");
	//本城拥有的城防
	$ret[] = sql_fetch_rows("select * from sys_city_defence where `cid`='$cid' order by did");

	$ret[] = sql_fetch_one("select * from sys_alarm where uid='$uid'");	

	return $ret;
}
//得到某个城市的全部信息
//基础信息:
//基本信息  
//建筑信息
function doGetCityAllInfo($uid,$cid)
{
	$ret = array();
	$cityinfo = sql_fetch_one("select * from sys_city where `cid`='$cid'");
	if (empty($cityinfo)) throw new Exception($GLOBALS['doGetCityAllInfo']['no_city_info']);
	$cityinfo['spacialSid']=getSpacialSoldierId($cid);
	//城市基本信息
	$ret[] = $cityinfo;

	$ret[] = doGetCityBaseInfo($uid,$cid);
	//所有的建筑信息
	$ret[] = getCityBuildingInfo($uid,$cid);
	//科技信息
	$ret[] = sql_fetch_rows("select tid,level from sys_city_technic where cid='$cid'");
	//州郡信息
	$ret[] = sql_fetch_rows("select province,jun from mem_world where wid=".cid2wid($cid));
	return $ret;
}

function getSpacialSoldierId($cid){
	$sid=sql_fetch_one_cell("select sid from cfg_soldier_special_city where cid='$cid' and type<>'4'");
	if(empty($sid)){
		return 0;
	}else{
		return $sid;
	}
}

function updateCityPeopleStable($cid)
{
	//人口稳定值=人口上限*民心
	$people_max = sql_fetch_one_cell("select `people_max` from mem_city_resource where `cid`='$cid'");
	$city_morale = sql_fetch_one_cell("select `morale` from mem_city_resource where `cid`='$cid'");
	$people_stable = $people_max * $city_morale * 0.01;
	sql_query("update mem_city_resource set `people_stable`='$people_stable' where `cid`='$cid'");
}
function updateCityPeopleMax($cid)
{
	//民房 N级增长人口上限100*N
	$people_max = sql_fetch_one_cell("select sum(level*(level+1)*50) from sys_building where `cid`='$cid' and bid=".ID_BUILDING_HOUSE);
	sql_query("update mem_city_resource set `people_max`='$people_max' where `cid`='$cid'");
	updateCityPeopleStable($cid);
}
function updateCityGoldMax($cid){
	$level=sql_fetch_one_cell("select level from sys_building where `cid`='$cid' and bid=".ID_BUILDING_GOVERMENT);
	$vw=10000;//万
	$gold_max = sql_fetch_one_cell("select level*(level+1)*500000 from sys_building where `cid`='$cid' and bid=".ID_BUILDING_GOVERMENT);
	if($level==11){
		$gold_max=6600*$vw;
	}else if($level==12){
		$gold_max=7800*$vw;		
	}
	else if($level==13){
		$gold_max=9100*$vw;		
	}
	else if($level==14){
		$gold_max=10500*$vw;		
	}
	else if($level==15){
		$gold_max=12000*$vw;		
	}
	sql_query("update mem_city_resource set `gold_max`='$gold_max' where `cid`='$cid'");
}
function checkUserPassport($uid,$password){
	$user = sql_fetch_one("select * from sys_user where uid='$uid'");
	$passport = $user['passport'];
	$passtype = $user['passtype'];
	$passsucc = false;
	@include ("./passport/$passtype.php");
	return $passsucc;
}

//更新所有资源产量
//需要从资源生产建筑里取人数，在资源建筑变化时要更新(ok)
//需要从科技里算加成，在升级科技时要更新(ok)
//需要取当前人口数，在人口不足且在变化时要更新(ok)
//需要取当前士兵数，要士兵变化时要更新(ok)
//需要取当前城守官的统率值，要在城守换人或城守官升统率的时候更新(todo)
//需要取当前城所占有的野地的数量，在占领或者被占领时更新(todo)
//需要取当前资源加成宝物当前状态，在使用或失效时更新(todo)
function updateCityResourceAdd($cid)
{
	$ownercid = sql_fetch_one_cell("select ownercid from mem_world where wid=".cid2wid($cid));
	if (empty($ownercid)) $ownercid = $cid;
	if (empty($ownercid)) return;
	sql_query("update sys_city_res_add set resource_changing=1 where cid=".$ownercid);
}
function checkGoods($uid,$gid)
{
	return checkGoodsCount($uid,$gid,1);
}

function reduceGoods($uid,$gid,$count,$type=0)
{
	$gid = intval($gid);
	$count = intval($count);
	if ($count > 0)
	{
		sql_query("insert into log_goods (`uid`,`gid`,`count`,`time`,`type`) values ('$uid','$gid','-$count',unix_timestamp(),'$type')");
		sql_query("update sys_goods set `count`=GREATEST(0,`count`-$count) where uid='$uid' and gid='$gid'");
		if($gid>110000 && $gid <160000) {
			$usedCount=sql_fetch_one_cell("SELECT -SUM(`count`) FROM log_goods WHERE gid>110000 AND gid <160000 AND uid=$uid AND `count`<0");
			if(!empty($usedCount)&&$usedCount>=30){
				finishAchivement($uid,30);
			}
		}else if ($gid==158){
			$usedCount=sql_fetch_one_cell("SELECT -SUM(`count`) FROM log_goods WHERE gid='$gid' AND uid=$uid AND `count`<0 and type=0");
			if($usedCount>=15){
				finishAchivement($uid,70016);//70016	不死小强
			}
		}
	}
	unlockUser($uid);
}

function reduceThings($uid,$tid,$count,$type=0)
{
	if ($count > 0)
	{
		sql_query("update sys_things set `count`=GREATEST(0,`count`-$count) where uid='$uid' and tid='$tid'");
		sql_query("insert into log_things (`uid`,`tid`,`count`,`time`,`type`) values ('$uid','$tid','-$count',unix_timestamp(),'$type')");
	}
	unlockUser($uid);
}

function addGoods($uid,$gid,$cnt,$type)
{
	if ($cnt==0) {
		return;
	}	
	if ($gid == 160052) {
		if (!sql_check("select 1 from log_qiyue where uid=$uid and gid=160052 and count<0")) {//如果没有使用过，可以掉落，但是只能还是1.
			sql_query("insert into sys_goods (uid,gid,count) values ('$uid','$gid','$cnt') on duplicate key update `count`=1");
			sql_query("insert into log_qiyue (uid,gid,count,time) values ('$uid','$gid','$cnt',unix_timestamp())");
		}
	} else {
		if($gid==0){			
			sql_query("update sys_user set gift=gift+'".$cnt."' where uid='".$uid."'");
			sql_query("insert into log_gift (uid,gid,count,time,type) values ('$uid','$gid','$cnt',unix_timestamp(),$type)");
		}else{
		    if($gid==152) {
			  sql_query("insert into sys_goods (uid,gid,count) values ('$uid','$gid','$cnt') on duplicate key update `count`=`count`+'$cnt'");
			  sql_query("insert into log_goods (uid,gid,count,time,type) values ('$uid','$gid','$cnt',unix_timestamp(),$type)");
			  $cnt= sql_fetch_one_cell("select count from sys_goods where uid = '$uid' and gid='$gid'");
			  $gid=888888;
			}
			sql_query("insert into sys_goods (uid,gid,count) values ('$uid','$gid','$cnt') on duplicate key update `count`=`count`+'$cnt'");
			sql_query("insert into log_goods (uid,gid,count,time,type) values ('$uid','$gid','$cnt',unix_timestamp(),$type)");
		}
	}
}

function addBook($uid,$bid,$count,$type)
{
	if ($count==0) {
		return;
	}
	for ($count = 1; $count < 20; $count++) {
		sql_query("insert into sys_user_book (uid,bid,level,hid) values ('$uid','$bid','1','0')");
		sql_query("insert into log_book (uid,bid,level,count,time) values ('$uid','$bid','1','1',unix_timestamp())");
		
	}
}


function addChibiGoods($uid,$gid,$cnt,$type){
	$a="";
	$a.=$gid.",";
	$a.=$cnt.",";
	$a.=$type.",";
	$param = array();
	$param[]=intval($gid);
	$param[]=$cnt;
	$param[]=$type;
	return sendChibiRemoteRequest($uid,"addChibiRemoteGoods",$param);
}
function  addMenghuoGood($uid,$gid,$cnt,$type){
	$params=array();
	$params[]=intval($gid);
	$params[]=$cnt;
	$params[]=$type;
	return  sendRemote9001Request($uid,"addMenghuoRemoteGoods",$params);
}

function addThings($uid,$tid,$cnt,$type)
{
	if ($cnt==0) {
		return;
	}
	sql_query("insert into log_things (uid,tid,count,time,type) values ('$uid','$tid','$cnt',unix_timestamp(),$type)");
	sql_query("insert into sys_things (uid,tid,count) values ('$uid','$tid','$cnt') on duplicate key update `count`=`count`+'$cnt'");
}
function giveTask($uid,$tid){ 
		sql_query("insert into sys_user_task (uid,tid,state) values ('$uid','$tid',0) on duplicate key update state=state");
		$goals = sql_fetch_rows("select id as gid from cfg_task_goal where tid=$tid and  (sort=80 or sort=50)");
		if($goals){
			foreach($goals as $goal){
				$gid=$goal['gid'];
				sql_query("insert into sys_user_goal (uid,gid,currentcount) values ('$uid','$gid',0)  on duplicate key update currentcount=currentcount");		
			}
		}
		return sql_fetch_one_cell("select name from cfg_task where id=$tid");			
}
function giveTasks($uid,$groupid){
		$tasks=sql_fetch_rows("select id from cfg_task where `group`=$groupid");
		foreach($tasks as $task){
			$tid=$task['id'];
			sql_query("insert into sys_user_task (uid,tid,state) values ('$uid','$tid',0) on duplicate key update state=state");
		}
		foreach($tasks as $task){
			$tid=$task['id'];
			$goals = sql_fetch_rows("select id as gid from cfg_task_goal where tid=$tid and (sort=80 or sort=50)");
			if($goals){
				foreach($goals as $goal){
					$gid=$goal['gid'];
					sql_query("insert into sys_user_goal (uid,gid,currentcount) values ($uid,$gid,0)  on duplicate key update currentcount=currentcount");
				}
			}
		}
		return sql_fetch_one_cell("select name from cfg_task_group where id=$groupid");
	}
function addArmor($uid,$armor,$cnt,$type,$stronglevel=0,$combine_level=0)
{
	if ($cnt==0) {
		return;
	}
	$strongvalue=sql_fetch_one_cell("select strong_value from cfg_strong_probability where level='$stronglevel'");
	if (empty($strongvalue)) {
		$strongvalue=0;
	}
	for($i=0;$i<$cnt;$i++){
		sql_query("insert into sys_user_armor (uid,armorid,hp,hp_max,hid,strong_level,strong_value,combine_level) values ($uid,'{$armor['id']}',{$armor['ori_hp_max']}*10,'{$armor['ori_hp_max']}',0,'$stronglevel','$strongvalue','$combine_level')");
	}
	updateBattleOpenState($uid,$armor['id']);
	sendDesignation($uid,$armor['id']);//赠送称号
	sql_query("insert into log_armor (uid,armorid,count,time,type) values ($uid,'{$armor['id']}',$cnt,unix_timestamp(),$type)");
}

function sendDesignation($uid,$armorid)
{
	$did=0;
	$armorid = intval($armorid);
	switch ($armorid)
	{
		case 12018:	//热血金枪
			$did=24;
			break;
		case 12016://末日之刃
			$did=25;
			break;
		default:
			$did=0;
			break;
	}
	if (!empty($did)) {
		if (!sql_check("select 1 from sys_user_designation where uid=$uid and did=$did"))
			sql_query("insert into sys_user_designation(did,uid,ison,state) values($did,'$uid','0','1')");
	}
}

function checkMoney($uid,$money)
{
	$usermoney=sql_fetch_one_cell("select money from sys_user where uid='$uid'");
	if(empty($usermoney)||($usermoney<$money)) return false;
	else return true;
}

function checkGift($uid,$money)
{
	$usermoney=sql_fetch_one_cell("select gift from sys_user where uid='$uid'");
	if(empty($usermoney)||($usermoney<$money)) return false;
	else return true;
}
function addMoney($uid,$money,$type){
	if ($money==0) return ;
	sql_query("insert into log_money (uid,count,time,type) values ('$uid','$money',unix_timestamp(),'$type')");
	sql_query("update sys_user set money=money+'$money' where uid='$uid'");
	if ($money < 0) {
		$now = sql_fetch_one_cell("select unix_timestamp()");//记录每天元宝消耗量
		$today = $now - (($now + 8 * 3600)%86400);
		sql_query("insert into log_day_money (day,money) values ('$today','$money') on duplicate key update `money`=`money`+'$money'"); 
		$user=sql_fetch_one_cell("select * from sys_user where uid=$uid");
		$type=sql_fetch_one_cell("select name from log_money_type where id=$type");
		if (empty($type)) {
			$type=sql_fetch_one_cell("select name from log_money_type where id=10");
		}
	}
}

function addGift($uid,$gift,$type)
{
	if ($gift==0) return ;
	sql_query("insert into log_gift (uid,count,time,type) values ('$uid','$gift',unix_timestamp(),'$type')");
	sql_query("update sys_user set gift=gift+'$gift' where uid='$uid'");
}

function checkThingsCount($uid,$tid,$need)
{
	if (!lockUser($uid)) throw new Exception($GLOBALS['checkThingsCount']['server_busy']);
	$cnt = sql_fetch_one_cell("select `count` from sys_things where uid='$uid' and tid='$tid'");
	if (empty($cnt)) $cnt = 0;

	if ($cnt < $need)
	{
		unlockUser($uid);
		return false;
	}
	else
	{
		return true;
	}

}
function checkGoodsCount($uid,$gid,$need)
{
	if (!lockUser($uid)) throw new Exception($GLOBALS['checkGoodsCount']['server_busy']);
	$cnt = sql_fetch_one_cell("select `count` from sys_goods where uid='$uid' and gid='$gid'");
	if (empty($cnt)) $cnt = 0;

	if ($cnt < $need)
	{
		unlockUser($uid);
		return false;
	}
	else
	{
		return true;
	}

}
function checkGoodsArray($uid,$gidArray)
{
	if (!lockUser($uid)) throw new Exception($GLOBALS['checkGoodsArray']['server_busy']);
	foreach($gidArray as $gid => $need)
	{
		$cnt = sql_fetch_one_cell("select `count` from sys_goods where uid='$uid' and gid='$gid'");
		if ($cnt < $need)
		{
			unlockUser($uid);
			return false;
		}
	}
	return true;
}
function addHeroExp($hid,$exp)
{
	$expadd = floor($exp * HERO_EXP_RATE);
	sql_query("update sys_city_hero set exp=exp+$expadd where herotype!=1000 and hid=".$hid);
}
function notifyUnionChange($uid,$unionid,$state)
{
	$username = sql_fetch_one_cell("select `name` from sys_user where uid='$uid'");
	$username=addslashes($username);
	sql_query("insert into mem_union_buf (uid,nick,union_id,state,updatetime) values ('$uid','$username','$unionid',$state,unix_timestamp())");
}
function lockUser($uid){
	$lockfile = './userlock/'.$uid.'.lock';
	if (file_exists($lockfile)&&( $GLOBALS['now'] - filemtime($lockfile) < 60))
	{
		//return false;
			return true;
	}
	touch($lockfile);
	return true;
}
function unlockUser($uid){
	$lockfile = './userlock/'.$uid.'.lock';
	@unlink($lockfile);
}

function newLockUser($uid,$commandFunc){
	$lockfile = './userlock/'.$commandFunc.$uid.'.lock';
	if (file_exists($lockfile)&&( $GLOBALS['now'] - filemtime($lockfile) < 60))
	{
	//	return false;
		return true;
	}
	touch($lockfile);
	return true;
}

function newUnlockUser($uid,$commandFunc){
	$lockfile = './userlock/'.$commandFunc.$uid.'.lock';
	@unlink($lockfile);
}

function throwHeroToField($hero){
	$hid = $hero['hid'];
	sql_query("delete from mem_hero_blood where hid='$hid'");
	sql_query("delete from sys_hero_armor where hid='$hid'");
	sql_query("update sys_user_armor set hid=0 where hid='$hid'");
	//去掉将领技能书
	sql_query("update sys_user_book set hid=0 where hid=$hid");
	//把人往野地里面丢，如果没有人的话，就放在上面，如果有人的话，如果这个人也是NPC，则另外找地方，如果这个人不是NPC的话，就替代他的位置。
	$findtimes = 10;   //找十次，如果找不到的话就丢掉了
	if(isActHero($hero["herotype"]) || isCardHero($hero["herotype"])){
		$findtimes=0;
	}else if ($hero['npcid']>0)	{
		$findtimes=40;//名将多找几次
	}
	while($findtimes > 0){
		$findtimes--;
		if ($hero['npcid'] > 0) {
			$wid = sql_fetch_one_cell("select wid from mem_world where ownercid=0 and province<=13 and type > 1 and state=0 order by rand() limit 1");
		} else {
			$wid = sql_fetch_one_cell("select wid from mem_world where ownercid=0 and type > 1 and state=0 order by rand() limit 1");
		}
		$newcid = wid2cid($wid);
		$oldhero = sql_fetch_one("select * from sys_city_hero where uid=0 and cid='$newcid'");
		if(empty($oldhero)){ //该地点无人
			sql_query("update sys_city_hero set cid='$newcid',state=4,uid=0,loyalty=70 where hid=$hid");
			//sql_query("update sys_city_hero set cid='$newcid',state=4,uid=0 where hid=$hid");
			break;
		}
		else{    //有人		
		  if ($oldhero['npcid'] > 0){    //也是一个NPC
			
				//重新找过
				continue;
			}
			else {   //不是NPC，算他倒霉，要被砍掉
			
				sql_query("update sys_city_hero set cid=$newcid,state=4,uid=0,loyalty=70 where hid=$hid");
				sql_query("delete from sys_city_hero where hid=$oldhero[hid]");
				sql_query("delete from mem_hero_blood where hid='$oldhero[hid]'");
				if(!isActHero($hero["herotype"]))
					sql_query("insert into sys_recruit_hero (`name`,`npcid`,`sex`,`face`,`cid`,`level`,`exp`,`affairs_add`,`bravery_add`,`wisdom_add`,`affairs_base`,`bravery_base`,`wisdom_base`,`loyalty`,`gold_need`,`gen_time`) values ('$oldhero[name]','$oldhero[npcid]','$oldhero[sex]','$oldhero[face]','0','$oldhero[level]','$oldhero[exp]','$oldhero[affairs_add]','$oldhero[bravery_add]','$oldhero[wisdom_add]','$oldhero[affairs_base]','$oldhero[bravery_base]','$oldhero[wisdom_base]','66',0,unix_timestamp())");
				//扔池里不用算工资
				//$lastid = sql_fetch_one_cell("select LAST_INSERT_ID()");
				//sql_query("update sys_recruit_hero set gold_need=(`level`*20+(GREATEST(affairs_base+affairs_add-90,0)+GREATEST(bravery_base+bravery_add-90,0)+GREATEST(wisdom_base+wisdom_add-90,0))*50)*50 where id='$lastid'");
				$troop = sql_fetch_one("select * from sys_troops where uid=0 and cid='$newcid' and hid=$oldhero[hid]");
				if (!empty($troop)){
					sql_query("update sys_troops set hid=$hid where id=$troop[id]");
				}
				break;
			}
		}
	}
	if ($findtimes == 0){    // 十次都没有找到，砍了
	  if ($hero['npcid']>0){
			return;//名将不删
		}
		sql_query("update sys_troops set hid=0 where hid='$hid'");
		sql_query("delete from sys_city_hero where hid='$hid'");
		if((!isActHero($hero["herotype"])) || (!isCardHero($hero["herotype"]))){
			sql_query("insert into sys_recruit_hero (`name`,`npcid`,`sex`,`face`,`cid`,`level`,`exp`,`affairs_add`,`bravery_add`,`wisdom_add`,`affairs_base`,`bravery_base`,`wisdom_base`,`loyalty`,`gold_need`,`gen_time`) values ('$hero[name]','$hero[npcid]','$hero[sex]','$hero[face]','0','$hero[level]','$hero[exp]','$hero[affairs_add]','$hero[bravery_add]','$hero[wisdom_add]','$hero[affairs_base]','$hero[bravery_base]','$hero[wisdom_base]','66',0,unix_timestamp())");
		}
	}
}
//迁城
function doChangeCityPosition($uid,$cid,$targetcid)
{
	set_time_limit(3600);
	
	//检查有没有城外驻军
	if (sql_check("select uid from sys_troops where uid='$uid' and cid='$cid'")) throw new Exception($GLOBALS['changeCityPosition']['has_army_outside']);

	//检查有没有在战场里
	if (sql_check("select uid from sys_troops where uid='$uid' and startcid='$cid'")) throw new Exception($GLOBALS['changeCityPosition']['has_army_outside']);
	//检查有没有在洛阳战场里
	if(sql_check("select uid from sys_luoyang_troops where uid='$uid' and startcid='$cid'"))throw new Exception($GLOBALS['changeCityPosition']['has_army_outside']);

	$ownerfields=sql_fetch_rows("select wid from mem_world where ownercid='$cid'");
	if(!empty($ownerfields))
	{
		$comma="";
		foreach($ownerfields as $mywid)
		{
			$fieldcids.=$comma;
			$fieldcids.=wid2cid($mywid['wid']);
			$comma=",";
		}
		if(sql_check("select uid from sys_troops where targetcid in ($fieldcids) and state=4 and uid<>'$uid' and uid > 0")) throw new Exception($GLOBALS['changeCityPosition']['has_ally_force']);
		if(sql_check("select uid from sys_troops where targetcid in ($fieldcids) and state=4 and uid ='$uid' and cid<>'$cid'")) throw new Exception($GLOBALS['changeCityPosition']['has_other_city_force']);
	}
	$wid = cid2wid($cid);
	$worldState = sql_fetch_one_cell("select state from mem_world where wid='$wid'");
	if ($worldState == 1) throw new Exception($GLOBALS['changeCityPosition']['city_in_battle']);

	$citytype = sql_fetch_one_cell("select type from sys_city where cid='$cid'");
	if ($citytype == 5) $citytype = 0;
	if ($citytype > 0) throw new Exception($GLOBALS['changeCityPosition']['cant_move_great_city']);

	if (sql_check("select cid from sys_city where cid='$targetcid'"))
	{
		throw new Exception($GLOBALS['changeCityPosition']['invalid_target_city']);
	}

	$targetwid = cid2wid($targetcid);
	$targetprovince = sql_fetch_one_cell("select province from mem_world where wid='$targetwid'");

	if(!sql_check("select type from mem_world where wid='$targetwid' and type=1 and state=0 and ownercid=0")){
		throw new Exception($GLOBALS['changeCityPosition']['invalid_target_city']);
	}

	sql_query("insert into log_move_city (time,uid,fromcid,tocid) values (unix_timestamp(),'$uid','$cid','$targetcid')");
	/*
	 sql_query("call change_city_position('$cid','$targetcid','$wid','$targetwid')");
	 sql_query("update sys_city set province='$targetprovince' where cid='$targetcid'");
	 */

	$heros=sql_fetch_rows("select * from sys_city_hero where cid='$targetcid'");
	foreach($heros as $hero)
	{
		throwHeroToField($hero);
	}
	
	//王者之城特殊处理
	$isSpecialCity = sql_fetch_one("select * from cfg_soldier_special_city where cid='$cid' and type='4'");
	if(!empty($isSpecialCity))  //当前迁移的城池是王者城并且激活了造特殊兵种功能
	{
		sql_query("update cfg_soldier_special_city set cid='$targetcid' where cid='$cid' and type='4'");
	}
	//市场资源交易
	sql_query("update sys_city_trade set cid='$targetcid' where cid='$cid' and state='0'");
	
	sql_query("update sys_city set cid='$targetcid' where cid='$cid'");
	sql_query("update sys_city set province='$targetprovince' where cid='$targetcid'");

	sql_query("update mem_world set ownercid='0' where ownercid='$cid' and type>0");
	sql_query("update mem_world set type=1,ownercid=0 where wid='$wid'");
	sql_query("update mem_world set type=0,ownercid='$targetcid' where wid='$targetwid'");

	sql_query("update sys_user set lastcid='$targetcid' where uid='$uid'");

	sql_query("delete from sys_building where cid='$targetcid'");
	sql_query("update sys_building set cid='$targetcid' where cid='$cid'");
	sql_query("delete from mem_building_destroying where cid='$targetcid'");
	sql_query("update mem_building_destroying set cid='$targetcid' where cid='$cid'");
	sql_query("delete from mem_building_upgrading where cid='$targetcid'");
	sql_query("update mem_building_upgrading set cid='$targetcid' where cid='$cid'");

	sql_query("delete from mem_technic_upgrading where cid='$targetcid'");
	sql_query("update mem_technic_upgrading set cid='$targetcid' where cid='$cid'");
	sql_query("delete from sys_city_technic where cid='$targetcid'");
	sql_query("update sys_city_technic set cid='$targetcid' where cid='$cid'");
	sql_query("update sys_technic set cid='$targetcid' where cid='$cid'");

	sql_query("delete from mem_city_buffer where cid='$targetcid'");
	sql_query("update mem_city_buffer set cid='$targetcid' where cid='$cid'");

	sql_query("delete from sys_city_soldier where cid='$targetcid'");
	sql_query("update sys_city_soldier set cid='$targetcid' where cid='$cid'");
	sql_query("delete from sys_city_draftqueue where cid='$targetcid'");
	sql_query("update sys_city_draftqueue set cid='$targetcid' where cid='$cid'");
	sql_query("delete from mem_city_draft where cid='$targetcid'");
	sql_query("update mem_city_draft set cid='$targetcid' where cid='$cid'");

	sql_query("delete from mem_city_wounded where cid='$targetcid'");
	sql_query("update mem_city_wounded set cid='$targetcid' where cid='$cid'");
	
	sql_query("delete from mem_city_captive where cid='$targetcid'");
	sql_query("update mem_city_captive set cid='$targetcid' where cid='$cid'");
	
	
	sql_query("delete from mem_city_lamster where cid='$targetcid'");
	sql_query("update mem_city_lamster set cid='$targetcid' where cid='$cid'");

	sql_query("update sys_city_trade set cid='$targetcid' where cid='$cid'");
	sql_query("update sys_city_trade set buycid='$targetcid' where buycid='$cid'");

	sql_query("delete from sys_city_defence where cid='$targetcid'");
	sql_query("update sys_city_defence set cid='$targetcid' where cid='$cid'");
	sql_query("delete from sys_city_reinforcequeue where cid='$targetcid'");
	sql_query("update sys_city_reinforcequeue set cid='$targetcid' where cid='$cid'");
	sql_query("delete from mem_city_reinforce where cid='$targetcid'");
	sql_query("update mem_city_reinforce set cid='$targetcid' where cid='$cid'");

	sql_query("update sys_city_hero set cid='$targetcid' where cid='$cid' and uid='$uid'");

	sql_query("delete from sys_city_tactics where cid='$targetcid'");
	sql_query("update sys_city_tactics set cid='$targetcid' where cid='$cid'");
	
	sql_query("update sys_troops set task=1,state=1 where cid='$targetcid'");
	sql_query("update sys_troops set cid='$targetcid' where cid='$cid'");
	sql_query("update sys_luoyang_troops set startcid='$targetcid' where startcid='$cid'");

	sql_query("delete from sys_city_res_add where cid='$targetcid'");
	sql_query("update sys_city_res_add set cid='$targetcid',resource_changing=1,field_food_add=0,field_wood_add=0,field_rock_add=0,field_iron_add=0 where cid='$cid'");
	sql_query("delete from mem_city_resource where cid='$targetcid'");
	sql_query("update mem_city_resource set cid='$targetcid' where cid='$cid'");

	sql_query("delete from mem_city_schedule where cid='$targetcid'");
	sql_query("update mem_city_schedule set cid='$targetcid' where cid='$cid'");

	sql_query("update sys_battle set cid='$targetcid' where cid='$cid'");

	sql_query("delete from sys_city_rumor where cid='$targetcid'");
	sql_query("update sys_city_rumor set cid='$targetcid' where cid='$cid'");

	sql_query("delete from sys_recruit_hero where cid='$targetcid'");
	sql_query("update sys_recruit_hero set cid='$targetcid' where cid='$cid'");
	
	sql_query("update sys_hero_expr set cid='$targetcid' where cid='$cid'");
	
	//自动运输更改
	sql_query("update mem_city_autotrans set tocid=$targetcid where tocid=$cid");
	sql_query("update mem_city_autotrans set fromcid=$targetcid where fromcid=$cid");
	
	$yysType = sql_fetch_one_cell("select value from mem_state where state=197");
	if ($yysType == 60) {
		checkSili($uid,$cid,$targetcid);
	}
}

//马来定制：迁入迁出司隶都需要告知所有玩家。
function checkSili($uid,$fromCid,$toCid) {
	$fromWid = cid2wid($fromCid);
	$toWid = cid2wid($toCid);
	$fromProvince = sql_fetch_one_cell("select province from mem_world where wid=$fromWid");
	$toProvince = sql_fetch_one_cell("select province from mem_world where wid=$toWid");
	if (($fromProvince == 1) && ($toProvince != 1)) {//从司隶迁到其他州
		$userName = sql_fetch_one_cell("select name from sys_user where uid=$uid");
		$cityName = sql_fetch_one_cell("select name from sys_city where cid=$fromCid");
		$fromX = floor($fromCid / 1000);
		$fromY = ($fromCid % 1000);
		$toCity = sql_fetch_one_cell("select name from cfg_province where id=$toProvince");
		$toX = floor($toCid / 1000);
		$toY = ($toCid % 1000);
		$msg = sprintf($GLOBALS['changeCityPosition']['out_sili'],$userName,$cityName,$fromY,$fromX,$toCity,$toY,$toX);
		sendSysInform(0,1,0,300,1800,1,49151,$msg);
	} else if (($fromProvince != 1) && ($toProvince == 1)) {//从其他州迁入司隶
		$userName = sql_fetch_one_cell("select name from sys_user where uid=$uid");
		$cityName = sql_fetch_one_cell("select name from sys_city where cid=$toCid");
		$fromX = floor($fromCid / 1000);
		$fromY = ($fromCid % 1000);
		$toX = floor($toCid / 1000);
		$toY = ($toCid % 1000);
		$msg = sprintf($GLOBALS['changeCityPosition']['in_sili'],$userName,$cityName,$fromY,$fromX,$toY,$toX);
		sendSysInform(0,1,0,300,1800,1,49151,$msg);
	}
}

function resetCityGoodsAdd($uid,$cid)
{
	$buffers = sql_fetch_rows("select * from mem_user_buffer where uid='$uid' and buftype <= 4 and endtime > unix_timestamp()");
	$food_add = 0;
	$wood_add = 0;
	$rock_add = 0;
	$iron_add = 0;
	foreach($buffers as $buffer)
	{
		$buftype = $buffer['buftype'];
		if ($buftype == 1)
		{
			$food_add = 25;
		}
		else if ($buftype == 2)
		{
			$wood_add = 25;
		}
		else if ($buftype == 3)
		{
			$rock_add = 25;
		}
		else if ($buftype == 4)
		{
			$iron_add = 25;
		}
	}
	sql_query("update sys_city_res_add set goods_food_add=$food_add,goods_wood_add=$wood_add,goods_rock_add=$rock_add,goods_iron_add=$iron_add,resource_changing=1 where cid=".$cid);
}

function getChibiState4User($uid, $param) {
	$enterChibi = array_shift($param);
	$ret = array();
	$nobility = intval ( sql_fetch_one_cell ( "select nobility from sys_user  where uid = $uid" ) );
	//赤壁战场，推恩令不起作用
	$nobility = getBufferNobility ( $uid, $nobility );
	if($nobility < 5) {
		throw new Exception($GLOBALS['battle']['nobility_not_rearch_dafu']);
	}
	$ret[] = $nobility;
	
	// 检查史诗进度
	if(!sql_check("select 1 from mem_state where state=5 and value=1")) {
		throw new Exception($GLOBALS['battle']['not_complete_shishi']);
	}
	$ret[] = 1;
	
	if($enterChibi)
		$ret[] = 1;
	else 
		$ret[] = 0;
	return $ret;
}

//取得用户推恩令以后的爵位
function getBufferNobility($uid,$realnobility){

	//检查是不是有推恩令
	$bufparam = sql_fetch_one_cell("select bufparam from mem_user_buffer where uid='$uid' and (buftype=16 or buftype=18) order by bufparam desc limit 1");
	if(!empty($bufparam)){
		//如果有
		//推恩后的爵位
		$nobility = $realnobility+$bufparam;
		//推恩爵位不能大于关内侯    	
		if($bufparam==5){
			if($nobility>19){
				$nobility=19;
			}
		}
		if($bufparam==2){
			if($nobility>18){
				$nobility=18;
			}
		}
		//如果推恩后的爵位大于实际爵位
		if($nobility>$realnobility)
		return $nobility;
	}
	return intval($realnobility);
}


function sendOpenBoxInform($goodNames,$goodsvalue,$uid,$gid){
	if($gid==50)
	return ;
	$name=sql_fetch_one_cell("select name from sys_user where uid='$uid'");

	$allname="";
	$boxName=sql_fetch_one_cell("select name from cfg_goods where gid='$gid'");

	foreach($goodNames as $goodName){
		$allname.=$goodName." ";
	}
	$msg = sprintf($GLOBALS['open_box']['msg'],$name,$boxName,$allname,$goodsvalue);
	//sql_query("insert into sys_inform (`type`,`inuse`,`starttime`,`endtime`,`interval`,`scrollcount`,`color`,`msg`) values (0,1,unix_timestamp(),unix_timestamp()+600,50000,1,49151,'$msg')");
	sendSysInform(0,1,0,600,50000,1,49151,$msg);
	if(defined("USER_FOR_51") && USER_FOR_51){
		require_once("51utils.php");
		add51GoodsEvent($allname,$goodsvalue);
	}	
	if (defined("PASSTYPE")){
		try{
			require_once 'game/agents/AgentServiceFactory.php';
		    AgentServiceFactory::getInstance($uid)->addGoodsEvent($allname,$goodsvalue);
		}catch(Exception $e){
			try{
				file_put_contents("./agents/log/interface-error.log",date("Y-m-d H:i:s",time())." || ".$e->getMessage()."\n",FILE_APPEND);
			}catch(Exception $err){
				
			}
		}
	}	
}
//信息消息 sys_inform
function sendSysInform($type,$inuse,$starttime,$endtime,$interval,$scrollcount,$color,$msg){
	sql_query("insert into sys_inform (`type`,`inuse`,`starttime`,`endtime`,`interval`,`scrollcount`,`color`,`msg`) values ($type,$inuse,unix_timestamp()+'$starttime',unix_timestamp()+'$endtime',$interval,$scrollcount,$color,'$msg')");
	/*
	 * 平台接口
	 */
	if (defined("PASSTYPE")){
		try{
		    require_once 'game/agents/AgentServiceFactory.php';
			AgentServiceFactory::getInstance($uid)->addSysInformEvent($msg);
		}catch(Exception $e){
			try{
				file_put_contents("./agents/log/interface-error.log",date("Y-m-d H:i:s",time())." || ".$e->getMessage()."\n",FILE_APPEND);
			}catch(Exception $err){
				
			}
		}
	}
}

function isSentGood($gid){
	if($gid==10||$gid==12||$gid==17||$gid==18||$gid==20||$gid==21||$gid==25||$gid==26||$gid==40||$gid==52||$gid==56||$gid==71||$gid==72||$gid==82)
		return true;
	if($gid==97||$gid==100||$gid==115||$gid==117||$gid==120)
		return true;
	if($gid==202||$gid==203||$gid==204||$gid==205||$gid==207||$gid==212||$gid==213||$gid==214)
		return true;
	return false;
}

function getFieldName($ftype){
	$fieldname=$GLOBALS['fileName']['0'];
	if($ftype==1)
	$fieldname =$GLOBALS['fileName']['1'];
	if($ftype==2)
	$fieldname =$GLOBALS['fileName']['2'];
	if($ftype==3)
	$fieldname =$GLOBALS['fileName']['3'];
	if($ftype==4)
	$fieldname =$GLOBALS['fileName']['4'];
	if($ftype==5)
	$fieldname =$GLOBALS['fileName']['5'];
	if($ftype==6)
	$fieldname =$GLOBALS['fileName']['6'];
	if($ftype==7)
	$fieldname =$GLOBALS['fileName']['7'];
	return $fieldname;
}

function battleid2cid($battleid,$citypos){
	return ($battleid+600)*1000+$citypos;
}

//随机创建部队
function createSoldier($npcValue,$soldiers,$level){
	$times=pow(2,$level);
	$npcValue=$npcValue*$times;
	$soldiersarray = explode(",", $soldiers);
	$soldiervalue=array(0,23,31 ,70,90,135,140,298,285,875,1000,1375,2900,31,90,135,140,285,26,89,127,128,263);
	$totalRnd = 0;
	$valueMap = array();
	$npcSoldiers ="";
	$typecount=0;
	foreach ($soldiersarray as $sid){
		$rnd = rand() % (10 - $totalRnd);
		$rnd = ($totalRnd + $rnd)>10?(10-$totalRnd):$rnd;
		if($rnd!=0){
			$valueMap[$sid] = $rnd;

			$totalRnd += $rnd;
			$typecount++;
		}
		if ($totalRnd >= 10){
			break;
		}
	}

	foreach ($valueMap as $k=>$v){
		$npcSoldiers.=$k.",";
		$npcSoldiers.= (int)ceil($npcValue *$v *0.1 /$soldiervalue[$k]).",";
	}
	$npcSoldiers=$typecount.",".$npcSoldiers;
	return $npcSoldiers;
}

function updateBattleOpenState($uid,$armorId)
{
	if($armorId >=20000 && $armorId <=31000)//战场装备
	{
		
		$battleInfos = sql_fetch_rows("select a.bid,b.battleId from cfg_armor a, sys_battle_open_condition b where a.id = $armorId and b.dependBattleId = a.bid");
		if(!empty($battleInfos))
		{
			$info = $battleInfos[0];
			$armorInfo = sql_fetch_one("select a.medalTypeId from cfg_shop a,cfg_things b where a.gid = $armorId and a.group = 6 and a.battleGoodsType = 1 and a.medalTypeId = b.tid");
			$instr="";
			$tempstr = sql_fetch_one_cell("select group_concat(distinct armorid) from sys_user_armor where armorid>=20000 and armorid <= 31000 and uid = $uid");
			if (!empty($tempstr)) $instr="and gid not in ($tempstr)";
			$armor = sql_fetch_one("select * from cfg_shop  where medalTypeId = $armorInfo[medalTypeId] and battleGoodsType = 1 ". $instr ." limit 1 ");
			
			if(empty($armor))
			{
				
				sql_query("delete from sys_battle_open_condition where dependBattleId = $info[bid]");
				foreach ($battleInfos as $battleInfo)
				{
					$count = sql_fetch_one_cell("select count(*) from sys_battle_open_condition where battleId = $battleInfo[battleId]");
					if($count == 0)
					{
						openBattleField($battleInfo['battleId']);
					}
					
				}
			}						
		}				
	}
}

function updateBattleOpenTime($id)
{
	$timeSpan = sql_fetch_one_cell("select value from mem_state where state = 311");
	$count = sql_fetch_one_cell("select count(*) from sys_battle_open_condition where battleId = $id and isConditionDoing = 0");
	if($count == 0)
	{
		if(sql_fetch_one_cell("select openTime from cfg_battle_field where id= $id") == 0)
		{
			$sqlCommand = "update cfg_battle_field set openTime = unix_timestamp() + $timeSpan where id= $id" ;
        	sql_query($sqlCommand);
		}	
	}
		
}

function openBattleField($battleId)
{
	sql_query("update cfg_battle_field set state = 1 where id = $battleId");
	
	//$gid = (int)($battleId/1000) + 1000;
	//sql_query("update cfg_goods set inuse = 1 where gid=$gid");
	//sql_query("update cfg_shop set onsale = 1 where gid=$gid");
			
	sql_query("update sys_battle_open_condition set isConditionDoing = 1 where dependBattleId = $battleId");
	$battles = sql_fetch_rows("select battleId from sys_battle_open_condition where dependBattleId = $battleId");
	foreach($battles as $battle)
	{
		//设置定时器，如果系统里都是狗熊，无法在n天内完成战场开启条件，则系统在n天后强制开启战场
		updateBattleOpenTime($battle['battleId']);	
	}
	
}
function triggerHuangJinTask()
{
	$rows=sql_fetch_rows("select id from cfg_task where (id>10000 and id<10500) or(id>11000 and id<15000) ");
	foreach($rows as $row){
		$tid=$row["id"];
		sql_query("insert into sys_user_task (uid,tid,state) (select uid,'$tid',0 from sys_user_task where tid=243 and state=1) on duplicate key update state=0");	
	}
	sql_query("update mem_state set value = 0 where state = 5");
	//更新战场开启状态
   	$battleInfos = sql_fetch_rows("select battleId from sys_battle_open_condition where otherCondition = 'huangJinZhiLuanShiShiStart'");
	if(!empty($battleInfos))
	{					
		sql_query("delete from sys_battle_open_condition where otherCondition = 'huangJinZhiLuanShiShiStart'");
		foreach ($battleInfos as $battleInfo)
		{
			$count = sql_fetch_one_cell("select count(*) from sys_battle_open_condition where battleId = $battleInfo[battleId]");
			if($count == 0)
			{
				openBattleField($battleInfo['battleId']);
			}						
		}
	}
}

function checkUserAchivement($uid,$achivement_id) {
	if ($uid <897) return true;
	$achivement_id=intval($achivement_id);
	if(sql_check("select 1 from sys_user_achivement where uid='$uid' and achivement_id=$achivement_id")){
		return true;
	}
	return false;
}


//完成成就
function finishAchivement($uid,$achivement_id) {
	if ($uid <897) return ;
	if(sql_check("select 1 from cfg_achivement where id='$achivement_id' and state='0'")){   //如果成就已经关闭，就不要执行了
		return ;
	}	
	if(sql_check("select 1 from sys_user_achivement where uid=$uid and achivement_id=$achivement_id")){
		return;
	}
	$open_time=sql_fetch_one_cell("select open_time from cfg_achivement where id=$achivement_id");
	if ($open_time==1){//脱离新手保护为正常玩家
		$state=sql_fetch_one_cell("select state from sys_user where uid=$uid");
		if ($state!=0) return;
	}else if ($open_time==2){ //黄巾史诗任务完成,只有这样才能完成这些成就
		$state=sql_fetch_one_cell("select value from mem_state where state=5");
		if ($state!=1) return;
	}
	sql_query("INSERT INTO sys_user_achivement(uid,achivement_id,TIME) VALUES('$uid','$achivement_id',UNIX_TIMESTAMP())");
	$informs=sql_fetch_one("select union_inform,system_inform,name,point from cfg_achivement where id='$achivement_id'");
	sql_query("update sys_user set achivement_count=achivement_count+1,achivement_point=achivement_point+$informs[point] where uid=$uid");
	$userinfo=sql_fetch_one("select name,union_id from sys_user where uid='$uid'");
	$username=$userinfo["name"];
	$unionid=$userinfo["union_id"];
	$msg=sprintf($GLOBALS['Achivement']['congrats'],$username,$informs["name"]);
	if($informs["union_inform"]==1){
		addUnionEvent($unionid,11,$msg);
	}
	if($informs["system_inform"]==1){
		sendSysInform(0,1,0,600,50000,1,49151,$msg);
	}
}

function logUserAction($uid,$aid){	
	sql_query("insert into log_user_action(uid,aid,time) values ($uid,$aid,unix_timestamp())");
	sql_query("insert into log_action_count(uid,aid,count) values ($uid,$aid,1) on duplicate key update count=count+1");
}
//function logActionCount($uid,$aid){	
//	sql_query("insert into log_action_count(uid,aid,count) values ($uid,$aid,1) on duplicate key update count=count+1");
//}
function logActionCountback($uid,$aid){	
	sql_query("insert into log_user_action(uid,aid,time) values ($uid,$aid,unix_timestamp())");
	sql_query("insert into log_action_count(uid,aid,count) values ($uid,$aid,1) on duplicate key update count=count-1");
}
function getActionCount($uid,$aid){
	return sql_fetch_one_cell("select count from log_action_count where uid = $uid and aid = $aid");
}

//防沉迷
function isAdultOpen() {
	$value = 0;
	$value = sql_fetch_one_cell("select value from mem_state where state = 99");
	if($value != 1) {
		return false;
	}
	return true;
}
function logWaigUserInfo($uid, $ip)
{//记录外挂用户信息
	$upTimeThreshold = 10*60;//时间阀值上限(10分钟)
	$downTimeThreshold = 60;//时间阀值下限(1分钟)
	
	$logWaiguaFile = "./sessions/waigua/" . $uid;
	$curTime = $GLOBALS['now'];
	if (file_exists($logWaiguaFile) && ($curTime - filemtime($logWaiguaFile) < $downTimeThreshold)) {
		return;
	}
	
	$lastLoginTime = sql_fetch_one_cell("select max(update_time) as lastLoginTime from log_waigua_online where uid = $uid");//外挂最近一次登录时间
	if (empty($lastLoginTime)) {//第一次登录
		$lastLoginTime = 0;
	}
	$intervalTime = $curTime - $lastLoginTime;
	if ($intervalTime > $upTimeThreshold) {//距离上次登录已经超过阀值上限，判定为一次新的登录
		sql_query("insert into log_waigua_online(uid, login_time, update_time, ip) values ('$uid', '$curTime', '$curTime', '$ip')");
	} else {//仅更新最近的记录
		sql_query("update log_waigua_online set update_time = '$curTime' where uid = '$uid' and update_time = '$lastLoginTime'");
	}
	@touch($logWaiguaFile);//创建文件(第一次登录)或更新文件时间
}

function isUserLosted($lastupdate)
{
	$intervalTime = $GLOBALS['now'] - $lastupdate;
	if ($intervalTime >= 31*86400) {//一个月未上线，判定为“失踪”
		return true;	
	} else {
		return false;
	}
}
function getUID(){
	$uid=$_SESSION['currentLogin_uid'];
	if(empty($uid)){
		throw new Exception($GLOBALS['battlenet']['cannot_connect_server']);	
	}
	return $uid; 	
}
function getCID(){
	$uid=getUID();
	$cid=sql_fetch_one_cell("select lastcid from sys_user where uid='$uid'");
	if(empty($cid)){
		throw new Exception($GLOBALS['user_info']['getlastciderror']);
	}
	return $cid;
}
//finishAchivement(1063,30);
function openLock($uid,$param)
{
	$type = array_shift($param);   //废弃类型
	$tmp = array_shift($param);
	$password = trim(array_shift($param));
	
	$ret = array();
	$ret[] = $type;
	
	$firstSet = false;    //玩家之前有没有设置过二级密码
	$passwordTrue = false;   
	//先判断玩家是否设置过二级密码
	$pwIsExist = sql_fetch_one("select * from sys_user_password where uid='$uid'");
	if(empty($pwIsExist))
	{
		$firstSet = true;
		$ret[] = $firstSet;
		return $ret;
	}	
	$targetPassword = md5($password.$tmp);
	
	if(sql_check("select 1 from sys_user_password where uid='$uid' and password='$targetPassword'"))
	{
		$passwordTrue = true;
	}
	
	$ret[] = $firstSet;
	$ret[] = $passwordTrue;
	return $ret;
}

function modifyPassword($uid,$param)
{
	$oldPassword = trim(array_shift($param));
	$tmp = array_shift($param);
	$newPassword = trim(array_shift($param));
	
	$type=-1;
	$oldTargetPassword = md5($oldPassword.$tmp);
	$targetPassword = md5($newPassword.$tmp);
	
	$pwIsExist = sql_fetch_one("select * from sys_user_password where uid='$uid'");
	if(empty($pwIsExist))  //为空就直接插入数据，不为空就得判断原2级密码是否正确
	{
		sql_query("insert into sys_user_password(`uid`,`time`,`password`) values('$uid',unix_timestamp(),'$targetPassword')");
		$type = 1;
	}else 
	{
		if($oldTargetPassword != $pwIsExist['password'])
		{
			throw new Exception($GLOBALS['password']['oldpassword_error']);
		}
		sql_query("update sys_user_password set password='$targetPassword' where uid='$uid'");
		$type = 2;
	}
	$ret = array();
	$ret[] = $type;
	
	return $ret;
}
function dogetCurYear()
{
	$starttime = getAssingStartTime();
	$currentYear = sql_fetch_one("select u.name,b.count from sys_user u,log_luoyang_belong b where u.uid=b.uid and b.time>=$starttime limit 1");
	if(empty($currentYear))$currentYear['count']=0;
	return $currentYear;
}
function getAssingStartTime()
{
	$now = sql_fetch_one_cell("select unix_timestamp()");
	$cur23Hour = sql_fetch_one_cell("select unix_timestamp(curdate())")+82800;
	$curday=date('N',$now);
	$startTime=0;
	if($curday<3){
		$startTime = $cur23Hour-($curday+4)*86400;
	}elseif ($curday>3){
		$startTime = $cur23Hour-($curday-3)*86400;
	}else{
		$startTime = $now>=$cur23Hour?$cur23Hour:$cur23Hour-($curday+4)*86400;
	}
	return $startTime;
}
//=====
function getBuildingTasks($uid,$cid){//完成成长任务
    $buildingrets = sql_fetch_rows("select * from sys_building where `state` = '1' and `cid`='$cid' and `state_endtime` <= unix_timestamp()");
	if(!empty($buildingrets)){
	  sql_query("update `sys_building` set `state` = '0',`level` = (`level` + '1') where `state` = '1' and `cid`='$cid' and `state_endtime` <= unix_timestamp()");//建筑更新一级
	  sql_query("delete from mem_building_upgrading where `cid`='$cid' and `state_endtime` <= unix_timestamp()");
	  foreach($buildingrets as &$ret){
	     $citybid=$ret['bid'];
		 $level=$ret['level']+1;
		 $expot = $level*($level+1)*40 ;
		 if($citybid<5) {$expot = $level*($level+1)*80 ; UpdateUsersCityResource($uid,$cid);}
         switch ($citybid) {
            case 1:
                completeTask($uid, 15);
                if ($level >= 5) {
                    completeTask($uid, 165);
                }
                break;
            case 2:
                completeTask($uid, 16);
                if ($level >1) {
				    completeTask($uid, 160);
                    completeTask($uid, 163);
                }
                break;
            case 3:
                completeTask($uid, 17);
                if ($level >1) {
				    completeTask($uid, 75);
                    completeTask($uid, 137);
                }
                break;
            case 4:
                completeTask($uid, 18);
				if ($level >1 && $level <4) {
				    completeTask($uid,$level+531);
                }
                break;
            case 5:
                updateCityPeopleMax($cid);
                updateCityPeopleStable($cid);
                completeTask($uid, 1);
                if ($level >1) {
                    completeTask($uid,2);
                }
                $people_max = sql_fetch_one_cell("select sum(level*(level+1)*50) from sys_building where `cid`='{$cid}' and bid=" . ID_BUILDING_HOUSE);
                if ($people_max >= 1000) {
                    completeTask($uid, 3);
                }
                if ($people_max >= 10000) {
                    completeTask($uid, 4);
                }
                if ($people_max >= 50000) {
                    completeTask($uid, 5);
                }
                break;
            case 6:
                updateCityGoldMax($cid);
                completeTask($uid, $level + 25);
				if ($level > 1) {
                    completeTask($uid, 224);
                }
				if ($level ==4) {
				    completeTask($uid, 226);
                }
                if ($level == 5) {
                    completeTask($uid, 363);
                    sql_query('UPDATE sys_user SET `state`=0 WHERE `uid`=' . $uid);
                    sql_query("replace into sys_user_task (`uid`,`tid`,`state`) select '{$uid}',cfg_task.id,'0' from cfg_task where (cfg_task.id>11000 and cfg_task.id<15000) or (cfg_task.id>10000 and cfg_task.id<10500)");
                }
                if ($level == 6) {
                    completeTask($uid, 229);
                }
                if ($level == 8) {
                    completeTask($uid, 232);
                }
                if ($level == 10) {
                    completeTask($uid, 235);
                }
				$expot = $level*($level+1)*1250;
                break;
            case 7:
                completeTask($uid, $level + 35);
				checkUsersTechnic($uid);
                break;
            case 8:
                completeTask($uid, 90);
                break;
            case 9:
                completeTask($uid, ($level + 90));
                break;
            case 10:
                completeTask($uid, 82);
                break;
            case 11:
                completeTask($uid, 83);
				if ($level >1 ) completeTask($uid, 102500);
				if ($level >2 ) completeTask($uid, 2060359);
                break;
            case 12:
                completeTask($uid, 66);
                break;
            case 13:
                completeTask($uid, 22);
                break;
            case 14:
                completeTask($uid, 134);
                if ($level >= 2) {
                    completeTask($uid, 135);
                }
                break;
            case 15:
                completeTask($uid, 136);
                break;
            case 16:
                completeTask($uid, 166);
                break;
            case 17:
                completeTask($uid, 164);
                break;
            case 18:
                completeTask($uid, 167);
                break;
            case 19:
                completeTask($uid, $level + 169);
                break;
            case 20:
                completeTask($uid, $level + 137);
				$expot = $level*($level+1)*850;
                break;
            }
	      $chiefhid = sql_fetch_one_cell("SELECT `chiefhid` FROM sys_city WHERE `cid`='{$cid}'");
          if ($chiefhid == 0) {
                $chiefhid = sql_fetch_one_cell("SELECT `generalid` FROM sys_city WHERE `cid`='{$cid}'");
            }
          if ($chiefhid > 0) {
                addHeroExp($chiefhid, $expot*50);
            }
		}
	}
}
function refreshFoodArmyUsers($cid){//军队用粮
    $food_army_use = 0;
    $ownerfields = sql_fetch_rows("select wid from mem_world where `ownercid`='{$cid}'");
    if (!empty($ownerfields)) {
        $comma = '';
        foreach ($ownerfields as $mywid) {
            $fieldcids = $comma;
            $fieldcids = wid2cid($mywid['wid']);
            $comma = ',';
        }
        $food_army_use = sql_fetch_one_cell("select sum(fooduse)*2 from sys_troops where targetcid in ({$fieldcids}) and state in (2,3,4,5,6) and uid > 1000");
    }
    $food_army_use += sql_fetch_one_cell("select sum(c.food_use*s.count) from sys_city_soldier s,cfg_soldier c where s.cid=$cid and s.sid=c.sid");
    $food_army_use *= 2 / 3;
    sql_query("update mem_city_resource set `food_army_use`='{$food_army_use}' where `cid`='{$cid}'");
    return $food_army_use;
}
function checkUsersTechnic($uid){//同步用户不同城池的科技
    $cities = sql_fetch_rows("SELECT cid FROM sys_city WHERE `uid`='{$uid}'");
    foreach ($cities as $city) {
        $technics = sql_fetch_rows("SELECT * FROM sys_technic WHERE `uid`='{$uid}'");
        foreach ($technics as $technic) {
            $min_level = 10;
            $bids = sql_fetch_rows("select * from cfg_technic_condition where `tid`='{$technic['tid']}' and `pre_type`=0 and `pre_id`=7 group by `pre_id`");
            foreach ($bids as $bid) {
                $curr_building_level = sql_fetch_one_cell("select level from sys_building where `cid`='{$city['cid']}' and `bid`='{$bid['pre_id']}' order by `level` desc limit 1");
                $level = sql_fetch_one_cell("select level from cfg_technic_condition where `tid`='{$technic['tid']}' and `pre_type`=0 and `pre_id`='{$bid['pre_id']}' and `pre_level`<='{$curr_building_level}' order by `level` desc limit 1");
                $sys_level = sql_fetch_one_cell("select level from sys_technic where `tid`='{$technic['tid']}' and `uid`='{$uid}'");
                if (!$sys_level) {
                    $level = 0;
                }
                if (!$level) {
                    $level = 0;
                }
                $min_level = min($min_level, $level, $sys_level);
                sql_query("replace into sys_city_technic VALUES ('{$city['cid']}','{$technic['tid']}','{$min_level}')");
                $min_level = $level;
            }
        }
    }
}
function SetCityBaseProduce($cid){//设置用户城市资源、人口，黄金上限
     $food_max=0;
	 $wood_max=0;
	 $rock_max=0;
	 $iron_max=0;
	 $gold_max=0;
	 $people_max=0;
	 $foodadd=0;
	 $woodadd=0;
	 $rockadd=0;
	 $ironadd=0;
	 $cityresource = sql_fetch_rows("select * from sys_building where cid='$cid' and bid<7 order by bid desc,level desc ");
	  if(!empty($cityresource)){
	     foreach ($cityresource as $key => $cityres){
	         $cidbid=$cityres['bid'];
	         $level=$cityres['level'];
	         $res_maxs=sql_fetch_one("select * from cfg_building_level where bid='$cidbid' and level='$level'");
	         $res_max=$res_maxs['using_people'];
	         switch($cidbid){
	             case 1:{$food_max=$food_max+$res_max*1000;$foodadd=$foodadd+$res_max*10; break;}
		         case 2:{$wood_max=$wood_max+$res_max*1000;$woodadd=$woodadd+$res_max*10;break;}
		         case 3:{$rock_max=$rock_max+$res_max*500;$rockadd=$rockadd+$res_max*5;break;}
		         case 4:{$iron_max=$iron_max+$res_max*400;$ironadd=$ironadd+$res_max*4;break;}
		         case 5:{$people_max=$people_max+(100*$level+$level*($level-1)*100/2);break;}
		         case 6:{$gold_max=$res_max*100000;break;}
				}
	        }
		 $cktlevels = sql_fetch_one_cell("select level from sys_building where cid='$cid' and bid='17' limit 1");
		 if(!empty($cktlevels)){//仓库处理
		     $tlevels = sql_fetch_one_cell("select level from sys_city_technic where `tid`=15 and `cid`='{$cid}'");
		     if(empty($tlevels)) $tlevels = 0;
		     if($tlevels>$cktlevels) $tlevels = $cktlevels;
		     $tlevel = 1 + ($tlevels * 0.1);
			 $cktlevel = sql_fetch_one_cell("select 100*$tlevel*using_people from cfg_building_level where  bid=17 and level='$cktlevels'");
			 $cidstore = sql_fetch_one("select * from sys_city_res_add where  cid='$cid'");
		     $food_max=10000+$cktlevel*$cidstore['food_store']+$food_max * $tlevel;
		     $wood_max=10000+$cktlevel*$cidstore['wood_store']+$wood_max * $tlevel;
		     $rock_max=10000+$cktlevel*$cidstore['rock_store']+$rock_max * $tlevel;
		     $iron_max=10000+$cktlevel*$cidstore['iron_store']+$iron_max * $tlevel;
			}
		 sql_query("update mem_city_resource set wood_max='$wood_max',food_max='$food_max',rock_max='$rock_max',iron_max='$iron_max',gold_max='$gold_max',people_max='$people_max',changing='1' where cid='$cid'");
        }
    }
function getMyCityProducts($uid,$cid){
	$city = sql_fetch_one("select * from sys_city_res_add where cid=".$cid);
	if (empty($city)){
		sql_query("insert into sys_city_res_add (cid,food_rate,wood_rate,rock_rate,iron_rate,chief_add) values ('$cid',80,80,80,80,0)");
		$city = sql_fetch_one("select * from sys_city_res_add where cid='$cid'");
	}
	//需要劳力
	$food_all_people = sql_fetch_one_cell("select sum(l.using_people) from sys_building b,cfg_building_level l where b.bid=l.bid and b.level=l.level and b.cid='".$cid."' and b.bid=".ID_BUILDING_FARMLAND);
	$wood_all_people = sql_fetch_one_cell("select sum(l.using_people) from sys_building b,cfg_building_level l where b.bid=l.bid and b.level=l.level and b.cid='".$cid."' and b.bid=".ID_BUILDING_WOOD);
	$rock_all_people = sql_fetch_one_cell("select sum(l.using_people) from sys_building b,cfg_building_level l where b.bid=l.bid and b.level=l.level and b.cid='".$cid."' and b.bid=".ID_BUILDING_ROCK);
	$iron_all_people = sql_fetch_one_cell("select sum(l.using_people) from sys_building b,cfg_building_level l where b.bid=l.bid and b.level=l.level and b.cid='".$cid."' and b.bid=".ID_BUILDING_IRON);
	//生产能力
	$food_add_base = GLOBAL_FOOD_RATE * $food_all_people * GAME_SPEED_RATE;
	$wood_add_base = GLOBAL_WOOD_RATE * $wood_all_people * GAME_SPEED_RATE;
	$rock_add_base = GLOBAL_ROCK_RATE * $rock_all_people * GAME_SPEED_RATE;
	$iron_add_base = GLOBAL_IRON_RATE * $iron_all_people * GAME_SPEED_RATE;
	//科技加成
	$food_add_rate_technic = sql_fetch_one_cell("select level*10 from sys_city_technic t where tid=".ID_TECHNIC_FOOD." and cid='".$cid."'");
	$wood_add_rate_technic = sql_fetch_one_cell("select level*10 from sys_city_technic t where tid=".ID_TECHNIC_WOOD." and cid='".$cid."'");
	$rock_add_rate_technic = sql_fetch_one_cell("select level*10 from sys_city_technic t where tid=".ID_TECHNIC_ROCK." and cid='".$cid."'");
	$iron_add_rate_technic = sql_fetch_one_cell("select level*10 from sys_city_technic t where tid=".ID_TECHNIC_IRON." and cid='".$cid."'");
	//将领加成
	$chief_add = 0;
	$skill_add = array(0=>0);
	$chiefHero = sql_fetch_one("select c.chiefhid,h.* from sys_city c left join sys_city_hero h on c.chiefhid=h.hid where h.cid=$cid and c.cid=".$cid);
	if ($chiefHero['chiefhid'] > 0){    //有将领的情况下
	   $hid = $chiefHero['chiefhid'];
	   $chief_add = $chiefHero['affairs_add']+$chiefHero['affairs_base']+$chiefHero['affairs_add_on'];
	   $heroCommand = $chiefHero["level"]+$chiefHero["command_base"]+$chiefHero["command_add_on"];
	   $cityPeopleMax = sql_fetch_one_cell("select people_max from mem_city_resource where cid=".$cid);
	   $hufu=1;
		if(sql_check("select hid from mem_hero_buffer where hid='$chiefHero[chiefhid]' and buftype=1 and endtime>unix_timestamp()")){
			$hufu=1.5;
		}
		$leaderTechLevel = intval(sql_fetch_one_cell("select level from sys_city_technic where cid='$cid' and tid=6"));
		$peoplerate = $heroCommand*10.0* (100*$hufu + $leaderTechLevel * 10) / ($cityPeopleMax+1);

		if ($peoplerate > 1.0) $peoplerate = 1.0;
		$chief_add =  $chief_add * $peoplerate;

		//文曲星符增加内政25%
		if(sql_check("select hid from mem_hero_buffer where hid='$chiefHero[chiefhid]' and buftype=2 and endtime>unix_timestamp()"))
		{
			$chief_add=$chief_add*1.25;
		}
		
		if (!empty($hid)) {
			//将领技能
			$attrObj=0;
		    $attrObj = sql_fetch_one_cell("select b.id from sys_user_book a,cfg_book b where a.bid=b.id and a.level=b.level and a.bid=8 and a.hid=$hid");
			if (!empty($attrObj)) {//苛捐杂税
				$skill_add[0] = 1;
				$skill_add[] = $attrObj;
				$skill_add[] = $city['skill_gold_add'];
		    }
		    $attrObj=0;
			$attrObj = sql_fetch_one_cell("select b.id from sys_user_book a,cfg_book b where a.bid=b.id and a.level=b.level and a.bid=9 and a.hid=$hid");
		    if (!empty($attrObj)) {//安居乐业
		    	$skill_add[0] = 1;
		    	$skill_add[] = $attrObj;
		    	$res_add = array();
		    	$res_add[] = $city['skill_food_add'];
		    	$res_add[] = $city['skill_wood_add'];
		    	$res_add[] = $city['skill_rock_add'];
		    	$res_add[] = $city['skill_iron_add'];
		    	$skill_add[] = $res_add;
		    }
		    $attrObj=0;
			$attrObj = sql_fetch_one_cell("select b.id from sys_user_book a,cfg_book b where a.bid=b.id and a.level=b.level and a.bid=10 and a.hid=$hid");
		    if (!empty($attrObj)) {//五谷丰登
		    	$skill_add[0] = 1;
		    	$skill_add[] = $attrObj;
		    	$skill_add[] = $city['skill_food_add'];
		    }
		    $attrObj=0;
			$attrObj = sql_fetch_one_cell("select b.id from sys_user_book a,cfg_book b where a.bid=b.id and a.level=b.level and a.bid=11 and a.hid=$hid");
		    if (!empty($attrObj)) {//茂林密谷
		    	$skill_add[0] = 1;
		    	$skill_add[] = $attrObj;
		    	$skill_add[] = $city['skill_wood_add'];
		    }
		    $attrObj=0;
			$attrObj = sql_fetch_one_cell("select b.id from sys_user_book a,cfg_book b where a.bid=b.id and a.level=b.level and a.bid=12 and a.hid=$hid");
		    if (!empty($attrObj)) {//裂石穿云
		    	$skill_add[0] = 1;
		    	$skill_add[] = $attrObj;
		    	$skill_add[] = $city['skill_rock_add'];
		    }
		    $attrObj=0;
			$attrObj = sql_fetch_one_cell("select b.id from sys_user_book a,cfg_book b where a.bid=b.id and a.level=b.level and a.bid=13 and a.hid=$hid");
		    if (!empty($attrObj)) {//铸炼冶金
		    	$skill_add[0] = 1;
		    	$skill_add[] = $attrObj;
		    	$skill_add[] = $city['skill_iron_add'];
		    }
		}
	}
	$food_army_use = sql_fetch_one_cell("select food_army_use from mem_city_resource where cid='$cid'");
	$goods_food_endtime = 0;
	if ($city['goods_food_add'] > 0){
		$goods_food_endtime = sql_fetch_one_cell("select endtime from mem_user_buffer where uid='$uid' and buftype=1");
	}
	$goods_wood_endtime = 0;
	if ($city['goods_wood_add'] > 0){
		$goods_wood_endtime = sql_fetch_one_cell("select endtime from mem_user_buffer where uid='$uid' and buftype=2");
	}
	$goods_rock_endtime = 0;
	if ($city['goods_rock_add'] > 0){
		$goods_rock_endtime = sql_fetch_one_cell("select endtime from mem_user_buffer where uid='$uid' and buftype=3");
	}
	$goods_iron_endtime = 0;
	if ($city['goods_iron_add'] > 0){
		$goods_iron_endtime = sql_fetch_one_cell("select endtime from mem_user_buffer where uid='$uid' and buftype=4");
	}
    $ret = array();
	$ret['rate'] = $city;
	$ret['fpople'] = $food_all_people;
	$ret['wpople'] = $wood_all_people;
	$ret['rpople'] = $rock_all_people;
	$ret['ipople'] = $iron_all_people;
	$ret['fbase'] = $food_add_base;
	$ret['wbase'] = $wood_add_base;
	$ret['rbase'] = $rock_add_base;
	$ret['ibase'] = $iron_add_base;
	$ret['ftech'] = $food_add_rate_technic;
	$ret['wtech'] = $wood_add_rate_technic;
	$ret['rtech'] = $rock_add_rate_technic;
	$ret['itech'] = $iron_add_rate_technic;
	$ret['armyuse'] = floor($food_army_use);
	$ret['chiefadd'] = $chief_add;
	$ret['fmax'] = $food_max;
	$ret['wmax'] = $wood_max;
	$ret['rmax'] = $rock_max;
	$ret['imax'] = $iron_max;
	$ret['fendtime'] = $goods_food_endtime;
	$ret['wendtime'] = $goods_wood_endtime;
	$ret['rendtime'] = $goods_rock_endtime;
	$ret['iendtime'] = $goods_iron_endtime;
	$ret['skilladd'] = $skill_add;
	return $ret;
}
function UpdateUsersCityResource($uid,$cid){//资源更新
    $cityinfo = getMyCityProducts($uid,$cid);
    $city_rate = $cityinfo['rate'];
    $food_all_people = $cityinfo['fpople'];
    $wood_all_people = $cityinfo['wpople'];
    $rock_all_people = $cityinfo['rpople'];
    $iron_all_people = $cityinfo['ipople'];
    $food_add_base = $cityinfo['fbase'];
    $wood_add_base = $cityinfo['wbase'];
    $rock_add_base = $cityinfo['rbase'];
    $iron_add_base = $cityinfo['ibase'];
    $food_add_rate_technic = $cityinfo['ftech'];
    $wood_add_rate_technic = $cityinfo['wtech'];
    $rock_add_rate_technic = $cityinfo['rtech'];
    $iron_add_rate_technic = $cityinfo['itech'];
    $food_army_use = $cityinfo['armyuse'];
    $chief_add = $cityinfo['chiefadd'];
    $goods_food_endtime = $cityinfo['fendtime'];
    $goods_wood_endtime = $cityinfo['wendtime'];
    $goods_rock_endtime = $cityinfo['rendtime'];
    $goods_iron_endtime = $cityinfo['iendtime'];
	$skill_add = $cityinfo['skilladd'];
    $rate = sql_fetch_one("select food_rate,wood_rate,rock_rate,iron_rate from sys_city_res_add where `cid`='{$cid}'");
	$skill_food_add = sql_fetch_one_cell("select skill_food_add from sys_city_res_add where `cid`='{$cid}'");
	$skill_wood_add = sql_fetch_one_cell("select skill_wood_add from sys_city_res_add where `cid`='{$cid}'");
	$skill_rock_add = sql_fetch_one_cell("select skill_rock_add from sys_city_res_add where `cid`='{$cid}'");
	$skill_iron_add = sql_fetch_one_cell("select skill_iron_add from sys_city_res_add where `cid`='{$cid}'");
	if(empty($skill_food_add)) $skill_food_add=0;
	if(empty($skill_wood_add)) $skill_wood_add=0;
	if(empty($skill_rock_add)) $skill_rock_add=0;
	if(empty($skill_iorn_add)) $skill_iorn_add=0;
	$skill_food_add = ($food_add_base*$skill_food_add) / 100;
	$skill_wood_add = ($food_add_base*$skill_wood_add) / 100;
	$skill_rock_add = ($food_add_base*$skill_rock_add) / 100;
	$skill_iron_add = ($food_add_base*$skill_iron_add) / 100;
    sql_query("update mem_city_resource set people_working ='{$food_all_people}'*'{$rate['food_rate']}'/100+'{$wood_all_people}'*'{$rate['wood_rate']}'/100+'{$rock_all_people}'*'{$rate['rock_rate']}'/100+'{$iron_all_people}'*'{$rate['iron_rate']}'/100 where `cid`='{$cid}'");
    $city = sql_fetch_one("select * from mem_city_resource where `cid`='{$cid}'");
    $product_rate = $city['people'] / ($city['people_working'] == 0 ? 1 : $city['people_working']);
    if ($product_rate > 1) {
        $product_rate = 1;
    }
    sql_query("update sys_city_res_add set chief_add = '{$chief_add}' where `cid`='{$cid}'");
    if ($uid > 1000) {
        if ($food_add_base >= 1000) {
            completeTask($uid, 189);
        }
        if ($food_add_base >= 5000) {
            completeTask($uid, 190);
        }
        if ($food_add_base >= 10000) {
            completeTask($uid, 191);
        }
        if ($food_add_base >= 50000) {
            completeTask($uid, 192);
        }
        if ($wood_add_base >= 1000) {
            completeTask($uid, 193);
        }
        if ($wood_add_base >= 5000) {
            completeTask($uid, 194);
        }
        if ($wood_add_base >= 10000) {
            completeTask($uid, 195);
        }
        if ($wood_add_base >= 50000) {
            completeTask($uid, 196);
        }
        if ($rock_add_base >= 1000) {
            completeTask($uid, 197);
        }
        if ($rock_add_base >= 5000) {
            completeTask($uid, 198);
        }
        if ($rock_add_base >= 10000) {
            completeTask($uid, 199);
        }
        if ($rock_add_base >= 50000) {
            completeTask($uid, 200);
        }
        if ($iron_add_base >= 1000) {
            completeTask($uid, 201);
        }
        if ($iron_add_base >= 5000) {
            completeTask($uid, 202);
        }
        if ($iron_add_base >= 10000) {
            completeTask($uid, 203);
        }
        if ($iron_add_base >= 50000) {
            completeTask($uid, 204);
        }
    }
    $tlevels = sql_fetch_one_cell("select level from sys_city_technic where `tid`=15 and `cid`='{$cid}'");
	if(empty($tlevels)) $tlevels = 0;
	$tlevel = 1 + ($tlevels * 0.1);
    $food_add =floor(100 + $skill_food_add +((($food_add_base * $product_rate) * (1 + ((($food_add_rate_technic + $city_rate['field_food_add']) + $city_rate['goods_food_add']) + $chief_add) / 100)) * $city_rate['food_rate']) / 100);
    $food_max =floor( 10000 + ($food_add_base * $tlevel) * 100 );
    $wood_add =floor( 100 + ((($wood_add_base * $product_rate) * (1 + ((($wood_add_rate_technic + $city_rate['field_wood_add']) + $city_rate['goods_wood_add']) + $chief_add) / 100)) * $city_rate['wood_rate']) / 100);
    $wood_max =floor( 10000 + ($wood_add_base * $tlevel) * 100);
    $rock_add =floor( 100 + ((($rock_add_base * $product_rate) * (1 + ((($rock_add_rate_technic + $city_rate['field_rock_add']) + $city_rate['goods_rock_add']) + $chief_add) / 100)) * $city_rate['rock_rate']) / 100);
    $rock_max =floor( 10000 + ($rock_add_base * $tlevel) * 100);
    $iron_add =floor( 100 + ((($iron_add_base * $product_rate) * (1 + ((($iron_add_rate_technic + $city_rate['field_iron_add']) + $city_rate['goods_iron_add']) + $chief_add) / 100)) * $city_rate['iron_rate']) / 100);
    $iron_max =floor( 10000 + ($iron_add_base * $tlevel) * 100);
	$foods=($city['food']+($food_add-$food_army_use)/225)>0?floor($city['food']+($food_add-$food_army_use)/225):0;
	$woods=($city['wood'] + $wood_add/225)>$wood_max?floor($city['wood']):floor($city['wood'] + $wood_add/225);
	$rocks=($city['rock'] + $rock_add/255)>$rock_max?floor($city['rock']):floor($city['rock'] + $rock_add/255);
	$irons=($city['iron'] + $iron_add/255)>$iron_max?floor($city['iron']):floor($city['iron'] + $iron_add/255);
	$golds=($city['people']*$city['gold_rate']/10000*$city['tax']-$ciyt['hero_fee'])/225;
	$golds= ($golds+$city['gold'])>0?floor($golds+$city['gold']):0;
	sql_query("update mem_city_resource set `food`='{$foods}',`wood`='{$woods}',`rock`='{$rocks}',`iron`='{$irons}',`gold`='{$golds}',`food_add`='{$food_add}',`food_max`='{$food_max}',`wood_add`='{$wood_add}',`wood_max`='{$wood_max}',`rock_add`='{$rock_add}',`rock_max`='{$rock_max}',`iron_add`='{$iron_add}',`iron_max`='{$iron_max}',`changing`='1' where `cid`='{$cid}'");
    //sql_query("update mem_city_resource set food=case when((food_add-food_army_use)>0) then LEAST(food+(food_add-food_army_use)/225,GREATEST(food,food_max)) else GREATEST(0,food+(food_add-food_army_use)/225) end,wood=LEAST(GREATEST(wood_max,wood),wood+wood_add/255),rock=LEAST(GREATEST(rock_max,rock),rock+rock_add/255),iron=LEAST(GREATEST(iron_max,iron),iron+iron_add/255),gold=case when((people*gold_rate/10000*tax-hero_fee)>0) then LEAST(GREATEST(gold_max,gold),gold+(people*gold_rate*tax/10000-hero_fee)/225) else GREATEST(0,gold+(people*gold_rate*tax/10000-hero_fee)/225) end where exists(select cid from sys_city where uid>1000 and cid=mem_city_resource.cid) and vacation=0 and forbidden=0");
  	//sql_query("update mem_city_resource set food='$foods',wood='$woods',rock='$rocks',iron='$irons',gold='$golds' where cid='$cid'");
  	updateCityPeopleMax($cid);
    updateCityPeopleStable($cid);
    updateCityGoldMax($cid);
}
function throwUsersHeroField($hero){
    $hid = $hero['hid'];
	sql_query("delete from mem_hero_blood where hid='$hid'");
	sql_query("delete from sys_hero_armor where hid='$hid'");
	sql_query("update sys_user_armor set hid=0 where hid='$hid'");
	sql_query("update sys_user_book set hid=0 where hid=$hid");
	//把人往野地里面丢，如果没有人的话，就放在上面，如果有人的话，如果这个人也是NPC，则另外找地方，如果这个人不是NPC的话，就替代他的位置。
	$findtimes = 10;    //找十次，如果找不到的话就丢掉了
	if(isActHero($hero["herotype"]) || isCardHero($hero["herotype"])){
		$findtimes=0;
	}else if ($hero['npcid']>0){
		$findtimes=40;//名将多找几次
	}
	while($findtimes > 0){
		$findtimes--;
		if ($hero['npcid'] > 0){
			$wid = sql_fetch_one_cell("select wid from mem_world where ownercid=0 and province<=13 and type > 1 and state=0 order by rand() limit 1");
		} else {
			$wid = sql_fetch_one_cell("select wid from mem_world where ownercid=0 and type > 1 and state=0 order by rand() limit 1");
		}
		$newcid = wid2cid($wid);
		$oldhero = sql_fetch_one("select * from sys_city_hero where uid=0 and cid='$newcid'");
		if(empty($oldhero)){ //该地点无人
			sql_query("update sys_city_hero set cid='$newcid',state=4,uid=0 where hid=$hid");
			break;
		}
		else{//有人
		  if ($oldhero['npcid'] > 0){    //也是一个NPC
			//重新找过
				continue;
			}
			else{    //不是NPC，算他倒霉，要被砍掉
			 sql_query("update sys_city_hero set cid=$newcid,state=4,uid=0,loyalty=70 where hid=$hid");
			 sql_query("delete from sys_city_hero where hid=$oldhero[hid]");
			 sql_query("delete from mem_hero_blood where hid='$oldhero[hid]'");
			 if(!isActHero($hero["herotype"]))
			     sql_query("insert into sys_recruit_hero (`name`,`npcid`,`sex`,`face`,`cid`,`level`,`exp`,`affairs_add`,`bravery_add`,`wisdom_add`,`affairs_base`,`bravery_base`,`wisdom_base`,`loyalty`,`gold_need`,`gen_time`) values ('$oldhero[name]','$oldhero[npcid]','$oldhero[sex]','$oldhero[face]','0','$oldhero[level]','$oldhero[exp]','$oldhero[affairs_add]','$oldhero[bravery_add]','$oldhero[wisdom_add]','$oldhero[affairs_base]','$oldhero[bravery_base]','$oldhero[wisdom_base]','66',0,unix_timestamp())");
			 $troop = sql_fetch_one("select * from sys_troops where uid=0 and cid='$newcid' and hid=$oldhero[hid]");
			 if (!empty($troop)){
				  sql_query("update sys_troops set hid=$hid where id=$troop[id]");
				}
			 break;
			}
		}
	}
	if ($findtimes == 0){    // 十次都没有找到，砍了
	  if ($hero['npcid']>0){
			return;//名将不删
		}
		sql_query("update sys_troops set hid=0 where hid='$hid'");
		sql_query("delete from sys_city_hero where hid='$hid'");
		if((!isActHero($hero["herotype"])) || (!isCardHero($hero["herotype"]))){
			sql_query("insert into sys_recruit_hero (`name`,`npcid`,`sex`,`face`,`cid`,`level`,`exp`,`affairs_add`,`bravery_add`,`wisdom_add`,`affairs_base`,`bravery_base`,`wisdom_base`,`loyalty`,`gold_need`,`gen_time`) values ('$hero[name]','$hero[npcid]','$hero[sex]','$hero[face]','0','$hero[level]','$hero[exp]','$hero[affairs_add]','$hero[bravery_add]','$hero[wisdom_add]','$hero[affairs_base]','$hero[bravery_base]','$hero[wisdom_base]','66',0,unix_timestamp())");
		}
	}
}
?>