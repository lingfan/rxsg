<?php
/**
 * This Adapter translates the specific Database type links to the data and pulls the data into very
 * specific local variables to later be retrieved by the gateway and returned to the client.
 *
 * Thanks to Andrew Robins for this contribution
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage adapters
 * @version $Id: informixAdapter.php,v 1.2 2005/07/22 10:58:09 pmineault Exp $
 */

require_once(AMFPHP_BASE . "shared/adapters/RecordSetAdapter.php");

class informixAdapter extends RecordSetAdapter {
	/**
	 * Constructor method for the adapter.  This constructor implements the setting of the
	 * 3 required properties for the object.
	 *
	 * @param resource $d The datasource resource
	 */
	
	function informixAdapter($d) 
	{
		parent::RecordSetAdapter($d);
		$fieldcount = ifx_num_fields($d);
		
		$properties=ifx_fieldproperties($d);
		
		for($i = 0; $i < $fieldcount; $i++) {
			$this->columns[$i] = key($properties);
			next($properties);
		}
		
		if(ifx_num_rows($d) > 0)
		{
			$line = ifx_fetch_row($d,"FIRST");
			do 
			{
				$this->rows[] = $line;
			} while ($line = ifx_fetch_row($d,"NEXT") );
		}
	}
}
?> 