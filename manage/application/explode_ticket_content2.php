<?php

	if (!defined("MANAGE_INTERFACE")) exit;
	function explode_ticket_content($content){
        $strs = explode(',',$content);
        $count_type = $strs[0];
        $contents = ''; 
        for($i=0;$i<$count_type;$i++){
            $goods_type = $strs[3*$i+1];
            $gid = $strs[3*$i+2];
            $good_name = ''; 
            if($goods_type==0){ 
                if($gid=='0'){
                    $good_name = '元宝';        
                }else{
                    $good_name = sql_fetch_one_cell("select `name` from cfg_goods where `gid`='$gid'");
                }     
            }else if($goods_type==1){ 
                $good_name = sql_fetch_one_cell("select `name` from cfg_armor where `id`='$gid'");                 
            }else if($goods_type==2){ 
                $good_name = sql_fetch_one_cell("select `name` from cfg_things where `tid`='$gid'");                     
            }
            $contents .= $goods_type.'-'.$good_name.'-'.$strs[3*$i+3].";&#10;"; 
        }
        return $contents;           
    }

	if (!isset($types_list)){exit("param_not_exist");}
	foreach($types_list as &$list)
	{
		$list['content'] = explode_ticket_content($list['content']);
	}
	$ret = $types_list;

	           

?>