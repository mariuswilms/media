<?php
/**
 * Document Medium File
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
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  2007-2009 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor', 'Media.Medium');
/**
 * Document Medium Class
 *
 * @package    media
 * @subpackage media.libs.medium
 */
class DocumentMedium extends Medium {
	var $adapters = array('Imagick', 'ImagickShell');
/**
 * Current width of medium
 *
 * @return int
 */
	function width()	{
		return $this->Adapters->dispatchMethod($this, 'width');
	}
/**
 * Current height of medium
 *
 * @return int
 */
	function height() {
		return $this->Adapters->dispatchMethod($this, 'height');
	}
/**
 * Determines a (known) ratio of medium
 *
 * @return mixed if String if $known is true or float if false
 */
	function ratio($known = true) {
		if (!$known) {
			return $this->width() / $this->height();
		}
		return $this->_knownRatio($this->width(), $this->height());
	}
}
?>