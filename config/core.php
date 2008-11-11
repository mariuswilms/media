<?php
/**
 * Plugin Configuration File
 *
 * Configuration for the media plugin
 *
 * Include it in your app's core.php or bootstrap.php then overwrite configuration
 * values if you like.
 *
 * Copyright (c) $CopyrightYear$ David Persson
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE
 *
 * PHP version $PHPVersion$
 * CakePHP version $CakePHPVersion$
 *
 * @category   configuration
 * @package    attm
 * @subpackage attm.plugins.media.config
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  $CopyrightYear$ David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version    SVN: $Id$
 * @version    Release: $Version$
 * @link       http://cakeforge.org/projects/attm The attm Project
 * @since      media plugin 0.50
 *
 * @modifiedby   $LastChangedBy$
 * @lastmodified $Date$
 */

	/**
	 * An absolute (slash terminated) path to a directory holding media files
	 * E.g.: /var/www/example.org/htdocs/app/webroot/media/
	 *
	 * Please also review the "directory layout" section in the docs
	 */
	if (!defined('MEDIA')) {
		define('MEDIA', WWW_ROOT . 'media' . DS);
	}

	/**
	 * Either a complete URL or an path fragment relative to your webroot
	 * (slash terminated)
	 *
	 * E.g.: http://www.example.org/app/media/
	 * E.g.: media/
	 */
	if (!defined('MEDIA_URL')) {
		define('MEDIA_URL', 'media/');
	}

	/**
	 * Settings used by MimeType class
	 */
	Configure::write('Mime.glob', array(
		'engine' => null, // null (auto detect) or core
		'db' => null, // absolute path to a glob db file in freedesktop, apache, or php format
	));

	Configure::write('Mime.magic', array(
		'engine' => null, // null (auto detect), core, fileinfo or mime_magic
		'db' => null, // absolute path to a magic db file in freedesktop, apache, or php format
	));

	/**
	 * Media filters
	 *
	 * A filter is a set of instructions
	 * Each instruction represents a call to a method of the Medium class
	 */
	Configure::write('Media.filter.audio', array());

	Configure::write('Media.filter.css', array(
		'c'		=> array('compress'),
		)
	);

	Configure::write('Media.filter.document', array(
		'xxs'	=> array('convert' => 'image/png', 'zoomCrop' => array(16, 16)),
		's'		=> array('convert' => 'image/png', 'fitCrop' => array(100, 100)),
		'm'		=> array('convert' => 'image/png', 'fit' => array(300, 300)),
		)
	);

	Configure::write('Media.filter.generic', array());

	Configure::write('Media.filter.image', array(
		'xxs' 	=> array('convert' => 'image/png', 'zoomCrop' => array(16, 16)),
		'xs'	=> array('convert' => 'image/png', 'zoomCrop' => array(32, 32)),
		's'		=> array('convert' => 'image/png', 'fitCrop' => array(100, 100)),
		'm'		=> array('convert' => 'image/png', 'fit' => array(300, 300)),
		'l'		=> array('convert' => 'image/png', 'fit' => array(450, 450)),
		'xl'	=> array('convert' => 'image/png', 'fit' => array(680, 440)),
		)
	);

	Configure::write('Media.filter.icon', array());

	Configure::write('Media.filter.js', array(
		'c'		=> array('compress'),
		)
	);

	Configure::write('Media.filter.text', array());

	Configure::write('Media.filter.video', array(
		'xxs'	=> array('convert' => 'image/png', 'zoomCrop' => array(16, 16)),
		's'		=> array('convert' => 'image/png', 'fitCrop' => array(100, 100)),
		'm'		=> array('convert' => 'image/png', 'fit' => array(300, 300)),
		)
	);
?>