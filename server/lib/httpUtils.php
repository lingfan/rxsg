<?php
class HttpUtils {
	/**
	 * http post
	 */
	public static function  httpPost($url, $post_string, $connectTimeout = 3600, $readTimeout = 3600) {
		$urlInfo = parse_url ( $url );
		$urlInfo ["path"] = ($urlInfo ["path"] == "" ? "/" : $urlInfo ["path"]);
		$urlInfo ["port"] = (isset ( $urlInfo ["port"] ) == false ? 80 : $urlInfo ["port"]);
		$hostIp = gethostbyname ( $urlInfo ["host"] );
		
		$urlInfo ["request"] = $urlInfo ["path"] . (empty ( $urlInfo ["query"] ) ? "" : "?" . $urlInfo ["query"]) . (empty ( $urlInfo ["fragment"] ) ? "" : "#" . $urlInfo ["fragment"]);
		
		$fsock = fsockopen ( $hostIp, $urlInfo ["port"], $errno, $errstr, $connectTimeout );
		if (false == $fsock) {
			return false;
		}
		/* begin send data */
		$in = "POST " . $urlInfo ["request"] . " HTTP/1.0\r\n";
		$in .= "Accept: */*\r\n";
		$in .= "User-Agent: PHP5 Client 1.1 (non-curl)\r\n";
		$in .= "Host: " . $urlInfo ["host"] . "\r\n";
		$in .= "Content-type: application/x-www-form-urlencoded\r\n";
		$in .= "Content-Length: " . strlen ( $post_string ) . "\r\n";
		$in .= "Connection: Close\r\n\r\n";
		$in .= $post_string . "\r\n\r\n";
		
		stream_set_timeout ( $fsock, $readTimeout );
		if (! fwrite ( $fsock, $in, strlen ( $in ) )) {
			fclose ( $fsock );
			return false;
		}
		unset ( $in );
		
		//process response
		$out = "";
		while ( $buff = fgets ( $fsock, 2048 ) ) {
			$out .= $buff;
		}
		//finish socket
		fclose ( $fsock );
		$pos = strpos ( $out, "\r\n\r\n" );
		$head = substr ( $out, 0, $pos ); //http head
		$status = substr ( $head, 0, strpos ( $head, "\r\n" ) ); //http status line
		$body = substr ( $out, $pos + 4, strlen ( $out ) - ($pos + 4) ); //page body
		if (preg_match ( "/^HTTP\/\d\.\d\s([\d]+)\s.*$/", $status, $matches )) {
			if (intval ( $matches [1] ) / 100 == 2) { //return http get body
				return $body;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}
?>