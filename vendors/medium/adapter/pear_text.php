<?php
/**
 * Pear Text Medium Adapter File
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
 * @subpackage attm.plugins.media.libs.medium.adapter
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
/**
 * Pear Text Medium Adapter Class
 * 
 * @category   media handling
 * @package    attm
 * @subpackage attm.plugins.media.libs.medium.adapter
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  $CopyrightYear$ David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://cakeforge.org/projects/attm The attm Project
 * @link       http://pear.php.net/package/Text_Statistics
 */
class PearTextMediumAdapter extends MediumAdapter {
	var $require = array(
							'mimeTypes' => array('text/plain'),
							'imports' => array(array('type' => 'Vendor','name' => 'Text_Statistics','file' => 'Text/Statistics.php')), 
							);	
								
	function initialize(&$Medium) {
		if (isset($Medium->objects['Text_Statistics'])) {
			return true;
		}
		
		if (!isset($Medium->contents['raw'])) {
			if (!isset($Medium->file)) {
				return false;
			}
			if (!$raw = file_get_contents($Medium->file)) {
				return false;
			}
			$Medium->contents['raw'] = $raw;
		}
		
		$Medium->objects['Text_Statistics'] = @new Text_Statistics($Medium->contents['raw']);
		
		return true;
	}
	
	function syllables(&$Medium) {
		return $Medium->objects['Text_Statistics']->numSyllables;
	}
	
	function words($unique = false) {
		if($unique) {
			return $this->_Text->uniqWords;
		}
		return $this->_Text->numWords;
	}
	
	function sentences(&$Medium) {
		return $Medium->objects['Text_Statistics']->numSentences;
	}

	function fleschScore(&$Medium) {
		return $Medium->objects['Text_Statistics']->flesch;
	}
	
	function lexicalDensity() {
		return round(($this->_Text->uniqWords / $this->_Text->numWords) * 100,0);
	}
}
?>