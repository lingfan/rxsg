<?php
//修复城池
//参数列表：
//passport:通行证
//cid:城池id
//返回
//成功或失败，并说明原因
if (!defined("MANAGE_INTERFACE"))
    exit;

if (!isset($passport)) {
    exit("param_not_exist");
}
if (!isset($cid)) {
    exit("param_not_exist");
}

if ((empty($passport)) && (empty($cids))) {
    $ret = "没有账号或cid";
} else {
    //验证空城
    $city = sql_fetch_one("select c.*,u.passport from sys_city c, sys_user u where u.uid=c.uid and c.cid = $cid");
    if (!empty($city) && $city['passport'] == $passport) {
        $mem_city = sql_fetch_one("select * from mem_city_resource where cid=$cid");
        if (empty($mem_city)) {
            sql_query("insert into sys_building (cid,xy,bid,level) values ('$cid',120,6,1) on duplicate key update xy=xy;
replace into mem_city_resource (`cid`,`people`,`food`,`wood`,`rock`,`iron`,`gold`,`food_max`,`wood_max`,`rock_max`,`iron_max`,`gold_max`,`food_add`,`wood_add`,`rock_add`,`iron_add`,`lastupdate`) values ('411384','50','5000','5000','5000','5000','5000','10000','10000','10000','10000','1000000',100,100,100,100,unix_timestamp());
replace into mem_city_schedule (`cid`,`create_time`,`next_good_event`,`next_bad_event`) values ('$cid',unix_timestamp(),unix_timestamp()-(unix_timestamp()+8*3600)%86400 + 86400 + 86400 * rand(),unix_timestamp()-(unix_timestamp()+8*3600)%86400 + 86400 + 86400 * rand());
update mem_city_resource m set m.`people_max`=(select sum(level*(level+1)*50) from sys_building s where s.`cid`=m.cid and s.bid=5) where m.`cid`='$cid';
update mem_city_resource m set `gold_max`=(select level*(level+1)*500000 from sys_building s where s.`cid`=m.cid and s.bid=6) where m.`cid`='$cid';
update mem_city_resource set `people_stable`=`people_max`*morale*0.01 where `cid`='$cid';
insert into sys_city_res_add (cid,resource_changing) values ('$cid',1) on duplicate key update resource_changing=1;
update mem_city_resource set people=GREATEST(people,people_stable) where cid='$cid';") or die("修复失败");
        $ret = "修复成功";
        }
        else
        {
            $ret = "该城池不需要修复！";        
        }
    } else {
        $ret = "该城池不属于" . $passport . "，不能修复";
    }
}
?>