<?php
	
/**
 * @author 张昌彪
 * @模块 公告管理 -- 登录公告
 * @功能 获得当前游戏服务器上的公告内容
 * @返回 string 公告内容
 */	 
	if (!defined("MANAGE_INTERFACE")) exit;

	$ret = sql_fetch_one("select * from  sys_announce where id = 1");
	

?>