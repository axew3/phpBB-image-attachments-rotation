<?php
/**
* Rotate image by axew3
* axew3.com
*
* This file is not part of the phpBB Forum Software package.
*
* @copyright (c) 2024 axew3.com
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

/**
* @ignore
*/

define('IN_PHPBB', true);

#/////////////////
#
// Define the root from here
$phpbb_root_path = './../../../../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
#
#/////////////////

// Thank you sun.
if (isset($_SERVER['CONTENT_TYPE']))
{
  if ($_SERVER['CONTENT_TYPE'] === 'application/x-java-archive')
  {
    exit;
  }
}
else if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Java') !== false)
{
  exit;
}

// implicit else: we are not in avatar mode
 include($phpbb_root_path . 'common.' . $phpEx);
 require($phpbb_root_path . 'includes/functions_download' . '.' . $phpEx);

///////////////////
//

 $request = $phpbb_container->get('request');
 $ajaxdata = $request->variable('data', '');
 $config = $phpbb_container->get('config');

if(empty($ajaxdata)){
  echo 'W3ERROR_NO AJAX DATA'; exit;
}

$ajaxdata = str_replace(chr(0), '', $ajaxdata);
$ajaxdata = explode(',',$ajaxdata);

 $degrees = isset($ajaxdata[0]) ? intval($ajaxdata[0]) : 0;
 $attach_id = isset($ajaxdata[1]) ? intval($ajaxdata[1]) : 0;
 $uSid = isset($ajaxdata[2]) ? $ajaxdata[2] : 0;
 $uAgent = isset($ajaxdata[3]) ? trim(base64_decode($ajaxdata[3])) : 0;
 $uAgent = str_replace(chr(0), '', $uAgent);
 $uIp = isset($ajaxdata[4]) ? trim($ajaxdata[4]) : '127.0.0.1';

  if ( preg_match('/[^0-9A-Za-z]/',$uSid) ){
   $uSid = 0;
  }

 unset($ajaxdata);

 if( $attach_id < 1 ){
  echo 'W3ERROR_NO ATTACHMENT'; exit;
 }

# Start session management, do not update session page.

 $user->session_begin(false);
 if (empty($user->data)) { return; }
 $auth->acl($user->data);
 $user->setup('viewtopic');

if (!$config['allow_attachments'] && !$config['allow_pm_attach'])
{
  echo 'W3ERROR_ATTACHMENT FUNCTIONALITY DISABLED'; exit;
}

# It is required also the forum id of this attach, to know if this user can or not execute things here
# -> since 1.0.6 > let only the file's owner to rotate the image

 /*$sql = 'SELECT T.*, A.*
  FROM ' . ATTACHMENTS_TABLE . " AS A
  JOIN " . TOPICS_TABLE . " AS T on T.topic_id = A.topic_id
  WHERE A.attach_id = $attach_id";
 $result = $db->sql_query($sql);
 $attachment = $db->sql_fetchrow($result);
 $db->sql_freeresult($result);*/

 # The above is not required since 1.0.6

if (empty($attachment)) // may it is an attachment just uploaded into a new topic/post that do not exist
{
 $sql = 'SELECT *
  FROM ' . ATTACHMENTS_TABLE . "
  WHERE attach_id = $attach_id";
 $result = $db->sql_query($sql);
 $attachment = $db->sql_fetchrow($result);
 $db->sql_freeresult($result);
}

if (!$attachment)
{
  echo 'W3ERROR_NO ATTACHMENT FOUND ON DB'; exit;
}
elseif (!download_allowed())
{
  echo 'W3ERROR_LINKAGE FORBIDDEN'; exit;
}
else
{

 if( $user->data['user_id'] < 2 )
 {

  $session = $request->variable($config['cookie_name'] . '_sid', '', false, \phpbb\request\request_interface::COOKIE);

  if( empty($session) && !empty($uSid) )
  {
    $session = $uSid;
  }

  if(!empty($session))
  {

     if ( preg_match('/[^0-9A-Za-z]/',$session) ){
        echo 'W3ERROR_NO VALID COOKIE sid'; exit;
      }

    $cuid = $request->variable($config['cookie_name'] . '_u', 0, false, \phpbb\request\request_interface::COOKIE);
    $cuid = $cuid < 2 ? $attachment['poster_id'] : $cuid;

   if( $cuid > 1 )
   { # maybe only the user_id is required here
       #$sql = "SELECT * FROM " . USERS_TABLE . " JOIN " . SESSIONS_TABLE . "
       $sql = "SELECT user_id FROM " . USERS_TABLE . " JOIN " . SESSIONS_TABLE . "
       WHERE " . SESSIONS_TABLE . ".session_user_id = '" . (int) $cuid . "'
        AND " . SESSIONS_TABLE . ".session_id = '" . $db->sql_escape($session)."'
        AND " . SESSIONS_TABLE . ".session_ip = '" . $db->sql_escape($uIp)."'
        AND " . SESSIONS_TABLE . ".session_browser = '" . $db->sql_escape($uAgent)."'
        AND " . SESSIONS_TABLE . ".session_user_id = " . USERS_TABLE . ".user_id";

      $result = $db->sql_query($sql);
      #$user_data = $db->sql_fetchrow($result);#$user_data['user_id'];
      $user_id = $db->sql_fetchrow($result);
    }
   }
  }

  #if(!empty($user_data['user_id']) && $user_data['user_id'] > 1 && $user->data['user_id'] < 2)
  if(!empty($user_id['user_id']) && $user_id['user_id'] > 1 && $user->data['user_id'] < 2)
  {
    $user->data['user_id'] = $user_id['user_id'];
  }

  # Let nobody but the attachment owner to follow
  #$own_attachment = $attachment['poster_id'] == $user->data['user_id'] ? true : false;
  # Allow admins having attachment permissions to edit
  $own_attachment = ($auth->acl_get('a_attach') || $attachment['poster_id'] == $user->data['user_id']) ? true : false;

   if ( !$own_attachment ) {
     echo 'W3ERROR_NO PERMISSION'; exit;
   }

#########

 $validImgExt = array("jpg", "jpeg", "gif", "png", "webp");

 if (!in_array(strtolower($attachment['extension']), $validImgExt)) {
    echo 'W3ERROR_NO VALID IMG EXTENSION'; exit;
 }

 $degrees = 360-$degrees; # passed clockwise, expect anticlockwise
 $filesFolderPhysicalName = $phpbb_root_path . $config['upload_path']. '/' . $attachment['physical_filename'];
 $filesFolderThumbPhysicalName = $phpbb_root_path . $config['upload_path']. '/' . 'thumb_' . $attachment['physical_filename'];

 if( strtolower($attachment['extension']) == 'jpg' OR strtolower($attachment['extension']) == 'jpeg' ){
   $source = @imagecreatefromjpeg($filesFolderPhysicalName);
  } elseif(strtolower($attachment['extension']) == 'gif'){
   $source = @imagecreatefromgif($filesFolderPhysicalName);
    $width = @imagesx($source);
    $height = @imagesy($source);
  } elseif(strtolower($attachment['extension']) == 'png'){
   $source = @imagecreatefrompng($filesFolderPhysicalName);
  } elseif(strtolower($attachment['extension']) == 'webp'){
   $source = @imagecreatefromwebp($filesFolderPhysicalName);
  }

    if(!$source)
    {
     echo "W3ERROR_SOURCEIMG NOTEXIST OR CANTCREATE"; exit;
    }

  if($source){
  // JPG
   if( strtolower($attachment['extension']) == 'jpg' OR strtolower($attachment['extension']) == 'jpeg' ){
    $rotate = imagerotate($source, $degrees, 0);
    $saved = imagejpeg($rotate, $filesFolderPhysicalName);
    // thumb
     $source = @imagecreatefromjpeg($filesFolderThumbPhysicalName);
     if($source){
       $rotate = imagerotate($source, $degrees, 0);
      if($rotate){
       imagejpeg($rotate, $filesFolderThumbPhysicalName);
      }
     }

   } elseif (strtolower($attachment['extension']) == 'gif'){
   # GIF: will not save the gif BG Matte if there is applied to the gif
   // https://www.axew3.com/w3/forums/viewtopic.php?f=7&t=1572
  $new_image = imagecreatetruecolor($width, $height);
  $transparencyIndex = imagecolortransparent($source);
  $transparencyColor = array('red' => 255, 'green' => 255, 'blue' => 255);

   if ($transparencyIndex >= 0) {
      //$transparencyColor = @imagecolorsforindex($source, $transparencyIndex); // this throw error in Php8 for gif
        $transparencyColor = $transparencyIndex != -1 ? imagecolorsforindex($new_image, ($transparencyIndex < imagecolorstotal($new_image) ? $transparencyIndex : $transparencyIndex - 1)) : 0;
    }
     if(!$transparencyColor){
      //exit;
    }
  $transparencyIndex = imagecolorallocate($new_image, $transparencyColor['red'], $transparencyColor['green'], $transparencyColor['blue']);
    imagefill($new_image, 0, 0, $transparencyIndex);
    imagecolortransparent($new_image, $transparencyIndex);
    imagecopyresampled($new_image, $source, 0, 0, 0, 0, $width, $height, $width, $height);
    //imagecopy($new_image, $source, 0, 0, 0, 0, $width, $height); // same result
    //imagecopyresized($new_image, $source, 0, 0, 0, 0, $width, $height, $width, $height); // same result
    $rotate = imagerotate($new_image, $degrees,0);
    $saved = imagegif($rotate,$filesFolderPhysicalName);

   // thumb ... there is no thumb for gif format

   } elseif (strtolower($attachment['extension']) == 'png'){
  // PNG
    $bgColor = imagecolorallocatealpha($source, 255, 255, 255, 127);
    $rotate = imagerotate($source, $degrees, $bgColor);
    imagesavealpha($rotate, true);
    $saved = imagepng($rotate,$filesFolderPhysicalName);
     // thumb
      $source = @imagecreatefrompng($filesFolderThumbPhysicalName);
      if($source){
       $rotate = imagerotate($source, $degrees, $bgColor);
       if($rotate){
        imagesavealpha($rotate, true);
        imagepng($rotate,$filesFolderThumbPhysicalName);
       }
      }
    } elseif( strtolower($attachment['extension']) == 'webp'){
       $rotate = imagerotate($source, $degrees, 0);
       $saved = imagewebp($rotate, $filesFolderPhysicalName);
       // thumb
       $source = @imagecreatefromwebp($filesFolderThumbPhysicalName);
       if($source){
         $rotate = imagerotate($source, $degrees, 0);
        if($rotate){ imagewebp($rotate, $filesFolderThumbPhysicalName); }
       }
      }

  if($source){
   $endOK = true;
   imagedestroy($source);
  }
  if($rotate){
   $endOK = true;
   imagedestroy($rotate);
  }


if ($saved){
  # Set the attach ID to a new one into db, to get the new file after, and not the cached one
  # There is a more efficient way to achieve this? Using phpbb.plupload, after the response?

 $sql_arr = array(
    'attach_id'    => '0',
    'post_msg_id'  => $attachment['post_msg_id'],
    'topic_id'     => $attachment['topic_id'],
    'in_message'   => $attachment['in_message'],
    'poster_id'    => $attachment['poster_id'],
    'is_orphan'    => $attachment['is_orphan'],
    'physical_filename' => $attachment['physical_filename'],
    'real_filename' => $attachment['real_filename'],
    'download_count' => $attachment['download_count'],
    'attach_comment' => $attachment['attach_comment'],
    'extension'    => $attachment['extension'],
    'mimetype'     => $attachment['mimetype'],
    'filesize'     => $attachment['filesize'],
    'filetime'     => $attachment['filetime'],
    'thumbnail'    => $attachment['thumbnail']
 );

$sql = 'INSERT INTO ' . ATTACHMENTS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_arr);
$db->sql_query($sql);
$new_attachID = (int) $db->sql_nextid();

 if($new_attachID > 0){ // delete older
   $sql = "DELETE FROM " . ATTACHMENTS_TABLE . "
        WHERE attach_id = $attach_id";
   $db->sql_query($sql);
  } else { unset($endOK); }

}

  if(isset($endOK) && $saved){ // if not saved, the placeholder ID will not match: anyway in this case, the placeholder will need to be removed, then re-added on post
   echo "OK-IMG-PROCESSED//#//".$attachment['real_filename'].'//#//'.$attachment['attach_id'].'//#//'.$new_attachID.'//#//'.$attachment['filesize'].'//#//'.$attachment['is_orphan'].'//#//'.$attachment['mimetype'].'//#//'.$attachment['attach_comment'];
  } else {
    echo "W3ERROR_SOMETHING WENT WRONG THE IMAGE MAY HAS NOT BEEN PROCESSED";
  }


  } // END if($source){

exit;

}
