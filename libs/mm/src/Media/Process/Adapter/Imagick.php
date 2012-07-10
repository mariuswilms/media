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

		// @fixme Workaround for imagick failing to work with handles before module version 3.0.
		// See http://pecl.php.net/bugs/bug.php?id=16932 for more information.
		// $this->_object->readImageFile($handle);
		$this->_object->readImageBlob(stream_get_contents($handle, -1, 0));

		// Reset iterator to get just the first image from i.e. multipage PDFs.
		if ($this->_object->getNumberImages() > 1) {
			$this->_object->setFirstIterator();
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
		// @fixme Workaround for imagick failing to work with handles before module version 3.0.
		// See http://pecl.php.net/bugs/bug.php?id=16932 for more information.
		// return $this->_object->writeImageFile($handle);
		return fwrite($handle, $this->_object->getImageBlob());
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

	public function passthru($key, $value) {
		$method = $key;
		$args = (array) $value;

		if (!method_exists($this->_object, $method)) {
			$message = "Cannot passthru to nonexistent method `{$method}` on internal object";
			throw new Exception($message);
		}
		return (boolean) call_user_func_array(array($this->_object, $method), $args);
	}

	// @link http://studio.imagemagick.org/pipermail/magick-users/2002-August/004435.html
	public function compress($value) {
		switch ($this->_object->getFormat()) {
			case 'tiff':
				return $this->_object->setImageCompression(Imagick::COMPRESSION_LZW);
			case 'png':
				$filter = ($value * 10) % 10;
				$level = (integer) $value;

				return $this->_object->setImageCompression(Imagick::COMPRESSION_ZIP)
					&& $this->_object->setImageCompressionQuality($level * 10 + $filter);
			case 'jpeg':
				return $this->_object->setImageCompression(Imagick::COMPRESSION_JPEG)
					&& $this->_object->setImageCompressionQuality((integer) (100 - ($value * 10)));
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

	public function depth($value) {
		return $this->_object->setImageDepth($value);
	}

	public function interlace($value) {
		if (!$value) {
			return $this->_object->setInterlaceScheme(Imagick::INTERLACE_NO);
		}
		$constant = 'Imagick::INTERLACE_' . strtoupper($this->_object->getFormat());

		if (!defined($constant)) {
			return false;
		}
		return $this->_object->setInterlaceScheme(constant($constant));
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