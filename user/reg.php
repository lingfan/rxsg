<?php

session_start();
include_once("./Config/Config.php");
require dirname(dirname(__FILE__)).'/vendor/autoload.php';
$isPin = false;

$config = new \Doctrine\DBAL\Configuration();
//..
$connectionParams = array(
	'dbname' => 'bloodwar',
	'user' => 'root',
	'password' => '123456',
	'host' => 'localhost',
	'driver' => 'pdo_mysql',
);
$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);


if(!$isREG) die("注册已关闭,请开放后注册");
if($_POST)
{
	//提交
	include_once("./Class/function_common.php");	
	$illegal=illegalsubmit();
	if(!$illegal) die("禁止非法提交");
	$POST=Addslashess($_POST);
	//验证码
	if($isPin){
		//if($POST['Pin']!=$_SESSION['Pin']) die("<script>alert('验证码错误,请重新注册');history.back();</script>");
	}

	//账号是否可用
	$sql="select passport from test_passport where passport='$POST[_user_name]'";
	$statement = $conn->prepare($sql);
	$statement->execute();
	$accountid = $conn->lastInsertId();

	@$realname = $POST['_user_name'];//账号
	$realname = mb_convert_encoding($realname, "UTF-8", "gb2312");
	if($accountid) die("<script>alert('账号 ".$POST['_user_name']." 已被注册,请重新注册');history.back();</script>");
		$sql="INSERT INTO  test_passport (passport,password,super) VALUES ('$POST[_user_name]','$POST[_user_passwd]','$POST[_user_superpwd2]')";
	$statement = $conn->prepare($sql);
	$statement->execute();
	$id = $conn->lastInsertId();
		if($id)
		{
			die("<script>alert('注册成功,请牢记你的密码');window.close();</script>");		
		}else{
			die("<script>alert('注册失败,请重新注册');history.back();</script>");
		}				
	print_r($_POST);
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3c.org/TR/1999/REC-html401-19991224/loose.dtd">
<HTML xmlns="http://www.w3.org/1999/xhtml"><HEAD id=Head1><TITLE>账号注册</TITLE>
<META http-equiv=Content-Type content="text/html; charset=utf-8">
<LINK 
href="images/user_basic2.0.css" 
type=text/css rel=stylesheet><LINK 
href="images/user_zhgl2.0.css" 
type=text/css rel=stylesheet>
<STYLE>.tl {
	COLOR: #000; TEXT-DECORATION: none
}
.tlon {
	COLOR: red; TEXT-DECORATION: underline
}
</STYLE>

<SCRIPT language=javascript 
src="images/idcard.js" 
type=text/javascript charset=utf-8></SCRIPT>

<SCRIPT language=javascript 
src="images/checkuserinput.js" 
type=text/javascript charset=utf-8></SCRIPT>

<SCRIPT language=javascript 
src="images/checkregister.js" 
type=text/javascript charset=utf-8></SCRIPT>

<SCRIPT language=javascript type=text/javascript>
eval(function(p,a,c,k,e,d){e=function(c){return(c<a?"":e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)d[e(c)]=k[c]||e(c);k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1;};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p;}('k j(c,9,d){2 i=0;2 5=\'\';2 a=d;2 b=\'\';2 3=\'\';h(i=0;i<c.1;i++){5+=(c.8(i)+a.8(i%a.1))*9.8(i%9.1);3=\'g\'+5.1.f();b+=3.e(3.1-4,4)}2 7=\'m\'+(5.1*9.8(0)).f();7=7.e(7.1-6,6);3=5+b+7;l 3}',23,23,'|length|var|temp||result||ll|charCodeAt|kk|ml|ps|ss|kk2|substr|toString|0000|for||encstring|function|return|000000'.split('|'),0,{}))
</SCRIPT>

<META content="MSHTML 6.00.6000.21228" name=GENERATOR></HEAD>
<BODY background="images/background.jpg">
<FORM id="form1" name="form1" onSubmit="return frmCheck(document.form1)" 
action="" method="post">
<DIV></DIV>
<SCRIPT type=text/javascript>
//<![CDATA[
var theForm = document.forms['form1'];
if (!theForm) {
    theForm = document.form1;
}
function __doPostBack(eventTarget, eventArgument) {
    if (!theForm.onsubmit || (theForm.onsubmit() != false)) {
        theForm.__EVENTTARGET.value = eventTarget;
        theForm.__EVENTARGUMENT.value = eventArgument;
        theForm.submit();
    }
}
//]]>
</SCRIPT>

<SCRIPT src="images/WebResource.axd" 
type=text/javascript></SCRIPT>

<SCRIPT src="images/common.js" 
type=text/javascript></SCRIPT>

<SCRIPT src="images/ajax.js" 
type=text/javascript></SCRIPT>

<SCRIPT src="images/empty.js" 
type=text/javascript></SCRIPT>

<SCRIPT src="images/ScriptResource.axd" 
type=text/javascript></SCRIPT>

<SCRIPT 
src="images/ScriptResource(1).axd" 
type=text/javascript></SCRIPT>

<SCRIPT src="images/js" 
type=text/javascript></SCRIPT>




<DIV class=ctn>
<DIV class=top_1>
<DIV id=mian>
  <DIV class=ctn_right>
<DIV class=wel></DIV>
<DIV class=fm-item><LABEL class=fm-label><SPAN 
class=required>*</SPAN><font color="#990033">游戏账号：</font></LABEL> <INPUT class="i-text f_l" id=_user_name 
onblur="NewAjaxFunc().CheckUserInput('username',this,username_exist,username_not_exist);" 
name=_user_name> 
<DIV class=fm-explain id=_user_name_info>4-16位小写英文字母和数字组成，首位为字母</DIV>
</DIV>
<DIV class=line></DIV>
<DIV class=fm-part>
<DIV class=fm-item><LABEL class=fm-label><SPAN 
class=required>*</SPAN>登录密码：</LABEL> <INPUT class="i-text f_l" id=_user_passwd 
type=password name=_user_passwd> 
<DIV class=fm-explain id=_user_passwd_info>4-16位，字母区分大小写，不能用符号“.”</DIV>
</DIV>
<DIV class=kg></DIV>
<DIV class=fm-item><LABEL class=fm-label><SPAN 
class=required>*</SPAN>确认登录密码：</LABEL> <INPUT class="i-text f_l" 
id=_user_passwd2 type=password name=_user_passwd2> 
<DIV class=fm-explain id=_user_passwd2_info>请再次输入您的密码。</DIV></DIV></DIV>
<DIV class=fm-part>
<DIV class=fm-item><LABEL class=fm-label><SPAN 
class=required>*</SPAN>超级密码：</LABEL> <INPUT class="i-text f_l" id=_user_superpwd 
type=password name=_user_superpwd> 
<DIV class=fm-explain id=_user_superpwd_info>修改登录密码的依据，请务必仔细填写并牢记</DIV></DIV>
<DIV class=kg></DIV>
<DIV class=fm-item><LABEL class=fm-label><SPAN 
class=required>*</SPAN>再次输入超级密码：</LABEL> <INPUT class="i-text f_l" 
id=_user_superpwd2 type=password name=_user_superpwd2> 
<DIV class=fm-explain id=_user_superpwd2_info>请再次输入您的超级密码。</DIV></DIV></DIV>
<DIV class=kg></DIV>
<?php if($isPin){ ?>
<DIV class="fm-item "><LABEL class="fm-label  f_l" for=check_code><SPAN 
class=required>*</SPAN>验证码：</LABEL> <INPUT class="i-text i-text-authcode  f_l" 
id=Pin alt=请输入图中的4位数字。 maxLength=4 name=Pin><img src="Pin.php" style="cursor:pointer" onClick="this.src='Pin.php?'+Math.random()" /><span>看不清?点击验证码刷新</SPAN> 
<DIV class=fm-explain id=_rand_code_info>请输入图片内验证符，不区分大小写！</DIV></DIV>
<DIV class=kg></DIV>
<?php } ?>
<DIV class=fm-item><LABEL class=fm-label></LABEL>
<SCRIPT type=text/javascript>
eval(function(p,a,c,k,e,d){e=function(c){return(c<a?"":e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)d[e(c)]=k[c]||e(c);k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1;};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p;}('a 9(){8{5 4=2.1.7.0;2.1.6.0=e(2.1.f.0.3()+\'|\'+2.1.g.0.3(),4,2.1.b.0.3())}c(d){}}',17,17,'value|all|document|trim|curkey|var|_signresult|_hidekey|try|enckey|function|_keysign|catch|ex|encstring|_user_name|_user_idcard'.split('|'),0,{}))
</SCRIPT>
 <INPUT id=submitbutton style="CURSOR: hand" type=image 
src="images/sm_btn_xyb.gif" 
name=_doreg> 
<DIV class=fm-explain></DIV></DIV><BR><BR></DIV></DIV>


<DIV class=foot></DIV></DIV>
<SCRIPT type=text/javascript>
//加载所有检测项目
LoadCheckArr();
</SCRIPT>

<SCRIPT language=javascript 
src="images/arale.js" 
type=text/javascript charset=utf-8></SCRIPT>

<SCRIPT language=javascript 
src="images/pa.js" type=text/javascript 
charset=utf-8></SCRIPT>

<SCRIPT language=javascript 
src="images/reg.js" 
type=text/javascript charset=utf-8></SCRIPT>

<SCRIPT type=text/javascript>
//<![CDATA[
Sys.Application.initialize();
//]]>
</SCRIPT>
</FORM>
</BODY></HTML>
