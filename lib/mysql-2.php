<?php

/*
 *  Copyright (C) 2011
 *     Ed Rackham (http://github.com/a1phanumeric/PHP-MySQL-Class)
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class mysql2 {

	var $con;
	private $myHost;
	private $myUser;
	private $myPass;
	private $myDatabase;
	private $myTable;

	public function __construct($db=array()) {
		$default = array(
			'host' => 'localhost',
			'user' => 'root',
			'pass' => ''
		);
		$db = array_merge($default,$db);

		$this->myHost = $db['host'];
		$this->myUser = $db['user'];
		$this->myPass = $db['pass'];

		if ($db['db']) {
			$this->myDatabase = $db['db'];
			if ($this->connect()) {
				$this->selectDb($db['db'], $this->con);
			}
		}
	}

	public function connect() {
		error_log("connecting to $this->myHost, $this->myUser, $this->myPass");
		$this->con = mysql_connect($this->myHost, $this->myUser, $this->myPass,true); // or die ('Error connecting to MySQL');
		return $this->con;
	}

	public function close() {
		return mysql_close($this->con);
	}

	public function selectDb($database) {
		return mysql_select_db($database, $this->con) || die("Database $database does not exist!");
	}

	public function getConnection() {
		return $this->con;
	}

	function __destruct() {
		error_log("ARRRGGGGG IM DYING");
		mysql_close($this->con);
	}

	function query($s='',$rows=false,$organize=true) {
		if (!$q=mysql_query($s,$this->con)) return false;
		if ($rows!==false) $rows = intval($rows);
		$rez=array(); $count=0;
		$type = $organize ? MYSQL_NUM : MYSQL_ASSOC;
		while (($rows===false || $count<$rows) && $line=mysql_fetch_array($q,$type)) {
			if ($organize) {
				foreach ($line as $field_id => $value) {
					$table = mysql_field_table($q, $field_id);
					if ($table==='') $table=0;
					$field = mysql_field_name($q,$field_id);
					$rez[$count][$table][$field]=$value;
				}
			} else {
				$rez[$count] = $line;
			}
			++$count;
		}
		if (!mysql_free_result($q)) return false;
		return $rez;
	}

	function execute($s='') {
		if (mysql_query($s,$this->con)) return true;
		return false;
	}

	function select($options) {
		$default = array (
			'table' => '',
			'fields' => '*',
			'condition' => '1',
			'order' => '1',
			'limit' => 50
		);
		$options = array_merge($default,$options);
		$sql = "SELECT {$options['fields']} FROM {$options['table']} WHERE {$options['condition']} ORDER BY {$options['order']} LIMIT {$options['limit']}";
		return $this->query($sql);
	}

	function row($options) {
		$default = array (
			'table' => '',
			'fields' => '*',
			'condition' => '1',
			'order' => '1'
		);
		$options = array_merge($default,$options);
		$sql = "SELECT {$options['fields']} FROM {$options['table']} WHERE {$options['condition']} ORDER BY {$options['order']}";
		$result = $this->query($sql,1,false);
		if (empty($result[0])) return false;
		return $result[0];
	}

	function get($table=null,$field=null,$conditions='1') {
		if ($table===null || $field===null) return false;
		$result=$this->row(array(
			'table' => $table,
			'condition' => $conditions,
			'fields' => $field
		));
		if (empty($result[$field])) return false;
		return $result[$field];
	}

	function update($table=null,$array_of_values=array(),$conditions='FALSE') {
		if ($table===null || empty($array_of_values)) return false;
		$what_to_set = array();
		foreach ($array_of_values as $field => $value) {
			if (is_array($value) && !empty($value[0])) $what_to_set[]="`$field`='{$value[0]}'";
			else $what_to_set []= "`$field`='".mysql_real_escape_string($value,$this->con)."'";
		}
		$what_to_set_string = implode(',',$what_to_set);
		return $this->execute("UPDATE $table SET $what_to_set_string WHERE $conditions");
	}

	function insert($table=null,$array_of_values=array()) {
		if ($table===null || empty($array_of_values) || !is_array($array_of_values)) return false;
		$fields=array(); $values=array();
		foreach ($array_of_values as $id => $value) {
			$fields[]=$id;
			if (is_array($value) && !empty($value[0])) $values[]=$value[0];
			else $values[]="'".mysql_real_escape_string($value,$this->con)."'";
		}
		$s = "INSERT INTO $table (".implode(',',$fields).') VALUES ('.implode(',',$values).')';
		if (mysql_query($s,$this->con)) return mysql_insert_id($this->con);
		return false;
	}

	function delete($table=null,$conditions='FALSE') {
		if ($table===null) return false;
		return $this->execute("DELETE FROM $table WHERE $conditions");
	}
}


// MySQL Class
class MySQL {
	// Base variables
	private $sLastError;       // Holds the last error
	private $sLastQuery;       // Holds the last query
	private $aResult;          // Holds the MySQL query result
	private $iRecords;         // Holds the total number of records returned
	private $iAffected;        // Holds the total number of records affected
	private $aRawResults;      // Holds raw 'arrayed' results
	private $aArrayedResult;   // Holds a single 'arrayed' result
	private $aArrayedResults;  // Holds multiple 'arrayed' results (usually with a set key)

	private $sHostname;  // MySQL Hostname
	private $sUsername;  // MySQL Username
	private $sDatabase;  // MySQL Database
    private $bPersistant = false; //

    private $mysqlError;
    private $mysqlQuery;
    private $mysqlResult;

	private $sDBLink;     // Database Connection Link

    public function __construct($host, $user, $pass, $database = null, $persistant = false) { 
        $this->sHostname = $host;
        $this->sUsername = $user;
        $this->sPassword = $pass;
        $this->sDatabase = $database;
        $this->bPersistant = $persistant;
    }

 	// Connects class to database
 	// $bPersistant (boolean) - Use persistant connection?
 	public function connect($bPersistant = false){

		if($this->sDBLink){
			mysql_close($this->sDBLink);
		}

		if($bPersistant){
			$this->sDBLink = mysql_pconnect($this->sHostname, $this->sUsername, $this->sPassword, true);
		} 
		else {
			$this->sDBLink = mysql_connect($this->sHostname, $this->sUsername, $this->sPassword, true);
		}

		if (!$this->sDBLink){
			$this->sLastError = 'Could not connect to server: ' . mysql_error($this->sDBLink);
	  		return false;
		}

		if ($this->sDatabase && !$this->selectDb($this->sDatabase)) {
			$this->sLastError = 'Could not connect to database: ' . mysql_error($this->sDBLink);
	  		return false;
		}
		error_log("DBLINK IS ($this->sDBLink)");
		return $this->sDBLink;
  	}

  	function getLink() {
  		return $this->sDBLink;
  	}

  	function closeDB() {
  		$val = mysql_close($this->sDBLink);
//		if ($val) {
//			$this->sDBLink = null;
//		}
  	}

  	// Select database to use
  	function selectDb($database) {
        if (!$this->sDBLink) {
            // We are trying to select a database without a connection - attempt to connect first.
            error_log("trying to connect to $this->sHostname, $this->sUsername, $this->sPassword, $database");
            $this->connect();
        }
		if (!mysql_select_db($database, $this->sDBLink)) {
	  		$this->sLastError = "Cannot select database ($database)" . mysql_error($this->sDBLink);
            error_log($this->sLastError);
	  		return false;
		}
		error_log("finished selecting db");
        return true;
	}

  	// Executes MySQL query
  	function ExecuteSQL($sSQLQuery){
		$this->sLastQuery   = $sSQLQuery;
		if ($this->aResult     = mysql_query($sSQLQuery, $this->sDBLink)){
			$this->iRecords   = @mysql_num_rows($this->aResult);
			$this->iAffected  = @mysql_affected_rows($this->sDBLink);
			return true;
		}
		else {
	  		$this->sLastError = mysql_error($this->sDBLink);
			return false;
		}
	}

	// Adds a record to the database
	// based on the array key names
	function Insert($aVars, $sTable, $aExclude = ''){
		// Catch Exceptions
		if ($aExclude == '') {
			$aExclude = array();
		}

		array_push($aExclude, 'MAX_FILE_SIZE');

		// Prepare Variables
		$aVars = $this->SecureData($aVars);

		$sSQLQuery = 'INSERT INTO `' . $sTable . '` SET ';
		foreach($aVars as $iKey=>$sValue){
	  		if (in_array($iKey, $aExclude)){
				continue;
		  	}
			$sSQLQuery .= '`' . $iKey . '` = "' . $sValue . '", ';
		}

		$sSQLQuery = substr($sSQLQuery, 0, -2);

		if ($this->ExecuteSQL($sSQLQuery)){
	  		return true;
		}
		else {
			return false;
		}
	}

	// Deletes a record from the database
	function Delete($sTable, $aWhere='', $sLimit='', $bLike=false) {
		$sSQLQuery = 'DELETE FROM `' . $sTable . '` WHERE ';
		if(is_array($aWhere) && $aWhere != '') {
	  		// Prepare Variables
			$aWhere = $this->SecureData($aWhere);

	  		foreach($aWhere as $iKey=>$sValue){
				if($bLike) {
		  			$sSQLQuery .= '`' . $iKey . '` LIKE "%' . $sValue . '%" AND ';
				}
				else {
					$sSQLQuery .= '`' . $iKey . '` = "' . $sValue . '" AND ';
				}
			}

	  		$sSQLQuery = substr($sSQLQuery, 0, -5);
		}

		if ($sLimit != '') {
			$sSQLQuery .= ' LIMIT ' .$sLimit;
		}

		if ($this->ExecuteSQL($sSQLQuery)){
	  		return true;
		}
		else {
			return false;
		}
	}

	// Gets a single row from $1
	// where $2 is true
  	function Select($sFrom, $aWhere='', $sOrderBy='', $sLimit='', $bLike=false, $sOperand='AND'){
		// Catch Exceptions
		if(trim($sFrom) == ''){
			return false;
		}

		$sSQLQuery = 'SELECT * FROM `' . $sFrom . '` WHERE ';

    	if(is_array($aWhere) && $aWhere != '') {
            // Prepare Variables
            $aWhere = $this->SecureData($aWhere);

            foreach($aWhere as $iKey=>$sValue){
                if($bLike){
                    $sSQLQuery .= '`' . $iKey . '` LIKE "%' . $sValue . '%" ' . $sOperand . ' ';
                }
                else {
                    $sSQLQuery .= '`' . $iKey . '` = "' . $sValue . '" ' . $sOperand . ' ';
                }
            }
            $sSQLQuery = substr($sSQLQuery, 0, -5);
        }
        else {
            $sSQLQuery = substr($sSQLQuery, 0, -7);
        }

        if ($sOrderBy != '') { $sSQLQuery .= ' ORDER BY ' .$sOrderBy; }

        if ($sLimit != '') { $sSQLQuery .= ' LIMIT ' .$sLimit; }

        if ($this->ExecuteSQL($sSQLQuery)){
            if ($this->iRecords > 0){
                $this->ArrayResults();
            }
            return true;
        }
        else {
            return false;
        }
    }

	public function select($query, &$mysqlreturnresult) {
 		global $MYSQL_READ_HOST,    $MYSQL_READ_DATABASE,    $MYSQL_READ_USERID,    $MYSQL_READ_PASSWORD;
 		global $MYSQL_DEFAULT_HOST, $MYSQL_DEFAULT_DATABASE, $MYSQL_DEFAULT_USERID, $MYSQL_DEFAULT_PASSWORD;

 		global $mysql_link, $mysql_fields, $mysql_errmsg, $mysql_found_rows;

  		$host     = $MYSQL_READ_HOST     ? $MYSQL_READ_HOST     : $MYSQL_DEFAULT_HOST;
 		$database = $MYSQL_READ_DATABASE ? $MYSQL_READ_DATABASE : $MYSQL_DEFAULT_DATABASE;
 		$userid   = $MYSQL_READ_USERID   ? $MYSQL_READ_USERID   : $MYSQL_DEFAULT_USERID;  
		$password = $MYSQL_READ_PASSWORD ? $MYSQL_READ_PASSWORD : $MYSQL_DEFAULT_PASSWORD;

 		$recfound = 0;
 		$mysql_link = mysql_connect($host, $userid, $password, true);
 		$mysql_fields = array();
 		$mysql_row = array();   
 		$mysqlreturnresult = array();

 		mysql_select_db($database, $this->sDBLink);
		$mysql_result = mysql_query($query, $this->sDBLink);

		if ($mysql_result) {    
			$numFields = mysql_num_fields($mysql_result);
			while ($mysql_row = mysql_fetch_row($mysql_result)) {
				for ($index = 0; $index < $numFields; $index++) {
					$mysqlreturnresult[$recfound][mysql_field_name($mysql_result, $index)] = stripslashes($mysql_row[$index]);
				} 
				++$recfound;
			} 
		}

		for ($index = 0; $index < $numFields; $index++) {
			array_push($mysql_fields, mysql_field_name($mysql_result, $index));
		}   

		$mysql_errmsg = mysql_error($this->sDBLink);
		mysql_select_db($database, $this->sDBLink);
		$mysql_result = mysql_query("SELECT FOUND_ROWS();", $this->sDBLink);

		if ($mysql_result) {  
			$numFields = mysql_num_fields($mysql_result);
			while ($mysql_row = mysql_fetch_row($mysql_result)) {
				for ($index = 0; $index < $numFields; $index++) {
					$mysql_found_rows = $mysql_row[0];
				} 
			}
		}   
		mysql_close($this->sDBLink);
		return($recfound);
	}


    // Updates a record in the database
    // based on WHERE
    function Update($sTable, $aSet, $aWhere, $aExclude = ''){
        // Catch Exceptions
        if(trim($sTable) == '' || !is_array($aSet) || !is_array($aWhere)){
            return false;
        }
        if($aExclude == ''){
            $aExclude = array();
        }

        array_push($aExclude, 'MAX_FILE_SIZE');

        $aSet   = $this->SecureData($aSet);
        $aWhere = $this->SecureData($aWhere);

        // SET

        $sSQLQuery = 'UPDATE `' . $sTable . '` SET ';

        foreach ($aSet as $iKey=>$sValue){
            if (in_array($iKey, $aExclude)){
                continue;
            }
            $sSQLQuery .= '`' . $iKey . '` = "' . $sValue . '", ';
        }

        $sSQLQuery = substr($sSQLQuery, 0, -2);

        // WHERE

        $sSQLQuery .= ' WHERE ';

        foreach($aWhere as $iKey=>$sValue){
            $sSQLQuery .= '`' . $iKey . '` = "' . $sValue . '" AND ';
        }

        $sSQLQuery = substr($sSQLQuery, 0, -5);

        if ($this->ExecuteSQL($sSQLQuery)){
            return true;
        }
        else {
            return false;
        }
    }

    // 'Arrays' a single result
    function ArrayResult(){
        $this->aArrayedResult = mysql_fetch_assoc($this->aResult) or die (mysql_error($this->sDBLink));
        return $this->aArrayedResult;
    }

    // 'Arrays' multiple result
    function ArrayResults(){
        $this->aArrayedResults = array();
        while ($aData = mysql_fetch_assoc($this->aResult)){
            $this->aArrayedResults[] = $aData;
        }
        return $this->aArrayedResults;
    }

    // 'Arrays' multiple results with a key
    function ArrayResultsWithKey($sKey='id'){
	   if (isset($this->aArrayedResults)){
	       unset($this->aArrayedResults);
        }
        $this->aArrayedResults = array();  
        while ($aRow = mysql_fetch_assoc($this->aResult)){
            foreach($aRow as $sTheKey => $sTheValue){
                $this->aArrayedResults[$aRow[$sKey]][$sTheKey] = $sTheValue;
            }
        }
        return $this->aArrayedResults;
    }

	// Performs a 'mysql_real_escape_string' on the entire array/string
	function SecureData($aData){
		if(is_array($aData)){
			foreach($aData as $iKey=>$sVal){
				if (!is_array($aData[$iKey])){
					$aData[$iKey] = mysql_real_escape_string($aData[$iKey], $this->sDBLink);
				}
			}
		}
		else {
			$aData = mysql_real_escape_string($aData, $this->sDBLink);
		}
		return $aData;
	}
}


//
// MYSQL DEFAULT DATABASE SETTINGS - 
// This information must be provided for fallback information
//
$gc_mysql=1;  // set to one when mysql.php is included

$MYSQL_DEFAULT_HOST     = "209.239.20.7"; # "localhost";
$MYSQL_DEFAULT_DATABASE = "opus";
$MYSQL_DEFAULT_USERID   = "root";
$MYSQL_DEFAULT_PASSWORD = "casbau29";

//
// MYSQL WRITE DATABASE SETTINGS -
// For situations where you have MYSQL replication turned on and you wish to
// write to a master, enter the MASTER connection information here.
//
$MYSQL_WRITE_HOST      = "209.239.20.7";
$MYSQL_WRITE_DATABASE  = "opus";
$MYSQL_WRITE_USERID    = "root";
$MYSQL_WRITE_PASSWORD  = "casbau29";

// MYSQL READ DATABASE SETTINGS -
// For situations where you have MYSQL replication turned on and you wish to
// read from a slave, enter the SLAVE connection information here.
//
$MYSQL_READ_HOST       = "209.239.20.7";
$MYSQL_READ_DATABASE   = "opus";
$MYSQL_READ_USERID     = "root";
$MYSQL_READ_PASSWORD   = "casbau29";

$MYSQL_DEFAULT_KEEPOPEN = 0; 

$SQL              = '';
$mysql_link       = 0;
$mysql_found_rows = 0;
$mysql_returned_rows = 0;
$mysql_fields     = array();
$mysql_errcode    = 0;
$mysql_errmsg     = '';
$mysql_insert_id  = '';
$mysql_prepared_statement = '';

function _mysql_get_records($query, &$mysqlreturnresult) {
   global $MYSQL_HOST; 
   global $MYSQL_DATABASE;
   global $MYSQL_USERID;
   global $MYSQL_PASSWORD; 
   global $MYSQL_DEFAULT_HOST;
   global $MYSQL_DEFAULT_DATABASE;
   global $MYSQL_DEFAULT_USERID;
   global $MYSQL_DEFAULT_PASSWORD;

   global $mysql_link; 
   global $mysql_fields;
   global $mysql_errmsg;
   global $mysql_found_rows;
   
   $mysqlreturnresult = array();

   $host     = $MYSQL_HOST ? $MYSQL_HOST : $MYSQL_DEFAULT_HOST;
   $database = $MYSQL_DATABASE ? $MYSQL_DATABASE : $MYSQL_DEFAULT_DATABASE;
   $userid   = $MYSQL_USERID ? $MYSQL_USERID : $MYSQL_DEFAULT_USERID;
   $password = $MYSQL_PASSWORD ? $MYSQL_PASSWORD : $MYSQL_DEFAULT_PASSWORD;
   $mysql_link = mysql_connect($host, $userid, $password, true);
   $mysql_fields = array();

   mysql_select_db($database, $mysql_link);

   $mysql_result = mysql_query($query, $mysql_link); 
   $recfound = 0; 

   if ($mysql_result) {    
	   $numFields = mysql_num_fields($mysql_result);
	   while ($mysql_row = mysql_fetch_row($mysql_result)) { 
			 for ($index = 0; $index < $numFields; $index++) {
				 $mysqlreturnresult[$recfound][mysql_field_name($mysql_result, $index)] = stripslashes($mysql_row[$index]); 
			 } 
	   ++$recfound; 
	   } 
	}

	for ($index = 0; $index < $numFields; $index++) {
	   array_push($mysql_fields, mysql_field_name($mysql_result, $index));
	}    

	$mysql_errmsg = mysql_error($mysql_link);
	$mysql_result = mysql_query("SELECT FOUND_ROWS();", $mysql_link);
	if ($mysql_result) {  
	   $numFields = mysql_num_fields($mysql_result);
	   while ($mysql_row = mysql_fetch_row($mysql_result)) {
			 for ($index = 0; $index < $numFields; $index++) {
				 $mysql_found_rows = $mysql_row[0];
			 } 
	   }
	} 
	mysql_close($mysql_link);
	return($recfound);
}

function _mysql_select($query, &$mysqlreturnresult) {
   global $MYSQL_READ_HOST,    $MYSQL_READ_DATABASE,    $MYSQL_READ_USERID,    $MYSQL_READ_PASSWORD;
   global $MYSQL_DEFAULT_HOST, $MYSQL_DEFAULT_DATABASE, $MYSQL_DEFAULT_USERID, $MYSQL_DEFAULT_PASSWORD;

   global $mysql_link, $mysql_fields, $mysql_errmsg, $mysql_found_rows;

   $host     = $MYSQL_READ_HOST     ? $MYSQL_READ_HOST     : $MYSQL_DEFAULT_HOST;
   $database = $MYSQL_READ_DATABASE ? $MYSQL_READ_DATABASE : $MYSQL_DEFAULT_DATABASE;
   $userid   = $MYSQL_READ_USERID   ? $MYSQL_READ_USERID   : $MYSQL_DEFAULT_USERID;  
   $password = $MYSQL_READ_PASSWORD ? $MYSQL_READ_PASSWORD : $MYSQL_DEFAULT_PASSWORD;

   $recfound = 0;
   $mysql_link = mysql_connect($host, $userid, $password, true);
   $mysql_fields = array();
   $mysql_row = array();   
   $mysqlreturnresult = array();

   mysql_select_db($database, $mysql_link);
   $mysql_result = mysql_query($query, $mysql_link);

   if ($mysql_result) {    
	   $numFields = mysql_num_fields($mysql_result);
	   while ($mysql_row = mysql_fetch_row($mysql_result)) {
			 for ($index = 0; $index < $numFields; $index++) {
				 $mysqlreturnresult[$recfound][mysql_field_name($mysql_result, $index)] = stripslashes($mysql_row[$index]);
			 } 
	   ++$recfound;
	   } 
	}    

	for ($index = 0; $index < $numFields; $index++) {
	   array_push($mysql_fields, mysql_field_name($mysql_result, $index));
	}   

	$mysql_errmsg = mysql_error($mysql_link);
	mysql_select_db($database, $mysql_link);
	$mysql_result = mysql_query("SELECT FOUND_ROWS();", $mysql_link);

	if ($mysql_result) {  
	   $numFields = mysql_num_fields($mysql_result);
	   while ($mysql_row = mysql_fetch_row($mysql_result)) {
			 for ($index = 0; $index < $numFields; $index++) {
				 $mysql_found_rows = $mysql_row[0];
			 } 
	   }
	}   
	mysql_close($mysql_link);
	return($recfound);
}

function _mysql_do(){
   global $MYSQL_WRITE_HOST,   $MYSQL_WRITE_DATABASE,   $MYSQL_WRITE_USERID,   $MYSQL_WRITE_PASSWORD;
   global $MYSQL_DEFAULT_HOST, $MYSQL_DEFAULT_DATABASE, $MYSQL_DEFAULT_USERID, $MYSQL_DEFAULT_PASSWORD;
   global $mysql_result, $mysql_errmsg, $mysql_insert_id, $mysql_prepared_statement;

   $numargs = func_num_args();
   $arglist = func_get_args();

   $statement = array_shift($arglist);

   $host      = $MYSQL_WRITE_HOST     ? $MYSQL_WRITE_HOST     : $MYSQL_DEFAULT_HOST;
   $database  = $MYSQL_WRITE_DATABASE ? $MYSQL_WRITE_DATABASE : $MYSQL_DEFAULT_DATABASE;
   $userid    = $MYSQL_WRITE_USERID   ? $MYSQL_WRITE_USERID   : $MYSQL_DEFAULT_USERID;  
   $password  = $MYSQL_WRITE_PASSWORD ? $MYSQL_WRITE_PASSWORD : $MYSQL_DEFAULT_PASSWORD;

   $found     = 0;

   $mysql_link = mysql_connect($host, $userid, $password, true);
   mysql_select_db($database, $mysql_link);

   //
   // Check to see if the argument(s) provided is an array ...
   //
   if (is_array($arglist[0])) {
	  // So we passed an arry of values for the SQL statement
	  //
	  $arglist = $arglist[0];
	  $numargs = count($arglist) + 1;
   }

   // Check to see if the values passed is just a single array or an array of arrays ...
   //
   if (is_array($arglist[0])) {
	  // Ok - so we have a multi dimensional array for values - assume INSERT statement and go from there
	  // We are expecting the just INSERT command without the values - let's add it and start preparing the INSERT statement.
	  $statement = $statement . " VALUES ";
	  $recs = count($arglist);
	  for ($i = 0; $i < $recs; $i++) {
		  $statement = $statement . "(";
		  $innerrecs = count($arglist[$i]);
		  for ($j = 0; $j < $innerrecs; $j++) {
			 $statement = $statement . $arglist[$i][$j] = (is_null($arglist[$i][$j]) || !isset($arglist[$i][$j])) ? 'NULL' : "'" . mysql_real_escape_string($arglist[$i][$j]) . "'";
			 if ($j < $innerrecs - 1) 
			   $statement = $statement . ", ";
		  }
		  $statement = $statement . ")";
		  if ($i < $recs - 1) 
			  $statement = $statement . ", ";
	  }
	   
	  $mysql_prepared_statement = $statement;
   }
   else {
	  // 
	  // Parse the values - checking for NULL and replacing as required
	  //
	  for ($i = 0; $i < $numargs - 1; $i++) {
		  $arglist[$i] = (is_null($arglist[$i]) || !isset($arglist[$i])) ? 'NULL' : "'" . mysql_real_escape_string($arglist[$i]) . "'";
	  }
	  $mysql_prepared_statement = vsprintf($statement, $arglist);
   }

   if ($mysql_prepared_statement == '') {
	   print "Error formating statement...\n";
	   print "[$statement]\n";
	   print_r($arglist);
	   print "\n";
   }

   $mysql_result = mysql_query($mysql_prepared_statement, $mysql_link);

   $mysql_insert_id = mysql_insert_id();

   $mysql_errmsg = mysql_error($mysql_link);
   $found = mysql_affected_rows();

   mysql_close($mysql_link);

   return($found);
}

$MYSQL_LOADED = 1;  // set to one when mysql.php is included

?>
