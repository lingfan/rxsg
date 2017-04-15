<?php
require_once("common.php");
require_once("utils.php");

$payinfo = sql_fetch_one("select * from pay_key where name='51com' and ip='$GLOBALS[rawip]'");
if (empty($payinfo))
{
    exit("not_valid_ip");
}

//51用户支付的回调
$appsecret = '88faa8c8e17f1c7aae7f98abf75c86b0';
$notice = decode51($_POST, $appsecret);
/*$notice = array(
	'order_id' => 1, // 您系统中的订单号，请保证唯一。只能由数字和字母组成，最长20位。
	'order_price' => 10,  // 单位: 分，范围: 1 - 50000 （1分至500元）
	'sn_platform' => "liushuihao34",  // 订单描述，最多 200 字节。
	'sn_app' => 1,
	'time_pay'=>124235
);*/
if (!$notice) {
	exit('1');
}	
//支付金额	
$money=	$notice['order_price'];
//订单号
$orderid=$notice['sn_app'];
//51平台流水号
$sn_platform=$notice['sn_platform'];
if ($money <= 0){
    exit("money_not_valid");
}
/*$orderidstr=substr($orderidstrall,2);
$orderid=intval($orderidstr);*/

$orderidstr=substr($orderid,2);
$orderid=intval($orderidstr);
	
$order=sql_fetch_one("select * from log_51_charge where id='$orderid'");

//订单不存在
if(empty($order)){
	//@file_put_contents("tt3",$orderid);
    exit("orderid_not_exist");
}
//订单已经处理过
if ($order["state"]==1){
	//@file_put_contents("tt4",$orderid);
    exit("orderid_has_pay");
}
$now = sql_fetch_one_cell("select unix_timestamp()");
$today = $now - (($now + 8 * 3600)%86400);
//目前只有一种type
$type=0;
$money=$money/10;
//给用户加元宝
$result=sql_query("update sys_user set money=money+'$money' where uid='$order[uid]'");
if($result){
	sql_query("insert into log_money (uid,count,time,type) values ('$order[uid]','$money',unix_timestamp(),$type)");
	sql_query("insert into pay_log (orderid,type,payname,passport,passtype,money,code,time) values ('$orderid','$type','51com','$order[passport]','51com','$money','$sn_platform',unix_timestamp())");
	sql_query("insert into pay_day_money (day,money) values ('$today','$money') on duplicate key update `money`=`money`+'$money'");
	sql_query("update log_51_charge set state=1,paytime='$notice[time_pay]',sn_platform='$sn_platform' where id='$orderid' "); 
	$user = sql_fetch_one("select * from sys_user where passport='$order[passport]' and passtype='51com'");      
	@include("./paygift.php");
	$paylog =sql_fetch_one("select * from pay_log where orderid='$orderid'");
	if (false==empty($paylog))
		exit('0');
}
exit("db update error");

    	    	
// 解析请求参数，失败返回 false，成功返回数组
function decode51($params, $secret) {
	$prefix = '51_sig_'; $prefix_len = strlen($prefix);
	$ret = array();
	foreach ($params as $key => $val) {
		if (strncmp($key, $prefix, $prefix_len) === 0) {
			$ret[substr($key, $prefix_len)] = $val;
		}
	}
	if (empty($ret)) return false;
	$str = '';
	ksort($ret);
	foreach ($ret as $k=>$v) $str .= "$k=$v";
	$str .= $secret;

	if ($params['51_sig'] != md5($str)) {
		return false;
	} else {
		return $ret;
	}
}
?>