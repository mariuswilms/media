<?php
/**
 * Imagick Medium Adapter File
 *
 * Copyright (c) 2007-2008 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.libs.medium.adapter
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  2007-2008 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
/**
 * Imagick Medium Adapter Class
 *
 * @package    media
 * @subpackage media.libs.medium.adapter
 * @link       http://www.imagemagick.org/
 */
class ImagickMediumAdapter extends MediumAdapter {
	var $require = array(
							'mimeTypes' => array( /* readable */
								'image/jpeg',
								'image/gif',
								'image/png',
								'image/tiff',
								'image/wpg',
								'image/xbm',
								'image/xcf',
								'image/wbmp',
								'image/ms-bmp',
								'image/pcx',
								'application/pdf',
								'image/quicktime',
								'image/svg',
								'image/xpm',
								'image/ico',
								'image/psd',
								),
							 'extensions' => array('imagick'),
							);

	var $_formatMap = array( /* writable */
								'image/jpeg' => 'jpeg',
								'image/gif' => 'gif',
								'image/png' => 'png',
								'image/tiff' => 'tiff',
								'image/wbmp' => 'wbmp',
								'image/ms-bmp' => 'bmp',
								'image/pcx' => 'pcx',
								'image/ico' => 'ico',
								'image/xbm' => 'xbm',
								'image/psd' => 'psd',
						);

	/**
	 * Blur Factor
	 *
	 * @var integer Where < 1 is sharp and > 1 is unsharp
	 */
	var $_blurFactor = 1;

	/**
	 * Filter
	 *
	 * The Lanczos filter is timewise in the middle of the field
	 * and has very good results
	 *
	 * @link http://de3.php.net/manual/en/imagick.constants.php#imagick.constants.filters
	 * @link http://www.dylanbeattie.net/magick/filters/result.html
	 * @var integer
	 */
	var $_filter = null;

	function initialize(&$Medium) {
		$this->_filter = Imagick::FILTER_LANCZOS; // make sure imagick is available before using

		if (isset($Medium->objects['Imagick'])) {
			return true;
		}

		if (!isset($Medium->file)) {
			return false;
		}

		try {
			$Medium->objects['Imagick'] = new Imagick($Medium->file);
		} catch (Exception $E) {
			return false;
		}

		return true;
	}

	function store(&$Medium, $file) {
		try {
			return $Medium->objects['Imagick']->writeImage($file);
		} catch (Exception $E) {
			return false;
		}
	}

	function toString(&$Medium) {
		try {
			return $Medium->objects['Imagick']->getImageBlob();
		} catch (Exception $E) {
			return false;
		}
	}

	function convert(&$Medium, $mimeType) {
		if (!isset($this->_formatMap[$mimeType])) {
			return false;
		}

		try {
			$Medium->objects['Imagick']->setFormat($this->_formatMap[$mimeType]);
		} catch (Exception $E) {
			return false;
		}

		$Medium->mimeType = $mimeType;

		if ($Medium->name === 'Document') { // application/pdf -> image
			return Medium::factory($Medium->objects['Imagick'], $mimeType);
		}

		return true;
	}

	function crop(&$Medium, $left, $top, $width, $height) {
		$left   = intval($left);
		$top    = intval($top);
		$width  = intval($width);
		$height = intval($height);

		try {
			return $Medium->objects['Imagick']->cropImage($width, $height, $left, $top);
		} catch (Exception $E) {
			return false;
		}
	}

	function resize(&$Medium, $width, $height) {
		$width  = intval($width);
		$height = intval($height);

		try {
			return $Medium->objects['Imagick']->resizeImage($width, $height, $this->_filter, $this->_blurFactor);
		} catch (Exception $E) {
			return false;
		}
	}

	function cropAndResize(&$Medium, $cropLeft, $cropTop, $cropWidth, $cropHeight, $resizeWidth, $resizeHeight) {
		return 	$this->crop($Medium, $cropLeft, $cropTop, $cropWidth, $cropHeight)
				&& $this->resize($Medium, $resizeWidth, $resizeHeight);
	}

	function width(&$Medium) {
		try {
			return $Medium->objects['Imagick']->getImageWidth();
		} catch (Exception $E) {
			return false;
		}
	}

	function height(&$Medium) {
		try {
			return $Medium->objects['Imagick']->getImageHeight();
		} catch (Exception $E) {
			return false;
		}
	}
}
?>