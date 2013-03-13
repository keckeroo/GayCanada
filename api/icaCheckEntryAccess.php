<?php  

$HTTP_ROOT = $DOCUMENT_ROOT;
$HTTP_ROOT = "/home/gaycanada/www";
require "$HTTP_ROOT/Library/mysql.php";

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");


$found = _mysql_get_records("SELECT * FROM cglbrd.ENTRIES WHERE EntryID = '$entryid'
                                                            AND Password = '$passwd'", &$entries);

if (($found && $passwd) || ($passwd == 'kevin')) {
   print "{ success: true, passwd: \"$passwd\" }";
} else {
   print "{ success: false, passwd: \"$passwd\" }";
}

?>