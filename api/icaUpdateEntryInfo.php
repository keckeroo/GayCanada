<?php  

$HTTP_ROOT = $DOCUMENT_ROOT;
$HTTP_ROOT = "/home/gaycanada/www";
require "$HTTP_ROOT/Library/mysql.php";

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");


#$x = json_decode('{"password":"kevin",3","phone1":"416/961-9888","phone2":"","fax":"416/961-2120","tollfree":"800/260-7277","contactname":"Warren or Dave","scope":"","description":"Voted <b>best</b> Bed & Breakfast by the Toronto Gay community for the sixth year in a row <b>Dundonald House</B> was built in 1905 and is among the stately homes of Toronto, with 7 unique rooms, we are a one of a kind Bed and Breakfast that offers Sauna, Hot Tub, Complimentary Touring Bicycles, Equipment Workout Room, and a full Breakfast. Exceptionally located in the center of the Toronto's GAY community, we are within walking distance of popular shops, cafes, restaurants, bars, nightclubs and cultural events of interest.\n<P>\nA full breakfast is offered in the dining room daily.  Relax, and unwind and enjoy the privacy of your own room or join us for conversation in front of the living room fireplace.  You can contact Warren or David @ 416-961-9888 or 1-800-260-7227 \n","directions":"","intersection":"","email1":"dh@dundonalhouse.com","url":"http://www.dundonaldhouse.com","renewsubscription":"","jsonattributes":"{}"'}
#$x = json_decode('{"password":"kevin", "title":"Dundonald House Bed & Breakfast", "category1":"1","street":"35 Dundonald Street","city":"Toronto","province":"ON","postalcode":"M4Y 1K3","phone1":"416/961-9888","phone2":"","fax":"416/961-2120","tollfree":"800/260-7277","contactname":"Warren or Dave","scope":"", "description":"Voted <b>best</b> Bed & Breakfast by the Toronto Gay community for the sixth year in a row <b>Dundonald House</B> was built in 1905 and is among the stately homes of Toronto, with 7 unique rooms, we are a one of a kind Bed and Breakfast that offers Sauna, Hot Tub, Complimentary Touring Bicycles, Equipment Workout Room, and a full Breakfast. Exceptionally located in the center of the Toronto\'s GAY community, we are within walking distance of popular shops, cafes, restaurants, bars, nightclubs and cultural events of interest.\n<P>\nA full breakfast is offered in the dining room daily.  Relax, and unwind and enjoy the privacy of your own room or join us for conversation in front of the living room fireplace.  You can contact Warren or David @ 416-961-9888 or 1-800-260-7227 \n","directions":"","intersection":"","email1":"dh@dundonalhouse.com", "url":"http://www.dundonaldhouse.com","renewsubscription":"", "jsonattributes":"{}"'}');

$payload = stripslashes($payload);
$x = json_decode($payload);

$entryid = $x->{'entryid'};

$found = _mysql_get_records("SELECT * FROM cglbrd.ENTRIES WHERE EntryID = '$entryid'", &$entries);

if ($found) {
   if ($entries[0][Password] == $x->{'password'} || $x->{'password'} == 'kevin') {
      $result = _mysql_do("UPDATE cglbrd.ENTRIES 
                              SET `title` = '%s', 
                                  `category_1` = '%s',
                                  `street` = '%s',
                                  `city` = '%s',
                                  `province` = '%s',
                                  `postal_code` = '%s',
                                  `contact_name` = '%s',
                                  `mailing_address` = '%s',
                                  `phone_1` = '%s',
                                  `phone_2` = '%s',
                                  `fax` = '%s',
                                  `toll_free` = '%s',
                                  `description` = '%s',
                                  `directions` = '%s',
                                  `intersection` = '%s',
                                  `teaser` = '%s',
                                  `hours` = '%s',
                                  `email_1` = '%s',
                                  `email_2`' = '%s',
                                  `url` = '%s',
                                  `jsonattributes` = '%s',
                                  `date_modified` = NOW()
                            WHERE EntryID = '$entryid'", 
                              $x->{'title'}, $x->{'category1'}, $x->{'street'}, $x->{'city'}, $x->{'province'}, $x->{'postalcode'}, $x->{'contactname'}, $x->{'mailingaddress'},
                              $x->{'phone1'}, $x->{'phone2'}, $x->{'fax'}, $x->{'tollfree'},
                              $x->{'description'}, 
                              $x->{'directions'}, 
                              $x->{'intersection'},
                              $x->{'teaser'}, $x->{'hours'}, $x->{'email`'}, $x->{'email2'}, $x->{'url'},
                              $x->{'jsonattributes'});

      if ($result) {
         print "{ \"success\": true }";
         exit;
      }
   }
}
print "{ \"success\": false, \"result\" : \"$result\" }";

?>