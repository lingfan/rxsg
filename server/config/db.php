<?php
////////////////////////////////////////////////////
//              Database Options               	  //
////////////////////////////////////////////////////

ini_set('include_path',ini_get('include_path').';'. '../lib');

define('db_persistent',1);
define('db_RDBMS', 'mysql');
define('db_Username', 'root');
define('db_Password', '123456');
define('db_Server', '127.0.0.1');
define('db_Port', '3306');
define('db_Database', 'bloodwar');

$GLOBALS['dbcharset'] = 'utf8';

require_once("chathost.php");
define('chat_port','5308');
define('SERVER_ID',1);
define('ENCRYPT_PROTOCOL',true);

define("THE_SERVER_ID","<server_mark>");
define('SERVER_NAME',"<server_name>");
$BATTLE_NET_URL="";
$BATTLE_NET_URL_9001="";
define('BATTLE_NET_ENABLE',false);

//赤壁战场配置

define("CHIBI_NET_URL","<chibi_url>");
define('chibi_chat_host','<chibi_host>');
define('chibi_chat_port','7308');
define('CHIBI_NET_ENABLE',false);
?>
