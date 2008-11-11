<?php
/**
 * Video Medium File
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
	public $adapters = array('FfMpegVideo', 'PearOggVideo');

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
	 * Quality
	 *
	 * @return int
	 */
	public function quality() {
		return $this->Adapter->quality();
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