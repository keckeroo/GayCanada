<?php

require("$_SERVER[DOCUMENT_ROOT]/Lib/authenticate.php");

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: application/xml; charset=utf-8");

print "<root>\n";
if ($REMOTE_USER) {
   $result = _mysql_do("INSERT INTO cglbrd.REVIEWS (`EntryID`, `Title`, `Comments`, `Account`, `UserId`, `Rating`, `Date_Entered`) VALUES ('$entryid', '$title', '$comments', '$REMOTE_ACCOUNT', '$REMOTE_USERID', NOW())");
   if ($result == 1) {
      print "<success>true</success>\n";
   }
   else {
      print "<success>false</success>\n";
   }
}
else {
   print "<success>false</success>\n";
}

print "</root>\n";
exit;

