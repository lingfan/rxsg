<?php
set_time_limit(1000);
$starttime = microtime();
require_once ("db_test.php");
function strencode($str, $key)
{
	$length = strlen($str);
	$key_len = strlen($key);
	$ret = '';
	for ($i = 0; $i < $length; $i++) {
		$ret .= $str[$i] ^ $key[$i % $key_len];
	}
	return $ret;
}
$ret = '';
try {
	//FOR RELEASE
	//    if($_SERVER['REMOTE_ADDR']!=data_interface_ip)
	//    {
	//        throw new Exception('ip_error');
	//    }
	//FOR DEVELOP TEST
	if (!in_array($_SERVER["REMOTE_ADDR"], $visit_ip_list)) {
		throw new Exception('ip_error');
	}

	@array_walk($_GET, 'addslashes_html');
	@array_walk($_POST, 'addslashes_html');

	if (empty($_GET['module'])) {
		throw new Exception('module_error: please add "module=params" in the url');
	}
	if (empty($_GET['action'])) {
		throw new Exception('action_error: please add "action=params" in the url');
	}

	$signstr = '';
	//    foreach ($_GET as $key => $getstr) {
	//        if ($key != "sign") {
	//            $signstr .= $getstr;
	//        }
	//    }
	$signstr = $_GET['module'].$_GET['action'];
	if ((!isset($_GET['sign'])) || (md5($signstr . '1+;4um2o;4ArKCb6aqE!3.+O.vC}h&oB^m3lSq7w.LIS') != $_GET['sign'])) {
		throw new Exception('sign_error');
	}
	$action_path = $_GET['module'] . "/" . $_GET['action'] . ".php";
	if (file_exists($action_path)) {
		@extract($_GET);
		@extract($_POST);
		
		include ($action_path);
		
		
	} else {
		throw new Exception('action_not_exist');
	}

}
catch (exception $e) {
	$ret['error'] = $e->getMessage();
	$array2xml = new ArrayUtility;
	$option = array('encoding' => 'utf-8', 'root' => 'data_interface');
	$xml = $array2xml->xmlize($ret, $option);
	echo $xml;
	exit;
}
$endtime = microtime();
$ret['content']['runtime'] = $endtime-$starttime;
$array2xml = new ArrayUtility;
$option = array('encoding' => 'utf-8', 'root' => 'data_interface');
$xml = $array2xml->xmlize($ret, $option);
echo $xml;

?>