<?php
if (!defined("MANAGE_INTERFACE"))
    exit;
if (!isset($sid)) {
    exit('params not exists');
}
$ret = sql_fetch_one_cell("select oriprice from cfg_shop c,adm_shop_sale s where s.operate_sid=c.id and s.id='$sid'");
?>