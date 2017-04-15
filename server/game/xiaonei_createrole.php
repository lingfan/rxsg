<?php
	require_once("UserFunc.php");
	require_once("xiaonei/xiaonei-util.php");
	$key = "U(mo*O6AeySJs3!2KC2ex0K*hxB3OOAL";	
	$sign= md5($_GET["passport"].$key.$_GET["time"]);
	if($sign != $_GET["sign"]){
		echo "0/验证错误";
		exit;
	}
	if (!empty($_GET["passport"]) && $_GET["type"] == 1){
		$passport=$_GET["passport"];
		$uid=sql_fetch_one_cell("select uid from sys_user where passport='$passport'");
		if ($uid==""){
			exit("0");//无玩家
		}else {
			$name=sql_fetch_one_cell("select name from sys_user where passport='$passport'");
			if(empty($name)){
			exit("2");//有玩家无建城
			}
			exit("1");//有玩家有建城
			
		}
	}else if($_GET["type"] == 2){
		$arr=array();
		$input_name = urldecode($_GET["input_name"]);
		$arr[]=$input_name;
		$arr[]="新城池";
		$arr[]=$_GET["input_zhou"];
		$arr[]=substr($input_name,0,1);
		$arr[]=$_GET["sex"];
		$arr[]=mt_rand(1,9);
		$arr[]=123456;
		$passport=$_GET["passport"];
		$uid=sql_fetch_one_cell("select uid from sys_user where passport='$passport'");
		if ($uid=="")  //还没有创建角色
		{
			$userCount = sql_fetch_one_cell("select count(*) from sys_user where uid > 1000");
			$maxUserCount = sql_fetch_one_cell("select value from mem_state where state=100");
			if ($userCount >= $maxUserCount)
			{
				echo "0/本服务器人数已满";
				exit;
			}
			$state = 3;     //刚创建角色，还没有城池
			// passtype 改变，xiaonei改成renren
			$uid = sql_insert("insert into sys_user (`passtype`,`passport`,`group`,`state`,`regtime`,`domainid`,`honour`) values ('renren','$passport','0','$state',unix_timestamp(),'0',0)");
			sql_query("insert into sys_online (`uid`,`lastupdate`,`onlineupdate`,`onlinetime`) values ('$uid',unix_timestamp(),unix_timestamp(),0)");
		}
		try{
			createRole($uid,$arr);
			exit('1');
		}catch(Exception $e){
			$ret=$e->getMessage();
			//$ret=iconv("UTF-8","GB2312//IGNORE",$ret);
			exit('0/'.$ret);
		}
		//exit;
	}
?>