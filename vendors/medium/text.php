<?php
/**
 * Text Medium File
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
 * Text Medium Class
 *
 * @package    media
 * @subpackage media.libs.medium
 */
class TextMedium extends Medium {
/**
 * Compatible adapters
 *
 * @var array
 */
	var $adapters = array('PearText', 'BasicText');
/**
 * Number of characters
 *
 * @return integer
 */
	function characters() {
		return $this->Adapters->dispatchMethod($this, 'characters', null,  array(
			'normalize' => true
		));
	 }
/**
 * Flesch Score
 *
 * @return float
 */
	function fleschScore() {
		return round($this->Adapters->dispatchMethod($this, 'fleschScore', null, array(
			'normalize' => false
		)), 2);
	}
/**
 * Lexical Density in percent
 *
 * 40- 50 low (easy to read)
 * 60- 70 high (hard to read)
 *
 * @link http://www.usingenglish.com/glossary/lexical-density-test.html
 * @return integer
 */
	function lexicalDensity() {
		return $this->Adapters->dispatchMethod($this, 'lexicalDensity', null, array(
			'normalize' => true
		));
	}
/**
 * Number of sentences
 *
 * @return integer
 */
	function sentences() {
		return $this->Adapters->dispatchMethod($this, 'sentences', null, array(
			'normalize' => true
		));
	}
/**
 * Number of syllables
 *
 * @return integer
 */
	function syllables()	{
		return $this->Adapters->dispatchMethod($this, 'syllables', null, array(
			'normalize' => true
		));
	}
/**
 * Truncate to given amount of characters
 *
 * @param integer $characters
 * @return string
 */
	function truncate($characters) {
		return $this->Adapters->dispatchMethod($this, 'truncate', array($characters));
	}
/**
 * Number of words
 *
 * @param boolean $unique
 * @return integer
 */
	function words($unique = false) {
		return $this->Adapters->dispatchMethod($this, 'words', null, array(
			'normalize' => true
		));
	}
}
?>