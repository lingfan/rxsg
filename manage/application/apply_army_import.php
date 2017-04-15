<?php

	if (!defined("MANAGE_INTERFACE")) exit;
	
	if (!isset($cityname)){exit("param_not_exist");}
	if (!isset($add_sid)){exit("param_not_exist");}
	if (!isset($add_count)){exit("param_not_exist");}
	if (!isset($add_reason)){exit("param_not_exist");}
	if (!isset($passport)){exit("param_not_exist");}
	
	$user = sql_fetch_one("select * from sys_user where passport='$passport'");  
	$uid=$user['uid'];
	$add_name = sql_fetch_one_cell("select `name` from cfg_soldier where `sid`='$add_sid'");  
	$user_name=$user['name'];
	if(!empty($add_count) && empty($add_reason)){
		echo "<strong>没有理由不能申请！[<a href=javascript:history.back()>返回</a>]</strong>";
	    exit;
	}

	if(!empty($add_sid) && !empty($add_count) && !empty($add_reason)){	    
	    //添加log
	    $opration_content = '提交了'.$add_count.' 数量的'.$add_name.'申请';
	    $ret[0] = sql_insert("insert into adm_log (`adm_name`,`opration`,`opration_content`,`oprate_time`) values ('$user_name','apply_army','$opration_content',unix_timestamp())");
	    $ret['user'] = $user;
	    $ret['cid'] = sql_fetch_one_cell("select cid from sys_city where uid='$uid' and name='$cityname'");
	}

?>