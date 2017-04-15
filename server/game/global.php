<?php         
require_once("utils.php");

function checkUserAuth($uid,$sid){
    $ip = $GLOBALS['ip'];
    $sessionfile = ROOT_PATH."/sessions/".$uid;
    $sessionid = @file_get_contents($sessionfile);
    if ($sessionid === FALSE)
    {
        $sessionid = sql_fetch_one_cell("select sid from sys_sessions where uid='$uid'");
        @file_put_contents($sessionfile,$sessionid);
    }
    if(!isset($_SESSION)){
        session_start();
    }
      
    if ($uid <= 0 || $sid != $sessionid /*|| $uid != $_SESSION['currentLogin_uid']*/)
    {   
    	
        throw new Exception("invalid_user_auth");
    }                                                                                 
}
function getCityInfoRes($uid,$cid){
    $ret = array();
    checkCityOwner($cid, $uid);
	$user = sql_fetch_one("select * from sys_user where uid='$uid'");
    $openLottery = sql_fetch_one_cell("select value from mem_state where state = 150");
	if(empty($openLottery) || $openLottery == "" || $openLottery == false)
	{
		$openLottery = 0;
	}
	$user['openLottery'] = $openLottery;
    $ret[] = $user;
    //推恩
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
	$ret[0]['isAdult'] = $isAdult;
	$ret[0]['onLineTime'] = $onLineTime;
    $ret[0]['openIndex']=sql_fetch_one_cell("select value from mem_state where state='111'");  //时时更新获取少数名族地区当前已开启到哪个区域
    $ret[0]['curDesigName']=sql_fetch_one_cell("select a.name from cfg_designation a left join sys_user_designation b on a.`did`=b.`did` where b.`uid`='$uid' and b.`ison`='1' and b.`state`='1'");
    $ret[0]['curDesigid']=sql_fetch_one_cell("select did from sys_user_designation where uid='$uid' and ison='1' and state='1'");
    $ret[0]['designations']=sql_fetch_rows("select * from cfg_designation a left join sys_user_designation b on a.`did`=b.`did` where b.`uid`='$uid'");
    foreach ($ret[0]['designations'] as $allDesig){
    	if(intval($allDesig['state'])==2){
    		$desid = $allDesig['did'];
    		sql_query("update sys_user_designation set `state`='1' where uid='$uid' and `did`='$desid'");
    	}
    }
    $hasGetReward=false;
	$lastUpdate = sql_fetch_one_cell("select substr(from_unixtime(getrewardtime),1,10) from sys_user_level where uid=$uid");
	$curDate = sql_fetch_one_cell("select substr(now(),1,10)");	
	if(!empty($lastUpdate)&&($lastUpdate == $curDate))
	{
		$hasGetReward=true;
	}
    $ret[0]['hasGetReward']=$hasGetReward;
    $ret[0]['currentYear']=dogetCurYear();
    //放这里检测下城池皮肤道具是否已经过期
    $now = sql_fetch_one_cell("select unix_timestamp()");
    $citySpecialType = sql_fetch_one_cell("select is_special from sys_city where cid='$cid'");
    $isInvalid = sql_fetch_one_cell("select endtime from mem_city_buffer where cid='$cid' and bufparam='705' and (buftype='11021' or buftype between 10932 and 10937)");   //城池换皮buff
    if((empty($isInvalid)||intval($isInvalid)<$now) && intval($citySpecialType)>=10 && intval($citySpecialType)<=50)    //先定个50的上限，不然永久城池皮肤都被清除了。
    {
    	sql_query("update sys_city set is_special='0' where cid='$cid' limit 1");
    }
	//===
	SetCityBaseProduce($cid);//更新所有资源产量上限
	//资源更新
    //sql_query("update mem_city_resource set food=case when((food_add-food_army_use)>0) then LEAST(food+(food_add-food_army_use)/225,GREATEST(food,food_max)) else GREATEST(0,food+(food_add-food_army_use)/225) end,wood=LEAST(GREATEST(wood_max,wood),wood+wood_add/255),rock=LEAST(GREATEST(rock_max,rock),rock+rock_add/255),iron=LEAST(GREATEST(iron_max,iron),iron+iron_add/255),gold=case when((people*gold_rate/10000*tax-hero_fee)>0) then LEAST(GREATEST(gold_max,gold),gold+(people*gold_rate*tax/10000-hero_fee)/225) else GREATEST(0,gold+(people*gold_rate*tax/10000-hero_fee)/225) end where exists(select cid from sys_city where uid>1000 and cid=mem_city_resource.cid) and vacation=0 and forbidden=0");
    //民心调整设置成6分调整一下
	refreshFoodArmyUsers($cid);
	UpdateUsersCityResource($uid,$cid);
	$timems= date('i');
	$timess= date('s');
	if($timems%6==0 && $timess<=16)
	   sql_query("update mem_city_resource SET morale=case when(morale<100-tax-complaint) then LEAST(100,morale+1) when(morale>100-tax-complaint) then GREATEST(0,morale-1) else morale end,`people_stable`=`people_max`*morale*0.01,people=case when(people<people_stable) then LEAST(people+CEILING(people_max/200),people_stable) when(people>people_stable) then GREATEST(people_stable,people-CEILING(people_max/200)) else GREATEST(0,people) end");
    //将领俸禄
	sql_query("update mem_hero_blood SET `force` =case when((`force`+`force_max`/150)<`force_max`) then (`force`+`force_max`/150) else `force_max` end,`energy` =case when((`energy`+`energy_max`/150)<`energy_max`) then (`energy`+`energy_max`/150) else `energy_max` end");
	//===
	$ret[] = sql_fetch_one("select m.*,b.endtime as shuilibian from mem_city_resource m left join mem_user_buffer b on b.uid='$uid' and b.buftype=15 where m.`cid`='$cid'");
	
    $ret[] = sql_fetch_one("select * from sys_alarm where uid='$uid'");
    $ret[] = sql_fetch_one_cell("select `skill_gold_add` from sys_city_res_add where `cid`='$cid'");  //获得技能增加黄金的比例
    $currentTime = sql_fetch_one_cell("select unix_timestamp()");
	$ret[] = $currentTime;
	$total = sql_fetch_one_cell("select count(*) from sys_info_alarm where uid='$uid'");
	$ret[] = $total;
    if($total > 0) {
    	$infoalarm = sql_fetch_one("select * from sys_info_alarm where uid=$uid order by time limit 1");
    	$ret[] = $infoalarm;
    	sql_query("delete from sys_info_alarm where id='$infoalarm[id]'");
    }
	
    return $ret;
} 
                           
function getCityInfoHero($uid,$cid,$hid=0){
	//hid=0是给沙场派将的时候用的
	/*
    $ret = array();
    //所有属于本城的将领列表
    $ret[] = sql_fetch_rows("select * from sys_city_hero where `cid`='$cid' and uid='$uid'");    
    return $ret;
    */
	if($hid==0){
    	$heroes=sql_fetch_rows("select * from sys_city_hero h left join mem_hero_blood m on m.hid=h.hid where h.`cid`='$cid' and h.uid='$uid'");
	}else{
		$heroes=sql_fetch_rows("select * from sys_city_hero h left join mem_hero_blood m on m.hid=h.hid where h.`hid`='$hid' and h.uid='$uid'");
	}
	$ret = array();
	foreach($heroes as $hero)
	{
		$buffers=sql_fetch_rows("select * from mem_hero_buffer where hid='$hero[hid]' and endtime>unix_timestamp()");
		$newhero=&$hero;
		foreach($buffers as $buf)
		{
			$typeidx="buf".$buf['buftype'];
			$newhero[$typeidx]=$buf['endtime'];
		}
		// 兵种速度加成
		$speedbuffs = sql_fetch_rows("select * from sys_hero_attribute where hid='$hero[hid]' and attid>=2000 and attid<=4999 and attid % 100 = 11");
		foreach($speedbuffs as $buf) 
		{
			$attid = $buf['attid'];
			$value = $buf['value'];
			$newhero[$attid] = $value;
		}
		//  兵种负重加成
		$carrybuffs = sql_fetch_rows("select * from sys_hero_attribute where hid='$hero[hid]' and attid>=2000 and attid<=4999 and attid % 100 = 12");
		foreach($carrybuffs as $buf) 
		{
			$attid = $buf['attid'];
			$value = $buf['value'];
			$newhero[$attid] = $value;
		}	
		// 英雄负重加成
		$carryHeroAdd = 0;
		$attr = sql_fetch_one("select * from sys_hero_attribute where hid='$hero[hid]' and attid=12");
		if(!empty($attr)) {
			$carryHeroAdd = $attr['value'];
		}
		$newhero['12'] = $carryHeroAdd;
		//英雄负重百分比加成
		$carryPercentAdd = 0;
		$attr = sql_fetch_one("select * from sys_hero_attribute where hid='$hero[hid]' and attid=10012");
		if(!empty($attr)) {
			$carryPercentAdd = $attr['value'];
		}
		$newhero['10012'] = $carryPercentAdd;
		
		//君主将修为等级
		if(intval($newhero['herotype'])==1000)
		{
			$level = sql_fetch_one_cell("select level from sys_user_level where uid='$uid'");
			if(empty($level)){$level=0;}
			$newhero['kingLevel']=$level;
		}
		$newhero['curCid'] = doGetHeroState($hero['hid']);
		$ret[] = $newhero;
	}
	return $ret;
}
function doGetHeroState($hid)
{
	//获取下将领当前所在的城池坐标
	$heroInfo = sql_fetch_one("select * from sys_city_hero where hid='$hid' limit 1");
	$cid = intval($heroInfo['cid']);
	if(intval($heroInfo['state'])==4)  //将领驻守就不取原来的城池了，取出当前驻守的坐标
	{
		$cid = sql_fetch_one_cell("select targetcid from sys_troops where hid='$hid' limit 1");
	}
	return $cid;
}
function getCityInfoArmy($uid,$cid)
{
    $ret = array();                                                                          
    //本城拥有的军队                    
    checkCityOwner($cid, $uid);                 
    $ret[] = sql_fetch_rows("select * from sys_city_soldier where `cid`='$cid' order by sid");
    return $ret;
}
function getCityInfoDefence($uid,$cid)
{
    $ret = array();
    //本城拥有的城防                  
    checkCityOwner($cid, $uid);
    $ret[] = sql_fetch_rows("select * from sys_city_defence where `cid`='$cid' order by did");
    return $ret;
}

function isCardHero($heroType)
{
	return ($heroType>=21250 && $heroType<=24250);
}
?>