<?php
/**
 * This Adapter translates the specific Database type links to the data and pulls the data into very
 * specific local variables to later be retrieved by the gateway and returned to the client.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage adapters
 * @version $Id: oci8Adapter.php,v 1.2 2005/07/22 10:58:09 pmineault Exp $
 */

require_once(AMFPHP_BASE . "shared/adapters/RecordSetAdapter.php");

class oci8Adapter extends RecordSetAdapter {
	/**
	 * Constructor method for the adapter.  This constructor implements the setting of the
	 * 3 required properties for the object.
	 * 
	 * @param resource $d The datasource resource
	 */
	 
	function oci8Adapter($d) {
		parent::RecordSetAdapter($d);
		$fieldcount = ocinumcols($d);
		for($j = 0; $j < $fieldcount; $j++) {
			$this->columnNames[] = ocicolumnname($d, $j+1);
		}
		
		$i = 0;
		while ( OCIFetchInto($d,$line,OCI_NUM+OCI_RETURN_LOBS+OCI_RETURN_NULLS)) {
			$this->rows[] = $line;
		} 
	}
}

?>