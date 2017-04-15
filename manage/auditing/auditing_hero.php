<?php

	function sendSysMail($touid,$title,$content)
    {
        $title = addslashes($title);
        $content = addslashes($content);

        $mid = sql_insert("insert into sys_mail_sys_content (`content`,`posttime`) values ('$content',unix_timestamp())");
        $sql="insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$touid','$mid','$title','0',unix_timestamp())";
        sql_insert($sql);
        sql_query("insert into sys_alarm (`uid`,`mail`) values ('$touid',1) on duplicate key update `mail`=1");
    }
	if (!defined("MANAGE_INTERFACE")) exit;
	
	if (!isset($request)){exit("param_not_exist");}
	if (!isset($adm_name)){exit("param_not_exist");}
	
	$name = $request['game_name'];
    $title = $request['mesg_title'];
    $content = $request['mesg_content'];
    if (empty($request)||($request['state'] != 0))
    {
   		exit("<strong>无效的申请。[<a href=javascript:history.back()>返回</a>]</strong>");
	}
	
	$exp = sql_fetch_one_cell("select `total_exp` from cfg_hero_level where level='$request[hero_level]'");
	
	if(!empty($title) || !empty($content)){
            if(!empty($request['uid'])){
                sendSysMail($request['uid'],$title,$content);
                //添加log
                $opration_content = '发送给“'.$name.'”一封系统信件';
                sql_query("insert into adm_log (`adm_name`,`opration`,`opration_content`,`oprate_time`) values ('$adm_name','send_mesg','$opration_content',unix_timestamp())");
            }
        }
	$opration_content = '审核了给玩家“'.$name.'”'.$request['hero_level'].' 级的武将 '.$request['hero_name'].'申请';
    sql_query("insert into adm_log (`adm_name`,`opration`,`opration_content`,`oprate_time`) values ('$adm_name','verify_hero','$opration_content',unix_timestamp())");

    
    $ret[] = sql_insert("insert into sys_city_hero(`uid`,`cid`,`exp`,`name`,`level`,`sex`,`face`,`affairs_base`,`bravery_base`,`wisdom_base`,`loyalty`,`affairs_add`,`bravery_add`,`wisdom_add`)
        values ('$request[uid]','$request[cid]','$exp','$request[hero_name]','$request[hero_level]','$request[hero_sex]','$request[hero_face]','$request[affairs_base]','$request[bravery_base]','$request[wisdom_base]','$request[loyalty]','$request[affairs_add]','$request[bravery_add]','$request[wisdom_add]')");
    $ret[] .= sql_insert("insert into mem_hero_blood (hid,`force_max`,`energy_max`) (select hid,100+ceil(level/5)+ceil((bravery_base+bravery_add)/3)+force_max_add_on,100+ceil(level/5)+ceil(wisdom_base+wisdom_add)/3+energy_max_add_on from sys_city_hero where uid=$request[uid]) on duplicate key update `force`=`force`;");
?>