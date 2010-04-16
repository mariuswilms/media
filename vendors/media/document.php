<?php
/**
 * Document Media File
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
 * @subpackage media.libs.media
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor', 'Media.Media');

/**
 * Document Media Class
 *
 * @package    media
 * @subpackage media.libs.media
 */
class DocumentMedia extends Media {

/**
 * Compatible adapters
 *
 * @var array
 */
	var $adapters = array('Imagick', 'ImagickShell');

/**
 * Current width of media
 *
 * @return integer
 */
	function width()	{
		return $this->Adapters->dispatchMethod($this, 'width', null, array(
			'normalize' => true
		));
	}

/**
 * Current height of media
 *
 * @return integer
 */
	function height() {
		return $this->Adapters->dispatchMethod($this, 'height', null, array(
			'normalize' => true
		));
	}

/**
 * Determines a (known) ratio of media
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