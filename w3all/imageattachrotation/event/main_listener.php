<?php
/**
 *
 * w3all - Attachments images rotation. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, axew3, https://axew3.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace w3all\imageattachrotation\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Image attachment rotation Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
	  return array(
	    'core.user_setup' => 'load_language_on_setup',
	    'core.download_file_send_to_browser_before'	=> 'download_file_send_to_browser_before',
	    'core.display_forums_modify_template_vars'	=> 'display_forums_modify_template_vars',
	  );
	}

	/* @var \phpbb\language\language */
	protected $language;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language	$language	Language object
	 */
	public function __construct(\phpbb\language\language $language)
	{
	  $this->language = $language;
	}

	/**
	 * Load common language files during user setup
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
		  'ext_name' => 'w3all/imageattachrotation',
		  'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * @param \phpbb\event\data	$event	Event object
	 */
 public function download_file_send_to_browser_before($event)
	{
	  global $auth,$attachment,$user,$phpbb_container,$cache;
	  $request = $phpbb_container->get('request');

   // if it is an allowed img and the request is 'rotate'

   $validImgExt = array("jpg", "jpeg", "gif", "png");
   $own_attachment = ($auth->acl_get('a_attach')  || $attachment['poster_id'] == $user->data['user_id']) ? true : false;
   $mode = $request->variable('mode', '');
   $attach_id = $attachment['attach_id'];

   if ( $mode == 'rImg' && in_array($attachment['extension'], $validImgExt) && $own_attachment === true ) {
	  require('./../ext/w3all/imageattachrotation/core/rotateImg.php');
   }
   // or let go
  }
  
 public function display_forums_modify_template_vars($event)
	{
	}
	
 }
