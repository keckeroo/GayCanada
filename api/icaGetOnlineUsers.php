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

//         $_SESSION['address']       = $_SERVER['REMOTE_ADDR'];
$returnString['host'] = $_SERVER['HTTP_HOST'];
//       = $_SERVER['HTTP_USER_AGENT'];

$database = 'opus';
$table    = 'ONLINE';

// gatekeeper($TT_USERID_OPTIONAL);

$enhanced = $REMOTE_PERM & 0x00008 == 0x00008;

$records = array();
$city    = $city ? $city : '';
$region  = $region ? $region : '';
$country = $country ? $country : 'CA';
$gender  = $gender ? $gender : '';

if ($enhanced) {
   $locationFilter = $locationFilter ? $locationFilter : 'city';
//   $genderFilter   = $genderFilter ? $genderFilter: 'all';
}

if ($gender) {
   $genderFilter = "AND GENDER = '$gender'";
}

$SQL = "SELECT * FROM $database.$table WHERE 1 = 1 $genderFilter ORDER BY UserID, City";

if ($city) {
   $SQL = "SELECT * FROM opus.ONLINE WHERE Province = '$region' AND City = '$city' $genderFilter ORDER BY UserID,City";
}
elseif ($region) {
   $SQL = "SELECT * FROM opus.ONLINE WHERE Province = '$region' $genderFilter ORDER BY UserID,City";
}

//else {
//   $SQL = "SELECT * FROM opus.ONLINE ORDER BY Country,FIND_IN_SET(Province,'$region,AB,BC,MB,NB,NS,NL,ON,PE,QC,NT,NU,SK,YK'),UserID,City";
//}

$returnString['remoteAccount'] = $REMOTE_ACCOUNT;

if ($REMOTE_ACCOUNT || 1) {
   $cmd = 'get';
   switch ($cmd) {
      case 'get': 
         $count = _mysql_get_records("$SQL", &$result1);
         $rc = count($mysql_fields);
         for ($i = 0; $i < $count; $i++) {
            for ($j = 0; $j < $rc; $j++) {
                $record[$mysql_fields[$j]] = $result1[$i][$mysql_fields[$j]];
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
         break;
      case 'add' :
#         $result = _mysql_do("INSERT INTO $database.$table (`account`, `entryid`, `filename`, `gallery`, `height`, `width`, `size`, `enabled`) VALUES
#                             (%d, '%s', '%s', %d, %d, %d, 'Yes')", $entryid, $filename, $gallery, $height, $width, $size);
#         $success = true;
         break;
      case 'update' :
#         $result = _mysql_do("REMOVE FROM $database.$table WHERE Account = '$REMOTE_ACCOUNT' AND mAccount = '$maccount'");
#         if ($result > 0) {
#            $success = true;
#         }
#         else {
#            $reason = 'Record not found';
#         }
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

#$returnString['total'] = $count;
$returnString['rows'] = $records;
$returnString['records'] = $records;
$returnString['success'] = $success;
$returnString['reason']  = $reason;
$returnString['sql'] = $SQL;

print json_encode($returnString);

exit;

?>