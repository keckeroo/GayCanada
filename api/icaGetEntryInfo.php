<?php  
require_once("$_SERVER[DOCUMENT_ROOT]/Library/authenticate.php");

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");

$success = false;
$keyreqd = false;
$errormsg  = '';
$records = array();
$returnString = array();

$entryid = $_REQUEST['recordkey'] ? $_REQUEST['recordkey'] : $entryid;

$count = _mysql_get_records("SELECT cglbrd.ENTRIES.*,
                             DATE_FORMAT(Date_Created, '%Y-%m-%d') AS Created,
                             DATE_FORMAT(Date_Modified, '%Y-%m-%d') AS Modified,
                             UNIX_TIMESTAMP(Date_Created) AS UNIX_DC,
                             UNIX_TIMESTAMP(now()) AS TimeNow,
                             IF(ENTRIES.Enhanced_Expires >= NOW(), 'Yes', CATEGORIES.Non_Profit) AS Show_Enhanced,
                             CATEGORIES.Non_Profit As Category_Non_Profit,
                             CATEGORIES.SubCategories
                             FROM cglbrd.ENTRIES, cglbrd.CATEGORIES
                             WHERE EntryID = '$entryid'
                               AND Category_1 = CATEGORIES.CategoryID", &$dataset);

if ($count) {
   $result = _mysql_do("UPDATE cglbrd.ENTRIES SET Accesses = Accesses + 1 WHERE EntryID = '$entryid'");
   $rc = count($mysql_fields);

   for ($j = 0; $j < $count; $j++) {
      for ($i = 0; $i < $rc; $i++) {
          $record[$mysql_fields[$i]] = utf8_encode($dataset[$j][$mysql_fields[$i]]);
      }
      array_push($records, $record);
   }
}

$success = $count > 0;
if ($count == 0) {
   $errormsg = "Unable to find entry record. ($entryid)";
}
else {
   $success = true;
   $returnString['records'] = $records;
}

$returnString['success'] = $success;
$returnString['errormsg']  = $errormsg;

print json_encode($returnString);
exit;

   print "<entryInfo>\n";
   print "<entryid>{$entries[0]['EntryID']}</entryid>\n";
   print "<userid>{$entries[0]['UserID']}</userid>\n";
   print "<password><![CDATA[{$entries[0]['Password']}]]></password>\n";
   print "<title><![CDATA[{$entries[0]['Title']}]]></title>\n";
   print "<category1>{$entries[0]['Category_1']}</category1>\n";
   print "<category2>{$entries[0]['Category_2']}</category2>\n";
   print "<subcategory><![CDATA[{$entries[0]['Sub_Heading']}]]></subcategory>\n";
   print "<subcategories><![CDATA[{$entries[0]['SubCategories']}]]></subcategories>\n";
   print "<street><![CDATA[{$entries[0]['Street']}]]></street>\n";
   print "<city><![CDATA[{$entries[0]['City']}]]></city>\n";
   print "<province><![CDATA[{$entries[0]['Province']}]]></province>\n";
   print "<postalcode><![CDATA[{$entries[0]['Postal_Code']}]]></postalcode>\n";
   print "<mailing><![CDATA[{$entries[0]['Mailing_Address']}]]></mailing>\n";
   print "<intersection><![CDATA[{$entries[0]['Intersection']}]]></intersection>\n";
   print "<locations><![CDATA[{$entries[0]['Locations']}]]></locations>\n";
   print "<directions><![CDATA[{$entries[0]['Directions']}]]></directions>\n";
   print "<phone1><![CDATA[{$entries[0]['Phone_1']}]]></phone1>\n";
   print "<phone2><![CDATA[{$entries[0]['Phone_2']}]]></phone2>\n";
   print "<fax><![CDATA[{$entries[0]['Fax']}]]></fax>\n";
   print "<tollfree><![CDATA[{$entries[0]['Toll_Free']}]]></tollfree>\n";
   print "<scope>{$entries[0]['Scope']}</scope>\n";
   print "<ausemail><![CDATA[{$entries[0]['AUS_Email']}]]></ausemail>\n";
   print "<contactname><![CDATA[{$entries[0]['Contact_Name']}]]></contactname>\n";
   print "<description><![CDATA[{$entries[0]['Description']}]]></description>\n";
   print "<teaser><![CDATA[{$entries[0]['Teaser']}]]></teaser>\n";
   print "<hours><![CDATA[{$entries[0]['Hours']}]]></hours>\n";
   print "<email1><![CDATA[{$entries[0]['Email_1']}]]></email1>\n";
   print "<email2><![CDATA[{$entries[0]['Email_2']}]]></email2>\n";
   print "<url><![CDATA[{$entries[0]['URL']}]]></url>\n";
   print "<clienttype><![CDATA[{$entries[0]['Client_Type']}]]></clienttype>\n";
   print "<agerange><![CDATA[{$entries[0]['Age_Range']}]]></agerange>\n";
   print "<jsonattributes><![CDATA[{$entries[0]['jsonAttributes']}]]></jsonattributes>\n";
   print "<subscriptiontype><![CDATA[{$entries[0]['Subscription_Type']}]]></subscriptiontype>\n";
   print "<enhancedexpires>{$entries[0]['Enhanced_Expires']}</enhancedexpires>\n";
   print "<subscriptionperiod>{$entries[0]['Subscription_Period']}</subscriptionperiod>\n";
   print "<bannerperiod>{$entries[0]['Banner_Period']}</bannerperiod>\n";
   print "<bannervisibility>{$entries[0]['Banner_Visibility']}</bannervisibility>\n";    
   print "<bannercreation>{$entries[0]['Banner_Creation']}</bannercreation>\n";    
   print "<autocity>{$entries[0]['Auto_City']}</autocity>\n";
   print "<longitude><![CDATA[{$entries[0]['Longitude']}]]></longitude>\n";
   print "<latitude><![CDATA[{$entries[0]['Latitude']}]]></latitude>\n";
   print "<nonprofit>{$entries[0][Category_Non_Profit]}</nonprofit>\n";
   print "<showenhanced>$show_enhanced</showenhanced>\n";
   print "</entryInfo>\n";

?>