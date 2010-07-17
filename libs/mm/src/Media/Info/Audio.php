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
 * `Media_Info_Audio` handles all audio files.
 * Most methods are simply inherited from the generic media type wile some overlap with
 * those defined in `Media_Info_Video`.
 *
 * @see Media_Info_Video
 */
class Media_Info_Audio extends Media_Info_Generic {

	/**
	 * Determines the quality of the media by
	 * taking bit rate into account.
	 *
	 * @return integer A number indicating the quality between 1 (worst) and 5 (best).
	 */
	public function quality() {
		if (!$bitRate = $this->get('bitRate')) {
			return;
		}
		if (!$bitRateMax = $this->get('bitRateMax')) { // i.e. mpeg has max = 500000
			$bitRateMax = 320000;
		}
		$bitRateMin = 32000;

		$qualityMax = 5;
		$qualityMin = 1;

		/* Normalize between 1 and 5 where min = 32000 and max = see above */
		if ($bitRate >= $bitRateMax) {
			$quality = $qualityMax;
		} elseif ($bitRate <= $bitRateMin) {
			$quality = $qualityMin;
		} else {
			$quality =
				(($bitRate - $bitRateMin) / ($bitRateMax - $bitRateMin))
				* ($qualityMax - $qualityMin)
				+ $qualityMin;
		}
		return (integer) round($quality);
	}
}

?>