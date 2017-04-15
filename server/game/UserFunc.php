<?php
require_once("./interface.php");
require_once("./utils.php");
require_once("./GoodsFunc.php");
require_once("./HeroFunc.php");
//require_once 'DataCenter.php';

function loadGlobalData($uid,$param)
{
	$ret = array();
	$ret[] = sql_fetch_one_cell("select unix_timestamp()");
	$ret[] = sql_fetch_rows("select bid,name,description from cfg_building order by bid");
	$ret[] = sql_fetch_rows("select tid,name from cfg_technic order by tid");
	$ret[] = sql_fetch_rows("select * from cfg_soldier order by sid");
	$ret[] = sql_fetch_rows("select * from cfg_defence order by did");
	$ret[] = sql_fetch_rows("select level,upgrade_exp,total_exp from cfg_hero_level order by level");
	//$ret[] = sql_fetch_rows("select gid,name from cfg_goods where inuse=1");
	$ret[] = sql_fetch_rows("select tid,name from cfg_things where inuse=1");
	$ret[] = sql_fetch_rows("select `count`,`desc` from cfg_count_desc order by `count` desc");
	$ret[] = sql_fetch_rows("select `id`,`name` from cfg_office_pos order by id");
	$ret[] = sql_fetch_rows("select `id`,`name` from cfg_nobility order by id");
	$ret[] = chat_host;
	$ret[] = chat_port;
	$ret[] = THE_SERVER_ID;
	$ret[] = SERVER_NAME;
//	$ret[] = battlenet_chat_host;
//	$ret[] = battlenet_chat_port;
	// this bool field indicates whether the cross battle is enabled in GUI
	if(defined("BATTLE_NET_ENABLE")) 
	//if(constant(BATTLE_NET_ENABLE))
		$ret[] = BATTLE_NET_ENABLE;
	else 
		$ret[] = false;
	//$ret[] = "http://10.0.0.4:8000/sg_login_bulletin/sg_bulletin/Index.aspx/";
	$ret[] = chibi_chat_host;
	$ret[] = chibi_chat_port;
	
	if(defined("CHIBI_NET_ENABLE")) 
	//if(constant(BATTLE_NET_ENABLE))
		$ret[] = CHIBI_NET_ENABLE;
	else 
		$ret[] = false;
		
	$ret[]=null;
	$ret[]=null;
	//马来订制
	$ret[]=sql_fetch_one_cell("select value from mem_state where state=197");
	//18禁不禁
	$ret[]=sql_fetch_one_cell("select value from mem_state where state=18");
	return $ret;
}
function getUserCities($uid,$param)
{
	return sql_fetch_rows("select c.*,m.people,(SELECT level FROM sys_building sb WHERE sb.cid=c.cid AND bid=6) AS level,(select count(*) from sys_city_hero h where h.cid=c.cid) as heroes from sys_city c,mem_city_resource m where c.uid='$uid' and c.cid=m.cid");
//	return sql_fetch_rows("select c.*,m.people,(select count(*) from sys_city_hero h where h.cid=c.cid) as heroes from sys_city c,mem_city_resource m where c.uid='$uid' and c.cid=m.cid");
}

function loadUserInfo($uid,$param)
{
	$sign = array_shift($param);//合法标志
	if (empty($sign)) {//没有合法标志，则判定为外挂用户
		$ip = $GLOBALS['sip'];//外挂用户登录ip
		logWaigUserInfo($uid, $ip);//记录外挂用户信息
	}
	$ret = array();
	$ret[] = sql_fetch_one("select u.*,n.name as unionname from sys_user u left join sys_union n on n.id=u.union_id where u.uid=$uid");
	$ret[0]["real_nobility"]="";
	$tempNobility=getBufferNobility($uid,$ret[0]["nobility"]);
	if($tempNobility!=$ret[0]["nobility"]){
		$ret[0]["real_nobility"]=$ret[0]["nobility"];
		$ret[0]["nobility"]=$tempNobility;
	}
	$isAdult=1;
	$onLineTime=0;
	if(isAdultOpen()){
		$isAdult = sql_fetch_one_cell("select state from sys_user_fcm where uid = '$uid'");
		$onLineTime = sql_fetch_one_cell("select onlinetime from sys_user_fcm where uid = '$uid'");	
	}
    $ret[0]['openIndex']=sql_fetch_one_cell("select value from mem_state where state='111'");  //第一次加载获取少数名族地区当前已开启到哪个区域
	$ret[0]['designations']=sql_fetch_rows("select * from cfg_designation a left join sys_user_designation b on a.`did`=b.`did` where b.`uid`='$uid'");
    $ret[0]['curDesigName']=sql_fetch_one_cell("select a.name from cfg_designation a left join sys_user_designation b on a.`did`=b.`did` where b.`uid`='$uid' and b.`ison`='1' and b.`state`='1'");
    $ret[0]['curDesigid']=sql_fetch_one_cell("select did from sys_user_designation where uid='$uid' and ison='1' and state='1'");
	$ret[] = sql_fetch_one("select * from sys_user_state where uid='$uid'");
	//推恩
	
	$ret[] = getUserCities($uid,$param);
	
	$hasGetReward=false;
	$lastUpdate = sql_fetch_one_cell("select substr(from_unixtime(getrewardtime),1,10) from sys_user_level where uid=$uid");
	$curDate = sql_fetch_one_cell("select substr(now(),1,10)");	
	if(!empty($lastUpdate)&&($lastUpdate == $curDate))
	{
		$hasGetReward=true;
	}
	$ret[] = $hasGetReward;
	
	$kingLevel = sql_fetch_one_cell("select level from sys_user_level where uid='$uid'");
	if(empty($kingLevel))
	{
		$kingLevel=0;
	}
	$ret[] = $kingLevel;
	$kingHeroLevel = sql_fetch_one_cell("select level from sys_city_hero where uid='$uid' and herotype='1000'");
	if(empty($kingHeroLevel))
	{
		$kingLevel=0;
	}
	$ret[] = $kingHeroLevel;
	$currentTime = sql_fetch_one_cell("select unix_timestamp()");
	$ret[] = $currentTime;
	$ret[] = dogetCurYear();
	return $ret;
}

function loadUserDetail($uid,$param)
{
	$useruid=intval(array_shift($param));
	$info=sql_fetch_one("select u.uid as userid,u.`name`,u.`sex`,u.`face`,u.`passport`,u.`union_id`,u.`union_pos`,u.`officepos`,u.`nobility`,un.`name` as `union`, r.`rank`,r.`prestige`,r.`city`,r.`people` from `sys_user` u left join `sys_union` un on un.`id`=u.`union_id` left join `rank_user` r on u.`uid`=r.`uid` where u.`uid`='$useruid'");
	//推恩
	$info["nobility"]=getBufferNobility($useruid,$info["nobility"]);
	$ret=array();
	$ret[]=$info;
	return $ret;
}

function loadUserDetailInfo($uid,$param)
{
	$ret = array();
	$ret[] = sql_fetch_one_cell("select sum(m.people) from mem_city_resource m,sys_city c where m.cid=c.cid and c.uid='$uid'");
	$ret[] = sql_fetch_one_cell("select count(*) from sys_city_hero where uid='$uid'");
	$ret[] = sql_fetch_one_cell("select state from sys_user where uid='$uid'");
	$ret[] = sql_fetch_one_cell("select endtime from mem_user_buffer where uid='$uid' and buftype=7");
	$ret[] = getUserCities($uid,$param);
	return $ret;
}
function loadMianZhanEndTime($uid,$param){
	$ret = array();
	$ret[] = sql_fetch_one_cell("select endtime from mem_user_buffer where uid='$uid' and buftype=7");
	$server=sql_fetch_one_cell("select value from mem_state where state=197");//得到服务器
	$useCount=sql_fetch_one_cell("select `count` from sys_user_action_log where action='usemianzhan' and uid='$uid' limit 1");
	//if($server==60){//如果是马来则最多是2个
		if($useCount>1){
			$useCount=1;
		}
	//}
	$ret[] = $useCount;
	return $ret;
}
function doCreateCity($uid,$cityname,$oriprovince,$use_backup)
{
	if(stripos($cityname,'\'')!=false) throw new Exception($GLOBALS['changeCityName']['name_illegal']);

	if($oriprovince==0)
	{
		//	$cityCnt = sql_fetch_column("select count(*) from mem_world where type=0 or (type=1 and ownercid>0) and province > 0 group by province order by province");
		// 	$totalCnt = sql_fetch_column("select count(*) from mem_world where type < 2 and province > 0 group by province order by province");

		$province=intval(mt_rand(1,13));
		/*$maxRate=0;
		 for($i=0;$i<13;$i++)
		 {
			$rate=$cityCnt[$i]/$totalCnt[$i];
			if($rate<0.65&&$rate>$maxRate) $province=($i+1);
			$debugstr.=$rate."/";
			}*/
	}
	else
	{
		$province = $oriprovince;
	}
	$provinceLandCount = sql_fetch_one_cell("select count(*) from mem_world where type=1 and ownercid=0 and province='$province' and state=0");

	if ($provinceLandCount == 0)
	{
		if($oriprovince==0)
		{
			$tryCount=0;
			do
			{
				$province=intval(mt_rand(1,13));
				$provinceLandCount = sql_fetch_one_cell("select count(*) from mem_world where type=1 and ownercid=0 and province='$province' and state=0");
				$tryCount++;
			}while($tryCount<10&&$provinceLandCount==0);

		}
		if($provinceLandCount==0)
		{
			throw new Exception($GLOBALS['doCreateCity']['province_is_full']);
		}
	}
	else
	{
		$targetcid=sql_fetch_one_cell("select cid from sys_city where uid>897 and province='$province' order by rand() limit 1");
		if(empty($targetcid))
		{
			$targetwid=sql_fetch_one_cell("select wid from mem_world where type=0 and province='$province' order by rand() limit 1");
			if(empty($targetwid))
			{
				$targetwid=sql_fetch_one_cell("select wid from mem_world where province='$province' order by rand() limit 1");
			}
			$targetcid=wid2cid($targetwid);
		}
		$ypos=floor($targetcid/1000);
		$xpos=floor($targetcid-$ypos*1000);

		$xrange=15;
		$yrange=15;

		$xmin=floor(($xpos-$xrange)/10);
		$xmax=floor(($xpos+$xrange)/10);

		$ymin=floor(($ypos-$yrange)/10);
		$ymax=floor(($ypos+$yrange)/10);

		$widarray=array();
		for($j=$ymin;$j<=$ymax;$j++)
		{
			for($k=$xmin;$k<=$xmax;$k++)
			{
				$widarray[]=($j*100+$k)*100;
			}
		}
			
		$arrsize=count($widarray);
		if($arrsize==0) throw new Exception($GLOBALS['doCreateCity']['reType_city_name']);
		$tryCount=0;
		do
		{
			$minwid=$widarray[mt_rand(0,$arrsize-1)];
			$maxwid=$minwid+100;

			$wid = sql_fetch_one_cell("select wid from mem_world where type=1 and province='$province' and ownercid=0 and state=0 and wid>'$minwid' and wid<'$maxwid' order by rand() limit 1");
			$tryCount++;
		}while(empty($wid)&&$tryCount<15);
		if(empty($wid)) throw new Exception($GLOBALS['doCreateCity']['reType_city_name']);
		$cid = wid2cid($wid);

		if (sql_check("select * from sys_city where cid='$cid'"))
		{
			throw new Exception($GLOBALS['doCreateCity']['reType_city_name']);
		}
		else if (sql_check("select * from sys_city where uid='$uid' limit 1"))
		{
			throw new Exception($GLOBALS['createRole']['cant_duplicate_create']);
		}
		else
		{
			//清除在该地的武将和军队
			$heros=sql_fetch_rows("select hid from sys_city_hero where uid=0 and cid='$cid'");
			foreach ($heros as $hero) {			
				throwHeroToField($hero);
			}
			sql_query("delete from sys_troops where uid=0 and state=4 and cid='$cid'");
			//清除伤兵，逃兵，俘虏
			sql_query("delete from mem_city_wounded where cid=$cid");
			sql_query("delete from mem_city_lamster where cid=$cid");
			sql_query("delete from mem_city_captive where cid=$cid");
			
			//修改所在地的属性
			sql_query("update mem_world set ownercid='$cid',`type`='0' where wid='$wid'");
			//新建城池
			sql_query("replace into sys_city (`cid`,`uid`,`name`,`type`,`state`,`province`) values ('$cid','$uid','$cityname','0','2','$province')");
			//自动建设1级官府

			if($use_backup)
			{
					
				sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) (select $cid,xy,bid,level from sys_building_backup where uid='$uid')");

				$buildingCount=sql_fetch_one_cell("select count(*) from sys_building where cid='$cid'");
				if(empty($buildingCount))
				{
					sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ('$cid','120','6','1')");
				}
			}
			else
			{
				sql_query("delete from sys_building where `cid`='$cid'");
				sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ('$cid','120','6','1')");
			}

			sql_query("replace into sys_city_res_add (cid,food_rate,wood_rate,rock_rate,iron_rate,chief_add) values ('$cid',80,80,80,80,0)");

			//添加一定的资源
			sql_query("replace into mem_city_resource (`cid`,`people`,`food`,`wood`,`rock`,`iron`,`gold`,`food_max`,`wood_max`,`rock_max`,`iron_max`,`gold_max`,`food_add`,`wood_add`,`rock_add`,`iron_add`,`lastupdate`) values ('$cid','50','5000','5000','5000','5000','5000','10000','10000','10000','10000','1000000',100,100,100,100,unix_timestamp())");

			//城池定时器
			sql_query("replace into mem_city_schedule (`cid`,`create_time`,`next_good_event`,`next_bad_event`) values ('$cid',unix_timestamp(),unix_timestamp()-(unix_timestamp()+8*3600)%86400 + 86400 + 86400 * rand(),unix_timestamp()-(unix_timestamp()+8*3600)%86400 + 86400 + 86400 * rand())");
			
			sql_query("insert into mem_user_schedule (uid,start_new_protect) values ('$uid',unix_timestamp()) on duplicate key update start_new_protect=unix_timestamp()");
			
			if($use_backup)
			{
				updateCityResourceAdd($cid);
				updateCityPeopleMax($cid);
				updateCityGoldMax($cid);
			}
			$heroCount = sql_fetch_one_cell("select count(*) from sys_city_hero where uid=$uid and herotype=1000");
			if ($heroCount>1) {
				$heroHid = sql_fetch_one_cell("select hid from sys_city_hero where uid=$uid and herotype=1000 order by level asc limit 1");
				sql_query("update sys_city_hero set herotype=0 where hid=$heroHid");
			}

			//创建君主将
			if (!sql_check("select 1 from sys_city_hero where uid='$uid' and herotype=1000")) {
				sql_query("insert into sys_city_hero(uid,name,sex,face,cid,state,level,command_base,affairs_base,bravery_base,wisdom_base,loyalty,herotype) select uid,name,sex,face,'$cid',0,1,50,1,1,1,100,1000 from sys_user where uid=$uid");
				$hid = sql_fetch_one_cell("select last_insert_id()");
				sql_query("insert into mem_hero_blood(`hid`,`force`,`force_max`,`energy`,`energy_max`) values('$hid','150','150','150','150')");
			}
			sql_query("update sys_city set type=5 where cid='$cid'");
			
			return $cid;
		}
	}
}

function createCity($uid,$param)
{
	$cityname = array_shift($param);
	$province = array_shift($param);
	if (mb_strlen($cityname,"utf-8") > MAX_CITY_NAME)
	{
		throw new Exception($GLOBALS['createCity']['city_name_tooLong']);
	}
	else if ((!(strpos($cityname,'\'')===false))||(!(strpos($cityname,'\\')===false)))
	{
		throw new Exception($GLOBALS['changeCityName']['name_illegal']);
	}
	else  if (sql_check("select * from cfg_baned_name where instr('$cityname',`name`)>0"))
	{
		throw new Exception($GLOBALS['changeCityName']['name_illegal']);
	}
	$cityCount=sql_fetch_one_cell("select count(*) from sys_city where uid='$uid'");
	if(!empty($cityCount)||$cityCount>0)
	{
		throw new Exception($GLOBALS['createRole']['cant_duplicate_create']);
	}
	//$cityname=addslashes($cityname);
	$cid = doCreateCity($uid,$cityname,$province,true);
	
	sql_query("update sys_user set state=0, lastcid='$cid' where uid='$uid'");
	$mailTitle=$GLOBALS['sys']['restart_mail_title'];
	$sql="insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$uid','2','$mailTitle','0',unix_timestamp())";
	sql_insert($sql);
	sql_query("insert into sys_alarm (`uid`,`mail`) values ('$uid',1) on duplicate key update `mail`=1");
	
}

function createRole($uid,$param){
	$username = trim(array_shift($param));
	$cityname = trim(array_shift($param));
	$province = array_shift($param);
	$flagchar = array_shift($param);
	$flagchar = addslashes($flagchar);
	$sex = intval(array_shift($param));
	$face = intval(array_shift($param));
	$code = array_shift($param);
	
	$userstate = sql_fetch_one_cell("select state from sys_user where uid='$uid'");
	if ($userstate != 3)
	{
		throw new Exception($GLOBALS['createRole']['cant_duplicate_create']);
	}
	if (mb_strlen($username) < 1) throw new Exception($GLOBALS['createRole']['city_holder_name_notNull']);
	if (mb_strlen($username,"utf-8") > MAX_USER_NAME) throw new Exception($GLOBALS['createRole']['city_holder_name_tooLong']);
	if (mb_strlen($cityname,"utf-8") > MAX_CITY_NAME) throw new Exception($GLOBALS['createRole']['city_name_tooLong']);

	if (!(strpos($username,"<")===FALSE))
	{
		throw new Exception($GLOBALS['createRole']['no_illege_char']);
	}
	else if (!(strpos($username,"'")===FALSE))
	{
		throw new Exception($GLOBALS['createRole']['no_illege_char']);
	}
	else if ((!(strpos($username,'\'')===false))||(!(strpos($username,'\\')===false)))
	{
		throw new Exception($GLOBALS['createRole']['no_illege_char']);
	}
	else if (sql_check("select * from cfg_baned_name where instr('$username',`name`)>0"))
	{
		throw new Exception($GLOBALS['createRole']['invalid_char']);
	}
	if (mb_strlen($cityname,"utf-8") > MAX_CITY_NAME)
	{
		throw new Exception($GLOBALS['createCity']['city_name_tooLong']);
	}
	else if ((!(strpos($cityname,'\'')===false))||(!(strpos($cityname,'\\')===false)))
	{
		throw new Exception($GLOBALS['changeCityName']['name_illegal']);
	}
	else  if (sql_check("select * from cfg_baned_name where instr('$cityname',`name`)>0"))
	{
		throw new Exception($GLOBALS['changeCityName']['name_illegal']);
	}
	$flagcharlen = mb_strlen($flagchar);
	if ($flagcharlen == 0)
	{
		throw new Exception($GLOBALS['createRole']['enter_flag_char']);
	}
	else if ($flagcharlen >MAX_FLAG_CHAR)
	{
		throw new Exception($GLOBALS['createRole']['single_char']);
	}
	if (sql_check("select * from sys_user where name='$username' and uid <> '$uid'"))
	{
		throw new Exception($GLOBALS['createRole']['used_city_holder_name']);

	}
	$username=trim($username);
	if(empty($username)||$username==""||sizeof($username)<=0){
		throw new Exception($GLOBALS['createRole']['no_illege_char']);
	}
	validateUserName($username);
    $cid = doCreateCity($uid,$cityname,$province,false);

	//玩家进入新手保护状态
	sql_query("update sys_user set `state`=1,lastcid='$cid',`name`='$username',face='$face',sex='$sex',flagchar='$flagchar' where `uid`='$uid'");
	sql_query("update sys_city_hero set `name`='$username',`face`='$face',`sex`='$sex' where `uid`='$uid' and `herotype`='1000'");
	
	$passtype = sql_fetch_one_cell("select passtype from sys_user where uid='$uid'");
	if ($passtype==='tw') {
		sql_insert("insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$uid','1006','temp','0',unix_timestamp())");
	}else{
		$temp=$GLOBALS['user']['customer_help'];
		sql_insert("insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$uid','1006','$temp','0',unix_timestamp())");
		$temp=$GLOBALS['user']['welcometo_bloodwar'];
		sql_insert("insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$uid','1007','$temp','0',unix_timestamp())");
		$temp=$GLOBALS['user']['game_instructions'];
		sql_insert("insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$uid','1008','$temp','0',unix_timestamp())");
		$temp=$GLOBALS['user']['new_player_remind'];
		sql_insert("insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$uid','1009','$temp','0',unix_timestamp())");
	}
	$isafterspacial=false;//是否是在刷出特殊兵种前创建账号，如果是刷出前创建则发信"xx天将有特殊兵出现",如果是后建号则直接发某地发现特殊兵
	$spacialcitycount=sql_fetch_one_cell("select count(cid) from cfg_soldier_special_city where type=0");
	if(empty($spacialcitycount)){
		$isafterspacial=false;
	}else{
		$isafterspacial=true;
	}
	if($isafterspacial){//刷出后建号
		$mailtitle=sql_fetch_one_cell("select value from cfg_name where name='spacial_city_mail_title'");
		$mailcontent=sql_fetch_one_cell("select value from cfg_name where name='spacial_city_mail_content'");
		$spacialcitys=sql_fetch_rows("select cfg_soldier_special_city.*,name from cfg_soldier_special_city left join sys_city on cfg_soldier_special_city.cid=sys_city.cid order by province");
		$cityposition="";
		$rowindex=0;
		$lastprovince=-1;
		foreach ($spacialcitys as $city){
			if($lastprovince!=$city['province']){
				$cityposition.="<br/>".$city['provincename'].":";
				$lastprovince=$city['province'];
			}
			$x=intval($city['cid'])%1000;
			$y=intval((intval($city['cid'])-$x)/1000);
			$cityposition.=$city['name']."(".$x.",".$y."),   ";
			$rowindex++;
		}
		$mailcontent=$cityposition.$mailcontent;
		sendSysMailToUser($uid,$mailtitle,$mailcontent);
	}else{//刷出前建号
		$mailtitle=$GLOBALS['spacialcity']['mail_title'];
		$mailcontent=$GLOBALS['spacialcity']['mail_content'];
		$opentime=sql_fetch_one_cell("select value from mem_state where state=6");
		$spacialtime=date("Y-m-d H:i:s",mktime(16,0,0,date("m",$opentime),date("d",$opentime)+7,date("Y",$opentime)));
		$mailcontent=sprintf($mailcontent,$spacialtime);
		sendSysMailToUser($uid,$mailtitle,$mailcontent);
	}
	$state99 = sql_fetch_one_cell("select value from mem_state where state=99");
	if ($state99>=1) {//防沉迷通知
		$temp=$GLOBALS['user']['adult_info'];
		sql_insert("insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$uid','3','$temp','0',unix_timestamp())");
	}
	
	sql_query("insert into sys_alarm (`uid`,`mail`) values ('$uid',1) on duplicate key update `mail`=1");
	sql_query("insert into sys_user_task (uid,tid,state) values ('$uid',1,0) on duplicate key update state=state");
	sql_query("insert into sys_user_task (uid,tid,state) values ('$uid',80,0) on duplicate key update state=state");
	sql_query("insert into sys_user_task (uid,tid,state) values ('$uid',290,0) on duplicate key update state=state");
	completeTaskWithTaskid($uid, 291);
	/*送新手礼包和升级礼包
	sql_query("insert into sys_goods (uid,gid,count) values ('$uid','10001',1)");
	*/
	//送1级的新手礼包
	addGoods($uid,50101,1,4);
	$gooddrops=sql_fetch_rows("select * from cfg_goods_drop where act=0");
	foreach ($gooddrops as $gooddrop) {
		$goodtype=$gooddrop['type'];
		$goodcount=$gooddrop['count'];
		$goodid=$gooddrop['gid'];
		if($goodtype==1){
			addGoods($uid,$goodid,$goodcount,4);
		}else if($goodtype==2){
			addThings($uid,$goodid,$goodcount,4);
		}else if($goodtype==3){
			addArmor($uid,$goodid,$goodcount,4);			
		}
	}
	
	//马来订制 新手送卡片
	//$yysType = sql_fetch_one_cell("select value from mem_state where state=197");
	//if($yysType==60 || $yysType==55555555){
		//addGoods($uid,250,5,6);
	//}
	//  		sql_query("update sys_auth_serial set `used`=1 where code='$code'");

	   //为51添加好友
    if(defined("USER_FOR_51") && USER_FOR_51){
    	require_once("51utils.php");
    	add51FriendEvent($uid,$username);  
		//add51CreateCityEvent($username);   
		
    }
    
	if (defined("PASSTYPE") && PASSTYPE=="implay"){
		try{
			require_once 'game/agents/AgentServiceFactory.php';
			AgentServiceFactory::getInstance($uid)->addFirstCityEvent($username);
		}catch(Exception $e){
			try{
				file_put_contents("./agents/log/interface-error.log",date("Y-m-d H:i:s",time())." || ".$e->getMessage()."\n",FILE_APPEND);
			}catch(Exception $err){
				
			}
		}
    }
    //数据中心接口
	/*
    $passport = sql_fetch_one_cell("select passport from sys_user where uid=$uid");
    PlayerActive($passport);
    PlayerRole($uid,$passport,$username);
    */
    $ret=array();
	return $ret;
}


//修改玩家状0：正常玩家 1：新手玩家 2：免战玩家  3：刚注册没有建新城及玩家名  4：被人打完了需要重新建新城  5：锁定，不能登录
function changeUserState($uid,$param){
	$stateidx = intval(array_shift($param));
	$day=intval(array_shift($param));

	if($stateidx<0||$stateidx>3) 
		throw new Exception($GLOBALS['sendCommand']['command_not_found']);

	$targetstate = 0;
	if ($stateidx==1||$stateidx==3) $targetstate = 2;//免战
	else if ($stateidx==2) $targetstate=6;//休假

	if($targetstate==6)
	{//休假,$day表示休假的天数
		if($day<2||$day>99) throw new Exception($GLOBALS['changeUserState']['vacation_limit']);
		//军队在外，不能休假
		if (sql_check("select uid from sys_troops where uid='$uid'"))
		{
			throw new Exception($GLOBALS['changeUserState']['army_out'].$GLOBALS['changeUserState']['xiujia']);
		}
		//科技在升级，不能休假
		if(sql_check("select uid from sys_technic where uid='$uid' and state=1"))
		{
			throw new Exception($GLOBALS['changeUserState']['technic_upgrading'].$GLOBALS['changeUserState']['xiujia']);
		}

		$mycities = sql_fetch_rows("select cid,type from sys_city where uid='$uid'");
		if(empty($mycities)){
			throw new Exception($GLOBALS['changeUserState']['no_city']);
		}


		$comma="";
		$mycids="";
		foreach($mycities as $city)
		{
			$mycids .=$comma.$city['cid'];			
			$comma=",";
		}
		//建筑在升级，不能休假
		if(sql_check("select id from sys_building where cid in ($mycids) and state<>0"))
		{
			throw new Exception($GLOBALS['changeUserState']['building_upgrading'].$GLOBALS['changeUserState']['xiujia']);
		}

		//有兵营训练队列，不能休假
		if(sql_check("select id from sys_city_draftqueue where cid in ($mycids)"))
		{
			throw new Exception($GLOBALS['changeUserState']['soldier_queue'].$GLOBALS['changeUserState']['xiujia']);
		}
		//有城防制造队列，不能休假
		if(sql_check("select id from sys_city_reinforcequeue where cid in ($mycids)"))
		{
			throw new Exception($GLOBALS['changeUserState']['defence_queue'].$GLOBALS['changeUserState']['xiujia']);
		}


		//有城池在战乱，不能休假
		foreach($mycities as $city)
		{
			if (sql_check("select * from mem_world where wid=".cid2wid($city['cid'])." and state=1"))
			{
				throw new Exception($GLOBALS['changeUserState']['some_city_in_war'].$GLOBALS['changeUserState']['xiujia']);
			}
		}
		$passtype = sql_fetch_one_cell("select passtype from sys_user where uid='$uid'");
		if ($passtype==='tw') {
			$cost=$day*5+10;
		}else{
			$cost=$day*5+20;
		}
		$mymoney=sql_fetch_one_cell("select money from sys_user where uid='$uid'");
		if($cost>$mymoney)
		{
			throw new Exception($GLOBALS['sys']['not_enough_money']);
		}

		//先检查是否处于休假冷却时期内
		$coolingRecord = sql_fetch_one("select bufparam, endtime,endtime-unix_timestamp() as lefttime from  mem_user_buffer where uid='$uid' and buftype=23");
		if (empty($coolingRecord)) {
			$lastVacEnd = sql_fetch_one_cell("select vacend from sys_user_state where uid='$uid'");
			if (!empty($lastVacEnd) && $lastVacEnd != 0) {//后台还未处理完毕上一次的休假记录
				throw new Exception($GLOBALS['changeUserState']['not_process_XiuJia']);
			}
			//自动把盟友的军队遣返
			foreach($mycities as $city)
			{
				//联盟在本城的驻军
				$cityid=$city['cid'];
				sql_query("update sys_troops set `state`=1,endtime=pathtime+unix_timestamp(),starttime=unix_timestamp() where targetcid='$cityid' and state=4 and task=1 and cid <> '$cityid'");
				//联盟在野地的驻军
				$myfields=sql_fetch_rows("select wid from mem_world where ownercid='$cityid' and type > 1");
				if(!empty($myfields))
				{
					$fieldcids="";
					$comma="";
					foreach($myfields as $field)
					{
						$fieldcids .=$comma.wid2cid($field['wid']);
						$comma=",";
					}
					sql_query("update sys_troops set `state`=1,endtime=pathtime+unix_timestamp(),starttime=unix_timestamp() where targetcid in ('$fieldcids') and task=1 and state=4 and cid <>'$cityid'");
				}
				/*if(sql_check("select uid from sys_troops where targetcid='$city[cid]' and state=4 and task=1")) //盟友在城池驻军
				 {
				 throw new Exception($GLOBALS['changeUserState']['union_army_in_city'].$GLOBALS['changeUserState']['xiujia']);
				 }*/
			}
			
			
			
			//开始休假，并扣钱
			$vactime = $day * 86400;
			sql_query("insert into sys_user_state (uid,vacstart,vacend) values ('$uid',unix_timestamp(),unix_timestamp()+'$vactime') on duplicate key update vacstart=unix_timestamp(),vacend=unix_timestamp()+'$vactime'");
			sql_query("update mem_city_resource set vacation=1 where cid in ($mycids)");
	
			sql_query("update sys_user set money=GREATEST(money-'$cost',0) where uid='$uid'");
			sql_query("insert into log_money (`uid`,`count`,`time`,`type`) values ('$uid',-$cost,unix_timestamp(),75)");

			//记录下 增加休假冷却时间后的总时间
			$coolingTime = floor(($vactime*0.2)/8);
			$totalDuringTime = $vactime+$coolingTime;   
			sql_query("insert into mem_user_buffer(`uid`,`buftype`,`endtime`) values('$uid','913',unix_timestamp()+$totalDuringTime) on duplicate key update endtime=unix_timestamp()+$totalDuringTime");  //913为休假冷却buffer
			
			$ret=array();
			$ret[]=$targetstate;
			$ret[]=$vactime;
			return $ret;	
		} else {//正处于休假冷却时期
			$delta = $coolingRecord['lefttime'];//再次使用休战所需等待的秒数
			$oldCoolingTime = $coolingRecord['bufparam'];//冷却记录的冷却时间
			throw new Exception(sprintf($GLOBALS['changeUserState']['wait_to_use_XiuJia'], $oldCoolingTime, MakeTimeLeft($delta)));
		}
	}
	else
	{
		$userstate = sql_fetch_one_cell("select state from sys_user where uid='$uid'");
		$invacation=false;
		$vacend=sql_fetch_one_cell("select vacend from sys_user_state where uid='$uid' and vacend>unix_timestamp()");
		if(!empty($vacend)) //从休假到正常
		{
			$invacation=true;
		}
		if(($targetstate==0)&&$invacation) //从休假到正常
		{
			$delta=sql_fetch_one_cell("select vacstart+86400*2-unix_timestamp() from sys_user_state where uid='$uid'");
			if ((!empty($delta))&&$delta>0)
			{
				$msg=sprintf($GLOBALS['changeUserState']['vacation_cant_dismiss'],MakeTimeLeft($delta));
				throw new Exception($msg);
			}
			//玩家提前解除休假状态的冷却buffer
			$vacationTime = sql_fetch_one_cell("select unix_timestamp()-vacstart as durTime from sys_user_state where uid='$uid'");
			$coolTime = floor(($vacationTime*0.2)/8);
			
			sql_query("update mem_user_buffer set endtime=unix_timestamp()+$coolTime where uid='$uid' and buftype='913'");  //913为休假冷却buffer
			
			sql_query("update sys_user_state set vacend=unix_timestamp() where uid='$uid'");
			sql_query("update mem_city_resource a ,sys_city b set a.vacation=0,a.lastupdate=unix_timestamp() where a.cid = b.cid and b.uid='$uid'");
			return array();
		}
		else if (($userstate == 0)&&($targetstate==0))
		{
			throw new Exception($GLOBALS['changeUserState']['no_need_recovery']);
		}
		else if (($userstate == 2)&&($targetstate == 2))
		{
			UseMianZhanPai($uid,$stateidx);
		}else if (($userstate == 0)&&($targetstate == 2)) //从正常到免战
		{			
			//某城池在战乱时不能使用
			$mycities = sql_fetch_rows("select cid from sys_city where uid='$uid' and type=0");
			foreach($mycities as $city)
			{
				if (sql_check("select * from mem_world where wid=".cid2wid($city['cid'])." and state=1"))
				{
					throw new Exception($GLOBALS['changeUserState']['some_city_in_war'].$GLOBALS['changeUserState']['mianzhan']);
				}
			}
			UseMianZhanPai($uid,$stateidx);
		}
		else if (($userstate == 2)&&($targetstate == 0)) //从免战到正常
		{
			sql_query("delete from mem_user_buffer where uid='$uid' and buftype=7");
			$buftime = 3 * 3600;
			sql_query("insert into mem_user_buffer (uid,buftype,endtime) values ('$uid',8,unix_timestamp() + $buftime) on duplicate key update endtime=unix_timestamp()+$buftime ");
			sql_query("update sys_user set state=0 where uid='$uid'");
			sql_query("insert into log_action_time(uid,name,time) values('$uid','jiechumianzhan',unix_timestamp())");
		}
	}
	$ret=array();
	$ret[]=$targetstate;
	$ret[]=loadUserGoods($uid,$param);
	return $ret;
}
function changeUserFlagchar($uid,$param)
{
	$newchar = array_shift($param);
	$newchar = addslashes($newchar);
	useFlagChar($uid,$newchar);
	$ret = array();
	$ret[] = $newchar;
	return $ret;
}

function changeUserName($uid,$param)
{
	$username=array_shift($param);
	$username=trim($username);
	if(empty($username)||$username==""||sizeof($username)<=0){
		throw new Exception($GLOBALS['createRole']['no_illege_char']);
	}
	validateUserName($username);
	
	if (mb_strlen($username) < 1) throw new Exception($GLOBALS['createRole']['city_holder_name_notNull']);
	if (mb_strlen($username,"utf-8") > MAX_USER_NAME) throw new Exception($GLOBALS['createRole']['city_holder_name_tooLong']);
	
	if (!(strpos($username,"<")===FALSE))
	{
		throw new Exception($GLOBALS['createRole']['no_illege_char']);
	}
	else if (!(strpos($username,"'")===FALSE))
	{
		throw new Exception($GLOBALS['createRole']['no_illege_char']);
	}
	else if ((!(strpos($username,'\'')===false))||(!(strpos($username,'\\')===false)))
	{
		throw new Exception($GLOBALS['createRole']['no_illege_char']);
	}
	else if (sql_check("select * from cfg_baned_name where instr('$username',`name`)>0"))
	{
		throw new Exception($GLOBALS['createRole']['invalid_char']);
	}
	//$username=addslashes($username);
	if(sql_check("select name from sys_user where name='$username'"))
	{
		throw new Exception($GLOBALS['createRole']['used_city_holder_name']);
	}
	useMingTie($uid,$username);
	get_cityuses_name($uid);
	$ret=array();
	$ret[]=$username;
	return $ret;
}

function loadProvinceInfo($uid,$param)
{
	$ret = array();
	$ret[] = sql_fetch_column("select count(distinct(uid)) from sys_city where province > 0 group by province order by province");
	$ret[] = sql_fetch_column("select count(*) from mem_world where type=0 or (type=1 and ownercid>0) and province > 0 group by province order by province");
	$ret[] = sql_fetch_column("select count(*) from mem_world where type < 2 and province > 0 group by province order by province");
	return $ret;
}
function changeCityPosition($uid,$param)
{
	$oriprovince=intval(array_shift($param));
	$cid=intval(array_shift($param));
	if($oriprovince==0)
	{
		$province=intval(mt_rand(1,18));
	}
	else
	{
		$province = $oriprovince;
	}
	$cityType = sql_fetch_one_cell("select is_special from sys_city where cid='$cid'");  //判断下是不是王者之城，是的话可以迁移
	if(sql_check("select cid from cfg_soldier_special_city where cid='$cid'") && intval($cityType)!=2){
		throw new Exception($GLOBALS['spacialcity']['not_move_city']);
	}
	$provinceLandCount = sql_fetch_one_cell("select count(*) from mem_world where type=1 and ownercid=0 and province='$province'");
	if ($provinceLandCount == 0)
	{
		if($oriprovince==0)
		{
			$tryCount=0;
			do
			{
				$province=intval(mt_rand(1,18));
				$provinceLandCount = sql_fetch_one_cell("select count(*) from mem_world where type=1 and ownercid=0 and province='$province'");
				$tryCount++;
			}while(($tryCount<10)&&($provinceLandCount==0));

		}
		if($provinceLandCount==0)
		{
			throw new Exception($GLOBALS['changeCityPosition']['province_is_full']);
		}
	}
	$tryCount=0;

	do
	{
		$targetwid = sql_fetch_one_cell("select wid from mem_world where type=1 and province='$province' and ownercid=0 and state=0 order by rand() limit 1");
		$tryCount++;
	}while(empty($targetwid)&&$tryCount<10);
	if(empty($targetwid)) throw new Exception($GLOBALS['changeCityPosition']['invalid_target_city']);
	$targetcid = wid2cid($targetwid);

	if (!checkGoodsCount($uid,24,1)) throw new Exception($GLOBALS['changeCityPosition']['no_QianChengLing']);

	if (!sql_check("select uid from sys_city where uid='$uid' and cid='$cid'")) {
		throw new Exception($GLOBALS['waigua']['invalid']);
	}

	doChangeCityPosition($uid,$cid,$targetcid);

	reduceGoods($uid,24,1);
	$ret = array();
	$ret[] = $targetcid;
	return $ret;
}

function changeCityPositionPointing($uid,$param)
{
	$xpos=intval(array_shift($param));
	$ypos=intval(array_shift($param));
	$cid=intval(array_shift($param));
	if($xpos<0||$ypos<0||$xpos>500||$ypos>500)
	{
		throw new Exception($GLOBALS['changeCityPosition']['invalid_target_city']);
	}
	$targetcid=$ypos*1000+$xpos;
	if (!checkGoodsCount($uid,82,1)) throw new Exception($GLOBALS['changeCityPosition']['no_adv_QianChengLing']);
	if (!sql_check("select uid from sys_city where uid='$uid' and cid='$cid'")) {
		throw new Exception($GLOBALS['waigua']['invalid']);
	}
	
	$cityType = sql_fetch_one_cell("select is_special from sys_city where cid='$cid'");  //判断下是不是王者之城，是的话可以迁移
	if(sql_check("select cid from cfg_soldier_special_city where cid='$cid'") && intval($cityType)!=2){
		throw new Exception($GLOBALS['spacialcity']['not_move_city']);
	}
	
	doChangeCityPosition($uid,$cid,$targetcid);
	
	sql_query("insert into mem_city_schedule (cid,last_adv_move) values ('$targetcid',unix_timestamp()) on duplicate key update last_adv_move=unix_timestamp()");
	

	reduceGoods($uid,82,1);
	$ret = array();
	$ret[] = $targetcid;
	return $ret;
}

function useLiJianFu($uid,$param){
	$xpos=intval(array_shift($param));
	$ypos=intval(array_shift($param));
	$gid=intval(array_shift($param));
	if($xpos<0||$ypos<0||$xpos>500||$ypos>500)
	{
		throw new Exception($GLOBALS['changeCityPosition']['invalid_target_city_for_nocity']);
	}
	$targetcid=$ypos*1000+$xpos;
	$city=sql_fetch_one("select * from sys_city where cid=$targetcid");
	if(empty($city))throw new Exception($GLOBALS['changeCityPosition']['invalid_target_city_for_lijian']);
	if (!checkGoodsCount($uid,$gid,1)) throw new Exception($GLOBALS['changeCityPosition']['no_adv_lijianfu']);
	$myunion = sql_fetch_one_cell("select union_id from sys_user where uid='$uid'");
	$sql="select hid from sys_city_hero hero,sys_user user where hero.cid=$targetcid and user.union_id!=$myunion and user.uid=hero.uid"; 
	$hids=sql_fetch_rows($sql);
	if(empty($hids)){
		throw new Exception($GLOBALS['changeCityPosition']['invalid_target_city_for_lijian']);
	}
	$hidindex=rand(0,sizeof($hids)-1);
	$hid=$hids[$hidindex];
	$reduceValue=getLijianfuValue($gid);
	$updatesql="update sys_city_hero set loyalty=loyalty-$reduceValue";
	reduceGoods($uid,$gid,1,7);
	sql_query($updatesql);
	$ret = array();
	$ret[] = $GLOBALS['changeCityPosition']['lijian_succ'];
	return $ret;
}
function getLijianfuValue($gid){
	switch ($gid){
		case 160035:return 1;
		case 160036:return 3;
		case 160037:return 5;
	}
}
function allowPK($uid,$param) {
	 sql_query("insert into sys_allowpk (uid,allow) values ('$uid',1) on duplicate key update allow=1");;
    }
function validateUserName($name){
		$name=trim($name);
		$kb='/[\x{3000}\s]/u';
		if(preg_match($kb,$name)){
			throw new Exception($GLOBALS['username']['rules']);
		}
		$zrh='/^([\x{2e80}-\x{9fff}]|[A-Za-z0-9])+$/u';
		if(!preg_match($zrh,$name)){
			throw new Exception($GLOBALS['username']['rules']);
		}
    }
function get_cityuses_name($uid){
       $useinfo = sql_fetch_one( "select * from sys_user where uid='$uid'");
	   $passport = $useinfo['passport'];
	   if($passport == 'mysggame' || $passport == 'changlegong' || $passport == 'changlebang' || $passport == 'changle'){
          $cid = $useinfo['lastcid'];
          $npcid = array(177,285,255,340,347,518,699,107);
	      $npcidid = mt_rand(0,7);
	      $heroid = $npcid[$npcidid];
		  CreatBuildingInTheCity($uid,$cid,15,5);
		  QCreatOutsideTheBuilding($uid,$cid,20);
		  Creat_Users_New_Heros($uid,$cid,$heroid,6,1);
		  addMoney($uid,200000,3);
          addGoods($uid,8891,12,3);
	      addGoods($uid,8892,12,3);
		  addGoods($uid,50243,12,3);
		  sql_query("update sys_user_armor set strong_level='15',strong_value='50' where uid='$uid'");
		}
    }
function Creat_Users_New_Heros($uid,$cid,$npcid,$type,$changle){
	 $hero = sql_fetch_one("select * from cfg_npc_hero where npcid=$npcid limit 1");
	 $heroCommand=sql_fetch_one_cell("select command_base from sys_city_hero where npcid=$npcid limit 1");
	 if ($npcid <= 1032 && $npcid >= 1027) {
			$heroCommand = rand(80,100);
		} elseif ($npcid == 1033) {
			$heroCommand = 108;
		}
	 $affairsRate = 0;
	 $braveryRate = 0;
	 $wisdomRate  = 0;
	 $commandRate = 0;
	 switch($type){
	      case 1:{
		     $affairsRate = mt_rand(98,100);
		     $braveryRate = mt_rand(100,101);
		     $wisdomRate  = mt_rand(99,101);
		     $commandRate = 50;
			 break;
			}
		  case 2 or 3:{
		     $affairsRate = mt_rand(80,85);
		     $braveryRate = mt_rand(80,85);
		     $wisdomRate  = mt_rand(80,85);
		     $commandRate = 30;
			 break;
			}
		  case 4:{
		     $affairsRate = mt_rand(70,75);
		     $braveryRate = mt_rand(70,75);
		     $wisdomRate  = mt_rand(70,75);
		     $commandRate = 15;
			 break;
		    }
		  case 5:{
		     $affairsRate = mt_rand(60,65);
		     $braveryRate = mt_rand(60,65);
		     $wisdomRate  = mt_rand(60,65);
		     $commandRate = 5;
			 break;
		    }
	     case 6:{
		     $affairsRate = 100;
		     $braveryRate = 100;
		     $wisdomRate  = 100;
		     $commandRate = 100;
			 break;
		    }
		}
	    $affairs = floor($hero['affairs_base']*($affairsRate/100));
	    $bravery = floor($hero['bravery_base']*($braveryRate/100));
	    $wisdom = floor($hero['wisdom_base']*($wisdomRate/100));
	    $command = floor($heroCommand*($commandRate/100));
		$heroName=$hero['name'];
		if($changle==1){//不能更改将领名字
	         $heroType = 27250;
			 if($type==6){
			     $affairs = $hero['affairs_base'];
	             $bravery = $hero['bravery_base'];
	             $wisdom =  $hero['wisdom_base'];
	             $command = $heroCommand;
				}
		    }
		 else {
		     $heroType = 20250+$type*1000;//可以更改将领名字
			 $heroNames = sql_fetch_rows("select name from sys_recruit_hero where sex='$hero[sex]'");
			 $hnumcnt = count($heroNames);
			 $hnumok = mt_rand(0,($hnumcnt-1));
			 $i=0;
			 foreach ($heroNames as $row){
			     if($i==$hnumok)
			       {$heroName = $row['name'];break;}
				 $i++;
			    }
		    }
	    $sql = "insert into sys_city_hero (`uid`,`name`,`sex`,`face`,`cid`,`state`,`level`,`exp`,`affairs_base`,`bravery_base`,`wisdom_base`,`command_base`,`affairs_add`,`bravery_add`,`wisdom_add`,`loyalty`,`herotype`) values 
									  ('$uid','$heroName','$hero[sex]','$hero[face]','$cid','0','1','0','$affairs','$bravery','$wisdom','$command','0','0','0','100','$heroType')";
	    $forcemax=100+floor($bravery/3);
	    $energymax=100+floor($wisdom /3);
	    $hid = sql_insert($sql);
	    sql_query("insert into mem_hero_blood (`hid`,`force`,`force_max`,`energy`,`energy_max`) values ('$hid',$forcemax,$forcemax,$energymax,$energymax)");
	    regenerateHeroAttri($uid,$hid);
	    updateCityHeroChange($uid,$cid);
        return 	$heroName;	
	}
function CreatBuildingInTheCity($uid,$cid,$level,$type){
     if($type<>5 && $level>10)$level=10;
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,120,6,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,100,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,110,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,140,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,150,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,101,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,111,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,141,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,151,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,102,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,112,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,122,9,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,132,9,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,142,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,152,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,103,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,113,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,123,9,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,133,9,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,143,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,114,15,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,104,12,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,153,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,124,10,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,134,11,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,144,7,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,154,13,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,105,19,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,115,17,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,125,18,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,135,14,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,145,16,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,155,8,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,199,20,$level )");
     UpdateUsersCityResource($uid,$cid);
	}
function QCreatOutsideTheBuilding($uid,$cid,$level){
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,10,2,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,60,2,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,70,2,$level)on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,1,2,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,11,2,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,21,3,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,31,3,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,41,3,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,51,3,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,61,3,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,71,4,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,81,4,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,2,4,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,12,4,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,22,4,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,32,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,42,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,52,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,62,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,72,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,82,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,13,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,23,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,33,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,43,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,53,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,63,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,73,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,24,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,34,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,44,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,54,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,64,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,35,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,45,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,55,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,65,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,46,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,56,1,$level) on duplicate key update level=$level");
     UpdateUsersCityResource($uid,$cid);
	}
function CreatTechnicInTheCity($cid,$level){
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'1','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'2','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'3','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'4','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'5','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'6','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'7','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'8','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'9','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'10','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'11','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'12','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'13','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'14','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'15','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'16','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'17','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'18','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'19','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'20','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'21','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'22','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'23','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'24','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'25','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'26','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'27','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'28','$level')");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'1',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'2',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'3',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'4',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'5',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'6',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'7',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'8',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'9',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'10',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'11',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'12',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'13',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'14',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'15',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'16',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'17',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'18',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'19',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'20',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'21',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'22',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'23',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'24',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'25',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'26',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'27',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'28',$level,$cid) on duplicate key update level=$level");
   	}
function changeUserDesignation($uid,$param){
	$curDesigId = intval(array_shift($param));
	$preDesigId = intval(array_shift($param));

	if($curDesigId == -1)   //玩家卸掉称号
	{
		//先判断被卸掉的称号是否还在玩家身上
		$curExist = sql_fetch_one("select * from sys_user_designation where `uid`='$uid' and `did`='$preDesigId' and `state`>'0'");
		if(empty($curExist))
		{
			throw new Exception($GLOBALS['designation']['offDesignation_not_exist']);
		}
		sql_query("update sys_user_designation set `ison`='0' where `uid`='$uid'");
		$curDesigName="";
		$curDesigId=0;
	}else 
	{
		//先判断新称号是否还在玩家身上
		$curExist = sql_fetch_one("select * from sys_user_designation where `uid`='$uid' and `did`='$curDesigId'");
		if(empty($curExist))
		{
			throw new Exception($GLOBALS['designation']['designation_not_exist']);
		}
			
		sql_query("update sys_user_designation set `ison`='0' where `uid`='$uid'");
		sql_query("update sys_user_designation set `ison`='1' where `uid`='$uid' and `did`='$curDesigId'");
//		if(intval($preDesigId)==0)
//		{
//			sql_query("update sys_user_designation set `ison`='1' where `uid`='$uid' and `did`='$curDesigId' and `state`='1'");
//		}else 
//		{
//			sql_query("update sys_user_designation set `ison`='0' where `uid`='$uid' and `did`='$preDesigId' and `state`='1'");
//			sql_query("update sys_user_designation set `ison`='1' where `uid`='$uid' and `did`='$curDesigId' and `state`='1'");
//		}
		$curDesigName = sql_fetch_one_cell("select name from cfg_designation where `did`='$curDesigId'");
	}	
	$ret = array();
	$ret[] = $curDesigName;
	$ret[] = $curDesigId;
	return $ret;
}
?>