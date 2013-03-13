<?php  

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

// gatekeeper($TT_USERID_OPTIONAL);

$count =  _mysql_get_records("SELECT forums.DISCUSSIONS.BoardID, forums.BOARDS.BoardName, forums.DISCUSSIONS.Messages, forums.DISCUSSIONS.DiscussionID, LEFT(forums.DISCUSSIONS.Subject,50) AS Subject, forums.DISCUSSIONS.Last_Message, forums.DISCUSSIONS.Last_Poster
                                FROM forums.DISCUSSIONS
                                LEFT JOIN forums.BOARDS 
                                  ON forums.BOARDS.BoardID = forums.DISCUSSIONS.BoardID
                               WHERE forums.BOARDS.GroupName = 'GLC' 
                            ORDER BY forums.DISCUSSIONS.Last_Message DESC LIMIT 13", &$dataset);

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

$returnString['success'] = $success;
$returnString['reason']  = $reason;

print json_encode($returnString);

exit;

?>