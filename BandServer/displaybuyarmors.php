<?php
 require_once("dbinc.php");
 $myuids=$_SESSION['myuid'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 <script src="TradeMain.js" type="text/javascript">  
 </script>
 <style type="text/css"> 
   table{border-collapse:collapse;border-spacing:0px; width:100%; border:#000 solid 1px;} 
   table td{border:1px solid #000;height:25px; text-align:center; border-left:0px;} 
   table th{ background:#edd3d4; color:#a10333; border:#000 solid 1px; white-space:nowrap; height:21px; border-top:0px;border-left:0px;} 
  .t_left{width:28%; height:auto; float:left;border-top:1px solid #000;border-left:1px solid #000;} 
  .t_r_content{width:101%; height:158px; background:#fff; overflow:auto;font-size:14px;overflow:scroll;} 
  .cl_freeze{height:140px;overflow:hidden; width:100%;} 
  .t_r{width:100%; height:auto; float:left;border-top:1px solid #000; border-right:#000 solid 1px;font-size:16px;}  
  .t_r table{width:99.5%;} 
  .t_r_title{width:97.7%;}
  .t_r_t{width:100%; overflow:hidden;font-size:14px;}
 </style>  
</head> 
<body>
  <iframe name="dformsubmit" style="display:none;">
  </iframe>
  <form action="TradeFunction.php" method="POST" name="formphpd" id="formphpd" target="dformsubmit">
   <div>
   <div class="t_r"> 
    <div class="t_r_t" id="t_r_t1"> 
     <div class="t_r_title"> 
      <table> 
       <tr> 
        <th width="20%">装备名称</th> 
        <th width="20%">装备品质</th> 
        <th width="20%">强化等级</th> 
        <th width="20%">属性加成</th> 
        <th width="20%">出售价格</th> 
       </tr> 
      </table> 
     </div> 
    </div> 
    <div class="t_r_content" id="t_r_content1" onscroll="aa('t_r_content1','t_r_t1')"> 
      <table id="selltable1">           
	    <?
		$arrmortype=array(1=>"灰装",2=>"白装",3=>"绿装",4=>"蓝装",5=>"紫装",6=>"橙装",7=>"红装");
        $armorinfos = sql_fetch_rows("select a.*,c.* from sys_user_armor_trade a left join cfg_armor c on c.id=a.armorid where a.uid='$myuids' and a.trade=0");	
        if(!empty($armorinfos)) foreach($armorinfos as $armorinfo){
          $suresid=$armorinfo['sid'];		
        ?> 
          <tr>
            <td width="20%" class="bordertop"><? echo $armorinfo['name'];?></td>
	        <td width="20%" class="bordertop"><? echo $arrmortype[$armorinfo['type']];?></td>
	        <td width="20%" class="bordertop"><? echo $armorinfo['strong_level'];?></td>
	        <td width="20%" class="bordertop"><? echo $armorinfo['sid'];?></td>
		    <td width="20%" class="bordertop" style="text-align:right;"><? echo $armorinfo['price'].'元';?>
		      <input type="checkbox" name="cancelsell" id="<?echo $armorinfo['sid'];?>" value="<?echo $armorinfo['sid'];?>" onclick="showmysure('<?echo $suresid;?>','formphpd','iframesellaId','sellarmors.php',event);">
			</td>
           </tr>
		<? 
        } 
        ?> 
      </table>
    </div>
   </div>
  </div> 
 </form> 
</body> 
</html> 