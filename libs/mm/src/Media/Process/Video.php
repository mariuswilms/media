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
 * `Media_Process_Video` allows for manipulating video files.
 * Most methods are simply inherited from the generic media type.
 */
class Media_Process_Video extends Media_Process_Generic {

	/**
	 * Alias for fitInside
	 *
	 * @fixme This method is a 1:1 copy from Media_Process_Image.
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
	 * @fixme This method is a 1:1 copy from Media_Process_Image.
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
	 * Normalizes dimensions ensuring they don't exceed actual dimensions of the image. This forces
	 * all operations on the image to never scale up.
	 *
	 * @fixme This method is a 1:1 copy from Media_Process_Image.
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
}

?>