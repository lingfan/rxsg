<?php
  require_once("dbinc.php");
  $myuids=$_SESSION['myuid'];
  $mymoneys=sql_fetch_one_cell("select money from sys_user where uid='$myuids'");  
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
  .t_r_content{width:103.7%; height:158px; background:#fff; overflow:auto;font-size:14px;overflow:scroll;} 
  .cl_freeze{height:140px;overflow:hidden; width:100%;} 
  .t_r{width:100%; height:auto; float:left;border-top:1px solid #000; border-right:#000 solid 1px;font-size:16px;}  
  .t_r table{width:99.5%;} 
  .t_r_title{width:97.7%;}
  .t_r_t{width:100%; overflow:hidden;font-size:14px;}
 </style>  
</head> 
<body>
  <iframe name="sformsubmitbuys" style="display:none;">    
  </iframe>
 <form action="TradeFunction.php" method="POST" name="formphpbuys" id="formphpbuys" onsubmit="" target="sformsubmitbuys"> 
  <div style="width:750px;">
   <table border="1" style="border-collapse:collapse;border-spacing:0px; width:100%;height:21px; border:#000 solid 1px;" align="center"> 
     <tr style="background:#F0F0E4"> 
      <th style="width:32%;background:#00FF33;color:#FF0000;margin:5px 10px;font-size:22px;">请选择你要购买的名将</th> 
	  <th style="width:68%;background:#00FFFF;color:#FF0099;margin:5px 10px;font-size:22px;">将领详细情况一览表</th> 
     </tr> 
     <tr>
	  <th style="width:32%;background:#00FF33;color:#FF0000;margin:5px 10px;font-size:22px;"> 	  
       <select name="heroname" id="heroname" size="6" style="width:120px;" onchange="displayheroimage('heroname','herormorpng','herormorprice','buyherotable1','totallmoneys1','myyuanbow');">
       <?
	   $armorinfos = sql_fetch_rows("select * from sys_city_hero_trade where uid!='$myuids' and trade=0 and npcid>0"); 
       foreach($armorinfos as $armorinfo){	    	   
       ?> 
       <option value="<? echo $armorinfo['hid'].';'.$armorinfo['sex'].';'.$armorinfo['face'].';'.$armorinfo['level'].';'.$armorinfo['command_base'].';'.$armorinfo['affairs_base'].';'.$armorinfo['bravery_base'].';'.$armorinfo['wisdom_base'].';'.$armorinfo['price'].';'.$mymoneys;?>"> <? echo $armorinfo['name'];?> </option> 
        <? 
        } 
        ?> 
       </select>
       <img name="herormorpng" id="herormorpng"  style="width:80px;height:95px;" /><br/><font color="#FF0000" size="1"><br/></font>
         热卖价格<input name="herormorprice" id="herormorprice"  color="#FF0000" type="text" readonly="readonly" style="width:60px;">元宝<br/><font color="#FF0000" size="1"><br/></font>
	     <font color="#FF0000" size="5">是否购买所选名将</font><input type="submit" name="buyhero" id="buyhero" value="是" onclick="getbuyselecthero('heroname','sureheroprice','surebuyheroid','iframebuyheroId','buyheros.php','myyuanbow')" ><br/>
         <input type="hidden" name="sureheroprice" id="sureheroprice"> 
		 <input type="hidden" name="surebuyheroid" id="surebuyheroid">
         <input type="hidden" name="myyuanbow"  id="myyuanbow">		 
  	  </th>
	  <th style="width:68%;background:#00FFFF;color:#FF0099;margin:5px 10px;font-size:22px;">
       <div>
        <div class="t_r"> 
          <div class="t_r_t" id="t_r_tb3"> 
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
           <div class="t_r_content" id="t_r_contentb3" onscroll="aa('t_r_contentb3','t_r_tb3')"> 
            <table id="buyherotable1">
             <tr>
              <td width="12.5%" class="bordertop"></td>
	          <td width="12.5%" class="bordertop"></td>
	          <td width="12.5%" class="bordertop"></td>
	          <td width="12.5%" class="bordertop"></td>
		      <td width="12.5%" class="bordertop"></td>
	          <td width="12.5%" class="bordertop"></td>
	          <td width="12.5%" class="bordertop"></td>
		      <td width="12.5%" class="bordertop"></td>
             </tr>			
	        </table>
			<br/>
		    <div id="totallmoneys1"></div>
           </div>
         </div>
        </div> 
	   </th>
	 </tr>
	</table>
  </div>
 </form>
</body> 
</html> 