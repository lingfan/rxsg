<?php
	//还原价格
	//参数列表：
	//$resumes需要还原价格的id数组
	//返回$ret['good_mdf']
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($resumes)){exit("param_not_exist");}
	$ret['good_mdf'] = sql_query("update cfg_shop set price=oriprice where id in (".$resumes.")"); 

?>