<?php
/**
 * Pear Text Medium Adapter File
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
 * @subpackage media.libs.medium.adapter
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
/**
 * Pear Text Medium Adapter Class
 *
 * @package    media
 * @subpackage media.libs.medium.adapter
 * @link       http://pear.php.net/package/Text_Statistics
 */
class PearTextMediumAdapter extends MediumAdapter {
	var $require = array(
		'mimeTypes' => array('text/plain'),
		'imports' => array(
			array('type' => 'Vendor','name' => 'Text_Statistics','file' => 'Text/Statistics.php')
	));

	function initialize($Medium) {
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

	function syllables($Medium) {
		return $Medium->objects['Text_Statistics']->numSyllables;
	}

	function words($unique = false) {
		if($unique) {
			return $this->_Text->uniqWords;
		}
		return $this->_Text->numWords;
	}

	function sentences($Medium) {
		return $Medium->objects['Text_Statistics']->numSentences;
	}

	function fleschScore($Medium) {
		return $Medium->objects['Text_Statistics']->flesch;
	}

	function lexicalDensity() {
		return round(($this->_Text->uniqWords / $this->_Text->numWords) * 100, 0);
	}
}
?>