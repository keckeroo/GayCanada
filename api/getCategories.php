<?php  

require("$_SERVER[DOCUMENT_ROOT]/lib/authenticate.php");

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");

$success      = false;
$reason       = '';
$apiRecords   = array();
$returnString = array();
$errormsg     = "";

$count = _mysql_get_records("SELECT * FROM cglbrd.CATEGORIES WHERE Enabled = 'Yes' ORDER BY Description_E LIMIT 0, 10", $dataset);

$rc = count($mysql_fields);

$allrecs = array ( "CategoryID" => 0,  "Category_Name" => "All Categories" , "Description_E" => "All Categories" ,  "Description_F" => "Tous Categories" );
array_push($apiRecords, $allrecs);

for ($j = 0; $j < $count; $j++) {
   for ($i = 0; $i < $rc; $i++) {
       $record[$mysql_fields[$i]] = $dataset[$j][$mysql_fields[$i]];
   }
   array_push($apiRecords, $record);
}

$success = $count > 0;
if ($count == 0) {
   $reason = 'No records found';  
}

$apiResponse['sqlInsertId']        	= $mysql_insert_id;
$apiResponse['sqlErrorCode']       	= $mysql_errcode;
$apiResponse['sqlErrorMessage']    	= $mysql_errmsg;
$apiResponse['sqlQuery']           	= $SQL;

$apiResponse['apiSuccess']         	= $apiSuccess;
$apiResponse['apiRecords']         	= $apiRecords;
$apiResponse['apiRecordsFound']    	= $mysql_found_rows;
$apiResponse['apiRecordsReturned'] 	= $count;
$apiResponse['apiErrorCode']       	= $apiErrorCode;
$apiResponse['apiErrorMessage']    	= $apiErrorMessages[$apiErrorCode];
$apiResponse['apiAction']          	= isset($cmd) ? $cmd : '';
$apiResponse['apiQueryString']     	= $_SERVER['QUERY_STRING'];

$apiResponse['limit'] 				= isset($limit) ? $limit : null;
$apiResponse['start'] 				= isset($start) ? $start : null;

//
// These values are the default values for sencha ....
//
$apiResponse['total']              = $mysql_found_rows; // Total records for query used in paging routines
$apiResponse['success']            = true;              // Did API finish without incident ?

print json_encode($apiResponse);

exit;

?>
