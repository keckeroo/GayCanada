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

gatekeeper($TT_USERID_OPTIONAL);

$uselimit = "LIMIT 0, 10";

//$REMOTE_PERM & $GC_ENHANCED ? "2" : "1";

if ($REMOTE_USER && $REMOTE_ACCOUNT) {
// $REMOTE_ACCOUNT = '48897';

   $count =  _mysql_get_records("SELECT checkout.INVOICES.*, checkout.LINE_ITEMS.ItemName, checkout.LINE_ITEMS.ItemSKU, checkout.LINE_ITEMS.UnitCost 
                                   FROM checkout.INVOICES LEFT JOIN checkout.LINE_ITEMS ON checkout.INVOICES.invoice = checkout.LINE_ITEMS.invoice
                                  WHERE checkout.INVOICES.account = '$REMOTE_ACCOUNT' 
                                    AND checkout.LINE_ITEMS.UnitCost > 0
                                  ORDER BY DateEntered", &$dataset);
}

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

$returnString['records'] = $records; 
$returnString['success'] = $success;
$returnString['reason']  = $reason;

print json_encode($returnString);

exit;

?>