<?php
/**
 * 获取某个时间段内激活游戏的玩家列表
 * 
 * @author dengyike
 * @since 2010-10-08
 * @example 调用格式：http://PAY_DATA_URL?gameid={0}&serverid={1}&start_time={2}&end_time={3}&extra={4}&sign={5}
 */
require_once (dirname(__FILE__)."/common_interface.php");

function getActivatedUsers($start_time, $end_time)
{//获取某个时间段内激活游戏的玩家列表
	$sql = "select uid, name, passport, passtype, regtime, state from sys_user where regtime between '$start_time' and '$end_time' and uid > 1000";
	$activedsUsers = sql_fetch_rows($sql);
	$result = array();
	$iCount = 0;
	foreach($activedsUsers as &$user) {
		$iCount++;
		$tmpArray = array();
		$uid = $user['uid'];
		$passport = $user['passport'];
		$passport = iconv("utf-8","gb2312//IGNORE", $passport);
		$passtype = $user['passtype'];
		$regtime = $user['regtime'];	
		$kingName = $user['name'];
		
		$state = $user['state'];
		
		if ($state != 3) {//创建了君主名
			$sql = "select time from log_goods where gid='50101' and uid = '$uid' and `type`=4";
			$logRecord = sql_fetch_one($sql);
			if (!empty($logRecord)) {
				$createTime = $logRecord['time'];
			} else {
				$createTime = 0;
			}
		} else 
		{
			$createTime = 0;
		}
		$tmpArray = array('uid'=>$uid, 'passport'=>$passport, 'passtype'=>$passtype, 'regtime'=>$regtime, 'rolename'=>$kingName, 'createtime'=>$createTime);
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
//$start_time = "1286804084";
//$end_time = "1288344608";
//$sign = "be97ec1153f7540418414f1b16593594";
////gameid=XRXSG_4_CHS_JOYPORT_1&serverid=1&time=1286773273&start_time=1273622400&end_time=1286496000&sign=32731a518d53d724176449e1b3cd4ae4&ip=61.147.67.100

$status = "";
$jsonResult = "";

//检查必填参数是否有为空的情况
if ( !checkParamValid($gameID, $serverID, $time, $start_time, $end_time, $sign) ) {
	$status = "params_not_valid";
} else if ( !checkClientIP("") ) {//ip合法性验证
	$status = "ip_not_valid";
} else if ( !checkTimeValid($time) ) {//时间验证
	$status = "time_error";
} else if ( !checkSignValid($gameID, $serverID,  $time, $start_time, $end_time, $extra_data, $sign) ) {//签名合法性验证
	$status = "sign_error";
}

if ($status != "") {//验证失败
	$result = array('status'=>$status);
	$jsonResult = json_encode($result);
	
} else {
	//获取某个时间段内激活游戏的玩家列表
	$status = "data_success";
	$queryparam = array( 'gameid' => $gameID,
	        'serverid' => $serverID,
	        'start_time' => $start_time,
	        'end_time' => $end_time
	);
	
	$activedUsers = getActivatedUsers($start_time, $end_time);
	
	$result = array('status'=>$status,
	    'queryparam' => $queryparam,
		'records' => $activedUsers
	);
	
	$jsonResult = json_encode($result);
}

$jsonResult = bzcompress($jsonResult, 9);
echo $jsonResult;
?>