<?php
/**
 * Pear Text Media Adapter File
 *
 * Copyright (c) 2007-2010 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.3
 *
 * @package    media
 * @subpackage media.libs.media.adapter
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */

/**
 * Pear Text Media Adapter Class
 *
 * @package    media
 * @subpackage media.libs.media.adapter
 * @link       http://pear.php.net/package/Text_Statistics
 */
class PearTextMediaAdapter extends MediaAdapter {
	var $require = array(
		'mimeTypes' => array('text/plain'),
		'imports' => array(
			array('type' => 'Vendor','name' => 'Text_Statistics','file' => 'Text/Statistics.php')
	));

	function initialize($Media) {
		if (isset($Media->objects['Text_Statistics'])) {
			return true;
		}

		if (!isset($Media->contents['raw'])) {
			if (!isset($Media->file)) {
				return false;
			}
			if (!$raw = file_get_contents($Media->file)) {
				return false;
			}
			$Media->contents['raw'] = $raw;
		}

		$Media->objects['Text_Statistics'] = @new Text_Statistics($Media->contents['raw']);
		return true;
	}

	function syllables($Media) {
		return $Media->objects['Text_Statistics']->numSyllables;
	}

	function words($unique = false) {
		if($unique) {
			return $this->_Text->uniqWords;
		}
		return $this->_Text->numWords;
	}

	function sentences($Media) {
		return $Media->objects['Text_Statistics']->numSentences;
	}

	function fleschScore($Media) {
		return $Media->objects['Text_Statistics']->flesch;
	}

	function lexicalDensity() {
		return round(($this->_Text->uniqWords / $this->_Text->numWords) * 100, 0);
	}
}
?>