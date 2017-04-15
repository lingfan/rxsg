<?php

//This file is intentionally left blank so that you can add your own global settings
//and includes which you may need inside your services. This is generally considered bad
//practice, but it may be the only reasonable choice if you want to integrate with
//frameworks that expect to be included as globals, for example TextPattern or WordPress
define('ROOT_PATH',dirname(dirname(__FILE__)));

require dirname(ROOT_PATH).'/vendor/autoload.php';

//Set start time before loading framework
list($usec, $sec) = explode(" ", microtime());
$amfphp['startTime'] = ((float)$usec + (float)$sec);

$servicesPath = "../game/";
$voPath       = "../game/vo/";

if(!defined('PATH_SEPARATOR')){
    if(substr(PHP_OS, 0, 3) == 'WIN') define('PATH_SEPARATOR', ';');else define('PATH_SEPARATOR', ':');
}
//设置根目录绝对路径到include_path,简化path的使用
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . realpath("../"));

//As an example of what you might want to do here, consider:

/*
if(!PRODUCTION_SERVER)
{
    define("DB_HOST", "localhost");
    define("DB_USER", "root");
    define("DB_PASS", "");
    define("DB_NAME", "amfphp");
}
*/

/**
 * @param Exception $exception
 */
function exception_handler($exception){
    error_log($exception->getMessage() . ' file:' . $exception->getFile() . ' line:' . $exception->getLine() . "\n", 3, '/tmp/rxsg_exception');
    return true;
}

function error_handler($errno, $errstr, $errfile, $errline){
    error_log($errstr . ' file:' . $errfile . ' line:' . $errline . "\n", 3, '/tmp/rxsg_error');
    return true;
}

set_exception_handler('exception_handler');
set_error_handler('error_handler');
?>