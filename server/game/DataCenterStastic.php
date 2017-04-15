<?php
require_once ("DataCenter.php");
require_once ("interface.php");

$currentPlayers=sql_fetch_one_cell("select count(*) from sys_online where lastupdate>unix_timestamp()-600");
ServerPlayer($currentPlayers);

