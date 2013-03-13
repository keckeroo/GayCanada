<?php

require_once("$_SERVER[DOCUMENT_ROOT]/Library/authenticate.php");

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

if ($getforums || $gettopics || $getpostings) {

   if ($getforums) {
      $count = _mysql_get_records("SELECT * FROM forums.BOARDS ORDER BY GroupName, Category, BoardName", &$dataset);
      $rc = count($mysql_fields);

      for ($j = 0; $j < $count; $j++) {
         for ($i = 0; $i < $rc; $i++) {
            $record[$mysql_fields[$i]] = $dataset[$j][$mysql_fields[$i]];
         }
         array_push($records, $record);
      }
      $success = $count > 0;
      if ($count == 0) {
         $reason = 'No records found';
      }
      else {
         $returnString['records'] = $records;
      }
   }

   if ($gettopics) {
      $count = _mysql_get_records("SELECT * FROM forums.DISCUSSIONS WHERE BoardID = '$forumid' ORDER BY BoardID, Last_Message DESC", &$dataset);
      $rc = count($mysql_fields);

      for ($j = 0; $j < $count; $j++) {
         for ($i = 0; $i < $rc; $i++) {
            $record[$mysql_fields[$i]] = $dataset[$j][$mysql_fields[$i]];
         }
         array_push($records, $record);
      }
      $success = $count > 0;
      if ($count == 0) {
         $reason = 'No records found';
      }
      else {
         $returnString['records'] = $records;
      }      
   }

   if ($getpostings) {
      $count = _mysql_get_records("SELECT * FROM forums.MESSAGES WHERE DiscussionID = '$topicid' ORDER BY Date_Created DESC", &$dataset);
      $rc = count($mysql_fields);

      for ($j = 0; $j < $count; $j++) {
         for ($i = 0; $i < $rc; $i++) {
            $record[$mysql_fields[$i]] = $dataset[$j][$mysql_fields[$i]];
         }
         array_push($records, $record);
      }
      $success = $count > 0;
      if ($count == 0) {
         $reason = 'No records found';
      }
      else {
         $returnString['records'] = $records;
      }      
   }

   $returnString['success'] = $success;
   $returnString['errormsg']  = $errormsg;

   print json_encode($returnString);
   exit;
}

?>