<?php
/**
 * @inform 运营接口 -- 及时数据 -- 注册人数
 * @author 张昌彪
 * @param null
 * @return  array('reg_num','reg_max')
 * @example 
 */
if (!defined("MANAGE_INTERFACE"))
    exit;
try {
    //参数判断
    if (!isset($day) || empty($day)) {
        $reg_num = sql_fetch_one_cell("select count(*) from sys_user where uid > 1000 ");
    } elseif (isset($day)  && !empty($day)) //$day的格式是当天凌晨的unixstamp
    {
        if (!isset($distance) || empty($distance)) {
            $distance = 24;
        }
        $spacetime = 86400 / $distance;
        for ($i = 0; $i < $distance; $i++) {
            $result = sql_fetch_one_cell("select count(*) from sys_user where uid > 1000 and regtime<" .
                (int)($day + $spacetime * $i + $spacetime));
                if (empty($result)){$result=0;}
                $reg_num[$i] = $result;
        }

    }
    $reg_max = sql_fetch_one_cell("select value from mem_state where state=100");

    if (mysql_error()) {
        throw new Exception(mysql_error());
    }
    if(empty($reg_max)){$reg_max=0;}
    if (empty($reg_num)){$reg_num=0;}
    $ret['content']['reg_max'] = $reg_max;
    $ret['content']['reg_num'] = $reg_num;
}
catch (exception $e) {
    $ret['error'] = $e->getMessage();
}






?>