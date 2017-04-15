<?php
/**
 * This Adapter translates the specific Database type links to the data and pulls the data into very
 * specific local variables to later be retrieved by the gateway and returned to the client.
 *
 * Adapted from Micah Caldwell's implementation on Flash-db.com boards
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage adapters
 * @version $Id: mysqliAdapter.php,v 1.2 2005/07/22 10:58:09 pmineault Exp $
 */
require_once(AMFPHP_BASE . "shared/adapters/RecordSetAdapter.php");

class mysqliobjectAdapter extends RecordSetAdapter
{
	/**
	 * Constructor method for the adapter.  This constructor implements the setting of the
	 * 3 required properties for the object.
	 * 
	 * @param resource $d The datasource resource
	 */
	function mysqliobjectAdapter($d)
	{
		parent::RecordSetAdapter($d);
		while($field = $d->fetch_field())
		{   
			$this->columns[] = $field->name;
		}
		
		if($d->num_rows > 0)
		{
			$d->data_seek(0); 
			while ($line = $d->fetch_row()) {
				$this->rows[] = $line;
			}
		}
	}
}
?>