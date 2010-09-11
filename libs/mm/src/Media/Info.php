<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2010 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright  2007-2010 David Persson <nperson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/mm
 */

require_once 'Mime/Type.php';

/**
 * `Media_Info` is the media information manager class and provides
 * a configurable factory method. In contrast to `Media_Process` the `Media_Info` type
 * classes operate with multiple adapters. This is possible due to the fact that the source'
 * state is not changed by the adapters (they're read only).
 */
class Media_Info {

	protected static $_config;

	public static function config(array $config = array()) {
		if (!$config) {
			return self::$_config;
		}
		self::$_config = $config;
	}

	/**
	 * This factory method takes a source or an instance of an adapter,
	 * guesses the type of media maps it to a media information class
	 * and instantiates it.
	 *
	 * @param array $config Valid values are:
	 *                      - `'source'`: An absolute path to a file.
	 *                      - `'adapters'`: Names or instances of media adapters (i.e. `array('Gd')`).
	 * @return Media_Process_Generic An instance of a subclass of `Media_Process_Generic` or
	 *                               if type could not be mapped an instance of the that class
	 *                               itself.
	 */
	public static function &factory(array $config = array()) {
		$default = array('source' => null, 'adapters' => array());
		extract($config + $default);

		if (!$source) {
			throw new BadMethodCallException("No source given.");
		}
		$name = Mime_Type::guessName($source);

		if (!$adapters) {
			if (!isset(self::$_config[$name])) {
				throw new Exception("No adapters configured for media name `{$name}`.");
			}
			$adapters = self::$_config[$name];
		}

		$name = ucfirst($name);
		$class = "Media_Info_{$name}";

		require_once "Media/Info/{$name}.php";

		$media = new $class(compact('source', 'adapters'));
		return $media;
	}
}

?>