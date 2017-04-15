<?php
//更新武器架和高级武器架信息
//参数列表：无
//返回
//array[]:result
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($gid))
    exit();
if ("145" == $gid) {
    sql_query("update cfg_goods set description = '使用武器架后永久增加5格装备栏，可多次使用，最多可扩展装备栏到500格' where gid='145'");
    sql_query("update cfg_shop set description='使用武器架后永久增加5格装备栏，可多次使用，最多可扩展装备栏到500格' where gid=145");
    $ret['message'] = "武器架信息更新成功！";
} elseif ("146" == $gid) {
    sql_query("update cfg_goods set description = '使用高级武器架后永久增加50格装备栏，可多次使用，最多可扩展装备栏到500格' where gid='146'");
    sql_query("update cfg_shop set description='使用高级武器架后永久增加50格装备栏，可多次使用，最多可扩展装备栏到500格' where gid=146");
    $ret['message'] = "高级武器架信息更新成功！";
}
?>