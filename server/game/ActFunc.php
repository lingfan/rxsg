<?php
require_once ("./interface.php");
require_once './TaskFunc.php';
require_once './GoodsFunc.php';
require_once './utils.php';


function getAvailableActRateByType($type) {
	$actRate=sql_fetch_one_cell("select rate from cfg_act where type=".intval($type)." and starttime<=unix_timestamp() and endtime>=unix_timestamp() limit 1");
	if(empty($actRate)){
		return false;
	}
	return $actRate;
}

function getAvailableActByType($type) {
	$act=sql_fetch_one("select actid, rate from cfg_act where type=".intval($type)." and starttime<=unix_timestamp() and endtime>=unix_timestamp() limit 1");
	if(empty($act)){
		return false;
	}
	return $act;
}
function getAvailableActByTypeRange($typestart,$typeend) {
	$act=sql_fetch_rows("select actid, rate,type from cfg_act where type between ".intval($typestart)." and ".intval($typeend)." and starttime<=unix_timestamp() and endtime>=unix_timestamp() limit 1");
	if(empty($act)){
		return false;
	}
	return $act;
}

function getAvailableBattleActs() {//[3001-3999]为战场活动
	$acts=sql_fetch_rows("select actid, rate, type from cfg_act where type between 3001 and 3999 and starttime<=unix_timestamp() and endtime>=unix_timestamp()");
	return $acts;
}

function isLucky($luckyResult, $maxResult, $minResult=1) {
	$rand = mt_rand ($minResult, $maxResult);
	if ($luckyResult >= $rand) {
		return true;
	}
	return false;
}


/*
 * *************** 公用接口 ***************
 */
function isActHero($heroType) {
	return ($heroType > 10 && $heroType != 100 && $heroType < 20000);
}
function hasAct($actid, $condition = "") {
	if ($condition != "")
		$condition = " and " . $condition;
	$act = sql_fetch_one ( "select actid from cfg_act where actid='$actid' $condition and unix_timestamp() between starttime and endtime" );
	return ! empty ( $act );
}

/*
 * *************** 招降将领活动接口 ***************
 */
function isSummonHeroAct($hero) {
	if (! isActHero ( $hero ["herotype"] ))
		return false;
	$actid = sql_fetch_one_cell ( "select actid from cfg_recruit_hero where herotype='$hero[herotype]'" );
	return hasAct ( $actid, "type=11" );
}
function getActSummonNeed($hero) {
	if (! isSummonHeroAct ( $hero ))
		return array ();
	$herotype = $hero ['herotype'];
	$list = sql_fetch_rows ( "select * from cfg_act_hero_summon where herotype='$herotype'" );
	return $list;
}
function sureActSummonHero($uid, $cid, $hero) {
	$list = getActSummonNeed ( $hero );
	if (count ( $list ) > 0) {
		foreach ( $list as $item ) {
			if (! checkGoalComplete ( $uid, $item )) {
				$msg = sprintf ( $GLOBALS ['sureSummonHero'] ['no_enough_goods'], $item ['name'], $hero ['name'] );
				throw new Exception ( $msg );
			}
		}
		//有足够的宝物的话，开始扣
		foreach ( $list as $item ) {
			reduceGoal ( $uid, 0, $item, 7 );
		}
		
		//检查要不要送东西		
		$actid = sql_fetch_one_cell ( "select actid from cfg_recruit_hero where herotype='$hero[herotype]'" );
		$rate = sql_fetch_one_cell ( "select rate from cfg_act where actid='$actid'" );
		$rand = mt_rand ( 1, 100 );
		$msg = ""; //得到东西要通知用户
		if ($rate >= $rand) {
			$msg = openDefaultBox ( $uid, $cid, $hero['herotype'], 11 ); //11为招降将领活动
		}
		return $msg;
	}
	return "NoSuchAct";//没有招降将领活动
}


/*
 * 客栈招募将领活动
 */
function checkAndDoRecruitHeroAct($uid, $cid, $herotype) {
	if(isActHero($herotype)){
		$actRate = sql_fetch_one_cell("select rate from cfg_act where actid in ( select actid from cfg_recruit_hero where herotype='$herotype') and unix_timestamp() between starttime and endtime limit 1");
		if ($actRate) { //有活动
			$msg = openDefaultBox ( $uid, $cid, $herotype, 2000 ); //2000为招募将领活动
			return $msg;
		}
	}
	return false;
}

/*
 * 鉴定宝藏活动
 */
function checkAndDoTreasureIdentifyAct($uid, $cid) {
	$act = getAvailableActByType(2101);
	if ($act) { //有活动	
		if (isLucky($act ['rate'],110)) {
			$msg = openDefaultBox ($uid, $cid, $act['actid'], 2100); //2100为鉴定宝藏活动
			return $msg;	
		}	
	}
	return false;
}


/*
 * 商城活动
 */
function checkAndDoShopAct($money, $uid) {
	$msg = '';
	$cid = sql_fetch_one_cell("select lastcid from sys_user where uid=$uid");
	$act = getAvailableActByType(5001);
	if ($act) { //有活动
		$rate = $act ['rate'] * $money;
		$maxRate = 3000;//概率上限30%
		if ($rate > $maxRate) { 
			$rate = $maxRate;
		}
		if (isLucky($rate,10000)) {		
			$gift= openDefaultBox ( $uid, $cid, $act ['actid'], 5000 ); //5000为商城活动
			if($gift){
				$msg .= $gift." ";		
			}
		}
	}
	
	$acttype=5002;
	$payActs = sql_fetch_rows("select g.*,a.starttime,a.endtime from cfg_act_shopgift g inner join cfg_act a on g.actid=a.actid where starttime<=unix_timestamp() and endtime>=unix_timestamp() and a.type=$acttype");
	foreach ($payActs as $payAct) {
		if($money>=$payAct['money_limit']){
			if(0==$payAct['max_money_limit']){//多充多得
				$giveCount = floor ( $money / $payAct['money_limit'] );
				$gift = openDefaultBox ( $uid, $cid, $payAct ['actid'], 5000, $giveCount); //5000为商城活动
				if($gift){
					$msg .= $gift." ";		
				}
			}else if($money<=$payAct['max_money_limit']) {//分段奖励
				$gift = openDefaultBox ( $uid, $cid, $payAct ['actid'], 5000); //5000为商城活动
				if($gift){
					$msg .= $gift." ";		
				}		
			}
		}
	}
	$acttype=5101;//当天首次消费
	$payActs = sql_fetch_rows("select g.*,a.starttime,a.endtime from cfg_act_shopgift g inner join cfg_act a on g.actid=a.actid where starttime<=unix_timestamp() and endtime>=unix_timestamp() and a.type=$acttype");
	foreach ($payActs as $payAct) {
		$starttime = $payAct['starttime'];
		$payCount = sql_fetch_one_cell("select count(1) from log_shop where uid='$uid' and time >= greatest(unix_timestamp(curdate()),$starttime) and paytype=0");
		if($payCount<=1 && $money>=$payAct['money_limit']){//1.活动期间当天第一次消费 2.消费数量达到要求 
			$gift = openDefaultBox ( $uid, $cid, $payAct ['actid'], 5000, 1); //5000为商城活动
			if($gift){
				$msg .= $gift." ";		
			}
		}
	}
	$acttype=5201;//当天累计消费
	$payActs = sql_fetch_rows("select g.*,a.starttime,a.endtime from cfg_act_shopgift g inner join cfg_act a on g.actid=a.actid where starttime<=unix_timestamp() and endtime>=unix_timestamp() and a.type=$acttype");
	foreach ($payActs as $payAct) {
		$starttime = $payAct['starttime'];
		$endtime = $payAct['endtime'];
		$total = sql_fetch_one("select sum(price*count) as totalMoney, max(time) as lastTime from log_shop where time >= greatest(unix_timestamp(curdate()),$starttime) and uid='$uid' and paytype=0");
		$totalMoney = $total ["totalMoney"];
		$lastTime = $total ["lastTime"];
		if($totalMoney>=$payAct['money_limit']){//累积消费达到N元宝
			$beforeTotal = sql_fetch_one_cell ( "select sum(price*count) as totalMoney from log_shop where uid='$uid' and time between greatest(unix_timestamp(curdate()),$starttime) and $lastTime-1 and paytype=0" );
			$giveCount = floor ( $totalMoney / $payAct['money_limit'] ) - floor ( $beforeTotal / $payAct['money_limit'] );//该得的-已得的
			if($giveCount<=0){
				continue;
			}
			$gift = openDefaultBox ( $uid, $cid, $payAct ['actid'], 5000, $giveCount); //5000为商城活动
			if($gift){
				$msg .= $gift." ";		
			}
		}
	}
	//5010	普通累积消费	累积充值每达到N元宝，送XX物品M个，多充多得，累积达到时发送	
	$acttype=5010;
	$payActs = sql_fetch_rows("select g.*,a.starttime,a.endtime from cfg_act_shopgift g inner join cfg_act a on g.actid=a.actid where starttime<=unix_timestamp() and endtime>=unix_timestamp() and a.type=$acttype");
	foreach ($payActs as $payAct) {
		$starttime = $payAct['starttime'];
		$endtime = $payAct['endtime'];
		$total = sql_fetch_one("select sum(price*count) as totalMoney, max(time) as lastTime from log_shop where time >= $starttime and uid='$uid' and paytype=0");
		$totalMoney = $total ["totalMoney"];
		$lastTime = $total ["lastTime"];
		if($totalMoney>=$payAct['money_limit']){//累积充值达到N元宝
			$beforeTotal = sql_fetch_one_cell ( "select sum(price*count) as totalMoney from log_shop where uid='$uid' and time between $starttime and $lastTime-1 and paytype=0" );
			$giveCount = floor ( $totalMoney / $payAct['money_limit'] ) - floor ( $beforeTotal / $payAct['money_limit'] );//该得的-已得的
			if($giveCount<=0){
				continue;
			}
			$gift = openDefaultBox ( $uid, $cid, $payAct ['actid'], 5000, $giveCount); //5000为商城活动
			if($gift){
				$msg .= $gift." ";		
			}
		}
	}
	return $msg;
}

/*
 * 寻宝活动--要藏宝图的
 */
function checkAndDoTreasureHuntAct($uid, $cid) {
	$act = getAvailableActByType(2401);
	if ($act) { //有活动
		if (isLucky($act ['rate'],100)) { //
			$msg = openDefaultBox ( $uid, $cid, $act ['actid'], 2400 ); //1为寻宝活动
		}
		return $msg;
	}
	return false;
}


/*
 * 回收装备活动
 */
function checkAndDoArmorRecycleAct($uid,$armorType) {
	//1：灰装，2：白装，3：绿装，4：蓝装，5：紫装，6：橙装，7：红装',
	//cfg_act 6300+armortype
	$act = getAvailableActByType($armorType+6400);
	if ($act) { //有活动	
		//if (isLucky($act ['rate'],100)) {
			$msg = openDefaultBox ($uid, 0, $act['actid'], 6000);
			return $msg;
		//}		
	}
	return false;
}
/*
 * 强化装备活动
 */
function checkAndDoStrongArmorAct($uid,$armorid,$tolevel,$is_zuoji) {
	$acts=sql_fetch_rows("select actid, rate, type from cfg_act where type between 6000 and 6399 and starttime<=unix_timestamp() and endtime>=unix_timestamp()");
	foreach($acts as $act){ //有活动	
		if (isLucky($act ['rate'],100)) {
			$actid=$act ['actid'];
			$armorType=$act['type'];
			$actarmorid=sql_fetch_one_cell("select hid as armorid from cfg_act_fight where actid=$actid");
			$sort=floor(($armorType-6000)/100); //0 装备  1坐骑 2都可以  3套装
			$acttolevel=$armorType%100;//活动要强化到的等级  11强化成功不分等级
			if($sort==3){
				$tieid=sql_fetch_one_cell("select tieid from cfg_armor where id='$armorid'");
			}
			if(($actarmorid==0||$actarmorid==false||($armorid!=0&&($armorid==$actarmorid||$actarmorid==$tieid)))&&($tolevel==$acttolevel||$acttolevel==11)&&($sort==$is_zuoji||$sort==2||$sort==3)){
				$msg = openDefaultBox ($uid,$cid, $act['actid'], 6000);
				return $msg;
			}	
		}
	}
	return false;
}

/*
 * 简易礼盒额外奖励活动
 */
function checkAndDoSimpleBoxAct($uid,$gid) {
	$actRate = getAvailableActRateByType(15);
	if ($actRate) { //有活动
		if (isLucky($actRate,100)) {
			$ret = openDefaultBox ( $uid, 0, $gid, 8 ); //8为简易礼盒额外奖励活动
		//$cid直接设0，如果有与$cid相关的物品则到程序中计算出$cid， 具体还未实现
		}
		if($ret && count($ret)>0){
			return $ret[0];//取第一个,只有第一个有值
		}		
	}
	return false;
}

/*
 * 市场活动
 */
function checkAndDoMarketAct($uid, $cid) {
	$act = getAvailableActByType(17);
	if ($act) { //有活动	
		if (isLucky($act ['rate'],100)) {
			$msg = openDefaultBox ($uid, $cid, $act['actid'], 12); //12为市场活动		
		}
		return $msg;
	}
	return false;
}

/*
 * 采集活动--不用藏宝图
 */
function checkAndDoGatherAct($uid, $cid) {
    /*这个是原来的只取一条活动记录
   	$act = getAvailableActByType(2301);
	if ($act){//有活动	
		if (isLucky($act ['rate'],100)) {
			$msg = openDefaultBox ($uid, $cid, $act['actid'], 2300); //2300为采集活动	
		}
		return $msg;
	}
	return false;
	*/
	//====这个是我改的因为活动有多条就随机取活动记录中的一条
	 $acts=sql_fetch_into_arrays("select actid, rate from cfg_act where type=2301 and starttime<=unix_timestamp() and endtime>=unix_timestamp()");
	 if(empty($acts)) return false;
	 $actnum=count($acts['actid']);
	 $actrates=0;
	 if($actnum>0)
	   $actrates = mt_rand(0,$actnum-1);
	 $actid=$acts['actid'][$actrates];
	 $actrate=$acts['rate'][$actrates];
	 if (isLucky($actrate,45)) {
			$msg = openDefaultBox ($uid, $cid, $actid, 2300); //2300为采集活动	
		}
	 return $msg;
	//=====
}

/* 随机活动：
 * 采集活动--采集XX类资源M个
 * 释放俘虏--释放XX兵M个
 * 治疗伤兵--治疗XX兵M个  
 * 招降俘虏  
 */

function finishTask($uid,$count,$tasktype) {
	sql_query("update sys_user_goal set currentcount=currentcount+'$count'  where uid='$uid' and gid in (select id from cfg_task_goal where sort=50 and type='$tasktype')");
}
function finishTaskMaxNum($uid,$count,$tasktype) {
	sql_query("update sys_user_goal set currentcount=greatest(currentcount,'$count')  where uid=$uid and gid in (select id from cfg_task_goal where sort=80 and type='$tasktype')");
}
		

/*
 * 登录活动  5元宝与充值奖励改成活动奖励
 */
function checkAndDoLoginAct($uid, $date) { //注意: 只取一个活动
	$act = getAvailableActByType(10);
	if ($act) { //有活动
		$rate = $act ['rate'];
		for($rewardtype = 3; $rewardtype <= 4; $rewardtype ++) {
			if (isLucky($rate,100)) { 
				$row = getOpenDefaultGoodsResult ( $uid, 10, $act ['actid'] ); //10为登录活动
				if(empty($row)){
					continue;
				}
				$boxdetail_id = $row ['id'];
				sql_query ( "update sys_user_login_reward set boxdetail_id=$boxdetail_id where uid=$uid and rewardtype=$rewardtype and date='$date'" );
			}
		}
	}
}

/*
 *根据概率从cfg_box_details中取出1条记录 
 * 
 */
function pickOneFromBoxConifg($srctype, $srcid) {
	$rows = sql_fetch_rows ( "select * from cfg_box_details where srctype=".intval($srctype)." and srcid=".intval($srcid));
	return pickOne($rows);
}
/*
 *根据概率从cfg_recruit_hero中取出1条记录 
 * 
 */
function pickOneFromHeroConfig($actid) {
	$rows = sql_fetch_rows("select * from cfg_recruit_hero where actid = ".intval($actid));
	return pickOne($rows);
}
function pickOne($rows) {
	if ($rows) {		
		$rateSum = 0;
		foreach ( $rows as $row ) {
			$rateSum += $row ['rate'];
		}
		$rate = mt_rand ( 1, $rateSum );
		$curRateSum = 0;
		foreach ( $rows as $temprow ) {
			$curRateSum += $temprow ['rate'];
			if ($curRateSum >= $rate) { //中奖
				return $temprow;
			}
		}
	}
	return false;//没配置东西
}
?>