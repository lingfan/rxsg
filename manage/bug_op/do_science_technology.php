<?php
//修复科技异常
//参数列表
//返回
//array[]:

if (!defined("MANAGE_INTERFACE")) exit;
if (!isset($uid)) {exit("param_not_exist");}
if (!isset($id)) {exit("param_not_exist");}
$cid = sql_fetch_one_cell("select cid from sys_city where cid not in (select cid from mem_technic_upgrading) and uid=$uid limit 1");
sql_query("update sys_technic set level=level+1,state=0,cid=$cid where id=$id");
$ret['message']="科技修改成功！";
?>