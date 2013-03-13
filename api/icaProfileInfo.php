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

$cmd = $cmd ? $cmd : 'get';

//gatekeeper($TT_USERID_OPTIONAL);

if ($REMOTE_ACCOUNT) {
   $fields = array('account','userid','firstname');

   switch ($cmd) {
      case 'get': 
         $count = _mysql_get_records("SELECT * FROM PROFILES WHERE UserID = '$REMOTE_USER' AND ACCOUNT = '$REMOTE_ACCOUNT'", &$account_info);
         if ($count == 0) {
            _mysql_do("INSERT INTO PROFILES (`account`, `userid`) VALUES ('$REMOTE_ACCOUNT', '$REMOTE_USER')");
            $count = _mysql_get_records("SELECT * FROM PROFILES WHERE UserID = '$REMOTE_USER' AND ACCOUNT = '$REMOTE_ACCOUNT'", &$account_info);
         }         
         $rc = count($mysql_fields);
         for ($i = 0; $i < $rc; $i++) {
             $record[$mysql_fields[$i]] = $account_info[0][$mysql_fields[$i]];
         }

         array_push($records, $record);
         $success = $count > 0;
         if ($count == 0) {
            $reason = 'No records found';
         }
         else {
            $returnString['records'] = $records; 
         }
         break;
      case 'add' :
#         $result = _mysql_do("INSERT INTO $database.$table (`account`, `entryid`, `filename`, `gallery`, `height`, `width`, `size`, `enabled`) VALUES
#                             (%d, '%s', '%s', %d, %d, %d, 'Yes')", $entryid, $filename, $gallery, $height, $width, $size);
#         $success = true;
         break;
      case 'update' :
         $result = _mysql_do("UPDATE PROFILES SET
             height = '%s',
             weight = '%s',
             haircolour = '%s',
             eyecolour = '%s',
             ethnicity = '%s',
             smoke = '%s',
             drink = '%s',
             orientation = '%s',
             maritalstatus = '%s',
             datemodified = now()
             WHERE ACCOUNT = '$REMOTE_ACCOUNT' AND USERID = '$REMOTE_USER'", 
             $height, $weight, $haircolour, $eyecolour, $ethnicity, $smoke, $drink, $orientation, $maritalstatus);

         if ($result > 0) {
            $success = true;
         }
         else {
            $reason = 'Record not found';
         }
         break;
      case 'remove' :
         $result = _mysql_get_records("SELECT * FROM $database.table WHERE `entryid` = '$entryid' AND `photoid` = '$photoid'", &$photos);
         if ($result == 1) {
         }
         $success = true;
         break;
      default: 
         $reason = 'Unknown command';
   }
}

$returnString['success'] = $success;
$returnString['reason']  = $reason;
$returnString['userid'] = $REMOTE_USER;
$returnString['account'] = $REMOTE_ACCOUNT;

print json_encode($returnString);

exit;

?>