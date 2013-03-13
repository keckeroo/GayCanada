<?php  

require("$_SERVER[DOCUMENT_ROOT]/Library/authenticate.php");

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");

$success = false;
$reason  = 'Unknown reason.';
$records = array();   
$returnString = array();

if ($REMOTE_ACCOUNT && $REMOTE_USER) {
   $count = _mysql_do("UPDATE PROFILES SET 
                       ethnicity = '%s', 
                       height = '%s',
                       weight = '%s',
                       eyecolour = '%s',
                       haircolour = '%s',
                       bodytype = '%s',
                       mannerisms = '%s',
                       drink = '%s',
                       smoke = '%s',
                       orientation = '%s',
                       maritalstatus = '%s',
                       discretion = '%s',
                       profilepurpose = '%s',
                       education = '%s',
                       children  = '%s',
                       employment = '%s',
                       onlooks = '%s',
                       onsmarts = '%s',
                       political = '%s',
                       religion = '%s',
                       occupation = '%s',
                       hiv = '%s',
                       attributes = '%s', languages = '%s', personality = '%s', pets = '%s', scene = '%s', interests = '%s', activities = '%s',
                       penislength = '%s', foreskin = '%s', pubic = '%s', role = '%s',
                       adultsharing = '%s', sharing = '%s', passkey = '%s', featured = '%s',
                       lookingfor = '%s', turnons = '%s', sexualactivity = '%s',
                       safersex = '%s',
                       q1 = '%s', q2 = '%s', q3 = '%s', q4 = '%s', q5 = '%s',
                       SystemStatus = 'Modified',
                       DateModified = NOW()
                       WHERE ACCOUNT = '$REMOTE_ACCOUNT'", $ethnicity, $height, $weight, $eyecolor, $haircolor, $bodytype,
                          $mannerisms, $drink, $smoke, $orientation, $maritalstatus, $outness, $profilepurpose,
                          $education, $children, $employment, $onlooks, $onsmarts, $politics, $religion, $occupation, $hiv, 
                          $attributes, $languages, $personality, $pets, $scene, $interests, $activities,
                          $penislength, $foreskin, $pubic, $role,
                          $adultsharing, $sharing, $passkey, $featured,
                          $lookingfor, $turnons, $sexualactivity,
                          $safersex,
                          $Q1, $Q2, $Q3, $Q4, $Q5);
   $success = $count > 0;
   $reason = $mysql_errmsg;
}
else {
   $reason = 'Not authorized or not logged in. Unable to updated message status / information.';
}

$returnString['records'] = $records; 
$returnString['success'] = $success;
$returnString['reason']  = $reason;

print json_encode($returnString);

exit;

?>