<?php
/**
 * Basic Text Medium Adapter File
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
 * Basic Text Medium Adapter Class
 *
 * @package    media
 * @subpackage media.libs.medium.adapter
 */
class BasicTextMediumAdapter extends MediumAdapter {
	var $require = array('mimeTypes' => array('text/plain'));

	function initialize($Medium) {
		if (!isset($Medium->file)) {
			return false;
		}
		return true;
	}

	function characters($Medium) {
		return filesize($Medium->file);
	}
}
?>