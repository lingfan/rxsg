<?php
session_start();
include_once("./Config/Config.php");
if(!$isFP) die("很抱歉,找回密码功能已关闭.");
if($_POST){
	//提交
	include_once("./Class/function_common.php");	
	$illegal=illegalsubmit();
	if(!$illegal) die("禁止非法提交");
	$POST=Addslashess($_POST);
	//验证码
	if($isPin){
		if($POST['Pin']!=$_SESSION['Pin'])
		{
			die("<script>alert('验证码错误,请重新输入');history.back();</script>");	
		} 
	}
	include_once("./Class/mysql_new_class.php");	
	$connobj=new mysql_class($SQLhost,$SQLuser,$SQLPWD,$DATABASE);
	//账号是否存在
	$sql="select passport,super from sys_user where passport='$POST[_user_name]'";
	$arr=$connobj->queryrow($sql);
	if(!$arr)
	{
		die("<script>alert('账号不存在,找回密码失败');history.back();</script>");;	
	}elseif($POST['_user_superpwd']!=$arr['super']){
		die("<script>alert('超级密码不正确,找回密码失败');history.back();</script>");	
	}
	$newpwd=$POST['_user_newpasswd'];
	$sql="update sys_user set password='$newpwd' where passport='$arr[passport]'";
	$connobj->querysql($sql);
	die("<script>alert('找回密码成功,请牢记你的新密码');window.close();</script>");		
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3c.org/TR/1999/REC-html401-19991224/loose.dtd">
<HTML xmlns="http://www.w3.org/1999/xhtml"><HEAD id=Head1><TITLE>找回密码</TITLE>
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
class=required>*</SPAN>游戏账号：</LABEL> <INPUT class="i-text f_l" id=_user_name name="_user_name"> 
<DIV class=fm-explain id=_user_name_info>请填写注册时所填写的游戏账号.</DIV>
</DIV>
<DIV class=fm-part>
<DIV class=fm-part>
<DIV class=fm-item><LABEL class=fm-label><SPAN 
class=required>*</SPAN>超级密码：</LABEL> <INPUT class="i-text f_l" id=_user_superpwd 
type=password name=_user_superpwd> 
<DIV class=fm-explain id=_user_superpwd_info>请填写注册时所填写的超级密码.</DIV>
</DIV>
<DIV class=fm-item><LABEL class=fm-label><SPAN 
class=required>*</SPAN>新 密 码：</LABEL> <INPUT class="i-text f_l" id=_user_newpasswd 
type=password name=_user_newpasswd> 
<DIV class=fm-explain id=_user_passwd_info>4-16位，字母区分大小写，不能用符号“.”</DIV></DIV>
<?php if($isPin){ ?>
<DIV class="fm-item "><LABEL class="fm-label  f_l" for=check_code><SPAN 
class=required>*</SPAN>验证码：</LABEL> <INPUT class="i-text i-text-authcode  f_l" 
id=Pin alt=请输入图中的4位数字。 maxLength=4 name=Pin><img src="Pin.php" style="cursor:pointer" onClick="this.src='Pin.php?'+Math.random()" /><span>看不清?点击验证码刷新</SPAN> 
<DIV class=fm-explain id=_rand_code_info>请输入图片内验证符，不区分大小写！</DIV></DIV>
<DIV class=kg></DIV>
<?php } ?>
<DIV class=fm-item>
 <INPUT id=submitbutton style="CURSOR: hand" type=image 
src="images/sm_btn_xyb1.gif" 
name=_doreg> 
<DIV class=fm-explain></DIV></DIV><BR><BR></DIV></DIV>


<DIV class=foot></DIV></DIV>

</FORM>
</BODY></HTML>
<script language="javascript" type="text/javascript">
var aCity={11:"北京",12:"天津",13:"河北",14:"山西",15:"内蒙古",21:"辽宁",22:"吉林",23:"黑龙江",31:"上海",32:"江苏",33:"浙江",34:"安徽",35:"福建",36:"江西",37:"山东",41:"河南",42:"湖北",43:"湖南",44:"广东",45:"广西",46:"海南",50:"重庆",51:"四川",52:"贵州",53:"云南",54:"西藏",61:"陕西",62:"甘肃",63:"青海",64:"宁夏",65:"新疆",71:"台湾",81:"香港",82:"澳门",91:"国外"}  
function cidInfo(sId)
{
    var iSum=0
    var info=""
    if(!/^\d{17}(\d|x)$/i.test(sId))
 {
        return false;
 }
    sId=sId.replace(/x$/i,"a");
    if(aCity[parseInt(sId.substr(0,2))]==null)
 {
     return false; 
 }
    sBirthday=sId.substr(6,4)+"-"+Number(sId.substr(10,2))+"-"+Number(sId.substr(12,2));
    var d=new Date(sBirthday.replace(/-/g,"/"))
    if(sBirthday!=(d.getFullYear()+"-"+ (d.getMonth()+1) + "-" + d.getDate()))
 {
     return false; 
 }
 
    for(var i = 17;i>=0;i --) 
 {
     iSum += (Math.pow(2,i) % 11) * parseInt(sId.charAt(17 - i),11) 
 }
    if(iSum%11!=1)
 {
     return false; 
 }
    return true;
}

</script>

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
	var idcard=$("_user_idcard").value;
	var iscd=cidInfo(idcard);
	if(!iscd){
		alert("身份证格式错误");
		$("_user_idcard").focus();
		 return false; 				
	}	
	var ue = $("_user_email").value;
	if(ue == ""){ 
		 alert("安全邮箱不能为空");
		$("_user_email").focus();			  
		return false; 
	 }
	if (ue.charAt(0) == "." || ue.charAt(0) == "@" || ue.indexOf('@', 0) == -1|| ue.indexOf('.', 0) == -1 || ue.lastIndexOf("@") == ue.length-1 || ue.lastIndexOf(".") == ue.length-1){ 
		  alert("安全邮箱格式错误");
		  $("_user_email").focus();			  
		  return false; 		   	  	 		
	}
	var us=$("_user_superpwd").value;
	if(us.length<4 || us.length>16){
		  alert("超级密码在4-16位之间");
		  $("_user_superpwd").focus();              
		  return false; 	
	}
	var unew=$("_user_newpasswd").value;
	if(unew.length<4 || unew.length>16)
	{
		  alert("新密码必须在4-16位之间");	
		  $("_user_newpasswd").focus();
		  return false; 		  
	}
	if(unew.indexOf(".")>=0)
	{
		  alert("新密码不能含 . ");	
		  $("_user_newpasswd").focus();	
		  return false; 		  	
	}
	//$("form1").submit();						 
	//return true;
}
</script>