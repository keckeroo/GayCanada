<?PHP
//error_reporting(E_ERROR);
//
// Authentication routine for all APIs 
//
// This routine is called at the top of each and every API script to obtain authentication
// creditials for a user (if logged in) and populates some easily accessible variables for
// use in the php API scripts
//
// Specifically the following variables are populated with information if a user is authenticated :
//
// $REMOTE_PROFILENAME - the user/profilename of the authenticated user
// $REMOTE_PROFILEID   - the account id of the authenticated user
// 
//
// As well - if the reserved variables loginUserID and loginPassword are found when invoking this
// script, we are assuming that a login request has been sent - so we will erase all existing
// authentication information which is held in any session variables and attempt to re-verify the
// user again with the supplied user creditials
//

//
// Include the MSSQL and MYSQL php api's
//
require_once "$_SERVER[DOCUMENT_ROOT]/lib/mysql.php";

//
// DEFINE SESSION / AUTHENTICATION VALUES
//

error_log("in AUTHENTICATE");

$mcon = new MySQL(array(
	"host" => "209.239.20.7", 
	"user" => "root", 
	"pass" => "casbau29", 
	"db" => "opus"
));

define('SESSION_DB_HOST',     '209.239.20.7');
define('SESSION_DB_USER',     'root');
define('SESSION_DB_PASS',     'casbau29');
define('SESSION_DB_DATABASE', 'opus');
define('SESSION_DB_TABLE',    'sessions');
define('SESSION_TIMEOUT',     14400);

//
// Form post fieldnames used for authentication
//
define('FORM_USERNAMEFIELD',  'loginUsername');
define('FORM_PASSWORDFIELD',  'loginPassword');
define('FORM_LOGOUT',         'logout');
//
// Authentication settings
//
define('AUTH_TABLENAME',      'ACCOUNTS');   // Database table used for authenication
define('AUTH_USERNAMEFIELD',  'UserID');     // Database column used for username
define('AUTH_PASSWORDFIELD',  'Password');   // Database column used for password

//-----------------------------------------------------------------------------------------------------
// NO USER SERVICABLE PARTS BELOW
//-----------------------------------------------------------------------------------------------------

$loginUserID   = isset($_REQUEST[FORM_USERNAMEFIELD]) ? $_REQUEST[FORM_USERNAMEFIELD] : '';
$loginPassword = isset($_REQUEST[FORM_PASSWORDFIELD]) ? $_REQUEST[FORM_PASSWORDFIELD] : '';

$apiSuccess    = false;
$apiErrorCode  = 0;
$apiResponse   = array();
$apiRecords    = array();
$apiErrorMessages = array(
   0 => 'Success',
   1 => "Username or Password is incorrect. Please check your<br>credentials and try again.",
   2 => "Password is incorrect",
   3 => "Your account is currently suspended",
  10 => "You are not logged in or your session has expired. Please re-login",
  20 => "Record not found or has been deleted.",
  90 => "Unknown or missing parameter(s) supplied. Please submit your request with appropriate parameters and try again.",
  99 => "Unknown error. Please try again "
);

$SESSION['connection']  = null;

//
// Determine current domain for cookie setup
//
$domainparts   = explode(".", $_SERVER["HTTP_HOST"]);
$partcount     = count($domainparts);
$domain        = "." . $domainparts[$partcount-2] . "." . $domainparts[$partcount-1];

session_set_cookie_params(SESSION_TIMEOUT, "/", $domain);
session_set_save_handler('_open', '_close', '_read', '_write', '_destroy', '_clean');
session_start();

$REMOTE_ENHANCED = 0;

$_SESSION['username']       = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$_SESSION['authenticated'] 	= isset($_SESSION['authenticated']) ? $_SESSION['authenticated'] : false;


if (isset($_REQUEST[FORM_LOGOUT])) {
   $RM = $_SESSION['username']; // retrieve userid (profile name) before wiping out session information. 

   $_SESSION = array();
   $_SESSION['authenticated'] = false;
   //
   // Delete user from Online table ..
   //
   $result = _mysql_do("DELETE FROM ONLINE WHERE UserID = '$RM'");
}

$_SESSION['attemptedAuth'] = 'false';

if ($loginUserID || $loginPassword) {
   //
   // User has entered creditials for logging in ... 
   //

   // Step 1. Delete all existing session information

   $_SESSION = array();
   $_SESSION['authenticated'] = false;
   $_SESSION['attemptedAuth'] = 'true';

   // Step 2. Search for record in account Table

   $LOGIN_SQL_STATEMENT = sprintf("SELECT *, TO_DAYS(Birthdate) AS B_Days, TO_DAYS(now()) AS N_Days
									 FROM %s WHERE %s = '%s'", AUTH_TABLENAME, AUTH_USERNAMEFIELD, $loginUserID);

   $found = _mysql_select($LOGIN_SQL_STATEMENT, $verify_user);

//, TO_DAYS(Birthdate) AS B_Days, TO_DAYS(now()) AS N_Days FROM opus.ACCOUNTS WHERE UserID = '$loginUserID' OR Account = '$loginUserID'", &$verify_user);

	if ($found > 0) {
		//
	  	// We've found a matching userid or account number ... 
	  	//
	  	if ($verify_user[0][AUTH_PASSWORDFIELD] == $loginPassword) {
			 // 
		 	// User information is valid ... let's check some values.
		 	//

		 	$_SESSION['address']       = $_SERVER['REMOTE_ADDR'];
		 	$_SESSION['browser']       = $_SERVER['HTTP_USER_AGENT'];

		 	if ($verify_user[0]['Permission'] == 2) {
				//
				// User is suspended ... issue appropriate error message
				//
				$apiErrorCode = 3;
		 	}
		 	else {
		 
				$valdays = ($verify_user[0]["N_Days"] - $verify_user[0]["B_Days"]) / 365.25;
				list ($age,$blah) = preg_split("/\./",$valdays, 2);

				$_SESSION['authenticated'] = true;
				$_SESSION['username']      = $verify_user[0][AUTH_USERNAMEFIELD];
				$_SESSION['userid']        = $verify_user[0][AUTH_USERNAMEFIELD];
				$_SESSION['account']       = $verify_user[0]['Account'];
				$_SESSION['gender']        = $verify_user[0]['Gender'];
				$_SESSION['age']           = $age;
				$_SESSION['birthdate']     = $verify_user[0]['Birthdate'];
				$_SESSION['cityid']        = $verify_user[0]['CityID'];
				$_SESSION['admin']         = $verify_user[0]['Admin'];
				$_SESSION['permission']    = $verify_user[0]['Permission'];
//            $_SESSION['enhanced']      = $verify_user[0]['Enhanced'];

//       "REPLACE INTO ONLINE (`E_Code`, `System_Status`, `Last_Seen`, `BioLine`, `CurTask`, `Gender`, `Pic`, `Age`, `Birthday`, `Admin`, `AcceptPage`, `Location`, `Community`, `City`, `Province`, `Country`, `Qm_Site`, `QMStatus`, `Browser`, `CurPurpose`)

		 	}
	  	}
	  	else {
			$apiErrorCode = 1;
	  	}
   	}
   	else {
		$apiErrorCode = 1;
   	}
}

//
//  If session is authenticated setup some 'quick' variables for us to access in call API's
//

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {

   $REMOTE_AUTHENTICATED = 1;
   $REMOTE_USERNAME      = $_SESSION['username'];
   $REMOTE_USER          = $_SESSION['username'];
   $REMOTE_USERID        = $_SESSION['username'];
   $REMOTE_ACCOUNT       = $_SESSION['account'];

   $REMOTE_ADMIN         = isset($_SESSION['isAdmin']) ? $_SESSION['isAdmin'] : '';
//   $REMOTE_CITYID        = $_SESSION['cityid'];
//   $REMOTE_CITYNAME      = $_SESSION['cityname'];
//   $REMOTE_REGIONID      = $_SESSION['regionid'];
//   $REMOTE_REGIONNAME    = $_SESSION['regionname'];
//   $REMOTE_COUNTRYID     = $_SESSION['countryid'];
//   $REMOTE_COUNTRYNAME   = $_SESSION['countryname'];
//   $REMOTE_NEIGHBOURHOOD = $_SESSION['neighbourhood'];
//   $REMOTE_TIMEZONE      = $_SESSION['timezone'];

//   $REMOTE_AGE           = $_SESSION['age'];
//   $REMOTE_GENDER        = $_SESSION['gender'];
//   $REMOTE_BIRTHDATE     = $_SESSION['birthdate'];
//   $REMOTE_ISBIRTHDAY    = isBirthday($REMOTE_BIRTHDATE);

//   $REMOTE_ENHANCED      = $_SESSION['enhanced'];
//   $REMOTE_LATITUDE      = $_SESSION['latitude'];
//   $REMOTE_LONGITUDE     = $_SESSION['longitude'];

//   $REMOTE_TRACE         = $_SESSION['trace'];
//   $REMOTE_PERMISSIONS   = $_SESSION['permissions'];

//   $insertResult = _mysql_do(
//       "REPLACE INTO ONLINE (`Account`, `UserId`, `sessionId`, `HeartBeat`, `E_Code`, `System_Status`, `Last_Seen`, `BioLine`, `CurTask`, `Gender`, `Pic`, `Age`, `Birthday`, `Admin`, `AcceptPage`, `Location`, `Community`, `City`, `Province`, `Country`, `Qm_Site`, `QMStatus`, `Browser`, `CurPurpose`)
//                     VALUES (%s, %s, %s, NOW(), %s, %s, NOW(), %s, %s, %s, %s, %i, %s, %s, %s, %s, %s, %s, %s,  %s, %s, %s, %s, %s)", 
//        $_SESSION['account'], $_SESSION['userid'], session_id(), 'ecode','system status', 'bioline', 'task', 'gender', 'pic', $_SESSION['age'], 0, 0, 0, 'location', 'community', 'city', 'province', 'country', 'qmsite', 'qmstatus', 'browser', 'purpose');

//   $insertResult = _mysql_do(
//         "REPLACE INTO ONLINE (`Account`, `UserId`, `sessionId`, `age`, `birthday`, `gender`, `HeartBeat`, `LastSeen`, `LastActivityChange`) 
//                       VALUES (%s, %s, %s, %s, %s, %s, NOW(), NOW(), NOW())", $_SESSION['account'], $_SESSION['userid'], session_id(), $_SESSION['age'], $REMOTE_ISBIRTHDAY, $_SESSION['gender']);
}
else {
	$REMOTE_AUTHENTICATED = 0;
   	$_SESSION['authenticated'] = false;
}

//
// This routine controls access to the API. If the user is not authenticated a message will
// be returned and the API aborted.
//

function isBirthday($birthDate) {
   list($year,$month,$day) = explode("-",$birthDate); 
   list($tyear,$tmonth,$tday) = explode("-", date('Y-m-d'));

   return ($tmonth == $month && $tday == $day);
}

function apiGatekeeper() {
   global $REMOTE_AUTHENTICATED, $apiErrorCode, $apiErrorMessages, $apiResponse, $apiRecords, $apiSuccess;
   
   if (!$REMOTE_AUTHENTICATED) {
	  $apiErrorCode = 10;

	  $apiResponse['apiSuccess']      = false;
	  $apiResponse['apiErrorCode']    = $apiErrorCode;
	  $apiResponse['apiErrorMessage'] = $apiErrorMessages[$apiErrorCode];

	  $apiResponse['records']         = $apiRecords;
	  $apiResponse['success']         = $apiSuccess;
	  $apiResponse['query']           = $_SERVER['QUERY_STRING'];

	  print json_encode($apiResponse);
	  exit;       
   }
}

//
// PHP SESSION ROUTINES - TALK TO MYSQL DATABASE
//

function _open() {
	global $SESSION;

	if ($SESSION['connection'] = mysql_connect(SESSION_DB_HOST, SESSION_DB_USER, SESSION_DB_PASS, true)) {
		error_log("opening session " . $SESSION['connection']);
		return mysql_select_db(SESSION_DB_DATABASE, $SESSION['connection']);
	}
	error_log("couldn't open session");
	return FALSE;
}

function _close() {
	global $SESSION;
	error_log("closing session " . $SESSION['connection']);

	return mysql_close($SESSION['connection']);
}

function _read($id) {
	global $SESSION;

	$id = mysql_real_escape_string($id, $SESSION['connection']);
	$sql = "SELECT data FROM " . SESSION_DB_TABLE . " WHERE id = '$id'";
	if ($result = mysql_query($sql, $SESSION['connection'])) {
		if (mysql_num_rows($result)) {
			$record = mysql_fetch_assoc($result);
			return $record['data'];
		}
	}
	return '';
}
 
function _write($id, $data) {   
	global $SESSION;

	$access = time();
	$id 	= mysql_real_escape_string($id, $SESSION['connection']);
	$access = mysql_real_escape_string($access, $SESSION['connection']);
	$data 	= mysql_real_escape_string($data, $SESSION['connection']);
	$sql 	= "REPLACE INTO " . SESSION_DB_TABLE . " VALUES  ('$id', '$access', '$data')";
	return mysql_query($sql, $SESSION['connection']);
}

function _destroy($id) {
	global $SESSION;

	$id = mysql_real_escape_string($id, $SESSION['connection']);
	$sql = "DELETE FROM " + SESSION_DB_TABLE + " WHERE id = '$id'";
	return mysql_query($sql, $SESSION['connection']);
}

function _clean($max) {
	global $SESSION;

	$old = time() - $max;
	$old = mysql_real_escape_string($old, $SESSION['connection']);
	$sql = "DELETE FROM " + SESSION_DB_TABLE + " WHERE access < '$old'";
	return mysql_query($sql, $SESSION['connection']);
}

?>
