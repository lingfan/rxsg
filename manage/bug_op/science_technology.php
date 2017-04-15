<?php
//查询科技异常
//参数列表
//返回
//array[]:

if (!defined("MANAGE_INTERFACE")) exit;
if (!isset($passport)) {exit("param_not_exist");}
$uid = sql_fetch_one_cell("select uid from sys_user where passport='$passport'");
$ret = sql_fetch_rows("select st.*,ct.name from sys_technic as st,cfg_technic as ct where st.tid = ct.tid and st.uid = $uid and st.cid not in (select cid from sys_city where uid=$uid) and st.state = 1");
?>