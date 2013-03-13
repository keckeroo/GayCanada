<?php
//
// This file authenticates the user for the login screen. If the user is already logged
// in, the database lookup is bypassed and authentication creditials are automatically
// populated into the result string allowing the login screen to be bypassed when the app
// is launched. Otherwise, authentication information is not available to be supplied to
// the application and, if required a login screen should be displayed.
//

require("$_SERVER[DOCUMENT_ROOT]/lib/authenticate.php"); // include the core authentication script

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");

$REMOTE_PROFILENAME  		= @$_REQUEST['REMOTE_PROFILENAME']; // isset($_REQUEST['REMOTE_PROFILENAME']) ? $_REQUEST['REMOTE_PROFILENAME'] : null;
$REMOTE_PROFILEID    		= isset($_REQUEST['REMOTE_PROFILEID']) ? $_REQUEST['REMOTE_PROFILEID'] : null;
$REMOTE_ENHANCED     		= isset($_REQUEST['REMOTE_ENHANCED']) ? $_REQUEST['REMOTE_ENHANCED'] : null;
$REMOTE_AUTHENTICATED 		= isset($_REQUEST['REMOTE_AUTHENTICATED']) ? $_REQUEST['REMOTE_AUTHENTICATED'] : null;


$apiResponse['profilename']   = isset($_REQUEST[FORM_USERNAMEFIELD]) ? $_REQUEST[FORM_USERNAMEFIELD] : null;
$apiResponse['password']      = isset($_REQUEST[FORM_PASSWORDFIELD]) ? $_REQUEST[FORM_PASSWORDFIELD] : null;
$apiResponse['sessionid']     = session_id();
$apiResponse['sessionname']   = session_name();
$apiResponse['authenticated'] = false;

if ($_SESSION['authenticated']) {
   $apiSuccess = true;
   $apiResponse['authenticated'] = true;
   $apiResponse['userInfo']['profilename']   = $REMOTE_PROFILENAME;
   $apiResponse['userInfo']['profileid']     = $REMOTE_PROFILEID;
   $apiResponse['userInfo']['enhanced']      = $REMOTE_ENHANCED;
   $apiResponse['userInfo']['authenticated'] = $REMOTE_AUTHENTICATED;
   $apiResponse['userInfo']['sessionid']     = session_id();
   $apiResponse['userInfo']['sessionname']   = session_name();
}
else {
   if (isset($_REQUEST['logout'])) {
	  $apiSuccess = true;
	  $reason = 'Logout successful.';
   }
}

$apiResponse['sqlInsertId']        = $mysql_insert_id;
$apiResponse['sqlErrorCode']       = $mysql_errcode;
$apiResponse['sqlErrorMessage']    = $mysql_errmsg;
$apiResponse['sqlQuery']           = $SQL;

$apiResponse['apiSuccess']         = $apiSuccess;
$apiResponse['apiRecords']         = $apiRecords;
$apiResponse['apiRecordsFound']    = $mysql_found_rows;
$apiResponse['apiRecordsReturned'] = isset($count) ? $count : 0;
$apiResponse['apiErrorCode']       = $apiErrorCode;
$apiResponse['apiErrorMessage']    = $apiErrorMessages[$apiErrorCode];
$apiResponse['apiAction']          = isset($cmd) ? $cmd : '';

$apiResponse['total']              = $mysql_found_rows; // Total records for query used in paging routines
$apiResponse['success']            = $apiSuccess;       // Did API finish without incident ?

print json_encode($apiResponse);
exit;

// "profilename":"keck",
// "password":"towerroad",
// "sessionid":"ku367otkicppcgplrpnq6e5qk5",
// "sessionname":"PHPSESSID",
// "authenticated":true,
// "authprofilename":"keck",
// "authprofileid":"226650",
// "authenhanced":"0",
// "sqlInsertId":0,"sqlErrorCode":null,"sqlErrorMessage":"","sqlQuery":null,"apiSuccess":true,"apiRecords":[],"apiRecordsFound":"1","apiRecordsReturned":null,"apiErrorCode":0,"apiErrorMessage":"Success","apiAction":null,"total":"1","success":true 

?>