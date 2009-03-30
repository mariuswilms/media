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
	 * Compatible adapters
	 *
	 * @var array
	 */
	$adapters = array('GetId3Video', 'FfMpegVideo', 'PearOggVideo');

	/**
	 * Title stored in medium metadata
	 *
	 * @return mixed String if metadata info exists, else null
	 */
	function title() {
		return trim($this->Adapters->dispatchMethod($this, 'title'));
	}

	/**
	 * Year stored in medium metadata
	 *
	 * @return mixed Integer if metadata info exists, else null
	 */
	function year() {
		return (integer)$this->Adapters->dispatchMethod($this, 'year');
	}

	/**
	 * Current height of medium
	 *
	 * @return integer
	 */
	function height() {
		return (integer)$this->Adapters->dispatchMethod($this, 'height');
	}

	/**
	 * Current width of medium
	 *
	 * @return integer
	 */
	function width() {
		return (integer)$this->Adapters->dispatchMethod($this, 'width');
	}

	/**
	 * Current bitrate of medium
	 *
	 * @url http://en.wikipedia.org/wiki/Bit_rate
	 * @return integer
	 */
	function bitrate() {
		return (integer)$this->Adapters->dispatchMethod($this, 'bitrate');
	}

	/**
	 * Determines the quality of the medium by
	 * taking definition and bitrate into account
	 *
	 * @return integer A number indicating quality between 1 (worst) and 5 (best)
	 */
	function quality() {
		$definition = $this->width() * $this->height();
		$bitrate = $this->bitrate();

		if (empty($definition) || empty($bitrate)) {
			return null;
		}

		/* Normalized between 1 and 5 where min = 19200 and max = 414720 */
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
			$quality = ($quality + $qualityMin * $bitrateCoef) / ($bitrateCoef + 1);
		} elseif ($bitrate <= 564000) {
			$quality = ($quality + $qualityMax * 2 / 5 * $bitrateCoef) / ($bitrateCoef + 1);
		} elseif ($bitrate <= 1152000) {
			$quality = ($quality + $qualityMax * 3 / 5 * $bitrateCoef) / ($bitrateCoef + 1);
		} elseif ($bitrate <= 2240000) {
			$quality = ($quality + $qualityMax * 4 / 5 * $bitrateCoef) / ($bitrateCoef + 1);
		} else {
			$quality = ($quality + $qualityMax * $bitrateCoef) / ($bitrateCoef + 1);
		}

		return (integer)round($quality);
	}

	/**
	 * Determines a (known) ratio of medium
	 *
	 * @return mixed String if $known is true or float if false
	 */
	function ratio($known = true) {
		$width = $this->width();
		$height = $this->height();

		if (empty($width) || empty($height)) {
			return null;
		}

		if (!$known) {
			return $width / $height;
		}
		return $this->_knownRatio($width, $height);
	}

}
?>
