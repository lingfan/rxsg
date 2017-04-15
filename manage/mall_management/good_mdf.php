<?php
	//修改商品价格
	//参数列表：
	//onsale，price，rebate，commend，hot，totalCount，userbuycnt，daybuycnt，description，sid
	//返回$ret商品信息
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($onsale)){exit("param_not_exist");}
	if (!isset($price)){exit("param_not_exist");}
	if (!isset($rebate)){exit("param_not_exist");}
	if (!isset($commend)){exit("param_not_exist");}
	if (!isset($hot)){exit("param_not_exist");}
	if (!isset($totalCount)){exit("param_not_exist");}
	if (!isset($userbuycnt)){exit("param_not_exist");}
	if (!isset($daybuycnt)){exit("param_not_exist");}
	if (!isset($description)){exit("param_not_exist");}
	if (!isset($sid)){exit("param_not_exist");}
	
	sql_query("update cfg_shop set `onsale`='$onsale',`price`='$price',`rebate`='$rebate',`commend`='$commend',`hot`='$hot',`totalCount`='$totalCount',`userbuycnt`='$userbuycnt',`daybuycnt`='$daybuycnt',`description`='$description' where id='$sid'");                   
    $ret = sql_fetch_one("select * from cfg_shop where id='$sid'");   

?>