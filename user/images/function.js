//获取表单对象
function gID(getID){
	return document.getElementById(getID);
}

//写cookie
function setCookie(cookieName, cookieValue, seconds) {
	var expires = new Date();
	expires.setTime(expires.getTime() + parseInt(seconds));
	document.cookie = escape(cookieName) 
		+ '=' + escape(cookieValue)
		+ (seconds ? '; expire=' + expires.toGMTString() : '')
		+ '; path=/; domain=91wan.com;';
}

//获取cookie
function getCookie(cname) {
	var cookie_start = document.cookie.indexOf(cname);
	var cookie_end = document.cookie.indexOf(";", cookie_start);
	return cookie_start == -1 ? '' : decodeURI(document.cookie.substring(cookie_start + cname.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length)));
}

//回车提交
function InputKeyPress(aFrmObj){
	var currKey=0,CapsLock=0; 
	var e = arguments[1];
	e=e||window.event; 
	var kCode=e.keyCode||e.which||e.charCode; 
	if(kCode == '13'){
		checkLogin(aFrmObj);
	}
}

//添加收藏夹
function addBookmark(url,title){
	if (window.sidebar) { 
		window.sidebar.addPanel(title, url,""); 
	} else if( document.all ) {
		window.external.AddFavorite( url, title);
	} else if( window.opera && window.print ) {
		return true;
	}
}


//设为首页
function setHomepage(url,title){
	if (document.all)
    {
		document.body.style.behavior='url(#default#homepage)';
		document.body.setHomePage(url);
    }
    else if (window.sidebar)
    {
		if(window.netscape)
		{
			 try
			{ 
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect"); 
			 } 
			 catch (e) 
			 { 
				alert( "该操作被浏览器拒绝，如果想启用该功能，请在地址栏内输入 about:config,然后将项 signed.applets.codebase_principal_support 值该为true" ); 
			 }
		}
    var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components. interfaces.nsIPrefBranch);
    prefs.setCharPref(title,url);
	}
}


//获取渠道id
var agentIDArray = Array(
"160^baidu.com","107^google.com.hk","123^hao123.com","210^vs","18773^114la.com",
"18778^dh818.com","18778^2345.com","16090^go2000.cn","16090^365j.com","16090^qq5.com",				 
"103^1616.net","104^uusee.net","106^9991.com","108^v2233.com","109^kzdh.com",								 
"18773^46.com","18046^345ba.com","18050^zhaodao123.com","18012^duote.com",
"17504^91danji.com","16515^quxiu.com","18762^duotegame.com","360^360.cn","19221^haouc.com",
"53107^17173.com","53108^86wan.com","53109^966.com","53110^yzz.cn","53111^07073.com","53112^cwebgame.com",
"53113^2366.com","53114^766.com","53115^e3ol.com","53116^reyoo.net","53117^ccjoy.com","53118^265g.com",
"53119^duowan.com","53120^pcgames.com.cn","53121^maituan.com","53122^6dan.com","53123^9u8u.com",
"53125^92pk.com","53126^kkpk.com","53127^wangye2.com","53128^popwan.com","53129^5068.com","53130^521g.com",
"53131^juxia.com","53132^52kl.net","53133^131.com","53134^game.163.com","53135^e004.com","53136^173eg.com",
"53137^uuu9.com","53138^games.sina.com.cn","53139^fm4399.com"
);					 
function getAgentID(){
	lastUrl = document.referrer;
	var agent_id =0 ;
	agent_id = getQueryString("agent_id");
	if (!agent_id){
		var agenttmp = "";
		for(var i = 0 ;i<agentIDArray.length;i++){
			agenttmp = agentIDArray[i].split("^");
			if (lastUrl.indexOf(agenttmp[1])!= -1){ 
				agent_id = agenttmp[0]; 
			}
		}
	}

	if (agent_id>0){
		setCookie("agent_id",agent_id,3600);
	}	

	var placeid = getQueryString("placeid");
	if (placeid){
		setCookie("placeid",placeid,3600);
	}
}

//组对象显示隐藏
function showDiv(tag,num,tid){
	for(var i=0;i<num;i++){
		gID(tag+i).style.display='none';
	}
	if(tid!=null){
		gID(tag+tid).style.display="block";
	}
}

function setClass(tag,classname){
	gID(tag).className=classname;
}

//版权信息年份
function getCurrYear(aYearID){  //font html
	var myDate = new Date();
	var curYear = myDate.getFullYear(); //获取完整的年份
	try{
		yearObj = document.getElementById(aYearID);
	}catch(e){;}
	if(yearObj){
		yearObj.innerHTML = curYear;	
	}
}

//上传图片打开新窗口
var mywin = null;
function NewWindow(mypage){
var myname=arguments[1]?arguments[1]:"上传图片";
var w=arguments[2]?arguments[2]:533;
var h=arguments[3]?arguments[3]:400;
var LeftPosition = (screen.width) ? (screen.width-w)/2 : 0;
var TopPosition = (screen.height) ? (screen.height-h)/2 : 0;
settings ='height='+h+'px,width='+w+'px,top='+TopPosition+',left='+LeftPosition+',toolbar=no, menubar=no, scrollbars=no, resizable=no,location=no, status=no';
mywin = window.open(mypage,myname,settings);
mywin.focus();
}


//获取传参值
function	getQueryString(queryStringName){
	var	returnValue="";
	var	URLString=new	String(document.location);
	var	serachLocation=-1;
	var	queryStringLength=queryStringName.length;
	do	{
		serachLocation=URLString.indexOf(queryStringName+"\=");
		if	(serachLocation!=-1) {
			if	((URLString.charAt(serachLocation-1)=='?')	||	(URLString.charAt(serachLocation-1)=='&')) {
				URLString=URLString.substr(serachLocation);
				break;
			}
			URLString=URLString.substr(serachLocation+queryStringLength+1);
		}
	}
	while	(serachLocation!=-1)
	if	(serachLocation!=-1){
		var	seperatorLocation=URLString.indexOf("&");
		if	(seperatorLocation==-1)	{
			returnValue=URLString.substr(queryStringLength+1);
		}
		else{
			returnValue=URLString.substring(queryStringLength+1,seperatorLocation);
		}	
	}
	returnValue   =   returnValue.replace(/#/g,''); 
	return	returnValue;
}

getAgentID();

try{
    ref = escape(document.referrer);
}catch(e){}

if(ref.indexOf('91wan.com')==-1 && ref!=''){
	setCookie("from_url",ref,3600);
}