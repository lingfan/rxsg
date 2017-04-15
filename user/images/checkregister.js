///根据不同表单酌情修改checkArr数组

var style_DF = "note";
var style_OK = "noteOK";
var style_ER = "noteError";
var style_FC = "noteFocus";
var divBegin = "<span class='note'>";
var divBegin_OK = "<span class='noteOK'>";
var divBegin_ER = "<span class='noteError'>";
var divEnd = "</span>";
var kg = "&nbsp;&nbsp;&nbsp;&nbsp;";
var msgok="<img src='images/2.0/ok.gif' align='absmiddle'> ";
var msgerr="<img src='images/2.0/err.gif' align='absmiddle'> ";

var msginfo_ok=kg+msgok + "<font color='#01c842'>输入正确!</font>"

var msgInfo_ER_MW = "";
var msgInfo_ER_MW1 =kg + msgerr + "账号不能为空！";
var msgInfo_ER_MW2 =kg + msgerr + "密码不能为空！";
var msgInfo_ER_MW3 =kg + msgerr + "请再次验证登陆密码！";
var msgInfo_ER_MW6 =kg + msgerr + "邮箱地址不能为空！";
var msgInfo_ER_MW7 =kg + msgerr + "请再次验证安全邮箱！";
var msgInfo_ER_MW8 =kg + msgerr + "超级密码不能为空！";
var msgInfo_ER_MW9 =kg + msgerr + "请再次验证超级密码！";
var msgInfo_ER_MW10 =kg + msgerr + "请输入图片上的4位验证码！";
var msgInfo_ER_MW11 =kg + msgerr + "请点击《用户使用协议》左下方框打钩,以确认同意用户协议并继续！";

var msgInfo_ER01 =kg + msgerr + "4-16位小写英文字母和数字组成，首位为字母";
var msgInfo_ER02 =kg + msgerr + "长度不能小于4个字符，且不能大于16个字符。";

var msgInfo_ER11 =kg + msgerr + "密码长度必须大于4位且小于16位。";
var msgInfo_ER12 =kg + msgerr + "密码不能与账号名相同。";
var msgInfo_ER13 =kg + msgerr + "密码不能含\".\"符号。";
var msgInfo_ER21 =kg + msgerr + "两次输入的密码不一致，请重新输入确认密码。";

var msgInfo_ER41 =kg + msgerr + "密码提示答案长度必须在4-18位。";

var msgInfo_ER51 = "";
var msgInfo_ER61 =kg + msgerr + "格式有误，15或18位的数字。";

var msgInfo_ER81 =kg + msgerr + "Email地址格式不正确!";
var msgInfo_ER82 =kg + msgerr + "电子邮件长度不能大于40个字符。";
var msgInfo_ER91 =kg + msgerr + "两次填写的安全邮箱必须一致，请修改。";

var msgInfo_ER101 =kg + msgerr + "格式有误：5-10位纯数字。";
var msgInfo_ER111 =kg + msgerr + "格式有误：只能输入11位纯数字。";

var msgInfo_ER121 =kg + msgerr + "密码长度必须大于4位且小于16位。";
var msgInfo_ER122 =kg + msgerr + "超级密码不能与账号名相同。";
var msgInfo_ER123 =kg + msgerr + "超级密码不能与账号密码相同。";
var msgInfo_ER131 =kg + msgerr + "验证码有误，请输入上边图片上的4位验证码。";

var msgInfo_ER141 =kg + msgerr + "昵称须在3-16位，不可使用数字作为第一位,只能包含汉字、英文字母、数字和下划线。";
var msgInfo_ER142 =kg + msgerr + "昵称的长度不能小于3个字符，且不能大于16个字符，中文不能超过5个汉字";
var msgInfo_ER151 =kg + msgerr + "两次输入的超级密码不一致，请重新输入。";
var msgInfo_ER171 =kg + msgerr + "请输入您的真实姓名。";

msgInfo_DE0 =kg + "4-16位小写英文字母和数字组成，首位为字母";

msgInfo_DE1 =kg + "4-16位，字母区分大小写，不能用符号“.”";
msgInfo_DE2 =kg + "请再次输入您的密码。";
msgInfo_DE3 =kg + "请选择密码提示问题。";
msgInfo_DE4 =kg + "请您填写密码提示答案，长度必须在4-18位字符。";
msgInfo_DE5 =kg + "请选择身份证类型。";
msgInfo_DE6 =kg + "<font color='red'>判断账号归属依据，注册后不能修改</font>";
msgInfo_DE7 =kg + "请选择您所在的地区。";
msgInfo_DE8 =kg + "<font color='red'>修改登录密码的依据，请务必仔细填写并牢记</font>";
msgInfo_DE9 =kg + "请再次输入您的邮箱！";
msgInfo_DE10 =kg + "找回丢失密码时的QQ号码！一旦所有方式都无法认证您的身份，本QQ将成为最后依据。";
msgInfo_DE11 =kg + "请输入手机号码！";
msgInfo_DE12 =kg + "<font color='red'>修改登录密码的依据，请务必仔细填写并牢记</font>";
msgInfo_DE13 =kg + "请输入图片内验证符，不区分大小写！";
msgInfo_DE14 =kg + "昵称须在3-16位，不可使用数字作为第一位，只能包含汉字、英文字母、数字和下划线。";
msgInfo_DE15 =kg + "请再次输入您的超级密码。";
msgInfo_DE16 =kg + "请确认您接受了协议！";
msgInfo_DE17 =kg + "请输入真实姓名";
var checkArr = new Array();
checkArr[0]=new Array("_user_name",true,"_user_name_info",style_DF,style_OK,style_ER,msgInfo_DE0,divBegin+msgInfo_DE0+divEnd,"checkUserName",
	new Array(divBegin_ER+msgInfo_ER_MW1+divEnd,'EMPTY'),
	new Array(divBegin_ER+msgInfo_ER01+divEnd,'USERNAME'),
	new Array(divBegin_ER+msgInfo_ER02+divEnd,'LENGTH',4,16)
	);

checkArr[1]=new Array("_user_passwd",true,"_user_passwd_info",style_DF,style_OK,style_ER,msgInfo_DE1,divBegin+msginfo_ok+divEnd,null,
	new Array(divBegin_ER+msgInfo_ER_MW2+divEnd,'EMPTY'),
	new Array(divBegin_ER+msgInfo_ER12+divEnd,'NOTSAME','_user_name'),
	new Array(divBegin_ER+msgInfo_ER13+divEnd,'PASSWORD'),
	new Array(divBegin_ER+msgInfo_ER11+divEnd,'LENGTH',4,16)
	
	);
checkArr[2]=new Array("_user_passwd2",true,"_user_passwd2_info",style_DF,style_OK,style_ER,msgInfo_DE2,divBegin+msginfo_ok+divEnd,null,
	new Array(divBegin_ER+msgInfo_ER_MW3+divEnd,'EMPTY'),
	new Array(divBegin_ER+msgInfo_ER21+divEnd,'SAME','_user_passwd')
	);	

checkArr[3]=new Array("_user_email",true,"_user_email_info",style_DF,style_OK,style_ER,msgInfo_DE8,divBegin+msginfo_ok+divEnd,null,
	new Array(divBegin_ER+msgInfo_ER_MW6+divEnd,'EMPTY'),
	new Array(divBegin_ER+msgInfo_ER81+divEnd,'EMAIL'),
	new Array(divBegin_ER+msgInfo_ER82+divEnd,'SMAX',40)
	);

checkArr[4]=new Array("_user_email2",true,"_user_email2_info",style_DF,style_OK,style_ER,msgInfo_DE9,divBegin+msginfo_ok+divEnd,null,
	new Array(divBegin_ER+msgInfo_ER_MW7+divEnd,'EMPTY'),
	new Array(divBegin_ER+msgInfo_ER91+divEnd,'SAME','_user_email')
	);			

checkArr[5]=new Array("_user_superpwd",true,"_user_superpwd_info",style_DF,style_OK,style_ER,msgInfo_DE12,divBegin+msginfo_ok+divEnd,null,
	new Array(divBegin_ER+msgInfo_ER_MW8+divEnd,'EMPTY'),
	new Array(divBegin_ER+msgInfo_ER121+divEnd,'LENGTH',4,16),
	new Array(divBegin_ER+msgInfo_ER122+divEnd,'NOTSAME','_user_name'),
	new Array(divBegin_ER+msgInfo_ER123+divEnd,'NOTSAME','_user_passwd')
	);	
checkArr[6]=new Array("_user_superpwd2",true,"_user_superpwd2_info",style_DF,style_OK,style_ER,msgInfo_DE15,divBegin+msginfo_ok+divEnd,null,
	new Array(divBegin_ER+msgInfo_ER_MW9+divEnd,'EMPTY'),
	new Array(divBegin_ER+msgInfo_ER121+divEnd,'LENGTH',4,16),
	new Array(divBegin_ER+msgInfo_ER151+divEnd,'SAME','_user_superpwd')
	);	

	checkArr[9]=new Array("_user_idcard",true,"_user_idcard_info",style_DF,style_OK,style_ER,msgInfo_DE6,divBegin+msginfo_ok+divEnd,"checkIdCard",
	new Array(divBegin_ER+msgInfo_ER_MW5+divEnd,'EMPTY')
	);
function checkExist(targetArr){
	for(var j=0;j<targetArr.length;j++){
		if (document.getElementById(targetArr[j][2]).className==style_ER)
		{
			submitCount++;
		}
	}
}
var userNameExist=0;
var nickNameExist=0;
var validCodeExist=0;
var IdCardOK =1;
function disabledsubmitbutton()
{
	document.getElementById('submitbutton').disabled=true;
}
function frmCheck(frm)
{
	try
	{
		with(frm)
		{
			
			if (!formCheckByArr(frm,checkArr,"div",true)){				
				return false;
			}
		}

		if (userNameExist ||validCodeExist||IdCardOK ){
			if(IdCardOK ){
				document.getElementById("_user_idcard_info").innerHTML="<font color='red'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;身份证号码有误</font>";
				document.getElementById("_user_idcard").select();
			}
			
			if(userNameExist ){
				document.getElementById("_user_name_info").innerHTML="<font color='red'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;游戏账号重复</font>";
				document.getElementById("_user_name").select();
			}

			if(validCodeExist ){
				document.getElementById("_rand_code_info").innerHTML=divBegin_ER + kg + msgerr + "对不起，验证码错误或过期，请刷新后重新输入" + divEnd;
				document.getElementById("_rand_code").select();
			}
			return false;
		}

		setTimeout('disabledsubmitbutton()',50);
		///document.getElementById('submitbutton').disabled=true;
		return true;	
	}
	catch(error)
	{
		functionError(error,"[FormCheckError]");
		return false;
	}
}

function checkUserName2(http_request) {
	var result = http_request.responseText;
	if (result=="0"){
		document.getElementById("_user_name_info").className=style_OK;
		document.getElementById("_user_name_info").innerHTML=divBegin_OK + kg+ msgok + "此账号可以使用。" + divEnd;
		userNameExist=0;
	}else if(result=="1"){
		document.getElementById("_user_name_info").className=style_ER;
		document.getElementById("_user_name_info").innerHTML=divBegin_ER + kg + msgerr + "此账号已经被注册，请修改" + divEnd;
		userNameExist=1;
	}else{
		document.getElementById("_user_name_info").className=style_DF;
		document.getElementById("_user_name_info").innerHTML=divBegin_DF + kg + msgerr + "本次验证失败，请检查网络是否畅通。"+result;
	}
}

function username_exist()
{
	userNameExist=true;
	document.getElementById("_user_name_info").className=style_ER;
	document.getElementById("_user_name_info").innerHTML=divBegin_ER + kg + msgerr + "此账号已经被注册，请修改" + divEnd; 
}
function username_not_exist()
{
	userNameExist=false;
	document.getElementById("_user_name_info").className=style_OK;
    document.getElementById("_user_name_info").innerHTML=divBegin_OK + kg+ msgok+ "此账号可以使用。" + divEnd; 
}

function checkUserName()
{
	//modify by wjx
	userNameExist=false;	
	document.getElementById("_user_name_info").className=style_OK;
	document.getElementById("_user_name_info").innerHTML=divBegin_OK + kg+ msgok+ "输入正确!" + divEnd;
}

function checkValidCode()
{
	//modify by wjx	
	
	document.getElementById("_rand_code_info").className=style_FC;
	document.getElementById("_rand_code_info").innerHTML="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;正在检查...";
	NewAjaxFunc().CheckUserInput('randcode',document.getElementById("_rand_code"),randcode_not_valid,randcode_valid);	
	/*
	window["checkCode"] = new XMLHttpClient("checkCode");
	var vcode  = document.getElementById("vcode").value;
	window["checkCode"].open('post','check.php?vcode='+encodeURI(vcode),true);
	
	//window["checkNick"].setRequestHeader("Content-Type", "text/plain;charset=UTF-8");
	window["checkCode"].attachOnStatus(200,checkValidCode2);
	window["checkCode"].send('/');
	//*/
}
function randcode_not_valid()
{
	validCodeExist=1;
	document.getElementById("_rand_code_info").className=style_ER;
	document.getElementById("_rand_code_info").innerHTML=divBegin_ER + kg + msgerr + "对不起，验证码错误或过期，请刷新后重新输入" + divEnd; 
}
function randcode_valid()
{
	validCodeExist=0;
	document.getElementById("_rand_code_info").className=style_OK;
    document.getElementById("_rand_code_info").innerHTML=divBegin_OK + kg+ msgok+ "输入正确!" + divEnd; 
}

/*
function checkValidCode2(http_request) {
	var result = http_request.responseText;
	if (result=="0"){
		document.getElementById("_rand_code_info").className=style_OK;
		document.getElementById("_rand_code_info").innerHTML=divBegin+"输入正确。"+divEnd;
		validCodeExist =0;
	}else if(result=="1"){
		document.getElementById("_rand_code_info").className=style_ER;
		document.getElementById("_rand_code_info").innerHTML=divBegin+"对不起，验证码错误或过期，请刷新后重新输入。"+divEnd;
		validCodeExist =1;
	}else{
		document.getElementById("_rand_code_info").className=style_DF;
		document.getElementById("_rand_code_info").innerHTML="本次验证失败，请检查网络是否畅通。"+result;
	}
}//*/


function checkNickName2(http_request) {
	var result = http_request.responseText;
	if (result=="0"){
		document.getElementById("nickname_info").className=style_OK;
		document.getElementById("nickname_info").innerHTML=divBegin_OK+ kg+ msgok+ "该昵称还未使用。"+divEnd;
		nickNameExist =0;
	}else if(result=="1"){
		document.getElementById("nickname_info").className=style_ER;
		document.getElementById("nickname_info").innerHTML=divBegin_ER+ kg + msgerr + "该昵称已经被其他用户使用。"+divEnd;
		nickNameExist =1;
	}else{
		document.getElementById("nickname_info").className=style_DF;
		document.getElementById("nickname_info").innerHTML=divBegin_DF + kg + msgerr + "本次验证失败，请检查网络是否畅通。"+result;
	}
}
function checkNickName()
{
	//document.getElementById("nickname_info").className=style_FC;
	//document.getElementById("nickname_info").innerHTML="正在核实该昵称是否可用...";

	//window["checkNick"] = new XMLHttpClient("checkNick");
	//var nickname  = document.getElementById("nickname").value;
	//window["checkNick"].open('post','check.php?nickname='+encodeURI(nickname),true);
	
	//window["checkNick"].setRequestHeader("Content-Type", "text/plain;charset=UTF-8");
	//window["checkNick"].attachOnStatus(200,checkNickName2);
	//window["checkNick"].send('/');
}
var remindiconmsg="";
function checkIdCard(){

	var IdCardValue= document.getElementById('_user_idcard').value;

	if(CheckIdCardValue(IdCardValue)){
		var CurrentAge = getAge();
        
		if(CurrentAge<18){
			IdCardOK =1;
			document.getElementById("_user_idcard_info").className=style_ER;
			document.getElementById("_user_idcard_info").innerHTML=divBegin_ER+ kg + msgerr + "抱歉，根据您所输入的身份证号码，您属于未满18周岁未成年人，无法注册。"+remindiconmsg+divEnd;
		}else{
			document.getElementById("_user_idcard_info").className=style_OK;
			document.getElementById("_user_idcard_info").innerHTML=divBegin_OK+ kg+ msgok+"输入正确!"+divEnd;
			IdCardOK =0;
		}
	}else{
		document.getElementById("_user_idcard_info").className=style_ER;
		document.getElementById("_user_idcard_info").innerHTML=divBegin_ER+IdCardErrorMsg+divEnd;
		IdCardOK =1;
	}	
}

/////*********************************各页面通用*****************************///
//-->
function selectPrompt(selectObj,spanObj1,spanObj2){
	if(selectObj.value=="-1"){
		spanObj1.style.display = "";
		spanObj2.style.display = "none";
		selectObj.value = '';
		document.getElementById('prompt1').value='';
	}
}
function changeSelect(prompt1){
	var selectObj = document.getElementById('prompt');
	if(getByteCount(prompt1.value)>=6 && getByteCount(prompt1.value)<=18){
		selectObj.options[selectObj.selectedIndex].value = prompt1.value;
		document.getElementById('prompt_info').className = style_OK;
	}else{
		document.getElementById('prompt_info').className = style_ER;
		document.getElementById('prompt_info').innerHTML=divBegin_ER+ kg + msgerr + "提示问题长度必须在6-18位。"+divEnd;
	}
}
function returnSelect(spanObj1,spanObj2){
	var selectObj = document.getElementById('prompt');

	spanObj1.style.display = "none";
	spanObj2.style.display = "";
	selectObj.options[selectObj.selectedIndex].value = '';
	document.getElementById('prompt_info').className = style_ER;
		
}

////copy from common.js
function chineseCount(str)
{//返回字符串中文字符的个数
	var c=0;
	for(var i=0;i<str.length;i++)
		if(str.charCodeAt(i)>=10000)
			c++;
	return c;
}

function isChinese(str)
{//单个字符是中文则返回true，若是字符串则只要有一个中文则返回true
	var flag=true;
	for(var i=0;i<str.length;i++)
		if(str.charCodeAt(i)>=10000)
			return true;
	return false;
}

function getByteCount(str)
{
//返回字符串的字节数，中文算两个字节 
//注:由于UTF-8中文占3个字节,所以这里已经改为3
//这里简化一下将中文也一个字符当作一个字节来算
	var c=0;
	for(var i=0;i<str.length;i++){
		if(str.charCodeAt(i)>=10000)
			c+=3;
		else
			c++;
	}
	return c;
}

function lengthOfByte(str,cLength)
{
//返回字符串的字节数，中文算cLength个字节 
	var c=0;
	for(var i=0;i<str.length;i++)
		if(str.charCodeAt(i)>=10000)
			c+=cLength;
		else
			c++;
	return c;
}

function leftOfByte(str,length)
{
//返回字符串左起共length个字节的字符串
	if (str == null||str==""){
		return "";
	}
	var temp;
	var yy = length;
    if (str.length<length){
        temp = str;
        yy = temp.length;
    }else{
        temp = str.substring(0,length);
    }

    var xx = lengthOfByte(temp,2) - length;
        
    while (xx>0){
        if (xx==1){
            yy = yy-xx;
        }else{
            yy = yy-xx/2;
        }
            
        temp = temp.substring(0,yy);
        xx = lengthOfByte(temp,2) - length;
    }

	return temp;
}

//--全部复选框全部选中与全不选中函数----
function selectCheckboxAll(s_count,s_prefix)
{
	try
	{
		var cnt=parseInt(s_count)
		for(var i=1;i<=cnt;i++)
			if(document.all(s_prefix+i)&&!document.all(s_prefix+i).disabled)
				document.all(s_prefix+i).checked=true;
	}
	catch(error)
	{
		functionError(error,"错误：[SelecteCheckboxAllError]");
	}
}
function unselectCheckboxAll(s_count,s_prefix)
{
	try
	{
		var cnt=parseInt(s_count)
		for(var i=1;i<=cnt;i++)
			if(document.all(s_prefix+i)&&!document.all(s_prefix+i).disabled)
				document.all(s_prefix+i).checked=false;
	}
	catch(error)
	{
		functionError(error,"错误：[UnSelecteCheckboxAllError]");
	}
}

//********************错误处理函数******************
function functionError(errObj,mess)
{
//执行函数时出错处理函数
//mess为传来的消息，有则替换默认消息
try
	{
		var ms="错误：[ExcuteJSError]";
		if(mess!=null&&mess!="")
			ms=mess;
		if(errObj!=null)
		{
			ms+="\n描述："+errObj.description;
			alert(ms);
		}		
	}
	catch(error)
	{
		alert("错误：[FunctionError's Error]");
	}
}


//清空Select列表
function selectBoxClear(boxObj)
{
	try
	{
		if(boxObj==null) return;
		for(var i=boxObj.options.length-1;i>=0;i--)
			boxObj.options.remove(i);
	}
	catch(error)
	{}
}

//向Select列表对象中添加新option项
function selectBoxAdd(boxObj,optVal,optText)
{ 
	try
	{
		if(boxObj==null) return;
		boxObj.options.add(new Option(optText,optVal));
	}
	catch(error)
	{}
}

//选中Select列表对象中值为optVal的项
function selectBoxChoose(boxObj,optVal)
{ 
	try
	{
		if(boxObj==null) return;
		for(var i=boxObj.options.length-1;i>=0;i--)
			if(boxObj.options[i].value==optVal)
			{
				boxObj.options[i].selected=true;
				return;
			}
	}
	catch(error)
	{}
}

//选中Select列表对象中值为optVal的项
function selectBoxChoose_text(boxObj,optVal)
{ 
	try
	{
		if(boxObj==null) return;
		for(var i=boxObj.options.length-1;i>=0;i--)
			if(boxObj.options[i].text==optVal)
			{
				boxObj.options[i].selected=true;
				return;
			}
	}
	catch(error)
	{}
}

function strim(strObj)
{//去前后空格
	if(strObj==null||strObj=="")
		return "";
	var s_str=""+strObj;

	while (s_str.substring (0, 1) == ' ') 
		s_str = s_str.substring (1, s_str.length);
	while (s_str.substring (s_str.length - 1, s_str.length) == ' ')
		s_str = s_str.substring (0, s_str.length - 1);
	return s_str;
} 

//获取url中的参数
function getParamValue(url,ParamName)
{
	var ii = url.indexOf("?");
	if (ii==-1)
	{
		return "";
	}
	url = url.substring(ii+1,url.length);
	ii = url.indexOf(ParamName + "=");
	if (ii==-1)
	{
		return "";
	}
	url = url.substring(ii+1,url.length);
	ii = url.indexOf("&");
	if (ii==-1)
	{
		return url.substring(ParamName.length ,url.length);
	}else{
		return url.substring(ParamName.length ,ii);
	}
}

function getItemValue(str,ItemName)
{
	var arr = str.split("|");
	for(var ii=0;ii<arr.length;ii++){
		if (arr[ii].substring(0,ItemName.length+1)==ItemName+"="){
			return arr[ii].substring(ItemName.length+1,arr[ii].length)
		}
	}
	return "";
}

function addItemValue(str,ItemName,ItemValue)
{
	var arr = str.split("|");
	var outstr = "";
	var bfind = false;
	for(var ii=0;ii<arr.length;ii++){
		if (arr[ii].substring(0,ItemName.length+1)==ItemName+"="){
			if (outstr==""){
				outstr = ItemName+"="+ItemValue;
			}else{
				outstr = outstr + "|" + ItemName+"="+ItemValue;
			}
			
			bfind = true;
		}else{
			if (arr[ii]!=""){
				if (outstr==""){
					outstr = arr[ii];
				}else{
					outstr = outstr + "|" + arr[ii];
				}
			}
		}
	}
	if (!bfind){
		if (outstr==""){
			outstr = ItemName+"="+ItemValue;
		}else{
			outstr = outstr + "|" + ItemName+"="+ItemValue;
		}
	}
	return outstr;
}

function getTextElementData(parentElement, elementName) {
	if(parentElement.getElementsByTagName(elementName)[0].childNodes.length > 1) {
		/** 
		 * Firefox需要这样取得CDATA的内容
		 **/
		for(var j=0; j<parentElement.getElementsByTagName(elementName)[0].childNodes.length; j++) {
			if (parentElement.getElementsByTagName(elementName)[0].childNodes[j].nodeName == "#cdata-section") {
				return parentElement.getElementsByTagName(elementName)[0].childNodes[j].data;
			}
		}
	}else if(parentElement.getElementsByTagName(elementName)[0].firstChild) {
		/**
		 * 取得节点的内容
		 **/
		return parentElement.getElementsByTagName(elementName)[0].firstChild.nodeValue;
	}
}

function findPosX(obj) {
	var currentleft = 0;
	if (obj&&obj.offsetParent) {
		currentleft = obj.offsetLeft;
		while (obj = obj.offsetParent) {
			currentleft += obj.offsetLeft;
		}
	} else if (obj&&obj.x) currentleft += obj.x;
	return currentleft;
} 

function findPosY(obj) {
	var currenttop = 0;
	if (obj&&obj.offsetParent) {
		currenttop = obj.offsetTop;
		while (obj = obj.offsetParent) {
			currenttop += obj.offsetTop;
		}
	} else if (obj&&obj.y) currenttop += obj.y;
	return currenttop;
}

function getElementsByClassName(node, className) {
	var children = node.getElementsByTagName('*');
	var elements = new Array();
	for (var i=0; i<children.length; i++) {
		var child = children[i];
		var classNames = child.className.split(' ');
		for (var j = 0; j < classNames.length; j++) {
			if (classNames[j] == className) {
				elements.push(child);
				break;
			}
		}
	}
	return elements;
}

function SetCookie(name, value, expire)
{
var expdate = new Date();
var argv = SetCookie.arguments;
var argc = SetCookie.arguments.length;
var expires = (argc > 2) ? argv[2] : null;
var path = (argc > 3) ? argv[3] : null;
var domain = (argc > 4) ? argv[4] : null;
var secure = (argc > 5) ? argv[5] : false;
if(expires!=null) expdate.setTime(expdate.getTime() + ( expires * 1000 ));
document.cookie = name + "=" + escape (value) +((expires == null) ? "" : ("; expires="+ expdate.toGMTString()))
+((path == null) ? "" : ("; path=" + path)) +((domain == null) ? "" : ("; domain=" + domain))
+((secure == true) ? "; secure" : "");
}

function GetCookie(name)
{
	var arg = name + "=";
	var alen = arg.length;
	var clen = document.cookie.length;
	var i = 0;
	while (i < clen)
	{
		var j = i + alen;
		if (document.cookie.substring(i, j) == arg){
			var endstr = document.cookie.indexOf (";", j);
			if (endstr == -1)
				endstr = document.cookie.length;
			return document.cookie.substring(j, endstr);
		}

		i = document.cookie.indexOf(" ", i) + 1;
		if (i == 0) break;
	}
	return null;
}


///copy from check.js

function formCheckByArr(targetObj,targetArr,warnType,bFocus){
	if(!targetObj)
		return false;

	var checkObj = null; 
	var checkType = null;
	if (targetObj.nodeName.toUpperCase()=="FORM")
	{
		checkObj = targetObj.getElementsByTagName("*");
		checkType = "FORM";
	}else{
		checkObj = new Array(targetObj);
		checkType = "OBJ";
	}
	var bErrorALL = false;

	for (var i=0;i<checkObj.length;i++){
		var itemType = checkObj[i].nodeName.toUpperCase();
		if (itemType!="INPUT"&&itemType!="SELECT")
			continue;
		var itemCType = checkObj[i].type.toUpperCase();
		
		if(itemCType!='CHECKBOX'){
			var itemValue = checkObj[i].value;
		}else{
			if(checkObj[i].checked){
				var itemValue = checkObj[i].value;
			}else{
				var itemValue ='';
			}
		}
		var checkflag = null;
		for(var j=0;j<targetArr.length;j++){
			if (targetArr[j][0]==checkObj[i].name)
			{
				checkflag = targetArr[j];
				break;
			}
		}
		if (checkflag == null||!targetArr[j][1])
		{
			continue;
		}
		//验证控件
		var bError = false;
		for (var x=9;x<checkflag.length;x++)
		{	

			switch(checkflag[x][1].toUpperCase()){
				//-------------------- 我是分隔线 --------------------
				case "EMPTY":

					if (itemCType == "RADIO"){
						var objArray = document.getElementsByName(checkObj[i].name);
						var check_flag = false;
						if (objArray != null && objArray.length != null) {
				        	for(var xx = 0; xx < objArray.length; xx++){
				        		if(objArray[xx].checked){
									check_flag = true;
									break;
								}
				        	}
				        	if (check_flag==false){
				        		checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
								if (bFocus){
									checkObj[i].focus();
									return false;
								}else{
									bError = true;
								}
				        	}
				       	}
					}else{

						if (isEmpty(itemValue)){	
					
							checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
							if (bFocus){
								checkObj[i].focus();
								return false;
							}else{
								bError = true;
							}
						}
					}

					break;
				//-------------------- 我是分隔线 --------------------
				case "EMPTY2":
					if (isEmpty(itemValue)&&isEmpty(document.getElementById(checkflag[x][2]).value)){
						checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
						if (bFocus){
							checkObj[i].focus();
							return false;
						}else{
							bError = true;
						}
					}
					break;
				//-------------------- 我是分隔线 --------------------
				case "LENGTH":
					if (!isEmpty(itemValue)&&(getByteCount(itemValue)<checkflag[x][2]||getByteCount(itemValue)>checkflag[x][3])){
						checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
						if (bFocus){
							checkObj[i].focus();
							return false;
						}else{
							bError = true;
						}
					}
					break;
				//-------------------- 我是分隔线 --------------------
				case "SMAX":
					if (!isEmpty(itemValue)&&getByteCount(itemValue)>checkflag[x][2]){
						checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
						if (bFocus){
							checkObj[i].focus();
							return false;
						}else{
							bError = true;
						}
					}
					break;
				//-------------------- 我是分隔线 --------------------
				case "SMIN":
					if (!isEmpty(itemValue)&&getByteCount(itemValue)<checkflag[x][2]){
						checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
						if (bFocus){
							checkObj[i].focus();
							return false;
						}else{
							bError = true;
						}
					}
					break;
				//-------------------- 我是分隔线 --------------------
				case "INT":
					if (!isEmpty(itemValue)&&!isInteger(itemValue)){
						checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
						if (bFocus){
							checkObj[i].focus();
							return false;
						}else{
							bError = true;
						}
					}
					break;
				//-------------------- 我是分隔线 --------------------
				case "NUM":
					if (!isEmpty(itemValue)&&!isNumber(itemValue)){
						checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
						if (bFocus){
							checkObj[i].focus();
							return false;
						}else{
							bError = true;
						}
					}
					break;	
				//-------------------- 我是分隔线 --------------------
				case "IMAX":
					if (!isEmpty(itemValue)&&!isNumber(itemValue)){
						checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
						if (bFocus){
							checkObj[i].focus();
							return false;
						}else{
							bError = true;
						}
					}
					if(parseFloat(itemValue)>checkflag[x][2])
					{	
						checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
						if (bFocus){
							checkObj[i].focus();
							return false;
						}else{
							bError = true;
						}
					}
					break;	
				//-------------------- 我是分隔线 --------------------
				case "IMIN":
					if (!isEmpty(itemValue)&&!isNumber(itemValue)){
						checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
						if (bFocus){
							checkObj[i].focus();
							return false;
						}else{
							bError = true;
						}
					}
					if(parseFloat(itemValue)<checkflag[x][2])
					{	
						checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
						if (bFocus){
							checkObj[i].focus();
							return false;
						}else{
							bError = true;
						}
					}
					break;	
				//-------------------- 我是分隔线 --------------------
				case "EMAIL":
					if (!isEmpty(itemValue)&&!checkEmail(itemValue)){
						checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
						if (bFocus){
							checkObj[i].focus();
							return false;
						}else{
							bError = true;
						}
					}
					break;				
				//-------------------- 我是分隔线 --------------------
				case "PASSWORD":
					if (!isEmpty(itemValue)&&checkPassword(itemValue)){
						checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
						if (bFocus){
							checkObj[i].focus();
							return false;
						}else{
							bError = true;
						}
					}
					break;
				//-------------------- 我是分隔线 --------------------
				case "TEL":
					if (!isEmpty(itemValue)&&!checkTel(itemValue)){
						checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
						if (bFocus){
							checkObj[i].focus();
							return false;
						}else{
							bError = true;
						}
					}
					break;
				//-------------------- 我是分隔线 --------------------
				case "MOBILE":
					if (!isEmpty(itemValue)&&!checkMobile(itemValue)){
						checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
						if (bFocus){
							checkObj[i].focus();
							return false;
						}else{
							bError = true;
						}
					}
					break;				
				//-------------------- 我是分隔线 --------------------
				case "QQ":
					if (!isEmpty(itemValue)&&!checkQQ(itemValue)){
						checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
						if (bFocus){
							checkObj[i].focus();
							return false;
						}else{
							bError = true;
						}
					}
					break;
				//-------------------- 我是分隔线 --------------------
				case "POSTCODE":
					if (!isEmpty(itemValue)&&!checkPostcode(itemValue)){
						checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
						if (bFocus){
							checkObj[i].focus();
							return false;
						}else{
							bError = true;
						}
					}
					break;
				//-------------------- 我是分隔线 --------------------
				case "SAME":
					if (!isEmpty(itemValue)&&itemValue!=document.getElementById(checkflag[x][2]).value){
						checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
						if (bFocus){
							checkObj[i].focus();
							return false;
						}else{
							bError = true;
						}
					}
					break;				
				//-------------------- 我是分隔线 --------------------
				case "NOTSAME":
					if (!isEmpty(itemValue)&&itemValue==document.getElementById(checkflag[x][2]).value){
						checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
						if (bFocus){
							checkObj[i].focus();
							return false;
						}else{
							bError = true;
						}
					}
					break;
				//-------------------- 我是分隔线 --------------------
				case "NICKNAME":
					if (!isEmpty(itemValue)&&!checkNickname(itemValue)){
						checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
						if (bFocus){
							checkObj[i].focus();
							return false;
						}else{
							bError = true;
						}
					}
					break;
				case "CHINANAME":
					if (!isEmpty(itemValue)&&!checkRealname(itemValue)){
						checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
						if (bFocus){
							checkObj[i].focus();
							return false;
						}else{
							bError = true;
						}
					}
					break;

				//-------------------- 我是分隔线 --------------------				
				case "USERNAME":
	
					if (!isEmpty(itemValue)&&!checkUsername(itemValue)){
						checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
						if (bFocus){
							checkObj[i].focus();
							return false;
						}else{
							bError = true;
						}
					}
					break;
				//-------------------- 我是分隔线 --------------------
				case "CATE":
					if (!isEmpty(itemValue)&&checkObj[i].options[checkObj[i].selectedIndex].text==".."){
						checkAlert(warnType,checkflag[x][0],targetArr[j][2],targetArr[j][5]);
						if (bFocus){
							checkObj[i].focus();
							return false;
						}else{
							bError = true;
						}
					}
					break;
				//-------------------- 我是分隔线 --------------------
				default:
					alert("不可识别的校验信息,请联系系统管理员!ErrorCode:"+checkflag[x][1].toUpperCase());
					if (bFocus){
						checkObj[i].focus();
						return false;
					}else{
						bError = true;
					}
					break;
			}
			if (bError)
			{
				bErrorALL = true;
				break;
			}
		}
		if (!bError){

			if (warnType=="div")
			{
				if (itemValue==null||itemValue=="")
				{
					var warnDiv = document.getElementById(targetArr[j][2]);
					warnDiv.innerHTML = targetArr[j][6];
					warnDiv.className = targetArr[j][3];
					//document.getElementById(targetArr[j][0]).focus();
				}else{

					var warnDiv = document.getElementById(targetArr[j][2]);
					warnDiv.innerHTML = targetArr[j][7];
					warnDiv.className = targetArr[j][4];
				}
			}
			if(checkType=="OBJ"&&checkflag[8]!=null){
				eval(checkflag[8]+"();");
			}
		}
	}
	return !bErrorALL;
}

function checkAlert(warn_Type,msg_Info,objID,style_ERROR)  
{
	if (msg_Info==null||msg_Info=="")
	{
		msg_Info = "您输入的数据不符合要求，请核实！";
	}

	if (warn_Type=="alert")
	{
		alert(msg_Info);
	}else{
		var warnDiv = document.getElementById(objID);
		if (warnDiv)
		{

			warnDiv.innerHTML = msg_Info;
			warnDiv.className = style_ERROR;
		}
	}
	return;
}

function isEmpty(temp)  
{//是否为空

	return ((temp==null)||(strim(temp).length==0))
}

function isNumber(str) 
{//是否是数字
	if(strim(str)=="")
		return false;

	var i; 
	for(i=0;i<str.length;i++) 
	{ 
		var ch=str.charAt(i); 
		if((ch<'0'||ch>'9')&&ch!='.') return false; 
	}
	if(str==".")
		return false;

	return true; 
} 

function isInteger(str) 
{//是否是整型数
	if(strim(str)=="")
		return false;

	var i; 
	for(i=0;i<str.length;i++) 
	{ 
		var ch=str.charAt(i); 
		if(ch<'0'||ch>'9') return false; 
	}
	return true; 
}

function checkEmail(check_obj)
{//检查Email格式是否正确
    if(check_obj.search(/^[\w-\.]+@[\w-\.]+(\.[A-Za-z]{2,3})+$/)>=0){
        return true;
      }else{
        return false;
      }
}
function checkPassword(check_obj){
	
	if(check_obj.search(/[\.]/)>=0){
        return true;
      }else{
        return false;
      }
}
function checkNickname(check_obj)
{//检查昵称是否合法
	var pattern_cn = /^[^0-9]{1}([\u4E00-\u9FA5]|[a-zA-Z0-9_])*$/;
	if(pattern_cn.test(check_obj)){
//		if((check_obj.slice(check_obj.length-1)=="_"||check_obj.slice(0,1)=="_")){
//			return false;
//		}
		return true;
	}else{
		return false;
	}
}
function checkRealname(check_obj)
{//检查真实姓名是否合法
	var pattern_cn = /^([\u4E00-\u9FA5])*$/;
	if(pattern_cn.test(check_obj)){
//		if((check_obj.slice(check_obj.length-1)=="_"||check_obj.slice(0,1)=="_")){
//			return false;
//		}
		return true;
	}else{
		return false;
	}
}
function checkUsername(check_obj)
{//检查昵称是否合法
	var pattern_cn = /^[a-z]{1}([a-z0-9])*$/;
	if(pattern_cn.test(check_obj)){
		if((check_obj.slice(check_obj.length-1)=="_"||check_obj.slice(0,1)=="_")){
			return false;
		}
		return true;
	}else{
		return false;
	}
}

function checkTel(check_obj)
{//检查电话格式是否正确
	var pattern_cn = /^([-0-9])*$/;
	if(pattern_cn.test(check_obj)){
		return true;
	}else{
		return false;
	}
}

function checkMobile(check_obj)
{//检查手机格式是否正确
	var pattern_cn = /1[3,5]\d{9}$/;
	if(pattern_cn.test(check_obj)){
		return true;
	}else{
		return false;
	}
}
function checkQQ(check_obj)
{//检查QQ号码是否正确
	var pattern_cn = /^\d{5,10}$/;
	if(pattern_cn.test(check_obj)){
		return true;
	}else{
		return false;
	}
}

function checkPostcode(check_obj)
{//检查邮政编码格式是否正确
	var pattern_cn = /\d{6}/;
	if(pattern_cn.test(check_obj)){
		return true;
	}else{
		return false;
	}
}


//密码强度;
function PasswordStrength(showed){	
	this.showed = (typeof(showed) == "boolean")?showed:true;
	this.styles = new Array();	
	this.styles[0] = {backgroundColor:"#EBEBEB",borderLeft:"solid 1px #FFFFFF",borderRight:"solid 1px #BEBEBE",borderBottom:"solid 1px #BEBEBE"};	
	this.styles[1] = {backgroundColor:"#FF4545",borderLeft:"solid 1px #FFFFFF",borderRight:"solid 1px #BB2B2B",borderBottom:"solid 1px #BB2B2B"};
	this.styles[2] = {backgroundColor:"#FFD35E",borderLeft:"solid 1px #FFFFFF",borderRight:"solid 1px #E9AE10",borderBottom:"solid 1px #E9AE10"};
	this.styles[3] = {backgroundColor:"#95EB81",borderLeft:"solid 1px #FFFFFF",borderRight:"solid 1px #3BBC1B",borderBottom:"solid 1px #3BBC1B"};
	
	this.labels= ["弱","中","强"];

	this.divName = "pwd_div_"+Math.ceil(Math.random()*100000);
	this.minLen = 5;
	
	this.width = "150px";
	this.height = "16px";
	
	this.content = "";
	
	this.selectedIndex = 0;
	
	this.init();	
}
PasswordStrength.prototype.init = function(){
	var s = '<table cellpadding="0" id="'+this.divName+'_table" cellspacing="0" style="width:'+this.width+';height:'+this.height+';float:left;">';
	s += '<tr>';
	for(var i=0;i<3;i++){
		s += '<td id="'+this.divName+'_td_'+i+'" width="33%" align="center"><span style="font-size:1px">&nbsp;</span><span id="'+this.divName+'_label_'+i+'" style="display:none;font-family: Courier New, Courier, mono;font-size: 12px;color: #000000;">'+this.labels[i]+'</span></td>';
	}	
	s += '</tr>';
	s += '</table>';
	this.content = s;
	if(this.showed){
		document.write(s);
		this.copyToStyle(this.selectedIndex);
	}	
}
PasswordStrength.prototype.copyToObject = function(o1,o2){
	for(var i in o1){
		o2[i] = o1[i];
	}
}
PasswordStrength.prototype.copyToStyle = function(id){

	this.selectedIndex = id;
	for(var i=0;i<3;i++){
		if(i == id-1){
			this.$(this.divName+"_label_"+i).style.display = "inline";
		}else{
			this.$(this.divName+"_label_"+i).style.display = "none";
		}
	}
	for(var i=0;i<id;i++){
		this.copyToObject(this.styles[id],this.$(this.divName+"_td_"+i).style);			
	}
	for(;i<3;i++){
		this.copyToObject(this.styles[0],this.$(this.divName+"_td_"+i).style);
	}
}
PasswordStrength.prototype.$ = function(s){

	return document.getElementById(s);
}
PasswordStrength.prototype.setSize = function(w,h){
	this.width = w;
	this.height = h;
}
PasswordStrength.prototype.setMinLength = function(n){
	if(isNaN(n)){
		return ;
	}
	n = Number(n);
	if(n>1){
		this.minLength = n;
	}
}
PasswordStrength.prototype.setStyles = function(){
	if(arguments.length == 0){
		return ;
	}
	for(var i=0;i<arguments.length && i < 4;i++){
		this.styles[i] = arguments[i];
	}
	this.copyToStyle(this.selectedIndex);
}
PasswordStrength.prototype.write = function(s){
	if(this.showed){
		return ;
	}
	var n = (s == 'string') ? this.$(s) : s;
	if(typeof(n) != "object"){
		return ;
	}

	n.innerHTML = this.content;
	this.copyToStyle(this.selectedIndex);
}
PasswordStrength.prototype.update = function(s){

	if(s.length < this.minLen){
		this.copyToStyle(0);
		return;
	}
	var ls = -1;
	if (s.match(/[a-z]/ig)){
		ls++;
	}
	if (s.match(/[0-9]/ig)){
		ls++;
	}
 	if (s.match(/(.[^a-z0-9])/ig)){
		ls++;
	}
	if (s.length < 6 && ls > 0){
		ls--;
	}
	 switch(ls) { 
		 case 0:
			 this.copyToStyle(1);
			 break;
		 case 1:
			 this.copyToStyle(2);
			 break;
		 case 2:
			 this.copyToStyle(3);
			 break;
		 default:
			 this.copyToStyle(0);
	 }
}


//必须加载checkuserinput.js以后加载