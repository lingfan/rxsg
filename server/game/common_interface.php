<?php

require_once (dirname(__FILE__)."/utils.php");
require_once (dirname(__FILE__)."/common.php");

$ClientIP="";
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) 	
	$ClientIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
if (isset($_SERVER['REMOTE_ADDR']))
	$ClientIP = $_SERVER['REMOTE_ADDR'];
		
//接口中使用到得一些常量
class InterfaceConstants
{
	public static $VALID_CLIENT_IPS = array('127.0.0.1','61.147.67.87','61.147.67.88','61.147.67.100','115.238.43.146');
	public static $MD5_KEY = "#0T65w[!uQpmYx!D>)[VvpotN)EuwLgg+oiw{%)s+L)"; 
	public static $VALID_TIME_LIMIT = 10800;//3小时内合法
	public static $INTERFACE_serverID = 1;//数据接口使用的serverid
	public static $INTERFACE_gameID = "XRXSG_4_ZH_CN_LEDU_1";//数据接口使用的gameID
	public static $MONEY_TYPE = 1;//充值的货币类型：人民币--1，美元--2，欧元--3，英镑--4，日元--5，韩元--6，新台币--7	，港元--8，澳元--9，马币--10，越南盾--11，印度卢比	--12，加元--13，	泰铢	--14
}

	
function checkParamValid($gameID, $serverID, $time, $start_time, $end_time, $sign) 
{//检查必填参数是否有为空的情况
	if ( !isset($gameID) || !isset($serverID) || !isset($time) || !isset($start_time) || !isset($end_time) || !isset($sign) ) {
		return false;
	} else {
		return true;
	}
}
function checkServerInfo($gameID, $serverID) 
{//检查服务器信息是否合法
	//检查gameID是否合法,格式为：游戏代号_游戏ID_语言代号_合作商代号_合作商ID，如RXSG_1_CHS_JOYPORT_1
	if ($gameID != InterfaceConstants::$INTERFACE_gameID || $serverID != InterfaceConstants::$INTERFACE_serverID) {
		return false;
	} else {
		return true;
	}
}
function checkTimeValid($time) 
{//检测是否于当前实际时间在允许的误差范围内
	$curTime = time();
	$delta = $curTime - $time;
	if ($delta <= InterfaceConstants::$VALID_TIME_LIMIT) {
		return true;
	} else {
		return false;
	}
	
}
function checkClientIP($ip)
{//检查客户端ip是否合法
	global $ClientIP;
	if (in_array($ClientIP, InterfaceConstants::$VALID_CLIENT_IPS)) {
		return true;
	} else {
		return false;
	}
}

function checkSignValid($gameID, $serverID, $time, $start_time, $end_time, $extra_data, $sign)
{//检查签名合法性
	$param = "";
	if (isset($extra_data) && !empty($extra_data)) {
		$param = strtolower(md5($gameID . $serverID . $time . $start_time . $end_time . $extra_data . InterfaceConstants::$MD5_KEY));
	} else {
		$param = strtolower(md5($gameID . $serverID . $time . $start_time . $end_time . InterfaceConstants::$MD5_KEY));
	}
	$result = strtolower(md5($param . InterfaceConstants::$MD5_KEY));
	if ( $result === strtolower($sign) ) {
		return true;
	} else {
		return false;
	}
}
?>