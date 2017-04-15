<?php
/**
 * The Executive class is responsible for executing the remote service method and returning it's value.
 *
 * Currently the executive class is a complicated chain of filtering events testing for various cases and
 * handling them.  Future versions of this class will probably be broken up into many helper classes which will
 * use a delegation or chaining pattern to make adding new exceptions or handlers more modular.  This will
 * become even more important if developers need to make their own custom header handlers.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage app
 * @author Musicman original design
 * @author Justin Watkins Gateway architecture, class structure, datatype io additions
 * @author John Cowen Datatype io additions, class structure
 * @author Klaasjan Tukker Modifications, check routines
 * @version $Id: php5Executive.php,v 1.3 2005/07/05 07:40:50 pmineault Exp $
 */

class Executive {
	/**
	 * The built instance of the service class
	 *
	 * @access private
	 * @var object
	 */
	var $_classConstruct;

	/**
	 * The method name to execute
	 *
	 * @access private
	 * @var string
	 */
	var $_methodname;

	/**
	 * The arguments to pass to the executed method
	 *
	 * @access private
	 * @var mixed
	 */
	var $_arguments;

	function __construct() {
	}

	/**
	 * The main method of the executive class.
	 *
	 * @param array $a Arguments to pass to the method
	 * @return mixed The results from the method operation
	 */
	static function doMethodCall(&$bodyObj, &$object, $method, $args)
	{
		try
		{
			$output = Executive::deferredMethodCall($bodyObj, $object, $method, $args);
		}
		catch(Exception $fault)
		{
            var_dump(0,$fault->getMessage());
            exit;
			$ex = new MessageException(E_USER_ERROR, $fault->getMessage(), $fault->getFile(), $fault->getLine(), 'AMFPHP_INCLUDE_ERROR');

			MessageException::throwException($bodyObj, $ex);
			$output = '__amfphp_error';
		}
		return $output;
	}

	/**
	 * Builds a class using a class name
	 * If there is a failure, catch the error and return to caller
	 */
	static function buildClass(&$bodyObj, $className)
	{
		global $amfphp;
		if(isset($amfphp['classInstances'][$className]))
		{
			return $amfphp['classInstances'][$className];
		}

		try
		{
			$construct = new $className($className);
			$amfphp['classInstances'][$className] = $construct;
		}
		catch(Exception $fault)
		{
			var_dump(1,$fault->getMessage());
			exit;
			//When constructing a class, getLine and getFile don't refer to the appropriate thing,
			//hence this hack
			$ex = new MessageException(E_USER_ERROR, $fault->getMessage(), $bodyObj->classPath, 'Undetermined line  in constructor', 'AMFPHP_BUILD_ERROR');
			MessageException::throwException($bodyObj, $ex);
			$construct = '__amfphp_error';
		}

		return $construct;
	}

	/**
	 * We are using a deferred metho call instead of directly
	 * calling the method because of a strange bug with throwing exceptions within
	 * an error handler which seems to break the convential rule for working with exceptions
	 * Nesting function calls seems to solve the problem, but not nesting try...catch
	 */
	static function deferredMethodCall(&$bodyObj, &$object, $method, $args)
	{
		try
		{
			if($object === NULL)
			{
				$output = call_user_func_array ($method, $args);
			}
			else
			{
				$output = call_user_func_array (array(&$object, $method), $args);
			}
		}
		catch(Exception $fault)
		{
			var_dump(2,$fault->getMessage());
			exit;
			$ex = new MessageException(E_USER_ERROR, $fault->getMessage(), $fault->getFile(), $fault->getLine(), 'AMFPHP_INCLUDE_ERROR');
			$output = '__amfphp_error';
			MessageException::throwException($bodyObj, $ex);

		}

		return $output;
	}

	/**
	 * Include a class
	 * If there is an error, catch and return to caller
	 */
	static function includeClass(&$bodyObj, $location)
	{
		$included = false;
		try
		{
			require_once($location);
			$included = true;
		}
		catch(Exception $fault)
		{
			var_dump(3,$fault->getMessage(), $fault->getFile(), $fault->getLine());

			exit;
			$included = false;
			$ex = new MessageException(E_USER_ERROR, $fault->getMessage(), $fault->getFile(), $fault->getLine(), 'AMFPHP_INCLUDE_ERROR');

			MessageException::throwException($bodyObj, $ex);
		}
		return $included;
	}
}
?>
