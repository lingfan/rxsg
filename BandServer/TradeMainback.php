<?php
if(!isset($_SESSION)){
    session_start();
}
require_once("dbinc.php");
require_once("Bandutils.php");
$passport=$_GET['user_name'];
$userinfo=sql_fetch_one("select uid,name,money from sys_user where passport='$passport'");//得到玩家uid,君主名，拥有元宝数量
$mybaofuhu=sql_fetch_one_cell("select count from sys_goods where uid='$userinfo[uid]' and gid=12157");//得到保护符数量
$mysjtz=sql_fetch_one_cell("select count from sys_goods where uid='$userinfo[uid]' and gid=8895");//得到升级图纸数量
$_SESSION['myuid']=$userinfo['uid'];
$_SESSION['mymoney']=$userinfo['money'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head> 
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 <title>玩家交易系统</title>
 <script type="text/javascript">
   function setmyTab(m,n){
     var tli=document.getElementById("leftmenu"+m).getElementsByTagName("li");
	 var mli=document.getElementById("mcont"+m).getElementsByTagName("ul"); 
	 for(i=0;i<tli.length;i++){ 
	     tli[i].className=i==n?"hover":"";
	     mli[i].style.display=i==n?"block":"none"; 
	    }
    
	}
 </script>
 <style type="text/css"> 
  .aa{width:120px;float:left;}
  .aa li{width:100%;height:36px;line-height:25px;background:#00FF33;color:#FF0000;margin:5px 10px;font-size:18px;display:block;text-align:center;text-decoration:none;padding:6px;cursor:pointer;}
  .mydiv1{width:80px;float:left;background:#0022FF;color:#FF0033;margin:5px 10px;font-size:18px;display:block;text-align:center;text-decoration:none;padding:16px;}
  .bb{float:left;}
 </style>   
</head> 
<body>
 <div style="width:1000px;background:#edd3d4;text-align:center;">
  <table>
   <tr>
     <th style="width:14%">
	  <div class="aa">
       <ul id="leftmenu0">
	    <li class="hover" onclick="setmyTab(0,0)">出售装备</li>
        <li onclick="setmyTab(0,1)">购买装备</li>
        <li onclick="setmyTab(0,2)">升级装备</li>
        <li onclick="setmyTab(0,3)">出售名将</li>
        <li onclick="setmyTab(0,4)">购买名将</li>
	   </ul>
      </div>
	 </th>
	 <th style="width:75%">
      <div id="mcont0" class="bb" style="background:#00FFFF;">  
        <ul class="block" style="display:block">
         <table border="1" style="border-collapse:collapse;border-spacing:0px; width:100%;height:21px; border:#000 solid 1px;" align="center"> 
          <tr style="background:#F0F0E4"> 
           <th style="width:32%;background:#00FF33;color:#FF0000;margin:5px 10px;font-size:22px;">请选择你要出售的装备</th> 
           <th style="width:68%;background:#00FFFF;color:#FF0099;margin:5px 10px;font-size:22px;">已购买装备情况一览表</th> 
          </tr> 
          <tr>
	       <th>
  		     <div class="bb" style="width:260px;background:#00FFFF;text-align:left;">  
	          <iframe src="sellarmors.php" id="iframesellaId" name="iframesellaId" width="100%" height="200px"></iframe>
             </div>
		   </th>
		   <th>
             <div class="bb" style="width:548px;background:#00FFFF;text-align:left;">   
	         <iframe src="displayarmors.php" id="iframedisaId" name="iframedisaId" width="100%" height="200px" scrolling="no" ></iframe>
             </div>
		   </th>
		 </tr>
		 </table>
       </ul>
       <ul class="block" style="display: none">
         <table border="1" style="border-collapse:collapse;border-spacing:0px; width:100%;height:21px; border:#000 solid 1px;" align="center"> 
          <tr style="background:#F0F0E4"> 
           <th style="width:32%;background:#00FF33;color:#FF0000;margin:5px 10px;font-size:22px;">请选择你要购买的装备</th> 
		   <th style="width:68%;background:#00FFFF;color:#FF0099;margin:5px 10px;font-size:22px;">装备详细情况一览表</th> 
          </tr> 
          <tr>
	       <th>
  		     <div class="bb" style="width:260px;background:#00FFFF;text-align:left;">  
	          <iframe src="buyarmors.php" id="iframebuyaId" name="iframebuyaId" width="100%" height="200px"></iframe>
             </div>
		   </th>
		   <th>
             <div class="bb" style="width:548px;background:#00FFFF;text-align:left;">   
	         <iframe src="displaybuyarmors.php" id="iframedispbId" name="iframedispbId" width="100%" height="200px" scrolling="no" ></iframe>
             </div>
		   </th>
		 </tr>
		 </table>
       </ul>
       <ul class="block" style="display: none">
       </ul>
       <ul class="block" style="display: none">
       </ul>
       <ul class="block" style="display: none">
       </ul>
       <ul class="block" style="display: none">
       </ul>
      </div>
    </th>
   </tr>
  </table>
 </div>  
</body> 
</html> 
