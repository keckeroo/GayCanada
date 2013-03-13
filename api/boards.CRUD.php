<?php

$MYSQL_TABLE      = "forums.BOARDS";
$MYSQL_RECORDKEY  = "BoardID";
$MYSQL_SORTFIELD  = null;
$MYSQL_ORDERFIELD = "GroupName, Category, BoardName";
$MYSQL_LIMIT      = 50;


require_once("$_SERVER[DOCUMENT_ROOT]/lib/authenticate.php");

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
$sortstring = null;

$sort         = @$_REQUEST['sort']? @$_REQUEST['sort'] : null;
$dir          = @$_REQUEST['DIR'] ? @$_REQUEST['DIR'] : 'ASC';
$cmd          = @$_REQUEST['cmd'] ? @$_REQUEST['cmd'] : 'read';
$start        = @$_REQUEST['start'] ? @$_REQUEST['start'] : 0;
$limit        = @$_REQUEST['limit'] >= 0 ? @$_REQUEST['limit'] : $MYSQL_LIMIT;

$searchstring = @$_REQUEST['searchstring'] ? @$_REQUEST['searchstring'] : '';
$searchfield  = @$_REQUEST['searchfield'] ? @$_REQUEST['searchfield'] : '';
$searchvalue  = @$_REQUEST['searchvalue'] ? @$_REQUEST['searchvalue'] : '';
$recordkey    = @$_REQUEST['recordkey'];

$account      = @$_REQUEST['account'] ? @$_REQUEST['account'] : '';
$userid       = @$_REQUEST['userid']  ? @$_REQUEST['userid'] : '';
$search       = @$_REQUEST['search']  ? @$_REQUEST['search'] : '';


if ($limit || $start) {
   $LIMIT = "LIMIT $start, $limit";
}

if ($searchstring) {
   $whereClause = "WHERE (ACCOUNT = '$searchstring' OR USERID LIKE '%$searchstring%')";
}

if ($recordkey) {
   $whereClause = "WHERE $MYSQL_RECORDKEY = '$recordkey'";
}

if ($sort) {
    $sortString = "ORDER BY $sort $dir";
}
//else {
//    $sortString = "ORDER BY $MYSQL_SORTFIELD ASC";
//}

switch ($cmd) {
    case 'read': 
       $count = _mysql_get_records("SELECT * FROM forums.BOARDS ORDER BY GroupName, Category, BoardName", $dataset);
       $rc = count($mysql_fields);
 
       for ($j = 0; $j < $count; $j++) {
          for ($i = 0; $i < $rc; $i++) {
             $record[$mysql_fields[$i]] = utf8_encode($dataset[$j][$mysql_fields[$i]]);
          }
          array_push($records, $record);
       }
       $success = $count > 0;
       if ($count == 0) {
          $errormsg = 'No records found';
       }
       else {
          $returnString['records'] = $records;
       }     
}

$returnString['sqlinsertid'] = $mysql_insert_id;
$returnString['sqlerror']    = $mysql_errmsg;   
$returnString['totalCount']  = $mysql_found_rows;

$returnString['success']     = $success;
$returnString['errormsg']    = $errormsg;
$returnString['records']     = $records;        
$returnString['sql']         = $SQL;

print json_encode($returnString);

exit;

if ($getforums || $gettopics || $getpostings) {

   if ($gettopics) {
      $count = _mysql_get_records("SELECT * FROM forums.DISCUSSIONS WHERE BoardID = '$forumid' ORDER BY BoardID, Last_Message DESC", $dataset);
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
      $count = _mysql_get_records("SELECT * FROM forums.MESSAGES WHERE DiscussionID = '$topicid' ORDER BY Date_Created DESC", $dataset);
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