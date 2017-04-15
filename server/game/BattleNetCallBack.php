<?php 	
	require_once("interface.php");
	require_once("utils.php");
	$key="M7XDFCR9WRRGRQ9ETBQ6";
	$param=$_POST;
	$hid=$param["hid"];						
	$tme=$param["tme"];	
	$sign=$param["sign"];
	$commandFunc=$param["func"];
	$xx=md5($hid.$tme.$key);
	error_log("$hid:$tme:$key:$sign:$xx");
	
	if ($sign!=md5($hid.$tme.$key)) exit("invalid request!!");
	$commandFunc($param);
	function endPK($param){
		$hid=$param["hid"];
		$exp_add=$param["exp_add"];
		sql_query("update sys_city_hero set exp=exp+$exp_add where hid = $hid");
	}
	echo("succ");
?>
