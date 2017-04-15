<?php
class HeroExpr {
	function __construct() {
	}
	
	/**
	 * 将领历练从这里开始
	 */
	function checkHeroExpr() {
		$heroExps = sql_fetch_rows("select * from sys_hero_expr where endtime<unix_timestamp() and state in (0,1) order by endtime");
		foreach ($heroExps as $heroExp) {
			$hid = $heroExp['hid'];
			$state = $heroExp['state'];
			$id = $heroExp['id'];
			
			if (0 == $state){ //历练正常结束			
				$this->finishHeroExpr($heroExp);
			}else if (1 == $state){
				$exp_add = 0;
				$uid = $heroExp['uid'];
				$carrymoney = $heroExp['carrymoney'];
				addMoney($uid,$carrymoney,121);
				$exp_add = $heroExp['exp_add']+10*randomRange($heroExp['hours'],2*$heroExp['hours']);	
				if ($exp_add>0)
					sql_query("update sys_city_hero set exp=exp+$exp_add where hid=$hid");	
			}
			sql_query("delete from sys_hero_expr where id=$id");			
			sql_query("update sys_city_hero set state = 0 where hid=$hid");	
		}
	}
	
	/**
	 * 历练正常结束，完成将领历练
	 */
	function finishHeroExpr($heroExp) {
		$uid = $heroExp['uid'];
		$cid = $heroExp['cid'];
		$hid = $heroExp['hid'];
		$hours = $heroExp['hours'];
		$type = $heroExp['type'];
		
		$hero = sql_fetch_one("select * from sys_city_hero where hid = $hid");
		$expAdd = $this->getHeroBaseExp($type,$hero['level'],$hours);
		sql_query("update sys_city_hero set exp=exp+$expAdd where hid={$heroExp['hid']}");
		$report = $this->getReportHead($type,$hero['name'],$hours,$expAdd);
		
		//活动奇遇
		//$this->actEvents($uid,$cid,$type,$hero['name'],$carryMoney,$hours,&$report);
		
		$report .= $this->baseEvents($heroExp);
		$report .= $this->heroFightEvents($heroExp);
		$report .= $this->commonExprEvents($heroExp);
		
		sendReport($uid,0,RT_HEROEXPR_END,$cid,$cid,$report);
	}
	
	/**
	 * 修身养性和闯荡江湖专属奇遇
	 */
	function baseEvents($heroExp) {
		$msg = "";
		$canHappen = mt_rand(1,100) < (25+$heroExp['hours']*$heroExp['hours']*1.2);
		if ($canHappen) {//发生修身养性专属奇遇
			if ($heroExp['type'] != 1 && $heroExp['type'] != 2) {
				$type = 1;
			} else {
				$type = $heroExp['type'];
			}
			$rewards = sql_fetch_rows("select * from cfg_hero_expr_reward where type=$type");//type=1,表示修身养性,2表示闯荡江湖
			$sumRate = sql_fetch_one_cell("select sum(rate) from cfg_hero_expr_reward where type=$type");
			$curRate = mt_rand(1,$sumRate);
			foreach ($rewards as $reward) {
				$curRate -= $reward['rate'];
				if ($curRate <= 0) {//发生奇遇
					$msg = $this->baseEventsHappen($heroExp,$reward);
					break;
				}
			}
		}
		return $msg;
	}
	
	/**
	 * 修身养性和闯荡江湖具体奇遇
	 */
	function baseEventsHappen($heroExp,$reward) {
		$msg = "";
		$hero = sql_fetch_one("select * from sys_city_hero where hid={$heroExp['hid']}");
		switch ($reward['sort']) {
			case 1://经验变化
				$msg .= $this->addHeroExp($heroExp,$hero,$reward);
				break;
			case 2://忠诚度发生变化
				$msg .= $this->addHeroLoyalty($heroExp,$hero,$reward);
				break;
			case 3://随机一种资源变化
				$msg .= $this->addExprRes($heroExp,$hero,$reward);
				break;
			case 4://随机一种道具
				$msg .= $this->addExprGoods($heroExp,$hero,$reward);
				break;
			case 5://随机获得一件宝珠
				$msg .= $this->addExprPearls($heroExp,$hero,$reward);
				break;
			case 6://基础属性变化
				$msg .= $this->addHeroBaseAddOn($heroExp,$hero,$reward);
				break;
			case 7://增加兵力
				$msg .= $this->addExprSoldier($heroExp,$hero,$reward);
				break;
			default: break;
		}
		return $msg;
	}
	
	/**
	 * 将领切磋开始处
	 */
	function heroFightEvents($heroExp) {
		$msg = "";
		$canHappen = mt_rand(1,100) <= 30;
		if ($canHappen) {//发生将领切磋奇遇
			$arr = $this->getRandFightResult($heroExp['uid'],$heroExp['hid'],$heroExp['type']);
			$rewards = sql_fetch_rows("select * from cfg_hero_expr_reward where type=3 and win={$arr['result']}");//type=3,表示将领切磋
			$sumRate = sql_fetch_one_cell("select sum(rate) from cfg_hero_expr_reward where type=3 and win={$arr['result']}");
			$curRate = mt_rand(1,$sumRate);
			foreach ($rewards as $reward) {
				$curRate -= $reward['rate'];
				if ($curRate <= 0) {//发生奇遇
					$msg = $this->heroFightEventHappen($heroExp,$reward,$arr);
					break;
				}
			}
		}
		return $msg;
	}
	
	/**
	 * 将领切磋具体奇遇
	 */
	function heroFightEventHappen($heroExp,$reward,$arr) {
		$msg = "";
		$hero = sql_fetch_one("select * from sys_city_hero where hid={$heroExp['hid']}");
		switch ($reward['sort']) {
			case 1://基础属性变化
				$msg .= $this->addHeroBaseAddOn($heroExp,$hero,$reward,$arr);
				break;
			case 2://精力和体力等属性变化
				$msg .= $this->addHeroOtherAttr($heroExp,$hero,$reward,$arr);
				break;
			case 3://将领装备耐久度变化
				$msg .= $this->addHeroArmorHp($heroExp,$reward,$arr);
				break;
			case 4://将领忠诚度发生变化
				$msg .= $this->addHeroLoyalty($heroExp,$hero,$reward,$arr);
				break;
			default: break;
		}
		return $msg;
	}
	
	/**
	 * 公共奇遇开始处
	 */
	function commonExprEvents($heroExp) {
		$msg = "";
		$canHappen = mt_rand(1,100)<=30+mt_rand(1,30);
		if ($canHappen) {//发生公共奇遇
			$rewards = sql_fetch_rows("select * from cfg_hero_expr_reward where type=4");//type=4,表示公共人物
			$sumRate = sql_fetch_one_cell("select sum(rate) from cfg_hero_expr_reward where type=4");
			$curRate = mt_rand(1,$sumRate);
			foreach ($rewards as $reward) {
				$curRate -= $reward['rate'];
				if ($curRate <= 0) {//发生奇遇
					$msg .= $this->commonExprEventHappen($heroExp,$reward);
					break;
				}
			}
		}
		return $msg;
	}
	
	/**
	 * 公共奇遇具体奇遇
	 */
	function commonExprEventHappen($heroExp,$reward) {
		$msg = "";
		switch ($reward['sort']) {
			case 1://通用道具事件
				$msg .= $this->commonGoodsEvent($heroExp,$reward);
				break;
			case 2://特殊道具事件
				$msg .= $this->specialGoodsEvent($heroExp,$reward);
				break;
			case 3://装备道具事件
				$msg .= $this->specialGoodsEvent($heroExp,$reward);
				break;
			case 4://基础属性事件
				$msg .= $this->specialGoodsEvent($heroExp,$reward);
				break;
			case 5://宝珠转化事件
				$msg .= $this->pearlsExchangeEvent($heroExp,$reward);
				break;
			case 6://装备强化事件
				$msg .= $this->strongArmorEvent($heroExp,$reward);
				break;
			case 7://好感度事件
				$msg .= $this->specialGoodsEvent($heroExp,$reward);
				break;
			case 8://加速造兵队列
				$msg .= $this->reduceSoldierDraftTime($heroExp,$reward);
				break;
			default: break;
		}
		return $msg;
	}
	
	/**
	 * 获取历练类型的名称
	 */
	function getTypeName($type) {
		return sql_fetch_one_cell("select name from cfg_hero_expr_types where `type`=$type");
	}
	
	/**
	 * 获取报告的信息头
	 */
	function getReportHead($type,$name,$hours,$expAdd) {
		$typeName = $this->getTypeName($type);
		$report = sprintf($GLOBALS['heroExpr']['type_name'],$typeName)."<br/>";
		$report .= sprintf($GLOBALS['heroExpr']['experience'],$name,$typeName,$hours,$expAdd);
		return $report."<br/>";;
	}
	
	/**
	 * 获取将领历练时间内的基本经验
	 */
	function getHeroBaseExp($type,$level,$hours) {
		$exp=0;
		switch ($type) {
			case 1: //修身养性
				$exp = $hours*$level*600 + mt_rand(1,1000);
				break;
			case 2: //闯荡江湖
				$exp = $hours*$level*1200 + mt_rand(1,1000);
				break;
			default:break;
		}
		return $exp;
	}
	
	/**
	 * 将领经验变化
	 */
	function addHeroExp($heroExp,$hero,$reward) {
		$exp = $this->getHeroBaseExp($heroExp['type'],$hero['level'],$heroExp['hours']);
		$details = str_replace('E',$exp,$reward['details']);
		$addCount = sql_fetch_one_cell(" select $details");
		$addCount = intval($addCount);
		if (empty($addCount)){
			$addCount = 0;
		}
		$userName = sql_fetch_one_cell("select name from sys_user where uid={$heroExp['uid']}");
		sql_query("update sys_city_hero set exp=exp+$addCount where hid={$heroExp['hid']}");
		$msg .= $reward['name']."<br/>";
		
		if (1 == $reward['inform']) {//发系统公告
			sendSysInform(
				sprintf(
					$GLOBALS['heroExpr']['hero_experience'],
					$userName,$hero['name'],
					$this->getTypeName($heroExp['type']),
					$exp+$addCount
				)
			);
		}
		return $msg;
	}
	
	/**
	 * 忠诚度发生变化
	 */
	function addHeroLoyalty ($heroExp,$hero,$reward,$arr = array()) {
		$addCount = $reward['details'];
		$peiNum = sql_fetch_one_cell("select count(*) from sys_hero_armor where armorid=12010 and hid={$heroExp['hid']}");
		$maxLoyalty = 100 + 25*$peiNum;
		if (($hero['loyalty']+$reward['details']) >= $maxLoyalty) {
			$addCount = $maxLoyalty - $hero['loyalty'];
		} elseif (($hero['loyalty']+$reward['details']) < 0) {
			$addCount = 0 - $hero['loyalty'];
		}
		if (empty($addCount)){
			$addCount = 0;
		}
		if (abs($addCount) > 0) {
			sql_query("update sys_city_hero set loyalty=loyalty+$addCount where hid={$heroExp['hid']}");
		}
		if (1 == $reward['type'] || 2 == $reward['type']) {//修身养性和闯荡江湖
			$msg = sprintf($reward['name'],$addCount)."<br/>";
		} elseif (3 == $reward['type']) {//将领切磋
			$msg = sprintf($reward['name'],$arr['userName'],$arr['heroName'])."<br/>";
		}
		$userName = sql_fetch_one_cell("select name from sys_user where uid={$heroExp['uid']}");
		if ($addCount >= 20) {//发系统公告
			sendSysInform(
				sprintf(
					$GLOBALS['heroExpr']['hero_loyalty'],
					$userName,$hero['name'],
					$this->getTypeName($heroExp['type']),
					$addCount
				)
			);
		}
		return $msg;
	}
	
	/**
	 * 增加历练获得的资源
	 */
	function addExprRes($heroExp,$hero,$reward) {
		$res = sql_fetch_one("select * from cfg_name where name in ('food','wood','rock','iron','gold') order by rand() limit 1");
		sql_query("update mem_city_resource set `{$res['name']}`=greatest(0,`{$res['name']}`+'{$reward['details']}') where `cid`='{$heroExp['cid']}'");
		$userName = sql_fetch_one_cell("select name from sys_user where uid={$heroExp['uid']}");
		$msg .= sprintf($reward['name'],$res['value'])."<br/>";
		if (1 == $reward['inform']) {//发系统公告
			sendSysInform(
				sprintf(
					$GLOBALS['heroExpr']['hero_goodslist'],
					$userName,$hero['name'],
					$this->getTypeName($heroExp['type']),
					$res['value'],
					$reward['details']
				)
			);
		}
		return $msg;
	}
	
	/**
	 * 增加历练获得的道具
	 */
	function addExprGoods($heroExp,$hero,$reward) {
		$goodsValue = $reward['details'];
		$userName = sql_fetch_one_cell("select name from sys_user where uid={$heroExp['uid']}");
		if ($goodsValue > 100) {
			$goodsValue = mt_rand(50,100);
		}
		$goodsId = sql_fetch_one_cell("select gid from cfg_shop where onsale = 1 and rebate !=1 and pack = 1 and `group` in (0,1,2,3,4) and price between $goodsValue-20 and $goodsValue+20 order by rand() limit 1 ");
		if (empty($goodsId)) {
			$goodsId = sql_fetch_one_cell("select gid from cfg_shop where onsale = 1 and rebate !=1 and pack = 1 and `group` in (0,1,2,3,4)  and price > $goodsValue order by price limit 1");
		}
		if (empty($goodsId)) {
			$goodsId = sql_fetch_one_cell("select gid from cfg_shop where onsale = 1 and rebate !=1 and pack = 1 and `group` in (0,1,2,3,4)  and price < $goodsValue order by price desc limit 1");
		}
		
		if (!empty($goodsId)) {
			$goodsName = sql_fetch_one_cell("select name from cfg_goods where gid=$goodsId");
			addUserGoods($heroExp['uid'],$goodsId,1,10);
			$msg .= sprintf($reward['name'],$goodsName)."<br/>";
			if (1 == $reward['inform']) {//发系统公告
				sendSysInform(
					sprintf(
						$GLOBALS['heroExpr']['hero_goods'],
						$userName,$hero['name'],
						$this->getTypeName($heroExp['type']),
						$goodsName
					)
				);
			}
		}
		return $msg;
	}
	
	/**
	 * 增加历练获得的宝珠
	 */
	function addExprPearls($heroExp,$hero,$reward) {
		$rows = sql_fetch_rows("select gid,name from cfg_hero_expr_goods where type=2");
		$userName = sql_fetch_one_cell("select name from sys_user where uid={$heroExp['uid']}");
		shuffle($rows);
		$goods = array_shift($rows);
		addUserGoods($heroExp['uid'],$goods['gid'],1,10);
		$msg .= sprintf($reward['name'],$goods['name'])."<br/>";
		if (1 == $reward['inform']) {//发系统公告
			sendSysInform(
				sprintf(
					$GLOBALS['heroExpr']['hero_goods'],
					$userName,$hero['name'],
					$this->getTypeName($heroExp['type']),
					$goods['name']
				)
			);
		}
		return $msg;
	}
	
	/**
	 * 增加历练获得将领基础属性
	 */
	function addHeroBaseAddOn($heroExp,$hero,$reward,$arr = array()) {
		$userName = sql_fetch_one_cell("select name from sys_user where uid={$heroExp['uid']}");
		$tempStr = $this->addHeroAttribute($heroExp,$reward);
		if (1 == $reward['type'] || 2 == $reward['type']) { //闯荡江湖和修身养性
			$msg .= $reward['name']."<br/>";
		} elseif (3 == $reward['type']) { //将领切磋
			$msg .= sprintf($reward['name'],$arr['userName'],$arr['heroName'])."<br/>";
		}
		if (1 == $reward['inform']) {//发系统公告
			sendSysInform(
				sprintf(
					$GLOBALS['heroExpr']['hero_attribute'],
					$userName,$hero['name'],
					$this->getTypeName($heroExp['type']),
					$tempStr
				)
			);
		}
		return $msg;
	}
	
	/**
	 * 增加将领历练获得的士兵
	 */
	function addExprSoldier($heroExp,$hero,$reward) {
		$userName = sql_fetch_one_cell("select name from sys_user where uid={$heroExp['uid']}");
		$sid = mt_rand(4,8);//从刀枪兵道铁骑
		sql_query("insert into sys_city_soldier(cid,sid,count) values('{$heroExp['cid']}',$sid,'{$reward['details']}') on duplicate key update count=count+{$reward['details']}");
		$soldierName = sql_fetch_one_cell("select name from cfg_soldier where sid=$sid");
		$msg = sprintf($reward['name'],$soldierName,$reward['details'])."<br/>";
		if (1 == $reward['inform']) {//发系统公告
			sendSysInform(
				sprintf(
					$GLOBALS['heroExpr']['add_soldiers'],
					$userName,$hero['name'],
					$this->getTypeName($heroExp['type']),
					$soldierName,$reward['details']
				)
			);
		}
		return $msg;
	}
	
	
	/**
	 * 更新将领基础属性
	 */
	function addHeroAttribute($heroExp,$reward) {
		$bravery_base_add_on=0; //1：勇武		
		$wisdom_base_add_on=0;  //2：智力
		$affairs_base_add_on=0; //3：内政		
		$command_base_add_on=0; //4：统率
		$attack_add_on=0; //5：攻击
		$defence_add_on=0; //6：防御
		$tempStr="";

		$attributes = explode(',',$reward['details']);
		
		for($i=0; $i<$attributes[0]; $i++) {
			$atrrType = $attributes[2*$i+1];
			$atrrValue = $attributes[2*$i+2];
			
			if (1 == $atrrType) {
				$bravery_base_add_on += $atrrValue;
				$atrrName = sql_fetch_one_cell("select name from cfg_attribute where attid=3");
				$tempStr .=$atrrName.$bravery_base_add_on;
			} elseif (2 == $atrrType) {
				$wisdom_base_add_on += $atrrValue;
				$atrrName = sql_fetch_one_cell("select name from cfg_attribute where attid=4");
				$tempStr .=$atrrName.$wisdom_base_add_on;
			} elseif (3 == $atrrType) {
				$affairs_base_add_on += $atrrValue;
				$atrrName = sql_fetch_one_cell("select name from cfg_attribute where attid=2");
				$tempStr .=$atrrName.$affairs_base_add_on;
			} elseif (4 == $atrrType) {
				$command_base_add_on += $atrrValue;
				$atrrName = sql_fetch_one_cell("select name from cfg_attribute where attid=1");
				$tempStr .=$atrrName.$command_base_add_on;
			}  elseif (5 == $atrrType) {
				$attack_add_on += $atrrValue;
				$atrrName = sql_fetch_one_cell("select name from cfg_attribute where attid=8");
				$tempStr .=$atrrName.$attack_add_on;
			}  elseif (6 == $atrrType) {
				$defence_add_on += $atrrValue;
				$atrrName = sql_fetch_one_cell("select name from cfg_attribute where attid=9");
				$tempStr .=$atrrName.$defence_add_on;
			}
		}
		insertHeroBaseAdd($heroExp['hid'], $heroExp['uid'],1, $bravery_base_add_on, $wisdom_base_add_on, $affairs_base_add_on, $command_base_add_on, $attack_add_on, $defence_add_on);
		return $tempStr;
	}
	
	/**
	 * 玩家精力和体力属性的变化
	 */
	function addHeroOtherAttr($heroExp,$hero,$reward,$arr = array()) {
		$force = 0; //1,体力
		$forceMax = 0; //2,体力最大值
		$energy = 0; //3,精力
		$energyMax = 0; //4，精力最大值
		
		$attributes = explode(',',$reward['details']);
		$tempStr="";
		$userName = sql_fetch_one_cell("select name from sys_user where uid={$heroExp['uid']}");

		for($i=0; $i < $attributes[0]; $i++) {
			$atrrType = $attributes[2*$i+1];
			$atrrValue = $attributes[2*$i+2];
			
			if (1 == $atrrType) {
				$heroBlood = sql_fetch_one("select * from mem_hero_blood where hid={$heroExp['hid']}");
				$force += intval($heroBlood['force_max']*0.01*$atrrValue);
				$atrrName = sql_fetch_one_cell("select name from cfg_attribute where attid=5");
				$tempStr .=$atrrName.$force;
			} elseif (2 == $atrrType) {
				$forceMax += $atrrValue;
				$atrrName = sql_fetch_one_cell("select name from cfg_attribute where attid=5");
				$tempStr .=$atrrName.$forceMax;
			} elseif (3 == $atrrType) {
				$heroBlood = sql_fetch_one("select * from mem_hero_blood where hid={$heroExp['hid']}");
				$energy += intval($heroBlood['energy_max']*0.01*$atrrValue);
				$energy = intval($energy);
				$atrrName = sql_fetch_one_cell("select name from cfg_attribute where attid=6");
				$tempStr .=$atrrName.$energy;
			} elseif (4 == $atrrType) {
				$energyMax += $atrrValue;
				$atrrName = sql_fetch_one_cell("select name from cfg_attribute where attid=6");
				$tempStr .=$atrrName.$energyMax;
			}
			addHeroBlood($heroExp['hid'],$force,$forceMax,$energy,$energyMax);
			
			$msg = sprintf($reward['name'],$arr['userName'],$arr['heroName'])."<br/>";
			if (1 == $reward['inform']) {//发系统公告
				sendSysInform(
					sprintf(
						$GLOBALS['heroExpr']['hero_attribute'],
						$userName,$hero['name'],
						$this->getTypeName($heroExp['type']),
						$tempStr
					)
				);
			}
		}
		return $msg;
	}
	
	/**
	 * 玩家装备耐久度变化
	 */
	function addHeroArmorHp($heroExp,$reward,$arr=array()) {
		$atrrValue = $reward['details'];
		$armors = sql_fetch_rows("select * from sys_user_armor where hid={$heroExp['hid']}");
		foreach ($armors as $armor) {
			$addCount = $armor['hp_max']*0.1*$atrrValue;//hp=hp_max*100
			if ($armor['hp']+$addCount > $armor['hp_max']*10) {
				$addCount = $armor['hp_max']*10 - $armor['hp'];
			} elseif ($armor['hp']+$addCount < 0) {
				$addCount = 0 - $armor['hp'];
			}
			sql_query("update sys_user_armor set hp=hp+$addCount where hid={$heroExp['hid']} and sid={$armor['sid']}");
		}
		$msg = sprintf($reward['name'],$arr['userName'],$arr['heroName'])."<br/>";
		return $msg;
	}
	
	/**
	 * 随机获取一个玩家及其将领
	 */
	function getRandFightResult($uid,$hid,$type) {
		$maxHid = sql_fetch_one_cell("select max(hid) from sys_city_hero where uid!=0");
		$tempHid = mt_rand(1,$maxHid);
		$tempHero = sql_fetch_one("select * from sys_city_hero where hid>=$tempHid and uid!=0 and uid !=$uid limit 1");
		$tempUserName = sql_fetch_one_cell("select name from sys_user where uid={$tempHero['uid']}");
		if (empty($tempUserName)) {
			$tempUserName = "???";
		}
		
		$myHeroInfo = sql_fetch_one("select a.energy,b.bravery_base+b.bravery_add+b.bravery_add_on as bravery,b.wisdom_base+b.wisdom_add+b.wisdom_add_on as wisdom from mem_hero_blood a,sys_city_hero b where a.hid=b.hid and b.hid=$hid");
		$b = ($myHeroInfo['bravery'] + $myHeroInfo['wisdom']) *0.1*$myHeroInfo['energy'];
		if ($b <= 0) {
			$b = 1;
		}
		$myValue = $myHeroInfo['bravery']*$myHeroInfo['bravery'] / $b;
		$tempHeroInfo = sql_fetch_one("select bravery_base+bravery_add+bravery_add_on as bravery,wisdom_base+wisdom_add+wisdom_add_on as wisdom from sys_city_hero where hid={$tempHero['hid']}");
		$tempEnergy = sql_fetch_one_cell("select energy from mem_hero_blood where hid={$tempHero['hid']}");
		if (empty($tempEnergy)) {
			$tempEnergy = 100;
		}
		$b = ($tempHeroInfo['bravery'] + $tempHeroInfo['wisdom']) *0.1*$tempEnergy;
		if ($b <= 0) {
			$b = 1;
		}
		$otherValue = $tempHeroInfo['bravery']*$tempHeroInfo['bravery'] / $b;
		$rate = 100 * $myValue /($myValue + $otherValue);
		$rate = intval($rate);
		$result = 0;
		$result = $this->getRandWin($rate,$type);
		
		$arr['userName']=$tempUserName;
		$arr['heroName']=$tempHero['name'];
		$arr['result']=$result;
		return $arr;
	}
	
	/**
	 * 伪随机，判定将领PK胜负
	 */
	function getRandWin($rate,$type) {
		$result = 0;
		
		if (1 == $type) {
			if ($rate > 75) {
				$rate = 75;
			} elseif ($rate < 25) {
				$rate = 25;
			}
		} elseif (2 == $type) {
			if ($rate > 80) {
				$rate = 80;
			} elseif ($rate < 40) {
				$rate = 40;
			}
		}
		
		$value = mt_rand(1,100);
		if ($value < $rate ) {
			$result = 1;
		}
		
		return $result; 
	}
	
	
	/**
	 * 公共奇遇获得普通道具事件
	 */
	function commonGoodsEvent($heroExp,$reward) {
		$msg = "";
		$flag = $this->isLimited($heroExp['uid'],$reward['id'],$reward['total']);
		if (!$flag) {
			return $msg;
		}
		
		$goodsInfo = $this->getExprCommonGoods();//获得随机出来的各种道具.
		$sql = "insert into sys_hero_expr_reward (`uid`,`cid`,`hid`,`type`,`sort`,`money`,`details`,`endtime`) values('{$heroExp['uid']}','{$heroExp['cid']}','{$heroExp['hid']}','{$heroExp['type']}','1','{$goodsInfo['curValue']}','{$goodsInfo['goodsList']}',unix_timestamp()+604800)";//七天后过期
		sql_query($sql);
		$id = sql_fetch_one_cell("select last_insert_id()");
		$msg = sprintf($reward['name'],$goodsInfo['curValue'],$goodsInfo['str'],$goodsInfo['sumValue']);
		$msg .= sprintf($GLOBALS['heroExpr']['ok_url'],$id)."<br/>";
		return $msg;
	}
	
	/**
	 * 加快将领所在城池的一个造兵队列
	 */
	function reduceSoldierDraftTime($heroExp,$reward) {
		$msg = "";
		$flag = $this->isLimited($heroExp['uid'],$reward['id'],$reward['total']);
		if (!$flag) {
			return $msg;
		}
		$money = mt_rand($reward['minnum'],$reward['maxnum']);
		$sql = "insert into sys_hero_expr_reward (`uid`,`cid`,`hid`,`type`,`sort`,`money`,`details`,`endtime`) values('{$heroExp['uid']}','{$heroExp['cid']}','{$heroExp['hid']}','{$heroExp['type']}','{$reward['sort']}','{$money}','{$reward['details']}',unix_timestamp()+604800)";//七天后过期
		sql_query($sql);
		$id = sql_fetch_one_cell("select last_insert_id()");
		$msg = sprintf($reward['name'],$money);
		$msg .= sprintf($GLOBALS['heroExpr']['ok_url'],$id)."<br/>";
		return $msg;
	}
	
	
	/**
	 * 增加公共奇遇特殊道具函数
	 */
	function specialGoodsEvent($heroExp,$reward) {
		$msg = "";
		if (7 == $reward['sort'] && $heroExp['hid'] > NPC_HID_END) return $msg; 
		$flag = $this->isLimited($heroExp['uid'],$reward['id'],$reward['total']);
		if (!$flag) {
			return $msg;
		}
		$money = mt_rand($reward['minnum'],$reward['maxnum']);
		$sort = $reward['sort'];//奖励类型1:普通道具，2:特殊道具，3:装备道具,4:基础属性,5:宝珠转换,6:强化装备,7:好感度，8，加快造兵队列。
		$sql = "insert into sys_hero_expr_reward (`uid`,`cid`,`hid`,`type`,`sort`,`money`,`details`,`endtime`) values('{$heroExp['uid']}','{$heroExp['cid']}','{$heroExp['hid']}','{$heroExp['type']}','$sort','{$money}','{$reward['details']}',unix_timestamp()+604800)";//七天后过期
		sql_query($sql);
		$id = sql_fetch_one_cell("select last_insert_id()");
		$msg = sprintf($reward['name'],$money);
		$msg .= sprintf($GLOBALS['heroExpr']['ok_url'],$id)."<br/>";
		return $msg;
	}
	
	/**
	 * 获得公共奇遇普通道具函数
	 */
	function getExprCommonGoods() {
		$rows = sql_fetch_rows("select * from cfg_hero_expr_goods where type=1"); //通用类道具
		shuffle($rows);
		$count = mt_rand(1,5);
		$str = "";
		$sumValue = 0;
		$curValue = 0;
		$goodsList = "";
		for ($i=0; $i<$count; $i++) {
			$row = array_shift($rows);
			$str .= $row['name']."*1,";
			$goodsList .= $row['gid'].",";
			$sumValue += $row['value'];
		}
		$curValue = $sumValue * 0.01 * mt_rand(85,95);
		$curValue = intval($curValue);
		return array('sumValue'=>$sumValue,'curValue'=>$curValue,'str'=>$str,'goodsList'=>$goodsList);
	}
	
	/**
	 * 公共奇遇宝珠转换
	 */
	function pearlsExchangeEvent($heroExp,$reward) {
		$msg = "";
		$flag = $this->isLimited($heroExp['uid'],$reward['id'],$reward['total']);
		if (!$flag) {
			return $msg;
		}
		$fromPearls = sql_fetch_rows("select * from cfg_goods where gid between 301 and 380 and value>=200");
		shuffle($fromPearls);
		$fromPearl = array_shift($fromPearls);
		$toPearls = sql_fetch_rows("select * from cfg_goods where gid between 301 and 380 and value={$fromPearl['value']} and gid!= {$fromPearl['gid']}");
		shuffle($toPearls);
		$toPearl = array_shift($toPearls);
		
		$money = mt_rand($reward['minnum'],$reward['maxnum']);
		$sort = $reward['sort'];//奖励类型1:普通道具，2:特殊道具，3:装备道具,4:基础属性,5:宝珠转换,6:强化装备
		$details = $fromPearl['gid'].','.$toPearl['gid'];
		$sql = "insert into sys_hero_expr_reward (`uid`,`cid`,`hid`,`type`,`sort`,`money`,`details`,`endtime`) values('{$heroExp['uid']}','{$heroExp['cid']}','{$heroExp['hid']}','{$heroExp['type']}','$sort','$money','{$details}',unix_timestamp()+604800)";//七天后过期
		sql_query($sql);
		$id = sql_fetch_one_cell("select last_insert_id()");
		$msg = sprintf($reward['name'],$money,$fromPearl['name'],$toPearl['name']);
		$msg .= sprintf($GLOBALS['heroExpr']['ok_url'],$id)."<br/>";
		return $msg;
	}
	
	/**
	 * 公共奇遇：升级玩家将领身上的一件装备
	 */
	function strongArmorEvent($heroExp,$reward) {
		$msg = "";
		$flag = $this->isLimited($heroExp['uid'],$reward['id'],$reward['total']);
		if (!$flag) {
			return $msg;
		}
		$armor = $this->getCanStrongArmor($heroExp['hid']);
		if (empty($armor['armorId'])) {
			return $msg;
		}
		//$money = mt_rand($reward['minnum'],$reward['maxnum']);
		$money = $reward['minnum']*$armor['level'];
		$sort = $reward['sort'];//奖励类型1:普通道具，2:特殊道具，3:装备道具,4:基础属性,5:宝珠转换,6:强化装备,7:好感度
		$details = $armor['armorId'];
		$sql = "insert into sys_hero_expr_reward (`uid`,`cid`,`hid`,`type`,`sort`,`money`,`details`,`endtime`) values('{$heroExp['uid']}','{$heroExp['cid']}','{$heroExp['hid']}','{$heroExp['type']}','$sort','$money','$details',unix_timestamp()+604800)";//七天后过期
		sql_query($sql);
		$id = sql_fetch_one_cell("select last_insert_id()");
		$msg = sprintf($reward['name'],$money,$armor['armorName']);
		$msg .= sprintf($GLOBALS['heroExpr']['ok_url'],$id)."<br/>";
		return $msg;
	}
	
	/**
	 * 获得玩家身上一件可以升级的装备
	 */
	function getCanStrongArmor($hid) {
		$armorId = 0;
		if (empty($hid)) {
			return $armorId;
		}
		$armors  = sql_fetch_rows("select b.* from sys_hero_armor a,sys_user_armor b,cfg_armor c where a.sid=b.sid and c.id=a.armorid and c.part!=12 and b.strong_level between 0 and 9 and a.hid=$hid");
		if (empty($armors)) {
			return $armorId;
		}
		shuffle($armors);
		$armor = array_shift($armors);
		$armorId = $armor['sid'];
		$strong_level = $armor['strong_level'];
		$armorName = sql_fetch_one_cell("select name from cfg_armor where id={$armor['armorid']}");
		return array('armorId'=>$armorId,'armorName'=>$armorName,'level'=>$strong_level);
	}
	
	/**
	 * 验证玩家是否已经达到了公共奇遇的上限
	 */
	function isLimited($uid,$rewardId,$count) {
		if (empty($count)) {//如果为0，不做限制
			return  true;
		}
		$arr = sql_fetch_one("select * from sys_user_expr_reward where uid=$uid and rewardid=$rewardId");
		if (!empty($arr) && $count <= intval($arr['count'])) {
			return false;
		}
		sql_query("insert into sys_user_expr_reward (`uid`,`rewardid`,`count`) values('$uid','$rewardId','1') on duplicate key update count=count+1");
		return true;
	}
	
	
	/**
	 * 活动奇遇
	 */
	function actEvents($uid,$cid,$type,$heroName,$carryMoney,$hours,&$report) {
		$act = sql_fetch_one("select * from cfg_act where type = 8 and unix_timestamp() between starttime and endtime");
		if (!empty($act)) {
			$actQiyu = sql_fetch_one("select * from cfg_act_hero_expr where actid={$act['actid']} and type=$type");
			if (!empty($actQiyu) && ($carryMoney >= $actQiyu['money']) && ($hours >= $actQiyu['hours']) && (rand()%10000 < $actQiyu['rate']*$hours)) {
				$canOpen = true;
				if ($actQiyu['oncecount'] > 0 && $actQiyu['oncecount'] <= sql_fetch_one_cell("select count(*) from log_act_hero_expr where uid=$uid and type={$actQiyu['type']} and qid={$actQiyu['qid']} and (time between {$act['starttime']} and {$act['endtime']})")) {
					$canOpen = false;
				}
				if ($actQiyu['oncecount'] > 0 && $actQiyu['totalcount'] <= sql_fetch_one_cell("select count(*) from log_act_hero_expr where uid=$uid and time between {$act['starttime']} and {$act['endtime']}")) {
					$canOpen = false;
				}
				if ($canOpen) {
					$rateSum = 0;
					sql_query("insert into log_act_hero_expr (uid,type,qid,time) values ('$uid','$type','{$actQiyu['qid']}',unix_timestamp())");
					$cfgboxdetails = sql_fetch_rows("select * from cfg_box_details where srctype=4 and srcid= {$actQiyu['qid']}");
					foreach ($cfgboxdetails as $cfgboxdetail) {
						$rateSum += $cfgboxdetail['rate'];
					}
					$curRandom = mt_rand() % $rateSum;
					$curRateSum = 0;
					foreach ($cfgboxdetails as $cfgboxdetail) {
						$curRateSum = $cfgboxdetail['rate'];
						if ($curRateSum < $curRandom) {
							continue;
						}
						$dayopencount = $cfgboxdetail['dayopencount'];
						$totalopencount = $cfgboxdetail['totalopencount'];
						$owncount = $cfgboxdetail['owncount'];
						$count = $cfgboxdetail['count'];
						$sort = $cfgboxdetail['sort'];
						$curtype = $cfgboxdetail['type'];
						$addTableName = "";
						if ($sort == 2) $addTableName = "goods";
						if ($sort == 2) $addTableName = "things";
						if ($sort == 2) $addTableName = "armor";
						
						if ($dayopencount > 0) {
							$todayopencount = sql_fetch_one_cell("select sum(count) from log_$addTableName where uid=$uid and type=6 and gid=$curtype and curdate()=date(from_unixtime(time))");
							if ($todayopencount >= $dayopencount) continue;
						}
						if($totalopencount > 0){
							$act_totalopencount=sql_fetch_one_cell("select sum(count) from log_$addTableName where uid=$uid and type=6 and gid=$curtype and (time between {$act['starttime']} and {$act['endtime']})");
							if ($act_totalopencount >= $totalopencount) continue;
						}
						if($owncount > 0){
							$user_ownercount = 0;
							if($sort!="6")
								$user_ownercount = sql_fetch_one_cell("select sum(count) from sys_$addTableName where uid=$uid and gid=$curtype");
							else 
								$user_ownercount = sql_fetch_one_cell("select count(1) from sys_$addTableName where uid=$uid and gid=$curtype");
							if ($user_ownercount >= $owncount) continue;
						}
						$tempid = $curtype;
						if ($sort == 2) {
							addUserGoods($uid,$tempid,$count,6);
							$getName = sql_fetch_one_cell("select name from cfg_goods where gid=$tempid");
						}
						if ($sort == 5) {
							addUserThings($uid,$tempid,$count,6);
							$getName = sql_fetch_one_cell("select name from cfg_things where tid=$tempid");
						}
						if ($sort == 6) {
							addUserArmor($uid,$tempid,$count,6);
							$getName = sql_fetch_one_cell("select name from cfg_armor where id=$tempid");
						}
						$qiyu_description = $actQiyu['description'];
						$qiyu_money = $actQiyu['money'];
						sprintf($qiyu_description,$qiyu_money,$getName,$count);
						$report .= $qiyu_description;
						sendReportByContent($uid,RT_HEROEXPR_END,$count,$cid,$cid,0,$report);
						addMoney($uid,$carryMoney-$qiyu_money,121);
						
						$inform = $cfgboxdetail['inform'];
						if ($inform == 1) {
							$name = sql_fetch_one_cell("select name from sys_user where uid =$uid");
							$heroExprInfom = sql_fetch_one_cell("select * from cfg_name where name like 'hero_expr_inform'");
							sprintf($heroExprInfom,$name,$heroName,$this->getTypeName($type),$getName);
							sendSysInform($heroExprInfom);
						}
						return;
					}
				}
			}
			
		}
	}
	
	
}
?>