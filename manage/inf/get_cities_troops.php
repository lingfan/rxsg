<?php
/**
 * @模块：查询查看 -- 城池信息--行军信息
 * @功能：查询当前城池出征军队的信息
 * @参数：int cid 城池id
 * @返回：
 *    array(
 *      array(

 *      )
 *    )
 */
	if (!defined("MANAGE_INTERFACE")) exit;
    if (!isset($uid))exit("param_not_exist");
    if(isset($cid)&&$cid!=0){
	    $troops = sql_fetch_rows("select c.name as cname,h.name as hname,t.targetcid,t.task,from_unixtime(starttime) as starttime,t.state,
		    from_unixtime(endtime) as endtime,pathtime, soldiers,CONCAT(c.cid%1000,',',floor(c.cid/1000)) as cposition, CONCAT(t.targetcid%1000,',',floor(t.targetcid/1000)) as tposition
	    	from sys_troops t, sys_city c, sys_city_hero h where t.cid=c.cid and t.hid=h.hid and t.cid='$cid'");	
    }else{
	    $troops = sql_fetch_rows("select c.name as cname,h.name as hname,t.targetcid,t.task,from_unixtime(starttime) as starttime,t.state,
	    	from_unixtime(endtime) as endtime,pathtime, soldiers,CONCAT(c.cid%1000,',',floor(c.cid/1000)) as cposition, CONCAT(t.targetcid%1000,',',floor(t.targetcid/1000)) as tposition
	   		from sys_troops t, sys_city c, sys_city_hero h where t.cid=c.cid and t.hid=h.hid and t.uid='$uid'");
	    
    }
    $sql_error = mysql_error();
    if(empty($troops)||!empty($sql_error))
    {
        $ret = array();
    }
    else
    {
    	$soldiers_type = sql_fetch_rows("select * from cfg_soldier");
    	foreach($troops as &$troop){
    		$troop['cname']=$troop['cname'].'('.floor($cid%1000).','.floor($cid/1000).')';
	    	$soldiers_str = "";
	        $soldiers = $troop['soldiers'];
	        $slist = explode(',',$soldiers);
	        $num = array_shift($slist);
	        array_pop($slist);
	        foreach($soldiers_type as $stype){
	            $soldtype[$stype['sid']]=$stype['name'];
	        }
	
	        for($i=0;$i<$num*2;$i+=2)
	        {
	            $sname = $soldtype[$slist[$i]];
	            $soldiers_str .= $sname.$slist[$i+1]."。";
	        }
	        $troop['soldiers'] = $soldiers_str;
    	}
        $ret = $troops;
    }
    	
?>