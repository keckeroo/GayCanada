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

$returnString['account'] = $REMOTE_ACCOUNT;

if ($REMOTE_ACCOUNT) {
#   $fields = array('account','userid','firstname');

   switch ($cmd) {
      case 'get': 
         $count = _mysql_get_records("SELECT ACCOUNTS.*, ACTOPTIONS.*, Horoscopes.Horoscope 
                                        FROM ACCOUNTS
                                        LEFT JOIN ACTOPTIONS 
                                          ON ACTOPTIONS.Account = ACCOUNTS.Account
                                        LEFT JOIN Horoscopes 
                                          ON Horoscopes.Sign = ACCOUNTS.Sign AND ACCOUNTS.Gender IN (Horoscopes.Gender)
                                       WHERE ACCOUNTS.Account = '$REMOTE_ACCOUNT'", &$account_info);

         $returnString['mysqlerr'] = $mysql_errmsg;
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
         if ($newpassword || $confirmpassword || $currentpassword) {
            # user is trying to update his password
            $success = false;
            $count = _mysql_get_records("SELECT * FROM ACCOUNTS WHERE Account = '$REMOTE_ACCOUNT'", &$account_info);
            if ($count == 0) {
               $returnString['errors']['title']  = "Record error.";
               $returnString['errors']['reason'] = "Unable to retreive your account info. Please try again later.";
               break;
            }
            if ($currentpassword <> $account_info[0]['Password']) {
               $returnString['errors']['title']  = "Password incorrect.";
               $returnString['errors']['reason'] = "Old password is incorrect or missing. Please try again.";
               break;
            }
            if (!$newpassword) {
               $returnString['errors']['title']  = "Password blank.";
               $returnString['errors']['reason'] = "New password cannot be blank. Please try again.";
               break;
            }
            if ($newpassword <> $confirmpassword) {
               $returnString['errors']['title']  = "Password mismatch.";
               $returnString['errors']['reason'] = "New password and confirmation do not match. Please try again.";
               break;
            }
            $result = _mysql_do("UPDATE ACCOUNTS SET 
                password = '%s',
                SecurityQuestion = '%s',
                SecurityAnswer = '%s',
                PassChanged = NOW()
                WHERE ACCOUNT = '$REMOTE_ACCOUNT'", $newpassword, $securityquestion, $securityanswer);
            if ($result == 1) {
                $success = true;
            }
            else {
               $returnString['errors']['title']  = "Update failed.";
               $returnString['errors']['reason'] = "Password update failed for unknown reason. Please try again.";
            }
            break;
         }
         else {
            if ($cityid) {
              // we may be updating to a new city ....

              $result = _mysql_get_records("SELECT city, regionid, country FROM geobytes.Cities WHERE cityid = '$cityid'", &$cityinfo);
              if ($result > 0) {
                 $country = $cityinfo[0]['country'];
                 $regionid  = $cityinfo[0]['regionid'];
                 $city    = $cityinfo[0]['city'];
                 $result = _mysql_get_records("SELECT code FROM geobytes.Regions WHERE regionid = '$regionid'", &$regioninfo);
                 $region = $regioninfo[0]['code'];
              }
            }

            $result = _mysql_do("UPDATE ACCOUNTS SET First_Name = '%s', Last_Name = '%s', Birthdate = '%s', Gender = '%s',
                Address = '%s', City = '%s', Province = '%s', Country = '%s', Postal_Code = '%s', CityID = '%s', Community = '%s',
                Email = '%s', Email_Alternate = '%s',
                SecurityQuestion = '%s', SecurityAnswer = '%s',
                Vac_Start_Date = '%s', Vac_End_Date = '%s', Vac_CityID = '%s', Vac_City = '%s', Vac_Province = '%s', Vac_Country = '%s',
                thumbnail = '%s',
                qmBackground = '%s',
                mail_notification = '%s',

                LastUpdated = NOW() WHERE Account = '$REMOTE_ACCOUNT'",
                $firstname, $lastname, $birthdate, $sex, 
                $address, $city, $region, $country, $postalcode, $cityid, $community,
                $email, $alternateemail, 
                $securityquestion, $securityanswer,
                $vacstartdate, $vacenddate, $vaccityid, $vaccity, $vacregion, $vaccountry,
                $thumbnail,
                $qmBackground, $mailnotification);
         }

         if ($result > 0) {
            $success = true;
         }
         else {
            $returnString['errors']['reason'] = $mysql_errmsg;
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