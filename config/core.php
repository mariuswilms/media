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
 * CakePHP version 1.2
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
 * either a (slash terminated) complete URL or an path fragment relative to your webroot.
 *
 * In case the corresponding directory isn't served use `false` as a value.
 *
 * Examples:
 * 	`'http://www.example.org/app/media/'`
 * 	`'media/'`
 * 	`false`
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
/**
 * MIME type detection by file extension
 *
 * Options:
 * 	engine - `null` for autodetection or `'core'`
 * 	db     - Absolute path to a glob db file in freedesktop, apache, or php format
 * 	         (required for core engine)
 */
Configure::write('Mime.glob', array(
	'engine' => null,
	'db'     => null,
));
/**
 * MIME type detection by file content
 *
 * Options:
 * 	engine - `null` for autodetection or `'core'`, `'fileinfo'`, `'mime_magic'`
 * 	db     - Absolute path to a glob db file in freedesktop, apache, or php format
 * 	         (optional for the fileinfo and mime_magic engine, required for core engine)
 */
Configure::write('Mime.magic', array(
	'engine' => null,
	'db'     => null,
));
/**
 * Filters and versions
 *
 * For each medium type a set of filters keyed by version name is configured.
 * A filter is a set of instructions which are processed by the Medium class.
 */
Configure::write('Media.filter.audio', array(
	's'   => array('convert' => 'image/png', 'fitCrop' => array(100, 100)),
	'm'   => array('convert' => 'image/png', 'fit' => array(300, 300)),
));
Configure::write('Media.filter.css', array(
	'c'   => array('compress'),
));
Configure::write('Media.filter.document', array(
	'xxs' => array('convert' => 'image/png', 'zoomCrop' => array(16, 16)),
	's'   => array('convert' => 'image/png', 'fitCrop' => array(100, 100)),
	'm'   => array('convert' => 'image/png', 'fit' => array(300, 300)),
));
Configure::write('Media.filter.generic', array());
Configure::write('Media.filter.image', array(
	'xxs' => array('convert' => 'image/png', 'zoomCrop' => array(16, 16)),
	'xs'  => array('convert' => 'image/png', 'zoomCrop' => array(32, 32)),
	's'   => array('convert' => 'image/png', 'fitCrop' => array(100, 100)),
	'm'   => array('convert' => 'image/png', 'fit' => array(300, 300)),
	'l'   => array('convert' => 'image/png', 'fit' => array(450, 450)),
	'xl'  => array('convert' => 'image/png', 'fit' => array(680, 440)),
	)
);
Configure::write('Media.filter.icon', array());
Configure::write('Media.filter.js', array(
	'c'   => array('compress'),
));
Configure::write('Media.filter.text', array());
Configure::write('Media.filter.video', array(
	'xxs' => array('convert' => 'image/png', 'zoomCrop' => array(16, 16)),
	's'   => array('convert' => 'image/png', 'fitCrop' => array(100, 100)),
	'm'   => array('convert' => 'image/png', 'fit' => array(300, 300)),
));
?>
