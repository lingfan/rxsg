<?php
/**
* Utilities file
*
* @author Steve Francia webmaster@supernerd.com
* @package db
* @subpackage db_utils
*/

// Copyright (c) 2006 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

 /**
  *  Make a case statement that return $mapfield[$field] for sql
  *  using mkdirr.
  *
  * @param       string   $field    The field name you want to create a directory for.
  * @param       array    $mapfield
  * @return      bool     Returns TRUE on success, FALSE on failure
  */
function sqlMap($field, $mapfield)
{
	$sql = "case $field ";
	foreach ($mapfield as $key => $value)
	{
		$sql .= "when $key then ". sql_escape_string($value) . " ";
	}
	$sql .= "else '' end";
	return $sql;
}

/**
* Take a Associated Array and Table name and insert the array's values into the database.
* Array should be in the format $arrayname['fieldname'] => value
*
* This function will escape <b>Everything</b> so please don't escape before hand.
*
* @param array $inArray the array to be inserted
* @param string $tablename the name of the table to insert the array into
* @return mixed $return the primary key value of the inserted record
*/
function db_insert_array($inArray, $tablename)
{
	sql_connect();
	global $defaultdb;

	foreach ($inArray as $field => $value)
	{
		$fields[] = $defaultdb->escape_identifier($field);
		$values[] = $defaultdb->escape_string($value);
	}

	$fieldstr = implode(",", $fields);
	$valuestr = implode(",", $values);
	$tablename = $defaultdb->escape_tablename($tablename);

	$query = "INSERT INTO $tablename ($fieldstr) VALUES ($valuestr)";

	$id = sql_insert($query);

	return $id;
}

/**
* Take a Associated Array and Table name, and Primary Key name and Id and update the database with the values in the array.
* Array should be in the format $arrayname['fieldname'] => value
*
* This function will escape <b>Everything</b> so please don't escape before hand.
*
* @param array $inArray the array to be inserted
* @param string $tablename the name of the table to insert the array into
* @param string $primarykey the name of the primary key field
* @param string $id of the primary key
*/
function db_update_array($inArray, $tablename, $primarykey, $primarykeyvalue)
{
	sql_connect();
	global $defaultdb;

	foreach ($inArray as $field => $value)
	{
		$updateStr = "";
		$updateStr .= $defaultdb->escape_identifier($field);
		$updateStr .= "=";
		$updateStr .= $defaultdb->escape_string($value);

		$updateArray[] = $updateStr;
	}

	$newupdateStr = implode(",", $updateArray);
	$tablename = $defaultdb->escape_tablename($tablename);
	$primarykey = $defaultdb->escape_identifier($primarykey);
	$primarykeyvalue = $defaultdb->escape_string($primarykeyvalue);

	$query = "UPDATE $tablename SET $newupdateStr WHERE $primarykey = $primarykeyvalue";

	return $defaultdb->query($query);
}
/**
* A Wrapper Function for update_array and insert_array. Examines the $primarykeyvalue
* and chooses which function to run.
*
* This function will escape <b>Everything</b> so please don't escape before hand.
*
* @param array $inArray the array to be inserted
* @param string $tablename the name of the table to insert the array into
* @param string $primarykey the name of the primary key field
* @param string $id of the primary key
* @return mixed $return the primary key value of the saved record
*/
function db_save_array($inArray, $tablename, $primarykey, $primarykeyvalue)
{
		if ($primarykeyvalue == "new")
			$return = db_insert_array($inArray, $tablename);
		else
		{
			db_update_array($inArray, $tablename, $primarykey, $primarykeyvalue);
			$return = $primarykeyvalue;
		}

		return $return;
}


?>
