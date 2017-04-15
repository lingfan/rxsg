<?php
require_once(ROOT_PATH."/lib/curl.php");
require_once("./utils.php");
define('VIP_KEY',"HB2K39PORMX9SLGXMXQ");
define('VIP_URL',"http://127.0.0.1/activity5/interface.aspx?");
/*
平台返回值：
params_not_invalid  表示参数不正确
sign_error   验证码错误
state=0&sign=MD5(0+key)时不需要弹窗
state=1&sign=MD5(1+key)时需要弹窗
 */
function checkVip($passport,$passtype)
{
	// /Interface.aspx?userid=tata004&action=allinformationvip&time=&sign=MD5(pid+userid+time+key)&pid=
	$action = "allinformationvip";
	$time = sql_fetch_one_cell("select unix_timestamp()");
	$sign = MD5($passtype.$passport.$time.VIP_KEY);
	$vipurl = VIP_URL."userid=".urlencode($passport)."&action=".$action."&time=".$time."&sign=".$sign."&pid=".$passtype;
	try{
		$curl=new cURL();
		$info=$curl->get($vipurl);
	}catch (Exception $e) {
		error_log("VIP验证异常");
	}
	if(preg_match("/state=(\d+)&sign=(.*)/", $info, $matches) ){
		if(strcasecmp($matches[2],MD5("1".VIP_KEY))==0&&($matches[1]==0||$matches[1]==1)){
			return $matches[1];
		}else{
			return 0;//出错就不弹窗			
		}
	}
}
/*
平台返回值:
params_not_invalid  表示参数不正确
sign_error   验证码错误
not_vip 该用户不是vip用户
 */
function getVipURL($passport,$passtype)
{
	// /Interface.aspx?userid=tata004&action=getlink&time=&sign=MD5(pid+userid+time+key)&pid=
	$action = "getlink";
	$time = sql_fetch_one_cell("select unix_timestamp()");
	$sign = MD5($passtype.$passport.$time.VIP_KEY);
	$vipurl = VIP_URL."userid=".urlencode($passport)."&action=".$action."&time=".$time."&sign=".$sign."&pid=".$passtype;
	try{
		$curl=new cURL();
		$info=$curl->get($vipurl);
 	}catch (Exception $e) {
 		error_log("VIP获得URL异常");
 	}
 	return $info;
}
/*
平台返回值：
params_not_invalid  表示参数不正确
sign_error   验证码错误
not_vip 该用户不是vip用户
成功修改数据库返回state=1&sign=MD5(1+key)，
失败返回state=-1&sign=MD5(-1+key)
 */
function sendCloseMsg($uid,$param)
{
	// /Interface.aspx?userid=tata004&action=refuse&state=1&time=&sign=MD5(pid+userid+time+key)&pid=
	$passport=array_shift($param);
	$passtype=array_shift($param);
	$state=array_shift($param);
	$action = "refuse";
	$time = sql_fetch_one_cell("select unix_timestamp()");
	$sign = MD5($passtype.$passport.$time.VIP_KEY);
 	$vipurl = VIP_URL."userid=".urlencode($passport)."&action=".$action."&state=".$state."&time=".$time."&sign=".$sign."&pid=".$passtype;
	try{
		$curl=new cURL();
		$info=$curl->get($vipurl);
	}catch (Exception $e) {
		error_log("VIP关闭信息异常");
	}
}
/*
返回值 
state=0&sign=MD5(0+key)  
state=1   需要mail  
 */
function sendVipMail($passport,$passtype)
{
	//Interface.aspx?userid=tata004&action=isneedmail&time=&sign=MD5(pid+userid+time+key)&pid=
	$action = "isneedmail";
	$time = sql_fetch_one_cell("select unix_timestamp()");
	$sign = MD5($passtype.$passport.$time.VIP_KEY);
 	$vipurl = VIP_URL."userid=".urlencode($passport)."&action=".$action."&time=".$time."&sign=".$sign."&pid=".$passtype;
	try{
		$curl=new cURL();
		$info=$curl->get($vipurl);
	}catch (Exception $e) {
		error_log("VIP发信异常");
	}
	if(preg_match("/state=(\d+)&sign=(.*)/", $info, $matches) ){
		if(strcasecmp($matches[2],MD5("1".VIP_KEY))==0&&$matches[1]==1){
			$vipurl =  getVipURL($passport,$passtype);
			$user = sql_fetch_one("select uid,name from sys_user where passport ='$passport' limit 1");
			if($user){
				$mailtitle = 'VIP用户信息调查 领取神秘千元宝箱大礼';
				$mailcontent = "亲爱的%s：\n　　恭喜您成为VIP用户！\n　　为了更好的为您制定量身服务方案，尊享客服一对一的在线解答，更快更便捷的解决问题，欢迎您参加我们的VIP用户信息完善活动，并专享VIP用户价值最高千元的贵宾礼品。以下是奖品内容：\n初级VIP\n　　2级内政镶嵌宝珠1\n　　2级勇武镶嵌宝珠1\n　　2级智谋镶嵌宝珠1\n　　2级统率镶嵌宝珠1\n高级VIP\n　　4级内政镶嵌宝珠1\n　　4级勇武镶嵌宝珠1\n　　4级智谋镶嵌宝珠1\n　　4级统率镶嵌宝珠1\n链接地址：%s\n\nvip热线：0571-89715599\nvip邮箱：vip@ledu.com\n客服站：http://kf.ledu.com/";	
				$mailcontent = sprintf($mailcontent,$user['name'],$vipurl);
				sendSysMail ($user['uid'], $mailtitle, $mailcontent );
			}
		}
	}
}

?>
