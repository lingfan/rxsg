<html> 
<head> 
<meta http-equiv="Content-Type" content="text/html" charset="utf-8">
</head> 
<body>
<form id="banduser" name="banduser" method="post" action="">
<p align="center">------请选择要删除的玩家君主名--------</p>
 <p align="center"> 
   <?
	 require_once("dbinc.php");
	 $userinfosql = sql_fetch_rows("select * from bloodwar.banduser where uid>1 ");
	 if(!empty($userinfosql)){
	 ?>  <font size="4"> 君主名称　登陆账号　号所在区</font></br> 
		 <select id="ss" size="8" name="username[]"  multiple="yes" width="345px"> <?
	     foreach ($userinfosql as $userinfo){//逐行获取结果集中的记录，得到数组row,数组row的下标对应着数据库中的字段值
             $olduid = $userinfo['uid']; //合服的uid
		     $name =$userinfo['name']; //合服后君主名
		     $passport = $userinfo['passport'];//合服前帐号
		     $bandnum = $userinfo['bandnum'];
		     ?>
	         <option value=<?echo $olduid;?>> <?echo $name.'　　'.$passport.'　　'.$bandnum;?> </option>  
             <?	
	        }?>
	     </select>
	 <?	}     ?>
	
</p>
<p align="center">
  <input type="hidden" name="action" value="list" />
  <input name="banduser"  value="确定删除" type="submit">
</p>
</form>
</body>
</html>
<?php
if(isset($_POST['action']) && $_POST['action'] == 'list'){
     require_once("dbinc.php");
	 if(!empty($_POST['username'])){
         foreach($_POST['username'] as $useruid){
             sql_query("update  bloodwar.banduser set state=1 where uid='$useruid'");//将要删除的玩家做上标记		  
	        } 
	    }
	}
?> 