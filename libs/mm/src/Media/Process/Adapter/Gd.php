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
require_once 'Mime/Type.php';

/**
 * This media process adapter allows for interfacing with the native `gd` pecl extension.
 *
 * @link       http://php.net/gd
 */
class Media_Process_Adapter_Gd extends Media_Process_Adapter {

	protected $_object;

	protected $_formatMap = array(
		'image/jpeg' => 'jpeg',
		'image/gif' => 'gif',
		'image/png' => 'png',
		'image/gd' => 'gd',
		'image/vnd.wap.wbmp' => 'wbmp',
		'image/xbm' => 'xbm',
	);

	protected $_format;

	protected $_compression;

	protected $_pngFilter;

	public function __construct($handle) {
		$mimeType = Mime_Type::guessType($handle);

		if (!isset($this->_formatMap[$mimeType])) {
			throw new OutOfBoundsException("Could not map MIME-type `{$mimeType}` to format.");
		}
		$this->_format = $this->_formatMap[$mimeType];

		$this->_object = imageCreateFromString(stream_get_contents($handle));

		if (!$this->_isResource($this->_object)) {
			throw new Exception("Was not able to create image from handle.");
		}

		if (imageIsTrueColor($this->_object)) {
			imageAlphaBlending($this->_object, false);
			imageSaveAlpha($this->_object, true);
		}
	}

	public function __destruct() {
		if ($this->_isResource($this->_object)) {
			imageDestroy($this->_object);
		}
	}

	public function store($handle) {
		$args = array($this->_object);

		switch ($this->_format) {
			case 'jpeg':
				if (isset($this->_compression)) {
					if (count($args) == 1) {
						$args[] = null;
					}
					$args[] = $this->_compression;
				}
				break;
			case 'png':
				if (isset($this->_compression)) {
					if (count($args) == 1) {
						$args[] = null;
					}
					$args[] = $this->_compression;

					if (isset($this->_pngFilter)) {
						$args[] = $this->_pngFilter;
					}
				}
				break;
		}

		ob_start();
		call_user_func_array('image' . $this->_format, $args);
		$blob = ob_get_clean();

		rewind($handle);
		return fwrite($handle, $blob);
	}

	public function convert($mimeType) {
		if (Mime_Type::guessName($mimeType) != 'image') {
			return true;
		}
		if (isset($this->_formatMap[$mimeType])) {
			return $this->_format = $this->_formatMap[$mimeType];
		}
		return false;
	}

	public function passthru($key, $value) {
		throw new Exception("The adapter has no passthru support.");
	}

	public function compress($value) {
		switch ($this->_format) {
			case 'jpeg':
				$this->_compression = (integer) (100 - ($value * 10));
				break;
			case 'png':
				if (version_compare(PHP_VERSION, '5.1.2', '>=')) {
					$this->_compression = (integer) $value;
				}
				if (version_compare(PHP_VERSION, '5.1.3', '>=')) {
					$filter = ($value * 10) % 10;
					$map = array(
						0 => PNG_FILTER_NONE,
						1 => PNG_FILTER_SUB,
						2 => PNG_FILTER_UP,
						3 => PNG_FILTER_AVG,
						4 => PNG_FILTER_PAETH,
					);

					if (array_key_exists($filter, $map)) {
						$this->_pngFilter = $map[$filter];
					} elseif ($filter == 5) {
						if (intval($value) <= 5 && imageIsTrueColor($this->_object)) {
							$this->_pngFilter = PNG_ALL_FILTERS;
						} else {
							$this->_pngFilter = PNG_NO_FILTER;
						}
					} else {
						$this->_pngFilter = PNG_ALL_FILTERS;
					}
				}
				break;
		}
		return true;
	}

	public function profile($type, $data = null) {
		throw new Exception("The adapter doesn't support the `profile` action.");
	}

	public function strip($type) {
		throw new Exception("The adapter doesn't support the `strip` action.");
	}

	public function depth($value) {
		throw new Exception("The adapter doesn't support the `depth` action.");
	}

	public function interlace($value) {
		if (in_array($this->_format, array('jpeg', 'png', 'gif'))) {
			imageInterlace($this->_object, $value ? 1 : 0);
			return true;
		}
		return false;
	}

	public function crop($left, $top, $width, $height) {
		$left   = (integer) $left;
		$top    = (integer) $top;
		$width  = (integer) $width;
		$height = (integer) $height;

		$image = imageCreateTrueColor($width, $height);
		$this->_adjustTransparency($this->_object, $image);

		if ($this->_isTransparent($this->_object)) {
			imageCopyResized(
				$image,
				$this->_object,
				0, 0,
				$left, $top,
				$width, $height,
				$width, $height
			);
		} else {
			imageCopyResampled(
				$image,
				$this->_object,
				0, 0,
				$left, $top,
				$width, $height,
				$width, $height
			);
		}
		if ($this->_isResource($image)) {
			$this->_object = $image;
			return true;
		}
		return false;
	}

	public function resize($width, $height) {
		$width  = (integer) $width;
		$height = (integer) $height;

		$image = imageCreateTrueColor($width, $height);
		$this->_adjustTransparency($this->_object, $image);

		if ($this->_isTransparent($this->_object)) {
			imageCopyResized(
				$image,
				$this->_object,
				0, 0,
				0, 0,
				$width, $height,
				$this->width(), $this->height()
			);
		} else {
			imageCopyResampled(
				$image,
				$this->_object,
				0, 0,
				0, 0,
				$width, $height,
				$this->width(), $this->height()
			);
		}
		if ($this->_isResource($image)) {
			$this->_object = $image;
			return true;
		}
		return false;
	}

	public function cropAndResize($cropLeft, $cropTop, $cropWidth, $cropHeight, $resizeWidth, $resizeHeight) {
		$cropLeft     = (integer) $cropLeft;
		$cropTop      = (integer) $cropTop;
		$cropWidth    = (integer) $cropWidth;
		$cropHeight   = (integer) $cropHeight;
		$resizeWidth  = (integer) $resizeWidth;
		$resizeHeight = (integer) $resizeHeight;

		$image = imageCreateTrueColor($resizeWidth, $resizeHeight);
		$this->_adjustTransparency($this->_object, $image);

		if ($this->_isTransparent($this->_object)) {
			imageCopyResized(
				$image,
				$this->_object,
				0, 0,
				$cropLeft, $cropTop,
				$resizeWidth, $resizeHeight,
				$cropWidth, $cropHeight
			);
		} else {
			imageCopyResampled(
				$image,
				$this->_object,
				0, 0,
				$cropLeft, $cropTop,
				$resizeWidth, $resizeHeight,
				$cropWidth, $cropHeight
			);
		}
		if ($this->_isResource($image)) {
			$this->_object = $image;
			return true;
		}
		return false;
	}

	public function width() {
		return imageSX($this->_object);
	}

	public function height() {
		return imageSY($this->_object);
	}

	protected function _isResource($image) {
		return is_resource($image) && get_resource_type($image) == 'gd';
	}

	protected function _isTransparent($image) {
		return imageColorTransparent($image) >= 0;
	}

	protected function _adjustTransparency(&$source, &$target) {
		if ($this->_isTransparent($source)) {
			$rgba  = imageColorsForIndex($source, imageColorTransparent($source));
			$color = imageColorAllocate($target, $rgba['red'], $rgba['green'], $rgba['blue']);
			imageColorTransparent($target, $color);
			imageFill($target, 0, 0, $color);
		} else {
			if ($this->_format == 'png') {
				imageAlphaBlending($target, false);
				imageSaveAlpha($target, true);
			} elseif ($this->_format != 'gif') {
				$white = imageColorAllocate($target, 255, 255, 255);
				imageFill($target, 0, 0 , $white);
			}
		}
	}
}

?>