<?php
	/**
	 * @author 方鸿鹏
	 * @method 获得一个科技信息通过账号，君主名或者用户id
	 * @param $passport 用户账号 $name 君主名 $uid 用户id
	 * @return 
	 */
	if (!defined("MANAGE_INTERFACE")) exit;	
	if (!isset($passport)&&!isset($name)&&!isset($uid))exit("param_not_exist");
	if(isset($passport)){
		$user = sql_fetch_one("select uid,passport,name from sys_user where passport='$passport'");
	}
	else if(isset($name)){
		$user = sql_fetch_one("select uid,passport,name from sys_user where name='$name'");
	}
	else{
		$user = sql_fetch_one("select uid,passport,name from sys_user where uid='$uid'");
	}
	$sql_error = mysql_error();
	if(!empty($user)&&empty($sql_error)){
		$technology = sql_fetch_rows("select st.uid, st.tid, ct.name, st.level from sys_technic st right join cfg_technic as ct on st.tid= ct.tid and st.uid = '$user[uid]'");
		$sql_error = mysql_error();
		if(!empty($technology)&&empty($sql_error)){
			$ret[] = $user;
			$ret[] = $technology;
		}
		else{
			$ret = 'no data';
		}
	}
	else{
		$ret = 'no data';
	}
?>