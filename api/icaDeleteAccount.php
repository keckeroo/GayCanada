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

if ($limit) {
   $uselimit = "LIMIT 0, $limit";
}

gatekeeper($TT_USERID_OPTIONAL);

$uselimit = "LIMIT 0, 10";

//$REMOTE_PERM & $GC_ENHANCED ? "2" : "1";

if ($REMOTE_USER && $REMOTE_ACCOUNT) {
   //
   // Request account delete ...
   //
   $result = _mysql_do("UPDATE ACCOUNTS SET 
                           Messages = '101 Pending Account Close: Member Request',
                           MessageBy = 'EPL',
                           MessageDate = NOW(),
                           SystemStatus = 'Inactive'
                           WHERE ACCOUNT = '$REMOTE_ACCOUNT'");

   $result = _mysql_do("UPDATE PERSONALS SET SystemStatus = 'Inactive' WHERE ACCOUNT = '$REMOTE_ACCOUNT'");
   $result = _mysql_do("UPDATE PROFILES  SET SystemStatus = 'Inactive' WHERE ACCOUNT = '$REMOTE_ACCOUNT'");

}

$rc = count($mysql_fields);

for ($j = 0; $j < $count; $j++) {
   for ($i = 0; $i < $rc; $i++) {
       $record[$mysql_fields[$i]] = $dataset[$j][$mysql_fields[$i]];
   }
   array_push($records, $record);
}

$success = $count > 0;
if ($count == 0) {
   $reason = 'No records found';
}

$returnString['records'] = $records; 
$returnString['success'] = $success;
$returnString['reason']  = $reason;

print json_encode($returnString);

exit;

?>