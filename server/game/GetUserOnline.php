<?php
/**
 * 获取某个时间段内玩家在线时间列表
 * 
 * @author dengyike
 * @since 2010-10-08
 * @example 调用格式：http://PAY_DATA_URL?gameid={0}&serverid={1}&start_time={2}&end_time={3}&extra={4}&sign={5}
 */
require_once (dirname(__FILE__)."/common_interface.php");

function dates_inbetween($beginDay, $endDay)
{
	$day = 60 * 60 * 24;
	$date1 = strtotime($beginDay);
	$date2 = strtotime($endDay);
	$days_diff = round(($date2 - $date1) / $day);
	$dates_array = array();
	$dates_array[] = date('Y-m-d', $date1);
	if ($days_diff > 0 ) {
		for ($i = 1; $i < $days_diff; $i++) {
			$dates_array[] = date('Y-m-d', ($date1 + ($day * $i)));
		}
		$dates_array[] = date('Y-m-d', $date2);		
	}
	return $dates_array;
}
function getPreDay($curDay)
{
	$day = 60 * 60 * 24;
	$date1 = strtotime($curDay);	
	$result = date('Y-m-d', ($date1 - $day ));
	return $result;
}
function isTableExist($tableName) 
{
	$sql = "select * from information_schema.tables where table_schema='bloodwarlog' and table_name = '$tableName'";
	$tableRecords = sql_fetch_rows($sql);
	if (count($tableRecords) > 0) {
		return true;
	} else { 
		return false;
	}
}

function getUserOnlinePerDay($curDay,$preDay) 
{
	$sql = "select a.uid,a.onlinetime-b.onlinetime as onlineTime from bloodwarlog.`log_user_$curDay` a left join bloodwarlog.`log_user_$preDay` b on a.uid=b.uid where a.onlinetime - b.onlinetime>0";
	$onlineRecords = sql_fetch_rows($sql);
	$totalPeople = count($onlineRecords);//总人数
	$today = strtotime($curDay);
	$records = array();
	foreach ($onlineRecords as $onlineRecord) {
		$uid = $onlineRecord['uid'];
		$onlineTime = $onlineRecord['onlineTime'];
		$tmpArray = array('uid' => $uid, 'onlinetime' => $onlineTime);
		$records[] = $tmpArray;
	}
	$result = array('day' => $today, 'cnt' => $totalPeople,'rows' => $records);
	return $result;
}
function getUserOnline($start_time, $end_time)
{//获取某个时间段内玩家在线时间列表
	$sql = "select date_format(from_unixtime($start_time), '%Y-%m-%d') as beginDay,  date_format(from_unixtime($end_time), '%Y-%m-%d') as endDay";
	$dayRecord = sql_fetch_one($sql);
	$beginDay = $dayRecord['beginDay'];
	$endDay = $dayRecord['endDay'];
	$dates_array = dates_inbetween($beginDay, $endDay);
	$result = array();

	foreach($dates_array as $date) {
		$record = "";
		$preDay = getPreDay($date);
		$tableName = "log_user_$date";
		$tableName2 = "log_user_$preDay";

		if (isTableExist($tableName) && isTableExist($tableName2)) {
			$record = getUserOnlinePerDay($date, $preDay);
			$result[] = $record;
		}
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

//$gameID = "RXSG_1_ZH_CN_WANGYE173_1";
//$serverID = "12";
//$time = "1288856662";
//$start_time = "1285862400";
//$end_time = "1285866000";
//$sign = "7a5c5a293c96f8067baf93a2b68d5bac";


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
	
	$userOnlineInfoes = getUserOnline($start_time, $end_time);
	
	$result = array('status'=>$status,
	    'queryparam' => $queryparam,
		'records' => $userOnlineInfoes
	);
	$jsonResult = json_encode($result);
}
$jsonResult = bzcompress($jsonResult, 9);
echo $jsonResult;

?>