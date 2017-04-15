<?php
/**
 * This Adapter translates the specific Database type links to the data and pulls the data into very
 * specific local variables to later be retrieved by the gateway and returned to the client.
 *
 * Adapted from Adam Schroeder's implementation on Flash-db.com boards
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage adapters
 * @version $Id: sqliteAdapter.php,v 1.1 2005/07/05 07:56:29 pmineault Exp $
 */

/**
 * Required classes
 */
require_once(AMFPHP_BASE . "shared/adapters/RecordSetAdapter.php");

class sqliteobjectAdapter extends RecordSetAdapter
{

	/**
	 * Constructor method for the adapter.  This constructor implements the setting of the
	 * 3 required properties for the object.
	 * 
	 * @param resource $d The datasource resource
	 */
	 
	function sqliteobjectAdapter($d)
	{
		parent::RecordSetAdapter($d);
		// grab all of the rows
		
		$fieldcount = $d->numFields();
		
		// loop over all of the fields
		for($i=0; $i<$fieldcount; $i++)  {
			// decode each field name ready for encoding when it goes through serialization
			// and save each field name into the array
			$this->columns[] = $d->fieldName($i);
		}
		
		if($d->numRows() > 0)
		{
			$this->rows = $d->fetchAll(SQLITE_NUM);
		}
	}
}
?>