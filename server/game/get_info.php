<?php
require_once("common.php");

$key_local = 'jm4c*x5nv#6su10y';
$action = !empty($_GET['action'])?$_GET['action']:'';

if($action == "get_webid")          
{
    if(empty($_GET['name']) || empty($_GET['key'])){
        exit('0'); 
    }
    $name = $_GET['name'];
    $key = $_GET['key'];
    if(strtoupper($key) == strtoupper(md5($name.$key_local))){
        $domainid = sql_fetch_one_cell("select `domainid` from sys_user where `passport`='$name'");
        if(empty($domainid)){
            exit('0');
        }else{
            echo $domainid;
            exit();
        }        
    }else{
		exit('0');
    }
}else{
    exit('0'); 
}
?>
