<?php
/**
 * Audio Medium File
 * 
 * Copyright (c) $CopyrightYear$ David Persson
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE
 * 
 * PHP version $PHPVersion$
 * CakePHP version $CakePHPVersion$
 * 
 * @category   media handling
 * @package    attm
 * @subpackage attm.plugins.media.libs.medium
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  $CopyrightYear$ David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version    SVN: $Id$
 * @version    Release: $Version$
 * @link       http://cakeforge.org/projects/attm The attm Project
 * @since      media plugin 0.50
 * 
 * @modifiedby   $LastChangedBy$
 * @lastmodified $Date$
 */
App::import('Vendor', 'Media.Medium');
/**
 * Audio Medium Class
 * 
 * @category   media handling
 * @package    attm
 * @subpackage attm.plugins.media.libs.medium
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  $CopyrightYear$ David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://cakeforge.org/projects/attm The attm Project
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