<?php
    //require_once 'DataCenter.php';
    require_once("common.php");
    $param = $_GET['p'];      
    
//    $param = base64_decode($param,true);
    $param = explode("|",$param);
    if (count($param) < 8) exit("param_error");
    $orderid = $param[0];
    $type = $param[1];
    $payname = addslashes($param[2]);
    $passport = addslashes($param[3]);
    $passtype = addslashes($param[4]);
    $money = intval($param[5]);
    $code = $param[6];
    $sign = $param[7];
    
    $payinfo = sql_fetch_one("select * from pay_key where name='$payname' and ip='$GLOBALS[rawip]'");

    if (empty($payinfo))
    {
    	exit("not_valid_ip");
    }
    $key = $payinfo['key'];
    $err = 0;
    if ($sign != md5($orderid.$type.$payname.$passport.$passtype.$money.$code.$key))
    {                     
        exit("sign_error");
    }
    else
    {
        $user = sql_fetch_one("select * from sys_user where passport='$passport' and passtype='$passtype'");
        if (empty($user))
        {
            exit("user_not_exist");
        }
        if ($money <= 0)
        {
            exit("money_not_valid");
        }
        if (($type != 0)&&($type != 1))
        {
            exit("type_not_valid");
        }
        if (sql_check("select * from pay_log where orderid='$orderid'"))
        {
        	exit("orderid_exist");
        }
        sql_query("insert ignore into pay_log (orderid,type,payname,passport,passtype,money,code,time) values ('$orderid','$type','$payname','$passport','$passtype','$money','$code',unix_timestamp())");
        $result = sql_affectedRows();
        if ($result<1) {
        	exit("orderid_exist");
        }
                
        sql_query("update sys_user set money=money+'$money' where uid='$user[uid]'");
        sql_query("insert into log_money (uid,count,time,type) values ('$user[uid]','$money',unix_timestamp(),$type)");
  
		$now = sql_fetch_one_cell("select unix_timestamp()");
		$today = $now - (($now + 8 * 3600)%86400);
		
	    sql_query("insert into pay_day_money (day,money) values ('$today','$money') on duplicate key update `money`=`money`+'$money'"); 
        
        @include("./paygift.php");

      //  PlayerPay($user[uid],$passport,$orderid,$money);
        exit("pay_succ");
    }
?>
