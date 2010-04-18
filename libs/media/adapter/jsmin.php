<?php
/**
 * Jsmin Media Adapter File
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
 * Jsmin Media Adapter Class
 *
 * @package    media
 * @subpackage media.libs.media.adapter
 * @link       http://code.google.com/p/jsmin-php/
 * @deprecated
 */
class JsminMediaAdapter extends MediaAdapter {
	var $require = array(
		'mimeTypes' => array('application/javascript'),
		'imports' => array(array('type' => 'Vendor', 'name'=> 'JSMin', 'file' => 'jsmin.php')),
	);

	function initialize($Media) {
		if (isset($Media->contents['raw'])) {
			return true;
		}

		if (!isset($Media->file)) {
			return false;
		}
		return $Media->contents['raw'] = file_get_contents($Media->file);
	}

	function store($Media, $file) {
		return file_put_contents($Media->contents['raw'], $file);
	}

	function compress($Media) {
		return $Media->contents['raw'] = trim(JSMin::minify($Media->contents['raw']));
	}
}
?>