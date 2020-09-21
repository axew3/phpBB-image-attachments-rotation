<?php
/**
* Rotate image by axew3 
* axew3.com
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* @ignore
*/

define('IN_PHPBB', true);

///////////////////
//

// $phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../../../../';
// Define the root from here
$phpbb_root_path = './../../../../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

//
///////////////////

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
 $request	= $phpbb_container->get('request');
 //$config = $phpbb_container->get('config'); // already included
 $ajaxdata = $request->variable('data', '');

if(empty($ajaxdata)){
	send_status_line(404, 'Not Found');
	trigger_error('ERROR_NO_ATTACHMENT');
}

$ajaxdata = str_replace(chr(0), '', $ajaxdata);
$ajaxdata = explode(',',$ajaxdata);

 $degrees = isset($ajaxdata[0]) ? intval($ajaxdata[0]) : 0;
 $attach_id = isset($ajaxdata[1]) ? intval($ajaxdata[1]) : 0;
 unset($ajaxdata);

 if( $attach_id < 1 ){
  send_status_line(404, 'Not Found');
	trigger_error('ERROR_NO_ATTACHMENT');
 }

//
///////////////////

// Start session management, do not update session page.
 $user->session_begin(false);
 $auth->acl($user->data);
 $user->setup('viewtopic');

$phpbb_content_visibility = $phpbb_container->get('content.visibility');

if (!$config['allow_attachments'] && !$config['allow_pm_attach'])
{
	send_status_line(404, 'Not Found');
	trigger_error('ATTACHMENT_FUNCTIONALITY_DISABLED');
}

 $sql = 'SELECT * 
	FROM ' . ATTACHMENTS_TABLE . "
	WHERE attach_id = $attach_id";
 $result = $db->sql_query($sql);
 $attachment = $db->sql_fetchrow($result);
 $db->sql_freeresult($result);

if (!$attachment)
{
	send_status_line(404, 'Not Found');
	trigger_error('ERROR_NO_ATTACHMENT');
}
else if (!download_allowed())
{
	send_status_line(403, 'Forbidden');
	trigger_error($user->lang['LINKAGE_FORBIDDEN']);
}
else
{
	$attachment['physical_filename'] = utf8_basename($attachment['physical_filename']);

	if (!$attachment['in_message'] && !$config['allow_attachments'] || $attachment['in_message'] && !$config['allow_pm_attach'])
	{
		send_status_line(404, 'Not Found');
		trigger_error('ATTACHMENT_FUNCTIONALITY_DISABLED');
	}

	if ($attachment['is_orphan'])
	{
		// We allow admins having attachment permissions to see orphan attachments...
		$own_attachment = ($auth->acl_get('a_attach') || $attachment['poster_id'] == $user->data['user_id']) ? true : false;

		if (!$own_attachment)
		{
			send_status_line(404, 'Not Found');
			trigger_error('ERROR_NO_ATTACHMENT');
		}

		// Obtain all extensions...
		$extensions = $cache->obtain_attach_extensions(true);
	}
	else
	{
		if (!$attachment['in_message'])
		{
			phpbb_download_handle_forum_auth($db, $auth, $attachment['topic_id']);

			$sql = 'SELECT forum_id, post_visibility
				FROM ' . POSTS_TABLE . '
				WHERE post_id = ' . (int) $attachment['post_msg_id'];
			$result = $db->sql_query($sql);
			$post_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$post_row || !$phpbb_content_visibility->is_visible('post', $post_row['forum_id'], $post_row))
			{
				// Attachment of a soft deleted post and the user is not allowed to see the post
				send_status_line(404, 'Not Found');
				trigger_error('ERROR_NO_ATTACHMENT');
			}
		}
		else
		{
			// Attachment is in a private message.
			$post_row = array('forum_id' => false);
			phpbb_download_handle_pm_auth($db, $auth, $user->data['user_id'], $attachment['post_msg_id']);
		}

		$extensions = array();
		if (!extension_allowed($post_row['forum_id'], $attachment['extension'], $extensions))
		{
			send_status_line(403, 'Forbidden');
			trigger_error(sprintf($user->lang['EXTENSION_DISABLED_AFTER_POSTING'], $attachment['extension']));
		}
	}

	$download_mode = (int) $extensions[$attachment['extension']]['download_mode'];
	$display_cat = $extensions[$attachment['extension']]['display_cat'];

	if (($display_cat == ATTACHMENT_CATEGORY_IMAGE || $display_cat == ATTACHMENT_CATEGORY_THUMB) && !$user->optionget('viewimg'))
	{
		$display_cat = ATTACHMENT_CATEGORY_NONE;
	}
	

// let admins and attachment owner to follow 


	$own_attachment = ($auth->acl_get('a_attach') || $attachment['poster_id'] == $user->data['user_id']) ? true : false;
	if (!$own_attachment) {
			send_status_line(403, 'Forbidden');
		}

///////////////////
//
	
 $validImgExt = array("jpg", "jpeg", "gif", "png");
 
 if (!in_array(strtolower($attachment['extension']), $validImgExt)) {
    send_status_line(404, 'Not Found');
		trigger_error('ERROR_NO_ATTACHMENT');
 }
 
 $degrees = 360-$degrees; // passed clockwise, expect anticlockwise
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
	} 
	
	  if(!$source)
    {
     echo "ERROR-SOURCEIMG-NOTEXIST-OR-CANTCREATE"; exit; 
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
	 // GIF: will not save the gif BG Matte if there is applied to the gif
	 // https://www.axew3.com/w3/forums/viewtopic.php?f=7&t=1572 
  $new_image = imagecreatetruecolor($width, $height);
  $transparencyIndex = imagecolortransparent($source);
  $transparencyColor = array('red' => 255, 'green' => 255, 'blue' => 255);
            
   if ($transparencyIndex >= 0) {
      $transparencyColor = @imagecolorsforindex($source, $transparencyIndex);   
    }
     if(!$transparencyColor){
     	echo "\n ERROR-IMAGECOLORSFORINDEX";
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
  // Set the attach ID to a new one into db, to get the new file after, and not the cached one
  // There is a more efficient way to achieve this? Using phpbb.plupload, after the response?
 
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
  }
  
}
  
  if(isset($endOK) && $saved){ // if not saved, the placeholder ID will not match: anyway in this case, the placeholder will need to be removed, then re-added on post
   echo "OK-IMG-PROCESSED//#//".$attachment['real_filename'].'//#//'.$attachment['attach_id'].'//#//'.$new_attachID.'//#//'.$attachment['filesize'].'//#//'.$attachment['is_orphan'].'//#//'.$attachment['mimetype'].'//#//'.$attachment['attach_comment'];
  } else {
  	echo "ERROR-IMG-NOTPROCESSED";
  }

   
  } // END if($source){

exit;
//
///////////////////
}
