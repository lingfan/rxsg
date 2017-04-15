<?php
 include "dbinc.php";

 $activedArmors = sql_fetch_rows("select sid,embed_holes from sys_user_armor where armorid in (52001,12009,12010) and embed_holes<>''");

 for($i=0;$i<count($activedArmors);$i++)
 { 	 
 	$sid = $activedArmors[$i]["sid"];
 	$embedHole = $activedArmors[$i]["embed_holes"];
 	$embedArr = explode(",",$embedHole);
 	
 	if(intval($embedArr[4])==3)
 	{
 		$embedArr[4]=4;
 	}
 	$newStr = implode(",",$embedArr);
 	
 	sql_query("update sys_user_armor set embed_holes='$newStr' where sid='$sid'");
 }
	echo "succ";
?>