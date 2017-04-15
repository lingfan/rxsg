<?php
define('MANAGE_ROOT_PATH', dirname(__FILE__));
require dirname(MANAGE_ROOT_PATH) . '/vendor/autoload.php';

require_once(MANAGE_ROOT_PATH . "/ipconfig.php");
require_once(MANAGE_ROOT_PATH . '/config.php');
require_once(dirname(MANAGE_ROOT_PATH) . "/server/lib/mysql.php");
require_once(dirname(MANAGE_ROOT_PATH) . "/server/lib/DB.php");


$ret = '';
try{
    if(!in_array($_SERVER["REMOTE_ADDR"], $gm_admin_ips)){
        //throw new Exception('ip_error');
    }
    @array_walk($_GET, 'addslashes');
    @array_walk($_POST, 'addslashes');
    if(empty($_GET['module'])){
        throw new Exception('module_error');
    }
    if(empty($_GET['action'])){
        throw new Exception('action_error');
    }


    $signstr = '';
    ksort($_GET);
    foreach($_GET as $key => $getstr){
        if($key != "sign"){
            $signstr .= $getstr;
        }
    }
    if((!isset($_GET['sign'])) || (md5($signstr . 'b3d1lbn1aInlOSavw') != $_GET['sign'])){
        //throw new Exception('sign_error');
    }

    if($_GET['module'] == 'update'){
        if($_GET['action'] == 'interface'){
            if(isset($_GET['update_sign']) && $_GET['update_sign'] == md5($_GET['module'] . $_GET['action'] . $update_key)){
                $zipfile = file_get_contents("http://127.0.0.1/gm/update/" . $_GET['update_sign'] . "/interface.zip");
                file_put_contents("newfile.zip", $zipfile);
                $ret[] = shell_exec("unzip -o newfile.zip");
                $ret[] = shell_exec("rm -rf newfile.zip");
            }
        }
    }else{
        $action_path = MANAGE_ROOT_PATH . '/' . $_GET['module'] . "/" . $_GET['action'] . ".php";
        if(file_exists($action_path)){
            @extract($_GET);
            @extract($_POST);
            include($action_path);
            //		print_r($ret);
            //		print_r(unserialize(gzdecode(base64_decode(strrev($o)))));
        }else{
            throw new Exception('action_not_exist');
        }
    }
}catch(Exception $e){
    echo strrev(base64_encode(gzencode(serialize($e->getMessage()))));
    exit;
}
echo strrev(base64_encode(gzencode(serialize($ret))));
?>