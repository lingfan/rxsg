<?php
/**
 * This Adapter translates the specific Database type links to the data and pulls the data into very
 * specific local variables to later be retrieved by the gateway and returned to the client.
 * 
 * This version of the frontbase adapter uses fast serialization
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage adapters
 * @version $Id: fbsqlAdapter.php,v 1.2 2005/07/22 10:58:09 pmineault Exp $
 */

require_once(AMFPHP_BASE . "shared/adapters/RecordSetAdapter.php");

class fbsqlAdapter extends RecordSetAdapter {
	/**
	 * Constructor method for the adapter.  This constructor implements the setting of the
	 * 3 required properties for the object.
	 * 
	 * @param resource $d The datasource resource
	 */
	function fbsqlAdapter($d) {
		parent::RecordSetAdapter($d);
		
		$fieldcount = fbsql_num_fields($d);
		
		for($i = 0; $i < $fieldcount; $i++) {
			$this->columns[] = fbsql_field_name($d, $i);
		} 
		
		if(fbsql_num_rows($d) > 0)
		{
			fbsql_data_seek($d, 0); 
			while ($line = fbsql_fetch_row($d)) {
				$this->rows[] = $line;
			}
		}
	} 
} 

?>