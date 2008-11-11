<?php
/**
 * Audio Medium File
 *
 * Copyright (c) 2007-2008 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.libs.medium
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  2007-2008 David Persson <davidpersson@qeweurope.org>
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
	 * Enter description here...
	 *
	 * @var unknown_type
	 */
	public $adapters = array('FfMpegAudio', 'PearMp3', 'PearOggAudio');

	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	public function artist() {
		return $this->Adapters->dispatchMethod($this, 'artist');
	}

	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	public function title() {
		return $this->Adapters->dispatchMethod($this, 'name');
	}

	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	public function album() {
		return $this->Adapters->dispatchMethod($this, 'album');
	}

	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	public function year() {
		return $this->Adapters->dispatchMethod($this, 'year');
	}

	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	public function duration() {
		return $this->Adapters->dispatchMethod($this, 'duration');
	}

	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	public function track() {
		return $this->Adapters->dispatchMethod($this, 'track');
	}

	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	public function samplingRate() {
		return (int) $this->Adapters->dispatchMethod($this, 'samplingRate');
	}

	/**
	 * Enter description here...
	 *
	 * @url http://en.wikipedia.org/wiki/Sampling_rate
	 * @return unknown
	 */
	public function quality() {
		$rate = $this->samplingRate();

		/* Normalized between 1 and 5 where min = 8000 and max = 192.000 */
		if($rate > 192000) {
			$quality = 5;
		} elseif($rate < 8000) {
			$quality = 0;
		} else {
			$quality = round((($rate - 8000) / (192000 - 8000)) * (5 * 1) + 1,0);
		}

		return $quality;
	}
}
?>