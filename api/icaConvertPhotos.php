<?php

$HTTP_ROOT = $DOCUMENT_ROOT;
$HTTP_ROOT = "/home/gaycanada/www/";
require "$HTTP_ROOT/Library/mysql.php";

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");

$UL_MAX_FILESIZE  = 1024;

$target_directory = "/home/gaycanada/userfiles/photos/";

include('../Library/class.upload.php');


$count = _mysql_get_records("SELECT * FROM PICTURES LIMIT 10", &$pictures);

for ($i = 0; $i < $count; $i++) {
   print "/home/gaycanada/www/pics/"  . $pictures[$i][Image] . "<br>";

}
exit;

if ($cmd == 'upload') {
    foreach ($_FILES as $k => $l) {
        $handle = new upload($_FILES[$k]);
    }

    // then we check if the file has been uploaded properly
    // in its *temporary* location in the server (often, it is /tmp)
    if ($handle->uploaded) {
        $uploadfile = $_FILES[$k]['name'];
        $filename = md5(rand(1000,9999).rand(1000,9999).rand(1000,9999).rand(1000,9999).$uploadfile);

        $handle->file_new_name_body = $filename;
        $handle->file_overwrite = true;

        $image_size  = $handle->file_src_size;
        $image_width = $handle->image_src_x;
        $image_height = $handle->image_src_y;

        $handle->Process($target_directory);
        if (! $handle->processed) {
            print "{'success' : false, 'errors': {'$k' : '{$handle->error}' }}\n";
            exit;
        }

        // we copy the file a second time
        $handle->file_new_name_body = $filename;
        $handle->file_name_body_add = '_sm';
        $handle->image_resize = true;
        $handle->image_x = 220;
        $handle->image_y = 220;
        $handle->image_ratio_crop = true;

        $handle->Process($target_directory);
        
        // we check if everything went OK
        if (! $handle->processed) {
            print "{'success' : false, 'errors': { '$k' : '{$handle->error}' }}\n";
            exit;
        }

        $handle->file_new_name_body = $filename;
        $handle->file_name_body_add = '_tn';
        $handle->image_resize = true;
        $handle->image_x = 75;
        $handle->image_y = 75;
        $handle->image_ratio_crop = true;
        $handle->Process($target_directory);
        
        // we check if everything went OK
        if (! $handle->processed) {
            print "{'success' : false, 'errors': { '$k' : '{$handle->error}' }}\n";
            exit;
        }
            
#        $result = _mysql_do("INSERT INTO Photos (userid, file, gallery, height, width, size, date_added) VALUES ('$REMOTE_ID', '$filename', '$gallery', '$image_height', '$image_width', '$image_size',  NOW())");

        $handle-> Clean();
#        update_galleries();

        print "{'success' : true, 'filename' : '$filename', 'gallery': '$gallery', 'height': $image_height, 'width' : $image_width, 'size': $image_size }\n";
        exit;
    } else {
        print "{'success' : false, 'errors': { '$k' : '{$handle->error}' }}\n";
        exit;
    }
}
else if ($cmd == 'update' || $cmd == 'reset') {
   if ($cmd == 'reset') {
      $thumbnailconfig = '0,0,100';
   }

   $found = _mysql_get_records("SELECT * FROM Photos where userid = '$REMOTE_ID' and photoid = '$photoid'", &$photo_info);

   if ($thumbnailconfig != $photo_info[0][thumbnail_config]) {
      $thumbnail_updated = ", thumbnail_updated = NOW()";
   }

   $result = _mysql_do("UPDATE Photos SET 
                           `gallery` = IF('$gallery' <> '', '$gallery', `gallery`),
                           `enabled` = IF('$enabled' <> '', '$enabled', `enabled`),
                           `thumbnail_config` = '$thumbnailconfig',
                           `caption` = '$caption' $thumbnail_updated
                        WHERE userid = '$REMOTE_ID' AND photoid = '$photoid'");

#   update_galleries();

//   writeit("here, $found, $thumbnailconfig\n");
   
   if ($cmd == 'reset') {
      $filename   = $photo_info[0][file];
      $filesource = $target_directory.$photo_info[0][file].'.jpg';
      $handle = new upload($filesource);

      $handle->file_new_name_body = $filename;
      $handle->file_name_body_add = '_sm';
      $handle->file_overwrite = true;
      $handle->file_auto_rename = false;
      $handle->image_resize = true;
      $handle->image_x = 220;
      $handle->image_y = 220;
      $handle->image_ratio_crop = true;

      $handle->Process($target_directory);
        
      // we check if everything went OK
      if (! $handle->processed) {
          print "{'success' : false, 'errors': { '$k' : '{$handle->error}' }}\n";
          exit;
      }

      $handle->file_new_name_body = $filename;
      $handle->file_name_body_add = '_tn';
      $handle->file_overwrite = true;
      $handle->file_auto_rename = false;
      $handle->image_resize = true;
      $handle->image_x = 75;
      $handle->image_y = 75;
      $handle->image_ratio_crop = true;
      $handle->Process($target_directory);
        
      // we check if everything went OK
      if (! $handle->processed) {
          print "{'success' : false, 'errors': { '$k' : '{$handle->error}' }}\n";
          exit;
      }
   }
   else if ($found && $thumbnailconfig != $photo_info[0][thumbnail_config]) {
      //
      // resize for thumbnail
      //
      list ($st, $sl, $sz) = split(',', $thumbnailconfig);

      $filename   = $photo_info[0][file];
      $filesource = $target_directory.$photo_info[0][file].'.jpg';
      if ($sz < 100) {
           $resize = "-thumbnail $sz%";
      }

      $command = "convert $filesource $resize -crop 220x220+$sl+$st $target_directory$filename"."_sm.jpg";
      system($command);
      $command = "convert $filesource $resize -crop 220x220+$sl+$st -thumbnail 75x75 $target_directory$filename"."_tn.jpg";
      system($command);
   }
   print "{'success' : true }\n";
}
else if ($cmd == 'delete') {
   //
   // Delete the photo from the database and the hard disk
   //
   $found = _mysql_get_records("SELECT * FROM Photos WHERE `userid` = '$REMOTE_ID' AND `photoid` = '$photoid'", &$photo_record);
   if ($found == 1) {
      $result = _mysql_do("DELETE FROM Photos WHERE `photoid` = '$photoid' AND `userid` = '$REMOTE_ID'");
      update_galleries();
      if ($result) {
         //
         // We've successfully removed the photo from the database, now remove it from the hard drive
         //
         $filename = $photo_record[0][file];
         $filelist = "rm $target_directory$filename".'*';
         if ($target_directory != '' && $filename != '') {
            //
            // time to remove the files from the hard disk
            //
            $result = system($filelist);
            if (!$result) {
               print "{'success' : false, 'errors': { reason: 'Unable to delete files. ($filelist)' }}\n";
               exit;
            }
         }
         else {
            print "{'success' : false, 'errors': { reason: 'File is blank. ($filelist)' }}\n";
            exit;
         }
      }
      else {
         print "{'success' : false, 'errors': { reason: 'Unable to remove photo from database. ($filelist)' }}\n";
         exit;
      }
   }
   else {
      print "{'success' : false, 'errors': { reason: 'Unable to locate photo record.' }}\n";
      exit;
   }
}
else {
   print "{'success' : false, 'errors': { reason: 'Unrecognized or missing command.' }}\n";
   exit;
}


function update_galleries() {
   global $REMOTE_ID;

   $galcount = _mysql_get_records("SELECT gallery, photoid FROM Photos WHERE `userid` = '$REMOTE_ID'", &$galleries);
   for ($i = 0; $i < $galcount; $i ++) {
      $galname = $galleries[$i][gallery];
      $gallery[$galname][] = $galleries[$i][photoid];
   }
   $x = json_encode($gallery);
   $abc = _mysql_do("UPDATE Users SET `galleries` = '$x' WHERE `userid` = '$REMOTE_ID'");
}

function writeit($text) {
    $x = fopen('convert.log', 'a');
    fwrite($x, $text);
    fclose($x);
}

?>
