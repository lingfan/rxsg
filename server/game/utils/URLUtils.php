<?php
abstract class URLUtils {
	
	public static function queryURL($url, $time_out = 10, $noreturn = false) {
		$urlarr = parse_url($url);
		$errno = "";
		$errstr = "";
		$transports = "";
		if($urlarr["scheme"] == "https") {
			$transports = "ssl://";
			$urlarr["port"] = "443";
		} else {
			$transports = "tcp://";
			$urlarr["port"] = ($urlarr["port"] == "" ? 80 : $urlarr["port"]);
		}
		$fp=@fsockopen($transports . $urlarr['host'],$urlarr['port'],$errno,$errstr,$time_out);
		if(!$fp) {
			die("ERROR: $errno - $errstr<br />\n");
		} else {
			$out = "GET ".$urlarr["path"].'?'. $urlarr["query"] . " HTTP/1.1\r\n";
			$out .= "Accept: */*\r\n";
			$out .= "Accept-Language: zh-cn\r\n";
			$out .= "UA-CPU: x86\r\n";
			$out .= "User-Agent: wangye173_rxsg_interface\r\n";
			$out .= "Host: ".$urlarr["host"]."\r\n";
			$out .= "Connection: Close\r\n";

			fwrite($fp, $out);
		
			if ($noreturn) {
				fclose($fp);
			 	return null;
			}
		
			while(!feof($fp)) {
				$info[]=@fgets($fp, 4096);
			}

			fclose($fp);
			return $info;
		}
	}
	
	public static function accessURL($url) {
		if ($url=='') 
			return false;
 		$fp = fopen($url, 'r') or exit('Open url faild!');
 		if ($fp) {
  			while (!feof($fp)) {
   				$file = fgets($fp);
   				echo  $file."\n";
  			}
  			unset($file);
 		}
	}

}
?>