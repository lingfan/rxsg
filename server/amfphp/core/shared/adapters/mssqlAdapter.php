<?php
/**
 * This Adapter translates the specific Database type links to the data and pulls the data into very
 * specific local variables to later be retrieved by the gateway and returned to the client.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage adapters
 * @version $Id: mssqlAdapter.php,v 1.2 2005/07/22 10:58:09 pmineault Exp $
 */
 
require_once(AMFPHP_BASE . "shared/adapters/RecordSetAdapter.php");

class mssqlAdapter extends RecordSetAdapter {
	/**
	 * Constructor method for the adapter.  This constructor implements the setting of the
	 * 3 required properties for the object.
	 * 
	 * @param resource $d The datasource resource
	 */
	function mssqlAdapter($d) {
		parent::RecordSetAdapter($d);

		$fieldcount = mssql_num_fields($d); // grab the number of fields
		
		for($i = 0; $i < $fieldcount; $i++) { // loop over all of the fields
			$this->columnNames[] = mssql_field_name($d, $i);
		} 
		
		if(mssql_num_rows($d) > 0)
		{
			mssql_data_seek($d, 0);
			while ($line = mssql_fetch_row($d)) {
				$this->rows[] = $line;
			}
		}
	} 
} 

?>