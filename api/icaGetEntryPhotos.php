<?php

require("$_SERVER[DOCUMENT_ROOT]/Library/authenticate.php");

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: application/xml; charset=utf-8");

$count = _mysql_get_records("SELECT * FROM cglbrd.PHOTOS WHERE EntryID = '$EntryID' AND Enabled = 'Yes' ORDER BY `Default` DESC", &$photos);

print "<root>\n";
for ($i = 0; $i < $count; $i++) {
   print "<photo>\n";
   print "<photoid>{$photos[$i]['photood']}</photoid>\n";
   print "<entryid>{$photos[$i]['entryid']}</entryid>\n";
   print "<file>{$photos[$i]['file']}</file>\n";
   print "<height>{$photos[$i]['height']}</height>\n";
   print "<width>{$photos[$i]['width']}</width>\n";
   print "<thumbnailconfig>{$photos[$i]['thumbnail_config']}</thumbnailconfig>\n";
   print "<caption><![CDATA[{$photos[$i]['caption']}]]></caption>\n";
   print "<description><![CDATA[{$photos[$i]['description']}]]></description>\n";
   print "<default>{$photos[$i]['default']}</default>\n";
   print "</photo>\n";
}
print "</root>\n";
exit;

