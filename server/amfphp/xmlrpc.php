<?php

	/**
	 * XML-RPC server
	 */
	include("globals.php");

	include "core/xmlrpc/app/Gateway.php";
	
	$gateway = new Gateway();
	
	$gateway->setBaseClassPath($servicesPath);
	
	$gateway->service();
?>