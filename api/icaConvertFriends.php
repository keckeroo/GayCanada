<?php

$HTTP_ROOT = $DOCUMENT_ROOT;
$HTTP_ROOT = "/home/gaycanada/www/";
require "$HTTP_ROOT/Library/mysql.php";

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");



$count = _mysql_get_records("SELECT account, userid, linkfriends from PROFILES ", &$profiles);

   $friends = array();
for ($i = 0; $i < $count; $i++) {

   if ($profiles[$i]['linkfriends'] == '') {
      continue;
   }
   print $profiles[$i]['account'] . ' ' . $profiles[$i]['userid'] . ' (' . $profiles[$i]['linkfriends'] . ")<br>\n";
   $friends = explode(',', $profiles[$i]['linkfriends']);
   $account = $profiles[$i]['account'];
   $userid  = $profiles[$i]['userid'];
   foreach ($friends as $friend) {
       $rosterfound = _mysql_do("SELECT * FROM ROSTER WHERE ACCOUNT = '$account' AND theiruserid = '$friend'", &$rosterrecord);
       $roster = 'isfriend';
       if ($rosterfound) {
          // We're doing an update
          $result = _mysql_do("UPDATE ROSTER SET $roster = 1, updated = NOW() WHERE ACCOUNT = '$account' AND theiruserid = '$friend'");
       }
       else {
         $result = _mysql_do("INSERT INTO ROSTER (`account`, `userid`, `theiruserid`, `$roster`, `updated`) VALUES ('$account', '$userid', '$friend', 1, NOW())");
       }
      print $friend . "<br>\n";
   }
}
exit;

?>