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

require_once 'Media/Process/Generic.php';

/**
 * The `Media_Process_Image` class provides methods for manipulating images through
 * resizing, cropping and other methods. It abstracts _most common_ image manipulations.
 * Images can only scaled down but never scaled up.
 */
class Media_Process_Image extends Media_Process_Generic {

	/**
	 * Alias for fitInside.
	 *
	 * @param integer $width
	 * @param integer $height
	 * @return boolean
	 */
	public function fit($width, $height) {
		return $this->fitInside($width, $height);
	}

	/**
	 * Resizes media proportionally keeping both sides within given dimensions.
	 *
	 * @param integer $width
	 * @param integer $height
	 * @return boolean
	 */
	public function fitInside($width, $height) {
		$rx = $this->_adapter->width() / $width;
		$ry = $this->_adapter->height() / $height;

		$r = $rx > $ry ? $rx : $ry;

		$width = $this->_adapter->width() / $r;
		$height = $this->_adapter->height() / $r;

		list($width, $height) = $this->_normalizeDimensions($width, $height, 'maximum');
		return $this->_adapter->resize($width, $height);
	}

	/**
	 * Resizes media proportionally keeping _smaller_ side within corresponding dimensions.
	 *
	 * @param integer $width
	 * @param integer $height
	 * @return boolean
	 */
	public function fitOutside($width, $height) {
		$rx = $this->_adapter->width() / $width;
		$ry = $this->_adapter->height() / $height;

		$r = $rx < $ry ? $rx : $ry;

		$width = $this->_adapter->width() / $r;
		$height = $this->_adapter->height() / $r;

		list($width, $height) = $this->_normalizeDimensions($width, $height, 'ratio');
		return $this->_adapter->resize($width, $height);
	}

	/**
	 * Crops media to provided dimensions.
	 *
	 * @param integer $width
	 * @param integer $height
	 * @return boolean
	 */
	public function crop($width, $height) {
		list($width, $height) = $this->_normalizeDimensions($width, $height, 'maximum');
		list($left, $top) = $this->_boxify($width, $height);

		return $this->_adapter->crop($left, $top, $width, $height);
	}

	/**
	 * Alias for zoomFit.
	 *
	 * @param integer $width
	 * @param integer $height
	 * @return boolean
	 */
	public function zoom($width, $height) {
		return $this->zoomFit($width, $height);
	}

	/**
	 * Enlarges media proportionally by factor 2.
	 *
	 * @param integer $width
	 * @param integer $height
	 * @return boolean
	 */
	public function zoomFit($width, $height) {
		$factor = 2;

		$width = $width * $factor;
		$height = $height * $factor;

		return $this->fitOutside($width, $height);
	}

	/**
	 * First crops an area (given by dimensions and enlarged by factor 2)
	 * out of the center of the media, then resizes that cropped
	 * area to given dimensions
	 *
	 * @param integer $width
	 * @param integer $height
	 * @param string $gravity Currently supported values are "center", "topleft",
	 *                      "topright", "bottomleft", "bottomright", defaults to "center"
	 * @return boolean
	 */
	public function zoomCrop($width, $height, $gravity = 'center') {
		$factor = 2;

		list($zoomWidth, $zoomHeight) = $this->_normalizeDimensions($width * $factor, $height * $factor, 'maximum');
		list($zoomLeft, $zoomTop) = $this->_boxify($zoomWidth, $zoomHeight, $gravity);
		list($width, $height) = array($zoomWidth / $factor, $zoomHeight / $factor);

		return $this->_adapter->cropAndResize(
			$zoomLeft, $zoomTop, $zoomWidth, $zoomHeight,
			$width, $height
		);
	}

	/**
	 * First resizes media so that it fills out the given dimensions,
	 * then cuts off overlapping parts.
	 *
	 * @param integer $width
	 * @param integer $height
	 * @param string $gravity Currently supported values are "center", "topleft",
	 *                      "topright", "bottomleft", "bottomright", defaults to "center"
	 * @return boolean
	 */
	public function fitCrop($width, $height, $gravity = 'center') {
		$rx = $this->_adapter->width() / $width;
		$ry = $this->_adapter->height() / $height;

		$r = $rx < $ry ? $rx : $ry;

		$resizeWidth = $this->_adapter->width() / $r;
		$resizeHeight = $this->_adapter->height() / $r;

		$this->_adapter->resize($resizeWidth, $resizeHeight);
		list($left, $top) = $this->_boxify($width, $height, $gravity);

		return $this->_adapter->crop($left, $top, $width, $height);
	}

	/**
	 * Selects level of compression (and in for some format the filters) than
	 * compresses the media according to provided value.
	 *
	 * For png images the decimal place denotes the type of filter to be used this means
	 * .0 is none, .1 is "sub", .2 is "up", .3 is "average", .4 is "paeth" and .5 is
	 * "adaptive". The number itself controls the zlib compression level from 1 (fastest)
	 * to 9 (best compression). Compression for png images is lossless.
	 *
	 * For jpeg images the provided value is multiplied with 10 and substracted from 100
	 * (the best jpeg quality). This means i.e. a given value of 1.5 results in a jpeg
	 * quality of 85. Compression for jpeg images is lossy.
	 *
	 * The tiff format with LZW compression (which is used by default) does not allow for
	 * controlling the compression level. Therefore the given value is simply ignored.
	 * Compression for tiff images is lossless.
	 *
	 * @param float $value Zero for no compression at all or a value between 0 and 9.9999999
	 * 	(highest compression); defaults to 1.5
	 * @return boolean
	 */
	public function compress($value = 1.5) {
		if ($value < 0 || $value >= 10) {
			throw new InvalidArgumentException("Compression value is not within range 0..10.");
		}
		return $this->_adapter->compress(floatval($value));
	}

	/**
	 * Strips unwanted data from an image. This operation is therefore always lossful.
	 * Be careful when removing color profiles (icc) and copyright information (iptc/xmp).
	 *
	 * @param string $type One of either `'8bim'`, `'icc'`, `'iptc'`, `'xmp'`, `'app1'`, `'app12'`, `'exif'`.
	 *                     Repet argument to strip multiple types.
	 * @return boolean
	 */
	public function strip($type) {
		foreach (func_get_args() as $type) {
			if (!$this->_adapter->strip($type)) {
				return false;
			}
		}
		return true;;
	}

	/**
	 * Embeds the provided ICC profile into the image. Allows for forcing a certain profile and
	 * transitioning from one color space to another.
	 *
	 * In case the image already has a color profile  embedded (which is highly recommended) it
	 * is used to convert to the target. In absence of an  embedded profile it is assumed that
	 * the image has the `sRGB IEC61966-2.1` (with blackpoint scaling) profile.
	 *
	 * Please note that most adapters will try to recover from a embedded corrupt profile
	 * by deleting it. Color profiles specified in the EXIF data of the image are not honored.
	 * This method works with ICC profiles only.
	 *
	 * @param string $file Absolute path to a profile file (most often with a `'icc'` extension).
	 * @return boolean
	 * @link http://www.cambridgeincolour.com/tutorials/color-space-conversion.htm
	 */
	public function colorProfile($file) {
		if (!is_file($file)) {
			return false;
		}

		$target  = file_get_contents($file);
		$current = $this->_adapter->profile('icc');

		if (!$current) {
			$file = dirname(dirname(dirname(dirname(__FILE__)))) . '/data/sRGB_IEC61966-2-1_black_scaled.icc';
			$current = file_get_contents($file);

			if (!$this->_adapter->profile('icc', $current)) {
				return false;
			}
		}
		if ($current == $target) {
			return true;
		}
		return $this->_adapter->profile('icc', $target);
	}

	/**
	 * Changes the color depths (of the channels).
	 *
	 * @param integer $value The number of bits in a color sample within a pixel. Usually `8`.
	 *                       This is _not_ the total number of bits per pixel but the bits per
	 *                       channel.
	 * @return boolean
	 */
	public function colorDepth($value) {
		return $this->_adapter->depth($value);
	}

	/**
	 * Enables or disables interlacing. Formats like PNG, GIF and JPEG support interlacing.
	 *
	 * @param boolean $value `true` to enable interlacing (progressive rendering), or
	 *                       `false` to disable it.
	 * @return boolean
	 */
	public function interlace($value) {
		return $this->_adapter->interlace($value);
	}

	/**
	 * Normalizes dimensions ensuring they don't exceed actual dimensions of the image. This forces
	 * all operations on the image to never scale up.
	 *
	 * @param integer $width
	 * @param integer $height
	 * @param string $recalculateBy Recalculate missing values or ones exceeding maximums
	 *                              using either `'ratio'` or `'maximum'`.
	 * @return array An array containing width and height.
	 */
	protected function _normalizeDimensions($width, $height, $recalculateBy = 'ratio') {
		if ($width > $this->_adapter->width()) {
			$width = null;
		}
		if ($height > $this->_adapter->height()) {
			$height = null;
		}

		if (is_null($width) && is_null($height)) {
			$width = $this->_adapter->width();
			$height = $this->_adapter->height();
		}

		if ($recalculateBy == 'maximum') {
			if (empty($width)) {
				$width = $this->_adapter->width();
			}
			if (empty($height)) {
				$height = $this->_adapter->height();
			}
		} else {
			if (empty($width)) {
				$ratio = $height / $this->_adapter->height();
				$width = $ratio * $this->_adapter->width();
			}
			if (empty($height)) {
				$ratio = $width / $this->_adapter->width();
				$height = $ratio * $this->_adapter->height();
			}
		}
		return array($width, $height);
	}

	/**
	 * Calculates a box' coordinates.
	 *
	 * @param integer $width
	 * @param integer $height
	 * @param string $gravity Currently supported values are "center", "topleft",
	 *                      "topright", "bottomleft", "bottomright", defaults to "center"
	 * @return array An array containing left and top coordinates
	 */
	protected function _boxify($width, $height, $gravity = 'center') {
		switch ($gravity) {
			case 'center':
				$left = max(0, ($this->_adapter->width() - $width) / 2);
				$top = max(0, ($this->_adapter->height() - $height) / 2);
				break;
			case 'topleft':
				$left = $top = 0;
				break;
			case 'topright':
				$left = max(0, $this->_adapter->width() - $width);
				$top = 0;
				break;
			case 'bottomleft':
				$left = 0;
				$top = max(0, $this->_adapter->height() - $height);
				break;
			case 'bottomright':
				$left = max(0, $this->_adapter->width() - $width);
				$top = max(0, $this->_adapter->height() - $height);
				break;
			default:
				throw new InvalidArgumentException("Unsupported gravity `{$gravity}`.");
		}
		return array($left, $top);
	}
}

?>