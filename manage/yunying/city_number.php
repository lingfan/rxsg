<?php
/**
 * @inform 运营接口 -- 及时数据 -- 城池数量
 * @author 张昌彪
 * @param null
 * @return string $num
 * @example  
 */
if (!defined("MANAGE_INTERFACE"))
    exit;
try{
    //参数判断
    if(!isset($day) && !empty($day))
    {
        $day = time();
    }
    $num = sql_fetch_one_cell("select count(*) from sys_city");
    if(mysql_error())
    {
        throw new Exception(mysql_error());
    }
    if (empty($num)){$num=0;}
    $ret['content']['city_number'] = $num; 
}
catch (exception $e) {
    $ret['error']=$e->getMessage();
}






?>