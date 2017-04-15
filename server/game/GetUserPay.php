<?php
/**
 * 获取某个时间段充值详细数据
 * 
 * @author dengyike
 * @since 2010-10-08
 * @example 调用格式：http://PAY_DATA_URL?gameid={0}&serverid={1}&start_time={2}&end_time={3}&extra={4}&sign={5}
 */
require_once (dirname(__FILE__)."/common_interface.php");

function getUserPay($start_time, $end_time)
{//获取某个时间段充值详细数据
	$sql = "select a.orderid, a.type, a.payname, b.uid, a.passport, a.passtype, a.money, a.time from pay_log a, sys_user b where a.passport=b.passport and a.time between '$start_time' and '$end_time'";
	$payRecords = sql_fetch_rows($sql);
	$result = array();

	foreach($payRecords as &$payRecord) {	
		$orderid = $payRecord['orderid'];
		$type = $payRecord['type'];
		$payname = $payRecord['payname'];
		$uid = $payRecord['uid'];
		$coin = $payRecord['money'];//元宝数
		try {
			$money = intval($coin) / 10; 
		} catch (Exception $e) {//人民币数
			$money = 0;
		}
		$mtype = InterfaceConstants::$MONEY_TYPE;//充值类型:人民币
		$time = $payRecord['time'];
		$tmpArray = array('orderid'=>$orderid, 'type'=>$type, 'payname'=>$payname, 'uid'=>$uid, 'money'=>$money, 'mtype'=>$mtype, 'coin'=>$coin, 'time'=>$time);
		$result[] = $tmpArray;
	}
	return $result;
}

//获取传递过来的参数
$gameID = $_GET['gameid'];
$serverID = $_GET['serverid'];
$time = $_GET['time'];
$start_time = $_GET['start_time'];
$end_time = $_GET['end_time'];
$extra_data = $_GET['extra'];
$sign = $_GET['sign'];


//$gameID = "RXSG_1_CHS_staff_22";
//$serverID = "1";
//$time = "1287646840";
//$start_time = "1285862400";
//$end_time = "1285866000";
//$sign = "be97ec1153f7540418414f1b16593594";

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
	
	$userPayInfoes = getUserPay($start_time, $end_time);
	
	$result = array('status'=>$status,
	    'queryparam' => $queryparam,
		'records' => $userPayInfoes
	);
	$jsonResult = json_encode($result);
}
$jsonResult = bzcompress($jsonResult, 9);
echo $jsonResult;

?>