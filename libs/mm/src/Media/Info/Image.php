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

require_once 'Media/Info/Generic.php';

/**
 * `Media_Info_Image`
 */
class Media_Info_Image extends Media_Info_Generic {

	/**
	 * Determines the ratio.
	 *
	 * @return float
	 */
	public function ratio() {
		return $this->get('width') / $this->get('height');
	}

	/**
	 * Determines the known ratio.
	 *
	 * @return string
	 */
	public function knownRatio() {
		return $this->_knownRatio();
	}

	/**
	 * Determines megapixels of media.
	 *
	 * @return integer
	 */
	public function megapixel() {
		return (integer) ($this->get('width') * $this->get('height') / 1000000);
	}

	/**
	 * Determines the quality of the media by
	 * taking amount of megapixels into account.
	 *
	 * @return integer A number indicating quality between 1 (worst) and 5 (best),
	 */
	public function quality() {
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
		return (integer) round($quality);
	}
}

?>