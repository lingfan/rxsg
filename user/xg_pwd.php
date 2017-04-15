<?php
session_start();
include("./Config/Config.php");
if(!$isUP) die("很抱歉,修改密码功能已关闭.");
if($_POST){
	//提交
	include_once("./Class/function_common.php");	
	$illegal=illegalsubmit();
	if(!$illegal) die("禁止非法提交");
	$POST=Addslashess($_POST);
	//验证码
	if($isPin){
		if($POST['Pin']!=$_SESSION['Pin']) die("<script>alert('验证码错误,请重新输入');history.back();</script>");	 
	}
	include_once("./Class/mysql_new_class.php");	
	$connobj=new mysql_class($SQLhost,$SQLuser,$SQLPWD,$DATABASE);
	//账号是否存在
	$sql="select passport,password from sys_user where passport='$POST[_user_name]'";
	$arr=$connobj->queryrow($sql);	
	if(!$arr)
	{
		die("<script>alert('账号不存在,修改密码失败');history.back();</script>");
	}
	if($POST['oldpasswd']!=$arr['password']){
		die("<script>alert('旧密码错误,修改密码失败');history.back();</script>");
	}else{	
		$sql="update sys_user set password='$POST[newpasswd]' where passport='$arr[passport]'";
		$connobj->querysql($sql);
		die("<script>alert('修改密码成功,请牢记你的新密码');window.close();</script>");
	}	
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3c.org/TR/1999/REC-html401-19991224/loose.dtd">
<HTML xmlns="http://www.w3.org/1999/xhtml"><HEAD id=Head1><TITLE>修改密码</TITLE>
<META http-equiv=Content-Type content="text/html; charset=gb2312">
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
<META content="MSHTML 6.00.6000.21228" name=GENERATOR></HEAD>
<BODY>
<FORM id="form1" name="form1" onSubmit="return frmCheck()" action="" method="post">
<DIV class=ctn>
<DIV class=top_1>
<DIV id=mian>
  <DIV class=ctn_right>
<DIV class=wel></DIV>
<DIV class=fm-item><LABEL class=fm-label><SPAN 
class=required>*</SPAN>游戏账号：</LABEL> <INPUT class="i-text f_l" id="_user_name" name="_user_name"> 
<DIV class=fm-explain id=_user_name_info>请填写注册时所填写的游戏账号.</DIV>
</DIV>
<DIV class=fm-item><LABEL class=fm-label><SPAN 
class=required>*</SPAN>旧 密 码：</LABEL> <INPUT class="i-text f_l" id="oldpasswd" 
type="password" name="oldpasswd"> 
<DIV class=fm-explain id=_user_passwd_info>请填写正在使用的密码.</DIV></DIV>
<DIV class=fm-item><LABEL class=fm-label><SPAN 
class=required>*</SPAN>新 密 码：</LABEL> <INPUT class="i-text f_l" id="newpasswd" 
type="password" name="newpasswd"> 
<DIV class=fm-explain id=_user_passwd_info>4-16位，字母区分大小写，不能用符号“.”</DIV></DIV>
<?php if($isPin){ ?>
<DIV class="fm-item "><LABEL class="fm-label  f_l" for=check_code><SPAN 
class=required>*</SPAN>验证码：</LABEL> <INPUT class="i-text i-text-authcode  f_l" 
id=Pin alt=请输入图中的4位数字。 maxLength=4 name=Pin><img src="Pin.php" style="cursor:pointer" onClick="this.src='Pin.php?'+Math.random()" /><span>看不清?点击验证码刷新</SPAN> 
<DIV class=fm-explain id=_rand_code_info>请输入图片内验证符，不区分大小写！</DIV></DIV>
<DIV class=kg></DIV>
<?php } ?>
<DIV class=fm-item>
 <INPUT id="submitbutton" style="CURSOR: hand" type="image" 
src="images/sm_btn_xyb2.gif" 
name="_doreg"> 
</DIV><BR><BR></DIV></DIV>


</DIV>

</FORM>
</BODY></HTML>
<script language="JavaScript" type="text/javascript"> 
function $(obj)
{
	if(typeof obj == 'string') return document.getElementById(obj);
	else if(typeof obj == 'object') return obj;
	else return false;
}
function frmCheck()
{
	var un=$("_user_name").value;
	var re=/^[0-9a-zA-Z]{4,16}$/; //只输入数字和字母的正则	   
	if(un.search(re)==-1)
	{
		alert("账号请输入数字和字母，字符介于4到十六个");
		$("_user_name").focus();		
		return false;
	}
	var uold=$("oldpasswd").value;
	if(uold.length<4 || uold.length>16)
	{
		  alert("旧密码必须在4-16位之间");	
		  $("oldpasswd").focus();
		  return false; 		  
	}
	if(uold.indexOf(".")>=0)
	{
		  alert("旧密码不能含 . ");	
		  $("oldpasswd").focus();	
		  return false; 		  	
	}
	var unew=$("newpasswd").value;
	if(unew.length<4 || unew.length>16)
	{
		  alert("新密码必须在4-16位之间");	
		  $("newpasswd").focus();
		  return false; 		  
	}
	if(unew.indexOf(".")>=0)
	{
		  alert("新密码不能含 . ");	
		  $("newpasswd").focus();	
		  return false; 		  	
	}
	//$("form1").submit();						 
	//return true;
}
</script>