<?php
/**
 *
 * w3 image attachments rotation. An extension for the phpBB Forum Software package.
 * @copyright (c) 2020, axew3, https://axew3.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * German translation by @HaioPaio
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

global $phpbb_container;
$config = $phpbb_container->get('config');
$request = $phpbb_container->get('request');
$mode = $request->variable('mode', '');

$lang = array_merge($lang, array(

	'W3ROTATEIMG_TEXT' => 'Drehen',
	'W3POPUP_TEXTEXPLAIN' => 'Klicke in das Bild um es zu drehen',
	'W3POPUP_BUTTONTEXT' => 'Bild speichern',
	'W3POPUP_ALERT' => 'Klicke in das Bild um es zu drehen, dann speichere es!',
	'W3POPUP_PROCESS' => 'In Bearbeitung...',

	// may DO NOT edit here below
	'W3IMAGEROTATION_PHPBBCOOKIEDOMAIN'	=> $config['cookie_domain'],
	'W3IMAGEROTATION_REQMODE'	=> $request->variable('mode', ''),

));
