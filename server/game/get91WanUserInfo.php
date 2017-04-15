<?php
require_once ("common.php");
require_once ("utils.php");
$passport = $_GET ['passport'];
//$passport = iconv("gb2312","utf-8",$_GET ['passport']);
$time = $_GET ['time'];
$sign = $_GET ['sign'];
$name = urldecode($_GET ['name']);
//$name = iconv("gb2312","utf-8",urldecode($_GET ['name']));
$type = $_GET ['type'];
$key = '[6YbpY:j*Ax+uk-(HaIMq+IddfTWejJH{2&';
try {
	if($type == 1){
	if(empty($passport)){
		throw new Exception ( 'empty_passport' );
	}
	if (md5 ( $passport . $key . $time ) != $sign) {
		throw new Exception ( 'sign_error' );
	}
	$sql = "select a.uid,a.passport,a.name,a.regtime,a.nobility nobilityid,b.name nobility from sys_user a ,cfg_nobility b where a.passport='$passport' and a.state !=5 and a.nobility = b.id";
	}elseif($type==2){
	if(empty($name)){
			throw new Exception ( 'empty_passport' );
	}
	if (md5 ( $name . $key . $time ) != $sign) {
		throw new Exception ( 'sign_error' );
	}
	$sql = "select a.uid,a.passport,a.name,a.regtime,a.nobility nobilityid,b.name nobility from sys_user a ,cfg_nobility b where a.name='$name' and a.state!=5 and a.nobility = b.id";
	}
	$rows = sql_fetch_one ( $sql );
	if (empty ( $rows )) {
		throw new Exception ( 'user_error' );
	}
	$uid = array_shift ( $rows );
	$sql = "select max(time) from log_login where uid='$uid'";
	$time = sql_fetch_one_cell ( $sql );
	$rows ['lasttime'] = $time;
} catch ( exception $e ) {
	echo $e->getMessage ();
	exit;
}
echo serialize($rows);
?>