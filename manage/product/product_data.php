<?php
/**
 * @inform 产品接口 -- 日数据恢复
 * @author 许孝敦
 * @param null
 * @return array(today_avg,)
 * @example  
 */
if (! defined("MANAGE_INTERFACE"))
    exit();
function twotoone ($two)
//二维数组转成一维数组
{
    for ($i = 0; $i < count($two); $i ++) {
        for ($j = 0; $j < count($two[$i]); $j ++) {
            $one[] = $two[$i][$j];
        }
    }
    return $one;
}
try {
    //参数判断
    if (! isset($day) || empty($day)) {
        $time = date("Ymd");
        $day = sql_fetch_one_cell("select unix_timestamp('$time')");
    } else {
        if ($day > time()) {
            throw new Exception('date error');
        }
    }
    if (! isset($type) || empty($type)) {
        throw new Exception('type error');
    }
    //	$regtime = sql_fetch_one_cell("select regtime from sys_user where uid=1001");
    //	if($day < $regtime){
    //		throw new Exception('date error');
    //	}
    switch ($type) {
        case 'FamousCityNum':
            {
                //名城占有数
                $result = sql_fetch_one_cell("select count(1) as count from sys_city where type>0 and uid>1000");
                if (! empty($result)) {
                    $ret['content']['FamousCityNum'] = $result;
                } else {
                    $ret['content']['FamousCityNum'] = 0;
                }
                break;
            }
        case 'FamousCenNum':
            {
                //名将占有数
                $result = sql_fetch_one_cell("select count(1) as count from sys_city_hero where npcid>0 and uid>1000");
                if (! empty($result)) {
                    $ret['content']['FamousCenNum'] = $result;
                } else {
                    $ret['content']['FamousCenNum'] = 0;
                }
                break;
            }
        case 'FamousCityPNumber':
            {
                //占有名城玩家人数
                $result = sql_fetch_one_cell("select count(distinct uid) as count from sys_city where type>0 and uid>1000");
                if (! empty($result)) {
                    $ret['content']['FamousCityPNumber'] = $result;
                } else {
                    $ret['content']['FamousCityPNumber'] = 0;
                }
                break;
            }
        case 'FamousCenPNumber':
            {
                //占有名将玩家人数
                $result = sql_fetch_one_cell("select count(distinct uid) as count from sys_city_hero where npcid>0 and uid>1000");
                if (! empty($result)) {
                    $ret['content']['FamousCenPNumber'] = $result;
                } else {
                    $ret['content']['FamousCenPNumber'] = 0;
                }
                break;
            }
        case 'FamousData':
            {
                /*************名城名将数据*************/
                $FamousCityNum = sql_fetch_one_cell("select count(1) from sys_city where type>0 and uid>1000");
                $FamousCenNum = sql_fetch_one_cell("select count(1) from sys_city_hero where npcid>0 and uid>1000");
                $FamousCityPNumber = sql_fetch_one_cell("select count(distinct uid) from sys_city where type>0 and uid>1000");
                $FamousCenPNumber = sql_fetch_one_cell("select count(distinct uid) from sys_city_hero where npcid>0 and uid>1000");
                $result['FamousCityNum'] = $FamousCityNum;
                $result['FamousCenNum'] = $FamousCenNum;
                $result['FamousCityPNumber'] = $FamousCityPNumber;
                $result['FamousCenPNumber'] = $FamousCenPNumber;
                if (! empty($result)) {
                    $ret['content']['FamousData'] = $result;
                } else {
                    $ret['content']['FamousData'] = 0;
                }
                break;
            }
        case 'Battle1':
            {
                //黄巾战场数据
                $result = sql_fetch_one("select count(distinct battlefieldid) as OpenTimes,count(distinct uid) as JoinNum,sum(metal) as MedalNum from log_battle_honour where starttime>=$starttime and starttime<=$endtime and battleid=1001");
                if (! empty($result)) {
                    $ret['content']['Battle1'] = $result;
                } else {
                    $ret['content']['Battle1'] = 0;
                }
                break;
            }
        case 'Battle2':
            {
                //官渡之战战场数据
                $result = sql_fetch_one("select count(distinct battlefieldid) as OpenTimes,count(distinct uid) as JoinNum,sum(metal) as MedalNum from log_battle_honour where starttime>=$starttime and starttime<=$endtime and battleid=2001");
                if (! empty($result)) {
                    $ret['content']['Battle2'] = $result;
                } else {
                    $ret['content']['Battle2'] = 0;
                }
                break;
            }
        case 'Battle3':
            {
                //十常侍之乱战场数据
                $result = sql_fetch_one("select count(distinct battlefieldid) as OpenTimes,count(distinct uid) as JoinNum,sum(metal) as MedalNum from log_battle_honour where starttime>=$starttime and starttime<=$endtime and battleid=3001");
                if (! empty($result)) {
                    $ret['content']['Battle3'] = $result;
                } else {
                    $ret['content']['Battle3'] = 0;
                }
                break;
            }
        case 'Battle4':
            {
                //讨伐董卓战场数据
                $result = sql_fetch_one("select count(distinct battlefieldid) as OpenTimes,count(distinct uid) as JoinNum,sum(metal) as MedalNum from log_battle_honour where starttime>=$starttime and starttime<=$endtime and battleid=4001");
                if (! empty($result)) {
                    $ret['content']['Battle4'] = $result;
                } else {
                    $ret['content']['Battle4'] = 0;
                }
                break;
            }
        case 'Battle5':
            {
                //千里走单骑
                $result = sql_fetch_one("select count(distinct battlefieldid) as OpenTimes,count(distinct uid) as JoinNum,sum(metal) as MedalNum from log_battle_honour where starttime>=$starttime and starttime<=$endtime and battleid=5001");
                if (! empty($result)) {
                    $ret['content']['Battle5'] = $result;
                } else {
                    $ret['content']['Battle5'] = 0;
                }
                break;
            }
        case 'Battle6':
            {
                //凤仪亭
                $result = sql_fetch_one("select count(distinct battlefieldid) as OpenTimes,count(distinct uid) as JoinNum,sum(metal) as MedalNum from log_battle_honour where starttime>=$starttime and starttime<=$endtime and battleid=8001");
                if (! empty($result)) {
                    $ret['content']['Battle6'] = $result;
                } else {
                    $ret['content']['Battle6'] = 0;
                }
                break;
            }
        case 'BattleData':
            {
                //战场系统数据
                $result[1] = sql_fetch_one("select '黄巾之乱' as BattleName,count(distinct battlefieldid) as OpenTimes,count(distinct uid) as JoinNum,sum(metal) as MedalNum from log_battle_honour where starttime>=$starttime and starttime<=$endtime and battleid=1001");
                if (empty($result[1]))
                    $result[1] = 0;
                $result[2] = sql_fetch_one("select '官渡之战' as BattleName,count(distinct battlefieldid) as OpenTimes,count(distinct uid) as JoinNum,sum(metal) as MedalNum from log_battle_honour where starttime>=$starttime and starttime<=$endtime and battleid=2001");
                if (empty($result[2]))
                    $result[2] = 0;
                $result[3] = sql_fetch_one("select '十常侍之乱' as BattleName,count(distinct battlefieldid) as OpenTimes,count(distinct uid) as JoinNum,sum(metal) as MedalNum from log_battle_honour where starttime>=$starttime and starttime<=$endtime and battleid=3001");
                if (empty($result[3]))
                    $result[3] = 0;
                $result[4] = sql_fetch_one("select '讨伐董卓' as BattleName,count(distinct battlefieldid) as OpenTimes,count(distinct uid) as JoinNum,sum(metal) as MedalNum from log_battle_honour where starttime>=$starttime and starttime<=$endtime and battleid=4001");
                if (empty($result[4]))
                    $result[4] = 0;
                $result[5] = sql_fetch_one("select '千里走单骑' as BattleName,count(distinct battlefieldid) as OpenTimes,count(distinct uid) as JoinNum,sum(metal) as MedalNum from log_battle_honour where starttime>=$starttime and starttime<=$endtime and battleid=5001");
                if (empty($result[5]))
                    $result[5] = 0;
                $result[6] = sql_fetch_one("select '凤仪亭' as BattleName,count(distinct battlefieldid) as OpenTimes,count(distinct uid) as JoinNum,sum(metal) as MedalNum from log_battle_honour where starttime>=$starttime and starttime<=$endtime and battleid=8001");
                if (empty($result[6]))
                    $result[6] = 0;
                if (! empty($result)) {
                    $ret['content']['Battle2'] = $result;
                } else {
                    $ret['content']['Battle2'] = 0;
                }
                break;
            }
        case 'GeneralExperiencesNum':
            {
                //将领历练次数
                $result = sql_fetch_one_cell("select count(*) from log_action_count where aid=21");
                if (! empty($result)) {
                    $ret['content']['GeneralExperiencesNum'] = $result;
                } else {
                    $ret['content']['GeneralExperiencesNum'] = 0;
                }
                break;
            }
        case 'GeneralCarryYuanbao':
            {
                //将领历练携带元宝
                $result = sql_fetch_one_cell("select abs(sum(count)) from log_money where type=120 and time>'$starttime' and time <='$endtime'");
                if (! empty($result)) {
                    $ret['content']['GeneralCarryYuanbao'] = $result;
                } else {
                    $ret['content']['GeneralCarryYuanbao'] = 0;
                }
                break;
            }
        case 'GeneralConsYuanbao':
            {
                //将领历练消耗元宝
                $xd = sql_fetch_one_cell("select abs(sum(count)) from log_money where type=120 and time>'$starttime' and time <='$endtime'");
                $fh = sql_fetch_one_cell("select abs(sum(count)) from log_money where type=121 and time>'$starttime' and time <='$endtime'");
                $result = $xd - $fh;
                if (! empty($result) && $result > 0) {
                    $ret['content']['GeneralConsYuanbao'] = $result;
                } else {
                    $ret['content']['GeneralConsYuanbao'] = 0;
                }
                break;
            }
        case 'GeneralData':
            {
                //武将历练系统数据
                $GeneralExperiencesNum = sql_fetch_one_cell("select count(*) from log_action_count where aid=21");
                if (empty($GeneralExperiencesNum))
                    $GeneralExperiencesNum = 0;
                $GeneralCarryYuanbao = sql_fetch_one_cell("select abs(sum(count)) from log_money where type=120 and time>'$starttime' and time <='$endtime'");
                if (empty($GeneralCarryYuanbao))
                    $GeneralCarryYuanbao = 0;
                $ConsYuanbao = sql_fetch_one_cell("select abs(sum(count)) from log_money where type=121 and time>'$starttime' and time <='$endtime'");
                $GeneralConsYuanbao = $GeneralCarryYuanbao - $ConsYuanbao;
                if (empty($GeneralConsYuanbao) || $GeneralConsYuanbao < 0)
                    $GeneralConsYuanbao = 0;
                $result['GeneralExperiencesNum'] = $GeneralExperiencesNum;
                $result['GeneralCarryYuanbao'] = $GeneralCarryYuanbao;
                $result['GeneralConsYuanbao'] = $GeneralConsYuanbao;
                if (! empty($result)) {
                    $ret['content']['GeneralData'] = $result;
                } else {
                    $ret['content']['GeneralData'] = 0;
                }
                break;
            }
        case 'EquipActiveNum':
            {
                //装备激活次数
                $result = sql_fetch_one_cell("select count(*) from log_action_count where aid=14");
                if (! empty($result)) {
                    $ret['content']['EquipActiveNum'] = $result;
                } else {
                    $ret['content']['EquipActiveNum'] = 0;
                }
                break;
            }
        case 'EquipEnhancedNum':
            {
                //装备强化次数
                $result = sql_fetch_one_cell("select count(*) from log_action_count where aid=15");
                if (! empty($result)) {
                    $ret['content']['EquipEnhancedNum'] = $result;
                } else {
                    $ret['content']['EquipEnhancedNum'] = 0;
                }
                break;
            }
        case 'EquipDismantingNum':
            {
                //装备拆解次数
                $result = sql_fetch_one_cell("select count(*) from log_action_count where aid=24");
                if (! empty($result)) {
                    $ret['content']['EquipDismantingNum'] = $result;
                } else {
                    $ret['content']['EquipDismantingNum'] = 0;
                }
                break;
            }
        case 'MaterialUpgradeNum':
            {
                //材料升级次数
                $result = sql_fetch_one_cell("select count(*) from log_action_count where aid=23");
                if (! empty($result)) {
                    $ret['content']['MaterialUpgradeNum'] = $result;
                } else {
                    $ret['content']['MaterialUpgradeNum'] = 0;
                }
                break;
            }
        case 'MountsTamedNum':
            {
                //坐骑驯化次数
                $result = sql_fetch_one_cell("select count(*) from log_action_count where aid=25");
                if (! empty($result)) {
                    $ret['content']['MountsTamedNum'] = $result;
                } else {
                    $ret['content']['MountsTamedNum'] = 0;
                }
                break;
            }
        case 'MountsEnhancedNum':
            {
                //坐骑强化次数
                $result = sql_fetch_one_cell("select count(*) from log_action_count where aid=16");
                if (! empty($result)) {
                    $ret['content']['MountsEnhancedNum'] = $result;
                } else {
                    $ret['content']['MountsEnhancedNum'] = 0;
                }
                break;
            }
        case 'EquipData':
            {
                //装备强化系统数据
                $EquipActiveNum = sql_fetch_one_cell("select count(*) from log_action_count where aid=14");
                if (empty($EquipActiveNum))
                    $EquipActiveNum = 0;
                $EquipEnhancedNum = sql_fetch_one_cell("select count(*) from log_action_count where aid=15");
                if (empty($EquipEnhancedNum))
                    $EquipEnhancedNum = 0;
                $MountsEnhancedNum = sql_fetch_one_cell("select count(*) from log_action_count where aid=16");
                if (empty($MountsEnhancedNum))
                    $MountsEnhancedNum = 0;
                $EquipDismantingNum = sql_fetch_one_cell("select count(*) from log_action_count where aid=24");
                if (empty($EquipDismantingNum))
                    $EquipDismantingNum = 0;
                $MaterialUpgradeNum = sql_fetch_one_cell("select count(*) from log_action_count where aid=23");
                if (empty($MaterialUpgradeNum))
                    $MaterialUpgradeNum = 0;
                $MountsTamedNum = sql_fetch_one_cell("select count(*) from log_action_count where aid=25");
                if (empty($MountsTamedNum))
                    $MountsTamedNum = 0;
                $MountsEnhancedNum = sql_fetch_one_cell("select count(*) from log_action_count where aid=16");
                if (empty($MountsEnhancedNum))
                    $MountsEnhancedNum = 0;
                $result['EquipActiveNum'] = $EquipActiveNum;
                $result['EquipEnhancedNum'] = $EquipEnhancedNum;
                $result['MountsEnhancedNum'] = $MountsEnhancedNum;
                $result['EquipDismantingNum'] = $EquipDismantingNum;
                $result['MaterialUpgradeNum'] = $MaterialUpgradeNum;
                $result['MountsTamedNum'] = $MountsTamedNum;
                if (! empty($result)) {
                    $ret['content']['EquipData'] = $result;
                } else {
                    $ret['content']['EquipData'] = 0;
                }
                break;
            }
        case 'BuildingBuildNum':
            {
                //建建筑物次数
                $result = sql_fetch_one_cell("select count(*) from log_user_action where aid=26 and time>'$starttime' and time <= '$endtime'");
                if (! empty($result)) {
                    $ret['content']['BuildingBuildNum'] = $result;
                } else {
                    $ret['content']['BuildingBuildNum'] = 0;
                }
                break;
            }
        case 'BuildingUpgradeNum':
            {
                //建筑物升级次数
                $result = sql_fetch_one_cell("select count(*) from log_user_action where aid=27 and time>'$starttime' and time <= '$endtime'");
                if (! empty($result)) {
                    $ret['content']['BuildingUpgradeNum'] = $result;
                } else {
                    $ret['content']['BuildingUpgradeNum'] = 0;
                }
                break;
            }
        case 'TechnologyUpgradeNum':
            {
                //科技研究次数
                $result = sql_fetch_one_cell("select count(*) from log_user_action where aid=22 and time>'$starttime' and time <= '$endtime'");
                if (! empty($result)) {
                    $ret['content']['TechnologyUpgradeNum'] = $result;
                } else {
                    $ret['content']['TechnologyUpgradeNum'] = 0;
                }
                break;
            }
        case 'PlayerToNpcDealNum':
            {
                //玩家与商人的交易次数
                $result = sql_fetch_one_cell("select count(*) from log_user_action where aid=19 and time>'$starttime' and time <= '$endtime'");
                if (! empty($result)) {
                    $ret['content']['PlayerToNpcDealNum'] = $result;
                } else {
                    $ret['content']['PlayerToNpcDealNum'] = 0;
                }
                break;
            }
        case 'PlayerToPlayerDealNum':
            {
                //玩家与玩家的交易次数
                $result = sql_fetch_one_cell("select count(*) from log_user_action where aid=20 and time>'$starttime' and time <= '$endtime'");
                if (! empty($result)) {
                    $ret['content']['PlayerToPlayerDealNum'] = $result;
                } else {
                    $ret['content']['PlayerToPlayerDealNum'] = 0;
                }
                break;
            }
        case 'EconomyData':
            {
                //经济系统数据
                $result['BuildingBuildNum'] = sql_fetch_one_cell("select count(*) from log_user_action where aid=26 and time>'$starttime' and time <= '$endtime'");
                if (empty($BuildingBuildNum))
                    $BuildingBuildNum = 0;
                $result['BuildingUpgradeNum'] = sql_fetch_one_cell("select count(*) from log_user_action where aid=27 and time>'$starttime' and time <= '$endtime'");
                
                if (empty($BuildingUpgradeNum))
                    $BuildingUpgradeNum = 0;
                $result['TechnologyUpgradeNum'] = sql_fetch_one_cell("select count(*) from log_user_action where aid=22 and time>'$starttime' and time <= '$endtime'");
                
                if (empty($TechnologyUpgradeNum))
                    $TechnologyUpgradeNum = 0;
                $result['PlayerToNpcDealNum'] = sql_fetch_one_cell("select count(*) from log_user_action where aid=19 and time>'$starttime' and time <= '$endtime'");
                
                if (empty($PlayerToNpcDealNum))
                    $PlayerToNpcDealNum = 0;
                $result['PlayerToPlayerDealNum'] = sql_fetch_one_cell("select count(*) from log_user_action where aid=20 and time>'$starttime' and time <= '$endtime'");
                
                if (empty($PlayerToPlayerDealNum))
                    $PlayerToPlayerDealNum = 0;
                if (! empty($result)) {
                    $ret['content']['EconomyData'] = $result;
                } else {
                    $ret['content']['EconomyData'] = 0;
                }
                break;
            }
    }
} catch (exception $e) {
    $ret = array();
    $ret['error'] = $e->getMessage();
}