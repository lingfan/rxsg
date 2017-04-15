<?php
/**
 * 获取某个时间段内在线的玩家人数
 * 
 * @author dengyike
 * @since 2010-10-08
 * @example 调用格式：http://PAY_DATA_URL?gameid={0}&serverid={1}&start_time={2}&end_time={3}&extra={4}&sign={5}
 */
require_once (dirname(__FILE__)."/common_interface.php");

function getUserLoginCount($start_time, $end_time)
{//获取某个时间段内在线的玩家人数
	$intervalTime = 600; //10分钟为限
	$numOfRecords = (intval($end_time) - intval($start_time)) / $intervalTime;
	$tmp_beginTime = $start_time;
	$result = array();
	for ($i = 0; $i < $numOfRecords; ++$i) {
		$tmp_endTime = $tmp_beginTime + $intervalTime;
		if ($tmp_endTime > $end_time) {
			$tmp_endTime = $end_time;
		}
		$sql = "select count(*) as totalNumber from log_login where time between '$tmp_beginTime' and '$tmp_endTime'";
		$userRecord = sql_fetch_one($sql);
		$count = $userRecord['totalNumber'];
		$tmpArray = array('time'=>$tmp_endTime, 'cnt'=>$count);
		$result[] = $tmpArray;
		$tmp_beginTime = $tmp_endTime;
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

//$gameID = "RXSG_1_CHS_JOYPORT_1";
//$serverID = "1";
//$time = "1286589933";
//$start_time = "1285381617";
//$end_time = "1285388617";
//$sign = "6ca4445a83b66328172d7f521673c821";


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
	
	$userLoginInfoes = getUserLoginCount($start_time, $end_time);
	
	$result = array('status'=>$status,
	    'queryparam' => $queryparam,
		'records' => $userLoginInfoes
	);
	$jsonResult = json_encode($result);
}
$jsonResult = bzcompress($jsonResult, 9);
echo $jsonResult;
?>