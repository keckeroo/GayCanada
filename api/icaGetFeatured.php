<?php  

$HTTP_ROOT = $DOCUMENT_ROOT;
$time = time();

$server       = split("\.",$SERVER_ADDR);

$REQUIRED = $login_required ? $login_required : 0;

// following line is local file only.
require "/home/gaycanada/Library/html2.php";


gatekeeper($TT_Username_OPTIONAL);

if (!$REMOTE_USER) {
//   exit;
}

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: application/xml; charset=utf-8");

print "<icaResponse status='Success'>\n";

$display = $display ? $display : 'profile';

if ($REMOTE_ID != $userid || $display == 'profile') {
   $restrict_disabled = "AND `enabled` = 'Yes'";
}


$num_photos = _mysql_get_records("SELECT * FROM PICTURES
                                   WHERE Account = '$account'
                                     AND Enabled = 'Yes'
                                     AND SystemStatus IN ('Active','Modified','Pending')
                                ORDER BY ByDefault DESC ",&$photolist);

#$num_photos = _mysql_get_records("SELECT PICTURES*, UNIX_TIMESTAMP(thumbnail_updated) AS ts, Users.acls, Users.username
#                                   FROM Photos LEFT JOIN Users ON (Photos.userid = Users.userid)
#                                   WHERE (Photos.userid = '$userid' OR Users.username = '$username')
#                                     AND Photos.gallery = '$gallery'
#                                     $restrict_disabled
#                                    ORDER BY Photos.date_added", &$photolist);

if ($userid == '') {
   $userid = $photolist[0][userid];
}
$dir = substr($userid, strlen($userid)-2, 2);

if ($num_photos > 0) {
   print "<photocount>$num_photos</photocount>\n";
   
   $gallery_access = 1;

   if ($gallery == 'private' && $userid != $REMOTE_ID && $REMOTE_USER != 'support') {
      $gallery_access = 0;

      if ($photolist[0][acls]) {
         $acls = json_decode($photolist[0][acls]);
         $listlength = sizeof($acls->{$gallery});
         for ($ll = 0; $ll < sizeof($acls->{$gallery}); $ll++) {
            if ($acls->{$gallery}[$ll]->{$REMOTE_ID} == $REMOTE_USER) {
                print "<granted>ACCESS GRANTED</granted>\n";
                $gallery_access = 1;
            }
         }
      }      
   }

   for ($i = 0; $i < $num_photos && $gallery_access; $i++) {
      print "<photoInfo>\n";
      print "<account>{$photolist[$i]['Account']}</account>\n";
      print "<photoid>{$photolist[$i]['ID']}</photoid>\n";
      print "<file>{$photolist[$i]['Image']}</file>\n";
      print "<url>/userfiles/photos/$dir/$userid/{$photolist[$i]['file']}_sm.jpg</url>\n";
      print "<origurl>/userfiles/photos/$dir/$userid/{$photolist[$i]['file']}.jpg</origurl>\n";
      print "<status>{$photolist[$i]['SystemStatus']}</status>\n";
      print "<adult>{$photolist[$i]['Adult']}</adult>\n";
      print "<height>{$photolist[$i][height]}</height>\n";
      print "<width>{$photolist[$i][width]}</width>\n";
#      print "<thumbnailconfig>{$photolist[$i][thumbnail_config]}</thumbnailconfig>\n";
#      print "<size>{$photolist[$i][size]}</size>\n";
      print "<caption><![CDATA[{$photolist[$i]['Caption']}]]></caption>\n";
#      print "<gallery>{$photolist[$i][gallery]}</gallery>\n";
#      print "<viewsthisweek>{$photolist[$i][views_this_week]}</viewsthisweek>\n";
#      print "<viewstotal>{$photolist[$i][views_total]}</viewstotal>\n";
#      print "<hotvotes>{$photolist[$i][hot_votes]}</hotvotes>\n";
      print "<dateadded>{$photolist[$i]['Date']}</dateadded>\n";
#      print "<ts>{$photolist[$i][ts]}</ts>\n";
      print "</photoInfo>\n";
   }
}

print "</icaResponse>\n";
?>