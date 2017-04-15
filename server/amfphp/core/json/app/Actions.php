<?php

function deserializationAction(&$body)
{
	$args = $body->getValue();
	$target = $args[0];
	
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
	
	//Now deserialize the arguments
	array_shift($args);
	
	$actualArgs = array();
	
	foreach($args as $key => $value)
	{
		//Look at the value to see if it is JSON-encoded
		$value = urldecode($value);
		if($value != "")
		{
			if($value[0] != '[' && $value[0] != '{' && $value != "null" && $value != "false" && $value != "true")
			{
				//check to see if it is a number
				$char1 = ord($value[0]);
				if($char1 >= 0x30 && $char1 <= 0x39)
				{
					//Then this is a number
					$value = json_decode($value, true);
				} //Else leave value as is
			}
			else
			{
				$value = json_decode($value, true);
			}
		}
		$actualArgs[] = $value;
	}
	
	$body->setValue($actualArgs);
}

function executionAction(& $body)
{
	$classConstruct = &$body->getClassConstruct();
	$methodName = $body->methodName;
	$args = $body->getValue();
	
	$output = Executive::doMethodCall($body, &$classConstruct, $methodName, $args);
	
	if($output !== "__amfphp_error")
	{
		$body->setResults($output);
	}
}


function serializationAction(& $body)
{
	//Take the raw response
	$rawResponse = & $body->getResults();
	
	adapterMap($rawResponse);
	
	//Now serialize it
	$encodedResponse = json_encode($rawResponse);
	
	if(count(NetDebug::getTraceStack()) > 0)
	{
		$trace = "/*" . implode("\n", NetDebug::getTraceStack()) . "*/";
		$encodedResponse = $trace . "\n" . $encodedResponse;
	}
	
	$body->setResults($encodedResponse);
}

if(!function_exists("json_encode"))
{
	include_once(AMFPHP_BASE . "shared/util/JSON.php");
	
	function json_encode($val)
	{
		$json = new Services_JSON();
		return $json->encode($val);
	}
	
	function json_decode($val, $asAssoc = FALSE)
	{
		if($asAssoc)
		{
			$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		}
		else
		{
			$json = new Services_JSON();
		}
		return $json->decode($val);
	}
}
?>