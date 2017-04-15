<?php
require_once 'config/db.php';
require_once 'lib/httpUtils.php';
require_once 'lib/mysql.php';
require_once 'game/agents/AgentService.php';


/**
 * 
 *  好玩（许劼）(http://rxsg1.implay.com)
 * 
 * 接口地址1：url=http://www.implay.com/uch/newfeed.php?target=所在游戏区&account=玩家帐号&title=动态信息
 * 接口地址2：http://www.implay.com/uch/newfeed.php?target=所在游戏区&account=玩家帐号&title=通知事件&master=君主名
	account：是玩家的账号（一串md5加密的字符串）
	title可选参数：
	title=建了（什么）号
	title=升了（什么）官
	title=升了（什么）爵位
	title=降服了名将（某某）
	title=对敌人宣战
	括号里面的内容需要游戏服务器方面填充
	可以用file_get_contents(url)来获取返回结果 fail代表失败 success代表成功！
 * 
 *
 */
class ImPlayService extends BaseService {
	//private $user;
	function ImPlayService($user){
		parent::__construct($user);
		//$this->user=$user;
	}

	/**
	 * 给所有的好友发信息
	 *
	 * @param unknown_type $user
	 * @param unknown_type $object
	 * @param unknown_type $param
	 * @param unknown_type $template
	 */
	protected function sendAllUserAction($user, $title, $master="") {
		$target="rxsg";
		if (SERVER_ID>1) $target =$target.SERVER_ID;
		$url ="http://www.implay.com/uch/newfeed.php?target=".$target; 	
    	$title = urlencode($title);	
		$url=$url."&account=".$user["passport"]."&title=".$title;
		$time = sql_fetch_one_cell("select unix_timestamp()");
		$verify=md5($target.$user["passport"].$time);		
		$url=$url."&time=$time&verify=$verify";
		if($master!=""){
			$url=$url."&master=$master";
		}
		$result = HttpUtils::httpPost($url,"");	
		//file_put_contents("F:/out.txt"," ".$url." : ".$result);		
	}	
	/**
	 * 发动了一场战争
	 * 【done】
	 * {name}在<a href="{appaddress}" target="_blank" >热血三国</a>中刚对{enemy}宣战了，今天要死磕到底！赶快去帮忙，说不定还能捡个大便宜！<a href="{serveraddress}" target="_blank" >立刻援军参战</a>
	 * @param unknown_type $enemyname
	 */
	function addStartWarEvent($enemyname) {
		$title = "刚对“".$enemyname."”宣战了，今天要死磕到底！赶快去帮忙，说不定还能捡个大便宜";
		$this->sendAllUserAction($this->user,$title);
	}
	/**
	 * {name}在<a href="{appaddress}" target="_blank" >热血三国</a>中被幸运星砸中大脑门，打开宝箱居然找到了{goods}，价值{count}礼金啊！擦干口水，<a href="{serveraddress}" target="_blank" >我也去试试运气！</a>
	 * 【done】
	 * @param unknown_type $goodsname
	 * @param unknown_type $goodsvalue
	 */
	function addGoodsEvent($goodsname, $goodsvalue) {
		$title ="被幸运星砸中大脑门，打开宝箱居然找到了".$goodsname."，价值".$goodsvalue."元宝啊";
		$this->sendAllUserAction($this->user,$title);
	
	}
	/**
	 * 找到名将
	 * {name}在<a href="{appaddress}" target="_blank" >热血三国</a>中成为历史名将{heroname}的主公，弟兄们都对他仰慕得五体投地！<a href="{serveraddress}" target="_blank" >我也去捉个名将奴役一下</a>
	 * @param unknown_type $heroname
	 */
	function addHeroEvent($heroname) {
		$title = "成为历史名将“".$heroname."”的主公，弟兄们都对Ta仰慕得五体投地";
		$this->sendAllUserAction($this->user,$title);
	}
	
	/**
	 * {name}在<a href="{appaddress}" target="_blank" >热血三国</a>地位如日中天，获称 {nobilityname} 爵位头衔！<a href="{serveraddress}" target="_blank" >查看游戏</a>
	 * 【done】
	 * @param unknown_type $nobilityname
	 */
	function addNobilityEvent($nobilityname) {
		$title = "地位如日中天，获称“".$nobilityname."”爵位头衔";
		$this->sendAllUserAction($this->user,$title);
	}
	/**
	 * {name}在<a href="{appaddress}" target="_blank" >热血三国</a>中一不小心升官发达，现在都要叫他 {pos} 大人啦！！如此聪明绝伦的你一定不会输给他吧！<a href="{serveraddress}" target="_blank" >打理我的内政</a>
	 * 【done】
	 * @param unknown_type $officepos
	 */
	function addOfficePosEvent($officepos) {
		$title = "一不小心升官发达，现在都要叫Ta“".$officepos."”大人啦" ;		
		$this->sendAllUserAction($this->user,$title);
	}
	/**
	 * 新建了一个城市
	 * 【done】
	 * {name}在<a href="{appaddress}" target="_blank" >热血三国</a>中创建了自己的城池，还给自己起了一个惊天地泣鬼神的名号叫 "{rolename}"。又多了一个盟军战友，<a href="{serveraddress}" target="_blank" >快去跟他打个招呼!</a>
	 * @param unknown_type $name
	 */
	function addCreateCityEvent($name) {
		$title = "创建了自己的城池，还给自己起了一个惊天地泣鬼神的名号叫“".$name."”";
		$this->sendAllUserAction($this->user,$title);
	}
	/**
	 * 创建人物和第一个城市
	 * 【done】
	 * 
	 * @param unknown_type $name
	 */
	function addFirstCityEvent($name) {
		$title = "创建了自己的城池，还给自己起了一个惊天地泣鬼神的名号叫“".$name."”";
		$this->sendAllUserAction($this->user,$title,$name);
	}
	
}
?>