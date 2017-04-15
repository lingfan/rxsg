<?php
function getXiaoneiBalance($uid)
{
	$key = "299e20aa9064c1107077b30fab7eb06c";
 	$code = "rexue";
	$balanceBaseURL = "http://pay.renren.com/service/account.do?";
	$chksum = md5($code.$uid.$key);
	$url = $balanceBaseURL."uid=".$uid."&code=".$code."&key=".$chksum;
    return file_get_contents($url);
}

function getCurrentUser($trace)
{
	
	$key = "299e20aa9064c1107077b30fab7eb06c";
 	$code = "rexue";
	$loginBaseURL = "http://gamejiekou.renren.com/login.do?";
	$trace="x".SERVER_ID;
	$s = $_COOKIE['t'];
	$chksum = md5($code.$trace.$s.$key);
	$url = $loginBaseURL."code=".$code."&trace=".$trace."&s=".$s."&key=".$chksum;
	//$content = file_get_contents($url);
	$ch=curl_init();
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	$content=curl_exec($ch);
	$arr = explode(":",$content);
	file_put_contents("/home/login.log",$url."|||".$content."\n",FILE_APPEND);
if (count($arr)==4) {
		return $arr[1];
	} else {
		return "";
	}
 
}
 
function consumeXiaoneiDou($uid,$trace,$name,$money,$billId) 
{
	$key = "299e20aa9064c1107077b30fab7eb06c";
 	$code = "rexue";
	$consumeBaseURL = "http://pay.renren.com/service/expend.do?";
	$trace="x".SERVER_ID;
	
	$name = urlencode($name);
	 
	$chksum = md5($code.$uid.$trace.$money.$billId.$key);
	$url = $consumeBaseURL."uid=".$uid."&code=".$code."&name=".$name."&trace=".$trace."&money=".$money."&billId=".$billId."&key=".$chksum;
	$content = file_get_contents($url);
	return $content==="0";
}

function getUserInfo($uid)
{
	$key = "299e20aa9064c1107077b30fab7eb06c";
 	$code = "rexue";
 	$getUserInfoURL = "http://gamejiekou.renren.com/getUserInfoForGameHall.do?";//uid=XXX&code=XXX&key=XXX
 	//$getUserInfoURL = "http://gamejiekou.renren.com/getUserInfo.do?";
 	
 	$chksum=md5($code.$uid.$key);
 	$url = $getUserInfoURL."uid=".$uid."&code=".$code."&key=".$chksum;
 	$content = file_get_contents($url);
 	if($content==-1) return "";
 	$xml = new SimpleXMLElement($content);
 	$ret=array('name'=>(string)$xml->name,'sex'=>(string)$xml->gender,'url'=>(string)$xml->tinyHeadUrl);
 	return $ret;
}

?>
