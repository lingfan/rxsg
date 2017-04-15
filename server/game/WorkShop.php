<?php
require_once './utils.php';


function loadInitWorkShopInfo($uid,$param)
{
	$curCid = intval(array_shift($param));
	checkCityExist($curCid,$uid);
	
	//
	$curDayZero = sql_fetch_one_cell("select unix_timestamp(curdate())");
	
	$userWorkShop = sql_fetch_one("select * from sys_user_workshop where uid='$uid'");
	if(empty($userWorkShop)){
		sql_query("insert into sys_user_workshop(`uid`) values('$uid')");
		$userWorkShop = sql_fetch_one("select * from sys_user_workshop where uid='$uid'");
	}
	$ret = array();
	$lastTime = sql_fetch_one_cell("select time from sys_user_workshop where uid='$uid'");
	$leavTime=0;
	if($lastTime>0)
	{
		$endTime = $lastTime+7200;  //2个小时
		$now = sql_fetch_one_cell("select unix_timestamp()");
		$leavTime = $endTime-intval($now)>0?$endTime-intval($now):0;
	}
	$ret[] = $leavTime;
	
	if(empty($userWorkShop['gidStr'])) return $ret;
	
	$goodArr = explode(',',$userWorkShop['gidStr']);	
	$goodCnt = array_shift($goodArr);
	$ret[] = $goodCnt;
	
	for($i=0;$i<count($goodArr);$i=$i+2)
	{
		$gid = $goodArr[$i];
		$price = $goodArr[$i+1];
		$good = sql_fetch_one("select * from cfg_goods where gid='$gid'");
		$ret[] = $good;
		$ret[] = $price;	
	}	
	return $ret;
}

function refreshWorkShop($uid,$param)
{
	$isNeedWuzhu = intval(array_shift($param));
	$curCid = intval(array_shift($param));
	checkCityExist($curCid,$uid);
	
	if($isNeedWuzhu!=0 && $isNeedWuzhu!=1) throw new Exception("数据异常！");
	
	$userWorkShop = sql_fetch_one("select * from sys_user_workshop where uid='$uid'");
	if(empty($userWorkShop)){
		sql_query("insert into sys_user_workshop(`uid`) values('$uid')");
		$userWorkShop = sql_fetch_one("select * from sys_user_workshop where uid='$uid'");
	} 
	if(intval($userWorkShop['count'])>=50) throw new Exception("今天立即刷新次数达到上限，请明天再来");
	
	$needWuzhuCount = 0;
	if($isNeedWuzhu==1)$needWuzhuCount=20;	
	$now = sql_fetch_one_cell("select unix_timestamp()");
	if(intval($now)-intval($userWorkShop['time'])<0)$needWuzhuCount=20;	
	if($needWuzhuCount>0)
	{
		$UserWuZhuCount = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='10960'");
		if(empty($UserWuZhuCount)||intval($UserWuZhuCount)<$needWuzhuCount) throw new Exception("您的五铢钱不够！");
	}	
	
	$workShopLevel = sql_fetch_one_cell("select level from sys_building where cid='$curCid' and bid='15'");
	if(empty($workShopLevel)) throw new Exception("当前城池没有工匠作坊！！！");
	
	//扣东西
	if($needWuzhuCount>0) addGoods($uid,10960,-$needWuzhuCount,1212);
	$freeRataArr = array(
		1=>45,
		2=>30,
		3=>20,
		4=>5,
	);
	$wuzhurateArr = array(
		1=>40,
		2=>30,
		3=>24,
		4=>5,
		5=>1
	);	
	$levelToCountArr = array(
		1=>1,
		2=>2,
		3=>3,
		4=>4,
		5=>5,
		6=>7,
		7=>9,
		8=>11,
		9=>13,
		10=>14	
	);
	$goodTypeRateArr = array(
		0=>5,
		1=>15,
		2=>5,
		3=>15,
		4=>25,
		6=>10,
		7=>25
	);
	$levelToPrice = array(
		1=>10,
		2=>30,
		3=>60,
		4=>90,
		5=>120
	);
	
	//生成工匠作坊物品
	$needRefreshCount = $levelToCountArr[$workShopLevel];
	$resultStr=$needRefreshCount.",";
	for($i=0;$i<$needRefreshCount;$i++)
	{
		//生成宝珠等级
		$levelRate = mt_rand(1,100);
		if($isNeedWuzhu==1)   //使用五铢钱刷新的
		{
			$resultLevel = getKey($levelRate,$wuzhurateArr);
		}else    //免费刷 不出5级珠子
		{
			$resultLevel = getKey($levelRate,$freeRataArr);
		}			
		//生成宝珠类型
		$typeRate = mt_rand(1,100);
		$attid = getKey($typeRate,$goodTypeRateArr);
		
		$newGid = 300+10*$attid+($resultLevel-1);
		$newPrice = $levelToPrice[$resultLevel]; 
		if($i==$needRefreshCount-1)
		{
			$resultStr .=$newGid.','.$newPrice;
		}else{
			$resultStr .=$newGid.','.$newPrice.',';
		}			
	}	
	$newcount = $userWorkShop['count']+1;
	$nowTime = sql_fetch_one_cell("select unix_timestamp()");
	//sql_query("update sys_user_workshop set `count`=LEAST(50,count+1),gidStr='$resultStr',time=unix_timestamp() where uid='$uid'");
	sql_query("update sys_user_workshop set `count`='$newcount',gidStr='$resultStr',time='$nowTime' where uid='$uid'");
	sql_query("insert into log_workshop_fresh(`uid`,`gidStr`,`cost`,`time`) values('$uid','$resultStr','$needWuzhuCount',unix_timestamp())");

	return loadInitWorkShopInfo($uid,array($curCid));
}


function getKey($compareValue,$arr)
{
	$sum = 0;
	$index = 0;
	foreach($arr as $key=>$value)
	{
		$sum += $value;
		if($sum>=$compareValue)
		{
			$index = $key;
			break;
		}	
	}
	return $index;
}

function buyWorkShopGood($uid,$param)
{
	$curCid = intval(array_shift($param));
	$gid = intval(array_shift($param));	
	checkCityExist($curCid,$uid);
	
	$gidStr = sql_fetch_one_cell("select gidStr from sys_user_workshop where uid='$uid'");
	if(empty($gidStr)) throw new Exception("您的工匠作坊没有数据！");
	$gidArr = explode(",",$gidStr);
	
	$gCount = array_shift($gidArr);
	$resultPrice=0;
	$resultGidStr="";
	$reclyCount = $gCount*2;
	for($i=0;$i<$reclyCount;$i=$i+2)
	{
		$curGid = intval($gidArr[$i]);
		$price = intval($gidArr[$i+1]);
		if($curGid==$gid){
			$resultPrice = $price;
			continue;
		}
		if($i<$reclyCount-1)
		{
			$resultGidStr .= $curGid.",".$price.",";
		}else{
			$resultGidStr .= $curGid.",".$price;
		}
	}
	
	if($resultPrice>0)
	{
		if(!sql_check("select 1 from sys_user where gift>$resultPrice and uid='$uid'"))throw new Exception("您的礼金不够！");
		addGoods($uid,$gid,1,1212);
		addGoods($uid,0,-$resultPrice,1212);	
		$resultGidStr = ($gCount-1).",".$resultGidStr;
		sql_query("update sys_user_workshop set gidStr='$resultGidStr' where uid='$uid'");
	}else{
		 throw new Exception("数据异常！");
	}
	
	$ret = array();
	$ret[] = "购买成功！";
	$ret[] = loadInitWorkShopInfo($uid,array($curCid));
	
	return $ret;
}