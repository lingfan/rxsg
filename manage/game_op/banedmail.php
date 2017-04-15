<?php
//参数列表：
//cid:cid
//返回
//array[]:result
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($content))
    exit();
if (! isset($type))
    exit();
if ($type == "add") {
    if (empty($content))
        continue;
    if (strlen($content) == 0)
        continue;
    sql_query("insert into cfg_baned_mail_content (content) values('$content') on duplicate key update content='$content'");
} elseif ($type == "del") {
    sql_query("delete from cfg_baned_mail_content where content='$content'");
}
?>