<?php
/**
 * Basic Text Media Adapter File
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
 * Basic Text Media Adapter Class
 *
 * @package    media
 * @subpackage media.libs.media.adapter
 */
class BasicTextMediaAdapter extends MediaAdapter {
	var $require = array('mimeTypes' => array('text/plain'));

	function initialize($Media) {
		if (!isset($Media->file)) {
			return false;
		}
		return true;
	}

	function characters($Media) {
		return filesize($Media->file);
	}
}
?>