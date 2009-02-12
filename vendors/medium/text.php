<?php
/**
 * Text Medium File
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
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  2007-2009 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor', 'Media.Medium');
/**
 * Text Medium Class
 *
 * @package    media
 * @subpackage media.libs.medium
 */
class TextMedium extends Medium {
	/**
	 * Enter description here...
	 *
	 * @var unknown_type
	 */
	public $adapters = array('PearText', 'BasicText');

	/**
	 * Number of characters
	 *
	 * @return int
	 */
	public function characters() {
		return $this->Adapters->dispatchMethod($this, 'characters');
	}

	/**
	 * Flesch Score
	 *
	 * @return float
	 */
	public function fleschScore() {
		return round($this->Adapters->dispatchMethod($this, 'fleschScore'), 2);
	}

	/**
	 * Lexical Density in percent
	 *
 	 * 40- 50 low (easy to read)
	 * 60- 70 high (hard to read)
	 *
	 * @link http://www.usingenglish.com/glossary/lexical-density-test.html
	 * @return int
	 */
	public function lexicalDensity() {
		return $this->Adapters->dispatchMethod($this, 'lexicalDensity');
	}

	/**
	 * Number of sentences
	 *
	 * @return int
	 */
	public function sentences() {
		return $this->Adapters->dispatchMethod($this, 'sentences');
	}

	/**
	 * Number of syllables
	 *
	 * @return int
	 */
	public function syllables()	{
		return $this->Adapters->dispatchMethod($this, 'syllables');
	}

	/**
	 * Truncate to given amount of characters
	 *
	 * @param int $characters
	 * @return string
	 */
	public function truncate($characters) {
		return $this->Adapters->dispatchMethod($this, 'truncate', array($characters));
	}

	/**
	 * Number of words
	 *
	 * @param bool $unique
	 * @return int
	 */
	public function words($unique = false) {
		return $this->Adapters->dispatchMethod($this, 'words', array($unique));
	}
}
?>