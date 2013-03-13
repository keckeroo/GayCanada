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

if ($limit) {
   $uselimit = "LIMIT 0, $limit";
}

#$count =  _mysql_get_records("SELECT cglbrd.ENTRIES.Title, cglbrd.ENTRIES.City, cglbrd.ENTRIES.Province, concat(cglbrd.ENTRIES.city, 'OntarioCanada') as cSearchString,
#                                      geobytes.Cities.*
#                                FROM cglbrd.ENTRIES, geobytes.Cities
#                               WHERE cglbrd.ENTRIES.Province = 'ON'  
#                                 AND concat(cglbrd.ENTRIES.city, 'OntarioCanada') = geobytes.Cities.searchname
#                               LIMIT 0, 10", &$dataset);

#$count =  _mysql_get_records("SELECT geobytes.Cities.cityid 
#                                FROM cglbrd.ENTRIES, geobytes.Cities
#                               WHERE cglbrd.ENTRIES.Province = 'ON'  
#                                 AND concat(cglbrd.ENTRIES.city, 'OntarioCanada') = geobytes.Cities.searchname
#                               LIMIT 0, 10", &$dataset);

$count =  _mysql_get_records("UPDATE cglbrd.ENTRIES set CityID = (SELECT geobytes.Cities.cityid 
                                FROM geobytes.Cities
                               WHERE geobytes.Cities.searchname = concat(cglbrd.ENTRIES.city, 'OntarioCanada'))
                               WHERE cglbrd.ENTRIES.Province = 'ON'
                               LIMIT 0, 10", &$dataset);

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