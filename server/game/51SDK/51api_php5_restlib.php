<?php
header('Cache-control: no-cache, no-store, must-revalidate');
/*
  +-----------------------------------------------------------------------------+
  | 51.com Platform PHP5 client	                                                |
  +-----------------------------------------------------------------------------+
  | version 1.2.1				                                                    |
  |		add method friends_getAppUsers()                                        |
  |							                                                    |
  |							                                                    |
  | Copyright (c) 2008 51.com, Inc.                                             |
  | All rights reserved.                                                        |
  |	                                                                            |
  | Redistribution and use in source and binary forms, with or without          |
  | modification, are permitted provided that the following conditions          |
  | are met:                                                                    |
  |	                                                                            |
  | 1. Redistributions of source code must retain the above copyright           |
  |    notice, this list of conditions and the following disclaimer.            |
  | 2. Redistributions in binary form must reproduce the above copyright        |
  |    notice, this list of conditions and the following disclaimer in the      |
  |    documentation and/or other materials provided with the distribution.     |
  |	                                                                            |
  | THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR        |
  | IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES   |
  | OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.     |
  | IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,            |
  | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT    |
  | NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,   |
  | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY       |
  | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT         |
  | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF    |
  | THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.           |
  +-----------------------------------------------------------------------------+
  | For help with this library, contact slei@mail.51.com                        |
  +-----------------------------------------------------------------------------+
*/

/**
 * the version of php sdk
 */
define("FIVEONE_OP_PHP_SDK_VERSION", "1.2");

if(!defined("FIVEONE_OP_API_DOMAIN")) {
	define("FIVEONE_OP_API_DOMAIN", "api");
}

class FiveOneRestClient {
	var $app_key;
	var $app_secret;
	var $session_key;
	var $user;
	var $time;
	var $friends_list;
	var $final_encode;		// the encoding print out finally  最终输出的数据的编码格式

	var $request_file;		// 请求的接口文件名称

	/**
	* Create the client.
	* @param string $session_key if you haven't gotten a session key yet, leave
	*                            this as null and then set it later by just
	*                            directly accessing the $session_key member
	*                            variable.
	*/
	function __construct($app_key, $app_secret, $session_key=null, $user=null, $time=null) {
		$this->app_key		= $app_key;
		$this->app_secret	= $app_secret;
		$this->session_key	= $session_key;
		$this->user			= $user;
		$this->time			= $time;
		$this->final_encode	= "utf-8";
		$this->last_call_id = 0;

		$this->request_file	= "restserver.php";
		$this->server_addr  = OpenApp_51::get_fiveone_url(FIVEONE_OP_API_DOMAIN) . "/1.0/" ./* FIVEONE_OP_PHP_SDK_VERSION . "/" .*/ $this->request_file;
	}

	/**
	 * Compatible php4
	 */
	function FiveOneRestClient($app_key, $app_secret, $session_key=null, $user=null, $time=null)
	{
		$this->__construct($app_key, $app_secret, $session_key, $user, $time);
	}

	/**
	* 返回当前用户的好友中已经添加此应用的帐号列表。当前用户取决于session_key参数。

	* Returns the identifiers of the current user's 51 friends who are signed up for the specific calling application.
	* The current user is determined from the session_key parameter. The values returned from this call are not storable.
	*
	* @return array of user
	*/
	function &friends_getAppUsers() {
		return $this->call_method('fiveone.friends.getappusers', array());
	}

	/**
	* 获取好友列表
	* 不传uid参数，获取当前登录用户的好友；
	* 指定uid，则返回指定uid的好友
	* 指定的uid必须是当前登录用户或当前登录用户的好友
	*
	* Returns the friends of the current session user or specified user.
	* @return array of friends
	*/
	function &friends_get($uid=null) {
		if (isset($this->friends_list)) {
		  return $this->friends_list;
		}
		return $this->call_method('fiveone.friends.get', array('uid'=>$uid));
	}

	/**
	* 比较两组用户的关系
	* uids1 和 uids2 两个数组元素的个数必须相同
	* uids1 和 uids2 两个数组的元素必须是当前登录用户或当前登录用户的好友
	*
	* Returns whether or not pairs of users are friends or reverse friends.
	* @param array $uids1: array of ids (id_1, id_2,...) of some length X
	* @param array $uids2: array of ids (id_A, id_B,...) of SAME length X
	* @return array of uid pairs with bool, true if pair are friends, and ture if pari are reverse friends, e.g.
	*   array( 0 => array('uid1' => id_1, 'uid2' => id_A, 'are_friends' => 1, 'are_friends_reverse' => 0),
	*          1 => array('uid1' => id_2, 'uid2' => id_B, 'are_friends' => 0, 'are_friends_reverse' => 1)
	*         ...)
	*/
	function &friends_areFriends($uids1, $uids2)
	{
		return $this->call_method('fiveone.friends.areFriends', array('uids1'=>$uids1, 'uids2'=>$uids2));
	}

	/**
	* 返回指定用户的指定的资料
	* uids 是用户数组
	* fields 是指定的用户资料字段
	*
	* Returns the requested info fields for the requested set of users
	* @param array $uids an array of user ids
	* @param array $fields an array of strings describing the info fields desired
	* @return array of users
	*/
	function &users_getInfo($uids, $fields)
	{
		return $this->call_method('fiveone.users.getInfo', array('uids' => $uids, 'fields' => $fields));
	}

	/**
	* 返回指定用户的指定的个人主页信息资料
	* uids 是用户数组
	* fields 是指定的个人主页用户资料字段
	*
	* Returns the requested info fields for the requested set of users
	* @param array $uids an array of user ids
	* @param array $fields an array of strings describing the info fields desired
	* @return array of homes
	*/
	function &homes_getInfo($uids, $fields)
	{
		return $this->call_method('fiveone.homes.getInfo', array('uids' => $uids, 'fields' => $fields));
	}

	/**
	* 获取用户在个人主页推荐的照片
	* uid 为空，获取当前用户推荐照片
	* uid不为空，获取指定用户的推荐照片
	*
	* Returns the tags on all photos specified.
	* @param string $uid : a uid to query
	* @return pictures user recommended at homepage
	*/
	function &photos_getHome($uid=null) {
		$param = $uid ? array('uid' => strtolower(trim($uid))) : array('uid' => strtolower(trim($this->user)));
		return $this->call_method('fiveone.photos.getHome', $param);
	}

	/**
	* 获取当前登录用户或当前登录用户好友的相册
	* 如果相册隐藏，不返回该相册信息
	* uid 为空，获取当前用户推荐照片
	* uid不为空，获取指定用户的推荐照片
	* aids 暂不支持
	*
	* Returns the albums created by the given user.
	* @param string $uid Optional: the user whose albums you want.
	*   A null value will return the albums of the session user.
	* @param array $aids is not working now.
	* @returns an array of album objects.
	*/
	function &photos_getAlbums($uid=null, $aids=null) {
		$param = $uid ? array('uid' => strtolower(trim($uid)), 'aids' => $aids) : array('uid' => strtolower(trim($this->user)), 'aids' => $aids);
		return $this->call_method('fiveone.photos.getAlbums', array('uid' => $uid, 'aids' => $aids));
	}

	/**
	* 获取当前登录用户或当前登录用户好友的相册
	* 如果相册隐藏，不返回该相册信息
	* uid 为空，获取当前用户推荐照片
	* uid不为空，获取指定用户的推荐照片
	* aid 相册ID
	* pids 照片ID数组
	* aid 和 pids 至少有一个不为空
	*
	* Returns photos according to the filters specified.
	* @param string $uid Optional: the user whose photos you want.
	*   A null value will return the photos of the session user.
	*	If you want session user's photo,set 'null'
	* @param int $aid Optional: Filter by an album, as returned by photos_getAlbums.
	* @param array $pids Optional: Restrict to a list of pids
	* Note that at least one of the (aid, pids) parameters must be specified.
	* @returns an array of photo objects.
	*/
	function &photos_get($uid, $aid, $pids) {
		$curr_user = $uid ? $uid : $this->user;
		return $this->call_method('fiveone.photos.get', array('uid' => $curr_user, 'aid' => $aid, 'pids'=>$pids));
	}

	/**
	* 获取当前登录用户的用户名
	* 返回当前登录用户用户名和用app_secret和username加密的字符串
	*
	* Returns the session user.
	* @return an array of username and a key generated by username and app secret key
	*/
	function &users_getLoggedInUser() {
		$login_user = OpenApp_51::get_user();
		if($login_user) {
			$arr[0]['uid'] = $login_user;
			$arr[0]['token'] = md5($appsecret.$login_user);
		}else {
			$arr[0]['uid'] = "";
			$arr[0]['token'] = "";
		}
		return $arr;
	}

	function games_addFavorites($gid, $sid=0) {
		return $this->call_method_v2('fiveone.favorites.add', array('gid' => $gid, 'sid' => $sid));
	}

	function games_delFavorites($gid, $sid=0) {
		return $this->call_method_v2('fiveone.favorites.del', array('gid' => $gid, 'sid' => $sid));
	}

	function games_setLastplay($gid, $sid=0) {
		return $this->call_method_v2('fiveone.user.setlastplay', array('gid' => $gid, 'sid' => $sid));
	}

	/**
	 * post params to the API at 51.com
	 */
	function create_post_string($method, $params) {

		$namespace = "51_sig";

		$params['user'] = $this->user;
		//$params['user'] = 'nickzhu_7';
		$params['session_key'] = $this->session_key;
		$params['app_key'] = $this->app_key;
		$params['time'] = $this->time;
		$params['method'] = $method;
		$params['version'] = FIVEONE_OP_PHP_SDK_VERSION;

		$params['call_id'] = $this->microtime_float();
		if ($params['call_id'] <= $this->last_call_id) {
		  $params['call_id'] = $this->last_call_id + 0.001;
		}
		$this->last_call_id = $params['call_id'];

		$prefix = $namespace . '_';
		$prefix_len = strlen($prefix);
		$fb_params = array();
		$post_data = "";

		foreach ($params as $name => $val)
                {
                        if (is_array($val))
                        {
                                $val = implode(',', $val);
                        }
			$params[$name] = OpenApp_51::no_magic_quotes($val);
		}
		$sig = OpenApp_51::generate_sig($params, $this->app_secret);
		foreach ($params as $name => $val)
		{
			if($this->final_encode != "utf-8" && $this->final_encode != "utf8")
				$val = iconv($this->final_encode,"utf-8",$val);

			$post_data .= $prefix . $name . "=" . urlencode($val) . "&";
		}
		$post_data .= $namespace . "=" . $sig;

		//error_log($post_data);
		return $post_data;
	}

	/**
	 * Interprets a string of XML into an array
	 */
	function convert_xml_to_result($xml, $method, $params) {

		$sxml = simplexml_load_string($xml);
		$result = self::convert_simplexml_to_array($sxml);

		return $result;
	}


	function post_request($method, $params) {

		$post_string = $this->create_post_string($method, $params);
		$result = httpRequest($this->server_addr, $post_string);
		return $result;
	}

	function post_request_v2($method, $params) {

		$post_string = $this->create_post_string($method, $params);
		$result = httpRequest('http://gameapi.51.com/restserver/request', $post_string);//var_dump($result);
		return $result;
	}

	/**
	 * set the encoding that printing out
	 * 设置返回的数据编码
	 */
	function set_encoding($enc="utf-8")
	{
		$arrEnc = array("utf-8", "gbk", "gb2312");
		if(!in_array(strtolower($enc), $arrEnc)) {
			$enc="utf-8";
		}
		$this->final_encode = $enc;
	}

	/**
	 * Performs a character set conversion on the string str from utf-8 to gbk
	 * @param	string	str		the string want to convert
	 * @param	bool	ignore	if ignore the chars that represented failed,
	 * @return	string	str		represented string
	 */
	function utf2Gbk($str, $ignore=true)
	{
		$this->final_encode = strtolower($this->final_encode);
		if($this->final_encode == "utf-8" || $this->final_encode == "utf8") {
			return $str;
		}
		if($ignore) {
			return iconv("utf-8", "{$this->final_encode}//IGNORE", $str);
		}else {
			return iconv("utf-8", "{$this->final_encode}", $str);
		}
	}

	function convert_simplexml_to_array($sxml) {
		$arr = array();
		if ($sxml) {
			foreach ($sxml as $k => $v) {
				if ($sxml['list']) {
					$arr[] = self::convert_simplexml_to_array($v);
				} else {
					$tmp = self::convert_simplexml_to_array($v);
					if(strtolower($v['enc']) == "base64") {
						$arr[$k] = self::utf2Gbk(base64_decode($tmp));
					}else {
						$arr[$k] = self::utf2Gbk($tmp);
					}
				}
			}
		}
		if (count($arr) > 0) {
			return $arr;
		} else {
			return (string)$sxml;
		}
	}

	/* UTILITY FUNCTIONS */
	function &call_method($method, $params) {

		$params['sdk_from'] = "php";
		$retStr = $this->post_request($method, $params);//var_dump($retStr);
		if($retStr == false) {
			$arr['error_code'] = 2;
			$arr['error_msg'] = "Service temporarily unavailable";
			return $arr;
		}
		$result = $this->convert_xml_to_result($retStr, $method, $params);//var_dump($result);
		if($result === "") {
			$result = array();
		}
		return $result;
	}

	/* UTILITY FUNCTIONS */
	function &call_method_v2($method, $params) {

		$params['sdk_from'] = "php";
		$retStr = $this->post_request_v2($method, $params);//var_dump($retStr);
		if($retStr == false) {
			$arr['error_code'] = 2;
			$arr['error_msg'] = "Service temporarily unavailable";
			return $arr;
		}
		$result = $this->convert_xml_to_result($retStr, $method, $params);//var_dump($result);
		if($result === "") {
			$result = array();
		}
		return $result;
	}

	/**
	 *
	 */
	function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

} // end class


/**
 * http post
 */
function httpRequest($url, $post_string, $connectTimeout = CONNECT_TIMEOUT, $readTimeout = READ_TIMEOUT)
{
	$result = "";
	if (function_exists('curl_init')) {
		$timeout = $connectTimeout + $readTimeout;
		// Use CURL if installed...
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, '51.com API PHP5 Client 1.1 (curl) ' . phpversion());
		$result = curl_exec($ch);
		curl_close($ch);
	} else {
		// Non-CURL based version...
		$result = socketPost($url, $post_string, $connectTimeout = CONNECT_TIMEOUT, $readTimeout = READ_TIMEOUT);
	}
	return $result;
}

/**
 * http post
 */
function socketPost($url, $post_string, $connectTimeout = CONNECT_TIMEOUT, $readTimeout = READ_TIMEOUT){
	$urlInfo = parse_url($url);
	$urlInfo["path"] = ($urlInfo["path"] == "" ? "/" : $urlInfo["path"]);
	$urlInfo["port"] = ($urlInfo["port"] == "" ? 80 : $urlInfo["port"]);
	$hostIp = gethostbyname($urlInfo["host"]);

	$urlInfo["request"] =  $urlInfo["path"]	.
		(empty($urlInfo["query"]) ? "" : "?" . $urlInfo["query"]) .
		(empty($urlInfo["fragment"]) ? "" : "#" . $urlInfo["fragment"]);

	$fsock = fsockopen($hostIp, $urlInfo["port"], $errno, $errstr, $connectTimeout);
	if (false == $fsock) {
		return false;
	}
	/* begin send data */
	$in = "POST " . $urlInfo["request"] . " HTTP/1.0\r\n";
	$in .= "Accept: */*\r\n";
	$in .= "User-Agent: 51.com API PHP5 Client 1.1 (non-curl)\r\n";
	$in .= "Host: " . $urlInfo["host"] . "\r\n";
	$in .= "Content-type: application/x-www-form-urlencoded\r\n";
	$in .= "Content-Length: " . strlen($post_string) . "\r\n";
	$in .= "Connection: Close\r\n\r\n";
	$in .= $post_string . "\r\n\r\n";

	stream_set_timeout($fsock, $readTimeout);
	if (!fwrite($fsock, $in, strlen($in))) {
		fclose($fsock);
		return false;
	}
	unset($in);

	//process response
	$out = "";
	while ($buff = fgets($fsock, 2048)) {
		$out .= $buff;
	}
	//finish socket
	fclose($fsock);
	$pos = strpos($out, "\r\n\r\n");
	$head = substr($out, 0, $pos);		//http head
	$status = substr($head, 0, strpos($head, "\r\n"));		//http status line
	$body = substr($out, $pos + 4, strlen($out) - ($pos + 4));		//page body
	if (preg_match("/^HTTP\/\d\.\d\s([\d]+)\s.*$/", $status, $matches)) {
		if (intval($matches[1]) / 100 == 2) {//return http get body
			return $body;
		} else {
			return false;
		}
	} else {
		return false;
	}
}
?>
