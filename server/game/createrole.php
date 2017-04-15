<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php
	require_once("UserFunc.php");
	require_once("xiaonei/xiaonei-util.php");
	if(count($_POST)==5){
		$arr=array();
		$arr[]=$_POST["input_name"];
		$arr[]=$GLOBALS['createRole']['new_city'];
		$arr[]=$_POST["input_zhou"];
		$arr[]=$_POST["flag"];
		$arr[]=$_POST["sex"];
		$arr[]=mt_rand(1,9);
		$arr[]=123456;
		$passport=$_POST["passport"];
		$uid=sql_fetch_one_cell("select uid from sys_user where passport='$passport'");
		if ($uid=="")  //还没有创建角色
		{
			$userCount = sql_fetch_one_cell("select count(*) from sys_user where uid > 1000");
			$maxUserCount = sql_fetch_one_cell("select value from mem_state where state=100");
			if ($userCount >= $maxUserCount)
			{
				$temp=$GLOBALS['createRole']['sever_is_full'];
				echo "<script> alert($temp);history.back();</script>";
				exit;
			}
			$state = 3;     //刚创建角色，还没有城池
			// passtype 改变，xiaonei改成renren
			$uid = sql_insert("insert into sys_user (`passtype`,`passport`,`group`,`state`,`regtime`,`domainid`,`honour`) values ('renren','$passport','0','$state',unix_timestamp(),'0',0)");
			sql_query("insert into sys_online (`uid`,`lastupdate`,`onlineupdate`,`onlinetime`) values ('$uid',unix_timestamp(),unix_timestamp(),0)");
		}
		try{
			createRole($uid,$arr);
			Header("HTTP/1.1 301 Moved Permanently");
			Header("Location:/index.php");
			exit;
		}catch(Exception $e){
			$ret=$e->getMessage();
			//$ret=iconv("UTF-8","GB2312//IGNORE",$ret);
			echo "<script> alert('$ret');history.back();</script>";
		}
		//exit;
	}else{
		$uid = getCurrentUser(SERVER_NAME);
		//echo "aa".$uid;
		if($uid==""){
			Header("HTTP/1.1 301 Moved Permanently");
			Header("Location:http://rexue.renren.com/");
			exit;
		}
		$userstate = sql_fetch_one_cell("select state from sys_user where passport='$uid'");
		if ($userstate!=""&&$userstate != 3)
		{
			Header("HTTP/1.1 301 Moved Permanently");
			Header("Location:/index.php");
			exit;
		}
	}
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>我爱热血三国1.2版</title>
<link href="xiaonei/css/common.css" rel="stylesheet" type="text/css" />
<script src="xiaonei/lang_zh_CN.js" language="javascript"></script>
<script>
	var LANG={
		"_Global_1":"随机","_Global_2":"司隶","_Global_3":"冀州","_Global_4":"豫州","_Global_5":"兖州","_Global_6":"徐州","_Global_7":"青州","_Global_8":"荆州","_Global_9":"扬州","_Global_10":"益州","_Global_11":"凉州","_Global_12":"并州","_Global_13":"幽州","_Global_14":"交州","_Global_15":"匈奴","_Global_16":"乌丸","_Global_17":"羌","_Global_18":"南蛮","_Global_19":"山越","_Global_2298":"系统会 <i>随机</i> 选择一个州","_Global_2299":"的城池情况:<br/><br/>君主数:","_Global_2300":"城池数:","_Global_2301":"拥挤度:<font color='#00FF00'>","_Global_2302":"拥挤度:<font color='#FFFF00'>","_Global_2303":"拥挤度:<font color='#FF0000'>"
	}
	function i18n(name){
		return LANG[name];
	}
	var PROVINCE_NAME=[i18n('_Global_1'),i18n('_Global_2'),i18n('_Global_3'),i18n('_Global_4'),i18n('_Global_5'),i18n('_Global_6'),i18n('_Global_7'),i18n('_Global_8'),i18n('_Global_9'),i18n('_Global_10'),i18n('_Global_11'),i18n('_Global_12'),i18n('_Global_13'),i18n('_Global_14'),i18n('_Global_15'),i18n('_Global_16'),i18n('_Global_17'),i18n('_Global_18'),i18n('_Global_19')];

	var passport=0;
	var sex=1;
	var provinceUserCountArray;
	var provinceCityCountArray;
	var provinceLandCountArray;
	function $(id){
		return document.getElementById(id);
	}
	function trim(str)
	{
		return str.replace(/(^\s*)|(\s*$)/g, "");
	}
	
	function submitForm(){
		var tname=trim($("name").value);
		if(tname.length>8){
			alert($GLOBALS['createRole']['name_too_long']);
			return;
		}
		if(tname.length<=1){
			alert($GLOBALS['createRole']['name_too_short']);
			return;
		}
		$("flag").value=tname.substring(0,1);
		$("input_name").value=$("name").value;
		$("input_zhou").value= $("zhouList").value;
		$("passport").value= passport;
		$("sex").value= sex;
		$("frm").submit();
	}
	function setUserInfo(p,obj){
		//alert(obj["name"].length);
		passport=p;
		if(obj=="") alert($GLOBALS['createRole']['login_first']);
		$("name").value=obj["name"];
		if(obj["sex"]==$GLOBALS['createRole']['is_man']){
			sex=1;
			$("pGril").style.display="none";
		}else{
			sex=0;
			$("pBoy").style.display="none";
		}
		$("imgHead").src=obj["url"];
	}
	function setProvinceInfo(obj){
		provinceUserCountArray=obj.usercount.toString().split(",");
		provinceCityCountArray=obj.citycount.toString().split(",");
		provinceLandCountArray=obj.citytotal.toString().split(",");
		var list = document.getElementById("zhouList");
		for(var i = 0; i <= 13; i++){
			var newOption = document.createElement("option");
			newOption.setAttribute("value", i);
			newOption.appendChild(document.createTextNode(i18n("_Global_"+(i+1))));
			list.appendChild(newOption);
		}
		selectProvince();
	}
	//alert(PROVINCE_NAME[8]);
	function selectProvince(){
		var ret="";
		var i=parseInt($("zhouList").value)-1;
		if(i<0){
			ret = i18n('_Global_2298');
		}else{
			var percent=Math.floor(100*parseInt(provinceCityCountArray[i]) / parseInt(provinceLandCountArray[i]));
			ret = "<i>"+PROVINCE_NAME[i+1]+"</i>"+i18n('_Global_2299')+provinceUserCountArray[i]+"<br/>";
			ret += i18n('_Global_2300')+provinceCityCountArray[i]+"<br/>";
			if(percent<40) ret += i18n('_Global_2301')+percent.toString()+"%</font>";
			else if(percent<80) ret += i18n('_Global_2302')+percent.toString()+"%</font>";
			else ret += i18n('_Global_2303')+percent.toString()+"%</font>";
		}
		$("zoudetails").innerHTML=ret;
		//alert($("zhouList").value);
	}
</script>
</head>
<body>
<div id="Warpper">
  <h1 class="bg1"></h1>
  <h1 class="bg2"></h1>
  <h1 class="bg3"></h1>    
  <!-- 左边 -->
  <ul class="Photo">
    <li>
      <div class="Photoimg"><img src="xiaonei/Images/Gril.jpg" width="50" height="50" id="imgHead" /></div>
      <p class="PhotoGril" id='pGril'>巾帼英雄</p>
      <p class="PhotoBoy" id='pBoy'>英雄豪杰</p>
    </li>
  </ul>
  <!-- 中间 -->
  <ul class="Inputbox">
    <li><em>君主姓名</em><span><input id="name" type="text" maxlength="8"/></span></li>
    <li><em>城池属地</em><span>
      <select id="zhouList" onchange="selectProvince()">
        <!--option value="0">随机</option-->
      </select>
    </span></li>
    <li class="ZhuanTai" id="zoudetails"><i>司隶</i> 的城池情况<br />
      <br />
      君主数：2532<br />
      城池数：2141<br />
      拥挤度：<b>23%</b></li>
  </ul>
  <!-- 右边 -->
  <ul class="ShouMing">
  <li><span>君主姓名不得超过8个字符</span></li>
  <li><span>请选择城池的位置</span></li>
  <li class="ZhuanTai2"><i>三国英雄辈出，群雄争霸烽火连天！<br/>
统帅千军万马纵横天下，坐拥江山美人。<br/>
热血三国圆你权统天下、霸业千秋梦！</i></li>
  </ul>
  <!-- 开始游戏 -->
  <div class="StartButton"><input name="input" type="button" onclick="submitForm();"/></div>
</div>

<form action="createrole.php" name="frm" id="frm" method="post" style="display:none">
	<input type="text" id="input_name" name="input_name"></input>
	<input type="text" id="input_zhou" name="input_zhou"></input>
	<input type="text" id="flag" name="flag"></input>
	<input type="text" id="passport" name="passport"></input>
	<input type="text" id="sex" name="sex"></input>
</form>	

</body>
</html>


<?php
	$userinfo = json_encode(getUserInfo($uid));
	echo "<script> setUserInfo($uid,$userinfo);</script>";
	$arr = loadProvinceInfo($uid,0);
	$provinceinfo=json_encode(array('usercount'=>$arr[0],'citycount'=>$arr[1],'citytotal'=>$arr[2]));
	//echo $provinceinfo;
	echo "<script> setProvinceInfo($provinceinfo);</script>";
	
?>
