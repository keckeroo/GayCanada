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

gatekeeper($TT_USERID_OPTIONAL);

//$REMOTE_PERM & $GC_ENHANCED ? "2" : "1";

if ($REMOTE_USER) {
   $count =  _mysql_get_records("SELECT PROFILES_LOGS.*, COUNT(*) AS Counts, MAX(Date) AS Maxdate
                                   FROM PROFILES_LOGS LEFT JOIN PROFILES
                                  ON PROFILES_LOGS.PRID = PROFILES.PRID
                                  WHERE PROFILES.USERID = '$REMOTE_USER'
                                    AND PROFILES_LOGS.UserID <> '$REMOTE_USER'
                                    AND PROFILES_LOGS.UserID <> ''
                                  GROUP BY PROFILES_LOGS.UserID 
                                  ORDER BY Maxdate DESC
                                  LIMIT 0, 50", &$dataset);
}
else {
  $reason = 'You must be logged in to use this feature.';
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
$returnString['perm']    = $REMOTE_PERM;
$returnString['user']    = $REMOTE_USER;

print json_encode($returnString);

exit;

?>