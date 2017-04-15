<?php
/**
 * @inform 运营接口 -- 及时数据 -- 新建号人数
 * @author 张昌彪
 * @param null
 * @return  array('cur_new_reg','yesterday_new_reg_all')
 * @example 
 */
if (!defined("MANAGE_INTERFACE"))
    exit;
try{
    //参数判断
    if (isset($day) && !empty($day)) //如果有某一天的值传递，就返回当天的最高在线
    {
        if (!isset($distance) || empty($distance)) {
            $distance = 24;
        }
        $cur_new_reg = array();
        $spacetime = 86400 / $distance;
        for ($i = 1; $i < $distance+1; $i++) {
            $result = sql_fetch_one_cell("select count(*) from sys_user where uid > 1000 and regtime>$day and regtime<".(int)($day+$space*$i));
            if (empty($result)){$result=0;}
            $cur_new_reg[] = $result;
        }

        $yesterday_new_reg_all = sql_fetch_one_cell("select count(*) from sys_user where uid > 1000 and regtime<$day and regtime>$day-86400");
        if (mysql_error()) {
            throw new Exception(mysql_error());
        }
        $ret['content']['cur_new_reg'] = $cur_new_reg;
        if (empty($yesterday_new_reg_all)){$yesterday_new_reg_all=0;}
        $ret['content']['yesterday_new_reg_all'] = $yesterday_new_reg_all;
    }
    else
    {
        $day = date('Ymd');
        $cur_new_reg = sql_fetch_one_cell("select count(*) from sys_user where uid > 1000 and regtime<unix_timestamp() and regtime>unix_timestamp($day)");
        $yesterday_new_reg_all = sql_fetch_one_cell("select count(*) from sys_user where uid > 1000 and regtime<unix_timestamp($day) and regtime>unix_timestamp($day)-86400");
        if(mysql_error())
        {
            throw new Exception (mysql_error());
        }
        if (empty($cur_new_reg)){$cur_new_reg=0;}
        if (empty($yesterday_new_reg_all)){$yesterday_new_reg_all=0;}
        $ret['content']['cur_new_reg'] = $cur_new_reg;
        $ret['content']['yesterday_new_reg_all'] = $yesterday_new_reg_all;
    }
}   
catch (exception $e) {
    $ret['error']=$e->getMessage();
}






?>