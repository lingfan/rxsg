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
  .t_r_content{width:101%; height:158px; background:#fff; overflow:auto;font-size:14px;overflow:scroll;} 
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
      <th style="width:32%;background:#00FF33;color:#FF0000;margin:5px 10px;font-size:22px;">请选择你要购买的装备</th> 
	  <th style="width:68%;background:#00FFFF;color:#FF0099;margin:5px 10px;font-size:22px;">装备详细情况一览表</th> 
     </tr> 
     <tr>
	  <th style="width:32%;background:#00FF33;color:#FF0000;margin:5px 10px;font-size:22px;"> 	  
       <select name="baname" id="baname" size="6" style="width:120px;" onchange="displayimages('baname','barmorpng','barmorprice','buystable1','totallmoneys','myyuanbo');">
       <?
	   $arrmortype=array(1=>"灰装",2=>"白装",3=>"绿装",4=>"蓝装",5=>"紫装",6=>"橙装",7=>"红装");
	   $arrmortypes=array(0=>"",1=>"精良",2=>"上品",3=>"玄铁",4=>"极品",5=>"嗜血",6=>"完美",7=>"绝世");	   
       $armorinfos = sql_fetch_rows("select a.*,c.* from sys_user_armor_trade a left join cfg_armor c on c.id=a.armorid where a.uid!='$myuids' and a.trade=0");		
       foreach($armorinfos as $armorinfo){
	    $attriinfo=$armorinfo['attribute'];	    
        if(empty($armorinfo['best_quality'])) $attribss='---';else{$atibutes=explode(",",$armorinfo['best_quality']);$attribss=$atibutes[3].'·'.$atibutes[4].'<br/>+'.$atibutes[2].'%';}	   
       ?> 
       <option value="<? echo $armorinfo['sid'].';'.$armorinfo['price'].';'.$armorinfo['name'].';'.$arrmortype[$armorinfo['type']].'·'.$arrmortypes[$armorinfo['combine_level']].';'.$armorinfo['strong_level'].';'.$attribss.';'.$attriinfo.';'.$mymoneys;?>" >  <? if($armorinfo['image']>0) echo $armorinfo['name'].'('.$armorinfo['image'].')';else echo $armorinfo['name'].'('.$armorinfo['id'].')';?> </option> 
       <? 
        } 
        ?> 
       </select>
       <img name="barmorpng" id="barmorpng"  style="width:80px;" /><br/><font color="#FF0000" size="1"><br/></font>
         热卖价格<input name="barmorprice" id="barmorprice" color="#FF0000" type="text" readonly="readonly" style="width:60px;">元宝<br/><font color="#FF0000" size="1"><br/></font>
	     <font color="#FF0000" size="5">是否购买所选装备</font><input type="submit" name="buysarmor" id="buysarmor" value="是" onclick="getbuyselectarmor('baname','surearmorprice','surebuyarmorsid','iframebuyaId','buyarmors.php','myyuanbo')" ><br/>
         <input type="hidden" name="surearmorprice" id="surearmorprice"> 
		 <input type="hidden" name="surebuyarmorsid" id="surebuyarmorsid">
         <input type="hidden" name="myyuanbo"  id="myyuanbo">		 
  	  </th>
	  <th style="width:68%;background:#00FFFF;color:#FF0099;margin:5px 10px;font-size:22px;">
       <div>
        <div class="t_r"> 
          <div class="t_r_t" id="t_r_tb"> 
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
           <div class="t_r_content" id="t_r_contentb" onscroll="aa('t_r_contentb','t_r_tb')"> 
            <table id="buystable1">
             <tr>
              <td width="20%" class="bordertop"></td>
	          <td width="20%" class="bordertop"></td>
	          <td width="20%" class="bordertop"></td>
	          <td width="20%" class="bordertop"></td>
		      <td width="20%" class="bordertop"></td>
             </tr>			
	        </table>
			<br/>
		    <div id="totallmoneys"></div>
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