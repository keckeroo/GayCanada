<?php  

require_once "$_SERVER[DOCUMENT_ROOT]/Library/authenticate.php";

// $mailbox = $icaMailFolder ? $icaMailFolder : "Inbox"; 

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");

$success = false;
$reason  = '';
$records = array();
$returnString = array();
$sessionid = session_id();

$returnString['newMail'] = 0;
$returnString['newMessages'] = 0;
$returnString['newMessageDate'] = 0;
$returnString['newMessageFrom'] = '';

//
// Start collecting heartbeat info
//

$MYSQL_DATABASE = "opus";

    // PULL DATA FROM CURRENT ACCOUNT TABLE TO UPDATE AND OVERWRITE LOGIN SET COOKIE $REMOTE VARIABLES
//                                   DATE_FORMAT(DATE_ADD(now(), INTERVAL $int_min MINUTE), '%l:%i %p') As CurrentTime,

$online_count = _mysql_get_records("SELECT * FROM opus.ONLINE WHERE `UserID` = '$REMOTE_USER'", &$online);


if ($REMOTE_ACCOUNT) {
$f0 = _mysql_get_records("SELECT ACCOUNTS.SystemStatus, ACCOUNTS.OnlineStatus, Refresh, ACCOUNTS.AcceptPage, ACCOUNTS.Limit_QMR, 
                                     ACCOUNTS.E_Code, ONLINE.QMStatus, ACCOUNTS.City,
                                     ONLINE.CurTask, TourGuide, ACCOUNTS.Gender,  ONLINE.Account AS OAccount,
                                     ACCOUNTS.Country, ONLINE.QM_Site, ONLINE.LastActivityChange, 
                                     ACCOUNTS.Province, ACCOUNTS.Birthdate, 
                                     ACCOUNTS.QMCookie, 
                                     PROFILES.SystemStatus AS P_SystemStatus, DATE_FORMAT(PROFILES.DateModified,'%Y%m%d') AS P_DateModified,
                                     PROFILES.AdultSharing, PROFILES.Pic, PROFILES.PRID,
                                     PROFILES.Breast, PROFILES.PenisLength, PROFILES.PenisGirth, PROFILES.CutUncut, PROFILES.Pubic, PROFILES.Role, 
                                     PROFILES.SexualFrequency, PROFILES.BodyHair, PROFILES.SexualActivity
                                FROM ACCOUNTS
                           LEFT JOIN ONLINE
                                  ON ACCOUNTS.Account = ONLINE.Account
                           LEFT JOIN PROFILES
                                  ON PROFILES.Account = ACCOUNTS.Account
                               WHERE ACCOUNTS.Account = '$REMOTE_ACCOUNT'", &$account); 

    $timezone = $REMOTE_ZONE;
    $gender   = $account[0]["Gender"];
    $admin    = $REMOTE_ADMIN;
    $acceptpage = $account[0]["AcceptPage"];
    $curtask  = $account[0]["CurTask"];
    $province = $account[0]["Province"];
    $city     = $account[0]["City"];
    $e_code   = $account[0]["E_Code"];
    $status   = $account[0]["SystemStatus"];
    $onlinestatus = $account[0]["OnlineStatus"];

    if (preg_match("/ActiveNote|Active2/", $account[0]["SystemStatus"])) {
        $status = 'Active';
    }
    $age      = $REMOTE_AGE;
    $country  = $account[0]["Country"];
    $birthday = date("m-d") == substr($account[0]["Birthdate"],5,5) ? 1 : 0;
    $QMCookie = $$REMOTE_ACCOUNT ? $$REMOTE_ACCOUNT : $account[0]["QMCookie"];

    $profile_date  = $account[0]["P_DateModified"] ? $account[0]["P_DateModified"]."|".$account[0]["P_SystemStatus"]."|".$account[0]["PRID"] : 0;
    if ($account[0]["AdultSharing"] == 1 && ($account[0]["Breast"] || $account[0]["PenisLength"] || $account[0]["PenisGirth"] || $account[0]["CutUncut"] || $account[0]["Pubic"] || $account[0]["Role"] || $account[0]["SexualFrequency"] || $account[0]["BodyHair"] || $account[0]["SexualActivity"])) {
        $profile_date .= "|XXX";
    }

    $picture_avail = $account[0]["Pic"] ? 1 : 0;

    $found_pics = _mysql_get_records("SELECT Image,Caption,Adult FROM PICTURES
                                       WHERE Account = '$REMOTE_ACCOUNT'
                                         AND SystemStatus IN ('Active','Modified','Pending')
                                         AND Adult = '0'
                                    ORDER BY ByDefault DESC",&$photos);
    $picture_avail = $photos[0]["Image"];
    $REMOTE_CITY = ucwords($REMOTE_CITY);    // conform all Citys to read "Winnipeg" or "New Westminster"

   if ($online_count > 0) {
      $result = _mysql_do("UPDATE ONLINE SET Browser = '$bb', City = \"$city\", Province = '$province', Country='$country', E_Code='$e_code', Age='$age', 
                                  LastSeen = now(), Gender = '$gender', Profile = '$profile_date', Pic = '$picture_avail', Admin = '$admin', 
                                  SystemStatus='$status', CurTask='$curtask', Birthday='$birthday',
                                  HeartBeat = NOW(), LastSeen = NOW(), SessionID = '$sessionid' 
                            WHERE USERID = '$REMOTE_USER'");
   }
   else {
      $result = _mysql_do("INSERT INTO opus.ONLINE (Browser, SessionID, Account, UserID, Heartbeat, Age, Gender, SystemStatus, E_Code, City, Province, Country) VALUES ('$bb', '$sessionid', '$REMOTE_ACCOUNT', '$REMOTE_USER', NOW(), '$REMOTE_AGE', '$REMOTE_GENDER', '$REMOTE_STATUS', '$_SESSION[E_Code]', \"$REMOTE_CITY\", '$REMOTE_PROVINCE', '$REMOTE_COUNTRY')");
      $result = _mysql_do("UPDATE ACCOUNTS SET REMOTE_AGENT = '$bb', REMOTE_ADDR = '$REMOTE_ADDR', TempHost = '$HTTP_HOST', QMCookie ='$QMCookie', OnlineStatus = 'online', refresh = NOW()
                            WHERE USERID = '$REMOTE_USER'");
   }
   $result = _mysql_get_records("SELECT COUNT(MID) AS newMail FROM MESSAGES WHERE Account = '$REMOTE_ACCOUNT' AND Status = 'Unread'", &$unread);

   $result = _mysql_get_records("SELECT UNIX_TIMESTAMP(Date) AS Date, fromUser FROM MESSAGES WHERE Account = '$REMOTE_ACCOUNT' AND Status = 'Unread' ORDER BY Date DESC", &$unreadinfo);
   if ($result > 0) {
      $returnString['newMessageDate'] = $unreadinfo[0][Date];
      $returnString['newMessageFrom'] = $unreadinfo[0][fromUser];
      $returnString['newMail'] = $unread[0][newMail];
      $returnString['newMessages'] = $unread[0][newMail];
   };

//   $result = _mysql_get_records("SELECT COUNT(MID) AS newMail FROM MESSAGES WHERE Account = '$REMOTE_ACCOUNT' AND Flag = '1'", &$unread);

   $returnString['userAccount'] = $REMOTE_ACCOUNT;
   $returnString['userId'] = $REMOTE_USER;
   $returnString['city'] = $REMOTE_CITY;
}


$result = _mysql_get_records("SELECT * FROM cglbrd.STATISTICS", &$stats);

$returnString['online'] = $stats[0][Online] + 0;
$returnString['authenticated'] = $REMOTE_ACCOUNT ? true : false;
$returnString['success'] = true;
$returnString['reason']  = $reason;

print json_encode($returnString);

exit;

?>


<?php


if ($REMOTE_ACCOUNT) {
    list($TZONE,$TSAVE) = split("\|",$REMOTE_ZONE);
    $int_min = get_minutes($TZONE);    
    $f0 = _mysql_get_records("SELECT ACCOUNTS.SystemStatus,  Refresh, ACCOUNTS.AcceptPage, ACCOUNTS.Limit_QMR, 
                                     ACCOUNTS.E_Code, ONLINE.QMStatus, ACCOUNTS.City,
                                     ONLINE.CurTask, TourGuide, ACCOUNTS.Gender, ONLINE.Location, ONLINE.Account AS OAccount,
                                     ACCOUNTS.Country, ONLINE.QM_Site, ONLINE.LastActivityChange, 
                                     ACCOUNTS.Province, ACTOPTIONS.MailViewPop, ACCOUNTS.Birthdate, 
                                     ACCOUNTS.QMCookie, DATE_FORMAT(DATE_ADD(now(), INTERVAL $int_min MINUTE), '%l:%i %p') As CurrentTime,
                                     PROFILES.SystemStatus AS P_SystemStatus, DATE_FORMAT(PROFILES.DateModified,'%Y%m%d') AS P_DateModified,
                                     PROFILES.AdultSharing, PROFILES.Pic, PROFILES.PRID,
                                     PROFILES.Breast, PROFILES.PenisLength, PROFILES.PenisGirth, PROFILES.CutUncut, PROFILES.Pubic, PROFILES.Role, PROFILES.SexualFrequency, PROFILES.BodyHair, PROFILES.SexualActivity
                                FROM ACCOUNTS
                           LEFT JOIN ONLINE
                                  ON ACCOUNTS.Account = ONLINE.Account
                           LEFT JOIN ACTOPTIONS
                                  ON ACTOPTIONS.Account = ACCOUNTS.Account
                           LEFT JOIN PROFILES
                                  ON PROFILES.Account = ACCOUNTS.Account
                               WHERE ACCOUNTS.Account = '$REMOTE_ACCOUNT'", &$account); 

    $timezone = $REMOTE_ZONE;
    $gender   = $account[0]["Gender"];
    $admin    = $REMOTE_ADMIN;
    $location_set = $location;
    $location = $location ? $location : "Logging On";
    $location = $location ? $location : $account[0]["Location"];
    $acceptpage = $account[0]["AcceptPage"];
    $curtask  = $account[0]["CurTask"];
    $province = $account[0]["Province"];
    $city     = $account[0]["City"];
    $e_code   = $account[0]["E_Code"];
    $status   = $account[0]["SystemStatus"];
    $age      = $REMOTE_AGE;
    $country  = $account[0]["Country"];
    $qmstatus = !$account[0]["QMStatus"] ? $account[0]["AcceptPage"] : $account[0]["QMStatus"];
    $tagline  = ereg_replace("\"","",$account[0]["BioLine"]);
    $mailview = $account[0]["MailViewPop"];
    $birthday = date("m-d") == substr($account[0]["Birthdate"],5,5) ? 1 : 0;
    $QMCookie = $$REMOTE_ACCOUNT ? $$REMOTE_ACCOUNT : $account[0]["QMCookie"];

    $browserupg = preg_match("/Mozilla\/4.7[0-9]/",$HTTP_USER_AGENT) ? "NS" : "MSIE";     
 
    if (!$$REMOTE_ACCOUNT) { 
          //QMCookie not set, so set ACCOUNTS.QMCookie
          $QMCookie = $$REMOTE_ACCOUNT ? $$REMOTE_ACCOUNT : $account[0]["QMCookie"];
          setcookie("$REMOTE_ACCOUNT","$QMCookie",time() + 12592000,"/","");
    }
    if (!$account[0]["OAccount"]) { 
         print "hey";
         if ( $REMOTE_PERM & $GC_ENHANCED) { 1; } else {
             //first seen back to GayCanada - new ONLINE table entry
             //reset qm cookie to default
             $vCookie = "$REMOTE_PROVINCE"."&&&UserID&&&";
             setcookie("$REMOTE_ACCOUNT","$vCookie",time() + 12592000,"/","");
         }
    }


    if ($location_set != $account[0]["Location"]) {
        $location_now = "now()";
    }
    else {
        $location_now = "\"".$account[0]["LastActivityChange"]."\"";
    }
    
    if (!$account[0]["QM_Site"]) {
        // SEED RANDOM FOR QM SITE
        srand ((double) microtime() * 1000000);
        $randval     = rand(0,2);

        $qm_site  = array('qm3','qm3','qm');
        $qm_url   = "$qm_site[$randval].gaycanada.com";

    }
    else {
        $qm_url   = $account[0]["QM_Site"] ? $account[0]["QM_Site"] : "qm3.gaycanada.com";
    }

    $qm_url = "qm.$GC_DOMAIN";

    //$qm_url = $REMOTE_PERM & $GC_ReadACCT ?  'qm.gaycanada.com' : $qm_url;

    $profile_date  = $account[0]["P_DateModified"] ? $account[0]["P_DateModified"]."|".$account[0]["P_SystemStatus"]."|".$account[0]["PRID"] : 0;

    if ($account[0]["AdultSharing"] == 1 && ($account[0]["Breast"] || $account[0]["PenisLength"] || $account[0]["PenisGirth"] || $account[0]["CutUncut"] || $account[0]["Pubic"] || $account[0]["Role"] || $account[0]["SexualFrequency"] || $account[0]["BodyHair"] || $account[0]["SexualActivity"])) {
        $profile_date .= "|XXX";
    }

    $picture_avail = $account[0]["Pic"] ? 1 : 0;

//    if (($REMOTE_PERM & $GC_PREMIUM) && $profile && $touser[0]["Pic"]) {
               $found_pics = _mysql_get_records("SELECT Image,Caption,Adult FROM PICTURES
                                                  WHERE Account = '$REMOTE_ACCOUNT'
                                                    AND SystemStatus IN ('Active','Modified','Pending')
                                                    AND Adult = '0'
                                               ORDER BY ByDefault DESC",&$photos);
               $picture_avail = $photos[0]["Image"];
//    }
    
    /*
    **   Check if user is logging for first time, if so, then put them as 'New' from 'Pending, Reminded' in SystemStatus
    **  Then, update the refresh to the current datetime.
    */

    if ($account[0]["Refresh"] == "0000-00-00 00:00:00") {
          $mysql_link = mysql_connect("$MYSQL_DEFAULT_HOST","$MYSQL_DEFAULT_USERID","$MYSQL_DEFAULT_PASSWORD");
          mysql_select_db("opus", $mysql_link);

          $query  = "UPDATE ACCOUNTS SET Refresh = now(), SystemStatus = \"New\", Messages=\"\",MessageBy=\"\", TempHost = \"$HTTP_HOST\", REMOTE_ADDR = \"$REMOTE_ADDR\", REMOTE_AGENT = \"$HTTP_USER_AGENT\", QMCookie =\"$QMCookie\"
                     WHERE Account = \"$REMOTE_ACCOUNT\"";
          $result = mysql_query($query, $mysql_link);

          $query2 = "UPDATE ONLINE SET Account = \"$REMOTE_ACCOUNT\", UserID = \"$REMOTE_USER\", LastSeen = now(), Gender = \"$gender\", Profile = \"$profile_date\", Pic = \"$picture_avail\", Admin = \"$admin\", AcceptPage = \"$acceptpage\", Location = \"$location\", City=\"$city\", Province = \"$province\", E_Code=\"$e_code\", Age=\"$age\", SystemStatus=\"$status\", CurTask=\"$curtask\",Country=\"$country\",QM_Site=\"$qm_url\",LastActivityChange=$location_now,QMStatus=\"$qmstatus\", BioLine=\"$tagline\", Browser=\"$HTTP_USER_AGENT\", Birthday=\"$birthday\"
                     WHERE Account = \"$REMOTE_ACCOUNT\"";
          $result = mysql_query($query2,$mysql_link);

          mysql_close($mysql_link);

    }
    elseif (preg_match("/ActiveNote|Active2/", $account[0]["SystemStatus"])) {
       #ActiveNote is a flag to mark users that have received a letter. -tb
       $mysql_link = mysql_connect("$MYSQL_DEFAULT_HOST","$MYSQL_DEFAULT_USERID","$MYSQL_DEFAULT_PASSWORD");

          mysql_select_db("opus", $mysql_link);

          $query  = "UPDATE ACCOUNTS SET Refresh = now(), SystemStatus = \"Active\", TempHost = \"$HTTP_HOST\", REMOTE_ADDR = \"$REMOTE_ADDR\", REMOTE_AGENT = \"$HTTP_USER_AGENT\", QMCookie =\"$QMCookie\"
                     WHERE Account = \"$REMOTE_ACCOUNT\"";
          $result = mysql_query($query, $mysql_link);

          $query2 = "UPDATE ONLINE SET Account = \"$REMOTE_ACCOUNT\", UserID = \"$REMOTE_USER\", LastSeen = now(), Gender = \"$gender\", Profile = \"$profile_date\", Pic = \"$picture_avail\", Admin = \"$admin\", AcceptPage = \"$acceptpage\", Location = \"$location\", City=\"$city\", Province = \"$province\", E_Code=\"$e_code\", Age=\"$age\", SystemStatus=\"$status\", CurTask=\"$curtask\",Country=\"$country\",QM_Site=\"$qm_url\",LastActivityChange=$location_now,QMStatus=\"$qmstatus\", BioLine=\"$tagline\", Browser=\"$HTTP_USER_AGENT\", Birthday=\"$birthday\"
                     WHERE Account = \"$REMOTE_ACCOUNT\"";
          $result = mysql_query($query2,$mysql_link);
          mysql_close($mysql_link);
    }
    else {
          $mysql_link = mysql_connect("$MYSQL_DEFAULT_HOST","$MYSQL_DEFAULT_USERID","$MYSQL_DEFAULT_PASSWORD");
          mysql_select_db("opus", $mysql_link);
          $query  = "UPDATE ACCOUNTS SET Refresh = now(), Location =\"$location\", TempHost = \"$HTTP_HOST\", REMOTE_ADDR = \"$REMOTE_ADDR\", REMOTE_AGENT = \"$HTTP_USER_AGENT\", QMCookie =\"$QMCookie\"
                     WHERE Account = \"$REMOTE_ACCOUNT\"";
          $result = mysql_query($query, $mysql_link);

          $query2 = "UPDATE ONLINE SET Account = \"$REMOTE_ACCOUNT\", UserID = \"$REMOTE_USER\", LastSeen = now(), Gender = \"$gender\", Profile = \"$profile_date\", Pic = \"$picture_avail\", Admin = \"$admin\", AcceptPage = \"$acceptpage\", Location = \"$location\", City=\"$city\", Province = \"$province\", E_Code=\"$e_code\", Age =\"$age\", SystemStatus=\"$status\", CurTask=\"$curtask\",Country=\"$country\",QM_Site=\"$qm_url\",LastActivityChange=$location_now,QMStatus=\"$qmstatus\", BioLine=\"$tagline\", Browser=\"$HTTP_USER_AGENT\", Birthday=\"$birthday\"
                     WHERE Account = \"$REMOTE_ACCOUNT\"";
          $result = mysql_query($query2,$mysql_link);
          mysql_close($mysql_link);
    }

#    $mysql_link = mysql_connect("$MYSQL_DEFAULT_HOST","$MYSQL_DEFAULT_USERID","$MYSQL_DEFAULT_PASSWORD");
#    mysql_select_db("opus", $mysql_link);    
#    $query_delete = "DELETE FROM ONLINE WHERE Account=\"0\" AND UserID =\"$REMOTE_ADDR\"";
#    $result = mysql_query($query_delete,$mysql_link);
#    mysql_close($mysql_link);

    $notify   = _mysql_get_records("SELECT UserID, MID FROM QMESSAGES WHERE Account = '$REMOTE_ACCOUNT' AND Page = '1' ORDER BY Date Desc", &$paged);

   // $notify=1;
} 
else {
   // No Account
   if (!$PAGER_SWITCH && $LOG_NONMEMBERS) {
      $location = $location == 'qMessenger' ? '' : $location;
      $location = $location ? $location : 'Uknown - Non Member';
      $mysql_link = mysql_connect("$MYSQL_DEFAULT_HOST","$MYSQL_DEFAULT_USERID","$MYSQL_DEFAULT_PASSWORD");
      mysql_select_db("opus", $mysql_link);

      $query2 = "REPLACE INTO ONLINE SET Account = \"0\", UserID = \"$REMOTE_ADDR\", LastSeen = now(), Gender = \"\", Profile = \"\", Pic = \"\", Admin = \"0\", AcceptPage = \"0\", Location = \"$location\", Province = \"\", E_Code=\"\", Age =\"\", SystemStatus=\"NonMember\", CurTask=\"0\",Country=\"\"";
      $result = mysql_query($query2,$mysql_link);
      mysql_close($mysql_link);
   }
}

$REFRESH       = $REMOTE_ACCOUNT && $f0 ? $account[0]["Limit_QMR"] : "180";
$microrefresh  = $REFRESH * 1000;
$CURRENT_TIME  = $account[0]["CurrentTime"] ? $account[0]["CurrentTime"] : date ("h:i A");

$found     = _mysql_get_records("SELECT Chatting, Online FROM cglbrd.STATISTICS", &$stats);
$online    = $stats[0]["Online"];
$chat_msg  = $stats[0]["Chatting"] > 0 ? $stats[0]["Chatting"] . " Chatting" : "Click to Chat";

if ($REMOTE_ACCOUNT) {
   _mysql_get_records("SELECT QM FROM opus.ADMIN",&$qmadmin);
   $PAGER_SWITCH = $qmadmin[0]["QM"];

   $PAGERON  = $account[0]["AcceptPage"] > 1  ? "<a class=rollr href=\"javascript:pop('http://$qm_url/?pager_status=1',0,'pager')\" onMouseOver=\"window.status='Turn your Quick Messenger Off'; return true\" onMouseOut=\"window.status=''; return true\" target=\"_self\">(On)</a>" : "<a class=rollr href=\"javascript:pop('http://$qm_url/index.php?pager_status=1',0)\" onMouseOver=\"window.status='Turn your Quick Messenger On'; return true\" onMouseOut=\"window.status=''; return true\" target=\"_self\">(OFF)</a>";
   $PAGER    = $PAGER_SWITCH             ? "<a class=rollr href=\"javascript:pop('http://$qm_url/?extended=1&focus=1',0,'pager')\" onMouseOver=\"window.status='Quicklyx send messages to another member online'; return true\" onMouseOut=\"window.status=''; return true\" target=\"_self\">qMessenger</a>" : "<a class=rollr href=\"javascript:alert('The Quick Messenger is temporarily offline.\\nPlease use the Chat to communicate with others on the system.');\">qMessenger</a>";

   $m1       = _mysql_get_records("SELECT FromUser, Account FROM MESSAGES WHERE Account = '$REMOTE_ACCOUNT' AND Flag ='1' AND Mailbox NOT IN ('Trash','Pages')", &$messagecheck);
   $helpdk   = _mysql_get_records("SELECT ID FROM helpdesk.TICKETS WHERE Account = '$REMOTE_ACCOUNT' AND Closed = '1' AND Viewed = '0'", &$helpdesk);
   if ($helpdk) {
      $GOTMAIL  = "gotMail('set',$helpdk,'hd');";
   }
   elseif ($m1) {
      $MESSAGES = "(<a class=rollr href=\"/GC_mygaycanada/messages/view_box.php?Mailbox=Inbox\" onMouseOver=\"window.status='View My Mail Inbox'; return true\" onMouseOut=\"window.status=''; return true\" target=\"gcmain\"><BLINK>$m1 New</Blink></a>)";
      $GOTMAIL  = "gotMail('set',$m1,'mm');";
   }
   else {
      $CLEARVAR = "gotMail('clear',0);";
   }
   $now = time();
   if ($notify && $account[0]["CurTask"] == "0" && $PAGER_SWITCH && $browserupg == "MSIE") { 
            $CONSOLE_ONLOAD = "top.console.pop('http://$qm_url/index.php?clear=1&extended=1&time=$now&focus=1');";
   } 

   if ($REMOTE_ADMIN > 1) {
       $ADMIN_POP = 'popAdmin();';
   }

   $MY_MAIL_LINK = $mailview == 1 ?  "<a class=rollr href=\"/GC_mygaycanada/messages/view_box.php?Mailbox=Inbox\" onMouseOver=\"window.status='View My Mail Inbox'; return true\" onMouseOut=\"window.status=''; return true\" target=gcmain>My Mail</a> <a target=\"_self\" href=\"javascript:top.pop('/GC_mygaycanada/messages/compose.php?pop=$mailview&do=1',0,'compose','480','800')\" onMouseOver=\"window.status='Compose a new Mail Message'; return true\" onMouseOut=\"window.status=''; return true\" onclick=\"top.pop('/GC_mygaycanada/messages/compose.php?pop=$mailview&do=1',0,'compose','490','800')\"><img align=top src=\"/GC_mygaycanada/messages/gifs/coolcompose.gif\" alt=\"Quick Compose a New Message\" border=0></a>" :  "<a class=rollr href=\"/GC_mygaycanada/messages/view_box.php?Mailbox=Inbox\" onMouseOver=\"window.status='View My Mail Inbox'; return true\" onMouseOut=\"window.status=''; return true\" target=gcmain>My Mail</a> <a target=\"gcmain\" href=\"/GC_mygaycanada/messages/compose.php?pop=$mailview&do=1\" onMouseOver=\"window.status='Compose a new Mail Message'; return true\" onMouseOut=\"window.status=''; return true\"><img align=top src=\"/GC_mygaycanada/messages/gifs/coolcompose.gif\" alt=\"Quick Compose a New Message\" border=0></a>";
//   $MY_MAIL_LINK = $REMOTE_PERM & $GC_ENHANCED  ?  "<a class=rollr href=\"/messages/view_box_new.php?Mailbox=Inbox\" onMouseOver=\"window.status='View My Mail Inbox'; return true\" onMouseOut=\"window.status=''; return true\" target=gcmain>My Mail</a> <a target=\"gcmain\" href=\"/messages/compose_new.php?pop=$mailview&do=1\" onMouseOver=\"window.status='Compose a new Mail Message'; return true\" onMouseOut=\"window.status=''; return true\"><img align=top src=\"/messages/gifs/coolcompose.gif\" alt=\"Quick Compose a New Message\" border=0></a>" : $MY_MAIL_LINK;

//   if ($account[0]["TourGuide"]) {
//      $FIRST_TIME="firstTime();";  
//   }

}

$ONLINE     = $REMOTE_USER  ? "<a class=rollr id=black href=\"javascript:top.console.pop('http://$qm_url/?extended=1&focus=1',0,'pager')\" onMouseOver=\"window.status='See who is online in the Chat Room'; return true\" onMouseOut=\"window.status=''; return true\" target=\"gcmain\">" : "<A href=\"javascript:show_online();\" target=\"_self\" class=rollr>"; 
$ONLINE     = $REMOTE_USER && $PAGER_SWITCH ? $ONLINE: "<a class=rollr href=\"javascript:alert('The Quick Messenger is temporarily offline for upgrades.\\nPlease use the Chat to communicate with others on the system.');\">";
$ONLINE_CHAT_CODE = "$online $ONLINE online</A> &middot; <A CLASS=rollr HREF=\"javascript:top.console.pop('http://realchat.gaycanada.com/?nick=$REMOTE_USER',0,'chat',440,640);\">$chat_msg</a>";
$LOGIN      = $REMOTE_ACCOUNT ? "<A CLASS=rollr HREF=\"/index.php?logout=1&logoutto=/index.php\" onMouseOver=\"window.status='Log out and close your GayCanada session.'; return true\" onMouseOut=\"window.status=''; return true\" target=\"_top\">Logout</a>" : "<A class=rollr HREF=\"/signup\" target=\"gcmain\">Join</a> &middot; <A class=rollr TARGET=\"gcmain\" href=\"/login.php\">Login</a>";
$time       = $REMOTE_ACCOUNT ? "<a class=rollr href=\"javascript:top.pop('/GC_mygaycanada/prompts.php?prompt=timezone',0,'prompt','120','520')\"  onMouseOver=\"window.status='Quicklyx change your time zone'; return true\" onMouseOut=\"window.status=''; return true\">$CURRENT_TIME</a>" : $CURRENT_TIME;
$BACKGROUND = "console_oct.gif";
$REMOTE_STRING = "";

if ($REMOTE_USER) {
   $REMOTE_STRING = "$MY_MAIL_LINK $MESSAGES &middot; <A CLASS=rollr HREF=\"/GC_mygaycanada/profiles/indexn.php?page=2:13\" TARGET=\"gcmain\"  onMouseOver=\"window.status='Update or set up your Public Profile'; return true\" onMouseOut=\"window.status=''; return true\">My Profile</A> &middot; $PAGER";
   if ($PAGER_SWITCH) {
      $REMOTE_STRING = "$REMOTE_STRING $PAGERON";
   }
}

unset($account);
$bgcolor = $bgcolor ? $bgcolor : "800000";

?>

<HTML>
<HEAD>
<META HTTP-EQUIV="Pragma" content="no-cache">
<meta name="robots" content="noindex,nofollow">
<STYLE>
<!--
  a {text-decoration:none;}
  TD { text-decoration: none;
       color:WHITE;
       font-size:9pt;
       font-weight:bold;
       font-style:normal;
       font-family:'Trebuchet MS','MS Sans Serif','Arial','Helvetica';
  }
  td.blanche       { text-decoration: none; color:white; }
  .rollo        { text-decoration:none;       color:#FFFFFF; }
  .rollo:hover  { text-decoration: underline; color:#FFCC33; }
  .rollr        { text-decoration: none;      color:#FFFFFF; }
  .rollr:hover  { text-decoration: underline; color:#FFDE9B; }

  body.consoleFrame         { background-color: #4C0202; font-family: Arial;  }
  body.consoleFrame #login  { display: block; font-size: 11px; color:white; font-weight: normal; padding: 3px; position: absolute; top: 0px; left: 610px;}
  body.consoleFrame #time   { display: block; font-size: 11px; color:white; font-weight: normal; width: 89px; padding: 3; position: absolute; top: 0px; left: 690px; border: solid 0px; border-color: #FFFFFF; text-align: center;
                              filter:progid:DXImageTransform.Microsoft.Gradient(  GradientType=0,StartColorStr=#800000, EndColorStr=#4c0202); }

  body.consoleFrame #regular { display: block; font-size: 11px; color:white; font-weight: normal; width: 690px; padding: 3; position: absolute; top: 0px; left: 1px; border: solid 0px; border-color: #FFFFFF; text-align: left;
                               filter:progid:DXImageTransform.Microsoft.Gradient(  GradientType=0,StartColorStr=#800000, EndColorStr=#4c0202); }

-->
</STYLE>
</HEAD>
<BODY CLASS=consoleFrame <?php print "onLoad=\"$CONSOLE_ONLOAD $GOTMAIL $FIRST_TIME $ADMIN_POP $CLEARVAR\""; ?>  VLINK="#000000" ALINK="#940000" LINK="#000000" TEXT="#000000" RIGHTMARGIN="0" LEFTMARGIN="0" TOPMARGIN="0" MARGINWIDTH="0" MARGINHEIGHT="0">
<BASE TARGET="gcmain">

<SCRIPT LANGUAGE="JavaScript">
<!--
   var qm;
   <?php if ($REMOTE_ACCOUNT) { ?>
   top.vUnread_Pages= <?= $notify ?>;
   top.vUnread_Msgs = <?= $m1 ?>;
   <?php } ?>
   if (top.vUnread_Msgs) {
        top.vRefreshMail = 1;
   }


   function cX(e) {  
     //close all child windows
     if (qm && qm.open && !qm.closed) {
         qm.close();     
     }
     top.location.href= '/index.php?logout=1&logoutto=/index.php';
   }

   function pop(my, keepsame,name,h,w,status,resize) {
      h = h ? h : <?= $HEIGHT ?>;
      w = w ? w : <?= $WIDTH ?>;
      status = status ? status  : 0;
      resize = resize ? 0  : 1;
      LeftPosition = (screen.width) ? (screen.width-w)/2 : 0;
      TopPosition = (screen.height) ? (screen.height-h)/2 : 0;
      name = name ? name : 'pager';

      if (keepsame) {
         window.location = my;
      }
      else {
         qm = window.open (my,name,'screenY='+TopPosition+',screenX='+LeftPosition+'top='+TopPosition+',left='+LeftPosition+',toolbar=0,location=0,directories=0,status='+status+',menubar=0,scrollbars=yes,width='+w+',height='+h+',resizable='+resize); 
      }
   }

   function newMail(my,keepsame,name,h,w,status,resize) {
       nm = window.open (my,name,'screenY=100,screenX=100,top=100,left=100,toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=no,width='+w+',height='+h+',resizable=0'); 
   }

   function go(address) {
      parent.gcmain.location.href=address;
   }

   function show_online() {
       alert('Only logged in Members\ncan view who is online.\nCheck out the Quick Messenger to send\ninstant messages!');
       return;
   }

   function gotMail(action,x,n) {
      // This function shows confirm message
      if (!top.vAcceptHelpDesk && action == 'set' && n == 'hd') {
         newMail('/GC_mygaycanada/messages/newmail.php?B='+x,0,'helpdesk',100,330,0);
      }
      else if (!top.vAcceptToRead && action == 'set' && n == 'mm') {
         newMail('/GC_mygaycanada/messages/newmail.php?A='+x,0,'newmail',100,330,0);
      }
      else if (action == 'clear') {
         top.vAcceptToRead = 0;
         top.vAcceptHelpDesk = 0;
      }
   }       
   function popAdmin() {
       if (top.vAdminPop == 0) {
           top.pop('/pop_admin.php',0,'admin_pop',200,450);
           top.vAdminPop = 1;
       }
   }

   function firstTime() {
      top.pop('/GC_mygaycanada/firsttime.php',0,'firsttime','300','450');
   }

//-->
</SCRIPT>
<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript" SRC="/Library/ajax.js"></SCRIPT>
<DIV ID=GC_console>Loading console ..
<SCRIPT LANGUAGE="JavaScript">
<!--

   initChannel(<?=$microrefresh?>, '/quickconsole.php');

// -->
</SCRIPT>
</BODY>
</HTML>
