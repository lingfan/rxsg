<?php
/**
 * @author 方鸿鹏
 * @method 获取赤壁用户信息
 * @param $passport $name $server
 * @return 
 */

	if (!defined("MANAGE_INTERFACE")) exit;
	
	if (!isset($passport)){exit("param_not_exist");}
	if (!isset($name)){exit("param_not_exist");}
	if (!isset($server)){exit("param_not_exist");}
	
	if(!empty($passport)){
		$data = sql_fetch_one_cell("select u.uid from sys_user u, sys_servers s 
		where u.from_serverid=s.from_serverid and u.passport='$passport' and s.server_name='$server'","chibinet");
		$sql_error = mysql_error();
		if(!empty($data)&&empty($sql_error)){
			$ret['data'] = $data;
		}
		else{
			$ret['data'] = array();
			if(empty($data))
				$ret['error'] = "服务器<font color='red'>".$server."</font>下账号<font color='red'>".$passport."</font>不存在！";
			else
				$ret['error'] = $sql_error;
		}
	} 
	else {
		if(!empty($name)){
			$data = sql_fetch_one_cell("select u.uid from sys_user u, sys_servers s 
			where u.from_serverid=s.from_serverid and u.name='$name' and s.server_name='$server'","chibinet");
			$sql_error = mysql_error();
			if(!empty($data)&&empty($sql_error)){
				$ret['data'] = $data;
			}
			else{
				$ret['data'] = array();
				if(empty($data))
					$ret['error'] = "服务器<font color='red'>".$server."</font>下君主名<font color='red'>".$name."</font>不存在！";
				else
					$ret['error'] = $sql_error;
			}
		}
	}
?>