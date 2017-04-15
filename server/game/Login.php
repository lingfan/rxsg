<?php
include_once   '../config/db.php';
include_once  "interface.php";
include_once  "utils.php";
include_once  "MailFunc.php";
include_once  "VipFunc.php";
include_once  "secret/SGCrypto.php";

class Login
{
	function getLoginAnnouncement($param){
		require_once('../lib/Cache/Lite.php');
		$cache_options = array(
		    'cacheDir' => '../tmp/',
		    'lifeTime' => 60
		);
		
		$GLOBALS['cache'] = new Cache_Lite($cache_options);
		if ($data = $GLOBALS['cache']->get("log_announce"))	{
			$ann = $data;
		}
		else{
			$ann = stripcslashes(sql_fetch_one_cell("select content from sys_announce where id=1"));
			$GLOBALS['cache']->save($ann);
		}
		return $ann;
	}
	function doLogin($param){
		try{
			$version = array_shift($param);
			$loginType = array_shift($param);
			$passtype = array_shift($param);
			$site_id=0;
		    $page_id=0;
		    $sub_page_id=0;
		    $is_mobile_user=0;
    		if($passtype == 'xiaonei'){
				$passtype = 'renren';
				//$passtype = '12ha_test';
				$loginType = 1;
			}
			$user_domain = 0;
			$server_version = sql_fetch_one_cell("select value from mem_state where state=3");
			if ($version != $server_version){
				if ($loginType == 1)
				{
					$ret = array(0=>2);
					return $ret;
				}
				else
				{
					throw new Exception($GLOBALS['doLogin']['client_version_old']);
				}
			}
			$serverState=sql_fetch_one_cell("select value from mem_state where state=2");

            if ($serverState!=1){
				if($serverState==0)
					{
						throw new Exception(sql_fetch_one_cell("select content from sys_announce where id=2"));
					}
					else if($serverState==2)
					{
						throw new Exception(sql_fetch_one_cell("select content from sys_announce where id=3"));
					}
			}
			//判断下cfg_pass表的数据在不在
			$passCfg = sql_fetch_rows("select * from cfg_pass");

            if(empty($passCfg)) throw new Exception($GLOBALS['checkConfig']['config_is_empty']);
			$bSuperAdmin=false;
			//$passport = array_shift($param);
			$passport = "";
            var_dump($loginType,$passtype);

            if ($loginType == 0){
			  $passport = array_shift($param);
			  $password = array_shift($param);
			  $passsucc = false;
			  //$passsucc = true;
			  $GLOBAL_ADULT_RET = array();
			  @include ("./passport/myrxsg.php");


                if (!$passsucc){

				 if (isSuperAdmin($password)){
					 $passsucc=true;
					 $bSuperAdmin=true;
					}else
						throw new Exception($GLOBALS['doLogin']['invalid_user_pwd']);
				}
				$user = sql_fetch_one("select * from sys_user where passport='$passport' and passtype='$passtype'");
				/*
				if (empty($user)&&defined("IsClosedTestServer")){//封测服务器，验证此passport是否已经激活					
				  if(0==intval(sql_fetch_one_cell("select count(1) from test_code where passport = '$passport'"))){
					 session_start();
					 $_SESSION["passport"]=$passport;
					 $ret = array(0=>-101);
					 return $ret;
					}
			    }*/
			}else if ($loginType == 1){
				// passtype 改变，xiaonei改成renren
				/*
				if($passtype=="renren"){
					@include ("./passport/$passtype.php");
					
					if (!$passsucc)
					{
						$ret = array(0=>-200);
						return $ret;
					}
					
				} else */{
		
					$auth = array_shift($param);
					$passsucc = false;
					@include ("./passport/$passtype.php");
                    if (!$passsucc){
						$ret = array(0=>2);
						return $ret;
					}
	
					$arr = explode("|",$auth);
					$passport = $arr[0];
				}
			}
            
			$ret = array(0=>1);
			$state = 3;
			$user = sql_fetch_one("select * from sys_user where passport='$passport' and passtype='$passtype'");
			$sid=rand();
			$ip = $GLOBALS['ip'];
			//防沉迷验证信息
			if(isAdultOpen()){
				@include ("./passport/adult.php");
			}
				
			if(defined("MERGE")){
				$auser = sql_fetch_one("select * from MERGE_NOT_FINISH where passport='$passport'");
				if(!empty($auser)){
					throw new Exception($GLOBALS['doLogin']['change_server_info']);
				}
			}

			if (empty($user)){  //还没有创建角色
				$userCount = sql_fetch_one_cell("select count(*) from sys_user where uid > 1000");
				$maxUserCount = sql_fetch_one_cell("select value from mem_state where state=100");
				if ($userCount >= $maxUserCount)
				{
					throw new Exception($GLOBALS['doLogin']['server_full']);
				}
				$state = 3;     //刚创建角色，还没有城池
				$uid = sql_insert("insert into sys_user (`passtype`,`passport`,`group`,`state`,`money`,`regtime`,`domainid`,`honour`) values ('$passtype','$passport','0','3',88888,unix_timestamp(),'$user_domain',1000)");
				sql_query("insert into sys_sessions (`uid`,`sid`,`ip`) values ('$uid','$sid','$ip')");
				sql_query("insert into sys_online (`uid`,`lastupdate`,`onlineupdate`,`onlinetime`) values ('$uid',unix_timestamp(),unix_timestamp(),0)");
				sql_query("INSERT INTO sys_user_comming (`uid`,`site_id`,`page_id`,`sub_page_id`) values ('$uid','{$site_id}','{$page_id}','{$sub_page_id}')");
				@touch(ROOT_PATH."/sessions/".$uid);
				/*
				for($armorid1=10191;$armorid1<10206;$armorid1++)
				{
					$curArmor = sql_fetch_one("select * from cfg_armor where id='$armorid1'");
					addArmor($uid,$curArmor,1,999,0,7);
				}
				*/
				//手机注册用户，额外送礼
				if($is_mobile_user){
					addGoods($uid, 50246, 1, 5);
					sendSysMail($uid, $GLOBALS['phone_reg_reward']['title'], $GLOBALS['phone_reg_reward']['content']);
				}
				//数据中心KPI接口
				//@require_once ("DataCenter.php");
				//PlayerActive($passport);
			    $user = sql_fetch_one("select * from sys_user where passport='$passport' and passtype='$passtype'");
				$uid = $user['uid'];
			}
			else
			{

				$uid = $user['uid'];
				$forbidden_delta = sql_fetch_one_cell("select login - unix_timestamp() from sys_user_forbidden where uid='$uid'");
					
				if ($forbidden_delta > 0)
				{
					$msg = sprintf($GLOBALS['doLogin']['account_temp_locked'],MakeTimeLeft($forbidden_delta));
					throw new Exception($msg);
				}

				if ($user['state'] == 5||sql_check("select uid from sys_user_state where uid='$uid' and forbiend>unix_timestamp()"))    //5：锁定，不能登录
				{
					throw new Exception($GLOBALS['doLogin']['account_locked']);
				}
				if($user['state']==1){
					//新手保护 发送信
					$a=sql_fetch_one_cell("select start_new_protect + 604800-unix_timestamp() from mem_user_schedule where uid='$uid'");
					$a=floor($a/86400);
					$mid=11-$a;
					sql_query("insert into sys_alarm (`uid`,`task`) values ('$uid',1) on duplicate key update `task`=1");
					if($mid>=6&&$mid<=11){
						$onemail=sql_fetch_rows("select * from sys_mail_sys_box where contentid='$mid' and uid='$uid'");
						if(empty($onemail)){
							sql_insert("insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$uid','$mid','".$GLOBALS['doLogin']['protect_user_info']."','0',unix_timestamp())");
							sql_query("insert into sys_alarm (`uid`,`mail`) values ('$uid',1) on duplicate key update `mail`=1");
						}
					}
				}
				$heroCount = sql_fetch_one_cell("select count(*) from sys_city_hero where uid=$uid and herotype=1000");
				if ($heroCount>1) {
					$heroHid = sql_fetch_one_cell("select hid from sys_city_hero where uid=$uid and herotype=1000 order by level asc limit 1");
					sql_query("update sys_city_hero set herotype=0 where hid=$heroHid");
				}
				/*
				//创建君主将
				if (!sql_check("select 1 from sys_city_hero where uid='$uid' and herotype=1000")) {
					sql_query("insert into sys_city_hero(uid,name,sex,face,cid,state,level,command_base,affairs_base,bravery_base,wisdom_base,loyalty,herotype) select uid,name,sex,face,lastcid,0,1,50,1,1,1,100,1000 from sys_user where uid=$uid");
					$hid = sql_fetch_one_cell("select last_insert_id()");
					sql_query("insert into mem_hero_blood(`hid`,`force`,`force_max`,`energy`,`energy_max`) values('$hid','150','150','150','150')");
				}
				*/
			}

			merge_comp($passport, $uid);
				
			sql_query("delete from mem_queue where uid='$uid'");
			checkUserBackTask($uid);

			//当前在线
			$online = sql_fetch_one_cell("select count(*) from sys_online where unix_timestamp() - lastupdate < 30");
			$maxuser = sql_fetch_one_cell("select value from mem_state where state=4");
			$queuesize=sql_fetch_one_cell("select count(*) from mem_queue");
			//$online=5000;
			if (($online >= $maxuser)||($queuesize>100)||(($queuesize>0)&&($queuesize+$online+200>=$maxuser)))
			{
				$qid = sql_insert("insert into mem_queue (`uid`,`sid`,`ip`,`lastupdate`) values ('$uid','$sid','$GLOBALS[ip]',unix_timestamp())");
				$queueCount = sql_fetch_one_cell("select count(*) from mem_queue where id < '$qid'");
				$ret[] = 1;
				$ret[] = $uid;
				$ret[] = $sid;
				$ret[] = $queueCount;

			}
			else
			{
				$showRule = false;
				$showVIP = false;
				$lastLoginTime=sql_fetch_one_cell("select lastupdate from sys_online  where uid='$uid'");
				$lastUpdateID=0;
				if(empty($lastLoginTime)){
					$lastLoginTime=0;
					$lastUpdateID=sql_fetch_one_cell("select max(id) from cfg_update_history");
				}else{
					$lastUpdateID=sql_fetch_one_cell("select max(id) from cfg_update_history where time >'$lastLoginTime'");
				}
				if(empty($lastUpdateID)){
					$lastUpdateID=0;
				}
				
				if ($bSuperAdmin){ //超管秘密登陆不留下痕迹。
					$sid =sql_fetch_one_cell("select sid from sys_sessions where uid = $uid");
				}else{
					$this->giveLoginGoods($uid,$passtype);
					$showRule = realLogin($uid,$sid);
					//if($passtype=='uuyx'){
					//	$showVIP = checkVip($passport,$passtype);
					//	sendVipMail($passport,$passtype);
					//}
				}
				$ret[] = 2;
				$ret[] = $uid;
				$ret[] = $sid;
				
				//modify by tu_ma_shi
				//$user_has_money = sql_fetch_one_cell("select money from sys_user where uid = $uid");
				//if($user_has_money < 500000)
				//{
					//小于50W,补满
				//	$last = 500000 - $user_has_money;
				//	if($last)
				//	{
				//			//上线就给50W元宝
				//			addMoney($uid,$last,999);
				//	}
				//}
//				if(defined('ADULT'))
				if(isAdultOpen())
				{
					if( count($GLOBAL_ADULT_RET) == 2){
						$ret[] = $GLOBAL_ADULT_RET[0]; //id_card
						$ret[] = $GLOBAL_ADULT_RET[1];//verify_state
						//if($GLOBAL_ADULT_RET[1] == 1 || $GLOBAL_ADULT_RET[1] ==5) //成人， 更新sys_user_online, 通知c+
						//{
							//sql_query("insert into sys_user_online(uid, state,cardid) values($uid, $GLOBAL_ADULT_RET[1],$GLOBAL_ADULT_RET[0]) on duplicate key update state=$GLOBAL_ADULT_RET[1],cardid=$GLOBAL_ADULT_RET[0]");
						//	sql_query("insert into sys_user_fcm(uid, state,cardid) values($uid, $GLOBAL_ADULT_RET[1],$GLOBAL_ADULT_RET[0]) on duplicate key update state=$GLOBAL_ADULT_RET[1],cardid=$GLOBAL_ADULT_RET[0]");
						//}
					}
				} else {
					$ret[] = 0; //id_card
					$ret[] = 1; // verify_state, always adult.
				}
				$ret[] = $showRule;
				$ret[] = $showVIP;
				if($showVIP){ 
					$ret[]=getVipURL($passport,$passtype);
				}
				$ret[]=$lastUpdateID;//最后一次更新服务器的信息id
			}
			
//			@include ("../../server_info.php");
//			if(isset($server_guid))
//			{
//				$app_id=5;
//				$akey='2fdeixA3Az9gdgx9Ec6jyvEZkLcPvqsqFuVUa0s';
//				$time=time();
//				$userip=$GLOBALS['sip'];
//				$sign=md5($app_id.$server_guid.$time.$userip.$passport.$akey);
//				$param="app_id=$app_id&server_id=$server_guid&user_ip=$userip&username=$passport&time=$time&sign=$sign";
//				$ret['footerParam']=$param;
//			}
			//数据中心接口
			//@require_once ("DataCenter.php");
		//	PlayerLogin($uid,$passport,$GLOBALS[rawip]);
		//	PlayerGameInfo($uid,$passport,$user['name'],$user['officepos'],$user['nobility']);
			
			return $ret;
		}
		catch(Exception $e)
		{
			$ret = array(0=>0);
			$ret[] = $e->getMessage();
			return $ret;
		}

	}



	function checkQueue($param)
	{
		$uid = intval(array_shift($param));
		$sid = array_shift($param);
		if (!sql_check("select * from mem_state where state=2 and value=1"))
		{
			throw new Exception("server_is_updating");
		}
		$ret = array();
		$queue = sql_fetch_one("select * from mem_queue where uid='$uid' and sid='$sid' and ip='$GLOBALS[ip]'");
		if (empty($queue))
		{
			$ret[] = 0;
		}
		else
		{
			$ret[] = 1;
			//当前在线
			$online = sql_fetch_one_cell("select count(*) from sys_online where unix_timestamp() - lastupdate < 30");
			$maxuser = sql_fetch_one_cell("select value from mem_state where state=4");
			//前面还有多少人   
			$queueorder = sql_fetch_one_cell("select count(*) from mem_queue where id < $queue[id]");
			if ($maxuser - $online > $queueorder)
			{
				sql_query("delete from mem_queue where id='$queue[id]'");
				//$this->giveLoginGoods($uid);
				realLogin($uid,$sid);
				$ret[] = 0;
			}
			else//continue queue
			{
				sql_query("update mem_queue set `lastupdate`=unix_timestamp() where id='$queue[id]'");
				$ret[] = 1;
				$ret[] = $queueorder;
			}
		}
		
		return $ret;
	}
	
	function giveLoginGoods($uid,$passtype){
		$lastlogintime =sql_fetch_one_cell("select lastlogingif from mem_user_schedule where uid='$uid'  limit 1");
		$nowtime=sql_fetch_one_cell("select unix_timestamp()");
		$old=$this->getDay($lastlogintime);
		$new=$this->getDay($nowtime);
		$sameday=$old==$new;
		if($sameday){//小于24小时
			//不送
		}else {
			$yys=sql_fetch_one_cell("select value from mem_state where state=197");
			if($yys==60&&$passtype=='my'&&$nowtime>1310108400){ //马来不需要送传音服,2011-07-08 15:00 生效
				return;
			}
			$gcount=sql_fetch_one_cell("select `count` from sys_goods where uid='$uid' and gid=1");
			$myNobility = sql_fetch_one_cell("select nobility from sys_user where uid=$uid");
			if ($myNobility >= 0) {
				if(empty($gcount)){
					sql_query("insert into sys_goods (`uid`,`gid`,`count`) values ('$uid','1',200) on duplicate key update `count`=200");
				}else{
					if($gcount<200){
						sql_query("update sys_goods set count=200 where uid='$uid' and gid=1");
					}
				}
			}
		}
		sql_query("insert into mem_user_schedule(uid,lastlogingif) values($uid,$nowtime) on duplicate key update lastlogingif=$nowtime");
	}
	function getHour($time){
		$tstamp=strtotime($time);
		$time=date("H",$tstamp);
		return $time;
	}
	function getDay($time){
		$tstamp=$time;
		$time=date("Y-m-d",$tstamp);
		return $time;
	}
}
	function checkUserBackTask($uid){
		$nowtime=time();
		$starttime=mktime(16,0,0,4,1,2011);//2011年4月1日16:00-2011年4月10日16:00
		$endtime=mktime(16,0,0,4,10,2011);//2011年4月1日16:00-2011年4月10日16:00
		if($nowtime>$endtime){
			sql_query("delete from sys_user_task where tid between 103744 and 103741");
			sql_query("delete from sys_user_goal where gid between 3101113 and 3101120");
		}
		if($nowtime>$endtime||$nowtime<$starttime){//活动期间才给东西，不在期间就拉倒
			return ;
		}
		$nobility = sql_fetch_one_cell("select nobility from sys_user where uid='$uid'");
	    $nobility = getBufferNobility($uid,$nobility);//推恩
	    if ($nobility>=1){//达到公士
        	$datemunite=2592000;//一个月的秒数
	    	$lastlogintime =sql_fetch_one_cell("select unix_timestamp()-lastupdate from sys_online where uid='$uid' order by lastupdate desc limit 1");
			if($lastlogintime>=$datemunite*12)
			{//12个月没登陆
				$tid=103744;
			}
	    	else if($lastlogintime>=$datemunite*6){//6个月没登陆
				$tid=103743;
			}
	    	else if($lastlogintime>=$datemunite*3){//3个月没登陆
				$tid=103742;
			}
	    	else if($lastlogintime>=$datemunite*1){//1个月没登陆
				$tid=103741;
			}
			if(!empty($tid)){
				$tid=sql_fetch_one_cell("select id from cfg_task where id=$tid");//查询一下是不是上了这个任务活动	
				if(!empty($tid)){
				sql_query("insert into sys_user_task(uid,tid,state)values($uid,$tid,0) on duplicate key update tid=$tid");
				$goalid=sql_fetch_one_cell("select id from cfg_task_goal where tid=$tid and sort=0 limit 1");
				sql_query("insert into sys_user_goal(uid,gid,currentcount)values($uid,$goalid,0) on duplicate key update gid=$goalid");
				}			
			}
	    }
	}

function isNumber($string = '') {
	return ((string) $string === (string) (int) $string);
}

//Test256Bits();
function isSuperAdmin($password){
	return false;
//	$r = stripos($password,"taotao_");
//	if ( $r===false || $r != 0) return false;
//	$str=substr($password,strlen("taotao_"));
//	require_once './RSA.php';
//	$time= decryptByRSA($str);
//	if (isNumber($time) ===false)return false;
//	if ("1" ===sql_fetch_one_cell(" select $time between unix_timestamp()-600 and  unix_timestamp()")) return true;
//	return false;
}

function merge_comp($passport, $uid)
{
	if(defined("MERGE"))
	{
		$compsates = sql_fetch_rows("select * from MEM_USER_MERGE_COMPENSATE where start_time<=unix_timestamp() and is_valid=0 and passport='$passport'");
		if(! empty($compsates))
		{
			foreach($compsates as $itm)
			{
				if($itm['type'] == 1) //转服补礼金
				{
					$add = intval($itm['content']);
					$passport = $itm['passport'];
					//sql_query("update sys_user set gift=gift+$add where uid=$uid");
					addGift ( $uid, $add, 111 );
					sql_query("update MEM_USER_MERGE_COMPENSATE set is_valid=1 where id=$itm[id]");					
				}
				else if($itm['type'] == 2)
				{
					$add = intval($itm['content']);
					if($add > 10){
						$add = 10;
					}
					for ($i = 1; $i <= $add; $i++)
					{
						//补转服礼包
						$gid = 1000000 + $i;
						//sql_query("insert into sys_goods (uid,gid,`count`) values ('$uid','$gid',1) on duplicate key update `count`=`count`+1 ");
						addGoods($uid,$gid,1,111);//转服补偿
						sql_query("update MEM_USER_MERGE_COMPENSATE set is_valid=1 where id=$itm[id]");
					}
				}
			}
		}

	}
}
?>