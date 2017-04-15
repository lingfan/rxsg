<?php

require_once("./utils.php");

class ConfigInfo
{
	function getConfigInfo()
	{
		$ret = array(0=>0);
		try {

			$passInfo = sql_fetch_rows("select * from cfg_pass");
			if(empty($passInfo))throw new Exception($GLOBALS['checkConfig']['config_is_empty']);
			$ret[] = $passInfo;
			
		}catch (Exception $e)
		{
			$ret = array(0=>1);
			$ret[] = $e->getMessage();
		}		
		return $ret;
	}
}