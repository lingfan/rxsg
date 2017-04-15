<?php
/**
 * @author 张昌彪
 * @模块 公告管理 -- 左下角滚动公告
 * @功能 添加该服务器上的左下角滚动公告
 * @参数 array(content,inuse,link,interval)
 * @返回 string
 *       插入成功返回id
 *       插入失败返回“insert_failed”
 */
if (!defined("MANAGE_INTERFACE"))
    exit;

if (!isset($add_array)) {
    exit('params_not_exit');
}
$add_content = $add_array[1];
$add_inuse = $add_array[0];
$add_link = $add_array[2];
$add_interval = $add_array[3];
$id = sql_insert("insert into sys_activity (`inuse`,`content`,`link`,`interval`) values ('$add_inuse','$add_content','$add_link','$add_interval')");
if (mysql_error()) {
    $ret = "insert_failed";
} else {
    $ret = $id;
}
?>
