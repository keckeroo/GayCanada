<?php

require_once "$_SERVER[DOCUMENT_ROOT]/Library/mysql.php";

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");

$success = false;
$reason  = '';
$records = array();   
$returnString = array();

$database = 'cglbrd';
$table    = 'PHOTOS';

switch ($cmd) {
   case 'get': 
      $count = _mysql_get_records("SELECT * FROM $database.$table WHERE EntryID = '$entryid' AND Enabled = 'Yes' ORDER BY `Default` DESC", &$photos);
      for ($i = 0; $i < $count; $i++) {
         $record = array('photoid'         => $photos[$i]['photoid'], 
                         'entryid'         => $photos[$i]['entryid'],
                         'filename'        => $photos[$i]['filename'],
                         'height'          => $photos[$i]['height'],
                         'width'           => $photos[$i]['width'],
                         'caption'         => $photos[$i]['caption'],
                         'description'     => $photos[$i]['description'],
                         'thumbnailconfig' => $photos[$i]['thumbnail_config'],
                         'default'         => $photos[$i]['default']
         );
         array_push($records, $record);
      }
      $success = $count > 0;
      if ($count == 0) {
         $reason = 'No records found';
      }
      else {
         $returnString['records'] = $records; 
      }
      break;
   case 'add' :
      $result = _mysql_do("INSERT INTO $database.$table (`entryid`, `filename`, `gallery`, `height`, `width`, `size`, `enabled`) VALUES
                          (%d, '%s', '%s', %d, %d, %d, 'Yes')", $entryid, $filename, $gallery, $height, $width, $size);
      $success = true;
      break;
   case 'remove' :
      $result = _mysql_get_records("SELECT * FROM $database.table WHERE `entryid` = '$entryid' AND `photoid` = '$photoid'", &$photos);
      if ($result == 1) {
      }
      $success = true;
      break;
   default: 
      $reason = 'Unknown command';
}

$returnString['success'] = $success;
$returnString['reason']  = $reason;

print json_encode($returnString);

exit;

?>