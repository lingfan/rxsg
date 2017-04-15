<?php
/**
 * @模块：查询查看 -- 野地查询 
 * @功能：查询当前城池拥有的野地情况信息
 * @参数：int cid 城池id
 * @返回：
 *    array(
 *      array(

 *      )
 *    )
 */
	if (!defined("MANAGE_INTERFACE")) exit;
    if (!isset($wid))exit("param_not_exist");
    $troop = sql_fetch_one("select * from sys_troops where id in (select troopid from sys_gather where wid = '$wid')");
    if(empty($troop))
    {
        $ret = $troop;
    }
    else
    {
        $soldiers = $troop['soldiers'];
        $slist = explode(',',$soldiers);
        $num = array_shift($slist);
        array_pop($slist);
        $soldiers_type = sql_fetch_rows("select * from cfg_soldier");
        foreach($soldiers_type as $stype){
            $soldtype[$stype['sid']]=$stype['name'];
        }

        for($i=0;$i<$num;$i+=2)
        {
            $sname = $soldtype[$slist[$i]];
            $ret[$sname] = $slist[$i+1];
        }
    }
    if(mysql_error())
    {
        $ret[] = mysql_error();
    }
    	
?>