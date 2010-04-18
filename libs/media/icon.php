<?php
/**
 * Icon Media File
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
 * @subpackage media.libs.media
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
if (!class_exists('ImageMedia')) {
	App::import('Lib', 'Media.ImageMedia', array('file' => 'media' . DS . 'image.php'));
}

/**
 * Icon Media Class
 *
 * @package    media
 * @subpackage media.libs.media
 */
class IconMedia extends ImageMedia {

	function __construct($file, $mimeType = null) {
		$message  = "IconMedia::__construct - ";
		$message .= "All functionality related to assets has been deprecated.";
		trigger_error($message, E_USER_NOTICE);
		parent::__construct($file, $mimeType);
	}
}
?>