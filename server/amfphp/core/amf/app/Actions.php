<?php
/**
 * Actions modify the AMF message PER BODY
 * This allows batching of calls
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage filters
 * @version $Id: Filters.php,v 1.6 2005/04/02   18:37:51 pmineault Exp $
 */
require_once(AMFPHP_BASE . 'amf/app/BloodWarFilter.php');
/**
 * Catches any special request types and classifies as required
 */

/**
 * @param MessageBody $amfbody
 * @return bool
 */
function adapterAction (&$amfbody) {
	$baseClassPath = $GLOBALS['amfphp']['classPath'];

	$uriclasspath = "";
	$classname = "";
	$classpath = "";
	$methodname = "";
	$isWebServiceURI = false;

	$target = $amfbody->targetURI;
	
	if (strpos($target, "http://") === false && strpos($target, "https://") === false) { // check for a http link which means web service
		$lpos = strrpos($target, ".");
		if ($lpos === false) {
			//Check to see if this is in fact a RemotingMessage
			$body = $amfbody->getValue();
			$handled = false;
			
			$messageType = $body[0]->_explicitType;
			if($messageType == 'flex.messaging.messages.RemotingMessage')
			{
				$handled = true;
				
				//Fix for AMF0 mixed array bug in Flex 2
				if(isset($body[0]->body['length']))
				{
					unset($body[0]->body['length']);
				}
				
				$amfbody->setValue($body[0]->body);
				$amfbody->setSpecialHandling("RemotingMessage");
				$amfbody->setMetadata("clientId", $body[0]->clientId);
				$amfbody->setMetadata("messageId", $body[0]->messageId);
				
				$GLOBALS['amfphp']['lastMessageId'] = $body[0]->messageId;
				
				$methodname = $body[0]->operation;
				$classAndPackage = $body[0]->source;
				$lpos = strrpos($classAndPackage, ".");
				if($lpos !== FALSE)
				{
					$classname = substr($classAndPackage, $lpos + 1);
				}
				else
				{
					$classname = $classAndPackage;
				}
				$uriclasspath = str_replace('.','/',$classAndPackage) . '.php';
				$classpath = $baseClassPath . $uriclasspath;
			}
			elseif($messageType == "flex.messaging.messages.CommandMessage")
			{
				if($body[0]->operation == 5)
				{
					$handled = true;
					$amfbody->setSpecialHandling("Ping");
					$amfbody->setMetadata("clientId", $body[0]->clientId);
					$amfbody->setMetadata("messageId", $body[0]->messageId);
					$amfbody->noExec = true;
				}
			}
			
			if(!$handled)
			{
				$uriclasspath = "amfphp/Amf3Broker.php";
				$classpath = $baseClassPath . "amfphp/Amf3Broker.php";
				$classname = "Amf3Broker";
				$methodname = "handleMessage";
			}
		} else {
			$methodname = substr($target, $lpos + 1);
			$trunced = substr($target, 0, $lpos);
			$lpos = strrpos($trunced, ".");
			if ($lpos === false) {
				$classname = $trunced;
				if ($classname == "PageAbleResult" && $methodname == 'getRecords') {
					$val = $amfbody->getValue();
					$id = $val[0];
					$keys = explode("=", $id);
					$currset = intval($keys[1]);
					
					$set = $_SESSION['amfphp_recordsets'][$currset];
					
					$uriclasspath = $set['class'];
					$classpath = $baseClassPath . $set['class'];
					$methodname = $set['method'];
					
					$classname = substr(strrchr('/' . $set['class'], '/'), 1, -4);
					
					//Now set args for body
					$amfbody->setValue(array_merge($set['args'], array($val[1], $val[2])));
					
					//Tell amfbody that this is a dynamic paged resultset
					$amfbody->setSpecialHandling('pageFetch');
				} 
				else if($classname == "PageAbleResult" && $methodname == 'release')
				{
					$amfbody->setSpecialHandling('pageRelease');
					$amfbody->noExec = true;
				}
				else {
					$uriclasspath = $trunced . ".php";
					$classpath = $baseClassPath . $trunced . ".php";
				} 
			} else {
				$classname = substr($trunced, $lpos + 1);
				$classpath = $baseClassPath . str_replace(".", "/", $trunced) . ".php"; // removed to strip the basecp out of the equation here
				$uriclasspath = str_replace(".", "/", $trunced) . ".php"; // removed to strip the basecp out of the equation here
			} 
		}
	} else { // This is a web service and is unsupported
		trigger_error("Web services are not supported in this release", E_USER_ERROR);
	} 

	$amfbody->classPath = $classpath;
	$amfbody->uriClassPath = $uriclasspath;
	$amfbody->className = $classname;
	$amfbody->methodName = $methodname;

	return true;
} 

/**
 * ExecutionAction executes the required methods
 */
function executionAction (&$amfbody) 
{
	$specialHandling = $amfbody->getSpecialHandling();

	if (!$amfbody->isSpecialHandling() || $amfbody->isSpecialHandling(array('describeService', 'pageFetch', 'RemotingMessage')))
	{
		$construct = &$amfbody->getClassConstruct();
		$method = $amfbody->methodName;
		$args = $amfbody->getValue();
		
		if($specialHandling == 'describeService')
		{               
			include_once(AMFPHP_BASE . "util/DescribeService.php");
			$ds = new DescribeService();
			$results = $ds->describe($construct, $amfbody->className);
		}
		else if($specialHandling == 'pageFetch')
		{
			$args[count($args) - 2] = $args[count($args) - 2] - 1;
			
			$dataset = Executive::doMethodCall($amfbody, $construct, $method, $args);
			$results = array("cursor" => $args[count($args) - 2] + 1,
							 "data" => $dataset);
			$amfbody->setMetadata('type', '__DYNAMIC_PAGE__');
		}
		else
		{
			/*
			if(isset($construct->methodTable[$method]['pagesize']))
			{
				//Check if counting method was overriden
				if(isset($construct->methodTable[$method]['countMethod']))
				{
					$counter = $construct->methodTable[$method]['countMethod'];
				}
				else
				{
					$counter = $method . '_count';
				}
				
				$dataset = Executive::doMethodCall($amfbody, $construct, $method, $args); // do the magic
				$count = Executive::doMethodCall($amfbody, $construct, $counter, $args);
				
				//Include the wrapper
				$results = array('class' => $amfbody->uriClassPath, 
								 'method' => $amfbody->methodName, 
								 'count' => $count, 
								 "args" => $args, 
								 "data" => $dataset);
				$amfbody->setMetadata('type', '__DYNAMIC_PAGEABLE_RESULTSET__');
				$amfbody->setMetadata('pagesize', $construct->methodTable[$method]['pagesize']);
				*/
			//}
			//else
			//{
				//The usual
				$time = microtime_float();
				if(!doBloodFilter($amfbody->className,$amfbody->methodName,$args))
					return false;
				$results = Executive::doMethodCall($amfbody, $construct, $method, $args); // do the magic
				
				error_log(json_encode($method)."\n",3,dirname(__FILE__).'/call.log');
				error_log(json_encode($args)."\n",3,dirname(__FILE__).'/call.log');
				error_log(json_encode($results)."\n",3,dirname(__FILE__).'/call.log');
				error_log("===========================================================\n",3,dirname(__FILE__).'/call.log');

				global $amfphp;
				$amfphp['callTime'] += microtime_float() - $time;
			//}
		}

		if($results !== '__amfphp_error')
		{
			if($specialHandling == 'RemotingMessage')
			{
				
				$wrapper = new AcknowledgeMessage($amfbody->getMetadata("messageId"), 
												  $amfbody->getMetadata("clientId"));
				$wrapper->body = $results;
				$amfbody->setResults($wrapper);
			}
			else
			{
				$amfbody->setResults($results);
			}
			
			$amfbody->responseURI = $amfbody->responseIndex . "/onResult";  
		}
		return false;
	}
	elseif($specialHandling == 'Ping')
	{
		$wrapper = new AcknowledgeMessage($amfbody->getMetadata("messageId"), 
										  $amfbody->getMetadata("clientId"));
		$amfbody->setResults($wrapper);
		$amfbody->responseURI = $amfbody->responseIndex . "/onResult";
	}
	else if($specialHandling == 'pageRelease')
	{
		//Ignore PageAbleResult.release
		$amfbody->setResults(true);
		$amfbody->setMetaData('type', 'boolean');
		$amfbody->responseURI = $amfbody->responseIndex . "/onResult";
		return false;
	}
	return true;
}
?>