<?php
/**
 * Js Medium File
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
 * @copyright  2007-2009 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor', 'Media.Medium');
/**
 * Js Medium Class
 *
 * @package    media
 * @subpackage media.libs.medium
 */
class JsMedium extends Medium {
/**
 * Compatible adapters
 *
 * @var array
 */
	var $adapters = array('Jsmin', 'BasicText');
/**
 * Number of characters
 *
 * @return integer
 */
	function characters() {
		return (integer)$this->Adapters->dispatchMethod($this, 'characters');
	}
/**
 * Compresses contents. of the medium
 *
 * @return string
 */
	function compress()  {
		return $this->Adapters->dispatchMethod($this, 'compress');
	}
}
?>