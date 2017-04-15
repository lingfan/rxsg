<?php
/**
 * The newest version of the PearDB adapter includes a hack to type number column
 * types as numbers, despite the fact that PHP does not offer this kind of info by default
 *
 * A contribution of Jaybee Reeves
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage adapters
 * @version $Id: peardbAdapter.php,v 1.1 2005/07/05 07:56:29 pmineault Exp $
 */

require_once(AMFPHP_BASE . "shared/adapters/RecordSetAdapter.php");

class peardbAdapter extends RecordSetAdapter {
	/**
	 * Constructor method for the adapter.  This constructor implements the setting of the
	 * 3 required properties for the object.
	 * 
	 * @param resource $d The datasource resource
	 */
	 
	function peardbAdapter($d) {
		
		parent::RecordSetAdapter($d);
		$fieldcount = $d->numCols();
		
		$intFields = array();
		$info = $d->dbh->tableInfo($d);
		for($i = 0; $i < $fieldcount; $i++) {
			$this->columnNames[$i] = $this->_charsetHandler->transliterate($info[$i]['name']);
			
			$type = $info[$i]['type'];
			if(in_array($type, array('int', 'real', 'year')))
			{
				$intFields[] = $i;
			}
		}
		
		if($d->numRows() > 0)
		{
			$line = $d->fetchRow(DB_FETCHMODE_ORDERED, 0);
			do {
				foreach($intFields as $key => $val)
				{
					$line[$val] = (float) $line[$val];
				}
				$this->rows[] = $line;
			} while ($line = $d->fetchRow(DB_FETCHMODE_ORDERED, $rows));
		}
	}
}

?>