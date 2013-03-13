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

$database = 'opus';
$table    = 'ONLINE';
$enhanced = $REMOTE_PERM & 0x00008 == 0x00008;

$city     = $_REQUEST['city'];
$region   = $_REQUEST['region'];
$country  = $_REQUEST['country'];
$gender   = $_REQUEST['gender'];
$username = $_REQUEST['username'];
$status   = $_REQUEST['status'];
$SQL      = "";

$searchfield = $_REQUEST['searchfield'];
$searchtext  = $_REQUEST['searchtext'];
$searchmatch = $_REQUEST['searchmatch'] ? $_REQUEST['searchmatch'] : 'exact';

$cmd = $_REQUEST['cmd'] ? $_REQUEST['cmd'] : 'list';

switch ($cmd) {
   case 'list': 
      break;
   case 'create' :
      break;
   case 'read' : 
      break;
}

$SQL = "SELECT * FROM $database.$table WHERE 1 = 1 $sexFilter ORDER BY UserID, City";

if ($city) {
   $locationFilter = "AND ACCOUNTS.PROVINCE = '$region' AND ACCOUNTS.CITY = '$city'";
   $orderBy = "ORDER BY UserID, City";
#   $SQL = "SELECT * FROM opus.ONLINE WHERE Province = '$region' AND City = '$city' $sexFilter ORDER BY UserID,City";
}
else if ($region) {
   $locationFilter = "AND ACCOUNTS.PROVINCE = '$region'";
   $orderBy = "ORDER BY UserID, City";
#   $SQL = "SELECT * FROM opus.ONLINE WHERE Province = '$region' $sexFilter ORDER BY UserID,City";
}

//else {
//   $SQL = "SELECT * FROM opus.ONLINE ORDER BY Country,FIND_IN_SET(Province,'$region,AB,BC,MB,NB,NS,NL,ON,PE,QC,NT,NU,SK,YK'),UserID,City";
//}

$LIMIT = 500;


if ($_REQUEST['username']) {
   $SQL = "SELECT UserID, Account, City, Province, Country, Gender, floor( DATEDIFF(NOW(),Birthdate )/ 365.25 ) as Age, DateEntered , OnlineStatus, LastLogin
             FROM ACCOUNTS 
            WHERE UserID LIKE '{$_REQUEST[username]}%'
            $locationFilter
            $sexFilter
            $orderBy LIMIT 0, $LIMIT";
}
else if ($_REQUEST['onlineFilter']) {
   $SQL = "SELECT ONLINE.*, ACCOUNTS.OnlineStatus, ACCOUNTS.LastLogin FROM ONLINE LEFT JOIN ACCOUNTS ON ONLINE.UserID = ACCOUNTS.UserID
            WHERE 1 = 1
            $locationFilter
            $sexFilter
            $orderBy LIMIT 0, $LIMIT"; 
}
else if ($_REQUEST['status'] == 'New') {
   $SQL = "SELECT UserID, Account, City, Province, Country, Gender, floor( DATEDIFF(NOW(),Birthdate )/ 365.25 ) as Age, DateEntered, OnlineStatus, LastLogin
             FROM ACCOUNTS 
            WHERE 1 = 1
             $locationFilter
             $sexFilter
             AND DATEDIFF(NOW(), DateEntered) < 31
           ORDER BY DateEntered DESC LIMIT 0, $LIMIT";
}
else if ($_REQUEST['status'] == 'All') {
   $SQL = "SELECT UserID, Account, City, Province, Country, Gender, floor( DATEDIFF(NOW(),Birthdate )/ 365.25 ) as Age, DateEntered, OnlineStatus, LastLogin
             FROM ACCOUNTS 
            WHERE 1 = 1
            $locationFilter
            $sexFilter
            $orderBy LIMIT 0, $LIMIT";
}

if ($REMOTE_ACCOUNT) {
   $cmd = 'get';
   switch ($cmd) {
      case 'get': 
         $count = _mysql_get_records("$SQL", &$dataset);
         $rc = count($mysql_fields);
         for ($i = 0; $i < $count; $i++) {
            for ($j = 0; $j < $rc; $j++) {
                $record[$mysql_fields[$j]] = $dataset[$i][$mysql_fields[$j]];
            }
            array_push($apiRecords, $record);
         }
         $success = $count > 0;
         if ($count == 0) {
            $reason = 'No records found';
         }
         else {
            $returnString['apiRecords'] = $apiRecords; 
         }
$reason = 'get';
         break;
      case 'add' :
#         $result = _mysql_do("INSERT INTO $database.$table (`account`, `entryid`, `filename`, `gallery`, `height`, `width`, `size`, `enabled`) VALUES
#                             (%d, '%s', '%s', %d, %d, %d, 'Yes')", $entryid, $filename, $gallery, $height, $width, $size);
#         $success = true;
$reason = 'add';
         break;
      case 'update' :
#         $result = _mysql_do("REMOVE FROM $database.$table WHERE Account = '$REMOTE_ACCOUNT' AND mAccount = '$maccount'");
#         if ($result > 0) {
#            $success = true;
#         }
#         else {
#            $reason = 'Record not found';
#         }
$reason = 'update';
         break;
      case 'remove' :
         $result = _mysql_get_records("SELECT * FROM $database.table WHERE `entryid` = '$entryid' AND `photoid` = '$photoid'", &$photos);
         if ($result == 1) {
         }
         $success = true;
$reason = 'remove';
         break;
      default: 
         $reason = 'Unknown command';
$reason = 'default';
   }
}

$apiResponse['sqlInsertId']        = $mysql_insert_id;
$apiResponse['sqlErrorCode']       = $mysql_errcode;
$apiResponse['sqlErrorMessage']    = $mysql_errmsg;
$apiResponse['sqlQuery']           = $SQL;

$apiResponse['apiSuccess']         = $apiSuccess;
$apiResponse['apiRecords']         = $apiRecords;
$apiResponse['apiRecordsFound']    = $mysql_found_rows;
$apiResponse['apiRecordsReturned'] = $count;
$apiResponse['apiErrorCode']       = $apiErrorCode;
$apiResponse['apiErrorMessage']    = $apiErrorMessages[$apiErrorCode];
$apiResponse['apiAction']          = $cmd;
$apiResponse['apiQueryString']     = $_SERVER['QUERY_STRING'];
$apiResponse['apiUsername']        = $REMOTE_ACCOUNT;

$apiResponse['limit'] = $limit;
$apiResponse['start'] = $start;

//
// These values are the default values for sencha ....
//
$apiResponse['total']              = $mysql_found_rows; // Total records for query used in paging routines
$apiResponse['success']            = true;              // Did API finish without incident ?

print json_encode($apiResponse);

exit;

?>