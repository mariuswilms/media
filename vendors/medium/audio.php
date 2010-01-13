<?php
/**
 * Audio Medium File
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
 * Audio Medium Class
 *
 * @package    media
 * @subpackage media.libs.medium
 */
class AudioMedium extends Medium {
/**
 * Compatible adapters
 *
 * @var array
 */
	var $adapters = array('Getid3Audio', 'FfmpegAudio', 'PearMp3');
/**
 * Artist stored in medium metadata
 *
 * @return mixed String if metadata info exists, else null
 */
	function artist() {
		return $this->Adapters->dispatchMethod($this, 'artist', null, array(
			'normalize' => true
		));
	}
/**
 * Title stored in medium metadata
 *
 * @return mixed String if metadata info exists, else null
 */
	function title() {
		return $this->Adapters->dispatchMethod($this, 'title', null, array(
			'normalize' => true
		));
	}
/**
 * Album name stored in medium metadata
 *
 * @return mixed String if metadata info exists, else null
 */
	function album() {
		return $this->Adapters->dispatchMethod($this, 'album', null, array(
			'normalize' => true
		));
	}
/**
 * Year stored in medium metadata
 *
 * @return mixed Integer if metadata info exists, else null
 */
	function year() {
		return $this->Adapters->dispatchMethod($this, 'year', null, array(
			'normalize' => true
		));
	}
/**
 * Track number stored in medium metadata
 *
 * @return mixed Integer if metadata info exists, else null
 */
	function track() {
		return $this->Adapters->dispatchMethod($this, 'track', null, array(
			'normalize' => true
		));
	}
/**
 * Duration in seconds
 *
 * @return integer
 */
	function duration() {
		return $this->Adapters->dispatchMethod($this, 'duration', null, array(
			'normalize' => true
		));
	}
/**
 * Current sampling rate of medium
 *
 * @url http://en.wikipedia.org/wiki/Sampling_rate
 * @return integer
 */
	function samplingRate() {
		return $this->Adapters->dispatchMethod($this, 'samplingRate', null, array(
			'normalize' => true
		));
	}
/**
 * Current bit rate of medium
 *
 * @url http://en.wikipedia.org/wiki/Bit_rate
 * @return integer
 */
	function bitRate() {
		return $this->Adapters->dispatchMethod($this, 'bitRate', null, array(
			'normalize' => true
		));
	}
/**
 * Determines the quality of the medium by
 * taking bit rate into account
 *
 * @return integer A number indicating quality between 1 (worst) and 5 (best)
 */
	function quality() {
		if (!$bitRate = $this->bitRate()) {
			return;
		}

		/* Normalized between 1 and 5 where min = 32000 and max = 320000 or 500000 */
		$bitRateMax = ($this->mimeType == 'audio/mpeg') ? 320000 : 500000;
		$bitRateMin = 32000;
		$qualityMax = 5;
		$qualityMin = 1;

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
		return (integer)round($quality);
	}
}
?>
