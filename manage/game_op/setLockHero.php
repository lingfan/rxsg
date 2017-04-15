<?php
if (! defined( "MANAGE_INTERFACE" ))
	exit();
if (! isset( $npcid ) || empty($npcid)) {
	exit( "param_not_exist" );
}
$oldcid = sql_fetch_one_cell("select cid from sys_city_hero where npcid='$npcid' limit 1");
if(empty($oldcid)) exit();
sql_query( "update sys_city_hero set uid='895',cid='215265',state=0 where npcid='$npcid' limit 1" );
sql_query( "update sys_city set counsellorid=0 where counsellorid='$npcid' and cid='$oldcid' limit 1" );
sql_query( "update sys_city set generalid=0 where generalid='$npcid' and cid='$oldcid' limit 1" );
sql_query( "update sys_city set chiefhid=0 where chiefhid='$npcid' and cid='$oldcid' limit 1" );
sql_query("update sys_user_armor set hid=0 where hid='$npcid'");
sql_query("delete from sys_hero_armor where hid='$npcid'");
$ret = "武将已保留";
?>