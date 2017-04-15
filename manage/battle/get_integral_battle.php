<?php
if (! defined("MANAGE_INTERFACE"))
    exit();
for ($i=0;$i<=900;$i=$i+100){
	$sql = "select  count(*) as count from sys_user where battle_score>$i and battle_score<=$i+100";
//$sql = "select  count(*) as count,from_serverid as server from sys_user where battle_score>$i and battle_score<=$i+100 group by from_serverid";
//$sql="select count(*) as count,b.server_name as server from sys_servers b left join sys_user a on a.from_serverid = b.from_serverid where a.battle_score>0 and a.battle_score<=0+100 group by b.from_serverid";
$ret[$i+100] = sql_fetch_one_cell($sql,'battlenet');
}
?>