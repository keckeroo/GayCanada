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

$count = _mysql_get_records("SELECT UserID, Image, LEFT(Q1,'150') AS Q1, UPPER(City) AS City, UPPER(Province) AS Province,
                             Country,ShowPicture
                    FROM PROFILES
               LEFT JOIN PICTURES ON PROFILES.Account = PICTURES.Account
                   WHERE PROFILES.SystemStatus = 'Active'
                     AND PROFILES.DateModified > DATE_SUB(now(), INTERVAL 182 DAY)
                     AND Q1 IS NOT NULL
                     AND Q1 <> ''
                     AND PICTURES.Image IS NOT NULL
                     AND PICTURES.Image <> ''
                     AND PICTURES.ByDefault = '1'
                     AND PICTURES.SystemStatus = 'Active'
                     AND PICTURES.Enabled = true
                     AND PICTURES.Adult = '0'
                     AND PICTURES.ShowPicture = '1'
                     AND Sharing = 'Public'
                     AND Country IN ('CA','US')
                     AND Featured = 'Yes' 
                     AND FeaturedOverride = 'Yes'
                ORDER BY RAND()
                   LIMIT 0,8",  &$dataset);

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