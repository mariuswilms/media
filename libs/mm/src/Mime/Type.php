<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2012 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright  2007-2012 David Persson <nperson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/mm
 */

/**
 * The `Mime_Type` class allows for detecting MIME types of files and streams
 * by analyzing it's contents and/or extension. The class makes use of two adapters
 * (`magic` and `glob`) which must be configured before using any of the methods.
 */
class Mime_Type {

	const REGEX = '^[\-\w\.\+]+\/[\-\w\.\+]+$';

	/**
	 * Magic.
	 *
	 * @see config()
	 * @var Mime_Type_Magic_Adapter
	 */
	public static $magic;

	/**
	 * Glob.
	 *
	 * @see config()
	 * @var Mime_Type_Glob_Adapter
	 */
	public static $glob;

	/**
	 * Mapping MIME type (part/needle) to media name.
	 *
	 * @see guessName()
	 * @var array
	 */
	public static $name = array(
		'application/ogg'       => 'audio',
		'application/pdf'       => 'document',
		'application/msword'    => 'document',
		'officedocument'        => 'document',
		'image/icon'            => 'icon',
		'text/css'              => 'css',
		'text/javascript'       => 'javascript',
		'text/code'             => 'generic',
		'text/rtf'              => 'document',
		'text/plain'            => 'text',
		'image/'                => 'image',
		'audio/'                => 'audio',
		'video/'                => 'video',
		'/'                     => 'generic'
	);

	/**
	 * Preferred types to use if yielding multiple results.
	 *
	 * @see guessType()
	 */
	public static $preferredTypes = array(
		'audio/ogg'
	);

	/**
	 * Preferred extensions to use if yielding multiple results.
	 *
	 * @see guessExtension()
	 */
	public static $preferredExtensions = array(
		'bz2', 'css', 'doc', 'html', 'jpg',
		'mov', 'mpeg', 'mp3', 'mp4', 'oga', 'ogv',
		'php', 'ps',  'rm', 'ra', 'rv', 'swf',
		'tar', 'tiff', 'txt', 'xhtml', 'xml'
	);

	/**
	 * Set and change configuration during runtime.
	 *
	 * @param string $type Either "Magic" or "Glob"
	 * @param array $config Config specifying engine and db
	 *              e.g. `array('adapter' => 'Fileinfo', 'file' => '/etc/magic')`.
	 */
	public static function config($type, array $config = array()) {
		if ($type != 'Magic' && $type != 'Glob') {
			return false;
		}

		$class = "Mime_Type_{$type}_Adapter_{$config['adapter']}";
		$file = "Mime/Type/{$type}/Adapter/{$config['adapter']}.php";

		require_once $file;

		$type[0] = strtolower($type[0]);
		self::${$type} = new $class($config);
	}

	public static function reset() {
		self::$glob = self::$magic = null;
	}

	/**
	 * Simplifies a MIME type string.
	 *
	 * @param string $mimeType A valid MIME type string.
	 * @param boolean If `false` removes properties, defaults to `false`.
	 * @param boolean If `false` removes experimental indicators, defaults to `false`.
	 * @return string The simplified MIME type string.
	 */
	public static function simplify($mimeType, $properties = false, $experimental = false) {
		if (!$experimental) {
			$mimeType = str_replace('x-', null, $mimeType);
		}

		if (!$properties) {
			if (strpos($mimeType, ';') !== false) {
				$mimeType = strtok($mimeType, ';');
			} else {
				$mimeType = strtok($mimeType, ' ');
			}
		}
		return $mimeType;
	}

	/**
	 * Guesses the extension (suffix) for an existing file or a MIME type.
	 *
	 * @param string|resource $file Path to a file, an open handle to a file or a MIME type string.
	 * @return string|void A string with the first matching extension (w/o leading dot).
	 */
	public static function guessExtension($file) {
		if (is_string($file) && preg_match('/' . self::REGEX . '/', $file)) {
			$mimeType = self::simplify($file, false, true);
		} else {
			$mimeType = self::guessType($file);
		}

		$globMatch = (array) self::$glob->analyze($mimeType, true);
		if (count($globMatch) === 1) {
			return array_shift($globMatch);
		}

		$preferMatch = array_intersect($globMatch, self::$preferredExtensions);
		if (count($preferMatch) === 1) {
			return array_shift($preferMatch);
		}
	}

	/**
	 * Guesses the MIME type of the file.
	 *
	 * @param string|resource $file Path to/name of a file or an open handle to a file.
	 * @param options $options Valid options are:
	 *                - `'paranoid'`: If set to `true` the file's MIME type is guessed by
	 *                                looking at it's contents only.
	 *                - `'properties'`: Leave properties intact, defaults to `false`.
	 *                - `'experimental'`: Leave experimental indicators intact, defaults to `true`.
	 * @return string|void String with MIME type on success.
	 */
	public static function guessType($file, $options = array()) {
		$defaults = array(
			'paranoid' => false,
			'properties' => false,
			'experimental' => true
		);
		extract($options + $defaults);

		$magicMatch = $globMatch = array();
		$openedHere = false;

		if (!$paranoid) {
			if (is_resource($file)) {
				$meta = stream_get_meta_data($file);
				$name = $meta['uri'];
			} else {
				$name = $file;
			}
			$globMatch = (array) self::$glob->analyze($name);

			if (count($globMatch) === 1) {
				 return self::simplify(array_shift($globMatch), $properties, $experimental);
			}
			$preferMatch = array_intersect($globMatch, self::$preferredTypes);

			if (count($preferMatch) === 1) {
				return array_shift($preferMatch);
			}
		}

		if (is_resource($file)) {
			$handle = $file;
		} elseif (is_file($file)) {
			$handle = fopen($file, 'rb');
			$openedHere = true;
		} else {
			return;
		}

		$magicMatch = self::$magic->analyze($handle);
		$magicMatch = empty($magicMatch) ? array() : array($magicMatch);

		if (empty($magicMatch)) {
			rewind($handle);
			$peek = fread($handle, 32);

			if ($openedHere) {
				fclose($handle);
			}

			if (preg_match('/[\t\n\r]+/', $peek)) {
				return 'text/plain';
			}
			return 'application/octet-stream';
		}

		if ($openedHere) {
			fclose($handle);
		}

		if (count($magicMatch) === 1) {
			return self::simplify(array_shift($magicMatch), $properties, $experimental);
		}

		if ($globMatch && $magicMatch) {
			$combinedMatch = array_intersect($globMatch, $magicMatch);

			if (count($combinedMatch) === 1) {
				return self::simplify(array_shift($combinedMatch), $properties, $experimental);
			}
		}
	}

	/**
	 * Determines lowercase media name.
	 *
	 * @param string $file Path to/name of a file, an open handle to a file or a MIME type string.
	 * @return string
	 */
	public static function guessName($file) {
		if (is_string($file) && preg_match('/' . self::REGEX . '/', $file)) {
			$mimeType = self::simplify($file);
		} else {
			$mimeType = self::guessType($file, array('experimental' => false));
		}
		foreach (self::$name as $pattern => $name) {
			if (strpos($mimeType, $pattern) !== false) {
				return $name;
			}
		}
		return 'generic';
	}
}

?>