<?php
	require_once("./utils.php");
	$type = $_GET["type"];
	if ($type==0){
		$passtype = $_GET["passtype"];
		$passport = $_GET["passport"];
		$passport=urldecode($passport);		
		$password = $_GET["password"];				
		if (!($passport && $passport && $passport)) exit(0); 
		$passsucc = false;
		@include ("./passport/$passtype.php");
		if ($passsucc) exit("loginNow(1);");
		else exit("loginNow(0);");
	}else if ($type==1){
		require_once("./Login.php");				
		$announce = Login::getLoginAnnouncement(array());
		$announce=addslashes($announce);
        $announce=str_replace(array("\r\n","\n","\r"),array("\n"),$announce);		
		exit("setAnnounce('".$announce."');");
	}
?>