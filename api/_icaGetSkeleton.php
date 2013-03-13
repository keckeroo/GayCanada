<?php

require("$_SERVER[DOCUMENT_ROOT]/Library/authenticate.php");

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");

gatekeeper($TT_USERID_OPTIONAL);

$success      = false;
$reason       = '';
$records      = array();
$returnString = array();
$errormsg     = '';

if ($start || $limit) {
   $limitString = "LIMIT $start, $limit";
}

if ($sort) {
    $sortString = "ORDER BY $sort $dir";
}
else {
    $sortString = "";
}

$count = _mysql_get_records("SELECT SQL_CALC_FOUND_ROWS
                             XXXXXXX 
                             $sortString
                             $limitString", &$dataset);

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
}
else {
}

$returnString['totalFound']   = $mysql_found_rows;
$returnString['totalRecords'] = $count;
$returnString['records']      = $records;
$returnString['success']      = $success;
$returnString['errormsg']     = $errormsg;

print json_encode($returnString);
exit;

?>
