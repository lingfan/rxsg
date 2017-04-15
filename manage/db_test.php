<?php
require_once("config_test.php");
mysql_connect(db_host, db_user,db_password) or die("Could not connect: " . mysql_error());
$currentdb = "";
function sql_selectdb($dbname)
{
	global $currentdb;
	if ($currentdb != $dbname)
	{
		$currentdb = $dbname;
		mysql_select_db($dbname);
		mysql_query("SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary");
	}
}
function sql_insert($sql,$dbname="bloodwar")
{
	sql_selectdb($dbname);
	mysql_query($sql);
	return mysql_insert_id();
}
function sql_query($sql,$dbname="bloodwar")
{
	sql_selectdb($dbname);
	mysql_query($sql);
}
function sql_fetch_one($sql,$dbname="bloodwar")
{
	sql_selectdb($dbname);
	$r = mysql_query($sql);
	if ((!empty($r))&&($row = mysql_fetch_array($r,MYSQL_ASSOC))) {
		return $row;
	}
	else
	{
		return mysql_error();
	}
	return 0;
}

function sql_fetch_one_cell($sql,$dbname="bloodwar")
{
	sql_selectdb($dbname);
	$r = mysql_query($sql);
	if ((!empty($r))&&($row = mysql_fetch_array($r,MYSQL_NUM))) {
		return $row[0];
	}
	else
	{
		return mysql_error();
	}
	return 0;
}
function sql_fetch_rows($sql,$dbname="bloodwar")
{
	sql_selectdb($dbname);
	$r = mysql_query($sql);

	$ret = array();
	if (!empty($r))
	{
		while($row = mysql_fetch_array($r,MYSQL_ASSOC)) {
			$ret[] = $row;
		}
	}
	else
	{
		return mysql_error();
	}
	return $ret;
}


function gzdecode($data) { 
	$len = strlen($data); 
	if ($len < 18 || strcmp(substr($data,0,2),"\x1f\x8b")) { 
		return null;  // Not GZIP format (See RFC 1952) 
	}
	$method = ord(substr($data,2,1));  // Compression method 
	$flags  = ord(substr($data,3,1));  // Flags 
	if ($flags & 31 != $flags) { 
		// Reserved bits are set -- NOT ALLOWED by RFC 1952 
		return null; 
	} 
	// NOTE: $mtime may be negative (PHP integer limitations) 
	$mtime = unpack("V", substr($data,4,4)); 
	$mtime = $mtime[1]; 
	$xfl	= substr($data,8,1); 
	$os		= substr($data,8,1); 
	$headerlen = 10; 
	$extralen	= 0; 
	$extra		= ""; 
	if ($flags & 4) { 
	 // 2-byte length prefixed EXTRA data in header 
	 if ($len - $headerlen - 2 < 8) { 
		 return false;		// Invalid format 
	 } 
	 $extralen = unpack("v",substr($data,8,2)); 
	 $extralen = $extralen[1]; 
	 if ($len - $headerlen - 2 - $extralen < 8) { 
		 return false;		// Invalid format 
	 } 
	 $extra = substr($data,10,$extralen); 
	 $headerlen += 2 + $extralen; 
	} 

	$filenamelen = 0; 
	$filename = ""; 
	if ($flags & 8) { 
	 // C-style string file NAME data in header 
	 if ($len - $headerlen - 1 < 8) { 
		 return false;		// Invalid format 
	 } 
	 $filenamelen = strpos(substr($data,8+$extralen),chr(0)); 
	 if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) { 
		 return false;		// Invalid format 
	 } 
	 $filename = substr($data,$headerlen,$filenamelen); 
	 $headerlen += $filenamelen + 1; 
	} 

	$commentlen = 0; 
	$comment = ""; 
	if ($flags & 16) { 
	 // C-style string COMMENT data in header 
	 if ($len - $headerlen - 1 < 8) { 
		 return false;		// Invalid format 
	 } 
	 $commentlen = strpos(substr($data,8+$extralen+$filenamelen),chr(0)); 
	 if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) { 
		 return false;		// Invalid header format 
	 } 
	 $comment = substr($data,$headerlen,$commentlen); 
	 $headerlen += $commentlen + 1; 
	} 

	$headercrc = ""; 
	if ($flags & 1) { 
	 // 2-bytes (lowest order) of CRC32 on header present 
	 if ($len - $headerlen - 2 < 8) { 
		 return false;		// Invalid format 
	 } 
	 $calccrc = crc32(substr($data,0,$headerlen)) & 0xffff; 
	 $headercrc = unpack("v", substr($data,$headerlen,2)); 
	 $headercrc = $headercrc[1]; 
	 if ($headercrc != $calccrc) { 
		 return false;		// Bad header CRC 
	 } 
	 $headerlen += 2; 
	} 

	// GZIP FOOTER - These be negative due to PHP's limitations 
	$datacrc = unpack("V",substr($data,-8,4)); 
	$datacrc = $datacrc[1]; 
	$isize = unpack("V",substr($data,-4)); 
	$isize = $isize[1]; 

	// Perform the decompression: 
	$bodylen = $len-$headerlen-8; 
	if ($bodylen < 1) { 
	 // This should never happen - IMPLEMENTATION BUG! 
	 return null; 
	} 
	$body = substr($data,$headerlen,$bodylen); 
	$data = ""; 
	if ($bodylen > 0) { 
	 switch ($method) { 
		 case 8: 
			 // Currently the only supported compression method: 
			 $data = gzinflate($body); 
			 break; 
		 default: 
			 // Unknown compression method 
			 return false; 
	 } 
	} else { 
	 // I'm not sure if zero-byte body content is allowed. 
	 // Allow it for now...	Do nothing... 
	} 

	// Verifiy decompressed size and CRC32: 
	// NOTE: This may fail with large data sizes depending on how 
	//			PHP's integer limitations affect strlen() since $isize 
	//			may be negative for large sizes. 
	if ($isize != strlen($data) || crc32($data) != $datacrc) { 
	 // Bad format!	Length or CRC doesn't match! 
	 return false; 
	} 
	return $data; 
}
class ArrayUtility
{
    /**
     * Convert array to xml tree
     *
     * @param array $array
     * @param array $options
     * @return string
     * @example xmlize()
     */
    public function xmlize($array, $options)
    {
        $encoding = isset($options['encoding']) ? $options['encoding'] : 'utf-8';
        $root = isset($options['root']) ? $options['root'] : 'response';
        $xml = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>\n<$root>\n";
        $xml .= self::_xmlize($array);
        $xml .= "</$root>";
        return $xml;
    }
    private function _xmlize($array)
    {
        $string = '';
        foreach ($array as $key => $value) {
            $stag = is_numeric($key) ? '<item id = "' . $key . '">' : '<' . $key . '>';
            $etag = is_numeric($key) ? '</item>' . "\n" : '</' . $key . '>' . "\n";
            //            if(empty($value))
            //            {
            //                $string .= "<$key>0</$key>";echo $string;
            //            }
            //            else
            {
                $string .= $stag . (is_array($value) ? "\n" . self::_xmlize($value) : $value) .
                    $etag;
            }

        }
        return $string;
    }

}
function addslashes_html(&$value, &$key)
{
    if(is_string($value))
    {
        $value = addslashes(htmlspecialchars_decode($value));
    }
    if(is_string($key))
    {
        $key = addslashes(htmlspecialchars_decode($key));
    }
}
?>