<?php
/**
 * This file contains actions which are used by all gateways
 */
include_once(AMFPHP_BASE . 'shared/util/Authenticate.php');
include_once(AMFPHP_BASE . 'shared/util/NetDebug.php');
include_once(AMFPHP_BASE . 'shared/util/Headers.php');
include_once(AMFPHP_BASE . 'shared/util/CharsetHandler.php');
/**
 * Class loader action loads the class from which we will get the remote method
 */
function classLoaderAction (&$amfbody) {
	 
	if(!$amfbody->noExec)
	{ 
		// change to the gateway.php script directory
		// now change to the directory of the classpath.  Possible relative to gateway.php
		$dirname = dirname($amfbody->classPath);
		if(is_dir($dirname))
		{
			chdir($dirname);
		}
		else
		{
			$ex = new MessageException(E_USER_ERROR, "The classpath folder {" . $amfbody->classPath . "} does not exist. You probably misplaced your service." , __FILE__, __LINE__, "AMFPHP_CLASSPATH_NOT_FOUND");
			MessageException::throwException($amfbody, $ex);
			return false;
		}

		$fileExists = @file_exists(basename($amfbody->classPath)); // see if the file exists

		if(!$fileExists)
		{
				$ex = new MessageException(E_USER_ERROR, "The class {" . $amfbody->className . "} could not be found under the class path {" . $amfbody->classPath . "}" , __FILE__, __LINE__, "AMFPHP_FILE_NOT_FOUND");
				MessageException::throwException($amfbody, $ex);
				return false;
		}
		
		global $amfphp;
		$time = microtime_float();
		$fileIncluded = Executive::includeClass($amfbody, "./" . basename($amfbody->classPath));


		$amfphp['includeTime'] += microtime_float() - $time;

		if (!$fileIncluded) 
		{
			$ex = new MessageException(E_USER_ERROR, "The class file {" . $amfbody->className . "} exists but could not be included. The file may have syntax errors, or includes at the top of the file cannot be resolved.", __FILE__, __LINE__, "AMFPHP_FILE_NOT_INCLUDED");
			MessageException::throwException($amfbody, $ex);
			return false;
		}
		
		if (!class_exists($amfbody->className))
		{ // Just make sure the class name is the same as the file name
				
				$ex = new MessageException(E_USER_ERROR, "The file {" . $amfbody->className . ".php} exists and was included correctly but a class by that name could not be found in that file. Perhaps the class is misnamed.", __FILE__, __LINE__, "AMFPHP_CLASS_NOT_FOUND");
				MessageException::throwException($amfbody, $ex);
				return false;
		}

		//Let executive handle building the class
		//The executive can handle making exceptions and all that, that's why
		$classConstruct = Executive::buildClass($amfbody, $amfbody->className);

		if($classConstruct !== '__amfphp_error')
		{
			$amfbody->setClassConstruct($classConstruct);
		}
		else
		{
			return false;
		}
	}
	return true;
} 

/**
 * MetaDataAction loads the required info from the methodTable
 */
function securityAction (&$amfbody) {
	if(!$amfbody->noExec)
	{
		$classConstruct = &$amfbody->getClassConstruct();
		$methodName = $amfbody->methodName;
		$className = $amfbody->className;
		
		//Check if method exists
		if (!method_exists($classConstruct, $methodName)) { // check to see if the method exists
			$ex = new MessageException(E_USER_ERROR, "The method  {" . $methodName . "} does not exist in class {" . $className . "}.", __FILE__, __LINE__, "AMFPHP_INEXISTANT_METHOD");
			MessageException::throwException($amfbody, $ex);
			return false;
		} 
		
		//Check if method is private (PHP4)
		if (strpos($methodName, '_') === 0) { // check to see if the method exists
			$ex = new MessageException(E_USER_ERROR, "The method  {" . $methodName . "} starts with an underscore and is therefore considered private, so it cannot be remotely called.", __FILE__, __LINE__, "AMFPHP_PRIVATE_METHOD");
			MessageException::throwException($amfbody, $ex);
			return false;
		} 
		
		//Check to see if method is private or protected (PHP5)
		/*
		if(class_exists('ReflectionMethod'))
		{
			$method = new ReflectionMethod($className, $methodName);
			if(!$method->isPublic())
			{
				$ex = new MessageException(E_USER_ERROR, "The method  {" . $methodName . "} in {" . $className . "} is not public and therefore cannot be called.", __FILE__, __LINE__, "AMFPHP_PRIVATE_METHOD");
				MessageException::throwException($amfbody, $ex);
				return false;
			}
		}
		*/
		$classConstruct = &$amfbody->getClassConstruct();
		$methodName = $amfbody->methodName;
		$className = $amfbody->className;
		
		if (method_exists($classConstruct, "beforeFilter")) {			
			//Pass throught the executive
			$allow = Executive::doMethodCall($amfbody, 
										$classConstruct, 
										'beforeFilter', 
										array($methodName));
			if ($allow === '__amfphp_error' || $allow === false) {
				$ex = new MessageException(E_USER_ERROR, "Method access blocked by beforeFilter in " . $className . " class", __FILE__, __LINE__, "AMFPHP_AUTHENTICATE_ERROR");
				MessageException::throwException($amfbody, $ex);
				return false;
			} 
		}
	}
	return true;
}

function adapterMap(&$results)
{
	if(is_array($results))
	{
		array_walk($results, 'adapterMap');
	}
	elseif(is_object($results))
	{
		$className = strtolower(get_class($results));
		if(array_key_exists($className, $GLOBALS['amfphp']['adapterMappings']))
		{
			$type = $GLOBALS['amfphp']['adapterMappings'][$className];
			$results = mapRecordSet($results, $type);
		}
		else
		{
			$vars = get_object_vars($results);
			
			array_walk($vars, 'adapterMap');
			
			foreach($vars as $key => $value)
			{
				$results->$key = $value;
			}
		}
	}
	elseif(is_resource($results))
	{
		$type = get_resource_type($results);
		$str = explode(' ', $type);
		if(in_array($str[1], array("result", 'resultset', "recordset", "statement")))
		{
			$results = mapRecordSet($results, $str[0]);
		}
		else
		{
			$results = false;
		}
	}
	return $results;
}

function mapRecordSet($result, $type)
{
	$classname = $type . "Adapter"; // full class name
	$includeFile = include_once(AMFPHP_BASE . "shared/adapters/" . $classname . ".php"); // try to load the recordset library from the sql folder
	if (!$includeFile) {
		trigger_error("The recordset filter class " . $classname . " was not found");
	} 
	$recordSet = new $classname($result); // returns formatted recordset
	
	return array("columns" => $recordSet->columns, "rows" => $recordSet->rows);
}
?>