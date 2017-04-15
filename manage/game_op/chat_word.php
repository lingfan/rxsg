<?php
//参数列表：
//cid:cid
//返回
//array[]:result
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($word))
    exit();
if (! isset($type))
    exit();
if ($type == "add") {
    $word = trim($word);
    if (empty($word))
        continue;
    if (strlen($word) == 0)
        continue;
    sql_query("insert into sys_chat_word (word) values('$word') on duplicate key update word='$word'");
} elseif ($type == "del") {
    sql_query("delete from sys_chat_word where word='$word'");
}
?>