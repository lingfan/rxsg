<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html" charset="utf-8" />
	<title>添加账号</title>
	<style>
		body {TEXT-ALIGN: center;}
		#center { MARGIN-RIGHT: auto;
				MARGIN-LEFT: auto;
				vertical-align:middle;
				MARGIN-TOP : 100;
				line-height:2;
				}
	</style>
</head>

<body>	
<div id="center">
<form method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>">
	增加新账号：<input type="text" name="passport"/><br>
	增加新密码：<input type="text" name="password"/><br>
	管理员账号：<input type="text" name="admin"/><br>
	管理员密码：<input type="password" name="adPassword"/><br>
	<input type="submit" value="提交" />
</form>
</div>
<?php
	require_once("./dbinc.php");
	$param = $_POST;
	if(count($param)>0)
	{
		$passport = addslashes(trim($param["passport"]));
		$password = addslashes(trim($param["password"]));
		$admin = addslashes(trim($param["admin"]));
		$adPassword = addslashes(trim($param["adPassword"]));
		
		try
		{
			$mdPassword = md5($adPassword);
			$adminInfo = sql_fetch_one("select * from test_passport where passport='$admin' and password='$mdPassword'");
			if(empty($adminInfo)) throw new Exception("管理员账号或者密码错误！");
			$passportIsExist = sql_fetch_one("select * from test_passport where passport='$passport'");
			if(!empty($passportIsExist)) throw new Exception("您新加的账号已经存在！");
			sql_query("insert into test_passport(`passport`,`password`) values('$passport','$password')");
			echo "<font color='#ff0000'>添加成功！</font>";
		}catch(Exception $e)
		{
			echo "<font color='#ff0000'>".$e->getMessage()."</font>";
		}
	}	
?>

</body>
</html>
