<?php  

require("$_SERVER[DOCUMENT_ROOT]/Library/mysql.php");

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

//$ff = fopen("params.txt", "a+");

$query = preg_replace('/,\s*|\s*/', "", $query);
#$searchname = preg_replace('/,\s*|\s*/', "", $searchname);

//fprintf($ff, "Query is $query\n\n");

//foreach ($_REQUEST as $key => $value) {
//   fprintf($ff, "$key is $value\n");
//}


if ($cityid) {
   $count =  _mysql_get_records("SELECT * FROM geobytes.Cities WHERE CityID = '$cityid'", &$dataset);
}
else if (is_numeric($query)) {
//   fprintf($ff, "QUERY IS NUMERIC\n");
   $count =  _mysql_get_records("SELECT * FROM geobytes.Cities WHERE CityID = '$query'", &$dataset);
}
else {
   $count = _mysql_get_records("SELECT * FROM geobytes.Cities WHERE searchname LIKE '$query%' ORDER BY searchname DESC LIMIT 13", &$dataset);
}

//fclose($ff);
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

$returnString['totalCount']= $count;
$returnString['success'] = $success;
$returnString['reason']  = $reason;

print json_encode($returnString);

exit;

?>