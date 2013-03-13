<?php  

$REQUIRED = 1;
require "$_SERVER[DOCUMENT_ROOT]/Library/html2.php";
$GC_SECTION = 'My GayCanada';
require_active();
include "mail_header.inc";
$page = $page ? $page : "2:9:1";

$mailbox = 'Inbox';

header("Content-Type: application/xml; charset=utf-8");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

if (!$REMOTE_USER) {
   exit;
}

_mysql_do("UPDATE MESSAGES SET Status =  IF('$icaMailStatus' <> '', '$icaMailStatus', Status),
                               Mailbox = IF('$icaMailFolder' <> '', '$icaMailFolder', Mailbox)
            WHERE MID = '$icaMailMID'
              AND UserID = '$REMOTE_USER'
              AND Account = '$REMOTE_ACCOUNT'");

print "<response status='Success'>\n";
print "<messageInfo>\n";
print "</messageInfo>\n";
print "</response>\n";
?>