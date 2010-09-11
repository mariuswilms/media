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
 * `Media_Process_Audio` provides methods to manipulate audio files and streams.
 */
class Media_Process_Audio extends Media_Process_Generic {

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
	public function compress($value = 1.5) {
		if ($value < 0 || $value >= 10) {
			throw new InvalidArgumentException("Compression value is not within the 0..10 range.");
		}
		return $this->_adapter->compress(floatval($value));
	}

	public function channels($value) {
		return $this->_adapter->channels((integer) $value);
	}

	public function sampleRate($value) {
		return $this->_adapter->sampleRate((integer) $value);
	}
}

?>