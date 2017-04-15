<?php
/**
 * 获取某个时间段充值详细数据
 * 
 * @author dengyike
 * @since 2010-10-08
 * @example 调用格式：http://PAY_DATA_URL?gameid={0}&serverid={1}&start_time={2}&end_time={3}&extra={4}&sign={5}
 */
require_once (dirname(__FILE__)."/common_interface.php");

$GTYPES = array(
				array('gtype'=>'1', 'name'=>'道具'),
				array('gtype'=>'2', 'name'=>'装备'),
				array('gtype'=>'3', 'name'=>'资源'),
				array('gtype'=>'4', 'name'=>'直接扣元宝')
		);

$CTYPES = array(
				array('ctype'=>'1', 'name'=>'购买道具'),
				array('ctype'=>'2', 'name'=>'装备修复'),
				array('ctype'=>'3', 'name'=>'市场向商人买'),
				array('ctype'=>'4', 'name'=>'市场卖给商人'),
				array('ctype'=>'5', 'name'=>'打包'),
				array('ctype'=>'6', 'name'=>'鉴定宝藏'),
				array('ctype'=>'7', 'name'=>'委托任务'),
				array('ctype'=>'8', 'name'=>'墨家立即完成'),
				array('ctype'=>'9', 'name'=>'鲁班立即完成'),
				array('ctype'=>'10', 'name'=>'休假消耗元宝'),
				array('ctype'=>'11', 'name'=>'市井传闻')
		);

function getSpendType($type) 
{
	$result = array();
	if ($type == 10) {//购买道具
		$result[] = 1;
		$result[] = 1;
	} else if ($type == 50) {//市场向商人买
		$result[] = 3;
		$result[] = 3;		
	} else if ($type == 51) {//市场卖给商人
		$result[] = 3;
		$result[] = 4;			
	} else if ($type == 52) {//打包
		$result[] = 4;
		$result[] = 5;	
	} else if ($type == 53) {//鉴定宝藏
		$result[] = 4;
		$result[] = 6;		
	} else if ($type == 54) {//委托任务
		$result[] = 4;
		$result[] = 7;		
	} else if ($type == 70) {//墨家立即完成
		$result[] = 4;
		$result[] = 8;		
	} else if ($type == 71) {//鲁班立即完成
		$result[] = 4;
		$result[] = 9;		
	} else if ($type == 75) {//休假消耗元宝
		$result[] = 4;
		$result[] = 10;		
	} else if ($type == 90) {//市井传闻
		$result[] = 4;
		$result[] = 11;		
	} else if ($type == 100) {//装备修复
		$result[] = 4;
		$result[] = 2;		
	}
	return $result;
}
function getSpendCoin($start_time, $end_time)
{//获取某个时间段内激活游戏的玩家列表
	$sql = "select a.uid,a.count, a.time,a.type, b.passport, b.passtype from log_money a, sys_user b where a.time between '$start_time' and '$end_time' and a.count < 0 and a.type in (50,51,52,53,54,70,71,75,90,100) and a.uid=b.uid";
	$spendRecords = sql_fetch_rows($sql);
	$result = array();
	foreach($spendRecords as &$spendRecord) {
		$uid = $spendRecord['uid'];
		$time = $spendRecord['time'];
		$coin = $spendRecord['count'];
		$type = $spendRecord['type'];	
		list($gtype,$ctype) = getSpendType($type);
		$gid = -1;
		$gcount = -1;
		$tmpArray = array('uid'=>$uid, 'gid'=>$gid, 'gtype'=>$gtype, 'gcount'=>$gcount, 'coin'=>$coin, 'ctype'=>$ctype, 'time'=>$time); 
		$result[] = $tmpArray;
	}
	//购买道具单独处理
	$sql = "select a.uid,a.time,a.count*a.price as totalMoney, c.passport, c.passtype,b.gid,a.count*b.pack as totalCount from log_shop a, cfg_shop b, sys_user c where a.uid=c.uid and a.shopid=b.id and a.time between '$start_time' and '$end_time'";
	$spendRecords = sql_fetch_rows($sql);
	foreach($spendRecords as &$spendRecord) {
		$uid = $spendRecord['uid'];
		$time = $spendRecord['time'];
		$type = 10;
		list($gtype,$ctype) = getSpendType($type);
		$gid = $spendRecord['gid'];//道具id
		$gcount = $spendRecord['totalCount'];//购买道具数量
		$coin= $spendRecord['totalMoney'];//coin
		$ctype = $ctype;
		$time = $time;
		$tmpArray = array('uid'=>$uid, 'gid'=>$gid, 'gtype'=>$gtype, 'gcount'=>$gcount, 'coin'=>$coin, 'ctype'=>$ctype, 'time'=>$time); 
		$result[] = $tmpArray;
	}	
	return $result;
}

////获取传递过来的参数
$gameID = $_GET['gameid'];
$serverID = $_GET['serverid'];
$time = $_GET['time'];
$start_time = $_GET['start_time'];
$end_time = $_GET['end_time'];
$extra_data = $_GET['extra'];
$sign = $_GET['sign'];

//$gameID = "RXSG_1_CHS_JOYPORT_1";
//$serverID = "1";
//$time = "1286589933";
//$start_time = "1285746714";
//$end_time = "1286530373";
//$sign = "8e1a832c06377b6d0ce0568640c56c7a";

$status = "";
$jsonResult = "";

//检查必填参数是否有为空的情况
if ( !checkParamValid($gameID, $serverID, $time, $start_time, $end_time, $sign) ) {
	$status = "params_not_valid";
} else if ( !checkClientIP("") ) {//ip合法性验证
	$status = "ip_not_valid";
} else if ( !checkTimeValid($time) ) {//时间验证
	$status = "time_error";
} else if ( !checkSignValid($gameID, $serverID, $time, $start_time, $end_time, $extra_data, $sign) ) {//签名合法性验证
	$status = "sign_error";
}

if ($status != "") {//验证失败
	$result = array('status'=>$status);
	$jsonResult = json_encode($result);
	
} else {
	$status = "data_success";
	$queryparam = array( 'gameid' => $gameID,
	        'serverid' => $serverID,
	        'start_time' => $start_time,
	        'end_time' => $end_time
	);
	
	$userPayInfoes = getSpendCoin($start_time, $end_time);
	
	$result = array('status'=>$status,
	    'queryparam' => $queryparam,
		'goods'=>$GTYPES,
		'coin' => $CTYPES,
		'records' => $userPayInfoes
	);
	$jsonResult = json_encode($result);
}
$jsonResult = bzcompress($jsonResult, 9);
echo $jsonResult;
?>