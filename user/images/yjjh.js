function setAccStatus(){
	var login_game_info = unescape(getCookie('login_game_info'));
	gID('login_game_info').innerHTML=login_game_info;
}


function setLogin(tag){
	if(getCookie("loginreg")){
		return false;
	}
	if(tag>0){
		setClass("regmenu0","login1111 fl");
		setClass("regmenu1","login1111 col1 fl");
	} else {
		setClass("regmenu0","login1111 col1 fl");
		setClass("regmenu1","login1111 fl");
	}
	showDiv('regLogTab',3,tag);
}

function setNews(tag,url){
	for(var i=0;i<3;i++){
		setClass("newsLi"+i,"");
	}
	gID("newsUrl").href=url;
	setClass("newsLi"+tag,'newstop11');
	showDiv('newsLiLst',3,tag);
}


function setImg(tag,url){
	if(tag==1){
		setClass("imgsLi0","wjzp111 fl");
		setClass("imgsLi1","wjzp112 fl");
	} else {
		setClass("imgsLi0","wjzp112 fl");
		setClass("imgsLi1","wjzp111 fl");
	}
	showDiv('imgsLiLst',2,tag);
	gID("imgUrl").href=url;
}

function regChk(){
	var login_name = gID('login_name').value;
	if(login_name.length == 0) {
		alert("帐号不能为空！");
		gID('login_name').focus();
		return false;
	} else if(login_name.length < 2 || login_name.length > 26) {
		alert("帐号长度不符合要求！必须是2～14个字符");
		gID('login_name').focus();
		return false;
	}

	var login_pwd = gID('login_pwd').value;
	if(login_pwd.length == 0) {
		alert("密码不能为空！");
		gID('login_pwd').focus();
		return false;
	}
	
	var relogin_pwd = gID('relogin_pwd').value;
	if(relogin_pwd.length == 0) {
		alert("重输密码不能为空！");
		gID('relogin_pwd').focus();
		return false;
	}
	
	if (relogin_pwd != login_pwd){
		alert("两次密码不一致");
		gID('relogin_pwd').focus();
		return false;
	}		
	
	var email = gID('email').value;
	if(email.length == 0) {
		alert("邮箱不能为空！");
		gID('email').focus();
		return false;
	}
	
	gID('frmReg').submit();
}
