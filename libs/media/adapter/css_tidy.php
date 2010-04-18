<?php
/**
 * Css Tidy Media Adapter File
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
 * Css Tidy Media Adapter Class
 *
 * @package    media
 * @subpackage media.libs.media.adapter
 * @link       http://csstidy.sourceforge.net/
 * @deprecated
 */
class CssTidyMediaAdapter extends MediaAdapter {

	var $require = array(
		'mimeTypes' => array('text/css'),
		'extensions' => array('ctype'),
		'imports' => array(
			array('type' => 'Vendor','name'=> 'csstidy','file' => 'csstidy/class.csstidy.php')),
	);

	var $_template = 'high_compression'; // or: highest_compression

	function initialize($Media) {
		if (!isset($Media->contents['raw']) && isset($Media->file)) {
			return $Media->contents['raw'] = file_get_contents($Media->file);
		}
		return true;
	}

	function store($Media, $file) {
		return file_put_contents($Media->contents['raw'], $file);
	}

	function compress($Media) {
		$Tidy = new csstidy() ;
		$Tidy->load_template($this->_template);
		$Tidy->parse($Media->contents['raw']);

		if ($compressed = $Tidy->print->plain()) {
			$Media->content['raw'] = $compressed;
			return true;
		}
		return false;
	}
}
?>