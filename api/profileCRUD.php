<?php
//
// LOCAL TABLE VARIABLES
//

$MYSQL_TABLE         = "PROFILES";
$MYSQL_RECORDKEY     = "PRID";
$MYSQL_DEFAULT_ORDER = "UserID";
$MYSQL_DEFAULT_ORDER = "LastLogin DESC";
$MYSQL_DEFAULT_LIMIT = 5;

require("$_SERVER[DOCUMENT_ROOT]/lib/authenticate.php");

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");

$apiSuccess   = false;
$reason       = '';
$apiRecords   = array();
$returnString = array();
$errormsg     = "";

$cmd         = $_REQUEST['cmd']        ? $_REQUEST['cmd'] : 'read';
$start       = $_REQUEST['start']      ? $_REQUEST['start'] : 0;
$limit       = $_REQUEST['limit']      ? $_REQUEST['limit'] : $MYSQL_DEFAULT_LIMIT;
$searchtype  = $_REQUEST['searchtype'] ? $_REQUEST['searchtype'] : 'exact';

$searchfield = $_REQUEST['searchfield'];
$searchvalue = $_REQUEST['searchvalue'];
$recordkey   = $_REQUEST['recordkey'];

if ($limit || $start) {
   $limitClause = "LIMIT $start, $limit";
}

if ($searchfield && $searchvalue) {
   switch ($searchtype) {
      case 'daterange'  : list($startvalue, $endvalue) = explode(',', $_REQUEST['searchvalue']);
                          $whereClause = "WHERE $MYSQL_TABLE.$searchfield BETWEEN '$startvalue' AND '$endvalue'";
                          break;
      case 'exact'      : $whereClause = "WHERE ($MYSQL_TABLE.$searchfield = '$searchvalue')";
                          break;
      case 'match'      : $whereClause = "WHERE ($MYSQL_TABLE.$searchfield LIKE '%$searchvalue%')";
                          break;
      case 'beginswith' : $whereClause = "WHERE ($MYSQL_TABLE.$searchfield LIKE '$searchvalue%')";
                          break;
      case 'endswith'   : $whereClause = "WHERE ($MYSQL_TABLE.$searchfield LIKE '%$searchvalue')";
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
      $SQL = "SELECT SQL_CALC_FOUND_ROWS ACCOUNTS.Account, ACCOUNTS.UserID, ACCOUNTS.CityID, ACCOUNTS.Province, ACCOUNTS.Gender, PROFILES.Age FROM 
              PROFILES LEFT JOIN ACCOUNTS ON PROFILES.Account = ACCOUNTS.Account $whereClause $orderClause $limitClause";

      $SQL = "SELECT count(*) AS TT FROM 
              PROFILES
              $whereClause $orderClause";

      $count = _mysql_get_records("$SQL", &$result1);
      $kevin = $result1[0][TT];

      $SQL = "SELECT SQL_CALC_FOUND_ROWS ACCOUNTS.LastLogin, PROFILES.* FROM 
              ACCOUNTS JOIN PROFILES ON ACCOUNTS.UserID = PROFILES.UserID
              $whereClause $orderClause $limitClause";

      $SQL = "SELECT * FROM PROFILES
              $whereClause $orderClause $limitClause";

      $count = _mysql_get_records("$SQL", &$result1);

      $rc = count($mysql_fields);
      for ($i = 0; $i < $count; $i++) {
         for ($j = 0; $j < $rc; $j++) {
             $record[$mysql_fields[$j]] = utf8_encode($result1[$i][$mysql_fields[$j]]);
         }
         array_push($apiRecords, $record);
      }
      if ($count == 0) {
         $reason = 'No records found';
      }
      $apiSuccess = true;
      break;     
   case 'create' :
       $result = _mysql_do("INSERT INTO $MYSQL_TABLE (`product_id`, `title`, `issue`, `manufacturer_id`, `manufacturer_product_id`, `departments`, `subcategory`,
                            `msrp`, `price`,
                            `teaser`, `description`,
                            `status`, `disabled`, `date_entered`, `date_modified`)
                            VALUES
                           ('%s-%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', NOW(), NOW())",
                           $_REQUEST['manufacturerid'], $_REQUEST['manufacturerproductid'], $_REQUEST['title'], $_REQUEST['issue'], $_REQUEST['manufacturerid'], $_REQUEST['manufacturerproductid'], $_REQUEST['departments'],
                           $_REQUEST['subcategory'], 
                           $_REQUEST['msrp'], $_REQUEST['price'],
                           $_REQUEST['teaser'], $_REQUEST['description'], $_REQUEST['status'], 'Yes');
       if ($result == 1) {
          $apiSuccess = true;
          $recordkey = $_REQUEST['manufacturerid'] . '-' . $_REQUEST['manufacturerproductid'];
       }
       break;
   case 'read':
      $SQL = "SELECT SQL_CALC_FOUND_ROWS * FROM $MYSQL_TABLE $whereClause $orderClause $limitClause";

      $SQL = "SELECT * FROM PROFILES 
              $whereClause $orderClause $limitClause";

      $count = _mysql_get_records("$SQL", &$result1);
      $rc = count($mysql_fields);
      for ($i = 0; $i < $count; $i++) {
         for ($j = 0; $j < $rc; $j++) {
             $record[$mysql_fields[$j]] = utf8_encode($result1[$i][$mysql_fields[$j]]);
         }
         array_push($apiRecords, $record);
      }
      if ($count == 0) {
         $reason = 'No records found';
      }
      $apiSuccess = true;
      break;
   case 'update' :
      $result = _mysql_do("UPDATE $MYSTORE_TABLE SET
                          Title = '%s', Issue = '%s', Price = '%s', MSRP = '%s',
                          Date_Modified = NOW()
                          WHERE $MYSTORE_RECORDKEY = '$recordkey'",
                          $_REQUEST['title'], $_REQUEST['issue'], $_REQUEST['price'], $_REQUEST['msrp']);
      if ($result > 0) {
          $apiSuccess = true;
      }
      else {
         $reason = 'Record not found';
      }
      break;
   case 'delete' :
      $result = _mysql_do("DELETE FROM $MYSQL_TABLE WHERE $MYSQL_RECORDKEY = '$recordkey'");


      if ($result == 1) {
      }
      $apiSuccess = true;
      break;
   default: 
      $reason = 'Unknown command';
}
 
$mysql_found_rows = $kevin;

$apiResponse['sqlInsertId']        = $mysql_insert_id;
$apiResponse['sqlErrorCode']       = $mysql_errcode;
$apiResponse['sqlErrorMessage']    = $mysql_errmsg;
$apiResponse['sqlQuery']           = $SQL;

$apiResponse['apiSuccess']         = $apiSuccess;
$apiResponse['apiRecords']         = $apiRecords;
$apiResponse['apiRecordsFound']    = $mysql_found_rows;
$apiResponse['apiRecordsReturned'] = $count;
$apiResponse['apiErrorCode']       = $apiErrorCode;
$apiResponse['apiErrorMessage']    = $apiErrorMessages[$apiErrorCode];
$apiResponse['apiAction']          = $cmd;
$apiResponse['apiQueryString']     = $_SERVER['QUERY_STRING'];
$apiResponse['apiUsername']        = $REMOTE_ACCOUNT;

$apiResponse['limit'] = $limit;
$apiResponse['start'] = $start;

//
// These values are the default values for sencha ....
//
$apiResponse['total']              = $mysql_found_rows; // Total records for query used in paging routines
$apiResponse['success']            = true;              // Did API finish without incident ?

print json_encode($apiResponse);
   
exit;

?>