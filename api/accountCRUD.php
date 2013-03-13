<?php

$MYSQL_TABLE      = "ACCOUNTS";
$MYSQL_RECORDKEY  = "account";
$MYSQL_ORDERFIELD = "UserID";

require_once("$_SERVER[DOCUMENT_ROOT]/Library/authenticate.php");

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");

$success = false;
$keyreqd = false;
$errormsg = '';
$reason  = '';
$records = array();   
$returnString = array();

$cmd          = $_REQUEST['cmd'] ? $_REQUEST['cmd'] : 'read';
$start        = $_REQUEST['start'] ? $_REQUEST['start'] : 0;
$limit        = $_REQUEST['limit'] >= 0 ? $_REQUEST['limit'] : 50;

$searchstring = $_REQUEST['searchstring'] ? $_REQUEST['searchstring'] : '';
$searchfield  = $_REQUEST['searchfield'] ? $_REQUEST['searchfield'] : '';
$searchvalue  = $_REQUEST['searchvalue'] ? $_REQUEST['searchvalue'] : '';
$recordkey    = $_REQUEST['recordkey'];

$account   = $_REQUEST['account'] ? $_REQUEST['account'] : '';
$userid    = $_REQUEST['userid']  ? $_REQUEST['userid'] : '';
$search    = $_REQUEST['search']  ? $_REQUEST['search'] : '';

if ($limit || $start) {
   $LIMIT = "LIMIT $start, $limit";
}

if ($searchstring) {
   $whereClause = "WHERE (ACCOUNT = '$searchstring' OR USERID LIKE '%$searchstring%')";
}

if ($recordkey) {
   $whereClause = "WHERE $MYSQL_RECORDKEY = '$recordkey'";
}

if ($sort) {
    $sortString = "ORDER BY $sort $dir";
}
else {
    $sortString = "ORDER BY $MYSQL_SORTFIELD ASC";
}

//if ($REMOTE_ACCOUNT) {
   switch ($cmd) {
      case 'getall' : 
         break;
      case 'create' :
         $result = _mysql_do("INSERT INTO $MYSQL_TABLE 
                                (`countryid`, `regionid`, `city`, `region`, `country`, `displayname`, `searchname`, `latitude`, `longitude`, `timezone`, `dmaid`, `code`, `population`)
                                VALUES
                                 ('%s', '%s', '%s', '%s', '%s', '%s, %s, %s', '%s%s%s', '%s', '%s', '%s', '%s', '%s', '%s')", 
                                 $countryid, $regionid, $city, $region, $country, $city, $region, $country, $city, $region, $country, $latitude, $longitude, $timezone, $dmaid, $code, $population);
         if ($result == 1) {
            $success = true;
         }
         break;
      case 'read':
         $SQL = "SELECT SQL_CALC_FOUND_ROWS * FROM $MYSQL_TABLE $whereClause ORDER BY $MYSQL_ORDERFIELD $LIMIT";
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