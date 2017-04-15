<?php
	$GLOBALS['passArray']=array('swfname'=>'ddazzz','swfkey'=>'lrywebfq','jskey'=>'mbelhiwezdmqodtbmjyksmsxtnimncmh');
	require_once("./server/config/key_constants.php");//将密钥文件包含进来		
	function urlsafe_b64encode($str) {
      $data = base64_encode($str);
      $data = str_replace(array('+','/','='),array('','',''),$data);
      return strtolower($data);
 	}
	function getswfname($swfkey){
		global  $key_rand;
		return urlsafe_b64encode(substr($swfkey,1,5).$key_rand);
	}
	if (time()>1249851600){
	$keys=array_values($defaultPassArray);
	$index=(intval((time()+3600*3)/86400)+$key_rand)%count($keys);
	$GLOBALS['passArray']['swfkey']=$keys[$index];
	$GLOBALS['passArray']['swfname']=getswfname($GLOBALS['passArray']['swfkey']);
	$GLOBALS['passArray']['jskey']='mbelhiwezdmqodtbmjyksmsxtnimncmh';
}?>