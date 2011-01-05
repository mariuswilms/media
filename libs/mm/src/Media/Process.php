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
 * `Media_Process` is the media processing manager class and provides
 * a configurable factory method.
 */
class Media_Process {

	protected static $_config;

	public static function config(array $config = array()) {
		if (!$config) {
			return self::$_config;
		}
		self::$_config = $config;
	}

	/**
	 * This factory method takes a source or an instance of an adapter,
	 * guesses the type of media maps it to a media processing class
	 * and instantiates it.
	 *
	 * @param array $config Valid values are:
	 *                      - `'source'`: An absolute path, a file or an open handle or
	 *                                    a MIME type if `'adapter'` is an instance.
	 *                      - `'adapter'`: A name or instance of a media adapter (i.e. `'Gd'`).
	 * @return Media_Process_Generic An instance of a subclass of `Media_Process_Generic` or
	 *                               if type could not be mapped an instance of the that class
	 *                               itself.
	 */
	public static function &factory(array $config = array()) {
		$default = array('source' => null, 'adapter' => null);
		extract($config + $default);

		if (!$source) {
			throw new BadMethodCallException("No source given.");
		}
		$name = Mime_Type::guessName($source);

		if (!$adapter) {
			if (!isset(self::$_config[$name])) {
				throw new Exception("No adapter configured for media name `{$name}`.");
			}
			$adapter = self::$_config[$name];
		}

		$name = ucfirst($name);
		$class = "Media_Process_{$name}";

		if (!class_exists($class)) { // Allows for injecting arbitrary classes.
			require_once "Media/Process/{$name}.php";
		}

		$media = new $class(compact('source', 'adapter'));
		return $media;
	}
}

?>