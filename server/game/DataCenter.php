<?php
require_once '../../server_info.php';
//define('DC_PASSTYPE','joyport');
//define('DC_GAMEID',1);
//define('DC_UCODE','ld_1_100_20001');
define('PATH','/logcenter/_flume/rxsg/');

	function PlayerSend($path,$data) {
		if (!is_dir($path)) {
			mkdir($path,0777);
		}
		$logName=date('Ymd').".log";
		@file_put_contents($path."/$logName",$data."\n",FILE_APPEND);
	}
	
	function PlayerActive($passport) {
		$dataTable='player_active';
		$time=time();
		$data=$passport."\t".$GLOBALS['partnerType']."\t".DC_GAMEID."\t".$GLOBALS['serverId']."\t".$time."\t".$GLOBALS['ucode']."\t".DC_SERVERNO;
		@PlayerSend(PATH.$dataTable,$data);
	}
	
	function PlayerRole($uid,$passport,$name) {
		$dataTable='player_role';
		$time=time();
		$data=$uid."\t".$passport."\t".$GLOBALS['partnerType']."\t".DC_GAMEID."\t".$GLOBALS['serverId']."\t".$time."\t".$name."\t0\t0\t".DC_SERVERNO;
		@PlayerSend(PATH.$dataTable,$data);
	}
	
	function PlayerPay($uid,$passport,$orderId,$money) {
		$dataTable='player_pay';
		$time=time();
		$gameMoney=$money*0.1;
		$data=$uid."\t".$passport."\t".$GLOBALS['partnerType']."\t".DC_GAMEID."\t".$GLOBALS['serverId']."\t".$orderId."\t".$gameMoney."\t$money\t0\t".$GLOBALS['partnerType']."\t".$time."\t".DC_SERVERNO;
		@PlayerSend(PATH.$dataTable,$data);
	}
	
	function PlayerLogin($uid,$passport,$ip) {
		$dataTable='player_login';
		$time=time();
		$data=$uid."\t".$passport."\t".$GLOBALS['partnerType']."\t".DC_GAMEID."\t".$GLOBALS['serverId']."\t".$time."\t".$ip."\t".DC_SERVERNO;
		@PlayerSend(PATH.$dataTable,$data);
	}
	
	function PlayerGameInfo($uid,$passport,$name,$officePose,$nobility) {
		$dataTable='player_gameinfo';
		$time=time();
		$data=$uid."\t".$passport."\t".$GLOBALS['partnerType']."\t".DC_GAMEID."\t".$GLOBALS['serverId']."\t".$name."\t".$officePose."\t".$nobility."\t0\t0\t".$time."\t".DC_SERVERNO;
		@PlayerSend(PATH.$dataTable,$data);
	}
	
	function PlayerCost($uid,$passport,$name,$money) {
		$dataTable='player_cost';
		$time=time();
		$data=$uid."\t".$passport."\t".$GLOBALS['partnerType']."\t".DC_GAMEID."\t".$GLOBALS['serverId']."\t".$name."\t0\t".$money."\t".$time."\t".$GLOBALS['partnerType']."\t0\t".DC_SERVERNO;
		@PlayerSend(PATH.$dataTable,$data);
	}
	
	function ServerPlayer($count) {
		$dataTable='server_player';
		$time=time();
		$data=DC_GAMEID."\t".$GLOBALS['serverId']."\t".$time."\t".$count."\t".DC_SERVERNO;
		@PlayerSend(PATH.$dataTable,$data);
	}
?>