<?php  include "index_top.php" ?>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html" charset="utf-8" />
<title><?=TITLE?></title>
<script src="AC_OETags.js" language="javascript"></script>
<script src="bloodwar.js" language="javascript"></script>
<script src="pass.js" language="javascript"></script>
<script src="lang_zh_CN.js" language="javascript"></script>

<?php  //if (is_callable(index_head)) index_head(); ?>
<style>
body { margin: 0px; overflow:hidden }
</style>
<script>

function showLogin()
 {
	if(getPassType()=="xiaonei"){
		window.location=getXiaoneiUrl();
	}else {
		var qqobj = getFlashMovieObject("BloodWar") ;
		 
		if ( qqobj) {
			qqobj.showFlashLogin();
		}
	}
 }
</script>
</head>
<body bgcolor=black leftmargin="0"  topmargin="0"  style="overflow:auto" onload="this.focus()" onload="focusflash();" onfocus="focusflash();" onresize="resetiframe();">
<TABLE width=1000 height=600 align=center border="0" cellpadding="0" cellspacing="0">
<TR>
	<TD align=center valign=top>
<script language="JavaScript" type="text/javascript">
<!--
// Globals
// Major version of Flash required
var requiredMajorVersion = 9; 
// Minor version of Flash required
var requiredMinorVersion = 0;
// Minor version of Flash required
var requiredRevision = 124;	
// Version check for the Flash Player that has the ability to start Player Product Install (6.0r65)
var hasProductInstall = DetectFlashVer(6, 0, 65);

// Version check based upon the values defined in globals
var hasRequestedVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);


// Check to see if a player with Flash Product Install is available and the version does not meet the requirements for playback
if ( hasProductInstall && hasRequestedVersion ) {
	// if we've detected an acceptable version
	// embed the Flash Content SWF when all tests are passed
	AC_FL_RunContent(
			"src", "BloodWar",
			"width", "1000",
			"height", "600",
			"align", "middle",
			"id", "BloodWar",
			"quality", "high",
			"wmode","window",
			"bgcolor", "#000000",
			"name", "BloodWar",
			"allowScriptAccess","sameDomain",
			"type", "application/x-shockwave-flash",
			"pluginspage", "http://www.adobe.com/go/getflashplayer"
	);
  } else {  // flash is too old or we can't detect the plugin
  var alternateContent = '<br/><br/><br/><br/><br/><br/><div align="center"><p><img src="noflash.jpg" width="476" height="230" border="0" usemap="#Map" /><map name="Map" id="Map"><area shape="rect" coords="63,105,218,129" href="http://sg.uuyx.com/uploadfile/install_flash_player_active_x_9f.exe" target="_blank" alt="点击下载安装Flash" /><area shape="rect" coords="56,181,228,211" href="http://sg.uuyx.com/uploadfile/install_flash_player_active_x_9f.rar" target="_blank" alt="点击下载Flash安装包" /></map></p></div>';
    document.write(alternateContent);  // insert non-flash content
  }
// -->
</script>
<noscript>
  	<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
			id="BloodWar" width="1000" height="600"
			codebase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab">
			<param name="movie" value="BloodWar.swf" />
			<param name="quality" value="high" />
			<param name="wmode"   value="window">   
			<param name="bgcolor" value="#000000" />
			<param name="allowScriptAccess" value="sameDomain" />
			<embed src="BloodWar.swf" quality="high" bgcolor="#000000"
				width="1000" height="600" name="BloodWar" align="middle"
				play="true"
				loop="false"
				quality="high"
				allowScriptAccess="sameDomain"
				type="application/x-shockwave-flash"
				pluginspage="http://www.adobe.com/go/getflashplayer">
			</embed>
	</object>
</noscript>
</TD>
</TR>
</TABLE>

<div style="overflow:auto;z-index:100;"> 
<!--<iframe id="content" frameborder="0"  name="content" scrolling="auto;"  style="position:absolute;background-color:transparent;border:0px;visibility:hidden;" allowtransparency="true">
</iframe>-->
</div>
<?php // if (is_callable(index_bottom)) index_bottom(); ?>
<div style=\"color:white;text-align:center;font-size:12px;margin-top:10px\"><?php if (defined('ICP')) echo(ICP);?></div>
</body>
</html>
