<?php
/**
 * Imagick Medium Adapter File
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
			'image/quicktime',
			'image/svg',
			'image/xpm',
			'image/ico',
			'image/psd',
			'application/pdf',
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

	function initialize($Medium) {
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

	function store($Medium, $file) {
		try {
			return $Medium->objects['Imagick']->writeImage($file);
		} catch (Exception $E) {
			return false;
		}
	}

	function toString($Medium) {
		try {
			return $Medium->objects['Imagick']->getImageBlob();
		} catch (Exception $E) {
			return false;
		}
	}

	function convert($Medium, $mimeType) {
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

	function compress($Medium, $value) {
		switch ($Medium->mimeType) {
			case 'image/tiff':
				$type = Imagick::COMPRESSION_LZW;
				$value = null;
				break;
			case 'image/png':
				$type = Imagick::COMPRESSION_ZIP;
				$value = (integer)$value; // FIXME correct ?
				break;
			case 'image/jpeg':
				$type = Imagick::COMPRESSION_JPEG;
				$value = (integer)(100 - ($value * 10));
				break;
			default:
				return true;
		}
		try {
			return $Medium->objects['Imagick']->setCompression($type)
			       && $Medium->objects['Imagick']->setCompressionQuality($value);
		} catch (Exception $E) {
			return false;
		}
	}

	function crop($Medium, $left, $top, $width, $height) {
		$left   = (integer)$left;
		$top    = (integer)$top;
		$width  = (integer)$width;
		$height = (integer)$height;

		try {
			return $Medium->objects['Imagick']->cropImage($width, $height, $left, $top);
		} catch (Exception $E) {
			return false;
		}
	}

	function resize($Medium, $width, $height) {
		$width  = (integer)$width;
		$height = (integer)$height;

		try {
			return $Medium->objects['Imagick']->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);
		} catch (Exception $E) {
			return false;
		}
	}

	function cropAndResize($Medium, $cropLeft, $cropTop, $cropWidth, $cropHeight, $resizeWidth, $resizeHeight) {
		return 	$this->crop($Medium, $cropLeft, $cropTop, $cropWidth, $cropHeight)
				&& $this->resize($Medium, $resizeWidth, $resizeHeight);
	}

	function width($Medium) {
		try {
			return $Medium->objects['Imagick']->getImageWidth();
		} catch (Exception $E) {
			return false;
		}
	}

	function height($Medium) {
		try {
			return $Medium->objects['Imagick']->getImageHeight();
		} catch (Exception $E) {
			return false;
		}
	}
}
?>