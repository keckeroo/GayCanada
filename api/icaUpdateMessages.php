<?php  

require("$_SERVER[DOCUMENT_ROOT]/Library/authenticate.php");

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");

$success = false;
$reason  = 'Unknown reason.';
$records = array();   
$returnString = array();

if ($limit) {
   $uselimit = "LIMIT 0, $limit";
}

if ($REMOTE_ACCOUNT && $REMOTE_USER) {
   $count = _mysql_do("UPDATE MESSAGES SET 
                      Mailbox = '$folder',
                     toFolder = '$folder',
                       status = '$status'
                        WHERE MID = '$mid'
                          AND ACCOUNT = '$REMOTE_ACCOUNT'");
   $success = $count > 0;
}
else {
   $reason = 'Not authorized or not logged in. Unable to updated message status / information.';
}

// $count =  _mysql_get_records("SELECT * FROM MESSAGES WHERE Account = '$REMOTE_ACCOUNT' AND UserID = '$REMOTE_USER' ORDER BY DATE DESC $uselimit", &$dataset);

//$rc = count($mysql_fields);

//for ($j = 0; $j < $count; $j++) {
//   for ($i = 0; $i < $rc; $i++) {
//       $record[$mysql_fields[$i]] = $dataset[$j][$mysql_fields[$i]];
//   }
//   array_push($records, $record);
//}

//$success = $count > 0;
//if ($count == 0) {
//   $reason = 'No records found';
//}

$returnString['records'] = $records; 
$returnString['success'] = $success;
$returnString['reason']  = $reason;

print json_encode($returnString);

exit;

?>