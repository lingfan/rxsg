<?php
//参数列表：无
//返回
//array[]:result
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($passport)) {
    exit("param_not_exist");
}
if (! isset($taskname)) {
    exit("param_not_exist");
}
$uid = sql_fetch_one_cell("select uid from sys_user where passport='$passport'");
if (empty($uid)) {
    $ret = "用户不存在";
} else {
    switch ($taskname) {
        case '1':
            {
                sql_query("replace into sys_user_task (uid,tid,state) select $uid,id,0 from cfg_task where id >=15001 and id<=15004");
                break;
            }
        case '2':
            {
                $sql = "replace into sys_user_task (uid,tid,state) select $uid,id,0 from cfg_task where id >=10101 and id<=10115";
                sql_query($sql);
                break;
            }
        case '3':
            {
                sql_query("replace into sys_user_task (uid,tid,state) select $uid,id,0 from cfg_task where id >=10201 and id<=10210");
                break;
            }
        case '4':
            {
                sql_query("replace into sys_user_task (uid,tid,state) select $uid,id,0 from cfg_task where id >=10301 and id<=10304");
                break;
            }
        case '5':
            {
                sql_query("replace into sys_user_task (uid,tid,state) select $uid,id,0 from cfg_task where id >=10401 and id<=10404");
                break;
            }
        case '6':
            {
                sql_query("replace into sys_user_task (uid,tid,state) select $uid,id,0 from cfg_task where id = 10500");
                break;
            }
        case '1000'://加官进爵
            {
                sql_query("replace into sys_user_task (uid,tid,state) values ('$uid',290,0)");
                sql_query("replace into sys_user_task (uid,tid,state) values ('$uid',18,0)");
                break;
            }
    }
}
if (empty($ret)) {
    $ret = "修复成功";
}
?>