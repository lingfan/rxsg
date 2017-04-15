<?php
//许孝敦
//修改科技等级
//参数列表：
//uid:用户id;tid科技id
//返回科技信息
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($passport))
    exit("uid_not_exist");
function pre_level ($tid, $level)
{
    $pre_level = sql_fetch_one_cell("select pre_level from cfg_technic_condition where tid=$tid and level=$level and pre_type=0 and pre_id=7");
    if (empty($pre_level)) {
        return 0;
    }
    return $pre_level;
}
$uid = sql_fetch_one_cell("select uid from sys_user where passport='$passport'");
$cids = sql_fetch_rows("select cid from sys_city where uid='$uid'");
foreach ($cids as $value) {
    $cid = $value['cid'];
    $sb_level = sql_fetch_one_cell("select level from sys_building where cid='$cid' and bid=7");
    sql_query("update sys_city_technic set level=0 where cid='$cid'");
    $rows = sql_fetch_rows("select tid,level from sys_technic where uid='$uid'");
    if (! empty($rows)) {
        foreach ($rows as $row) {
            $tid = $row['tid'];
            $level = $row['level'];
            if (pre_level($tid, $level) <= $sb_level) {
                sql_query("insert into sys_city_technic (cid,tid,level) values ('$cid','$tid','$level') on duplicate key update level='$level'");
            }
        }
    }
}
$ret['message'] = "科技刷新成功！";
?>