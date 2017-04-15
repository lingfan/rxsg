function GetCookieVal(offset)
//获得Cookie解码后的值
{
	var endstr = document.cookie.indexOf (";", offset);
	if (endstr == -1)
		endstr = document.cookie.length;
	return unescape(document.cookie.substring(offset, endstr));
}
function SetCookie(name, value)
//设定Cookie值
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
function DelCookie(name)
//删除Cookie
{
var exp = new Date();
exp.setTime (exp.getTime() - 1);
var cval = GetCookie (name);
document.cookie = name + "=" + cval + "; expires="+ exp.toGMTString();
}

//获得Cookie的原始值
function GetCookie(name)
{
	var arg = name + "=";
	var alen = arg.length;
	var clen = document.cookie.length;
	var i = 0;
	while (i < clen)
	{
		var j = i + alen;
		if (document.cookie.substring(i, j) == arg)
			return GetCookieVal (j);
		i = document.cookie.indexOf(" ", i) + 1;
		if (i == 0) break;
	}
	return null;
}

function isSaveUsername()
{
	return GetCookie("saveusername");
}

function isSavePassword()
{
	return GetCookie("savepassword");
}
function getSavedUsername()
{
	return GetCookie("username");
}
function getSavedPasstype()
{
	return GetCookie("passtype");
}
function getSavedPassword()
{
	return GetCookie("password");
}
function isFromWebPage()
{
	return "true";
}
function getWangye173Auth()
{
	return GetCookie("passport_username");
}
function setSaveUsername(save,username,passtype)
{
	if (save)
	{
		SetCookie("saveusername","true",3600*24);
		SetCookie("username",username,3600*24);
		SetCookie("passtype",passtype,3600*24)
		return true;
	}
	else
	{
		DelCookie("saveusername");
		DelCookie("username");
		return false;
	}
}
function setSavePassword(save,password)
{
	if (save)
	{
		SetCookie("savepassword","true",3600*24);
		SetCookie("password",password,3600*24);
	}
	else
	{
		DelCookie("savepassword");
		DelCookie("password");
	}
}

var x,y,w,h;
var xoffset=72;
var yoffset=72;
var titlestr="热血三国";

function moveIFrame(x2,y2,w2,h2) {
	
	w=parseInt(w2);
	x=parseInt(x2);
	y=parseInt(y2);
	h=parseInt(h2);
	var frameRef=document.getElementById("content");
	xoffset = -6;
	if(navigator.appName=='Netscape'){
		xoffset = -2;
	}
	if(document.body.clientWidth>=1000){
		frameRef.style.left=(x+(document.body.clientWidth-1000)/2-xoffset)+"px";
	}else {
		frameRef.style.left=310+"px";
	}
	frameRef.style.top=y+yoffset+"px";
	frameRef.width=w+"px";
	frameRef.height=h+"px";
}
function resetiframe(){
	if (!x) return;
	var frameRef=document.getElementById("content");
	if(document.body.clientWidth>=1000){
		frameRef.style.left=x+(document.body.clientWidth-1000)/2-xoffset+"px";
	}else {
		frameRef.style.left=310+"px";
	}
	frameRef.style.top=y+yoffset+"px";
}
function getOs()
{
    var OsObject = "";
   if(navigator.userAgent.indexOf("MSIE")>0) {
        return "MSIE";
   }
   if(navigator.userAgent.indexOf("Firefox")>0){
        return "Firefox";
   }
   if(navigator.userAgent.indexOf("Safari")>0) {
        return "Safari";
   } 
   if(navigator.userAgent.indexOf("Camino")>0){
        return "Camino";
   }
   if(navigator.userAgent.indexOf("Gecko/")>0){
        return "Gecko";
   }
  
} 
function setIFrameContent( contentSource )
{
	document.getElementById("content").src = contentSource;
}

function setIFrameScroll(bscrolling)
{
	if (bscrolling)
		document.getElementById("content").style.overflow="auto";
	else
		document.getElementById("content").style.overflow="hidden";
}


function hideIFrame()
{
	document.getElementById("content").src ='about:blank';
	moveIFrame(0,0,1,1);
	document.getElementById("content").style.visibility="hidden";
}

function showIFrame()
{
	document.getElementById("content").style.visibility="visible";
}
//这个函数用于解决在IE9中出现关闭时候出现提示对话框的现象
function closethiswindows() 
{ 
	var isEZ = false;

	try { isEZ = /EZ/.test(window.external.BrowserVersion); } catch(e) {}

	if (isEZ) 
	{ window.external.close(); } 
	else 
	{
	    window.opener = null;
	    window.close();
	}
} 

//这个函数用于在IE中显示Flash时候自动聚焦
function focusflash()
{
	var qqobj = getFlashMovieObject( "BloodWar" ) ;
	if ( qqobj) { qqobj.focus(); }
}

function setFocusEvents() {    
	var isIE = (navigator.appName == "Microsoft Internet Explorer");
	if (isIE) {
		document.onfocusout = function() { onWindowBlur(); }
		document.onfocusin = function() { onWindowFocus(); }
	} else {
		window.onblur = function() { onWindowBlur(); }
		window.onfocus = function() { onWindowFocus(); }
	}
}

function onWindowFocus() {
   var qqobj = getFlashMovieObject( "BloodWar" ) ;
   if ( qqobj && qqobj.winShow){
   	  qqobj.winShow();
   }  
}

function onWindowBlur() {
   var qqobj = getFlashMovieObject( "BloodWar");
   if ( qqobj && qqobj.winHide ){
   	  qqobj.winHide();   
   }
}
function getFlashMovieObject(movieName){
	if  (document[movieName]){
		return document[movieName];
	}else if (window[movieName]){
		return window[movieName];
	}else if(document.embeds && document.embeds[movieName]){
		return document.embeds[movieName];
	}else{
		return document.getElementById(movieName);
    }
}
function getEndPoint(){
	return "/server/amfphp/gateway.php";
}
//setFocusEvents();

