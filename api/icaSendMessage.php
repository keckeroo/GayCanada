<?php  

require("$_SERVER[DOCUMENT_ROOT]/Library/authenticate.php");

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");

$success = false;
$keyreqd = false;
$errormsg  = '';
$records = array();
$returnString = array();

$returnString['success'] = $success;

$mailbox        = 'Inbox';

if (!$REMOTE_USER) {
   $returnString['errormsg'] = 'Access Denied';
   print json_encode($returnString);
   exit;
}

if (!$mailTo || !$mailSubject || !$mailBody) {
   $returnString['errormsg'] = 'Missing required field(s).';
   print json_encode($returnString);
   exit;
}

//
// Message has enough required fields ....
//


// get account number of the 'To' person

$result = _mysql_get_records("SELECT Account, Mail_Notification, Userid, Service, ServiceExpire, Permission FROM ACCOUNTS WHERE UserID = '$mailTo'", &$toRecord);

if ($result == 0) {
   $returnString['errormsg'] = 'Unknown username.';
   print json_encode($returnString);
   exit;
}
else {
   $toAccount = $toRecord[0]['Account'];
   $toUser    = $toRecord[0]['Userid'];
   $toService = $toRecord[0]['Service'];
   $toNotification = $toRecord[0]['Mail_Notification'];
}


$now    = time();
$mid    = md5($now.$REMOTE_USER.$mailTo);
$expiry = $now + 15724800;

//$query  = "INSERT INTO MESSAGES SET Account=\"$accountid\", UserID =\"$Recipient\", ToField=\"$ToField\", CcField=\"$ccfield\",
//           FromUser =\"$FromUser\", 
//               Flag = \"1\""; 

$result = _mysql_do("INSERT INTO MESSAGES SET 
                     Mailbox = 'Inbox',
                     toFolder = 'Inbox',
                     fromUser = '$REMOTE_USER',
                     fromAccount = '$REMOTE_ACCOUNT',
                     fromFolder = 'Sent',
                     Date = NOW(),
                     Status = 'Unread',
                     ExpiryDate = '%s',
                     MID = '%s',
                     Account = '%s', UserId = '%s', ToField = '%s', Subject = '%s', Message = '%s'", $expiry, $mid, $toAccount, $mailTo, $mailTo, $mailSubject, $mailBody);

//    systemadminMail($Recipient,$FromUser);
//    if ($REMOTE_TRACE) { trace_account($REMOTE_ACCOUNT,$query); }

if ($toNotification == 'Immediately') {
   # check to see if user is online or not ....
   $resultImmediate = _mysql_get_records("SELECT * FROM ONLINE WHERE UserID = '$mailTo'", &$online);
   if ($resultImmediate == 0) {
      # user is not online - so send notification ....
      $lines = exec("cd /home/gaycanada/Library ; ./notify_mail.cgi immediately $mailTo");
   }
}


$returnString['success'] = $result > 0;
$returnString['errormsg'] = $mysql_error;

print json_encode($returnString);
exit;



print "From: $REMOTE_USER<br>\n";
print "To: $icaMailTo<br>\n";
print "Subject: $icaMailSubject<br>\n";
print "Body: $icaMailBody<br>\n";
exit;

$fromto          = $mailbox == "Sent" ? "To" : "From";
$sentreceived    = $mailbox == "Sent" ? "Sent" : "Received";
//$int_min         = get_minutes($REMOTE_ZONE);

##
## Retrieve Signature Information for user and get limit information.
##

_mysql_get_records("SELECT Signature,MailConf FROM ACTOPTIONS WHERE Account='$REMOTE_ACCOUNT'",&$signature); 
_mysql_get_records("SELECT Mail_FontSize, Mail_Boxes,Limit_ExpiryDays, Limit_NumMsgs,MailViewPop
                      FROM ACCOUNTS
                      LEFT JOIN ACTOPTIONS
                        ON ACTOPTIONS.Account = ACCOUNTS.Account
                     WHERE ACTOPTIONS.Account  = '$REMOTE_ACCOUNT'", &$account);

list ($font, $size) = split("\|", $account[0]["Mail_FontSize"], 2);

$sizes  = array ("/\-2/","/\-1/","/\+1/","/\+2/");
$values = array ("8", "10","12","14");

$sizenew = preg_replace($sizes, $values,$size);

$boxes = split("\|", $account[0]["Mail_Boxes"]);
$limit_expirydays = $account[0]["Limit_ExpiryDays"];
$limit_nummsgs    = $account[0]["Limit_NumMsgs"];

$FromUser = $REMOTE_USER;

if ($icaMailTo) { //  && preg_match("/\,/",$icaMailTo)){
   $icaMailTo = ereg_replace(" +","", $icaMailTo);
   $sendM = split(",", $icaMailTo); 

   //## Check to see if UserID is in the database...    
   for ($i=0; $i < count($sendM); $i++) { 
        $user = $sendM[$i];
     	$userfound = _mysql_get_records("SELECT Account FROM IDENTITIES WHERE UserID = '$user'", &$checkusers);
        if (!$userfound) { 
            not_found($FromUser,$Recipient,$Subject,$Message); 
        }
   }


   ## Now, that all members have checked out and are valid members, insert messages
   $count = 1;
   for ($d=0;$d<count($sendM);$d++) { 
       $mailbox = 'Inbox';
       $counter = "-$count";
       $user_do = $sendM[$d];
       $found_contact = _mysql_get_records("SELECT UserID,MemberID,Status FROM contacts.RECORDS 
                                             WHERE UserID = '$user_do' AND MemberID = '$FromUser'", &$contacts);
 
      if ($found_contact) { $mailbox = $contacts[0]["Status"] == "Block" ? "Trash" : "Inbox"; }
 
      _mysql_get_records("SELECT Account FROM IDENTITIES WHERE UserID = '$user_do'",&$ids);

      $accountid = $ids[0]["Account"];
      create_message($accountid, $FromUser, $icaMailTo, $user_do, $icaMailSubject, $icaMailBody, $counter, $mailbox, $icaMailCC);
      ++$count;
    }        
}

if (0) { // $icaMailTo && !preg_match("/\,/",$icaMailTo)) {
   $userfound = _mysql_get_records("SELECT Account FROM IDENTITIES WHERE UserID = '$Recipient'", &$checkusers);
   if (!$userfound) { 
      not_found($FromUser,$Recipient,$Subject,$Message); 
   }
   $found_contact = _mysql_get_records("SELECT UserID,MemberID,Status FROM contacts.RECORDS 
                                         WHERE UserID = '$Recipient' AND MemberID = '$FromUser'", &$contacts);

   $mailbox = $contacts[0]["Status"] == "Block" ? "Trash" : "Inbox";

   create_message($checkusers[0]["Account"],$FromUser,$tofield, $Recipient, $Subject, $Message, '1',$mailbox,$ccfield);  
}

if ($CC) {
    $CC = ereg_replace(" +","",$CC);

    $sendM = split(",", $CC); 
    //print "Multiple CC: $CC"; print_r($sendM); print "<br>";
    //## Check to see if UserID is in the database...    

    for ($i=0;$i<count($sendM);$i++) { 
        $user = $sendM[$i];

        $userfound = _mysql_get_records("SELECT Account FROM IDENTITIES WHERE UserID = '$user'", &$checkusers);
        if (!$userfound) { 
            not_found($FromUser,$user,$Subject,$Message); 
        }
     } ## Ends first foreach

     ## Now, that all members have checked out and are valid members, insert messages
     $count =1;
     for ($d=0;$d<count($sendM);$d++) { 
        $counter = "-$count";
        $user_do = $sendM[$d];
        $found_contact = _mysql_get_records("SELECT UserID,MemberID,Status FROM contacts.RECORDS 
                                              WHERE UserID = '$user_do' AND MemberID = '$FromUser'", &$contacts);

        if ($found_contact) { 
           $mailbox = $contacts[0]["Status"] == "Block" ? "Trash" : "Inbox"; 
        }

        _mysql_get_records("SELECT Account FROM IDENTITIES WHERE UserID = '$user_do'",&$ids);
        $accountid = $ids[0]["Account"];
        create_message($accountid, $FromUser, $tofield, $user_do, $Subject, $Message, $counter, $mailbox, $ccfield);  
        ++$count;
    }
}

if (0) { // $CC && !preg_match("/\,/",$CC)){
         //print "Single $CC CC<br>";
         ## For single recipients
         $userfound = _mysql_get_records("SELECT Account FROM IDENTITIES WHERE UserID = '$CC'", &$checkccUser);
         if (!$userfound) { not_found($FromUser,$CC,$Subject,$Message); }
         $found_contact = _mysql_get_records("SELECT UserID,MemberID,Status FROM contacts.RECORDS 
                                             WHERE UserID = '$CC' AND MemberID = '$FromUser'", &$contacts);

         $mailbox = $contacts[0]["Status"] == "Block" ? "Trash" : "Inbox";

         create_message($checkccUser[0]["Account"],$FromUser,$tofield, $CC, $Subject, $Message, '1',$mailbox,$ccfield);  
}

function create_message($accountid, $FromUser, $ToField, $Recipient, $Subject, $Message, $Counter, $Mailbox, $ccfield) {
    global $MYSQL_DEFAULT_HOST, $MYSQL_DEFAULT_USERID,$MYSQL_DEFAULT_PASSWORD,$REMOTE_TRACE,$REMOTE_ACCOUNT;
    $now = time();
    $mid = "MID". md5($now.$FromUser.$Recipient) . "$Counter";
    $expiry = $now + 15724800;
    $Mailbox = $Mailbox ? $Mailbox : "Inbox";

    systemadminMail($Recipient,$FromUser);

    $mysql_link = mysql_connect("$MYSQL_DEFAULT_HOST","$MYSQL_DEFAULT_USERID","$MYSQL_DEFAULT_PASSWORD");
    mysql_select_db("opus", $mysql_link);

    $query = "INSERT INTO MESSAGES SET Account=\"$accountid\", UserID =\"$Recipient\", ToField=\"$ToField\", CcField=\"$ccfield\",
               Mailbox = \"$Mailbox\", FromUser =\"$FromUser\", Message = \"$Message\", Subject = \"$Subject\", 
                 MID =\"$mid\", Flag = \"1\", ExpiryDate = \"$expiry\", Date=now()"; 
    $result = mysql_query($query,$mysql_link);
    mysql_close($mysql_link);

    if ($REMOTE_TRACE) { trace_account($REMOTE_ACCOUNT,$query); }
}

print "<response status='Success'>\n";
print "<messageInfo>\n";
print "<subject>Subject was $icaMailSubject</subject>\n";
print "</messageInfo>\n";
print "</response>\n";

exit;

function send_message() {
    global $Recipient,$CC,$FromUser,$Message,$Subject,$REMOTE_USER,$REMOTE_ACCOUNT;
    global $Direction,$DirectionMID,$Xsender, $MailConf;
    global $MYSQL_DEFAULT_HOST,$MYSQL_DEFAULT_USERID,$MYSQL_DEFAULT_PASSWORD;    
    $email_domains = array ("/@gaycanada.com/","/@gayalberta.com/","/@cglbrd.com/","/@mygaycanada.com/","/@gaylifecanada.com/");
    $Recipient = preg_replace($email_domains,"",$Recipient); // if user puts in @gaycanada.com remove it.

    if (preg_match("/^[a-zA-Z0-9\-\.]+\@[a-zA-Z0-9\-\.]+([a-zA-Z]{2,3}|[0-9]{1,3})$/",$Recipient) || preg_match("/^[a-zA-Z0-9\-\.]+\@[a-zA-Z0-9\-\.]+([a-zA-Z]{2,3}|[0-9]{1,3})$/",$CC)) {
       print "<blockquote>You're Recipient address <b>$Recipient</b> must be a VALID GayCanada Identity.  You are not
              permitted to use email addresses that have @ symbols, which is an Email address.</blockquote>";
       print "<a href=\"javascript:history.back();\">Go Back</a>";
       exit();
    }
    else {
       if (!$Recipient || !$FromUser || !$Message || !$Subject) {
          print "$f1 Hey $REMOTE_USER, You cannot leave the following fields empty: <br>\n";
          if (!$Subject) print "Subject<br>\n";
          if (!$FromUser) print "From YOU!<br>\n";
          if (!$Recipient) print "You cannot send a message to \"Choose Member\".  Please go back and try again";
          if (!$Message) print "No Message?  What was the point of sending a message?<br>\n";
          exit();
       }
       else {

          start_create_message($FromUser, $Recipient, $Subject, $Message, $CC);
          if ($Direction) {
              $mysql_link = mysql_connect("$MYSQL_DEFAULT_HOST","$MYSQL_DEFAULT_USERID","$MYSQL_DEFAULT_PASSWORD");
              mysql_select_db("opus", $mysql_link);

              $query = "UPDATE MESSAGES SET Direction = \"$Direction\" WHERE MID = \"$DirectionMID\"";

              $result = mysql_query($query,$mysql_link);
              mysql_close($mysql_link);

          }
          if ($Xsender) {
              $now = time();
              $mid = "MID". md5($now.$FromUser.$Recipient) . "OB";             
              $mysql_link = mysql_connect("$MYSQL_DEFAULT_HOST","$MYSQL_DEFAULT_USERID","$MYSQL_DEFAULT_PASSWORD");
              mysql_select_db("opus", $mysql_link);

              $query = "INSERT INTO MESSAGES SET Account=\"$REMOTE_ACCOUNT\", UserID =\"$REMOTE_USER\", Mailbox = \"Sent\", CcField=\"$CC\", FromUser =\"$Recipient\", Message = \"$Message\", Subject = \"$Subject\", MID =\"$mid\", Flag = \"0\", Date=now(), Received =now()"; 
              $result = mysql_query($query,$mysql_link);
              mysql_close($mysql_link);
          }
       }
    }
}

?>
