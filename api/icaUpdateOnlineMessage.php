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

if (!$REMOTE_ACCOUNT) {
   exit;
}

$result = _mysql_do("UPDATE ACCOUNTS SET OnlineMessage = '$onlinemessage', OnlineMessageUpdated = NOW()
                      WHERE Account = '$REMOTE_ACCOUNT' AND UserID = '$REMOTE_USER'");


if ($result >= 0 ) {
   print "{success: true }\n";
}
else {
   print "{success: false, errors: { reason: 'Unable to update account information ($mysql_errmsg).  ' }}\n";
}

?>