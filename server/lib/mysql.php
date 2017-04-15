<?php
	function sql_query( $query, $Db = -1 )
	{
        $stmt = prepare($query);
        $return = $stmt->rowCount();
        return $return;


		//sql_connect();
		//global $defaultdb;
		//$return = $defaultdb->query($inQueryString);
		//return $return;
	}



	function sql_insert($query)
	{
        $stmt = prepare($query);
        $return = DBPDO::getConn()->lastInsertId();
        return $return;

		//sql_connect();
		//global $defaultdb;
		//$defaultdb->insert($query);
		//return sql_fetch_one_cell('select last_insert_id()');
	}


	///////////////////////////////////////////////
	//	Query returns true if rows are returned  //
	///////////////////////////////////////////////

	function sql_check($query)
	{
        $stmt = prepare($query);
        $return = $stmt->rowCount();
        return $return;

		//sql_connect();
		//global $defaultdb;
		//$return = $defaultdb->check($query);
		//return $return;
	}

	function sql_fetch_into_arrays($query)
	{
        $stmt = prepare($query);
        $return = $stmt->fetchAll();
        return $return;

		//sql_connect();
		//global $defaultdb;
		//$return = $defaultdb->fetch_into_arrays($query);
		//return $return;

	}




	function sql_fetch_column($query)
	{

        $stmt = prepare($query);
        $return = array();
        while($val = $stmt->fetchColumn()) {
            $return[] = $val;
        }
        return $return;


		//sql_connect();
		//global $defaultdb;
		//	the database clas should also create fetch_column and depricate
		//	new_fetch_into_array
		//$return = $defaultdb->new_fetch_into_array($query);
		//return $return;
	}



	function sql_fetch_one($query)
	{
        $stmt = prepare($query);
        $return = $stmt->fetch();
        return $return;

		//sql_connect();
		//global $defaultdb;
		//$return = $defaultdb->fetch_one($inQueryString);
		//return $return;
	}


	function sql_fetch_rows($query, $inReturnObjects = 0)
	{
        $stmt = prepare($query);
        $return = $stmt->fetchAll();
        return $return;

		//sql_connect();
		//global $defaultdb;
		//$return = $defaultdb->fetch_rows($inQuery, $inReturnObjects);
		//return $return;
	}

	function sql_fetch_map($query, $inKeyField)
	{

        $stmt = prepare($query);
        $rows = $stmt->fetchAll();
        $results = array();
        foreach($rows as $row)
        {
            $mapKey = $row[ $inKeyField ];

            foreach($row as $key => $val)
            {
                $results[$mapKey][$key] = $val;
            }
        }
        return $results;


		///sql_connect();
		//global $defaultdb;
		//$return = $defaultdb->fetch_map($query,$inKeyField);
		//return $return;
	}


	function sql_fetch_simple_map($query, $inKeyField, $inValueField)
	{
        $stmt = prepare($query,__METHOD__); 

        $rows = $stmt->fetchAll();
        $results = array();
        foreach($rows as $row)
        {
            $cur = &$results;
            $cur = &$cur[$row[$inKeyField]];
            $lastKey = $row[$inKeyField];

            if(isset($cur) && !empty($lastKey))
            {
                trigger_error("duplicate key in query: \n $query \n");
            }
            $cur = $row[ $inValueField ];
        }

        return $results;


		//sql_connect();
		//global $defaultdb;
		//$return = $defaultdb->fetch_simple_map($query, $inKeyField, $inValueField);
		//return $return;
	}



	function sql_fetch_one_cell($query, $inField = 0)
	{
        $stmt = prepare($query,__METHOD__);
        $return = $stmt->fetchColumn($inField);
        return $return;

		//sql_connect();
		//global $defaultdb;
		//$return = $defaultdb->fetch_one_cell($query, $inField);
		//return $return;
	}


	function prepare($sql,$type='') {
        $stmt = DBPDO::getConn()->prepare($sql);
        error_log(date('Y-m-d H:i:s')."#{$type} ".$sql."\n",3,'/tmp/sql_'.date('Ymd'));
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute();
        return $stmt;
    }

?>