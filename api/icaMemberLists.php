<?php  

require("$_SERVER[DOCUMENT_ROOT]/Library/authenticate.php");

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");

$count = 0;

gatekeeper($TT_USERID_OPTIONAL);

$records = array();
$result['errors']['errorcode'] = 0;
$result['success'] = false;
$result['count'] = -1;

$cmd = $cmd ? $cmd : 'get';

switch ($cmd) {
   case 'get' : 
      $count = _mysql_get_records("SELECT * FROM MEMBERLISTS WHERE ACCOUNT = '$account' AND FIND_IN_SET(group, 'FRIENDS')", &$dataset);
   
      $rc = count($mysql_fields);
      $result['success'] = true;
      $result['count'] = $count;

      for ($j = 0; $j < $count; $j++) {
         for ($i = 0; $i < $rc; $i++) {
             $record[$mysql_fields[$i]] = $dataset[$j][$mysql_fields[$i]];
         }
         array_push($records, $record);
      }

      if ($count == 0) {
         $result['errors']['reason'] =  'No records found';
      }
      else {
         $result['records'] = $records;
      }

      break;
}

print json_encode($result);

?>