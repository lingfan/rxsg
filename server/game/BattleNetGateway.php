<?php 	
	require_once("interface.php");
	require_once("utils.php");
	require_once("BattleFunc.php");
	require_once("HeroFunc.php");
	require_once("BattleNetServices.php");
	define("BATTLE_NET_KEY","M7XDFCR9WRRGRQ9ETBQ6");
	if (!defined('PATH_SEPARATOR')) {if (substr(PHP_OS, 0, 3) == 'WIN') define('PATH_SEPARATOR', ';'); else define('PATH_SEPARATOR', ':');}	
	ini_set('include_path',ini_get('include_path').PATH_SEPARATOR.realpath("../")); 	
	
	$param=$_POST;
	if(empty($param))$param=$_GET;		
	$from_uid=$param["from_uid"];	
	$sign=$param["sign"];
	$commandFunc=$param["commandFunc"];
	$content=urldecode($param["content"]);
	$content_encoding=$param["content-encoding"];
	//$tt=$from_uid.$commandFunc.$content.BATTLE_NET_KEY;
 
 
	
	$ret=array();
	if($sign!=md5($from_uid.$commandFunc.$content.BATTLE_NET_KEY)) 
		$ret[]=0;
	else{
		$ret[]=1;
	
		try{
			if($content_encoding=="csv"){
				$inputParams=explode("|",$content);
				if(count($inputParams)==1){
					$inputParams=$inputParams[0];
				}
				$ret[]=$commandFunc($from_uid,$inputParams);
			}else{
				$inputParams=json_decode($content,true);
				if(is_array($inputParams)&&count($inputParams)==1){
				     $inputParams=array_shift($inputParams);
				}
				$ret[]=$commandFunc($from_uid,$inputParams); 
			}
		}catch(Exception $e){
			$ret = array(0=>0);
			$ret[] = $e->getMessage();
	    }
	}
    if($content_encoding=="csv"){
    	print implode("|",$ret);
    }else{
    	print json_encode($ret);
    }
	 
?>
