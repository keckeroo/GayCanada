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

$cmd       = $_REQUEST['cmd'] ? $_REQUEST['cmd'] : 'read';
$recordkey = $_REQUEST['recordkey'];
$start     = $_REQUEST['start'] ? $_REQUEST['start'] : 0;
$limit     = $_REQUEST['limit'] ? $_REQUEST['$limit'] : 25;

$MYSQL_TABLE     = "ROSTER";
$MYSQL_RECORDKEY = "recordkey";

if ($limit || $start) {
   $LIMIT = "LIMIT $start, $limit";
}

if ($searchString) {
   $searchString = "WHERE (SUPPLIER_NAME LIKE '%$searchString%' OR SUPPLIER_ID LIKE '%$searchString%')";
}

//$searchString = "WHERE SUPPLIER_NAME LIKE '%kevin%'";

if ($recordkey) {
   $recordFilter = "WHERE $MYSQL_RECORDKEY = '$recordkey'";
}

if ($sort) {
    $sortString = "ORDER BY $sort $dir";
}
else {
    $sortString = "ORDER BY SUPPLIER_NAME ASC";
}


//if ($REMOTE_ACCOUNT) {
   switch ($cmd) {
      case 'create' :
         $result = _mysql_do("INSERT INTO $MYSQL_TABLE 
                                (`supplier_id`, `supplier_name`, `address_1`, `address_2`, `city`, `state`, `zipcode`, `contact_name`, `phone`, `fax`, `email`, `instructions`, 
                                 `minimum_order`, `availability_min`, `availability_max`, `availability_unit`, `notes`, `disabled`, date_entered, last_updated)
                                VALUES
                                 ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', NOW(), NOW())", 
                                 $supplierid, $suppliername, $address1, $address2, $city, $state, $zipcode, $contactname, $phone, $fax, $email, $instructions, $minimumorder, $availabilitymin,
                                 $availabilitymax, $availabilityunit, $notes, $disabled);
         if ($result == 1) {
            $success = true;
         }
         break;
      case 'read':
         $SQL = "SELECT SQL_CALC_FOUND_ROWS * FROM $MYSQL_TABLE $recordFilter $searchString $sortString $LIMIT"; 
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
         $result = _mysql_do("UPDATE $MYSQL_TABLE SET
                             supplier_name = '%s',
                             address_1 = '%s',
                             address_2 = '%s',
                             city = '%s',
                             state = '%s',
                             zipcode = '%s',
                             contact_name = '%s',
                             phone = '%s',
                             fax = '%s',
                             email = '%s',
                             instructions = '%s',
                             minimum_order = '%s',
                             availability_min = '%s',
                             availability_max = '%s',
                             availability_unit = '%s',
                             notes = '%s',
                             last_updated = NOW(),
                             disabled = '%s'
                             WHERE $MYSQL_RECORDKEY = '$recordkey'", 
                             $suppliername, $address1, $address2, $city, $state, $zipcode, $contactname, $phone, $fax, $email, $instructions, $minimumorder, $availabilitymin,
                             $availabilitymax, $availabilityunit, $notes, $disabled);
         if ($result > 0) {
            $success = true;
         }
         else {
            $reason = 'Record not found';
         }
         break;
      case 'delete' :
         $result = _mysql_do("DELETE FROM $MYSQL_TABLE WHERE $MYSQL_RECORDKEY = '$recordkey'");
         if ($result == 1) {
            $success = true;
         }
         else {
//            $reason = 'This supplier is currently in use - you cannot delete this supplier.';
         }
         break;
      default: 
         $reason = 'Unknown command';
   }
//}


$returnString['sqlinsertid'] = $mysql_insert_id;
$returnString['sqlerror']    = $mysql_errmsg;
$returnString['totalCount']  = $mysql_found_rows;

$returnString['success']     = $success;
$returnString['reason']      = $reason;
$returnString['records']     = $records; 
$returnString['sql']         = $SQL;

print json_encode($returnString);

exit;

?>