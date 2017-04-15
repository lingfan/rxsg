<?php
/**
 * Add a few 4.3.0 functions to old versions of PHP
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage io
 * @version $Id$
 */
 
if (!function_exists("ob_get_clean")) {
   function ob_get_clean() {
	   $ob_contents = ob_get_contents();
	   ob_end_clean();
	   return $ob_contents;
   }
}



if(!function_exists("file_put_contents")) {
	if (!defined('FILE_APPEND')) {
		define('FILE_APPEND', 8);
	}
	function file_put_contents($file, $string, $modifiers = NULL) {
		$mode = $modifiers == FILE_APPEND ? 'a' : 'w';
		$f=fopen($file, $mode);
		$result = fwrite($f, $string);
		fclose($f);
		return $result;
	}
}

function patched_array_search($needle, $haystack, $strict = FALSE) //We only need strict actually
{
	foreach($haystack as $key => $val) {
	   if ($needle === $val) {
	       return($key);
	   }
	}
	return FALSE;
}

function microtime_float()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

if(!function_exists('is_a'))
{
	//We only use is_a as a replacement for PHP5-related stuff, so we always return false
	//anyways
	function is_a($obj, $d)
	{
		return false;
	}
}
?>