<?php
	require_once("interface.php");
	require_once("utils.php");

require_once(ROOT_PATH."/lib/curl.php");
	
	define("CHIBI_NET_KEY","M7XDFCR9WRRGRQ9ETBQ6");	
	
	function sendChibiRemoteRequest($uid,$commandFunc,$param=array()){		
		if(defined("CHIBI_NET_ENABLE") && CHIBI_NET_ENABLE){
		
			$sendParam=array();
			$content=json_encode($param);
			$sendParam["commandFunc"]=$commandFunc;
			$sendParam["from_uid"]=$uid;
			$sendParam["from_serverid"]=THE_SERVER_ID;
			$sendParam["sign"]=md5($uid.$commandFunc.$content.CHIBI_NET_KEY);
			$sendParam["content"]=$content;
			$curl=new cURL();
			$result=$curl->post(CHIBI_NET_URL,$sendParam);
			if($result===FALSE){
				throw new Exception($GLOBALS['chibi']['cannot_connect_server']);
			}
			$ret = json_decode($result,true);
			if($ret[0]==0){
				throw new Exception($GLOBALS['chibi']['connection_error']);
			}
			return $ret[1];
		}else {
			throw new Exception("");
		}
	}
?>