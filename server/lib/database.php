<?php
/**
* @package db
* @subpackage database
*/
// Copyright (c) 2005 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

/**
 * database 
 * 
 * @package 
 * @version $id$
 * @copyright 1997-2006 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com> 
 * @author John Lesusur
 * @author Rick Gigger
 * @author Richard Bateman
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class database
{
	/**
	 * db 
	 * 
	 * @var mixed
	 * @access public
	 */
	var $db = null;
	/**
	 * transaction 
	 * 
	 * @var float
	 * @access public
	 */
	var $transaction = 0;

	/**
	 * database 
	 * 
	 * @param mixed $dsn 
	 * @access public
	 * @return void
	 */
	function __construct($dsn)
	{

		$options = array(
	    	'debug'       => 2
		);

		if (defined('db_persistent'))
			$options['persistent'] = db_persistent;

		$this->dsn = &$dsn;
		$this->db = DB::connect($dsn, $options);
		if(DB::isError($this->db))
		{
			$this->error($this->db);
		}
		$this->db->setFetchMode(DB_FETCHMODE_ASSOC);
	}

	/**
	 * getDSN 
	 * 
	 * @access public
	 * @return void
	 */
	function getDSN()
	{
		return $this->dsn;
	}

	/**
	 * verifyQuery 
	 * 
	 * @param mixed $inQuery 
	 * @access public
	 * @return void
	 */
	function verifyQuery($inQuery)
	{
		if(defined("verify_queries") && verify_queries)
		{
			$inQuote = 0;
			for($i = 0 ; $i < strlen($inQuery); $i++)
			{
				if(!$inQuote && $inQuery[$i] == ';')
					trigger_error("this query had a ;, and is not safe...");
				else if($inQuery[$i] == '\'')
				{
					if($inQuote)
					{
						$inQuote = 0;
					}
					else
						$inQuote = 1;
				}
				else if($inQuery[$i] == '\\')
				{
					$i++;
				}
			}
		}
	}

	/**
	 * makeDSN 
	 * 
	 * @param mixed $dbtype 
	 * @param mixed $host 
	 * @param mixed $port 
	 * @param mixed $username 
	 * @param mixed $password 
	 * @param mixed $database 
	 * @access public
	 * @return void
	 */
	function makeDSN($dbtype, $host, $port, $username, $password, $database)
	{
		return array(
		    'phptype'  => $dbtype,
		    //'dbsyntax' => false,
		    'username' => $username,
		    'password' => $password,
		    //'protocol' => false,
		    'hostspec' => $host,
		    'port'     => $port,
		    //'socket'   => false,
		    'database' => $database,

	   );
	}

	/**
	 * begin_transaction 
	 * 
	 * @access public
	 * @return void
	 */
	function begin_transaction( )
	{
		if($this->transaction == 0)
			$this->db->query("BEGIN");
		$this->transaction++;
	}

	/**
	 * commit_transaction 
	 * 
	 * @access public
	 * @return void
	 */
	function commit_transaction( )
	{
		$this->transaction--;
		if($this->transaction == 0)
			$this->db->query("END");
	}

	/**
	 * rollback_transaction 
	 * 
	 * @access public
	 * @return void
	 */
	function rollback_transaction( )
	{
		$this->transaction--;
		if($this->transaction == 0)
			$this->db->query("ROLLBACK");
	}

	/**
	 * error 
	 * 
	 * @param mixed $result 
	 * @access public
	 * @return void
	 */
	function error($result,$sql = "")
	{
		while ($this->transaction)
		{
			sql_rollback_transaction();
		}
		//echo substr($inQueryString, 0, 1200) . "<br>" .
		//echo_r($result);
		
		error_log("PearDB returned an error. The error was " . $result->getMessage().". sql=".$sql);
		if(db_Debug)
			trigger_error("PearDB returned an error. The error was " . $result->getMessage().". sql=".$sql);
		else
			trigger_error("PearDB returned an error. ");
		die();
	}

	/**
	 * query 
	 * 
	 * @param mixed $inQueryString 
	 * @param mixed $Db 
	 * @access public
	 * @return void
	 */
	function query( $inQueryString, $Db = -1 )
	{
		$this->verifyQuery($inQueryString);
		$result = $this->db->query($inQueryString);
		if(DB::isError($result))
		{
			$this->error($result,$inQueryString);
		}
		return $result;
	}

	/**
	 * get_fields 
	 * 
	 * @param mixed $table 
	 * @access public
	 * @return void
	 */
	function get_fields($table)
	{
		return $this->db->tableInfo($table);
	}

	/**
	 * insert 
	 * 
	 * @param mixed $query 
	 * @access public
	 * @return void
	 */
	function insert($query)
	{
		return $this->db->query($query);
	}

	/**
	 * fetch_sequence 
	 * 
	 * @param mixed $sequence 
	 * @access public
	 * @return void
	 */
	function fetch_sequence( $sequence )
	{
		return $this->db->getOne("select nextval('\"$sequence\"'::text)");
	}

/**
* returns true if rows are returned
*
* @param string $query the query for the database
* @return boolean
*/
	function check($query)
	{
		$result = $this->db->query($query);

		if(DB::isError($result))
		{
			$this->error($result,$query);
		}

		if($result->numRows() < 1)
		{
			$result->free();
			return 0;
		}
		else
		{
			$result->free();
			return 1;
		}
	}

	/**
	 * fetch_into_arrays 
	 * 
	 * @param mixed $query 
	 * @access public
	 * @return void
	 */
	function fetch_into_arrays($query)
	{
		$result = $this->db->getAll($query, array(), DB_FETCHMODE_ASSOC | DB_FETCHMODE_FLIPPED);
		if(DB::isError($result))
		{
			$this->error($result,$query);
		}
		return $result;
	}

	/**
	 * fetch_into_arrobjs 
	 * 
	 * @param mixed $query 
	 * @access public
	 * @return void
	 */
	function fetch_into_arrobjs($query)
	{
		$this->verifyQuery($query);
		bug("this function deprecated, please use a different one...");
		$result = $this->db->getAll($query);
		if(DB::isError($result))
		{
			$this->error($result,$query);
		}

		return $result;
	}

	/**
	 * new_fetch_into_array 
	 * 
	 * @param mixed $query 
	 * @access public
	 * @return void
	 */
	function new_fetch_into_array($query)
	{
		$this->verifyQuery($query);
		$result = $this->db->getCol($query);
		if(DB::isError($result))
		{
			$this->error($result,$query);
		}
		return $result;
	}

	/**
	 * fetch_into_array 
	 * 
	 * @param mixed $inTableName 
	 * @param mixed $inFieldName 
	 * @param string $inExtra 
	 * @access public
	 * @return void
	 */
	function fetch_into_array($inTableName, $inFieldName, $inExtra = "")
	{
		bug("please change this to a query and use new_fetch_into_array");
		$result = $this->db->getCol("SELECT $inFieldName FROM $inTableName $inExtra");
		if(DB::isError($result))
		{
			$this->error($result);
		}
		return $result;;
	}

/**
* Use this function to get a record from the database. It will be returned as an array with the key as the fieldname and the value as the value.
*
* @param string $query the query for the database
* @return associative array in the form [fieldname] => value;
*/
	function fetch_one($inQueryString)
	{

		$result = $this->db->query($inQueryString);
		if(DB::isError($result))
		{
			$this->error($result,$inQueryString);
		}

		$numRows = $result->numRows();

		if($numRows > 1)
		{
			trigger_error ( "Only one result was expected. " . $numRows . " were returned");
		}
		else if($numRows == 0)
		{
			return(false);
		}

		$row = $result->fetchRow();
		$result->free();

		return $row;
	}
/**
* Use this function to get a record, or multiple records from the database.
* It will be returned as a two dimensional array. The first dimension will be an array with the key being the value of the primary key in each record.
* The second dimension would be identical to that returned from fetch_one but without the primary key.
*
* @param string $query the query for the database
* @return associative array in the form [primarykeyvalue][fieldname] => value;
*/
	function fetch_assoc($inQuery)
	{
		$this->verifyQuery($inQuery);
		$result = $this->db->getAssoc($inQuery);
		if(DB::isError($result))
		{
			$this->error($result,$inQuery);
		}

		return $result;
	}

	/**
	 * fetch_rows 
	 * 
	 * @param mixed $inQuery 
	 * @param int $inReturnObjects 
	 * @access public
	 * @return void
	 */
	function fetch_rows($inQuery, $inReturnObjects = 0)
	{
		$this->verifyQuery($inQuery);
		$rows = array();
		if($inReturnObjects)
		{
			$rows = $this->db->getAll($inQuery, array(), DB_FETCHMODE_OBJECT);
		}
		else
		{
			$rows = $this->db->getAll($inQuery);
		}
		if(DB::isError($rows))
		{
			$this->error($rows,$inQuery);
		}
		return $rows;
	}

	/**
	 * &fetch_map 
	 * 
	 * @param mixed $inQuery 
	 * @param mixed $inKeyField 
	 * @access public
	 * @return void
	 */
	function &fetch_map($inQuery, $inKeyField)
	{
		$this->verifyQuery($inQuery);
		$rows = $this->db->getAll($inQuery);
		if(DB::isError($rows))
		{
			$this->error($rows,$inQuery);
		}
		$results = array();

		foreach($rows as $row)
		{
			if( is_array($inKeyField))
			{
				$cur = &$results;

				foreach( $inKeyField as $val )
				{
					$curKey = $row[ $val ];

					if( !isset( $cur[ $curKey ] ) )
					{
						$cur[ $curKey ] = array();
					}

					$cur = &$cur[ $curKey ];
				}
				if(count($cur))
				{
					echo_r($results);
					trigger_error("duplicate key $curKey, would silently destroy data");
				}

				$cur = $row;
			}
			else
			{
				$mapKey = $row[ $inKeyField ];

				foreach($row as $key => $val)
				{
					$results[$mapKey][$key] = $val;
				}
			}
		}
		return $results;
	}


	/**
	 * fetch_simple_map 
	 * 
	 * @param mixed $inQuery 
	 * @param mixed $inKeyField 
	 * @param mixed $inValueField 
	 * @access public
	 * @return void
	 */
	function fetch_simple_map($inQuery, $inKeyField, $inValueField)
	{
		$this->verifyQuery($inQuery);
		$rows = $this->db->getAll($inQuery);
		if(DB::isError($rows))
		{
			$this->error($rows,$inQuery);
		}
		$results = array();

		foreach($rows as $row)
		//while($row = sql_fetch_array($rows))
		{
			$cur = &$results;
			if(is_array($inKeyField))
			{
				foreach($inKeyField as $key)
				{
					$cur = &$cur[$row[$key]];
					$lastKey = $row[$key];
				}
			}
			else
			{
				$cur = &$cur[$row[$inKeyField]];
				$lastKey = $row[$inKeyField];
			}
			if(isset($cur) && !empty($lastKey))
			{
				trigger_error("duplicate key in query: \n $inQuery \n");
			}
			$cur = $row[ $inValueField ];
		}

		return $results;
	}


	/**
	 * &fetch_complex_map 
	 * 
	 * @param mixed $inQuery 
	 * @param mixed $inKeyField 
	 * @access public
	 * @return void
	 */
	function &fetch_complex_map($inQuery, $inKeyField)
	{
		$this->verifyQuery($inQuery);
		$rows = $this->db->getAll($inQuery);
		if(DB::isError($rows))
		{
			$this->error($rows,$inQuery);
		}
		$results = array();

		//	loop through each row in the result set

		foreach($rows as $row)
		{
			if( gettype($inKeyField) == "array")
			{
				$cur = &$results;

				foreach( $inKeyField as $val )
				{
					$curKey = $row[ $val ];

					if( !isset( $cur[ $curKey ] ) )
					{
						$cur[ $curKey ] = array();
					}

					$cur = &$cur[ $curKey ];
				}

				$cur[] = $row;
			}
			else
			{
				//	get the key for the result map
				$mapKey = $row[ $inKeyField ];

				$results[$mapKey][] = $row;
			}
		}

		return $results;
	}


	/**
	 * fetch_one_cell 
	 * 
	 * @param mixed $inQueryString 
	 * @param int $inField 
	 * @access public
	 * @return void
	 */
	function fetch_one_cell($inQueryString, $inField = 0)
	{
		$result = $this->db->query($inQueryString, array(), DB_FETCHMODE_ORDERED);
		if(DB::isError($result))
		{
			$this->error($result,$inQueryString);
		}

		$numRows = $result->numRows();
		if($numRows > 1)
		{
			trigger_error(substr($inQueryString, 0, 150) . "<br>Only one result was expected. " . $numRows . " were returned.<br>");
		}
		else if($numRows == 0)
		{
			$result->free();
			return(false);
		}

		$row = $result->fetchRow(DB_FETCHMODE_ORDERED);
		$result->free();
		if (!isset($row[$inField]))
		{
			$row[$inField] = null;
		}

		return $row[$inField];
	}

	/**
	 * &prepare_tree_query 
	 * 
	 * @param mixed $inQueryString 
	 * @param string $idField 
	 * @param string $parentField 
	 * @access public
	 * @return void
	 */
	function &prepare_tree_query($inQueryString, $idField = "id", $parentField = "parent")
	{
		$map = &$this->fetch_map($inQueryString, $idField);
		$complex = array();
		foreach($map as $id => $obj)
		{
			$complex[$obj[$parentField]][] = &$map[$id];
		}
		$answer[$idField] = &$map;
		$answer[$parentField] = &$complex;
		return $answer;
	}

	/**
	 * &better_fetch_tree 
	 * 
	 * @param mixed $inQueryString 
	 * @param mixed $rootNode 
	 * @param string $idField 
	 * @param string $parentField 
	 * @access public
	 * @return void
	 */
	function &better_fetch_tree( &$inQueryString, $rootNode, $idField = "id", $parentField = "parent")
	{
		if(!is_array($inQueryString))
		{
			//do your own complex mapping...
			//find the root nodes as you go...
			$objects = &$this->prepare_tree_query($inQueryString, $idField, $parentField);
		}
		else
		{
			//php5 clone this
			$objects = &$inQueryString;
		}
		if(is_array($rootNode) && in_array($object[$idField], $rootNode))
		{
			foreach($rootNode as $node)
			{
				$tree[$node] = $objects[$idField][$node];
			}
		}
		else
		{
			$tree = $objects[$idField][$rootNode];
		}

		if(is_array($rootNode))
		{
			foreach($rootNode as $node)
			{
				$tree[$node]['children'] = $this->__sql_better_append_children($node, $objects, $idField, $parentField);
			}
		}
		else
		{
			$tree['children'] = $this->__sql_better_append_children($rootNode, $objects, $idField, $parentField);
		}

		return $tree;
	}

	/**
	 * &fetch_tree 
	 * 
	 * @param mixed $inQueryString 
	 * @param mixed $rootNode 
	 * @param string $idField 
	 * @param string $parentField 
	 * @access public
	 * @return void
	 */
	function &fetch_tree( $inQueryString, $rootNode, $idField = "id", $parentField = "parent")
	{
		if(is_array($inQueryString))
		{
			$objects = $inQueryString;
		}
		else
		{
			$objects = $this->fetch_map($inQueryString, $idField);
		}
		if(is_array($rootNode))
		{
			foreach($rootNode as $node)
			{
				//php 5 need clone here
				$node = $objects[$node];
				$tree[] = $this->__sql_append_children($node, $objects, $idField, $parentField);
			}
		}
		else
		{
			//php 5 need clone here
			$rootNode = $objects[$rootNode];
			$tree = $this->__sql_append_children($rootNode, $objects, $idField, $parentField);
		}

		return $tree;
	}

	/**
	 * &__sql_append_children 
	 * 
	 * @param mixed $rootObject 
	 * @param mixed $objects 
	 * @param mixed $idField 
	 * @param mixed $parentField 
	 * @access public
	 * @return void
	 */
	function &__sql_append_children(&$rootObject, $objects, $idField, $parentField)
	{
		foreach($objects as $object)
		{
			if(isset($object[$parentField]) && $object[$parentField] == $rootObject[$idField])
			{
				$rootObject["children"][$object[$idField]] = $object;
				$this->__sql_append_children($rootObject["children"][$object[$idField]], $objects, $idField, $parentField);
			}

		}

		return $rootObject;
	}

	/**
	 * &__sql_better_append_children 
	 * 
	 * @param mixed $rootObjectId 
	 * @param mixed $objects 
	 * @param mixed $idField 
	 * @param mixed $parentField 
	 * @param mixed $depth 
	 * @access public
	 * @return void
	 */
	function &__sql_better_append_children(&$rootObjectId, &$objects, $idField, $parentField, $depth = -1)
	{
		if($depth != 0)
		{
			$children = array();
			if(isset($objects[$parentField][$rootObjectId]))
			{
				foreach($objects[$parentField][$rootObjectId] as $object)
				{
					$children[$object[$idField]] = $object;
					$children[$object[$idField]]['children'] = $this->__sql_better_append_children($object[$idField], $objects, $idField, $parentField, $depth - 1);
				}
			}
		}
		return $children;
	}


	//	inQuerystring can be a map (php array/hashtable), and then it will use the map instead of querying the database....
	//	This helps in making multiple calls when you need separate arrays for each parent node's children.
	//	Might be too much of a secret hack though - at least the var name should probably be changed

	/**
	 * &fetch_children 
	 * 
	 * @param mixed $inQueryString 
	 * @param mixed $rootNode 
	 * @param string $idField 
	 * @param string $parentField 
	 * @access public
	 * @return void
	 */
	function &fetch_children( $inQueryString, $rootNode, $idField = "id", $parentField = "parent")
	{
		//	get the set of rows that we are dealing with.  It shoudld contain all of the rows that could possibly
		//	end up as nodes in the tree
		if(is_array($inQueryString))
		{
			$objects = $inQueryString;
		}
		else
		{
			//markprofile();
			$objects = $this->fetch_map($inQueryString, $idField);
			//markprofile();
		}
		//markprofile();
		if(is_array($rootNode))
		{
			foreach($rootNode as $node)
			{
				$children[$objects[$node][$idField]] = $objects[$node];
			}
		}
		else
		{
			//	get the id of the root node and and set it to the data for the root node
			//	in our result object (children)
			$children[$objects[$rootNode][$idField]] = $objects[$rootNode];
		}

		//fixed point algorithm....
		$done = false;
		while(!$done)
		{
			$done = true;
			foreach($objects as $object)
			{
				//	if the db row has a parent and is not already in the tree
				if(isset($children[$object[$parentField]]) && !isset($children[$object[$idField]]))
				{
					$done = false;

					$children[$object[$idField]] = $object;
					$keys = array_keys($children[$object[$parentField]]);
					//fill in inherited properties from parents....
					//is this a good idea?
					//*
					foreach($keys as $key)
					{
						if(!isset($children[$object[$idField]][$key]))
						{
							$children[$object[$idField]][$key] = $children[$object[$parentField]][$key];
						}
					}
					//*/
				}
			}
		}
		//markprofile();
		return $children;
	}

	//	inQuerystring can be a map (php array/hashtable), and then it will use the map instead of querying the database....
	//	This helps in making multiple calls when you need separate arrays for each parent node's children.
	//	Might be too much of a secret hack though - at least the var name should probably be changed

	/**
	 * &better_fetch_children 
	 * 
	 * @param mixed $inQueryString 
	 * @param mixed $rootNode 
	 * @param string $idField 
	 * @param string $parentField 
	 * @param mixed $depth 
	 * @access public
	 * @return void
	 */
	function &better_fetch_children( $inQueryString, $rootNode, $idField = "id", $parentField = "parent", $depth = -1)
	{
		//	get the set of rows that we are dealing with.  It shoudld contain all of the rows that could possibly
		//	end up as nodes in the tree
		if(is_array($inQueryString))
		{
			$objects = $inQueryString;
		}
		else
		{
			$objects = $this->prepare_tree_query($inQueryString, $idField);
		}

		if(is_array($rootNode))
		{
			foreach($rootNode as $node)
			{
				$children[$node] = $objects['id'][$node];
			}
		}
		else
		{
			//	get the id of the root node and and set it to the data for the root node
			//	in our result object (children)
			$children[$rootNode] = $objects['id'][$rootNode];
		}
		foreach($children as $id => $node)
		{
			$this->_fetch_children($children, $objects, $id, $depth);
		}
		//markprofile();
		//echo_r($children);
		return $children;
	}

	/**
	 * _fetch_children 
	 * 
	 * @param mixed $children 
	 * @param mixed $objects 
	 * @param mixed $id 
	 * @param mixed $depth 
	 * @access protected
	 * @return void
	 */
	function _fetch_children(&$children, &$objects, $id, $depth = -1)
	{
		if(isset($objects['parent'][$id]) && ($depth != 0))
		{
			foreach($objects['parent'][$id] as $index => $node)
			{
				$children[$node['id']] = $node;
				$this->_fetch_children($children, $objects, $node['id'], $depth - 1);
			}
		}
	}

	/**
	 * &fetch_parents 
	 * 
	 * @param mixed $inQueryString 
	 * @param mixed $leafNode 
	 * @param string $idField 
	 * @param string $parentField 
	 * @access public
	 * @return void
	 */
	function &fetch_parents($inQueryString, $leafNode, $idField = "id", $parentField = "parent")
	{
		//	get the set of rows that we are dealing with.  It should contain all of the rows that could possibly
		//	end up in the parent chain
		$objects = $this->fetch_map($inQueryString, $idField);

		//	set up the first node, we will go up from here
		$parents[$leafNode] = $objects[$leafNode];

		//	walk up the tree to the root
		$nextParent = $objects[$leafNode][$parentField];
		while(isset($objects[$nextParent]) && $objects[$nextParent] != NULL && !isset($parents[$nextParent]))
		{
			$parents[$objects[$nextParent][$idField]] = $objects[$nextParent];
			$nextParent = $objects[$nextParent][$parentField];
		}
		return $parents;
	}

	/**
	 * get_table_info 
	 * 
	 * @param mixed $inTable 
	 * @access public
	 * @return void
	 */
	function get_table_info($inTable)
	{
		$result = $this->db->tableinfo($inTable);

		if(DB::isError($result))
		{
			$this->error($result);
		}
		return $result;
	}

	/**
	 * escape_string 
	 * 
	 * @param mixed $inString 
	 * @access public
	 * @return void
	 */
	function escape_string($inString)
	{
		return $this->db->quoteSmart($inString);
	}

	/**
	 * escape_identifier 
	 * 
	 * @param mixed $inString 
	 * @access public
	 * @return void
	 */
	function escape_identifier($inString)
	{
		return $this->db->quoteIdentifier($inString);
	}

	/**
	 * escape_tablename 
	 * 
	 * @param mixed $inString 
	 * @access public
	 * @return void
	 */
	function escape_tablename($inString)
	{
		$name = explode(".", $inString);
		foreach($name as $part)
		{
			$newname[] = $this->db->quoteIdentifier($part);
		}
		return implode('.', $newname);
	}
}
?>
