<?php
if(!isset($_SESSION)){
    session_start();
}
	if($_POST["checkcode"]!=$_SESSION['checkcode']){
		Header("Location:../../error.html?err2");	 		
	}else{	
		require_once("./interface.php");	
		$code=$_POST["code"];
		$passport = $_POST["passport"];
		$password = $_POST["password"];
		$passsucc = false;
		$loginType=0;	
		require_once("./passport/uuyx.php");		
		if ($passsucc==false){
			Header("Location:../../error.html?err0");	    	
		}else if (1==intval(sql_fetch_one_cell("select count(1) from test_code where passport = '$passport' "))){//该账号已激活
			Header("Location:../../error.html?err4");		
		}else if (0==intval(sql_fetch_one_cell("select count(1) from test_code where code = '$code' "))){//没有该激活码
			Header("Location:../../error.html?err1");		
		}else if (1==intval(sql_fetch_one_cell("select count(1) from test_code where code = '$code' and passport is not null"))){//已被激活
			Header("Location:../../error.html?err3");
		}else{					
			sql_query("update test_code set passport ='$passport' where code = '$code'");
			Header("Location:../../right.html");		
		}	
	}
			
?>