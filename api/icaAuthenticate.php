<?php

require("$_SERVER[DOCUMENT_ROOT]/Library/authenticate.php");

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");

$success = false;
$reason  = '';
$records = array();   
$returnString = array();

$enhanced = $REMOTE_PERM & 0x00008 == 0x00008;

$returnString['userid'] = $_REQUEST['loginUserid'];
$returnString['password'] = $_REQUEST['loginPassword'];
$returnString['sessionid'] = session_id();
$returnString['sessionname'] = session_name();
$returnString['gcAuthFailReason'] = $gcAuthFailReason;
$returnString['authenticated'] = false;

if ($_SESSION['authenticated']) {
   $success = true;
   $returnString['authUserid'] = $REMOTE_USER;
   $returnString['authAccount'] = $REMOTE_ACCOUNT;
   $returnString['authenticated'] = $_SESSION['authenticated'];
}
else {
   if ($_REQUEST['logout']) {
      $reason = 'Logout successful.';
   }
   else {
      $reason = 'Authentication failed. Please try again.';
   }
}

$returnString['rows'] = $records;
$returnString['records'] = $records;
$returnString['success'] = $success;
$returnString['reason']  = $reason;

print json_encode($returnString);

exit;

?>