<?php
/**
 * Imagick Media Adapter File
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
 * @subpackage media.libs.media.adapter
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */

/**
 * Imagick Media Adapter Class
 *
 * @package    media
 * @subpackage media.libs.media.adapter
 * @link       http://www.imagemagick.org/
 */
class ImagickMediaAdapter extends MediaAdapter {
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

	function initialize($Media) {
		if (isset($Media->objects['Imagick'])) {
			return true;
		}

		if (!isset($Media->file)) {
			return false;
		}

		try {
			$Media->objects['Imagick'] = new Imagick($Media->file);
		} catch (Exception $E) {
			return false;
		}
		return true;
	}

	function store($Media, $file) {
		try {
			return $Media->objects['Imagick']->writeImage($file);
		} catch (Exception $E) {
			return false;
		}
	}

	function toString($Media) {
		try {
			return $Media->objects['Imagick']->getImageBlob();
		} catch (Exception $E) {
			return false;
		}
	}

	function convert($Media, $mimeType) {
		if (!isset($this->_formatMap[$mimeType])) {
			return false;
		}

		try {
			$Media->objects['Imagick']->setFormat($this->_formatMap[$mimeType]);
		} catch (Exception $E) {
			return false;
		}

		$Media->mimeType = $mimeType;

		if ($Media->name === 'Document') { // application/pdf -> image
			return Media::factory($Media->objects['Imagick'], $mimeType);
		}
		return true;
	}

	function compress($Media, $value) {
		switch ($Media->mimeType) {
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
			return $Media->objects['Imagick']->setCompression($type)
			       && $Media->objects['Imagick']->setCompressionQuality($value);
		} catch (Exception $E) {
			return false;
		}
	}

	function crop($Media, $left, $top, $width, $height) {
		$left   = (integer)$left;
		$top    = (integer)$top;
		$width  = (integer)$width;
		$height = (integer)$height;

		try {
			return $Media->objects['Imagick']->cropImage($width, $height, $left, $top);
		} catch (Exception $E) {
			return false;
		}
	}

	function resize($Media, $width, $height) {
		$width  = (integer)$width;
		$height = (integer)$height;

		try {
			return $Media->objects['Imagick']->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);
		} catch (Exception $E) {
			return false;
		}
	}

	function cropAndResize($Media, $cropLeft, $cropTop, $cropWidth, $cropHeight, $resizeWidth, $resizeHeight) {
		return 	$this->crop($Media, $cropLeft, $cropTop, $cropWidth, $cropHeight)
				&& $this->resize($Media, $resizeWidth, $resizeHeight);
	}

	function width($Media) {
		try {
			return $Media->objects['Imagick']->getImageWidth();
		} catch (Exception $E) {
			return false;
		}
	}

	function height($Media) {
		try {
			return $Media->objects['Imagick']->getImageHeight();
		} catch (Exception $E) {
			return false;
		}
	}
}
?>