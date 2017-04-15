<?php
require_once 'config/db.php';
require_once 'game/agents/AgentService.php';
class BaseService implements AgentService{
	
	protected $user=null;
	
	function BaseService($user)
	{
		$this->user=$user;
	}
	
	//推道到外部接口,具体在子类实现
	protected function sendAllUserAction($user, $title) {}

	//官服接口
	function addSysInformEvent($msg){}
	function addUnionMemberEvent($unionid,$Flag){}
	function addUnionDiplomacyEvent($unionid,$targetid,$relation){}
	function addTransferUnionChiefEvent($unionid,$name1,$name2){}
	function addUnionOfficialResignEvent($unionid,$name,$jobname){}
	function addUnionDeclareWarEvent($uname1,$uname2){}
	function addPushArmyOperationTimeEvent($troopid){}
	function addFriendEvent($tname){}
	function addAcceptTrickEvent($msg){}
	
	//好玩，同学接口
	function addStartWarEvent($enemyname) {}
	function addGoodsEvent($goodsname, $goodsvalue) {}
	function addHeroEvent($heroname) {}
	function addNobilityEvent($nobilityname) {}
	function addOfficePosEvent($officepos) {}
	function addCreateCityEvent($name) {}
}
/*class myService extends BaseService{
	function myService($user){
		parent::__construct($user);
		echo $this->user;
	}
}
$tService=new myService("aa");
*/
?>