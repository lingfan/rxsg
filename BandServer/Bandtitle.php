<html> 
<head> 
<meta http-equiv="Content-Type" content="text/html" charset="utf-8">
<title>风云三国合服设置</title>
</head> 
<body> 
<form id="form_select" name="form_select" method="post" action="">
<p align="center">------使用说明--------</p>
<p>1、将要合服的区的<font color="#00FF00">所有数据库</font>用<font color="#00FF00">导出法备份</font>,比如1区的可以备份成bloodwar1.sql 2区可以备份成bloodwar2.sql;<br/>
   2、将要合的区的数据库放入新区与新区数据库同在一个文件夹并要<font color="#00FF00">改名</font>，如改成rxsg;新区的数据库文件夹bloodwar不能变成其它的名子！<br/>
   3、将备份好的sys_building数据库,按对应的区导入成功后,设置代码中$BandDb的值（这个是你要合的区所对应的数据库文件夹名）;$BandNum的值（这个是区号'设置成你对应的！）;<br/>
   4、合好一个区后，关闭服务器，删除刚才合区的数据库，拷贝下一个要合的数据库，打开服务器，接着重复2--4步骤直到你把要合的区合完！<br/>
   注：一定要记得每次设置代码中的两个参数$BandDb;$BandNum的值<br/>
</p>
</form>
</body>
</html>