<?php

interface AgentService {
	function addStartWarEvent($enemyname) ;
	function addGoodsEvent($goodsname, $goodsvalue) ;
	function addHeroEvent($heroname);
	function addNobilityEvent($nobilityname);
	function addOfficePosEvent($officepos) ;
	function addCreateCityEvent($name) ;
}

?>