<?php
    
    require_once("common.php");
    
    
    if (!isset($_GET['p'])) exit("param_error");
    $passport = $_GET['p'];
    $passtype = 'wangye173';
    if (isset($_GET['t'])) $passtype = $_GET['t'];
    $user = sql_fetch_one("select * from sys_user where passport='$passport' and passtype='$passtype'");
    
    if (empty($user))
    {
        exit("user_not_exist");
    }
    $rank = sql_fetch_one_cell("select rank from rank_user where uid='$user[uid]'");
    if (empty($rank))
    {
    	$rank = sql_fetch_one_cell("select count(*) from rank_user")+1;
    }
    $nobility = sql_fetch_one_cell("select name from cfg_nobility where id='$user[nobility]'");
    $officepos = sql_fetch_one_cell("select name from cfg_office_pos where id='$user[officepos]'");
    
    echo $user['name']."|".$rank."|".$user['prestige']."|".$nobility."|".$officepos;
?>
