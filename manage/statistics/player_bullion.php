<?php
	//用户剩余元宝统计
	//参数列表：
	//返回
	//区间
	//array[0]:=0
	//array[1]:1-10
	//array[2]:11-50
	//array[3]:51-100
	//array[4]:101-300
	//array[5]:301-500
	//array[6]:501-1000
	//array[7]:>1000
	if (!defined("MANAGE_INTERFACE")) exit;
	$ret[0] = sql_fetch_one_cell("select count(*) from sys_user where uid>1000 and money=0");
	$ret[1] = sql_fetch_one_cell("select count(*) from sys_user where uid>1000 and money<=10 and money>1");
	$ret[2] = sql_fetch_one_cell("select count(*) from sys_user where uid>1000 and money<=50 and money>10");
	$ret[3] = sql_fetch_one_cell("select count(*) from sys_user where uid>1000 and money<=100 and money>50");
	$ret[4] = sql_fetch_one_cell("select count(*) from sys_user where uid>1000 and money<=300 and money>100");
	$ret[5] = sql_fetch_one_cell("select count(*) from sys_user where uid>1000 and money<=500 and money>300");
	$ret[6] = sql_fetch_one_cell("select count(*) from sys_user where uid>1000 and money<=1000 and money>500");
	$ret[7] = sql_fetch_one_cell("select count(*) from sys_user where uid>1000 and money>1000");
?>