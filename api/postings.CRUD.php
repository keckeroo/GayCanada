<?php
//
// LOCAL TABLE VARIABLES
//

$MYSQL_TABLE         = "forums.MESSAGES";
$MYSQL_RECORDKEY     = "messageid";
$MYSQL_DEFAULT_ORDER = "Date_Created DESC";
$MYSQL_DEFAULT_LIMIT = 10;
$MYSQL_CREATE_FIELDS = "(`discussionid`, `account`, `userid`, `message`, `date_created`)"; 

require("$_SERVER[DOCUMENT_ROOT]/Library/authenticate.php");

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");

$success = false;
$reason  = '';
$records = array();   
$returnString = array();

$cmd          = $_REQUEST['cmd']        ? $_REQUEST['cmd'] : 'read';
$start        = $_REQUEST['start']      ? $_REQUEST['start'] : 0;
$limit        = $_REQUEST['limit'] >= 0 ? $_REQUEST['limit'] : $MYSQL_DEFAULT_LIMIT;
$searchtype   = $_REQUEST['searchtype'] ? $_REQUEST['searchtype'] : 'exact';

$searchfield  = $_REQUEST['searchfield'];
$searchvalue  = $_REQUEST['searchvalue'];
$recordkey    = $_REQUEST['recordkey'];

if ($limit || $start) {
   $limitClause = "LIMIT $start, $limit";
}

if ($searchfield && $searchvalue) {
   switch ($searchtype) {
      case 'between'    : list($startvalue, $endvalue) = explode(',', $_REQUEST['searchvalue']);
                          $whereClause = "WHERE $MYSQL_TABLE.$searchfield BETWEEN '$startvalue' AND '$endvalue'";
                          break;
      case 'exact'      : $whereClause = "WHERE ($MYSQL_TABLE.$searchfield = '$searchvalue')";
                          break;
      case 'match'      : $whereClause = "WHERE ($MYSQL_TABLE.$searchfield LIKE '%$searchvalue%')";
                          break;
      case 'beginswith' : $whereClause = "WHERE ($MYSQL_TABLE.$searchfield LIKE '$searchvalue%')";
                          break;
      case 'endswith'   : $whereClause = "WHERE ($MYSQL_TABLE.$searchfield LIKE '%$searchvalue')";
                          break;
   }
}  

if ($recordkey) {
   $whereClause = "WHERE $MYSQL_RECORDKEY = '$recordkey'";
}

if ($sort) {
   $orderClause = "ORDER BY $sort $dir";
}
else {
   $orderClause = "ORDER BY $MYSQL_DEFAULT_ORDER";
}

switch ($cmd) {
      case 'list' :
         break;
      case 'create' :
         if ($_REQUEST['topicid'] && $REMOTE_ACCOUNT && $REMOTE_USER && $_REQUEST['posting']) {
            $SQL = "INSERT INTO $MYSQL_TABLE $MYSQL_CREATE_FIELDS VALUES ('%s', '%s', '%s', '%s', NOW())";
            $result = _mysql_do($SQL, $_REQUEST['topicid'], $REMOTE_ACCOUNT, $REMOTE_USER, $_REQUEST['posting']);

            if ($result == 1) {
               $success = true;
               // Update board information
               $update = _mysql_do("UPDATE DISCUSSIONS SET Last_Message = NOW(), Last_Poster = '$REMOTE_USER', Messages = Messages+1 WHERE DiscussionID = '$_REQUEST[topicid]'");
            }
         }
         else {
            $success = false;
            $reason = "Missing required field(s) for posting. ($_REQUEST[topicid],$REMOTE_ACCOUNT,$REMOTE_USER,$_REQUEST[posting])";
         }
         break;
      case 'read':
         $SQL = "SELECT SQL_CALC_FOUND_ROWS * FROM $MYSQL_TABLE $whereClause $orderClause $limitClause"; 
         $count = _mysql_get_records("$SQL", &$result1);
         $rc = count($mysql_fields);
         for ($i = 0; $i < $count; $i++) {
            for ($j = 0; $j < $rc; $j++) {
                $record[$mysql_fields[$j]] = utf8_encode($result1[$i][$mysql_fields[$j]]);
            }
            array_push($records, $record);
         }
         if ($count == 0) {
            $reason = 'No records found';
         }
         $success = true;
         break;
      case 'update' :
//         $SQL = "UPDATE $MYSQL_TABLE SET 
//                 title = '%s',
//                 comments = '%s',
//                 rating = '%s'
//                 WHERE $MYSQL_RECORDKEY = '$recordkey'";

//         $result = _mysql_do($SQL, $_REQUEST['title'], $_REQUEST_['comments'],  $_REQUEST['rating']);

         if ($result > 0) {
            $success = true;
         }
         else {
            $reason = 'Record not found';
         }
         break;
      case 'delete' :
         $SQL = "DELETE FROM $MYSQL_TABLE WHERE $MYSQL_RECORDKEY = '$recordkey' AND ACCOUNT = '$REMOTE_ACCOUNT'";
         $result = _mysql_do($SQL);
         if ($result == 1) {
            $success = true;
            // We were able to delete this posting - so update the topic record with updated information .... thanks.
//            $topicrecord = _mysql_get_records("SELECT COUNT(*) AS totalCount, MAX(Date_Created) as Last_Post, MAX(MessageID) FROM forums.DISCUSSIONS WHERE forums.DISCUSSIONS.DiscussionID = '$_REQUEST[topicid]'", &$topics);
            $result = _mysql_get_records("SELECT * FROM forums.MESSAGES WHERE forums.MESSAGES.DiscussionID = '$_REQUEST[topicid]' ORDER BY MessageID DESC LIMIT 1", &$posts);
            if ($result == 1) {
               $lp = $posts[0][UserID];
               $lpd = $posts[0][Date_Created];
               $result = _mysql_do("UPDATE forums.DISCUSSIONS SET 
                                           Messages = (SELECT COUNT(*) FROM forums.MESSAGES WHERE forums.MESSAGES.DiscussionID = '$_REQUEST[topicid]'),
                                           Last_Poster = '$lp', 
                                           Last_Message = '$lpd'
                                     WHERE forums.DISCUSSIONS.DiscussionID = '$_REQUEST[topicid]'");
            }
            else {
                // There are no more messages left in this topic - remove the topic.
                
            }

         }
         else {
            $reason = 'Unable to locate posting record or you do not have enough privileges to delete this post.';
         }
         break;
      default: 
         $reason = 'Unknown command or not enough params supplied.';
}

$returnString['sqlinsertid'] = $mysql_insert_id;
$returnString['sqlerror']    = $mysql_errmsg;
$returnString['totalCount']  = $mysql_found_rows;

$returnString['success']     = $success;
$returnString['reason']      = $reason;
$returnString['records']     = $records;
$returnString['sql']         = $SQL;
$returnString['cmd']         = $cmd;

print json_encode($returnString);

exit;

?>