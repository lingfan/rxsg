<?php

	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($price)){exit("param1_not_exist");}
	if (!isset($totalCount)){exit("param2_not_exist");}
	if (!isset($delete)){exit("param3_not_exist");}
	if (!isset($add)){exit("param4_not_exist");}
	if (!isset($userbuycnt)){exit("param5_not_exist");}
	if (!isset($daybuycnt)){exit("param6_not_exist");}
	if (!isset($sid)){exit("param7_not_exist");}
	if (!isset($campaign_id)){exit("param8_not_exist");}
	if (!isset($enable)){exit("param9_not_exist");}
	if (!isset($onsale)){exit("param10_not_exist");}
	if (!isset($rebate)){exit("param11_not_exist");}
	if (!isset($commend)){exit("param12_not_exist");}
	if (!isset($hot)){exit("param13_not_exist");}
	if (!isset($end_hour)){exit("param14_not_exist");}
	if (!isset($end_day)){exit("param15_not_exist");}
	if (!isset($start_hour)){exit("param16_not_exist");}
	if (!isset($start_day)){exit("param17_not_exist");}
	if (!isset($description)){exit("param18_not_exist");}
	if (!isset($operate_type)){exit("param19_not_exist");}
	$start_time = $start_day.' '.$start_hour;
    $end_time = $end_day.' '.$end_hour;
	
	$oriprice = sql_fetch_one_cell("select oriprice from cfg_shop c,adm_shop_sale s where s.operate_sid=c.id and s.id='$sid'");
        if($price != $oriprice){
            $operate_type = '1';     
        }
    if(!empty($delete)){
            sql_query("delete from adm_shop_sale where id='$sid'");
            $ret['show'] = '删除操作成功！';   
        }else if(!empty($add)){
            sql_query("insert into adm_shop_sale (`operate_sid`,`campaign_id`,`enable`,`operate_type`,`price`,`rebate`,`commend`,`hot`,`totalCount`,`userbuycnt`,`daybuycnt`,`description`,`start_time`,`end_time`,`onsale`) values ('$sid','$campaign_id','$enable','$operate_type','$price','$rebate','$commend','$hot','$totalCount','$userbuycnt','$daybuycnt','$description',UNIX_TIMESTAMP('$start_time'),UNIX_TIMESTAMP('$end_time'),'$onsale')");                           
            $ret['show'] = '添加操作成功！';   
        }else{
            sql_query("update adm_shop_sale set `enable`='$enable',`onsale`='$onsale',`operate_type`='$operate_type',`price`='$price',`rebate`='$rebate',`commend`='$commend',`hot`='$hot',`totalCount`='$totalCount',`userbuycnt`='$userbuycnt',`daybuycnt`='$daybuycnt',`description`='$description',`start_time`=UNIX_TIMESTAMP('$start_time'),`end_time`=UNIX_TIMESTAMP('$end_time') where id='$sid'");                   
            $ret['show'] = '修改操作成功！';
        }
        $ret['campaign_contents'] = sql_fetch_rows("select s.id as id,s.enable as enable,s.onsale as onsale,s.price as price,s.rebate as rebate,s.commend as commend,s.hot as hot,s.operate_type as operate_type,FROM_UNIXTIME(s.start_time) as start_time,s.start_time as start_time2,FROM_UNIXTIME(s.end_time) as end_time,s.end_time as end_time2,s.totalCount as totalCount,s.userbuycnt as userbuycnt,s.daybuycnt as daybuycnt,s.description as description,c.name as name,c.oriprice as oriprice from adm_shop_sale s left join cfg_shop c on (s.operate_sid=c.id) where `campaign_id`='$campaign_id'");
        $ret['goods'] = sql_fetch_rows("select * from cfg_shop order by id asc");

?>