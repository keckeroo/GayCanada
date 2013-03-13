<?php

require("$_SERVER[DOCUMENT_ROOT]/Library/authenticate.php");

$userid   = $userid   ? $userid   : $REMOTE_ACCOUNT;

$im = "/home/gaycanada/gifs/white-square.png";

if ($username) {
   $result = _mysql_get_records("SELECT * FROM ONLINE WHERE UserID = '$username'", &$account);
   if ($result > 0) {
      $im = "/home/gaycanada/gifs/green-square.png";
   }
}
$cmd    = 'cat ' . $im;

header("Content-Type: image/png");
header("Cache-Control: max-age=86400");
passthru($cmd);

exit();

?>