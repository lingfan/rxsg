<?php 	
	require_once("interface.php");
	require_once("utils.php");
	
	require_once(ROOT_PATH."/lib/curl.php");
	define("BATTLE_NET_KEY","M7XDFCR9WRRGRQ9ETBQ6");	
	function startPK($uid,$param)
	{
		$hid=intval(array_shift($param));
		if ($hid==0)
			$hid=sql_fetch_one_cell("select hid from sys_city_hero where uid=$uid limit 1");
		$param=sql_fetch_one("select name,sex,face,passport,passtype from sys_user where uid=$uid");
		$hero=sql_fetch_one("select hid,sex,face,name,exp from sys_city_hero where hid=$hid");
		$param["func"]="startPK";
		$param["from_hid"]=$hid;
		$param["hero_sex"]=$hero["sex"];
		$param["hero_face"]=$hero["face"];
		$param["hero_name"]=$hero["name"];
		$ret= sendRequest($uid,$param);		
		$ret[]=$hero;
		return $ret;
	}
	function object2array($object) {
	    if (is_object($object)) {
	        foreach ($object as $key => $value) {
	            $array[$key] = $value;
	        }
	    }
	    else {
	        $array = $object;
	    }
	    return $array;
	}
	 
	
	function sendRequest($from_uid,$param){		
		$tme=time();
		$param["tme"]=$tme;
		$param["from_uid"]=$from_uid;
		$param["serverid"]=THE_SERVER_ID;
		$ip = $GLOBALS['ip'];
		$param["ip"]=$ip;		
		$param["sign"]=md5(THE_SERVER_ID.$from_uid.$tme.BATTLE_NET_KEY);
		$param_str="";
		$i=0;
		foreach($param as $key=>$value){
			if ($i++==0)$param_str=$param_str."?";
			else $param_str=$param_str."&";
			$param_str=$param_str.$key."=".urlencode($value);
		}
		$url=BATTLE_NET_URL.$param_str;
		echo($url."\n");		
		$result=file_get_contents($url);
		$ret = object2array(json_decode($result));		
		return $ret;
	}
	
	function sendRemoteRequest($uid,$commandFunc,$param=array()){		
		if(defined("BATTLE_NET_ENABLE") && BATTLE_NET_ENABLE){
			global $BATTLE_NET_URL;
			if (empty($BATTLE_NET_URL)) {
				$BATTLE_NET_URL = sql_fetch_one_cell("select neturl from cfg_battlenet_server where bid=6001");
			}
		
			$sendParam=array();
			$content=json_encode($param);
			$sendParam["commandFunc"]=$commandFunc;
			$sendParam["from_uid"]=$uid;
			$sendParam["from_serverid"]=THE_SERVER_ID;
			$sendParam["sign"]=md5($uid.$commandFunc.$content.BATTLE_NET_KEY);
			$sendParam["content"]=$content;
			$curl=new cURL();
			$result=$curl->post($BATTLE_NET_URL,$sendParam);
			if($result===FALSE){
				throw new Exception($GLOBALS['battlenet']['cannot_connect_server']);
			}
			$ret = json_decode($result,true);
			if($ret[0]==0){
				throw new Exception($GLOBALS['battlenet']['connection_error']);
			}
			return $ret[1];
		}else {
			throw new Exception("");
		}
	}
	
	function sendRemote9001Request($uid,$commandFunc,$param=array()){		
		if(defined("BATTLE_NET_ENABLE") && BATTLE_NET_ENABLE){
			global $BATTLE_NET_URL_9001;
			if (empty($BATTLE_NET_URL_9001)) {
				$BATTLE_NET_URL_9001 = sql_fetch_one_cell("select neturl from cfg_battlenet_server where bid=9001");
			}
		
			$sendParam=array();
			$content=json_encode($param);
			$sendParam["commandFunc"]=$commandFunc;
			$sendParam["from_uid"]=$uid;
			$sendParam["from_serverid"]=THE_SERVER_ID;
			$sendParam["sign"]=md5($uid.$commandFunc.$content.BATTLE_NET_KEY);
			$sendParam["content"]=$content;
			$curl=new cURL();
			$result=$curl->post($BATTLE_NET_URL_9001,$sendParam);
			if($result===FALSE){
				throw new Exception($GLOBALS['battlenet']['cannot_connect_server']);
			}
			$ret = json_decode($result,true);
			if($ret[0]==0){
				throw new Exception($GLOBALS['battlenet']['connection_error']);
			}
			return $ret[1];
		}else {
			throw new Exception("");
		}
	}
	 
	
	
?>