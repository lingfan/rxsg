<?php                      
/*	define("WAR_BASE",realpath((dirname(__FILE__))) . "/");              
	require_once(WAR_BASE."../config/db.php");
	require_once(WAR_BASE."../lib/DB.php");
	require_once(WAR_BASE."../lib/mysql.php");
	require_once(WAR_BASE."../lib/database.php"); */
define("BLOODWAR_BASE", realpath(((dirname(__FILE__)))) . "/");                  
if (!defined('PATH_SEPARATOR')) {if (substr(PHP_OS, 0, 3) == 'WIN') define('PATH_SEPARATOR', ';'); else define('PATH_SEPARATOR', ':');}	 
ini_set('include_path',ini_get('include_path').PATH_SEPARATOR. BLOODWAR_BASE. '../lib');
require_once(BLOODWAR_BASE . "../config/db.php");
require_once(BLOODWAR_BASE . "../lib/DB.php");
require_once(BLOODWAR_BASE . "../lib/mysql.php");
require_once(BLOODWAR_BASE . "../lib/database.php");          
?>