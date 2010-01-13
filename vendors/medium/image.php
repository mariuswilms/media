<?php
/**
 * Image Medium File
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
 * @subpackage media.libs.medium
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor', 'Media.Medium');
/**
 * Image Medium Class
 *
 * @package    media
 * @subpackage media.libs.medium
 */
class ImageMedium extends Medium {
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
 * Resizes medium proportionally
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
 * Resizes medium proportionally
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
 * Crops medium to provided dimensions
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
 * Enlarges medium proportionally by factor 2
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
 * out of the center of the medium, then resizes that cropped
 * area to given dimensions
 *
 * @param integer $width
 * @param integer $height
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
 * First resizes medium so that it fills out the given dimensions,
 * then cuts off overlapping parts
 *
 * @param integer $width
 * @param integer $height
 * @param string $align
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
 * Current width of medium
 *
 * @return integer
 */
	function width()	{
		return $this->Adapters->dispatchMethod($this, 'width', null, array(
			'normalize' => true
		));
	}
/**
 * Current height of medium
 *
 * @return integer
 */
	function height() {
		return $this->Adapters->dispatchMethod($this, 'height', null, array(
			'normalize' => true
		));
	}
/**
 * Selects compression type and filters than compresses the medium
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
 * Determines the quality of the medium by
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
 * Determines a (known) ratio of medium
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
 * Determines megapixels of medium
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
 * 	with medium's corresponding size
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
 * @param string $align Currently "center" is supported only
 * @return array An array containing left and top coordinates
 */
	function _boxify($width, $height, $gravity = 'center') {
		if ($width > $this->width()) {
			$left = 0;
		} else {
			$left = ($this->width() - $width) / 2;
		}

		if ($height > $this->width()) {
			$top = 0;
		} else {
			$top = ($this->height() - $height) / 2;
		}
		return array($left, $top);
	}
}
?>
