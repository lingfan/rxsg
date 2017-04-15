<?php
	/**
	 *请求游戏帐号状态接口
	 */
	 require_once("./utils.php");
	 
	 /**
	  *功能：获取游戏帐号的状态信息
	  *参数：$uid：用户id $username：帐号 $key：密钥
	  *返回：array{
	  *			username //用户名
	  *			status //帐号状态
	  *			dtime //处理时间 unix时间戳
	  *			reason //处理理由
	  *			rtime //帐号恢复时间 unix时间戳
	  *			time //接口当前运行的时间, unix时间戳
	  *			sign //签名串: 取以上所有参数，按字典排序，加上key, 再做md5运算
	  *      }
	  */
	function getUserState($uid, $username, $key)
	{
		$data = array();
		$user_state = sql_fetch_one("select *,unix_timestamp() as cur_time from sys_user_state where uid='$uid' and forbiend>unix_timestamp() limit 1");
		if(!empty($user_state)){
			$data['username'] = $username;
			$data['status'] = '服务冻结';
			$data['dtime'] = $user_state['forbistart'];
			$data['reason'] = "";
			$data['rtime'] = $user_state['forbiend'];
			$data['time'] = $user_state['cur_time'];
			$data['sign'] = md5($data['dtime'].$data['reason'].$data['rtime'].$data['status'].$data['time'].$username.$key);
		}
		else{
			$data['username'] = $username;
			$data['status'] = '服务正常';
			$data['dtime'] = "";
			$data['reason'] = "";
			$data['rtime'] = "";
			$data['time'] = time();
			$data['sign'] = md5($data['dtime'].$data['reason'].$data['rtime'].$data['status'].$data['time'].$username.$key);
		}
		return $data;
	}
	
	$error_code = 0;//错误代码: 0—表示成功，其它示失败。错误码可自行定义
	$error_body = "";//错误原因说明
	$data = "";//帐号状态数据
	$ret = array();
	//获取参数
	$remotetime = $_GET['time']; //请求发出的时间, unix时间戳
	$passport = $_GET['username']; //用户名
	$sign = $_GET['sign']; //签名串: md5(time=时间&username=用户名&key=密钥)
	
	//验证参数
	if(empty($remotetime)||empty($passport)||empty($sign)){
		$error_code = 1;
		$error_body = "param_error";
	}
	else{
		//请求超时验证
		$delay=time()-$remotetime;
    	if(!($delay>-3600&&$delay<3600)){
    		$error_code = 1;
    		$error_body = "request_timed_out";
    	}
    	else{
    		//签名验证
    		$key = "bb02IuofhmkQTQWcDoWl/gLz3X969i0+GkMdXESuekSppMqJlgvSSEDklqPMziBfegUUGMkXUyp7Is2PPxvIiNLy2eltBMrvDeFhvATPeEHaUcKdV5cNUAmVNH+E";
    		if (strtolower($sign) != strtolower(md5($remotetime.$passport.$key))){
    			$error_code = 1;
    			$error_body = "sign_error";
    		}
    		else{
    			//获取数据
    			$user = sql_fetch_one("select * from sys_user where uid>1000 and passport='$passport' limit 1");
    			if(empty($user)){
    				$error_code = 1;
    				$error_body = "user_not_exist";
    			}
    			else{
    				$data = getUserState($user['uid'], $passport, $key);
    				$error_body = "";
    			}
    		}
    	}
	}
	
	$ret['error_code'] = $error_code;
	$ret['error_body'] = $error_body;
	$ret['data'] = $data;
	$json = json_encode($ret);
	echo urldecode($json);
?>