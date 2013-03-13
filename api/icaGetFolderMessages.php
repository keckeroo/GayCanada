<?php  

$mailbox = $icaMailFolder ? $icaMailFolder : "Inbox"; 

$REQUIRED = 1;
require "$_SERVER[DOCUMENT_ROOT]/Library/html2.php";
$GC_SECTION = 'My GayCanada';
require_active();
$page = $page ? $page : "2:9:1";


header("Content-Type: application/xml; charset=utf-8");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

print "<messageList>\n";


    $MYSQL_DATABASE = "opus";

    $fromto          = $mailbox == "Sent" ? "To" : "From";
    $sentreceived    = $mailbox == "Sent" ? "Sent" : "Received";
    $found           = 0;

    _mysql_get_records("SELECT Mail_FontSize, Mail_Boxes,Limit_ExpiryDays, Limit_NumMsgs,MailViewPop
                          FROM ACCOUNTS
                     LEFT JOIN ACTOPTIONS
                            ON ACTOPTIONS.Account = ACCOUNTS.Account
                         WHERE ACTOPTIONS.Account  = '$REMOTE_ACCOUNT'", &$account);

    $int_min = get_minutes($REMOTE_ZONE);

    list ($font, $size) = explode("\|", $account[0]["Mail_FontSize"], 2);

    $sizes  = array ("/\-2/","/\-1/","/\+1/","/\+2/");
    $values = array ("8", "10","12","14");

    $sizenew = preg_replace($sizes, $values,$size);

    $boxes = explode("\|", $account[0]["Mail_Boxes"]);
    $limit_expirydays = $account[0]["Limit_ExpiryDays"];
    $limit_nummsgs    = $account[0]["Limit_NumMsgs"];


    $orderby = $orderby ? $orderby : "Date";
    $desc    = $desc    ? $desc    : "DESC";

    $descu   = $desc == "DESC" ? "ASC" : "DESC";
    $updown  = $desc == "DESC" ? "<img src=\"/gifs/buttons/down.gif\">" : "<img src=\"/gifs/buttons/up.gif\">";
    $updowndate  = $orderby == "Date" || $orderby == "Date" ? $updown : "";
    $updownsub   = $orderby == "Subject" ? $updown : "";
    $updownfrom  = $orderby == "FromUser" ? $updown : "";

    $found_total = _mysql_get_records("SELECT COUNT(MID) AS Total FROM MESSAGES WHERE Account = '$REMOTE_ACCOUNT' AND Mailbox='$mailbox'",&$total_msgs);
    $found_total = $total_msgs[0]["Total"];

    if (!$tm) {
         $total = _mysql_get_records("SELECT COUNT(MID) AS MID
                                      FROM MESSAGES
                                      WHERE Account = '$REMOTE_ACCOUNT'
                                      AND MailBox = '$mailbox'
                                      LIMIT $limit_nummsgs", &$mail_total);
    }
    $found = $tm ? $tm : $mail_total[0]["MID"];
#    $found = $tm > $found_total ? $found_total : $tm;

    $start = $index_start ? $index_start : $start;
    $end   = $index_end   ? $index_end   : $end;


    if ($found_total < $limit_nummsgs) {
        if ($start && $end) {
            $LIMIT_STRING  = "$start,20";
        } else {
            $start = 0;
            $end   = 19;
            $LIMIT_STRING  = "0,20";
        }
    }
    else if ($found_total > $limit_nummsgs) {
        $start = $start ? $start : 0;
        $LIMIT_STRING = "$start,20";
    }
    else {
        $LIMIT_STRING = "0,$limit_nummsgs";
    }
# print "$found_total,$limit_nummsgs,$LIMIT_STRING";

    $mailget = _mysql_get_records("SELECT *, 
                                 LEFT(Subject,45) AS Subject,
                                 DATE_FORMAT(DATE_ADD(now(), INTERVAL $int_min MINUTE), '%a, %b %d') AS RightNow,
                                 DATE_FORMAT(DATE_ADD(Date, INTERVAL $int_min MINUTE), '%a, %b %d') AS DateShortYesterday,
                                 DATE_FORMAT(DATE_ADD(Date, INTERVAL $int_min MINUTE), '%h:%i %p') AS DateShortToday,
                                 DATE_FORMAT(DATE_ADD(Received, INTERVAL $int_min MINUTE), '%m/%d/%Y %l:%i %p') AS ReceivedShort,
                                 REPLACE(FromUser, ',', ', ') AS FromUser
                                 FROM MESSAGES
                                 WHERE Account = '$REMOTE_ACCOUNT'
                                   AND MailBox = '$mailbox'
                                 ORDER BY $orderby $desc
                                 ", &$mail);

#print "<br>($mailget, $REMOTE_ACCOUNT, $int_min, $REMOTE_ZONE)<br>";
    if ($REMOTE_TRACE) { trace_account($REMOTE_ACCOUNT,"Viewing Mailbox: $mailbox"); }   

    $MSG_MID = $mail[0]["MID"];
    //$DISABLED = $mailbox == 'Trash' ? "DISABLED" : "";

    $selectall = "<input name=\"selall\" $DISABLED type=\"checkbox\" value=\"Check All\" onClick=\"CheckAll();\">";

    if ($found > 15) { // && $found < $limit_nummsgs) {
        $mail_index = "\n&nbsp;<SELECT NAME=MailIndex size=1 onChange=\"msgInd(this.options[this.selectedIndex].value)\">\n\t<option value='null'>Messages</option>\n";
        $found = $found < $limit_nummsgs ? $found : $limit_nummsgs;
        for ($f=0;$f<$found;$f++) {
            $c=$f+1; $d=$f; 
            $t= $f+20 > $found ? $found : $f+20;
            $s=$t-1;
            $index_selected = ($d == $start && $s == $end) ? "SELECTED" : "";
            if (preg_match("/^0$/",$f))           { $mail_index .= "\t<option $index_selected value='/GC_mygaycanada/messages/view_box.php?Mailbox=$mailbox&start=$d&end=$s&index=$f&tm=$found&orderby=$orderby&desc=$desc'>Viewing Messages $c - $t</option>\n"; }
            if (preg_match("/(0|2|4|6|8)0$/",$f)) { $mail_index .= "\t<option $index_selected value='/GC_mygaycanada/messages/view_box.php?Mailbox=$mailbox&start=$d&end=$s&index=$f&tm=$found&orderby=$orderby&desc=$desc'>$c - $t</option>\n"; }
        }
        $mail_index .= "</SELECT>\n\n";
    } 

    if ($return_message) {
        alert($return_message,$return_icon);
    }

    $current_datetime = get_gc_time($account[0]["Zone"],"D, M d, h:i a",0);

    $line = 0;
    for ($m=0; $m < $mailget; $m++) {
        $mail_fromuser  = $mail[$m]["FromUser"];
        $mail_mid       = $mail[$m]["MID"];
        $mail_mailbox   = $mail[$m]["Mailbox"];      
        $mail_receivedshort = $mail[$m]["ReceivedShort"];
        $mail_subject   = $mail[$m]["Subject"];
        $mail_dateshort = $mail[$m]["RightNow"] == $mail[$m]["DateShortYesterday"] ? $mail[$m]["DateShortToday"] : $mail[$m]["DateShortYesterday"];

        // For the NEXT/PREV Buttons
        $nmid           = $mail[$m+1]["MID"];
        $pmid           = $mail[$m-1]["MID"];
 
        $status = "";
        $BG = $line %2 ? "#FFFFFF" : "#FFF6E7";
        $boldon = "";
        $boldoff = "";
        if ($mail[$m]["Direction"] == 'Reply') {
           $switchto = 'repliedto';
           $icon = "<IMG BORDER=0 ALIGN=TOP NAME=\"STAT$line\" src=\"/GC_mygaycanada/messages/gifs/erepliedto.gif\" alt=\"Replied To\">";
        }
        else if ($mail[$m]["Direction"] == 'Forward') {
           $switchto = 'forwardto';
           $icon = "<IMG BORDER=0 ALIGN=TOP NAME=\"STAT$line\" src=\"/GC_mygaycanada/messages/gifs/eforwardedto.gif\" alt=\"Forwarded\">";
        }
        else if ($mail[$m]["Flag"]) {
            $switchto = 'read';
           $icon = "<IMG BORDER=0 ALIGN=TOP NAME=\"STAT$line\" src=\"/GC_mygaycanada/messages/gifs/unopened_2002.gif\" ALT=\"New Message\">";
//           $boldon = "<B>";
//           $boldoff = "</B>";
        }
        else {
           $switchto = 'read';
           $icon = "<IMG BORDER=0 ALIGN=TOP NAME=\"STAT$line\" src=\"/GC_mygaycanada/messages/gifs/opened_2002.gif\" alt=\"Old Message\">";
	}

        $found_contact = _mysql_get_records("SELECT MemberID, Status FROM contacts.RECORDS 
                                             WHERE UserID = '$REMOTE_USER' AND MemberID = '$mail_fromuser'", &$contacts);
        if ($found_contact) {
           if ($contacts[0]["Status"] == "Priority") {
              $status = "<span class=priority>!</span>";
           }
           else if ($contacts[0]["Status"] == "Block") {
              $status = "<img src=\"/gifs/gcicons/errormessage.gif\">";
           }
           else {
              $status = "&nbsp;";
           }
        }
        else {
           $status = "&nbsp;";
        }

print "<messageHeader>\n";
print "   <mid>$mail_mid</mid>\n";
print "   <from>$mail_fromuser</from>\n";
print "   <subject>$mail_subject</subject>\n";
print "   <date>{$mail[$m]['Date']}</date>\n";
print "   <status>{$mail[$m]['Status']}</status>\n";
print "</messageHeader>\n";


        $tr_onclick  = " onClick=\"javascript: mail('$mail_mid','$mail_mailbox','$mail_fromuser','$switchto','$line'); lineSel(this);\" ";
        $link        = "<A style=\"color:940000\" href=\"javascript: mail('$mail_mid','$mail_mailbox','$mail_fromuser','$switchto','$line','$nmid','$pmid','$orderby','$desc','$LIMIT_STRING','0','$start','$end')\" onClick=\"lineSel(this);\" name=\"$line\">";
        $folder      = $mailbox == "Trash" ? "Inbox"    : "Trash";
        $checkdelete =  "<input type=\"checkbox\" name=itemSelected[] value=\"$mail_mid\" onClick=\"checkIt(this);\">";
        $addeditcont   = !preg_match("/Non Member|AutoAdmin/i", $mail[$m]["FromUser"]) && $mailbox != "Sent" ? "<a href=\"/GC_mygaycanada/friends/?edit_contact=1&MemberID=$mail_fromuser&page=2:4:0\"><img src=\"/GC_mygaycanada/messages/gifs/contacts_2002.gif\" border=0 ALT=\"Add/Edit $mail_fromuser's Contact Info\"></a>" : "&nbsp;";

        $checkbox = "<IMG BORDER=0 NAME=\"POINT$line\" SRC=\"/gifs/blank.gif\" HEIGHT=11 WIDTH=1>";
        $recdate = substr($mail[$m]["Date"], 0, 16);

#      print "<TR ID=\"$line\" bgcolor=\"$BG\">
#                   <td>$checkdelete</td>
#                   <td>$status $checkbox $status $icon $link$mail_fromuser</a></td>
#                   <td>$mail_subject</td>
#                   <td NOWRAP>$mail_dateshort</td>
#                   <td>$addeditcont</td>
#               </tr>\n";
        ++$line;
    }      
#    if (!$mailget && $mailbox == "QMessages" && !($REMOTE_PERM & $GC_ENHANCED)) {
#            print "<tr><td colspan=7 class=msg height=50><B>Function not Enabled</B><br>This folder will only show \"Saved Quick Messages\" if your account is set up to
#                        save Quick Messages.  For more information on how to add this feature view My Account > My Features.</td></tr>";
#    }
#    elseif (!$mailget && $mailbox == "QMessages" && ($REMOTE_PERM & $GC_ENHANCED)) {
#            print "<tr><td colspan=7 class=msg height=50>This folder will only show \"Saved Quick Messages\" if your account is set up to
#                        save Quick Messages. When you get a Quick Message from My qMessenger, just click the save button to save it into 
#                        this folder.</td></tr>";
#    }

print "</messageList>\n";
?>