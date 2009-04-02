<?php
/**
 * Archive Medium File
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
 * @copyright  2007-2009 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor', 'Media.Medium');
/**
 * Archive Medium Class
 *
 * @package    media
 * @subpackage media.libs.medium
 */
class ArchiveMedium extends Medium {
	/**
	 * Compatible adapters
	 *
	 * @var array
	 */
	var $adapters = array('GetId3Archive');
	/**
	 * List of files in archive
	 *
	 * @return array
	 */
	function files() {
		return (array)$this->Adapters->dispatchMethod($this, 'files');
	}
	/**
	 * Compression percentage
	 *
	 * @return integer
	 */
	function compression() {
		return (integer)round($this->Adapters->dispatchMethod($this, 'compression'));
	}
}
?>
