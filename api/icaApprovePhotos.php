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

$action = $_REQUEST['action'] ? $_REQUEST['action'] : 'read';

if ($REMOTE_USERID || $action == 'read') {
   switch ($action) {
      case 'read': 
         $SQL = "SELECT PICTURES.*, ACCOUNTS.Manager 
                   FROM PICTURES, ACCOUNTS 
                  WHERE PICTURES.Account = ACCOUNTS.Account
                    AND PICTURES.SystemStatus IN ('Pending','Modified','Referred')
               ORDER BY ACCOUNTS.Manager ASC, PICTURES.Date";
         $count =  _mysql_get_records($SQL, &$dataset);
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
         break;
      case 'create' :
      case 'update' :
         //
         // see if record exists for this user ....
         //
         $theiruserid = $_REQUEST['theiruserid'];
         if ($REMOTE_ACCOUNT && $REMOTE_USERID && $theiruserid && $roster && $rostervalue) {
            $rosterfound = _mysql_do("SELECT * FROM ROSTER WHERE ACCOUNT = '$REMOTE_ACCOUNT' AND theiruserid = '$theiruserid'", &$rosterrecord);
            if ($rosterfound) {
               // We're doing an update
               $result = _mysql_do("UPDATE ROSTER SET $roster = $rostervalue, updated = NOW() WHERE ACCOUNT = '$REMOTE_ACCOUNT' AND USERID = '$REMOTE_USERID' AND theiruserid = '$theiruserid'");
            }
            else {
               $result = _mysql_do("INSERT INTO ROSTER (`account`, `userid`, `theiruserid`, `$roster`, `updated`) VALUES ('$REMOTE_ACCOUNT', '$REMOTE_USERID', '$theiruserid', $rostervalue, NOW())");
               $reason = $mysql_errmsg;
            }
            $success = ($result != 0);
         }
         else {
            $reason = 'Missing required values.';
         }
         break;
      case 'delete' :    
         break;
   }
}
else {
   $reason = 'You must be logged in to use this feature.';
}

$returnString['mysqlerrtext'] = $mysql_errmsg;
$returnString['sql'] = $SQL;
$returnString['theiruserid'] = $theiruserid;
$returnString['roster'] = $roster;
$returnString['rostervalue'] = $rostervalue;
$returnString['totalCount'] = $count;
$returnString['records'] = $records; 
$returnString['result']  = $result;
$returnString['success'] = $success;
$returnString['reason']  = $reason;
$returnString['perm']    = $REMOTE_PERM;
$returnString['userid']  = $REMOTE_USERID;
$returnString['account'] = $REMOTE_ACCOUNT;

print json_encode($returnString);

exit;

?>