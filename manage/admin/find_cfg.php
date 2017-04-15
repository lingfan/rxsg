<?php
/**
 * @author 张昌彪
 * @模块 
 * @功能 查询整张cfg表
 * @参数
 * @返回 string
 *       整张cfg的id
 *       
 */
if (!defined("MANAGE_INTERFACE"))
    exit;
$cfg_things_ids = sql_fetch_column("select tid from cfg_things");
$cfg_armor_ids = sql_fetch_column("select id from cfg_armor");
$cfg_goods_ids = sql_fetch_column("select gid from cfg_goods");
$ret['things'] = $cfg_things_ids;
$ret['armor'] = $cfg_armor_ids;
$ret['goods'] = $cfg_goods_ids;
?>