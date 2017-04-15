<?php
require_once("./common.php");

  if ($loginType == 0){
	 $pass=sql_fetch_one("select * from test_passport where passport='$passport'");
	 if(eregi("^[0-9]+$",$passport)) throw new Exception("账号开头必须是字母！");
	 if(strlen($password)<4) throw new Exception("密码不能少于4个字符！");
	 if(!$pass){
		 throw new Exception("错误的帐号！请在首页注册！");
	    }
	 if($pass['password']<>$password&&!$passsucc) throw new Exception("密码不正确！");
	 //if($pass['pass']<>1&&!$passsucc) throw new Exception("您的账号未激活，请联系客服激活！");
	 $passsucc=true;
    }
?>