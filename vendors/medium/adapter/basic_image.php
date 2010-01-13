<?php
/**
 * Basic Image Medium Adapter File
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
 * @subpackage media.libs.medium.adapter
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
/**
 * Basic Image Medium Adapter Class
 *
 * @package    media
 * @subpackage media.libs.medium.adapter
 */
class BasicImageMediumAdapter extends MediumAdapter {
	var $require = array(
		'mimeTypes' => array(
			'image/jpeg',
			'image/gif',
			'image/png',
			'image/tiff',
			'image/xbm',
			'image/wbmp',
			'image/ms-bmp',
			'image/xpm',
			'image/ico',
			'image/psd',
	));

	function initialize($Medium) {
		if (!isset($Medium->file)) {
			return false;
		}
		return true;
	}

	function width($Medium) {
		list($width, $height) = getimagesize($Medium->file);
		return $width;
	}

	function height($Medium) {
		list($width, $height) = getimagesize($Medium->file);
		return $height;
	}
}
?>