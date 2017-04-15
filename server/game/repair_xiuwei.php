<?php
require_once("./interface.php");
require_once("./utils.php");

$users = sql_fetch_rows("select uid,passport from sys_user where uid>1000");
foreach ($users as $user) {
        $hefu = sql_fetch_one("select from_uid,from_database from hefu where from_passport='{$user['passport']}'");
        if (empty($hefu)) continue;
        $info=sql_fetch_one("select * from sys_user_level where uid={$user['uid']}");
        if (empty($info)) {
                sql_query("insert into sys_user_level(uid,level,time,getrewardtime) select {$user['uid']},level,time,getrewardtime from {$hefu['from_database']}.sys_user_level where uid={$hefu['from_uid']}");
                //echo "insert into sys_user_level(uid,level,time,getrewardtime) select {$user['uid']},level,time,getrewardtime from {$hefu['from_database']}.sys_user_level where uid={$hefu['from_uid']}"."\n";
        } else {
                sql_query("update sys_user_level a,{$hefu['from_database']}.sys_user_level b set a.level=b.level where a.uid={$user['uid']} and b.uid={$hefu['from_uid']}");
                //echo "update sys_user_level a,{$hefu['from_database']}.sys_user_level b set a.level=b.level where a.uid={$user['uid']} and b.uid={$hefu['from_uid']}"."\n";
        }
}