///根据不同表单酌情修改checkArr数组

var tipimg="";
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
		document.getElementById('prompt_info').innerHTML=divBegin+ kg + msgerr + "提示问题长度必须在6-18位。"+divEnd;
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

			warnDiv.innerHTML = msg_Info+tipimg;
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
function checkUsername(check_obj)
{//检查昵称是否合法
	var pattern_cn = /^[a-z]{1}([a-z0-9@\._])*$/;
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
	var s = '<table cellpadding="0" id="'+this.divName+'_table" cellspacing="0" style="width:'+this.width+';height:'+this.height+';">';
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

//加载所有建设队列
function LoadCheckArr()
{
    for (var xx=0;xx<checkArr.length;xx++)
    {
		
	    var inputObj = document.getElementById(checkArr[xx][0]);
	    if (inputObj)
	    {
		    inputObj.onfocus = function(){document.getElementById(this.id+'_info').className = style_FC;};
		    inputObj.onblur = function(){ formCheckByArr(this,checkArr,'div',false) };
		    if(inputObj.getAttribute("type")=="password"){
			    inputObj.oncut = function(){return false;};
			    inputObj.onpaste = function(){return false;};
			    inputObj.oncopy = function(){return false;};
		    }
    		
		    if(checkArr[xx][0]=='_user_passwd'){
			    inputObj.onkeyup = function(){
				    ps.update(this.value);	
			    };
		    }
	    }
    	
	    var warnDiv = document.getElementById(checkArr[xx][2]);
	    if (warnDiv)
	    {
		    warnDiv.innerHTML = checkArr[xx][6];
		    warnDiv.className = checkArr[xx][3];
	    }

    }
}

