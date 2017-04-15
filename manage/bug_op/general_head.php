<?php
//修复将领头像不显示
//参数列表：name 将领名称
//返回
//是否正确执行
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($name))
    exit("param_not_exist");
$sex = sql_fetch_one_cell("select sex from sys_city_hero where name='$name'");
if (1 == $sex) {
    sql_query("update sys_city_hero set face = 677 where name = '$name' and sex =1 limit 1");
}else{
	sql_query("update sys_city_hero set face = 3 where name = '$name' and sex =0 limit 1");
}
$ret['message'] = "将领头像修复成功！";
?>