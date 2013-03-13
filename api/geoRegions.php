<?php  

require("$_SERVER[DOCUMENT_ROOT]/lib/mysql.php");

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");

$success = false;
$reason  = '';
$records = array();   
$returnString = array();

$start = isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;
$limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 10000;
$cmd   = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : 'get';
$countryid = isset($_REQUEST['countryid']) ? $_REQUEST['countrid'] : '43';

$SQL   = "SELECT SQL_CALC_FOUND_ROWS * FROM geobytes.Regions WHERE CountryId = '$countryid' ORDER BY Region LIMIT $start,$limit";
$count =  _mysql_get_records($SQL, $dataset);

$rc = count($mysql_fields);
for ($j = 0; $j < $count; $j++) {
   for ($i = 0; $i < $rc; $i++) {
       $record[$mysql_fields[$i]] = utf8_encode($dataset[$j][$mysql_fields[$i]]);
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

$returnString['total'] = $mysql_found_rows;
$returnString['totalCount']= $mysql_found_rows;
$returnString['success'] = $success;
$returnString['reason']  = $reason;
$returnString['sql'] = $SQL;

print json_encode($returnString);

exit;

?>