<?php
/**
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage adapters
 * @version $Id: odbcAdapter.php,v 1.2 2005/07/22 10:58:09 pmineault Exp $
 */
 
require_once(AMFPHP_BASE . "shared/adapters/RecordSetAdapter.php");

class odbcAdapter extends RecordSetAdapter {
	/**
	 * Constructor method for the adapter.  This constructor implements the setting of the
	 * 3 required properties for the object.
	 * 
	 * The body of this method was provided by Mario Falomir... Thanks.
	 * 
	 * @param resource $d The datasource resource
	 */
	function odbcAdapter($d) {
		parent::RecordSetAdapter($d);
		// count number of fields
		$fieldcount = odbc_num_fields($d); 
		
		// grab the number of fields
		// loop over all of the fields
		for($i = 0; $i < $fieldcount; $i++) {
			// decode each field name ready for encoding when it goes through serialization
			// and save each field name into the array
			$this->columns[] = odbc_field_name($d, $i + 1);
		} 
		
		if(odbc_num_rows($d) > 0)
		{
			$line = odbc_fetch_row($d, 0);
			do {
				$this->rows[] = $line;
			} while ($line = odbc_fetch_row($d));
		}
	} 
} 

?>