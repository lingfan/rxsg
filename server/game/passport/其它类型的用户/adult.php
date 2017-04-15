<?php
//$key="1A954A5997B0A337AD8D1F58C55D8EEE";
$key='#0T65w[!uQpmYx!D>)[VvpotN)EuwLgg+oiw{%)s+L)';
$gameid=1;
$url="http://www.rxsgfcm.com/authinfo?gameid=$gameid&";
//$url = "http://interface1.uuyx.com/sgAuthentication.aspx?";
$alarmErrMsg = "";
global $GLOBAL_ADULT_RET;
function check_adult_result($url,$time_out = "10",$noreturn=false)
{
	global $GLOBAL_ADULT_RET;
	global $alarmErrMsg;
	$urlarr = parse_url($url);
	$errno = "";
	$errstr = "";
	$transports = "";
	if($urlarr["scheme"] == "https") {
		$transports = "ssl://";
		$urlarr["port"] = "443";
	} else {
		$transports = "tcp://";
		//$urlarr["port"] = "80";
		$urlarr["port"] = ($urlarr["port"] == "" ? 80 : $urlarr["port"]);
	}
	$fp=@fsockopen($transports . $urlarr['host'],$urlarr['port'],$errno,$errstr,$time_out);
	if(!$fp) {
		$alarmErrMsg .= "$errno - $errstr";
	} else {
		$out = "GET ".$urlarr["path"].'?'. $urlarr["query"] . " HTTP/1.1\r\n";
		$out .= "Accept: */*\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "UA-CPU: x86\r\n";
		$out .= "User-Agent: wangye173_rxsg_interface\r\n";
		$out .= "Host: ".$urlarr["host"]."\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "\r\n";
		
		fwrite($fp, $out);
		
		if ($noreturn) return;
		
		while(!feof($fp)) {
			$info[]=@fgets($fp, 4096);
		}

		fclose($fp);
		//$infostr=print_r($info,true);
		//file_put_contents("/bloodwar/server/game/passport/adult.log",$url."\n".$infostr);
		foreach($info as $item)
		{
			if(preg_match("/id_card=(\w*)\&verify_state=(\d)\&sign=(.*)/", $item, $matches) )
			{
				$GLOBAL_ADULT_RET[0] = $matches[1];
				$GLOBAL_ADULT_RET[1] = $matches[2];
				if ($GLOBAL_ADULT_RET[1]==5) {
					$GLOBAL_ADULT_RET[1]=1;//审核中 不需要防沉迷
				}
				//file_put_contents("/bloodwar/server/game/passport/adult2.log",$item);
				return true;
			}elseif(preg_match("/Info_illegal/",$item) ||preg_match("/user_not_exist/",$item))
			{
				$GLOBAL_ADULT_RET[0] = 0;
				$GLOBAL_ADULT_RET[1] = 0;
				return true;
			}
		}
		$alarmErrMsg .= implode($info,"\t");
//		$GLOBAL_ADULT_RET[0] = "";
//		$GLOBAL_ADULT_RET[1] = "";
		//file_put_contents("/bloodwar/server/game/passport/adult3.log",$GLOBAL_ADULT_RET[1]);
		return false;
	}
}

$nowtime=time();
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) 
{	
	$tmpip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
else
{
	$tmpip = $_SERVER['REMOTE_ADDR'];
}
$pid = $passtype;
$url =$url. "pid=$pid"."&user=".urlencode($passport)."&time=$nowtime&sign=".md5($gameid.$pid.$passport.$nowtime.$key);
try{
check_adult_result($url);
//thorw new exception($url);
}catch (Exception $e) {
	$alarmErrMsg .= $e->getMessage();
}
if (count($GLOBAL_ADULT_RET)<>2) {
	$GLOBAL_ADULT_RET[0] = "0";
	$GLOBAL_ADULT_RET[1] = "1";
	error_log("防沉迷异常： $url msg:".$alarmErrMsg);
}
