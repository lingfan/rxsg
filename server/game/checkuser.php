<?php
    
    require_once("common.php");
    
    
    if (!isset($_GET['p'])) exit("param_error");
    
    $passport = addslashes($_GET['p']);
    $passtype = isset($_GET['passtype'])?$_GET['passtype']:'wangye173';
    $user = sql_fetch_one("select * from sys_user where passport='$passport' and passtype='$passtype'");
    
    if (empty($user))
    {
        exit("0");
    }
    else
    {
    	echo "1";
    }
?>
