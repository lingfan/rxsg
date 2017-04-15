<?php
if ($loginType == 0)
{
	$key = "bfd51210-0fd3-424b-af94-557df4877239";
	$key_2 = 'sdfh56HFGjklho';
	$url = "http://wif.12ha.com/webuserchecklogin/webgame_rxsg_userchecklogin_get.aspx?";
	$url .= "pusername=".$passport."&ppsw=".md5($password)."&ptype=wg&sign=".md5($passport.md5($password)."wg".$key);
	$ret = 1;
	if ($ret == "1"){
		file_put_contents("test.txt",'1113');
		$tnd = time();
		$sign = md5($passport.$password.'jinku_1'.$tnd.$key_2);
		$redirect_url = "http://127.0.0.1/index.php?passport=".$passport."&union=jinku_1&password=".$password."&tnd=".$tnd."&sign=".$sign;
		header("location:".$redirect_url);
		 "<script type=\"text/javascript\">\nwindow.location.href = \"$redirect_url\";\n</script>";
	}else{
		header("location:/index.php");
	}
}
?>