<?php
/**
 * @模块：查询查看 -- 野地查询 
 * @功能：查询当前城池拥有的野地情况信息
 * @参数：int cid 城池id
 * @返回：
 *    array(
 *      array(
 *      wid:野地id
 *      type:野地类型
 *      state:野地状态
 *      level:等级
 *      )
 *    )
 */
	if (!defined("MANAGE_INTERFACE")) exit;	
	if (!isset($cid))exit("param_not_exist");
    $ret = sql_fetch_rows("select wid,type,state,level,CONCAT(floor(wid%10000/100)*10+wid%10,',',floor(wid/10000)*10+floor(wid/10)%10) as position from mem_world where type>0 and ownercid=$cid");
?>