
//检查登录状态
function chkAccStatus(tag,num){
	var login_id_tem=getCookie("username");
	if(login_id_tem){
		var username=unescape(getCookie('username'));
		gID('username').innerHTML=username?'尊敬的：'+username :"游客";
		showDiv(tag,num,1);
	} else {
		showDiv(tag,num,0);
	}
}

//登录表单提交
function checkLogin(theform){
	
	var gourl=document.location.href;

	var tmparr=gourl.split('/');

	if(tmparr[4]=='login.php'){
		gourl=document.referrer;
	}

	if(gourl=='/enter/login.php' || gourl==''){
		gourl='/';
	}
	
	var login_id_tem=getCookie("username");
	if(login_id_tem){
		alert("您已登陆，请进入游戏");
		location.href=gourl;
	}
	
	var username=gID("username");
	var password=gID("password");
	if(username.value == ""){
		alert("帐号不能为空！");
		username.focus();
		return false;
	}
	if(password.value == ""){
		alert("密码不能为空！");	
		//password.focus();
		//$("#password").focus();
		return false;
	}

	//var paras='act=1&e=index&username='+encodeURIComponent(username.value)+'&login_pwd='+encodeURIComponent(password.value);
	var paras='act=1&e=index&username='+username.value+'&login_pwd='+password.value;
	subLogin(gourl,paras);
}

function subLogin(gourl,paras) {
	$.ajax(
    {
        type:"POST",
        url:"/enter/login.php",
		  data:paras,
        success: function(result){  
					if(result=='ok'){
						location.href=gourl;
					}else if(result=='code error'){
						alert('验证码错误!');
						return false;
					}else if(result=='pwd error'){
						alert('您输入的密码错误！');
						return false;
					}else if(result=='value error'){
						alert('您输入的帐号密码有误！');
						return false;
					}else{
						alert('登录失败！');
					}
        },
        error:function(){
            alert('登陆失败,请检查你的用户名密码!');
            return false;
        }
    });
}

//退出登录
function logout(){
	$.ajax(
    {
        type:"POST",
        url:"/enter/login.php",
		  data:'act=logout',
        success: function(result){  
				location.href=document.location;
        },
        error:function(){
            location.href=document.location;
        }
    });
}


if(getCookie("flb")!=''){
	document.title=getCookie("flb");
}
