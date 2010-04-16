<?php
/**
 * Js Media File
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
 * Js Media Class
 *
 * @package    media
 * @subpackage media.libs.media
 * @deprecated
 */
class JsMedia extends Media {

/**
 * Compatible adapters
 *
 * @var array
 */
	var $adapters = array('Jsmin', 'BasicText');

	function __construct($file, $mimeType = null) {
		$message  = "JsMedia::__construct - ";
		$message .= "All functionality related to assets has been deprecated.";
		trigger_error($message, E_USER_NOTICE);
		parent::__construct($file, $mimeType);
	}

/**
 * Number of characters
 *
 * @return integer
 */
	function characters() {
		return $this->Adapters->dispatchMethod($this, 'characters', null, array(
			'normalize' => true
		));
	}

/**
 * Compresses contents. of the media
 *
 * @return string
 */
	function compress()  {
		return $this->Adapters->dispatchMethod($this, 'compress');
	}
}
?>