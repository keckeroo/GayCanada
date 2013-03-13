<?php  

require("$_SERVER[DOCUMENT_ROOT]/lib/authenticate.php");

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");

$count = 0;

//gatekeeper($TT_USERID_OPTIONAL);


$apiSuccess   = false;
$reason       = '';
$apiRecords   = array();
$returnString = array();
$errormsg     = "";

$cmd         = $_REQUEST['cmd']        ? $_REQUEST['cmd'] : 'read';
$start       = $_REQUEST['start']      ? $_REQUEST['start'] : 0;
$limit       = $_REQUEST['limit']      ? $_REQUEST['limit'] : $MYSQL_DEFAULT_LIMIT;

$display = $_REQUEST['display'] ? $_REQUEST['display'] : 'profile';
$userid  = $_REQUEST['userid']  ? $_REQUEST['userid'] : $REMOTE_USER;
$userid  = $_REQUEST['username'] ? $_REQUEST['username'] : $userid;
$gallery = $_REQUEST['gallery'] ? $_REQUEST['gallery'] : 'Public';

if ($REMOTE_ID != $userid || $display == 'profile') {
//   $restrict_disabled = "AND `enabled` = 'Yes'";
}

$adult = $adult || $_REQUEST['gallery'] == 'Adult' ? 1 : 0;

if ($adult) {
  if (!$REMOTE_ACCOUNT) {
      $result['errors']['errorcode'] = 10;
      $result['errors']['reason'] = "You must be logged into GayCanada to access this gallery.";
   }
   else if ($REMOTE_PERM & $GC_X_RATED > 0) {
      $result['errors']['errorcode'] = 11;
      $result['errors']['reason'] = "You must be a platinum member to access the adult area of this profile.";
   }
}

if ($result['errors']['errorcode'] == 0) {
   $count = _mysql_get_records("SELECT PICTURES.* FROM ACCOUNTS,PICTURES
                                    WHERE ACCOUNTS.userid = '$userid' 
                                      AND PICTURES.Account = ACCOUNTS.Account
                                      AND ADULT = $adult
                                      AND Enabled = 'Yes'
                                      AND PICTURES.SystemStatus IN ('Active','Modified','Pending')
                                    ORDER BY PICTURES.ID",&$dataset);

   $rc = count($mysql_fields);
   $result['success'] = true;

   for ($j = 0; $j < $count; $j++) {
      for ($i = 0; $i < $rc; $i++) {
          $record[$mysql_fields[$i]] = $dataset[$j][$mysql_fields[$i]];
      }
      array_push($apiRecords, $record);
   }

   if ($count == 0) {
      $reason = 'No records found';
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