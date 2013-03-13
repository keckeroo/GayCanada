<?php  

$REQUIRED = 1;
require("$_SERVER[DOCUMENT_ROOT]/Library/html2.php");
require "$_SERVER[DOCUMENT_ROOT]/Library/accounts.inc";
require "$_SERVER[DOCUMENT_ROOT]/Library/functions.inc";

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");

if (!$REMOTE_ACCOUNT) {
   exit;
}

$found   = _mysql_get_records("SELECT * FROM ACCOUNTS WHERE Account = '$REMOTE_ACCOUNT'",&$accounts);

if ($found) {
   // birthdate input from ajax is : Thu May 13 1976 00:00:00 GMT-0600 (Mountain Daylight Time)
   // convert to similar format
   $d = date_create($birthdate);
   $d = date_format($d,"Y-m-d");

   if ($accounts[0]["Birthdate"] != $d) {
       $birth_code = $accounts[0]["Birthdate"] ." -> $d";
       //     print "<PRE>$d<br>".$accounts[0]["Birthdate"]."</PRE><br>";
       //Monitor Birthdate changes

       if ($REMOTE_PERM & $GC_ENHANCED) {
           add_note($REMOTE_USER,$REMOTE_ACCOUNT,"6",$birth_code);
       }
       else {
           // submit account for review of EPL codes
           $new_status = _mysql_do("UPDATE ACCOUNTS SET SystemStatus=\"Referred\", Messages=\"107 EPL Required : $birth_code\", 
                                    MessageBy=\"EPL\",MessageDate=now() WHERE Account=\"$REMOTE_ACCOUNT\"");
           add_note($REMOTE_USER,$REMOTE_ACCOUNT,"6",$birth_code);
       }
   }
}

if ($securityquestion || $securityanswer || $Password) {
    if ($accounts[0]["Password"] == $CurPassword) {
         $sql = " SecurityQuestion = '$securityquestion',
                  SecurityAnswer   = '$securityanswer',";
         if ($Password == $Password2) {
             $sql .= "Password     = '$Password',";
         }
    }
}

if ($cityid) {
   // we may be updating to a new city ....

   $result = _mysql_get_records("SELECT city, region, country FROM geobytes.Cities WHERE cityid = '$cityid'", &$cityinfo);
   if ($result > 0) {
      $country = $cityinfo[0]['country'];
      $region  = $cityinfo[0]['region'];
      $city    = $cityinfo[0]['city'];
   }
}

$result1 = _mysql_do("UPDATE ACCOUNTS
              SET Country      = IF('$Country' <> '', '$country', Country),
                  Province       = IF('$province' <> '', '$province', Province),
                  City         = IF('$city' <> '', '$city', city),
                  cityid       = IF('$cityid' <> '', '$cityid', cityid),
                  Email        = IF('$email' <> '', '$email', email),
                  Email_Alternate  = IF('$emailalternate' <> '', '$emailalternate', Email_Alternate),
                  Community        = '$community',
                  $sql
                  First_Name       = '$firstname',
                  Last_Name        = '$lastname',
                  Address          = '$address',
                  Postal_Code      = '$postalcode',
                  Zone             = '$zone',
                  DST              = $dst,
                  thumbnail        = '$thumbnail',
                  Gender           = '$gender'
            WHERE Account = '$REMOTE_ACCOUNT' AND UserID = '$REMOTE_USER'");
//                  Birthdate        = '$birthdate'

$result2 = _mysql_do("UPDATE ACTOPTIONS
              SET NotifyMe   = $mailnotification,
                  MailConf   = $mailconfirmation,
                  IncludeSig = $includesig,
                  Inactive   = $inactiveflag,
                  Signature  = '$mailsignature'
            WHERE Account = '$REMOTE_ACCOUNT'");

$found_upd = _mysql_get_records("SELECT TO_DAYS(Birthdate) AS B_Days, TO_DAYS(now()) AS N_Days
                                 FROM ACCOUNTS
                                 WHERE Account = '$REMOTE_ACCOUNT'", &$birthdate_check);

$valdays = ($birthdate_check[0]["N_Days"] - $birthdate_check[0]["B_Days"]) / 365.25;
list ($age_now,$blah) = split("\.",$valdays);  
    
$age = $age_now;

$result = _mysql_do("UPDATE ONLINE
              SET  City = '$city', Province = '$province', Country='$country', Community = '$community',
                   Gender = '$gender', Age = '$age', Heartbeat = now()
              WHERE Account = '$REMOTE_ACCOUNT'");

if ($result1 >= 0 || $result2 >= 0 || $result >= 0) {
   print "{success: true }\n";
}
else {
   print "{success: false, errors: { reason: 'Unable to update account information ($mysql_errmsg).  ' }}\n";
}

?>