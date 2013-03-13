<?php

require("$_SERVER[DOCUMENT_ROOT]/lib/authenticate.php");

$extension  = '.jpg';

$userid   = $_REQUEST['userid']   ? $_REQUEST['userid'] : $REMOTE_USER;
$userid   = $_REQUEST['username'] ? $_REQUEST['username'] : $userid;
$type     = $_REQUEST['type'] ? $_REQUEST['type'] : 'tn';
$photoid  = $_REQUEST['photoid'];

$new_root   = "/home/gaycanada/userfiles/photos/";
$image_root = "/home/gaycanada/pics";
$thumb_root = "/home/gaycanada/pics/thumbs";

include('../Library/class.upload.php');

$im = "/home/gaycanada/images/npa1.jpg";

if ($type == 'bu') {
   $type = '';
}

//
// Default action - if no photo id is provide, show the current UserId thumbnail is one exists ....
//

if ($legacy) {
   

}
else if ($photoid) {
    $result = _mysql_get_records("SELECT * FROM PICTURES WHERE ID = '$photoid' and (Enabled = 'Yes' OR ACCOUNT = '$REMOTE_ACCOUNT')", &$picture);

//    $result = _mysql_get_records("SELECT * FROM PICTURES WHERE ID = '$photoid'", &$picture);

    if ($result && ($picture[0]['enabled'] == 'Yes' || $picture[0]['Account'] == $REMOTE_ACCOUNT) ) {
       $account = $picture[0][Account];
       $image   = $picture[0][Image];
       $file    = $picture[0][file];
       $adult   = $picture[0][Adult];

       $target_directory = $new_root.substr($account, strlen($account)-2,2)."/".$account."/";
       $gallery = $gallery ? $gallery : 'Public';

       if ($picture[0][converted] == 'No') {
          $file = convert_image($image_root, $image, $photoid, $target_directory, $gallery, $file);
       }

       $im = $type ? "$target_directory/$file"."_".$type.".jpg" : "$target_directory/$file.jpg";

       if ($adult && $REMOTE_ENHANCED == 0) {
          $im = "/home/gaycanada/images/profiles/xrated3.gif";
       }

    }
}
else if ($userid) {

   $result = _mysql_get_records("SELECT Account FROM ACCOUNTS WHERE UserID = '$userid'", &$account);
   if ($result > 0) {
      $account = $account[0][Account];
      $target_directory = $new_root.substr($account, strlen($account)-2,2)."/".$account."/";
      $result = _mysql_get_records("SELECT * FROM PICTURES WHERE Account = '$account' AND ByDefault = 1 and Enabled = 'Yes'", &$picture);

      if ($result > 0) {
         $account = $picture[0][Account];
         $image = $picture[0][Image];
         $file = $picture[0][file];  
         $photoid = $picture[0]['ID'];

         $target_directory = $new_root.substr($account, strlen($account)-2,2)."/".$account."/";
         $gallery = $gallery ? $gallery : 'Public';

         if ($picture[0][converted] == 'No') {
            $file = convert_image($image_root, $image, $photoid, $target_directory, $gallery, $file);
         }
         else {
            $file = $picture[0][file];
         }
         $im = $type ? "$target_directory/$file"."_".$type.".jpg" : "$target_directory/$file.jpg";
      }
   }
}
else {
   // we were specified a username so we're going to get the thumbnail for this user
   $result = _mysql_get_records("SELECT Image FROM PICTURES WHERE Account = '$userid' AND ByDefault = 1", &$thumbnail);
   if ($result > 0) {
      $root1 = substr($userid, 0, 2);
      $root2 = substr($userid, 2, 2);
      $source = "{$thumb_root}/t_{$thumbnail[0][Image]}";
      $im = "{$thumb_root}/t_{$thumbnail[0][Image]}";
   }
}
if ($photoidxxx) {
   $result = _mysql_get_records("SELECT * FROM Photos WHERE photoid = '$photoid'", &$photo_info);
   if ($result) {
      $userid = $photo_info[0][userid];
      $file   = $photo_info[0][file];
      $dir    = substr($userid, strlen($userid)-2, 2);
      $im     = "/home/www/cruising/userfiles/photos/$dir/$userid/$file$type$extension";
      if ($photo_info[0][userid] != $REMOTE_ID && $type != 'tn' && $REMOTE_USER != 'keckeroo'){
         $resultup = _mysql_do("UPDATE Photos SET views_this_week = views_this_week + 1, views_total = views_total + 1 WHERE photoid = '$photoid'");
      }
   }
}

//
// Do some behind the scenes conversion / migrating of images
//

$cmd    = 'cat ' . $im;

header("Content-Type: image/jpeg");
header("Cache-Control: max-age=86400");
passthru($cmd);

// print $cmd;

exit();

function convert_image($image_root, $image, $photoid, $target_directory, $gallery, $file) {
    if ($file) {
       // we've previously converted this image and should have a source
       // but for whatever reason we are going to convert it again *without* generating a new filename
       $src = "{$target_directory}/{$file}.jpg";
    }
    else {
       $src = "{$image_root}/{$image}";
    }

    if (! file_exists($src)) {
        print "{'success' : false, 'errors': {'$k' : 'File does not exist ($src)' }}\n";
        exit;
    }

    $handle = new upload($src);
    if ($handle->uploaded) {
       if ($file) {
          $filename = $file;
       }
       else {
          $filename = md5(rand(1000,9999).rand(1000,9999).rand(1000,9999).rand(1000,9999));
       }

       $handle->file_new_name_body = $filename;
       $handle->file_overwrite = true;
       $handle->image_convert = 'jpg';

       $image_size  = $handle->file_src_size;
       $image_width = $handle->image_src_x;  
       $image_height = $handle->image_src_y; 

       $handle->process($target_directory);
       if (! $handle->processed) {
          print "{'success' : false, 'errors': {'$k' : '{$handle->error}' }}\n";
          exit;
       }

       $maxwidth = $image_width <= 750 ? $image_width : 750;
       $maxheight = $image_height <= 520 ? $image_height: 520;

       $handle->file_new_name_body = $filename;  
       $handle->file_name_body_add = '_sm';  
       $handle->image_resize = true;  
       $handle->image_x = $maxwidth;
       $handle->image_y = $maxheight;
       $handle->image_ratio = true;
       $handle->image_convert = 'jpg';
       $handle->file_overwrite = true;

       $handle->process($target_directory);
        
       // we check if everything went OK
       if (! $handle->processed) {
          print "{'success' : false, 'errors': { '$k' : '{$handle->error}' }}\n";
          exit;
       }

       $handle->file_new_name_body = $filename;
       $handle->file_overwrite = true;
       $handle->file_name_body_add = '_tn';
       $handle->image_resize = true;
       $handle->image_x = 75;
       $handle->image_y = 75;
       $handle->image_ratio_crop = true;
       $handle->image_convert = 'jpg';

       $handle->process($target_directory);
        
       // we check if everything went OK
       if (! $handle->processed) {
          print "{'success' : false, 'errors': { '$k' : '{$handle->error}' }}\n";
          exit;
       }
       $result = _mysql_do("UPDATE PICTURES SET file = '$filename', height = $image_height, width = $image_width, size = $image_size, converted = 'Yes' WHERE ID = '$photoid'");
   } 
   return $filename;
}

?>
 