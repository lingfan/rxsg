<?php
/**
 * This	Adapter	translates the specific	Database type links	to the data	and	pulls the data into	very
 * specific	local variables	to later be	retrieved by the gateway and returned to the client.
 *
 * Correct typing for MySQL databases contributed by Patrick Gutlich
 * 
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package	flashservices
 * @subpackage adapters
 * @version	$Id: adodbAdapter.php,v	1.2	2005/07/22 10:58:09	pmineault Exp $
 */
 
require_once(AMFPHP_BASE . "shared/adapters/RecordSetAdapter.php");

class adodbAdapter extends RecordSetAdapter	{
	/**
	 * Constructor method for the adapter.	This constructor implements	the	setting	of the
	 * 3 required properties for the object.
	 * 
	 * @param resource $d The datasource resource
	 */
	
	function adodbAdapter($d) {
		parent::RecordSetAdapter($d);
		$fieldcount	= $d->FieldCount();	// grab	the	number of fields
		
		for($i = 0;	$i < $fieldcount; $i++)	{ // loop over all of the fields
			$fld = $d->FetchField($i);
			$this->columns[] = $fld->name;
		}
		
		$d->MoveFirst();
		$this->rows = $d->GetArray();
	} 
} 

?>