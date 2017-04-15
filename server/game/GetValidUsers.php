<?php
/**
 * 获取截止到查询时间点的有效用户信息
 * 
 * @author dengyike
 * @since 2010-10-08
 * @example 调用格式：http://PAY_DATA_URL?gameid={0}&serverid={1}&start_time={2}&end_time={3}&extra={4}&sign={5}
 */
require_once (dirname(__FILE__)."/common_interface.php");

function getValidUsers($endTime)
{//获取截止到查询时间点的有效用户信息,
//	$sql = "select aa.uid,aa.name,aa.state,max(cc.level) as validLevel from sys_user aa left join sys_city bb on aa.uid=bb.uid left join sys_building cc on bb.cid=cc.cid where aa.regtime<'$endTime' and cc.bid=6 and aa.uid > 1000 group by aa.uid having validLevel>=3";
	$result = array();	
	$sql = "select aa.uid,aa.name as rolename,aa.regtime as createtime,1 as type from sys_user aa inner join (select uid,min(time) as tt from log_goods where gid=39 and type=4 and count=1 group by uid) bb on aa.uid=bb.uid where bb.tt<='$endTime'";
	$validUsers = sql_fetch_rows($sql);
	
	$sql="select aa.uid,aa.name as rolename,aa.regtime as createtime,2 as type from sys_user aa inner join (select uid,min(time) as tt from log_goods where gid=-1 and type=33 and count=1 group by uid) bb on aa.uid=bb.uid inner join (select uid,min(time) as tt from log_goods where gid=-2 and type=33 and count=1 group by uid) cc on aa.uid=cc.uid where bb.tt<='$endTime' and cc.tt<='$endTime'";
	$valid2Users = sql_fetch_rows($sql);
	
	//深度有效 必须是 有效用户 先这样实现吧 以后再改
	$sql="select aa.uid,aa.name as rolename,aa.regtime as createtime,1 as type from sys_user aa inner join (select uid,min(time) as tt from log_goods where gid=-1 and type=33 and count=1 group by uid) bb on aa.uid=bb.uid inner join (select uid,min(time) as tt from log_goods where gid=-2 and type=33 and count=1 group by uid) cc on aa.uid=cc.uid where bb.tt<='$endTime' and cc.tt<='$endTime'";
	$valid3Users = sql_fetch_rows($sql);
	if (empty($valid2Users)) {
		$valid2Users = array();
	}
	if (empty($validUsers)) {
		$validUsers = array();
	}
	if (empty($valid3Users)) {
		$valid3Users = array();
	}
	$result = array_merge($validUsers,$valid2Users,$valid3Users);
	
//	foreach($validUsers as &$user) {
//		$tmpArray = array();
//		$uid = $user['uid'];
//		$kingName = $user['name'];
//		$state = $user['state'];
//		$level = $user['validLevel'];//官府等级
//		if ($level >=3 && $level < 5) {//初级有效
//			$validFlag = 1;
//		} else {
//			$validFlag = 2;
//		}
//		if ($state != 3) {//创建了君主名
//			$sql = "select time from log_goods where gid='50101' and uid = '$uid' and `type`=4";
//			$logRecord = sql_fetch_one($sql);
//			if (!empty($logRecord)) {
//				$createTime = $logRecord['time'];
//			} else {
//				$createTime = 0;
//			}
//		} else 
//		{
//			$createTime = 0;
//		}
//		$tmpArray = array('uid'=>$uid, 'rolename'=>$kingName, 'createtime'=>$createTime, 'type'=>$validFlag);
//		$result[] = $tmpArray;
//	}
	return $result;
}

////获取传递过来的参数
$gameID = $_GET['gameid'];
$serverID = $_GET['serverid'];
$time = $_GET['time'];
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

//$gameID, $serverID, $time, $start_time, $end_time, $sign
//检查必填参数是否有为空的情况
if ( !checkParamValid($gameID, $serverID, $time,'','', $sign) ) {
	$status = "params_not_valid";
} else if ( !checkClientIP("") ) {//ip合法性验证
	$status = "ip_not_valid";
} else if ( !checkTimeValid($time) ) {//时间验证
	$status = "time_error";
} else if ( !checkSignValid($gameID, $serverID, $time,'','', $extra_data, $sign) ) {//签名合法性验证
	$status = "sign_error";
}

if ($status != "") {//验证失败
	$result = array('status'=>$status);
	$jsonResult = json_encode($result);
	
} else {
	$status = "data_success";
	$queryparam = array( 'gameid' => $gameID,
	        'serverid' => $serverID,
	        'time' => $time
	);
	
	$userInfoes = getValidUsers($time);
	
	$result = array('status'=>$status,
	    'queryparam' => $queryparam,
		'records' => $userInfoes
	);
	$jsonResult = json_encode($result);
}
$jsonResult = bzcompress($jsonResult, 9);
echo $jsonResult;
?>