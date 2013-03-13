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

$database = 'opus';
$table    = 'ACCOUNTS';

gatekeeper($TT_USERID_OPTIONAL);

if ($REMOTE_ACCOUNT) {
   $result = _mysql_get_records("SELECT PRID FROM PROFILES WHERE ACCOUNT = '$REMOTE_ACCOUNT' AND USERID = '$REMOTE_USER'", &$prid);
   $prid = $prid[0]['PRID'];

   $count = _mysql_get_records("SELECT * FROM PROFILES_LOGS WHERE PRID = '$prid' ORDER BY Date DESC", &$dataset);

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
   else {
      $returnString['records'] = $records; 
   }
}

$returnString['totalcount'] = $count;
$returnString['success'] = $success;
$returnString['reason']  = $reason;

print json_encode($returnString);

exit;

?>