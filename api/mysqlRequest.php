
<?php




  /**
   * This class produces a cURL request object consisting of one or more parts. Each part is configured by passing a multi-dimensional 
   * hash array of both the header values and the body of the request.
   *  
   *     $request = new Request(array(
   *      "headers" => array(
   *        "Content-Type" => "application/json",
   *        "Accept" => "application/json"
   *      ),
   *      "postfields" => '{ "Subject" : "Test Subject", "Message" : "Test message." }'
   *    ));
   *
   * @class Request
   *
   */

	require("$_SERVER[DOCUMENT_ROOT]/lib/authenticate.php");
	
	header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
	header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
	header ("Pragma: no-cache");
	header ("Content-Type: text/html; charset=utf-8");

class mySQLRequest {

	/*
	 * The array of the parts that will be sent in the message.  Use #content to implode the parts into a valid format needed for a MIME multipart message.
	 * @property {string} contents
	 */

	private $mysql_table = "cglbrd.CITIES";
	private $mysql_recordkey = "CityID";
	private $mysql_default_order = "City";
	private $mysql_default_limit = 50;

	private $succes = false;
	private $reason = null;
	private $records = array();
	private $returnString = array();

	$cmd          = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : 'read';
	$start        = isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;
	$limit        = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : $MYSQL_DEFAULT_LIMIT;
	$searchfield  = isset($_REQUEST['searchfield']) ? $_REQUEST['searchfield'] : '';
	$searchvalue  = isset($_REQUEST['searchvalue']) ? $_REQUEST['searchvalue'] : '';
	$recordkey    = isset($_REQUEST['recordkey']) ? $_REQUEST['recordkey'] : '';

	private $start      = "";
  	private $contents       = array();
  	private $multipart      = false;
  	private $prepared       = false;

  	/**
   	 * @constructor
     *
     * Creates a new Request object.
	 *
   	 * @param {array} [config] Hash array of the header and postfield or content values. The configuration array may be omitted for requests which will not be sending information (eg GET). For detailed information about the format of the configuration array, please see {@link Request#addContent}
   	 * 
   	 * @return {object} The Request object for use by the Base class method {@link Base#makeRequest}
     */

	public function __construct($contents = null) {

		$this->addContent((array) $contents);
  	}

 	public function addContent($configs) {
		$contentid = "<" . rand() . "." . ((string) time()) . "@sencha.com>";

		$content = array(
			"headers" => array(
			"Content-Type"  => "application/json",
			"Accept"        => "application/json",
			"Content-ID"  => $contentid
	  	),
	  	"postfields" => array()
		);

		array_push($this->contents, $this->MergeArrays($content, $configs));

		if (count($this->contents) == 1) {
	  		$this->start = $contentid;
		}
  	}

	/**
	 * Return the header array from the first element of the contents array as a newly formatted array of strings made by combining the key and value 
	 * fields into single strings separated by a colon. This format is intended to be used by cURL for the 'CURLOPT_HTTPHEADER' setting.
	 *
	 * @method getHeaders
 	 *
	 * @return {array} An array of individual headers for the request in the format of "key: value"
	 *
   	 */
	public function getHeaders() {
	$this->prepareRequest();

	$headers = array();
	$contentSize = count($this->contents);

	foreach ($this->contents[0]['headers'] as $key => $value) {
	  if ($key === 'Content-ID' && $contentSize == 1) {
		continue;
	  }
	  if (isset($value) && $value <> '') {
		array_push($headers, "$key: $value");
	  }
	}
	return $headers;
  }

 	/**
     * Returns the entire body of the request.
     *
     * @method getPostfields
     *
     * @returns {string} The body of the request. If multipart, then the body will contain the boundaries, headers and contents of all parts as a single text string.
     *
     */
	public function getPostfields() {
		$this->prepareRequest();
		return $this->contents[0]["postfields"];
  	}

  /**
   * Return the contents of this request, properly formatted for delivery to an API. Once called, this method 'locks' the contents, performing any transformation to multipart as required.
   *
   * @method prepareRequest
   *
   * @hide
   */
  public function prepareRequest() {
	if (! $this->prepared) {
	  if (count($this->contents) > 1 || $this->multipart) {
		// Convert entire package to multipart mime ....

		$content    = $this->getContentsAsMultipart();
		$type     = $this->contents[0]['headers']['Content-Type'];
//        $header     = "multipart/form-data; type=\"$type\"; start=\"$this->start\"; boundary=\"$this->boundary\"";
		$header     = "multipart/related; type=\"$type\"; start=\"$this->start\"; boundary=\"$this->boundary\"";

		// Wrap mime contents in multipart header making sure to preserve any credential headers from the first element
		// and copy them to the new headers.

		$this->contents[0] = $this->MergeArrays($this->contents[0], 
		  array(
			"headers" => array(
			  "Content-Type" => $header,
			  "Content-Disposition" => null,
			  "Content-ID" => null
			),
			"postfields" => $content
		  )
		);

		array_splice($this->contents, 1); // remove all trailing elements of the array as they are now in the postfields as multipart.
	  }

	  if ($this->contents[0]['headers']['Content-Type'] === 'application/json' && ! $this->isJson($this->contents[0]['postfields'])) {
		$this->contents[0]['postfields'] = json_encode($this->contents[0]['postfields']);
	  }
	}

	$this->prepared = true;
  }


  /*
   * Merge two arrays recursively
   */
  private function MergeArrays($Arr1, $Arr2) {
	foreach($Arr2 as $key => $Value) {
	  if(array_key_exists($key, $Arr1) && is_array($Value))
		$Arr1[$key] = $this->MergeArrays($Arr1[$key], $Arr2[$key]);
	  else
		$Arr1[$key] = $Value;
	}
	return (array) $Arr1;
  }

  /*
   * Determine if input string is a properly formatted JSON object.
   * @method isJson
   */
  private function isJson($string) {
	if (is_string($string)) {
	  json_decode($string);
	  return (json_last_error() == JSON_ERROR_NONE);
	}
	return false;
  }

}

?>


if ($limit || $start) {
   $limitClause = "LIMIT $start, $limit";
}

if ($searchfield && $searchvalue) {
   $whereClause = "WHERE $searchfield = '$searchvalue'";
}

if ($recordkey) {
   $whereClause = "WHERE $MYSQL_RECORDKEY = '$recordkey'";
}

if ($sort) {
   $orderClause = "ORDER BY $sort $dir";
}
else {
   $orderClause = "ORDER BY $MYSQL_DEFAULT_ORDER";
}


switch ($cmd) {
	 
	  case 'create' :
//         $result = _mysql_do("INSERT INTO $MYSQL_TABLE 
//                                (`supplier_id`, `supplier_name`, `address_1`, `address_2`, `city`, `state`, `zipcode`, `contact_name`, `phone`, `fax`, `email`, `instructions`, 
//                                 `minimum_order`, `availability_min`, `availability_max`, `availability_unit`, `notes`, `disabled`, date_entered, last_updated)
//                                VALUES
//                                 ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', NOW(), NOW())", 
//                                 $supplierid, $suppliername, $address1, $address2, $city, $state, $zipcode, $contactname, $phone, $fax, $email, $instructions, $minimumorder, $availabilitymin,
//                                 $availabilitymax, $availabilityunit, $notes, $disabled);
//         if ($result == 1) {
//            $success = true;
//         }
		 break;
	  case 'read':
		 $SQL = "SELECT SQL_CALC_FOUND_ROWS * FROM $MYSQL_TABLE $whereClause $orderClause $limitClause"; 
		 $count = _mysql_get_records("$SQL", $result1);
		 $rc = count($mysql_fields);
		 for ($i = 0; $i < $count; $i++) {
			for ($j = 0; $j < $rc; $j++) {
				$record[$mysql_fields[$j]] = utf8_encode($result1[$i][$mysql_fields[$j]]);
			}
			array_push($records, $record);
		 }
		 if ($count == 0) {
			$reason = 'No records found';
		 }
		 $success = true;
		 break;
	  case 'update' :
		 $SQL = "UPDATE $MYSQL_TABLE SET 
				 title = '%s',
				 comments = '%s',
				 rating = '%s'
				 WHERE $MYSQL_RECORDKEY = '$recordkey'";

		 $result = _mysql_do($SQL, $_REQUEST['title'], $_REQUEST_['comments'],  $_REQUEST['rating']);

		 if ($result > 0) {
			$success = true;
		 }
		 else {
			$reason = 'Record not found';
		 }
		 break;
	  case 'delete' :
//         $result = _mysql_do("DELETE FROM $MYSQL_TABLE WHERE $MYSQL_RECORDKEY = '$recordkey'");
//         if ($result == 1) {
//            $success = true;
//         }
//         else {
//            $reason = 'This supplier is currently in use - you cannot delete this supplier.';
//         }
		 break;
	  default: 
		 $reason = 'Unknown command';
}

	$returnString['sqlinsertid'] = $mysql_insert_id;
	$returnString['sqlerror']    = $mysql_errmsg;
	$returnString['totalCount']  = $mysql_found_rows;

	$returnString['success']     = $success;
	$returnString['reason']      = $reason;
	$returnString['records']     = $records;
	$returnString['sql']         = $SQL;

	print json_encode($returnString);

	exit;

	$url 	= "$this->base_url/oauth/access_token";
	$data 	= "grant_type=authorization_code&client_id={$this->client_id}&client_secret={$this->client_secret}&code=$code";

	$request = new mySQLRequest("host", "database", "username", "password");

	return $this->executeStatement(array(
				"headers"       => array(
					"Content-Type" => "application/x-www-form-urlencoded"
				),
				"postfields"    => $data
			));

			return $this->makeRequest("POST", $url, $request);

?>