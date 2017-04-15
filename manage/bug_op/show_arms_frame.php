<?php
//查询武器架和高级武器架信息
//参数列表：无
//返回
//array[]:result
if (! defined("MANAGE_INTERFACE"))
    exit();
    $ret['145'] = sql_fetch_one("select gid,description from cfg_goods where gid='145'");
    $ret['146'] = sql_fetch_one("select gid,description from cfg_goods where gid='146'");
?>