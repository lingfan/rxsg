<?php                      
require_once("./interface.php");
require_once("./utils.php");
require_once("./HeroFunc.php");
require_once("./utils/URLUtils.php");
require_once("./TaskFuncAdd.php");
require_once("./HotelFunc.php");

function getUnionFamousCityGold($uid)
{
	$unionid=sql_fetch_one_cell("select union_id from sys_user where uid='$uid'");
	if($unionid==0) return 0;
	$cities=sql_fetch_rows("select type,count(*) from sys_city c, sys_user u where u.uid=c.uid and u.union_id='$unionid' and c.type>0 and c.type<5 group by c.type");
	$gold=0;
	if(!empty($cities))
	{
		foreach($cities as $city)
		{
			if($city['type']==1) $gold=$gold+10000;
			else if($city['type']==2) $gold=$gold+30000;
			else if($city['type']==3) $gold=$gold+100000;
			else if($city['type']==4) $gold=$gold+300000;
		}
	}
	return $gold;
}

function checkGoalComplete($uid,$goal)
{
	if (!empty($goal['uid']) && $goal['sort'] != 50 && $goal['sort'] != 80) // 非累计任务有记录就算完成
    {
        return true;
    }
    if ($goal['sort'] == 100 || $goal['sort'] == 110 || $goal['sort'] == 120) {//战场任务 sys_user_goal 没记录就是没完成
    	return false;
    }
	    
 	$name = sql_fetch_one_cell("select name from sys_user where uid=$uid");

    if ($goal['sort'] == 1)    //资源  1黄金,  2粮食,3木材,4石料,5铁锭,6人口,7民心,8民怨,9声望,
    {
		if ($goal['type'] == 9)     //声望
		{
            return sql_fetch_one_cell("select prestige from sys_user where uid='$uid'") >= $goal['count']; 
        }
        else if ($goal['type'] == 53) //玩家排名
        {
			$rank=sql_fetch_one_cell("select `rank` from rank_user where `name`='$name'");
			if($rank >=1 && $rank <=1000) {
        		return true;
			}
        }
        else if ($goal['type'] == 54) //军事排名
        {
			$rank=sql_fetch_one_cell("select `rank` from rank_military where `name`='$name'");
			if($rank >=1 && $rank <=1000) {
 				return true;
			}
        }
        else if ($goal['type'] == 55) //进攻排名
        {
			$rank=sql_fetch_one_cell("select `rank` from rank_military_attack where `name`='$name'");
			if($rank >=1 && $rank <=1000) {
 				return true;
			}
        }
        else if ($goal['type'] == 56) //防御排名
        {
			$rank=sql_fetch_one_cell("select `rank` from rank_military_defence where `name`='$name'");
			if($rank >=1 && $rank <=1000) {
 	        	return true;
			}
        }
        else if ($goal['type'] == 11)       //联盟人数
        {   
            $union_id  = sql_fetch_one_cell("select union_id from sys_user where uid='$uid'");
            if ($union_id == 0) return false;
            
            return sql_fetch_one_cell("select member from sys_union where id='$union_id'") >= $goal['count'];
        }
        else if ($goal['type'] == 17)   //官职
        {
            return sql_fetch_one_cell("select officepos from sys_user where uid='$uid'") >= $goal['count'];
        }
        else if ($goal['type'] == 18)   //爵位
        {
        	$nobility = sql_fetch_one_cell("select nobility from sys_user where uid='$uid'");
        	//推恩
        	$nobility = getBufferNobility($uid,$nobility);
            return  $nobility >= $goal['count'];
        }
        else if ($goal['type'] == 19)
        {
            return sql_fetch_one_cell("select money from sys_user where uid='$uid'") >= $goal['count'];
        }
        else if ($goal['type']==20)
        {
        	return sql_fetch_one_cell("select count(*) from sys_city where uid='$uid' and type>0 and type<5")>=$goal['count'];
        }
        else if ($goal['type']==21)
        {
        	$union_id  = sql_fetch_one_cell("select union_id from sys_user where uid='$uid'");
            if ($union_id == 0) return false;
            return sql_fetch_one_cell("select count(*) from sys_city a,sys_user b where a.type>0 and a.type<5 and a.uid=b.uid  and  b.union_id='$union_id'")>=$goal['count'];
        }
        else if ($goal['type'] == 22)
        {
            return sql_fetch_one_cell("select money from sys_user where uid='$uid'") >= $goal['count'];
        }
        else if ($goal['type'] == 122)
        {
            return sql_fetch_one_cell("select money from sys_user where uid='$uid'") >= $goal['count'];
        }
        else if (($goal['type'] >= 12)&&($goal['type'] <= 15))  //四种基础产量
        {
            $lastcid = sql_fetch_one_cell("select lastcid from sys_user where uid='$uid'");
            if ($goal['type'] == 12)
            {
                return sql_fetch_one_cell("select sum(b.level*(b.level+1)*50) from sys_city c,sys_building b where c.cid='$lastcid' and b.cid='$lastcid' and b.bid=".ID_BUILDING_FARMLAND) >= $goal['count'];
            }
            else if ($goal['type'] == 13)
            {
                return sql_fetch_one_cell("select sum(b.level*(b.level+1)*50) from sys_city c,sys_building b where c.cid='$lastcid' and b.cid='$lastcid' and b.bid=".ID_BUILDING_WOOD) >= $goal['count'];
            }
            else if ($goal['type'] == 14)
            {
                return sql_fetch_one_cell("select sum(b.level*(b.level+1)*50) from sys_city c,sys_building b where c.cid='$lastcid' and b.cid='$lastcid' and b.bid=".ID_BUILDING_ROCK) >= $goal['count'];
            }
            else if ($goal['type'] == 15)
            {
                return sql_fetch_one_cell("select sum(b.level*(b.level+1)*50) from sys_city c,sys_building b where c.cid='$lastcid' and b.cid='$lastcid' and b.bid=".ID_BUILDING_IRON) >= $goal['count'];
            }     
        }
        else if ($goal['type'] ==31 ){
        	//累计获得50次战场胜利
        	$time = sql_fetch_one_cell("select unix_timestamp()");
        	$start = $time - $time%86400 + 1;
        	$end   = $start+86400;
        	$ret=sql_fetch_one_cell("select uid from log_everyday_task where uid=$uid and tid=".$goal['tid']." and gettime>=$start and gettime<=$end ");
        	if (!empty($ret)){
        		return false;
        	}
        	
        	$start = sql_fetch_one_cell("select gettime from log_everyday_task where uid=$uid and tid=".$goal['tid']);
        	$end = $time;
        	if (empty($start)){
        		$start = 0;
        	}
        	
        	$count = sql_fetch_one_cell("select count(*) from log_battle_honour where uid=$uid and result=0 and quittime>=$start and quittime<=$end and battleid in(1001,2001,3001,4001)");
        	if ($count>=50){
        	    return true;
        	}
        }
    	else if ($goal['type'] ==32 ){
    		//在黄巾之乱战场（难度10级）中获得1场胜利
    	    $time = sql_fetch_one_cell("select unix_timestamp()");
        	$start = $time - $time%86400 + 1;
        	$end   = $start+86400;
        	
        	$ret=sql_fetch_one_cell("select uid from log_everyday_task where uid=$uid and tid=".$goal['tid']." and gettime>=$start and gettime<=$end");
        	if (!empty($ret)){
        		return false;
        	}
        	$start = sql_fetch_one_cell("select gettime from log_everyday_task where uid=$uid and tid=".$goal['tid']);
        	$end = $time;
    	    if (empty($start)){
        		$start = 0;
        	}
        	
        	$count = sql_fetch_one_cell("select count(*) from log_battle_honour where uid=$uid and result=0 and level=10 and battleid=1001 and quittime>=$start and quittime<=$end");
        	if ($count>=1){
        	    return true;
        	}
        }
    	else if ($goal['type'] ==33 ){
    		//在黄巾之乱战场获得10场胜利
    	    $time = sql_fetch_one_cell("select unix_timestamp()");
        	$start = $time - $time%86400 + 1;
        	$end   = $start+86400;
        	$ret=sql_fetch_one_cell("select uid from log_everyday_task where uid=$uid and tid=".$goal['tid']." and gettime>=$start and gettime<=$end ");
        	if (!empty($ret)){
        		return false;
        	}
        	$start = sql_fetch_one_cell("select gettime from log_everyday_task where uid=$uid and tid=".$goal['tid']);
        	$end = $time;
    	    if (empty($start)){
        		$start = 0;
        	}
        	
        	$count = sql_fetch_one_cell("select count(*) from log_battle_honour where uid=$uid and result=0 and  battleid=1001 and quittime>=$start and quittime<=$end");
        	if ($count>=10){
        	    return true;
        	}        	
        }
    	else if ($goal['type'] ==34 ){
    		//在官渡之战战场（难度10级）中作为袁军获得1场胜利
    		$time = sql_fetch_one_cell("select unix_timestamp()");
        	$start = $time - $time%86400 + 1;
        	$end   = $start+86400;
        	$ret=sql_fetch_one_cell("select uid from log_everyday_task where uid=$uid and tid=".$goal['tid']." and gettime>=$start and gettime<=$end ");
        	if (!empty($ret)){
        		return false;
        	}
        	
        	$start = sql_fetch_one_cell("select gettime from log_everyday_task where uid=$uid and tid=".$goal['tid']);
        	$end = $time;
    	    if (empty($start)){
        		$start = 0;
        	}
        	
        	$count = sql_fetch_one_cell("select count(*) from log_battle_honour where uid=$uid and result=0 and unionid=3 and level=10 and battleid=2001 and quittime>=$start and quittime<=$end");
        	if ($count>=1){
        	    return true;
        	}
        }
    	else if ($goal['type'] ==35 ){
    		//在官渡之战战场中作为袁军获得10场胜利
    		$time = sql_fetch_one_cell("select unix_timestamp()");
        	$start = $time - $time%86400 + 1;
        	$end   = $start+86400;
        	$ret=sql_fetch_one_cell("select uid from log_everyday_task where uid=$uid and tid=".$goal['tid']." and gettime>=$start and gettime<=$end ");
        	if (!empty($ret)){
        		return false;
        	}
        	
        	$start = sql_fetch_one_cell("select gettime from log_everyday_task where uid=$uid and tid=".$goal['tid']);
        	$end = $time;
    		if (empty($start)){
        		$start = 0;
        	}
        	
        	$count = sql_fetch_one_cell("select count(*) from log_battle_honour where uid=$uid and result=0 and unionid=3 and battleid=2001 and quittime>=$start and quittime<=$end");
        	if ($count>=10){
        	    return true;
        	}
        	
        }
    	else if ($goal['type'] ==36 ){
    		//在官渡之战战场（难度10级）中作为曹军获得1场胜利
    	    $time = sql_fetch_one_cell("select unix_timestamp()");
        	$start = $time - $time%86400 + 1;
        	$end   = $start+86400;
        	$ret=sql_fetch_one_cell("select uid from log_everyday_task where uid=$uid and tid=".$goal['tid']." and gettime>=$start and gettime<=$end ");
        	if (!empty($ret)){
        		return false;
        	}
        	
        	$start = sql_fetch_one_cell("select gettime from log_everyday_task where uid=$uid and tid=".$goal['tid']);        
        	$end = $time;
    		if (empty($start)){
        		$start = 0;
        	}
        	
        	$count = sql_fetch_one_cell("select count(*) from log_battle_honour where uid=$uid and result=0 and unionid=4 and level=10 and battleid=2001 and quittime>=$start and quittime<=$end");
        	if ($count>=1){
        	    return true;
        	}        	
        }
    	else if ($goal['type'] ==37 ){
    		//在官渡之战战场中作为曹军获得10场胜利
    	    $time = sql_fetch_one_cell("select unix_timestamp()");
        	$start = $time - $time%86400 + 1;
        	$end   = $start+86400;
        	$ret=sql_fetch_one_cell("select uid from log_everyday_task where uid=$uid and tid=".$goal['tid']." and gettime>=$start and gettime<=$end ");
        	if (!empty($ret)){
        		return false;
        	}
        	
        	$start = sql_fetch_one_cell("select gettime from log_everyday_task where uid=$uid and tid=".$goal['tid']);
        	$end = $time;
    	     if (empty($start)){
        		$start = 0;
        	}
        	
        	$count = sql_fetch_one_cell("select count(*) from log_battle_honour where uid=$uid and result=0 and unionid=4 and battleid=2001 and quittime>=$start and quittime<=$end");
        	if ($count>=10){
        	    return true;
        	}    		
        	
        }
        else if ( $goal['type'] ==38 ){
        	//获得1次剧情战场胜利
        	return sql_fetch_one_cell("select uid from log_battle_honour where uid=$uid and battleid=1001 and result=0 limit 1");
        }
        else if ( $goal['type'] ==39){
        	//获得1次据点战场胜利
        	return sql_fetch_one_cell("select uid from log_battle_honour where uid=$uid and battleid=2001 and result=0 limit 1");
        }
    	else if ($goal['type'] ==40 ){
    		//在十常侍之乱战场（难度10级）中获得1场胜利。
    	    $time = sql_fetch_one_cell("select unix_timestamp()");
        	$start = $time - $time%86400 + 1;
        	$end   = $start+86400;
        	
        	$ret=sql_fetch_one_cell("select uid from log_everyday_task where uid=$uid and tid=".$goal['tid']." and gettime>=$start and gettime<=$end");
        	if (!empty($ret)){
        		return false;
        	}
        	$start = sql_fetch_one_cell("select gettime from log_everyday_task where uid=$uid and tid=".$goal['tid']);
        	$end = $time;
    	    if (empty($start)){
        		$start = 0;
        	}
        	
        	$count = sql_fetch_one_cell("select count(*) from log_battle_honour where uid=$uid and result=0 and level=10 and battleid=3001 and quittime>=$start and quittime<=$end");
        	if ($count>=1){
        	    return true;
        	}
        }else if ($goal['type'] ==41 ){
    		//在十常侍之乱战场获得10场胜利。
        	$time = sql_fetch_one_cell("select unix_timestamp()");
        	$start = $time - $time%86400 + 1;
        	$end   = $start+86400;
        	$ret=sql_fetch_one_cell("select uid from log_everyday_task where uid=$uid and tid=".$goal['tid']." and gettime>=$start and gettime<=$end ");
        	if (!empty($ret)){
        		return false;
        	}
        	$start = sql_fetch_one_cell("select gettime from log_everyday_task where uid=$uid and tid=".$goal['tid']);
        	$end = $time;
    	    if (empty($start)){
        		$start = 0;
        	}
        	
        	$count = sql_fetch_one_cell("select count(*) from log_battle_honour where uid=$uid and result=0 and  battleid=3001 and quittime>=$start and quittime<=$end");
        	if ($count>=10){
        	    return true;
        	}  
        }else if ($goal['type'] ==42 ){
    		//在讨伐董卓战场（难度10级）中获得1场胜利。
            	    $time = sql_fetch_one_cell("select unix_timestamp()");
        	$start = $time - $time%86400 + 1;
        	$end   = $start+86400;
        	
        	$ret=sql_fetch_one_cell("select uid from log_everyday_task where uid=$uid and tid=".$goal['tid']." and gettime>=$start and gettime<=$end");
        	if (!empty($ret)){
        		return false;
        	}
        	$start = sql_fetch_one_cell("select gettime from log_everyday_task where uid=$uid and tid=".$goal['tid']);
        	$end = $time;
    	    if (empty($start)){
        		$start = 0;
        	}
        	
        	$count = sql_fetch_one_cell("select count(*) from log_battle_honour where uid=$uid and result=0 and level=10 and battleid=4001 and quittime>=$start and quittime<=$end");
        	if ($count>=1){
        	    return true;
        	}
        }else if ($goal['type'] ==43 ){
    		//在讨伐董卓战场获得10场胜利。
 	      		$time = sql_fetch_one_cell("select unix_timestamp()");
        	$start = $time - $time%86400 + 1;
        	$end   = $start+86400;
        	$ret=sql_fetch_one_cell("select uid from log_everyday_task where uid=$uid and tid=".$goal['tid']." and gettime>=$start and gettime<=$end ");
        	if (!empty($ret)){
        		return false;
        	}
        	$start = sql_fetch_one_cell("select gettime from log_everyday_task where uid=$uid and tid=".$goal['tid']);
        	$end = $time;
    	    if (empty($start)){
        		$start = 0;
        	}
        	
        	$count = sql_fetch_one_cell("select count(*) from log_battle_honour where uid=$uid and result=0 and  battleid=4001 and quittime>=$start and quittime<=$end");
        	if ($count>=10){
        	    return true;
        	}
        }else if ($goal['type'] ==44 || $goal['type'] ==45 || $goal['type'] ==46 ){
    		//44:在逐鹿中原跨服战场（战区不限）中，成功参与1场战斗。
    		//45:在逐鹿中原跨服战场（战区不限）中，获得1次胜利。
    		//46:在逐鹿中原跨服战场（战区不限）中，获得10次胜利。
			if ($goal['type']==44) {//在逐鹿中原跨服战场（战区不限）中，成功参与1场战斗。
				$num = sql_fetch_one_cell("select count(1) from bak_sys_user_battle_state where uid='$uid' and bid=6001 and sent_troop_count>0 and unix_timestamp(jointime)>=unix_timestamp(curdate())");
			}else{
				$num = sql_fetch_one_cell("select count(1) from bak_sys_user_battle_state where uid='$uid' and bid=6001 and iswinner=1 and quittime>=unix_timestamp(curdate())");
			}
        	$count = $goal['count'];
        	return ($num>=$count);
        }else if ( $goal['type'] ==50){//一定等级的将领
    		$level = $goal['count'];
    		$count=sql_fetch_one_cell("select count(1) from sys_city_hero where state = 0 and uid= $uid and level >= $level and herotype != '1000'");
    		if ($count>=1){
        	    return true;
        	}
        }else if ( $goal['type'] ==51){//装备颜色装：灰色、白色等
    		$type = $goal['count'];
    		$count=sql_fetch_one_cell("select count(1) from sys_user_armor a, cfg_armor b where  a.hid=0 and a.uid= $uid and a.armorid=b.id and  b.type = $type");
    		if ($count>=1){
        	    return true;
        	}
        }else if ( $goal['type'] ==52){//装备类型：头部脚部等
    		$part = $goal['count'];
    		$count=sql_fetch_one_cell("select count(1) from sys_user_armor a, cfg_armor b where a.hid=0 and  a.uid= $uid and a.armorid=b.id and   b.part = $part");
    		if ($count>=1){
        	    return true;
        	}
        }else if ($goal['type'] >=60 && $goal['type'] <=64 ){
//成功进入七擒孟获跨服战场，无论最终胜利或失败。
//成功进入七擒孟获跨服战场，且最终获得战场胜利。
//累积获得3场七擒孟获跨服战场胜利。
//累积获得5场七擒孟获跨服战场胜利。
        	
			if ($goal['type']==60) {//成功进入七擒孟获跨服战场，无论最终胜利或失败。
				$num = sql_fetch_one_cell("select count(1) from bak_sys_user_battle_state where uid='$uid' and bid=9001");
			}else{
				$num = sql_fetch_one_cell("select count(1) from bak_sys_user_battle_state where uid='$uid' and bid=9001 and state=2");
			}
        	$count = $goal['count'];
        	return ($num>=$count);
        }else
        {
            $cityres = sql_fetch_one("select c.* from mem_city_resource c,sys_user u where c.cid=u.lastcid and u.uid='$uid'");
            switch ($goal['type'])
            {
                case 1:
                    return $cityres['gold'] >= $goal['count'];
                case 2:
                    return $cityres['food'] >= $goal['count'];
                case 3:
                    return $cityres['wood'] >= $goal['count']; 
                case 4:
                    return $cityres['rock'] >= $goal['count']; 
                case 5:
                    return $cityres['iron'] >= $goal['count']; 
                case 6:
                    return $cityres['people'] >= $goal['count']; 
                case 7:
                    return $cityres['morale'] >= $goal['count']; 
                case 8:
                    return $cityres['complaint'] >= $goal['count']; 
                case 10:    //人口上限
                    return $cityres['people_max'] >= $goal['count'];
                case 16:    //黄金产量
                    return ($cityres['people']*$cityres['tax']*0.01) >= $goal['count'];
            }
        }                
    }
    else if ($goal['sort'] == 2)   //2:宝物  
    {
//    	if($goal['type']>110000&&$goal['type']<160000){//任务目标为武魂的特殊处理
//			$userhascount=sql_fetch_one_cell("select sum(count) from sys_goods where uid='$uid' and gid>='$goal[type]' and gid%10000=$goal[type]%10000 order by gid");
//			return $userhascount>= $goal['count'];
//		}
			return sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='$goal[type]'") >= $goal['count'];
    }
    else if ($goal['sort'] == 3)  //3:军队    
    {
        return sql_fetch_one_cell("select count from sys_city_soldier s,sys_user u where u.uid='$uid' and u.lastcid=s.cid and s.sid='$goal[type]'") >= $goal['count'];
    }
    else if ($goal['sort'] == 4)   //4:城防     
    {
        return sql_fetch_one_cell("select count from sys_city_defence d,sys_user u where u.uid='$uid' and u.lastcid=d.cid and d.did='$goal[type]'") >= $goal['count'];
    }
    else if ($goal['sort'] == 5)  //5:任务物品  
    {
        return sql_fetch_one_cell("select count from sys_things where uid='$uid' and tid='$goal[type]'") >= $goal['count'];
    }
	else if ($goal['sort'] == 6)  //6:装备  
    {
    	$stronglv = intval($goal['strong_level']);//要求强化等级
    	$armorid = intval($goal['type']);
    	if($armorid>0){
        	return sql_fetch_one_cell("select count(1) from sys_user_armor where uid='$uid' and hid=0 and armorid=$armorid and strong_level>=$stronglv") >= $goal['count'];
    	}else{//0表示不限制装备,只要求等级
    		return sql_fetch_one_cell("select count(1) from sys_user_armor where uid='$uid' and hid=0 and strong_level>=$stronglv") >= $goal['count'];
    	}
    }
    else if ($goal['sort'] == 8)	//8:名城
    {
    	return sql_fetch_one_cell("select count(*) from sys_city where uid='$uid' and type='$goal[type]'")>=$goal['count'];
    }
    else if($goal['sort'] == 9)	//9:名将
    {
    	return sql_fetch_one_cell("select count(*) from sys_city_hero where uid='$uid' and npcid='$goal[type]'")>=$goal['count'];
    }
	else if($goal['sort'] == 101)	//101:活动临时事件,杀死将领
	{
		return sql_fetch_one_cell("select count from temp_act_event where uid='$uid' and eid='$goal[type]'")>=$goal['count'];
	}
	else if($goal['sort'] == 102)	//102:活动临时事件,当天战场胜利
	{
		$battleLv = intval($goal['strong_level']);//要求战场等级
		if($battleLv==0){//0表示没等级限制
			return sql_fetch_one_cell("select count(*) from log_battle_honour where uid=$uid and result=0 and battleid='$goal[type]' and quittime>=unix_timestamp(curdate())")>=$goal['count'];
		}else{
			return sql_fetch_one_cell("select count(*) from log_battle_honour where uid=$uid and result=0 and battleid='$goal[type]' and level=$battleLv and quittime>=unix_timestamp(curdate())")>=$goal['count'];
		}
	}
	else if($goal['sort'] == 103)	//103:活动将领
	{
		return sql_fetch_one_cell("select count(*) from sys_city_hero where uid=$uid and herotype=$goal[type]")>=$goal['count'];
	}else if($goal['sort'] == 104)	//104:战场杀将活动 当天杀将//处理方式已改变，为兼容考虑，先放一段时间
	{//处理战场里的同名将--同一战场杀两个将就会出错了。。。
		$act = sql_fetch_one("select starttime, endtime, type from cfg_act where actid=$goal[type]");
		$acts=sql_fetch_rows("select actid from cfg_act where starttime=$act[starttime] and endtime=$act[endtime] and type=$act[type]");
		$actStr = '-1';
		foreach ($acts as $act){
			$actStr.=",".$act['actid'];
		}
		return sql_fetch_one_cell("select count(count) from log_act where uid=$uid and actid in ($actStr) and time>=unix_timestamp(curdate())")>=$goal['count'];
	}else if($goal['sort'] == 105)	//临时活动事件
	{
	}
	else if($goal['sort'] == 50||$goal['sort'] == 80) // 50:需累计完成任务 80:要求单次完成
	{
		if($goal['currentcount'] >= $goal['count'])
		{ 
			return true;		
		}
	}
	else if($goal['sort'] == 10 || $goal['sort'] == 11 || $goal['sort'] == 12)//名将专属任务 - 掠夺 - 侦查
	{
		if(1==sql_fetch_one_cell("select `state` from sys_attack_position where uid='$uid' and tid='$goal[tid]'"))
		{
			return true;
		}
		else {
			return false;
		}
	}
	else if($goal['sort'] == 13) //建筑等级
	{
		$lastcid = sql_fetch_one_cell("select lastcid from sys_user where uid='$uid'");
		return sql_fetch_one_cell("select max(level) from sys_building where cid=$lastcid and bid='$goal[type]'")>=$goal['count'];
	}
	else if($goal['sort'] == 14) //兵种转化
	{
		return sql_fetch_one_cell("select count(*) from log_soldier_convert where uid=$uid")>0;
	}
	else if($goal['sort'] == 202) //俘虏营俘虏数量
	{
		$lastcid = sql_fetch_one_cell("select lastcid from sys_user where uid='$uid'");
		$sid=$goal['type'];
		if($sid==0){	//0,不分兵种
			$count = sql_fetch_one_cell("select sum(count) from mem_city_captive  where cid='$lastcid'");
		}else{
			$count = sql_fetch_one_cell("select sum(count) from mem_city_captive  where cid='$lastcid' and sid='$sid'");
		}
		return $count>=$goal['count'];
	}
	else if($goal['sort'] == 201) //武将等级
	{
		$herotype = $goal['type'];
		if ($herotype==155) {//火鸡猎人
			$herocnt = sql_fetch_one_cell("select count(*) from sys_city_hero where  herotype=$herotype and state=0 and uid='$uid'");
			return ($herocnt>=$goal['count']);
		}
		
		$herolevel=sql_fetch_one_cell("select max(level) from sys_city_hero where  herotype=$herotype and state=0 and uid='$uid'"); //142热血勇士
		if($herolevel){
			$stronglv = intval($goal['strong_level']);
			return $herolevel>=$stronglv;
		}
		return false;
	}else if($goal['tid'] == 101762){ //软合服开服活动
		$openTime = sql_fetch_one_cell("select value from mem_state where state=6");//开服时间
		//$lastsTime = 3600*24*14;//2周
		//$endtime=$openTime+$lastsTime;
		//return sql_fetch_one_cell("select -sum(count) from log_goods where uid='$uid' and gid=82 and count<0 and `type`=0 and time between $openTime and $endtime")>=3;
		return sql_fetch_one_cell("select -sum(count) from log_goods where uid='$uid' and gid=82 and count<0 and `type`=0")>=3;
	}else if($goal['tid'] == 101763 || $goal['tid'] == 101764 || $goal['tid'] == 101765 || $goal['tid'] == 101766){
		$unionid=sql_fetch_one_cell("select union_id from sys_user where uid='$uid' and union_id>0 limit 1");
		if($unionid>0){
			$users=sql_fetch_one_cell("select group_concat(uid)  from sys_user where union_id=$unionid");
			//$citys=array(498244,477336,383198,346448,328361,314128,306149,291251,278370,269364,251431,249221,225061,216474,216271,187383,179369,139490,130321,112404,94218,92463,89277,81497,40066,17455);
			$citysinfo='不落之城';
			switch($goal['tid']){
			 case 101763: return sql_fetch_one_cell("select count(1) from sys_city where name='$citysinfo' and uid in ($users)")>=1;
			 case 101764: return sql_fetch_one_cell("select count(1) from sys_city where type=1 and uid in ($users)")>=10;
             case 101765: return sql_fetch_one_cell("select count(1) from sys_city where type=2 and uid in ($users)")>=5;
             case 101766: return sql_fetch_one_cell("select count(1) from sys_city where type=3 and uid in ($users)")>=1;
			}
			/*
			if($goal['tid'] == 101763)
				return sql_fetch_one_cell("select count(1) from sys_city where type=1 and uid in ($users)")>=1;
			else if($goal['tid'] == 101764){
				return sql_fetch_one_cell("select count(1) from sys_city where type=1 and uid in ($users)")>=10;
			}
			else if($goal['tid'] == 101765){
				return sql_fetch_one_cell("select count(1) from sys_city where type=3 and uid in ($users)")>=1;
			}
			else if($goal['tid'] == 101765){
				return sql_fetch_one_cell("select count(1) from sys_city where type=3 and uid in ($users)")>=1;
			}
			*/
		}
	}else if($goal['tid'] == 102012){
		return (sql_check("select 1 from sys_city_hero where uid=$uid and herotype=147"))&&(sql_check("select 1 from sys_goods where uid=$uid and gid=19999 and count>0"));
	}else if($goal['sort']==203){//备战洛阳任务是否完成检测
		return checkBeiZhanLuoYang($uid);
	}else if($goal['sort'] == 204){//在线时间
		return sql_fetch_one_cell("select unix_timestamp()-`time` from log_login where uid=$uid order by `time` desc limit 1")>=$goal['count']*60;
	}else if($goal['sort']==205){//建筑的个数是不是到达了制定的数目
		$cid=sql_fetch_one_cell("select lastcid from sys_user where uid='$uid'");
		$type=$goal['type'];
		$bcount=sql_fetch_one_cell("select count(id) from sys_building where cid=$cid and bid=$type  and state=0");
		if($bcount>=$goal['count']){
			return true;
		}
	}else if($goal['sort']==206){
		$cid=sql_fetch_one_cell("select lastcid from sys_user where uid='$uid'");
		$sid=$goal['type'];
		$count=sql_fetch_one_cell("select `count` from sys_city_soldier where cid=$cid and sid='$sid' ");	
		return $count>=$goal['count'];
	}
//	}else if($goal['sort']==206){//
//		if($goal['type']==0){//强化装备
//			return true ;
//		}
//	}
	return false;
}
function checkTaskComplete($uid,&$tasklist)
{
	$completeTask = false;
	$firstSet = true;
	
	
    foreach($tasklist as &$task)
    {
        $goals = sql_fetch_rows("select g.*,u.uid, u.currentcount from cfg_task_goal g left join sys_user_goal u on u.gid=g.id and u.uid='$uid' where g.tid='$task[id]'");
       
        $complete = true;
        foreach($goals as $goal)
        {
            if (!checkGoalComplete($uid,$goal))
            {
                $complete = false;
                break;
            }
        }
        $tid = $goal["tid"];
        if (sql_check("select 1 from cfg_task_goal where tid='$tid' and sort=100")){
    	    if (empty($goal["uid"])){
    		    $complete = false;
    	    }
    	    else{
    	    	$complete = true;    	    	
    	    }
   		}
   		
   		$task['state'] = $complete;
   		if( true==$complete && true==$firstSet){
   			sql_query("insert into sys_alarm (`uid`,`task`) values ('$uid',1) on duplicate key update `task`=1");
   			$firstSet=false;
   		}
    }
    
    return $firstSet;
}

function dropTask($uid,$param)
{
	$taskgroup=intval(array_shift($param));
	if ($taskgroup>=200000){
		$param1=array();
		$param1[] = 4;
		sql_query("delete from sys_user_reward_task where uid='$uid' and tid=$taskgroup");
		sql_query("update sys_pub_reward_task set number=number-1 where id='$taskgroup'");
		return getAllTaskByType($uid,$param1);
	}
	if($taskgroup%10 == 1)
	{
		$npcid=intval(floor(($taskgroup-20001)/10));
		$taskmax = (40000+$npcid)*10+9;
		$taskmin = (40000+$npcid)*10+1;
		
		if(2==sql_fetch_one_cell("select `state` from sys_lionize where uid=$uid and npcid=$npcid"))
		{
			deleteHero($uid,$npcid);
			//sql_query("update sys_lionize set friend=friend-10 where `state`=2 and uid='$uid' and npcid=$npcid");
		}
		
		sql_query("delete from sys_attack_position where tid>=$taskmin and tid<=$taskmax");
		sql_query("delete from sys_user_goal where uid='$uid' and gid>=$taskmin and gid<=$taskmax");
	    sql_query("delete from sys_user_task where uid='$uid' and tid>=$taskmin and tid<=$taskmax");
	    sql_query("delete from sys_hero_task where uid='$uid' and `group`=$taskgroup");
	    sql_query("insert into log_lionize(uid,npcid,`type`,`count`,`time`)  select  uid,npcid,18,0,unix_timestamp() from sys_lionize where uid='$uid' and npcid=$npcid");
		sql_query("delete from sys_lionize where uid='$uid' and npcid=$npcid");
	}
	else {
		$npcid=intval(floor(($taskgroup-20000)/10));
		$taskid1=20000+$npcid;
		$taskid2=30000+$npcid;
		sql_query("delete a from sys_user_goal a,cfg_task_goal b where a.uid='$uid' and a.gid=b.id and (b.tid='$taskid1' or b.tid='$taskid2')");
	    sql_query("delete from sys_user_task where uid='$uid' and (tid='$taskid1' or tid='$taskid2')");
	}
    $param2=array();
    $param2[]=2;
    return getAllTaskByType($uid,$param2);
}

//完成任务计数
function checkTaskCount($uid,$tasklist)
{
	$count = 0;

	foreach($tasklist as &$task)
	{
		$goals = sql_fetch_rows("select g.*,u.uid, u.currentcount from cfg_task_goal g left join sys_user_goal u on u.gid=g.id and u.uid='$uid' where g.tid='$task[id]'");
			
		$complete = true;
		foreach($goals as $goal)
		{
			if (!checkGoalComplete($uid,$goal))
			{
				$complete = false;
				break;
			}
		}
		
		$tid = $goal["tid"];
        if (sql_check("select 1 from cfg_task_goal where tid='$tid' and sort=100")){
			if (empty($goal["uid"])){
				$complete = false;
			}
			else{
				$complete = true;

			}
		}
			
		if($complete==true)
		{
			$count++;
		}
		
	}

	return $count;
}


function dropSysTask($uid,$param)
{
	$tid=intval(array_shift($param));
	$task = sql_fetch_one("select * from cfg_task where id='$tid'");
	if(empty($task)) {
		throw new Exception($GLOBALS['sysTask']['not_task_of_user']);
	}
	if($task['group'] < 80000 || $task['group'] > 99999) {
		throw new Exception($GLOBALS['sysTask']['not_systask']);
	}
	$task = sql_fetch_one("select * from sys_user_task where uid='$uid' and tid='$tid'");
	if(!empty($task)) {
	    sql_query("update sys_user_task set state=1 where uid='$uid' and tid='$tid'");
	}
    $param2=array();
    $param2[]=7;
    return getAllTaskByType($uid,$param2);
}

function getTaskTypeGroupList($uid,$param)
{
	
	//checkCompletedTaskList($uid);
		
	$type=intval(array_shift($param));
	if( $type<0||$type>7 ) $type=0;
	$ret = array();
	if($type == 7) {
		//doGetResetSystemTask($uid);
		$lastUpdate = sql_fetch_one_cell("select substr(from_unixtime(last_reset_sys_task),1,10) from mem_user_schedule where uid=$uid");
		$curDate = sql_fetch_one_cell("select substr(now(),1,10)");
		//如果上次刷新时间和当前时间不是同一天，就让玩家免费重新刷新一次。
		if ($lastUpdate != $curDate) {
			//首先重置调度时间。
			sql_query("update mem_user_schedule set last_reset_sys_task=unix_timestamp() where uid='$uid'");
			sql_query("insert into mem_user_systask_num(uid,count) values($uid,0) on duplicate key update count=0");
		}
		sql_query("update sys_user_task a, sys_user_taskstate b set a.state=1 where a.uid=$uid and a.uid =b.uid and a.tid =b.tid and  b.endtime<unix_timestamp()");
		$task=sql_fetch_rows("select distinct g.* as id from sys_user_task u,cfg_task t,cfg_task_group g where u.tid=t.id and u.uid='$uid' and u.state=0 and g.id = t.`group` and g.type='$type' order by g.id");		
		
		//完成任务计数
		foreach ($task as &$thegroup)
		{
			$tasklist = sql_fetch_rows("select t.* from sys_user_task u,cfg_task t where u.uid='$uid' and u.tid=t.id and t.group='$thegroup[id]' and u.state=0");			
			$thegroup['count']=checkTaskCount($uid,$tasklist);			
		}
	
		$ret[]=$task;
		$count = sql_fetch_one_cell("select `count` from mem_user_systask_num where uid=$uid");	
		if(empty($count)) {
			$ret[] = 0;
		} else {
			$ret[] = $count;
		}
	}
	else if($type<=3 || $type>=5)
	{
		if ($type == 1) {//日常任务
			checkDailyTask($uid);
		}
		if(isBeiZhanLuoYangVisible($uid)){
			$groupStr = sql_fetch_one_cell("select group_concat(distinct(t.`group`)) as id from sys_user_task u,cfg_task t where u.tid=t.id and u.uid='$uid' and u.state=0");		
		}else{
			$groupStr = sql_fetch_one_cell("select group_concat(distinct(t.`group`)) as id from sys_user_task u,cfg_task t where u.tid=t.id and u.uid='$uid' and u.state=0 and t.`group`<>112001");//不可见就不刷备战洛阳任务组					
		}
		if(empty($groupStr)) 
			$task=array();
		else
			$task=  sql_fetch_rows("select * from cfg_task_group where id in ($groupStr) and type='$type' order by priority,id");

		$heroGroupStr=sql_fetch_one_cell("select group_concat(distinct(`group`)) from sys_hero_task where uid=$uid and `state`=1");
		if(empty($heroGroupStr)) 
			$herotask=array();	
		else	
			$herotask = sql_fetch_rows("select * from cfg_task_group where `type`=$type and id in ($heroGroupStr) order by priority,id");		
    	$task=array_merge($task,$herotask);
    	
		//完成任务计数
		foreach ($task as &$thegroup)
		{
			$tasklist = sql_fetch_rows("select t.* from sys_user_task u,cfg_task t where u.uid='$uid' and u.tid=t.id and t.group='$thegroup[id]' and u.state=0");			
			$tasklist_two = sql_fetch_rows("select t.* from sys_hero_task u,cfg_task t where u.uid='$uid' and u.tid=t.id and u.group='$thegroup[id]' and u.state=1");			
			$tasklist=array_merge($tasklist,$tasklist_two);
			$count = checkTaskCount($uid,$tasklist);	
			$thegroup['count']=$count;		
		}
		
		
		$ret[]=$task;
		$ret[]=sql_fetch_one_cell("select value from mem_state where state=7");
		$ret[]=sql_fetch_one_cell("select value from mem_state where state=8");
		
		//获取是否官爵
//		$hotelLevel = sql_fetch_one_cell("select level from sys_building where cid='$cid' and bid=".ID_BUILDING_HOTEL);
//		$ret[] = $hotelLevel;
	
		$nobility=sql_fetch_one_cell("select nobility from sys_user where uid='$uid'");
		$nobility = getBufferNobility($uid,$nobility);
    	if ($nobility<5) {
        	$ret[] = false;
    	}
    	else {
    		$ret[] = true;
    	}
	}
	else if($type==4){
    	//委托任务
    	$groupStr=sql_fetch_one_cell("select group_concat(tid) from sys_user_reward_task where uid='$uid' and state<=0 and tid>=200000");
    	if (empty($groupStr)) 
    		$tasks=array();
    	else
    		$tasks = sql_fetch_rows("select * from sys_pub_reward_task where id in ($groupStr)");
    	$index=0;
    	$ret[]=array();
    	foreach($tasks as $task){
    		$task['groupid']=$task['id'];
    		
    		//完成任务计数
			//$tasklist = sql_fetch_rows("select t.* from sys_user_task u,cfg_task t where u.uid='$uid' and u.tid=t.id and t.group='$task[groupid]' and u.state=0");				
    		$taskstate=sql_fetch_one_cell("select `state` from sys_user_reward_task where uid=$uid and tid='$task[id]'");
			if($taskstate<0)
			{
				$task['count']=1;
			}
			else {
				$task['count']=0;
			}	
    		
    		$task['description']=$GLOBALS['recordTask']['task_group_description'];
    		if($task['type']=="0")
    			$task['name']=$GLOBALS['recordTask']['task_group_name_0'];    		
    		else if($task['type']=="1")
    			$task['name']=$GLOBALS['recordTask']['task_group_name_1'];
    		else if($task['type']=="2")
    			$task['name']=$GLOBALS['recordTask']['task_group_name_2'];
    		$ret[0][$index++]=$task;	
    	}    	
    }
    
    return $ret;
}
/*
 * 检查用户是否有开源节流任务，如果没有，就增加上。避免在c++全服检查，增加计算量。
 */
function checkDailyTask($uid) {
	$res = sql_fetch_one_cell("select count(*) from sys_user_task where tid in(801,802,803,804,805) and uid=$uid");
	if ($res == 0) {
	    sql_query("insert into sys_user_task (uid,tid,state) values($uid,801,0)");
		sql_query("insert into sys_user_task (uid,tid,state) values($uid,802,0)");
		sql_query("insert into sys_user_task (uid,tid,state) values($uid,803,0)");
		sql_query("insert into sys_user_task (uid,tid,state) values($uid,804,0)");
		sql_query("insert into sys_user_task (uid,tid,state) values($uid,805,0)");
	}
}

function getAllTaskByType($uid,$param)
{
	
	//checkCompletedTaskList($uid);
		
	$type=intval(array_shift($param));
	if( $type<0||$type>7 ) $type=0;
	$ret = array();
	if($type<=3 || $type>=5) {		
		
    	$rec =  sql_fetch_rows("select distinct g.* from sys_user_task u,cfg_task t,cfg_task_group g where t.group = g.id and u.tid=t.id and u.uid='$uid' and u.state=0  and g.type ='$type' order by g.id");
    	$herotask = sql_fetch_rows("select distinct g.* from sys_hero_task a,cfg_task_group g where g.`type`='$type'  and a.`group` = g.id and a.uid='$uid' and a.`state`=1  order by id");    	
    	$herotask = array_merge($rec,$herotask);
    	
		//完成任务计数
		foreach ($herotask as &$thegroup)
		{
			$tasklist = sql_fetch_rows("select t.* from sys_user_task u,cfg_task t where u.uid='$uid' and u.tid=t.id and t.group='$thegroup[id]' and u.state=0");			
			$tasklist_two = sql_fetch_rows("select t.* from sys_hero_task u,cfg_task t where u.uid='$uid' and u.tid=t.id and u.group='$thegroup[id]' and u.state=1");			
			$tasklist=array_merge($tasklist,$tasklist_two);
			$thegroup['count']=checkTaskCount($uid,$tasklist);			
		}
		$ret[]=$herotask;
		
    	foreach($herotask as $taskgroup) {
    		$groupid = $taskgroup['id'];
    		$nextparam = array();
    		$nextparam[] = $groupid;
    		$ret[] = getTaskList($uid,$nextparam);
    	}
		$count = sql_fetch_one_cell("select `count` from mem_user_systask_num where uid=$uid");	
		if(empty($count)) {
			$ret[] = 0;
		} else {
			$ret[] = $count;
		}
	}
    else if($type==4){
    	//委托任务
    	
    	$tasks =  sql_fetch_rows("select a.* from sys_pub_reward_task a,sys_user_reward_task b where a.id =b.tid and b.uid='$uid' and b.state<=0 and b.tid>=200000");
    	$index=0;
    	$ret[]=array();
    	foreach($tasks as $task){
    		$task['groupid']=$task['id'];
    		
    		$taskstate=sql_fetch_one_cell("select `state` from sys_user_reward_task where uid=$uid and tid='$task[id]'");
			if($taskstate<0)
			{
				$task['count']=1;
			}
			else {
				$task['count']=0;
			}
    		
    		$task['description']=$GLOBALS['recordTask']['task_group_description'];
    		if($task['type']=="0")
    			$task['name']=$GLOBALS['recordTask']['task_group_name_0'];    		
    		else if($task['type']=="1")
    			$task['name']=$GLOBALS['recordTask']['task_group_name_1'];
    		else if($task['type']=="2")
    			$task['name']=$GLOBALS['recordTask']['task_group_name_2'];
    		$ret[0][$index++]=$task;
    		$tasklist = array();
    		$tasklist[] = $task;
    		$tasktemp = array();
    		$tasktemp[] = $tasklist;
    		$ret[] = $tasktemp;	
    	}    	
    }
    
    return $ret;
}

function getTaskList($uid,$param)
{
    $groupid = intval(array_shift($param));
    
    if($groupid>=200000){
    	return getUserRewardTaskList($uid,$groupid);   
    }
	//$tasklist = sql_fetch_rows("select t.* from sys_hero_task h right join cfg_task t on h.tid=t.id left join sys_user_task u on t.id=u.tid where u.uid='$uid' and h.uid=$uid and u.state=0 and h.group=$group and t.group=$group or t.group=-1");
    $tasklist = sql_fetch_rows("select t.* from sys_user_task u,cfg_task t where u.uid='$uid' and u.tid=t.id and t.group='$groupid' and u.state=0 order by t.id");                 
   	//$tasklist.push(sql_fetch_rows("select t.* from  sys_hero_task h left join cfg_task t on t.id=h.tid where h.uid=$uid and h.group=$group"));
   	$templist = sql_fetch_rows("select t.* from sys_hero_task h,cfg_task t where h.uid=$uid and h.group=$groupid and t.id=h.tid and h.state=1");
   	$tasklist=array_merge($tasklist,$templist);
    checkTaskComplete($uid,$tasklist); 
    $ret = array();
    $ret[] = $tasklist;
    $ret[] = checkTaskCount($uid,$tasklist);	
    
    return $ret;
}

function checkCompletedTaskList( $uid )
{	
	$tasklist = sql_fetch_rows("select t.* from sys_user_task u,cfg_task t where u.uid='$uid' and u.tid=t.id and u.state=0");                 
    $firstSet = checkTaskComplete($uid,$tasklist);
    
    
    if (true==$firstSet){
    	//check 委托任务
    	$tasks=sql_fetch_rows("select * from sys_user_reward_task where uid='$uid' and state=-1");
	    if(!empty($tasks)){
	    	sql_query("insert into sys_alarm (`uid`,`task`) values ('$uid',1) on duplicate key update `task`=1");
	    }
	    else{
	    	sql_query("update sys_alarm set `task`='0' where `uid`='$uid'");
	    }
    }
}

function getUserRewardTaskList($uid,$groupid){
	$ret=array();
	$tasks=sql_fetch_rows("select * from sys_user_reward_task where uid='$uid' and tid='$groupid' and state<=0");
	$ret[]=array();
	//checkTaskComplete($uid,$tasklist); 
	$index=0;
    foreach($tasks as $task){
    	
    	if($task['type']=="0")
    		$task['name']=$GLOBALS['recordTask']['task_group_name_0'];    		
    	else if($task['type']=="1")
    		$task['name']=$GLOBALS['recordTask']['task_group_name_1'];
    	else if($task['type']=="2")
    		$task['name']=$GLOBALS['recordTask']['task_group_name_2'];
    		
    	if($task['state']=="0"){
    		//未完成
    		$task['state']=false;
    	}else if($task['state']=="-1"){
    		//完成未领取奖励
    		$task['state']=true;
    	}
    	$task['id']=$task['tid'];
    	$ret[0][$index++]=$task;	  		
    }   
	return $ret;
}
function getRewardTaskDetail($uid,$tid,$param)
{
	$ret = array();

    $taskinfo = sql_fetch_one("select * from sys_pub_reward_task where id='$tid'");
    $todo = $GLOBALS['recordTask']['task_content_prefix'].$taskinfo['todo'];
    $taskinfo['content']=$todo;
    $taskinfo['todo']=$GLOBALS['recordTask']['task_group_description'];
    
	if($taskinfo['state']=="0" || $taskinfo['finishuid']!=$uid){
    	//未完成
    	$taskinfo['state']=false;
    }else if($taskinfo['state']=="-1"){
    	//完成为领取奖励
    	$taskinfo['state']=true;
    }
    if($taskinfo['type']=="0")
    	$taskinfo['name']=$GLOBALS['recordTask']['task_group_name_0'];    		
    else if($taskinfo['type']=="1")
    	$taskinfo['name']=$GLOBALS['recordTask']['task_group_name_1'];
    else if($taskinfo['type']=="2")
    	$taskinfo['name']=$GLOBALS['recordTask']['task_group_name_2'];
    
    $ret[]=$taskinfo;
    
    $goals=array();
    $goals[0]["content"]=genRewardTaskGoal($taskinfo['targetcid'],$taskinfo['targetname'],$taskinfo['type'],$taskinfo['goal']);
    $goals[0]["state"]=$taskinfo['state'];
    
    $reward=array();
    $reward[0]['sort']=1;
    $reward[0]['type']=20;
    $reward[0]['count']=$taskinfo["money"];
    
    $ret[]=$goals;
    $ret[]=$reward;
    
    
    return $ret;
}
function genRewardTaskContent($targetcid,$targetname,$endtime,$type,$goal){
	$result="";
	$pos=getPosition($targetcid);
	$time=MakeEndTime($endtime);
	if($type==0)
		return sprintf($GLOBALS['recordTask']['task_content_0'],"",$time,$targetname,$pos,$goal);
	else if($type==1)
		return sprintf($GLOBALS['recordTask']['task_content_1'],"",$time,$targetname,$pos,$goal);
	else if($type==2){
		if($goal==0)
			return sprintf($GLOBALS['recordTask']['task_content_2'],"",$time,$targetname,$pos);
		else if($goal==1)
			return sprintf($GLOBALS['recordTask']['task_content_3'],"",$time,$targetname,$pos);
	}
}
function genRewardTaskGoal($targetcid,$targetname,$type,$goal){
	$pos=getPosition($targetcid);
	if($type==0)
		return sprintf($GLOBALS['recordTask']['goal_0'],$targetname,$pos,$goal);
	else if($type==1)
		return sprintf($GLOBALS['recordTask']['goal_1'],$targetname,$pos,$goal);
	else if($type==2){
		if($goal==0)
			return sprintf($GLOBALS['recordTask']['goal_2'],$targetname,$pos);
		else if($goal==1)
			return sprintf($GLOBALS['recordTask']['goal_3'],$targetname,$pos);
	}
}


function getTaskDetail($uid,$param)
{                                 
    $tid = intval(array_shift($param));
    //悬赏任务
    if($tid>=200000 && $tid<400000){
    	return getRewardTaskDetail($uid,$tid,$param);  
    }
    
    $goalSortType = sql_fetch_one_cell("select sort from cfg_task_goal where tid=$tid limit 1");
    $ret = array();

    $ret[] = sql_fetch_one("select * from cfg_task where id='$tid'");
    
    $goals = sql_fetch_rows("select g.*,u.uid, u.currentcount from cfg_task_goal g left join sys_user_goal u on u.gid=g.id and u.uid='$uid' where g.tid='$tid' order by g.id");

    foreach ($goals as &$goal)
    {
         $goal['state'] = checkGoalComplete($uid,$goal);
    }
    
    if ($tid == 250)    //食君之禄
    {
        foreach ($goals as &$goal)
        {
             $goal['content'] = sql_fetch_one_cell("select p.name from sys_user u left join cfg_office_pos p on p.id=u.officepos where u.uid='$uid'");           
        }    
    }
    else if ($tid == 251)   //采食封邑
    {
        foreach ($goals as &$goal)
        {
             $goal['content'] = sql_fetch_one_cell("select n.name from sys_user u left join cfg_nobility n on n.id=u.nobility where u.uid='$uid'");
        }
    }
    
    
    $ret[] = $goals;
    
    if ($tid == 250)//食君之禄   
    {
        $salary = sql_fetch_one_cell("select p.salary from sys_user u left join cfg_office_pos p on p.id=u.officepos where u.uid='$uid'"); 
        $reward = array();
        $reward[] = array('sort'=>1,'type'=>1,'count'=>$salary);
        $ret[] = $reward;
    }
    else if ($tid == 251)  //采食封邑
    {
        $salary = sql_fetch_one_cell("select n.salary from sys_user u left join cfg_nobility n on n.id=u.nobility where u.uid='$uid'");   
        $reward = array();
        $reward[] = array('sort'=>1,'type'=>2,'count'=>$salary);
        $reward[] = array('sort'=>1,'type'=>3,'count'=>$salary);
        $reward[] = array('sort'=>1,'type'=>4,'count'=>$salary);
        $reward[] = array('sort'=>1,'type'=>5,'count'=>$salary);
        $ret[] = $reward;
    }
    else if ($tid==279)	//共享利益
    {
    	$gold=getUnionFamousCityGold($uid);
    	$reward=array();
        $reward[] = array('sort'=>1,'type'=>1,'count'=>$gold);
        $ret[] = $reward;
    }
    else
    {
    	$rewards = sql_fetch_rows("select * from cfg_task_reward where tid='$tid' order by type asc");
    	$battleLevel = 0;
    	foreach( $rewards as &$reward){
    	    if ($goalSortType==100){
    	    	//单服战场奖励
    	    	if ($battleLevel==0){
        		    $battleLevel = sql_fetch_rows("select level from sys_user_battle_state where uid='$uid' and state=0");
        		    if(empty($battleLevel)) {
        		    	$battleLevel = sql_fetch_rows("select level from log_battle_honour where uid='$uid' order by quittime desc limit 1");
        		    }
    	    	}
        		$reward["count"] = $battleLevel[0]["level"]*$battleLevel[0]["level"]*$reward["count"];
        	}else if($goalSortType==110){
        		//任务奖励不再根据等级加成
        		//跨服战场奖励
        		//$battleLevel=sql_fetch_one_cell("select level from sys_user_battle_state where uid='$uid' and state=0 and in_cross_battle=1");
        		//$areaLevel=3;
        		//if(empty($battleLevel)) {
        		//	$areaLevel=sql_fetch_one_cell("select area_level from log_user_battlenet where uid=$uid order by quittime desc limit 1");
        		//}else{
        		//	if($battleLevel==12)$areaLevel=0;
        		//	else if($battleLevel==10)$areaLevel=1;
        		//	else if($battleLevel==5)$areaLevel=2;
        		//	else if($battleLevel==1)$areaLevel=3;
        		//}
        		//$reward["count"] = (4-$areaLevel)*$reward["count"];
        	}else if($goalSortType==120) {
                //跨服PVE战场奖励
                $battleLevel=sql_fetch_one_cell("select level+1 from sys_user_battle_state where uid='$uid' and state=0 and in_cross_battle=1");
                if(empty($battleLevel)) {
                  $battleLevel=sql_fetch_one_cell("select area_level+1 from log_user_battlenet where uid=$uid order by quittime desc limit 1");
                } 
                $reward["count"] = intval(($battleLevel+1)*$reward["count"]/2);
            }
    	}
        $ret[] = $rewards;
    }

    
    return $ret;
}      

function giveResource($uid,$cid,$type,$count,$log_money_type=60,$retmsg=false)
{                                                                   
	$msg="";                       
    switch($type)
    {
        case 1:                                                                                   
            sql_query("update mem_city_resource set gold=gold+'$count' where cid='$cid'");
            if($retmsg)$msg=sprintf($GLOBALS['resPackage']['gain_gold'],$count);
            break;
        case 2:                                                                         
            sql_query("update mem_city_resource set food=food+'$count' where cid='$cid'");
            if($retmsg)$msg=sprintf($GLOBALS['resPackage']['gain_food'],$count);
            break;
        case 3:           
            sql_query("update mem_city_resource set wood=wood+'$count' where cid='$cid'");
            if($retmsg)$msg=sprintf($GLOBALS['resPackage']['gain_wood'],$count);  
            break;
        case 4:
            sql_query("update mem_city_resource set rock=rock+'$count' where cid='$cid'");
            if($retmsg)$msg=sprintf($GLOBALS['resPackage']['gain_rock'],$count);
            break;
        case 5:
            sql_query("update mem_city_resource set iron=iron+'$count' where cid='$cid'");
            if($retmsg)$msg=sprintf($GLOBALS['resPackage']['gain_iron'],$count);
            break; 
        case 6:                                                                          
            sql_query("update mem_city_resource set people=people+'$count' where cid='$cid'");
            if($retmsg)$msg=sprintf($GLOBALS['resPackage']['gain_people'],$count);
            break;   
        case 7:                                                                               
    		if($retmsg)
				$msg=sprintf($GLOBALS['resPackage']['gain_morale'],sql_fetch_one_cell("select LEAST(100-morale,'$count') from mem_city_resource where cid='$cid' "));						
        	//TODO Morale                                                                   
            sql_query("update mem_city_resource set morale=LEAST(100,morale+'$count'),`people_stable`=`people_max` * LEAST(100,morale+'$count') * 0.01 where cid='$cid'");
            break;   
        case 8:			                                                                              
            sql_query("update mem_city_resource set complaint=GREATEST(0,complaint+'$count') where cid='$cid'");
            break;     
        case 9:     
        	if($retmsg) $msg=sprintf($GLOBALS['resPackage']['gain_prestige'],sql_fetch_one_cell("select GREATEST(100-morale,'$count') from mem_city_resource where cid='$cid' "));                                                                                 
            sql_query("update sys_user set prestige=prestige+'$count',warprestige=warprestige+'$count' where uid='$uid'");
            break;
        case 17:
            sql_query("update sys_user set officepos=greatest(officepos,'$count') where uid='$uid'");
            if($retmsg) $msg=sprintf($GLOBALS['resPackage']['gain_officepos'],sql_fetch_one_cell("select name from cfg_office_pos where id='$count' "));  
            $officepos = sql_fetch_one_cell("select name from cfg_office_pos where id='$count'");         
             if (defined("PASSTYPE")){
             	try{
		            require_once 'game/agents/AgentServiceFactory.php';            
		            AgentServiceFactory::getInstance($uid)->addOfficePosEvent($officepos);
             	}catch(Exception $e){
					try{
						file_put_contents("./agents/log/interface-error.log",date("Y-m-d H:i:s",time())." || ".$e->getMessage()."\n",FILE_APPEND);
					}catch(Exception $err){
						
					}
				}
            }
            if(defined("USER_FOR_51") && USER_FOR_51){
            	try{
	            	require_once("51utils.php");				
	    			return add51OfficePosEvent($officepos);
            	}catch(Exception $e){
					try{
						file_put_contents("./agents/log/interface-error.log",date("Y-m-d H:i:s",time())." || ".$e->getMessage()."\n",FILE_APPEND);
					}catch(Exception $err){
						
					}
				}
            }    
            break;                 
        case 18:
            sql_query("update sys_user set nobility=greatest(nobility,'$count') where uid='$uid'");
            if($retmsg) $msg=sprintf($GLOBALS['resPackage']['gain_nobility'],sql_fetch_one_cell("select name from cfg_nobility where id='$count' ")); 
            $nobilityname = sql_fetch_one_cell("select name from cfg_nobility where id='$count'");
            //成就系统：：爵位成就
            if($count==1){
            	finishAchivement($uid,37);
            }else if($count==5){
            	finishAchivement($uid,38);
            }else if($count==11){
            	finishAchivement($uid,39);
            }else if($count>=14){
            	finishAchivement($uid,$count+26);
            }
            //接口
            if (defined("PASSTYPE")){
            	try{
		            require_once 'game/agents/AgentServiceFactory.php';
		            AgentServiceFactory::getInstance($uid)->addNobilityEvent($nobilityname);
            	}catch(Exception $e){
					try{
						file_put_contents("./agents/log/interface-error.log",date("Y-m-d H:i:s",time())." || ".$e->getMessage()."\n",FILE_APPEND);
					}catch(Exception $err){
						
					}
				}
            }       
            if(defined("USER_FOR_51") && USER_FOR_51){
            	try{
	            	require_once("51utils.php");				
	    					return add51NobilityEvent($nobilityname);
            	}catch(Exception $e){
					try{
						file_put_contents("./agents/log/interface-error.log",date("Y-m-d H:i:s",time())." || ".$e->getMessage()."\n",FILE_APPEND);
					}catch(Exception $err){
						
					}
				}
            }  
            break;
        case 19:
            addGift($uid,$count,$log_money_type);
            if($retmsg) $msg=sprintf($GLOBALS['resPackage']['gain_yuanbao'],$count);   
            break;
		case 20:
			addMoney($uid,$count,54);
			break;
		case 22:
			addMoney($uid,$count,57);//57任务扣除
			break;
        case 122:
        	addMoney($uid,$count,$log_money_type);
        	break;
        case 30:
        	sql_query("update sys_user set honour=honour+'$count' where uid='$uid'");
        	break;
        case 500:
        	sendRemoteRequest($uid,"addBattleNetScore",intval($count));
        	break;
        case 501:
        	sendRemoteRequest($uid, "addUserGongxun",intval($count));
        	break;
        case 502:
        	$tmpparams=array(9001,intval($count));
            sendRemote9001Request($uid, "addPVEBattleScore",$tmpparams);
            break;
    }
    return $msg;
}
function giveGoods($uid,$type,$count,$log_type=4,$retmsg=false)
{
   $msg="";
   addGoods($uid,$type,$count,$log_type);
   if($retmsg){
	   	$goodname=sql_fetch_one_cell("select name from cfg_goods where gid = $type");
	   	if ($count>0) $goodname=$goodname."*".$count;  
	   	$msg=sprintf($GLOBALS['resPackage']['gain_goods'],$goodname);   
   }
   return $msg;
}
function giveArmy($cid,$type,$count,$retmsg=false)
{
	$msg="";
    sql_query("insert into sys_city_soldier (`cid`,`sid`,`count`) values ('$cid','$type','$count') on duplicate key update `count`=`count`+'$count'");
    sql_query("insert into log_city_soldier (`cid`,`sid`,`uid`,`count`,`type`) values ('$cid','$type','0','$count',6) on duplicate key update `count`=`count`+'$count'");
	updateCityResourceAdd($cid);
	if($retmsg){
	   	$name=sql_fetch_one_cell("select name from cfg_soldier where sid = $type");
		if ($count>0) $name=$name."*".$count;  
	   	$msg=sprintf($GLOBALS['resPackage']['gain_soldier'],$name);   
    }
    return $msg;
}
function giveDefence($cid,$type,$count,$retmsg=false)
{
	$msg="";
    sql_query("insert into sys_city_defence (`cid`,`did`,`count`) values ('$cid','$type','$count') on duplicate key update `count`=`count`+'$count'");
	if($retmsg){
	   	$name=sql_fetch_one_cell("select name from cfg_defence where did = $type");
	   	 if ($count>0) $name=$name."*".$count;  
	   	$msg=sprintf($GLOBALS['resPackage']['gain_defence'],$name);   
    }
    return $msg;
}
function giveThings($uid,$type,$count,$log_type=4,$retmsg=false)
{   
   $msg="";
   addThings($uid,$type,$count,$log_type);
   if($retmsg){
	   	$name=sql_fetch_one_cell("select name from cfg_things where tid = $type");  
	   	if ($count>0) $name=$name."*".$count;
	   	$msg=sprintf($GLOBALS['resPackage']['gain_things'],$name);   
   }    
   return $msg;
}
function cutArmorBySid($uid,$armorid,$sid,$log_type=4)
{
	sql_query("delete from sys_user_armor where uid='$uid' and sid='$sid' and hid=0");
	sql_query("insert into log_armor (uid,armorid,count,time,type) values ($uid,$armorid,-1,unix_timestamp(),$log_type)");
}
function cutArmor($uid,$id,$count,$log_type=4)
{
	sql_query("delete from sys_user_armor where uid='$uid' and armorid='$id' and hid=0 limit $count");
	sql_query("insert into log_armor (uid,armorid,count,time,type) values ($uid,$id,-$count,unix_timestamp(),$log_type)");
}
function giveArmor($uid,$type,$count,$log_type=4,$retmsg=false,$stronglevel=0)
{
	$msg="";
	$armor=sql_fetch_one("select * from cfg_armor where id='$type'");
	addArmor($uid,$armor,$count,$log_type,$stronglevel);
	//Log已经在addArmor()里写了，不能重复	
	if($retmsg){
	   	$name=sql_fetch_one_cell("select name from cfg_armor where id = $type");  
	   	if ($count>0) $name=$name."*".$count;
	   	$msg=sprintf($GLOBALS['resPackage']['gain_armor'],$name);   
    }   
    return $msg;
}
function giveMoney($uid,$count,$log_money_type=60,$retmsg=false){		
	  $msg="";
      addGift($uid,$count,$log_money_type);
      if($retmsg) $msg=sprintf($GLOBALS['resPackage']['gain_yuanbao'],$count);  
      return $msg;
}
function cutActEvent($uid,$type,$eid,$count,$log_type=4)
{
	sql_query("insert into temp_act_event (uid,type,eid,count) values ('$uid','$type','$eid','-$count') on duplicate key update count=count-$count");
}
//给任务奖励
function giveReward($uid,$cid,$reward,$log_type=4,$log_money_type=60,$retmsg=false,$stronglevel=0)
{
	if ($reward['sort'] == 1)
	{
		return  giveResource($uid,$cid,$reward['type'],$reward['count'],$log_money_type,$retmsg);
	}
	else if ($reward['sort'] == 2)
	{
		if ($reward["type"]==0)
		return giveMoney($uid,$reward['count'],$log_money_type,$retmsg);
		return    giveGoods($uid,$reward['type'],$reward['count'],$log_type,$retmsg);
	}
	else if ($reward['sort'] == 3)
	{
		$soldier_type=$reward['type'];
		if($soldier_type==0){//随机兵种
			$soldier_type = mt_rand(1,12);
		}
		return  giveArmy($cid,$soldier_type,$reward['count'],$retmsg);
    }
    else if ($reward['sort'] == 4)
    {
        return  giveDefence($cid,$reward['type'],$reward['count'],$retmsg);
    }
    else if ($reward['sort'] == 5)
    {
        return  giveThings($uid,$reward['type'],$reward['count'],$log_type,$retmsg);
    }
    else if ($reward['sort']==6)
    {
		return  giveArmor($uid,$reward['type'],$reward['count'],$log_type,$retmsg,$stronglevel);
    }
    else if ($reward['sort']==10)
    {
		return  giveTask($uid,$reward['type']);
    }
    else if ($reward['sort']==11)
    {
		return  giveTasks($uid,$reward['type']);
    }
	else if ($reward['sort']==8)//NPC前来攻城
	{
		
		sql_insert ( "insert into sys_troops (`uid`,`cid`,`hid`,`task`,`state`,`starttime`,`pathtime`,`endtime`,`soldiers`,`resource`,`people`,`fooduse`,`battlefieldid`,`battleunionid`,`targetcid`,`startcid`,bid,ulimit) 
		  values (443,115345,443,4,'0',unix_timestamp(),'50',unix_timestamp()+50,'1,7,80000','0','0','0','0','0','$cid',115345,0,0)" );
		if($retmsg){
			return "来打你了！！嘻嘻";//return msg TO BE DONE
	    }
	}
	return "";
}

function reduceGoal($uid,$cid,$goal,$log_type=4,$log_money_type=60,$retmsg=false)
{
	switch ($goal['sort'])
	{
		case 1:
			giveResource($uid,$cid,$goal['type'],-$goal['count'],$log_money_type,$retmsg);
			break;
		case 2:
			giveGoods($uid,$goal['type'],-$goal['count'],$log_type,$retmsg);
			break;
		case 3:
			giveArmy($cid,$goal['type'],-$goal['count']);
			break;
		case 4:
			giveDefence($cid,$goal['type'],-$goal['count']);
			break;
		case 5:
			giveThings($uid,$goal['type'],-$goal['count'],$log_type,$retmsg);
			break;
		case 6:
			cutArmor($uid,$goal['type'],$goal['count'],$log_type);
			break;
		case 101://活动临时事件
			cutActEvent($uid,$goal['sort'],$goal['type'],$goal['count']);
			break;
		case 203 :{			
			$unionid=sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
			reduceUnionTasks($unionid,$goal['type'],$goal['count']);
			break;
		}
		case 201 :{	//活动将		
			$herotype=$goal['type'];
			$count=$goal['count'];
			$heros=sql_fetch_rows("select hid from sys_city_hero where uid=$uid and herotype=$herotype and state=0  order by level limit $count ");
			foreach($heros as $hero){
				deleteHero($uid,$hero['hid']);
			}
			break;
		}
		default: break;
	}
}
function reduceUnionTasks($unionid,$tid,$count){
	sql_query("update luoyang_progress set `curvalue`=`curvalue`-$count where union_id=$unionid and tid=$tid");
}

function taskCanRecomplete($tid)
{
	if($tid<100000){
		return (($tid>10000&&$tid<10600)||($tid>11000&&$tid<15000) || ($tid>15000&&$tid<16000) );//史诗任务	
	}elseif ($tid<=100396){//活动任务
		return (($tid>100021&&$tid<100025)||($tid>100140&&$tid<100144)||($tid==100171)||($tid==100201)
				||($tid==100261||$tid==100262)||($tid==100281||$tid==100291||$tid==100292) ||($tid>=100311&&$tid<=100314)
				||($tid>=100321&&$tid<=100324)||($tid>=100341&&$tid<=100343) || ($tid>=100361&&$tid<=100364) 
				|| ($tid>=100381&&$tid<=100384)||($tid>=100391&&$tid<=100396));
	}else {
		$taskStat = sql_fetch_one_cell("select `default` from cfg_task where id=$tid");
		return ($taskStat==100);//100表示不限次数
	}
}


function isHuangJinJuanXian($tid)//黄巾捐献任务
{
	return ($tid>11000&&$tid<15000);
}

function isDongZhuoJuanXian($tid)//讨伐董卓捐献任务
{
	return ($tid>15000&&$tid<16000);
}


function isHuangJinDuiHuan($tid)//讨伐董卓捐献任务的兑换任务
{
	return ($tid>10000&&$tid<10500);
}

function isHuangJinKill($tid)//消灭黄金军任务，在捐献任务完成后触发
{
	return ($tid>10500&&$tid<11000);
}

function triggerDongZhuoTaskByUser($uid){
	$rows=sql_fetch_rows("select id from cfg_task where `group`=15000");
	foreach($rows as $row){
		$tid=$row["id"];
		sql_query("insert into sys_user_task (uid,tid,state) values ('$uid','$tid',0) on duplicate key update state=0");	
	}
}
function triggerDongZhuoOtherTaskByUser($uid){
	$rows=sql_fetch_rows("select id from cfg_task where id >15100 and id <16000");
	foreach($rows as $row){
		$tid=$row["id"];
		sql_query("insert into sys_user_task (uid,tid,state) values ('$uid','$tid',0) on duplicate key update state=0");	
	}	
}
function triggerDongZhuoTask()
{
	sql_query("update mem_state set value=1 where state=7");
	$rows=sql_fetch_rows("select id from cfg_task where `group`=15000");
	foreach($rows as $row){
		$tid=$row["id"];
		sql_query("insert into sys_user_task (uid,tid,state) (select uid,'$tid',0 from sys_user_task where tid=243 and state=1) on duplicate key update state=0");	
	}
	$count = sql_fetch_one_cell("select count(1) from sys_city where uid = 710");
	if (sql_fetch_one_cell("select count(1) from sys_city where uid = 896")<=10){
		$citys=sql_fetch_rows("select cid from sys_city a,mem_world b  where a.cid = b.ownercid and b.state = 0 and b.province<=13 and uid = 710 order by rand() limit ".intval($count/2));
		$cids = array();
		foreach($citys as $city) $cids[]=$city["cid"];			
		sql_query("update sys_city set uid = 896 where cid in (".join(",", $cids).")");
		sql_query("update sys_city_hero set uid = 896 where uid = 710 and cid in (select cid from sys_city where uid = 896)");
	}
	
	//更新战场开启状态
   	$battleInfos = sql_fetch_rows("select battleId from sys_battle_open_condition where otherCondition = 'taoFaDongZhuoShiShiStart'");
	if(!empty($battleInfos))
	{					
		sql_query("delete from sys_battle_open_condition where otherCondition = 'taoFaDongZhuoShiShiStart'");
		foreach ($battleInfos as $battleInfo)
		{
			$count = sql_fetch_one_cell("select count(*) from sys_battle_open_condition where battleId = $battleInfo[battleId]");
			if($count == 0)
			{
				//sql_query("update cfg_battle_field set state = 1 where id = $battleInfo[battleId]");
				openBattleField($battleInfo['battleId']);
			}						
		}
	}
   
}



function finishDongZhuoTask(){
	sql_query("update mem_state set value=3 where state=7");  
	sql_query("update cfg_battle_field set state='1' where id='12001'");    
}
function triggerDongZhuoOtherTask()
{
	$kingUid = sql_fetch_one_cell("select uid from sys_city where cid = 215265");
	if($kingUid==659){//已经是董卓的天下了,防止无限发信
		return ;
	}
	
	$rows=sql_fetch_rows("select id from cfg_task where id >15100 and id <16000");
	foreach($rows as $row){
		$tid=$row["id"];
		sql_query("insert into sys_user_task (uid,tid,state) (select uid,'$tid',0 from sys_user_task where tid=243 and state=1) on duplicate key update state=0");
	}	
	sql_query("update sys_city set chiefhid= 659,uid=659 where cid = 215265");
	sql_query("update sys_city set chiefhid= 0 where cid = 225185");
	sql_query("update mem_state set value=2 where state=7");  
	
	$title=  $GLOBALS['taodong']["mailtitle"];
	$content=$GLOBALS['taodong']["mailcontent"];
	sendAllSysMail($title,"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$content);		
	sendSysInform(0,1,0,300,1800,1,49151,$content);
}
function getHerosByLevel($uid,$param){
	$cid = intval(array_shift($param));
	$type = intval(array_shift($param));
	$value = intval(array_shift($param));
	return sql_fetch_rows("select hid,name from sys_city_hero where uid = $uid and state = 0 and level >=  $value order by level limit 20");	
}
function getArmorsByPartOrType($uid,$param)
{
	$cid = intval(array_shift($param));
	$type = intval(array_shift($param));
	$aid = intval(array_shift($param));
	$value = intval(array_shift($param));
	$cnt=intval(array_shift($param));
	
	//$armors=sql_fetch_rows("select * from cfg_armor a,sys_user_armor b where b.uid = $uid and a.id=b.armorid and b.hid=0 and a.id=$aid");
	
	if ($type==51){//
		$armors=sql_fetch_rows("select * from cfg_armor a,sys_user_armor b where b.uid = $uid and a.id=b.armorid and b.hid=0 and type =$value order by value");
	}else if ($type==52){//
		$armors=sql_fetch_rows("select * from cfg_armor a,sys_user_armor b where b.uid = $uid and a.id=b.armorid and b.hid=0 and part =$value order by value");
	}else{
		$armors=sql_fetch_rows("select * from cfg_armor a,sys_user_armor b where b.uid = $uid and a.id=b.armorid and b.hid=0 and a.id=$aid");
	}
	
	$ret[] = addSpecialArr($armors);
    $ret[] = getArmorNewAttribute($armors);
    $ret[] = getArmorEmbedGoods($armors);
    $ret[] = getTieInfo($armors, -1);
	$ret[] = getTieArmorAttribute($armors);
	$ret[] = getDeifyAttribute($armors);
	$ret[] = getFusionAttribute($armors);
	return $ret;
}
//在开启的时候集体设置任务
function triggerHuangJinCityTask()
{
	sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task, cfg_task where tid=243 and state=1 and id in (10501,6301,6302,6401,6402)) on duplicate key update state=0");
}
function addHuangJinProgress($uid,$tid,$count)
{
	sql_query("update huangjin_progress set curvalue=LEAST(maxvalue,curvalue+$count) where tid='$tid'");
    sql_query("insert into log_task_epic (uid,tid,count,time) values ($uid,$tid,$count,unix_timestamp())");
    //成就系统：：史诗任务
    $total=sql_fetch_one_cell("select sum(count) from log_task_epic where uid=$uid");
    $list=array(100,250,1000,2500,10000,25000);
	for($i=0;$i<count($list);$i++){
		if($total>=$list[$i] && ($total-$count)<$list[$i])
			finishAchivement($uid,10041+$i);
	}
}
function addDongZhuoProgress($uid,$tid,$count)
{
	sql_query("update dongzhuo_progress set curvalue=LEAST(maxvalue,curvalue+$count) where tid='$tid'");
    sql_query("insert into log_task_epic (uid,tid,count,time) values ($uid,$tid,$count,unix_timestamp())");
    //成就系统：：史诗任务
	$total=sql_fetch_one_cell("select sum(count) from log_task_epic where uid=$uid");
	$list=array(100,250,1000,2500,10000,25000);
	for($i=0;$i<count($list);$i++){
		if($total>=$list[$i] && ($total-$count)<$list[$i])
			finishAchivement($uid,10041+$i);
	}
}

//取得用户悬赏任务的奖励
function getUserRewardTaskReward($uid,$tid){	    
	
    $task = sql_fetch_one("select * from sys_user_reward_task where `tid`='$tid' and uid=$uid");    
    if(empty($task)){
    	 throw new Exception($GLOBALS['getReward']['already_got']);
    }
    
    if($task["state"]==0){
    	throw new Exception($GLOBALS['getReward']['task_not_finished']);
    }else if($task["state"]==1){
    	throw new Exception($GLOBALS['getReward']['already_got']);
    }else if($task["state"]==-1){
    	//未领取奖励状态
    	$reward=sql_fetch_one("select uid,todo,money,targetcid from sys_pub_reward_task where id=$task[tid] ");
    	if(empty($reward)){
    		//已经过期，或者非法
    		throw new Exception($GLOBALS['getReward']['already_got']);
    	}
    	else{
    		if(!lockUser($uid)) throw new Exception($GLOBALS['pacifyPeople']['server_busy']);
    		giveResource($uid,-1,20,$reward['money']*9/10);
    		//sql_query("update sys_user_reward_task set state=1 where tid=$task[tid] and uid=$uid ");
    		//sql_query("update sys_pub_reward_task set state=1 where id=$tid");
    		sql_query("delete from  sys_user_reward_task where tid=$task[tid]");
    		sql_query("delete from  sys_pub_reward_task  where id=$tid");
    		//sql_query("insert into log_reward_city_temp values($uid,0,1) on duplicate key update count=count+1;");
    		$username=sql_fetch_one_cell("select name from sys_user where uid=$uid ");
    		//发送报告
    		$content = sprintf($GLOBALS['recordTask']['report'],$username,$reward['todo'],$reward['money']);
			sendReport($reward['uid'],"reward_task",34,$task['targetcid'],$task['targetcid'],$content);
			unlockUser($uid);
    	}
    }
  
    $ret=array();
    $ret[] = 1;
    return $ret;
}

function getReward($uid,$param)
{
    $tid = intval(array_shift($param));
	//随机任务，记录个log
	//if($tid >=80000 && $tid <=99999)
	//{
	//	sql_query("insert into log_user_finish_systask(`uid`,`tid`,`timestamp`) values($uid, $tid, unix_timestamp())");
	//}
 	if($tid>=200000&& $tid<400000){
    	//用户悬赏任务
    	return getUserRewardTaskReward($uid,$tid);  
    }
    if ($tid>=86000&& $tid<=86300) {
    	if (!sql_check("select uid from sys_user_task_num where uid=$uid")) {
    		sql_query("insert into sys_user_task_num(uid,type,num) values($uid,1,0)");
    	} else {
    		if (sql_check("select 1 from sys_user_task_num where uid=$uid and num>=maxnum")) {
    			$ret=array();
			    $ret[] = 0;
			    $ret[]=$GLOBALS['getReward']['user_task_maxnum'];
			    return $ret;
    		}
    	}
    }
    $goalSortType = sql_fetch_one_cell("select sort from cfg_task_goal where tid=$tid limit 1");
    if(!lockUser($uid)) throw new Exception($GLOBALS['pacifyPeople']['server_busy']);
    
    if (!sql_check("select 1 from sys_user_task where uid=$uid and tid=$tid") && !sql_check("select 1 from sys_hero_task where uid=$uid and tid=$tid")) {
    	throw new Exception($GLOBALS['getReward']['already_got']);
    }
	
    $msg = "";
    
	//非黄巾史诗任务，只能交一次
    if (($tid>800||$tid<400)&&(!taskCanRecomplete($tid)) && sql_check("select * from sys_user_task where `uid`='$uid' and `tid`='$tid' and state=1"))
    {
        throw new Exception($GLOBALS['getReward']['already_got']);
    }
    $selectId=0;
    if (count($param)>0) $selectId = array_shift($param);
    if (($tid>=400 && $tid<=800) && sql_check("select * from sys_hero_task where `uid`='$uid' and `group`='$selectId'  and `tid`='$tid' and state=2"))
    {
        throw new Exception($GLOBALS['getReward']['already_got']);
    }
    
    
    $task = sql_fetch_one("select * from cfg_task where `id`='$tid'");
	//重复刷任务bug fix
	if($tid<=0 || empty($task)) throw new Exception($GLOBALS['getReward']['no_cfg_task']);
	
    $inform = intval($task["inform"]);
    if ($inform) $retmsg=true;
    $goals = sql_fetch_rows("select g.*,u.uid, u.currentcount from cfg_task_goal g left join sys_user_goal u on u.gid=g.id and u.uid='$uid' where g.tid='$tid'");
    

    $complete = true;
    //throw new Exception($selectId);
    foreach($goals as $goal)
    {
    	
        if (!checkGoalComplete($uid,$goal))
        {
            $complete = false;
            break;
        }
        if ($goal['sort']== 1 ){
         	if ($goal['type']>= 51 && $goal['type']<= 52 ){
         		$armor = sql_fetch_one_cell("select b.part,b.type from sys_user_armor a,cfg_armor b  where a.armorid = b.id and  uid =$uid and sid = $selectId");
         		if (empty($armor)){
         			if ($goal['type']==51 && $armor["type"] != $goal['count']){
	         			$complete = false;
	         			break;		
         			}
         			if ($goal['type']==52 && $armor["part"] != $goal['count']){
	         			$complete = false;
	         			break;		
         			}
         		}
         	}else if ($goal['type'] == 50){
         		$count = sql_fetch_one_cell("select count(1) from sys_city_hero where uid = $uid and hid = $selectId and herotype != '1000' and level >= ".$goal['count']);
         		if ($count ==0){
         			$complete = false;
         			break;		
         		}	
         	}            
        }	
    }
    $selectId2=0;
    if($task['group']==104430&&$tid!=104431){
    	$selectId2=array_shift($param);
    	//throw new Exception($selectId."|".$selectId2);
        $armor1 = sql_fetch_one_cell("select b.part,b.type from sys_user_armor a,cfg_armor b  where a.armorid = b.id and a.hid=0 and   uid =$uid and sid = $selectId");
        $armor2 = sql_fetch_one_cell("select b.part,b.type from sys_user_armor a,cfg_armor b  where a.armorid = b.id and a.hid=0 and  uid =$uid and sid = $selectId2");
        if(empty($armor1)||empty($armor2)){
        	$complete=false;
        }else{
        	if($armor1['part']!=$armor2['part']){
        		$complete=false;
        	}
        }
    }
    
	if ($isBattleTask){
        if (!empty($goal["uid"])){
    	   $complete = true;
        }
   	}
   		
    if (!$complete) throw new Exception($GLOBALS['getReward']['task_not_finished']);

    $cid = sql_fetch_one_cell("select lastcid from sys_user where uid='$uid'");   
    if($tid>=112001&&$tid<=112004){
    		$union_id=sql_fetch_one_cell("select union_id from sys_user where uid='$uid'");
   			$progress=sql_fetch_one("select maxvalue,curvalue from luoyang_progress where tid='$tid' and union_id=$union_id");
   			if(!empty($progress)){
			   	if($progress['curvalue']>=$progress['maxvalue'])
			   	{
			       	throw new Exception($GLOBALS['getReward']['not_enough_remain_task']);
			   	}
   			}
    }
if($tid == 216){//伍长 发激活邮箱信件
		$nowTime = time();
		$server_start_time = sql_fetch_one("select value from mem_state where state=6");
		if($nowTime < $GLOBALS['activate_mail_box']['range'] + $server_start_time['value']) {
			
		$user = sql_fetch_one("select name, passtype, passport, unix_timestamp() cur_time from sys_user where uid=$uid");
		}
	}
	if($tid == 219) {//蔷夫 发激活邮箱信件
		$nowTime = time();
		$server_start_time = sql_fetch_one("select value from mem_state where state=6");
		if($nowTime < $GLOBALS['activate_mail_box']['range'] + $server_start_time['value']) {
		$user = sql_fetch_one("select name, passtype, passport, unix_timestamp() cur_time from sys_user where uid=$uid");
		}
	}
    if ($tid == 250)//食君之禄   
    {
        $salary = sql_fetch_one_cell("select p.salary from sys_user u left join cfg_office_pos p on p.id=u.officepos where u.uid='$uid'"); 
        giveResource($uid,$cid,1,$salary);
    }
    else if ($tid == 251)  //采食封邑
    {
        $salary = sql_fetch_one_cell("select n.salary from sys_user u left join cfg_nobility n on n.id=u.nobility where u.uid='$uid'");  
        giveResource($uid,$cid,2,$salary); 
        giveResource($uid,$cid,3,$salary);
        giveResource($uid,$cid,4,$salary);
        giveResource($uid,$cid,5,$salary);  
    }
    else if($tid==279)
    {
    	$gold=getUnionFamousCityGold($uid);
    	giveResource($uid,$cid,1,$gold);
    }
    else if ($tid>11000&&$tid<15000) //黄巾军史诗捐献任务
    {
    	$progress=sql_fetch_one("select maxvalue,curvalue from huangjin_progress where tid='$tid'");
    	if($progress['curvalue']>=$progress['maxvalue'])
    	{
        	//sql_query("update sys_user_task set state=1 where uid='$uid' and tid='$tid'");
        	sql_query("update sys_user_task set state=1 where tid='$tid'");
    		$ret=array();
		    $ret[] = 0;
		    $ret[]=$GLOBALS['getReward']['global_task_end'];
		    return $ret;
    	}
    	$rewards = sql_fetch_rows("select * from cfg_task_reward where tid='$tid'");
    	$union_id=sql_fetch_one_cell("select union_id from sys_user where uid='$uid'");
        foreach($rewards as $reward)
        {
        	if($reward['sort']==5) //记录捐献后获得的物品
        	{
        		if($reward['type']==11001)
        		{
        			sql_query("insert into huangjin_task_log (uid,jungong) values ('$uid',$reward[count]) on duplicate key update jungong=jungong+$reward[count]");
        			if($union_id>0) sql_query("insert into huangjin_task_log_union (unionid,jungong) values ('$union_id',$reward[count]) on duplicate key update jungong=jungong+$reward[count]");
        		}
        		else if($reward['type']==12001)
        		{
        			sql_query("insert into huangjin_task_log (uid,juanxian) values ('$uid',$reward[count]) on duplicate key update juanxian=juanxian+$reward[count]");
        			if($union_id>0) sql_query("insert into huangjin_task_log_union (unionid,juanxian) values ('$union_id',$reward[count]) on duplicate key update juanxian=juanxian+$reward[count]");
        		}
        		else if($reward['type']==13001)
        		{
        			sql_query("insert into huangjin_task_log (uid,qinwang) values ('$uid',$reward[count]) on duplicate key update qinwang=qinwang+$reward[count]");
        			if($union_id>0) sql_query("insert into huangjin_task_log_union (unionid,qinwang) values ('$union_id',$reward[count]) on duplicate key update qinwang=qinwang+$reward[count]");
        		}
        		else if($reward['type']==14001)
        		{
        			sql_query("insert into huangjin_task_log (uid,gongpin) values ('$uid',$reward[count]) on duplicate key update gongpin=gongpin+$reward[count]");
        			if($union_id>0) sql_query("insert into huangjin_task_log_union (unionid,gongpin) values ('$union_id',$reward[count]) on duplicate key update gongpin=gongpin+$reward[count]");
        		}
        	}
            giveReward($uid,$cid,$reward);
        }
        addHuangJinProgress($uid,$tid,1);
        //sql_query("update huangjin_progress set curvalue=LEAST(maxvalue,curvalue+1) where tid='$tid'");
        //sql_query("insert into log_task_epic (uid,tid,count,time) values ($uid,$tid,1,unix_timestamp())");
        if($progress['curvalue']>=$progress['maxvalue']-1)
        {
        	sql_query("update sys_user_task set state=1 where tid='$tid'");
        	$unfinish=sql_fetch_one_cell("select count(*) from huangjin_progress where curvalue<maxvalue");
        	if($unfinish==0)
        	{
        		sql_query("update mem_state set value=1 where state=5");        		
        		triggerHuangJinCityTask();  
        		//triggerDongZhuoTask();    	
        		huangjinfinish();
        		//更新战场开启状态
	        	$battleInfos = sql_fetch_rows("select battleId from sys_battle_open_condition where otherCondition = 'huangJinZhiLuanShiShiDone'");
				if(!empty($battleInfos))
				{					
					sql_query("delete from sys_battle_open_condition where otherCondition = 'huangJinZhiLuanShiShiDone'");
					foreach ($battleInfos as $battleInfo)
					{
						$count = sql_fetch_one_cell("select count(*) from sys_battle_open_condition where battleId = $battleInfo[battleId]");
						if($count == 0)
						{
							openBattleField($battleInfo[battleId]);
														
						}						
					}
				}
        		$battle = sql_fetch_one("select battleId from sys_battle_open_condition where otherCondition = 'defeatGuanYu'");
				if(!empty($battle))
				{					
					sql_query("update sys_battle_open_condition set isConditionDoing = 1 where otherCondition = 'defeatGuanYu'");
					//设置定时器，如果系统里都是狗熊，无法在n天内完成战场开启条件，则系统在n天后强制开启战场
					updateBattleOpenTime($battle['battleId']);							
				}
													
        	}
        }
    }else if (($tid>15000&&$tid<16000)&&!($tid>=15505 and $tid<=15516)) //讨伐董卓史诗捐献任务
    {
    	$progress=sql_fetch_one("select maxvalue,curvalue from dongzhuo_progress where tid='$tid'");
    	if($progress['curvalue']>=$progress['maxvalue'])
    	{      
        	sql_query("update sys_user_task set state=1 where tid='$tid'");
    		$ret=array();
		    $ret[] = 0;
		    $ret[]=$GLOBALS['getReward']['global_task_end'];
		    return $ret;
    	}
    	$rewards = sql_fetch_rows("select * from cfg_task_reward where tid='$tid'");
    	$union_id=sql_fetch_one_cell("select union_id from sys_user where uid='$uid'");
        foreach($rewards as $reward)
        {
            giveReward($uid,$cid,$reward);
        }
        addDongZhuoProgress($uid,$tid,1);
        //sql_query("update dongzhuo_progress set curvalue=LEAST(maxvalue,curvalue+1) where tid='$tid'");
        //sql_query("insert into log_task_epic (uid,tid,count,time) values ($uid,$tid,1,unix_timestamp())");
        if($progress['curvalue']>=$progress['maxvalue']-1)
        {
        	sql_query("update sys_user_task set state=1 where tid='$tid'");
        	$unfinish=sql_fetch_one_cell("select count(*) from dongzhuo_progress where curvalue<maxvalue");
        	if($unfinish==0)
        	{
        		finishDongZhuoTask();        	
        		dongzhuo2finish();	       
        		luoyangstart(); 	
        	}
        	$unfinish1=sql_fetch_one_cell("select count(*) from dongzhuo_progress where curvalue<maxvalue and `group`=15000");
        	$dongzhuovalue=sql_fetch_one_cell("select value from mem_state where state=7");
        	if($unfinish1==0&&$dongzhuovalue<2)//讨伐阉党之外还有任务没完成
        	{
        	      triggerDongZhuoOtherTask();
        	      dongzhuo1finish();
        	      dongzhuo2start();
        	}
        }
    }
    else if($tid>400&&$tid<800)//名将公共任务
    {
    	$rewards = sql_fetch_rows("select * from cfg_task_reward where tid='$tid'");
    	foreach($rewards as $reward)
        {
        	if($reward['sort']==1)
        	{
        		sql_query("update sys_hero_task set state=2 where `group`=$selectId and tid=$tid and uid=$uid");
        		$tempcount=$reward['count'];
        		$hid=($selectId-20001)/10;
        		sql_query("update sys_lionize set `friend`=`friend`+$tempcount where uid=$uid and npcid=$hid");
        		sql_query("update sys_lionize set `friend`=100 where friend>100 and uid=$uid and npcid=$hid");
        		//log
        		sql_query("insert into log_lionize(uid,npcid,`type`,`count`,`time`)  select  uid,npcid,3,friend,unix_timestamp() from sys_lionize where uid=$uid and npcid=$hid");
        	}
        }
    }
    else if($tid>400000&&$tid<420000)//名将专属任务
    {
    	
    	$rewards = sql_fetch_rows("select * from cfg_task_reward where tid='$tid'");
    	$hid=($selectId-20001)/10;
    	
   		$tempuid = sql_fetch_one_cell("select uid from sys_city_hero where hid=$hid");
	    if($uid!=$tempuid){
	       	throw new Exception($GLOBALS['getReward']['err']);
	    }
    	
    	foreach($rewards as $reward)
        {
	    	if($reward['sort']==1)
	    	{
	    		$tempcount=$reward['count'];
	       		$hid=($selectId-20001)/10;
	       		
	       		//$temploc=sql_fetch_one_cell("select friend from sys_lionize where uid=$uid and npcid=$hid");
	       		sql_query("update sys_lionize set `friend`=`friend`+1 where uid=$uid and npcid=$hid");
	       		
	       		sql_query("update sys_lionize set `friend`=120 where `friend`>120 and uid=$uid and npcid=$hid");
	       		//$temploc=sql_fetch_one_cell("select friend from sys_lionize where uid=$uid and npcid=$hid");
	       		
	       		//log
        		sql_query("insert into log_lionize(uid,npcid,`type`,`count`,`time`)  select  uid,npcid,0,friend,unix_timestamp() from sys_lionize where uid=$uid and npcid=$hid");
        		
	       		$friend=sql_fetch_one_cell("select `friend` from sys_lionize where uid=$uid and npcid=$hid");
	       		//自己所属名将的属性加成，base*friend/100；
	       		if($friend>100)
	       		{
	       			deleteHeroBaseAddPlus($hid,3);
		       		$mul = ($friend-100)/100;
		       		$row=sql_fetch_one("select a.cid as herocid,$mul*b.affairs_base as affairs_base_add_on,$mul*b.bravery_base as bravery_base_add_on,$mul*b.wisdom_base as wisdom_base_add_on from sys_city_hero a,cfg_npc_hero b where a.hid=$hid and b.npcid=$hid");
					$affairs_base_add_on =intval($row["affairs_base_add_on"]);
					$bravery_base_add_on = intval($row["bravery_base_add_on"]);
					$wisdom_base_add_on = intval($row["wisdom_base_add_on"]);
					
					sql_query("insert into sys_city_hero_base_add (hid,uid,affairs_base_add_on,bravery_base_add_on,wisdom_base_add_on,type) values($hid,$uid,$affairs_base_add_on,$bravery_base_add_on,$wisdom_base_add_on,3)");	
					sql_query("update sys_city_hero set bravery_base=bravery_base+$bravery_base_add_on,wisdom_base=wisdom_base+$wisdom_base_add_on,affairs_base=affairs_base+$affairs_base_add_on where hid = $hid");
									
					$herocid=$row["herocid"];
					updateCityHeroChange($uid,$herocid);
					regenerateHeroAttri($uid, $hid);
	       		}
	    	}
        }
        sql_query("delete from sys_user_goal where uid=$uid and gid=$tid");
        sql_query("delete from sys_attack_position where uid=$uid and tid=$tid");
    }else 
    {
 	   if ($tid==101961||$tid==101963||$tid==101964) { 
			sql_query ( "update sys_user_task set state=1 where uid=$uid and tid in (101961,101963,101964)" );
 	   }
    	$log_type=4;
    	$log_money_type=60;
    	$retmsg=false;
    	if ($inform==1) $retmsg=true;
    	
        $rewards = sql_fetch_rows("select * from cfg_task_reward where tid='$tid'");
        $battleLevel = 0;
        foreach($rewards as $reward)
        {
        	if ($goalSortType==100){
    	    	//单服战场奖励
        		if ($battleLevel==0){
        		    $battleLevel = sql_fetch_rows("select level from sys_user_battle_state where uid='$uid' and state=0");
        			 if(empty($battleLevel)) {
        		    	$battleLevel = sql_fetch_rows("select level from log_battle_honour where uid='$uid' order by quittime desc limit 1");
        		    }
        		}
        		$reward["count"] = $battleLevel[0]["level"]*$battleLevel[0]["level"]*$reward["count"];
        	}else if($goalSortType==110){
	        	//跨服战场奖励
	        	//$battleLevel=sql_fetch_one_cell("select level from sys_user_battle_state where uid='$uid' and state=0 and in_cross_battle=1");
	        	//$areaLevel=3;
	        	//if(empty($battleLevel)) {
	        	//	$areaLevel=sql_fetch_one_cell("select area_level from log_user_battlenet where uid=$uid order by quittime desc limit 1");
	        	//}else{
	        	//	if($battleLevel==12)$areaLevel=0;
	        	//	else if($battleLevel==10)$areaLevel=1;
	        	//	else if($battleLevel==5)$areaLevel=2;
	        	//	else if($battleLevel==1)$areaLevel=3;
	        	//}
	        	//$reward["count"] = (4-$areaLevel)*$reward["count"];
	        	sql_query("update sys_user_task set state=1 where tid='$tid'and uid='$uid'");
            }else if($goalSortType==120) {
            	//跨服PVE战场奖励
                $battleLevel=sql_fetch_one_cell("select level+1 from sys_user_battle_state where uid='$uid' and state=0 and in_cross_battle=1");
                if(empty($battleLevel)) {
                  $battleLevel=sql_fetch_one_cell("select area_level+1 from log_user_battlenet where uid=$uid order by quittime desc limit 1");
                } 
                $reward["count"] = intval(($battleLevel+1)*$reward["count"]/2);
                sql_query("update sys_user_task set state=1 where tid='$tid'and uid='$uid'");
            }
           $msg=$msg.giveReward($uid,$cid,$reward,$log_type,$log_money_type,$retmsg);
        }
        
        if ( ($tid==6001) || ($tid==6101) || ($tid==6102) || ($tid==6201) || ($tid==6202) || ($tid==6211) || ($tid==6212) 
        	|| ($tid==6301)|| ($tid==6302)|| ($tid==6401)|| ($tid==6402)){
        	sql_query("insert into log_everyday_task values($uid,$tid,unix_timestamp()) on duplicate key update gettime=unix_timestamp()");
        }
    }
    
    if(!taskCanRecomplete($tid)) //黄巾军史诗任务，永久开放
    {	//特殊活动任务
    	if($tid>=100632&&$tid<=100634){
    		sql_query("update sys_user_task set state=1 where uid=$uid and tid between 100632 and 100634");
    	}else if($tid>=100642&&$tid<=100644){
    		sql_query("update sys_user_task set state=1 where uid=$uid and tid between 100642 and 100644");
    	}else if($tid>=100652&&$tid<=100654){
    		sql_query("update sys_user_task set state=1 where uid=$uid and tid between 100652 and 100654");
    	}else {
    		sql_query("insert into sys_user_task (`uid`,`tid`,`state`) values ('$uid','$tid',1) on duplicate key update `state`=1");
    	}
    }
    //如果任务目标要扣除相应的数据，则在这里扣除
    $goals = sql_fetch_rows("select * from cfg_task_goal where tid='$tid'");
    foreach($goals as $goal)
    {
        if ($goal['reduce'] == 1)
        {
        	if($goal['sort']==6&&($task['group']==104430&&$task['id']!=104431)){
        		$armorid = sql_fetch_one_cell("select armorid from sys_user_armor where sid=$selectId");
        		$armorid2 = sql_fetch_one_cell("select armorid from sys_user_armor where sid=$selectId2");
        		cutArmorBySid($uid,$armorid,$selectId);
        		cutArmorBySid($uid,$armorid2,$selectId2);
        	}else	if ($goal['sort']== 1 &&($goal['type']== 51 || $goal['type']== 52 )){
        		$armorid = sql_fetch_one_cell("select armorid from sys_user_armor where sid=$selectId");
        		cutArmorBySid($uid,$armorid,$selectId);
        	}else if ($goal['sort']== 1 &&($goal['type']== 50 )){
        		require_once './HeroFunc.php';        		
        		deleteHero($uid,$selectId);
        	} else if ($goal['sort'] == 50) {
        		sql_query("update sys_user_goal set currentcount=0 where gid='$goal[id]' and uid=$uid");
			}else if($goal['sort']==203){//
				$union_id=sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
				sql_query("update luoyang_progress set curvalue=curvalue-maxvalue where tid='$goal[type]' and union_id=$union_id");
			}else{
//				if($goal['sort']== 2 &&$goal['type']>110000&&$goal['type']<160000){//武魂的特殊扣除，依gid从低到高扣除
//					$goal['type']=sql_fetch_one_cell("select gid from sys_goods where uid='$uid' and gid>='$goal[type]' and gid%10000=$goal[type]%10000 and count>0 order by gid limit 1");
//				}
				reduceGoal($uid,$cid,$goal);
			}
    }
		}
    
    $triggers = sql_fetch_rows("select * from cfg_task where pretid='$task[id]'");    
    foreach($triggers as $trigger)
    {
        $triggerout=true;
        $state = $trigger['default'];
        if ($state==100) {//100仅表示这个任务可以重复完成
        	$state = 0;
        }
        
        if($tid==243)
	    {
	    	if(isHuangJinJuanXian($trigger['id'])||isHuangJinDuiHuan($trigger['id']))
	    	{
	    		//看系统黄巾之乱史诗任务是否开启
		    	$huangJinTaskValue=sql_fetch_one_cell("select value from mem_state where state = 5");
				if ($huangJinTaskValue==-1){
		    		$triggerout=false;
				} 
	    		else 
	    		{
			    	$huangjinProgress=sql_fetch_one_cell("select value from mem_state where state=5");
			    	if(1==$huangjinProgress) //如果黄巾史诗任务已经完成了，就不勾出捐献和兑换任务了
			    	{
		    			$triggerout=false;	
			    	}
	    		}
	    	}	    		    	    		    		    	
	    }
	    if($trigger['id']>=103005 and $trigger['id']<=103009){//董卓1阶段史诗完成才刷出的任务
  			$huangjinProgress=sql_fetch_one_cell("select value from mem_state where state=7");
	    	if(2==$huangjinProgress) //如果董卓一阶段任务已经完成了，就不勾出捐献和兑换任务了
	    	{
    			$triggerout=true;	
	    	}else{
    			$triggerout=false;		    		
	    	}
	    }
	    if($trigger["id"]==10501||($trigger["id"]==6301)|| ($trigger["id"]==6302)|| ($trigger["id"]==6401)|| ($trigger["id"]==6402)){
	    	$triggerout=(1==$huangjinProgress);	
	    }else if($trigger["id"]==7001||$trigger["id"]==7002||$trigger["id"]==7003){
	    	if(defined("BATTLE_NET_ENABLE") && BATTLE_NET_ENABLE){
	    		$triggerout = true;
	    	}else{
	    		$triggerout = false;
	    	}
	    }
	    if($triggerout)
	    {
				$nextgoals = sql_fetch_rows("select id from cfg_task_goal where tid='$trigger[id]' and sort in (50,80)");//随机任务要初始化goal
				foreach($nextgoals as $nextgoal){
					$gid = $nextgoal[id];
					sql_query("insert into sys_user_goal (uid,gid,currentcount) values ('$uid','$gid','0') on duplicate key update currentcount=currentcount");
				}
       		sql_query("insert into sys_user_task (uid,tid,state) values ('$uid','$trigger[id]','$state') on duplicate key update state='$state'");     //给玩家加成长任务
	    	
    	}
    }
	if($tid==243){//脱离保护		
		$shiShiTaskValue=sql_fetch_one_cell("select value from mem_state where state =7");
		if ($shiShiTaskValue==1){
    		triggerDongZhuoTaskByUser($uid); 
		} else if ($shiShiTaskValue==2){
    	    triggerDongZhuoOtherTaskByUser($uid);
    	}
    	finish243($uid);
	}
    unlockUser($uid);
    

    $inform = intval($task["inform"]);  
    if($inform==1)	//发送广播
    {    	    
    	$uname=sql_fetch_one_cell("select name from sys_user where uid='$uid'");
    	$uname=addslashes($uname);    	
    	$content=sprintf($GLOBALS['getReward']['inform'],$uname,$task['name'],$msg);    	
    	sendSysInform(0,1,0,300,1800,1,49151,$content);
    	//sql_query("insert into sys_inform (type,inuse,starttime,endtime,`interval`,scrollcount,color,msg) values (1,1,unix_timestamp(),unix_timestamp()+300,1800,2,16776960,'$content')");
    }  
    //任务log记录
//| 60601 | 天下无双 |
//| 60602 | 千年帝都 |
//| 60623 | 初试身手 |
//| 60624 | 所向披靡 |
//| 60625 | 战场无双 |
	$logTasks = array(-1,60601,60602,60623,60624,60625);
    if (array_search($tid,$logTasks)) {
    	sql_query("insert into log_task (uid,tid,count,time) values ($uid,$tid,1,unix_timestamp())");
    }
		//联盟任务记录任务个数
    recordUnionTask($uid,$goal);
    if(checkXianDiMiZhaoEnoughTask($uid)){//如果记录足够 了,那就给盟主刷个献帝密诏的任务
    	$unioncreater=sql_fetch_one_cell("select leader from sys_union where id=(select union_id from sys_user where uid=$uid)");
    	sql_query("insert into sys_user_task(uid,tid,state)values('$unioncreater',112005,0) on duplicate key update  state=0");
    }
    if ($tid>=86000&& $tid<=86300) {
    	sql_query("update sys_user_task_num set num=num+1 where type=1 and uid=$uid");
    }
    
    $ret=array();
    $ret[] = 1;  
    return $ret;
}
function checkXianDiMiZhaoEnoughTask($uid){
		$unionid=sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
		if($unionid==0){
			return false;
		}
		$uniontasks=sql_fetch_one_cell(" select count(*) from luoyang_progress where union_id=$unionid");
		if(empty($uniontasks)||$uniontasks<4){
			return false;
		}
		$sql="select count(*) from luoyang_progress where union_id=$unionid and curvalue<maxvalue";
		$count=sql_fetch_one_cell($sql);//当前值都比最大值大了，当然就达到数量了了
		if(empty($count)){
			return true;
		}else{
			return false;
		}
}
function recordUnionTask($uid,$goal){
	if($goal['tid']>=112001&&$goal['tid']<=112004){
		$tid=$goal['tid'];
		$unionid=sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
		$task=sql_fetch_one("select * from cfg_task where id=$tid");
		$taskMax=getTaskMax($tid);
		if(!empty($unionid)){
			sql_query("insert into luoyang_progress(union_id,tid,maxvalue,curvalue,`group`,`name`) values('$unionid','$tid','$taskMax',1,'$task[group]','$task[name]') on duplicate key update  curvalue=curvalue+1");
		}
	}
}
function getTaskMax($tid){//根据备战洛阳中各个任务的最大个数
//(102001, 102001, 0, '搜集罪证')
//(102002, 102001, 0, '解救妇人')
//(102003, 102001, 0, '剪除党羽')
//(102004, 102001, 0, '汉室威信')
//(102005, 102002, 0, '献帝密诏')
//(102006, 102003, 0, '惩治爪牙')
//(102007, 102003, 0, '贤良忠臣')
	$sql="select count from cfg_task_goal where sort=203 and type=$tid";
	$count=sql_fetch_one_cell($sql);
	return $count;
}

function getMultiReward($uid,$param)
{
    $tid = intval(array_shift($param));
    $cnt = intval(array_shift($param));
    if($cnt<=0||$cnt>=1000)
    {
    	throw new Exception($GLOBALS['getReward']['invalid_count']);
    }
    else if($cnt==1)
    {
    	$param2=array();
    	$param2[]=$tid;
    	return getReward($uid,$param2);
    }
    if(!lockUser($uid)) throw new Exception($GLOBALS['pacifyPeople']['server_busy']);
	
	//非黄巾史诗任务，只能交一次
    if (!taskCanRecomplete($tid))
    {
        throw new Exception($GLOBALS['getReward']['not_allowed_multi']);
    }
    $task = sql_fetch_one("select * from cfg_task where `id`='$tid'");    
    $goals = sql_fetch_rows("select * from cfg_task_goal where tid='$tid'");
    $complete = true;
    foreach($goals as &$goal)
    {
    	$goal['count']=$cnt*$goal['count'];
        if (!checkGoalComplete($uid,$goal))
        {
            $complete = false;
            break;
        }        
        if ($goal['sort']== 1 &&($goal['type']>= 50 && $goal['type']<= 52 )){
         	$complete = false;
            break;
        }		
    }
    if (!$complete)
    {
    	$msg=sprintf($GLOBALS['getReward']['not_enough_things'],$cnt);
    	 throw new Exception($msg);
    }
	$cid = sql_fetch_one_cell("select lastcid from sys_user where uid='$uid'");
	if (isHuangJinJuanXian($tid)) //黄巾军史诗捐献任务
    {
    	$progress=sql_fetch_one("select maxvalue,curvalue from huangjin_progress where tid='$tid'");
    	if($progress['curvalue']+$cnt>=$progress['maxvalue'])
    	{
        	throw new Exception($GLOBALS['getReward']['not_enough_remain']);
    	}
    	$rewards = sql_fetch_rows("select * from cfg_task_reward where tid='$tid'");
    	$union_id=sql_fetch_one_cell("select union_id from sys_user where uid='$uid'");
        foreach($rewards as &$reward)
        {
        	$reward['count']=$reward['count']*$cnt;
        	if($reward['sort']==5) //记录捐献后获得的物品
        	{
        		if($reward['type']==11001)
        		{
        			sql_query("insert into huangjin_task_log (uid,jungong) values ('$uid',$reward[count]) on duplicate key update jungong=jungong+$reward[count]");
        			if($union_id>0) sql_query("insert into huangjin_task_log_union (unionid,jungong) values ('$union_id',$reward[count]) on duplicate key update jungong=jungong+$reward[count]");
        		}
        		else if($reward['type']==12001)
        		{
        			sql_query("insert into huangjin_task_log (uid,juanxian) values ('$uid',$reward[count]) on duplicate key update juanxian=juanxian+$reward[count]");
        			if($union_id>0) sql_query("insert into huangjin_task_log_union (unionid,juanxian) values ('$union_id',$reward[count]) on duplicate key update juanxian=juanxian+$reward[count]");
        		}
        		else if($reward['type']==13001)
        		{
        			sql_query("insert into huangjin_task_log (uid,qinwang) values ('$uid',$reward[count]) on duplicate key update qinwang=qinwang+$reward[count]");
        			if($union_id>0) sql_query("insert into huangjin_task_log_union (unionid,qinwang) values ('$union_id',$reward[count]) on duplicate key update qinwang=qinwang+$reward[count]");
        		}
        		else if($reward['type']==14001)
        		{
        			sql_query("insert into huangjin_task_log (uid,gongpin) values ('$uid',$reward[count]) on duplicate key update gongpin=gongpin+$reward[count]");
        			if($union_id>0) sql_query("insert into huangjin_task_log_union (unionid,gongpin) values ('$union_id',$reward[count]) on duplicate key update gongpin=gongpin+$reward[count]");
        		}
        	}
            giveReward($uid,$cid,$reward);
        }
        addHuangJinProgress($uid,$tid,$cnt);
        //sql_query("update huangjin_progress set curvalue=LEAST(maxvalue,curvalue+$cnt) where tid='$tid'");
        //sql_query("insert into log_task_epic (uid,tid,count,time) values ($uid,$tid,$cnt,unix_timestamp())");
        if($progress['curvalue']>=$progress['maxvalue']-$cnt)
        {
        	sql_query("update sys_user_task set state=1 where tid='$tid'");
        	$unfinish=sql_fetch_one_cell("select count(*) from huangjin_progress where curvalue<maxvalue");
        	if($unfinish==0)
        	{
        		sql_query("update mem_state set value=1 where state=5");
        		triggerHuangJinCityTask();
        		huangjinfinish();
        		//triggerDongZhuoTask();
        		
        		//更新战场开启状态
	        	$battleInfos = sql_fetch_rows("select battleId from sys_battle_open_condition where otherCondition = 'huangJinZhiLuanShiShiDone'");
				if(!empty($battleInfos))
				{					
					sql_query("delete from sys_battle_open_condition where otherCondition = 'huangJinZhiLuanShiShiDone'");
					foreach ($battleInfos as $battleInfo)
					{
						$count = sql_fetch_one_cell("select count(*) from sys_battle_open_condition where battleId = $battleInfo[battleId]");
						if($count == 0)
						{
							sql_query("update cfg_battle_field set state = 1 where id = $battleInfo[battleId]");
						}						
					}
				}
        		$battleInfo = sql_fetch_one("select battleId from sys_battle_open_condition where otherCondition = 'defeatGuanYu'");
				if(!empty($battleInfo))
				{					
					sql_query("update sys_battle_open_condition set isConditionDoing = 1 where otherCondition = 'defeatGuanYu'");
					//设置定时器，如果系统里都是狗熊，无法在n天内完成战场开启条件，则系统在n天后强制开启战场
					updateBattleOpenTime($battleInfo['battleId']);							
				}
				
        	}
        }
    }
	else if ($tid>15000&&$tid<16000&&!($tid>=15505 and $tid<=15516)) //讨伐董卓史诗捐献任务
    {
    	$progress=sql_fetch_one("select maxvalue,curvalue from dongzhuo_progress where tid='$tid'");
    	if($progress['curvalue']+$cnt>$progress['maxvalue'])
    	{
        	throw new Exception($GLOBALS['getReward']['not_enough_remain']);
    	}
    	$rewards = sql_fetch_rows("select * from cfg_task_reward where tid='$tid'");
    	$union_id=sql_fetch_one_cell("select union_id from sys_user where uid='$uid'");
        foreach($rewards as $reward)
        {
        	$reward['count']=$reward['count']*$cnt;
            giveReward($uid,$cid,$reward);
        }
        addDongZhuoProgress($uid,$tid,$cnt);
        //sql_query("update dongzhuo_progress set curvalue=LEAST(maxvalue,curvalue+$cnt) where tid='$tid'");
        //sql_query("insert into log_task_epic (uid,tid,count,time) values ($uid,$tid,$cnt,unix_timestamp())");
        if($progress['curvalue']>=$progress['maxvalue']-$cnt)
        {
        	sql_query("update sys_user_task set state=1 where tid='$tid'");
        	$unfinish=sql_fetch_one_cell("select count(*) from dongzhuo_progress where curvalue<maxvalue");
        	if($unfinish==0)
        	{
        		finishDongZhuoTask(); 
        		dongzhuo2finish();	       
        		luoyangstart();        		
        	}
        	
        	$unfinish1=sql_fetch_one_cell("select count(*) from dongzhuo_progress where curvalue<maxvalue and `group`=15000");
        	$dongzhuovalue=sql_fetch_one_cell("select value from mem_state where state=7");
        	if($unfinish1==0&&$dongzhuovalue<2)//讨伐阉党之外还有任务没完成
        	{
        	      triggerDongZhuoOtherTask();
        	      dongzhuo1finish();
        	      dongzhuo2start();
        	}
        }
    }else if($tid>=112001&&$tid<=112004){//备战洛阳
			/*$unionid=sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
			
    		$progress=sql_fetch_one("select maxvalue,curvalue from luoyang_progress where tid='$tid' and union_id=$unionid");
		   	
			$task=sql_fetch_one("select * from cfg_task where id=$tid");
			$taskMax=getTaskMax($tid);
    		if(!empty($progress)){
			   	if($progress['curvalue']+$cnt>$progress['maxvalue'])
			   	{
			       	throw new Exception($GLOBALS['getReward']['not_enough_remain']);
			   	}
    		}else{
    			if($cnt>$taskMax){
    				throw new Exception($GLOBALS['getReward']['not_enough_remain']);
    			}
    		}
			if(!empty($unionid)){
				sql_query("insert into luoyang_progress(union_id,tid,maxvalue,curvalue,`group`,`name`) values('$unionid','$tid','$taskMax',$cnt,'$task[group]','$task[name]') on duplicate key update  curvalue=curvalue+$cnt");
			}
			
        	$rewards = sql_fetch_rows("select * from cfg_task_reward where tid='$tid'");
	        foreach($rewards as $reward)
	        {
	        	$reward['count']=$reward['count']*$cnt;
	            giveReward($uid,$cid,$reward);
	        }
	        
		    if(checkXianDiMiZhaoEnoughTask($uid)){//如果记录足够 了,那就给盟主刷个献帝密诏的任务
		    	$unioncreater=sql_fetch_one_cell("select leader from sys_union where id=(select union_id from sys_user where uid=$uid)");
		    	sql_query("insert into sys_user_task(uid,tid,state)values('$unioncreater',112005,0) on duplicate key update  state=0");
		    }
			*/
    } else 
    {
    	$rewards = sql_fetch_rows("select * from cfg_task_reward where tid='$tid'");
        foreach($rewards as $reward)
        {
        	$reward['count']=$reward['count']*$cnt;
            giveReward($uid,$cid,$reward);
        }
    }
    /*
	if($tid>=112001&&$tid<=112004){
			$progress=sql_fetch_one("select maxvalue,curvalue from luoyang_progress where tid='$tid'");
		   	if($progress['curvalue']+$cnt>=$progress['maxvalue'])
		   	{
		       	throw new Exception($GLOBALS['getReward']['not_enough_remain']);
		   	}
			$unionid=sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
			$task=sql_fetch_one("select * from cfg_task where id=$tid");
			$taskMax=getTaskMax($tid);
			if(!empty($unionid)){
				sql_query("insert into luoyang_progress(union_id,tid,maxvalue,curvalue,`group`,`name`) values('$unionid','$tid','$taskMax',1,'$task[group]','$task[name]') on duplicate key update  curvalue=curvalue+$cnt");
			}
	}
*/
    //如果任务目标要扣除相应的数据，则在这里扣除
    $goals = sql_fetch_rows("select * from cfg_task_goal where tid='$tid'");
    foreach($goals as &$goal)
    {
    	$goal['count']=$cnt*$goal['count'];
        if ($goal['reduce'] == 1)
        {
            reduceGoal($uid,$cid,$goal);
        }
    }
    
    unlockUser($uid);
    
    $ret=array();
    $ret[] = 1;
    return $ret;
}

function postURL($url, $uparams, $time_out = "10") {
	$urlarr = parse_url($url);
	$errno = "";
	$errstr = "";
	$transports = "";
	if($urlarr["scheme"] == "https") {
		$transports = "ssl://";
		$urlarr["port"] = "443";
	} else {
		$transports = "tcp://";
		//$urlarr["port"] = "80";
		$urlarr["port"] = ($urlarr["port"] == "" ? 80 : $urlarr["port"]);
	}
	$fp=@fsockopen($transports . $urlarr['host'],$urlarr['port'],$errno,$errstr,$time_out);
	if(!$fp) {
		die("ERROR: $errno - $errstr<br />\n");
	} else {
		$out = "POST ".$urlarr["path"].'?'. $urlarr["query"] . " HTTP/1.1\r\n";
		$out .= "Content-type: application/x-www-form-urlencoded\r\n";
		//$out .= "Content-length: application/x-www-form-urlencoded\r\n";
		$out .= "Accept: */*\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "UA-CPU: x86\r\n";
		$out .= "User-Agent: wangye173_rxsg_interface\r\n";
		$out .= "Host: ".$urlarr["host"]."\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "\r\n";
		$out .= "$uparams \r\n";
		$out .= "\r\n";
		
		fwrite($fp, $out);
		
		//if ($noreturn) return;
		
		while(!feof($fp)) {
			$info[]=@fgets($fp, 4096);
		}

		fclose($fp);
	}
}
function checkBeiZhanLuoYang($uid){//看洛阳里面的用户是不是可以捐献了
	/*$unionid=sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
	if(empty($unionid)){//也检查下吧，其实在任务刷出的地方就检测用户是否加入联盟，如果没有联盟就不刷给他任务
		return false;
	}
	$leader=sql_fetch_one_cell("select `leader` from sys_union where id=$unionid");
	if($uid!=$leader){//不是联盟主就没机会完成
		return false;
	}else{//是盟主就看这个联盟的人捐献的东西是否完成了数量
		$sql0="select * from luoyang_progress where union_id=$unionid limit 1";
		$count0=sql_fetch_rows($sql0);//如果都没有联盟的记录那当然就不可能完成了
		if(empty($count0)){
			return false;
		}
		$sql="select * from luoyang_progress where union_id=$unionid and curvalue<maxvalue";
		$count=sql_fetch_rows($sql);//当前值都比最大值大了，当然就完成一次了
		if(empty($count)){
			return true;
		}else{
			return false;
		}
	}*/
	return false;
}
/**
 * 
 *备战洛阳是否可见
1.没有联盟就不可见
2.退出联盟不可见
3.踢出联盟不可见
4.盟主有献帝诏书就不可见
5.献帝诏书到期才可见
6.洛阳开启了才可见
 * @param unknown_type $uid
 * 返回true表示用户可见，false表示用户不可见
 */
function isBeiZhanLuoYangVisible($uid){
	/*
	$luoyangvalue=sql_fetch_one_cell("select value from mem_state where state=8");
	if($luoyangvalue!=1){//不是备战洛阳阶段那就不可见
		return false;
	}
	
	$unionid=sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
	$leader=sql_fetch_one_cell("select `leader` from sys_union where id=$unionid");//盟主uid
	
	if(empty($unionid)){//如果没有联盟就不刷给他任务
		return false;
	}
	
	$leadergood=sql_fetch_one_cell("select count from sys_goods where uid=$leader and gid=161501");
	if(!empty($leadergood)){	//看盟主是不是有献帝诏书,有就不刷
		return false;
	}
	
	$nowtime=sql_fetch_one_cell("select unix_timestamp()");
	$bufs=sql_fetch_rows("select * from mem_user_buffer where uid='$leader' and endtime>'$nowtime' and buftype=161501");
	if(!empty($bufs)){//看联盟是不是在使用buffer，使用中也不刷
		return false;
	}
	
	$leadertask=sql_fetch_one_cell("select 1 from sys_user_task where uid=$leader and tid=112005 and state=0");//如果盟主有献帝密诏任务就不刷了
	if(!empty($leadertask)){
		$count = sql_fetch_one_cell("select count(*) from luoyang_progress where curvalue <maxvalue and union_id=$unionid");
		if (!empty($count)) {
			sql_query("update sys_user_task set state=1 where uid='$leader' and tid=112005");
			return  true; //修在没有完成任务的情况下，盟主的任务也能看到的bug	
		} else {
			return false;
		}
	}
	*/
	return true;
	
}
/**
 * 
 * 当前 的行为id对应的掉落物品表$actiontype,对应到cfg_act_task_good_drop的actionid
 *
 * @param int $actiontype
 */
function dropTaskGood($uid,$cid,$actiontype,$param=0){
	$actionGoodDrop1=sql_fetch_rows('select cfg_act_task_good_drop.* from cfg_act left join  cfg_act_task_good_drop on cfg_act_task_good_drop.actid=cfg_act.actid where cfg_act.starttime<unix_timestamp() and cfg_act.endtime>unix_timestamp() and cfg_act_task_good_drop.actiontype='.$actiontype);
	$actionGoodDrop2=sql_fetch_rows('select * from cfg_act_task_good_drop  where actid=0 and  starttime<unix_timestamp() and endtime>unix_timestamp() and actiontype='.$actiontype);
	$actionGoodDrop=array_merge($actionGoodDrop1,$actionGoodDrop2);
	$tropGoodName="";
	foreach ($actionGoodDrop as $drop){
		if($drop['param']==0){
			if(rand(1,100)<$drop['rate']){//掉物品
				giveReward($uid,$cid,$drop);
				$tropGoodName.="  ".$drop['name'];
			}
		}else {
			if($param>$drop['param']){//传入的param必需比要求的param大
				if(rand(1,100)<$drop['rate']){//掉物品
					giveReward($uid,$cid,$drop);
					$tropGoodName.="  ".$drop['name'];
				}
			}
		}
	}
	return $tropGoodName;
}
?>