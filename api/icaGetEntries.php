<?php  

require("$_SERVER[DOCUMENT_ROOT]/Library/authenticate.php");

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");

$success      = false;
$reason       = '';
$records      = array();   
$returnString = array();
$errormsg     = "";

//$start = $start ? $start : 0;
//$limit = $limit ? $limit : 25;

if ($start || $limit) {
   $limitString = "LIMIT $start, $limit";
}

if ($sort) {
    $sortString = "ORDER BY $sort $dir";
}
else {
    $sortString = "";
}

//gatekeeper($TT_USERID_OPTIONAL);

$count = _mysql_get_records("SELECT * FROM cglbrd.CATEGORIES WHERE CategoryID = '$categoryid'", &$dataset);

$returnString['englishname'] = $dataset[0]['Description_E'];
$returnString['frenchname'] = $dataset[0]['Description_F'];


if ($categoryid) {
   $categoryFilter = "AND Category_1 = '$categoryid'";
}

if ($query) {
   $searchFilter = "AND TITLE LIKE '%$query%'";
}

$SQL = "SELECT SQL_CALC_FOUND_ROWS
        entryid, title, street as address, city, province, postal_code, phone_1, cityid, teaser,
                         IF(ENTRIES.Enhanced_Expires >= NOW(), 'Yes', 'No') as enhancedlisting
                               FROM cglbrd.ENTRIES 
                              WHERE 1 = 1
                                $categoryFilter
                                $searchFilter
                                AND Disabled = 'No'
                           ORDER BY Province, City, enhancedlisting DESC, Title 
                           $limitString";

$count = _mysql_get_records($SQL, &$dataset);

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

$returnString['records']      = $records;
$returnString['totalFound']   = $mysql_found_rows;
$returnString['totalRecords'] = $count;
$returnString['success']      = $success;
$returnString['reason']       = $reason;
$returnString['sql']          = $SQL;

print json_encode($returnString);

exit;

?>