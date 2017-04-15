<?php                      
define("BLOODWAR_BASE", realpath(((dirname(__FILE__)))) . "/");
require dirname(BLOODWAR_BASE) . '/vendor/autoload.php';

if (!defined('PATH_SEPARATOR')) {if (substr(PHP_OS, 0, 3) == 'WIN') define('PATH_SEPARATOR', ';'); else define('PATH_SEPARATOR', ':');}	 
ini_set('include_path',ini_get('include_path').PATH_SEPARATOR. BLOODWAR_BASE. '../lib');
require_once(BLOODWAR_BASE . "../server/config/db.php");
require_once(BLOODWAR_BASE . "../server/lib/DB.php");
require_once(BLOODWAR_BASE . "../server/lib/mysql.php");
require_once(BLOODWAR_BASE . "../server/lib/database.php");
?>