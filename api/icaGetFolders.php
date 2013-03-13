<?php  

$mailbox = $icaMailFolder ? $icaMailFolder : "Inbox"; 

$REQUIRED = 1;
require "$_SERVER[DOCUMENT_ROOT]/Library/html2.php";
$GC_SECTION = 'My GayCanada';
require_active();

header("Content-Type: application/xml; charset=utf-8");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

print "<response status=\"success\">\n";

$MYSQL_DATABASE = "opus";

$total = _mysql_get_records("SELECT COUNT(MID) AS MID
                               FROM MESSAGES
                              WHERE Account = '$REMOTE_ACCOUNT'
                                AND MailBox = '$mailbox'
                               LIMIT $limit_nummsgs", &$folders);


print "<icaMailFolderInfo>\n";
print "   <folderName>Inbox</folderName>\n";
print "   <folderUnreadCount>0</folderUnreadCount>\n";
print "   <folderTotalCount>0</folderTotalCount>\n";
print "</icaMailFolderInfo>\n";
print "<icaMailFolderInfo>\n";
print "   <folderName>Sent</folderName>\n";
print "   <folderUnreadCount>0</folderUnreadCount>\n";
print "   <folderTotalCount>0</folderTotalCount>\n";
print "</icaMailFolderInfo>\n";
print "<icaMailFolderInfo>\n";
print "   <folderName>Trash</folderName>\n";
print "   <folderUnreadCount>0</folderUnreadCount>\n";
print "   <folderTotalCount>0</folderTotalCount>\n";
print "</icaMailFolderInfo>\n";

for ($m=0; $m < 0; $m++) {
        $mail_fromuser  = $mail[$m]["FromUser"];
        $mail_mid       = $mail[$m]["MID"];
        $mail_mailbox   = $mail[$m]["Mailbox"];      
        $mail_receivedshort = $mail[$m]["ReceivedShort"];
        $mail_subject   = $mail[$m]["Subject"];
        $mail_dateshort = $mail[$m]["RightNow"] == $mail[$m]["DateShortYesterday"] ? $mail[$m]["DateShortToday"] : $mail[$m]["DateShortYesterday"];

        // For the NEXT/PREV Buttons
        $nmid           = $mail[$m+1]["MID"];
        $pmid           = $mail[$m-1]["MID"];
 
        $found_contact = _mysql_get_records("SELECT MemberID, Status FROM contacts.RECORDS 
                                             WHERE UserID = '$REMOTE_USER' AND MemberID = '$mail_fromuser'", &$contacts);

   print "<folderInfo>\n";
   print "   <mid>$mail_mid</mid>\n";
   print "   <from>$mail_fromuser</from>\n";
   print "   <subject>$mail_subject</subject>\n";
   print "   <date>{$mail[$m]['Date']}</date>\n";
   print "   <status>{$mail[$m]['Status']}</status>\n";
   print "</folderInfo>\n";
}

print "</response>\n";
?>