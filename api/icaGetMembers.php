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
$table    = 'ONLINE';

gatekeeper($TT_USERID_OPTIONAL);

$enhanced = $REMOTE_PERM & 0x00008 == 0x00008;

$city    = $city    ? $city : '';
$region  = $region  ? $region : '';
$country = $country ? $country : 'CA';
$gender  = $gender  ? $gender : '';
$status  = $status  ? $status : 'online';

if ($enhanced) {
//   $locationFilter = $locationFilter ? $locationFilter : 'city';
//   $genderFilter   = $genderFilter ? $genderFilter: 'all';
}

if ($sex) {
   $sexFilter = "AND ACCOUNTS.GENDER = '$sex'";
}

if ($status == 'online') {
   $statusFilter = "AND ONLINE.Account IS NOT NULL";
}


$SQL = "SELECT * FROM $database.$table WHERE 1 = 1 $sexFilter ORDER BY UserID, City";

if ($city) {
   $locationFilter = "AND ACCOUNTS.PROVINCE = '$region' AND ACCOUNTS.CITY = '$city'";
   $orderBy = "ORDER BY UserID, City";
#   $SQL = "SELECT * FROM opus.ONLINE WHERE Province = '$region' AND City = '$city' $sexFilter ORDER BY UserID,City";
}
else if ($region) {
   $locationFilter = "AND ACCOUNTS.PROVINCE = '$region'";
   $orderBy = "ORDER BY UserID, City";
#   $SQL = "SELECT * FROM opus.ONLINE WHERE Province = '$region' $sexFilter ORDER BY UserID,City";
}

//else {
//   $SQL = "SELECT * FROM opus.ONLINE ORDER BY Country,FIND_IN_SET(Province,'$region,AB,BC,MB,NB,NS,NL,ON,PE,QC,NT,NU,SK,YK'),UserID,City";
//}

$LIMIT = 500;


if ($username) {
   $SQL = "SELECT UserID, Account, City, Province, Country, Gender, floor( DATEDIFF(NOW(),Birthdate )/ 365.25 ) as Age, DateEntered , OnlineStatus, LastLogin
             FROM ACCOUNTS 
            WHERE UserID LIKE '$username%'
            $locationFilter
            $sexFilter
            $orderBy LIMIT 0, $LIMIT";
}
else if ($status == 'Online') {
   $SQL = "SELECT ONLINE.*, ACCOUNTS.OnlineStatus, ACCOUNTS.LastLogin FROM ONLINE LEFT JOIN ACCOUNTS ON ONLINE.UserID = ACCOUNTS.UserID
            WHERE 1 = 1
            $locationFilter
            $sexFilter
            $orderBy LIMIT 0, $LIMIT"; 
}
else if ($status == 'New') {
   $SQL = "SELECT UserID, Account, City, Province, Country, Gender, floor( DATEDIFF(NOW(),Birthdate )/ 365.25 ) as Age, DateEntered, OnlineStatus, LastLogin
             FROM ACCOUNTS 
            WHERE 1 = 1
             $locationFilter
             $sexFilter
             AND DATEDIFF(NOW(), DateEntered) < 31
           ORDER BY DateEntered DESC LIMIT 0, $LIMIT";
}
else if ($status == 'All') {
   $SQL = "SELECT UserID, Account, City, Province, Country, Gender, floor( DATEDIFF(NOW(),Birthdate )/ 365.25 ) as Age, DateEntered, OnlineStatus, LastLogin
             FROM ACCOUNTS 
            WHERE 1 = 1
            $locationFilter
            $sexFilter
            $orderBy LIMIT 0, $LIMIT";
}

if ($REMOTE_ACCOUNT) {
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

$returnString['success'] = $success;
$returnString['reason']  = $reason;
$returnString['sql'] = $SQL;

print json_encode($returnString);

exit;

?>