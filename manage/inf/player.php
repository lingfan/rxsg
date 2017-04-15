<?php
	//玩家信息搜索
	//参数列表：
	//union_id联盟id
	//nobility
	//officepos

	//
	//返回unionlist联盟名字，nobility，officepos，timenow现在时间
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($union_id))exit("param_not_exist");
	if (!isset($nobility))exit("param_not_exist");
	if (!isset($officepos))exit("param_not_exist");
	
	$ret['unionlist'] = sql_fetch_one_cell("select name from sys_union where id='$union_id'");
    $ret['nobility'] = sql_fetch_one_cell("select name from cfg_nobility where id='$nobility'");
    $ret['officepos'] = sql_fetch_one_cell("select name from cfg_office_pos where id='$officepos'");
    $ret['timenow'] = sql_fetch_one_cell("select unix_timestamp()");
    $ret[].=mysql_error();
    if(empty($ret))$ret = 'no data';
?>