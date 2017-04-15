<?php
/*
	51.com网站为第三方应用程序提供的SDK开发包中的用户身份认证及加解密类
	V1.0.1	2008-09-27
*/

include_once '51api_php5_restlib.php';
define('OpenApp_51_API_VALIDATION_ERROR', 1);
class OpenApp_51 {
  public $api_client;

  public $app_key;
  public $app_secret;

  public $fb_params;
  public $user;
  public $session_key;
  public $time;

  public function __construct($app_key, $app_secret) 
  {
    $this->app_key    = $app_key;
    $this->app_secret     = $app_secret;

    $this->check_user();
    
    $this->api_client = new FiveOneRestClient($app_key, $app_secret,$this->session_key,$this->user,$this->time);
  }

  public function check_user() {
    $this->fb_params = $this->decode_params($_POST, POST_TIMEOUT, $this->app_secret ,'51_sig');
    if (!$this->fb_params) 
    {
      foreach($_POST as $k => $v)
      {
      	if(strpos($k,'51_sig'))
	     	{
	     		$this->real_login();
	     		exit();
	     	}
      }
      
      $this->fb_params = $this->decode_params($_GET, GET_TIMEOUT, $this->app_secret,'51_sig');
    }
    if ($this->fb_params) 
    {
      $user        = isset($this->fb_params['user'])        ? $this->fb_params['user'] : null;
      $session_key = isset($this->fb_params['session_key']) ? $this->fb_params['session_key'] : null;
      $time     = isset($this->fb_params['time'])     ? $this->fb_params['time'] : null;
      $this->set_user($user, $session_key, $time);
    } 
    else
    { 
	    foreach($_GET as $k => $v)
      {
      	if(strpos($k,'51_sig'))
	     	{
	     		$this->real_login();
	     		exit();
	     	}
      }
      
	    if (!empty($_COOKIE) && $cookies = $this->decode_params($_COOKIE, COOKIE_TIMEOUT, $this->app_secret,$this->app_key)) 
	    {
	      $this->set_user($cookies['user'], $cookies['session_key'],$cookies['time']);
	    }
  	}

    return !empty($this->fb_params);
  }
  
  public static function decode_params($params, $timeout=null, $app_secret, $namespace='51_sig') {
    $prefix = $namespace . '_';
    $prefix_len = strlen($prefix);
    $fb_params = array();
    foreach ($params as $name => $val) {
      if (strpos($name, $prefix) === 0) {
        $fb_params[substr($name, $prefix_len)] = self::no_magic_quotes($val);
      }
    }
    if ($timeout && (!isset($fb_params['time']) || time() - (int)$fb_params['time'] > $timeout)) {
      return array();
    }
    if (!isset($params[$namespace]) || !self::verify_signature($fb_params, $app_secret,$params[$namespace])) {
      return array();
    }
    return $fb_params;
  }
  
  public function redirect($url) 
  {
    if (preg_match('/^https?:\/\/([^\/]*\.)?OpenApp_51\.com(:\d+)?/i', $url)) 
    {
      // make sure OpenApp_51.com url's load in the full frame so that we don't
      // get a frame within a frame.
      //echo "<script type=\"text/javascript\">\ntop.location.href = \"$url\";\n</script>";
      echo "<script type=\"text/javascript\">\nwindow.location.href = \"$url\";\n</script>";
    } 
    else 
    {
      header('Location: ' . $url);
    }
  }

  public function get_user() {
    return $this->user;
  }

  public static function current_url() {
    return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  }

  public function require_login() {
    if ($user = $this->get_user()) {
      return $user;
    }
    /*
    echo "************";
    echo self::current_url();
    echo "<br>";
    echo $this->get_login_url(self::current_url(), $this->in_frame());
    echo "************";
    */
    $this->redirect($this->get_login_url(self::current_url()));
    
  }
  
  public function real_login() {
    $this->redirect($this->get_login_url(self::current_url()));    
  }

  public static function get_OpenApp_51_url($subdomain='apps') {
    return 'http://' . $subdomain . '.51.com';
  }
  
  public function get_add_url($next=null) {
    return self::get_OpenApp_51_url().'/add.php?app_key='.$this->app_key .
      ($next ? '&next=' . urlencode($next) : '');
  }

  public function get_login_url($next) {
    return self::get_OpenApp_51_url().'/login.php?v=1.0&app_key=' . $this->app_key . ($next ? '&next=' . urlencode($next)  : '');
  }

  public static function generate_sig($params_array, $app_secret) {
  	$str = '';

    ksort($params_array);
    foreach ($params_array as $k=>$v) {
      $str .= "$k=$v";
    }
    $str .= $app_secret;
    
    //echo $str."<br>";
    
    return md5($str);
  }

  public function set_user($user, $session_key, $time) 
  {
    //if (!isset($_COOKIE[$this->app_key . '_user']) || $_COOKIE[$this->app_key . '_user'] != $user) 
    //{
      $cookies = array();
      $cookies['user'] = $user;
      $cookies['session_key'] = $session_key;
      $cookies['time'] = $time;
      $sig = self::generate_sig($cookies, $this->app_secret);
      
      //增加下面这条语句，以表明不同网站间COOKIE的信任关系
      header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTR STP IND DEM"');
      
      foreach ($cookies as $name => $val) 
      {
				//setcookie($this->app_key . '_' . $name, $val, (int)$expires);
				setcookie($this->app_key . '_' . $name, $val,0,"/");
				$_COOKIE[$this->app_key . '_' . $name] = $val;
      }
      setcookie($this->app_key, $sig,0,"/");
      $_COOKIE[$this->app_key] = $sig;
    //}
    $this->user = $user;
    $this->api_client->session_key = $session_key;
    $this->session_key=$session_key;
    $this->time = $time;
  }

  /**
   * Tries to undo the badness of magic quotes as best we can
   * @param     string   $val   Should come directly from $_GET, $_POST, etc.
   * @return    string   val without added slashes
   */
  public static function no_magic_quotes($val) {
    if (get_magic_quotes_gpc()) {
      return stripslashes($val);
    } else {
      return $val;
    }
  }

  public static function verify_signature($fb_params, $app_secret,$expected_sig) {
    //echo "<br>".self::generate_sig($fb_params, $app_secret);
    //echo "<br>".$expected_sig;
    return self::generate_sig($fb_params, $app_secret) == $expected_sig;
  }
  
  public static function get_fiveone_url($subdomain='www') {
    return 'http://' . $subdomain . '.51.com';
  }
}

?>
