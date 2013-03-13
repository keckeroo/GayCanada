<?php  

$HTTP_ROOT = $DOCUMENT_ROOT;
$HTTP_ROOT = "/home/gaycanada/www";
require "$HTTP_ROOT/Library/mysql.php";

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");

$found = _mysql_get_records("SELECT aus_email, title, entryid, password FROM cglbrd.ENTRIES WHERE EntryID = '$entryid'", &$entries);

if ($entries[0]['aus_email'] != '') {
    sendpwd('keckeroo@hotmail.com', $entries[0][title], $entries[0][entryid], $entries[0][password]);
    print "{ success: true }";
}
else {
    print "{ success: false }";
}

function sendpwd($email, $title, $entryid, $password) {
   $body = "GayCanada Automated Update System (AUS) 
Password Retrieval

Either you request or the GayCanada staff sent you this letter to remind you
of the  password to your GayCanada Directory entry.

Entry Title: $title
Entry ID: $entryid
Password: $password

To update your entry please enter the above password to gain access to
the AUS.

If you encounter problems with this password or gaining access to the AUS, please
contact our help desk at 1-800-245-2734 Monday - Friday, 9am to 6 pm Central Time.

We thank you for your continued support of GayCanada.

The GayCanada Administrative Team.
http://www.gaycanada.com

";

$to = $email;
$sub = "GayCanada Directory Acccess Information";
$from = "From: GayCanada Accounts <notices@gaycanada.com>\n";

   if (mail($to,$sub,$body,$from)) {
       return TRUE;
   }
   else {
       return FALSE;
   }
}

?>
