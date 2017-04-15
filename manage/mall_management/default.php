<?php
	//商品列表
	//参数列表：
	//$inPath
	//返回$ret['good_mdf']
	//
	
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($inPath)){exit("param_not_exist");}
	$ret['good_mdf'] = array();
    if(!empty($inPath) && isset($inPath[1]) && $inPath[1] == 1){
	    $id = $inPath[2];
	    $ret['good_mdf'] = sql_fetch_one("select * from cfg_shop where id='$id'");   
    }
    elseif(!empty($inPath) && isset($inPath[1]) && $inPath[1] == 2){
    	if (isset($inPath[2])&&isset($inPath[3])){
	   	    $id = $inPath[2];
	   	    $contents=$inPath[3];
	    	
		    
		    $ret['good_mdf'] = sql_query("update cfg_shop set name='$contents[name]',gid='$contents[gid]', 		    
		    pack='$contents[pack]',price='$contents[price]',oriprice='$contents[oriprice]',onsale='$contents[onsale]', 
		    totalCount='$contents[totalCount]',userbuycnt='$contents[userbuycnt]',daybuycnt='$contents[daybuycnt]', 		    
		    position='$contents[position]',`group`='$contents[group]',rebate='$contents[rebate]',commend='$contents[commend]', 		    
		   	hot='$contents[hot]',starttime='$contents[starttime]',endtime='$contents[endtime]',image='$contents[image]', 
		    description='$contents[description]' where id='$id'"); 
		    
		    
    	}
    }
    elseif(!empty($inPath) && isset($inPath[1]) && $inPath[1] == 3){
    	if (isset($inPath[3])){
    		foreach ($inPath[3] as $contents){
    			$ret['good_mdf'] = sql_query("update cfg_shop set name='$contents[name]',gid='$contents[gid]', 
			    pack='$contents[pack]',price='$contents[price]',oriprice='$contents[oriprice]',onsale='$contents[onsale]', 
			    totalCount='$contents[totalCount]',userbuycnt='$contents[userbuycnt]',daybuycnt='$contents[daybuycnt]', 
			    position='$contents[position]',`group`='$contents[group]',rebate='$contents[rebate]',commend='$contents[commend]', 
			    hot='$contents[hot]',starttime='$contents[starttime]',endtime='$contents[endtime]',image='$contents[image]', 
			    description='$contents[description]' where id='$contents[id]'"); 
    		}
    	}
    }
    $ret['shop_list'] = sql_fetch_rows("select * from cfg_shop where onsale='1' order by id asc"); 

?>