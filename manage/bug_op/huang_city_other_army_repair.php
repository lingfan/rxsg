<?php
if (!defined("MANAGE_INTERFACE")) exit;
sql_query("delete from sys_city_soldier where cid in (select cid from sys_city where uid=894) and sid<=17");
$ret="黄巾城其他兵种处理成功";
?>