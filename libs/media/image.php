<?php
/**
 * Image Media File
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
App::import('Lib', 'Media.Media');

/**
 * Image Media Class
 *
 * @package    media
 * @subpackage media.libs.media
 */
class ImageMedia extends Media {

/**
 * Compatible adapters
 *
 * @var array
 */
	var $adapters = array('BasicImage', 'Imagick', 'Gd', 'ImagickShell');

/**
 * Alias for fitInside
 *
 * @param integer $width
 * @param integer $height
 * @return boolean
 */
	function fit($width, $height) {
		return $this->fitInside($width, $height);
	}

/**
 * Resizes media proportionally
 * keeping both sides within given dimensions
 *
 * @param integer $width
 * @param integer $height
 * @return boolean
 */
	function fitInside($width, $height) {
		$rx = $this->width() / $width;
		$ry = $this->height() / $height;

		if ($rx > $ry) {
			$r = $rx;
		} else {
			$r = $ry;
		}

		$width = $this->width() / $r;
		$height = $this->height() / $r;

		$args = $this->_normalizeDimensions($width, $height, 'maximum'); /* maximum ?? */
		return $this->Adapters->dispatchMethod($this, 'resize', $args);
	}

/**
 * Resizes media proportionally
 * keeping smaller side within corresponding dimensions
 *
 * @param integer $width
 * @param integer $height
 * @return boolean
 */
	function fitOutside($width, $height) {
		$rx = $this->width() / $width;
		$ry = $this->height() / $height;

		if ($rx < $ry) {
			$r = $rx;
		} else {
			$r = $ry;
		}

		$width = $this->width() / $r;
		$height = $this->height() / $r;

		$args = $this->_normalizeDimensions($width, $height, 'ratio');
		return $this->Adapters->dispatchMethod($this, 'resize', $args);
	}

/**
 * Crops media to provided dimensions
 *
 * @param integer $width
 * @param integer $height
 * @return boolean
 */
	function crop($width, $height) {
		list($width, $height) = $this->_normalizeDimensions($width, $height, 'maximum');
		list($left, $top) = $this->_boxify($width, $height);
		return $this->Adapters->dispatchMethod($this, 'crop', array($left, $top, $width, $height));
	}

/**
 * Alias for zoomFit
 *
 * @param integer $width
 * @param integer $height
 * @return boolean
 */
	function zoom($width, $height) {
		return $this->zoomFit($width, $height);
	}

/**
 * Enlarges media proportionally by factor 2
 *
 * @param integer $width
 * @param integer $height
 * @return boolean
 */
	function zoomFit($width, $height) {
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
	function zoomCrop($width, $height, $gravity = 'center') {
		$factor = 2;

		list($zoomWidth, $zoomHeight) = $this->_normalizeDimensions($width * $factor, $height * $factor, 'maximum');
		list($zoomLeft, $zoomTop) = $this->_boxify($zoomWidth, $zoomHeight, $gravity);
		list($width, $height) = array($zoomWidth / $factor, $zoomHeight / $factor);

		return $this->Adapters->dispatchMethod(
			$this,
			'cropAndResize',
			array($zoomLeft, $zoomTop, $zoomWidth, $zoomHeight, $width, $height)
		);
	}

/**
 * First resizes media so that it fills out the given dimensions,
 * then cuts off overlapping parts
 *
 * @param integer $width
 * @param integer $height
 * @param string $gravity Currently supported values are "center", "topleft",
 *                      "topright", "bottomleft", "bottomright", defaults to "center"
 * @return boolean
 */
	function fitCrop($width, $height, $gravity = 'center') {
		$rx = $this->width() / $width;
		$ry = $this->height() / $height;

		if ($rx < $ry) {
			$r = $rx;
		} else {
			$r = $ry;
		}

		$resizeWidth = $this->width() / $r;
		$resizeHeight = $this->height() / $r;

		$this->Adapters->dispatchMethod($this, 'resize', array($resizeWidth, $resizeHeight));
		list($left, $top) = $this->_boxify($width, $height, $gravity);
		return $this->Adapters->dispatchMethod($this, 'crop', array($left, $top, $width, $height));
	}

/**
 * Current width of media
 *
 * @return integer
 */
	function width()	{
		return $this->Adapters->dispatchMethod($this, 'width', null, array(
			'normalize' => true
		));
	}

/**
 * Current height of media
 *
 * @return integer
 */
	function height() {
		return $this->Adapters->dispatchMethod($this, 'height', null, array(
			'normalize' => true
		));
	}

/**
 * Selects compression type and filters than compresses the media
 * according to provided value
 *
 * Compressing may result in lossy quality for e.g. jpeg but
 * not for png images. The decimal place denotes the type of filter
 * used and the number as a whole the (rounded) compression value.
 *
 * @param float $value Zero for no compression at all or a value between 0 and 9.9999999
 * 	(highest compression); defaults to 1.5
 * @return boolean
 */
	function compress($value = 1.5) {
		if ($value < 0 || $value >= 10) {
			return false;
		}
		return $this->Adapters->dispatchMethod($this, 'compress', array(floatval($value)));
	}

/**
 * Strips unwanted data from an image. This operation is therefore always lossful.
 * Be careful when removing color profiles (icc) and copyright information (iptc/xmp).
 *
 * @param string $type One of either `'8bim'`, `'icc'`, `'iptc'`, `'xmp'`, `'app1'`, `'app12'`, `'exif'`.
 *                     Repet argument to strip multiple types.
 * @return boolean
 */
	function strip($type) {
		foreach (func_get_args() as $type) {
			if (!$this->Adapters->dispatchMethod($this, 'strip', array($type))) {
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
	function profileColor($file) {
		if (!is_file($file)) {
			return false;
		}

		$target  = file_get_contents($file);
		$current = $this->Adapters->dispatchMethod($this, 'profile', array('icc'));

		if (!$current) {
			$file = App::pluginPath('Media') . 'vendors' . DS . 'sRGB_IEC61966-2-1_black_scaled.icc';
			$current = file_get_contents($file);

			if (!$this->Adapters->dispatchMethod($this, 'profile', array('icc', $current))) {
				return false;
			}
		}
		if ($current != $target) {
			if (!$this->Adapters->dispatchMethod($this, 'profile', array('icc', $target))) {
				return false;
			}
		}
		return true;
	}

/**
 * Determines the quality of the media by
 * taking amount of megapixels into account
 *
 * @return integer A number indicating quality between 1 (worst) and 5 (best)
 */
	function quality() {
		$megapixel = $this->megapixel();

		/* Normalized between 1 and 5 where min = 0.5 and max = 10 */
		$megapixelMax = 10;
		$megapixelMin = 0.5;
		$qualityMax = 5;
		$qualityMin = 1;

		if ($megapixel > $megapixelMax) {
			$quality = $qualityMax;
		} elseif ($megapixel < $megapixelMin) {
			$quality = $qualityMin;
		} else {
			$quality =
				(($megapixel - $megapixelMin) / ($megapixelMax - $megapixelMin))
				* ($qualityMax - $qualityMin)
				+ $qualityMin;
		}
		return (integer)round($quality);
	}

/**
 * Determines a (known) ratio of media
 *
 * @return mixed String if $known is true or float if false
 */
	function ratio($known = true) {
		if (!$known) {
			return $this->width() / $this->height();
		}
		return $this->_knownRatio($this->width(), $this->height());
	}

/**
 * Determines megapixels of media
 *
 * @return integer
 */
	function megapixel() {
		return (integer)($this->width() * $this->height() / 1000000);
	}

/**
 * Normalizes dimensions
 *
 * @param integer $width
 * @param integer $height
 * @param integer $set Either "ratio" or "maximum" Maximum replaces sizes execeeding limits
 * 	with media's corresponding size
 * @return array An array containing width and height
 */
	function _normalizeDimensions($width, $height, $set = 'ratio') {
		if ($width > $this->width()) {
			$width = null;
		}
		if ($height > $this->height()) {
			$height = null;
		}

		if (is_null($width) && is_null($height)) {
			$width = $this->width();
			$height = $this->height();
		}

		if ($set == 'maximum') {
			if (empty($width)) {
				$width = $this->width();
			}
			if (empty($height)) {
				$height = $this->height();
			}
		} else {
			if (empty($width)) {
				$ratio = $height / $this->height();
				$width = $ratio * $this->width();
			}
			if (empty($height)) {
				$ratio = $width / $this->width();
				$height = $ratio * $this->height();
			}
		}
		return array($width, $height);
	}

/**
 * Calculates a box coordinates
 *
 * @param integer $width
 * @param integer $height
 * @param string $gravity Currently supported values are "center", "topleft",
 *                      "topright", "bottomleft", "bottomright", defaults to "center"
 * @return array An array containing left and top coordinates
 */
	function _boxify($width, $height, $gravity = 'center') {
		switch ($gravity) {
			default:
				$message = 'Image::_boxify - Unsupported value given for gravity parameter.';
				trigger_error($message, E_USER_NOTICE);
			case 'center':
				$left = max(0, ($this->width() - $width) / 2);
				$top = max(0, ($this->height() - $height) / 2);
				break;
			case 'topleft':
				$left = $top = 0;
				break;
			case 'topright':
				$left = max(0, $this->width() - $width);
				$top = 0;
				break;
			case 'bottomleft':
				$left = 0;
				$top = max(0, $this->height() - $height);
				break;
			case 'bottomright':
				$left = max(0, $this->width() - $width);
				$top = max(0, $this->height() - $height);
				break;
		}
		return array($left, $top);
	}
}
?>