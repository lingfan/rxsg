<?php 

include("../../index_top.php");

//echo ini_get('include_path');
require_once("dbinc.php");
require_once("xiaonei/guid.class.php"); 
include_once("xiaonei/xiaonei-util.php");
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

$uid = getCurrentUser(SERVER_ID);


if($uid==""){
 	echo "<script> window.location='http://www.renren.com';</script>";
	exit;
}


$curBalance = getXiaoneiBalance($uid);


function payFail()
{
	echo "<script> window.location='xiaonei/rexue-pay-failed.html';</script>";
	exit;
}

function paySucc($exchangeCount)
{
	echo "<script> window.location='xiaonei/rexue-pay-success.php?exchangeCount=$exchangeCount';</script>";
	exit;
}

if (!empty($_POST['exchangeCount'])){
	 $exchangeCount = $_POST['exchangeCount'];
	 if(!is_numeric($exchangeCount)  || $exchangeCount>1000000 || $exchangeCount<=0 ){
	 	payFail();
	 }
	 $serverName = $_POST['serverName']; 
	 if(true){


	 	$name = sql_fetch_one_cell("select name from sys_user where passport='".$uid."'");
	 	if (empty($name)) {
	 		payFail();
	 	}

		$guid = new Guid();
		$orderid = $guid->toString();  
		$name="热血三国";
	 	if(consumeXiaoneiDou($uid,$serverName,$name,$exchangeCount,$orderid)) {
	 		$money = $exchangeCount*10;
	 		$result=sql_query("update sys_user set money=money+".$money." where passport='".$uid."'");

		 	if($result){
		 		$now = sql_fetch_one_cell("select unix_timestamp()");
				$today = $now - (($now + 8 * 3600)%86400);
				$type = 0;
				sql_query("insert into log_money (uid,count,time,type) values ('$uid','$money',unix_timestamp(),$type)");
				sql_query("insert into pay_log (orderid,type,payname,passport,passtype,money,code,time) values ('$orderid','$type','renren','$uid','renren','$money','rexue',unix_timestamp())");
				sql_query("insert into pay_day_money (day,money) values ('$today','$money') on duplicate key update `money`=`money`+'$money'");
				$user = sql_fetch_one("select * from sys_user where passport='$uid' and passtype='renren'");      
				include "paygift.php";
		 		 

				paySucc($exchangeCount);
				exit;
	
		 	}else {
				payFail();
			}
	 } else {
	 	payFail();
	 }
	}
}
 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />

<link href="xiaonei/css/base.css" rel="stylesheet" type="text/css" media="all"/>
<title>热血三国 - 游戏大厅- 校内网</title>
<script> 
function onCountChange(value) {
	var count = parseInt(value);
	
	document.getElementById("yuanbaoCount").innerHTML = ""+parseInt(value)*10;
}

function submitCheck() {
	count = parseInt(document.getElementById("exchangeCount").value);
	if(isNaN(count)){
		alert("请正确输入要兑换的元宝数量");
		return false;
	}  else if(document.getElementById("serverName").value==""){
		alert("请选择您要兑换元宝的服务器");
		return false;
	}
	return true;
}
</script>
</head>
<body id="rexuePage">
<div id="wrapper">
<div id="container">
		<!-- navigation-wrapper -->
<div class="navigation-wrapper">
		<div class="navigation clearfix">
				<div id="logo">
						 <h1><a title="校内" href="http://www.renren.com/">校内</a></h1>
				</div>
				<div class="nav-body clearfix">
						<div class="nav-main">
							<div class="menu">
									<div class="menu-title"><a href="http://www.renren.com/">首页</a></div>
							</div>
								<div style="margin: 0pt;" class="menu">
										<div class="menu-title"><a href="http://game.renren.com/">游戏</a></div>
								</div>

						</div>

						<div class="nav-other">
								<div class="menu">
										<div class="charge menu-title"><a href="http://pay.renren.com" target="_blank">充值</a></div>
								</div>
								<div class="menu last">
										<div class="menu-title"><a href="http://www.renren.com/Logout.do">退出</a></div>
								</div>

						 </div>
				</div>
		</div>
</div>
<!--  /navigation-wrapper -->
<div class="main-content">
	<div class="main-inner">
		  <div class="arrow"></div>
		 <!--  box-info -->
			  <div class="box-info clearfix">
					<img src="xiaonei/img/rexue.gif"  class="logo" />
					<div class="text">
						<h3 ><span>校内豆兑换元宝：</span><em>1</em> 个校内豆 = <em>10</em>个元宝</h3>
						<div class="cite">
							<p ><b>校内豆：</b>是校内网的通用虚拟货币，您可以用校内豆兑换成校内网各种游戏的游戏币。</p>
							<p ><b>元　宝：</b>是《热血三国》游戏货币，可在游戏中购买道具、礼盒、装备等所有虚拟物品。</p>
						</div>
					</div>
			  </div>
			<!--/   box-info -->
			<form name="payform" action="rexue-pay.php" method="post" >   
			<div class="pay-table">
					<table>
						<tr>
							<th>您现在有校内豆：</th>
							<td><em><?php echo $curBalance ?></em></td>
						</tr>
							<tr>
							<th>请填写您需要兑换的宝石数量：</th>
							<td><input type="text" id="exchangeCount" name="exchangeCount" class="input-text" value="10"  onkeyup="onCountChange(this.value)">个校内豆 = <em><div id="yuanbaoCount" style="display:inline">100</div></em>个元宝</td>
						</tr>
							<tr>
							<th>您正在充值的服务器：</th>
							<td>  
							<?=TITLE?> <input type="hidden" name="serverName" value="<?=SERVER_ID?>">
							<!--
							<select id="serverName" name="serverName">
								 <option value="server1">龙吞天下</option>
						</select> --> 
						</td>
						</tr>
						<tr>
							<td></td>
							<td class="btn"> <input type="image" src="xiaonei/img/btn-submit.gif" onClick="return submitCheck();">   
							<a href="http://pay.renren.com" target="_blank"><img src="xiaonei/img/btn-pay.gif" alt="充值校内豆" title="充值校内豆" /></a></td>
						</tr>
					</table>
			</div>
			</form>
	</div>
	
</div>
</div>
</div>
		<div id="footer">
			<div class="copyright">
				<span class="float-right">
					<a href="http://renren.com/getsysupdateinfo.do">校内日志</a><span class="pipe">|</span><a href="http://renren.com/info/About.do">关于</a><span class="pipe">|</span><a href="http://app.renren.com/developers/portal.do">开放平台</a><span class="pipe">|</span><a href="http://renren.com/info/jobs.jsp">招聘</a><span class="pipe">|</span><a href="http://supprot.renren.com/GetGuestbookList.do">客服</a><span class="pipe">|</span><a href="http://renren.com/info/Help.do">帮助</a><span class="pipe">|</span><a href="http://renren.com/info/PrivacyClaim.do">隐私声明</a>
				</span>
				<span>千橡公司 <span title="revision$revxxx$; ${applicationScope.hostName}">©</span> 2008</span>
			</div>
		</div>

</body>
</html>
