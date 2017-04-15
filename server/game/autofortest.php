<?php 
require_once("./utils.php");
$passport=$_GET["passport"];
if ($passport=="") exit("need passport");

$cid = sql_fetch_one_cell("select lastcid from sys_user where passport='$passport'");
$uid = sql_fetch_one_cell("select uid from sys_user where passport='$passport'");

sql_query("update sys_user set state=0,nobility=9,officepos=5,money=300000,honour=100000000 where passport='$passport'");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ('$cid',120,6,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,100,5,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,110,5,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,140,5,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,150,5,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,101,5,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,111,5,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,141,5,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,151,5,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,102,5,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,112,5,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,122,5,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,132,5,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,142,5,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,152,5,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,103,5,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,113,5,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,123,5,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,133,5,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,143,5,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,153,7,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,104,8,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,114,9,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,124,10,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,134,11,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,144,12,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,154,13,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,105,14,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,115,15,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,125,16,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,135,17,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,145,18,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,155,19,10 )");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,199,20,10 )");

sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,10,2,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,60,2,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,70,2,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,1,2,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,11,2,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,21,3,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,31,3,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,41,3,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,51,3,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,61,3,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,71,4,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,81,4,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,2,4,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,12,4,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,22,4,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,32,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,42,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,52,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,62,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,72,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,82,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,13,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,23,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,33,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,43,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,53,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,63,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,73,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,24,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,34,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,44,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,54,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,64,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,35,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,45,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,55,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,65,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,46,1,10)");
sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,56,1,10)");


sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'1','10')");
sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'2','10')");
sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'3','10')");
sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'4','10')");
sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'5','10')");
sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'6','10')");
sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'7','10')");
sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'8','10')");
sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'9','10')");
sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'10','10')");
sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'11','10')");
sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'12','10')");
sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'13','10')");
sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'14','10')");
sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'15','10')");
sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'16','10')");
sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'17','10')");
sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'18','10')");
sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'19','10')");
sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'20','10')");

sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'1',10,$cid) on duplicate key update level=10");
sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'2',10,$cid) on duplicate key update level=10");
sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'3',10,$cid) on duplicate key update level=10");
sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'4',10,$cid) on duplicate key update level=10");
sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'5',10,$cid) on duplicate key update level=10");
sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'6',10,$cid) on duplicate key update level=10");
sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'7',10,$cid) on duplicate key update level=10");
sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'8',10,$cid) on duplicate key update level=10");
sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'9',10,$cid) on duplicate key update level=10");
sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'10',10,$cid) on duplicate key update level=10");
sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'11',10,$cid) on duplicate key update level=10");
sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'12',10,$cid) on duplicate key update level=10");
sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'13',10,$cid) on duplicate key update level=10");
sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'14',10,$cid) on duplicate key update level=10");
sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'15',10,$cid) on duplicate key update level=10");
sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'16',10,$cid) on duplicate key update level=10");
sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'17',10,$cid) on duplicate key update level=10");
sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'18',10,$cid) on duplicate key update level=10");
sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'19',10,$cid) on duplicate key update level=10");
sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'20',10,$cid) on duplicate key update level=10");

sql_query("update sys_city_res_add set resource_changing=1 where cid=$cid ");
sql_query("update mem_city_resource set `changing`=1,people=104500,people_max=104500,gold=55000000,gold_max=55000000  where cid=$cid ");

sql_query("update sys_user set money = 1000000 where uid = $uid;");
sql_query("update mem_city_resource set gold=500000000,food=500000000,food_army_use=100000,food_add=2000000,wood=500000000,wood_add=500000,iron=500000000,iron_add=500000,rock=500000000,rock_add=500000,people=80000,people_max=80000 where cid = $cid limit 1;");
sql_query("insert into sys_city_soldier values($cid,1,10000) on duplicate key update count = 10000;");
sql_query("insert into sys_city_soldier values($cid,2,10000) on duplicate key update count = 10000;");
sql_query("insert into sys_city_soldier values($cid,3,10000) on duplicate key update count = 10000;");
sql_query("insert into sys_city_soldier values($cid,4,10000) on duplicate key update count = 10000;");
sql_query("insert into sys_city_soldier values($cid,5,10000) on duplicate key update count = 10000;");
sql_query("insert into sys_city_soldier values($cid,6,10000) on duplicate key update count = 10000;");
sql_query("insert into sys_city_soldier values($cid,7,10000) on duplicate key update count = 10000;");
sql_query("insert into sys_city_soldier values($cid,8,10000) on duplicate key update count = 10000;");
sql_query("insert into sys_city_soldier values($cid,9,10000) on duplicate key update count = 10000;");
sql_query("insert into sys_city_soldier values($cid,10,10000) on duplicate key update count = 10000;");
sql_query("insert into sys_city_soldier values($cid,11,10000) on duplicate key update count = 10000;");
sql_query("insert into sys_city_soldier values($cid,12,10000) on duplicate key update count = 10000;");
sql_query("insert into sys_city_soldier values($cid,45,10000) on duplicate key update count = 10000;");
sql_query("insert into sys_city_soldier values($cid,46,10000) on duplicate key update count = 10000;");
sql_query("insert into sys_city_soldier values($cid,47,10000) on duplicate key update count = 10000;");
sql_query("insert into sys_city_soldier values($cid,48,10000) on duplicate key update count = 10000;");
sql_query("insert into sys_city_soldier values($cid,49,10000) on duplicate key update count = 10000;");
sql_query("insert into sys_city_soldier values($cid,50,10000) on duplicate key update count = 10000;");

sql_query("insert into sys_city_soldier values($cid,11,10000) on duplicate key update count = 10000;");
sql_query("insert into sys_city_soldier values($cid,12,10000) on duplicate key update count = 10000;");

sql_query("REPLACE INTO sys_goods(uid,gid,COUNT) VALUES($uid,30,1000)");
sql_query("REPLACE INTO sys_goods(uid,gid,COUNT) VALUES($uid,31,1000)");
sql_query("REPLACE INTO sys_goods(uid,gid,COUNT) VALUES($uid,32,1000)");
sql_query("REPLACE INTO sys_goods(uid,gid,COUNT) VALUES($uid,33,1000)");
sql_query("REPLACE INTO sys_goods(uid,gid,COUNT) VALUES($uid,34,1000)");
sql_query("REPLACE INTO sys_goods(uid,gid,COUNT) VALUES($uid,35,1000)");
sql_query("REPLACE INTO sys_goods(uid,gid,COUNT) VALUES($uid,36,1000)");
sql_query("REPLACE INTO sys_goods(uid,gid,COUNT) VALUES($uid,37,1000)");
sql_query("REPLACE INTO sys_goods(uid,gid,COUNT) VALUES($uid,38,1000)");
sql_query("REPLACE INTO sys_things(uid,tid,COUNT) VALUES($uid,30000,1000)");
sql_query("insert into sys_user_task(uid,tid,state) values($uid,243,0) on duplicate key update uid = uid;");
sql_query("insert into sys_user_goal(uid,gid) values($uid,363) on duplicate key update uid = uid;");
try{
	require_once './HotelFunc.php';
	regenerateRecruitHero($cid,10);
	$rows=sql_fetch_rows("select id from sys_recruit_hero limit 10");
	foreach ($rows as $row) {
		recruitHero($uid,$cid,array($row["id"]));
	}
}catch(Exception $e){}

echo("auto build succ!");
?>