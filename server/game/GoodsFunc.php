<?php
require_once("./interface.php");
require_once("./utils.php");
require_once("./HeroFunc.php");
require_once("./EquipmentFunc.php");
require_once ("./ActFunc.php");
require_once ("./TaskFunc.php");
function loadUserGoods($uid,$param)
{
	$ret = array();
	$exstid="104001";
	//throw new Exception(sql_fetch_one_cell("select count(*) from sys_things t left join cfg_things f on f.tid=t.tid where t.uid='$uid' and t.`count` > 0 order by f.position"));
	//	$ret[] = sql_fetch_rows("select * from sys_goods g left join cfg_goods f on f.gid=g.gid where g.uid='$uid' and g.`count` > 0 order by f.`group`,f.gid");
	//	$ret[] = sql_fetch_rows("select * from sys_things t left join cfg_things f on f.tid=t.tid where t.uid='$uid' and t.`count` > 0 order by f.position");
	$ret[] = sql_fetch_rows("select * from sys_goods g join cfg_goods f on f.gid=g.gid where g.uid='$uid' and g.`count` > 0 order by f.`group`,f.gid");
	$ret[] = sql_fetch_rows("select * from sys_things t join cfg_things f on f.tid=t.tid and t.tid not in($exstid) where t.uid='$uid' and t.`count` > 0 order by f.position");
	return $ret;
}


function loadSuipian($uid,$param)
{
	$ret = array();
	$ret[] = sql_fetch_rows("select * from sys_goods g left join cfg_goods f on f.gid=g.gid where g.uid='$uid' and g.gid>100000 and g.gid<160000 and g.`count` > 0 order by f.`group`,f.position");
	return $ret;
}

function useGoods($uid,$param)
{
	$gid = intval(array_shift($param));
	if($gid>110000&&$gid<160000){$hid= intval(array_shift($param));$useCount = 1;}
	 else
	  $useCount = intval(array_shift($param));
	if($useCount<1){	$useCount = 1;}
	//if($useCount<1) throw new Exception("数量 参数错误！");
	if($useCount>=100) throw new Exception("最高次数为99次！");
	$ret = array();
	$ret[]=$gid;
	$cnt = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='$gid'");
	$cid = sql_fetch_one_cell("select lastcid from sys_user where uid='$uid'");
	if (empty($cnt))
	{
		if($gid>110000&&$gid<160000)
		{
			throw new Exception($GLOBALS['useGoods']['no_this_wuhun']);
		}
		if ((($gid == 2)||($gid == 44))
		|| (($gid == 3)||($gid == 45))
		|| (($gid == 4)||($gid == 46))
		|| (($gid == 5)||($gid == 47))
		|| ($gid==54||$gid==55)
		|| $gid == 57||$gid==25||$gid==6||$gid==48||$gid==7||$gid==49||$gid==60||$gid==61||$gid==62
		|| $gid == 58||$gid==140
		|| $gid == 56  || $gid == 142
		|| $gid == 124 || $gid == 117
		|| $gid == 120|| $gid == 15 || $gid == 133 || $gid ==134 || $gid ==154||$gid==164||$gid==166||$gid==165) {
			throw new Exception("not_enough_goods$gid");
		}
		else
		{
			throw new Exception($GLOBALS['useGoods']['no_this_good']);
		}
	}
	
	$gidArr = array(2,3,4,5,6,7,25,85,86,87,88,89,90,91,92,93,94,112,118,1001,1002,1003,10333,165,166,10957,10958,10959);   //开放批量使用的gid
	if($useCount>1)
	{
		//if(($gid<40000||$gid>50000)&&!in_array($gid,$gidArr)) throw new Exception("当前道具没有开启多开功能！");
	}  
	
	
	if ($gid == 1)
	{
		throw new Exception($GLOBALS['useGoods']['acoustic_used_in_world_channel']);
	}
	else if (($gid == 2)||($gid == 44)) //神农锄    粮食产量增加25%，持续24小时。
	{
		$endtime = useShenNongChu($uid,$gid,$useCount);
		$ret[] = 2; //代表弹出剩余时间提示框
		$ret[] = $GLOBALS['useGoods']['ShenNongChu_valid_date'];
		$ret[] = intval($endtime);
	}
	else if (($gid == 3)||($gid == 45)) //鲁班斧    木材产量增加25%，持续24小时。
	{
		$endtime = useLuBanFu($uid,$gid,$useCount);
		$ret[] = 2; //代表弹出剩余时间提示框
		$ret[] = $GLOBALS['useGoods']['LuBanFu_valid_date'];
		$ret[] = intval($endtime);
	}
	else if (($gid == 4)||($gid == 46)) //开山锤    石料产量增加25%，持续24小时。  
	{
		$endtime = useKaiShanCui($uid,$gid,$useCount);
		$ret[] = 2; //代表弹出剩余时间提示框
		$ret[] = $GLOBALS['useGoods']['KaiShanCui_valid_date'];
		$ret[] = intval($endtime);
	}
	else if (($gid == 5)||($gid == 47)) //玄铁炉    铁锭产量增加25%，持续24小时。    
	{
		$endtime = useXuanTieLu($uid,$gid,$useCount);
		$ret[] = 2; //代表弹出剩余时间提示框
		$ret[] = $GLOBALS['useGoods']['XuanTieLu_valid_date'];
		$ret[] = intval($endtime);
	}
	else if (($gid == 6)||($gid == 48))
	{
		$endtime = useXianZhenZhaoGu($uid,$gid);
		$ret[] = 2; //代表弹出剩余时间提示框
		if($gid==10084) $ret[] = $GLOBALS['useGoods']['XianZhenZhaoGu_qiang_date'];
		else $ret[] = $GLOBALS['useGoods']['XianZhenZhaoGu_valid_date'];
		$ret[] = intval($endtime);
	}
	else if (($gid == 161501))
	{
		$endtime = userXianDiZhaoShu($uid,$gid);
		$ret[] = 2; //代表弹出剩余时间提示框
		$ret[] =$GLOBALS['useGoods']['Xiandizhaoshu_valid_date'];
		$ret[] = intval($endtime);
	}
	else if (($gid == 7)||($gid == 49)||($gid==10085)) //八阵图
	{
		$endtime = useBaGuaZhenTu($uid,$gid,$useCount);
		$ret[] = 2; //代表弹出剩余时间提示框
		if($gid==10085) $ret[] = $GLOBALS['useGoods']['BaGuaZhenTu_qiang_date'];
		else $ret[] = $GLOBALS['useGoods']['BaGuaZhenTu_valid_date'];
		$ret[] = intval($endtime);
	}
	else if ($gid == 8)   //墨家残卷
	{
		throw new Exception($GLOBALS['useGoods']['MoJiaCanJuan']);
	}
	else if ($gid == 9)
	{
		throw new Exception($GLOBALS['useGoods']['MojiaTuZhi']);
	}
	else if ($gid == 10)
	{
		throw new Exception($GLOBALS['useGoods']['MoJiaDianJi']);
	}
	else if ($gid == 12)
	{
		//throw new Exception($GLOBALS['useGoods']['MianZhanPai']);
		$endtime = UseMianZhanPai($uid,1);
		$ret[] = 2; //代表弹出剩余时间提示框
		$ret[] = $GLOBALS['useGoods']['MianZhanPai_valid_date'];
		$ret[] = intval($endtime);
	}
	else if ($gid == 13)
	{
		throw new Exception($GLOBALS['useGoods']['JinNang']);
	}
	else if ($gid == -100)
	{
		$msg="恭喜你获得".useaddyuanbao($uid)."元宝";
		$ret[] = 0; //代表弹出信息框
		$ret[] = $msg;
		//$ret[] = $GLOBALS['useGoods']['use_yuanbao_succ'];
	}
	else if ($gid == 15)
	{
		useMenzhulin($uid,$cid);
		$ret[] = 0; //代表弹出信息框
		$ret[] = $GLOBALS['useGoods']['use_MengZhuLing_succ'];
	}
	else if ($gid == 22)  // 洗髓丹
	{
		throw new Exception($GLOBALS['useGoods']['XiSuiDan_used_for_reset_hero']);
	}
	else if ($gid == 23)    //招贤榜
	{
		throw new Exception($GLOBALS['useGoods']['ZhaoXianBang_used_for_hire_hero']);
	}
	else if (($gid == 16)||($gid == 19))    //青铜礼盒
	{
		$ret[] = 1;     //代表开礼盒
		$ret[] = useCopperBox($uid);
	}
	else if (($gid == 17)||($gid == 20))
	{
		$ret[] = 1;     //代表开礼盒
		$ret[] = useSilverBox($uid);
	}
	else if (($gid == 18)||($gid == 21))
	{
		$ret[] = 1;     //代表开礼盒
		$ret[] = useGoldBox($uid);
	}
	else if ($gid == 25)	//青囊书
	{
		$endtime = useQingNangShu($uid,$gid,$useCount);
		$ret[] = 2; //代表弹出剩余时间提示框
		$ret[] = $GLOBALS['useGoods']['QingNangShu_valid_date'];
		$ret[] = intval($endtime);
	}
	else if ($gid==165 )	//高级青囊书
	{
		$endtime = useQingNangShu($uid,$gid,$useCount);
		$ret[] = 2; //代表弹出剩余时间提示框
		$ret[] = $GLOBALS['useGoods']['QingNangShu_valid_date_2'];
		$ret[] = intval($endtime);
	}
	else if (($gid >=41)&&($gid<=43)) //珠宝盒
	{
		$ret[]=1;
		$ret[]=openGemBox($uid,$gid);
	}
	else if (($gid ==10428)) //玄冰匣
	{
		$ret[]=1;
		$ret[]=openXuanBinBox($uid,$gid);
	}
	else if ($gid >40000 && $gid < 50000) //技能盒子
	{
		$ret[]=0;
		$ret[]=addSkillBook($uid,$gid,$useCount);
	}
	else if (($gid ==10487)) //竹编箱子
	{
		$ret[]=1;
		$ret[]=openXuanBinBox($uid,$gid);
	}
	else if ($gid == 50) //古朴木盒
	{
		$ret[] = 1; //代表显示宝物列表
		$ret[] = useOldWoodBox($uid);
	}
	else if ($gid==51) //清仓令
	{
		$endtime=useQingCangLing($uid);
		$ret[]=2;
		$ret[]=$GLOBALS['useGoods']['QingCangLing_valid_date'];
		$ret[]=intval($endtime);
	}
	else if ($gid==52) //墨家秘笈
	{
		throw new Exception($GLOBALS['useGoods']['MoJiaMiJi']);
	}
	else if ($gid==54||$gid==55) //税吏鞭
	{
		$endtime = useShuiLiBian($uid,$gid);
		$ret[] = 2; //代表弹出剩余时间提示框
		$ret[] = $GLOBALS['useGoods']['ShuiLiBian_valid_date'];
		$ret[] = intval($endtime);
	}
	else if ($gid==56) //徭役令
	{
		$endtime = useYaoYiLin($uid);
		$ret[] = 2; //代表弹出剩余时间提示框
		$ret[] = $GLOBALS['useGoods']['YaoYiLin_valid_date'];
		$ret[] = intval($endtime);
	}
	else if ($gid==166) //徭役令
	{
		$endtime = useYaoYiLinGaoJi($uid,$gid,$useCount);
		$ret[] = 2; //代表弹出剩余时间提示框
		$ret[] = $GLOBALS['useGoods']['YaoYiLin_valid_date_2'];
		$ret[] = intval($endtime);
	}
	else if ($gid==10321) //募兵令
	{
		$endtime = useMuBingLing($uid);
		$ret[] = 2; //代表弹出剩余时间提示框
		$ret[] = $GLOBALS['useGoods']['MuBingLing_valid_date'];
		$ret[] = intval($endtime);
	}else if ($gid==19061) //南蛮战书
	{
		getOutTroops($uid,$cid,$gid);
		$ret[] = 0; 
		$ret[] = $GLOBALS['useGoods']['NanManZhanShu_use_succ'];
	}else if ($gid==19062) //山越战书
	{
		getOutTroops($uid,$cid,$gid);
		$ret[] = 0; 
		$ret[] = $GLOBALS['useGoods']['ShanYueZhanShu_use_succ'];
	}else if ($gid==19063) //匈奴战书
	{
		getOutTroops($uid,$cid,$gid);
		$ret[] = 0; 
		$ret[] = $GLOBALS['useGoods']['XiongNuZhanShu_use_succ'];
	}else if($gid==10931)  //王者兵符
	{
		$msg = useWangZheBingFu($uid,$cid,$gid);
		$ret[]=0;
		$ret[]=$msg;
	}else if(($gid>=10932 && $gid<=10937) ||$gid==10996 ||($gid>=11021&&$gid<=11022)||$gid==11078)  //城池换皮
	{
		$msg = changeCityMap($uid,$cid,$gid);
		$ret[]=0;
		$ret[]=$msg;
	}else if($gid>=10957&&$gid<=10959)  //成长卷
	{
		$msg = useAddUserHeroExpBook($uid,$cid,$gid,$useCount);
		$ret[]=0;
		$ret[]=$msg;
	}
	else if ($gid==133) //军令状
	{
		$endtime = useJunLingZhuang($uid);
		$ret[] = 2; //代表弹出剩余时间提示框
		$ret[] = $GLOBALS['useGoods']['Junlingzhuang_valid_date'];
		$ret[] = intval($endtime);
	}else if ($gid==117) //高级推恩令
	{
		$tuienling=useGaojiTuiEnLing($uid,$gid);
		$endtime = $tuienling[1];
		$ret[] = 4; //代表弹出剩余时间提示框
		$ret[] = intval($endtime);
		$ret[] = $tuienling[0];
	}
	else if ($gid==124) //推恩令
	{
		$tuienling=useTuiEnLing($uid,$gid);
		$endtime = $tuienling[1];
		$ret[] = 5; //代表弹出剩余时间提示框
		$ret[] = intval($endtime);
		$ret[] = $tuienling[0];
	}
	else if ($gid==119) //宝藏盒
	{
		$ret[] = 1; //代表显示宝物列表
		$ret[] = useTreasureBox($uid,$gid);
	}
	else if ($gid==120) //商队契约
	{
		$endtime = useShangDuiQiYue($uid,$gid);
		$ret[] = 2; //代表弹出剩余时间提示框
		$ret[] = $GLOBALS['useGoods']['ShangDuiQiYue_valid_date'];
		$ret[] = intval($endtime);
	}
	else if ($gid==57) //典民令
	{
		$msg=useDianMinLin($uid,$cid);
		$ret[] = 0; //代表弹出信息框
		$ret[] = $msg;
	}
	else if ($gid==139) //典民令
	{
		$msg=useTaiPingYaoShu($uid,$cid);
		$ret[] = 0; //代表弹出信息框
		$ret[] = $msg;
	}
	else if($gid==164){//使用沙场令
		$msg=$GLOBALS['joinshachang']['shachanglinsucc'];
		$ret[]=0;
		$ret[]=$msg;
		sql_query ( "update sys_sc_user set `remain`=5 where uid=$uid" );
		reduceGoods($uid,$gid,1);
	}
	else if ($gid==142) //巡查令
	{
		$endtime=useXunChaLin($uid,$cid);
		$ret[] = 2; //代表弹出剩余时间提示框
		$ret[] = $GLOBALS['useGoods']['xuncha_valid_date'];
		$ret[] = intval($endtime);
	}
	else if ($gid==58) //安民告示
	{
		$msg=useAnMingGaoShi($uid,$cid);
		$ret[] = 0; //代表弹出信息框
		$ret[] = $msg;
	}
	else if($gid==59) //军旗
	{
		throw new Exception($GLOBALS['useGoods']['JunQi_used_for_army']);
	}
	else if (($gid==60)||($gid==61)||($gid==62)) //三个考工记
	{
		$endtime=useKaoGongJi($uid,$gid);
		$name=sql_fetch_one_cell("select name from cfg_goods where gid='$gid'");
		$ret[]=2;
		$ret[]=sprintf($GLOBALS['useGoods']['KaoGongJi_valid_date'],$name);
		$ret[]=intval($endtime);
	}
	else if($gid==63)
	{
		throw new Exception($GLOBALS['useGoods']['HanXinSanPian_used_for_army']);
	}
	else if ($gid==64)
	{
		throw new Exception($GLOBALS['useGoods']['BeiChengMen_used_for_army']);
	}
	else if($gid==85||$gid==86)	//金砖、金条
	{
		$msg=openGoldBar($uid,$cid,$gid,$useCount);
		$ret[] = 0; //代表弹出信息框
		$ret[] = $msg;
	}
	else if($gid>=87&&$gid<=94) //辎重包、辎重箱
	{
		$msg=openResBox($uid,$cid,$gid,$useCount);
		$ret[] = 0; //代表弹出信息框
		$ret[] = $msg;
	}
	else if ($gid==134) //赦免文书
	{

		$msg=useSheMianWenShu($uid,$cid);
		$ret[] = 0; //代表弹出信息框
		$ret[] = $msg;
	}else if ($gid==158) //誓师文书
	{

		$msg=useShiShiWenShu($uid,$cid);
		$ret[] = 0; //代表弹出信息框
		$ret[] = $msg;

	}else if ($gid==138) //请战书
	{
		$msg=useQingZhanShu($uid,$gid);
		$ret[] = 0; //代表弹出信息框
		$ret[] = $msg;
	}
	else if($gid==95||$gid==96||$gid==97||($gid>=101&&$gid<=112)) //装备箱
	{
		$ret[]=3; //代表开出装备
		$ret[]=useArmorBox($uid,$gid);
	}
	else if($gid==145 || $gid==146){ //武器架
		$count = addArmorShelf($uid, $gid);
		$ret[] = 0; //代表弹出信息框
		$ret[] = sprintf($GLOBALS['goods']['armor_column_add'], $count);
	}
	else if($gid>110000 && $gid <160000){ //武魂
		//$hid=$param[2];
		$ret[] = 0; //代表弹出信息框		
		$ret[] = useWuHun($uid, $hid,$gid);
		$ret[] = $hid;
	}	
	else if ($gid == 10013)
	{
		$ret[] = 0; //直接显示框
		$ret[] = useResourcePackage($uid,$gid);
	}
	else if ($gid == 10017) //钥匙链
	{
		$ret[] = 1;
		$ret[] = useKeyChain($uid);
	}	
	else if($gid==121||$gid==122)
	{
		$msg=useXiuJiaFu($uid,$cid,$gid);
		$ret[] = 0; //代表弹出信息框
		$ret[] = $msg;
	}
	else if($gid>=50101 && $gid<=50110 || $gid>=1000001 && $gid<=1000010){
		if($gid>=1000001){
			$need_level = $gid-1000000;
		}else {
			$need_level = $gid-50100;
		}
		$government_level = sql_fetch_one_cell("select max(b.level) as level from sys_building b,sys_city c where b.cid=c.cid and c.uid='$uid' and b.bid=".ID_BUILDING_GOVERMENT);
		//$government_level = sql_fetch_one_cell("select level from sys_building where cid='$cid' and bid=".ID_BUILDING_GOVERMENT);		
		if ($government_level < $need_level){
			$need_name = sql_fetch_one_cell("select name from cfg_goods where gid='$gid' limit 1");
			throw new Exception(sprintf($GLOBALS['useGiftGoods']['govenment_lessThen_needlevel'],$need_level,$need_name));
		}
		$ret[]=1;
		$ret[]= openDynamicBox($uid,$gid);
		if($gid==50104)completeTaskWithTaskid($uid,420526);
		if($gid==50106)completeTaskWithTaskid($uid,420527);
		if($gid==50108)completeTaskWithTaskid($uid,420528);
		if($gid==50110)completeTaskWithTaskid($uid,420529);
	}
	else if ($gid>=50000 && $gid<=60000)
	{
		$ret[]=1;
		$ret[]=openDynamicBox($uid,$gid);
	}
	else if ($gid == 147) //神秘传音符
	{
		$ret[]=6;
		$ret[]=useShenMiChuanYinFu($uid);
	}
	else if ($gid == 150) // 答题礼包
	{
		$ret[]=1;
		$ret[]=useDaTiLiBao($uid);
	}
	else if ($gid == 153) //宝石箱
	{
		$ret[]=1;
		$ret[]=openBaoshiBox($uid,$gid);
	}
	else if ($gid == 200) //宝珠盒
	{
		$ret[]=1;
		$ret[]=openBaozhuhe($uid,$gid);
	}
	else if ($gid ==154)//求贤诏
	{
		$endtime = useQiuXianZhao($uid);
		$ret[] = 2; //代表弹出剩余时间提示框
		$ret[] = $GLOBALS['useGoods']['QiuXianZhao_valid_date'];
		$ret[] = intval($endtime);
	}
	else if ($gid == 156) //自荐状
	{
		$ret[] = 0;
		$ret[] = openHeroBox_taskreward($uid);
	}else if ($gid == 10215) //热血勇士召唤令
	{
		$ret[] = 0;
		$ret[] = openHeroBox_actreward($uid);
	}else if($gid == 11227)   //统领召唤令
	{
		$ret[] = 0;
		$ret[] = openHeroBox_tongling($uid);
	}else if($gid == 11228)   //侍卫召唤令
	{
		$ret[] = 0;
		$ret[] = openHeroBox_shiwei($uid);
	}else if($gid == 12051)  //热血家将
	{
		$ret[] = 0;
		$ret[] = openHeroBox_jiajiang($uid);
	}
	else if ($gid==10158) //高级建筑图纸
	{
		$endtime = useAdvancedConstructionPlan($uid,$cid,$gid);
		$ret[] = 2; //代表弹出剩余时间提示框
		$ret[] = $GLOBALS['useGoods']['AdvancedConstructionPlan'];
		$ret[] = intval($endtime);
	}
	else if($gid==250){
		//名将卡
		$ret[]=0;
		$ret[]=giveMeHeroCard($uid,$gid);
	}else if($gid>210000 && $gid<250000){
		//卡片名将
		$ret[]=0;
		$ret[]=generateHero4Card($uid,$cid,$gid);
	}else if($gid==251 || $gid==252 || $gid==253 || $gid==254){
		$ret[]=0;
		$ret[]=useArmyOrder($uid,$cid,$gid);
	} else if ($gid == 14) {//盟主密诏
		useMenZhuMiZhao($uid);
		$ret[] = 0; //代表弹出信息框
		$ret[] = $GLOBALS['useGoods']['use_MengZhuMiZhao_succ'];
	}else if ((($gid > 10000)&&($gid <= 10012))||($gid == 10072)||($gid == 10016)||(($gid>=10018)&&($gid<=10024)))
	{
		$ret[] = 1; //代表显示宝物列表
		$ret[] = useHuodongGoods($uid,$gid);
	}else if($gid==10014||$gid==10015||$gid==10083||$gid==10270||$gid==10279||$gid==10282||$gid == 10254 || ($gid >=10035 && $gid<=10056))
	{
		throw new Exception ("这个道具已过期" );
	}else if ($gid==10303||$gid==10304){
		$ret[]=1;
		$ret[]=openEggBox($uid,$gid);
		
	}else if ($gid==10308||$gid==10417){
		$my_count=sql_fetch_one_cell("select count from sys_goods where gid=$gid and count>0 and uid=$uid");
		if($my_count){
			$my_rank=sql_fetch_one_cell("select count(1)+1 from sys_goods where gid=$gid and count>'$my_count'");
			$my_prestige=sql_fetch_one_cell("select prestige from sys_user where uid=$uid");
			$same_rank=sql_fetch_one_cell("select count(1) from sys_goods where gid=$gid and count='$my_count'");
			if($same_rank>1){//如果有并列的 则按声望排 
				$my_prestige=sql_fetch_one_cell("select prestige from sys_user where uid=$uid");
				$my_rank_add=sql_fetch_one_cell("select count(1) from sys_goods aa left join sys_user bb on aa.uid=bb.uid  where gid=$gid and count='$my_count' and prestige>$my_prestige");
				$my_rank+=$my_rank_add;
			}
		}
		$goodlist100=sql_fetch_rows("select count from sys_goods aa left join sys_user bb on aa.uid=bb.uid where gid=$gid and count>0 order by count desc ,prestige desc limit 100");
		$rank10_count=$goodlist100[9]['count'];
		$rank20_count=$goodlist100[19]['count'];
		$rank50_count=$goodlist100[49]['count'];
		$rank100_count=$goodlist100[99]['count'];
		if(!$my_count) $my_count='--';
		if(!$my_rank) $my_rank='--';
		if(!$rank10_count) $rank10_count='--';
		if(!$rank20_count) $rank20_count='--';
		if(!$rank50_count) $rank50_count='--';
		if(!$rank100_count) $rank100_count='--';
		$rankmsg="我当前排名：%s;\n第10名拥有个数：%s;\n第20名拥有个数：%s;\n第50名拥有个数：%s;第10名拥有个数：%s;";
		$rankmsg=sprintf($rankmsg,$my_rank,$rank10_count,$rank20_count,$rank50_count,$rank100_count);
		throw new Exception($rankmsg);
		
	}else if ($gid==10314){
		addGoods($uid,10314,-1,6);
		if(isLucky(30,100)){
			addGoods($uid,10312,1,6);
			$name=sql_fetch_one_cell("select name from sys_user where uid=$uid");
			$msg="恭喜玩家%s打开万圣节面具获得了万圣节的糖果1";
			$msg=sprintf($msg,$name);
			sql_query("insert into sys_inform (`type`,`inuse`,`starttime`,`endtime`,`interval`,`scrollcount`,`color`,`msg`) values (0,1,unix_timestamp(),unix_timestamp()+600,50000,1,49151,'$msg')");
			$ret[]=1;
			$arr[]= sql_fetch_one ( "select *,1 as count,value from cfg_goods where gid=10312" );
			$ret[]=$arr;
		}else{
			throw new Exception("恭喜您被万圣节恶魔整蛊了，这个面具是假的！");
		}
	}else if ($gid==10333){
		$endtime = useZhaoAnLing($uid,$gid,$useCount);
		$ret[] = 2; //代表弹出剩余时间提示框
		$ret[] = $GLOBALS['useGoods']['ZhaoAnLing_valid_date'];
		$ret[] = intval($endtime);
		
	}else if($gid==1015){//武魂碎片礼盒
		$ret[]=1;
		$ret[]=openSuiPianHe($uid,$gid);
	} elseif ($gid == 10816){//王者之城
		userWangZheZhiCheng($uid,$gid,$cid);
		$ret[]=0;
		$ret[]=$GLOBALS['good']['use_good_succ'];
	} else if($gid==1016){
		$ret[]=1;
		$ret[]=openBazhuHe($uid,$gid);
	}else if($gid>=200000&&$gid<=210000){
		$ret[]=1;
		$ret[]=openCombineArmorBox($uid,$gid);
	}else if($gid>=8887 && $gid<=8892){
		$ret[]=1;
		if($gid<8891)
		  $msg=openlongyuanArmorBox($uid,$gid);
		else
		  $msg=openlongyuantieArmorBox($uid,$gid);
		throw new Exception($msg);
	}else if($gid==8893 || $gid==8894){
		$ret[]=1;
		$msg=openunionandbattleBox($uid,$gid);
		throw new Exception($msg);
	}
	else{ //默认打开道具，一般为活动道具
		$ret[]=1;
		$ret[]=openDefaultBox($uid,$cid,$gid,0);
	}
	/*else
	 {
		throw new Exception($GLOBALS['useGoods']['func_not_in_use']);
		}*/
	return $ret;
}

function sellGoods($uid, $param) {
	$cid = intval(array_shift ( $param ));
	$gid = intval(array_shift ( $param ));
	$goodsCount = intval(array_shift ( $param ));
	checkForSell($uid,$gid);
	$level = sql_fetch_one_cell ( "select level from sys_building where cid='$cid' and bid=" . ID_BUILDING_MARKET );
	if ($level < 5) {
		throw new Exception ( $GLOBALS ['sellGoods'] ['building_level'] );
	}
	$nobility = sql_fetch_one_cell ( "select nobility from sys_user where uid='$uid'" );
	//推恩 
	$nobility = getBufferNobility ( $uid, $nobility );
	if ($nobility < 1) {
		throw new Exception ( $GLOBALS ['sellGoods'] ['nobility_low'] );
	}

	$goldAdd = intval ( sql_fetch_one_cell ( "select value from cfg_goods where gid='$gid'" ) );
	$goldAdd = $goldAdd * 500 * $goodsCount;
	$myCount = intval ( sql_fetch_one_cell ( "select count from sys_goods where uid='$uid' and gid='$gid'" ) );
	if ($goodsCount <= 0 || $myCount < $goodsCount) {
		throw new Exception ( $GLOBALS ['sellGoods'] ['not_enough_goods'] );
	}

	sql_query ( "update mem_city_resource set `gold`=`gold`+$goldAdd where cid='$cid'" );
	reduceGoods ( $uid, $gid, $goodsCount, 9 );
	$ret = array ();
	$ret [] = sql_fetch_one ( "select * from sys_goods g left join cfg_goods f on f.gid=g.gid where g.uid='$uid' and g.gid='$gid'" );
	$ret [] = $cid;
	$ret [] = sql_fetch_one_cell ( "select gold from mem_city_resource where cid='$cid'" );
	return $ret;
}

//检测这个物品是否可以回收的，如果不回收的就抛异常
function checkForSell($uid,$gid){
	if($gid==160052){
		throw new Exception ($GLOBALS['sellGoods']['not_for_sell']);
	}
//	if($gid>=160026&&$gid<=160052){
//		throw new Exception ($GLOBALS['sellGoods']['not_for_sell']);
//	}
}

function getUserTypeGoods($uid, $param) {
	$type = array_shift ( $param );
	if ($type == 0)
	return getLuBanGoods ( $uid, $param );
	else if ($type == 1) {
		return getMojiaGoods ( $uid, $param );
	}
	else if ($type > 110000 && $type < 160000)
	{
		$ret = array ();
		$gids = "";
		$gids=$type;
		$ret [] = sql_fetch_rows ( "select c.*,g.count from cfg_goods c left join sys_goods g on g.gid=c.gid and g.uid='$uid' where c.gid in ($gids) order by c.value" );
		return $ret;
	}
	else {
		$ret = array ();
		$gids = "";
		switch ($type) {
			case 2 : //体力药品
				$gids = "74,75,76,77";//,10150暂不上
				break;
			case 3 : //精力药品
				$gids = "78,79,80,81";//,10151暂不上
				break;
			case 4 : //民心
				$gids = "58";
				break;
			case 5 : //黄金
				$gids = "54,55";
				break;
			case 6 : //人口
				$gids = "57";
				break;
			case 7 : //粮食
				$gids = "2,44";
				break;
			case 8 : //木材
				$gids = "3,45";
				break;
			case 9 : //石料
				$gids = "4,46";
				break;
			case 10 : //铁锭
				$gids = "5,47";
				break;
			case 11 : //武将加经验
				$gids = "113,114,115";
				break;
			case 12 : //12,陷阵战鼓
				$gids = "6,48";
				break;
			case 13 : //13八卦阵图
				$gids = "7,49";
				break;
			case 14 : //14,青曩书
				$gids = "25";
				break;
			case 15 : //15清仓令
				$gids = "51";
				break;
			case 16 : //16徭役令
				$gids = "56";
				break;
			case 17 : //17考工记1
				$gids = "60";
				break;
			case 18 : //18考工记2
				$gids = "61";
				break;
			case 19 : //19考工记3
				$gids = "62";
				break;
			case 20 : // 20 高级推恩令
				$gids = "117";
				break;
			case 21 : // 20 商队契约
				$gids = "120";
				break;
			case 22 : // 20 推恩令
				$gids = "124";
				break;
			case 23 : // 23 军令状
				$gids = "133";
				break;
			case 24 : //打孔器
				$gids = "206,207";
				break;
			case 25 : //高级打孔器
				$gids = "207";
				break;
			case 26 :
				$gids = "201,12156";
				break;		
			case 27:
				$gids = "125,63";
				break;
			case 28:  //特级金刚钻
				$gids ="10840";
				break;
			case 30 : //武器架
				$gids = "145, 146";
				break;
			case 142 : //巡查令
				$gids = "142";
				break;
			case 143 : //免战牌
				$gids = "12";
				break;
			case 145 : //君主将加经验
				$gids = "10957,10958,10959";
				break;
				//活动道具
			case 10083 :
			case 10084 :
			case 10085 :
				$gids = $type;
				break;
			case 166 :
				$gids="166";
				break;
			case -16656:
				$gids="166,56";
				break;
			case 42 : //所有推恩令
				$gids = "124,117";
				break;
			case 43 : //虎符				
				$gids = "26,10152,160040,160041,160042,19204";
				break;
			case 44 : //文曲星符
				$gids = "27,10153,160043,160044,160045,19203";
				break;
			case 45 : //武曲星符
				$gids = "28,10155,160049,160050,160051,19202";
				break;
			case 46 : //智多星符
				$gids = "29,10154,160046,160047,160048,19205";
				break;
//			case 43 : //虎符		
//				$gids = "26,10152";
//				break;
//			case 44 : //文曲星符
//				$gids = "27,10153";
//				break;
//			case 45 : //武曲星符
//				$gids = "28,10155";
//				break;
//			case 46 : //智多星符
//				$gids = "29,10154";
//				break;
			case 47 : //盟主令
				$gids = "15";
				break;
			case 48 ://赦免文书
				$gids = "134";
				break;
			case 49 ://护命金丹
				$gids = "151";
				break;
			case 50 ://求贤诏
				$gids = "154";
				break;
			case 51 ://誓师文书
				$gids = "158";
				break;
			case 52://上品陷阵战鼓，中品陷阵战鼓，下品陷阵战鼓，增加攻击的
				$gids = "160029,160030,160031";
				break;
			case 53 ://上品八封阵图，中品八封阵图，下品八封阵图，增加防御的
				$gids = "160026,160027,160028";
				break;
			case 54 ://加速行军的东西
				$gids = "160019,160022";
				break;
			default:
				$gids = $type;
					
		}
		$ret [] = sql_fetch_rows ( "select c.*,g.count from cfg_goods c left join sys_goods g on g.gid=c.gid and g.uid='$uid' where c.gid in ($gids) order by c.value" );
		return $ret;
	}
}

function useTypeGoods($uid, $param) {
	$type = intval(array_shift ( $param ));
	$gid = intval(array_shift ( $param ));
	$cid = intval(array_shift ( $param ));
	if($type>110000&&$type<160000)//武魂
	{
		$hid = array_shift ( $param );
		$param2 = array ();
		$param2 [] = $gid;
		$param2 [] = $hid;
		return useGoods ( $uid, $param2 );
	}
	switch ($type) {
		case 0 :
			return useLuBanGoods ( $uid, $cid, $gid, $param );
			break;
		case 1 :
			return useMojiaGoods ( $uid, $cid, $gid, $param );
			break;
		case 2 :
			return useForceGoods ( $uid, $cid, $gid, $param );
			break;
		case 3 :
			return useEnergyGoods ( $uid, $cid, $gid, $param );
			break;
		case 4 :
		case 5 :
		case 6 :
		case 7 :
		case 8 :
		case 9 :
		case 10 :
		case 142 :
		case 143 :
			$param2 = array ();
			$param2 [] = $gid;
			return useGoods ( $uid, $param2 );
			break;
		case 11 :
			return useAddHeroExpBook ( $uid, $cid, $gid, $param );
			break;
		case 145 :
			useAddUserHeroExpBook($uid,$cid,$gid,1);
			$retTmp = array();
			$retTmp[] = getCityInfoHero($uid, $cid);
			return $retTmp;
			break;
		case 12 : //12,陷阵战鼓
		case 13 : //13八卦阵图
		case 14 : //14,青曩书
		case 15 : //15清仓令
		case 16 : //16徭役令
		case 17 : //17考工记1
		case 18 : //18考工记2
		case 19 : //19考工记3
		case 10333 : //诏安令
		case 161501 : //献帝诏书
		case 165 : //高级青囊书
			$param2 = array ();
			$param2 [] = $gid;
			return useGoods ( $uid, $param2 );
			break;
		case 20 : //20 高级推恩令
			$param2 = array ();
			$param2 [] = $gid;
			return useGoods ( $uid, $param2 );
			break;
		case 21 : //21 商队契约
			$param2 = array ();
			$param2 [] = $gid;
			return useGoods ( $uid, $param2 );
			break;
		case 22 : //21 推恩令
			$param2 = array ();
			$param2 [] = $gid;
			return useGoods ( $uid, $param2 );
			break;
		case 23 : //23 军令状
			$param2 = array ();
			$param2 [] = $gid;
			return useGoods ( $uid, $param2 );
			break;
		case 24 : //打孔器
		case 25 :
		case 26 : //化石粉
		case 28 : //特级金刚钻
			$param2 = array ();
			$param2 [] = array_shift ( $param ); //armor sid
			$param2 [] = $gid;
			$param2 [] = array_shift ( $param ); //position
			$param2 [] = array_shift ( $param ); //useType
			$param2 [] = array_shift ( $param ); //count
			return openHole ( $uid, $param2 );
			break;
		case 30 : //武器架
			$count = addArmorShelf ( $uid, $gid );
			$ret = array ();
			$ret [] = $count;
			return $ret;
			break;
			//活动道具
		case 10083 :
		case 10084 :
		case 10085 :
			$param2 = array ();
			$param2 [] = $gid;
			return useGoods ( $uid, $param2 );
			break;
		case 42 : //所有推恩令	
			$param2 = array ();
			$param2 [] = $gid;
			return useGoods ( $uid, $param2 );
			break;
		case 43 : //虎符
			$hid = array_shift ( $param );
			$param2 = array ();
			$param2 [] = $gid;
			return;
			break;
		case 49: //护命金丹
			return useBigForceGoods ( $uid, $cid, $gid, $param );
			break;
		case 48://盟主令
			$param2 = array ();
			$param2 [] = $gid;
			return useGoods ( $uid, $param2 );
			break;
		case 47://赦免文书
			$param2 = array ();
			$param2 [] = $gid;
			return useGoods ( $uid, $param2 );
			break;
		case 51://誓师文书
			$param2 = array ();
			$param2 [] = $gid;
			return useGoods ( $uid, $param2 );
			break;
		case 50://求贤诏
			$param2 = array ();
			$param2 [] = $gid;
			return useGoods ( $uid, $param2 );
			break;
		case 166://求贤诏
			{
				$param2 = array ();
				$param2 [] = $gid;
				return useGoods ( $uid, $param2 );
				break;
			}
		case -16656://徭役令
			{
				$param2 = array ();
				$param2 [] = $gid;
				return useGoods ( $uid, $param2 );
				break;
			}
/*		case 11178://拆卸符
			{
				$param2=array();
				$param2[]=array_shift($param);//sid
				$param2[]=array_shift($param);//position
				return dismantlePearl($uid,$param2);				
			}*/
	}
}



function getMojiaGoods($uid, $param) {
	$timeleft = intval ( array_shift ( $param ) );
	if ($timeleft < 0)
	$timeleft = 0;
	$ret = array ();
	$passtype = sql_fetch_one_cell("select passtype from sys_user where uid='$uid'");
	if ($passtype==='tw') {
		$ret[]=intval(floor($timeleft*1.8/3600)+15);
	}else{
		$ret [] = floor ( ($timeleft * 5 / 3600) + 30 );
	}
	$goodsList = sql_fetch_rows ( "select c.*,g.count from cfg_goods c left join sys_goods g on g.gid=c.gid and g.uid='$uid' where c.gid in (8,9,10,52,53,65,66) order by c.value" );
	$finalList = array ();
	foreach ( $goodsList as $goods ) {
		if (empty ( $goods ['count'] )) {
			//if($goods['gid']!=10)
			{
				$goods ['count'] = 0;
				if ($goods ['gid'] == 53) {
					$goods ['count'] = $GLOBALS ['getMoJiaGoods'] ['complete_quickly'];
					array_unshift ( $finalList, $goods );
				} else
				$finalList [] = $goods;
			}
		} else {
			if ($goods ['gid'] == 53)
			array_unshift ( $finalList, $goods );
			else
			$finalList [] = $goods;
		}
	}
	$ret [] = $finalList;
	return $ret;
}
function useMojiaGoods($uid, $cid, $gid, $param) {
	$cid = array_shift ( $param );
	$inner = array_shift ( $param );
	$x = array_shift ( $param );
	$y = array_shift ( $param );
	$bid = array_shift ( $param );
	//$useType = array_shift ( $param ); //０：建筑，１：科技


	if (! (($gid >= 8 && $gid <= 10) || $gid == 53 || $gid == 52 || $gid == 65 || $gid == 66))
	throw new Exception ( $GLOBALS ['useMojiaGoods'] ['invalid_param'] );

	if ($gid == 53) //墨家弟子，直接完成，扣除元宝
	{
		$paytype = array_shift($param);
		$cost = 0;

		$technic = sql_fetch_one ( "select *,unix_timestamp() as nowtime from sys_technic where uid='$uid' and cid='$cid' and state=1" );
		if (empty ( $technic ))
		throw new Exception ( $GLOBALS ['useMojiaGoods'] ['no_need_to_use'] );
		$timeleft = $technic ['state_endtime'] - $technic ['nowtime'];
		if ($timeleft <= 1)
		throw new Exception ( $GLOBALS ['useMojiaGoods'] ['no_need_to_use'] );
		$passtype = sql_fetch_one_cell("select passtype from sys_user where uid='$uid'");
		if ($passtype==='tw') {
			$cost = intval(floor($timeleft*1.8/3600)+15);
		}else{
			$cost = intval ( floor ( $timeleft * 5 / 3600 ) + 30 );
		}
		if($paytype == 0) {
			$money = sql_fetch_one_cell ( "select money from sys_user where uid='$uid'" );
			if ($cost > $money)
			throw new Exception ( $GLOBALS ['sys'] ['not_enough_money'] );
		} else {
			$money = sql_fetch_one_cell ( "select gift from sys_user where uid='$uid'" );
			if ($cost > $money)
			throw new Exception ( $GLOBALS ['sys'] ['not_enough_gift'] );
		}
		sql_query ( "update sys_technic set state_endtime = unix_timestamp()-1 where id='$technic[id]'" );
		sql_query ( "update mem_technic_upgrading set state_endtime=unix_timestamp()-1 where id='$technic[id]'" );

		if ($cost > 0) {
			if($paytype == 0) {
				addMoney($uid, 0-$cost, 70);
			} else {
				addGift($uid, 0-$cost, 70);
			}
				
			//			sql_query ( "update sys_user set money=GREATEST(money-$cost,0) where uid='$uid'" );
			//			sql_query ( "insert into log_money (`uid`,`count`,`time`,`type`) values ('$uid',-$cost,unix_timestamp(),70)" );
		}
	} else {

		//查找正在建造的建筑或科技
		$technic = sql_fetch_one ( "select * from sys_technic where uid='$uid' and cid='$cid' and state=1" );
		if (empty ( $technic ))
		throw new Exception ( $GLOBALS ['useMojiaGoods'] ['technique_no_need'] );

		if (! checkGoods ( $uid, $gid ))
		throw new Exception ( "not_enough_goods$gid" );

		$timeadd = 900;
		if ($gid == 10) //墨家宝典，缩短35%
		{
			sql_query ( "update sys_technic set state_endtime=unix_timestamp() + FLOOR(0.65*(state_endtime-unix_timestamp())) where id='$technic[id]'" );
			sql_query ( "update mem_technic_upgrading set state_endtime=unix_timestamp() + FLOOR(0.65*(state_endtime-unix_timestamp())) where id='$technic[id]'" );
		} else {
			if ($gid == 8)
			$timeadd = 900; //墨家残卷
			else if ($gid == 9)
			$timeadd = 10800; //墨家图纸,3小时
			else if ($gid == 52) //墨家秘笈，随机缩短6-30小时
			{
				$valrange = mt_rand ( 1000, 2000 );
				if ($valrange < 1500) //50%落在12-16小时
				{
					$timeadd = mt_rand ( 12, 16 );
				} else if ($valrange < 1800) //30%落在16-24小时
				{
					$timeadd = mt_rand ( 16, 24 );
				} else if ($valrange < 1950) //10%落在24-28小时
				{
					$timeadd = mt_rand ( 24, 28 );
				} else //5%落在28-36小时
				{
					$timeadd = mt_rand ( 28, 36 );
				}
				$timeadd = $timeadd * 3600;
			} else if ($gid == 65)
			$timeadd = 3600; //墨家散页，减少1小时
			else if ($gid == 66)
			$timeadd = 36000; //墨家古典，减少10小时
				

			sql_query ( "update sys_technic set state_endtime = GREATEST(unix_timestamp()-1,state_endtime - $timeadd) where id='$technic[id]'" );
			sql_query ( "update mem_technic_upgrading set state_endtime=GREATEST(unix_timestamp()-1,state_endtime-$timeadd) where id='$technic[id]'" );
				
			if ($gid == 8) //墨家残卷
			{
				completeTask ( $uid, 365 );
			}
		}
		reduceGoods ( $uid, $gid, 1 );
	}
	return getCollegeInfo ( $uid, $cid );

}

function getLuBanGoods($uid, $param) {
	$timeleft = intval ( array_shift ( $param ) );
	if ($timeleft < 0)
	$timeleft = 0;
	$ret = array ();
	$passtype = sql_fetch_one_cell("select passtype from sys_user where uid='$uid'");
	if ($passtype==='tw') {
		$ret[]=floor($timeleft*1.6/3600)+12;
	}else{
		$ret [] = floor ( ($timeleft * 4.5 / 3600) + 25 );
	}
	$goodsList = sql_fetch_rows ( "select c.*,g.count from cfg_goods c left join sys_goods g on g.gid=c.gid and g.uid='$uid' where c.gid>=67 and c.gid<=73 order by c.value" );
	$finalList = array ();
	foreach ( $goodsList as $goods ) {
		if (empty ( $goods ['count'] )) {
			$goods ['count'] = 0;
			if ($goods ['gid'] == 73) {
				$goods ['count'] = $GLOBALS ['getMoJiaGoods'] ['complete_quickly'];
				array_unshift ( $finalList, $goods );
			} else
			$finalList [] = $goods;
		} else {
			if ($goods ['gid'] == 73)
			array_unshift ( $finalList, $goods );
			else
			$finalList [] = $goods;
		}
	}
	$ret [] = $finalList;
	return $ret;
}

function enhanceHero($uid, $hid,$gid){
	if($hid<=0 || $gid<10150 || ($gid >10155&&$gid<19202) || $gid>19205){
		return sprintf($GLOBALS['act']['msg_tip'],$GLOBALS['useLuBanGoods']['invalid_param']);
	}
	$count = sql_fetch_one_cell("select count from sys_goods where uid=$uid and gid=$gid");
	if($count<=0){
		return sprintf($GLOBALS['act']['msg_tip'],$GLOBALS['useLuBanGoods']['no_enough_goods']);
	}
	$heroType= sql_fetch_one_cell("select herotype from sys_city_hero where hid=$hid");
	if ($heroType == 1000) {
		throw new Exception($GLOBALS['user_hero']['cannot_use_goods']);
	}
	$type = 4;//1:历练\n2:武魂\n3:好感度超过100\n4:吃药
	$log_type = 4;
	$health = 0;
	$energy = 0;
	$brave = 0;
	$wisdon = 0;
	$affairs = 0;
	$command = 0;
	switch ($gid){
		case 10150:
			$health = 1;
			$maxLimit = 100;
			break;
		case 10151:
			$energy = 1;
			$maxLimit = 100;
			break;
		case 10152:
			$command = 1;
			$maxLimit = 50;
			break;
		case 10153:
			$affairs = 1;
			$maxLimit = 50;
			break;
		case 10154:
			$wisdon = 1;
			$maxLimit = 50;
			break;
		case 10155:
			$brave = 1;
			$maxLimit = 50;
			break;
		case 19202:
			$brave = 2;
			$maxLimit = 50;
			break;
		case 19203:
			$affairs = 2;
			$maxLimit = 50;
			break;
		case 19204:
			$command = 2;
			$maxLimit = 50;
			break;
		case 19205:
			$wisdon = 2;
			$maxLimit = 50;
			break;
		default:break;
			
	}
	//这之前将领是别人的
	$startTime = sql_fetch_one_cell("select max(time) from log_act where uid<>$uid and actid=$hid and sort=2 and type=$gid and log_type=$log_type");
	$totalUsed = sql_fetch_one_cell("select sum(count) from log_act where uid=$uid and actid=$hid and sort=2 and type=$gid and log_type=$log_type and time>=$startTime+1");
	if($totalUsed>=$maxLimit){
		return sprintf($GLOBALS['act']['msg_tip'],$GLOBALS['enhanceHero']['exceedMaxLimit']);
	}

	//	if ($health>0 || $energy>0){
	//		sql_query("update mem_hero_blood set energy_max=energy_max+$energy, force_max=force_max+$health where hid=$hid");
	//	}
	sql_query("insert into sys_city_hero_base_add (uid,hid,bravery_base_add_on,wisdom_base_add_on,affairs_base_add_on,command_base_add_on,type) values ($uid,$hid,$brave,$wisdon,$affairs,$command,$type)");
	sql_query("update sys_city_hero set bravery_base=bravery_base+$brave,wisdom_base=wisdom_base+$wisdon,affairs_base=affairs_base+$affairs,command_base=command_base+$command where hid = $hid");
	sql_query("insert into log_act (uid, actid, sort, type, count, log_type, time) values ($uid, $hid, 2, $gid, 1, $log_type, unix_timestamp())");
	addGoods ( $uid, $gid, - 1, 7 );

	regenerateHeroAttri ( $uid, $hid );
	$cid = sql_fetch_one_cell ( "select cid from sys_city where chiefhid=$hid and uid=$uid" );
	if (!empty($cid)){
		sql_query ( "update sys_city_res_add set resource_changing=1 where cid='$cid'" );
	}
}

function useLuBanGoods($uid, $cid, $gid, $param) {
	$cid = array_shift ( $param );
	$inner = array_shift ( $param );
	$x = array_shift ( $param );
	$y = array_shift ( $param );
	$bid = array_shift ( $param );
	//$useType = array_shift ( $param ); //０：建筑，１：科技


	if (! ($gid >= 67 && $gid <= 73))
	throw new Exception ( $GLOBALS ['useLuBanGoods'] ['invalid_param'] );

	if ($gid == 73) //鲁班传人，直接完成，扣除元宝
	{
		$paytype = array_shift($param);
		$cost = 0;
		$xy = encodeBuildingPosition ( $inner, $x, $y );
		$building = sql_fetch_one ( "select *,unix_timestamp() as nowtime from sys_building where cid='$cid' and bid='$bid' and xy='$xy' and state <> 0" );
		if (empty ( $building ))
		throw new Exception ( $GLOBALS ['useLuBanGoods'] ['no_need_to_use'] );
		$timeleft = $building ['state_endtime'] - $building ['nowtime'];
		if ($timeleft <= 1)
		throw new Exception ( $GLOBALS ['useLuBanGoods'] ['no_need_to_use'] );
		$passtype = sql_fetch_one_cell("select passtype from sys_user where uid='$uid'");
		if ($passtype==='tw') {
			$cost = floor($timeleft*1.6/3600)+12;
		}else{
			$cost = intval ( floor ( $timeleft * 4.5 / 3600 ) + 25 );
		}
		
		if($paytype == 0)
		{
			$money = sql_fetch_one_cell ( "select money from sys_user where uid='$uid'" );
			if ($cost > $money)
			throw new Exception ( $GLOBALS ['sys'] ['not_enough_money'] );
		} else {
			$money = sql_fetch_one_cell ( "select gift from sys_user where uid='$uid'" );
			if ($cost > $money)
			throw new Exception ( $GLOBALS ['sys'] ['not_enough_gift'] );
		}
		sql_query ( "update sys_building set state_endtime = unix_timestamp()-1 where id='$building[id]'" );
		if ($building ['state'] == 1) // upgrading
		{
			sql_query ( "update mem_building_upgrading set state_endtime=unix_timestamp()-1 where id='$building[id]'" );
		} else if ($building ['state'] == 2) //destroying
		{
			sql_query ( "update mem_building_destroying set state_endtime=unix_timestamp()-1 where id='$building[id]'" );
		}
		if ($cost > 0) {
			if($paytype == 0) {
				addMoney($uid, 0-$cost, 71);
			} else {
				addGift($uid, 0-$cost, 71);
			}
			//			sql_query ( "update sys_user set money=GREATEST(money-$cost,0) where uid='$uid'" );
			//			sql_query ( "insert into log_money (`uid`,`count`,`time`,`type`) values ('$uid',-$cost,unix_timestamp(),71)" );
		}
	} else {

		//查找正在建造的建筑或科技


		$xy = encodeBuildingPosition ( $inner, $x, $y );
		$building = sql_fetch_one ( "select * from sys_building where cid='$cid' and bid='$bid' and xy='$xy' and state <> 0" );
		if (empty ( $building ))
		throw new Exception ( $GLOBALS ['useLuBanGoods'] ['no_need_to_use'] );

		if (! checkGoods ( $uid, $gid ))
		throw new Exception ( "not_enough_goods$gid" );
		if ($gid == 72) //鲁班全集，缩短30%
		{
			sql_query ( "update sys_building set state_endtime=unix_timestamp() + FLOOR(0.7*(state_endtime-unix_timestamp())) where id='$building[id]'" );
			if ($building ['state'] == 1) // upgrading
			{
				sql_query ( "update mem_building_upgrading set state_endtime=unix_timestamp() + FLOOR(0.7*(state_endtime-unix_timestamp())) where id='$building[id]'" );
			} else if ($building ['state'] == 2) //destroying
			{
				sql_query ( "update mem_building_destroying set state_endtime=unix_timestamp() + FLOOR(0.7*(state_endtime-unix_timestamp())) where id='$building[id]'" );
			}
		} else {
			$timeadd = 900;
			if ($gid == 67)
			$timeadd = 900; //鲁班残页，15分钟
			else if ($gid == 68)
			$timeadd = 3600; //鲁班便笺，1小时
			else if ($gid == 69)
			$timeadd = 9000; //鲁班草图,2个半小时
			else if ($gid == 70)
			$timeadd = 28800; //鲁班书册,8小时
			else if ($gid == 71) //鲁班秘录随机缩短10-30小时
			{
				$valrange = mt_rand ( 1000, 2000 );
				if ($valrange < 1500) //50%落在10-15小时
				{
					$timeadd = mt_rand ( 10, 15 );
				} else if ($valrange < 1800) //30%落在15-20小时
				{
					$timeadd = mt_rand ( 15, 20 );
				} else if ($valrange < 1950) //15%落在20-25小时
				{
					$timeadd = mt_rand ( 20, 25 );
				} else //5%落在25-30小时
				{
					$timeadd = mt_rand ( 25, 30 );
				}
				$timeadd = $timeadd * 3600;
			}
			sql_query ( "update sys_building set state_endtime = GREATEST(unix_timestamp()-1,state_endtime - $timeadd) where id='$building[id]'" );
			if ($building ['state'] == 1) // upgrading
			{
				sql_query ( "update mem_building_upgrading set state_endtime=GREATEST(unix_timestamp()-1,state_endtime-$timeadd) where id='$building[id]'" );
			} else if ($building ['state'] == 2) //destroying
			{
				sql_query ( "update mem_building_destroying set state_endtime=GREATEST(unix_timestamp()-1,state_endtime-$timeadd) where id='$building[id]'" );
			}
			if ($gid == 68) //鲁班残页
			{
				completeTask ( $uid, 364 );
			}
		}
		reduceGoods ( $uid, $gid, 1 );
	}
	return getCityBuildingInfo ( $uid, $cid );
}

function useBigForceGoods($uid, $cid, $gid, $param) {
	$hid = intval(array_shift ( $param ));
	if (! checkGoods ( $uid, $gid )) {
		throw new Exception ( "not_enough_goods$gid" );
	}
	$heroState = sql_fetch_one_cell ( "select state from sys_city_hero where hid='$hid'" );
	//if($heroState>1)

	if(isHeroInCity($heroState)==0)
	{
		throw new Exception($GLOBALS['useGoods']['hero_state_wrong']);
	}
	$percent=1;
	sql_query("update mem_hero_blood set `force`=LEAST(`force`+`force_max`*$percent,`force_max`) where hid='$hid'");
	sql_query("delete from sys_hero_rest where hid='$hid'");
	sql_query("update sys_city_hero set hero_health=0 where hid='$hid'");
	reduceGoods($uid,$gid,1);
	return getCityInfoHero($uid,$cid);
}

function useForceGoods($uid, $cid, $gid, $param) {
	$hid = array_shift ( $param );
	if (! checkGoods ( $uid, $gid )) {
		throw new Exception ( "not_enough_goods$gid" );
	}
	$heroHeroState = sql_fetch_one_cell ( "select hero_health from sys_city_hero where hid='$hid'" );
	if($heroHeroState!=0)
	{
		throw new Exception($GLOBALS['useGoods']['hero_health_wrong']);
	}
	$heroState = sql_fetch_one_cell ( "select state from sys_city_hero where hid='$hid'" );
	if(isHeroInCity($heroState)==0)
	{
		throw new Exception($GLOBALS['useGoods']['hero_state_wrong']);
	}
	$percent=0;
	if($gid==74) $percent=0.1;
	else if($gid==75) $percent=0.3;
	else if($gid==76) $percent=0.6;
	else if ($gid==77||$gid==151) $percent=1;
	sql_query("update mem_hero_blood set `force`=LEAST(`force`+`force_max`*$percent,`force_max`) where hid='$hid'");
	sql_query("delete from sys_hero_rest where hid='$hid'");
	sql_query("update sys_city_hero set hero_health=0 where hid='$hid'");
	reduceGoods($uid,$gid,1);
	return getCityInfoHero($uid,$cid);
}

function useEnergyGoods($uid,$cid,$gid,$param)
{
	$hid=array_shift($param);
	if(!checkGoods($uid,$gid))
	{
		throw new Exception("not_enough_goods$gid");
	}
	$heroHeroState = sql_fetch_one_cell ( "select hero_health from sys_city_hero where hid='$hid'" );
	if($heroHeroState!=0)
	{
		throw new Exception($GLOBALS['useGoods']['hero_health_wrong']);
	}
	$heroState=sql_fetch_one_cell("select state from sys_city_hero where hid='$hid'");

	//if($heroState>1)
	if (isHeroInCity ( $heroState ) == 0) {
		throw new Exception ( $GLOBALS ['useGoods'] ['hero_state_wrong'] );
	}
	$percent = 0;
	if ($gid == 78)
	$percent = 0.1;
	else if ($gid == 79)
	$percent = 0.3;
	else if ($gid == 80)
	$percent = 0.6;
	else if ($gid == 81)
	$percent = 1;
	sql_query ( "update mem_hero_blood set `energy`=LEAST(`energy`+`energy_max`*$percent,`energy_max`) where hid='$hid'" );
	reduceGoods ( $uid, $gid, 1 );
	return getCityInfoHero ( $uid, $cid );
}

function getMaxHeroLevel($npcid){    
	if(isInBigHero($npcid)){
		return 125;
	}
	return 120;
}
function useAddHeroExpBook($uid, $cid, $gid, $param) {
	if (! checkGoods ( $uid, $gid )) {
		throw new Exception ( "not_enough_goods$gid" );
	}
	$hid = array_shift ( $param );
	$heroInfo = sql_fetch_one ( "select * from sys_city_hero where hid='$hid' and cid='$cid'" );
	if (empty ( $heroInfo ))
	throw new Exception ( $GLOBALS ['addHeroPoint'] ['cant_find_hero'] );
	if ($heroInfo['herotype'] == 1000) {//军主将特殊标记
		throw new Exception ($GLOBALS['user_hero']['cannot_use_book']);
	}
	
	//马来订制 卡片签到系统
	$yysType = sql_fetch_one_cell("select value from mem_state where state=197");
	/*
	if($yysType==60 || $yysType==55555555){
		if($heroInfo['npcid']>0){
			if($heroInfo['level']>=120){
				throw new Exception($GLOBALS['useGoods']['hero_level_full']);
			}
		}else if($heroInfo['level']>=100){
			throw new Exception($GLOBALS['useGoods']['hero_level_full']);
		}
	}else{
		if ($heroInfo ['level'] >= 100){
			throw new Exception ( $GLOBALS ['useGoods'] ['hero_level_full'] );
		}
	}
	*/
	if($heroInfo ['level']>=getMaxHeroLevel($heroInfo['npcid'])){
		throw new Exception($GLOBALS['useGoods']['hero_level_full']);
	}
	
	if (isHeroInCity ( $heroInfo ['state'] ) == 0) //$heroInfo['state']>1)
	{
		throw new Exception ( $GLOBALS ['useGoods'] ['hero_state_wrong'] );
	}
	$goodCnt = sql_fetch_one_cell("select count from sys_goods where gid='$gid' and uid='$uid'");
	if($goodCnt>10) $goodCnt=10;
	$expadd = 0;
	if ($gid == 113)
	$expadd = 3000*$goodCnt;
	else if ($gid == 114)
	$expadd = 30000*$goodCnt;
	else if ($gid == 115)
	$expadd = 300000*$goodCnt;
	sql_query ( "update sys_city_hero set exp=exp+$expadd where hid='$hid'" );
	reduceGoods ( $uid, $gid, $goodCnt);
	return getCityInfoHero ( $uid, $cid );
}

//君主将加经验
function useAddUserHeroExpBook($uid, $cid, $gid,$useCount) {
	$goodCnt = sql_fetch_one_cell("select count from sys_goods where gid='$gid' and uid='$uid'");
	if(empty($goodCnt)||intval($goodCnt)<$useCount) throw new Exception($GLOBALS['duihuan']['not_enogh']);

	$heroInfo = sql_fetch_one ( "select * from sys_city_hero where uid='$uid' and herotype='1000'" );
	if (empty($heroInfo))
	{
		throw new Exception ( $GLOBALS['useGoods']['king_cannot_find']);
	}	
	$levelLimit=100;
	$level = sql_fetch_one_cell("select level from sys_user_level where uid='$uid'");
	if(!empty($level) && intval($level)==10)
	{
		$levelLimit=125;
	}
	if($heroInfo ['level']>=$levelLimit){
		throw new Exception($GLOBALS['useGoods']['hero_level_full']);
	}
	
	if (isHeroInCity ( $heroInfo ['state'] ) == 0) //$heroInfo['state']>1)
	{
		throw new Exception ( $GLOBALS ['useGoods'] ['hero_state_wrong'] );
	}
	
	$heroCurExp = sql_fetch_one_cell("select exp from sys_city_hero where hid='$heroInfo[hid]'");
	$maxExp = sql_fetch_one_cell("select total_exp from cfg_hero_level where level='$levelLimit'");
	$leaveExp = $maxExp-$heroCurExp;
	
	$expadd = 0;
	if ($gid == 10957)
	$expadd = 5000;
	else if ($gid == 10958)
	$expadd = 15000;
	else if ($gid == 10959)
	$expadd = 30000;
	
	$needCnt = ceil($leaveExp/$expadd);
	//$realCnt = $useCount;
	$realCnt = $goodCnt;
	if($useCount>$needCnt){
		$realCnt = $needCnt;
		sql_query("update sys_city_hero set exp='$maxExp' where hid='$heroInfo[hid]'");
	}else{
		$expadd = $expadd*$realCnt;
		sql_query ( "update sys_city_hero set exp=exp+$expadd where hid='$heroInfo[hid]'" );
	} 
	reduceGoods ( $uid, $gid, $realCnt);
	
	$msg = sprintf($GLOBALS['useGoods']['king_use_succ'],$expadd);
	
	return $msg;
}

function useMenZhuMiZhao($uid) 
{//使用"盟主密诏"
	static $gid = 14; 
	static $vice_chief_pos = 4;//副盟主
	
	if (! checkGoods($uid, $gid)) {
		throw new Exception("not_enough_goods$gid");
	}
	$user = sql_fetch_one ( "select * from sys_user where uid='$uid'" );
	$unionid = $user['union_id'];
	$unionpos = $user['union_pos'];
	
	$curLeaderID = sql_fetch_one_cell("select leader from sys_union where `id`='$unionid'");//当前盟主id
	$lastupdate = sql_fetch_one_cell("select lastupdate from sys_online where uid='$curLeaderID'");
	
	if (! isUserLosted($lastupdate)) {//盟主未处于失踪状态，则不能使用
		throw new Exception ($GLOBALS['useMenZhuMiZhao']['not_leader_lost']);
	}
	if ($unionpos != $vice_chief_pos) {//不是副盟主，则不能使用
		throw new Exception ($GLOBALS['useMenZhuMiZhao']['not_union_vice_chief']);
	}
	
	sql_query("update sys_user set union_pos=4 where uid='$curLeaderID'");//当前盟主降为副盟主
	sql_query("update sys_user set union_pos=5 where uid='$uid'");//副盟主提升为盟主
	sql_query("update sys_union set `leader` = '$uid' where `id`='$unionid'");
	exchangeUnionState($curLeaderID,$uid);
	reduceGoods ( $uid, $gid, 1 );
}
function useMenzhulin($uid, $cid) {

	if (! checkGoods ( $uid, 15 )) {
		$tempgid = 15;
		throw new Exception ( "not_enough_goods$tempgid" );
	}
	$user = sql_fetch_one ( "select * from sys_user where uid='$uid'" );
	if ($user ['union_id'] == 0)
	throw new Exception ( $GLOBALS ['useMenzhulin'] ['not_join_union'] );

	$union = sql_fetch_one ( "select * from sys_union where id='$user[union_id]'" );
	if (empty ( $union ))
	throw new Exception ( $GLOBALS ['useMenzhulin'] ['union_not_exist'] );

	if ($union ['chieforder'] != 0)
	throw new Exception ( $GLOBALS ['useMenzhulin'] ['already_used'] );
	sql_query ( "update sys_union set chieforder=1 where id='$union[id]'" );

	reduceGoods ( $uid, 15, 1 );
}
function useaddyuanbao($uid){
    $gid=-100;
    $goodCnt = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='$gid'");
	if(empty($goodCnt)) throw new Exception($GLOBALS['duihuan']['not_enogh']);
	sql_query("update sys_user set money=money+$goodCnt where uid='$uid'");
	reduceGoods ( $uid,-100, $goodCnt );
	return $goodCnt;
}
function useXiShuiDan($uid, $hid) {
	$hero = sql_fetch_one ( "select * from sys_city_hero where hid='$hid'" );
	if (empty ( $hero ))
	throw new Exception ( $GLOBALS ['useXiShuiDan'] ['no_hero_info'] );

	$goodsNeed = ceil ( $hero ['level'] / 10 );
	if (! checkGoodsCount ( $uid, 22, $goodsNeed )) {
		throw new Exception ( "not_enough_goods22#$goodsNeed" );
	}

	sql_query ( "update sys_city_hero set affairs_add=0,bravery_add=0,wisdom_add=0 where hid='$hid'" );

	reduceGoods ( $uid, 22, $goodsNeed );
}
function useZhaoXinLin($uid, $cid) {
	if (! checkGoods ( $uid, 23 )) {
		//		throw new Exception($GLOBALS['useZhaoXinLin']['no_ZhaoXinLin']);
		throw new Exception ( "not_enough_goods23" );
	}
	sql_query ( "update mem_city_schedule set `last_reset_recruit`=0 where `cid`='$cid'" );
	completeTask ( $uid, 89 );
	reduceGoods ( $uid, 23, 1 );
}

function useTaskMagic($uid, $cid) {
	//check goods
	sql_query ( "update mem_user_schedule set `last_reset_sys_task`=0 where uid=$uid" );
}

function UseMianZhanPai($uid,$fromIndex)
{
	$result = -1;
	//先检查是否处于3小时免战冷却时期内
	$serverType = sql_fetch_one_cell("select value from  mem_state where state=197");
	if($fromIndex==3)   //休战状态得清除免战冷却时间
	{
		if(sql_check("select 1 from sys_user where uid='$uid' and money<300"))throw new Exception($GLOBALS['lottery']['no_money']);
		sql_query("delete from mem_user_buffer where uid='$uid' and buftype='8'");
		sql_query("delete from mem_user_buffer where uid='$uid' and buftype='913'");
	}
	$coolingRecord = sql_fetch_one("select endtime,endtime-unix_timestamp() as lefttime from  mem_user_buffer where uid='$uid' and buftype=8");
	$leftTime = sql_fetch_one_cell("select endtime-unix_timestamp() as lefttime from  mem_user_buffer where uid='$uid' and buftype=7");
	
	//检测是否有休假冷却buffer
	$bufEndTime = sql_fetch_one_cell("select endtime from mem_user_buffer where uid='$uid' and buftype='913'");
	if (!empty($bufEndTime))
	{
		$delta = $bufEndTime - sql_fetch_one_cell("select unix_timestamp()");
		$lastLevyTime = MakeTimeLeft($delta);
		throw new Exception(sprintf($GLOBALS['changeUserState']['vacation_wait_to_use_MianZhanPai'],$lastLevyTime));
	}
	
	if (empty($coolingRecord)) {
		$usecount=0;
         
		if($leftTime>0){
			if ($serverType == 1) {
				$actionlog=sql_fetch_one("select `count` from log_user_mianzhan where uid='$uid' limit 1");	
			} else { 
				$actionlog=sql_fetch_one("select `count`,id from sys_user_action_log where action='usemianzhan' and uid='$uid' limit 1");
			}
			if(empty($actionlog)){
				$usecount=0;				
			} else {
				$usecount=$actionlog['count'];
			}
			if($actionlog['count']>=5 && $serverType == 1){
				throw new Exception ( $GLOBALS ['useMianZhan'] ['not_use'] );
			}
		}else{
			if ($serverType == 1) {
				sql_query("update log_user_mianzhan set `count`=0  where uid='$uid'");	
			} else {
				sql_query("update sys_user_action_log set `count`=0  where uid='$uid' and action='usemianzhan' ");	
			}
		}
		$server=sql_fetch_one_cell("select value from mem_state where state=197");//得到服务器
		
		if($fromIndex==3){
			addMoney($uid, -300, 731);
		}else {
			if($usecount>1){
			$usecount=1;
			}
			$reduceCount=$usecount+1;//连续使用需要的物品个数是连续次数加1
			if (! checkGoodsCount ( $uid, 12, $reduceCount)) {//玩家没有免战牌
				$msg=sprintf($GLOBALS['sellGoods']['not_enough_goods_mianzhan'],$reduceCount);
				throw new Exception($msg);
			}
			reduceGoods ( $uid, 12, $reduceCount );//消耗掉一个免战牌
		}
		
		
		sql_query ( "update sys_user set state=2 where uid='$uid'" );
		$usetime = 12 * 3600; //12小时免战
		sql_query ( "insert into mem_user_buffer (uid,buftype,endtime) values ('$uid','7',unix_timestamp()+$usetime) on duplicate key update endtime=endtime + $usetime" );
		
		
		
		$usecount+=1;
		$mark=$GLOBALS['useGoods']['MianZhanPai_2'];
		
		
		if(empty($actionlog)){
		    if ($serverType == 1) {
			    sql_query("insert into log_user_mianzhan(uid,`count`)values('$uid','$usecount') on duplicate key update `count`='count' + 1");	
			} else {
				sql_query("insert into sys_user_action_log(uid,action,`count`,`time`,mark)values('$uid','usemianzhan','$usecount',unix_timestamp(),'$mark') on duplicate key update `count`='count' + 1");
			}		
		} else {
		    if ($serverType == 1) {
				sql_query("update log_user_mianzhan set `count`=`count`+1 where uid='$uid'");	
			} else {
				$actionlogid=$actionlog['id'];
			    sql_query("update sys_user_action_log set `count`=`count`+1,`time`=unix_timestamp() where id='$actionlogid'");	
			}
		}
		$result =  sql_fetch_one_cell ( "select endtime from  mem_user_buffer where uid='$uid' and buftype=7" );//返回免战牌结束的时间点	
	} else {//正处于免战冷却时期
		$delta = $coolingRecord['lefttime'];//再次使用免战牌所需等待的秒数
		throw new Exception(sprintf($GLOBALS['changeUserState']['wait_to_use_MianZhanPai'],MakeTimeLeft($delta)));
	}
	return $result;
}

function useShenNongChu($uid, $gid,$useCount) //神农锄    粮食产量增加25%，持续24小时。
{
	$delay = 86400;
	$goodsname = $GLOBALS ['useShenNongChu'] ['ShenNongChu'];
	if ($gid == 44) {
		$delay = 86400 * 7;
		$goodsname = $GLOBALS ['useShenNongChu'] ['advanced_ShenNongChu'];
	}
	$delay = $delay*$useCount;
	$goodCnt = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='$gid'");
	if(empty($goodCnt)||intval($goodCnt)<$useCount) throw new Exception($GLOBALS['duihuan']['not_enogh']);
	
	sql_query ( "update sys_city_res_add a,sys_city c set a.goods_food_add=25,a.resource_changing=1 where c.uid='$uid' and a.cid=c.cid" );
	sql_query ( "insert into mem_user_buffer (uid,buftype,endtime) values ('$uid','1',unix_timestamp()+$delay) on duplicate key update endtime=endtime + $delay" );
	reduceGoods ( $uid, $gid, $useCount );
	$cid = sql_fetch_one_cell("select lastcid from sys_user where uid=$uid");
	if($gid == 2) {//神农锄
		completeTaskWithTaskid($uid, 320);
		addCityResources($cid, 0, 0, 0, 500, 0);//额外获得粮食500。
	} elseif ($gid == 44) {//高级神农锄
		addCityResources($cid, 0, 0, 0, 5000, 0);//额外获得粮食5000。
	}
	return sql_fetch_one_cell ( "select endtime from  mem_user_buffer where uid='$uid' and buftype=1" );
}
function useLuBanFu($uid, $gid,$useCount) //鲁班斧    木材产量增加25%，持续24小时。     
{
	$delay = 86400;
	$goodsname = $GLOBALS ['useLuBanFu'] ['LuBanFu'];
	if ($gid == 45) {
		$delay = 86400 * 7;
		$goodsname = $GLOBALS ['useLuBanFu'] ['advanced_LuBanFu'];
	}
	$delay = $delay*$useCount;
	$goodCnt = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='$gid'");
	if(empty($goodCnt)||intval($goodCnt)<$useCount) throw new Exception($GLOBALS['duihuan']['not_enogh']);
	
	sql_query ( "update sys_city_res_add a,sys_city c set a.goods_wood_add=25,a.resource_changing=1 where c.uid='$uid' and a.cid=c.cid" );
	sql_query ( "insert into mem_user_buffer (uid,buftype,endtime) values ('$uid','2',unix_timestamp()+$delay) on duplicate key update endtime=endtime + $delay" );
	reduceGoods ( $uid, $gid, $useCount );
	$cid = sql_fetch_one_cell("select lastcid from sys_user where uid=$uid");
	if($gid == 3) {
		completeTaskWithTaskid($uid, 321);
		addCityResources($cid, 500, 0, 0, 0, 0);//额外获得木材500。
	} elseif ($gid == 45) {
		addCityResources($cid, 5000, 0, 0, 0, 0);//额外获得木材5000。
	}
	return sql_fetch_one_cell ( "select endtime from  mem_user_buffer where uid='$uid' and buftype=2" );
}
function useKaiShanCui($uid, $gid,$useCount) //开山锤    石料产量增加25%，持续24小时。  
{
	$delay = 86400;
	$goodsname = $GLOBALS ['useKaiShanCui'] ['KaiShanCui'];
	if ($gid == 46) {
		$delay = 86400 * 7;
		$goodsname = $GLOBALS ['useKaiShanCui'] ['advanced_KaiShanCui'];
	}
	$delay = $delay*$useCount;
	$goodCnt = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='$gid'");
	if(empty($goodCnt)||intval($goodCnt)<$useCount) throw new Exception($GLOBALS['duihuan']['not_enogh']);
	
	sql_query ( "update sys_city_res_add a,sys_city c set a.goods_rock_add=25,a.resource_changing=1 where c.uid='$uid' and a.cid=c.cid" );
	sql_query ( "insert into mem_user_buffer (uid,buftype,endtime) values ('$uid','3',unix_timestamp()+$delay) on duplicate key update endtime=endtime + $delay" );
	reduceGoods ( $uid, $gid, $useCount );
	$cid = sql_fetch_one_cell("select lastcid from sys_user where uid=$uid");
	if($gid == 4) {
		completeTaskWithTaskid($uid, 322);
		addCityResources($cid, 0, 500, 0, 0, 0);//额外获得石料500。
	} elseif ($gid == 46) {
		addCityResources($cid, 0, 5000, 0, 0, 0);//额外获得石料5000。
	}
	return sql_fetch_one_cell ( "select endtime from  mem_user_buffer where uid='$uid' and buftype=3" );

}
function useXuanTieLu($uid, $gid,$useCount) //玄铁炉    铁锭产量增加25%，持续24小时。  
{
	$delay = 86400;
	$goodsname = $GLOBALS ['useXuanTieLu'] ['XuanTieLu'];
	if ($gid == 47) {
		$delay = 86400 * 7;
		$goodsname = $GLOBALS ['useXuanTieLu'] ['advanced_XuanTieLu'];
	}
	$delay = $delay*$useCount;
	$goodCnt = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='$gid'");
	if(empty($goodCnt)||intval($goodCnt)<$useCount) throw new Exception($GLOBALS['duihuan']['not_enogh']);
	
	sql_query ( "update sys_city_res_add a,sys_city c set a.goods_iron_add=25,a.resource_changing=1 where c.uid='$uid' and a.cid=c.cid" );
	sql_query ( "insert into mem_user_buffer (uid,buftype,endtime) values ('$uid','4',unix_timestamp()+$delay) on duplicate key update endtime=endtime + $delay" );
	reduceGoods ( $uid, $gid, $useCount );
	$cid = sql_fetch_one_cell("select lastcid from sys_user where uid=$uid");
	if($gid == 5) {
		completeTaskWithTaskid($uid, 323);
		addCityResources($cid, 0, 0, 500, 0, 0);//额外获得铁锭500。
	} elseif ($gid == 47) {
		addCityResources($cid, 0, 0, 5000, 0, 0);//额外获得铁锭5000。
	}
	return sql_fetch_one_cell ( "select endtime from  mem_user_buffer where uid='$uid' and buftype=4" );
}
function useXianZhenZhaoGu($uid, $gid) //陷阵战鼓军队攻击增加10%，持续24小时。
{
	$delay = 86400;
	$oldtype = 10084;
	$buftype = 5;
	$goodsname = $GLOBALS ['useXianZhenZhaoGu'] ['XianZhenZhaoGu'];
	if ($gid == 48) {
		$delay = 86400 * 7;
		$goodsname = $GLOBALS ['useXianZhenZhaoGu'] ['advanced_XianZhenZhaoGu'];
	}
	if ($gid == 10084) {
		$oldtype = 5;
		$buftype = 10084;
		$delay = 86400 / 24;
		$goodsname = $GLOBALS ['useXianZhenZhaoGu'] ['qiang_XianZhenZhaoGu'];
	}
	if (! checkGoods ( $uid, $gid )) {
		$msg = sprintf ( $GLOBALS ['useXianZhenZhaoGu'] ['no_XianZhenZhaoGu'], $goodsname );
		throw new Exception ( $msg );
	}
	if (1 == (sql_fetch_one_cell ( "select count(1) from mem_user_buffer where uid='$uid' and buftype='$oldtype'" ))) { //不可同时使用
		throw new Exception ( $GLOBALS ['useXianZhenZhaoGu'] ['nouse_XianZhenZhaoGu'] );
	}

	sql_query ( "insert into mem_user_buffer (uid,buftype,endtime) values ('$uid','$buftype',unix_timestamp()+$delay) on duplicate key update endtime=endtime + $delay" );
	reduceGoods ( $uid, $gid, 1 );
	if($gid == 6) {
		completeTaskWithTaskid($uid, 304);
	}
	return sql_fetch_one_cell ( "select endtime from  mem_user_buffer where uid='$uid' and buftype='$buftype'" );
}
function userXianDiZhaoShu($uid, $gid) //使用献帝诏书
{
	$delay = 86400*3;
	$buftype = $gid;
	$goodsname =sql_fetch_one_cell("select name from cfg_goods where gid=$gid");
	if (! checkGoods ( $uid, $gid )) {
		$msg = sprintf ( $GLOBALS ['useXianZhenZhaoGu'] ['no_XianZhenZhaoGu'], $goodsname );
		throw new Exception ( $msg );
	}
	sql_query ( "insert into mem_user_buffer (uid,buftype,endtime) values ('$uid','$buftype',unix_timestamp()+$delay) on duplicate key update endtime=endtime + $delay" );
	reduceGoods ( $uid, $gid, 1 );
	$title=  $GLOBALS['startbeizhanluoyang']["mailtitle"];
	$title=$GLOBALS['sendUnionMail']['union'].$title;
	$content= $GLOBALS['startbeizhanluoyang']["mailcontent"];
	$inform= $GLOBALS['startbeizhanluoyanginform']["mailcontent"];
	//sendAllSysMail($title,"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$content);
	$union=sql_fetch_one("select union_id,name from sys_user where uid='$uid'");
	$unionid=$union['union_id'];
	$fromname=$union['name'];
	$unionname=sql_fetch_one_cell("select name from sys_union where id=$unionid");
	$inform=$unionname.$inform;
	$mid = sql_insert("insert into sys_mail_content (`content`,`posttime`) values ('$content',unix_timestamp())");
	sql_insert("insert into sys_mail_box (`uid`,`name`,`fromuid`,`fromname`,`contentid`,`title`,`read`,`recvstate`,`sendstate`,`posttime`, `type`) (select `uid`,`name`,'$uid','$fromname','$mid','$title','0','0','0',unix_timestamp(),'2' from `sys_user` where `union_id`='$unionid')");
	sql_query("insert into sys_alarm (`uid`,`mail`) (select `uid`,1 from `sys_user` where `union_id`='$unionid') on duplicate key update `mail`=1");
	
	sendSysInform(0,1,0,300,1800,1,49151,$inform);
	return sql_fetch_one_cell ( "select endtime from  mem_user_buffer where uid='$uid' and buftype='$buftype'" );
}
function useBaGuaZhenTu($uid, $gid,$useCount) //八卦阵图军队防御增加10%，持续24小时。
{
	$delay = 86400;
	$oldtype = 10085;
	$buftype = 6;
	$goodsname = $GLOBALS ['useBaGuaZhenTu'] ['BaGuaZhenTu'];
	if ($gid == 49) {
		$delay = 86400 * 7;
		$goodsname = $GLOBALS ['useBaGuaZhenTu'] ['advanced_BaGuaZhenTu'];
	}
	if ($gid == 10085) {
		$oldtype = 6;
		$buftype = 10085;
		$delay = 86400 / 24;
		$goodsname = $GLOBALS ['useBaGuaZhenTu'] ['qiang_BaGuaZhenTu'];
	}
	$delay = $delay*$useCount;
	$goodCnt = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='$gid'");
	if(empty($goodCnt)||intval($goodCnt)<$useCount) throw new Exception($GLOBALS['duihuan']['not_enogh']);
	
	
	$tType = sql_fetch_one_cell ( "select count(1) from mem_user_buffer where uid='$uid' and buftype='$oldtype'" );

	if (1 == (sql_fetch_one_cell ( "select count(1) from mem_user_buffer where uid='$uid' and buftype='$oldtype'" ))) { //不可同时使用
		throw new Exception ( $GLOBALS ['useBaGuaZhenTu'] ['nouse_BaGuaZhenTu'] );
	}

	sql_query ( "insert into mem_user_buffer (uid,buftype,endtime) values ('$uid','$buftype',unix_timestamp()+$delay) on duplicate key update endtime=endtime + $delay" );
	reduceGoods ( $uid, $gid, $useCount );
	if($gid == 7) {
		completeTaskWithTaskid($uid, 305);
	}
	return sql_fetch_one_cell ( "select endtime from  mem_user_buffer where uid='$uid' and buftype='$buftype'" );
}
function useQingNangShu($uid, $gid,$useCount) //青囊书   可以恢复的伤兵人数增加30%，50%，效果持续24小时
{
	$goodCnt = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='$gid'");
	if(empty($goodCnt)||intval($goodCnt)<$useCount) throw new Exception($GLOBALS['duihuan']['not_enogh']);
	
	if ($gid == 25) {
		$oldtype = 10083;
		$buftype = 9;
		$time = 86400;
	}
	if ($gid == 10083) {
		$oldtype = 9;
		$buftype = 10083;
		$time = 3600;
	}
	if ($gid == 165) {
		$oldtype = 10083;
		$buftype = 165;
		$time = 86400;
	}
	$time = $time*$useCount;
	if (1 == (sql_fetch_one_cell ( "select count(1) from mem_user_buffer where uid='$uid' and buftype='$oldtype'" ))) { //不可同时使用
		throw new Exception ( $GLOBALS ['useQingNangShu'] ['nouse_QingNangShu'] );
	}
	sql_query ( "insert into mem_user_buffer (uid,buftype,endtime) values ('$uid','$buftype',unix_timestamp()+$time) on duplicate key update endtime=endtime + $time" );
	reduceGoods ( $uid, $gid, $useCount );
	return sql_fetch_one_cell ( "select endtime from  mem_user_buffer where uid='$uid' and buftype='$buftype'" );
}
//打开只掉落一个道具的箱子，比如古朴木盒
function openSimpleGoodsBox($uid, $dropRate, $type) {
	$allrate = 0;
	foreach ( $dropRate as $goodsRate ) {
		$allrate += $goodsRate ['rate'];
	}
	$rnd = mt_rand () % $allrate;
	$ratesum = 0;

	$ret = array ();
	$cnt = 1;
	for($i = 0; $i < count ( $dropRate ); $i ++) {
		$goods = $dropRate [$i];
		$ratesum += $goods ['rate'];

		if ($rnd < $ratesum) {
			$gid = $goods ['gid'];
			addGoods ( $uid, $gid, $cnt, 3 );
			$oneGood = sql_fetch_one ( "select *,$cnt as count,value from cfg_goods where gid='$gid'" );
			$ret [] = $oneGood;
				
			//如果大于50 发送公
			if (isSentGood ( $oneGood ['gid'] )) {
				$sendNames = array ();
				$sendNames [] = "“" . $oneGood ["name"] . "”" . $cnt;
				sendOpenBoxInform ( $sendNames, $oneGood ['value'] * $cnt, $uid, $type );
			}
			break;
		}
	}

	//活动额外奖励///
	$msg = checkAndDoSimpleBoxAct($uid, $type);
	if($msg){
		$ret[] = $msg;
	}
	return $ret;
}

function useQingCangLing($uid) {
	if (! checkGoods ( $uid, 51 )) {
		throw new Exception ( $GLOBALS ['useGoods'] ['no_this_good'] );
	}

	sql_query ( "insert into mem_user_buffer (uid,buftype,endtime) values ('$uid','10',unix_timestamp()+604800) on duplicate key update endtime=endtime + 604800" );
	reduceGoods ( $uid, 51, 1 );
	return sql_fetch_one_cell ( "select endtime from  mem_user_buffer where uid='$uid' and buftype=10" );
}

function useShuiLiBian($uid, $gid) //税吏鞭   黄金产量增加25%，持续24(7*24)小时。
{
	$delay = 86400;
	$goodsname = $GLOBALS ['useShuiLiBian'] ['ShuiLiBian'];
	if ($gid == 55) {
		$delay = 86400 * 7;
		$goodsname = $GLOBALS ['useShuiLiBian'] ['advanced_ShuiLiBian'];
	}
	if (! checkGoods ( $uid, $gid )) {
		//$msg = sprintf($GLOBALS['useShuiLiBian']['no_ShuiLiBian'],$goodsname);
		$msg = "not_enough_goods$gid";
		throw new Exception ( $msg );
	}
	sql_query ( "update mem_city_resource m, sys_city c set m.gold_rate=125 where c.uid='$uid' and m.cid=c.cid" );
	sql_query ( "insert into mem_user_buffer (uid,buftype,endtime) values ('$uid','15',unix_timestamp()+$delay) on duplicate key update endtime=endtime + $delay" );
	reduceGoods ( $uid, $gid, 1 );
	$cid = sql_fetch_one_cell("select lastcid from sys_user where uid=$uid");
	if($gid == 54) {
		completeTaskWithTaskid($uid, 324);
		addCityResources($cid, 0, 0, 0, 0, 500);//额外获得黄金500。
	} elseif ($gid == 55) {
		addCityResources($cid, 0, 0, 0, 0, 5000);//额外获得黄金5000。
	}
		
	return sql_fetch_one_cell ( "select endtime from  mem_user_buffer where uid='$uid' and buftype=15" );
}

function useGaojiTuiEnLing($uid, $gid) {
	if (! checkGoods ( $uid, 117 )) {
		throw new Exception ( $GLOBALS ['useGoods'] ['no_this_good'] );
	}

	$nobility = sql_fetch_one_cell ( "select nobility from sys_user where uid='$uid'" );
	if ($nobility < 5) {
		throw new Exception ( $GLOBALS ['gaojituienling'] ['dafuyishang'] );
	}
	$inuse = sql_fetch_one_cell ( "select endtime-unix_timestamp() from mem_user_buffer where uid='$uid' and buftype=16 " );
	if (! empty ( $inuse )) {
		//正在使用 则时间延长;
		sql_query ( "update mem_user_buffer set endtime=endtime + 864000 where uid='$uid' and buftype=16 " );
		reduceGoods ( $uid, 117, 1 );
		$ret = array ();

		$nobility = getBufferNobility ( $uid, $nobility );
		$ret [] = $nobility;
		$ret [] = sql_fetch_one_cell ( "select endtime-unix_timestamp() from  mem_user_buffer where uid='$uid' and buftype=16" );
		return $ret;
	}

	if ($nobility >= 19) {
		throw new Exception ( $GLOBALS ['userTuiEnling'] ['guanneihou'] );
	}

	sql_query ( "insert into mem_user_buffer (uid,buftype,bufparam,endtime) values ('$uid','16',5,unix_timestamp()+864000) on duplicate key update endtime=endtime + 864000" );
	reduceGoods ( $uid, 117, 1 );
	$nobility = getBufferNobility ( $uid, $nobility );
	$ret [] = $nobility;
	$ret [] = sql_fetch_one_cell ( "select endtime-unix_timestamp() from  mem_user_buffer where uid='$uid' and buftype=16" );
	return $ret;
}

function useTuiEnLing($uid, $gid) {
	if (! checkGoods ( $uid, 124 )) {
		throw new Exception ( $GLOBALS ['useGoods'] ['no_this_good'] );
	}

	$nobility = sql_fetch_one_cell ( "select nobility from sys_user where uid='$uid'" );
	$inuse = sql_fetch_one_cell ( "select endtime-unix_timestamp() from mem_user_buffer where uid='$uid' and buftype=18 " );
	if (! empty ( $inuse )) {
		//正在使用 则时间延长;
		sql_query ( "update mem_user_buffer set endtime=endtime + 86400*3 where uid='$uid' and buftype=18 " );
		reduceGoods ( $uid, 124, 1 );
		completeTaskWithTaskid($uid, 306);
		$ret = array ();

		$nobility = getBufferNobility ( $uid, $nobility );
		$ret [] = $nobility;
		$ret [] = sql_fetch_one_cell ( "select endtime-unix_timestamp() from  mem_user_buffer where uid='$uid' and buftype=18" );
		return $ret;
	}

	if ($nobility >= 18) {
		throw new Exception ( $GLOBALS ['userTuiEnling'] ['dashuzhang'] );
	}

	sql_query ( "insert into mem_user_buffer (uid,buftype,bufparam,endtime) values ('$uid','18',2,unix_timestamp()+86400*3) on duplicate key update endtime=endtime + 86400*3" );
	reduceGoods ( $uid, 124, 1 );
	completeTaskWithTaskid($uid, 306);

	$nobility = getBufferNobility ( $uid, $nobility );
	$ret [] = $nobility;
	$ret [] = sql_fetch_one_cell ( "select endtime-unix_timestamp() from  mem_user_buffer where uid='$uid' and buftype=18" );
	return $ret;
}
function useSheMianWenShu($uid, $gid) {
	if (! checkGoods ( $uid, 134 )) {
		throw new Exception ( $GLOBALS ['useGoods'] ['no_this_good'] );
	}
	$honour = sql_fetch_one_cell("select honour from sys_user where uid=$uid");
	if($honour>=0)
	{
		throw new Exception ($GLOBALS['useGoods']['no_need_shemian']);
	}
	$result = sql_query ( "update sys_user set honour=0 where honour<0 and uid='$uid'" );
	if ($result) {
		reduceGoods ( $uid, 134, 1 );
		return $GLOBALS ['useGoods'] ['shemian_suc'];
	} else {
		unlockUser ( $uid );
		return $GLOBALS ['useGoods'] ['shemian_fail'];
	}

}

function getBattleNetScore($uid)
{
	return sendRemoteRequest($uid,"getBattleNetScore");
}
function restoreBattleNetScore($uid,$cnt)
{
	return sendRemoteRequest($uid,"restoreBattleNetScore",$cnt);
}
function useShiShiWenShu($uid, $gid) {
	if (! checkGoods ( $uid, 158 )) {
		throw new Exception ( $GLOBALS ['useGoods'] ['no_this_good'] );
	}
	$score = getBattleNetScore($uid);
	if($score>=100)
	{
		throw new Exception ($GLOBALS['useGoods']['no_need_shishi']);
	}
	$result=restoreBattleNetScore($uid,100);
	if ($result) {
		reduceGoods ( $uid, 158, 1 );
		return $GLOBALS ['useGoods'] ['shishi_suc'];
	} else {
		unlockUser ( $uid );
		return $GLOBALS ['useGoods'] ['shishi_fail'];
	}

}

function useQingZhanShu($uid, $gid) {
	$today_war_count = sql_fetch_one_cell ( "select today_war_count from mem_user_schedule where uid = $uid" );
	if (empty ( $today_war_count ))
	$today_war_count = 0;
	if ($today_war_count == 0)
	throw new Exception ( $GLOBALS ['useGoods'] ['today_war_count_zero'] );

	if (! checkGoods ( $uid, 138 )) {
		throw new Exception ( "not_enough_goods138" );
	}
	$result = sql_query ( "update mem_user_schedule set today_war_count=0 where uid='$uid'" );
	if ($result) {
		reduceGoods ( $uid, 138, 1 );
		return $GLOBALS ['useGoods'] ['qingzhan_suc'];
	} else {
		unlockUser ( $uid );
		throw new Exception ( $GLOBALS ['useGoods'] ['qingzhan_fail'] );
	}
}
function useQingZhanShuDirect($uid, $gid) {
	$today_war_count = sql_fetch_one_cell ( "select today_war_count from mem_user_schedule where uid = $uid" );
	if (empty ( $today_war_count ))
	$today_war_count = 0;
	if ($today_war_count == 0)
	throw new Exception ( $GLOBALS ['useGoods'] ['today_war_count_zero'] );

	if (! checkGoods ( $uid, 138 )) {
		throw new Exception ( "not_enough_goods138" );
	}
	$result = sql_query ( "update mem_user_schedule set today_war_count=0 where uid='$uid'" );
	if ($result) {
		reduceGoods ( $uid, 138, 1 );
		$ret = array ();
		$ret [] = 138;
		$ret [] = 0; //代表弹出信息框
		$ret [] = $GLOBALS ['useGoods'] ['qingzhan_suc'];
		return $ret;
	} else {
		unlockUser ( $uid );
		throw new Exception ( $GLOBALS ['useGoods'] ['qingzhan_fail'] );
	}
}

function useShangDuiQiYue($uid, $gid) {
	if (! checkGoods ( $uid, 120 )) {
		throw new Exception ( $GLOBALS ['useGoods'] ['no_this_good'] );
	}

	$inuse = sql_fetch_one_cell ( "select endtime from mem_user_buffer where uid='$uid' and buftype=17 " );
	if (! empty ( $inuse )) {
		//正在使用 则时间延长;
		sql_query ( "update mem_user_buffer set endtime=endtime + 259200 where uid='$uid' and buftype=17 " );
		reduceGoods ( $uid, 120, 1 );
		return sql_fetch_one_cell ( "select endtime from  mem_user_buffer where uid='$uid' and buftype=17" );
	}

	sql_query ( "insert into mem_user_buffer (uid,buftype,bufparam,endtime) values ('$uid','17',0,unix_timestamp()+259200) on duplicate key update endtime=endtime + 259200" );
	reduceGoods ( $uid, 120, 1 );
	return sql_fetch_one_cell ( "select endtime from  mem_user_buffer where uid='$uid' and buftype=17" );
}

function useYaoYiLin($uid) {
  return useYaoYiLinAll($uid,56,1);
}
function useYaoYiLinGaoJi($uid,$gid,$useCount) {
return useYaoYiLinAll($uid,$gid,$useCount);
}
function useYaoYiLinAll($uid,$gid,$useCount){
	$buffertype=11;
	if($gid==56){
		//低级徭役令,16gaoj 
		$buffertype=11;
	}else if($gid==166){//高级
		$buffertype=166;
	}
	$goodCnt = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='$gid'");
	if(empty($goodCnt)||intval($goodCnt)<$useCount) throw new Exception($GLOBALS['duihuan']['not_enogh']);
	
	$time = 259200*$useCount;

	sql_query ( "insert into mem_user_buffer (uid,buftype,endtime) values ('$uid','$buffertype',unix_timestamp()+$time) on duplicate key update endtime=endtime + 259200" );
	reduceGoods ( $uid, $gid, $useCount );
	completeTaskWithTaskid($uid, 307);

	logUserAction($uid,18);
	$actionCount = getActionCount($uid,18);
	if ($actionCount>=20)
	finishAchivement($uid,8);

	return sql_fetch_one_cell ( "select endtime from  mem_user_buffer where uid='$uid' and buftype=$buffertype" );
}
function useMuBingLing($uid) {
	if (! checkGoods ( $uid, 10321)) {
		throw new Exception ( "not_enough_goods10321" );
	}

	sql_query ( "insert into mem_user_buffer (uid,buftype,endtime) values ('$uid','10321',unix_timestamp()+86400) on duplicate key update endtime=endtime + 86400" );
	reduceGoods ( $uid, 10321, 1 );
	logUserAction($uid,10321);
	return sql_fetch_one_cell ( "select endtime from  mem_user_buffer where uid='$uid' and buftype=10321" );
}

function useQiuXianZhao($uid)
{
	if (! checkGoods ( $uid, 154 )) {
		throw new Exception ( "not_enough_goods154" );
	}

	sql_query ( "insert into mem_user_buffer (uid,buftype,endtime) values ('$uid','22',unix_timestamp()+604800) on duplicate key update endtime=endtime + 604800" );
	reduceGoods ( $uid, 154, 1 );
	return sql_fetch_one_cell ( "select endtime from  mem_user_buffer where uid='$uid' and buftype=22" );
}

function useJunLingZhuang($uid) {
	if (! checkGoods ( $uid, 133 )) {
		throw new Exception("not_enough_goods133");
	}

	sql_query ( "insert into mem_user_buffer (uid,buftype,bufparam,endtime) values ('$uid','19',3,unix_timestamp()+86400) on duplicate key update endtime=endtime + 86400" );
	reduceGoods ( $uid, 133, 1 );
	return sql_fetch_one_cell ( "select endtime from  mem_user_buffer where uid='$uid' and buftype=19" );
}

function useDianMinLin($uid, $cid) {
	if (! checkGoods ( $uid, 57 ))
	throw new Exception ( "not_enough_goods57" );
	$owner = sql_fetch_one_cell ( "select uid from sys_city where cid='$cid'" );
	if (empty ( $owner ) || $owner != $uid)
	throw new Exception ( $GLOBALS ['addPeople'] ['not_your_city'] );
	$cityinfo = sql_fetch_one ( "select people,people_max from mem_city_resource where cid='$cid'" );
	$people_max = $cityinfo ['people_max'];
	$people = $cityinfo ['people'];
	if ($people >= $people_max)
	throw new Exception ( $GLOBALS ['addPeople'] ['city_full'] );
	$add = ceil ( $people_max * 0.2 );
	if ($add < 100)
	$add = 100;
	sql_query ( "update mem_city_resource set people=people+$add where cid='$cid'" );
	reduceGoods ( $uid, 57, 1 );
	completeTaskWithTaskid($uid, 308);
	updateCityResourceAdd ( $cid );
	return (sprintf ( $GLOBALS ['addPeople'] ['succ'], $add ));
}

function useTaiPingYaoShu($uid, $cid) {
	if (! checkGoods ( $uid, 139 ))
	throw new Exception ( $GLOBALS ['addPeople'] ['no_taiping'] );
	$owner = sql_fetch_one_cell ( "select uid from sys_city where cid='$cid'" );
	if (empty ( $owner ) || $owner != $uid)
	throw new Exception ( $GLOBALS ['addPeople'] ['not_your_city'] );
	$cityinfo = sql_fetch_one ( "select people,people_max from mem_city_resource where cid='$cid'" );
	$people_max = $cityinfo ['people_max'];
	$people = $cityinfo ['people'];
	if ($people >= $people_max)
	throw new Exception ( $GLOBALS ['addPeople'] ['city_full'] );
	$add = ceil ( $people_max * 0.1 );
	if ($add < 100)
	$add = 100;
	sql_query ( "update mem_city_resource set people=people+$add where cid='$cid'" );
	reduceGoods ( $uid, 139, 1 );
	updateCityResourceAdd ( $cid );
	return (sprintf ( $GLOBALS ['addPeople'] ['succ'], $add ));
}

//高级建筑图纸
function useAdvancedConstructionPlan($uid, $cid, $gid) //高级建筑图纸 普通城资源地开放至12级，持续12小时
{
	$delay = 12*3600;
	if (! checkGoods ( $uid, $gid )) {
		$msg = "not_enough_goods$gid";
		throw new Exception ( $msg );
	}
	$cityType = sql_fetch_one_cell("select type from sys_city where cid=$cid");
	if ($cityType == 5) $cityType = 0;
	if($cityType!=0){
		throw new Exception ( $GLOBALS['AdvancedConstructionPlan']['cant_use_in_great_city'] );
	}
	sql_query("insert into mem_city_buffer(cid,buftype,bufparam,endtime) values ('$cid','10001',0,unix_timestamp()+$delay) on duplicate key update endtime=endtime+$delay");
	$endtime = sql_fetch_one_cell("select endtime from mem_city_buffer where cid='$cid' and buftype=10001");

	reduceGoods ( $uid, $gid, 1 );

	return $endtime;
}

//巡查令
function useXunChaLin($uid,$cid)
{
	if(!checkGoods($uid,142))
	throw new Exception ( "not_enough_goods142");
	$delay=86400*3;
	sql_query("insert into mem_user_buffer (uid,buftype,bufparam,endtime) values ('$uid',100,0,unix_timestamp()+$delay) on duplicate key update endtime=endtime+$delay,bufparam=0");
	reduceGoods($uid,142,1);
	return sql_fetch_one_cell("select endtime from mem_user_buffer where uid = $uid and buftype=100");
}
/**
 * 60%
 70%
 80%
 95%
 110%
 *
 *
 * @param unknown_type $uid
 * @param unknown_type $hid
 * @param unknown_type $gid
 */
function useWuHun($uid, $hid,$gid){
	$npcid=($gid-110000)%10000;
	$muls=array(0.6,0.7,0.8,0.95,1.1);
	$mul=$muls[($gid-110000)/10000];
	if(!checkGoods($uid,$gid)){
		throw new Exception(  sprintf($GLOBALS['wuhun']['no_wuhun'],sql_fetch_one_cell("select name from cfg_goods where gid=$gid")));
	}
	$hero = sql_fetch_one("select * from sys_city_hero where uid='$uid' and hid='$hid' limit 1");
	if($gid>151026 && $gid<151030){
		switch($gid){
		     case 151027:{if($hero['level']<119 || $hero['npcid']<0) throw new Exception("只有名将将领达到120级才能使用初级晋级丹！");$uplevel=125;break;}
		     case 151028:{if($hero['level']<124 || $hero['npcid']<0) throw new Exception("只有名将将领达到125级才能使用中级晋级丹！");$uplevel=130;break;}
             case 151029:{if($hero['level']<129 || $hero['npcid']<0) throw new Exception("只有名将将领达到130级才能使用高级晋级丹！");$uplevel=135;break;}	 
		    }
		 $uname=sql_fetch_one_cell("select name from sys_user where uid='$uid'");
		 sql_query("update sys_city_hero set level='$uplevel',exp='695100000' where hid = $hid");
		 $msg="牛叉叉了,玩家【".$uname."】的将领【".$hero['name']."】已经成功升到".$uplevel."级了！";
		 sendSysInform(0,1,0,600,50000,1,14972979,$msg);
		 //$msg="恭喜你,你的将领【".$hero['name']."】已经成功升到".$uplevel."级了！";
		 reduceGoods($uid,$gid,1);
		 return "恭喜你,该将领已经成功升级了！";
	    }
	$bufftype=$gid-25;
	$buffer = sql_fetch_one("select * from mem_hero_buffer where hid =$hid and buftype=".$bufftype);
	if (empty($buffer)==false){
		sql_query(" update mem_hero_buffer set endtime = endtime+3600*24  where  hid =$hid and  buftype=$bufftype ");
		$cid=$hero["cid"];
		updateCityHeroChange($uid,$cid);
		reduceGoods($uid,$gid,1);

		return $GLOBALS['useGoods']['usewuhun_suc'];
		//return $GLOBALS['useGoods']['usewuhun_suc'];
	}else{
		if ($hero['state'] != 0)
		{
			throw new Exception($GLOBALS['wuhun']['only_wuhun_free_hero']);
		}
		$row=sql_fetch_one("select $mul*b.affairs_base-a.affairs_base as affairs_base_add_on,$mul*b.bravery_base-a.bravery_base as bravery_base_add_on,$mul*b.wisdom_base-a.wisdom_base as wisdom_base_add_on from sys_city_hero a,cfg_npc_hero b where a.hid=$hid and b.npcid=$npcid");
		$affairs_base_add_on =intval($row["affairs_base_add_on"]);
		$bravery_base_add_on = intval($row["bravery_base_add_on"]);
		$wisdom_base_add_on = intval($row["wisdom_base_add_on"]);
		$command_base_add_on = sql_fetch_one_cell("select command_base from sys_city_hero where npcid=$npcid");
		$command_base_add_on = intval($command_base_add_on*$mul);

		$command_base = sql_fetch_one_cell("select command_base from sys_city_hero where hid = $hid");//计算统率的增加值		
		$command_base_add_on = $command_base_add_on-$command_base;

		sql_query("insert into sys_city_hero_base_add (hid,uid,affairs_base_add_on,bravery_base_add_on,wisdom_base_add_on,command_base_add_on,type) values($hid,$uid,$affairs_base_add_on,$bravery_base_add_on,$wisdom_base_add_on,$command_base_add_on,2)");
		sql_query("update sys_city_hero set bravery_base=bravery_base+$bravery_base_add_on,wisdom_base=wisdom_base+$wisdom_base_add_on,affairs_base=affairs_base+$affairs_base_add_on,command_base=command_base+$command_base_add_on where hid = $hid");

		sql_query("delete from mem_hero_buffer where hid =$hid and buftype>(110000-25) and buftype<(160000-25)");
		sql_query("insert into mem_hero_buffer (hid,buftype,endtime) values($hid,$bufftype,unix_timestamp()+3600*24)");

		$cid=$hero["cid"];
		updateCityHeroChange($uid,$cid);
	}
	reduceGoods($uid,$gid,1);
	regenerateHeroAttri($uid, $hid);
	return $GLOBALS['useGoods']['usewuhun_suc'];
}
//使用碎片生成武魂
function genWuHunBySuipian($uid,$param){
	$gid=intval(array_shift($param));
	$useJuHunFan=array_shift($param);
	$goodCount = 10;
	if ($useJuHunFan){
		if (!checkGoods($uid,148))
		{
			throw new Exception("not_enough_goods148");
		}
		$goodCount=1;
	}
	unlockUser($uid);
	if (!checkGoodsCount($uid,$gid,$goodCount)){
		throw new Exception($GLOBALS['useGoods']['not_enough_wuHunSuiPian']);
	}
	reduceGoods($uid,$gid,$goodCount);
	if ($useJuHunFan){
		reduceGoods($uid,148,1);
	}
	$ret = array();
	$ret[]=$gid;
	$ret[] = 0;
	if (mt_rand(1,100)<=70 || $useJuHunFan){
		$hid = $gid-100000;
		$wuhun=getWuhunByHid($hid);
		addGoods($uid,$wuhun,1,0);
			
		$ret[]= $GLOBALS['useGoods']['genWuHunBySuipian_succ'];
	}else
	$ret[]= $GLOBALS['useGoods']['genWuHunBySuipian_fail'];

	logUserAction($uid,10);
	return $ret;
}

function getWuhunByHid($hid){
	$randValue = mt_rand(1,100);
	if ($randValue<=60) return $hid+110000;
	else if ($randValue<=90) return $hid+120000;
	else if ($randValue<=97) return $hid+130000;
	else if ($randValue<=99) return $hid+140000;
	else return $hid+150000;
}
//使用画像生成武魂
function genWuHunByRumor($uid,$param){
	$tid=intval(array_shift($param));

	if (!checkGoods($uid,149))
	{
		throw new Exception("not_enough_goods149");
	}
	unlockUser($uid);
	//throw new Exception("djjh");
	if (!checkThingsCount($uid,$tid,1)){

		throw new Exception($GLOBALS['useGoods']['no_this_rumor']);
	}
	//throw new Exception($gid);
	reduceGoods($uid,149,1);
	//addThings($uid,$tid,-1,0);
	$hid = $tid-20000;
	$ret = array();
	$ret[]=$gid;
	$ret[] = 0;
	if (mt_rand(1,100)<=90){ //得到武魂碎片
		$wuhunsuipian=	100000+$hid;
		addGoods($uid,$wuhunsuipian,1,0);
		$ret[]= $GLOBALS['useGoods']['genWuHunByRumor_getsuipian'];
	}else{
		$wuhun=getWuhunByHid($hid);
		addGoods($uid,$wuhun,1,0);
		$ret[]= $GLOBALS['useGoods']['genWuHunByRumor_getwuhun'];
	}
	logUserAction($uid,11);
	return $ret;
}

function useAnMingGaoShi($uid,$cid)
{
	if (!checkGoods($uid,58))
	{
		throw new Exception("not_enough_goods58");
	}
	$anminginfo=sql_fetch_one("select last_anming,unix_timestamp() as nowtime from mem_city_schedule where cid='$cid'");
	$lasttime=$anminginfo['last_anming'];
	$nowtime=$anminginfo['nowtime'];

	if((!empty($lasttime)&&($nowtime-$lasttime<259200)))
	{
		throw new Exception(sprintf($GLOBALS['useGoods']['AnMingGaoShi_cool_down'],MakeTimeLeft(259200-($nowtime-$lasttime))));
	}
	sql_query("update mem_city_resource set morale=100, complaint=0, morale_stable=100-`tax`,`people_stable`=`people_max` where cid='$cid'");
	sql_query("insert into mem_city_schedule (cid,last_anming) values ('$cid',unix_timestamp()) on duplicate key update last_anming=unix_timestamp()");
	reduceGoods($uid,58,1);
	completeTaskWithTaskid($uid, 309);
	return $GLOBALS['useGoods']['AnMingGaoShi_succ'];

}

function useKaoGongJi($uid,$gid)
{
	if(!checkGoods($uid,$gid)) throw new Exception("not_enough_goods$gid");
	$buftype=12+($gid-60);
	sql_query("insert into mem_user_buffer (uid,buftype,endtime) values ('$uid','$buftype',unix_timestamp()+86400) on duplicate key update endtime=endtime+86400");
	reduceGoods($uid,$gid,1);
	return sql_fetch_one_cell("select endtime from mem_user_buffer where uid='$uid' and buftype='$buftype'");
}
function openDynamicBox($uid,$mygid) //动态生产的礼包
{
	if(!lockUser($uid)) throw new Exception($GLOBALS['pacifyPeople']['server_busy']);
	
	$record=sql_fetch_one("select * from cfg_pack_goods where gid='$mygid'");

	if(empty($record)) throw new Exception($GLOBALS['useGoods']['no_pack_good']);
	
	$goodCount = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='$mygid'");
	if(empty($goodCount)||intval($goodCount)<1)throw new Exception($GLOBALS['duihuan']['not_enogh']);
	reduceGoods ( $uid, $mygid, 1 );

	$ret=array();
	$res = $record['res'];
	if ($res!=""){
		$reslist=explode(',',$res);
		$cid = sql_fetch_one_cell("select lastcid from sys_user where uid='$uid'");
		//		include_once 'TaskFunc.php';
		//黄金,粮食,木材,石料,铁锭		
		for($i = 0; $i < 5; $i ++) {
			$type = $i + 1;
			$cnt = $reslist [$i];
			if ($cnt == 0)
			continue;
			giveResource ( $uid, $cid, $type, $cnt );
			if ($type == 1)
			$gid = 85;
			else if ($type == 2)
			$gid = 87;
			else if ($type == 3)
			$gid = 88;
			else if ($type == 4)
			$gid = 89;
			else if ($type == 5)
			$gid = 90;
			$ret [] = array ("name" => $GLOBALS ['resPackage'] ['res_' . $type], "count" => $cnt, "gid" => $gid, "description" => $GLOBALS ['resPackage'] ['res_' . $type], "imagetype" => 0, "image" => 0 );
		}
	}
	$goods = $record ['goods'];

	$goodslist = explode ( ',', $goods );
	$goodcnt = $goodslist [0];
	$money = 0;
	//if(!lockUser($uid)) throw new Exception($GLOBALS['pacifyPeople']['server_busy']);

	for($i = 1; $i < $goodcnt * 2; $i += 2) {
		$gid = $goodslist [$i];
		$cnt = $goodslist [$i + 1];
		if ($gid == 0) {
			$money += $cnt;
			$ret [] = array ("name" => $GLOBALS ['sys'] ['LiJin'], "count" => $cnt, "gid" => 0, "description" => $GLOBALS ['sys'] ['description_of_LiJin'], "imagetype" => 0, "image" => 0 );

		} else {
			addGoods ( $uid, $gid, $cnt, 6 );
			$oneGood = sql_fetch_one ( "select *,$cnt as count from cfg_goods where gid='$gid'" );
			$ret [] = $oneGood;

		}
	}
	$things = $record ['things'];

	$thingslist = explode ( ',', $things );
	$thingcnt = $thingslist [0];
	for($i = 1; $i < $thingcnt * 2; $i += 2) {
		$tid = $thingslist [$i];
		$cnt = $thingslist [$i + 1];
		addthings ( $uid, $tid, $cnt, 8 );
		$onething = sql_fetch_one ( "select *,2 as gtype,$cnt as count from cfg_things where tid='$tid'" );
		$ret [] = $onething;
	}

	$armors = $record ['armor'];
	if ($armors != '') {
		$armorlist = explode ( ',', $armors );
		$armorcnt = $armorlist [0];
		for($i = 1; $i < $armorcnt * 2; $i += 2) {
			$aid = $armorlist [$i];
			$cnt = $armorlist [$i + 1];
				
			$armor = sql_fetch_one ( "select * from cfg_armor where id='$aid'" );
			$armor ['cnt'] = $cnt;
			$armor ['gtype'] = 1;
			$armor ['hp'] = $armor ['ori_hp_max'];
			$armor ['hp_max'] = $armor ['ori_hp_max'];
			addArmor ( $uid, $armor, $cnt, 3 );
			$ret [] = $armor;
		}
	}

	if ($money > 0) {
		addGift ( $uid, $money, 3 );
	}
	unlockUser ( $uid );
	return $ret;
}

function openTreasureBox($uid, $totalValue, $dropRate, $type) {
	$dropCount = 9;
	$tryCount = 50;
	$goodsGet = array ();
	$ret = array ();

	while ( ($totalValue > 0) && ($dropCount > 0) && (count ( $dropRate ) > 0) && ($tryCount > 0) ) {

		$allrate = 0;
		foreach ( $dropRate as $goodsRate ) {
			$allrate += $goodsRate ['rate'];
		}

		$rnd = mt_rand () % $allrate;
		$ratesum = 0;
		$tryCount --;

		for($i = 0; $i < count ( $dropRate ); $i ++) {
			$goods = $dropRate [$i];
			$ratesum += $goods ['rate'];
			if ($rnd < $ratesum) {
				$countmax = floor ( $totalValue / $goods ['value'] );
				if ($goods ['rate'] <= 5) {
					$cnt = 1;
				} else {
					$cnt = mt_rand ( 1, ceil ( $countmax / 2 ) );
				}
				if ($cnt > 0 && $countmax > 0) {
					$goodsGet [$goods ['gid']] = $cnt;
					$totalValue -= $goods ['value'] * $cnt;
					$dropCount --;
					array_splice ( $dropRate, $i, 1 );
					break;
				}
			}
		}
	}
	if ($totalValue > 0 && $type > 0) {
		if ($type == 0) {
			if ($totalValue > 5)
			$totalValue = mt_rand ( 1, 5 );
		} else if ($type == 1) {
			if ($totalValue > 10)
			$totalValue = mt_rand ( 1, 10 );
		} else if ($type == 2) {
			if ($totalValue > 25)
			$totalValue = mt_rand ( 1, 25 );
		}
		addGift ( $uid, $totalValue, 20 );
		$goodsGet ['0'] = $totalValue;
	}
	$sendGoodNames = array ();
	$values = 0;
	foreach ( $goodsGet as $gid => $cnt ) {
		if ($gid > 0) {
			addGoods ( $uid, $gid, $cnt, 3 );
		}
		$oneGood = sql_fetch_one ( "select *,$cnt as count,value from cfg_goods where gid='$gid'" );
		if (isSentGood ( $oneGood ['gid'] )) {
			$sendGoodNames [] = "“" . $oneGood ["name"] . "”" . $cnt;
			$values += $oneGood ["value"] * $cnt;
		}
		$ret [] = $oneGood;
	}

	if (count ( $sendGoodNames ) > 0)
	sendOpenBoxInform ( $sendGoodNames, $values, $uid, $type + 16 );
	return $ret;
}
function getValueRand($min1, $max1, $min2, $max2, $min3, $max3, $min4, $max4) {
	$rnd = mt_rand () % 100;
	if ($rnd < 65) {
		return mt_rand ( $min1, $max1 );
	} else if ($rnd < 80) {
		return mt_rand ( $min2, $max2 );
	} else if ($rnd < 95) {
		return mt_rand ( $min3, $max3 );
	} else {
		return mt_rand ( $min4, $max4 );
	}
}
function openCopperBox($uid) {
	//$totalValue = getValueRand(21,30,31,50,15,20,51,75);
	$totalValue = getValueRand ( 21, 30, 31, 35, 15, 20, 36, 60 );
	$dropRate = sql_fetch_rows ( "select gid,copperbox as rate,value from cfg_goods where inuse=1 and copperbox > 0 and value > 0 and value <= $totalValue order by rand()" );
	return openTreasureBox ( $uid, $totalValue, $dropRate, 0 );
}
function openSilverBox($uid, $type = 1) {
	//$totalValue = getValueRand(101,150,151,200,90,100,201,360);
	$totalValue = getValueRand ( 121, 180, 181, 210, 90, 120, 211, 360 );
	$dropRate = sql_fetch_rows ( "select gid,silverbox as rate,value from cfg_goods where inuse=1 and silverbox > 0 and value > 0 and value <= $totalValue order by rand()" );

	return openTreasureBox ( $uid, $totalValue, $dropRate, $type );
}
function openGoldBox($uid) {
	//$totalValue = getValueRand(401,500,501,600,360,400,601,1050);
	$totalValue = getValueRand ( 321, 480, 481, 560, 240, 320, 561, 960 );
	$dropRate = sql_fetch_rows ( "select gid,goldbox as rate,value from cfg_goods where inuse=1 and goldbox > 0 and value > 0 and value <= $totalValue order by rand()" );
	return openTreasureBox ( $uid, $totalValue, $dropRate, 2 );
}

function openTreasure($uid) {
	$dropRate = sql_fetch_rows ( "select gid,treasurebox as rate,value from cfg_goods where inuse=1 and treasurebox > 0 and value > 0 order by rand()" );
	return openSimpleGoodsBox ( $uid, $dropRate, 119 );
}
function useCopperBox($uid) {
	if (! checkGoods ( $uid, 16 )) {
		throw new Exception ( $GLOBALS ['useCopperBox'] ['no_CopperBox'] );
	}
	unlockUser ( $uid );
	if (! checkGoods ( $uid, 19 )) {
		throw new Exception ( $GLOBALS ['useCopperBox'] ['no_CopperKey'] );
	}
	//打开青铜礼盒
	//1%的机率变成白银礼盒
	if ((mt_rand () % 100) == 0) {
		$ret = openSilverBox ( $uid, 0 );
	} else {
		$ret = openCopperBox ( $uid );
	}
	reduceGoods ( $uid, 16, 1 );
	reduceGoods ( $uid, 19, 1 );

	return $ret;
}
function useSilverBox($uid) {
	if (! checkGoods ( $uid, 17 )) {
		throw new Exception ( $GLOBALS ['useSilverBox'] ['no_SiverBox'] );
	}
	unlockUser ( $uid );
	if (! checkGoods ( $uid, 20 )) {
		throw new Exception ( $GLOBALS ['useSilverBox'] ['no_SiverKey'] );
	}
	//打开白银礼盒
	//1%的机率变成黄金礼盒
	if ((mt_rand () % 100) == 0) {
		$ret = openGoldBox ( $uid );
	} else {
		$ret = openSilverBox ( $uid );
	}
	reduceGoods ( $uid, 17, 1 );
	reduceGoods ( $uid, 20, 1 );

	return $ret;
}
function useTreasureBox($uid) {
	if (! checkGoods ( $uid, 119 )) {
		throw new Exception ( $GLOBALS ['useTreasureBox'] ['no_TreasureBox'] );
	}
	unlockUser ( $uid );
	//打开宝藏盒    
	$ret = openTreasure ( $uid );
	reduceGoods ( $uid, 119, 1 );

	return $ret;
}
function useGoldBox($uid) {
	if (! checkGoods ( $uid, 18 )) {
		throw new Exception ( $GLOBALS ['useGoldBox'] ['no_GoldBox'] );
	}
	unlockUser ( $uid );
	if (! checkGoods ( $uid, 21 )) {
		throw new Exception ( $GLOBALS ['useGoldBox'] ['no_GoldKey'] );
	}
	//打开黄金礼盒      


	$ret = openGoldBox ( $uid );

	reduceGoods ( $uid, 18, 1 );
	reduceGoods ( $uid, 21, 1 );

	return $ret;
}
function openOldWoodBox($uid) {
	$dropRate = sql_fetch_rows ( "select gid,woodbox as rate,value from cfg_goods where inuse=1 and woodbox > 0 and value > 0 order by rand()" );
	$ret= openSimpleGoodsBox ( $uid, $dropRate, 50 );
	$oneGood=$ret[0];
	if ($oneGood["gid"]==18)
	finishAchivement($uid,7);

	return $ret;
}
function openLoveBean($uid) {
	$dropRate = sql_fetch_rows ( "select gid,lovebean as rate,value from cfg_goods where inuse=1 and lovebean > 0 and value > 0 order by rand()" );
	return openSimpleGoodsBox ( $uid, $dropRate, 10014 );
}
//古朴木盒
function useOldWoodBox($uid) {
	if (! checkGoods ( $uid, 50 )) {
		throw new Exception ( $GLOBALS ['useOldWoodBox'] ['no_OldWoodBox'] );
	}
	$ret = openOldWoodBox ( $uid );
	reduceGoods ( $uid, 50, 1 );
	return $ret;
}
//答题礼包
function useDaTiLiBao($uid) {
	if (! checkGoods ( $uid, 150 )) {
		throw new Exception ( $GLOBALS ['useDaTiLiBao'] ['no_DaTiLiBao'] );
	}
	$ret = openOldWoodBox ( $uid );
	reduceGoods ( $uid, 150, 1 );
	return $ret;
}

function useKeyChain($uid) {
	$mygid = 10017;
	if (! checkGoods ( $uid, $mygid )) {
		throw new Exception ( $GLOBALS ['useKeyChain'] ['no_KeyChain'] );
	}
	$dropCount = 10;
	$goodsGet = array ();
	$ret = array ();

	while ( $dropCount > 0 ) {

		$dropCount --;
		$rnd = mt_rand () % 1000;
		if ($rnd < 5) {
			if (isset ( $goodsGet [21] )) {
				$goodsGet [21] += 1;
			} else {
				$goodsGet [21] = 1;
			}
		} else if ($rnd < 55) {
			if (isset ( $goodsGet [20] )) {
				$goodsGet [20] += 1;
			} else {
				$goodsGet [20] = 1;
			}
		} else {
			if (isset ( $goodsGet [19] )) {
				$goodsGet [19] += 1;
			} else {
				$goodsGet [19] = 1;
			}
		}
	}
	foreach ( $goodsGet as $gid => $cnt ) {
		addGoods ( $uid, $gid, $cnt, 3 );
		$ret [] = sql_fetch_one ( "select *,$cnt as count from cfg_goods where gid='$gid'" );
	}

	reduceGoods ( $uid, $mygid, 1 );
	return $ret;
}
/**
 * 点击武魂碎片盒,
 *
 * @param 用户的id号 $uid
 * @param 商品的id号 $goodid
 */
function openSuiPianHe($uid, $goodid) {
		if(!checkGoods($uid, $goodid)){
		throw new Exception($GLOBALS['goods']['no_baoshibox']);
	}
	$goods=sql_fetch_rows("select gid,name from cfg_goods where gid between 100000 and 101026");
	
	$randnum = mt_rand(0,sizeof($goods));
	$good=$goods[$randnum];
	$choose = $good['gid'];
	addGoods($uid, $choose, 1, 3);
	$ret [] = sql_fetch_one ( "select *,1 as `count` from cfg_goods where gid='$choose'" );
	
	reduceGoods ( $uid, $goodid, 1 );
	$uname=sql_fetch_one_cell("select name from sys_user where uid='$uid'");
	$gname=$good['name'];
	$msg = sprintf($GLOBALS['shachange']['enter_6'] ,$uname,$gname);
	if($msg){
			sendSysInform(0,1,0,300,1800,1,49151,$msg);
	}
	return $ret;
}
/**
 * 点击宝珠盒盒,
 *
 * @param 用户的id号 $uid
 * @param 商品的id号 $goodid
 */
function openBazhuHe($uid, $goodid) {
		if(!checkGoods($uid, $goodid)){
		throw new Exception($GLOBALS['goods']['no_baoshibox']);
	}
	$goodrate=array(1=>45,2=>35,3=>15,4=>5);
	$rateNum=rand(1,100);
	$totalrate=0;
	$selected=1;
	foreach ($goodrate as $level=>$tate){
		$totalrate+=$tate;
		if($rateNum<$totalrate){
			$selected=$level;
			break;
		}
	}
	$selected=$selected-1;
	$gid="3".mt_rand(0,7).$selected;
//	error_log($gid);
	$good=sql_fetch_one("select gid,name from cfg_goods where gid='$gid'");
	
//	$randnum = mt_rand(0,sizeof($goods));
//	$good=$goods[$randnum];
	$choose = $gid;
	addGoods($uid, $choose, 1, 3);
	$ret [] = sql_fetch_one ( "select *,1 as `count` from cfg_goods where gid='$choose'" );
	
	reduceGoods ( $uid, $goodid, 1 );
	$uname=sql_fetch_one_cell("select name from sys_user where uid='$uid'");
	$gname=$good['name'];
	$msg = sprintf($GLOBALS['shachange']['enter_8'] ,$uname,$gname);
	if($msg){
			sendSysInform(0,1,0,300,1800,1,49151,$msg);
	}
	return $ret;
}
/**
 * 点击使用宝珠盒的处理函数，参考打开宝石箱的函数openBaoshiBox
 *
 * @param 用户的id号 $uid
 * @param 商品的id号 $goodid
 */
function openBaozhuhe($uid, $goodid) {
		if(!checkGoods($uid, $goodid)){
		throw new Exception($GLOBALS['goods']['no_baoshibox']);
	}
	$gems = array (209 => 15, 210 => 15, 211 => 15, 1 => 55);
	
	$randnum = mt_rand(1, 100);
	$sumrate = 0;
	$choose = 1;
	foreach ($gems as $gid => $rate) {
		$sumrate += $rate;
		if($randnum <= $sumrate) {
			$choose = $gid;
			break;
		}
	}
	if ($choose == 1) {
		$type = mt_rand(1, 8);
		$choose = 300 + ($type - 1) * 10 + $choose - 1;//只能这么写。。。
	}
	addGoods($uid, $choose, 1, 3);
	$ret [] = sql_fetch_one ( "select *,1 as `count` from cfg_goods where gid='$choose'" );
	
	reduceGoods ( $uid, $goodid, 1 );
	
	return $ret;
}
function openBaoshiBox($uid, $goodid) {
	if(!checkGoods($uid, $goodid)){
		throw new Exception($GLOBALS['goods']['no_baoshibox']);
	}
	$gems = array (209 => 13, 210 => 13, 211 => 13, 1 => 50, 2 => 8, 3 => 3);

	$randnum = mt_rand(1, 100);
	$sumrate = 0;
	$choose = 1;
	foreach ($gems as $gid => $rate) {
		$sumrate += $rate;
		if($randnum <= $sumrate) {
			$choose = $gid;
			break;
		}
	}
	if ($choose >= 1 && $choose <=3) {
		$type = mt_rand(1, 8);
		$choose = 300 + ($type - 1) * 10 + $choose - 1;//只能这么写。。。
	}
	
	if ($choose==209||$choose==210||$choose==211){		
		addGoods($uid, $choose, 2, 3);
	}else{
		addGoods($uid, $choose, 1, 3);
	}
	//addGoods($uid, $choose, 1, 3);
	$ret [] = sql_fetch_one ( "select *,1 as `count` from cfg_goods where gid='$choose'" );

	reduceGoods ( $uid, $goodid, 1 );

	return $ret;
}

function openGemBox($uid, $mygid) {
	if ($mygid == 41) {
		$gems = array (30 => 9, 31 => 8, 32 => 7 );
		$totalValue = 80;
	} else if ($mygid == 42) {
		$gems = array (33 => 12, 34 => 10, 35 => 8 );
		$totalValue = 140;
	} else if ($mygid == 43) {
		$gems = array (36 => 15, 37 => 10, 38 => 5 );
		$totalValue = 240;
	}

	$dropRate = array ();
	foreach ( $gems as $gid => $rate ) {
		$goodsRate = array ();
		$goodsRate ['gid'] = $gid;
		$goodsRate ['value'] = sql_fetch_one_cell ( "select value from cfg_goods where gid='$gid'" );
		$goodsRate ['rate'] = $rate;
		$dropRate [] = $goodsRate;
		$allrate += $rate;
	}

	$dropCount = 3;
	$tryCount = 20;
	$goodsGet = array ();
	$ret = array ();

	while ( ($totalValue > 0) && ($dropCount > 0) && (count ( $dropRate ) > 0) && ($tryCount > 0) ) {
		$allrate = 0;
		foreach ( $dropRate as $goodsRate ) {
			$allrate += $goodsRate ['rate'];
		}
		$rnd = mt_rand () % $allrate;
		$ratesum = 0;
		$tryCount --;
		for($i = 0; $i < count ( $dropRate ); $i ++) {
			$goods = $dropRate [$i];
			$ratesum += $goods ['rate'];
			if ($rnd < $ratesum) {
				$countmax = floor ( $totalValue / $goods ['value'] );
				if ($goods ['rate'] <= 5) {
					$cnt = 1;
				} else {
					$cnt = mt_rand ( 1, $countmax );
				}
				if ($cnt > 0 && $countmax > 0) {
					$goodsGet [$goods ['gid']] = $cnt;
					$totalValue -= $goods ['value'] * $cnt;
					$dropCount --;
					array_splice ( $dropRate, $i, 1 );
					break;
				}
			}
		}
	}
	foreach ( $goodsGet as $gid => $cnt ) {
		addGoods ( $uid, $gid, $cnt, 3 );
		$ret [] = sql_fetch_one ( "select *,$cnt as count from cfg_goods where gid='$gid'" );
	}

	reduceGoods ( $uid, $mygid, 1 );

	return $ret;
}
function openXuanBinBox($uid, $mygid) {
	if ($mygid == 10428) {
		$reward0=array(10500=>1);//第一种奖励
		$reward1=array(10500=>1,0=>999);//第二种奖励
		
		$rewards=array($reward0,$reward1);//各种奖励的数组
		$rewardrates=array(0=>99,1=>1);//各种奖励对应的概率
		$sumrate=100;
	} 

	$droprate=mt_rand(0,$sumrate);
	$cursum=0;
	foreach ( $rewardrates as  $index=>$rate ) {
		$cursum+=$rate;
		if($cursum>=$droprate){
			$reward=$rewards[$index];
			break;
		}
	}
	if(!empty($reward)){
		$goodsGet=$reward;
	}else{
		$goodsGet=array();
	}
	foreach ( $goodsGet as $gid => $cnt ) {
		addGoods ( $uid, $gid, $cnt, 3 );
		$ret [] = sql_fetch_one ( "select *,$cnt as count from cfg_goods where gid='$gid'" );
	}

	reduceGoods ( $uid, $mygid, 1 );

	return $ret;
}

function addSkillBook($uid,$gid,$useCount) {
	
	$bid = intval(($gid - 40000)/100);
	$level = intval($gid % 100);
	$count = sql_fetch_one_cell("select count(*) from sys_user_book where uid=$uid");
	$bookCount = sql_fetch_one_cell("select count from sys_goods where uid=$uid and gid=$gid");
	$bookName = sql_fetch_one_cell("select `name` from cfg_book where `id`=$bid limit 1");
	if (empty($bookCount)) {
		throw new Exception($GLOBALS['book']['no_protect_flag']);
	}
	$curShelfNum = sql_fetch_one_cell("select num from sys_user_bookShelfNum where uid='$uid'");
	if (empty($curShelfNum))
	{
		$curShelfNum = 100;
	}
	if ($count>=intval($curShelfNum)||$useCount+$count>intval($curShelfNum)){
	     throw new Exception($GLOBALS['useGoods']['hero_skill_1']);
	}else{    
	      reduceGoods($uid,$gid,$useCount);
	}
	
	for($i=0;$i<$useCount;$i++)
	{
		sql_query("insert into sys_user_book(uid,bid,hid,level) values('$uid','$bid','0','$level')");
	    sql_query("insert into log_book(uid,bid,level,count,time) values ('$uid','$bid','$level','1',unix_timestamp())");
	}
	
	$msg=sprintf($GLOBALS['book']['get_skillBook'],$level,$bookName);
	return $msg;
}

function useFlagChar($uid, $newchar) {
	if (! checkGoods ( $uid, 39 )) {
		throw new Exception ( "not_enough_goods39" );
	}

	$charlen = mb_strlen ( $newchar );
	if ($charlen == 0) {
		throw new Exception ( $GLOBALS ['useFlagChar'] ['type_flag_name'] );
	} else if ($charlen > MAX_FLAG_CHAR) {
		throw new Exception ( $GLOBALS ['useFlagChar'] ['only_one_char'] );
	}

	sql_query ( "update sys_user set flagchar='$newchar' where uid='$uid'" );

	completeTask ( $uid, 371 );
	reduceGoods ( $uid, 39, 1 );
}

function useMingTie($uid, $username) {
	if (! checkGoods ( $uid, 84 )) {
		throw new Exception ( "not_enough_goods84" );
	}
	sql_query ( "update sys_user set name='$username' where uid='$uid'" );
	sql_query("update sys_city_hero set name='$username' where uid='$uid' and herotype=1000");
	reduceGoods ( $uid, 84, 1 );
}

function useFireBarrel($uid, $building, $cid, $xy) {
	if (! checkGoods ( $uid, 83 )) {
		throw new Exception ( "not_enough_goods83" );
	}
	$bid = $building ['bid'];
	$real_time_need = 0;
	sql_query ( "update sys_building set `state`='2',`state_starttime`=unix_timestamp(),
	`state_endtime`=unix_timestamp()+'$real_time_need'
	where `cid`='$cid' and `xy`='$xy'" );
	$dstlevel = 0; //将降级后的级别填入，结束时直接用这个级别计算
	sql_query ( "insert into mem_building_destroying (id,cid,xy,bid,level,state_endtime) values ('$building[id]','$cid','$xy','$bid','$dstlevel',unix_timestamp()+'$real_time_need')
	on duplicate key update `state_endtime`=unix_timestamp()+'$real_time_need'" );

	if ($building ['bid'] == 20) {
		sql_query ( "delete from sys_city_defence where cid=$cid " ); //20表示城墙
		sql_query ( "delete from mem_city_reinforce where cid=$cid " );
	}

	//sql_query("delete from mem_building_upgrading where cid='$cid' and xy='$xy'");
	//sql_query("delete from mem_building_destroying where cid='$cid' and xy='$xy'");
	//sql_query("delete from sys_building where cid='$cid' and xy='$xy'");
	reduceGoods ( $uid, 83, 1 );
}

function openGoldBar($uid, $cid, $gid,$useCount) {
	$goodCnt = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='$gid'");
	if(empty($goodCnt)||intval($goodCnt)<$useCount) throw new Exception($GLOBALS['duihuan']['not_enogh']);
	if($goodCnt>50) $goodCnt=50;
	$useCount=$goodCnt;
	if ($gid == 85){
		$goldAdd = 100000*$useCount;
	}else{
		$goldAdd = 1000000*$useCount;
	}
	addCityResources ( $cid, 0, 0, 0, 0, $goldAdd );
	reduceGoods ( $uid, $gid, $useCount );
	return sprintf ( $GLOBALS ['resPackage'] ['gain_gold'], $goldAdd );
}

function openResBox($uid, $cid, $gid,$useCount) {
	$goodCnt = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='$gid'");
	if(empty($goodCnt)||intval($goodCnt)<$useCount) throw new Exception($GLOBALS['duihuan']['not_enogh']);
	
	
	switch ($gid) {
		case 87 :
			$resAdd = 100000*$useCount;
			addCityResources ( $cid, 0, 0, 0, $resAdd, 0 );
			$msg = sprintf ( $GLOBALS ['resPackage'] ['gain_food'], $resAdd );
			break;
		case 88 :
			$resAdd = 100000*$useCount;
			addCityResources ( $cid, $resAdd, 0, 0, 0, 0 );
			$msg = sprintf ( $GLOBALS ['resPackage'] ['gain_wood'], $resAdd );
			break;
		case 89 :
			$resAdd = 100000*$useCount;
			addCityResources ( $cid, 0, $resAdd, 0, 0, 0 );
			$msg = sprintf ( $GLOBALS ['resPackage'] ['gain_rock'], $resAdd );
			break;
		case 90 :
			$resAdd = 100000*$useCount;
			addCityResources ( $cid, 0, 0, $resAdd, 0, 0 );
			$msg = sprintf ( $GLOBALS ['resPackage'] ['gain_iron'], $resAdd );
			break;
		case 91 :
			$resAdd = 1000000*$useCount;
			addCityResources ( $cid, 0, 0, 0, $resAdd, 0 );
			$msg = sprintf ( $GLOBALS ['resPackage'] ['gain_food'], $resAdd );
			break;
		case 92 :
			$resAdd = 1000000*$useCount;
			addCityResources ( $cid, $resAdd, 0, 0, 0, 0 );
			$msg = sprintf ( $GLOBALS ['resPackage'] ['gain_wood'], $resAdd );
			break;
		case 93 :
			$resAdd = 1000000*$useCount;
			addCityResources ( $cid, 0, $resAdd, 0, 0, 0 );
			$msg = sprintf ( $GLOBALS ['resPackage'] ['gain_rock'], $resAdd );
			break;
		case 94 :
			$resAdd = 1000000*$useCount;
			addCityResources ( $cid, 0, 0, $resAdd, 0, 0 );
			$msg = sprintf ( $GLOBALS ['resPackage'] ['gain_iron'], $resAdd );
			break;
	}
	reduceGoods ( $uid, $gid, $useCount );
	return $msg;
}

//装备包
function useArmorBox($uid, $gid) {
	if (! checkGoods ( $uid, $gid )) {
		throw new Exception ( $GLOBALS ['useGoods'] ['no_this_good'] );
	}
	$armor_column = sql_fetch_one_cell ( "select `armor_column` from sys_user where uid=$uid" );
	$curCount = sql_fetch_one_cell ( "select count(*) from sys_user_armor where uid='$uid' and hid=0" );
	if ($curCount >= $armor_column) {
		throw new Exception ( $GLOBALS ['useGoods'] ['armor_box_full'] );
	}

	$levelrate = mt_rand ( 1, 100 );

	$levelcondition = "";
	if ($levelrate < 80) {
		//80%概率落到最高将领5级以内的
		$maxlevel = sql_fetch_one_cell ( "select max(level)  from sys_city_hero where uid='$uid'" );
		if (empty ( $maxlevel )) {
			$maxlevel = 1;
		}
		$maxlevel += 5;
		$levelcondition = " and hero_level<'$maxlevel' ";
	}

	if ($gid < 101) {
		$rate = mt_rand ( 1, 100 );
		$type = $gid - 95 + 1;
		if ($gid == 95) {
			if ($rate <= 90)
			$type = 1;
			else if ($rate <= 99)
			$type = 2;
			else
			$type = 3;
		} else if ($gid == 96) {
			if ($rate <= 90)
			$type = 2;
			else
			$type = 3;
		}
		$armor = sql_fetch_one ( "select * from cfg_armor where type='$type' and inuse=1 and box_drop=1 $levelcondition order by rand() limit 1" );
		if (empty ( $armor )) {
			$armor = sql_fetch_one ( "select * from cfg_armor where type='$type' and inuse=1 and box_drop=1  order by rand() limit 1" );
		}
	} else {
		$part = $gid - 100;
		$randvalue = mt_rand ( 0, 100 );
		if ($randvalue <= 75)
		$type = 1;
		else if ($randvalue <= 95)
		$type = 2;
		else
		$type = 3;
		$armor = sql_fetch_one ( "select * from cfg_armor where part='$part' and type='$type' and inuse=1 and box_drop=1 $levelcondition order by rand() limit 1" );
		if (empty ( $armor )) {
			$armor = sql_fetch_one ( "select * from cfg_armor where part='$part' and type='$type' and inuse=1 and box_drop=1  order by rand() limit 1" );
		}
	}
	if (empty ( $armor )) {
		throw new Exception ( $GLOBALS ['useGoods'] ['invalid_data'] );
	}
	if (isSentGood ( $armor ['gid'] )) {
		$sendNames = array ();
		$sendNames [] = "“" . $armor ["name"] . "”" . $cnt;
		sendOpenBoxInform ( $sendNames, $armor ['value'] * $cnt, $uid, $gid );
	}
	addArmor ( $uid, $armor, 1, 3 );
	$armor ['hp'] = $armor ['ori_hp_max'];
	$armor ['hp_max'] = $armor ['ori_hp_max'];
	reduceGoods ( $uid, $gid, 1 );
	if($gid == 95) {
		completeTaskWithTaskid($uid, 310);
	} else if($gid == 112) {
		completeTaskWithTaskid($uid, 311);
	}
	$ret = array ();
	$armor ['gtype'] = 1;
	$ret [] = $armor;
	return $ret;
}

//资源礼包
function useResourcePackage($uid, $mygid) {
	if (! checkGoods ( $uid, $mygid )) {
		$name = sql_fetch_one_cell ( "select name from cfg_goods where gid='$mygid'" );

		$msg = sprintf ( $GLOBALS ['useResourcePackage'] ['no_ResourcePackage'], $name );
		throw new Exception ( $msg );
	}
	$cid = sql_fetch_one_cell ( "select lastcid from sys_user where uid='$uid'" );
	addCityResources ( $cid, 100000, 100000, 100000, 100000, 10000 );

	reduceGoods ( $uid, $mygid, 1 );
	return $GLOBALS ['useResourcePackage'] ['gain_resource'];
}
//GID>10000的活动宝物
function useHuodongGoods($uid, $mygid) {
	if (! checkGoods ( $uid, $mygid )) {
		$name = sql_fetch_one_cell ( "select name from cfg_goods where gid='$mygid'" );
		$msg = sprintf ( $GLOBALS ['useHuodongGoods'] ['no_HuoDongGoods'], $name );
		throw new Exception ( $msg );
	}
	$cid = sql_fetch_one_cell ( "select lastcid from sys_user where uid='$uid'" );
	$government_level = sql_fetch_one_cell ( "select level from sys_building where cid='$cid' and bid=" . ID_BUILDING_GOVERMENT );

	$ret = array ();
	if ($mygid == 10001) //新手礼包
	{
		return openDynamicBox ( $uid, $mygid );
	} else if ($mygid == 10002) //升级礼包，需要官府３
	{
		if ($government_level < 3)
		throw new Exception ( $GLOBALS ['useHuodongGoods'] ['govenment_lessThen_three'] );
		return openDynamicBox ( $uid, $mygid );
	} else //白银礼包I，需要官府２   
	//白银礼包II，需要官府４   
	//黄金礼包I，需要官府５   
	//黄金礼包II，需要官府７   
	//黄金礼包III，需要官府９   
	//超值建设礼包
	//超值城主礼包      
	if ($mygid == 10010) //功勋礼包
	{
		if ($government_level < 3)
		throw new Exception ( $GLOBALS ['useHuodongGoods'] ['lessThen_three_for_GongXun'] );
		addGift ( $uid, 50, 2 );
		$ret [] = array ("name" => $GLOBALS ['useHuodongGoods'] ['YuanBao'], "count" => "50", "gid" => 0 );
	} else if ($mygid == 10011) //生产礼包
	{
		return openDynamicBox ( $uid, $mygid );
	} else if ($mygid == 10012) //高级生产礼包
	{
		return openDynamicBox ( $uid, $mygid );
	} else if ($mygid == 10016) //伯乐包
	{
		$goodslist = array (22 => 10, 23 => 10 );
		foreach ( $goodslist as $gid => $cnt ) {
			addGoods ( $uid, $gid, $cnt, 6 );
			$ret [] = sql_fetch_one ( "select *,$cnt as count from cfg_goods where gid='$gid'" );
		}
	} else if ($mygid == 10018) //建设礼包
	{
		return openDynamicBox ( $uid, $mygid );
	} else if ($mygid == 10019) //城主礼包
	{
		return openDynamicBox ( $uid, $mygid );
	} else if ($mygid == 10020) //天御礼包内有“八卦阵图”1、“智多星”1、“虎符”1
	{
		$goodslist = array (7 => 1, 29 => 1, 26 => 1 );
		foreach ( $goodslist as $gid => $cnt ) {
			addGoods ( $uid, $gid, $cnt, 6 );
			$ret [] = sql_fetch_one ( "select *,$cnt as count from cfg_goods where gid='$gid'" );
		}
	} else if ($mygid == 10021) //武神礼包内有“陷阵战鼓”1、“武曲星”1、“虎符”1
	{
		$goodslist = array (6 => 1, 28 => 1, 26 => 1 );
		foreach ( $goodslist as $gid => $cnt ) {
			addGoods ( $uid, $gid, $cnt, 6 );
			$ret [] = sql_fetch_one ( "select *,$cnt as count from cfg_goods where gid='$gid'" );
		}
	} else if ($mygid == 10022) //遁世礼包内有“陷阵战鼓”1、“迁城令”2、“免战牌”2
	{
		$goodslist = array (24 => 2, 12 => 2 );
		foreach ( $goodslist as $gid => $cnt ) {
			addGoods ( $uid, $gid, $cnt, 6 );
			$ret [] = sql_fetch_one ( "select *,$cnt as count from cfg_goods where gid='$gid'" );
		}
	} else if ($mygid == 10023) //中包洗髓丹内有“洗髓丹”5
	{
		$goodslist = array (22 => 5 );
		foreach ( $goodslist as $gid => $cnt ) {
			addGoods ( $uid, $gid, $cnt, 6 );
			$ret [] = sql_fetch_one ( "select *,$cnt as count from cfg_goods where gid='$gid'" );
		}
	} else if ($mygid == 10024) //中包锦囊内有“锦囊”20
	{
		$goodslist = array (13 => 20 );
		foreach ( $goodslist as $gid => $cnt ) {
			addGoods ( $uid, $gid, $cnt, 6 );
			$ret [] = sql_fetch_one ( "select *,$cnt as count from cfg_goods where gid='$gid'" );
		}
	} else if ($mygid == 10072) //“礼上加礼”新手大礼包需要玩家脱离新手保护时才能使用。
	{
		$state = sql_fetch_one_cell ( "select state from sys_user where uid =$uid " );
		if ($state == 1)
		throw new Exception ( "你未脱离新手保护期，不能打开礼包。" );
		$goodslist = array (1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 54 => 1, 12 => 3, 56 => 1, 24 => 3, 67 => 1, 68 => 1, 69 => 1, 8 => 1, 65 => 1, 9 => 1, 63 => 1, 64 => 1, 40 => 1 );
		foreach ( $goodslist as $gid => $cnt ) {
			addGoods ( $uid, $gid, $cnt, 6 );
			$ret [] = sql_fetch_one ( "select *,$cnt as count from cfg_goods where gid='$gid'" );
		}
	}

	reduceGoods ( $uid, $mygid, 1 );
	return $ret;
}

//自荐状
function openHeroBox_taskreward($uid) {
	$cid = sql_fetch_one_cell("select lastcid from sys_user where uid=$uid");
	if(!checkGoods($uid, 156)) {
		throw new Exception($GLOBALS['useGoods']['hero_box_notenough']);
	}
	if(!cityHasHeroPosition($uid,$cid)) {
		throw new Exception($GLOBALS['useGoods']['not_enough_position']);
	}
	$sex = mt_rand(0,1);
	$name = generateHeroName($sex);
	$face = ($sex==0)?mt_rand(1,9):mt_rand(1001,1070);
	reduceGoods($uid, 156, 1);
	//固定属性
	$sql = "insert into sys_city_hero (`uid`,`name`,`sex`,`face`,`cid`,`state`,`level`,`exp`,`affairs_base`,`bravery_base`,`wisdom_base`,`loyalty`) "
	."values('$uid', '$name', '$sex', '$face', '$cid', 0, 1, 1, 70, 70, 70, 70)";
	sql_query($sql);
	$hid = sql_fetch_one_cell("select hid from sys_city_hero where name='$name' and sex='$sex' and face='$face' and uid='$uid' order by hid desc limit 1");
	sql_query("insert into mem_hero_blood(`hid`) values('$hid')");
	regenerateHeroAttri($uid,$hid);
	updateCityHeroChange($uid,$cid);
	return $GLOBALS['useGoods']['get_one_hero'];
}
//热血勇士召唤令
function openHeroBox_actreward($uid) {
	$cid = sql_fetch_one_cell("select lastcid from sys_user where uid=$uid");
	if(!checkGoods($uid, 10215)) {
		throw new Exception($GLOBALS['useGoods']['hero_box_notenough']);
	}
	if(!cityHasHeroPosition($uid,$cid)) {
		throw new Exception($GLOBALS['useGoods']['not_enough_position']);
	}
	$sex = mt_rand(0,1);
	//$name = generateHeroName($sex);
	$name = '热血勇士';
	$face = ($sex==0)?mt_rand(1,9):mt_rand(1001,1070);
	reduceGoods($uid, 10215, 1);
	//固定属性
	$sql = "insert into sys_city_hero (`uid`,`name`,`sex`,`face`,`cid`,`state`,`level`,`exp`,`affairs_base`,`bravery_base`,`wisdom_base`,`loyalty`,`command_base`,`herotype`) "
	."values('$uid', '$name', '$sex', '$face', '$cid', 0, 2, 100, 60, 80, 60, 70,30,142)";
	sql_query($sql);
	$hid = sql_fetch_one_cell("select hid from sys_city_hero where name='$name' and sex='$sex' and face='$face' and uid='$uid' order by hid desc limit 1");
	sql_query("insert into mem_hero_blood(`hid`) values('$hid')");
	regenerateHeroAttri($uid,$hid);
	updateCityHeroChange($uid,$cid);
	return $GLOBALS['useGoods']['get_one_hero'];
}
//统领召唤令
function openHeroBox_tongling($uid)
{
	$cid = sql_fetch_one_cell("select lastcid from sys_user where uid=$uid");
	if(!checkGoods($uid, 11227)) {
		throw new Exception($GLOBALS['useGoods']['hero_box_notenough']);
	}
	if(!cityHasHeroPosition($uid,$cid)) {
		throw new Exception($GLOBALS['useGoods']['not_enough_position']);
	}
	$name = $GLOBALS ['useGoods']['act_hero_tongling'];
	$face = mt_rand(1001,1070);
	reduceGoods($uid, 11227, 1);
	//固定属性
	$sql = "insert into sys_city_hero (`uid`,`name`,`sex`,`face`,`cid`,`state`,`level`,`exp`,`affairs_base`,`bravery_base`,`wisdom_base`,`loyalty`,`command_base`,`herotype`) "
	."values('$uid', '$name', '1', '$face', '$cid', 0, 2, 100, 90, 100, 90, 80,60,1001)";
	sql_query($sql);
	$hid = sql_fetch_one_cell("select hid from sys_city_hero where name='$name' and sex='1' and face='$face' and uid='$uid' order by hid desc limit 1");
	sql_query("insert into mem_hero_blood(`hid`) values('$hid')");
	regenerateHeroAttri($uid,$hid);
	updateCityHeroChange($uid,$cid);
	return $GLOBALS['useGoods']['get_one_hero'];
}
//侍卫召唤令
function openHeroBox_shiwei($uid)
{
	$cid = sql_fetch_one_cell("select lastcid from sys_user where uid=$uid");
	if(!checkGoods($uid, 11228)) {
		throw new Exception($GLOBALS['useGoods']['hero_box_notenough']);
	}
	if(!cityHasHeroPosition($uid,$cid)) {
		throw new Exception($GLOBALS['useGoods']['not_enough_position']);
	}
	$name = $GLOBALS ['useGoods']['act_hero_shiwei'];
	$face = mt_rand(1001,1070);
	reduceGoods($uid, 11228, 1);
	//固定属性
	$sql = "insert into sys_city_hero (`uid`,`name`,`sex`,`face`,`cid`,`state`,`level`,`exp`,`affairs_base`,`bravery_base`,`wisdom_base`,`loyalty`,`command_base`,`herotype`) "
	."values('$uid', '$name', '1', '$face', '$cid', 0, 2, 100, 70, 90, 70, 80,50,1002)";
	sql_query($sql);
	$hid = sql_fetch_one_cell("select hid from sys_city_hero where name='$name' and sex='1' and face='$face' and uid='$uid' order by hid desc limit 1");
	sql_query("insert into mem_hero_blood(`hid`) values('$hid')");
	regenerateHeroAttri($uid,$hid);
	updateCityHeroChange($uid,$cid);
	return $GLOBALS['useGoods']['get_one_hero'];
}
function openHeroBox_jiajiang($uid)
{
	$cid = sql_fetch_one_cell("select lastcid from sys_user where uid=$uid");
	if(!checkGoods($uid, 12051)) {
		throw new Exception($GLOBALS['useGoods']['hero_box_notenough']);
	}
	if(!cityHasHeroPosition($uid,$cid)) {
		throw new Exception($GLOBALS['useGoods']['not_enough_position']);
	}
	$name = $GLOBALS['useGoods']['act_hero_jiajiang'];
	$face = mt_rand(1001,1070);
	reduceGoods($uid, 12051, 1);
	//固定属性
	$sql = "insert into sys_city_hero (`uid`,`name`,`sex`,`face`,`cid`,`state`,`level`,`exp`,`affairs_base`,`bravery_base`,`wisdom_base`,`loyalty`,`command_base`,`herotype`) "
	."values('$uid', '$name', '1', '$face', '$cid', 0, 2, 100, 70, 80, 70, 80,70,1003)";
	sql_query($sql);
	$hid = sql_fetch_one_cell("select hid from sys_city_hero where name='$name' and sex='1' and face='$face' and uid='$uid' order by hid desc limit 1");
	sql_query("insert into mem_hero_blood(`hid`) values('$hid')");
	regenerateHeroAttri($uid,$hid);
	updateCityHeroChange($uid,$cid);
	return $GLOBALS['useGoods']['get_one_hero'];
}
function openunionandbattleBox($uid,$gid){
     $usenames=sql_fetch_one_cell("select name from sys_user where uid='$uid'");
	 $usename=$usenames.',打开战场礼包,获得了:';
	 if($gid==8894) {$usename=$usenames.',打开联盟礼包,获得了:';}
	 $gcnt=mt_rand(1,5);
	 $okmsg="恭喜你获得:";
	 for($i=0;$i<$gcnt;$i++){
	     $rate=mt_rand(1,105);
		 $goodtype=5;//普通
		 if($rate%6==0) $goodtype=1;//战场类12%
		 else if(($rate%7==0)) $goodtype=2;//镶嵌类16%
	      else if($rate%9==0) $goodtype=3;//装备类11%
		    else if(($rate%11==0)) $goodtype=4;//马装类10%
		 $cnt=mt_rand(2,5);
		 $mmt=0;
		 switch($goodtype){
		     case 1:{//战场类
			     $goodname='荣誉:';
				 $goodid=mt_rand(0,15);
				 if($goodid>10){
				     $cnt=$cnt*mt_rand(993,1841);
				     sql_query("update sys_user set honour=honour+'$cnt' where uid='$uid'");//荣誉
					 $smsg="恭喜玩家:".$usename.$goodname.$cnt;
					 $color=3316172;
					}
				  else{
			         if($goodid<8) $goodid=$goodid+30000;
					  else if($goodid==8 || $goodid==9) $goodid=31008;
					   else if($goodid==10) $goodid=30010;
					 $cnt=$cnt*3;
					 $goodname=sql_fetch_one_cell("select name from cfg_things where tid='$goodid'");
					 addThings($uid,$goodid,$cnt,3);
					 $smsg="恭喜玩家:".$usename.$goodname.$cnt."个！";
					 $color=3289805;
				    }
			     sendSysInform(0,1,0,600,50000,1,$color,$smsg);
				 break;
			    }
			 case 2:{
			     $isok=mt_rand(0,1);
				 if($isok){
				     $goodidss=mt_rand(0,79);//10级以下的镶嵌珠宝id
					 $goodid=$goodidss+300;
					}else{
					  $goodidss=mt_rand(401,419);
					  $goodid=$goodidss*100+mt_rand(1,6);//6级以下的技能书id
					}
				 $getrands=mt_rand(0,10);
			     if($getrands%3==0){//开到10级以上的镶嵌珠宝
				       if($isok)
				          $goodid=mt_rand(17500,17539);//10以上的镶嵌珠宝id
						 else{
						     $goodidss=mt_rand(401,419);
							 $goodid=$goodidss*100+10;//10级技能书id
						    }
					 $mmt=1;
					}
			     break;
			    }
			 case 3:{
			     $goodid=mt_rand(10714,10729);
			     $getrands=mt_rand(0,10);
			     if($getrands==1 || $getrands==7){//开到极品装
				      $goodid=8889;
					  $cnt=1;
					  $mmt=1;
					}
			     break;
			    }
			 case 4:{
			     $goodid=mt_rand(10219,10223);
				 $getrands=mt_rand(0,10);
			     if($getrands==2 || $getrands==6){//开到极品马装
				      $goodid=10617;
					  $cnt=1;
					  $mmt=1;
				    }
				 break;
			    }
			 default:{
			      $goodid=mt_rand(1,125);
				  if($goodid<101 && $goodid>97) $goodid=97;
			      break;
			    }
		    }
		 if($goodtype>1){
		     $goodname=sql_fetch_one_cell("select name from cfg_goods where gid='$goodid'");
			 $smsg="恭喜玩家:".$usename.$goodname.$cnt."个！";
			 $color=3316172;
			 if($mmt==1) $color=14381203;
			 sendSysInform(0,1,0,600,50000,1,$color,$smsg);
			 addGoods($uid,$goodid,$cnt,3);
		    }
		 $okmsg.=$goodname.$cnt.';';
		}
     reduceGoods($uid, $gid, 1);
	 return $okmsg;
    }
function openlongyuantieArmorBox($uid,$gid){
    $tieid = array(0=>53029,1=>53030,2=>53031,3=>53032,4=>53033,5=>53034,6=>53035,7=>53036,8=>53037,9=>53037,10=>53038,11=>53038,12=>53039,13=>53039,14=>53039,15=>53040);
	$msg="恭喜你获得:绝世的龙渊1套16件装备!";
	if($gid==8891){
	  $tieid = array(0=>53041,1=>53042,2=>53043,3=>53044,4=>53045,5=>53046,6=>53047,7=>53048,8=>53049,9=>53049,10=>53050,11=>53050,12=>53051,13=>53051,14=>53051,15=>53052);
	  $msg="恭喜你获得:绝世的白虎1套16件装备!";
	}
    for($i=0;$i<16;$i++){
	 $curArmor=sql_fetch_one("select * from cfg_armor where id='$tieid[$i]'");
     addArmor($uid,$curArmor,1,999,0,7);
	}
	reduceGoods($uid, $gid, 1);
	return $msg;
}
function openlongyuanArmorBox($uid,$gid){
    $getrand=mt_rand(0,100);
	$use_info=0;
    switch($gid){
	  case 8887:{$gidid=mt_rand(53029,53039);$baohename="龙渊";break;}
	  case 8889:{$gidid=mt_rand(53041,53051);$baohename="绝世白虎";break;}
	  case 8888:{$gidid=mt_rand(53029,53039);$baohename="绝世龙渊";break;}
	  case 8890:{$gidid=mt_rand(53041,53051);$baohename="白虎";break;}
	}
	if($getrand>85 && $getrand<91){
	  $gidid=53040;
	  $gidname="龙渊宝马！";
	  if($gid==8889 || $gid==8890){
	    $gidid=53052;
		$gidname="白虎神兽！";
	  }
	  $use_info=1;
	}
	$curArmor = sql_fetch_one("select * from cfg_armor where id='$gidid'");
	$gidname=sql_fetch_one_cell("select name from cfg_armor where id='$gidid'");
	$usename=sql_fetch_one_cell("select name from sys_user where uid='$uid'");
	if($gid==8889 || $gid==8888){
	  addArmor($uid,$curArmor,1,999,0,7);
	  $msg="恭喜玩家:".$usename.",打开".$baohename."装备匣,获得了:绝世的".$gidname;
	}
	else{
	  addArmor($uid,$curArmor,1,3,0,0);
	  $msg="恭喜玩家:".$usename.",打开".$baohename."装备匣,获得了:".$gidname;
	}
	if($use_info){
	  sendSysInform(1,1,0,600,50000,1,16247152,$msg);//第一个参数0 聊天室，1顶部
	}else{
	  sendSysInform(0,1,0,600,50000,1,16247152,$msg);//第一个参数0 聊天室，1顶部
	}
	reduceGoods($uid, $gid, 1);
	return $msg;
}
function openHeroBox_actHero($uid,$gid,$name,$herotype,$level,$affairs_base,$bravery_base,$wisdom_base,$command_base,$sex=-1,$face=-1) {
	$cid = sql_fetch_one_cell("select lastcid from sys_user where uid=$uid");
	if(!checkGoods($uid,$gid)) {
		throw new Exception($GLOBALS['useGoods']['hero_box_notenough']);
	}
	if(!cityHasHeroPosition($uid,$cid)) {
		throw new Exception($GLOBALS['useGoods']['not_enough_position']);
	}
	if($sex==-1){
		$sex = mt_rand(0,1);
	}
	//$name = generateHeroName($sex);
	if($face==-1){
		$face = ($sex==0)?mt_rand(1,9):mt_rand(1001,1070);
	}
	reduceGoods($uid, $gid, 1);
	//生成三项基本属性的比值
	$affairs_rate = mt_rand(300,900);
	$bravery_rate = mt_rand(300,900);
	$wisdom_rate = mt_rand(300,900);
	$all_rate = $affairs_rate + $bravery_rate + $wisdom_rate;
	$affairs_add = round($level * $affairs_rate / $all_rate);
	$bravery_add = round($level * $bravery_rate / $all_rate);
	$wisdom_add  = $level - $affairs_add - $bravery_add;
	$hero_total_exp = sql_fetch_one_cell("select total_exp from cfg_hero_level where level='$level'");
	$sql = "insert into sys_city_hero (`uid`,`name`,`sex`,`face`,`cid`,`state`,`level`,`exp`,`affairs_base`,`bravery_base`,`wisdom_base`,`loyalty`,`command_base`,`herotype`,`affairs_add`,`bravery_add`,`wisdom_add`) "
	."values('$uid', '$name', '$sex', '$face', '$cid', 0, '$level','$hero_total_exp' , '$affairs_base', '$bravery_base', '$wisdom_base', 70,'$command_base','$herotype','$affairs_add','$bravery_add','$wisdom_add')";
	sql_query($sql);
	$hid = sql_fetch_one_cell("select hid from sys_city_hero where name='$name' and sex='$sex' and face='$face' and uid='$uid' order by hid desc limit 1");
	sql_query("insert into mem_hero_blood(`hid`) values('$hid')");
	regenerateHeroAttri($uid,$hid);
	updateCityHeroChange($uid,$cid);
	return $GLOBALS['useGoods']['get_one_hero'];
}

function openHeroBox($uid, $cid, $gid) {
	if (cityHasHeroPosition ( $uid, $cid )) {
		$alreadyCount = sql_fetch_one_cell ( "select count(*) from sys_city_hero where uid='$uid' and `herotype`=2" );
		if ($alreadyCount >= 10) {
			throw new Exception ( $GLOBALS ['activity'] ['hero_count_limit'] );
		}
		$levelRand = mt_rand ( 1, 100 );
		$level = 20;
		if ($levelRand < 35)
		$level = mt_rand ( 20, 29 );
		else if ($levelRand < 65)
		$level = mt_rand ( 30, 39 );
		else if ($levelRand < 85)
		$level = mt_rand ( 40, 49 );
		else if ($levelRand < 95)
		$level = mt_rand ( 50, 59 );
		else
		$level = mt_rand ( 60, 70 );

		$attriLevel = mt_rand ( 1, 100 );
		$attri1 = 30;
		$attri2 = 30;
		$attri3 = 70;
		$heroNamePre = $GLOBALS ['activity'] ['hero_type_name1'];
		if ($attriLevel < 35) {
			$attri3 = 70;
			$heroNamePre = $GLOBALS ['activity'] ['hero_type_name1'];
		} else if ($attriLevel < 65) {
			$attri3 = 75;
			$heroNamePre = $GLOBALS ['activity'] ['hero_type_name2'];
		} else if ($attriLevel < 85) {
			$attri3 = 80;
			$heroNamePre = $GLOBALS ['activity'] ['hero_type_name3'];
		} else if ($attriLevel < 95) {
			$attri3 = 85;
			$heroNamePre = $GLOBALS ['activity'] ['hero_type_name4'];
		} else {
			$attri3 = 89;
			$heroNamePre = $GLOBALS ['activity'] ['hero_type_name5'];
		}

		$totalAttri = $attri1 + $attri2 + $attri3;
		$attriadd1 = floor ( ($attri1 / $totalAttri) * $level );
		$attriadd2 = floor ( ($attri2 / $totalAttri) * $level );
		$attriadd3 = $level - $attriadd1 - $attriadd2;
		//生成一个随机性别
		$sex = (mt_rand ( 0, 9 ) == 0) ? 0 : 1; //10分之一的机率
		//男人有859个头像，女人有105个头像 
		$face = ($sex == 0) ? mt_rand ( 1, 9 ) : mt_rand ( 1001, 1070 );
		$hero_exp = sql_fetch_one_cell ( "select total_exp from cfg_hero_level where level='$level'" );
		$loyalty = 70;
		$heroType = (mt_rand () % 3);
		if ($heroType == 0) //谋士
		{
			$heroname = $heroNamePre . $GLOBALS ['activity'] ['wisdom_hero'];
			$sql = "insert into sys_city_hero (`uid`,`name`,`sex`,`face`,`cid`,`state`,`level`,`exp`,`affairs_base`,`bravery_base`,`wisdom_base`,`affairs_add`,`bravery_add`,`wisdom_add`,`loyalty`,`herotype`) values ('$uid','$heroname','$sex','$face','$cid','0','$level','$hero_exp','$attri1','$attri2','$attri3','$attriadd1','$attriadd2','$attriadd3','$loyalty',2)";
			$forcemax = 100 + floor ( $level / 5 ) + floor ( ($attri2 + $attriadd2) / 3 );
			$energymax = 100 + floor ( level / 5 ) + floor ( ($attri3 + $attriadd3) / 3 );
		} else if ($heroType == 1) //政客
		{
			$heroname = $heroNamePre . $GLOBALS ['activity'] ['affairs_hero'];
			$sql = "insert into sys_city_hero (`uid`,`name`,`sex`,`face`,`cid`,`state`,`level`,`exp`,`affairs_base`,`bravery_base`,`wisdom_base`,`affairs_add`,`bravery_add`,`wisdom_add`,`loyalty`,`herotype`) values ('$uid','$heroname','$sex','$face','$cid','0','$level','$hero_exp','$attri3','$attri1','$attri2','$attriadd3','$attriadd1','$attriadd2','$loyalty',2)";
			$forcemax = 100 + floor ( $level / 5 ) + floor ( ($attri1 + $attriadd1) / 3 );
			$energymax = 100 + floor ( level / 5 ) + floor ( ($attri2 + $attriadd2) / 3 );
		} else if ($heroType == 2) //武将
		{
			$heroname = $heroNamePre . $GLOBALS ['activity'] ['bravery_hero'];
			$sql = "insert into sys_city_hero (`uid`,`name`,`sex`,`face`,`cid`,`state`,`level`,`exp`,`affairs_base`,`bravery_base`,`wisdom_base`,`affairs_add`,`bravery_add`,`wisdom_add`,`loyalty`,`herotype`) values ('$uid','$heroname','$sex','$face','$cid','0','$level','$hero_exp','$attri1','$attri3','$attri2','$attriadd1','$attriadd3','$attriadd2','$loyalty',2)";
			$forcemax = 100 + floor ( $level / 5 ) + floor ( ($attri3 + $attriadd3) / 3 );
			$energymax = 100 + floor ( level / 5 ) + floor ( ($attri2 + $attriadd2) / 3 );
		}
		//招人
		$hid = sql_insert ( $sql );
		sql_query ( "insert into mem_hero_blood (`hid`,`force`,`force_max`,`energy`,`energy_max`) values ('$hid',100,$forcemax,100,$energymax)" );
		updateCityHeroChange ( $uid, $cid );
		reduceGoods ( $uid, $gid, 1 );
		return sprintf ( $GLOBALS ['activity'] ['get_hero_tip'], $heroname );
	} else {
		throw new Exception ( $GLOBALS ['recruitHero'] ['hotel_level_low'] );
	}
}


function xiuJia($uid, $day) {
	//军队在外，不能休假
	if (sql_check ( "select uid from sys_troops where uid='$uid'" )) {
		throw new Exception ( $GLOBALS ['changeUserState'] ['army_out'] . $GLOBALS ['changeUserState'] ['xiujia'] );
	}
	//科技在升级，不能休假
	if (sql_check ( "select uid from sys_technic where uid='$uid' and state=1" )) {
		throw new Exception ( $GLOBALS ['changeUserState'] ['technic_upgrading'] . $GLOBALS ['changeUserState'] ['xiujia'] );
	}
	$mycities = sql_fetch_rows ( "select cid from sys_city where uid='$uid'" );
	$comma = "";
	$mycids = "";
	foreach ( $mycities as $city ) {
		$mycids .= $comma . $city ['cid'];
		$comma = ",";
	}
	//建筑在升级，不能休假
	if (sql_check ( "select id from sys_building where cid in ($mycids) and state<>0" )) {
		throw new Exception ( $GLOBALS ['changeUserState'] ['building_upgrading'] . $GLOBALS ['changeUserState'] ['xiujia'] );
	}

	//有兵营训练队列，不能休假
	if (sql_check ( "select id from sys_city_draftqueue where cid in ($mycids)" )) {
		throw new Exception ( $GLOBALS ['changeUserState'] ['soldier_queue'] . $GLOBALS ['changeUserState'] ['xiujia'] );
	}
	//有城防制造队列，不能休假
	if (sql_check ( "select id from sys_city_reinforcequeue where cid in ($mycids)" )) {
		throw new Exception ( $GLOBALS ['changeUserState'] ['defence_queue'] . $GLOBALS ['changeUserState'] ['xiujia'] );
	}

	//有城池在战乱，不能休假
	foreach ( $mycities as $city ) {
		if (sql_check ( "select * from mem_world where wid=" . cid2wid ( $city ['cid'] ) . " and state=1" )) {
			throw new Exception ( $GLOBALS ['changeUserState'] ['some_city_in_war'] . $GLOBALS ['changeUserState'] ['xiujia'] );
		}
	}
	
	//先检查是否处于休假冷却时期内
	$coolingRecord = sql_fetch_one("select bufparam, endtime,endtime-unix_timestamp() as lefttime from  mem_user_buffer where uid='$uid' and buftype=23");
	if (empty($coolingRecord)) {
		$lastVacEnd = sql_fetch_one_cell("select vacend from sys_user_state where uid='$uid'");
		if (!empty($lastVacEnd) && $lastVacEnd != 0) {//后台还未处理完毕上一次的休假记录
			throw new Exception($GLOBALS['changeUserState']['not_process_XiuJia']);
		}
		//自动把盟友的军队遣返
		foreach($mycities as $city)
		{
			//联盟在本城的驻军
			$cityid=$city['cid'];
			sql_query("update sys_troops set `state`=1,endtime=pathtime+unix_timestamp(),starttime=unix_timestamp() where targetcid='$cityid' and state=4 and task=1 and cid <> '$cityid'");
			//联盟在野地的驻军
			$myfields=sql_fetch_rows("select wid from mem_world where ownercid='$cityid' and type > 1");
			if(!empty($myfields))
			{
				$fieldcids="";
				$comma="";
				foreach($myfields as $field)
				{
					$fieldcids .=$comma.wid2cid($field['wid']);
					$comma=",";
				}
				sql_query("update sys_troops set `state`=1,endtime=pathtime+unix_timestamp(),starttime=unix_timestamp() where targetcid in ('$fieldcids') and task=1 and state=4 and cid <>'$cityid'");
			}
		}
		
		//开始休假
		$vactime = $day * 86400;
		sql_query("insert into sys_user_state (uid,vacstart,vacend) values ('$uid',unix_timestamp(),unix_timestamp()+'$vactime') on duplicate key update vacstart=unix_timestamp(),vacend=unix_timestamp()+'$vactime'");
		sql_query("update mem_city_resource set vacation=1 where cid in ($mycids)");
		
		//记录下 增加休假冷却时间后的总时间
		$coolingTime = floor(($vactime*0.2)/8);
		$totalDuringTime = $vactime+$coolingTime;
		sql_query("insert into mem_user_buffer(`uid`,`buftype`,`endtime`) values('$uid','913',unix_timestamp()+$totalDuringTime) on duplicate key update endtime=unix_timestamp()+$totalDuringTime");  //913为休假冷却buffer
				
	} else {//正处于休假冷却时期
		$delta = $coolingRecord['lefttime'];//再次使用休战所需等待的秒数
		$oldCoolingTime = $coolingRecord['bufparam'];//冷却记录的冷却时间
		throw new Exception(sprintf($GLOBALS['changeUserState']['wait_to_use_XiuJia'], $oldCoolingTime, MakeTimeLeft($delta)));
	}
		
//	//开始休假，并扣钱
//	$vactime = $day * 86400;
//	sql_query ( "insert into sys_user_state (uid,vacstart,vacend) values ('$uid',unix_timestamp(),unix_timestamp()+'$vactime') on duplicate key update vacstart=unix_timestamp(),vacend=unix_timestamp()+'$vactime'" );
//	sql_query ( "update mem_city_resource set vacation=1 where cid in ($mycids)" );
}

function useXiuJiaFu($uid, $cid, $mygid) {
	if (! checkGoods ( $uid, $mygid )) {
		$name = sql_fetch_one_cell ( "select name from cfg_goods where gid='$mygid'" );
		$msg = sprintf ( $GLOBALS ['useHuodongGoods'] ['no_HuoDongGoods'], $name );
		throw new Exception ( $msg );
	}
	$day = 3;
	if ($mygid == 122)
	$day = 10;
	xiuJia ( $uid, $day );
	reduceGoods ( $uid, $mygid, 1 );
	$endtime = intval ( sql_fetch_one_cell ( "select unix_timestamp()" ) ) + $day * 86400;
	file_put_contents ( "./sessions/" . $uid, "0" );
	file_put_contents ( "/bloodwar/server/game/sessions/" . $uid, "0" );
	//return "休假开始，你会自动掉线。休假将于" . MakeEndTime ( $endtime ) . "结束！";
	return sprintf($GLOBALS['goods']['start_xiujia'], MakeEndTime ( $endtime ));
}

/**
 *
 * 打开设置了cfg_box_details的道具，一般会活动中的道具
 * $srctype=0 表示道具礼盒，$srcid对应cfg_goods表中的gid, 主要用于大乐透活动
 * $srctype=1 表示从宝藏图中开出， $srcid对应cfg_act表中的id， 主要用于寻宝活动
 * $srctype=2 表示客栈刷将领开出， $srcid对应表cfg_recruit_hero中的id， 主要用于客栈刷将领活动
 *
 * $basecount为乘数基数
 */
function openDefaultBox($uid,$cid,$srcid,$srctype,$basecount=1){
	//	include_once 'TaskFunc.php';
	if ($srctype==0){//扣箱子
		$gid = $srcid;
		if (!checkGoods($uid,$srcid)){
			$name = sql_fetch_one_cell("select name from cfg_goods where gid='$gid'");
			$msg = sprintf($GLOBALS['useHuodongGoods']['no_HuoDongGoods'],$name);
			throw new Exception($msg);
		}
		$boxCon=sql_fetch_one("select * from cfg_box_open_condition where boxid= $gid or keyid = $gid");
		if ($boxCon){
			unlockUser($uid);
			if ($gid==$boxCon["boxid"]){
				if (!checkGoods($uid,$boxCon["keyid"])){
					throw new Exception($boxCon["nokeydesc"]);
				}
			}else{
				if (!checkGoods($uid,$boxCon["boxid"])){
					throw new Exception($boxCon["noboxdesc"]);
				}
				$srcid=$boxCon["boxid"];
			}
		}
	}
	$rows=sql_fetch_rows("select * from cfg_box_details where srctype=$srctype and srcid='$srcid'");
	if(empty($rows)){
		$msg=$GLOBALS['useGoods']['func_not_in_use'];
		if ($srctype==0){//箱子
			throw new Exception($msg);
		}
		return false;//不给东西
	}
	$lucky = false;
	$act252Rate = sql_fetch_one_cell("select rate from cfg_act where actid = 252 and unix_timestamp() between starttime and endtime");
	if($act252Rate>0 && ($gid==10211||$gid==10212)){
		$rand=mt_rand(1,100);
		if($rand<=$act252Rate){		//没爆
			$lucky=true;
			$lucyInfo = sql_fetch_one("select * from log_act where uid='$uid' and actid=252 and sort=2 and type=10212 and log_type=0");
			if (empty($lucyInfo)) {
				sql_query("insert into log_act (uid, actid, sort, type, count, log_type, time) values ($uid, 252, 2, 10212, 1, 0, unix_timestamp())");
			}else{
				sql_query("update log_act set count=count+1 where uid='$uid' and actid=252 and sort=2 and type=10212 and log_type=0");
			}
			if($lucyInfo['count']>=10-1){
				finishAchivement($uid,100009);
			}
		}else{
			sql_query("delete from log_act where uid='$uid' and actid=252 and sort=2 and type=10212 and log_type=0");
		}
	}
	if ($srctype==0){//扣箱子
		if ($boxCon){
			reduceGoods($uid,$boxCon["boxid"],1);
			if(!$lucky){
				reduceGoods($uid,$boxCon["keyid"],1);
			}
		}else{
			reduceGoods($uid,$srcid,1);
		}
	}

	$row =  getOpenDefaultGoodsResult($uid,$srctype,$srcid);
	if(empty($row)){
		return false;//达到上限了不得东西
	}
	$sort = $row["sort"];
	$type = $row["type"];
	$count = $row["count"];
	$limit = $row['limit'];//可以开出上限
	 

	//记得在物品描述中提醒招贤馆没空位会导致不能得到将领
	//这段代码不好，以后用到时要改
	if ($sort==7){ //活动将领 
		return openDefaultHeroBox($uid,$cid,$srctype,$type);
	}
    return openDefaultGoodsNow($uid,$cid,$srcid,$srctype,$sort,$type,$count,$basecount,$limit);
}

function getOpenDefaultGoodsResult($uid,$srctype,$srcid){
	$gift = pickOneFromBoxConifg($srctype, $srcid);
	if(empty($gift)){
		return false;
	}
	$giftSort = intval($gift['sort']);
	$giftId = intval($gift['type']);
	$actid = intval($srcid);

	$dayLeft = 2000000000;
	$totalLeft = 2000000000;
	$ownLeft = 2000000000;
	$hasLimit = false;

	if ($gift['dayopencount']>0){//每日限制
		$hasLimit = true;
		$currentCount = sql_fetch_one_cell("select sum(count) from log_act where uid=$uid and actid=$actid and sort=$giftSort and type=$giftId and log_type=$srctype and time>=unix_timestamp(curdate())");
		$dayLeft = $gift['dayopencount'] - $currentCount;
	}
	if ($gift['totalopencount']>0){//总活动限制
		$hasLimit = true;
		$currentCount = sql_fetch_one_cell("select sum(count) from log_act where uid=$uid and actid=$actid and sort=$giftSort and type=$giftId and log_type=$srctype");
		$totalLeft = $gift['totalopencount']-$currentCount;
	}
	if ($gift['owncount']>0){//全服活动限制
		$hasLimit = true;
		$currentCount = sql_fetch_one_cell("select sum(count) from log_act where actid=$actid and sort=$giftSort and type=$giftId and log_type=$srctype");
		$ownLeft = $gift['owncount']- $currentCount;
	}
	if ($hasLimit) {
		$cnt = min($dayLeft,$totalLeft,$ownLeft);
		if($cnt<=0 ){
			return false;
		}
		$gift['limit'] = $cnt;
	}
	return $gift;
}
function openDefaultGoodsNow($uid,$cid,$srcid,$srctype,$sort,$type,$count,$basecount=1,$limit=0){
	$srcid = intval($srcid);
	$srctype = intval($srctype);
	$sort = intval($sort);
	$type = intval($type);
	$count = intval($count);
    if($srctype==6000) $basecount=mt_rand(1,3);
	$row = sql_fetch_one("select * from cfg_box_details where srcid = $srcid and srctype = $srctype and sort= $sort and type = $type and count= $count");
	if(empty($row)){
		return false;
	}
	$row["count"]=$row["count"]*$basecount;
	if ($limit>0) {
		$row["count"] = min($row["count"],$limit);
	}
	$cnt = $row["count"];
	$inform = $row["inform"];
	$stronglevel = $row["temp"];
	/********************活动总量限制Begin************************/
	if($srctype==1000){
			$acts=sql_fetch_rows("select * from cfg_act where type between 1000 and 2000 and actid='$srcid'");
			foreach($acts as $act){
				$starttime = $act['starttime'];
				$endtime = $act['endtime'];
				$totalcount = $act['totalcount'];
				$datcount = $act['daycount'];
				if($datcount>0){
					$userdaycount = sql_fetch_one_cell("select sum(count)  from log_act  where log_type=1000 and count>0 and time >=unix_timestamp(curdate()) and uid='$uid' and actid='$srcid'");
				}
				if($totalcount>0){
					$usertotalcount = sql_fetch_one_cell("select sum(count)  from log_act  where log_type=1000 and count>0 and time between $starttime and  $endtime and uid='$uid' and actid='$srcid'");
				}
				if((($totalcount>0)&&($usertotalcount>=$totalcount))||(($datcount>0)&&($userdaycount>=$datcount))){
					return false;
				}else if(($totalcount>0)||($datcount>0)){
					sql_query("insert into log_act (uid, actid, sort, type, count, log_type, time) values ($uid, $srcid, $sort, $type, $cnt, $srctype, unix_timestamp())");
				}

		}
	}
	/********************活动总量限制End************************/
	$goodgetsname=giveReward($uid,$cid,$row,6,20,true,$stronglevel);
	if($row['dayopencount']>0 ||$row['totalopencount']>0 || $row['owncount']>0){//限量奖励
		sql_query("insert into log_act (uid, actid, sort, type, count, log_type, time) values ($uid, $srcid, $sort, $type, $cnt, $srctype, unix_timestamp())");
	}

	if ($sort==2 && $type>=19990 && $type<=19999) {//19990-19999为活动令牌，对玩家不可见
		return false;
	}
	if($sort==2&&($type==160042||$type==160043||$type==160029||$type==160049||$type==160026||$type==160046 ||$type==0||$type==-100)){
		$inform=1;
	}

	if ($inform){ //inform==1表示需要通知用户
		$uname=sql_fetch_one_cell("select name from sys_user where uid='$uid'");
		$uname=addslashes($uname);

		if (($srctype>=100 && $srctype<=103) || $srctype==10) {//每日登录奖励 10:登录奖励活动
			$msg = sprintf($GLOBALS['dailyreward']['inform'] ,$uname,$goodgetsname);
		}else if ($srctype==0){
			$goodname = sql_fetch_one_cell("select name from cfg_goods where gid='$srcid'");
			$msg = sprintf($GLOBALS['goodsopen']['inform'] ,$uname,$goodname.$goodgetsname);
		}else if($srctype==2400){  //寻宝活动
			$msg = sprintf($GLOBALS['treasureResult']['inform'] ,$uname,$goodgetsname);
		}else if($srctype==6){//商城活动
			$msg = sprintf($GLOBALS['shopReward']['inform'] ,$uname,$goodgetsname);
		}else if($srctype==2000){//招募将领
			$heroname = sql_fetch_one_cell("select heroname from cfg_recruit_hero where herotype='$srcid'");
			$msg = sprintf($GLOBALS['getHero']['inform'],$uname, $heroname, $goodgetsname);
		}else if($srctype==11){//招降将领
			$heroname = sql_fetch_one_cell("select heroname from cfg_recruit_hero where herotype='$srcid'");
			$msg = sprintf($GLOBALS['summonHero']['inform'],$uname, $heroname, $goodgetsname);
		}else if ($srctype==3000) {//战场
			$acttype=sql_fetch_one_cell("select type from cfg_act where actid=$srcid");
			$bid = (($actType-3000)/100)*1000+1;
			$battleFiledName = $GLOBALS['battle']['name'][$bid];
			$msg = sprintf($GLOBALS['battle']['inform'],$uname,$battleFiledName,$goodgetsname);
		}else if ($srctype==1000) {//充值活动
			$msg = sprintf($GLOBALS['pay']['inform'],$uname,$goodgetsname);
		}else if ($srctype==2100) {//鉴定宝藏
			$msg = sprintf($GLOBALS['treasureIdentifyReward']['inform'],$uname,$goodgetsname);
		}else if ($srctype==6000) {//强化装备
			$msg = sprintf($GLOBALS['equipment']['inform'],$uname,$goodgetsname);
		}else if ($srctype==2300){//野地采集
			$msg = sprintf($GLOBALS['gatherField']['inform'],$uname,$goodgetsname);
		}
		if($msg){
			sendSysInform(0,1,0,300,1800,1,49151,$msg);
		}
	}

	if ($srctype==0 ||$srctype==8){
		$ret=array();
		if ($sort == 2){
			$ret[] = sql_fetch_one("select *,$cnt as count from cfg_goods where gid='$type'");
		}else if ($sort == 5){
			$thing=sql_fetch_one("select *,$cnt as count from cfg_things where tid='$type'");
			$thing['gtype']=2;
			$ret[] = $thing;
		}else if ($sort==6){
			$armor=sql_fetch_one("select * from cfg_armor where id='$type'");
			$armor['cnt']=1;
			$armor['gtype']=1;
			$armor['hp']=$armor['ori_hp_max'];
			$armor['hp_max']=$armor['ori_hp_max'];
			$ret[] = $armor;
		}else if ($sort==1){
			if ($type == 1){
				$gid = 85;
			}else if ($type == 2){
				$gid = 87;
			}else if ($type == 3){
				$gid = 88;
			}else if ($type == 4){
				$gid = 89;
			}else if ($type == 5){
				$gid = 90;
			}
			if ($type >= 1 && $type <= 5) {
				$ret [] = array ("name" => $GLOBALS ['resPackage'] ['res_' . $type], "count" => $cnt, "gid" => $gid, "description" => $GLOBALS ['resPackage'] ['res_' . $type], "imagetype" => 0, "image" => 0 );
			}
		}else if ($sort==10||$sort==11){
			$goodgetsname=sprintf("开启任务组【%s】",$goodgetsname);
		}

		if (count($ret)==0) throw new Exception($goodgetsname);
		return $ret;
	}else if ($srctype==1000){
		$ret=array();
		$matches = array();
		preg_match("/(?<=【).+?(?=\\*)/", $goodgetsname, $matches);
		$name = $matches[0];
		if ($sort==10){
			$ret[] = $goodgetsname;
			$ret[] = 0;
			return $ret;
		}
		if (!$name) {
			preg_match("/(?<=获得).+?(?=\\d+)/", $goodgetsname, $matches);
			$name = $matches[0];
		}
		$ret[] = $name;
		$ret[] = $cnt;
		return $ret;
	}
	return $goodgetsname;
}
function  openDefaultHeroBox($uid,$cid,$srctype,$type){
	unlockUser($uid);
	if (!cityHasHeroPosition($uid,$cid)){
		throw new Exception($GLOBALS['recruitHero']['hotel_level_low']);
	}

	$hero = sql_fetch_one("select * from cfg_recruit_hero where id = $type");
	$level=$hero["level"];
	if (isset($hero["min_level"])){
		$min_level=$hero["min_level"];
		$max_level=$hero["max_level"];
		if 	($min_level>0 && $max_level>0){
			$level = mt_rand($min_level,$max_level);
		}
	}
	$attri1=mt_rand($hero["min_affairs_base"],$hero["max_affairs_base"]);
	$attri2=mt_rand($hero["min_bravery_base"],$hero["max_bravery_base"]);
	$attri3=mt_rand($hero["min_wisdom_base"],$hero["max_wisdom_base"]);
	$loyalty=mt_rand($hero["min_loyalty"],$hero["max_loyalty"]);
	$heroname = $hero["heroname"];
	$herotype = $hero["herotype"];
	$totalAttri=$attri1+$attri2+$attri3;
	$attriadd1=floor(($attri1/$totalAttri)*$level);
	$attriadd2=floor(($attri2/$totalAttri)*$level);
	$attriadd3=$level-$attriadd1-$attriadd2;
	//生成一个随机性别
	$sex = $hero["sex"];//10分之一的机率
	//男人有859个头像，女人有105个头像 
	$face = ($sex==0)?mt_rand(1,9):mt_rand(1001,1070);
	$hero_exp = sql_fetch_one_cell("select total_exp from cfg_hero_level where level='$level'");
	$loyalty=70;

	$sql = "insert into sys_city_hero (`uid`,`name`,`sex`,`face`,`cid`,`state`,`level`,`exp`,`affairs_base`,`bravery_base`,`wisdom_base`,`affairs_add`,`bravery_add`,`wisdom_add`,`loyalty`,`herotype`) values ('$uid','$heroname','$sex','$face','$cid','0','$level','$hero_exp','$attri1','$attri2','$attri3','$attriadd1','$attriadd2','$attriadd3','$loyalty','$herotype')";
	$forcemax=100+floor($level/5)+floor(($attri2+$attriadd2)/3);
	$energymax=100+floor($level/5)+floor(($attri3+$attriadd3)/3);

	//招人
	$hid = sql_insert($sql);
	sql_query("insert into mem_hero_blood (`hid`,`force`,`force_max`,`energy`,`energy_max`) values ('$hid',100,$forcemax,100,$energymax)");
	updateCityHeroChange($uid,$cid);

	//$msg = "恭喜你获得“".$heroname."”一名，请去招贤馆查看。";
	$msg = sprintf($GLOBALS['goods']['get_hero'],$heroname);
	if ($srctype==0){
		throw new Exception($msg);
	}
	return $msg;
}

function addArmorShelf($uid, $gid) {
	$count = sql_fetch_one_cell ( "select `armor_column` from sys_user where uid=$uid" );
	if ($count >= 500) {
		throw new Exception ( $GLOBALS ['goods'] ['armor_column_full'] );
	}
	$goods = sql_fetch_one ( "select * from sys_goods where uid=$uid and gid=$gid" );
	if (empty ( $goods ) || $goods ['count'] <= 0) {
		throw new Exception ( "not_enough_goods$gid" );
	}
	$add = 0;
	if ($gid == 145)
	$add = 5;
	if ($gid == 146)
	$add = 50;
	$target = $count;
	if ($count + $add > 500)
	$target = 500;
	else
	$target = $count + $add;
	//成就系统：：装备栏500格
	if($target==500){
		finishAchivement($uid,36);
	}
	sql_query ( "update sys_user set armor_column=$target where uid=$uid" );
	addGoods($uid, $gid, -1, 0);
	return $target;
	//return sprintf($GLOBALS['goods']['armor_column_add'], $target);
}

function showDlg($uid, $param) {
	$itemid = intval(array_shift ( $param ));
	$ret [] = sql_fetch_one ( "select c.* from cfg_shop c where c.gid ='$itemid' and c.pack=1 order by price desc limit 1" );
	return $ret;
}

function useShenMiChuanYinFu($uid) {
	if (! checkGoods ( $uid, 147 )) {
		throw new Exception ( $GLOBALS ['useShenMiChuanYinFu'] ['no_ShenMiChuanYinFu'] );
	}
	$questions = sql_fetch_rows ( "select * from sys_question q" );
	$qlength = count ( $questions );
	$randomNumber = rand ( 0, $qlength - 1 );
	reduceGoods ( $uid, 147, 1 );
	return $questions [$randomNumber];
}

function afterUseShenMiChuanYinFu($uid, $param) {
	$msg = array_shift ( $param );
	$msg = addslashes($msg);
	//	$user = sql_fetch_one("select * from sys_user where uid=$uid");
	//	$msg = $user['name'].$msg;
	sendSysInform(0,1,0,300,60,1,49151,$msg);
	//sql_query ( "insert into sys_inform (type,inuse,starttime,endtime,`interval`,scrollcount,color,msg) values (0,1,unix_timestamp(),unix_timestamp()+60,0,1,49151,'$msg')" );
	// 第四个参数 type = 9 表示通过答对神秘传音符提供的问题而得到的奖励
	addGoods ( $uid, 150, 1, 9 );
	$ret = array ();
	return $ret;
}

//使用道具讨逆圣旨进行不宣而战
function useTaoNiShengZhi($uid,$param)
{
	if (! checkGoods ( $uid, 157 )) {
		throw new Exception("not_enough_goods157#1");
	}

	$targetuid = intval(array_shift($param));
	$targetcid= intval(array_shift($param));
	if (sql_check("select * from mem_user_inwar where (uid='$uid' and targetuid='$targetuid') or (targetuid='$uid' and uid='$targetuid')"))
	{
		throw new Exception($GLOBALS['startWar']['war_is_declared']);
	}
	$user=sql_fetch_one("select name,state,lastcid from sys_user where uid='$uid'");
	$mystate = $user['state'];
	if ($mystate == 1) throw new Exception($GLOBALS['startWar']['new_protect']);
	$targetuser=sql_fetch_one("select name,state,lastcid,union_id from sys_user where uid='$targetuid'");
	$targetstate = $targetuser['state'];
	if ($targetstate == 1) throw new Exception($GLOBALS['startWar']['target_new_protect']);


	$user = sql_fetch_one("select name,prestige,lastcid from sys_user where uid='$uid'");
	$username=$user['name'];
	$myprestige=$user['prestige'];
	$targetcity = getCityNamePosition($targetcid);
	$targetuid = sql_fetch_one_cell("select uid from sys_city where cid='$targetcid'");
	$targetuser= sql_fetch_one("select name,prestige from sys_user where uid='$targetuid'");
	$targetusername=$targetuser['name'];
	$targetprestige=$targetuser['prestige'];
	$delay=3600*8;
	sql_query("insert into mem_user_trickwar (uid,targetuid,endtime) values ('$uid','$targetuid',unix_timestamp()+$delay) on duplicate key update endtime=unix_timestamp()+$delay");

	if(empty($targetprestige))
	{
		$prestige_reduce_rate=1;
	}
	else
	{
		$prestige_reduce_rate = $myprestige / $targetprestige;
	}
	if ($prestige_reduce_rate > 5) $prestige_reduce_rate = 5;
	if ($prestige_reduce_rate < 1) $prestige_reduce_rate = 1;
	$prestige_reduce = $myprestige * $prestige_reduce_rate * 0.01;
	//sql_query("update sys_user set warprestige=GREATEST(0,warprestige-$prestige_reduce) where uid='$uid'"); //改为不降低声望了
	//给自己的战报
	$now = sql_fetch_one_cell("select unix_timestamp()");
	$report = sprintf($GLOBALS['trickBuXuanErZhan']['succ_report'],$targetusername,MakeEndTime($now),MakeEndTime($now + $delay),$prestige_reduce);
	sendReport($uid,0,49,$user['lastcid'],$targetcid,$report);

	//给对方的战报 
	$caution = sprintf($GLOBALS['trickBuXuanErZhan']['succ_caution'],$username,MakeEndTime($now),MakeEndTime($now + $delay));
	sendReport($targetuid,0,49,$user['lastcid'],$targetcid,$caution);
	completeTaskGoalBySortandType($uid, 53, 9);

	reduceGoods ( $uid, 157, 1 );

	/*
	 * 平台接口
	 */
	if (defined("PASSTYPE")){
		try{
			require_once 'game/agents/AgentServiceFactory.php';
			AgentServiceFactory::getInstance($targetuid)->addStartWarEvent($username);
		}catch(Exception $e){
			try{
				file_put_contents("./agents/log/interface-error.log",date("Y-m-d H:i:s",time())." || ".$e->getMessage()."\n",FILE_APPEND);
			}catch(Exception $err){

			}
		}
	}

	$ret[]=  sprintf($GLOBALS['trickBuXuanErZhan']['succ'],$targetusername,MakeTimeLeft($delay));
	return $ret;
}


function giveMeHeroCard($uid, $mygid){
	/*
	$rate = mt_rand(1,1000);
	$cardType = 0;
	$part=1;
	if($rate>995){
		$cardType = 1;//紫卡
	}else if($rate>975){
		$cardType = 2;//红卡
	}else if($rate>=875){
		$cardType = 3;//黄卡
	}else{
		$cardType = 4;//蓝卡
	}
	$part = sql_fetch_one_cell("select value from mem_state where state=250");
	$hreo = sql_fetch_one("select hid,name from cfg_hero_card_schedule where part!=0 and part<=$part order by rand() limit 1");
	$hreoName = sql_fetch_one_cell("select `name` from cfg_npc_hero where npcid='$hreo[hid]'");

	$cardId = 200000+$cardType*10000+$hreo['hid'];
	if(!checkGoods($uid,$mygid))
	{
		throw new Exception($GLOBALS['useGoods']['no_this_good']);
	}else{
		reduceGoods($uid,$mygid,1);
	}	
	addGoods($uid,$cardId,1,6);
	*/
	//$ret=array();
	//$ret[]="恭喜您获得'$hreo[name]'".$GLOBALS['useGoods']['hero_card'][$cardType];
	$yysType = sql_fetch_one_cell("select value from mem_state where state=197");
	if($yysType!=60 && $yysType!=55555555){
		throw new Exception($GLOBALS['goods']['not_available']);
	}
	$re = useOneHeroCard($uid);
	if($re==0){
		throw new Exception($GLOBALS['useGoods']['no_this_good']);
	}
	$cardType=$re[0];
	$hid=$re[1];
	$hreoName = sql_fetch_one_cell("select `name` from cfg_npc_hero where npcid='$hid'");
	$msg = sprintf($GLOBALS['useGoods']['hero_card_1'] ,$hreoName,$GLOBALS['useGoods']['hero_card'][$cardType]);
	if($cardType==1){
		$name = sql_fetch_one_cell("select name from sys_user where uid=$uid");			
		$msg2 = sprintf($GLOBALS['useGoods']['hero_card_11'], $name);
		sql_query("insert into sys_inform (type,inuse,starttime,endtime,`interval`,scrollcount,color,msg) values (0,1,unix_timestamp(),unix_timestamp()+60,0,1,15627776,'$msg2')");
	}	
	return $msg;
}

function generateHero4Card($uid,$cid,$mygid){
	$yysType = sql_fetch_one_cell("select value from mem_state where state=197");
	if($yysType!=60 && $yysType!=55555555){
		throw new Exception($GLOBALS['goods']['not_available']);
	}
	if (!cityHasHeroPosition($uid,$cid))
		 throw new Exception($GLOBALS['recruitHero']['hotel_level_low']);
		 
	$heroCount4Card = sql_fetch_one_cell("select count(1) from sys_city_hero where uid=$uid and herotype>=21250 and herotype<=24250");
	if($heroCount4Card>=5){
		 throw new Exception($GLOBALS['useGoods']['hero_card_7']);
	}
	
	$npcid = $mygid%10000;
	$cardType = floor(($mygid-200000)/10000);
	$hero = sql_fetch_one("select * from cfg_npc_hero where npcid=$npcid limit 1");
	$heroCommand=sql_fetch_one_cell("select command_base from sys_city_hero where npcid=$npcid limit 1");
		if ($npcid <= 1032 and $npcid >= 1027) {
			$heroCommand = rand(80,100);
		} elseif ($npcid == 1033) {
			$heroCommand = 108;
		}

	$affairsRate = 0;
	$braveryRate = 0;
	$wisdomRate  = 0;
	$commandRate = 0;
	if($cardType==1){
		$affairsRate = mt_rand(90,95);
		$braveryRate = mt_rand(90,95);
		$wisdomRate  = mt_rand(90,95);
		$commandRate = 50;
	}else if($cardType==2){
		$affairsRate = mt_rand(80,85);
		$braveryRate = mt_rand(80,85);
		$wisdomRate  = mt_rand(80,85);
		$commandRate = 30;
	}else if($cardType==3){
		$affairsRate = mt_rand(70,75);
		$braveryRate = mt_rand(70,75);
		$wisdomRate  = mt_rand(70,75);
		$commandRate = 15;
	}else if($cardType==4){
		$affairsRate = mt_rand(60,65);
		$braveryRate = mt_rand(60,65);
		$wisdomRate  = mt_rand(60,65);
		$commandRate = 5;
	}
	$affairs = floor($hero['affairs_base']*($affairsRate/100));
	$bravery = floor($hero['bravery_base']*($braveryRate/100));
	$wisdom = floor($hero['wisdom_base']*($wisdomRate/100));
	$command = floor($heroCommand*($commandRate/100));
	$heroType = 20250+$cardType*1000;
	$heroName=$hero['name'];
	$sql = "insert into sys_city_hero (`uid`,`name`,`sex`,`face`,`cid`,`state`,`level`,`exp`,`affairs_base`,`bravery_base`,`wisdom_base`,`command_base`,`affairs_add`,`bravery_add`,`wisdom_add`,`loyalty`,`herotype`) values 
									  ('$uid','$heroName','$hero[sex]','$hero[face]','$cid','0','1','0','$affairs','$bravery','$wisdom','$command','0','0','0','100','$heroType')";
									  
	$forcemax=100+floor($bravery/3);
	$energymax=100+floor($wisdom /3);
	
	if(!checkGoods($uid,$mygid))
	{
		throw new Exception($GLOBALS['useGoods']['no_this_good']);
	}else{
		reduceGoods($uid,$mygid,1);
	}
	$hid = sql_insert($sql);
	sql_query("insert into mem_hero_blood (`hid`,`force`,`force_max`,`energy`,`energy_max`) values ('$hid',$forcemax,$forcemax,$energymax,$energymax)");
	regenerateHeroAttri($uid,$hid);
	updateCityHeroChange($uid,$cid);								  
	$noteType=mt_rand(1,3);
	return $hero['name'].':'.$GLOBALS['useGoods']['hero_card_4'][$noteType];
}



function mixCard($uid,$param){
	$yysType = sql_fetch_one_cell("select value from mem_state where state=197");
	if($yysType!=60 && $yysType!=55555555){
		throw new Exception($GLOBALS['goods']['not_available']);
	}
	$cardCount=count($param);
	if($cardCount<2){
		throw new Exception($GLOBALS['useGoods']['hero_card_5']);
	}
	$oriCardId=$param[0];
	$oriColor = floor(($oriCardId-200000)/10000);
	$heroId = floor($oriCardId%10000);
	if($oriColor==1){
		throw new Exception($GLOBALS['useGoods']['hero_card_6']);
	}
	
	$okRate=mt_rand(1,100);
	//$staticRate=mt_rand(1,100);
	
	$ret = array();
	for($i=1;$i<$cardCount;$i++){
		if(!checkGoods($uid,$param[$i]))
		{
			throw new Exception($GLOBALS['useGoods']['no_this_good']);
		}else{
			reduceGoods($uid,$param[$i],1,251);	
		}
	}
	if($okRate<(4*($cardCount-1))){	
		/*	20100714 取消指名率
		if($staticRate<(12*($cardCount-1))){		
						
		}else{
			$newColor = $oriColor-1;
			$part = sql_fetch_one_cell("select value from mem_state where state=250");
			$hreo = sql_fetch_one("select hid,name from cfg_hero_card_schedule where part!=0 and part<=$part order by rand() limit 1");	
			$heroId = 	$hreo['hid'];
			$newCardId = 200000+$newColor*10000+$hreo['hid'];
		}
		*/
		$newCardId=$oriCardId-10000;
		
		if(!checkGoods($uid,$oriCardId))
		{
			throw new Exception($GLOBALS['useGoods']['no_this_good']);
		}
		reduceGoods($uid,$oriCardId,1,251);
		
		addGoods($uid,$newCardId,1,250);
		$hreoName = sql_fetch_one_cell("select name from cfg_npc_hero where npcid=$heroId limit 1");
		$ret[]=$GLOBALS['useGoods']['hero_card_3'].$hreoName.$GLOBALS['useGoods']['hero_card'][$oriColor-1];
		
		if($oriColor==2){
			$name = sql_fetch_one_cell("select name from sys_user where uid=$uid");			
			$msg2 = sprintf($GLOBALS['useGoods']['hero_card_12'], $name,$hreoName.$GLOBALS['useGoods']['hero_card'][$oriColor-1]);
			sql_query("insert into sys_inform (type,inuse,starttime,endtime,`interval`,scrollcount,color,msg) values (0,1,unix_timestamp(),unix_timestamp()+60,0,1,15627776,'$msg2')");
		}	
		
	}else{
		$ret[]=$GLOBALS['useGoods']['hero_card_2'];
	}
	return $ret;
}


function useArmyOrder($uid, $cid,$mygid){
	$yysType = sql_fetch_one_cell("select value from mem_state where state=197");
	if($yysType!=60 && $yysType!=55555555){
		throw new Exception($GLOBALS['goods']['not_available']);
	}
	if(!checkGoods($uid,$mygid))
	{
		throw new Exception($GLOBALS['useGoods']['no_this_good']);
	}else{
		reduceGoods($uid,$mygid,1);
	}
	if($mygid==251){
		addCitySoldier($cid,8,500);
	}else if($mygid==252){
		addCitySoldier($cid,10,500);
	}else if($mygid==253){
		addCitySoldier($cid,12,500);
	}else if($mygid==254){
		addCitySoldier($cid,11,500);
	}
	updateUserPrestige($uid);
	return $GLOBALS['useGoods']['hero_card_8'];
}

function multiUseCard($uid,$param){
    //$gid = intval(array_shift($param));
	$yysType = sql_fetch_one_cell("select value from mem_state where state=197");
	if($yysType!=60 && $yysType!=55555555){
		throw new Exception($GLOBALS['goods']['not_available']);
	}
	$count=100;
	$currCount = sql_fetch_one_cell("select count from sys_goods where gid=250 and uid=$uid");
	if($currCount<$count){
		$count=$currCount;
	}
	$okCount=array();
	for($i=0;$i<$count;$i++){
		$re = useOneHeroCard($uid);
		if($re!=0){
			$okCount[$re[0]]++;
		}
	}
	$okSumCount = 0;
	$cardMsg='';
	$ziseKa=0;
	foreach ($okCount as $cardType=>$cardCount){
		$okSumCount+=$cardCount;
		$cardMsg.=$cardCount.$GLOBALS['useGoods']['hero_card_9'].$GLOBALS['useGoods']['hero_card'][$cardType];
		if($cardType==1){
			$ziseKa=1;
		}
	}
	
	if($ziseKa==1){
		$name = sql_fetch_one_cell("select name from sys_user where uid=$uid");			
		$msg2 = sprintf($GLOBALS['useGoods']['hero_card_11'], $name);
		sql_query("insert into sys_inform (type,inuse,starttime,endtime,`interval`,scrollcount,color,msg) values (0,1,unix_timestamp(),unix_timestamp()+60,0,1,15627776,'$msg2')");
	}
	
	
	$msg=sprintf($GLOBALS['useGoods']['hero_card_10'],$okSumCount,$cardMsg);
	$ret = array();
	$ret[]=$msg;
	return $ret;
}




function useOneHeroCard($uid){
	$rate = mt_rand(1,1005);
	$cardType = 0;
	$part=1;
	if($rate>999){
		$cardType = 1;//紫卡
	}else if($rate>989){
		$cardType = 2;//红卡
	}else if($rate>=889){
		$cardType = 3;//黄卡
	}else{
		$cardType = 4;//蓝卡
	}
	$part = sql_fetch_one_cell("select value from mem_state where state=250");
	$hreo = sql_fetch_one("select hid,name from cfg_hero_card_schedule where part!=0 and part<=$part order by rand() limit 1");
	//$hreoName = sql_fetch_one_cell("select `name` from cfg_npc_hero where npcid='$hreo[hid]'");

	$cardId = 200000+$cardType*10000+$hreo['hid'];
	if(!checkGoods($uid,250))
	{
		return 0;
	}else{
		reduceGoods($uid,250,1);
	}	
	addGoods($uid,$cardId,1,3);
	$ret = array();
	$ret[]=$cardType;
	$ret[]=$hreo['hid'];
	return $ret;
}
function openEggBox($uid,$gid){
	if (!checkGoods ( $uid, $gid )) {
		$name = sql_fetch_one_cell ( "select name from cfg_goods where gid='$gid'" );
		$msg = sprintf ("你没有%s,不能使用。", $name );
		throw new Exception ( $msg );
	}
	unlockUser($uid);
	if ($gid==10303){
		if(!checkGoods($uid,10307)) throw new Exception("你没有砸蛋锤，不能使用银蛋。");
	}else if($gid==10304){
		if (!checkGoods($uid,10307)) throw new Exception("你没有砸蛋锤，不能使用金蛋。");
	}
	$ret = array ();
	if($gid==10303){
		$rand = mt_rand (1, 100);
		$count=0;
		if ($rand <=60) {
			$count=1;
		}else if($rand<=90) {
			$count=2;
		}else{
			$count=3;
		}
		$ret[] = sql_fetch_one ( "select *,$count as count,value from cfg_goods where gid=10305" );
		$ret[] = sql_fetch_one ( "select *,2 as count,value from cfg_goods where gid=23" );
		addGoods($uid,10305,$count,6);
		addGoods($uid,23,2,6);
	}else if ($gid==10304){
		$rand = mt_rand (1, 100);
		$count=0;
		if ($rand <=50) {
			$count=1;
		}else if($rand<=95) {
			$count=2;
		}else{
			$count=3;
		}
		$ret[] = sql_fetch_one ( "select *,$count as count,value from cfg_goods where gid=10306" );
		$ret[] = sql_fetch_one ( "select *,2 as count,value from cfg_goods where gid=205" );
		$ret[] = sql_fetch_one ( "select *,1 as count,value from cfg_goods where gid=203" );
		$ret[] = sql_fetch_one ( "select *,1 as count,value from cfg_goods where gid=155" );
		$ret[] = sql_fetch_one ( "select *,1 as count,value from cfg_goods where gid=22" );
		addGoods($uid,10306,$count,6);
		addGoods($uid,205,2,6);
		addGoods($uid,203,1,6);
		addGoods($uid,155,1,6);
		addGoods($uid,22,1,6);
	}
	reduceGoods ( $uid, $gid, 1 );
	reduceGoods ( $uid, 10307, 1 );
	return $ret;
	

}
/**
 * 
 * 在使用道具把盟主位置转给其他的人时，必须把自己的献帝密诏道具，罚董勤王任务，献帝密诏buffer都转到新盟主的门下
 *
 * @param unknown_type $fromleader
 * @param unknown_type $toleader
 */
function exchangeUnionState($fromleader,$toleader){
	$gid=161501;
	$count=sql_fetch_one_cell("select count from sys_goods where uid=$fromleader and gid=$gid");
	if(!empty($count)){
		sql_query("insert into sys_goods(uid,gid,count)values($toleader,$gid,$count) on duplicate key update count=$count");
	}
	sql_query("delete from sys_goods where uid=$fromleader and gid=$gid");
	
	
	$nowtime=sql_fetch_one_cell("select unix_timestamp()");
	$bufs=sql_fetch_rows("select * from mem_user_buffer where uid='$fromleader' and endtime>'$nowtime' and buftype=$gid");
	if(!empty($bufs)){//看联盟是不是在使用buffer，使用中也不刷
		sql_query("delete from mem_user_buffer where uid=$toleader and buftype=$gid");
		sql_query("update mem_user_buffer set uid=$toleader where uid=$fromleader and buftype=$gid");
	}
	
	sql_query("delete from sys_user_task where uid=$toleader and tid=112005");
	sql_query("update sys_user_task set uid=$toleader where uid=$fromleader and tid=112005");
}
function useZhaoAnLing($uid,$gid,$useCount) {
	$goodCnt = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='$gid'");
	if(empty($goodCnt)||intval($goodCnt)<$useCount) throw new Exception($GLOBALS['duihuan']['not_enogh']);
	
	$addTime = 86400*$useCount;
	sql_query ( "insert into mem_user_buffer (uid,buftype,bufparam,endtime) values ('$uid','10333',0,unix_timestamp()+$addTime) on duplicate key update endtime=endtime + 86400" );
	reduceGoods ( $uid, 10333, $useCount );
	return sql_fetch_one_cell ( "select endtime from  mem_user_buffer where uid='$uid' and buftype=10333" );
}
function userWangZheZhiCheng($uid,$gid,$cid) {
	if ($gid != 10816) {
		throw new Exception($GLOBALS['good']['not_enough_goods83']);
	}
	if (!checkGoods($uid,$gid)) {
		throw new Exception($GLOBALS['good']['not_enough_goods83']);
	}

	$cityInfo = sql_fetch_one("select * from sys_city c,sys_building b where c.cid=b.cid and c.cid='$cid' and c.uid='$uid' and b.bid='6'");	
	doCheckCityAndType($cityInfo);
	
	sql_query("update sys_city set is_special=2 where cid='$cid'");
	reduceGoods ( $uid, $gid, 1 );
	
	//清理下当前城池原来的时效性皮肤buff
	$bufInfo = sql_fetch_one("select * from mem_city_buffer where cid='$cid' and bufparam='705' limit 1");
	if(!empty($bufInfo)){sql_query("delete from mem_city_buffer where cid='$cid' and bufparam='705' limit 1");}
		
	$isOwnWangZheDesig = sql_fetch_rows("select * from sys_user_designation where uid='$uid' and did='19'");
	if(empty($isOwnWangZheDesig))
	{
		sql_query("insert into sys_user_designation(`did`,`uid`,`ison`,`state`) values('19','$uid','0','1')");
	}
}

function goodsCompose($uid,$param)
{
	$gid = intval(array_shift($param));
	$cnt = 1;
	$composeGidArr = array(10830,10831,10832,10833,10834,10835,10836,10837,10838,10839,19081,19082);
	
	$isSucc=1;   //合成是否成功  0为不成功，1为成功
	
	if (!in_array($gid,$composeGidArr))
	{
		throw new Exception($GLOBALS['goods']['can_not_compose']);
	}
	if (intval($gid)==10839)
	{
		throw new Exception($GLOBALS['goods']['good_compose_maxLevel']);
	}
	
	$curCount = sql_fetch_one_cell("select count from sys_goods where uid=$uid and gid=$gid");
	$newGid = intval($gid)+1;
	
	if(intval($gid)==19081)    //马鬃
	{
		if(intval($curCount)<5)
		{
			throw new Exception($GLOBALS['goods']['good_not_enough_1']);
		}
		addGoods($uid,$gid,-5,917);
		addGoods($uid,$newGid,1,917);
	}else if (intval($gid)==19082)     //马鞭
	{
		if(intval($curCount)<2)
		{
			throw new Exception($GLOBALS['goods']['good_not_enough_2']);
		}
		$rate = mt_rand(1,100);
		if($rate<=25)
		{
			addGoods($uid,$gid,-2,917);
			addGoods($uid,$newGid,1,917);
		}else 
		{
			addGoods($uid,$gid,-2,917);
			$isSucc=0;
		}
	}else       //聚魂珠
	{
		if(intval($curCount)<2)
		{
			throw new Exception($GLOBALS['goods']['good_not_enough']);
		}
				
		addGoods($uid,$gid,-2,917);
		addGoods($uid,$newGid,1,917);
	}
	
	$oneGood = sql_fetch_rows("select *,$cnt as count from cfg_goods where gid='$newGid'" );
	
	$ret = array();
	$ret[] = $isSucc;
	$ret[] = $oneGood;
	return $ret;
}

function getOutTroops($uid,$cid,$gid) {
	$soldierType="";
	$outUid="";
	$msg="";
	$npcValue=0;
	$gids=array(19061,19062,19063);
	
	if (!checkGoods($uid,$gid)) {
		throw new Exception($GLOBALS['good']['not_enough_goods83']);
	}
	if (!in_array($gid,$gids) || !sql_check("select cid from sys_city where uid=$uid and cid=$cid")) {
		throw new Exception($GLOBALS['sendCommand']['command_exception']);	
	}
	switch ($gid) {
		case 19061: $province=17;$npcValue=rand(10000,50000); break;
		case 19062: $province=18;$npcValue=rand(10000,50000); break;
		case 19063: $province=14;$npcValue=rand(10000,50000); break;
		default: $province=0; break;
	}
	if ($gid == 19063) {
		$myProvince = sql_fetch_one_cell("select province from mem_world where ownercid=$cid and type=0");
		if ($myProvince<14) {
			throw new Exception($GLOBALS['out']['not_in_out']);
		}
	}
	switch($province) {
		case 14: $soldierType="87,88,89,90,91,92,93,94,95";$outUid="262";break;
		case 15: $soldierType="78,79,80,81,82,83,84,85,86";$outUid="832";break;
		case 16: $soldierType="51,52,53,54,55,56,57,58,59";$outUid="398";break;
		case 17: $soldierType="60,61,62,63,64,65,66,67,68";$outUid="261";break;
		case 18: $soldierType="69,70,71,72,73,74,75,76,77";$outUid="608";break;
		default: $soldierType="87,88,89,90,91,92,93,94,95";$outUid="262";break;
	}
	$soldier = getOutSoldiers($npcValue,$soldierType);
	$hid=sql_fetch_one_cell("select hid from sys_city_hero where cid=0 order by rand() limit 1");
	sql_query("insert into sys_troops(uid,cid,hid,targetcid,task,state,starttime,pathtime,endtime,soldiers,people,ulimit) values('$outUid','0','$hid','$cid','4','0',unix_timestamp(),unix_timestamp()+180,unix_timestamp()+180,'$soldier','500000','$gid')");
	sql_query("insert into sys_alarm (uid,enemy) values ('$uid',1) on duplicate key update enemy=1");
	
	$provinceName=sql_fetch_one_cell("select name from cfg_province where id=$province");
	$cityName=sql_fetch_one_cell("select name from sys_city where cid=$cid");
	$msg=sprintf($GLOBALS['out']['call_out_troops'],$provinceName,$cityName);
	sendReport($uid,"0",9,$cid,$cid,$msg);
	sql_query("insert into sys_alarm (uid,report) values ('$uid',1) on duplicate key update report=1");
	
	addGoods($uid,$gid,-1,1);
}
function getOutSoldiers($npcValue,$soldiers) {
	$soldierRate=array();
	$sum=1;
	$str="";
	
	$soldierTypes=explode(",",$soldiers);
	
	for ($i=0;$i<9;++$i) {
		$j=rand(0,20);
		$soldierRate[]=$j;
		$sum=$sum+$j;
	}
	
	for($k=0;$k<count($soldierTypes);++$k) {
		$num=(int)($npcValue*$soldierRate[$k]/$sum);
		$str .= ",".$soldierTypes[$k].",".$num;
	}
	return count($soldierTypes).$str;
}

//批量提升修为
function addUserLevelTimes($uid,$param) {
	
	$times = array_shift($param);
	$gid = 10961;   //孙子兵法
	if(!checkGoodsCount($uid,$gid,$times)|| empty($times)) 
	{
		throw new Exception($GLOBALS['good']['not_enough_goods83']);
	}
	$ret=array();
	$msg='';
	$curLevel = sql_fetch_one_cell("select level from sys_user_level where uid=$uid");
	if ($curLevel>=10) {
		throw new Exception($GLOBALS['user_hero']['user_level']);
	}
	
	for($i=0;$i<$times;++$i) {
		$ret=addUserLevel($uid);
		if (!empty($ret[1])) break;
	}
	$targetLevel = sql_fetch_one_cell("select level from sys_user_level where uid=$uid");
	
	//$count=sql_fetch_one_cell("select count from sys_goods where uid=$uid and gid='$gid'");
	$goodInfo =sql_fetch_one("select c.*,s.count from cfg_goods c left join sys_goods s on c.gid=s.gid and s.uid='$uid' where c.gid='$gid'");
	if (!empty($ret[1])) {//单次升级遇到了异常；
		if ($targetLevel>$curLevel) {
			$msg=$GLOBALS['user_hero']['upgrade_success'].$ret[0];
		} else {
			$msg=$ret[0];
		}
		unlockUser($uid);
		return array($goodInfo,$targetLevel,$msg);
	}
	if ($targetLevel>$curLevel) {
		$msg=$GLOBALS['user_hero']['upgrade_success'];
	} else {
		$msg=$GLOBALS['user_hero']['upgrade_fail'];
	}
	unlockUser($uid);
	return array($goodInfo,$targetLevel,$msg);
}

//升级修为
function addUserLevel($uid) {
	$levelRate = array(
		1=>5000,
		2=>1500,
		3=>500,
		4=>300,
		5=>100,
		6=>50,
		7=>25,
		8=>10,
		9=>5,
		10=>1,
	);
	$levelLimit = array(
		1=>1,
		2=>10,
		3=>20,
		4=>30,
		5=>40,
		6=>50,
		7=>60,
		8=>70,
		9=>80,
		10=>100,
	);
	$baodi = array(
		1=>1,
		2=>5,
		3=>20,
		4=>30,
		5=>50,
		6=>100,
		7=>300,
		8=>500,
		9=>1000,
		10=>3000,
	);
	//孙子兵法强化保底活动
//	$startTime=0;		//活动开始时间，unix_timestamp()
//	$endTime=0;			//活动结束时间，unix_timestamp()
//	$actFlag=false;		//是否在活动期间
//	$passFlag=false;	//是否达到保底次数
//	$serverTime = sql_fetch_one_cell("select unix_timestamp()"); 
//	if ($startTime<=$serverTime && $endTime>=$serverTime) {
//		$actFlag=true;
//	}
	$userLevel = sql_fetch_one("select level,count from sys_user_level where uid=$uid");
	$curLevel = intval($userLevel['level']);
	$useTimes = intval($userLevel['count']);
	if ($curLevel>=10) {
		return array($GLOBALS['user_hero']['user_level'],1);
	}
	$targetLevel=$curLevel+1;
	$success=0;
	if(($baodi[$targetLevel]-1) <= $useTimes){
		$passFlag=true;
	}
	$heroLevel = sql_fetch_one_cell("select level from sys_city_hero where uid='$uid' and herotype=1000");
	if ($heroLevel < $levelLimit[$targetLevel]) {
		return array($GLOBALS['user_hero']['limit_level'],1);
	}
	if (rand(1,10000) <= $levelRate[$targetLevel] || $passFlag) 
	{
		$success=1;
		sql_query("insert into sys_user_level(uid,level,time,count) values('$uid',$targetLevel,unix_timestamp(),0) on duplicate key update level=$targetLevel,time=unix_timestamp(),count=0");
		sql_query("insert into log_user_level_add values($uid,$curLevel,$targetLevel,unix_timestamp()) ");
		//增加奖励
		$command_base=0;
		$bravery_base=0;
		$affairs_base=0;
		$wisdom_base=0;
		if ($targetLevel==1) {
			$command_base=10;
		} elseif ($targetLevel==3) {
			$command_base=10;
			$bravery_base=10;
		} elseif ($targetLevel==5) {
			$command_base=10;
			$bravery_base=10;
			$wisdom_base=10;
		} elseif ($targetLevel==7) {
			$command_base=10;
			$bravery_base=10;
			$affairs_base=10;
			$wisdom_base=10;
		} elseif ($targetLevel==9) {
			sql_query("update sys_city_hero set speed_add_on=speed_add_on+10 where uid=$uid and herotype=1000 limit 1");
		}
		sql_query("update sys_city_hero set command_base=command_base+$command_base,bravery_base=bravery_base+$bravery_base,affairs_base=affairs_base+$affairs_base,wisdom_base=wisdom_base+$wisdom_base where uid=$uid and herotype=1000 limit 1");
	}
	else //失败了，保底计数
	{
		sql_query("insert into sys_user_level(uid,level,time,count) values('$uid',$curLevel,unix_timestamp(),1) on duplicate key update time=unix_timestamp(),count=count+1");
	}
	reduceGoods($uid,10961,1);
	
//	if ($actFlag) {
//		//活动开启，赠送物品
//		addGoods($uid,11148,1,1);
//	}
	//增加玩家的声望
	if ($success == 1) {
		$designation=0;
		switch ($targetLevel) {
			case 6: $designation=20;break;
			case 8: $designation=21;break;
			case 9: $designation=22;break;
			case 10: $designation=23;break;
			default: $designation=0;
		}
		if ($designation>0) {
			if (!sql_check("select 1 from sys_user_designation where uid=$uid and did=$designation"))
				sql_query("insert into sys_user_designation(did,uid,ison,state) values('$designation','$uid','0','1')");
		}
	}
	return array('',0);
}

function useWangZheBingFu($uid,$cid,$gid)
{
	$cityInfo = sql_fetch_one("select * from sys_city where cid='$cid' and uid='$uid'");
	if(empty($cityInfo))
	{
		throw new Exception($GLOBALS['useGoods']['city_not_exists']);
	}
	if(intval($cityInfo['is_special'])!=2)
	{
		throw new Exception($GLOBALS['useGoods']['city_not_wangzhe']);
	}
	
	$count = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='$gid'");
	if(empty($count)||intval($count)<1)
	{
		throw new Exception($GLOBALS['useGoods']['wangzhebingfu_not_enough']);
	}
	
	$sid = mt_rand(45,50);
	$soldierName = sql_fetch_one_cell("select name from cfg_soldier where sid='$sid'");
	
	$province = $cityInfo['province'];
	$provinceName = $cityInfo['name'];

	
	$type=0;
	$isOwnInfo = sql_fetch_one("select * from cfg_soldier_special_city where cid='$cid'");  
	if(empty($isOwnInfo))
	{
		sql_query("insert into cfg_soldier_special_city(`cid`,`sid`,`province`,`type`,`provincename`,`rsid`) values('$cid','$sid','$province','4','$provinceName','0')");
		$msg = $GLOBALS['useGoods']['add_specialSoldier_succ'];
		$type=703;
	}else 
	{
		sql_query("update cfg_soldier_special_city set sid='$sid' where cid='$cid' limit 1");
		$msg = sprintf($GLOBALS['useGoods']['convert_specialSoldier_succ'],$soldierName);
		$type=704;
	}
	addGoods($uid, $gid, -1, $type);
	return $msg;
}

function changeCityMap($uid,$cid,$gid)     //城池自己选皮肤
{
	$cityInfo = sql_fetch_one("select * from sys_city where cid='$cid' and uid='$uid'");
	doCheckCityAndType($cityInfo);
	$count = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='$gid'");
	if(empty($count)||intval($count)<1)
	{
		throw new Exception($GLOBALS['useGoods']['skinGood_not_enough']);
	}
	$goodInfo = sql_fetch_one("select * from cfg_goods where gid='$gid'");
	
	$isSpecial=0;
	switch(strval($gid))
	{
		case "10932":
		$isSpecial = 10;   //7天
		break;
		case "10933":
		$isSpecial = 11;   //7天
		break;
		case "10934":
		$isSpecial = 12;   //7天
		break;
		case "10935":
		$isSpecial = 13;   //7天
		break;
		case "10936":
		$isSpecial = 14;   //7天
		break;
		case "10937":
		$isSpecial = 15;    //7天
		break;
		case "10996":      //永久
		$isSpecial = 99;
		break;
		case "11021":     //7天
		$isSpecial = 16;
		break;
		case "11022":      //永久
		$isSpecial = 98;
		break;
		case "11078":      //永久
		$isSpecial = 97;
		break;
		default:break;			
	}
	
	//扣除道具
	addGoods($uid, $gid, -1, 0706);
	$bufInfo = sql_fetch_one("select * from mem_city_buffer where cid='$cid' and bufparam='705' limit 1");
	
	if(intval($gid)==10996 || intval($gid)==11022|| intval($gid)==11078)   //永久城池皮肤单独处理
	{
		//清理下当前城池原来的时效性皮肤buff
		if(!empty($bufInfo)){sql_query("delete from mem_city_buffer where cid='$cid' and bufparam='705' limit 1");}
		
		sql_query("update sys_city set is_special='$isSpecial' where cid='$cid'");
		$msg1 = $GLOBALS['useGoods']['change_city_map_succ'];
		return $msg1;
	}
	
	$now = sql_fetch_one_cell("select unix_timestamp()");
	$interval = 604800;    //7天效果
	$time = $now+$interval;   
	
	
	sql_query("update sys_city set is_special='$isSpecial' where cid='$cid'");
	
	if(empty($bufInfo))
	{
		sql_query("insert into mem_city_buffer(`cid`,`buftype`,`bufparam`,`endtime`) values('$cid','$gid','705','$time')");
	}else if(intval($bufInfo['buftype']) == intval($gid))
	{
		sql_query("update mem_city_buffer set endtime=endtime+$interval where cid='$cid' and buftype='$gid' and $bufInfo[bufparam]='705'");
	}else 
	{
		sql_query("update mem_city_buffer set endtime='$time',buftype='$gid' where cid='$cid' and buftype='$bufInfo[buftype]' and $bufInfo[bufparam]='705'");
	}	
	$endtime = sql_fetch_one_cell("select from_unixtime(endtime) from mem_city_buffer where cid='$cid' and buftype='$gid' and bufparam='705' limit 1");
	$msg = sprintf($GLOBALS['useGoods']['cityMap_valid_date'],$goodInfo['name'],$endtime);
	
	return $msg;
}
function doCheckCityAndType($cityInfo)
{
	if(empty($cityInfo))
	{
		throw new Exception($GLOBALS['useGoods']['city_not_exists']);
	}
	if(intval($cityInfo['type'])>0 || intval($cityInfo['is_special'])==1 || intval($cityInfo['is_special'])==2 ||intval($cityInfo['is_special'])==99||intval($cityInfo['is_special'])==98||intval($cityInfo['is_special'])==97)
	{
		throw new Exception($GLOBALS['useGoods']['city_type_error']);
	}
	$isBuLuoCity = sql_fetch_one("select * from cfg_soldier_special_city where cid='$cityInfo[cid]' and type<4 limit 1");
	if(!empty($isBuLuoCity))
	{
		throw new Exception($GLOBALS['goods']['not_special_city']);
	}
}
function openxiyujinhe($uid,$gid){
     $ret = array();
     $rand=mt_rand(1,214);
	 if($rand>166 && $rand<177){
	     $username=sql_fetch_one_cell("select name from sys_user where uid='$uid'");
	     $msg="恭喜玩家：".$username."打开西域锦盒获得了1张乾坤碎片!";
	     sendSysInform(0,1,0,600,50000,1,16247152,$msg);
	     $mygid=10414;
	    } else{
	     $mygid=$rand;
		 if($mygid>176 && $mygid<201){
		     $mygid=250+mt_rand(0,4);
		     $username=sql_fetch_one_cell("select name from sys_user where uid='$uid'");
		     $mygidname=sql_fetch_one_cell("select name from cfg_goods where gid='$mygid' limit 1");
	         $msg="恭喜玩家：".$username."打开西域锦盒获得了1张".$mygidname;
	         sendSysInform(0,1,0,600,50000,1,16247152,$msg);
		    }
	      $mygidname=sql_fetch_one_cell("select name from cfg_goods where gid='$mygid' limit 1");
		}
	 $msg="恭喜玩家：".$username."打开西域锦盒获得了".$mygidname;
	 sql_query("insert into sys_goods (uid,gid,count) values ('$uid','$mygid',1) on duplicate key update count=count+1");
     reduceGoods ( $uid, $gid, 1 );	
	 $ret[]=$msg;
     return $ret;	 
    }
function openCombineArmorBox($uid,$gid)
{
	if(!checkGoods($uid,$gid))throw new Exception($GLOBALS['useGoods']['no_this_good']);
	$boxInfo = sql_fetch_rows("select * from cfg_box_combine_details where gid='$gid'");
	if(empty($boxInfo))throw new Exception($GLOBALS['useGoods']['invalid_data']);
	
	$ret = array();
	$rate = mt_rand(1,100);
	$sum=0;
	foreach($boxInfo as $oneBoxInfo)
	{
		$boxRate = intval($oneBoxInfo['rate']);
		$sum +=$boxRate;
		if($sum>=$rate){
			$armorInfo = sql_fetch_one("select * from cfg_armor where id={$oneBoxInfo['armorid']}");
			if(empty($armorInfo))throw new Exception($GLOBALS['equipment']['no_such_armor']);
			$count = intval($oneBoxInfo['count']);
			addArmor($uid, $armorInfo, $count, 815,0,$oneBoxInfo['combine_level']);
			$armorInfo['count'] = $count;
			$armorInfo['gtype'] = 1;
			$armorInfo['hp'] = $armorInfo ['ori_hp_max'];
			$armorInfo['hp_max'] = $armorInfo ['ori_hp_max'];
			$ret[] = $armorInfo;
			break;
		}
	}
	addGoods($uid, $gid, -1, 815);
	
	return $ret;
}
?>