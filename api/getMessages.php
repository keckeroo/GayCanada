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
$SQL          = "";

$cmd    = $_REQUEST['cmd'] ? $_REQUEST['cmd'] : 'list';
$folder = $_REQUEST['folder'] ? $_REQUEST['folder'] : 'inbox';
$limit  = $_REQUEST['limit'];
$start  = $_REQUEST['start'];

if ($limit) {
   $uselimit = "LIMIT $start, $limit";
}


switch ($cmd) {
   case 'list' :
      $SQL = "SELECT SQL_CALC_FOUND_ROWS `mid`, `fromuser`, `subject`, `date`, `mailbox`, `message`, `status` 
                FROM MESSAGES 
               WHERE Account = '$REMOTE_ACCOUNT' 
                 AND UserID = '$REMOTE_USER' 
                 AND MAILBOX = '$folder'
               ORDER BY DATE DESC $uselimit";
      break;
   case 'read' :
      $SQL = "SELECT `mid`, `fromuser`, `subject`, `date`, `mailbox`, `message`, `status` FROM MESSAGES 
               WHERE Account = '$REMOTE_ACCOUNT' 
                 AND UserID = '$REMOTE_USER' 
                 AND MID = '$_REQUEST[recordkey]'";
      break;
}

if ($SQL) {
    $count = _mysql_get_records($SQL, &$dataset);

    $rc = count($mysql_fields);

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
    else {
       $returnString['records'] = $records; 
    }
}

if ($cmd == 'update') {
   $result = _mysql_do("UPDATE MESSAGES SET mailbox = '%s' WHERE MID = '$mid' AND ACCOUNT = '$REMOTE_ACCOUNT' and UserID = '$REMOTE_USER'", $folder);
   if ($result != 1) {
      $reason = 'Unable to update message.';
      $success = false;
   }
}

if ($cmd == 'delete') {
   $count = _mysql_do("DELETE FROM MESSAGES WHERE Account = $REMOTE_ACCOUNT' AND UserID = '$REMOTE_USER' AND MID = '$mid");
   $success = $count > 0;
   if ($count == 0) {
   }
   else {
      $reason = 'Message not found.';
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
$apiResponse['apiSession']         = session_id();
$apiResponse['apiUsername']        = $REMOTE_USERID;

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