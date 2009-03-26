<?php
/**
 * Video Medium File
 *
 * Copyright (c) 2007-2009 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.libs.medium
 * @copyright  2007-2009 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor', 'Media.Medium');
/**
 * Video Medium Class
 *
 * @package    media
 * @subpackage media.libs.medium
 */
class VideoMedium extends Medium {
	/**
	 * Enter description here...
	 *
	 * @var unknown_type
	 */
	public $adapters = array('FfMpegVideo', 'GetId3Video' , 'PearOggVideo');

	/**
	 * Duration in seconds
	 *
	 * @return int
	 */
	public function duration() {
		return $this->Adapters->dispatchMethod($this, 'duration');
	}

	/**
	 * Height
	 *
	 * @return int
	 */
	public function height() {
		return $this->Adapters->dispatchMethod($this, 'height');
	}

	/**
	 * Width
	 *
	 * @return int
	 */
	public function width() {
		return $this->Adapters->dispatchMethod($this, 'width');
	}

	/**
	 * Bitrate
	 *
	 * @return int
	 */
	public function bitrate() {
		return $this->Adapters->dispatchMethod($this, 'bitrate');
	}
	/**
	 * Quality
	 *
	 * @return integer A number indicating quality between 1 (worst) and 5 (best)
	 */
	public function quality() {
		$definition = $this->width() * $this->height();
		$bitrate = $this->bitrate();

		if (empty($definition) || empty($bitrate)) {
			return null;
		}

		/* Normalized between 1 and 5 where min = 0.5 and max = 10 */
		$definitionMax = 720 * 576;
		$definitionMin = 160 * 120;
		$qualityMax = 5;
		$qualityMin = 1;

		if ($definition >= $definitionMax) {
			$quality = $qualityMax;
		} elseif ($definition <= $definitionMin) {
			$quality = $qualityMin;
		} else {
			$quality =
				(($definition - $definitionMin) / ($definitionMax - $definitionMin))
				* ($qualityMax - $qualityMin)
				+ $qualityMin;
		}

		$bitrateCoef = 3;

		if ($bitrate <= 128000) {
			$quality = ($quality + $bitrateCoef) / ($bitrateCoef + 1);
		} elseif ($bitrate <= 564000) {
			$quality = ($quality + 2 * $bitrateCoef) / ($bitrateCoef + 1);
		} elseif ($bitrate <= 1152000) {
			$quality = ($quality + 3 * $bitrateCoef) / ($bitrateCoef + 1);
		} elseif ($bitrate <= 2240000) {
			$quality = ($quality + 4 * $bitrateCoef) / ($bitrateCoef + 1);
		} else {
			$quality = ($quality + 5 * $bitrateCoef) / ($bitrateCoef + 1);
		}

		return intval(round($quality));
	}

	/**
	 * fixed ratio
	 *
	 * @return string
	 */
	function ratio($known = true) {
		if (!$known) {
			return $this->width() / $this->height();
		}
		return $this->_knownRatio($this->width(), $this->height());
	}
}
?>
