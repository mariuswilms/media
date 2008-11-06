<?php
/**
 * Text Medium File
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
 * Text Medium Class
 * 
 * @category   media handling
 * @package    attm
 * @subpackage attm.plugins.media.libs.medium
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  $CopyrightYear$ David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://cakeforge.org/projects/attm The attm Project
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