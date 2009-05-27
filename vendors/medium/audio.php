<?php
/**
 * Audio Medium File
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
 * @copyright  2007-2009 David Persson <davidpersson@gmx.de>
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
	var $adapters = array('Getid3Audio', 'FfmpegAudio', 'PearMp3', 'PearOggAudio');
/**
 * Artist stored in medium metadata
 *
 * @return mixed String if metadata info exists, else null
 */
	function artist() {
		return trim($this->Adapters->dispatchMethod($this, 'artist'));
	}
/**
 * Title stored in medium metadata
 *
 * @return mixed String if metadata info exists, else null
 */
	function title() {
		return trim($this->Adapters->dispatchMethod($this, 'title'));
	}
/**
 * Album name stored in medium metadata
 *
 * @return mixed String if metadata info exists, else null
 */
	function album() {
		return trim($this->Adapters->dispatchMethod($this, 'album'));
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
 * Track number stored in medium metadata
 *
 * @return mixed Integer if metadata info exists, else null
 */
	function track() {
		return (integer)$this->Adapters->dispatchMethod($this, 'track');
	}
/**
 * Duration in seconds
 *
 * @return integer
 */
	function duration() {
		return (integer)$this->Adapters->dispatchMethod($this, 'duration');
	}
/**
 * Current sampling rate of medium
 *
 * @url http://en.wikipedia.org/wiki/Sampling_rate
 * @return integer
 */
	function samplingRate() {
		return (integer)$this->Adapters->dispatchMethod($this, 'samplingRate');
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
 * taking bitrate into account
 *
 * @return integer A number indicating quality between 1 (worst) and 5 (best)
 */
	function quality() {
		if (!$bitrate = $this->bitrate()) {
			return null;
		}

		/* Normalized between 1 and 5 where min = 32000 and max = 320000 or 500000 */
		$bitrateMax = ($this->mimeType == 'audio/mpeg') ? 320000 : 500000;
		$bitrateMin = 32000;
		$qualityMax = 5;
		$qualityMin = 1;

		if ($bitrate >= $bitrateMax) {
			$quality = $qualityMax;
		} elseif ($bitrate <= $bitrateMin) {
			$quality = $qualityMin;
		} else {
			$quality =
				(($bitrate - $bitrateMin) / ($bitrateMax - $bitrateMin))
				* ($qualityMax - $qualityMin)
				+ $qualityMin;
		}
		return (integer)round($quality);
	}
}
?>
