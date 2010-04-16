<?php
/**
 * Medium Helper File
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
 * @subpackage media.views.helpers
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Helper', 'Media.Media');

class MediumHelper extends MediaHelper {

	function __construct($settings = array()) {
		$message  = "Medium helper has been renamed to media helper. Please update ";
		$message .= "your `helpers` properties.";
		trigger_error($message, E_USER_NOTICE);
		parent::__construct($settings);
	}

	function __call($method, $args) {
		$message  = "Medium helper has been renamed to media helper. Please update ";
		$message .= "any references to `\$medium->{$method}()` in your templates.";
		trigger_error($message, E_USER_NOTICE);
		return $this->dispatchMethod($method, $args);
	}
}

?>