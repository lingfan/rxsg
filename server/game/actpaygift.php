<?php
require_once ("./utils.php");
require_once ('./TaskFunc.php');
require_once ('./GoodsFunc.php');

//global $uid;//注释掉
//global $money;//注释掉


$passport= sql_fetch_one_cell("select passport from sys_user where uid=$uid limit 1");

//type_id	name	description	details
//1101	首笔	首次充值满N元宝，送XX物品C个	首笔满N元宝，送物品C个，发系统信
$acttype=1101;
$payActs = sql_fetch_rows("select g.*,a.starttime,a.endtime from cfg_act_paygift g inner join cfg_act a on g.actid=a.actid where starttime<=unix_timestamp() and endtime>=unix_timestamp() and a.type=$acttype");
foreach ($payActs as $payAct) {
	$payCount = sql_fetch_one_cell("select count(1) from pay_log where passport='$passport' and time between ".$payAct['starttime']." and ".$payAct['endtime']);
	if($payCount<=1 && $money>=$payAct['money_limit']){//1.活动期间第一次充值 2.充值数量达到要求 
		rewardAndSendMail(1,$payAct['actid'],$uid,$payAct);//首充给的东西数量固定
	}
}

//type_id	name	description	details
//1102	当天首笔	当天首次充值满N元宝，送XX物品C个	当天首笔满N元宝，送物品C个，发系统信
$acttype=1102;
$payActs = sql_fetch_rows("select g.*,a.starttime,a.endtime from cfg_act_paygift g inner join cfg_act a on g.actid=a.actid where starttime<=unix_timestamp() and endtime>=unix_timestamp() and a.type=$acttype");
foreach ($payActs as $payAct) {
	$starttime = $payAct['starttime'];
	$payCount = sql_fetch_one_cell("select count(1) from pay_log where passport='$passport' and time >= greatest(unix_timestamp(curdate()),$starttime)");
	if($payCount<=1 && $money>=$payAct['money_limit']){//1.活动期间当天第一次充值 2.充值数量达到要求 
		rewardAndSendMail(1,$payAct['actid'],$uid,$payAct);//首充给的东西数量固定
		if($payAct['actid']==472) {
			giveGoods($uid,25,1,6);
		}
	}
}

//type_id	name	description	details
//1001	普通	 单笔充值达到N元宝，送XX物品M个，多充多得	单笔满N元宝，送物品M个，发系统信
$acttype=1001;
$payActs = sql_fetch_rows("select g.*,a.starttime,a.endtime from cfg_act_paygift g inner join cfg_act a on g.actid=a.actid where starttime<=unix_timestamp() and endtime>=unix_timestamp() and a.type=$acttype");
foreach ($payActs as $payAct) {
	if($money>=$payAct['money_limit']){
		if(0==$payAct['max_money_limit']){//多充多得
			$giveCount = floor ( $money / $payAct['money_limit'] );
			rewardAndSendMail($giveCount,$payAct['actid'],$uid,$payAct);
		}else if($money<=$payAct['max_money_limit']) {//分段奖励
			rewardAndSendMail(1,$payAct['actid'],$uid,$payAct);
		}
	}
}

//type_id	name	description	details
//1201	累积充值	累积充值达到N元宝，送XX物品M个，活动结束发送	每笔充值后发系统信，任务结束发奖励与系统信（脚本实现）
$acttype=1201;
$payActs = sql_fetch_rows("select g.*,a.starttime,a.endtime from cfg_act_paygift g inner join cfg_act a on g.actid=a.actid where starttime<=unix_timestamp() and endtime>=unix_timestamp() and a.type=$acttype");
foreach ($payActs as $payAct) {
	if($money>=$payAct['money_limit']){	
		$starttime = $payAct['starttime'];
		$endtime = $payAct['endtime'];
		$userTotal = sql_fetch_one_cell("select  sum(money) as total from pay_log where time between $starttime and $endtime and passport='$passport'");

		$mailtitle = $payAct ["mailtitle"];
		$mailcontent = $payAct ["mailcontent"];
		$mailcontent = str_replace ( "{userTotal}", $userTotal, $mailcontent );
		sendSysMail ( $uid, $mailtitle, $mailcontent );
	}
}

//type_id	name	description	details
//1211	单天累积充值	当天累积充值达到N元宝，送XX物品M个，累积达到时发送	当天累积充值达到N元宝，送XX物品M个，累积达到时发送奖励与系统信
$acttype=1211;
$payActs = sql_fetch_rows("select g.*,a.starttime,a.endtime from cfg_act_paygift g inner join cfg_act a on g.actid=a.actid where starttime<=unix_timestamp() and endtime>=unix_timestamp() and a.type=$acttype");
foreach ($payActs as $payAct) {
	$starttime = $payAct['starttime'];
	$endtime = $payAct['endtime'];
	$total = sql_fetch_one("select sum(money) as totalMoney, max(time) as lastTime from pay_log where time >= greatest(unix_timestamp(curdate()),$starttime) and passport='$passport'");
	
	$totalMoney = $total ["totalMoney"];
	$lastTime = $total ["lastTime"];
	
	if($totalMoney>=$payAct['money_limit']){//累积充值达到N元宝
		$beforeTotal = sql_fetch_one_cell ( "select sum(money) as totalMoney from pay_log where passport='$passport' and time between greatest(unix_timestamp(curdate()),$starttime) and $lastTime-1" );
		$giveCount = floor ( $totalMoney / $payAct['money_limit'] ) - floor ( $beforeTotal / $payAct['money_limit'] );//该得的-已得的
		//if($beforeTotal<$payAct['money_limit']){//充值前没达到（之前没发放奖励）
		if ($giveCount>0){
			rewardAndSendMail($giveCount,$payAct['actid'],$uid,$payAct);
		}
	}
}

//type_id	name	description	details
//1221	普通累积充值	累积充值每达到N元宝，送XX物品M个，多充多得，累积达到时发送	累积充值每达到N元宝，送XX物品M个，累积达到时发送奖励与系统信
$acttype=1221;
$payActs = sql_fetch_rows("select g.*,a.starttime,a.endtime from cfg_act_paygift g inner join cfg_act a on g.actid=a.actid where starttime<=unix_timestamp() and endtime>=unix_timestamp() and a.type=$acttype");
foreach ($payActs as $payAct) {
	$starttime = $payAct['starttime'];
	$endtime = $payAct['endtime'];
	$total = sql_fetch_one("select sum(money) as totalMoney, max(time) as lastTime from pay_log where time >= $starttime and passport='$passport'");
	
	$totalMoney = $total ["totalMoney"];
	$lastTime = $total ["lastTime"];
	
	if($totalMoney>=$payAct['money_limit']){//累积充值达到N元宝
		$beforeTotal = sql_fetch_one_cell ( "select sum(money) as totalMoney from pay_log where passport='$passport' and time between $starttime and $lastTime-1" );
		$giveCount = floor ( $totalMoney / $payAct['money_limit'] ) - floor ( $beforeTotal / $payAct['money_limit'] );//该得的-已得的
		rewardAndSendMail($giveCount,$payAct['actid'],$uid,$payAct);
	}
}
function openTasks($uid,$sid,$eid){//第一次打开多个连续任务
	for($tid=$sid;$tid<=$eid;$tid++){
		sql_query("insert into sys_user_task (uid,tid,state)values($uid,$tid,0 ) on duplicate key update tid=tid");
	}
	for($tid=$sid;$tid<=$eid;$tid++){
		$goals = sql_fetch_rows("select id as gid from cfg_task_goal where tid=$tid and (sort=80 or sort=50)");
		if($goals){
			foreach($goals as $goal){
				$gid=$goal[gid];
				sql_query("insert into sys_user_goal (uid,gid,currentcount) values('$uid','$gid',0) on duplicate key update gid=gid");
			}
		}
	}
}
function rewardAndSendMail($giveCount,$actid,$uid,$act_paygift) {
	if ($giveCount > 0) {
		$cid = sql_fetch_one_cell("select lastcid from sys_user where uid=$uid");
		$i=0;
		$gifts = array();
		while ($i<$giveCount) {
			$tempGift = openDefaultBox($uid, $cid, $actid, 1000);//1000为充值活动
			if ($tempGift) {
				$giftname = $tempGift[0];
				$cnt = $tempGift[1];
				if (isset($gifts[$giftname]) && $cnt>0){
					$gifts[$giftname] += $cnt;
				}else{
					$gifts[$giftname]=$cnt;
				}
			}
			$i++;
		}
		if (count($gifts)>0) {
			foreach ($gifts as $giftname=>$cnt) {
				if($cnt>0)
					$giftnameStr .= $giftname."*".$cnt."，";
				else 
					$giftnameStr .= "【".$giftname."】，";
			}
			$giftnameStr = substr($giftnameStr,0,strlen($giftnameStr)-3);
			
			$mailcontent = $act_paygift ["mailcontent"];
			$mailtitle = $act_paygift ["mailtitle"];
			$mailcontent = sprintf($mailcontent,$giftnameStr);
			sendSysMail ( $uid, $mailtitle, $mailcontent );
		}
	}
}

	//成长任务充值
	completeTaskWithTaskid($uid,295);


?>