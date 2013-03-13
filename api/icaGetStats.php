<?php

require("$_SERVER[DOCUMENT_ROOT]/Library/authenticate.php");

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: application/xml; charset=utf-8");

if ($REMOTE_USER) {
$kevin = 10;
   $online_count = _mysql_get_records("SELECT * FROM opus.ONLINE WHERE `UserID` = '$REMOTE_USER'", &$online);
   if ($online_count > 0) {
      $result = _mysql_do("UPDATE opus.ONLINE SET Heartbeat = NOW() WHERE UserId = '$REMOTE_USER'");
   }
   else {
      $result = _mysql_do("INSERT INTO opus.ONLINE (Account, UserID, Heartbeat, Age, Gender, SystemStatus, E_Code, City, Province, Country) VALUES ('$REMOTE_ACCOUNT', '$REMOTE_USER', NOW(), '$REMOTE_AGE', '$REMOTE_GENDER', '$REMOTE_STATUS', '$_SESSION[E_Code]', '$REMOTE_CITY', '$REMOTE_PROVINCE', '$REMOTE_COUNTRY')");
   }
}

$found = _mysql_get_records("SELECT * FROM cglbrd.STATISTICS", &$stats);

print "<root>\n";
print "<stats>\n";
print " <online>{$stats[0][Online]}</online>\n";
print " <remoteid>{$REMOTE_USER}</remoteid>\n";
print " <remote>$online_count</remote>\n";
print " <kevin>{$REMOTE_AREA}</kevin>\n";
print "</stats>\n";
print "</root>\n";
exit;

