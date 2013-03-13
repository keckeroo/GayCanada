<?php
//
// LOCAL TABLE VARIABLES
//

$MYSQL_TABLE         = "cglbrd.ENTRIES";
$MYSQL_RECORDKEY     = "entryid";
$MYSQL_DEFAULT_ORDER = "Province, City, Title ASC";
$MYSQL_DEFAULT_LIMIT = 50;

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

$cmd         = $_REQUEST['cmd']        ? $_REQUEST['cmd'] : 'read';
$start       = $_REQUEST['start']      ? $_REQUEST['start'] : 0;
$limit       = $_REQUEST['limit'] >= 0 ? $_REQUEST['limit'] : $MYSQL_DEFAULT_LIMIT;
$searchtype  = $_REQUEST['searchtype'] ? $_REQUEST['searchtype'] : 'exact';

$searchfield = $_REQUEST['searchfield'];
$searchvalue = $_REQUEST['searchvalue'];
$recordkey   = $_REQUEST['recordkey'];
$categoryid  = $_REQUEST['categoryid'] > 0  ? $_REQUEST['categoryid'] : '';

if ($categoryid) {
   $categoryFilter = "AND Category_1 = '$categoryid'";
}

if ($province && $province != 'ALL') {
   $provinceFilter = "AND Province = '$province'";
}

if ($limit || $start) {
   $limitClause = "LIMIT $start, $limit";
}

$whereClause = 'WHERE 1 = 1';

if ($searchfield && $searchvalue) {
   switch ($searchtype) {
      case 'exact'      : $whereClause = "WHERE $MYSQL_TABLE.$searchfield = '$searchvalue'";
                          break;
      case 'match'      : $whereClause = "WHERE $MYSQL_TABLE.$searchfield LIKE '%$searchvalue%'";
                          break;
      case 'beginswith' : $whereClause = "WHERE $MYSQL_TABLE.$searchfield LIKE '$searchvalue%'";
                          break;
      case 'endswith'   : $whereClause = "WHERE $MYSQL_TABLE.$searchfield LIKE '%$searchvalue'";
                          break;
   }
}

if ($recordkey) {
   $whereClause = "WHERE $MYSQL_RECORDKEY = '$recordkey'";
}

if ($sort) {
   $orderClause = "ORDER BY $sort $dir";
}
else {
   $orderClause = "ORDER BY $MYSQL_DEFAULT_ORDER";
}

switch ($cmd) {
   case 'list' : 
      $SQL = "SELECT SQL_CALC_FOUND_ROWS * FROM $MYSQL_TABLE $whereClause $categoryFilter $provinceFilter $orderClause $limitClause"; 
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
   case 'create' :
      $result = _mysql_do("INSERT INTO $MYSQL_TABLE 
                          (`title`, `cityid`, `category_1`, `date_created`, `date_modified`)
                          VALUES
                          ('%s', '%s', '%s', NOW(), NOW())",
                          $_REQUEST['title'], $_REQUEST['cityid'], $_REQUEST['category1']);
      if ($result == 1) {
          $success = true;
          $recordkey = $mysql_insert_id;
      }
      break;
   case 'read':
      $SQL = "SELECT SQL_CALC_FOUND_ROWS * FROM $MYSQL_TABLE $whereClause $orderClause $limitClause"; 
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
      $SQL = "UPDATE $MYSQL_TABLE SET 
              title = '%s',
              street = '%s',
              cityid = '%s',
              city = '%s',
              province = '%s',
              paymentmethods = '%s',
              policies = '%s'
              WHERE $MYSQL_RECORDKEY = '$recordkey'";

      $result = _mysql_do($SQL, $_REQUEST['title'], $_REQUEST['street'],  $_REQUEST['cityid'], $_REQUEST['city'], $_REQUEST['region'], $_REQUEST['paymentmethods'], $_REQUEST['policies']);

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
         $reason = 'Error deleting entry.';
      }
      break;
   default: 
      $reason = 'Unknown command';
}

$returnString['sqlinsertid'] = $mysql_insert_id;
$returnString['sqlerror']    = $mysql_errmsg;
$returnString['totalCount']  = $mysql_found_rows;

$returnString['recordkey']   = $recordkey;
$returnString['success']     = $success;
$returnString['reason']      = $reason;
$returnString['records']     = $records;
$returnString['sql']         = $SQL;

print json_encode($returnString);

exit;

?>