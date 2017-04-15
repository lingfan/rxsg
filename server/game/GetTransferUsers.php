<?php
/**
 * 获取转合服用户信息
 * 
 * @author dengyike
 * @since 2010-10-08
 * @example 调用格式：http://PAY_DATA_URL?gameid={0}&serverid={1}&start_time={2}&end_time={3}&extra={4}&sign={5}
 */
require_once (dirname(__FILE__)."/common_interface.php");

function getLimitTime(){
	$open_time=sql_fetch_one_cell("select value from mem_state where state=6");
	$limit_time=$open_time;
	return $limit_time;
}
function getTransferUserCount(){
	$limit_time=getLimitTime();
	return sql_fetch_one_cell("select count(1) from sys_user where uid>1000 and regtime<'$limit_time'");
}
function getTransferUsers($offset,$limit)
{//获取截止到查询时间点的有效用户信息,
	$limit_time=getLimitTime();
	//初级有效用户，声望>=1000
	$sql="SELECT uid,passport,passtype,regtime,name AS rolename, regtime AS createtime FROM sys_user WHERE uid>1000 AND regtime<'$limit_time' limit $offset,$limit";
	$result = sql_fetch_rows($sql);
	return $result;
}

////获取传递过来的参数
$gameID = $_GET['gameid'];
$serverID = $_GET['serverid'];
$offset=$_GET['offset'];
$limit=$_GET['limit'];
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
} else if ( !checkSignValid2($_GET) ) {//签名合法性验证
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
	$count=getTransferUserCount();
	if($offset>=0&&$limit>0){
		$userInfoes = getTransferUsers($offset,$limit);
	}else{
		$userInfoes=array();
	}
	
	$result = array('status'=>$status,
	    'queryparam' => $queryparam,
	    'count'=>$count,
		'records' => $userInfoes,
	);
	$jsonResult = json_encode($result);
}
$jsonResult = bzcompress($jsonResult, 9);
echo $jsonResult;