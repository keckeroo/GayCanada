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

$MYSQL_TABLE     = "geobytes.Cities";
$MYSQL_RECORDKEY = "cityid";
$MYSQL_SORTFIELD = 'City';

$cmd       = $_REQUEST['cmd'] ? $_REQUEST['cmd'] : 'read';
$recordkey = $_REQUEST['recordkey'];
$start     = $_REQUEST['start'] ? $_REQUEST['start'] : 0;
$limit     = $_REQUEST['limit'] ? $_REQUEST['limit'] : 5000;
$regionid  = $_REQUEST['regionid'];
$cityid    = $_REQUEST['cityid'];


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

if ($regionid) {
   $MYSQL_KEYFIELD = 'regionid';
   $MYSQL_KEYVALUE = $regionid;
}

if ($cityid) {
   $MYSQL_KEYFIELD = 'cityid';
   $MYSQL_KEYVALUE = $cityid;
}

//if ($REMOTE_ACCOUNT) {
   switch ($cmd) {
      case 'getall' : 
         break;
      case 'create' :
         $searchname = $city.$region.$country;
         $searchname = preg_replace('/,\s*|\s*/', "", $searchname);
         $result = _mysql_do("INSERT INTO $MYSQL_TABLE 
                                (`countryid`, `regionid`, `city`, `region`, `country`, `displayname`, `searchname`, `latitude`, `longitude`, `timezone`, `dmaid`, `code`, `population`)
                                VALUES
                                 ('%s', '%s', '%s', '%s', '%s', '%s, %s, %s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", 
                                 $countryid, $regionid, $city, $region, $country, $city, $region, $country, $searchname, $latitude, $longitude, $timezone, $dmaid, $code, $population);
         if ($result == 1) {
            $success = true;
         }
         break;
      case 'read':
         $SQL = "SELECT SQL_CALC_FOUND_ROWS * FROM $MYSQL_TABLE WHERE $MYSQL_KEYFIELD = '$MYSQL_KEYVALUE' ORDER BY $MYSQL_SORTFIELD $LIMIT";
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
                             countryid = '%s',
                             regionid = '%s',
                             city = '%s',
                             region = '%s',
                             country = '%s',
                             displayname = '%s',
                             searchname = '%s',
                             latitude = '%s',
                             longitude = '%s',
                             timezone = '%s',
                             dmaid = '%s',
                             code = '%s'
                             WHERE $MYSQL_KEYFIELD = '$MYSQL_KEYVALUE'",
                             $countryid, $regionid, $city, $region, $country, $displayname, $searchname, $latitude, $longitude, $timezone, $dmaid, $code);
         if ($result > 0) {
            $success = true;
         }
         else {
            $reason = 'Record not found';
         }
         break;
      case 'delete' :
         $result = _mysql_do("DELETE FROM $MYSQL_TABLE WHERE $MYSQL_KEYFIELD = '$MYSQL_KEYVALUE'");
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

$returnString['totalCount']  = $mysql_found_rows;
$returnString['sqlinsertid'] = $mysql_insert_id;
$returnString['sqlerror']    = $mysql_errmsg;

$returnString['success'] = $success;
$returnString['reason']  = $reason;
$returnString['records'] = $records; 
$returnString['sql'] = $SQL;

print json_encode($returnString);

exit;

?>