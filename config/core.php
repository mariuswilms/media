<?php
/**
 * Plugin Configuration File
 *
 * Include it in your app's core.php or bootstrap.php
 * then customize configuration values if you need to.
 *
 * Copyright (c) 2007-2009 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.config
 * @copyright  2007-2009 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 * @since      media 0.50
 */
/**
 * An absolute (slash terminated) path to a directory holding media files
 *
 * E.g.: /var/www/example.org/htdocs/app/webroot/media/
 */
	if (!defined('MEDIA')) {
		define('MEDIA', WWW_ROOT . 'media' . DS);
	}
/**
 * Either a (slash terminated) complete URL or an path fragment relative to your webroot
 *
 * E.g.: http://www.example.org/app/media/
 * E.g.: media/
 */
	if (!defined('MEDIA_URL')) {
		define('MEDIA_URL', 'media/');
	}
/**
 * MIME type detection by file extension
 *
 * 	engine - null for autodetection or core
 * 	db     - absolute path to a glob db file in freedesktop, apache, or php format
 * 	         (required for core)
 */
	Configure::write('Mime.glob', array(
		'engine' => null,
		'db'     => null,
	));
/**
 * MIME type detection by file content
 *
 * 	engine - null for autodetection or core, fileinfo, mime_magic
 * 	db     - absolute path to a glob db file in freedesktop, apache, or php format
 * 	         (optional for the fileinfo and mime_magic engine, required for core)
 */
	Configure::write('Mime.magic', array(
		'engine' => null,
		'db'     => null,
	));
/**
 * Media filters
 *
 * A filter is a set of instructions.
 * Each instruction represents a call to a method of the Medium class.
 */
	Configure::write('Media.filter.audio', array(
		's'   => array('convert' => 'image/png', 'fitCrop' => array(100, 100)),
		'm'   => array('convert' => 'image/png', 'fit' => array(300, 300)),
	    )
    );
	Configure::write('Media.filter.css', array(
		'c'   => array('compress'),
		)
	);
	Configure::write('Media.filter.document', array(
		'xxs' => array('convert' => 'image/png', 'zoomCrop' => array(16, 16)),
		's'   => array('convert' => 'image/png', 'fitCrop' => array(100, 100)),
		'm'   => array('convert' => 'image/png', 'fit' => array(300, 300)),
		)
	);
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
		)
	);
	Configure::write('Media.filter.text', array());
	Configure::write('Media.filter.video', array(
		'xxs' => array('convert' => 'image/png', 'zoomCrop' => array(16, 16)),
		's'   => array('convert' => 'image/png', 'fitCrop' => array(100, 100)),
		'm'   => array('convert' => 'image/png', 'fit' => array(300, 300)),
		)
	);
?>
