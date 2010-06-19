<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2010 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright  2007-2010 David Persson <nperson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/mm
 */

require_once 'Media/Process/Adapter.php';

/**
 * This media process adapter allows for interfacing with ImageMagick through
 * the `imagick` pecl extension (which must be loaded in order to use this adapter).
 *
 * @link       http://php.net/imagick
 * @link       http://www.imagemagick.org
 */
class Media_Process_Adapter_Imagick extends Media_Process_Adapter {

	protected $_object;

	protected $_formatMap = array(
		'application/pdf' => 'pdf',
		'image/jpeg' => 'jpeg',
		'image/gif' => 'gif',
		'image/png' => 'png',
		'image/tiff' => 'tiff',
		'image/wbmp' => 'wbmp',
		'image/ms-bmp' => 'bmp',
		'image/pcx' => 'pcx',
		'image/ico' => 'ico',
		'image/xbm' => 'xbm',
		'image/psd' => 'psd'
	);

	public function __construct($handle) {
		$this->_object = new Imagick();

		try {
			$this->_object->readImageFile($handle);
		} catch (ImagickException $e) {
			// @fixme Workaaround for imagick failing to work with handles before module version 3.0.
			// See http://pecl.php.net/bugs/bug.php?id=16932 for more information.
			$this->_object->readImageBlob(stream_get_contents($handle, -1, 0));
		}

		$mimeType = Mime_Type::guessType($handle);

		if (!isset($this->_formatMap[$mimeType])) {
			throw new OutOfBoundsException("MIME type `{$mimeType}` cannot be mapped to a format.");
		}
		// We need to explictly `setFormat()` here, otherwise `getFormat()` returns `null`.
		$this->_object->setFormat($this->_formatMap[$mimeType]);
	}

	public function __destruct() {
		if ($this->_object) {
			$this->_object->clear();
		}
	}

	public function store($handle) {
		try {
			return $this->_object->writeImageFile($handle);
		} catch (ImagickException $e) {
			// @fixme Workaaround for imagick failing to work with handles before module version 3.0.
			// See http://pecl.php.net/bugs/bug.php?id=16932 for more information.
			return fwrite($handle, $this->_object->getImageBlob());
		}
	}

	public function convert($mimeType) {
		if (Mime_Type::guessName($mimeType) != 'image') {
			return true;
		}
		if (!isset($this->_formatMap[$mimeType])) {
			throw new OutOfBoundsException("MIME type `{$mimeType}` cannot be mapped to a format.");
		}
		return $this->_object->setFormat($this->_formatMap[$mimeType]);
	}

	public function compress($value) {
		switch ($this->_object->getFormat()) {
			case 'tiff':
				return $this->_object->setCompression(Imagick::COMPRESSION_LZW);
			case 'png':
				return $this->_object->setCompression(Imagick::COMPRESSION_ZIP)
					&& $this->_object->setCompressionQuality((integer) $value);
			case 'jpeg':
				return $this->_object->setCompression(Imagick::COMPRESSION_JPEG)
					&& $this->_object->setCompressionQuality((integer) (100 - ($value * 10)));
			default:
				throw new Exception("Cannot compress this format.");
		}
	}

	public function profile($type, $data = null) {
		if (!$data) {
			$profiles = $this->_object->getImageProfiles('*', false);

			if (!in_array($type, $profiles)) {
				return false;
			}
			return $this->_object->getImageProfile($type);
		}

		try {
			return $this->_object->profileImage($type, $data);
		} catch (Exception $e) {
			$corruptProfileMessage = 'color profile operates on another colorspace `icc';
			// $corruptProfileCode = 465;

			if (strpos($e->getMessage(), $corruptProfileMessage) !== false) {
				return $this->strip($type) && $this->profile($type, $data);
			}
			throw $e;
		}
	}

	public function strip($type) {
		return $this->_object->profileImage($type, null);
	}

	public function crop($left, $top, $width, $height) {
		$left   = (integer) $left;
		$top    = (integer) $top;
		$width  = (integer) $width;
		$height = (integer) $height;

		return $this->_object->cropImage($width, $height, $left, $top);
	}

	public function resize($width, $height) {
		$width  = (integer) $width;
		$height = (integer) $height;

		return $this->_object->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);
	}

	public function cropAndResize($cropLeft, $cropTop, $cropWidth, $cropHeight, $resizeWidth, $resizeHeight) {
		return $this->crop($cropLeft, $cropTop, $cropWidth, $cropHeight)
			&& $this->resize($resizeWidth, $resizeHeight);
	}

	public function width() {
		return $this->_object->getImageWidth();
	}

	public function height() {
		return $this->_object->getImageHeight();
	}
}

?>