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
  <iframe name="sformsubmit1" style="display:none;">
  </iframe>
  <form action="TradeFunction.php" method="POST" name="formphpdh" id="formphpdh" target="sformsubmit1">
   <div>
   <div class="t_r"> 
    <div class="t_r_t" id="t_r_t2"> 
     <div class="t_r_title"> 
      <table> 
       <tr> 
        <th width="12.5%">名将姓名</th> 
               <th width="12.5%">名将性别</th> 
               <th width="12.5%">名将等级</th> 
               <th width="12.5%">名将统率</th> 
               <th width="12.5%">名将内政</th> 
			   <th width="12.5%">名将勇武</th> 
               <th width="12.5%">名将智谋</th>
			   <th width="12.5%">出售价格</th> 
       </tr> 
      </table> 
     </div> 
    </div> 
    <div class="t_r_content" id="t_r_content2" onscroll="aa('t_r_content2','t_r_t2')"> 
      <table id="heroselltable">           
	    <?
		 $armorinfos = sql_fetch_rows("select * from sys_city_hero_trade where uid='$myuids' and trade=0 and npcid>0"); 	
         if(!empty($armorinfos)) foreach($armorinfos as $armorinfo){  
            $surehid='h'.$armorinfo['hid'];			 
        ?> 
          <tr style="background:#00FF33;color:#FF0000;margin:5px 10px;font-size:14px;">
             <td width="12.5%" class="bordertop"><? echo $armorinfo['name'];?></td>
	         <td width="12.5%" class="bordertop"><? echo $mmsex=$armorinfo['sex']>0?"男":"女";?></td>
	         <td width="12.5%" class="bordertop"><? echo $armorinfo['level'];?></td>
	         <td width="12.5%" class="bordertop"><? echo $armorinfo['command_base'];?></td>
		     <td width="12.5%" class="bordertop"><? echo $armorinfo['affairs_base'];?></td>
	         <td width="12.5%" class="bordertop"><? echo $armorinfo['bravery_base'];?></td>
	         <td width="12.5%" class="bordertop"><? echo $armorinfo['wisdom_base'];?></td>
		     <td width="12.5%" class="bordertop" style="text-align:right;"><? echo $armorinfo['price'];?>
			   <input type="checkbox" name="sureheroid" id="<? echo 'h'.$armorinfo['hid'];?>" value="<?echo $armorinfo['hid'];?>" onclick="showmysure(this.id,'formphpdh','iframesellheroId','sellheros.php',event)">
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