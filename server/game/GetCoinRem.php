<?php
/**
 * 获取当前时间点玩家手上未消费的游戏币数据
 * 
 * @author dengyike
 * @since 2010-10-08
 * @example 调用格式：http://PAY_DATA_URL?gameid={0}&serverid={1}&start_time={2}&end_time={3}&extra={4}&sign={5}
 */
require_once (dirname(__FILE__)."/common_interface.php");

function getCoinRem()
{//获取当前时间点玩家手上未消费的游戏币数据
	$sql = "select uid, passport, passtype, money from sys_user where state <= 2 and uid > 1000";
	$userRecords = sql_fetch_rows($sql);
	$result = array();
	foreach($userRecords as &$userRecord) {
		$uid = $userRecord['uid'];
		$money = $userRecord['money'];//元宝数
		if ($money > 0) {
			$tmpArray = array('uid'=>$uid, 'coin'=>$money);
			$result[] = $tmpArray;
		}
	}
	return $result;
}

//获取传递过来的参数
$gameID = $_GET['gameid'];
$serverID = $_GET['serverid'];
$time = $_GET['time'];
$extra_data = $_GET['extra'];
$sign = $_GET['sign'];

$status = "";
$jsonResult = "";

//$gameID = "XRXSG_4_CHS_JOYPORT_1";
//$serverID = "1";
//$time = "1286763883";
//$sign = "34742391742c5081f3920c18f0cede1e";

//检查必填参数是否有为空的情况
if ( !isset($gameID) || !isset($serverID) || !isset($time) || !isset($sign) ) {
	$status = "params_not_valid";
} else if ( !checkClientIP("") ) {//ip合法性验证
	$status = "ip_not_valid";
} else if ( !checkTimeValid($time) ) {//时间验证
	$status = "time_error";
} else {
	//签名合法性验证
	$param = "";
	if (isset($extra_data) && !empty($extra_data)) {
		$param = strtolower(md5($gameID . $serverID . $time . $extra_data . InterfaceConstants::$MD5_KEY));
	} else {
		$param = strtolower(md5($gameID . $serverID . $time . InterfaceConstants::$MD5_KEY));
	}
	$result = strtolower(md5($param . InterfaceConstants::$MD5_KEY));
	if ( $result !== strtolower($sign) ) {
		$status = "sign_error";
	}
}

$curTime = time();

if ($status != "") {//验证失败
	$result = array('status'=>$status);
	$jsonResult = json_encode($result);
	
} else {
	$status = "data_success";
	$queryparam = array( 'gameid' => $gameID,
	        'serverid' => $serverID,
	        'time' => $curTime
	);
	$userRecords = getCoinRem();
	$result = array('status'=>$status,
	    'queryparam' => $queryparam,
		'records' => $userRecords
	);
	$jsonResult = json_encode($result);
}
$jsonResult = bzcompress($jsonResult, 9);
echo $jsonResult;
?>