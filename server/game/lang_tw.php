<?php


$GLOBALS['sys']['not_enough_money'] = "你沒有足夠的元寶，請充值后再操作。";
$GLOBALS['sys']['YuanBao'] = "元寶";
$GLOBALS['sys']['description_of_YuanBao'] = "元寶，用于購買商場道具，可通過充值獲得。";
$GLOBALS['sys']['LiJin'] = "禮金";
$GLOBALS['sys']['description_of_LiJin'] = "禮金，用于購買商場道具，可通過完成任務或打開寶盒獲得。";
$GLOBALS['sys']['restart_mail_title']="東山再起";

$GLOBALS['activity']['wisdom_hero']="謀士";
$GLOBALS['activity']['affairs_hero']="政客";
$GLOBALS['activity']['bravery_hero']="副將";
$GLOBALS['activity']['hero_type_name1']="無名";
$GLOBALS['activity']['hero_type_name2']="清閑";
$GLOBALS['activity']['hero_type_name3']="安逸";
$GLOBALS['activity']['hero_type_name4']="得志";
$GLOBALS['activity']['hero_type_name5']="潛隱";
$GLOBALS['activity']['hero_count_limit']="你擁有的活動將領已經達到10名，需要解雇一名通過聚賢包獲得的活動將領，才能繼續打開聚賢包。";
$GLOBALS['activity']['get_hero_tip']="恭喜你獲得“%s”一名，請去招賢館查看。";

//building
$GLOBALS['doGetSimpleBuildingInfo']['noresource']        =    "沒有相應的資源數據";
$GLOBALS['doGetSimpleBuildingInfo']['pre_building']    =    "前提建筑";
$GLOBALS['doGetSimpleBuildingInfo']['pre_technic']        =    "前提科技";
$GLOBALS['doGetSimpleBuildingInfo']['pre_thing']        =    "需要物品";
$GLOBALS['doGetSimpleBuildingInfo']['level']            =    "等級";
$GLOBALS['doGetSimpleBuildingInfo']['count']            =    "數量";
$GLOBALS['getBuildingInfo']['nobuilding']                =    "不存在該建筑";
$GLOBALS['getBuildingInfo']['upgrading']                =    "建筑正在升級中";
$GLOBALS['getBuildingInfo']['destroying']                =    "建筑正在拆除中";
$GLOBALS['getBuildingInfo']['building_error']            =    "建造建筑錯誤";
$GLOBALS['getBuildingInfo']['same_building_has_build']            =    "相同的建筑已經建造。";
$GLOBALS['getBuildingInfo']['resource_not_enough']        =    "資源不足";
$GLOBALS['getBuildingInfo']['people_not_enough']        =    "人口不足，不能建造此建筑。";
$GLOBALS['getBuildingInfo']['no_pre_building']            =    "前提建筑沒有建好。";
$GLOBALS['getBuildingInfo']['no_pre_technic']            =    "前提科技沒有研究好。";
$GLOBALS['getBuildingInfo']['no_pre_thing']            =    "你沒有相應的任務物品。";
$GLOBALS['getBuildingInfo']['government_not_enough']    =    "你的官府等級不足，不能在這塊空地上建造。";
//$GLOBALS['getBuildingInfo']['upgrading_queue_full']    =    "等級%d的官府可以同時建造或拆除%d個建筑，現在建造列表已滿，不能再建造新的建筑了。";
$GLOBALS['getBuildingInfo']['destroying_queue_full']    =    "等級%d的官府可以同時建造或拆除%d個建筑，現在建造列表已滿，不能再拆除新的建筑了。";
$GLOBALS['getBuildingInfo']['govenment_1_destroy']    =    "1級官府不能拆除。";
$GLOBALS['getBuildingInfo']['govenment_all_destroy']    =    "官府不能徹底拆除。";

$GLOBALS['getBuildingInfo']['upgrading_queue_full']    =    "現在建造列表已滿，不能再建造新的建筑了。";
$GLOBALS['getBuildingInfo']['upgrading_queue_full2']    =    "現在建造列表已滿，不能再建造新的建筑了。使用“徭役令”可以增加建造隊列。";


//city
$GLOBALS['getCityInfo']['not_your_city']    =    "當前城池已經被占領，不能進入，請重新登錄一下。";
$GLOBALS['getCityInfo']['city_be_invaded'] = "當前城池已經被占領，不能進入。自動切換到其他城池。";
$GLOBALS['changeCityName']['name_too_long']    =    "城池名不能超過8個字符。";
$GLOBALS['changeCityName']['name_illegal']    =    "城池名字含有不被允許的字符。";
$GLOBALS['changeCityName']['today_changed']    =    "每天只能修改一次城池名。";
$GLOBALS['changeCityName']['change_name_to']   =    "成功修改城池名為“%s”。";
$GLOBALS['levyResource']['time_limit']   =    "%s后才能征收物資。";
$GLOBALS['changeCityName']['bigcity_norename'] = "歷史名城不能改名。";    

$GLOBALS['treasureResult']['inform'] = "【%s】在活動期間采集寶藏，除了收獲藏寶盒，還幸運的%s";
$GLOBALS['goodsopen']['inform'] = "恭喜【%s】打開%s！";

$GLOBALS['levyResource']['gold'] = "黃金" ;
$GLOBALS['levyResource']['food'] = "糧食";
$GLOBALS['levyResource']['wood'] = "木材";
$GLOBALS['levyResource']['rock'] = "石料";
$GLOBALS['levyResource']['iron'] = "鐵錠";
$GLOBALS['levyResource']['succ_levy'] = "成功征收%s,民心降20。";
$GLOBALS['levyResource']['not_enough_morale'] = "民心低于20，不能征收物資。";

$GLOBALS['pacifyPeople']['server_busy'] = "服務器忙，請稍后再進行操作。";
$GLOBALS['pacifyPeople']['wait_more_secs'] = "%s后才能安撫百姓。";
$GLOBALS['pacifyPeople']['no_enough_food'] = "本城的糧食不夠。";
$GLOBALS['pacifyPeople']['no_enough_gold'] = "本城的黃金不夠。";
$GLOBALS['pacifyPeople']['succ_pacify'] = "成功安撫百姓";

$GLOBALS['addPeople']['not_your_city'] = "城池不屬于你";
$GLOBALS['addPeople']['succ'] = "成功招徠百姓%d人";
$GLOBALS['addPeople']['city_full'] = "當前人口已經超過城池人口上限，不能招徠更多流民了。";
$GLOBALS['addPeople']['no_goods'] = "你沒有道具“典民令”，不能招徠流民，請先去商城購買。";

$GLOBALS['addPeople']['no_taiping'] = "你沒有道具“太平要術”，不能招徠流民。";

$GLOBALS['gatherFieldStart']['field_is_pingdi'] = "該地為平地，不能采集。";
$GLOBALS['gatherFieldStart']['field_is_city'] = "該地為城池，不能采集。";
$GLOBALS['gatherFieldStart']['field_in_battle'] = "此野地正在戰亂中，不能進行采集。";
$GLOBALS['gatherFieldStart']['field_level_0'] = "0級野地不能采集。";
$GLOBALS['gatherFieldStart']['not_your_field'] = "該野地已經不屬于你，不能進行采集。";
$GLOBALS['gatherFieldStart']['you_are_gathering'] = "你已經在采集中。";
$GLOBALS['gatherFieldStart']['no_army'] = "你沒有在此野地的駐軍，不能進行采集。";
$GLOBALS['gatherFieldStart']['no_hero'] = "在此野地的駐軍沒有將領，不能進行采集。";
///////////Test////////
//'珍珠','珊瑚','琉璃','琥珀','瑪瑙','水晶','翡翠','玉石','夜明珠'
$GLOBALS['gatherFieldResult']['ZhenZhu'] = "珍珠";
$GLOBALS['gatherFieldResult']['ShanHu'] = "珊瑚";
$GLOBALS['gatherFieldResult']['LiuLi'] = "琉璃";
$GLOBALS['gatherFieldResult']['HuPo'] = "琥珀";
$GLOBALS['gatherFieldResult']['MaNao'] = "瑪瑙";
$GLOBALS['gatherFieldResult']['ShuiJing'] = "水晶";
$GLOBALS['gatherFieldResult']['FeiCui'] = "翡翠";
$GLOBALS['gatherFieldResult']['YuShi'] = "玉石";
$GLOBALS['gatherFieldResult']['YeMingZhu'] = "夜明珠";
$GLOBALS['gatherFieldResult']['GuPuMuHe'] = "古樸木盒";
$GLOBALS['gatherFieldResult']['CangBaoHe'] = "藏寶盒";
$GLOBALS['gatherFieldResult']['XiangSiDou'] = "相思豆";
$GLOBALS['gatherFieldResult']['XiangSiYuDi'] = "相思雨滴";

$GLOBALS['gatherFieldResult']['food'] = "糧食";
$GLOBALS['gatherFieldResult']['wood'] = "木材";
$GLOBALS['gatherFieldResult']['rock'] = "石料";
$GLOBALS['gatherFieldResult']['iron'] = "鐵錠";

$GLOBALS['gatherFieldEnd']['field_in_battle'] = "該野地正在戰亂中，不能進行收獲。";
$GLOBALS['gatherFieldEnd']['not_your_field'] = "該野地不屬于你，不能進行收獲。";
$GLOBALS['gatherFieldEnd']['no_people_gather'] = "無人采集，沒有獲得任何東西。";
$GLOBALS['gatherFieldEnd']['field_level_0'] = "0級野地過于貧瘠，無法進行采集。";
$GLOBALS['gatherFieldEnd']['gather_time_lessThen_1'] = "采集時間小于1小時，沒有任何收獲。";
$GLOBALS['gatherFieldEnd']['through_gathering'] = "經過%s的采集，共收獲";
$GLOBALS['gatherFieldEnd']['already_got'] = "你已經收獲過了。";
$GLOBALS['gather']['end_all'] = "全部收獲，請到公文戰報里查看收獲結果。";
$GLOBALS['discardField']['iron'] = "鐵錠";
$GLOBALS['discardField']['food'] = "糧食";

$GLOBALS['gatherFieldResult']['not_your_field'] = "該野地已經不屬于你。";
$GLOBALS['gatherFieldResult']['cant_dismiss_with_army'] = "該地有軍隊駐扎，不能放棄，請召回所有軍隊。";

$GLOBALS['callBackFieldTroop']['invalid_army'] = "無效的軍隊";

$GLOBALS['kickBackFieldTroop']['army_not_exist'] = "該駐軍已經不存在。";

$GLOBALS['discardCity']['invalid_pwd'] = "密碼錯誤，不能廢棄城池。";
$GLOBALS['discardCity']['not_your_city'] = "該城池已經不屬于你，不能進行操作！";
$GLOBALS['discardCity']['city_in_battle'] = "該城池正在戰亂中，不能廢棄城池。";
$GLOBALS['discardCity']['has_army_outside'] = "該城池有軍隊在外，不能廢棄城池。";
$GLOBALS['discardCity']['has_union_army'] = "該城（或其附屬野地）有其它盟友駐軍，不能棄城。";
$GLOBALS['discardCity']['giveup_city'] = "你放棄了%s，該城已經不再屬于你了。";


//command
$GLOBALS['sendCommand']['command_not_found'] = "不存在的請求";


//trick
$GLOBALS['useTrick']['caution_title'] = "計謀警報";
$GLOBALS['useTrick']['trick_not_exist'] = "該計謀不存在。";
$GLOBALS['useTrick']['no_enough_bag'] = "你的錦囊不足，不能使用該計謀。";
$GLOBALS['useTrick']['hero_not_exist'] = "該將領不存在。";
$GLOBALS['useTrick']['hero_not_incity'] = "不在城池內的將領不能使用計謀。";
$GLOBALS['useTrick']['hero_no_energy'] = "將領沒有足夠的精力來使用計謀了。";
$GLOBALS['useTrick']['target_is_mine'] = "不能對自己的城池使用該計謀。";
$GLOBALS['useTrick']['target_is_union'] = "不能對盟友的城池使用該計謀。";
$GLOBALS['useTrick']['target_is_not_my_troop'] = "不能對不是自己的軍隊使用該計謀。";
$GLOBALS['useTrick']['target_is_not_on_way'] = "軍隊不在行軍途中，不能使用該計謀。";
$GLOBALS['useTrick']['target_has_no_hero'] = "該軍隊沒有將領，不能使用“金蟬脫殼”";
$GLOBALS['useTrick']['target_is_just_run'] = "你剛剛對該軍隊使用過“金蟬脫殼”，%s后才能再次對該軍隊使用。";
$GLOBALS['useTrick']['target_not_coming_1'] = "該軍隊不是行軍狀態，不能使用“八門金鎖”。";
$GLOBALS['useTrick']['target_not_coming_2'] = "該軍隊不是行軍狀態，不能使用“關門打狗”。";
$GLOBALS['useTrick']['fail_no_wisdom'] = "用計失敗。一山還有一山高，對方智高一籌，輕易識破了我方的計謀。";
$GLOBALS['useTrick']['target_in_vacation'] = "對方處在休假狀態，不能對其城池使用計謀。";
$GLOBALS['useTrick']['target_be_locked'] = "對方已經被鎖定，不能對其城池使用計謀。";



$GLOBALS['trickCaoMuJieBin']['succ'] = "“草木皆兵”用計成功。該城池被偵察時將顯示軍隊人數為真實的%d倍，效果將持續到%s。";
$GLOBALS['trickKongCheng']['succ'] = "“空城計”用計成功。該城池被偵察時將顯示軍隊人數為真實的%d%%，效果將持續到%s。";
$GLOBALS['trickPaoZhuangYingYu']['succ'] = "“拋磚引玉”用計成功。該城池被偵察時將顯示資源數為真實的%d倍，效果將持續到%s。";
$GLOBALS['trickJinBiQingYe']['succ'] = "“堅壁清野”用計成功。該城池被偵察時將顯示資源數為真實的%d%%，效果將持續到%s。";

$GLOBALS['trickAnDuChenChang']['succ'] = "“暗渡陳倉”用計成功。你現在可以打破敵人封鎖，從被敵人圍困的城池內調動軍隊出城，效果將持續到%s。";

$GLOBALS['trickYaoYinHuoZhong']['succ'] = "“妖言惑眾”用計成功。\n%s民心-%d。";
$GLOBALS['trickYaoYinHuoZhong']['succ_caution'] = "%s對%s使用“妖言惑眾”！<br/>智者千慮必有一失，我方中計了！<br/>%s民心-%d。<br/>賑災、祈福可以恢復民心。";

$GLOBALS['trickYaoYinHuoZhong']['fail'] = "“妖言惑眾”用計失敗。\n吃一塹長一智，對方剛剛中過同樣的計策，不會重復上當了。";
$GLOBALS['trickYaoYinHuoZhong']['fail_caution'] = "%s對%s使用“妖言惑眾”！<br/>雕蟲小技也敢獻丑，我方識破了計謀。";


$GLOBALS['trickChenHuoDaJie']['succ'] = "“趁火打劫”用計成功。\n %s 倉庫保護能力降低%s,持續時間%s分鐘";
$GLOBALS['trickChenHuoDaJie']['succ_caution'] = "%s對%s使用“趁火打劫”！<br/>智者千慮必有一失，我方中計了！<br/>倉庫保護能力降低%s,持續時間%s分鐘";
$GLOBALS['trickChenHuoDaJie']['fail'] = "“趁火打劫”用計失敗。\n吃一塹長一智，對方剛剛中過同樣的計策，不會重復上當了。";
$GLOBALS['trickChenHuoDaJie']['fail_caution'] = "%s對%s使用“趁火打劫”！<br/>雕蟲小技也敢獻丑，我方識破了計謀。";

$GLOBALS['trickShunTengMoGua']['succ'] = "“順藤摸瓜”用計成功”！\n請到公文戰報里查看對方的城池和位置。";
$GLOBALS['trickShunTengMoGua']['alarm'] = "%s對我方使用“順藤摸瓜”！<br/>智者千慮必有一失，我方中計了！<br/>我方所有城池和位置被對手獲得。";
$GLOBALS['trickShunTengMoGua']['report_first_line'] = "“順藤摸瓜”用計成功,對方擁有城池 ：<br/>";
$GLOBALS['trickShunTengMoGua']['report_city'] ="城池名 ： %s, 坐標 : [%s,%s] <br/>";

$GLOBALS['trickWeiWeiJiuZhao']['succ'] = "“圍魏救趙”用計成功”！\n對方軍隊正返回城池。";
$GLOBALS['trickWeiWeiJiuZhao']['succ_caution'] = "%s對我方使用“圍魏救趙”！<br/>智者千慮必有一失，我方中計了！<br/>所有軍隊正在返回。";
$GLOBALS['trickWeiWeiJiuZhao']['fail'] = "“圍魏救趙”用計失敗。\n吃一塹長一智，對方剛剛中過同樣的計策，不會重復上當了。";
$GLOBALS['trickWeiWeiJiuZhao']['fail_caution'] ="%s對%s使用“圍魏救趙”！<br/>雕蟲小技也敢獻丑，我方識破了計謀。";

$GLOBALS['trickFenShaoLiangCao']['succ'] = "“焚燒糧草”用計成功”！\n燒毀敵人糧草%s。";
$GLOBALS['trickFenShaoLiangCao']['succ_caution'] = "%s對%s使用“焚燒糧草”！<br/>智者千慮必有一失，我方中計了！<br/>糧草被焚燒%s。";
$GLOBALS['trickFenShaoLiangCao']['fail'] = "“焚燒糧草”用計失敗。\n吃一塹長一智，對方剛剛中過同樣的計策，不會重復上當了。";
$GLOBALS['trickFenShaoLiangCao']['fail_caution'] ="%s對%s使用“焚燒糧草”！<br/>雕蟲小技也敢獻丑，我方識破了計謀。";

$GLOBALS['trickXuZhangShengShi']['succ'] = "“虛張聲勢”用計成功”！軍隊顯示人數%s倍,持續時間至%s。";
$GLOBALS['trickYanQiXiGu']['succ']= "“偃旗息鼓”用計成功”！軍隊顯示人數%s,持續時間至%s。";

$GLOBALS['trickZhuSiMaJi']['table_start'] ="<table border=0 cellspacing=1 cellpadding=1 bgcolor='#FFFFFF'><tr><td bgcolor='#17292B'><strong>任務</strong></td><td bgcolor='#17292B'><strong>出發地</strong></td><td bgcolor='#17292B'><strong>坐標</strong></td><td bgcolor='#17292B'><strong>目的地</strong></td><td bgcolor='#17292B'><strong>坐標</strong></td><td bgcolor='#17292B'><strong>到達時間</strong></td></tr>";
$GLOBALS['trickZhuSiMaJi']['tr_start']	  ="<tr>";
$GLOBALS['trickZhuSiMaJi']['td']          ="<td bgcolor='#17292B'>%s</td>";
$GLOBALS['trickZhuSiMaJi']['tr_end']	  ="</tr>";
$GLOBALS['trickZhuSiMaJi']['table_end']   ="</table>";
$GLOBALS['trickZhuSiMaJi']['succ']= "“蛛絲馬跡”用計成功”！\n請到公文戰報里查看對方的軍隊的情報。";
$GLOBALS['trickZhuSiMaJi']['alarm']= "%s對我方使用“蛛絲馬跡”！<br/>智者千慮必有一失，我方中計了！<br/>我方所有軍隊動向被對手獲得。";

$GLOBALS['trickTiaoBoLiJian']['fail'] = "“挑撥離間”用計失敗。\n吃一塹長一智，對方將領剛剛中過同樣的計策，都不會重復上當了。";
$GLOBALS['trickTiaoBoLiJian']['fail_caution'] = "%s對%s使用“挑撥離間”！<br/>雕蟲小技也敢獻丑，我方識破了計謀。<br/>";
$GLOBALS['trickTiaoBoLiJian']['fail_nohero'] = "對方城中沒有將領，白費功夫。";
$GLOBALS['trickTiaoBoLiJian']['fail_caution_nohero'] = "城中沒有將領，對方“挑撥離間”失敗，白費功夫。";

$GLOBALS['trickTiaoBoLiJian']['succ'] = "“挑撥離間”用計成功！\n";           
$GLOBALS['trickTiaoBoLiJian']['succ_reduceloyalty'] = "“%s”忠誠-%d。\n";
$GLOBALS['trickTiaoBoLiJian']['succ_surrender'] = "“%s”忠誠盡失，被勸降了。\n";
$GLOBALS['trickTiaoBoLiJian']['succ_nooffice'] = "“%s”忠誠盡失，可惜我方招賢館位置不足，無法接納。";

$GLOBALS['trickTiaoBoLiJian']['succ_caution'] = "%s對%s使用“挑撥離間”！<br/>智者千慮必有一失，我方中計了！<br/>";

$GLOBALS['trickTiaoBoLiJian']['succ_caution_reduceloyalty'] = "“%s”忠誠-%d。<br/>";
$GLOBALS['trickTiaoBoLiJian']['succ_caution_surrender'] = "“%s”忠誠盡失，被勸降了。<br/>";
$GLOBALS['trickTiaoBoLiJian']['succ_caution_nooffice'] = "“%s”忠誠盡失。<br/>";
$GLOBALS['trickTiaoBoLiJian']['succ_caution_tail'] = "賞賜將領可以提升將領忠誠。";

$GLOBALS['trickShiMianMaiFu']['succ'] = "“十面埋伏”用計成功！\n%s%s內無法出征。";
$GLOBALS['trickShiMianMaiFu']['succ_caution'] = "%s對%s使用“十面埋伏”！<br/>智者千慮必有一失，我方中計了！<br/>%s%s內無法出征。<br/>封鎖結束時間：%s。<br/>用“暗度陳倉”可以打破封鎖。";

$GLOBALS['trickShiMianMaiFu']['fail'] = "“十面埋伏”用計失敗。\n吃一塹長一智，對方剛剛中過同樣的計策，不會重復上當了。";
$GLOBALS['trickShiMianMaiFu']['fail_caution'] = "%s對%s使用“十面埋伏”！<br/>雕蟲小技也敢獻丑，我方識破了計謀。";  

$GLOBALS['trickBuXuanErZhan']['succ'] = "“不宣而戰”用計成功！ \n你和%s進入戰爭狀態，戰爭將持續%s。";


$GLOBALS['trickBuXuanErZhan']['cool_down'] = "“不宣而戰”用計失敗。\n吃一塹長一智，對方剛剛中過同樣的計策，不會重復上當了。";
$GLOBALS['trickBuXuanErZhan']['succ_report'] = "你對%s不宣而戰！<br/>你們已經進入戰爭狀態！<br/>戰爭期間雙方可以互相掠奪、占領對方的城池。<br/>戰爭持續%s后自動結束。<br/>戰爭開始時間：%s。<br/>戰爭結束時間：%s。<br/>出師無名，你的聲望降低%d。<br/>你可以使用道具，提升將領和軍隊的作戰能力，使他們能更有效的消滅敵人。<br/>“虎符”增加將領的統率，“武曲星符”增加將領的攻擊，“智多星符”增加將領的防御，<br/>“青囊書”增加軍隊傷兵的恢復數量，“陷陣戰鼓”增加軍隊攻擊力，“八卦陣圖”增加軍隊防御力。<br/>戰后別忘記到“校場”的“傷兵營”恢復傷兵。";
$GLOBALS['trickBuXuanErZhan']['succ_caution'] = "%s對你不宣而戰！<br/>你們已經進入戰爭狀態！<br/>戰爭期間雙方可以互相掠奪、占領對方的城池。<br/>戰爭持續%s后自動結束。<br/>戰爭開始時間：%s。<br/>戰爭結束時間：%s。<br/>你可以使用道具，提升將領和軍隊的作戰能力，使他們能更有效的消滅敵人。<br/>。“虎符”增加將領的統率，“武曲星符”增加將領的攻擊，“智多星符”增加將領的防御，<br/>“青囊書”增加軍隊傷兵的恢復數量，“陷陣戰鼓”增加軍隊攻擊力，“八卦陣圖”增加軍隊防御力。<br/>戰后別忘記到“校場”的“傷兵營”恢復傷兵。<br/>使用“免戰牌”在一段時間內避免被攻擊，使用“遷城令”遠離你的敵人。如果敵人太強大，你可以選擇不出戰，避免軍隊損失。或者聯系你的盟友來協助你防守。";

$GLOBALS['trickJinChaoTuoQiao']['succ'] = "“金蟬脫殼”用計成功！\n我方軍隊正在快速返回。";
$GLOBALS['trickJinChaoTuoQiao']['cool_down'] = "一支部隊一小時內只能使用一次“金蟬脫殼”。";

$GLOBALS['startWar']['succ_report'] = "你對%s宣戰。<br/>宣戰8小時后正式進入戰爭狀態。<br/>戰爭期間雙方可以互相掠奪、占領對方的城池。<br/>戰爭持續48小時后自動結束。<br/>戰爭開始時間：%s。<br/>戰爭結束時間：%s。<br/>你可以使用道具，提升將領和軍隊的作戰能力，使他們能更有效的消滅敵人。<br/>“虎符”增加將領的統率，“武曲星符”增加將領的攻擊，“智多星符”增加將領的防御，<br/>“青囊書”增加軍隊傷兵的恢復數量，“陷陣戰鼓”增加軍隊攻擊力，“八卦陣圖”增加軍隊防御力。<br/>戰后別忘記到“校場”的“傷兵營”恢復傷兵。";
$GLOBALS['startWar']['succ_caution'] = "%s對你宣戰。<br/>宣戰8小時后正式進入戰爭狀態。<br/>戰爭期間雙方可以互相掠奪、占領對方的城池。<br/>戰爭持續48小時后自動結束。<br/>戰爭開始時間：%s。<br/>戰爭結束時間：%s。<br/>你可以使用道具，提升將領和軍隊的作戰能力，使他們能更有效的消滅敵人。<br/>。“虎符”增加將領的統率，“武曲星符”增加將領的攻擊，“智多星符”增加將領的防御，<br/>“青囊書”增加軍隊傷兵的恢復數量，“陷陣戰鼓”增加軍隊攻擊力，“八卦陣圖”增加軍隊防御力。<br/>戰后別忘記到“校場”的“傷兵營”恢復傷兵。<br/>使用“免戰牌”在一段時間內避免被攻擊，使用“遷城令”遠離你的敵人。如果敵人太強大，你可以選擇不出戰，避免軍隊損失。或者聯系你的盟友來協助你防守。";

$GLOBALS['trickBaMemJinShuo']['fail'] = "“八門金鎖”用計失敗。\n吃一塹長一智，對方剛剛中過同樣的計策，不會重復上當了。";   
$GLOBALS['trickBaMemJinShuo']['fail_caution'] = "%s對我方軍隊使用“八門金鎖”！<br/>雕蟲小技也敢獻丑，我方識破了計謀。";    
$GLOBALS['trickBaMemJinShuo']['succ'] = "“八門金鎖”用計成功！\n敵方軍隊行軍時間延長%s。"; 
$GLOBALS['trickBaMemJinShuo']['succ_caution'] = "%s對我方軍隊使用“八門金鎖”！<br/>智者千慮必有一失，我方中計了！<br/>我方軍隊行軍時間延長%s。";

$GLOBALS['trickGuanMemDaGou']['fail_caution'] = "%s對我方軍隊使用“關門打狗”！<br/>雕蟲小技也敢獻丑，我方識破了計謀。";

$GLOBALS['trickGuanMemDaGou']['succ_caution'] = "%s對我方軍隊使用“關門打狗”！<br/>智者千慮必有一失，我方中計了！<br/>軍隊無法召回！<br/>用“金蟬脫殼”可以快速召回軍隊。";
$GLOBALS['trickGuanMemDaGou']['succ'] = "“關門打狗”用計成功！\n敵方軍隊無法召回！";

$GLOBALS['trickQianLiBenXi']['succ'] = "“千里奔襲”用計成功！\n我方軍隊行軍速度加快！";
$GLOBALS['trickQianLiBenXi']['wrong_state'] = "“千里奔襲”只能對行進中的隊伍使用！";
$GLOBALS['trickQianLiBenXi']['cool_down'] = "一支部隊一小時內只能使用一次“千里奔襲”。";


$GLOBALS['trickYouDiShenRu']['fail'] = "“誘敵深入”用計失敗。\n吃一塹長一智，對方剛剛中過同樣的計策，不會重復上當了。";   
$GLOBALS['trickYouDiShenRu']['fail_caution'] = "%s對我方軍隊使用“誘敵深入”！<br/>雕蟲小技也敢獻丑，我方識破了計謀。";    
$GLOBALS['trickYouDiShenRu']['succ'] = "“誘敵深入”用計成功！\n敵方軍隊行軍速度加快"; 
$GLOBALS['trickYouDiShenRu']['succ_caution'] = "%s對我方軍隊使用“誘敵深入”！<br/>智者千慮必有一失，我方中計了！<br/>我方軍隊行軍速度加快。";  

//Bufffer
$GLOBALS['getCityBuffer']['not_your_city'] = "該城池不屬于你！";


//Char
$GLOBALS['sendChatMsg']['no_enough_acoustic'] = "你沒有傳音符，不能在世界頻道聊天。";

$GLOBALS['getChatMsg']['chatFunc_shutDown'] = "該版本聊天功能暫時關閉。";


//defence
$GLOBALS['doGetDefenceInfo']['pre_building'] = "前提建筑";
$GLOBALS['doGetDefenceInfo']['level'] = "等級";
$GLOBALS['doGetDefenceInfo']['pre_technic'] = "前提科技";

$GLOBALS['getWallInfo']['no_wall'] = "該地尚未建造城墻";

$GLOBALS['startReinforceQueue']['build_zero_defence'] = "不能建造0個城防";
$GLOBALS['startReinforceQueue']['no_defence_info'] = "沒有該城防信息";
$GLOBALS['startReinforceQueue']['no_enough_resource'] = "資源不足";
$GLOBALS['startReinforceQueue']['no_free_space'] = "城墻位置已滿。";
$GLOBALS['startReinforceQueue']['no_pre_building'] = "前提建筑沒有建好。";
$GLOBALS['startReinforceQueue']['no_pre_technic'] = "前提科技沒有研究好。";
$GLOBALS['startReinforceQueue']['queue_reach_limit'] = "城墻的建造隊列已經達到上限。";

$GLOBALS['stopReinforceQueue']['no_barracks_info'] = "沒有該兵營信息";
$GLOBALS['stopReinforceQueue']['no_reinforcement_info'] = "沒有該城防信息";

$GLOBALS['dissolveDefence']['cant_dissolve_zero'] = "不能拆除0個城防";
$GLOBALS['dissolveDefence']['no_wall_info'] = "沒有該城墻信息";
$GLOBALS['dissolveDefence']['no_reinforcement_info'] = "沒有該城防信息";
$GLOBALS['dissolveDefence']['cant_dissolve_exceed'] = "不能拆除比當前城防數量還要多的城防";

$GLOBALS['accDefence']['only_onece'] = "一個城防建造隊列只能加速一次。";
$GLOBALS['accDefence']['no_goods'] = "你沒有道具“備城門”，請先去商城購買。";


//goods
$GLOBALS['checkGoodsCount']['server_busy'] = "服務器忙，請稍后再進行操作。";

$GLOBALS['checkGoodsArray']['server_busy'] = "服務器忙，請稍后再進行操作。";

$GLOBALS['useMenzhulin']['no_MenZhuLin'] = "你沒有盟主令。";
$GLOBALS['useMenzhulin']['not_join_union'] = "你還沒有加入聯盟，不能使用盟主令";
$GLOBALS['useMenzhulin']['union_not_exist'] = "聯盟不存在。";
$GLOBALS['useMenzhulin']['already_used'] = "聯盟已經使用過“盟主令”了，無須再次使用。";

$GLOBALS['useXiShuiDan']['no_hero_info'] = "無此將領信息。";
$GLOBALS['useXiShuiDan']['no_enough_XiShuiDan'] = "你沒有足夠的洗髓丹,不能給該將領洗點。";

$GLOBALS['useZhaoXinLin']['no_ZhaoXinLin'] = "你沒有招賢榜,不能招賢。";

$GLOBALS['UseMianZhanPai']['no_MianZhanPai'] = "你沒有免戰牌，不能進入免戰狀態";

$GLOBALS['useShenNongChu']['ShenNongChu'] = "神農鋤";
$GLOBALS['useShenNongChu']['advanced_ShenNongChu'] = "高級神農鋤";
$GLOBALS['useShenNongChu']['no_ShenNongChu'] = "你沒有%s，不能使用。";

$GLOBALS['useLuBanFu']['LuBanFu'] = "魯班斧";
$GLOBALS['useLuBanFu']['advanced_LuBanFu'] = "高級魯班斧";
$GLOBALS['useLuBanFu']['no_LuBanFu'] = "你沒有%s，不能使用。";

$GLOBALS['useKaiShanCui']['KaiShanCui'] = "開山錘";
$GLOBALS['useKaiShanCui']['advanced_KaiShanCui'] = "高級開山錘";
$GLOBALS['useKaiShanCui']['no_KaiShanCui'] = "你沒有%s，不能使用。";

$GLOBALS['useXuanTieLu']['XuanTieLu'] = "玄鐵爐";
$GLOBALS['useXuanTieLu']['advanced_XuanTieLu'] = "高級玄鐵爐";
$GLOBALS['useXuanTieLu']['no_XuanTieLu'] = "你沒有%s，不能使用。";

$GLOBALS['useXianZhenZhaoGu']['XianZhenZhaoGu'] = "陷陣戰鼓";
$GLOBALS['useXianZhenZhaoGu']['advanced_XianZhenZhaoGu'] = "高級陷陣戰鼓";
$GLOBALS['useXianZhenZhaoGu']['no_XianZhenZhaoGu'] = "你沒有%s，不能使用。";

$GLOBALS['useBaGuaZhenTu']['BaGuaZhenTu'] = "八卦陣圖";
$GLOBALS['useBaGuaZhenTu']['advanced_BaGuaZhenTu'] = "高級八卦陣圖";
$GLOBALS['useBaGuaZhenTu']['no_BaGuaZhenTu'] = "你沒有%s，不能使用。";

$GLOBALS['useShuiLiBian']['ShuiLiBian'] = "稅吏鞭";
$GLOBALS['useShuiLiBian']['advanced_ShuiLiBian'] = "高級稅吏鞭";
$GLOBALS['useShuiLiBian']['no_ShuiLiBian'] = "你沒有%s，不能使用。";

$GLOBALS['useQingNangShu']['no_QingNangShu'] = "你沒有青囊書，不能使用。";
$GLOBALS['useQingCangLing']['no_QingCangLing'] = "你沒有清倉令，不能使用。";

$GLOBALS['openTreasureBox']['YuanBao'] = "元寶";

$GLOBALS['useCopperBox']['no_CopperBox'] = "你沒有青銅寶箱，不能使用鑰匙。";
$GLOBALS['useCopperBox']['no_CopperKey'] = "你沒有青銅鑰匙，不能打開寶箱。";

$GLOBALS['useSilverBox']['no_SiverBox'] = "你沒有白銀寶箱，不能使用鑰匙。";
$GLOBALS['useSilverBox']['no_SiverKey'] = "你沒有白銀鑰匙，不能打開寶箱。";

$GLOBALS['useTreasureBox']['no_TreasureBox'] = "你沒有寶藏盒，不能使用。";
$GLOBALS['useGoldBox']['no_GoldBox'] = "你沒有黃金寶箱，不能使用鑰匙。";
$GLOBALS['useGoldBox']['no_GoldKey'] = "你沒有黃金鑰匙，不能打開寶箱。";
$GLOBALS['useOldWoodBox']['no_OldWoodBox'] = "你沒有古樸木盒，不能使用。";
$GLOBALS['useLoveBean']['no_LoveBean'] = "你沒有相思豆，不能使用。";
$GLOBALS['useBoleBao']['no_BoleBao'] = "你沒有伯樂包，不能使用。";

$GLOBALS['useFlagChar']['no_FlagChar'] = "你沒有旌旗，不能修改旗號。";
$GLOBALS['useFlagChar']['type_flag_name'] = "請輸入旗號。";
$GLOBALS['useFlagChar']['only_one_char'] = "旗號只能為一位字符。";

$GLOBALS['useMingTie']['no_goods'] = "你沒有“名貼”，不能修改君主名。";
$GLOBALS['useFireBarrel']['no_goods'] = "你沒有“火藥筒”，不能徹底拆除建筑。";

$GLOBALS['resPackage']['gain_gold'] = "獲得黃金%d。";
$GLOBALS['resPackage']['gain_food'] = "獲得糧食%d。";
$GLOBALS['resPackage']['gain_wood'] = "獲得木材%d。";
$GLOBALS['resPackage']['gain_rock'] = "獲得石料%d。";
$GLOBALS['resPackage']['gain_iron'] = "獲得鐵錠%d。";
$GLOBALS['resPackage']['gain_people'] = "獲得人口%d。";
$GLOBALS['resPackage']['gain_morale'] = "增加民心%d。";
$GLOBALS['resPackage']['gain_complaint'] = "增加民怨%d。";
$GLOBALS['resPackage']['gain_prestige'] = "增加聲望%d。";
$GLOBALS['resPackage']['gain_officepos'] = "晉升爵位為%s";
$GLOBALS['resPackage']['gain_nobility'] = "晉升官職為%s";
$GLOBALS['resPackage']['gain_yuanbao'] = "獲得了【禮金%d】";
$GLOBALS['resPackage']['gain_goods'] = "獲得了【%s】";
$GLOBALS['resPackage']['gain_soldier']="獲得了【%s】";
$GLOBALS['resPackage']['gain_defence']="獲得了【%s】";
$GLOBALS['resPackage']['gain_things']="獲得了【%s】";
$GLOBALS['resPackage']['gain_armor']="獲得了【%s】";

$GLOBALS['useResourcePackage']['no_ResourcePackage'] = "你沒有%s，不能使用。";
$GLOBALS['useResourcePackage']['gain_resource'] = "獲得黃金10000，糧食100000，木材100000，石料100000，鐵錠100000。";

$GLOBALS['useLoveRain']['gain_food'] = "相思雨滴發出明亮的閃光，你獲得糧食10000。";
$GLOBALS['useLoveRain']['gain_wood'] = "相思雨滴發出明亮的閃光，你獲得木材10000。";
$GLOBALS['useLoveRain']['gain_rock'] = "相思雨滴發出明亮的閃光，你獲得石料10000。";
$GLOBALS['useLoveRain']['gain_iron'] = "相思雨滴發出明亮的閃光，你獲得鐵錠10000。";
$GLOBALS['useLoveRain']['gain_gold'] = "相思雨滴發出明亮的閃光，你獲得黃金10000。";

$GLOBALS['useHuodongGoods']['no_HuoDongGoods'] = "你沒有%s，不能使用。";
$GLOBALS['useHuodongGoods']['govenment_lessThen_three'] = "你的官府等級不足3級，不能打開升級禮包。";
$GLOBALS['useHuodongGoods']['govenment_lessThen_two'] = "你的官府等級不足2級，不能打開白銀禮包I。";
$GLOBALS['useHuodongGoods']['govenment_lessThen_four'] = "你的官府等級不足4級，不能打開白銀禮包II。";
$GLOBALS['useHuodongGoods']['govenment_lessThen_five'] = "你的官府等級不足5級，不能打開黃金禮包I。";
$GLOBALS['useHuodongGoods']['govenment_lessThen_seven'] = "你的官府等級不足7級，不能打開黃金禮包II。";
$GLOBALS['useHuodongGoods']['govenment_lessThen_nine'] = "你的官府等級不足9級，不能打開黃金禮包III。";
$GLOBALS['useHuodongGoods']['lessThen_three_for_GongXun'] = "你的官府等級不足3級，不能打開功勛禮包。";
$GLOBALS['useHuodongGoods']['YuanBao'] = "元寶";


//ground
$GLOBALS['getGroundInfo']['no_ground_built'] = "該城池尚未建造校場。";

$GLOBALS['StartTroop']['no_flag']="你沒有道具“軍旗”，請先去商城購買或在出征時取消“使用軍旗”選項。";
$GLOBALS['StartTroop']['target_cant_be_current'] = "目標不能是當前城池。";
$GLOBALS['StartTroop']['city_in_battle'] = "城池正在遭受攻擊，不能出城。";
$GLOBALS['StartTroop']['suffer_ShiMianMaiFu'] = "你中了\"十面埋伏\",%s內不能出征。使用\"暗度陳倉\"可以打破封鎖。";
$GLOBALS['StartTroop']['adv_move_cooldown'] = "使用了高級遷城令的城池，在24小時內只能給自己的城池運輸，你需要等待%s后才能進行其他出征活動。";
$GLOBALS['StartTroop']['invalid_target'] = "指定的目標地點無效。";
$GLOBALS['StartTroop']['insufficient_ground_level'] = "校場等級不足，不能出征。";
$GLOBALS['StartTroop']['hero_not_found'] = "城內沒有找到該將領。";
$GLOBALS['StartTroop']['hero_is_busy'] = "該將領不在空閑狀態，不能出征。";
$GLOBALS['StartTroop']['hero_not_enough_force'] = "%s需要消耗%d點體力。將領體力不足，無法出征。";
$GLOBALS['StartTroop']['cant_detect_friendly_union'] = "不能偵察友好聯盟的城池。";
$GLOBALS['StartTroop']['not_in_battle_condition'] = "你們不處在戰爭狀態。不能進行出征。";
$GLOBALS['StartTroop']['wait_to_battle'] = "你們已經宣戰，但需要到%s才能出征。";
$GLOBALS['StartTroop']['huangjin_unfinished'] = "時機尚未成熟，必須完成黃巾史詩任務后才能攻擊名城。";
$GLOBALS['StartTroop']['has_following'] = "%s麾下有%d名忠心耿耿的部下，現在攻擊他的時機還不成熟。你必須先削弱他的羽翼，直到他勢單力薄剩下最后一個孤城的時候才能討伐他。";
$GLOBALS['StartTroop']['capital']="時機尚未成熟，不能攻打都城。";
$GLOBALS['StartTroop']['changan']="時機尚未成熟，不能攻打長安。";
//$GLOBALS['StartTroop']['has_great_hero'] = "出征失敗！該城池有名將鎮守，士兵不愿前去送死。在你成為太守之前，無法攻擊該城池。";

//"運輸","派遣","偵察","掠奪","占領"
$GLOBALS['StartTroop']['transport'] = "運輸";
$GLOBALS['StartTroop']['send'] = "派遣";
$GLOBALS['StartTroop']['detect'] = "偵察";
$GLOBALS['StartTroop']['harry'] = "掠奪";
$GLOBALS['StartTroop']['occupy'] = "占領";
$GLOBALS['StartTroop']['fanji'] ="反擊";
$GLOBALS['StartTroop']['qiyi'] ="起義";
$GLOBALS['StartTroop']['goto_battle'] = "派往戰場";
$GLOBALS['StartTroop']['some_thing_wrong'] = "目標地狀態出錯，請與客服聯系獲得幫助。";
$GLOBALS['StartTroop']['still_in_protection'] = "你還在新手保護階段，不能對其它玩家城池進行偵察，掠奪或占領。";
$GLOBALS['StartTroop']['in_peace_condition'] = "免戰狀態下不能對其它玩家城池進行偵察，掠奪或占領。";
$GLOBALS['StartTroop']['target_in_protection'] = "對方處在新手保護階段，不能進行偵察，掠奪或占領。";
$GLOBALS['StartTroop']['target_in_vacation'] = "對方處于休假狀態，不能對其進行出征。";
$GLOBALS['StartTroop']['target_be_locked'] = "對方處于被鎖定狀態，不能對其進行出征。";
$GLOBALS['StartTroop']['target_in_peace'] = "目標城池處于免戰狀態，不能進行偵察，掠奪或占領。";
$GLOBALS['StartTroop']['only_transport_to_friendly'] = "只能運輸到自己或同盟的城池。";
$GLOBALS['StartTroop']['transport_in_peace_or_protection'] = "新手或免戰狀態下，不能給盟友運輸。";
$GLOBALS['StartTroop']['only_send_to_friendly'] = "只能派遣到自己或同盟的城池和野地。";
$GLOBALS['StartTroop']['not_allow_union_troop'] = "對方不允許盟友駐軍，請讓對方先到鴻臚寺開啟允許選項。";
$GLOBALS['StartTroop']['send_in_peace_or_protection'] = "新手或免戰狀態下，不能向盟友派遣。";
$GLOBALS['StartTroop']['only_towards_enemy'] = "只能%s非同盟的城池或野地。";
$GLOBALS['StartTroop']['no_so_many_army'] = "城池中沒有這么多軍隊。";
$GLOBALS['StartTroop']['no_soldier'] = "軍隊中沒有士兵，不能出戰。";
$GLOBALS['StartTroop']['army_with_spy'] = "軍隊有斥候才能出征偵察。";
$GLOBALS['StartTroop']['spy_cant_alone'] = "斥候無法獨自出征進行掠奪、占領";
$GLOBALS['StartTroop']['no_enough_ground_level'] = "當前級別的校場不能發送超過%d的軍隊。";
$GLOBALS['StartTroop']['no_enough_food'] = "城內糧食不足，不能出征。";
$GLOBALS['StartTroop']['cant_carry_negative'] = "不能攜帶負數的資源出征。";
$GLOBALS['StartTroop']['no_enough_resource'] = "城內資源不足，不能出征。";
$GLOBALS['StartTroop']['army_carry_limit'] = "你的軍隊載重不足，無法帶上這么多資源。";
$GLOBALS['StartTroop']['succ'] = "出征成功。\n在軍隊面板中可以查看軍隊動態。";
$GLOBALS['StartTroop']['fail'] = "出征失敗。";
$GLOBALS['setAttackTactics']['succ'] = "成功修改出征戰術。";

$GLOBALS['setResistTactics']['succ'] = "成功修改防守戰術。";

$GLOBALS['cureWoundedSoldier']['no_enough_gold'] = "本城的黃金不足，不能治療所有傷兵。";
$GLOBALS['cureWoundedSoldier']['no_wounded_soldier'] = "本城沒有傷兵，不需要治療。";
$GLOBALS['cureWoundedSoldier']['succ'] = "治療成功，傷兵全部康復，返回軍營。";

$GLOBALS['dismissWoundedSoldier']['succ'] = "遣散成功，傷兵全部解甲歸田，成為城池人口。";

$GLOBALS['sayToLamster']['no_enough_gold'] = "本城的黃金不足，不能勸說所有逃兵。";
$GLOBALS['sayToLamster']['no_wounded_soldier'] = "本城沒有逃兵，不需要勸說。";
$GLOBALS['sayToLamster']['succ'] = "勸說成功，逃兵全部歸隊，返回軍營。";

$GLOBALS['dismissLamster']['succ'] = "遣散成功，逃兵全部解甲歸田，成為城池人口。";

//hotel
$GLOBALS['generateRecruitHero']['no_data_of_this_level'] = "沒有該級別數據";

$GLOBALS['getHotelInfo']['no_hotel_built'] = "該城池尚未建造客棧。";

$GLOBALS['recruitHero']['no_enough_gold'] = "本城池的黃金不足，不能招募該將領。";
$GLOBALS['recruitHero']['hotel_level_low'] = "招賢館等級不夠，不能容納更多的將領。";
$GLOBALS['recruitHero']['already_Have_One'] = "你已經招募了一名“%s”，需要解雇原來的將領才能重新招募！";
$GLOBALS['getRumor']['hotel_level_low']="客棧5級才能使用“市井傳聞”。";
$GLOBALS['getRumor']['never_heard'] = "從來沒有聽說過這個事情，客官你說笑的吧！";
$GLOBALS['getRumor']['dont_know_where_he_is'] = "不過他現在在什么地方小的就不清楚了。";
$GLOBALS['getRumor']['is_exile_now'] = "聽說%s剛剛在一場戰斗中失敗，正流亡荒野，暫時沒有人知道他躲藏的位置，等過一段時間他出現后再來打聽他的消息吧";
$GLOBALS['getRumor']['pay_for_hero'] = "聽說，%s在%s。如果你給我%d個元寶，我就告訴你更準確的情報，再給你一張他的畫像，有了畫像你就能發現并俘虜他。";
$GLOBALS['getRumor']['dont_know_where_it_is'] = "不過現在流落到什么地方小的就不清楚了。";
$GLOBALS['getRumor']['pay_for_staff'] = "聽說，%s在%s。如果你給我%d個元寶，我就告訴你更準確的情報。";;

$GLOBALS['searchRumor']['input_name_to_seartch'] = "請輸入有效的搜索名稱。";
$GLOBALS['searchRumor']['no_hotel_built'] = "沒有客棧不能搜索";
$GLOBALS['searchRumor']['no_enough_gold'] = "本城池沒有足夠的黃金。";
$GLOBALS['searchRumor']['no_useful_info'] = "客官，實在不好意思，我這里沒有你要問的消息。";

$GLOBALS['moreRumor']['no_enough_gold'] = "本城池沒有足夠的黃金。";

$GLOBALS['askDetail']['never_heard'] = "從來沒有聽說過這個事情，客官你說笑的吧！";
$GLOBALS['askDetail']['no_enough_YuanBao'] = "你連這點元寶都沒有，就不要浪費我的時間啦。";
$GLOBALS['askDetail']['no_info_of_hero'] = "我沒有這個人的消息，不勞您費元寶啦。";
$GLOBALS['askDetail']['hero_location'] = "據可靠消息，%s在%s。行動一定要快，不要被別人搶先了。";
$GLOBALS['askDetail']['word'] = ",字";
$GLOBALS['askDetail']['no_info_of_staff'] = "我沒有關于%s的消息，不勞您費元寶啦。";
$GLOBALS['askDetail']['staff_location'] = "據可靠消息%s在%s。行動一定要快，不要被別人搶先了。"; 

$GLOBALS['recordTask']['no_task_related_to_hero'] = "無此武將相關的任務";
$GLOBALS['recordTask']['no_task_related_to_staff'] = "無此物品相關的任務";
$GLOBALS['recordTask']['no_rumor_to_record'] = "沒有什么傳聞可記錄的。";
$GLOBALS['recordTask']['task_already_recorded'] = "您已經領取了此任務。";
$GLOBALS['recordTask']['task_accomplished'] = "您已經完成此任務。";
$GLOBALS['recordTask']['task_record_succ'] = "領取任務成功。";
$GLOBALS['recordTask']['npc_hero_exist'] = "你已經擁有該將領，不能領取該任務。";
$GLOBALS['recordTask']['task_list_full'] = "你的名將任務已經達到上限，放棄或完成一個任務后才能繼續領取。";
$GLOBALS['recordTask']['task_group_description'] = "玩家可以在客棧接受、發布委托任務。首先完成該委托任務的玩家，可以獲得相應的元寶獎勵。請注意委托的期限，過期后不能獲得獎勵。";
$GLOBALS['recordTask']['task_group_name'] = "委托任務";
$GLOBALS['recordTask']['task_group_name_0'] = "掠奪資源";
$GLOBALS['recordTask']['task_group_name_1'] = "消滅兵力";
$GLOBALS['recordTask']['task_group_name_2'] = "占領土地";

$GLOBALS['recordTask']['report']='你發布的委托任務由玩家 %s 完成，委托獎勵已被領取。<br/>任務內容：%s <br/>任務獎勵：元寶 %s 。';

$GLOBALS['recordTask']['task_content_prefix'] = "任務發布人要求你";
$GLOBALS['recordTask']['task_content_0'] = "%s在 %s 之前，掠奪 %s%s，獲得資源價值黃金 %s 。";
$GLOBALS['recordTask']['task_content_1'] = "%s在 %s 之前，到 %s%s，消滅兵力折合人口數 %s 。";
$GLOBALS['recordTask']['task_content_2'] = "%s在 %s 之前，占領一次 %s%s 。";
$GLOBALS['recordTask']['task_content_3'] = "%s在 %s 之前，完全占領 %s%s 。";

$GLOBALS['recordTask']['goal_0'] = "掠奪 %s%s，獲得資源價值黃金 %s 。";
$GLOBALS['recordTask']['goal_1'] = "到 %s%s，消滅兵力折合人口數 %s 。";
$GLOBALS['recordTask']['goal_2'] = "占領一次 %s%s 。";
$GLOBALS['recordTask']['goal_3'] = "完全占領 %s%s 。";


$GLOBALS['fetchRewardTask']['task_record_succ'] = "領取任務成功。";
$GLOBALS['fetchRewardTask']['no_task'] = "無此任務或該任務已經完成或過期";
$GLOBALS['fetchRewardTask']['my_task'] = "不能領取自己發布的委托任務。";
$GLOBALS['fetchRewardTask']['task_already_recorded'] = "您已經領取了此任務。";
$GLOBALS['fetchRewardTask']['task_accomplished'] = "您已經完成此任務。";
$GLOBALS['fetchRewardTask']['task_list_full'] = "你的委托任務已經達到上限，放棄或完成一個任務后才能繼續領取。";

//login
$GLOBALS['doLogin']['client_version_old'] = "客戶端的版本過低，請關閉瀏覽器，重新登錄游戲。";
$GLOBALS['doLogin']['server_not_start'] = "服務器尚未開放，請稍后再登錄。";
$GLOBALS['doLogin']['invalid_user_pwd'] = "錯誤的用戶名或密碼。";
$GLOBALS['doLogin']['server_full'] = "本服務器人數已滿。";
$GLOBALS['doLogin']['account_temp_locked'] = "你的帳號已經被封禁，%s后才能重新登錄。";
$GLOBALS['doLogin']['account_locked'] = "玩家君主已經被鎖定，不能登錄。";
$GLOBALS['doLogin']['need_51_login']="請先到51網登錄";
$GLOBALS['doLogin']['protect_user_info'] = "新手保護提醒";

//loginFunc
$GLOBALS['login']['login_fail'] = "登錄失敗，請填寫正確的賬號和密碼。";


//mail
$GLOBALS['getSysMail']['fromname'] = "系統";
$GLOBALS['readInboxMail']['mail_lost'] = "信件丟失，請與客服聯系！";

$GLOBALS['readOutboxMail']['mail_lost'] = "信件丟失，請與客服聯系！";

$GLOBALS['readSysMail']['mail_lost'] = "信件丟失，請與客服聯系！";

$GLOBALS['checkMailFull']['inbox_full'] = "收件箱已滿，請刪除多余信件后再發送。";
$GLOBALS['checkMailFull']['outbox_full'] = "發件箱已滿，請刪除多余信件后再發送。";

$GLOBALS['sendPersonMail']['untitled'] = "無標題";
$GLOBALS['sendPersonMail']['enemy'] = "你在對方仇人名單中，無法發信";
$GLOBALS['sendPersonMail']['cant_find_addressee'] = "找不到收信人［%s］！";
$GLOBALS['sendPersonMail']['content_illegal'] = "信件內容包含非法字符，不能發送";
$GLOBALS['sendPersonMail']['auto_mail_content'] = "【系統自動提示信息：官方不會以任何形式在個人信件中通知用戶中獎，如果您收到此類信件，請不要相信，更不要向信息發布者匯款，謹防受騙！】\n\n";

$GLOBALS['sendUnionMail']['untitled'] = "無標題";
$GLOBALS['sendUnionMail']['union'] = "［聯盟］";
$GLOBALS['sendUnionMail']['not_champion'] = "你不是盟主或副盟主，不能發聯盟群發信！";
$GLOBALS['sendUnionMail']['no_enough_acoustic'] = "您沒有足夠的傳音符，請去商城購買后再發送聯盟群發信。";


//market
$GLOBALS['getMarketInfo']['no_market_built'] = "該城池尚未建造市場。";

$GLOBALS['cancelSell']['cant_cancel'] = "已經達成的交易不能取消。";

$GLOBALS['accelerateSell']['no_MuNiuLiuMa'] = "你沒有“木牛流馬”,不能對交易進行加速。";
$GLOBALS['accelerateSell']['trade_not_exist'] = "指定的交易不存在，不能進行加速。";

$GLOBALS['buyFromMerchant']['input_amount'] = "請輸入正常的購買數量。";
$GLOBALS['buyFromMerchant']['no_enough_gold'] = "本城的黃金不夠。";
$GLOBALS['buyFromMerchant']['no_enough_YuanBao'] = "元寶數量不足，不能完成交易。";
$GLOBALS['buyFromMerchant']['succ'] = "交易成功！";
$GLOBALS['buyFromMerchant']['buy_limit'] = "%d級市場和商人交易的單筆交易上限為%d00000黃金。";

$GLOBALS['sellToMerchant']['input_amount'] ="請輸入正常的出售數量。";
$GLOBALS['sellToMerchant']['no_enough_food'] = "本城的糧食不足，不能完成交易。";
$GLOBALS['sellToMerchant']['no_enough_wood'] = "本城的木材不足，不能完成交易。";
$GLOBALS['sellToMerchant']['no_enough_rock'] = "本城的石料不足，不能完成交易。";
$GLOBALS['sellToMerchant']['no_enough_iron'] = "本城的鐵錠不足，不能完成交易。";
$GLOBALS['sellToMerchant']['no_enough_YuanBao'] = "你的元寶數量不足，不能完成交易。";
$GLOBALS['sellToMerchant']['succ'] = "交易成功！";

$GLOBALS['sellToUser']['invalid_amount'] = "出售數量不正確。";
$GLOBALS['sellToUser']['trade_time_limit'] = "交易時限不能少于1小時。";
$GLOBALS['sellToUser']['no_free_caravan'] = "城內已經沒有空閑商隊了。";
$GLOBALS['sellToUser']['single_trade_upperLimit'] = "%d級市場的單筆交易上限為%d00000。";
$GLOBALS['sellToUser']['no_enough_food'] = "本城的糧食不夠。";
$GLOBALS['sellToUser']['price_runaway'] = "出售價格超出規定范圍，不能出售。";
$GLOBALS['sellToUser']['no_enough_wood'] = "本城的木材不夠。";
$GLOBALS['sellToUser']['no_enough_rock'] = "本城的石料不夠。";
$GLOBALS['sellToUser']['no_enough_iron'] = "本城的鐵錠不夠。";

$GLOBALS['buyFromUser']['trade_not_exist'] = "該交易不存在。";
$GLOBALS['buyFromUser']['bought_by_others'] = '該資源已經被其他玩家搶先購買。';
$GLOBALS['buyFromUser']['cant_buy_from_yourself'] = "你不能購買自己其它城池的資源。";
$GLOBALS['buyFromUser']['no_enough_gold'] = "本城的黃金不足。";
$GLOBALS['buyFromUser']['no_free_caravan'] = "城內已經沒有空閑商隊了，請升級市場。";
$GLOBALS['buyFromUser']['distance_too_far'] = "城池之間的距離過遠，交易失敗。";
$GLOBALS['buyFromUser']['sell_to_union_only'] = "對方只賣給同一聯盟的人，你和對方已經不在同一聯盟內，不能購買。";
$GLOBALS['sellGoods']['building_level'] = "市場等級達到5級才能出售寶物。";
$GLOBALS['sellGoods']['nobility_low'] = "爵位達到“公士”才能出售寶物。";
$GLOBALS['sellGoods']['not_enough_goods'] = "你沒有那么多道具，請正確填寫道具數量。";
$GLOBALS['reward_task']['nobility_low'] = "爵位達到大夫才能使用委托任務。";


//office
$GLOBALS['getOfficeInfo']['no_office_built'] = "鐵錠";

$GLOBALS['setCityChief']['set_chief_fail'] = "任命將領失敗。";
$GLOBALS['setCityChief']['set_chief_hero_busy'] = "將領出征中，不能任命。";

$GLOBALS['dismissHero']['cant_dissmiss_this'] = "不能解雇該將領。";
$GLOBALS['dismissHero']['only_dissmiss_free_hero'] = "空閑狀態的將領才能解雇。";

$GLOBALS['upgradeHero']['cant_upgrade_this'] = "不能升級該將領。";
$GLOBALS['upgradeHero']['cant_upgrade_out_hero'] = "不能升級不在城池內的將領。";
$GLOBALS['upgradeHero']['no_enough_exp'] = "將領的經驗不足，不能升級。";

$GLOBALS['addHeroPoint']['cant_find_hero'] = "找不到該將領。";
$GLOBALS['addHeroPoint']['cant_add_out_hero'] = "不能給不在城池內的將領加屬性點。";
$GLOBALS['addHeroPoint']['no_extra_potential'] = "沒有多余的潛力。";

$GLOBALS['clearHeroPoint']['cant_find_hero'] = "找不到該將領。";
$GLOBALS['clearHeroPoint']['cant_clean_out_hero'] = "不能給不在城池里的將領洗點。";

$GLOBALS['changeHeroName']['name_too_long'] = "將領名太長，不能超過4個字。";
$GLOBALS['changeHeroName']['input_valid_name'] = "請輸入有效的將領名字";
$GLOBALS['changeHeroName']['invalid_char'] = "不能采用非法的字符串作為將領名。";
$GLOBALS['changeHeroName']['cant_find_hero'] = "找不到該將領。";
$GLOBALS['changeHeroName']['cant_change_out_hero'] = "不能給不在城池內的將領改名。";
$GLOBALS['changeHeroName']['cant_change_famous_hero'] = "不能給歷史將領改名。";

$GLOBALS['largessHero']['cant_find_hero'] = "找不到該將領。";
$GLOBALS['largessHero']['cant_largess_out_hero'] = "不能給不在城池內的將領賞賜。";
$GLOBALS['largessHero']['wait_duration'] = "你剛剛賞賜過該將領，%s后才能再次賞賜。";
$GLOBALS['largessHero']['no_enough_gold'] = "本城的黃金不足，不能賞賜將領。";
$GLOBALS['largessHero']['no_need_gold'] = "黃金已經無法打動該將領，你需要賞賜他更貴重的珍寶。";
$GLOBALS['largessHero']['no_this_prop'] = "你沒有此道具，不能賞賜將領。";

$GLOBALS['releaseHero']['hero_not_exist'] = "此將領不存在";
$GLOBALS['releaseHero']['hero_not_captive'] = "該將領不在俘虜狀態，不能釋放";
$GLOBALS['releaseHero']['hero_not_coming'] = "該將領不在投奔狀態，不能回絕";

$GLOBALS['getNpcIntroduce']['no_hero_info'] = "無該將領信息";
$GLOBALS['getNpcIntroduce']['not_famous_hero'] = "該將領不是歷史武將，沒有說明。";

$GLOBALS['trySummonHero']['no_hero_info'] = "無該將領信息";
$GLOBALS['trySummonHero']['hero_not_captive'] = "不在俘虜狀態，不能招降。";
$GLOBALS['trySummonHero']['no_enough_nobility'] = "我的主公，必定是威震天下的英雄，你連\"%s\"都沒有達到，我是不會跟隨你的。";
$GLOBALS['trySummonHero']['hero_need'] = "若要求得良將，須得以誠相待，賞賜寶物金帛是必不可少的。收服該將領需要：";
$GLOBALS['trySummonHero']['gold'] = "黃金";
$GLOBALS['tryAcceptHero']['hero_not_coming'] = "不在投奔狀態，不能接納。";

$GLOBALS['tryCallbackHero']['hotel_level_low'] = "招賢館等級不夠，不能容納更多的將領。";
$GLOBALS['tryCallbackHero']['no_hero_info'] = "無該將領信息";
$GLOBALS['tryCallbackHero']['hero_not_exile'] = "不在流亡狀態，不能召回。";
$GLOBALS['tryCallbackHero']['hero_need'] = "若要求得良將，須得以誠相待，賞賜寶物金帛是必不可少的。收服該將領需要：";
$GLOBALS['tryCallbackHero']['gold'] = "黃金";


$GLOBALS['sureSummonHero']['hero_not_exist'] = "此將領不存在";
$GLOBALS['sureSummonHero']['hero_not_captive'] = "該將領不在俘虜狀態，不能招降";
$GLOBALS['sureSummonHero']['no_enough_gold'] = "本城沒有足夠的黃金，不能使該將領為你效命。";
$GLOBALS['sureSummonHero']['no_enough_goods'] = "你沒有足夠的%s，不能使%s為你效命。";


//report
$GLOBALS['report']['connection_drop'] = "你已經掉線，請重新登錄。";
$GLOBALS['report']['cant_operate_others'] = "你無權對其它人的數據進行操作。";


//reportFunc
$GLOBALS['callBackTroop']['invalid_army'] = "無效的軍隊。";
$GLOBALS['callBackTroop']['on_back']="這支軍隊被對方使用了“關門打狗”計謀，無法返回，使用“金蟬脫殼”，可以快速召回軍隊。";
$GLOBALS['callBackTroop']['gather']="軍隊正在采集，不能召回。請先收獲再召回。";
$GLOBALS['callBackTroop']['army_in_battle'] = "不能召回正在戰斗中的軍隊。";
$GLOBALS['callBackTroop']['army_on_way_back'] = "該軍隊已經在回城途中了。";

$GLOBALS['getBattleData']['battle_end'] = "戰斗已經結束，請查看報戰。";
$GLOBALS['getBattleData']['battle_data_lost'] = "戰斗數據丟失！";

$GLOBALS['setSoldierTactics']['cant_change_enemy_tactics'] = "你不能改變對方的戰術。";


//shop
$GLOBALS['buyGoods']['invalid_amount'] = "購買數量無效。";
$GLOBALS['buyGoods']['stop_sale'] = "此商品已經停售。";
$GLOBALS['buyGoods']['sold_out'] = "此商品已經賣光了。";
$GLOBALS['buyGoods']['no_enough_YuanBao'] = "你的元寶不足，請充值。";
$GLOBALS['buyGoods']['no_enough_Gift'] = "你的禮金不足。";
$GLOBALS['buyGoods']['reach_remain_amount_todayLimit'] = "購買數量無效。此商品每人每天限購%d個，你今天已經購買%d個，只能再購買%d個此商品。";
$GLOBALS['buyGoods']['reach_buy_todayLimit'] = "此商品每人每天限購%d個，你今天已經達到購買限制，請明天再來購買此商品。";
$GLOBALS['buyGoods']['reach_remain_amountLimit'] = "購買數量無效。此商品每人限購%d個，你已經購買%d個，只能再購買%d個此商品。";
$GLOBALS['buyGoods']['reach_buy_limit'] = "此商品每人限購%d個，你已經達到購買限制，不能再購買更多此商品。";
$GLOBALS['buyGoods']['nobility_limit'] = "只有爵位達到“公士”才能購買和使用“聚賢包”。";

$GLOBALS['buyGoods']['no_enough_Credit'] = "你的榮譽值不夠，不能購買此商品";
$GLOBALS['buyGoods']['no_enough_Medal'] = "你的%s不夠，不能購買此商品";
$GLOBALS['buyGoods']['no_medal'] = "你的%s數量不夠，不能換%s個漢室勛章";
$GLOBALS['buyGoods']['can_not_exchange'] = "此商品已經不能用勛章兌換。";
$GLOBALS['buyGoods']['only_one_goods'] = "此商品只能購買1個"; 
$GLOBALS['buyGoods']['no_tip'] = "沒有屬性提示"; 


$GLOBALS['exchangeLiquan']['code_notNull'] = "禮券碼不能為空。";
$GLOBALS['exchangeLiquan']['invalid_code'] = "禮券碼無效。請重新輸入正確的禮券碼。";
$GLOBALS['exchangeLiquan']['used_code'] = "該禮券碼已被使用。";
$GLOBALS['exchangeLiquan']['code_bind'] = "該禮券碼已和另外的玩家綁定，你無法使用。";
$GLOBALS['exchangeLiquan']['YuanBao'] = "元寶";
$GLOBALS['exchangeLiquan']['description_of_YuanBao'] = "元寶，用于購買商場道具，可通過充值獲得。";


//soldier
$GLOBALS['doGetSoldierInfo']['pre_building'] = "前提建筑";
$GLOBALS['doGetSoldierInfo']['level'] = "等級";
$GLOBALS['doGetSoldierInfo']['pre_technic'] = "前提科技";

$GLOBALS['getArmyInfo']['no_barracks_built'] = "該地尚未建造軍營。";

$GLOBALS['startDraftQueue']['cant_recruit_zero'] = "不能招募0個士兵。";
$GLOBALS['startDraftQueue']['no_barracks_info'] = "沒有該兵營信息";
$GLOBALS['startDraftQueue']['no_army_branch_info'] = "沒有該兵種信息。";
$GLOBALS['startDraftQueue']['no_enough_resource'] = "資源不足。";
$GLOBALS['startDraftQueue']['lack_free_people'] = "空閑人口不足，不能訓練%d個士兵。";
$GLOBALS['startDraftQueue']['no_pre_building'] = "前提建筑沒有建好。";
$GLOBALS['startDraftQueue']['no_pre_technic'] = "前提科技沒有研究好。";
$GLOBALS['startDraftQueue']['reach_queue_limit'] = "該兵營的訓練隊列已經達到上限。";

$GLOBALS['stopDraftQueue']['no_barracks_info'] = "沒有該兵營信息。";
$GLOBALS['stopDraftQueue']['no_army_branch_info'] = "沒有該兵種信息。";

$GLOBALS['dissolveSoldier']['cant_dismiss_zero'] = "不能解散0個兵。";
$GLOBALS['dissolveSoldier']['no_barracks_info'] = "沒有該兵營信息";
$GLOBALS['dissolveSoldier']['no_army_branch_info'] = "沒有該兵種信息。";
$GLOBALS['dissolveSoldier']['cant_dismiss_exceed'] = "不能解散比當前士兵數量還要多的士兵。";
$GLOBALS['accSoldier']['only_once'] = "一個造兵隊列只能加速一次。";
$GLOBALS['accSoldier']['no_goods'] = "你沒有道具“韓信三篇”，請先去商城購買道具。";

//store
$GLOBALS['modifyStoreRate']['negative_store_rate'] = "存放比例不能為負數！";
$GLOBALS['modifyStoreRate']['resource_total_100'] = "四項資源存放比例之和不能超過100。";
$GLOBALS['modifyStoreRate']['succ_change_rate'] = "修改倉庫存放比例成功！";

$GLOBALS['payToPack']['res_type_error'] = "沒有這樣的資源類型";
$GLOBALS['payToPack']['not_enough_moeny'] = "您的元寶數量不夠";
$GLOBALS['payToPack']['not_enough_res'] = "您的資源數目不足";
$GLOBALS['payToPack']['count_error'] = "打包數目不正確";


//task
$GLOBALS['getReward']['already_got'] = "你已經領取過該任務的獎勵。";
$GLOBALS['getReward']['task_not_finished'] = "任務尚未完成,不能領取獎勵";
$GLOBALS['getReward']['global_task_end'] = "該任務已經結束。";
$GLOBALS['getReward']['invalid_count'] = "請輸入正確人領取數量。";
$GLOBALS['getReward']['not_enough_things'] = "任務物品不足，不能領取%d次。";
$GLOBALS['getReward']['not_enough_remain']="任務剩余次數不足，批量領取失敗。";
$GLOBALS['getReward']['not_allowed_multi']="該任務不允許批量領取。";



$GLOBALS['doGetTechnicInfo']['pre_building'] = "前提建筑";
$GLOBALS['doGetTechnicInfo']['level'] = "等級";
$GLOBALS['doGetTechnicInfo']['pre_technic'] = "前提科技";

$GLOBALS['getCollegeInfo']['no_college_built'] = "該城池尚未建造學院。";

$GLOBALS['startUpgradeTechnic']['no_technic_info'] = "沒有該科技信息。";
$GLOBALS['startUpgradeTechnic']['technic_full']="科技已經升達到頂級，不能繼續研究了";
$GLOBALS['startUpgradeTechnic']['only_analysis_1_tech'] = "一所書院只能同時研究一項科技，已經有其它科技正在研究。";
$GLOBALS['startUpgradeTechnic']['no_enough_resource'] = "資源不足，不能升級該科技。";
$GLOBALS['startUpgradeTechnic']['no_pre_building'] = "前提建筑沒有建好。";
$GLOBALS['startUpgradeTechnic']['no_pre_technic'] = "前提科技沒有研究好。";

$GLOBALS['stopUpgradeTechnic']['no_upgrading_tech_info'] = "無此在建的科技的信息";


//union
$GLOBALS['getHongLuInfo']['no_HongLu_built'] = "該城池尚未建造鴻臚寺。";

$GLOBALS['kickUnionTroop']['not_your_city'] = "本城池不屬于你，不能進行操作。";
$GLOBALS['kickUnionTroop']['army_not_exist'] = "該駐軍已經不存在。";

$GLOBALS['createUnion']['already_joined_other_union'] = "你已經屬于一個聯盟，只有退出原來聯盟后才能再創建新的聯盟。";
$GLOBALS['createUnion']['union_name_notNull'] = "聯盟名字不能為空。";
$GLOBALS['createUnion']['union_name_tooLong'] = "聯盟名字過長，不能超過8個字符。";
$GLOBALS['createUnion']['has_ivalid_char'] = "聯盟名字含有不被允許的字符";
$GLOBALS['createUnion']['level_lessThen_2'] = "你的鴻臚寺等級不足2級，不能創建聯盟。";
$GLOBALS['createUnion']['gold_not_enough'] = "本城池的黃金不足10000,不能創建聯盟。";
$GLOBALS['createUnion']['use_another_name'] = "聯盟名已經被占用，請重新輸入聯盟名。";
$GLOBALS['createUnion']['add_union_event'] = " 創建聯盟";

$GLOBALS['getUnionApplyInvite']['succ'] = "］已經通過你的申請請求，你現在已經是聯盟的一員了";

$GLOBALS['applyJoin']['no_HongLu_built'] = "你尚未建造鴻臚寺，不能申請加入聯盟。";
$GLOBALS['applyJoin']['already_joined_other_union'] = "你已經在一個聯盟中，退出當前聯盟后才能申請加入其它聯盟。";
$GLOBALS['applyJoin']['reset_application'] = "你已經申請加入［%s］,去鴻臚寺撤消原申請之后才能重新申請。";
$GLOBALS['applyJoin']['send_application_succ'] = "申請發送成功，請等待盟主同意！";
$GLOBALS['applyJoin']['union_not_exist'] = "該聯盟已經解散。";

$GLOBALS['getApplyList']['not_official'] = "你不是聯盟官員，無權查看聯盟申請列表。";

$GLOBALS['acceptApply']['taget_joined_other_union'] = "對方已經加入其他聯盟，不能再加入本聯盟。";
$GLOBALS['acceptApply']['target_has_no_HongLu'] = "對方尚未建造鴻臚寺，不能加入聯盟。";
$GLOBALS['acceptApply']['not_official'] = "你不是聯盟官員，無權處理玩家的聯盟申請。";
$GLOBALS['acceptApply']['union_not_exist'] = "聯盟不存在！";
$GLOBALS['acceptApply']['data_record_not_exist'] = "數據記錄不存在！";
$GLOBALS['acceptApply']['no_HongLu_built'] = "你沒有建造鴻臚寺，不能加新的玩家。";
$GLOBALS['acceptApply']['union_is_full'] = "你的聯盟人數已滿，不能再加新的玩家。";
$GLOBALS['acceptApply']['addUnionEvent'] = "%s 通過了 %s 入盟申請！";

$GLOBALS['rejectApply']['not_official'] = "你不是聯盟官員，無權處理玩家的聯盟申請。";

$GLOBALS['acceptInvite']['invalid_invitation'] = "該邀請已經無效。";
$GLOBALS['acceptInvite']['union_not_exist'] = "該聯盟已經不存在。";
$GLOBALS['acceptInvite']['no_HongLu_built'] = "你沒有建造鴻臚寺，不能加入聯盟。";
$GLOBALS['acceptInvite']['already_joined_other_union'] = "你已經屬于一個聯盟，不能加入其它聯盟。";
$GLOBALS['acceptInvite']['addUnionEvent'] = "%s 邀請 %s 加入聯盟！";

$GLOBALS['rejectInvite']['invalid_invitation'] = "該邀請已經無效。";
$GLOBALS['rejectInvite']['union_not_exist'] = "該聯盟已經不存在。";

$GLOBALS['loadUnionDetail']['union_dissmissed'] = "該聯盟已經解散。";

$GLOBALS['loadUnionInfo']['not_belongTo_union'] = "你已經不屬于任何聯盟。請重新選擇聯盟加入或者創建聯盟。";
$GLOBALS['loadUnionInfo']['your_union_is_out'] = "你屬于的聯盟已經不存在。請重新選擇聯盟加入或者創建聯盟。";

$GLOBALS['loadUnionMemberList']['you_belongTo_none_union'] = "你已經不屬于任何一個聯盟。";

$GLOBALS['leaveUnion']['you_belongTo_none_union'] = "你當前不屬于任何一個聯盟。";
$GLOBALS['leaveUnion']['your_union_is_out'] = "你所屬的聯盟已經不存在。";
$GLOBALS['leaveUnion']['chief_cant_leave'] = "你的聯盟還有成員，身為盟主不能退出聯盟。";
$GLOBALS['leaveUnion']['official_cant_leave'] = "聯盟官員不能退出聯盟，請先辭職!";
$GLOBALS['leaveUnion']['addUnionEvent'] = "%s 退出了聯盟！";

$GLOBALS['getInviteList']['you_are_not_official'] = "你不是聯盟官員，無權邀請玩家加入聯盟。";

$GLOBALS['cancelInvite']['you_are_not_official'] = "你不是聯盟官員，無權取消邀請。";

$GLOBALS['inviteUser']['enter_target_name'] = "請輸入你邀請的玩家名。";
$GLOBALS['inviteUser']['name_length_most_8'] = "玩家名字最多8個字符";
$GLOBALS['inviteUser']['you_are_not_official'] = "你不是聯盟官員，無權發起邀請。";
$GLOBALS['inviteUser']['named_user_not_exist'] = "你輸入的玩家不存在，請重新輸入。";
$GLOBALS['inviteUser']['cant_invite_yourself'] = "不能邀請自己加入聯盟";
$GLOBALS['inviteUser']['taget_joined_other_union'] = "對方已經加入某個聯盟，不能接受邀請。";
$GLOBALS['inviteUser']['your_union_is_full'] = "你的聯盟人數已滿，不能再邀請新的玩家。";

$GLOBALS['kickMember']['enter_target_name'] = "請指定要開除的成員名字！";
$GLOBALS['kickMember']['name_length_most_8'] = "玩家名稱最多8個字符！";
$GLOBALS['kickMember']['not_elder'] = "你的盟內職位不是長老以上級別，無權開除會員。";
$GLOBALS['kickMember']['target_name_not_exist'] = "你輸入的玩家不存在，請重新輸入。";
$GLOBALS['kickMember']['target_not_in_your_union'] = "該玩家不是本盟會員，不能開除";
$GLOBALS['kickMember']['descend_target_level'] = "不能開除擔任聯盟職務的成員，請先將其降級！";
$GLOBALS['kickMember']['cant_kick_oneself'] = "不能開除自己，請用“退出聯盟”功能來退出。";
$GLOBALS['kickMember']['addUnionEvent'] = "%s 把 %s 開除出聯盟！";

$GLOBALS['changeLeader']['enter_target_name'] = "請輸入新盟主名字";
$GLOBALS['changeLeader']['name_length_most_8'] = "玩家名稱最多8個字符！";
$GLOBALS['changeLeader']['you_are_not_chief'] = "你不是盟主，無權轉讓盟主。";
$GLOBALS['changeLeader']['target_name_not_exist'] = "你輸入的玩家不存在，請重新輸入。";
$GLOBALS['changeLeader']['target_not_in_your_union'] = "該玩家不是本盟會員，不能轉讓。";
$GLOBALS['changeLeader']['upgrade_vice_chief'] = "盟主只能轉讓給副盟主，請將對方提升為副盟主再進行轉讓！";
$GLOBALS['changeLeader']['addUnionEvent'] = "盟主 %s 把盟主轉讓給 %s ！";

$GLOBALS['getUnionIntro']['you_are_not_chief'] = "你不是盟主或副盟主，沒有權限修改介紹文字。";

$GLOBALS['modifyIntro']['union_name_notNull'] = "聯盟名字不能為空！";
$GLOBALS['modifyIntro']['union_name_tooLong'] = "聯盟名字過長，不能超過8個字符。";
$GLOBALS['modifyIntro']['union_description_tooLong'] = "聯盟介紹過長，不能超過200字符。";
$GLOBALS['modifyIntro']['union_announce_tooLong'] = "聯盟公告過長，不能超過500字符。";
$GLOBALS['modifyIntro']['you_are_not_chief'] = "你不是盟主或副盟主，沒有權限修改介紹文字。";
$GLOBALS['modifyIntro']['union_name_in_use'] = "聯盟名字已被其他聯盟占用，請更換其他名字！";
$GLOBALS['modifyIntro']['invalid_char'] = "聯盟名字含有不被允許的字符";
$GLOBALS['modifyIntro']['addUnionEvent'] = "%s 將聯盟名稱改為 %s !";

$GLOBALS['getUnionRelation']['not_belongTo_union'] = "你已經不屬于任何聯盟。請重新選擇聯盟加入或者創建聯盟。";

$GLOBALS['addUnionRelation']['enter_target_name'] = "請輸入對方聯盟名字";
$GLOBALS['addUnionRelation']['union_name_tooLong'] = "聯盟名稱最多8個字符！";
$GLOBALS['addUnionRelation']['you_are_not_chief'] = "你不是盟主或副盟主，沒有權限處理聯盟外交關系。";
$GLOBALS['addUnionRelation']['cant_contact_with_oneself'] = "不能和自己的聯盟建立外交關系！";
$GLOBALS['addUnionRelation']['target_union_not_exist'] = "你輸入的聯盟不存在！";
$GLOBALS['addUnionRelation']['too_frequency'] = "%s后才能再次更改與該聯盟的外交關系。！";
$GLOBALS['addUnionRelation']['friendly'] = "友好";
$GLOBALS['addUnionRelation']['neutral'] = "中立";
$GLOBALS['addUnionRelation']['hostile'] = "敵對";
$GLOBALS['addUnionRelation']['union_declare_war'] = "聯盟宣戰";
$GLOBALS['addUnionRelation']['mail_content'] = "　　%s 將對本盟的外交關系設置為 %s，本盟隨時可能遭到敵人的襲擊。<br/>　　將對方聯盟加為敵對關系，可以與其交戰。";
$GLOBALS['addUnionRelation']['unionWar_declare'] = "［%s］聯盟對［%s］聯盟宣戰！";
$GLOBALS['addUnionRelation']['set_A_and_B'] = "%s 將本盟和 %s 的外交關系設置為 %s";
$GLOBALS['addUnionRelation']['set_B_and_A'] = "%s 將對本盟的外交關系設置為 %s";

$GLOBALS['removeUnionRelation']['you_are_not_chief'] = "你不是盟主或副盟主，沒有權限處理聯盟外交關系。";
$GLOBALS['removeUnionRelation']['friendly'] = "友好";
$GLOBALS['removeUnionRelation']['neutral'] = "中立";
$GLOBALS['removeUnionRelation']['hostile'] = "敵對";
$GLOBALS['removeUnionRelation']['cancel_A_and_B'] = "%s 取消了本盟和 %s 的 %s 外交關系";
$GLOBALS['removeUnionRelation']['cancel_B_and_A'] = "%s 取消了和本盟的 %s 外交關系";

$GLOBALS['getUnionEvent']['not_in_union'] = "你不在任何聯盟中，沒有聯盟事件！";

$GLOBALS['setUnionProvicy']['not_authorizied'] = "你沒有權限修改對方的聯盟職位！";
$GLOBALS['setUnionProvicy']['union_memeber'] = "成員";
$GLOBALS['setUnionProvicy']['union_vice_chief'] = "副盟主";
$GLOBALS['setUnionProvicy']['union_elder'] = "長老";
$GLOBALS['setUnionProvicy']['union_official'] = "官員";
$GLOBALS['setUnionProvicy']['descend_level'] = "%s 將 %s 降級為 %s";
$GLOBALS['setUnionProvicy']['upgrade_level'] = "%s 將 %s 升級為 %s";

$GLOBALS['demissionUnion']['not_in_union'] = "你已經不在聯盟中";
$GLOBALS['demissionUnion']['no_any_position'] = "你已經沒有任何職務，不用辭職";
$GLOBALS['demissionUnion']['union_dissmissed'] = "聯盟已經解散！";
$GLOBALS['demissionUnion']['chief_cant_resign'] = "盟主不能辭職，請先轉讓盟主，再辭職";
$GLOBALS['demissionUnion']['union_memeber'] = "成員";
$GLOBALS['demissionUnion']['union_vice_chief'] = "副盟主";
$GLOBALS['demissionUnion']['union_elder'] = "長老";
$GLOBALS['demissionUnion']['union_official'] = "官員";
$GLOBALS['demissionUnion']['add_union_event'] = "%s 辭去 %s 職務";

$GLOBALS['getUnionReport']['not_in_union'] = "你不在任何聯盟中，沒有聯盟軍情！";

$GLOBALS['getUnionReportDetail']['not_in_union'] = "你已經不在任何聯盟中，不能查看聯盟軍情！";
$GLOBALS['getUnionReportDetail']['report_not_found'] = "戰報不存在或者已經過期，被系統刪除了！";


//user
$GLOBALS['useGoods']['no_this_good'] = "你擁有的該道具數量為0，請去商城購買后再使用。";
$GLOBALS['useGoods']['no_pack_good'] = "禮包不存存，請與客服聯系。";
$GLOBALS['useGoods']['acoustic_used_in_world_channel'] = "傳音符是在世界頻道聊天的時候使用。";
$GLOBALS['useGoods']['ShenNongChu_valid_date'] = "神農鋤有效期截止到";
$GLOBALS['useGoods']['LuBanFu_valid_date'] = "魯班斧有效期截止到";
$GLOBALS['useGoods']['KaiShanCui_valid_date'] = "開山錘有效期截止到";
$GLOBALS['useGoods']['XuanTieLu_valid_date'] = "玄鐵爐有效期截止到";
$GLOBALS['useGoods']['XianZhenZhaoGu_valid_date'] = "陷陣戰鼓有效期截止到";
$GLOBALS['useGoods']['BaGuaZhenTu_valid_date'] = "八卦陣圖有效期截止到";
$GLOBALS['useGoods']['MoJiaCanJuan'] = "墨家殘卷";
$GLOBALS['useGoods']['MojiaTuZhi'] = "墨家圖紙";
$GLOBALS['useGoods']['MoJiaDianJi'] = "墨家典籍";
$GLOBALS['useGoods']['MoJiaMiJi'] = "墨家密笈";
$GLOBALS['useGoods']['MianZhanPai'] = "免戰牌";
$GLOBALS['useGoods']['JinNang'] = "錦囊";
$GLOBALS['useGoods']['use_MengZhuLing_succ'] = "“盟主令”使用成功，聯盟成員人數上限提升為100。";
$GLOBALS['useGoods']['XiSuiDan_used_for_reset_hero'] = "洗髓丹是在給將領洗點的時候使用。";
$GLOBALS['useGoods']['ZhaoXianBang_used_for_hire_hero'] = "“招賢榜”在客棧的“招賢納士”處使用。";
$GLOBALS['useGoods']['QingNangShu_valid_date'] = "“青囊書”有效期截止到";
$GLOBALS['useGoods']['YaoYiLin_valid_date'] = "“徭役令”有效期截止到";
$GLOBALS['useGoods']['Junlingzhuang_valid_date'] = "“軍令狀”有效期截止到";
$GLOBALS['useGoods']['TuiEnLing_valid_date'] = "“推恩令”有效期截止到";
$GLOBALS['useGoods']['TuiEnLing_valid_msg'] = "““推恩令”使用成功，您的爵位暫時提升到“%s”，持續%s時間。";
$GLOBALS['useGoods']['ShangDuiQiYue_valid_date'] = "“商隊契約”有效期截止到";
$GLOBALS['useGoods']['QingCangLing_valid_date'] = "“清倉令”有效期截止到";
$GLOBALS['useGoods']['KaoGongJi_valid_date']="“%s”有效時間截止到";
$GLOBALS['useGoods']['ShuiLiBian_valid_date']="“稅吏鞭”有效時間截止到";
$GLOBALS['useGoods']['JunQi_used_for_army'] = "“軍旗”在軍隊出征的時候使用。";
$GLOBALS['useGoods']['AnMingGaoShi_cool_down']="“安民告示”72小時內只能使用一次，請在%s后再使用。";
$GLOBALS['useGoods']['AnMingGaoShi_succ']="“安民告示”使用成功，當前城池民心升至100，民怨降為0。";
$GLOBALS['useGoods']['HanXinSanPian_used_for_army']="“韓信三篇”是在對招兵隊列加速的時候使用。";
$GLOBALS['useGoods']['BeiChengMen_used_for_army']="“備城門”在對城防建造隊列加速的時候使用。";
$GLOBALS['useGoods']['armor_box_full']="你已經沒有足夠的空間來放置新的裝備，請將多余的裝備回收。";
$GLOBALS['useGoods']['func_not_in_use'] = "此功能尚未開放。";
$GLOBALS['useGoods']['invalid_data']="數據錯誤，請與客服聯系。";
$GLOBALS['useGoods']['hero_state_wrong']="將領正在出征或者沒有效忠于你。只能給在本城內效忠于你的將領使用。";
$GLOBALS['useGoods']['hero_level_full']="將領等級達到上限，不需要再增加經驗。";
$GLOBALS['useGoods']['no_need_shemian']="你沒有小于0的戰場榮譽，不需要使用赦免文書。";
$GLOBALS['useGoods']['shemian_suc']="赦免文書使用成功，你的戰場榮譽已經變為0。";
$GLOBALS['useGoods']['shemian_fail']="赦免文書使用失敗";

$GLOBALS['useGoods']['qingzhan_suc']="請戰書使用成功，你的劇情戰場參戰次數已經變為0。";
$GLOBALS['useGoods']['qingzhan_fail']="請戰書使用失敗";
$GLOBALS['useGoods']['today_war_count_zero']="當前劇情戰場參戰次數為0,不需要重置";


$GLOBALS['useMojiaGoods']['invalid_param'] = "參數錯誤";
$GLOBALS['useMojiaGoods']['no_need_to_use'] = "該科技不需要使用寶物。";
$GLOBALS['useMojiaGoods']['no_enough_goods'] = "你沒有足夠的寶物，不能使用。";

$GLOBALS['useLuBanGoods']['invalid_param'] = "參數錯誤";
$GLOBALS['useLuBanGoods']['no_need_to_use'] = "該建筑不需要使用寶物。";
$GLOBALS['useLuBanGoods']['no_enough_goods'] = "你沒有足夠的寶物，不能使用。";


$GLOBALS['doCreateCity']['province_is_full'] = "該州的城池已經太多了，請選擇其它州建城。";
$GLOBALS['doCreateCity']['reType_city_name'] = "創建城池出錯，請重新選擇州。";

$GLOBALS['createCity']['city_name_tooLong'] = "城池名不能超過8個字符。";

$GLOBALS['createRole']['cant_duplicate_create'] = "您不能重復創建新城池。";
$GLOBALS['createRole']['city_holder_name_notNull'] = "君主名不能為空。";
$GLOBALS['createRole']['city_holder_name_tooLong'] = "君主名不能超過8個字符。";
$GLOBALS['createRole']['city_name_tooLong'] = "城池名不能超過8個字符。";
$GLOBALS['createRole']['invalid_char'] = "不能采用非法的字符串作為君主名。";
$GLOBALS['createRole']['no_illege_char'] = "君主名不能含有不被允許的字符";
$GLOBALS['createRole']['enter_flag_char'] = "請輸入旗號。";
$GLOBALS['createRole']['single_char'] = "旗號只能為一位字符。";
$GLOBALS['createRole']['used_city_holder_name'] = "君主名已經被占用，請重新輸入。";

$GLOBALS['changeUserState']['mianzhan'] = "免戰";
$GLOBALS['changeUserState']['xiujia'] = "休閑";
$GLOBALS['changeUserState']['invalid_pwd'] = "密碼錯誤，不能修改狀態。";
$GLOBALS['changeUserState']['no_need_recovery'] = "你當前狀態已經是正常了，不需要恢復。";
$GLOBALS['changeUserState']['no_need_MianZhanPai'] = "你當前狀態已經是免戰，不需要重新免戰。";
$GLOBALS['changeUserState']['wait_to_use_MianZhanPai'] = "后才能使用免戰牌。";
$GLOBALS['changeUserState']['some_city_in_war'] = "你的某個城池處于戰亂中，不能";
$GLOBALS['changeUserState']['army_out'] = "你有軍隊在外，不能";
$GLOBALS['changeUserState']['technic_upgrading'] = "你有科技在升級，不能";
$GLOBALS['changeUserState']['building_upgrading'] = "你有建筑在升級，不能";
$GLOBALS['changeUserState']['soldier_queue'] = "你有兵營正在招募軍隊，不能";
$GLOBALS['changeUserState']['defence_queue'] = "你有城防正在建造，不能";
$GLOBALS['changeUserState']['union_army_in_city'] = "你的城池有盟友軍隊駐軍，不能";
$GLOBALS['changeUserState']['vacation_limit'] = "休假至少2天，最多99天。";
$GLOBALS['changeUserState']['vacation_cant_dismiss'] = "休假至少要48小時后才能解除。在%s后才能解除休假狀態。";


$GLOBALS['changeCityPosition']['has_army_outside'] = "本城有軍隊在外，不能遷城。";
$GLOBALS['changeCityPosition']['has_ally_force'] = "本城（或附屬野地）有其它盟友駐軍，不能遷城。";
$GLOBALS['changeCityPosition']['has_other_city_force'] = "本城附屬野地有你其他城池駐軍，不能遷城";
$GLOBALS['changeCityPosition']['city_in_battle'] = "本城正在戰亂中，不能遷城。";
$GLOBALS['changeCityPosition']['cant_move_great_city'] = "不能對名城使用遷城。";
$GLOBALS['changeCityPosition']['no_QianChengLing'] = "你沒有遷城令，不能遷城。";
$GLOBALS['changeCityPosition']['no_adv_QianChengLing'] = "你沒有高級遷城令，不能遷城。";
$GLOBALS['changeCityPosition']['province_is_full'] = "該州的城池已無空地，請選擇其它州遷城。";
$GLOBALS['changeCityPosition']['invalid_target_city'] = "遷城只能遷往無主平地，目標不符合條件，請重新遷城。";


//util
$GLOBALS['MakeEndTime']['year'] = "年";
$GLOBALS['MakeEndTime']['month'] = "月";
$GLOBALS['MakeEndTime']['day'] = "日";

$GLOBALS['MakeTimeLeft']['hour'] = "小時";
$GLOBALS['MakeTimeLeft']['min'] = "分鐘";
$GLOBALS['MakeTimeLeft']['sec'] = "秒";

$GLOBALS['checkCityExist']['no_city_info'] = "沒有該城池信息。";

$GLOBALS['realLogin']['ip_blocked'] = "你的IP段已經被禁。";

$GLOBALS['doGetCityAllInfo']['no_city_info'] = "沒有該城池的信息。";

$GLOBALS['doGetCityResource']['no_city_info'] = "無此城池信息";

$GLOBALS['setCityTax']['invalid_tax'] = "稅率不合法。";


//world
$GLOBALS['startWar']['war_is_declared'] = "你們已經處于宣戰狀態。";
$GLOBALS['startWar']['new_protect'] = "你處于新手保護狀態，無法宣戰。";
$GLOBALS['startWar']['target_new_protect'] = "對方處于新手保護狀態，無法宣戰。";

$GLOBALS['createCityFromLand']['only_flatlands_can_build'] = "只有平地才能筑城";
$GLOBALS['createCityFromLand']['target_flatlands_notYours'] = "目標平地不是本城池的附屬平地，不能在此處筑城";
$GLOBALS['createCityFromLand']['target_flatlands_in_war'] = "目標平地正在戰亂中，不能筑城。";
$GLOBALS['createCityFromLand']['no_army'] = "沒有軍隊駐在此處不能筑城。";
$GLOBALS['createCityFromLand']['no_enough_resource'] = "你所有的駐軍攜帶的資源不足，請帶上每種資源及黃金各10000才能筑城";
$GLOBALS['createCityFromLand']['nobility_not_enough'] = "你的爵位不夠，筑城失敗。當你的爵位晉升為“%s”時，才能統治更多的城池。";

$GLOBALS['addFavourites']['already_in_fav'] = "該目標已被收藏。 ";
$GLOBALS['addFavourites']['fav_is_full'] = "你的收藏目標已達到上限10個，刪除其他目標后才能繼續收藏。";
$GLOBALS['addFavourites']['succ'] ="收藏成功！你可以在校場的出征界面查看收藏列表。";

$GLOBALS['deleteFavourites']['error_in_del_fav'] = "刪除收藏目標出錯。";

$GLOBALS['setFavouritesComments']['already_exist'] = "收藏目標不存在。";
$GLOBALS['setFavouritesComments']['succ'] = "修改目標備注成功。";

$GLOBALS['getMoJiaGoods']['complete_quickly']="立即完成";

$GLOBALS['equipArmor']['arm_not_exist']="該件裝備不存在";
$GLOBALS['equipArmor']['not_right_part']="不能裝備在這個部位";
$GLOBALS['equipArmor']['no_hp_max']="裝備已經沒有耐久，不能使用，請先修復。";
$GLOBALS['equipArmor']['arm_in_use']="該件裝備已經被其他武將使用了";
$GLOBALS['equipArmor']['level']="這件裝備需要將領等級達到%d級才能使用。";
$GLOBALS['equipArmor']['hero_state_wrong']="將領不在本城或沒有效忠于你。只能給本城內效忠于你的將領換裝。";
$GLOBALS['repairArmor']['no_need']="這件裝備沒有損壞，不需要修理。";
$GLOBALS['repairArmor']['no_gold']="本城黃金不足。";
$GLOBALS['repairArmor']['no_hp_max']="裝備已經沒有耐久，不能修理，只能修復了。";
$GLOBALS['renovateArmor']['no_need']="這件裝備沒有損毀，不需要修復。";
$GLOBALS['renovateArmor']['no_money']="你沒有足夠的元寶，請充值后再修復。";
$GLOBALS['sellArmor']['market_level_low']="市場達到5級才能回收裝備。";
$GLOBALS['sellArmor']['nobility_low']="爵位達到“公士”才能回收裝備。";

//govern
$GLOBALS['governOthers']['city_cannot_govern']="你所在城池不是名城，不能下達政令。";
$GLOBALS['governOthers']['target_not_in_war']="該城池處于免戰狀態，不能下達政令。";
$GLOBALS['governOthers']['not_enouth_government_level']="名城官府等級達到10級，才能下達政令。";
$GLOBALS['governOthers']['target_has_been_govern']="該城池今天已經被征收過了，不能重復下達政令。";
$GLOBALS['governOthers']['too_many_time']="%s在%s每天可以下達%s次政令，你今天已經下令%s次，不能再次下令了。";
$GLOBALS['governOthers']['not_enough_level']="該城池不受你管轄，不能下達政令。";
$GLOBALS['governOthers']['gold_report']="%s城（%s,%s）向你征收稅賦，你損失黃金%s。";
$GLOBALS['governOthers']['people_report']="%s城（%s,%s）向強抽壯丁，你損失人口%s。";
$GLOBALS['governOthers']['food_report']="%s城（%s,%s）向你征收糧食，你損失糧食%s。";
$GLOBALS['governOthers']['incorporation_report']="%s城（%s,%s））強行收編你的軍隊，你損失%s%s。";
$GLOBALS['governOthers']['disarmament_report']="%s城（%s,%s）勒令你裁軍，你損失%s%s。";

$GLOBALS['governOthers']['gold_suc']="收稅成功，獲得黃金%s。";
$GLOBALS['governOthers']['people_suc']="抽丁成功，獲得人口%s。";
$GLOBALS['governOthers']['food_suc']="征糧成功，增加糧食%s。";
$GLOBALS['governOthers']['incorporation_suc']="收編成功，增加%s%s。";
$GLOBALS['governOthers']['disarmament_suc']="裁軍成功，對方減少%s%s。";


//寶藏
$GLOBALS['treasure']['not_enough_map']="你已經沒有藏寶圖了。";
$GLOBALS['treasure']['not_enough_money']="你的元寶數目不足，長者不愿為你鑒寶...";
$GLOBALS['treasure']['report']="經過鑒定，寶藏埋藏的地點在%s[%s，%s]，在該地點采集1小時后，可獲得寶藏。趕快行動，超過24小時，寶藏就會消失了。寶藏消失時間：%s";
$GLOBALS['treasure']['succ']="長者破譯了寶藏地圖的玄機，送給你一封密信，快到公文里查看吧！";
$GLOBALS['treasure']['has_not']="你周圍的土地貧瘠，沒有寶藏，就不收你的元寶了...";

$GLOBALS['heroState']['0']="空閑";
$GLOBALS['heroState']['1']="城守";
$GLOBALS['heroState']['2']="出征";
$GLOBALS['heroState']['3']="戰斗";
$GLOBALS['heroState']['4']="駐守";
$GLOBALS['heroState']['5']="俘虜";
$GLOBALS['heroState']['6']="投奔";

$GLOBALS['fileName']['0']="野地";
$GLOBALS['fileName']['1']="平地";
$GLOBALS['fileName']['2']="荒漠";
$GLOBALS['fileName']['3']="森林";
$GLOBALS['fileName']['4']="草原";
$GLOBALS['fileName']['5']="山地";
$GLOBALS['fileName']['6']="湖泊";
$GLOBALS['fileName']['7']="沼澤";

$GLOBALS['auto_trans']['max_auto_trans']="自動運輸商隊不能超過10支。";
$GLOBALS['auto_trans']['time_too_long']="開始時間不能大于商隊契約失效時間";
$GLOBALS['auto_trans']['not_my_city']="目標城池必須是我方城池";
$GLOBALS['auto_trans']['max_count']="運輸數量不能超過出發城池市場等級的限制";
$GLOBALS['auto_trans']['no_qiyue']="你沒有使用商隊契約";

$GLOBALS['open_box']['msg']="%s 打開%s ，在一堆寶物中發現%s,價值%s禮金";
$GLOBALS['open_box']['qingtong']="青銅寶箱";
$GLOBALS['open_box']['baiyin']="白銀寶箱";
$GLOBALS['open_box']['huangjin']="黃金寶箱";
$GLOBALS['open_box']['gupu']="古樸木盒";
$GLOBALS['open_box']['xiangsi']="相思豆";
$GLOBALS['open_box']['yuanbao']="元寶";

$GLOBALS['summon_hero']['npc']="名將 %s 被 %s 降服，投其帳下效力";
$GLOBALS['start_war']['union_msg']="盟友 %s 被敵人 %s 宣戰。";

$GLOBALS['userTuiEnling']['guanneihou']="您的爵位已經達到或超過“關內侯”，無須再使用推恩令。";

$GLOBALS['battle']['nobility_not_rearch']="您的爵位還沒達到或超過“公士”，無法打開戰場。";
$GLOBALS['battle']['no_battle_field']="沒有這個戰場";
$GLOBALS['battle']['user_already_in_battle']="你已經在一個戰場中，不能再創建新的戰場。";
$GLOBALS['battle']['honour_invalid']="您的戰場榮譽為負數，不能加入或者創建戰場。使用赦免文書可以將所有為負值的戰場榮譽清0。";
$GLOBALS['battle']['max_people']="所選擇陣營人數已滿，請選擇其他陣營。";
$GLOBALS['battle']['create_failed']="創建戰場失敗。";
$GLOBALS['battle']['user_not_in_battle']="你已經不在戰場中或者戰場已經結束。";
$GLOBALS['battle']['state_0']="前進";
$GLOBALS['battle']['state_1']="返回";
$GLOBALS['battle']['state_2']="等待";
$GLOBALS['battle']['state_3']="戰斗";
$GLOBALS['battle']['state_4']="駐軍";
$GLOBALS['battle']['troop_in_fight']="軍隊正在戰斗中。";
$GLOBALS['battle']['troop_waiting_fight']="軍隊正在等待戰斗。";
$GLOBALS['battle']['troop_not_stay']="你所選擇的軍隊正在行動中，請選擇其他軍隊。";
$GLOBALS['battle']['troop_not_ahead']="軍隊不在前進中，不能召回。";
$GLOBALS['battle']['troop_in_same_city_not_stay']="不能攻擊同據點內不在駐守狀態的軍隊。";
$GLOBALS['battle']['callback_succ']="軍隊正在返回出發城池，請到出征面板中查看。";
$GLOBALS['battle']['callback_fail']="召回軍隊失敗。";
$GLOBALS['battle']['troop_not_exist']="戰場中沒有當前你所選擇的這支軍隊。";
$GLOBALS['battle']['targettroop_not_exist']="戰場中沒有當前你要攻擊的這支軍隊。";
$GLOBALS['battle']['city_not_exist']="據點不存在";
$GLOBALS['battle']['city_cannot_goto']="只能前往相鄰的據點。";
$GLOBALS['battle']['not_same_union']="據點不屬于我方陣營，不能派遣";
$GLOBALS['battle']['dispatch_suc']="派遣成功，請到出征面板中查看。";
$GLOBALS['battle']['dispatch_fail']="派遣軍隊失敗。";
$GLOBALS['battle']['attack_troop_not_exist']="目標軍隊不存在，不能攻擊。";
$GLOBALS['battle']['same_union']="同一陣營不能攻擊。";
$GLOBALS['battle']['troop_in_fight_when_quit']="還有軍隊正在行動中，不能退出戰場。";
$GLOBALS['battle']['exit_suc']="你已經中途退出戰場，戰場中調遣的援軍返還為戰場榮譽，剩余軍隊正在返回城池，請到公文戰報里查看戰場結果。";
$GLOBALS['battle']['battle_froze']="戰場已經結束，你不能再行動，戰場將在剩余的最后一場戰斗結束后關閉。";
$GLOBALS['battle']['no_enough_taofa']="沒有足夠的討伐令，不能創建戰場。";
$GLOBALS['battle']['call_army_max']="調遣援軍超過最大數目";
$GLOBALS['battle']['call_army_not_enough_honour']="你沒有足夠的戰場榮譽來調遣援軍";
$GLOBALS['battle']['call_army_not_enough_yuanjunling']="你沒有足夠的援軍令來調遣援軍";
$GLOBALS['battle']['call_army_fail']="調遣援軍失敗。";
$GLOBALS['battle']['call_army_suc']="調遣援軍成功。";
$GLOBALS['battle']['troop_not_in_move']="軍隊不在行進中，不需要加速。";
$GLOBALS['battle']['faster_army_suc']="加速行軍成功，軍隊行進速度提高50%。";
$GLOBALS['battle']['faster_army_fail']="加速行軍失敗。";
$GLOBALS['battle']['call_army_not_enough_jixingjun']="你沒有急行軍令，不能加速行軍，請到商城里購買。";
$GLOBALS['battle']['troop_in_same_city']="當前選擇的軍隊已經在這個據點中。";
$GLOBALS['battle']['union_flag_text']=array(1=>'漢',2=>'黃',3=>'袁',4=>'曹',5=>'漢',6=>'宦');
$GLOBALS['battle']['union_name']=array(1=>'漢',2=>'黃',3=>'袁紹',4=>'曹操',5=>'漢',6=>'宦');
$GLOBALS['battle']['state_in']="參加";
$GLOBALS['battle']['state_invite']="邀請中";
$GLOBALS['battle']['invite_not_creator']="你不是戰場的創建者，不能邀請。";
$GLOBALS['battle']['invite_max_people']="當前戰場已經達到最大人數，不能再邀請。";
$GLOBALS['battle']['name']=array(1001=>'黃巾之亂',2001=>'官渡之戰',3001=>'十常侍之亂');
$GLOBALS['battle']['task_group']=array(1=>'60000,60001,60002,60003,60004',3=>'60005,60006,60007,60008,60009,60010',4=>'60011,60012,60013,60014,60015,60016,60017,60018',5=>'60019,60020');

$GLOBALS['battle']['task_thing']=array(1=>'0',3=>'0',4=>'0',5=>'40001,40002,40003');

$GLOBALS['battle']['invite_user_not_exist']="您所邀請的用戶不存在。";
$GLOBALS['battle']['invite_user_suc']="邀請已經發送。";
$GLOBALS['battle']['invite_user_fail']="邀請失敗。";
$GLOBALS['battle']['invite_user_already']="您已經向該用戶發送過邀請。";
$GLOBALS['battle']['invite_not_exist']="該邀請不存在或者已經取消";
$GLOBALS['battle']['cancel_invite_suc']="取消邀請成功。";
$GLOBALS['battle']['cancel_invite_fail']="取消邀請失敗。";
$GLOBALS['battle']['invite_not_enough_honour']="被邀請人戰場榮譽是負數，不能邀請。";
$GLOBALS['battle']['join_user_already_in_battle']="你已經在一個戰場中，不能再加入新的戰場。";
$GLOBALS['battle']['today_war_count_reach_limit']="你今日的參戰次數已經達到5次了，可使用道具請戰書讓參戰次數立即變為0";
$GLOBALS['battle']['no_such_invite']="沒有這個邀請。";
$GLOBALS['battle']['join_fail']="加入戰場失敗。";
$GLOBALS['battle']['road_not_opens']="前往該據點的通路尚未打開。";
$GLOBALS['battle']['battle_in_ready']="戰場還沒有達到開啟條件，你還不能行動，請等待。";
$GLOBALS['battle']['cao_has_food']="曹操糧草尚未耗盡，不能貿然攻擊許都！";
$GLOBALS['battle']['yuan_has_food']="袁紹糧草尚未耗盡，不能貿然攻擊鄴城！";
$GLOBALS['battle']['no_evidence']="沒有罪證，不能貿然攻擊洛陽！";
$GLOBALS['battle']['back_target']="戰場";
$GLOBALS['battle']['spy_cant_alone']="斥候無法獨立出征到戰場。";
$GLOBALS['battle']['user_full']="該戰場人數已滿，不能加入。";
$GLOBALS['battle']['too_many_battle']="戰場數目已滿，暫時不能開啟新的戰場";

$GLOBALS['battle']['troop_leave']="[%s] 軍 [%s] 部 拔營起寨，前往 [%s]。";

$GLOBALS['battle']['become_captain'] = "由于原隊長退出，你已經榮升為隊長，有權利邀請隊員。";

$GLOBALS['start_battle_troop']['city_not_allow']="該據點不是出發據點。";
$GLOBALS['start_battle_troop']['not_enought_honour']="向該據點出發需要更多的戰場榮譽。";

$GLOBALS['battle']['quit_not_ready']="%s 戰場還在準備中，退出不扣除戰場榮譽，現有戰場榮譽%s。";
$GLOBALS['battle']['quit_lose']="%s 戰場結果：我方陣營失敗，扣除戰場榮譽%s。剩余援軍返還戰場榮譽%s。現有戰場榮譽 %s。";
$GLOBALS['battle']['quit_win'] ="%s 戰場結果：我方陣營勝利，獎勵戰場榮譽%s。剩余援軍返還戰場榮譽%s。獲得%s 勛章 %s 枚 。現有戰場榮譽 %s。";
$GLOBALS['battle']['quit_leave'] ="%s 戰場未結束逃離戰場，扣除戰場榮譽%s。剩余援軍返還戰場榮譽%s。現有戰場榮譽 %s。";
$GLOBALS['battle']['quit_leave_notstartbattle'] ="%s 戰場還未開啟就離開了，不扣除戰場榮譽。現有戰場榮譽 %s。";

$GLOBALS['battle']['metal_name']=array(1=>"平定黃巾勛章",3=>"袁軍官渡勛章",4=>"曹軍官渡勛章",5=>"平定十常侍勛章");
$GLOBALS['battle']['metal_gid']=array(1=>30001,3=>30002,4=>30003,5=>30004);

$GLOBALS['gaojituienling']['dafuyishang']="大夫以上爵位才能使用高級推恩令。";
$GLOBALS['reward_task']['no_goods']="你沒有委托文書，不能發布委托。";
$GLOBALS['reward_task']['money_zero']="元寶獎勵不能少于10個。";
$GLOBALS['reward_task']['day_error']="委托天數應在10天以內。";
$GLOBALS['reward_task']['not_enough_money']="元寶不夠，不能發布委托任務。";
$GLOBALS['reward_task']['task_type_error']="沒有此類委托任務。";
$GLOBALS['reward_task']['goal_error']="委托任務目標無效。";
$GLOBALS['reward_task']['no_level']="只有五級以上客棧才能發布委托任務。";
$GLOBALS['reward_task']['too_much']="你已經發布了10個委托任務，不能繼續發布更多委托。";


$GLOBALS['start_battle_troop']['target_not_exist']="你還有沒有加入戰場或戰場已經關閉，不能出征";
$GLOBALS['start_battle_troop']['max_troop']="你已經向戰場派遣了2支軍隊，不能再派遣了。";
$GLOBALS['start_battle_troop']['no_hero']="沒有將領帶領不能前往戰場。";

$GLOBALS['battle']['login'] = "%s 軍 %s 加入了戰場。";
$GLOBALS['battle']['logout'] = "%s 軍 %s 退出了戰場。";
$GLOBALS['battle']['no_battle_infor'] = "沒有戰場信息";

$GLOBALS['dismissHero']['has_armor']="卸下將領身上所有的裝備才能解雇！";
$GLOBALS['upgradeHero']['level_100']="將領已經升到頂級。";
$GLOBALS['changeUserState']['no_city']="你沒有城池。";

$GLOBALS['paygift']['firstpay_title']="新服首沖送大禮";
$GLOBALS['paygift']['firstpay_content']="親愛的玩家：\n\n感謝您參加本次“新服首沖送大禮”充值活動，您已獲得：遷城令*1、建筑圖紙*1、徭役令*1、珍珠*2、白色裝備箱*1，請注意查收您的物品欄，祝您游戲愉快！\n\n&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;《熱血三國》運營團隊";

$GLOBALS['thingsname']['hanshi_xunzhang']="漢室勛章";
$GLOBALS['thingsname']['pingding_huangjin_xunzhang']="平定黃巾勛章";
$GLOBALS['thingsname']['yuanjun_guandu_xunzhang']="袁軍官渡勛章";
$GLOBALS['thingsname']['caojun_guandu_xunzhang']="曹軍官渡勛章";

$GLOBALS['building']['pre_building']="前提建筑";
$GLOBALS['battle']['not_enought_gezi']="你沒有足夠的信鴿用來偵察，請到商城購買。";
$GLOBALS['battle']['patrol_report'] ="戰場偵察結果：<br/>地點 %s<br/>敵將 %s<br/>等級 %s<br/>";
$GLOBALS['battle']['patrol_report_soldier']=
array(
1=>"民夫",
2=>"義兵",
3=>"斥候",
4	=>"長槍兵",
5	=>"刀盾兵",
6	=>"弓箭兵",
9	=>"輜重車",
7	=>"輕騎兵",
8	=>"鐵騎兵",
10	=>"床弩",
11	=>"沖車",
12	=>"投石車",
13	=>"流民",
14	=>"匪兵",
15	=>"強盜",
16	=>"山賊",
17	=>"馬賊",
18	=>"黃巾眾",
19	=>"黃巾軍",
20	=>"黃巾精兵",
21	=>"黃巾弓手",
22	=>"黃巾頭目"
);
$GLOBALS['battle']['patrol_report_suc']="偵察成功，請到公文戰報里查看敵人部隊信息。";

?>