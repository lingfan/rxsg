<?php
	//参数列表：
	//cid:cid
	//返回
	//array[]:result
	if (!defined("MANAGE_INTERFACE")) exit;
	$ret = sql_fetch_rows('select * from sys_chat_word');
?>