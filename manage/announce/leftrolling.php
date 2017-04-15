<?php
  /**
   * @author 张昌彪
   * @模块: 公告管理 —— 左下角滚动公告
   * @功能: 列出游戏服务器的左下角滚动公告列表
   * @返回: array
   *        左下角滚动公告列表 
   */
  if (!defined("MANAGE_INTERFACE")) exit;    
  
    $ret=sql_fetch_rows("select * from sys_activity order by id asc");

?>
