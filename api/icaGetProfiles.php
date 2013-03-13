<?php

require("$_SERVER[DOCUMENT_ROOT]/Library/authenticate.php");

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");

gatekeeper($TT_USERID_OPTIONAL);

$success = false;
$keyreqd = false;
$errormsg  = '';
$records = array();
$returnString = array();

$sex_code  =  array("1"=>"Male",
                    "2"=>"Female",
                    "4"=>"Trans, Male To Female",
                    "8"=>"Trans, Female To Male");

//if ($Iam || $UserID) {
//   $userid = $Iam ? $Iam : $UserID;
//}

$userid  = $userid ? $userid : $userid2;
$passkey = $passkey ? $passkey : "";

$count = _mysql_get_records("SELECT ACCOUNTS.UserID as MainID, PROFILES.*, DATE_FORMAT(PROFILES.DateCreated, '%b %d, %Y') AS Date_Created,
                                    DATE_FORMAT(PROFILES.DateModified, '%b %d, %Y') AS Date_Modified,
                                    DATE_FORMAT(ACCOUNTS.Refresh, '%b %d, %Y') AS Last_Seen,
                                    ACCOUNTS.Country, ACCOUNTS.City, ACCOUNTS.Province, ACCOUNTS.Service, IF(ACCOUNTS.Community, ACCOUNTS.Community, '') AS Community,
                                    STATUS_UPDATES.StatusUpdate
                             FROM ACCOUNTS LEFT JOIN PROFILES ON ACCOUNTS.Account = PROFILES.Account
                                           LEFT JOIN STATUS_UPDATES ON ACCOUNTS.Account = STATUS_UPDATES.Account
                             WHERE 
                                ACCOUNTS.UserID = '$userid' 
                               AND PROFILES.SystemStatus != 'Inactive' 
                          ORDER BY STATUS_UPDATES.DateEntered DESC
                             LIMIT 10", &$dataset);

$start = $start ? $start : 0;
$limit = $limit ? $limit : 25;
$searchstring = $query ? "AND ACCOUNTS.UserID LIKE '%" . $query . "%'" : "";
$filterSex    = $sex   ? "AND ACCOUNTS.Gender = '$sex'" : "";

$count = _mysql_get_records("SELECT SQL_CALC_FOUND_ROWS PROFILES.*, ACCOUNTS.Gender as Sex, ACCOUNTS.CityID, ACCOUNTS.DateEntered, ACCOUNTS.LastUpdated, ACCOUNTS.Refresh, geobytes.Cities.*
                               FROM ACCOUNTS LEFT JOIN PROFILES ON ACCOUNTS.Account = PROFILES.Account
                                      LEFT JOIN geobytes.Cities ON ACCOUNTS.CityID = geobytes.Cities.CityID
                              WHERE PROFILES.SystemStatus != 'Inactive'
                                $searchstring
                                $filterSex
                              LIMIT $start, $limit", &$dataset);

$accountid = $dataset[0]["Account"];
$pics = $dataset[0]["Pic"];
$returnString['totalFound'] = $mysql_found_rows;

if ($count) {
   // lets update the pics table for this user
   // this will eventually be removed
//   $result = _mysql_do("UPDATE PICTURES SET `enabled` = 'No' WHERE Account = '$accountid'");
//   $result = _mysql_do("UPDATE PICTURES SET `enabled` = 'Yes' WHERE Account = '$accountid' AND ID in ($pics)");   
}


if ($dataset[0]["AdultSharing"] == 1 && ($dataset[0]["Breast"] || $dataset[0]["PenisLength"] || $dataset[0]["PenisGirth"] || $dataset[0]["CutUncut"] || $dataset[0]["Pubic"] || $dataset[0]["Role"] || $dataset[0]["SexualFrequency"] || $dataset[0]["BodyHair"] || $dataset[0]["SexualActivity"])) {
   $record['AdultProfileAvailable'] = 1;
}

$prid      = $dataset[0]["PRID"];
if ($adultp == "1") $banner_zone = $banner_zone + 1;

$rc = count($mysql_fields);

for ($j = 0; $j < $count; $j++) {
   for ($i = 0; $i < $rc; $i++) {
       $record[$mysql_fields[$i]] = $dataset[$j][$mysql_fields[$i]];
   }
   array_push($records, $record);
}

$success = true; //$count > 0;
if ($count == 0) {
   $errormsg = 'No records found.';
   $keyreqd  = false;
}
else {
   if ($dataset[0]['Sharing'] == 'Private' && $dataset[0]['PassKey']) {
       // Passkey is set and sharing is private - require passkey
       $keyreqd = true;
       if ($passkey && $passkey == $dataset[0]['PassKey']) {
          $returnString['records'] = $records;
          $success = true;
          // Success - add tracking log ...
          $prid = $dataset[0]['PRID'];
          $result = _mysql_do("UPDATE PROFILES SET COUNTER = COUNTER + 1 WHERE PRID = '$prid'");
          $result = _mysql_do("INSERT INTO PROFILES_LOGS (`prid`, `account`, `userid`, `referrer`, `ip_address`, `date`) VALUES ('%s', '%s', '%s', '', '', NOW())", 
                    $prid, $REMOTE_ACCOUNT, $REMOTE_USER);
       }
       else {
          $errormsg = "Incorrect or missing passkey. Please try again.";
          $success = false;
       }
   }
   else {
      $result = _mysql_do("UPDATE PROFILES SET COUNTER = COUNTER + 1 WHERE PRID = '$prid'");
      $result = _mysql_do("INSERT INTO PROFILES_LOGS (`prid`, `account`, `userid`, `referrer`, `ip_address`, `date`) VALUES ('%s', '%s', '%s', '', '', NOW())", 
                $prid, $REMOTE_ACCOUNT, $REMOTE_USER);
      $returnString['logInserted'] = $result;
      $returnString['records'] = $records;
   }
}

$returnString['records'] = $records;
$returnString['keyreqd'] = $keyreqd;
$returnString['success'] = $success;
$returnString['errormsg']  = $errormsg;

print json_encode($returnString);
exit;



/////////////////////////////////

$passwordmatch = $QKQ == $dataset[0]["PassKey"] ? 1 : 0; 
$passwordmatch = $passwordmatch;

if ($REMOTE_PERM & $GC_PREMIUM) {
    $passwordmatch = 1;
}

if (!$found || $dataset[0]["SystemStatus"] == 'Disabled' || 
               ($dataset[0]["Sharing"] == "Private" && !$passwordmatch) ||
               ($dataset[0]["Sharing"] == "Restricted" && !$REMOTE_USER)) {
     ?>
     <CENTER>     
     <FORM METHOD=post ACTION="index.php">     
     <input type=hidden name=userid value="<?=$userid?>">
     <TABLE BORDER=0>     
     <TR valign=top><TD></TD>     
     <TD>     
     <TABLE CELLPADDING=0 CELLSPACING=0 BORDER=0 WIDTH=606>     
     <TR VALIGN=TOP><TD>
     </TD><TD>


         <?php if (!$found) { 
               alert("There is no member profile for $userid or this member does not exist at GayCanada.","error");
            }
            if ($profile[0]["Sharing"] == "Restricted" && !$REMOTE_USER) {
               $alert_msg  = "<table border=0 cellspacing=0 cellpadding=0 width=100%>";
               $alert_msg .= "<tr><td colspan=2>&nbsp;</TD></TR>";
               $alert_msg .= "<tr><td colspan=2>This profile can only be viewed by members of GayCanada who are logged in.</TD></TR>";
               //$alert_msg .= "<tr><td height=25 align=right>If you are using your GayCanada password, enter your Name&nbsp;</td><td><input type=text name=GayCanadaName size=15 value='$REMOTE_USER'> </td></tr>";
               $alert_msg .= "</table>";
               alert("<B>Restricted Profile</B> " . $alert_msg,'info');
            }
            if ($profile[0]["SystemStatus"] == 'Disabled') {
               alert("This member's profile is Disabled and requires immediate attention.  Until such time, it will be unviewable.","error");
            }
            if ($profile[0]["Sharing"] == "Public" || ($profile[0]["Sharing"] == "Private" && !$passwordmatch)) { 
               $alert_msg  = "<table border=0 cellspacing=0 cellpadding=0 width=100%>";
               $alert_msg .= "<tr><td colspan=2><B>Profile Requires Pass Key</B></td></tr>";
               $alert_msg .= "<tr><td colspan=2>$userid has set up a special password that is required to review their profile.  You can request
                                  their Pass Key or <a class=link href='http://$GC_URLS[GCMAIN]/index.php?main=/GC_mygaycanada/upgrade/index.php'><B>become a Platinum Member</a></B> and login into GayCanada (link below) to unlock this Profile.</td></tr>";
               $alert_msg .= "<tr><td height=40 align=right><B>Enter the Pass Key &nbsp;<td><input name=QKQ type=password size=15>&nbsp;<input type=submit name=submit value=Unlock></td></tr>";
               $alert_msg .= "<tr><td colspan=2 class=c04><img sr='http://$GC_URLS[IMAGES]/blank.gif' height=1 width=1></td></tr>";
               $alert_msg .= "<tr><td height=25><a class=link href='javascript:alert(\"Request a Passkey link coming soon.\");'>Request a Passkey</a></td><td height=25 align=right><a class=link href='http://$GC_URLS[PROFILES]/index.php?Iam=$userid&login_required=1'>Log into GayCanada and View this Profile</a></td></tr>";
               //$alert_msg .= "<tr><td height=25 align=right>If you are using your GayCanada password, enter your Name&nbsp;</td><td><input type=text name=GayCanadaName size=15 value='$REMOTE_USER'> </td></tr>";
               $alert_msg .= "</table>";
               alert("<B>Action Required</B> " . $alert_msg,'info');
            }
     end_profile();
  }
  elseif ($found) {

    $TZONE = $TZONE ? $TZONE : 0;
    $found2 = _mysql_get_records("SELECT Admin,Refresh, DATE_FORMAT(DateEntered, '%b %d, %Y') AS Date_Entered,
                                         DATE_FORMAT(DATE_ADD(Refresh, INTERVAL $TZONE HOUR), '%b %d, %Y at %l:%m %p') AS Refresh_,
                                         UNIX_TIMESTAMP(now()) AS NOW_UNIX,
                                         UNIX_TIMESTAMP(Refresh) AS REFRESH_UNIX, Service,
                                         DATEDIFF(now(),DateEntered) AS DaysCalc
                                    FROM ACCOUNTS
                                   WHERE Account = '$accountid'", &$account);


    $background = $account[0]["Admin"] ? "back-profile-admin.gif" : "back-profile.gif";  
    $cur_online = ($account[0]["NOW_UNIX"] - $account[0]["REFRESH_UNIX"]) <= 180 ? 1 : 0;
    $DOMAIN = $REMOTE_TEMPHOST ? $REMOTE_TEMPHOST : "www.gaycanada.com";

    $profile[0]["Interests"] = eregi_replace(",",", ", $profile[0]["Interests"]);
    $profile[0]["Height"] = eregi_replace(",", "'", $profile[0]["Height"]);
	
    //### Group Stats into comma delimited.
    $hair  = $profile[0]["HairColour"] ? $profile[0]["HairColour"] ." hair" : "";
    $eyes  = $profile[0]["EyeColour"] ? $profile[0]["EyeColour"] . " eyes" : "";
    $stats = array ($profile[0]["Age"], $profile[0]["MaritalStatus"], $profile[0]["Height"], $profile[0]["Weight"], $hair , $eyes);

    for ($i=0;$i<count($stats); $i++) {
       if ($stats[$i]) {
          $NewStats[] = $stats[$i];
       }
    }
    $STATS = join(", ", $NewStats);
    //### End

    $attributes  = ereg_replace(",",", ",$profile[0]["Attributes"]);
    $personality = ereg_replace(",",", ",$profile[0]["Personality"]);
    $languages = ereg_replace(",",", ",$profile[0]["Languages"]);
    $profile[0]["DateCreated"] = substr($profile[0]["DateCreated"],0,10);
    $profile[0]["DateModified"] = substr($profile[0]["DateModified"],0,10);

    $loggedin = $cur_online ? "<FONT COLOR=\"#FFC606\"><b>Member is Online!</B></font>" : "Last seen on ".$account[0]["Refresh_"];
    $counter  = ++$profile[0]["Counter"];

    $reviews = _mysql_get_records("SELECT * FROM cglbrd.REVIEWS WHERE UserID = \"$userid\"", &$reviews_);
    $blogs   = $profile[0]["Weblog"];

    $mysql_link = mysql_connect("$MYSQL_DEFAULT_HOST", "$MYSQL_DEFAULT_USERID","$MYSQL_DEFAULT_PASSWORD");
    mysql_select_db("opus", $mysql_link);   
  
    if ($REMOTE_PERM & $GC_PREMIUM) {
       $query1   = "UPDATE PROFILES_WATCH SET DateVisited = now() WHERE PRID = \"$prid\" AND Account=\"$REMOTE_ACCOUNT\"";
       $result  = mysql_query($query1, $mysql_link);
    }
    if (!$ni) {
       //no increment counter
       $query   = "UPDATE PROFILES SET Counter = \"$counter\" WHERE PRID = \"$prid\"";
       $result  = mysql_query($query, $mysql_link);
    }

//    if ($profile[0]["ProfileLog"]) {
        $section = $tab ? $tab : "Profile";  
        $GC_LL_Cookie = split("\;",$GC_LL);
        $REMOTE_USER_COOKIE = $REMOTE_USER ? $REMOTE_USER : $GC_LL_Cookie[1];
        $query2  = "INSERT INTO PROFILES_LOGS SET PRID=\"$prid\", Section=\"$section\", Account=\"$REMOTE_ACCOUNT\", UserID=\"$REMOTE_USER_COOKIE\", Date = now(), IP_Address=\"$REMOTE_ADDR\", Referrer=\"$HTTP_REFERER\"";
        $result2 = mysql_query($query2, $mysql_link);
//    }

    mysql_close($mysql_link);

    if ($profile[0]["WebURL"]) {
       $website = preg_match("/^http:\/\//i", $profile[0]["WebURL"]) && $profile[0]["WebURL"] ? "<a class=link href=\"http://$domain/open.php?location=".$profile[0]["WebURL"]."\" target=\"_blank\">Click to Visit My Web Site</a>" : "<a href=\"http://$domain/open.php?location=http://".$profile[0]["WebURL"]."\" target=\"_blank\">Click to Visit My Web Site!</a>";    
    }
#    $website = $profile[0]["WebURL"] && $REMOTE_PERM & $GC_ENHANCED ? $website : "<a href=\"javascript:alert('This Member has a website you must be logged in and an Enhanced Member to view');\">My Web Site</a>";
#    $website = $profile[0]["WebURL"] && $REMOTE_PERM & $GC_ENHANCED ? $website : "<a href=\"javascript:alert('This Member has a website you must be logged in and an Enhanced Member to view');\">My Web Site</a>";

    $adduser = "<a  onmouseover=\"window.status='Add user to MyFriends address book'; return true\" onMouseout=\"window.status=''; return true\"  href=\"http://$GC_URLS[GCMAIN]/index.php?main=/friends/?edit_contact%3D1%26MemberID%3D$userid\" target=_top><img border=0 hspace=1 vspace=1 align=absmiddle src=\"http://$GC_URLS[IMAGES]/niftyicons/add_contact3.gif\"> Add to MyFriends</a>";
    $city    = $profile[0]["City"];

    $NAME    = $profile[0]["Name"] ? $profile[0]["Name"] : $profile[0]["UserID"];


        
//    if ($profile[0]["Sharing"] == "Public" || ($profile[0]["Sharing"] == "Private" && $passwordmatch)) { }

    $found_cglbrd_city = _mysql_get_records("SELECT City, Province, Num_Profiles FROM cglbrd.CITIES WHERE City = \"$city\" LIMIT 1", &$cglbrd_city);
    if ($found_cglbrd_city) {    
        $page         = $cglbrd_city[0]["City"].$cglbrd_city[0]["Province"].".html";
        $page         = preg_replace("/[\/ ']/","",$page);
        $short_prov   = strtolower($profile[0]["Province"]);
        $city_line    = $cglbrd_city[0]["Num_Profiles"] >= '10' ? "<a class=link href=\"http://$GC_URLS[GCMAIN]/index.php?main=%2FGC_community%2Fprofiles%2Findex.php%3Fuse_html%3D1%26citypage=$page%26province[]%3D".$profile[0]["Province"]."%26city%3D".$cglbrd_city[0]["City"]."\" target=_top>There are ".$cglbrd_city[0]["Num_Profiles"]." profiles in the ".$cglbrd_city[0]["City"]." area.</a>&nbsp;<br>" : "";
    }

    $smoker_status = $profile[0]["Smoke"] ? "<B>Smoker:</B> " . $profile[0]["Smoke"] : "";
    $prov = strtolower($profile[0]["Province"]);
    $city = strtolower($profile[0]["City"]);
    $district = $profile[0]["District"] ? "<BR><B>District/Community:</B> ".$profile[0]["District"] : "";

    if ($profile[0]["AdultSharing"] == 1 && ($profile[0]["Breast"] || $profile[0]["PenisLength"] || $profile[0]["PenisGirth"] || $profile[0]["CutUncut"] || $profile[0]["Pubic"] || $profile[0]["Role"] || $profile[0]["SexualFrequency"] || $profile[0]["BodyHair"] || $profile[0]["SexualActivity"])) {
        $apa = 1; //adult profile available
    }

    _mysql_get_records("SELECT * FROM HEADERS WHERE Scope = 'Profiles' ORDER BY RAND() LIMIT 1", &$header);

    if (($adultp && $profile[0]["AdultSharing"] == 1 && !$REMOTE_PERM) ){ // || ($photosp && !$REMOTE_PERM)) {
        gatekeeper($TT_USERID_REQUIRED);
    }

    ?>
    
    <CENTER>
    <FORM METHOD=post ACTION="/index.php">
    <TABLE BORDER=0>
    <TR valign=top><TD></TD>
    <TD>
    <TABLE CELLPADDING=0 CELLSPACING=0 BORDER=0 WIDTH=606>
    <TR VALIGN=TOP><TD>
    </TD><TD>
    <TABLE CELLPADDING=0 CELLSPACING=0 BORDER=0 WIDTH=604>
    <TR VALIGN=TOP>
        <TD WIDTH=1 VALIGN=BOTTOM><IMG SRC=http://images.gaycanada.com/profiles/left-top_corner.gif WIDTH=4 HEIGHT=4 BORDER=0></TD>
        <TD WIDTH=602 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" height=1 width=1></TD>
        <TD WIDTH=1 VALIGN=BOTTOM ALIGN=RIGHT><IMG SRC=http://images.gaycanada.com/profiles/right-top_corner.gif WIDTH=4 HEIGHT=4 BORDER=0></TD>
    </TR>

    <TR><TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
        <TD WIDTH=602 BGCOLOR="#000000" ID=white>
        <table cellspacing="0" cellPadding="0" width="100%" border="0">
        <tr>
           <td background="/gifs/backgrounds/gc_adbar_bg.gif"><a href="http://www.gaycanada.com" target=_top><img src="http://images.gaycanada.com/logos/gc_logo_new1.gif" border=0  width=77 height=67></a></td>
           <td background="/gifs/backgrounds/gc_adbar_bg.gif" align=left><table border=0 cellpadding=0 cellspacing=0 width=225>
               <tr><td><a href="http://www.gaycanada.com" target=_top><img src="/gifs/logos/gc_logo_right1.gif" border=0 width=225 height=47></a></td></tr>
               <tr><td><a href="http://www.gaycanada.com" target=_top><img src="/gifs/logos/glb_anim_new.gif" border=0 width=225 height=20></a></td></tr>
               </table>
          </td>
          <td background="/gifs/backgrounds/gc_adbar_bg.gif" width=476 align=right><div id=membername><?=$profile[0]["UserID"]?>'s Profile&nbsp;</div>
              <a href="http://<?= $HTTP_HOST ?>/index.php?Iam=<?= $profile[0]["UserID"] ?>">http://<?= $HTTP_HOST ?>/<?= $profile[0]["UserID"] ?></a>&nbsp;</td>
        </TR>
        </TABLE>
        </TD>
        <TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
    </TR>
	
    <TR><TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
        <TD WIDTH=602 CLASS=c03 ALIGN=CENTER>

        <TABLE BORDER=0 CELLPADDING=1 CELLSPACING=0 WIDTH=95%>
        <TR VALIGN=TOP><TD ><div id=white><?= $loggedin ?><BR><?=$account[0]["Service"]?> Member with <B><?=number_format($counter)?> Total Hits</B><BR>Member Since <B><?=$account[0]["Date_Entered"]?></b> or <b><?=number_format($account[0]["DaysCalc"])?> Days</b></div></TD>
            <TD ID=white ALIGN=RIGHT>
                <table border=0 cellspacing=0 cellpadding=0>
                <tr><td class=white_text align=right>Last Updated:</td><td class=white_text>&nbsp;<?= $profile[0]["Date_Modified"] ?></td></tr>
                <tr><td class=white_text align=right>First Created:</td><td class=white_text>&nbsp;<?= $profile[0]["Date_Created"] ?></td></tr>
                <tr><td class=white_text align=right>Profile Status:</td><td class=white_text>&nbsp;<?= $profile[0]["SystemStatus"] ?></TD></TR>
                </table>
            </TD>
        </TR>
        </TABLE>
        </TD>
        <TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
    </TR>

    <TR><TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
        <TD WIDTH=602 CLASS=c03 ALIGN=CENTER><hr class=dashline width=99%></td>
        <TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
    </TR>
    </TABLE>

    <TABLE CELLPADDING=0 CELLSPACING=0 BORDER=0 WIDTH=604>
    <TR><TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
        <TD WIDTH=602 CLASS=c03 ALIGN=CENTER>

        <TABLE BORDER=0 CELLPADDING=3 CELLSPACING=0 WIDTH=95%>
        <?php

        for ($i=0;$i<count($images);$i++){
             $found_pics = _mysql_get_records("SELECT Image,Caption,Adult FROM PICTURES 
                                                WHERE Account = '$accountid' 
                                                  AND ID = '$images[$i]'
                                                  AND SystemStatus IN ('Active','Modified','Pending')
                                             ORDER BY ByDefault DESC ",&$photos);
             if ($i==0 && $photos[0]["Image"]) {
                 $adult = $photos[0]["Adult"] ? 1 : 0;
                 $name = $photos[0]["Image"];
                 $picture = "t_$name";
                 $pic_lg  = "$name";

                 $PicLink = "<a href=\"javascript:pop('pictureviewer.php?view=1&loc=PROFILES&id=$images[$i]&user=".$profile[0]["UserID"]."&accountid=$accountid&record=$prid&pic=1',0,'PV')\">";
                 $galleryLink = "<a href=\"#\" id=\"primary_photo_zoom_url\" onClick=\"NewWindow('http://profiles.gaycanada.com/imagegallery.php?id=$accountid&currentIter=$i','name','750','600','no');return false\">";
                 $Pic = $photos[0]["Image"] ? "$galleryLink<img galleryimg=\"no\" id=primary_photo border=0 src=\"http://pics.gaycanada.com/thumbs/$picture\" onload=\"vZoom.add(this, 'http://pics.gaycanada.com/$pic_lg');\" ALT=\"\"></a>" : "<img src=\"http://images.gaycanada.com/profiles/npa1.jpg\">"; 
                 $photo_description = $photos[0]["Caption"] ? "<DIV ID=white><B>Photo Caption:</B><BR><I>".$photos[0]["Caption"]."</I></DIV>" : "";
             }
        }
        ?> <TR VALIGN=TOP><TD ALIGN=CENTER><?php
                 $Pic = $photos[0]["Image"] ? "$Pic" : "<img src=\"http://images.gaycanada.com/profiles/npa1.jpg\">"; 
                 $Pic = $photos[0]["Image"] && $adult ? "$galleryLink<img src=\"http://images.gaycanada.com/profiles/xrated3.gif\" ALT=\"X-Rated Photo\" border=0></a>" : "$Pic";
                 print "$Pic<br>";
        ?>  </TD>
            <TD ALIGN=LEFT class=dashline BGCOLOR=#FFFFFF><?php
                 $tabs = "<a href=\"http://$GC_URLS[PROFILES]/index.php?Iam=$userid&ni=1&QKQ=$QKQ\" target=_top>MyProfile</a>";
                 if ($userid == $REMOTE_USER) { $tabs .= ",<a href=\"http://$GC_URLS[GCMAIN]/?main=%2FGC_mygaycanada%2Fprofiles%2Findexn.php%3FEdit_Ad%3D1%26Account%3D$accountid%26page%3D2%3A13\">Edit</a>";}

                 if (count($images) > 1){ $tabs .= ",<a href=\"index.php?Iam=$userid&photosp=1&ni=1&tab=Photos&QKQ=$QKQ\">Photos</a>"; }
                 if ($apa)       { $tabs .= ",<a href=\"index.php?Iam=$userid&adultp=1&ni=1&tab=Adult&QKQ=$QKQ\">Adult</a> "; }
                 if ($reviews)   { $tabs .= ",<a href=\"index.php?Iam=$userid&reviewsp=1&ni=1&tab=Reviews&QKQ=$QKQ \">Reviews</a> "; }

                 tabs($tabs,'100%','$tab'); ?>
                <div id=dfn><I><B><?= $NAME ?></B></I></DIV><DIV ID=bodytext><?= $STATS ?></DIV><DI ID=bodytext><?= $profile[0]["Other"] ?></DIV>
                <br>
                <table border=0 cellspacing=0 cellpaddinbg=0 width=100%>
                <tr><td class=bodytext><B>Gender:</B> <?=$sex_code[$profile[0]["Gender"]] ?> &nbsp;&nbsp;<B>Age:</B> <?= $profile[0]["Age"] ?>&nbsp;&nbsp;<?=$smoker_status?>

                    
                    </td>
                    
                    <TD align=center rowspan=2><?php if ($profile[0]["Sign"]) { ?><div style='background-color:#5D190B; border:1px solid #940000; width:150px; color:white;'><img align=top src="http://images.gaycanada.com/profiles/<? echo(strtolower($profile[0]["Sign"])); ?>.gif"> <?= $profile[0]["Sign"] ?></div><? } ?>
                     <!-- <a href="http://<?=$GC_URLS[PROFILES]?>/index.php?Iam=<?=$userid?>&rate=1&prid=<?=$prid?>"><img border=0 alt="Thumbs Up!" src="http://<?=$GC_URLS[IMAGES]?>/niftyicons/thumbsup.gif"></a> &nbsp; <a href="http://<?=$GC_URLS[PROFILES]?>/index.php?Iam=<?=$userid?>&rate=2&prid=<?=$prid?>"><img alt="Thumbs Down!" border=0 src="http://<?=$GC_URLS[IMAGES]?>/niftyicons/thumbsdown.gif"></a><br>
                     <B><?=$numb_up?></b> up, <b><?=$numb_dn?></b> down<br><i>click to vote</i>
                     -->
                     </td>
                </tr>
                <tr><td class=bodytext><B>I Live In:</B> <? print $profile[0]["City"].", ".$profile[0]["Province"]." &nbsp; <B>Country:</b> ".$profile[0]["Country"]."$district"; ?>
                    <BR><?=$city_line?></td></tr>
                <? if ($profile[0]["Vac_City"]) { ?>
                <tr><td colspan=2><? alert("<B>I am currently on Vacation and Visiting:</B><br>
                                           <img src=\"/gifs/blank.gif\" width=20 height=5><B>Location:</B> ".$profile[0]["Vac_City"].", ".$profile[0]["Vac_Province"]." &nbsp; &nbsp; <B>Country:</B> ".$profile[0]["Vac_Country"]."",travel);
                                  ?>
                    </td>
                </tr>
                <? } ?>
                <tr><td class=bodytext><?=$website?></td></tr>
                </table>

            </TD>
        </TR>     
        <TR><TD COLSPAN=3><?=$photo_description?></TD></TR>
        </TABLE>
        </TD>
        <TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://<?=$GC_URLS[IMAGES]?>/misc/blank.gif" width=1></TD>
    </TR>

    <TR><TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://<?=$GC_URLS[IMAGES]?>/misc/blank.gif" width=1></TD>
        <TD WIDTH=602 CLASS=c05 ALIGN=CENTER>
        <TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=95% CENTER>
        <TR><TD height=50 class=white_text>&nbsp;Jump to New Member Name:<BR>&nbsp;<INPUT TYPE=TEXT SIZE=18 NAME=Iam STYLE="background-color:FFCC33; font-weight: bold; color:black; border: 1px solid FFFFFF"> <INPUT STYLE="border: 1px solid BLACK" TYPE=SUBMIT NAME=GO VALUE=GO></TD>
            <TD height=50 class=white_text><a  onmouseover="window.status='Send a Message'; return true" onMouseout="window.status=''; return true" href="http://<?=$GC_URLS[GCMAIN]?>/index.php?main=%2FGC_mygaycanada/messages%2Fcompose.php%3Ffor%3D<?=$userid?>"><img align=absmiddle hspace=1 border=0 src="http://<?=$GC_URLS[GCMAIN]?>/GC_mygaycanada/messages/gifs/coolcompose.gif"> Send Message</a><br>
                <?=$adduser?></TD>
            <TD height=50 class=white_text align=left><? if ($REMOTE_PERM & $GC_ENHANCED) { ?> <a onmouseover="window.status='Add to your HotList?'; return true" onMouseout="window.status=''; return true" href="javascript:hotlist();"><img border=0 align=absmiddle src="http://<?=$GC_URLS[IMAGES]?>/niftyicons/hotlist.gif" height=16 hspace=1> Add to your HotList <? } ?>
                <BR>                                                                           <B><a onmouseover="window.status='Notifies you when this Profile Updates'; return true" onMouseout="window.status=''; return true"  style="line-height:18px" href="javascript:watchlist();">Notify Me when this Profile Updates</a></B></td>
        </TR>
        </TABLE>
        </TD>
        <TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
    </TR>

    <TR><TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
        <TD WIDTH=602 CLASS=c03 ALIGN=CENTER><hr class=dashline width=99%></td>
        <TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
    </TR>

    <? if ($adultp && $profile[0]["AdultSharing"] == 1 && $REMOTE_PERM & $GC_CX_RATED) { 
           if ($profile[0]["PenisLength"]) {
               $pl = $profile[0]["PenisLength"];
               _mysql_get_records("SELECT OptionID FROM FORMFIELDS WHERE FieldID = 'PenisLength' AND ValueID = '$pl'",&$pl_id);
               $profile[0]["PenisLength"] = $pl_id[0]["OptionID"];
           }
           ?>
           <!-- ADULT STUFF -->
           <TR><TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
               <TD WIDTH=602 CLASS=c03 ALIGN=CENTER>
                  <TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1 WIDTH=95%>
                  <TR BGCOLOR="RED">
                      <TD CLASS=questions COLSPAN=2>&nbsp;My Adult Related Information...</B></TD>
                  </TR>
                  <? if ($profile[0]["Breast"]) { ?><TR VALIGN=TOP><TD CLASS=response>My Breast Size is:</td><td WIDTH=60% CLASS=response><?= $profile[0]["Breast"] ?></td></tr><? } ?>
                  <? if ($profile[0]["PenisLength"]) { ?><TR VALIGN=TOP><TD CLASS=response>My Penis Length is:</td><td WIDTH=60% CLASS=response><?= $profile[0]["PenisLength"] ?></td></tr><? } ?>
                  <? if ($profile[0]["PenisGirth"]) { ?><TR VALIGN=TOP><TD CLASS=response>My Penis Thickness is:</td><td WIDTH=60% CLASS=response><?= $profile[0]["PenisGirth"] ?></td></tr><? } ?>
                  <? if ($profile[0]["CutUncut"]) { ?><TR VALIGN=TOP><TD CLASS=response>I am :</td><td WIDTH=60% CLASS=response><?= $profile[0]["CutUncut"] ?></td></tr><? } ?>
                  <? if ($profile[0]["Pubic"]) { ?><TR VALIGN=TOP><TD CLASS=response>I keep My Pubic Area:</td><td WIDTH=60% CLASS=response><?= $profile[0]["Pubic"] ?></td></tr><? } ?>
                  <? if ($profile[0]["Role"]) { ?><TR VALIGN=TOP><TD CLASS=response>My Sexual Role:</td><td WIDTH=60% CLASS=response><?= $profile[0]["Role"] ?></td></tr><? } ?>
                  <? if ($profile[0]["SexualFrequency"]) { ?><TR VALIGN=TOP><TD CLASS=response>I have Sex:</td><td WIDTH=60% CLASS=response><?= $profile[0]["SexualFrequency"] ?></td></tr><? } ?>
                  <? if ($profile[0]["BodyHair"])   { ?><TR VALIGN=TOP><TD CLASS=response>Body Hair:</td><td WIDTH=60% CLASS=response><?= ereg_replace(",",", ",$profile[0]["BodyHair"]); ?></td></tr><? } ?>
                  <? if ($profile[0]["SexualActivity"])   { ?><TR VALIGN=TOP><TD CLASS=response>Sexual Activities:</td><td WIDTH=60% CLASS=response><?= ereg_replace(",",", ",$profile[0]["SexualActivity"]); ?></td></tr><? } ?>
                  </TABLE>
               </TD>
               <TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
           </TR>
           <!-- END ADULT STUFF -->
    <? } 
       elseif($adultp) {
              ?>
           <TR><TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
               <TD WIDTH=602 class=c03 ALIGN=CENTER>
               <TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1 WIDTH=95%>
               <TR><TD class=c02>
                   <?
                   $upgrade_message = "Want to see the Adult Details of this Member? This Member has detailed information that is
                                       not visable to your Account.<P>  Click on the link below to Upgrade your Account.";
                   reach_limit($REMOTE_ACCOUNT,0,"ADULT DETAILS","100%");
                   ?>
                </td></tr>
                </TABLE>
                </TD>
                <TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
           </TR>
            <!-- END ADULT STUFF -->    
    <? }

    if ($reviewsp) { 

           ?>
           <TR><TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
               <TD WIDTH=602 CLASS=c03 ALIGN=CENTER>
                  <TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1 WIDTH=95%>
                  <TR BGCOLOR="YELLOW">
                      <TD CLASS=questions COLSPAN=2>&nbsp;My Reviews...</B></TD>
                  </TR>
                  <TR><TD COLSPAN=2>
                     <TABLE BORDER=0 CELLPADDING=1 CELLSPACING=0 WIDTH=100%>
               
                     <?
                     for ($i=0;$i<$reviews;$i++) {
                          $entry_id = $reviews_[$i]["EntryID"];
                          unset($entry);
                          _mysql_get_records("SELECT * FROM cglbrd.ENTRIES WHERE EntryID = '$entry_id'",&$entry);
                          print "<tr><td bgcolor=white height=28><B>".$entry[0]["Title"]."</B></td>
                                     <td bgcolor=white align=right>".$entry[0]["City"].", ".$entry[0]["Province"]."</td></tr>\n";
                          print "<tr><td bgcolor=white colspan=2><div style='margin-left:10'><I>&quot;".$reviews_[$i]["Comments"]."&quot;</I><br>Rated ".$reviews_[$i]["Rating"]." out of 5</div></td></tr>\n";
                     }
                     ?>

                     </TABLE>
                  </TD></TR>
                  </TABLE>
               </TD>
               <TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
           </TR>
           <?
    }

    if ($blogsp) { 

           ?>
           <TR><TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
               <TD WIDTH=602 CLASS=c03 ALIGN=CENTER>
                  <TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1 WIDTH=95%>
                  <TR BGCOLOR="TAN">
                      <TD CLASS=questions COLSPAN=2>&nbsp;My WebLogs...</B></TD>
                  </TR>
                  <TR><TD COLSPAN=2>
                     <TABLE BORDER=0 CELLPADDING=1 CELLSPACING=0 WIDTH=100%>
               
                     <?
                     $found_weblogs = _mysql_get_records("SELECT * FROM WEBLOGS_SETUP
                                                       LEFT JOIN WEBLOGS_DAILY
                                                              ON WEBLOGS_DAILY.ID = WEBLOGS_SETUP.ID
                                                           WHERE PRID=\"$prid\" AND WEBLOGS_SETUP.ID=\"$blogs\" 
                                                        ORDER BY DateEntered DESC",&$weblogs);
                     for ($i=0;$i<$found_weblogs;$i++) {
                          $wid = $weblogs[$i]["WID"];
                          print "<tr><td bgcolor=white height=28><B>".$weblogs[$i]["Date"]."</B></td>
                                     <td bgcolor=white align=right>".$weblogs[$i]["Subject"]."</td></tr>\n";
                          print "<tr><td bgcolor=white colspan=2><div style='margin-left:10'>".$weblogs[$i]["DailyBlog"]."</div></td></tr>\n";
                     }
                     ?>

                     </TABLE>
                  </TD></TR>
                  </TABLE>
               </TD>
               <TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
           </TR>
           <?
    }
     

    if ($photosp) {
        ?>
 
        <!-- PHOTOS PAGE -->
        <TR><TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
            <TD WIDTH=602 CLASS=c03 ALIGN=CENTER>
            <TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1 WIDTH=95%>
            <TR CLASS=c05>
                <TD CLASS=white_text COLSPAN=3>&nbsp;<B>My Photos</B></TD>
            </TR>

            <?
            //print "<tr><td colspan=3>"; print_r($images); print "</td></tr>";
            for ($i=0;$i<count($images);$i++){            
                $count = $count ? $count : 1;
                _mysql_get_records("SELECT * FROM PICTURES WHERE ID = '$images[$i]'",&$photos);
                $adult = $photos[0]["Adult"] ? 1 : 0;
                $piclink = "<a href=\"javascript:pop('pictureviewer.php?view=1&loc=PROFILES&id=$images[$i]&user=".$profile[0]["UserID"]."&accountid=$accountid&record=$prid&pic=$count',0,'PV');\">";
                $galleryLink = "<a href=\"#\" onClick=\"NewWindow('http://profiles.gaycanada.com/imagegallery.php?id=$accountid&currentIter=$i','name','750','600','no');return false\">";
//                $galleryLink = "<a href=\"http://profiles.gaycanada.com/imagegallery.php?id=$accountid&currentIter=$i\" onclick=\"NewWindow(this.href,'name','750','600','no');return false\">";
                $Pic = $photos[0]["Image"] ? "$galleryLink<img oncontextmenu=\"return false;\" galleryimg=\"no\" onmousedown=\"return false;\" onmousemove=\"return false;\"  border=0 src=\"http://pics.gaycanada.com/thumbs/t_".$photos[0]["Image"]."\" ALT=\"Click to Enlarge\" border=0></a>" : "<img src=\"http://images.gaycanada.com/profiles/npa.gif\">"; 
                $Pic = $photos[0]["Image"] && $adult ? "$galleryLink<img src=\"http://images.gaycanada.com/profiles/xrated3.gif\" ALT=\"X-Rated Photo\" border=0></a>" : "$Pic";
                $photo_description = $photos[0]["Caption"] ? "<DIV ID=white><B>Photo Caption:</B><BR><I>".$photos[0]["Caption"]."</I></DIV>" : "";
                if ($count == 1)                             print "<tr valign=top>\n";
                                                             print "<td>$Pic<br>$photo_description</td>\n";
                if ($count == 3 || $i+1 == count($images)) { print "</tr>\n\n";
                                                             $count = 1;
                } else {
                  $count++;
                }
            }
            ?>
 
            </TABLE>
            </TD>
            <TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
        </TR>     
        <!-- END PHOTOS PAGE -->
        <?
    }
    elseif ($photosp && !$REMOTE_PERM) {
        ?>
        <!-- PHOTOS PAGE -->
        <TR><TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
            <TD WIDTH=602 class=c03 ALIGN=CENTER>
            <TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1 WIDTH=95%>
            <TR CLASS=c05>
                <TD CLASS=white_text COLSPAN=3>&nbsp;<B>My Photos</B></TD>
            </TR>
            <TR VALIGN=TOP><TD CLASS=response>This member may have more Photos that could be visible... You need to be a Member to view
                 the other photos.
            </TD></TR>
            </TABLE>
            </TD>
            <TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
        </TR>
        <!-- END ADULT STUFF -->    
        <?
    }

     end_profile();
}



function end_profile() {
     global $banner_zone,$noad, $time, $banner_code, $REMOTE_PERM, $GC_ENHANCED;
     ?>
     <TR><TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
         <TD WIDTH=602 CLASS=c03 ALIGN=CENTER height=40><B><font color=white>Copyright &copy; 1994-2008 CGLBRD Ltd. <br> Building relationships online...</td>
         <TD WIDTH=1 BGCOLOR="#940000"><IMG SRC="http://images.gaycanada.com/misc/blank.gif" width=1></TD>
     </TR> 

     </TABLE>
     </FORM>
    </TD>
</TR>
</TABLE>

</TD>
</TR>
</TABLE>
<DIV STYLE="display:none;"> 

</DIV>
<? 
}
?>
</BODY>
