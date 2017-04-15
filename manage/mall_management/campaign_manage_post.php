<?php

if (!defined("MANAGE_INTERFACE"))
    exit;

if (!isset($id)) {
    exit("param_not_exist");
}
if (!isset($name)) {
    exit("param_not_exist");
}
if (!isset($delete)) {
    exit("param_not_exist");
}
if (!isset($day)) {
    exit("param_not_exist");
}
if (!isset($hour)) {
    exit("param_not_exist");
}
if (!isset($enable)) {
    exit("param_not_exist");
}

if (!empty($id) && !empty($name)) {
    if (!empty($delete)) {
        sql_query("delete from adm_shop_campaign where `id`='$id'");
    } else {
        sql_query("update adm_shop_campaign set enable='$enable',`name`='$name' where `id`='$id'");
        if (!empty($day) || !empty($hour)) {
            $unix_time = $day * 86400 + $hour * 3600;
            sql_query("update adm_shop_sale set start_time=start_time+$unix_time,end_time=end_time+$unix_time where `campaign_id`='$id'");
        }
    }
    $ret['show'] = '操作成功！';
} else {
    $ret['show'] = '操作失败！';
}

?>