<?php

function deserializationAction(&$body)
{
	$data = $body->getValue();
	
	//Get the method that is being called
	$description = xmlrpc_parse_method_descriptions($data);
	$target = $description['methodName'];
	
	$baseClassPath = $GLOBALS['amfphp']['classPath'];
	
	$lpos = strrpos($target, '.');
	
	$methodname = substr($target, $lpos + 1);
	$trunced = substr($target, 0, $lpos);
	$lpos = strrpos($trunced, ".");
	if ($lpos === false) {
		$classname = $trunced;
		$uriclasspath = $trunced . ".php";
		$classpath = $baseClassPath . $trunced . ".php";
	} else {
		$classname = substr($trunced, $lpos + 1);
		$classpath = $baseClassPath . str_replace(".", "/", $trunced) . ".php"; // removed to strip the basecp out of the equation here
		$uriclasspath = str_replace(".", "/", $trunced) . ".php"; // removed to strip the basecp out of the equation here
	} 
	
	$body->methodName = $methodname;
	$body->className = $classname;
	$body->classPath = $classpath;
	$body->uriClassPath = $uriclasspath;
	$body->packageClassMethodName = $description['methodName'];
}

function executionAction(& $body)
{
	$classConstruct = $body->getClassConstruct();
	$methodName = $body->methodName;
	$className = $body->className;
	
	$xmlrpc_server = xmlrpc_server_create();
	
	$lambdaFunc = 'return adapterMap(call_user_func_array (array(&$userData[0], $userData[1]), $args));';
	$func = create_function('$a,$args,$userData', $lambdaFunc);

	xmlrpc_server_register_method($xmlrpc_server,
		$body->packageClassMethodName,
		$func);
	
	$request_xml = $body->getValue();
	$args = array($xmlrpc_server, $request_xml, array(&$classConstruct, $methodName));
	$nullObj = NULL;
	$response = Executive::doMethodCall($body, $nullObj, 'xmlrpc_server_call_method', $args);
	//$response = xmlrpc_server_call_method();
	
	if($response !== "__amfphp_error")
	{
		$body->setResults($response);
	}
	else
	{
		return false;
	}
}

/**
 * Debug action
 */
function debugAction(& $body)
{
	if(count(NetDebug::getTraceStack()) != 0)
	{
		$previousResults = $body->getResults();
		$debugInfo = NetDebug::getTraceStack();
		$debugString = "<!-- " . implode("\n", $debugInfo) . "-->";
		$body->setResults($debugString . "\n" . $previousResults);
	}
}

/**
 * This won't ever be called unless there is an error
 */
function serializationAction(& $body)
{
	$request_xml = $body->getValue();
	$toSerialize = $body->getResults();
	
	$lambdaFunc = 'return $userData;';
	$func = create_function('$a,$b,$userData', $lambdaFunc);
	
	$xmlrpc_server = xmlrpc_server_create();
	
	$request_xml = $body->getValue();
	
	xmlrpc_server_register_method($xmlrpc_server,
		$body->packageClassMethodName,
		$func);

	$response = xmlrpc_server_call_method($xmlrpc_server, $request_xml, $toSerialize);
	
	$body->setResults($response);
}

?>