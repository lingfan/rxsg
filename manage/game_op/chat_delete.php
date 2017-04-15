<?php
	//参数列表：
	//cid:cid
	//返回
	//array[]:result
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($deleteids)){exit("param_not_exist");}
	
	sql_query("delete from adm_user where userid in (".implode(',',$deleteids).")");

?>