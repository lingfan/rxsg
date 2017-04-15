<?php
    
    require_once("./utils.php");
    
    $param = $_GET['p'];      
    
//    $param = base64_decode($param,true);
    $param = explode("|",$param);
    if (count($param) < 8) exit("param_error");
    $orderid = $param[0];
    $type = $param[1];
    $payname = addslashes($param[2]);
    $passport = addslashes($param[3]);
    $passtype = addslashes($param[4]);
    $itemcode = $param[5];
    $code = $param[6];
    $sign = $param[7];
    
    $payinfo = sql_fetch_one("select * from pay_key where name='$payname' and ip='$GLOBALS[rawip]'");

    if (empty($payinfo))
    {
    	exit("not_valid_ip");
    }
    $key = $payinfo['key'];
    $err = 0;
    if ($sign != md5($orderid.$type.$payname.$passport.$passtype.$itemcode.$code.$key))
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
        if ($itemcode <= 0)
        {
            exit("itemcode_not_valid");
        }
        if (($type != 0)&&($type != 1))
        {
            exit("type_not_valid");
        }
        if (sql_check("select * from gold_card_log where orderid='$orderid'"))
        {
        	exit("orderid_exist");
        }
		$now = sql_fetch_one_cell("select unix_timestamp()");
		//´¦ÀíÄÚÈİ£º
		$array_count = explode(";",$itemcode);
		$add_count = $array_count[0];
		$add_content = $array_count[1];
		$array_content = explode("*",$add_content);
		//print_r $array_count.'|'.$add_count.'|'.$add_content.'|'.$array_content;
		if(count($array_content)==$add_count){
			foreach($array_content as $content_cehck)
			{
				$str_content = explode(",",$content_cehck);
				$content_type = $str_content[0];
				$id = $str_content[1];
				$count = $str_content[2];
				if(!($count>0 && $count<=10000)){
					exit("wrong_count");
				}
				if($content_type=='1')
				{
					$is_goods = sql_fetch_one("select * from cfg_goods where gid='$id'");
					if(empty($is_goods)){
						exit("goods_not_exist");
					}
				}
				else if($content_type=='2')
				{
					$is_armor = sql_fetch_one("select * from cfg_armor where id='$id'");
					if(empty($is_armor)){
						exit("armor_not_exist");
					}
				}
				else if($content_type=='3')
				{
					$is_thing = sql_fetch_one("select * from cfg_things where tid='$id'");
					if(empty($is_thing)){
						exit("thing_not_exist");
					}
				}
			}
			foreach($array_content as $content)
			{
				$str_content = explode(",",$content);
				$content_type = $str_content[0];
				$id = $str_content[1];
				$count = $str_content[2];
				//if(!empty($content_type) && !empty($id) && !empty($count))
				{
					if($content_type=='1')
					{
						if($id==0)
						{
							$money=$count;
							$type=3;
							sql_query("update sys_user set money=money+'$money' where uid='$user[uid]'");
					        sql_query("insert into log_money (uid,count,time,type) values ('$user[uid]','$money',unix_timestamp(),$type)");
						}
						else
						{
							sql_query("insert into sys_goods (uid,gid,count) values ('$user[uid]','$id',$count) ON DUPLICATE KEY UPDATE count=count+$count");
							sql_query("insert into log_goods (uid,gid,count,time,type) values ('$user[uid]','$id',$count,unix_timestamp(),8)");
						}
					}
					else if($content_type=='2')
					{
						$hp_max = sql_fetch_one_cell("select `ori_hp_max` from cfg_armor where id='$id'");
						for($i=0;$i<$count;$i++){
							sql_query("insert into sys_user_armor (uid,armorid,hp,hp_max,hid) values ('$user[uid]','$id',$hp_max*10,$hp_max,0)");
						}
						sql_query("insert into log_armor (uid,armorid,count,time,type) values ('$user[uid]','$id',$count,unix_timestamp(),8)");
					}
					else if($content_type=='3')
					{
						
						sql_query("insert into sys_things (uid,tid,count) values ('$user[uid]','$id',$count) ON DUPLICATE KEY UPDATE count=count+$count");
						sql_query("insert into log_things (uid,tid,count,time,type) values ('$user[uid]','$id',$count,unix_timestamp(),8)");
					}
					else if($content_type=='4')
					{
						$money=$count;
						$type=3;
						sql_query("update sys_user set gift=gift+'$money' where uid='$user[uid]'");
				        sql_query("insert into log_gift (uid,count,time,type) values ('$user[uid]','$money',unix_timestamp(),$type)");
					}
				}
				sql_query("insert into gold_card_log (orderid,type,payname,passport,passtype,itemcode,code,time) values ('$orderid','$type','$payname','$passport','$passtype','$itemcode','$code',unix_timestamp())");
			}
			exit("pay_succ");
		}else{
			exit("wrong_type_count");
		}
    }
?>
