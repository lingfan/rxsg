<?php
//战场系统
$array ['BattleType'] = array ('WarBook' => '请战书', 'Militaryorder' => '军令状', 'PardonBook' => '赦免文书', 'HelpOrder' => '援军令', 'QuickWalkOrder' => '急行军令', 'HitOrder' => '讨伐令', 'Pigeon' => '信鸽' );
//历练系统
$array ['GeneralType'] = array ('Clearance' => '通关文书', 'QuickCallorder' => '急召令','InspectionOrder'=>'巡查令' );//'GenExper'=>'武将历练'
//交易运输
$array ['TradingType'] = array ('CaravanContract' => '商队契约', 'Trojan' => '木牛流马');// ,'CtoP'=>'市场向商人买','PtoC'=>'市场卖给商人'
//建筑建造
$array ['BuildingType'] = array('CorveeOrder'=>'徭役令','BuildingBook'=>'建筑图纸','LubanPage'=>'鲁班残页','LubanEasy'=>'鲁班便笺','LubanFigure'=>'鲁班草图',
'LubanBook'=>'鲁班书册','LubanSecurt'=>'鲁班秘录','LubanAll'=>'鲁班全集','CityDoor'=>'备城门','Tinderbox'=>'火药筒');//,'QuickEnd'=>'鲁班立即完成'
//内政发展
$array ['InteriorType'] = array('PushEnOrder'=>'推恩令','APushEnOrder'=>'高级推恩令','NoritamiOrder'=>'典民令','AdvanceNtice'=>'安民告示','WenQuxin'=>'文曲星符',
'WuQuxing'=>'武曲星符','MastermindFu'=>'智多星符');
//战斗系统
$array ['WarInfoType'] = array('NoWar'=>'免战牌','CityOrder'=>'迁城令','ACityOrder'=>'高级迁城令','Capsule'=>'青囊书','HuFu'=>'虎符','Tips'=>'锦囊','Flag'=>'军旗',
'Drummer'=>'陷阵战鼓','Map'=>'八卦阵图','ADrummer'=>'高级陷阵战鼓','AMapc'=>'高级八卦阵图','HanXin'=>'韩信三篇','FireHit'=>'猛火油罐'); //,'SmallTrip'=>'小包锦囊'
//资源生产
$array ['SourceInfoType'] = array('Shennonghoe'=>'神农锄','Lubanax'=>'鲁班斧','MountainHammer'=>'开山锤','Hyuntielu'=>'玄铁炉','AdShenNonghoe'=>'高级神农锄',
'AMountainHammer'=>'高级开山锤','AHyuntielu'=>'高级玄铁炉','Publicanswhip'=>'税吏鞭','APublicanswhip'=>'高级税吏鞭','KGJpamphlets'=>'考工记简章','KGJcompiled'=>'考工记精编',
'KGJBalam'=>'考工记秘录');
//武将属性
$array ['GeneralnfoType'] = array('washingDan'=>'洗髓丹','Hemostatic'=>'止血散','Ajuga'=>'金疮药','BigDan'=>'大还丹','resurrection'=>'九转还魂丹',
'qingxin'=>'清心丸','xingshen'=>'醒神丹','tianyuan'=>'天元丹','yuluwan'=>'九花玉露丸','Armexper'=>'练兵经验','Warexper'=>'兵法心得','ArmyRoad'=>'治军之道');//,'swashingDan'=>'小包洗髓丹'
//其他
$array ['OtherlnfoType'] = array('CangBao'=>'藏宝图','ProxyBook'=>'委托文书','Chuannotes'=>'传音符','MengOrder'=>'盟主令','JFlag'=>'旌旗','FarmousArt'=>'名帖','Weapons'=>'武器架','AWeapons'=>'高级武器架','GetGood'=>'招贤榜','LiveDan'=>'护命金丹','TaoniShengzhi'=>'讨逆圣旨');
//,'HotBag'=>'打包','Proxytask'=>'委托任务抽税','YuanbaoCons'=>'休假消耗元宝','Treasure'=>'鉴定宝藏','ShijinNews'=>'市井传闻','SChuannotes'=>'小包传音符','SAChuannote'=>'中包传音符'
//科技发展
$array ['TechnologyType'] = array('MoDisabledPaper'=>'墨家残卷','MoScatterPage'=>'墨家散页','MoClassical'=>'墨家图纸','MoGoji'=>'墨家古籍','MoSercrit'=>'墨家秘笈',
'MoBao'=>'墨家宝典');//,'MoSoonFinish'=>'墨家立即完成'
//宝箱类
$array ['ChestType'] = array(
'JewelryBox'=>'珠宝盒',
'DelicacyJewelryBox'=>'精致珠宝盒',
'RarityJewelryBox'=>'稀世珠宝盒',
'BronzeKey'=>'青铜钥匙',
'SilverKey'=>'白银钥匙',
'GoldKey'=>'黄金钥匙',
'FingerEquipmentBox'=>'手指装备盒',
'AccessoriesEquipmentBox'=>'饰品装备盒',
'WeaponEquipmentBox'=>'武器装备盒',
'MountEquipment'=>'坐骑缰绳',
'BoleBox'=>'伯乐包',
'ShoulderEquipmentBox'=>'肩部装备盒',
'ChestEquipmentBox'=>'胸部装备盒',
'BackEquipmentBox'=>'背部装备盒',
'WaistEquipmentBox'=>'腰部装备盒',
'ArmEquipmentBox'=>'手臂装备盒',
'FootEquipmentBox'=>'脚部装备盒',
'BronzeJewelryBox'=>'青铜礼盒',
'SilverJewelryBox'=>'白银礼盒',
'GoldJewelryBox'=>'黄金礼盒',
'GrayEquipmentBox'=>'灰色装备盒',
'WhitBox'=>'白色装备盒',
'GreenBox'=>'绿色装备盒',
'HeaderBox'=>'头部装备盒',
'NeckBox'=>'颈部装备盒',
'HuangjinBox'=>'黄巾礼盒',
'GuanDuBox'=>'官渡礼盒',
'ShichangBox'=>'十常侍礼盒',
'DongZhuoBox'=>'讨伐董卓礼盒');
//装备强化
$array ['EquipStrongType'] = array('StrongJewelry'=>'强化宝珠','ImmortalWorkSymbol'=>'天工符','SmartSymbol'=>'巧手符','HeadHunterSymbol'=>'伯乐符','TeacherEmperrorNeedle'=>'师皇针',
'AllJewelry'=>'乾坤宝珠','KingkongDril'=>'金刚钻','AKingkongDril'=>'高级金刚钻','Stone'=>'化石粉','FossilsPowder'=>'炼化鼎');
//武魂类
$array ['WuHunType'] = array('SeduceSoul'=>'引魂香','CollectSoulFabric'=>'聚魂幡');
?>