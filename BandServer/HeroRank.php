<html> 
<head> 
<meta http-equiv="Content-Type" content="text/html" charset="utf-8">
<title>将领排行</title>
</head> 
<body bgcolor="#CCFFCC" > 
<p align="left"><font color="#FF0000" size="5">势力名称:</font>
<?
  require_once("dbinc.php");  
  $heroins = sql_fetch_rows("select * from cfg_npc_hero where order by bravery_base desc ");
  foreach($heroins as $heroin){
     echo $heroin['npcid'].'<br>'.$heroin['name'].'<br>'.$heroin['bravery_base'].'<br>'.$heroin['affairs_base'].'<br>'.$heroin['wisdom_base'].'<br/>';
    }
  ?>


</body>
</html>  