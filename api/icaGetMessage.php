<?php  

$REQUIRED = 1;
require "$_SERVER[DOCUMENT_ROOT]/Library/html2.php";
$GC_SECTION = 'My GayCanada';
require_active();

$mailbox = 'Inbox';

header("Content-Type: application/xml; charset=utf-8");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

print "<icaResponse status='Success'>\n";
print "<messageInfo>\n";

    $MYSQL_DATABASE = "opus";

    _mysql_get_records("SELECT Mail_FontSize, Mail_Boxes,Limit_ExpiryDays, Limit_NumMsgs,MailViewPop
                          FROM ACCOUNTS
                     LEFT JOIN ACTOPTIONS
                            ON ACTOPTIONS.Account = ACCOUNTS.Account
                         WHERE ACTOPTIONS.Account  = '$REMOTE_ACCOUNT'", &$account);

    $int_min = get_minutes($REMOTE_ZONE);

    list ($font, $size) = split("\|", $account[0]["Mail_FontSize"], 2);
    $sizes  = array ("/\-2/","/\-1/","/\+1/","/\+2/");
    $values = array ("8", "10","12","14");
    $sizenew = preg_replace($sizes, $values,$size);

    $mailget = _mysql_get_records("SELECT *, 
                                 DATE_FORMAT(DATE_ADD(now(), INTERVAL $int_min MINUTE), '%a, %b %d') AS RightNow,
                                 DATE_FORMAT(DATE_ADD(Date, INTERVAL $int_min MINUTE), '%a, %b %d') AS DateShortYesterday,
                                 DATE_FORMAT(DATE_ADD(Date, INTERVAL $int_min MINUTE), '%h:%i %p') AS DateShortToday,
                                 DATE_FORMAT(DATE_ADD(Received, INTERVAL $int_min MINUTE), '%m/%d/%Y %l:%i %p') AS ReceivedShort,
                                 REPLACE(FromUser, ',', ', ') AS FromUser
                                 FROM MESSAGES
                                 WHERE Account = '$REMOTE_ACCOUNT'
                                   AND MailBox = '$mailbox'
                                   AND MID = '$MID'
                                 ", &$mail);

if ($mailget > 0) {
print "   <mid>{$mail[0]['MID']}</mid>\n";
print "   <from>{$mail[0]['FromUser']}</from>\n";
print "   <subject>{$mail[0]['Subject']}</subject>\n";
$message = $mail[0]['Message'];
//if (preg_match('/<pre>/', $message) {
//}
//else {
   $message = preg_replace('/\n/', '<br>', $message);
//}
print "   <message><![CDATA[$message]]></message>\n";
#print "   <message><![CDATA[{$mail[0]['Message']}]]></message>\n";
#print "   <message>{$mail[0]['Message']}</message>\n";
print "   <date>{$mail[0]['Date']}</date>\n";
print "   <status>{$mail[0]['Flags']}</status>\n";
    }      

print "</messageInfo>\n";
print "</icaResponse>\n";
?>