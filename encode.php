<?php 
    require_once "server/config/db.php";
    require_once "server/config/key.php";
    $key=$GLOBALS['passArray']['jskey'];
  	$script='function isIEa(){return(navigator.appName=="Microsoft Internet Explorer")}function getBloodWar(){return getFlashMovieObject("BloodWar")}function $(a){return document.getElementById(a)}function getCurEncodingStr(){try{return getBloodWar().getEncoding.toString()}catch(a){return""}}function encodeDataNow(b){var d=getCurEncodingStr();var c=(d.length>160&&d.length<180)||!isIEa();if(!c){return""}var a=xtea_encrypt(b,key);return base64_encode(a)}function base64_encode(e){var d="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";var c="";var b=e.length;var a,g;var f=0;while(b-->0){a=e.charCodeAt(f++);c+=d.charAt((a>>2)&63);if(b--<=0){c+=d.charAt((a<<4)&63);c+="==";break}g=e.charCodeAt(f++);c+=d.charAt(((a<<4)|((g>>4)&15))&63);if(b--<=0){c+=d.charAt((g<<2)&63);c+="=";break}a=e.charCodeAt(f++);c+=d.charAt(((g<<2)|((a>>6)&3))&63);c+=d.charAt(a&63)}return c}function long2str(c,b){var e=c.length;var f=(e-1)<<2;if(b){var a=c[e-1];if((a<f-3)||(a>f)){return null}f=a}for(var d=0;d<e;d++){c[d]=String.fromCharCode(c[d]&255,c[d]>>>8&255,c[d]>>>16&255,c[d]>>>24&255)}if(b){return c.join("").substring(0,f)}else{return c.join("")}}function str2long(e,b){var a=e.length;var c=[];for(var d=0;d<a;d+=4){c[d>>2]=e.charCodeAt(d)|e.charCodeAt(d+1)<<8|e.charCodeAt(d+2)<<16|e.charCodeAt(d+3)<<24}if(b){c[c.length]=a}return c}function xtea_encrypt(h,o){if(h==""){return""}var r=str2long(h,true);var d=str2long(o,false);if(d.length<4){d.length=4}var c=r.length-1;var i=r[c],j=r[0],m=2654435769;var l,g,b,a=Math.floor(6+52/(c+1)),f=0;while(0<a--){f=f+m&4294967295;g=f>>>2&3;for(b=0;b<c;b++){j=r[b+1];l=(i>>>5^j<<2)+(j>>>3^i<<4)^(f^j)+(d[b&3^g]^i);i=r[b]=r[b]+l&4294967295}j=r[0];l=(i>>>5^j<<2)+(j>>>3^i<<4)^(f^j)+(d[b&3^g]^i);i=r[c]=r[c]+l&4294967295}return long2str(r,false)}function ggg(){try{if(gggc==10){return}submitS(getFirstEncodingStr()+":"+getCurEncodingStr());gggc++}catch(a){}}';    
    $script=str_replace("key","'$key'",$script);
	$swfname=$GLOBALS['passArray']['swfname'];
	$swfkey=$GLOBALS['passArray']['swfkey'];
	$swfpassfile="swf/pass/".$swfname.".swf?1";   
?>
<script>
<?php echo $script ?>
function getxx(){return 1}
function getrandnum(){return <?php echo $key_rand?>;}
function getEncryptProtocol(){return <?php echo ENCRYPT_PROTOCOL?>;}function getSwfPassFile(){return '<?php echo $swfpassfile?>';}
</script>


