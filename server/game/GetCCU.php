<?php
/**
 * 获取某个时间段内同时在线玩家数
 * 
 * @author pcy
 * @since 2011-01-13
 * @example 调用格式：http://PAY_DATA_URL?gameid={0}&serverid={1}&start_time={2}&end_time={3}&extra={4}&sign={5}
 */
require_once (dirname(__FILE__)."/common_interface.php");
function getCCU($start_time, $end_time)
{//获取某个时间段内同时在线玩家数
	$start_time=intval($start_time);
	$end_time=intval($end_time);
	if($start_time<1213891200) //2008-06-20
	{
		$start_time=1213891200;
	}
	$now=time();
	if($end_time>$now)
	{
		$end_time=$now;
	}
	//30秒在线
	$sql="SELECT time,online FROM bloodwarlog.log_online WHERE time>'$start_time' AND time<'$end_time'";
	$onlines = sql_fetch_rows($sql);
	//10分钟在线
	$sql="SELECT time,online FROM bloodwarlog.log_online10 WHERE time>'$start_time' AND time<'$end_time'";
	$online10s = sql_fetch_rows($sql);
	//30分钟在线
	$sql="SELECT time,online FROM bloodwarlog.log_online30 WHERE time>'$start_time' AND time<'$end_time'";
	$online30s = sql_fetch_rows($sql);

	$result = array();

	foreach($onlines as $online) {
		$result[]=array('time'=>$online['time'],'cnt'=>$online['online'],'type'=>'1');
	}
	foreach($online10s as $online) {
		$result[]=array('time'=>$online['time'],'cnt'=>$online['online'],'type'=>'2');
	}
	foreach($online30s as $online) {
		$result[]=array('time'=>$online['time'],'cnt'=>$online['online'],'type'=>'3');
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
	
	$userCCUInfoes = getCCU($start_time, $end_time);
	
	$result = array('status'=>$status,
	    'queryparam' => $queryparam,
		'records' => $userCCUInfoes
	);
	$jsonResult = json_encode($result);
}
$jsonResult = bzcompress($jsonResult, 9);
echo $jsonResult;