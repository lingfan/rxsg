<?php 
if (!defined("MANAGE_INTERFACE"))
    exit;
if (!isset($sid)) {exit('params not exists');}
if (!isset($campaign_id)) {exit('params not exists');}
if (!isset($enable)) {exit('params not exists');}
if (!isset($onsale)) {exit('params not exists');}
if (!isset($operate_type)) {exit('params not exists');}
if (!isset($price)) {exit('params not exists');}
if (!isset($rebate)) {exit('params not exists');}
if (!isset($commend)) {exit('params not exists');}
if (!isset($hot)) {exit('params not exists');}
if (!isset($totalCount)) {exit('params not exists');}
if (!isset($userbuycnt)) {exit('params not exists');}
if (!isset($daybuycnt)) {exit('params not exists');}
if (!isset($description)) {exit('params not exists');}
if (!isset($start_time)) {exit('params not exists');}
if (!isset($end_time)) {exit('params not exists');}
sql_query("insert into adm_shop_sale (`operate_sid`,`campaign_id`,`enable`,`onsale`,`operate_type`,`price`,`rebate`,`commend`,`hot`,`totalCount`,`userbuycnt`,`daybuycnt`,`description`,`start_time`,`end_time`) values ('$sid','$campaign_id','$enable','$onsale','$operate_type','$price','$rebate','$commend','$hot','$totalCount','$userbuycnt','$daybuycnt','$description',UNIX_TIMESTAMP('$start_time'),UNIX_TIMESTAMP('$end_time'))");
?>