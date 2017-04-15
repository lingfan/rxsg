<?php

// global exception handler

function reportExceptions ($code, $descr, $filename, $line)
{
	// obey error_level set by system/user
	if (!($code & error_reporting())) {
		return;
	}


	// init a new error info object
	$error = new MessageException($code, $descr, $filename, $line, "AMFPHP_RUNTIME_ERROR");
	
	// add the error object to the body of the AMFObject
	$amfbody = new MessageBody(NULL, $GLOBALS['amfphp']['lastMethodCall']);
	MessageException::throwException($amfbody, $error);
	//$amfbody->setResults($error);
	
	if($GLOBALS['amfphp']['encoding'] == 'amf0' || $GLOBALS['amfphp']['encoding'] == 'amf3')
	{
		// build a new AMFObject
		$amfout = new AMFObject("");
	
		$amfout->addBody($amfbody);  
		
		// Add the trace headers we have so far while we're at it
		debugFilter($amfout);
		
		// create a new serializer
		$serializer = new AMFSerializer();
		
		// serialize the data
		$data = $serializer->serialize($amfout);
	
		// send the correct header
		header('Content-type: application/x-amf');
		// flush the amf data to the client.
		print($data);
		
		// kill the system after we find a single error
		exit;
	}
	else
	{
		serializationAction($amfbody);
		print($amfbody->getResults());
		exit;
	}

}

set_error_handler("reportExceptions");

?>