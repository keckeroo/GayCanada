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

class MySQLResult {
	private $numRowsFound = 0;
	private $numRowsReturned = 0;
	private $rowsFound = array();

	private $error = false;
	private $errorCode;
	private $errorMessage;

	private $insertId;
	private $preparedStatement;

	private $response;
	private $rawResponse;

	private $success = false;

	/**
	 * @constructor
	 * Creates and parses a CURL response.
	 *
	 *		$curl_response = curl_exec($curl);
	 *		$curl_info = curl_getinfo($curl);
	 *
	 *  	$response = new Response(array(
	 *			"curl_info"     => $curl_info,
	 *			"curl_response" => $curl_response
	 *		));
	 *
	 * @param {array} config response parts
	 * @param {object} config.curl_info The link to the cURL info
	 * @param {object} config.curl_response The full cURL response.
	 *
	 */
	public function __construct($result, $link) {
		$recfound = 0;
		$mysql_fields = array();
		
		if ($result) {
			$this->numFields = mysql_num_fields($result);
			while ($mysql_row = mysql_fetch_row($result)) { 
				for ($index = 0; $index < $this->numFields; $index++) {
					$mysqlreturnresult[$recfound][mysql_field_name($result, $index)] = stripslashes($mysql_row[$index]); 
				} 
				++$recfound; 
			} 
		}

		for ($index = 0; $index < $this->numFields; $index++) {
			array_push($mysql_fields, mysql_field_name($result, $index));
		}    

		$mysql_errmsg = mysql_error($link);
		$mysql_result = mysql_query("SELECT FOUND_ROWS();", $link);
		if ($mysql_result) {  
			$numFields = mysql_num_fields($mysql_result);
			while ($mysql_row = mysql_fetch_row($mysql_result)) {
				for ($index = 0; $index < $numFields; $index++) {
					$mysql_found_rows = $mysql_row[0];
				} 
			}
		} 

	}

	/**
	 * Retrieve HTTP response code from returned from the API request.
	 *
	 * @method getHttpCode
	 *
	 * @return {integer} The HTTP response code returned from the request.
	 *
	 */
	public function getNumRowsReturned() {
		return 10;
	}

	/**
	 * Used to see whether or not the API response returned an error.
	 * @method isError
	 * @return {boolean} true | false
	 */
	public function isError() {
		return $this->error ? true : false;
	}

	/**
	 * Retrieves any error message returned from the API request. Empty if the request was successful. 
	 * @method getErrorMessage
	 * @return {string} The error message returned from the request
	 */
	public function getErrorMessage() {
		return $this->errorMessage;
	}
	/**
	 * Retrieves the HTTP error response code returned from the API request.
	 * @method getErrorCode
	 * @return {integer} The HTTP response code returned from the request. This method returns the same value as getHttpCode and is provided as a convenience function for getting HTTP response codes which are 400 and above.
	 */
	public function getErrorCode() {
	   return $this->errorCode;
	}
	
	/**
	 * Returns the error message, if any was found.
	 * @method error 
	 * @deprecated Please use {@link Response#getErrorMessage} instead.
	 */
	public function error() {
		return  $this->error;
	}

	/**
	 * Returns the decoded data from the server (assuming there was no exception)
	 * @method data
	 * @deprecated Please use {@link Reponse#getRawResponse} or {@link Reponse#getParsedResponse}
	 */
	public function data() {
		return $this->response;
	}

	/**
	 * Retreives the HTTP headers returned from the API request.
	 * @method getHeaders
	 * @return {array} Returns an array of key/value pairs of the headers returned from the API request.
	 */
	public function getHeaders() {
		return $this->headers;
	}

	/**
	 * Retreives the response body without any preprocessing.
	 * @method getRawResponse
	 * @return {string} Returns the body of the response received from the API as is and unprocessed.
	 */
	public function getRawResponse() {
		return $this->rawResponse;
	}

	/**
	 * Attempts to parse the response returned from request and returns it as a JSON or XML object. If the response cannot be parsed, the response is returned as is.
	 * @method getParsedResponse
	 * @return {mixed} JSON or XML object, or original contents of the response if it cannot be parsed.
	 */
	public function getParsedResponse() {
		return $this->response;
	}

	/**
	 * Parses the data passed in.
	 * It first tries json_decode and if it fails tries simplexml_load_string.
	 * If both fail, the original value passed in is returned.
	 *
	 * @method parse_response 
	 * @param {string} response the CURL response body
	 * @return {string} parsed JSON, XML or original contents of response.
	 *
	 * @method parse_response
	 * @hide
	 */
	private function parse_response($body) {
		$isParsed = false;

		$parsed = json_decode($body);

		// If parsing as JSON failed, try parsing as XML...
		if (is_null($parsed)) {
			libxml_use_internal_errors(true);
//			$parsed = simplexml_load_string("<xml>$body</xml>");
			$parsed = simplexml_load_string($body);
			if ($parsed) {
				$parsed = json_decode(json_encode($parsed));
				$isParsed = true;
			}
		}
		else {
			$isParsed = true;
		}

//		if (! $isParsed) {
//			error_log("Non JSON/XML response returned - returning response contents verbatim.");
//		}
		return $isParsed ? $parsed : $body;
	}
}

class MySQL {
	// Base variables
	private $sLastError;		// Holds the last error
	private $sLastQuery;		// Holds the last query
	private $aResult;			// Holds the MySQL query result
	private $iRecords;			// Holds the total number of records returned
	private $iAffected;			// Holds the total number of records affected
	private $aRawResults;		// Holds raw 'arrayed' results
	private $aArrayedResult;	// Holds a single 'arrayed' result
	private $aArrayedResults;	// Holds multiple 'arrayed' results (usually with a set key)

	private $sHostname;			// MySQL Hostname
	private $sUsername;			// MySQL Username
	private $sPassword;			// MySQL Password
	private $sDatabase;			// MySQL Database

	private $start;
	private $limit;
	private $searchfield;
	private $searchvalue;
	private $sort;
	private $recordkey;
	private $numFields = 0;

	private $cmd = 'read';

	var $sDBLink;			// Database Connection Link

	// Class Constructor
	// Assigning values to variables

	public function __construct($config = array()) {
		$default = array(
			'hostname' => 'localhost',
			'username' => 'root',
			'password' => '',
			'database' => ''
		);

		$config = array_merge($default,$config);

		$this->sHostname = $config['hostname'];
		$this->sUsername = $config['username'];
		$this->sPassword = $config['password'];

		if ($config['database']) {
			$this->sDatabase = $config['database'];
			if ($this->connect()) {
				$this->selectDb($this->sDatabase, $this->sDBLink);
			}
		}		
	}

	// Connects class to database
	// $bPersistant (boolean) - Use persistant connection?
	function connect($bPersistant = false){
		if($this->sDBLink){
			mysql_close($this->sDBLink);
		}

		if($bPersistant){
			$this->sDBLink = mysql_pconnect($this->sHostname, $this->sUsername, $this->sPassword, true);
		}else{
			$this->sDBLink = mysql_connect($this->sHostname, $this->sUsername, $this->sPassword, true);
		}

		if (!$this->sDBLink){
   			$this->sLastError = 'Could not connect to server: ' . mysql_error($this->sDBLink);
			return false;
		}

		if(!$this->selectDb()){
			$this->sLastError = 'Could not connect to database: ' . mysql_error($this->sDBLink);
			return false;
		}
		return true;
	}

	// Select database to use
	function selectDb(){
		if (!mysql_select_db($this->sDatabase, $this->sDBLink)) {
			$this->sLastError ='Cannot select database: ' . mysql_error($this->sDBLink);
			return false;
		}else{
			return true;
		}
	}

	// Executes MySQL query
	function ExecuteSQL($sSQLQuery){
		$recfound = 0;
		$mysqlfields = array();

		$this->sLastQuery 	= $sSQLQuery;

		if ($this->aResult 		= mysql_query($sSQLQuery, $this->sDBLink)){
			$this->iRecords 	= @mysql_num_rows($this->aResult);
			$this->iAffected	= @mysql_affected_rows($this->sDBLink);

			return new MySQLResult($this->aResult, $this->sDBLink);

//			mysql_close($this->sDBLink);
//			return($recfound);
//			return true;
		}else{
			$this->sLastError = mysql_error($this->sDBLink);
			return false;
		}
	}

	// Adds a record to the database
	// based on the array key names
	function Insert($aVars, $sTable, $aExclude = ''){
		// Catch Exceptions
		if($aExclude == ''){
			$aExclude = array();
		}

		array_push($aExclude, 'MAX_FILE_SIZE');

		// Prepare Variables
		$aVars = $this->SecureData($aVars);

		$sSQLQuery = 'INSERT INTO `' . $sTable . '` SET ';
		foreach($aVars as $iKey=>$sValue){
			if(in_array($iKey, $aExclude)){
				continue;
			}
			$sSQLQuery .= '`' . $iKey . '` = "' . $sValue . '", ';
		}

		$sSQLQuery = substr($sSQLQuery, 0, -2);

		if($this->ExecuteSQL($sSQLQuery)){
			return true;
		}else{
			return false;
		}
	}

	// Deletes a record from the database
	function Delete($sTable, $aWhere='', $sLimit='', $bLike=false){
		$sSQLQuery = 'DELETE FROM `' . $sTable . '` WHERE ';
		if(is_array($aWhere) && $aWhere != ''){
			// Prepare Variables
			$aWhere = $this->SecureData($aWhere);

			foreach($aWhere as $iKey=>$sValue){
				if($bLike){
					$sSQLQuery .= '`' . $iKey . '` LIKE "%' . $sValue . '%" AND ';
				}else{
					$sSQLQuery .= '`' . $iKey . '` = "' . $sValue . '" AND ';
				}
			}

			$sSQLQuery = substr($sSQLQuery, 0, -5);
		}

		if($sLimit != ''){
			$sSQLQuery .= ' LIMIT ' .$sLimit;
		}

		if($this->ExecuteSQL($sSQLQuery)){
			return true;
		}else{
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

		if(is_array($aWhere) && $aWhere != ''){
			// Prepare Variables
			$aWhere = $this->SecureData($aWhere);

			foreach($aWhere as $iKey=>$sValue){
				if($bLike){
					$sSQLQuery .= '`' . $iKey . '` LIKE "%' . $sValue . '%" ' . $sOperand . ' ';
				}else{
					$sSQLQuery .= '`' . $iKey . '` = "' . $sValue . '" ' . $sOperand . ' ';
				}
			}

			$sSQLQuery = substr($sSQLQuery, 0, -5);

		}else{
			$sSQLQuery = substr($sSQLQuery, 0, -7);
		}

		if($sOrderBy != ''){
			$sSQLQuery .= ' ORDER BY ' .$sOrderBy;
		}

		if($sLimit != ''){
			$sSQLQuery .= ' LIMIT ' .$sLimit;
		}

		if($this->ExecuteSQL($sSQLQuery)){
			if($this->iRecords > 0){
				$this->ArrayResults();
			}
			return true;
		}else{
			return false;
		}

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

		$aSet 	= $this->SecureData($aSet);
		$aWhere = $this->SecureData($aWhere);

		// SET

		$sSQLQuery = 'UPDATE `' . $sTable . '` SET ';

		foreach($aSet as $iKey=>$sValue){
			if(in_array($iKey, $aExclude)){
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

		if($this->ExecuteSQL($sSQLQuery)){
			return true;
		}else{
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
		if(isset($this->aArrayedResults)){
			unset($this->aArrayedResults);
		}
		$this->aArrayedResults = array();
		while($aRow = mysql_fetch_assoc($this->aResult)){
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
				if(!is_array($aData[$iKey])){
					$aData[$iKey] = mysql_real_escape_string($aData[$iKey], $this->sDBLink);
				}
			}
		}else{
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
   $numFields = 0;

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
   if (@is_array($arglist[0])) {
	  // So we passed an arry of values for the SQL statement
	  //
	  $arglist = $arglist[0];
	  $numargs = count($arglist) + 1;
   }

   // Check to see if the values passed is just a single array or an array of arrays ...
   //
   if (@is_array($arglist[0])) {
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
