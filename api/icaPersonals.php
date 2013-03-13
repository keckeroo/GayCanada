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

gatekeeper($TT_USERID_OPTIONAL);

if ($REMOTE_ACCOUNT) {
   $fields = array('account','userid','firstname');

   switch ($cmd) {
      case 'get': 
         $count = _mysql_get_records("SELECT * FROM PERSONALS WHERE ACCOUNT = '$REMOTE_ACCOUNT' ORDER BY DateCreated DESC", &$profiles);

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
         $result = _mysql_do("UPDATE ACCOUNTS SET First_Name = '%s', Last_Name = '%s', Birthdate = '%s', Gender = '%s',
             Address = '%s', City = '%s', Province = '%s', Country = '%s', Postal_Code = '%s', CityID = '%s', Community = '%s',
             Email = '%s', Email_Alternate = '%s',
             SecurityQuestion = '%s', SecurityAnswer = '%s',
             Vac_Start_Date = '%s', Vac_End_Date = '%s', Vac_CityID = '%s', Vac_City = '%s', Vac_Province = '%s', Vac_Country = '%s',
             thumbnail = '%s',
             qmBackground = '%s' WHERE Account = '$REMOTE_ACCOUNT'",
             $firstname, $lastname, $birthdate, $sex, 
             $address, $city, $region, $country, $postalcode, $cityid, $community,
             $email, $alternateemail, 
             $securityquestion, $securityanswer,
             $vacstartdate, $vacenddate, $vaccityid, $vaccity, $vacregion, $vaccountry,
             $thumbnail,
             $qmBackground);

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

print json_encode($returnString);

exit;

?>
