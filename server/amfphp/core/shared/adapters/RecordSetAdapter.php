<?php
/**
 * RecordSetAdapter is the superclass of all database adapter implementations.
 * 
 * To keep the apadters encapsulated, the  getter methods have been added to
 * this superclass instead of direct property access.  This superclass is really "abstract"
 * even though abstraction isn't supported until PHP5.  This class must be extended
 * and an implementation to set the 3 properties needs to be defined.
 * 
 * The implementation for setting the 3 properties can be defined either in the constructor
 * or by overwriting the getter methods.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage adapters
 * @version $Id: RecordSetAdapter.php,v 1.1 2005/07/05 07:56:29 pmineault Exp $
 */

class RecordSetAdapter {
	var $rows = array();
	var $columns = array();

	/**
	 * The number of rows in this recordset
	 * 
	 * @access private 
	 * @var int 
	 */
	var $numRows = -1;

	/**
	 * Dummy constructor function.
	 * 
	 * @param resource $d The result resource
	 */

	function RecordSetAdapter ($d) {
		$this->_resultResource = $d;
		$this->_charsetHandler = new CharsetHandler('sqltophp');
		$this->_directCharsetHandler = new CharsetHandler('sqltoflash');
		$this->isBigEndian = AMFPHP_BIG_ENDIAN;
	} 

	/**
	 * getter for the number of rows
	 * 
	 * @return int The number of rows
	 */
	function getRowCount () {
		if($this->numRows == -1)
		{
			$this->numRows = count($this->rows);
		}
		return $this->numRows;
	} 
	
	function getID() {
		return md5(microtime());
	}
} 

?>