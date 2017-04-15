<?php
function check_city_resource($uid, $cid){
    //$cityinfo = getCityProduct($uid,array($cid));
	$cityinfo = getUsersCityProduct($uid,$cid);
    $city_rate = array_shift($cityinfo);
    $food_all_people = array_shift($cityinfo);
    $wood_all_people = array_shift($cityinfo);
    $rock_all_people = array_shift($cityinfo);
    $iron_all_people = array_shift($cityinfo);
    $food_add_base = array_shift($cityinfo);
    $wood_add_base = array_shift($cityinfo);
    $rock_add_base = array_shift($cityinfo);
    $iron_add_base = array_shift($cityinfo);
    $food_add_rate_technic = array_shift($cityinfo);
    $wood_add_rate_technic = array_shift($cityinfo);
    $rock_add_rate_technic = array_shift($cityinfo);
    $iron_add_rate_technic = array_shift($cityinfo);
    $food_army_use = array_shift($cityinfo);
    $chief_add = array_shift($cityinfo);
    $goods_food_endtime = array_shift($cityinfo);
    $goods_wood_endtime = array_shift($cityinfo);
    $goods_rock_endtime = array_shift($cityinfo);
    $goods_iron_endtime = array_shift($cityinfo);
	$skill_add = array_shift($cityinfo);
    $rate = sql_fetch_one("select food_rate,wood_rate,rock_rate,iron_rate from sys_city_res_add where `cid`='{$cid}'");
	$skill_food_add = sql_fetch_one_cell("select skill_food_add from sys_city_res_add where `cid`='{$cid}'");
	$skill_wood_add = sql_fetch_one_cell("select skill_wood_add from sys_city_res_add where `cid`='{$cid}'");
	$skill_rock_add = sql_fetch_one_cell("select skill_rock_add from sys_city_res_add where `cid`='{$cid}'");
	$skill_iron_add = sql_fetch_one_cell("select skill_iron_add from sys_city_res_add where `cid`='{$cid}'");
	if(empty($skill_food_add)) $skill_food_add=0;
	if(empty($skill_wood_add)) $skill_wood_add=0;
	if(empty($skill_rock_add)) $skill_rock_add=0;
	if(empty($skill_iorn_add)) $skill_iorn_add=0;
	$skill_food_add = ($food_add_base*$skill_food_add) / 100;
	$skill_wood_add = ($food_add_base*$skill_wood_add) / 100;
	$skill_rock_add = ($food_add_base*$skill_rock_add) / 100;
	$skill_iron_add = ($food_add_base*$skill_iron_add) / 100;
    sql_query("update mem_city_resource set people_working ='{$food_all_people}'*'{$rate['food_rate']}'/100+'{$wood_all_people}'*'{$rate['wood_rate']}'/100+'{$rock_all_people}'*'{$rate['rock_rate']}'/100+'{$iron_all_people}'*'{$rate['iron_rate']}'/100 where `cid`='{$cid}'");
    $city = sql_fetch_one("select * from mem_city_resource where `cid`='{$cid}'");
    $product_rate = $city['people'] / ($city['people_working'] == 0 ? 1 : $city['people_working']);
    if ($product_rate > 1) {
        $product_rate = 1;
    }
    sql_query("update sys_city_res_add set chief_add = '{$chief_add}' where `cid`='{$cid}'");
    if ($uid > 1000) {
        if ($food_add_base >= 1000) {
            completeTask($uid, 189);
        }
        if ($food_add_base >= 5000) {
            completeTask($uid, 190);
        }
        if ($food_add_base >= 10000) {
            completeTask($uid, 191);
        }
        if ($food_add_base >= 50000) {
            completeTask($uid, 192);
        }
        if ($wood_add_base >= 1000) {
            completeTask($uid, 193);
        }
        if ($wood_add_base >= 5000) {
            completeTask($uid, 194);
        }
        if ($wood_add_base >= 10000) {
            completeTask($uid, 195);
        }
        if ($wood_add_base >= 50000) {
            completeTask($uid, 196);
        }
        if ($rock_add_base >= 1000) {
            completeTask($uid, 197);
        }
        if ($rock_add_base >= 5000) {
            completeTask($uid, 198);
        }
        if ($rock_add_base >= 10000) {
            completeTask($uid, 199);
        }
        if ($rock_add_base >= 50000) {
            completeTask($uid, 200);
        }
        if ($iron_add_base >= 1000) {
            completeTask($uid, 201);
        }
        if ($iron_add_base >= 5000) {
            completeTask($uid, 202);
        }
        if ($iron_add_base >= 10000) {
            completeTask($uid, 203);
        }
        if ($iron_add_base >= 50000) {
            completeTask($uid, 204);
        }
    }
    $tlevel = sql_fetch_one_cell("select 1+level/10 from sys_city_technic where `tid`=15 and `cid`='{$cid}'");
    if (!$tlevel) {
        $tlevel = 1;
    }
    $food_add = 100 + $skill_food_add +((($food_add_base * $product_rate) * (1 + ((($food_add_rate_technic + $city_rate['field_food_add']) + $city_rate['goods_food_add']) + $chief_add) / 100)) * $city_rate['food_rate']) / 100;
    $food_max = 10000 + ($food_add_base * $tlevel) * 100;
    $wood_add = 100 + ((($wood_add_base * $product_rate) * (1 + ((($wood_add_rate_technic + $city_rate['field_wood_add']) + $city_rate['goods_wood_add']) + $chief_add) / 100)) * $city_rate['wood_rate']) / 100;
    $wood_max = 10000 + ($wood_add_base * $tlevel) * 100;
    $rock_add = 100 + ((($rock_add_base * $product_rate) * (1 + ((($rock_add_rate_technic + $city_rate['field_rock_add']) + $city_rate['goods_rock_add']) + $chief_add) / 100)) * $city_rate['rock_rate']) / 100;
    $rock_max = 10000 + ($rock_add_base * $tlevel) * 100;
    $iron_add = 100 + ((($iron_add_base * $product_rate) * (1 + ((($iron_add_rate_technic + $city_rate['field_iron_add']) + $city_rate['goods_iron_add']) + $chief_add) / 100)) * $city_rate['iron_rate']) / 100;
    $iron_max = 10000 + ($iron_add_base * $tlevel) * 100;
    sql_query("update mem_city_resource set `food_add`='{$food_add}',`food_max`='{$food_max}',`wood_add`='{$wood_add}',`wood_max`='{$wood_max}',`rock_add`='{$rock_add}',`rock_max`='{$rock_max}',`iron_add`='{$iron_add}',`iron_max`='{$iron_max}' where `cid`='{$cid}'");
    updateCityPeopleMax($cid);
    updateCityPeopleStable($cid);
    updateCityGoldMax($cid);
}
function getUsersCityProduct($uid,$cid){
	$city = sql_fetch_one("select * from sys_city_res_add where cid=".$cid);
	if (empty($city)){
		sql_query("insert into sys_city_res_add (cid) values ('$cid')");
		$city = sql_fetch_one("select * from sys_city_res_add where cid='$cid'");
	}
	//需要劳力
	$food_all_people = sql_fetch_one_cell("select sum(l.using_people) from sys_building b,cfg_building_level l where b.bid=l.bid and b.level=l.level and b.cid='".$cid."' and b.bid=".ID_BUILDING_FARMLAND);
	$wood_all_people = sql_fetch_one_cell("select sum(l.using_people) from sys_building b,cfg_building_level l where b.bid=l.bid and b.level=l.level and b.cid='".$cid."' and b.bid=".ID_BUILDING_WOOD);
	$rock_all_people = sql_fetch_one_cell("select sum(l.using_people) from sys_building b,cfg_building_level l where b.bid=l.bid and b.level=l.level and b.cid='".$cid."' and b.bid=".ID_BUILDING_ROCK);
	$iron_all_people = sql_fetch_one_cell("select sum(l.using_people) from sys_building b,cfg_building_level l where b.bid=l.bid and b.level=l.level and b.cid='".$cid."' and b.bid=".ID_BUILDING_IRON);
	//生产能力
	$food_add_base = GLOBAL_FOOD_RATE * $food_all_people * GAME_SPEED_RATE;
	$wood_add_base = GLOBAL_WOOD_RATE * $wood_all_people * GAME_SPEED_RATE;
	$rock_add_base = GLOBAL_ROCK_RATE * $rock_all_people * GAME_SPEED_RATE;
	$iron_add_base = GLOBAL_IRON_RATE * $iron_all_people * GAME_SPEED_RATE;
	//科技加成
	$food_add_rate_technic = sql_fetch_one_cell("select level*10 from sys_city_technic t where tid=".ID_TECHNIC_FOOD." and cid='".$cid."'");
	$wood_add_rate_technic = sql_fetch_one_cell("select level*10 from sys_city_technic t where tid=".ID_TECHNIC_WOOD." and cid='".$cid."'");
	$rock_add_rate_technic = sql_fetch_one_cell("select level*10 from sys_city_technic t where tid=".ID_TECHNIC_ROCK." and cid='".$cid."'");
	$iron_add_rate_technic = sql_fetch_one_cell("select level*10 from sys_city_technic t where tid=".ID_TECHNIC_IRON." and cid='".$cid."'");
	//将领加成
	$chief_add = 0;
	$skill_add = array(0=>0);
	$chiefHero = sql_fetch_one("select c.chiefhid,h.* from sys_city c left join sys_city_hero h on c.chiefhid=h.hid where h.cid=$cid and c.cid=".$cid);
	if ($chiefHero['chiefhid'] > 0)    //有将领的情况下
	{
		$hid = $chiefHero['chiefhid'];
		
		$chief_add = $chiefHero['affairs_add']+$chiefHero['affairs_base']+$chiefHero['affairs_add_on'];
		$heroCommand = $chiefHero["level"]+$chiefHero["command_base"]+$chiefHero["command_add_on"];
		$cityPeopleMax = sql_fetch_one_cell("select people_max from mem_city_resource where cid=".$cid);
		$hufu=1;
		if(sql_check("select hid from mem_hero_buffer where hid='$chiefHero[chiefhid]' and buftype=1 and endtime>unix_timestamp()"))
		{
			$hufu=1.5;
		}
		$leaderTechLevel = intval(sql_fetch_one_cell("select level from sys_city_technic where cid='$cid' and tid=6"));
		$peoplerate = $heroCommand*10.0* (100*$hufu + $leaderTechLevel * 10) / ($cityPeopleMax+1);

		if ($peoplerate > 1.0) $peoplerate = 1.0;
		$chief_add =  $chief_add * $peoplerate;

		//文曲星符增加内政25%
		if(sql_check("select hid from mem_hero_buffer where hid='$chiefHero[chiefhid]' and buftype=2 and endtime>unix_timestamp()"))
		{
			$chief_add=$chief_add*1.25;
		}
		
		if (!empty($hid)) {
			//将领技能
			$attrObj=0;
		    $attrObj = sql_fetch_one_cell("select b.id from sys_user_book a,cfg_book b where a.bid=b.id and a.level=b.level and a.bid=8 and a.hid=$hid");
			if (!empty($attrObj)) {//苛捐杂税
				$skill_add[0] = 1;
				$skill_add[] = $attrObj;
				$skill_add[] = $city['skill_gold_add'];
		    }
		    $attrObj=0;
			$attrObj = sql_fetch_one_cell("select b.id from sys_user_book a,cfg_book b where a.bid=b.id and a.level=b.level and a.bid=9 and a.hid=$hid");
		    if (!empty($attrObj)) {//安居乐业
		    	$skill_add[0] = 1;
		    	$skill_add[] = $attrObj;
		    	$res_add = array();
		    	$res_add[] = $city['skill_food_add'];
		    	$res_add[] = $city['skill_wood_add'];
		    	$res_add[] = $city['skill_rock_add'];
		    	$res_add[] = $city['skill_iron_add'];
		    	$skill_add[] = $res_add;
		    }
		    $attrObj=0;
			$attrObj = sql_fetch_one_cell("select b.id from sys_user_book a,cfg_book b where a.bid=b.id and a.level=b.level and a.bid=10 and a.hid=$hid");
		    if (!empty($attrObj)) {//五谷丰登
		    	$skill_add[0] = 1;
		    	$skill_add[] = $attrObj;
		    	$skill_add[] = $city['skill_food_add'];
		    }
		    $attrObj=0;
			$attrObj = sql_fetch_one_cell("select b.id from sys_user_book a,cfg_book b where a.bid=b.id and a.level=b.level and a.bid=11 and a.hid=$hid");
		    if (!empty($attrObj)) {//茂林密谷
		    	$skill_add[0] = 1;
		    	$skill_add[] = $attrObj;
		    	$skill_add[] = $city['skill_wood_add'];
		    }
		    $attrObj=0;
			$attrObj = sql_fetch_one_cell("select b.id from sys_user_book a,cfg_book b where a.bid=b.id and a.level=b.level and a.bid=12 and a.hid=$hid");
		    if (!empty($attrObj)) {//裂石穿云
		    	$skill_add[0] = 1;
		    	$skill_add[] = $attrObj;
		    	$skill_add[] = $city['skill_rock_add'];
		    }
		    $attrObj=0;
			$attrObj = sql_fetch_one_cell("select b.id from sys_user_book a,cfg_book b where a.bid=b.id and a.level=b.level and a.bid=13 and a.hid=$hid");
		    if (!empty($attrObj)) {//铸炼冶金
		    	$skill_add[0] = 1;
		    	$skill_add[] = $attrObj;
		    	$skill_add[] = $city['skill_iron_add'];
		    }
		}
	}

	//当兵吃粮
	$food_army_use = sql_fetch_one_cell("select food_army_use from mem_city_resource where cid='$cid'");
	$goods_food_endtime = 0;
	if ($city['goods_food_add'] > 0)
	{
		$goods_food_endtime = sql_fetch_one_cell("select endtime from mem_user_buffer where uid='$uid' and buftype=1");
	}
	$goods_wood_endtime = 0;
	if ($city['goods_wood_add'] > 0)
	{
		$goods_wood_endtime = sql_fetch_one_cell("select endtime from mem_user_buffer where uid='$uid' and buftype=2");
	}
	$goods_rock_endtime = 0;
	if ($city['goods_rock_add'] > 0)
	{
		$goods_rock_endtime = sql_fetch_one_cell("select endtime from mem_user_buffer where uid='$uid' and buftype=3");
	}
	$goods_iron_endtime = 0;
	if ($city['goods_iron_add'] > 0)
	{
		$goods_iron_endtime = sql_fetch_one_cell("select endtime from mem_user_buffer where uid='$uid' and buftype=4");
	}

	$ret = array();
	$ret[] = $city;
	$ret[] = $food_all_people;
	$ret[] = $wood_all_people;
	$ret[] = $rock_all_people;
	$ret[] = $iron_all_people;
	$ret[] = $food_add_base;
	$ret[] = $wood_add_base;
	$ret[] = $rock_add_base;
	$ret[] = $iron_add_base;
	$ret[] = $food_add_rate_technic;
	$ret[] = $wood_add_rate_technic;
	$ret[] = $rock_add_rate_technic;
	$ret[] = $iron_add_rate_technic;
	$ret[] = $food_army_use;
	$ret[] = $chief_add;

	$ret[] = $goods_food_endtime;
	$ret[] = $goods_wood_endtime;
	$ret[] = $goods_rock_endtime;
	$ret[] = $goods_iron_endtime;
	
	$ret[] = $skill_add;

	return $ret;
}
function check_people_building($cid){
    $people_building1 = sql_fetch_one_cell(('select sum(b.using_people) from cfg_building_level b,mem_building_upgrading u where u.cid=\'' . $cid) . '\' and b.bid=u.bid and b.level=u.level');
    $people_building2 = sql_fetch_one_cell(('select sum(b.using_people) from cfg_building_level b,mem_building_destroying d where d.cid=\'' . $cid) . '\' and b.bid=d.bid and b.level=d.level');
    sql_query(((('update mem_city_resource set people_building=\'' . $people_building1) . '\'+\'') . $people_building2) . "' where cid={$cid}");
}
function change_values($table, $name, $value, $where = 1){
    $sql = "update {$table} set {$name} = '{$value}' where {$where}";
    sql_query($sql);
}
function check_Technic($uid){
    $cities = sql_fetch_rows("SELECT cid FROM sys_city WHERE `uid`='{$uid}'");
    foreach ($cities as $city) {
        $technics = sql_fetch_rows("SELECT * FROM sys_technic WHERE `uid`='{$uid}'");
        foreach ($technics as $technic) {
            $min_level = 10;
            $bids = sql_fetch_rows("select * from cfg_technic_condition where `tid`='{$technic['tid']}' and `pre_type`=0 and `pre_id`=7 group by `pre_id`");
            foreach ($bids as $bid) {
                $curr_building_level = sql_fetch_one_cell("select level from sys_building where `cid`='{$city['cid']}' and `bid`='{$bid['pre_id']}' order by `level` desc limit 1");
                $level = sql_fetch_one_cell("select level from cfg_technic_condition where `tid`='{$technic['tid']}' and `pre_type`=0 and `pre_id`='{$bid['pre_id']}' and `pre_level`<='{$curr_building_level}' order by `level` desc limit 1");
                $sys_level = sql_fetch_one_cell("select level from sys_technic where `tid`='{$technic['tid']}' and `uid`='{$uid}'");
                if (!$sys_level) {
                    $level = 0;
                }
                if (!$level) {
                    $level = 0;
                }
                $min_level = min($min_level, $level, $sys_level);
                sql_query("replace into sys_city_technic VALUES ('{$city['cid']}','{$technic['tid']}','{$min_level}')");
                $min_level = $level;
            }
        }
        if ($technic['tid'] < 5) {
            check_city_resource($uid, $city['cid']);
        }
    }
}
function add_troop_array($troop, $soldierArray){
    $target_soldiers = troop2array($troop['soldiers']);
    if (is_array($soldierArray)) {
        $soldierArray = getArrayMerge($soldierArray, $target_soldiers);
    } else {
        $soldierArray = $target_soldiers;
    }
    $soldierNum = 0;
    $comma = '';
    foreach ($soldierArray as $sid => $cnt) {
        if ($sid > 0 && $cnt > 0) {
            $soldiers .= $comma;
            $soldiers .= ($sid . ',') . $cnt;
            $comma = ',';
            $soldierNum++;
        }
    }
    if ($soldiers) {
        $soldiers = ($soldierNum . ',') . $soldiers;
    } else {
        return 0;
    }
    return $soldiers;
}
function add_soldier($soldier1, $soldier2){
    $soldierArray1 = troop2array($soldier1);
    $soldierArray2 = troop2array($soldier2);
    $soldierArray = getArrayMerge($soldierArray1, $soldierArray2);
    $soldiers = array2troop($soldierArray);
    return $soldiers;
}
function addResource($resource1, $resource2){
    $rArray1 = $resource1;
    $rArray2 = explode(',', $resource2);
    $i = 0;
    foreach ($rArray1 as $v) {
        $ret[] = $v + $rArray2[$i];
        $i++;
    }
    $resource = implode(',', $ret);
    return $resource;
}
function getArrayMerge($descs, $json_wares){
    if (is_array($descs) && is_array($json_wares)) {
        $arrayMerge = array();
        foreach ($json_wares as $key => $value) {
            if (array_key_exists($key, $descs)) {
                $arrayMerge[$key] = $value + $descs[$key];
                unset($descs[$key]);
            } else {
                $arrayMerge[$key] = $value;
            }
        }
        $result = $arrayMerge + $descs;
        ksort($result);
        return $result;
    } else {
        return false;
    }
}
function getArraySub($descs = array(), $json_wares = array()){
    if (!is_array($descs)) {
        $descs = array();
    }
    if (!is_array($json_wares)) {
        $json_wares = array();
    }
    if (is_array($descs) && is_array($json_wares)) {
        $arrayMerge = array();
        foreach ($json_wares as $key => $value) {
            if (array_key_exists($key, $descs)) {
                $arrayMerge[$key] = $descs[$key] - $value;
                unset($descs[$key]);
            } else {
                $arrayMerge[$key] = 0 - $value;
            }
        }
        $result = $arrayMerge + $descs;
        ksort($result);
        return $result;
    } else {
        return array();
    }
}
function arraySubArray($a, $b){
    if (!is_array($a)) {
        $a = array();
    }
    if (!is_array($b)) {
        $b = array();
    }
    foreach ($a as $key => $v) {
        foreach ($b as $v1) {
            if ($v == $v1) {
                unset($a[$key]);
            }
        }
    }
    return $a;
}
function check_inwar($cid, $targetcid, $task = '0'){    
    $msg='';
    $targetIsUnion = false;
    $targetuid = 0;
    $uid = sql_fetch_one_cell("select uid from sys_city where `cid`='{$cid}'");
    $targetwid = cid2wid($targetcid);
    $worldinfo = sql_fetch_one("select * from mem_world where `wid`='{$targetwid}'");
    $targetCityInfo = sql_fetch_one("select * from sys_city where `cid`='{$worldinfo['ownercid']}'");
    $targetcitytype = $targetCityInfo['type'];
    $taskname = array($GLOBALS['StartTroop']['transport'], $GLOBALS['StartTroop']['send'], $GLOBALS['StartTroop']['detect'], $GLOBALS['StartTroop']['harry'], $GLOBALS['StartTroop']['occupy']);
    if ($worldinfo['ownercid'] == $cid) {
        $targetIsUnion = true;
        $targetuid = $uid;
    } else {
        if ($worldinfo['type'] == 0) {
            $targetuid = $targetCityInfo['uid'];
        } else {
            if ($worldinfo['ownercid'] != 0) {
                $targetuid = $targetCityInfo['uid'];
            }
        }
    }
    $myUserInfo = sql_fetch_one("select * from sys_user where `uid`='{$uid}'");
    $targetUserInfo = sql_fetch_one("select * from sys_user where `uid`='{$targetuid}'");
    $result['relation'] = '1';
    if (!empty($targetuid)) {
        $targetunion = $targetUserInfo['union_id'];
        $myunion = $myUserInfo['union_id'];
        if ($uid == $targetuid) {
            $targetIsUnion = true;
            $result['relation'] = '0';
        } else {
            if (!empty($targetunion)) {
                if ($myunion == $targetunion && $myunion > 0) {
                    $targetIsUnion = true;
                    $result['relation'] = '0';
                }
            }
        }
        if ($worldinfo['type'] == 0 && !$targetIsUnion) {
            $union_relation = sql_fetch_one("select * from sys_union_relation where `unionid`='{$myunion}' and `target`='{$targetunion}'");
            if ((!empty($union_relation) && $union_relation['type'] == 0) && $task == 2) {
                $msg = $GLOBALS['StartTroop']['cant_detect_friendly_union'];
                $result['relation'] = '0';
            }
        }
    }
    $mystate = $myUserInfo['state'];
    if ($task == 0) {
        if (!($targetIsUnion && $worldinfo['type'] == 0)) {
            $msg = $GLOBALS['StartTroop']['only_transport_to_friendly'];
            ${$result['result']} = 0;
        } else {
            if ($targetuid != $uid) {
                if ($mystate == 2 || $mystate == 1) {
                    $msg = $GLOBALS['StartTroop']['transport_in_peace_or_protection'];
                    $result['result'] = 0;
                }
            }
        }
    } else {
        if ($task == 1) {
            if (!$targetIsUnion) {
                $msg = $GLOBALS['StartTroop']['only_send_to_friendly'];
                $result['result'] = 0;
            } else {
                if ($targetuid != $uid) {
                    if ($mystate == 2 || $mystate == 1) {
                        $msg = $GLOBALS['StartTroop']['send_in_peace_or_protection'];
                    }
                    $allowUnionTroop = getAllowUnionTroop($targetuid, $targetcid);
                    if (empty($allowUnionTroop)) {
                        $msg = $GLOBALS['StartTroop']['not_allow_union_troop'];
                        $result['result'] = 0;
                    }
                }
            }
        } else {
            if ($targetIsUnion) {
                $msg = sprintf($GLOBALS['StartTroop']['only_towards_enemy'], $taskname[$task]);
            }
        }
    }
    if (empty($msg)) {
        $result['result'] = true;
    } else {
        $result['result'] = false;
    }
    $result['msg'] = $msg;
    return $result;
}
function change_hero($uid, $cid, $hid, $state, $targetuid = '0', $targetcid = '0'){
    $resalt = sql_check("select * from sys_city_hero,sys_city where sys_city_hero.uid='{$uid}' and sys_city_hero.cid='{$cid}' and sys_city_hero.hid='{$hid}' and sys_city_hero.uid=sys_city.uid and sys_city_hero.cid");
    if ($resalt && $hid > 0) {
        if ($targetuid == 0) {
            $targetuid = $uid;
        }
        if ($targetcid == 0) {
            $targetcid = $cid;
        }
        if ($cid != $targetcid) {
            if ($uid == $targetuid) {
                sql_query("update sys_city_hero set `cid`='{$targetcid}',`state`='{$state}' where `hid`='{$hid}'");
            } else {
                sql_query("update sys_city_hero set `uid`='{$targetuid}',`cid`='{$targetcid}',`state`='{$state}' where `hid`='{$hid}'");
            }
            updateCityHeroChange($uid, $cid);
            updateCityHeroChange($targetuid, $targetcid);
        } else {
            sql_query("update sys_city_hero set `state`='{$state}' where `hid`='{$hid}'");
        }
    }
}
function troop2array($soldiers = 0){
    if ($soldiers) {
        $soldierArray = explode(',', $soldiers);
        $numSoldiers = array_shift($soldierArray);
        $takeSoldiers = array();
        for ($i = 0; $i < $numSoldiers; $i++) {
            $sid = array_shift($soldierArray);
            $cnt = array_shift($soldierArray);
            if ($cnt > 0) {
                $takeSoldiers[$sid] = $cnt;
            }
        }
        return $takeSoldiers;
    } else {
        return array();
    }
}
function defence2array($soldiers){
    $soldierArray = explode(',', $soldiers);
    $numSoldiers = array_shift($soldierArray);
    $takeSoldiers = array();
    for ($i = 0; $i < $numSoldiers; $i++) {
        $sid = array_shift($soldierArray);
        $oldcnt = array_shift($soldierArray);
        $cnt = array_shift($soldierArray);
        if ($cnt < 0) {
            $cnt = 0;
        }
        $takeSoldiers[$sid]['cnt'] = $cnt;
        $takeSoldiers[$sid]['oldcnt'] = $oldcnt;
    }
    return $takeSoldiers;
}
function check_resource($resource){
    if ($resource != '0' && $resource != '0,0,0,0,0,') {
        $resourceArray = explode(',', $resource);
        $resource = array();
        $resource['gold'] = array_shift($resourceArray);
        $resource['food'] = array_shift($resourceArray);
        $resource['wood'] = array_shift($resourceArray);
        $resource['rock'] = array_shift($resourceArray);
        $resource['iron'] = array_shift($resourceArray);
    }
    return $resource;
}
function add_resource($r1, $r2, $carry = 0){
    $resource1 = check_resource($r1);
    $resource2 = check_resource($r2);
    $all1 = ((($resource1['gold'] + $resource1['food']) + $resource1['wood']) + $resource1['rock']) + $resource1['iron'];
    $all2 = ((($resource2['gold'] + $resource2['food']) + $resource2['wood']) + $resource2['rock']) + $resource2['iron'];
    if ($all1 + $all2 > $carry) {
        $x = ($carry - $all1) / $all2;
    } elseif ($all2 > 0) {
        $x = 1;
    } else {
        $x = 0;
    }
    if ($carry == 0) {
        $x = 0;
    }
    $resources['gold'] = floor($resource1['gold'] + $resource2['gold'] * $x);
    $resources['food'] = floor($resource1['food'] + $resource2['food'] * $x);
    $resources['wood'] = floor($resource1['wood'] + $resource2['wood'] * $x);
    $resources['rock'] = floor($resource1['rock'] + $resource2['rock'] * $x);
    $resources['iron'] = floor($resource1['iron'] + $resource2['iron'] * $x);
    $resource['0'] = ((((((($resources['gold'] . ',') . $resources['food']) . ',') . $resources['wood']) . ',') . $resources['rock']) . ',') . $resources['iron'];
    $grobres = $resources['gold'] - $resource1['gold'];
    $frobres = $resources['food'] - $resource1['food'];
    $wrobres = $resources['wood'] - $resource1['wood'];
    $rrobres = $resources['rock'] - $resource1['rock'];
    $irobres = $resources['iron'] - $resource1['iron'];
    $resource['1'] = ((((((($grobres . ',') . $frobres) . ',') . $wrobres) . ',') . $rrobres) . ',') . $irobres;
    return $resource;
}
function get_city_resource($cid, $city = 0){
    $myres = sql_fetch_one("select * from mem_city_resource where `cid`='{$cid}'");
    if (empty($myres)) {
        return false;
    }
    $wood = $myres['wood'];
    $rock = $myres['rock'];
    $iron = $myres['iron'];
    $food = $myres['food'];
    $gold = $city > 0 ? $myres['gold'] : 0;
    $res = ((((((($gold . ',') . $food) . ',') . $wood) . ',') . $rock) . ',') . $iron;
    return $res;
}
function check_field_owner($cid){
    $wid = cid2wid($cid);
    $field = sql_fetch_one("select * from mem_world where `wid`='{$wid}'");
	if ($field['ownercid'] > 0) {
        $field['uid'] = sql_fetch_one_cell("select uid from sys_city where `cid`='{$field['ownercid']}'");
    } else {
        $field['uid'] = sql_fetch_one_cell("select uid from sys_troops WHERE `cid`='{$cid}' limit 1");
        if (!$field['uid']) {
            $field['uid'] = 0;
        }
    }
    return $field;
}

function check_city_ground_hero($cid){
    $ret = sql_fetch_one("select hid,state from sys_city_hero where `cid`='{$cid}' and state in (0,7) and `uid`=(select uid from sys_city where cid='{$cid}') order by state desc limit 1");
    if (!$ret) {
        $ret['hid'] = 0;
        $ret['state'] = 0;
    }
    return $ret;
}
function get_defcity_hero($cid){
    $ret = sql_fetch_one_cell("select hid from sys_city_hero where `cid`='$cid' and state=7");
    if(!empty($ret)) return $ret;
	$ret = sql_fetch_one_cell("select hid from sys_city_hero where `cid`='$cid' and state=1");
    if(!empty($ret)) return $ret;
	$ret = sql_fetch_one_cell("select hid from sys_city_hero where `cid`='$cid' and state=8");
    if(!empty($ret)) return $ret;
	$ret = sql_fetch_one_cell("select hid from sys_city_hero where `cid`='$cid' and state=0 limit 1");
    if(!empty($ret)) return $ret;
	return 0;
}
function checkTactics($uid, $troopid){
    $tactics = sql_fetch_one("select * from sys_troop_tactics where `troopid`='{$troopid}'");
    if (!$tactics) {
        $tactics = sql_fetch_one("select * from sys_user_tactics where `uid`='{$uid}'");
    }
    if (!$tactics) {
        $plunder = '1,1,1;2,1,2;3,1,3;4,1,4;5,1,5;6,1,6;7,1,7;8,1,8;9,1,9;10,1,10;11,1,11;12,1,12;45,2,45;46,2,46;47,2,47;48,2,48;49,2,49;50,2,50';
        $invade = '1,1,1,1,1;2,1,2,1,2;3,1,3,1,3;4,1,4,1,4;5,1,5,1,5;6,1,6,1,6;7,1,7,1,7;8,1,8,1,8;9,1,9,1,9;10,1,10,1,10;11,1,11,1,11;12,1,12,1,12;45,2,45,2,45;46,2,46,2,46;47,2,47,2,47;48,2,48,2,48;49,2,49,2,49;50,2,50,2,50';
        $field = '1,1,1;2,1,2;3,1,3;4,1,4;5,1,5;6,1,6;7,1,7;8,1,8;9,1,9;10,1,10;11,1,11;12,1,12;45,2,45;46,2,46;47,2,47;48,2,48;49,2,49;50,2,50';
        $patrol = '3,1,3';
        sql_query("insert into sys_user_tactics (`uid`,`plunder`,`invade`,`field`,`patrol`) values ('{$uid}','{$plunder}','{$invade}','{$field}','{$patrol}') on duplicate key update `plunder`='{$plunder}',`invade`='{$invade}',`field`='{$field}',`patrol`='{$patrol}'");
        $tactics = array('plunder' => $plunder, 'invade' => $invade, 'field' => $field, 'patrol' => $patrol);
    }
    return $tactics;
}
function checkSodier($field){
    $type = $field['type'];
    $wid = $field['wid'];
    $cid = wid2cid($wid);
    $level = $field['level'];
    $uid = $field['uid'];
    $hid = choseHero($cid);
	$sodiertype[14][0]='87,88,89,90,91';//匈奴
	$sodiertype[14][1]='87,88,89,90,91,92,93,94,95';
	$sodiertype[15][0]='78,79,80,81,82';//乌丸
	$sodiertype[15][1]='78,79,80,81,82,83,84,85,86';
	$sodiertype[16][0]='52,53,54,55,56';//羌
	$sodiertype[16][1]='51,52,53,54,55,56,57,58,59';
	$sodiertype[17][0]='60,61,62,63,64';//南蛮
	$sodiertype[17][1]='60,61,62,63,64,65,66,67,68';
	$sodiertype[18][0]='69,70,71,72,73';//山越
	$sodiertype[18][1]='69,70,71,72,73,74,75,76,77';
	$s_province=sql_fetch_one_cell("select province from mem_world where `wid`='{$wid}'");
    if ($uid < 1000) {
        if (($type == 0 && $uid < 1000) && $level > 0) {//占领NPC城池先建城防
            createCityDefence($field);
        }
        $time = sql_fetch_one_cell("select (unix_timestamp()-last_create_npc)/3600 from mem_world_schedule where `wid`='{$wid}'");
        if (!$time || $time > 1) {
            $time = 1;
        } else if ($time <= 0) {
            $time = 0.0005;
        }
        if ($type == 0) {//占领情况
		  if($uid == NPC_HUANJIN){
              $soldierType = '18,19,20,21,22';
			}
			else if($uid == 896){//十常侍
			   $soldierType = '23,24,25,26,27';
			 } else if($uid == 659){//董卓
				   $soldierType = '28,29,30,31,32';
			   }
                else {
				  $soldierType = '1,2,3,4,5,6,7,8,9,10,11,12';
				  if($level<8) $soldierType = '1,2,3,4,5,6,7';
				  if($s_province>13){
 				      $i = $level>7?1:0;
				      $soldierType =$sodiertype[$s_province][$i];
					}                
                }
            $npcValue = sql_fetch_one_cell("select npcvalue from cfg_bigcity_npcvalue where `cid`='{$cid}'");
            if (!$npcvalue) {
                $npcValue = sql_fetch_one_cell("select npcvalue from cfg_city_npcvalue where `level`='{$level}'");
            }
			$cidtype=sql_fetch_one_cell("select type from sys_city where `cid`='{$cid}'");
            $npcValue=$npcValue *($cidtype + $level/4 + 1.8);
			if($s_province>13) $npcValue *= 2.5;
        } else {
          $npcValue = sql_fetch_one_cell("select npcvalue from cfg_field_npcvalue where `level`='{$level}'");
          $mmsg_uid = mt_rand(0,6);
		  switch($mmsg_uid){
			  case 1:{$soldierType ='18,19,20,21,22';$uid=894;if($s_province>13)$soldierType =$sodiertype[$s_province][0]; break;}
			  case 2:{$soldierType ='23,24,25,26,27';$uid=896;if($s_province>13)$soldierType =$sodiertype[$s_province][0]; break;}
			  case 3:{$soldierType ='28,29,30,31,32';$uid=659;if($s_province>13)$soldierType =$sodiertype[$s_province][0]; break;}
			  default:{$soldierType ='13,14,15,16,17';$uid=0;if($s_province>13)$soldierType =$sodiertype[$s_province][0]; break;}
			}
          if (sql_check("select cid from sys_city_hero where `npcid`<>0 and `cid`='{$cid}'")) {
              if ($level < 8) {
                    $soldierType = '1,2,3,4,5,6,7';
					if($s_province>13) $soldierType =$sodiertype[$s_province][0];
                } else {
                    $soldierType = '1,2,3,4,5,6,7,8,9,10';
					if($s_province>13) $soldierType =$sodiertype[$s_province][1];
                }
            }
          $npcValue = $npcValue*($level/4+1.5);
		  if($s_province>13) $npcValue *= 1.8;
        }
        if (!$npcValue) {
            $npcValue = $type == 0 ? '5000' : '500';
        }
       	
        if ($type > 0) {
            $oldTroop = sql_fetch_one("select * from sys_troops where `cid`='{$cid}' and `uid`<1000 order by id asc limit 1");
            if ($oldTroop) {
                $uid = $oldTroop['uid'];
                $soldierType = checkSoldierType($oldTroop['soldiers']);
                $soValue = sol2Value($oldTroop['soldiers']);
            } else {
                $soValue = 0;
            }
            $npcValue = ($npcValue - $soValue) * $time;
            $soldierArray = createmapSoldier($npcValue, $soldierType, $type);
            if ($oldTroop) {
                $soldiers = add_troop_array($oldTroop, $soldierArray);
            } else {
                $soldiers = array2troop($soldierArray);
            }
            if ($soldiers != 0) {
                sql_query("delete from sys_troops where cid='{$cid}' and `uid`<'1000'");
                $value = sol2Value($soldiers);
                $resource = v2r($value, $type);
                sql_query("insert into sys_troops(`uid`,`cid`,`hid`,`task`,`state`,`soldiers`,`resource`) values('{$uid}','{$cid}','{$hid}','5','4','{$soldiers}','{$resource}')");
            } else {
                return $uid;
            }
        } else {
            $soValue = 0;
            $oldTroop = sql_fetch_rows("select * from sys_city_soldier where `cid`='{$cid}' and `count`>0");
            if ($oldTroop) {
                foreach ($oldTroop as $troop) {
                    $soldierArray[$troop['sid']] = $troop['count'];
                }
                $soValue = sol2Value($soldierArray);
            }
            $npcValue = ($npcValue - $soValue) * $time;
            $soldierArray = createmapSoldier($npcValue, $soldierType, $type);
            if ($soldierArray) {
                addCitySoldiers($cid, $soldierArray, '1');
            } else {
                return $uid;
            }
        }
        sql_query("insert into mem_world_schedule(`wid`,`last_create_npc`) values ('{$wid}',unix_timestamp()) on duplicate key update `last_create_npc`=unix_timestamp()");
    }
	return $uid;
}
function createCityDefence($field){
    $type = $field['type'];
    $wid = $field['wid'];
    $cid = wid2cid($wid);
    $level = $field['level'];
    $uid = $field['uid'];
    if ($type == 0 && $uid < 1000) {
        $time = sql_fetch_one_cell("select (unix_timestamp()-last_create_defence)/3600 from mem_world_schedule where `wid`='{$wid}'");
        if (!$time || $time > 1) {
            $time = 1;
        } elseif ($time <= 0) {
            $time = 0.0005;
        }
        switch ($level) {
        case 1:
            $sql = "('{$cid}',1,'{$count}')";
            break;
        case 2:
            $sql = "('{$cid}',1,'11082'),('{$cid}',2,'6212')";
            break;
        case 3:
            $sql = "('{$cid}',1,'12365'),('{$cid}',2,'6418'),('{$cid}',3,'3980')";
            break;
        case 4:
            $sql = "('{$cid}',1,'13333'),('{$cid}',2,'6666'),('{$cid}',3,'4445')";
            break;
        case 5:
            $sql = "('{$cid}',1,'12687'),('{$cid}',2,'6344'),('{$cid}',3,'4229'),('{$cid}',4,'2985')";
            break;
        case 6:
            $sql = "('{$cid}',1,'13662'),('{$cid}',2,'7631'),('{$cid}',3,'5088'),('{$cid}',4,'3553')";
            break;
        case 7:
            $sql = "('{$cid}',1,'14644'),('{$cid}',2,'7322'),('{$cid}',3,'4881'),('{$cid}',4,'3381'),('{$cid}',5,'2509')";
            break;
        case 8:
            $sql = "('{$cid}',1,'16828'),('{$cid}',2,'8414'),('{$cid}',3,'5610'),('{$cid}',4,'3847'),('{$cid}',5,'2826')";
            break;
        case 9:
            $sql = "('{$cid}',1,'19035'),('{$cid}',2,'9518'),('{$cid}',3,'6345'),('{$cid}',4,'4309'),('{$cid}',5,'3132')";
            break;
        case 10:
            $sql = "('{$cid}',1,'21265'),('{$cid}',2,'10633'),('{$cid}',3,'7088'),('{$cid}',4,'4767'),('{$cid}',5,'3428')";
            break;
        }
        $sql1 = "insert into sys_city_defence (`cid`,`did`,`count`) values {$sql} on duplicate key update `count`=case when((`count`+values(count)*{$time})>4294967000) then 0 when((`count`+values(count)*'{$time}')>values(count)) then values(count) else (`count`+values(count)*{$time}) end";
        sql_query($sql1);
        sql_query("insert into mem_world_schedule(`wid`,`last_create_defence`) values ('{$wid}',unix_timestamp()) on duplicate key update `last_create_defence`=unix_timestamp()");
    }
}
function v2r($value, $type){
    if ($value > 0) {
        $value /= 10;
        $gold = 0;
        $food = 0;
        $wood = 0;
        $rock = 0;
        $iron = 0;
        switch ($type) {
        case 0:
            break;
        case 1:
            $temp = mt_rand(0, 4);
            break;
        case 2:
            $temp = 3;
            break;
        case 3:
            $temp = 2;
            break;
        case 4:
            $temp = 1;
            break;
        case 5:
            $temp = 4;
            break;
        case 6:
            $temp = 1;
            break;
        case 7:
            $temp = 1;
            break;
        }
        switch ($temp) {
        case 0:
            $gold = floor($value / 10);
            break;
        case 1:
            $food = floor($value);
            break;
        case 2:
            $wood = floor($value);
            break;
        case 3:
            $rock = floor($value / 2);
            break;
        case 4:
            $iron = floor($value * 0.4);
            break;
        }
        $gold = $gold < 0 ? 0 : $gold;
        $food = $food < 0 ? 0 : $food;
        $wood = $wood < 0 ? 0 : $wood;
        $rock = $rock < 0 ? 0 : $rock;
        $iron = $iron < 0 ? 0 : $iron;
        $resource = (((((((($gold . ',') . $food) . ',') . $wood) . ',') . $rock) . ',') . $iron) . ',';
    } else {
        $resource = 0;
    }
    return $resource;
}
function array2troop($soldierArray, $def = 0){
    $i = 0;
    if ($def) {
        if ($soldierArray) {
            foreach ($soldierArray as $sid => $cnt) {
                if ($cnt > 0) {
                    $soldiers .= (((($sid . ',') . $cnt) . ',') . $cnt) . ',';
                    $i++;
                }
            }
        }
    } else {
        foreach ($soldierArray as $sid => $cnt) {
            if ($cnt > 0) {
                $soldiers .= (($sid . ',') . $cnt) . ',';
                $i++;
            }
        }
    }
    if ($i) {
        $soldiers = ($i . ',') . $soldiers;
    } else {
        $soldiers = $i;
    }
    return $soldiers;
}
function city2soldier($cid){
    $soldiers = sql_fetch_rows("select * from sys_city_soldier where cid='{$cid}' and `count`>0");
    $soldierArray = array();
    if ($soldiers) {
        foreach ($soldiers as $soldier) {
            $soldierArray[$soldier['sid']] = $soldier['count'];
        }
    } else {
        $soldierArray[2] = 1;
    }
    return $soldierArray;
}
function checkSoldierType($soldiers){
    if (!is_array($soldiers)) {
        $newsoldiers = troop2array($soldiers);
    } else {
        $newsoldiers = $soldiers;
    }
    foreach ($newsoldiers as $sid => $cnt) {
        $soldierType .= $sid . ',';
    }
    return substr($soldierType, 0, strlen($soldierType) - 1);
}
function createmapSoldier($npcValue, $soldierType, $type){
    $soldiersarray = explode(',', $soldierType);
    $count = count($soldiersarray);
    $soldiervalue = $GLOBALS['soldier']['soldiervalue'];
    $totalRnd = 0;
    $valueMap = array();
    $npcSoldiers = '';
    $typecount = 0;
    $nowcount = 0;
    if ($count < 6) {
        foreach ($soldiersarray as $sid) {
            $rnd = rand() % (100 - $totalRnd);
            $rnd = $totalRnd + $rnd > 100 ? 100 - $totalRnd : $rnd;
            if ($rnd != 0) {
                $valueMap[$sid] = $rnd;
                $totalRnd += $rnd;
                $typecount++;
            }
            if ($totalRnd > 100) {
                break;
            }
        }
    } else {
        arsort($soldiersarray);
        foreach ($soldiersarray as $sid) {
            $rnd = mt_rand(1, 5) == 1 && $type != 0 ? 0 : mt_rand(0, 100 - $totalRnd);
            for ($rnd; $rnd > 10; $rnd = floor($rnd / 2)) {
                
            }
            if ($nowcount == $count - 1) {
                $rnd = 100 - $totalRnd;
            }
            $rnd = $totalRnd + $rnd > 100 ? 100 - $totalRnd : $rnd;
            if ($rnd != 0) {
                $valueMap[$sid] = $rnd;
                $totalRnd += $rnd;
                $typecount++;
            }
            if ($totalRnd >= 100) {
                break;
            }
            $nowcount++;
        }
    }
    foreach ($valueMap as $k => $v) {
        if ($k > 0 && $v > 0) {
            $count = (int) floor(((($npcValue * $v) * 0.0078) / $soldiervalue[$k]));
            if ($count < 1) {
                $typecount--;
            } else {
                $npcSoldiers .= (($k . ',') . $count) . ',';
                $troops[$k] = $count;
            }
        }
    }
    if (!$troops) {
        $troops[] = 0;
    } else {
        ksort($troops);
    }
    return $troops;
}
function rand_troop($soldiers){
    $soldiersarray = explode(',', $soldiers);
    shuffle($soldiersarray);
    $soldiersarray = array_slice($soldiersarray, 0, mt_rand(1, 5));
    asort($soldiersarray);
    $soldiers = implode($soldiersarray, ',');
    return $soldiers;
}
function choseHero($cid){
    $hid = sql_fetch_one_cell("select hid from sys_city_hero where cid='{$cid}' and state='7' limit 1");
    if (!$hid) {
        $hid = sql_fetch_one_cell("select hid from sys_city_hero where cid='{$cid}' and state='1' limit 1");
    }
    if (!$hid) {
        $hid = sql_fetch_one_cell("select hid from sys_city_hero where cid='{$cid}' and state='8' limit 1");
    }
	if (!$hid) {
        $hid = sql_fetch_one_cell("select hid from sys_city_hero where cid='{$cid}' order by rand() limit 1");
    }
    if (!$hid) {
        $hid = 0;
    }
    return $hid;
}
function sol2Value($soldiers){
    $soldiervalue = $GLOBALS['soldier']['soldiervalue'];
    if (!is_array($soldiers)) {
        $soldierArray = explode(',', $soldiers);
        $numSoldiers = array_shift($soldierArray);
        $takeSoldiers = array();
        $exp = 0;
        for ($i = 0; $i < $numSoldiers; $i++) {
            $sid = array_shift($soldierArray);
            $cnt = array_shift($soldierArray);
            $exp += (int) floor((($cnt * $soldiervalue[$sid]) / 0.784));
        }
    } else {
        foreach ($soldiers as $sid => $cnt) {
            $exp += (int) floor((($cnt * $soldiervalue[$sid]) / 0.784));
        }
    }
    return floor($exp);
}
function array2count($soldierArray){
    $allcnt = 0;
    foreach ($soldierArray as $cnt) {
        $allcnt += $cnt;
    }
    return $allcnt;
}
function getContentHero($hid, $isbattle = 0){
    if ($hid > 0) {
        if ($isbattle) {
            $hero = sql_fetch_one('SELECT name,level,face,sex FROM cfg_battle_hero WHERE hid=' . $hid);
        } else {
            $hero = sql_fetch_one('SELECT name,level,face,sex FROM sys_city_hero WHERE hid=' . $hid);
        }
        if ($hero['sex']) {
            $sex = 'boy';
        } else {
            $sex = 'girl';
        }
        $ret = sprintf($GLOBALS['report']['heroname'], ($sex . '_') . $hero['face'], $hero['name'], $hero['name'], $hero['level']);
    } else {
        $ret = $GLOBALS['report']['empty'];
    }
    return $ret;
}
function getContentBattleHero($hid, $isbattle = 0){
    if ($hid > 0) {
        if ($isbattle) {
            $hero = sql_fetch_one('SELECT name,level,face,sex FROM cfg_battle_hero WHERE hid=' . $hid);
        } else {
            $hero = sql_fetch_one('SELECT name,level,face,sex FROM sys_city_hero WHERE hid=' . $hid);
        }
        if ($hero['sex']) {
            $sex = 'boy';
        } else {
            $sex = 'girl';
        }
        $ret = sprintf($GLOBALS['report']['hero'], ($sex . '_') . $hero['face'], $hero['name'], $hero['name'], $hero['level']);
    } else {
        $ret = $GLOBALS['report']['empty'];
    }
    return $ret;
}
function getAdd($cid, $hid, $batteler = 0){
    if ($hid == 0) {
        $heroadd['command_base'] = 0;
        $heroadd['command_add_on'] = 0;
        $heroadd['bravery_base'] = 0;
        $heroadd['bravery_add'] = 0;
        $heroadd['attack_add_on'] = 0;
        $heroadd['wisdom_base'] = 0;
        $heroadd['wisdom_add'] = 0;
        $heroadd['defence_add_on'] = 0;
        $heroadd['speed_add_on'] = 0;
        $heroadd['affairs_base'] = 0;
        $heroadd['affairs_add_on'] = 0;
        $heroadd['level'] = 0;
    } elseif (!$batteler) {
        $heroadd = sql_fetch_one("select * from sys_city_hero where `hid`='{$hid}'");
    } else {
        $heroadd = sql_fetch_one("select * from cfg_battle_hero where `hid`='{$hid}'");
        $heroadd['command_base'] = $heroadd['command'];
        $heroadd['command_add_on'] = 0;
        $heroadd['bravery_base'] = $heroadd['bravery'];
        $heroadd['bravery_add'] = 0;
        $heroadd['attack_add_on'] = 0;
        $heroadd['wisdom_base'] = $heroadd['wisdom'];
        $heroadd['wisdom_add'] = 0;
        $heroadd['defence_add_on'] = 0;
        $heroadd['speed_add_on'] = $heroadd['speedadd'];
        $heroadd['affairs_base'] = $heroadd['affairs'];
        $heroadd['affairs_add_on'] = 0;
    }
    $goods_command = 0;
    $goods_blood = 0;
    $goods_att = 0;
    $goods_def = 0;
    $agoodsBufs = sql_fetch_rows("select buftype from mem_hero_buffer WHERE `endtime`>unix_timestamp() and `hid`='{$hid}'");
    if (!empty($agoodsBufs)) {
        foreach ($agoodsBufs as $agoodsBuf) {
            switch ($agoodsBuf['buftype']) {
            case 1:
                $goods_command = 0.5;
                break;
            case 2:
                $goods_blood = 0.25;
                break;
            case 3:
                $goods_att = 0.25;
                break;
            case 4:
                $goods_def = 0.25;
                break;
            }
        }
    }
    $tec_command_add = sql_fetch_one_cell("select level from sys_city_technic where `cid`='{$cid}' and `tid`='6'");
    $tec_attc_add = sql_fetch_one_cell("select level from sys_city_technic where `cid`='{$cid}' and `tid`='9'");
    $tec_def_add = sql_fetch_one_cell("select level from sys_city_technic where `cid`='{$cid}' and `tid`='10'");
    $tec_speed_add = sql_fetch_one_cell("select level from sys_city_technic where `cid`='{$cid}' and `tid`='12'");
    $tec_jiayu = sql_fetch_one_cell("select level from sys_city_technic where cid='{$cid}' and tid='13'");
    $tec_blood_add = sql_fetch_one_cell("select level from sys_city_technic where `cid`='{$cid}' and `tid`='16'");
    $tec_plund_add = sql_fetch_one_cell("select level from sys_city_technic where `cid`='{$cid}' and `tid`='20'");
    $command = ($heroadd['command_base'] + $heroadd['level']) * ((1 + $goods_command) + 0.1 * $tec_command_add) + $heroadd['command_add_on'];
    $act = (($heroadd['bravery_base'] + $heroadd['bravery_add']) * ((1 + $goods_att) + 0.05 * $tec_attc_add) + $heroadd['bravery_add_on']) + $heroadd['attack_add_on'] / 10;
    $def = (($heroadd['wisdom_base'] + $heroadd['wisdom_add']) * ((1 + $goods_def) + 0.05 * $tec_def_add) + $heroadd['wisdom_add_on']) + $heroadd['defence_add_on'] / 10;
    $speed = $heroadd['speed_add_on'];
    $speed1 = 0.1 * $tec_speed_add;
    $speed2 = $tec_jiayu * 0.05;
    $shoot = $heroadd['range'] * (1 + 0.05 * $tec_shoot_add);
    $blooe_heroadd = ($heroadd['affairs_add'] + $heroadd['affairs_add_on']) + $heroadd['affairs_base'];
    $blood = ($blooe_heroadd / 300 + $goods_blood) + 0.05 * $tec_blood_add;
    $plund = 1 + 0.03 * $tec_plund_add;
    $ret['command'] = $command;
    $ret['act'] = $act;
    $ret['def'] = $def;
    $ret['speed'] = $speed;
    $ret['speed1'] = $speed1;
    $ret['speed2'] = $speed2;
    $ret['blood'] = $blood;
    $ret['plund'] = $plund;
    return $ret;
}
function take_dtactics($uid, $cid, $type, $id, $task = 0){
    if ($type == 0) {
        $troop = sql_fetch_one("select * from sys_troops where `state`=4 and `id`<>'{$id}' and `targetcid`='{$cid}' order by endtime asc limit 1");
        $soldierArray = troop2array($troop['soldiers']);
        $return = take_atactics($uid, $troop['id'], $soldierArray, $type);
    } else {
        if ($uid > 1000) {
            $tactics = sql_fetch_one("select * from sys_city_tactics where `cid`='{$cid}'");
        }
        if (!$tactics) {
            $tactics['deplunder_join'] = '2,4,5,6,7,8,10,12';
            $tactics['deplunder'] = '1,2,1;2,1,2;3,2,3;4,1,4;5,1,5;6,1,6;7,1,7;8,1,8;9,2,9;10,1,10;11,1,11;12,1,12;45,2,45;46,2,46;47,2,47;48,2,48;49,2,49;50,2,50';
            $tactics['depatrol_join'] = '3';
            $tactics['depatrol'] = '3,1,3';
            $tactics['deinvade_join'] = '2,4,5,6,7,8,10,12';
            $tactics['deinvade'] = '1,2,1,1,1;2,1,2,1,2;3,2,3,1,3;4,1,4,1,4;5,1,5,1,5;6,2,6,1,6;7,1,7,1,7;8,1,8,1,8;9,2,9,1,9;10,2,10,1,10;11,2,11,1,11;12,2,12,1,12;45,2,45,2,45;46,2,46,2,46;47,2,47,2,47;48,2,48,2,48;49,2,49,2,49;50,2,50,2,50;15,2,6;16,2,4;17,2,11';
        }
        $tactic = $type == 2 ? $tactics['deplunder'] : $tactics['deinvade'];
        if ($task) {
            $tactic = $tactics['depatrol'];
        }
        if ($uid < 1000) {
            if (!$task) {
                $soldiers = sql_fetch_rows("select s.sid,s.count,c.* from sys_city_soldier s,cfg_soldier c where s.sid=c.sid and s.count>0 and s.cid='{$cid}'");
            } else {
                $soldiers = sql_fetch_rows("select s.sid,s.count,c.* from sys_city_soldier s,cfg_soldier c where s.sid=c.sid and s.sid='3' and s.count>0 and s.cid='{$cid}'");
            }
        } else {
            $sidTemp = $type == 2 ? $tactics['deplunder_join'] : $tactics['deinvade_join'];
            if ($task) {
                $sidTemp = $tactics['depatrol_join'];
            }
            if (empty($sidTemp)) {
                $sidTemp = '0';
            }
            $soldiers = sql_fetch_rows("select s.sid,s.count,c.* from sys_city_soldier s,cfg_soldier c where s.sid=c.sid and s.count>0 and s.cid='{$cid}' and s.sid in ({$sidTemp})");
          
		}
        if ($type == 1) {
            $soldiers1 = sql_fetch_rows("select s.did*10000 as sid,s.count,c.* from sys_city_defence s,cfg_defence c where s.did=c.did and s.count>0 and s.cid='{$cid}'");
            $soldiers = array_merge($soldiers, $soldiers1);
        }
        if ($soldiers) {
            $soldinfo = soldierinfo($soldiers);
            $soldier_tactic = explode(';', $tactic);
            foreach ($soldier_tactic as $tact) {
                $t_s = explode(',', $tact);
                $sin_soldier[$t_s['0']]['action'] = $t_s['1'];
                $sin_soldier[$t_s['0']]['target'] = $t_s['2'];
                $sin_soldier[$t_s['0']]['action2'] = $t_s['3'];
                $sin_soldier[$t_s['0']]['target2'] = $t_s['4'];
            }
            $comma = '';
            foreach ($soldiers as $soldier) {
                $sidType = $soldier['type'];
				$sid = $soldier['sid'];
				if($sid<100) $sidType = sql_fetch_one_cell("select type from cfg_soldier where sid='$sid'");
                $return[$sid]['action'] = $sin_soldier[$sidType]['action'];
                $return[$sid]['target'] = $sin_soldier[$sidType]['target'];
                $return[$sid]['cnt'] = $soldier['count'];
                $return[$sid]['type'] = $sidType;
                $sidTemp .= $comma;
                $sidTemp .= $sid;
                $comma = ',';
                if (!isset($sin_soldier[$sidType]['action'])) {
                    $return[$sid]['action'] = 1;
                    $return[$sid]['target'] = $sidType;
                    $return[$sid]['action2'] = 1;
                    $return[$sid]['target2'] = $sidType;
                } elseif ($sin_soldier[$sidType]['action2']) {
                    $return[$sid]['action2'] = $sin_soldier[$sidType]['action2'];
                    $return[$sid]['target2'] = $sin_soldier[$sidType]['target2'];
                } else {
                    $return[$sid]['action2'] = $sin_soldier[$sidType]['action'];
                    $return[$sid]['target2'] = $sin_soldier[$sidType]['target'];
                }
            }
        } else {
            $return['1'] = 0;
        }
        $return['soldinfo'] = $soldinfo;
    }
    return $return;
}
function take_atactics($uid, $id, $soldierArray, $type){
    if ($uid > 1000) {
        $tactics = sql_fetch_one("select * from sys_troop_tactics where `troopid`='{$id}'");
    }
    if (!$tactics) {
        $tactics['plunder'] = '1,2,1;2,1,2;3,2,3;4,1,4;5,1,5;6,1,6;7,1,7;8,1,8;9,2,9;10,1,10;11,1,11;12,1,12;45,2,45;46,2,46;47,2,47;48,2,48;49,2,49;50,2,50';
        $tactics['invade'] = '1,2,1,1,1;2,1,2,1,2;3,2,3,1,3;4,1,4,1,4;5,1,5,1,5;6,2,6,1,6;7,1,7,1,7;8,1,8,1,8;9,2,9,1,9;10,2,10,1,10;11,2,11,1,11;12,2,12,1,12;45,2,45,2,45;46,2,46,2,46;47,2,47,2,47;48,2,48,2,48;49,2,49,2,49;50,2,50,2,50;15,2,6;16,2,4;17,2,11';
        $tactics['field'] = '1,2,1;2,1,2;3,1,3;4,1,4;5,1,5;6,1,6;7,1,7;8,1,8;9,2,9;10,1,10;11,1,11;12,1,12';
        $tactics['patrol'] = '3,1,3';
    }
    $tactic = $type == 0 ? $tactics['field'] : ($type == 1 ? $tactics['invade'] : $tactics['plunder']);
    $comma = '';
    //$sidType = $GLOBALS['sid']['type'];
    $soldier_tactic = explode(';', $tactic);
    foreach ($soldier_tactic as $tact) {
        $t_s = explode(',', $tact);
        $sin_soldier[$t_s['0']]['action'] = $t_s['1'];
        $sin_soldier[$t_s['0']]['target'] = $t_s['2'];
        $sin_soldier[$t_s['0']]['action2'] = $t_s['3'];
        $sin_soldier[$t_s['0']]['target2'] = $t_s['4'];
    }
    foreach ($soldierArray as $sid => $cnt) {
	    $sidType = sql_fetch_one_cell("select type from cfg_soldier where sid='$sid'");
        $return[$sid]['action'] = $sin_soldier[$sid]['action'];
        $return[$sid]['target'] = $sin_soldier[$sid]['target'];
        $return[$sid]['cnt'] = $cnt;
        //$return[$sid]['type'] = $sidType[$sid];
		$return[$sid]['type'] = $sidType;
        $sidTemp .= $comma;
        $sidTemp .= $sid;
        $comma = ',';
        if (!isset($sin_soldier[$sid]['action'])) {
            $return[$sid]['action'] = 1;
            //$return[$sid]['target'] = $sidType[$sid];
			$return[$sid]['target'] = $sidType;
            $return[$sid]['action2'] = 1;
            //$return[$sid]['target2'] = $sidType[$sid];
			$return[$sid]['target2'] = $sidType;
        } elseif (isset($sin_soldier[$sid]['action2'])) {
            //$return[$sid]['action2'] = $sin_soldier[$sidType[$sid]]['action2'];
            //$return[$sid]['target2'] = $sin_soldier[$sidType[$sid]]['target2'];
			$return[$sid]['action2'] = $sin_soldier[$sidType]['action2'];
            $return[$sid]['target2'] = $sin_soldier[$sidType]['target2'];
        } else {
            //$return[$sid]['action2'] = $sin_soldier[$sidType[$sid]]['action'];
            //$return[$sid]['target2'] = $sin_soldier[$sidType[$sid]]['target'];
			$return[$sid]['action2'] = $sin_soldier[$sidType]['action'];
            $return[$sid]['target2'] = $sin_soldier[$sidType]['target'];
        }
    }
    $return['0'] = $sidTemp;
    return $return;
}
function battleType($field, $task){
    if ($field['type'] > 0) {
        $type = 0;
    } elseif ($task == 4) {
        $type = 1;
    } elseif (($task == 7 || $task == 8) || $task == 9) {
        $type = 4;
    } else {
        $type = 2;
    }
    return $type;
}
function soldierinfo($soldierinfo){
    $ret = array();
    foreach ($soldierinfo as $v) {
        $ret[$v['sid']] = $v;
    }
    return $ret;
}
function getshootadd($cid){
    $tec_shoot_add = sql_fetch_one_cell("select level from sys_city_technic where `cid`='{$cid}' and `tid`=14");
    $tec_shoot_add = empty($tec_shoot_add) ? 0 : $tec_shoot_add;
    return 1 + 0.05 * $tec_shoot_add;
}
function getCrray($soldierArray, $tid = 0){
    $crrayArray = $GLOBALS['soldier']['carry'];
    foreach ($soldierArray as $sid => $cnt) {
        $crray += ($cnt * $crrayArray[$sid]) * (1 + $tid / 10);
    }
    return $crray;
}
function refreshFoodArmyUse($cid){
    $food_army_use = 0;
    $ownerfields = sql_fetch_rows("select wid from mem_world where `ownercid`='{$cid}'");
    if (!empty($ownerfields)) {
        $comma = '';
        foreach ($ownerfields as $mywid) {
            $fieldcids = $comma;
            $fieldcids = wid2cid($mywid['wid']);
            $comma = ',';
        }
        $food_army_use = sql_fetch_one_cell("select sum(fooduse)*2 from sys_troops where targetcid in ({$fieldcids}) and state in (2,3,4,5,6) and uid > 1000");
    }
    $food_army_use += sql_fetch_one_cell(('select sum(c.food_use*s.count) from sys_city_soldier s,cfg_soldier c where s.cid=\'' . $cid) . '\' and s.sid=c.sid');
    $food_army_use *= 2 / 3;
    sql_query("update mem_city_resource set `food_army_use`='{$food_army_use}' where `cid`='{$cid}'");
    return $food_army_use;
}
function throwHeroField($hero){
    $hid = $hero['hid'];
    sql_query("delete from mem_hero_blood where hid='{$hid}'");
    sql_query("delete from sys_hero_armor where hid='{$hid}'");
    sql_query("update sys_user_armor set hid=0 where uid='{$hero['uid']}' and hid='{$hid}'");
    sql_query("update sys_troops set hid=0 where hid='{$hid}'");
    $findtimes = 20;
    while ($findtimes > 0) {
        $findtimes--;
        $newcid = sql_fetch_one_cell('select (floor(t1.wid/10000)*10+floor(((t1.wid%100)/10)))*1000+floor((t1.wid%10000)/100)*10+floor(t1.wid%10) cid from mem_world t1 join (select round(rand()*(250000-1)+1) id) t2 where t1.id=t2.id order by t2.id limit 1');
        $oldhero = sql_fetch_one("select * from sys_city_hero where uid=0 and cid='{$newcid}'");
        if (empty($oldhero)){
            sql_query("update sys_city_hero set cid='{$newcid}',state=4,uid=0 where hid={$hid}");
            break;
        } else {
            continue;
        }
    }
    if ($findtimes == 0) {
        sql_query("delete from sys_city_hero where hid='{$hid}'");
        sql_query("insert into sys_recruit_hero (`name`,`npcid`,`sex`,`face`,`cid`,`level`,`exp`,`affairs_add`,`bravery_add`,`wisdom_add`,`affairs_base`,`bravery_base`,`wisdom_base`,`loyalty`,`gold_need`,`gen_time`) values ('{$hero['name']}','{$hero['npcid']}','{$hero['sex']}','{$hero['face']}','0','{$hero['level']}','{$hero['exp']}','{$hero['affairs_add']}','{$hero['bravery_add']}','{$hero['wisdom_add']}','{$hero['affairs_base']}','{$hero['bravery_base']}','{$hero['wisdom_base']}','60','0',unix_timestamp())");
    }
}
function checkUserMoney($uid){
    $check = sql_fetch_rows("select * from log_money where uid='{$uid}' and count>0 and type<>999 and type<>3");
    $check2 = sql_fetch_one("select sum(l.count) sum,u.money from log_money l,sys_user u where u.uid=l.uid and u.uid='{$uid}'");
    if ($check || ($check2['sum'] && $check2['sum'] != $check2['money'] || !$check2['sum'] && $check2['money'] != 0)) {
        sql_query("insert into test_cheat(`uid`,`money`,`time`) values ('{$uid}','{$check2['money']}',from_unixtime(unix_timestamp())) on duplicate key update `money`='{$check2['money']}',`time`=unix_timestamp()");
        throw new Exception('系统检测到您存在作弊行为，您的账号已被系统记录,请将元宝调回正常再进行游戏，谢谢配合！');
    }
}
function checkUserGift($uid){
    $check = sql_fetch_rows("select * from log_gift where uid='{$uid}' and count>2013 and type<>999 and type<>3 or (count>3000 and type=60)");
    $check2 = sql_fetch_one("select sum(l.count) sum,u.gift from log_gift l,sys_user u where u.uid=l.uid and u.uid='{$uid}'");
    if ($check || ($check2['sum'] && $check2['sum'] != $check2['gift'] || !$check2['sum'] && $check2['gift'] != 0)) {
        sql_query("insert into test_cheat(`uid`,`money`,`time`) values ('{$uid}','{$check2['gift']}',from_unixtime(unix_timestamp())) on duplicate key update `money`='{$check2['gift']}',`time`=unix_timestamp()");
        throw new Exception('系统检测到您存在作弊行为，您的账号已被系统记录，请将礼金调回正常再进行游戏，谢谢配合！');
    }
}
function checkTaskGroupDoing($uid, &$tasklist){
    $completeTask = false;
    $firstSet = true;
    $docnt = 0;
    foreach ($tasklist as &$task) {
        $goals = sql_fetch_rows("select g.*,u.uid from cfg_task_goal g left join sys_user_goal u on u.gid=g.id and u.uid='{$uid}' where g.tid='{$task['id']}'");
        $complete = true;
        foreach ($goals as $goal) {
            if (!checkGoalComplete($uid, $goal)) {
                $complete = false;
                break;
            }
        }
        if ($goal['tid'] >= 60000 && $goal['tid'] <= 60024 || $goal['tid'] >= 60100 && $goal['tid'] <= 60144) {
            if (empty($goal['uid'])) {
                $complete = false;
            } else {
                $complete = true;
            }
        }
        if ($complete) {
            $docnt++;
        }
    }
    return $docnt;
}
function click(){
    
}
function getreporttitle($cityname, $tarname, $task, $state, $me = 1){
    $tasktype = $GLOBALS['report']['type'][$task];
    $statetype = $state == 0 ? '已到达目标' : '已经返回' . $cityname;
    if ($me) {
        return sprintf($GLOBALS['report']['title'], $tarname, $tasktype, $statetype);
    } else {
        return sprintf($GLOBALS['report']['title1'], $tarname, $tasktype);
    }
}

function getreportsoldier($soldierarray){
    $msg = '';
    $name = $GLOBALS['battle']['patrol_report_soldier'];
    foreach ($soldierarray as $sid => $cnt) {
        $sname = $name[$sid];
        $msg .= sprintf($GLOBALS['report']['b_count'], $sname, $cnt);
    }
    return $msg;
}
function getReportTitleType($task, $state){
    $ret = 0;
    switch ($state) {
    case 0:
        switch ($task) {
        case 0:
            $ret = 1;
            break;
        case 1:
            $ret = 3;
            break;
        case 2:
            $ret = 5;
            break;
        case 3:
            $ret = 7;
            break;
        case 4:
            $ret = 9;
            break;
        case 7:
            $ret = 36;
            break;
        case 8:
            $ret = 37;
            break;
        case 9:
            $ret = 38;
            break;
        }
        break;
    case 1:
        switch ($task) {
        case 0:
            $ret = 2;
            break;
        case 1:
            $ret = 4;
            break;
        case 2:
            $ret = 6;
            break;
        case 3:
            $ret = 8;
            break;
        case 4:
            $ret = 10;
            break;
        case 7:
            $ret = 36;
            break;
        case 8:
            $ret = 37;
            break;
        case 9:
            $ret = 38;
            break;
        }
        break;
    case 3:
        switch ($task) {
        case 1:
            $ret = 1;
            break;
        case 2:
            $ret = 12;
            break;
        case 3:
            $ret = 13;
            break;
        case 4:
            $ret = 14;
            break;
        case 9:
            $ret = 39;
            break;
        }
        break;
    }
    return $ret;
}
?>