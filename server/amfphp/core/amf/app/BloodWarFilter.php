<?php
//用来过滤不正常请求

function escape_single_quote(&$args) {
	foreach($args as &$element){
	    if(is_string($element)){
	        $element=addslashes($element);
	    }else if(is_array($element)){
	    	escape_single_quote($element);
	    }
	}
}
function doBloodFilter($className,$methodName,&$args) {
	escape_single_quote($args);
	return true;
}
/*
$param=array();
$param[]="hetao";
$param[]="1' or '1'='1";
doBloodFilter("","",$param);
print_r($param);
*/
?>