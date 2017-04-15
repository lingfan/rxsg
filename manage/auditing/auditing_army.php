<?php

	if (!defined("MANAGE_INTERFACE")) exit;
	function sendSysMail($touid,$title,$content)
    {
        $title = addslashes($title);
        $content = addslashes($content);

        $mid = sql_insert("insert into sys_mail_sys_content (`content`,`posttime`) values ('$content',unix_timestamp())");
        $sql="insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$touid','$mid','$title','0',unix_timestamp())";
        sql_insert($sql);
    }

	if (!isset($name)){exit("param_not_exist");}
	if (!isset($request)){exit("param_not_exist");}
	if ($request['army_to']==2){
		$to = '逃兵营';
		sql_query("insert into mem_city_lamster (`count`,`cid`,`sid`) values('$request[count]','$request[cid]','$request[sid]') ON DUPLICATE KEY UPDATE `count`=`count`+'$request[count]'");
	}
	elseif ($request['army_to']==3){
		$to = '伤兵营';
		sql_query("insert into mem_city_wounded (`count`,`cid`,`sid`) values('$request[count]','$request[cid]','$request[sid]') ON DUPLICATE KEY UPDATE `count`=`count`+'$request[count]'");
	}
	else {
		$to = '正常';
		sql_query("insert into sys_city_soldier (`count`,`cid`,`sid`) values('$request[count]','$request[cid]','$request[sid]') ON DUPLICATE KEY UPDATE `count`=`count`+'$request[count]'");
    	sql_query("update sys_city_res_add set resource_changing=1 where cid='$request[cid]'");
	}
    //添加log
    $opration_content = '审核了 '.$request['count'].' 数量的'.$to.' 的 '.$request['name'].'申请';
    sql_query("insert into adm_log (`adm_name`,`opration`,`opration_content`,`oprate_time`) values ('$name','verify_army','$opration_content',unix_timestamp())");

	$uid = $request['uid'];
	if(!empty($request['title'])&&!empty($request['content']))
	{
		sendSysMail($uid,$request['title'],$request['content']);
		$ret = 'mail_success';
	}
	else 
	{
		$ret = 'success';
	}
?>