<?php
/**
 * Plugin Configuration File
 *
 * In order to make the plugin work you must include this file
 * within either your app’s `core.php` or `bootstrap.php`.
 *
 * To overwrite defaults you'll define constants before including this file,
 * and overwrite other settings selectively with `Configure::write()`
 * calls after including it.
 *
 * Copyright (c) 2007-2010 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.3
 *
 * @package    media
 * @subpackage media.config
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 * @since      media 0.50
 */

/**
 * Directory paths
 *
 * Each constant is defined with a value which is
 * an absolute (slash terminated) path to a directory holding media files.
 *
 * Example:
 * 	`'/var/www/example.org/htdocs/app/webroot/media/'`
 */
if (!defined('MEDIA')) {
	define('MEDIA', WWW_ROOT . 'media' . DS);
}
if (!defined('MEDIA_STATIC')) {
	define('MEDIA_STATIC', MEDIA . 'static' . DS);
}
if (!defined('MEDIA_FILTER')) {
	define('MEDIA_FILTER', MEDIA . 'filter' . DS);
}
if (!defined('MEDIA_TRANSFER')) {
	define('MEDIA_TRANSFER', MEDIA . 'transfer' . DS);
}

/**
 * URL paths
 *
 * Each constant is defined with a value which is
 * an (slash terminated) URL path fragment relative to your webroot.
 *
 * In case the corresponding directory isn't served use `false` as a value.
 */
if (!defined('MEDIA_URL')) {
	define('MEDIA_URL', 'media/');
}
if (!defined('MEDIA_STATIC_URL')) {
	define('MEDIA_STATIC_URL', MEDIA_URL . 'static/');
}
if (!defined('MEDIA_FILTER_URL')) {
	define('MEDIA_FILTER_URL', MEDIA_URL . 'filter/');
}
if (!defined('MEDIA_TRANSFER_URL')) {
	define('MEDIA_TRANSFER_URL', MEDIA_URL . 'transfer/');
}

/*
 * Bootstrap the `mm` library. We are putting the library into the include path which
 * is expected (by the library) in order to be able to load classes.
 */
$mm = dirname(dirname(__FILE__)) . DS . 'libs' . DS . 'mm';

if (strpos(ini_get('include_path'), $mm) === false) {
	ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . $mm . DS . 'src');
}

/**
 * Configure the MIME type detection. The detection class is two headed which means it
 * uses both a glob (for matching against file extensions) and a magic adapter (for
 * detecting the type from the content of files). Available `Glob` adapters are `Apache`,
 * `Freedesktop`, `Memory` and `Php`. These adapters are also available as a `Magic`
 * variant with the addtion of a `Fileinfo` magic adapter. Not all adapters require
 * a file to be passed along with the configuration.
 *
 * @see TransferBehavior
 * @see MetaBehavior
 * @see MediaHelper
 */
require_once 'Mime/Type.php';

if (extension_loaded('fileinfo')) {
	Mime_Type::config('Magic', array(
		'adapter' => 'Fileinfo'
	));
} else {
	Mime_Type::config('Magic', array(
		'adapter' => 'Freedesktop',
		'file' => $mm . DS . 'data' . DS . 'magic.db'
	));
}
if ($cached = Cache::read('mime_type_glob')) {
	Mime_Type::config('Glob', array(
		'adapter' => 'Memory'
	));
	foreach ($cached as $item) {
		Mime_Type::$glob->register($item);
	}
} else {
	Mime_Type::config('Glob', array(
		'adapter' => 'Freedesktop',
		'file' => $mm . DS . 'data' . DS . 'glob.db'
	));
	Cache::write('mime_type_glob', Mime_Type::$glob->to('array'));
}

/**
 * Configure the adpters to be used by media process class. Adjust this
 * mapping of media names to adapters according to your environment. For example:
 * most PHP installations have GD enabled thus should choose the `Gd` adapter for
 * image transformations. However the `Imagick` adapter may be more desirable
 * in other cases and also supports transformations for documents.
 *
 * @see GeneratorBehavior
 */
require_once 'Media/Process.php';

Media_Process::config(array(
	// 'audio' => 'SoxShell',
	// 'document' => 'Imagick',
	'image' => 'Gd',
	// 'video' => 'FfmpegShell'
));

/**
 * Configure the adpters to be used by media info class. Adjust this
 * mapping of media names to adapters according to your environment. In contrast
 * to `Media_Proces` which operates only with one adapter per media type
 * `Media_Info` can use multiple adapter per media type.
 *
 * @see MetaBehavior
 */
require_once 'Media/Info.php';

Media_Info::config(array(
	// 'audio' => array('SoxShell', 'GetId3'),
	// 'document' => array('Imagick'),
	'image' => array('ImageBasic'),
	// 'video' => array('FfmpegShell', 'GetId3')
));

/**
 * Filters and versions
 *
 * For each media type a set of filters keyed by version name is configured.
 * A filter is a set of instructions which are processed by the Media class.
 *
 * @see GeneratorBehavior
 */
// $sRGB = $mm . DS . 'data' . DS . 'sRGB_IEC61966-2-1_black_scaled.icc';

$s = array('convert' => 'image/png', 'zoomCrop' => array(100, 100));
$m = array('convert' => 'image/png', 'fitCrop' => array(300, 300));
$l = array('convert' => 'image/png', 'fit' => array(600, 440));

Configure::write('Media.filter', array(
	'audio' => compact('s', 'm'),
	'document' => compact('s', 'm'),
	'generic' => array(),
	'image' => compact('s', 'm', 'l'),
	'video' => compact('s', 'm')
));

/**
 * @deprecated
 */
Configure::write('Media.filter.css', array('c'   => array('compress')));
Configure::write('Media.filter.icon', array());
Configure::write('Media.filter.text', array());
Configure::write('Media.filter.js', array('c'   => array('compress')));

?>