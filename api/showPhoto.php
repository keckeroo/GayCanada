<?php
// Name: showPhoto.php
// Date Created: 2011-10-26
// Last Updated: 2011-11-09
//
// Use: Displays all account images respecting photo rights and settings.
//
// Revision History :
// 2011-11-01 - updated to access image file based on passed parameters rather than
//              looking up info in database. Saves overhead on database queries and
//              allows for a self-contained image server without the need for a database.
// 2011-11-09 - finished coding so that image serving is all encoded within URL and can
//              function without access to database for permissions.
//

require("$_SERVER[DOCUMENT_ROOT]/lib/authenticate.php");

$NOPHOTO_SRC   = "../resources/images/nophoto.jpg";
$PRIVATE_SRC   = "../resources/images/private.jpg";
$PHOTO_ROOT    = "/home/squirt.userfiles/images";
$PHOTO_SUBROOT = "";
$PHOTO_SRC     = $NOPHOTO_SRC;
$PHOTO_ACCESS  = false;

$PHOTO_ID      = isset($_REQUEST['id']) ? $_REQUEST['id'] : '0';     // Photo ID
$PHOTO_FKEY    = isset($_REQUEST['fk']) ? $_REQUEST['fk'] : '0';     // Foreign Key 
$PHOTO_TYPE    = isset($_REQUEST['pt']) ? $_REQUEST['pt'] : '0';     // Photo type
$PHOTO_SIZE    = isset($_REQUEST['ps']) ? $_REQUEST['ps'] : 't';     // Size to show
$PHOTO_PRIVATE = isset($_REQUEST['pf']) ? $_REQUEST['pf'] : '0';     // Private flag
$PHOTO_HASH    = isset($_REQUEST['hash']) ? $_REQUEST['hash'] : '0'; // Hash

// 
// If the private flag is turned on we have to see if this user is allowed
// to view the picture.
//
// hash = MD5($PHOTO_ID, $PHOTO_FKEY, $PHOTO_TYPE, $PHOTO_PRIVATE [, $REMOTE_PROFILEID ])

$computed_hash_without = MD5($PHOTO_ID . $PHOTO_FKEY . $PHOTO_TYPE . $PHOTO_PRIVATE );
$computed_hash_with    = MD5($PHOTO_ID . $PHOTO_FKEY . $PHOTO_TYPE . $PHOTO_PRIVATE . $REMOTE_PROFILEID );

if ($PHOTO_HASH == $computed_hash_with || $PHOTO_HASH == $computed_hash_without) {
   if ($PHOTO_PRIVATE != '0') {
      $PHOTO_SRC = $PRIVATE_SRC;

      if ($computed_hash_with == $PHOTO_HASH) {
         $PHOTO_ACCESS = true;
      }
   }
   else {
       $PHOTO_ACCESS = true;
   }
}

if ($PHOTO_ACCESS) {
   switch ($PHOTO_TYPE) {
      case 20 : $PHOTO_SUBROOT = "profiles"; break;
      case 21 : $PHOTO_SUBROOT = "emails";   break;
      case 4  : $PHOTO_SUBROOT = "cruising"; break;
   }
   $paddedid = sprintf("%010d", $PHOTO_FKEY);
   $part1 = substr($paddedid, 7, 3);
   $part2 = substr($paddedid, 4, 3);
   $PHOTO_SRC = "$PHOTO_ROOT/$PHOTO_SUBROOT/$part1/$part2/$PHOTO_FKEY/{$PHOTO_ID}_{$PHOTO_SIZE}.jpg";
}

$cmd = 'cat ' . (file_exists($PHOTO_SRC) ? $PHOTO_SRC : $NOPHOTO_SRC);

header("Content-Type: image/jpeg");
header("Cache-Control: max-age=86400");
passthru($cmd);

?>