<?php
//获得成就信息
//参数列表：
//cid:城市id
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($name) || empty($name))
    exit("param_not_exist");
if (! isset($search_type))
    exit("param_not_exist");
if ($search_type == "accuracy") {
    if (! empty($passport)) {
        $ret = sql_fetch_rows("select sua.*,ca.name,ca.content,ca.todo,from_unixtime(sua.`time`) as `time`,su.passport,su.name as uname from sys_user_achivement sua left join cfg_achivement ca on sua.achivement_id = ca.id left join sys_user su on su.uid = sua.uid where sua.uid=su.uid and ca.name like '%$name%' and su.passport='$passport'");
    } else {
        $ret = sql_fetch_rows("select sua.*,ca.name,ca.content,ca.todo,from_unixtime(sua.`time`) as `time`,su.passport,su.name as uname from sys_user_achivement sua left join cfg_achivement ca on sua.achivement_id = ca.id  left join sys_user su on su.uid = sua.uid  where sua.uid=su.uid and ca.name like '%$name%' and su.name = '$search_name'");
    }
} else {
    if (! empty($passport)) {
        $ret = sql_fetch_rows("select sua.*,ca.name,ca.content,ca.todo,from_unixtime(sua.`time`) as `time`,su.passport,su.name as uname from sys_user_achivement sua left join cfg_achivement ca on sua.achivement_id = ca.id  left join sys_user su on su.uid=sua.uid  where sua.uid=su.uid and ca.name like '%$name%' and su.passport like '%$passport%'");
    } else {
        $ret = sql_fetch_rows("select sua.*,ca.name,ca.content,ca.todo,from_unixtime(sua.`time`) as `time`,su.passport,su.name as uname from sys_user_achivement sua left join cfg_achivement ca on sua.achivement_id = ca.id  left join sys_user su on su.uid=sua.uid  where sua.uid=su.uid and ca.name like '%$name%' and su.name like '%$search_name%'");
    }
}
if (empty($ret))
    $ret = 'no data';
?>