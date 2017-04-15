<?php
if(!isset($_SESSION)){
    session_start();
}
	if(isset($_GET['51_sig_time'])&&isset($_GET['51_sig_user'])&&isset($_GET['51_sig_app_key'])&&isset($_GET['51_sig_session_key'])&&isset($_GET['51_sig'])){
		$_SESSION['51_sig_time'] = $_GET['51_sig_time'];
		$_SESSION['51_sig_user'] = $_GET['51_sig_user'];
		$_SESSION['51_sig_app_key'] = $_GET['51_sig_app_key'];
		$_SESSION['51_sig_session_key'] = $_GET['51_sig_session_key'];
		$_SESSION['51_sig'] = $_GET['51_sig'];
		Header("HTTP/1.1 301 Moved Permanently");
		Header("Location:charge51.php");
	}
	require_once("game/51SDK/appinclude.php");	
	require_once("config/db.php");
	if(empty($user51)){
		exit("无法访问");
	}
	$servername=SERVER_NAME;	
	//$servername=iconv("GBK","UTF-8",SERVER_NAME);
		
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>充值</title>
<link href="Master.css" rel="stylesheet" type="text/css" />
<script>
function selectPayAmount(obj)
{
    var value = obj.value;
    value  = value.replace(/(0*)([1-9])([0-9]*)/gi,"$2$3");
    obj.value=value;
    if(value!="" && !isNaN(value))
      document.getElementById("ingotAmount").innerHTML=parseInt(value)*10;
}

</script>
</head>

<body >
<div id="header"><div id="logo"></div></div>
<div id="menu">
    <ul>
        <li class="current"><a href="#">充值中心</a></li>
        <li><a href="http://rxsg.51.com/index.php?te=2" target="_blank">新闻公告</a></li>
        <li><a href="http://rxsg.51.com/index.php?te=1" target="_blank">专区首页</a></li>
        <li><a href="http://rxsg.51.com/index.php?te=4" target="_blank">游戏资料</a></li>
        <li><a href="http://qun.51.com/rexuesanguo/index.php" target="_blank">火热论坛</a></li>
    </ul>
</div>
<div>
	<iframe id="if_1"   frameborder="0" style="padding:0 0 0 89px;height:600px;width:700px; margin:auto; clear:both;display:none">
	</iframe>
</div>
<div id="wrapper">	
	    
    <!-- 填写信息 -->
    <ul class="tags">
    	<li class="at one"><em>请填写充值信息</em></li>
    </ul>
    <div>
    	<div class="rollBox">
            <i class="tl"></i>
            <i class="tr"></i>
            <i class="bl"></i>
            <i class="br"></i>
            <div class="cont">
            	 <br />
                 <form >
                 <table class="tb_form">
                 	<tr><th width="150">用戶名</th><td class="f14"><?php echo $user51 ?></td></tr>   
                 	<tr><th width="150">充入游戏</th><td class="f14">热血三国</td></tr>                  
                    <tr><th>充入服务器</th><td class="f14"><?php echo $servername ?></td></tr>          
                    <tr><th>我要充值</th><td><select name="amount" id="amount" onchange="selectPayAmount(this);"><option value="5">5元</option><option value="10">10元</option><option value="20" selected>20元</option><option value="30" selected>30元</option><option value="50">50元</option><option value="100">100元</option><option value="500">500元</option><option value="1000">1000元</option><option value="2000">2000元</option></select> = <span id="ingotAmount">300</span>个元宝</td></tr>
                    <tr>
                        <td class="fc">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" class="next" value="人民币支付" onclick="document.getElementById('if_1').src='<?php echo PAY51_POST_URL ?>?'+ 'amount='+amount.value;document.getElementById('wrapper').style.display='none';document.getElementById('if_1').style.display='block';" /></td>
                        <td class="fc"><input type="button" class="next" value="51币支付" onclick="document.getElementById('if_1').src='<?php echo PAY51_POST_URL ?>?'+ 'amount='+amount.value+'&paytype=51b';document.getElementById('wrapper').style.display='none';document.getElementById('if_1').style.display='block';" /></td>
                    </tr>
                 </table>
                 </form>
                 <br /><div style="text-align:center;color:red;font-size:14px;">提醒： 如需使用神州行卡充值请选择50元或者100元。51币充值单次最多50元</div><br />
                 <div class="clear"></div>
                 <dl class="comment" style="display:none;">
                 	<dt>银行卡支付注意事项：</dt>                    
                 </dl>
            </div>
        </div>
    </div>
</div>

<div id="footer">
	<p>客服热线: 0571-88067758<i class="fgx">/</i>邮箱: rxsg@uuyx.com<i class="fgx">/</i><a href="http://qun.51.com/rexuesanguo/forum.php?fid=7593" target="_blank">客服说明</a>
	<a href="http://qun.51.com/rexuesanguo/forum.php?fid=7593" target="_blank">客服论坛</a></p>
    <p>版权所有 © 2009 365wy.com</p>
</div>
</body>
</html>
'