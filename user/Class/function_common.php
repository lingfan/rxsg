<?php
/*=============作者:An QQ:233355455===============
  =============时间:2010-04-11 21:00==============
  =============功能:蜀门注册程序通用方法=======================
  =============注:请使用者尊重作者,请勿删除此版权,此注册程序仿官方完美注册,默认赠送钻石:1W=============

  =======/dy天地英雄(200909183) 增加注册权限 ===== bbs.game138.net ===============
*/
//防止跨站点提交
function illegalsubmit(){
   $arr=parse_url($_SERVER['HTTP_REFERER']);
   if(!isset($arr['port']))
   {
      $port=80;
   }else{
      $port=$arr['port'];
   }
   if($arr['host']!=$_SERVER['SERVER_NAME'] || $port!=$_SERVER['SERVER_PORT'])
   {
      	return false;
   }else{
		return true;   		
   }
}
 //检测魔法应用是否开启,没开启则Addslashes加反斜线;
function Addslashess($arr){
   if(get_magic_quotes_gpc()){
        return $arr; 
   }else{
		if(gettype($arr)=="array"){
	         foreach($arr as $k=>$v)
		     {
				if(gettype($v)=="array")
				{
					foreach($v as $kk=>$vv)
					{
						$arr[$k][$kk]=addslashes($vv);
					}
				}else{
					$arr[$k]=addslashes($v);
				}
		     }
		     return $arr; 
	    }else{
		   return addslashes($arr);			   
	    }
	}	
}	   
?>