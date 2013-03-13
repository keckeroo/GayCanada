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

if ($REMOTE_ACCOUNT) {
   $count =  _mysql_get_records("SELECT Horoscopes.horoscope FROM ACCOUNTS LEFT JOIN Horoscopes ON ACCOUNTS.Sign = Horoscopes.Sign 
                                 WHERE ACCOUNTS.Account = '$REMOTE_ACCOUNT' LIMIT 0, 1", &$dataset);
   if ($count == 0) {
       $count = _mysql_get_records("SELECT Birthdate FROM ACCOUNTS WHERE ACCOUNT = '$REMOTE_ACCOUNT'", &$account);
       $birthdate = $account[0]['Birthdate'];
   if ($birthdate) {
      list($year,$month,$day)=explode("-",$birthdate);
      if(($month==1 && $day>20)||($month==2 && $day<20)){
          $sign= "Aquarius";
      }else if(($month==2 && $day>18 )||($month==3 && $day<21)){
          $sign= "Pisces";
      }else if(($month==3 && $day>20)||($month==4 && $day<21)){
          $sign= "Aries";
      }else if(($month==4 && $day>20)||($month==5 && $day<22)){
          $sign= "Taurus";
      }else if(($month==5 && $day>21)||($month==6 && $day<22)){
          $sign= "Gemini";
      }else if(($month==6 && $day>21)||($month==7 && $day<24)){
          $sign= "Cancer";
      }else if(($month==7 && $day>23)||($month==8 && $day<24)){
          $sign= "Leo";
      }else if(($month==8 && $day>23)||($month==9 && $day<24)){
          $sign= "Virgo";
      }else if(($month==9 && $day>23)||($month==10 && $day<24)){
          $sign= "Libra";
      }else if(($month==10 && $day>23)||($month==11 && $day<23)){
         $sign= "Scorpio";
      }else if(($month==11 && $day>22)||($month==12 && $day<23)){
          $sign= "Sagittarius";
      }else if(($month==12 && $day>22)||($month==1 && $day<21)){
          $sign= "Capricorn";
      }
      $returnString['sign'] = $sign;
      $count =  _mysql_get_records("SELECT * FROM Horoscopes WHERE Sign = '$sign'", &$dataset);
      _mysql_do("UPDATE ACCOUNTS SET Sign = '$sign' WHERE ACCOUNT = '$REMOTE_ACCOUNT' AND UserID = '$REMOTE_USER'");
   }
   }
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
else {
   $returnString['records'] = $records; 
}

$returnString['success'] = $success;
$returnString['reason']  = $reason;

print json_encode($returnString);

exit;

?>