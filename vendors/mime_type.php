<?php
/**
 * Mime Type File
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
 * @subpackage media.libs
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
uses('file');
/**
 * Mime Type Class
 *
 * Detection of a file's MIME type by it's contents and/or extension.
 * This is the main interface for MIME type detection wrapping
 * (native) magic and glob mechanisms.
 *
 * @package    media
 * @subpackage media.libs
 */
class MimeType extends Object {
/**
 * Magic
 *
 * @var mixed An instance of the MimeMagic or finfo class or a string containing 'mime_magic'
 * @access private
 */
	var $__magic;
/**
 * Glob
 *
 * @var object An instance of the MimeGlob class
 * @access private
 */
	var $__glob;
/**
 * Return a singleton instance of MimeType.
 *
 * @return object MimeType instance
 * @access public
 */
	function &getInstance() {
		static $instance = array();

		if (!$instance) {
			$instance[0] = new MimeType();
			$instance[0]->__loadMagic(Configure::read('Mime.magic'));
			$instance[0]->__loadGlob(Configure::read('Mime.glob'));
		}
		return $instance[0];
	}
/**
 * Change configuration during runtime
 *
 * @param string $property Either "magic" or "glob"
 * @param array $config Config specifying engine and db
 * 	e.g. array('engine' => 'fileinfo', 'db' => '/etc/magic')
 */
	function config($property = 'magic', $config = array()) {
		$_this =& MimeType::getInstance();

		if ($property === 'magic') {
			$_this->__loadMagic($config);
		} elseif ($property === 'glob') {
			$_this->__loadGlob($config);
		}
	}
/**
 * Guesses the extension (suffix) for an existing file or a MIME type
 *
 * @param string $file A MIME type or an absolute path to file
 * @param array $options Currently not used
 * @return mixed A string with the first matching extension (w/o leading dot),
 * 	false if nothing matched
 * @access public
 */
	function guessExtension($file, $options = array()) {
		$_this =& MimeType::getInstance();
		$globMatch = array();
		$preferred = array(
			'bz2', 'css', 'doc', 'html', 'jpg',
			'mpeg', 'mp3', 'ogg', 'php', 'ps',
			'rm', 'ra', 'rv', 'swf', 'tar',
			'tiff', 'txt', 'xhtml', 'xml',
		);

		if (is_file($file)) {
			$mimeType = $_this->guessType($file);
		} else {
			$mimeType = $file;
		}

		if (is_a($_this->__glob, 'MimeGlob')) {
			$globMatch = $_this->__glob->analyze($mimeType, true);
		}

		if (count($globMatch) === 1) {
			return array_shift($globMatch);
		}

		$preferMatch = array_intersect($globMatch, $preferred);

		if (count($preferMatch) === 1) {
			return array_shift($preferMatch);
		}
		return null;
	}
/**
 * Guesses the MIME type of the file
 *
 * Empty results are currently not handled:
 * 	application/x-empty
 * 	application/x-not-regular-file
 *
 * @param string $file
 * @param options $options Valid options are:
 *	- `'paranoid'` If set to true only then content for the file is used for detection
 *	- `'properties'` Used for simplification, defaults to false
 *	- `'experimental'` Used for simplification, defaults to false
 * @return mixed string with MIME type on success
 * @access public
 */
	function guessType($file, $options = array()) {
		$_this =& MimeType::getInstance();

		$defaults = array(
			'paranoid' => false,
			'properties' => false,
			'experimental' => true,
		);
		extract($options + $defaults);

		$magicMatch = $globMatch = array();

		if (!$paranoid) {
			if (is_a($_this->__glob, 'MimeGlob')) {
				$globMatch = $_this->__glob->analyze($file);
			}
			if (count($globMatch) === 1) {
				 return MimeType::simplify(array_shift($globMatch), $properties, $experimental);
			}
		}

		if (!is_readable($file)) {
			return null;
		}

		if (is_a($_this->__magic, 'finfo')) {
			$magicMatch = $_this->__magic->file($file);
		} elseif ($_this->__magic === 'mime_magic') {
			$magicMatch = mime_content_type($file);
		} elseif (is_a($_this->__magic, 'MimeMagic')) {
			$magicMatch = $_this->__magic->analyze($file);
		}
		$magicMatch = empty($magicMatch) ? array() : array($magicMatch);

		if (empty($magicMatch)) {
			$File = new File($file);

			if (preg_match('/[\t\n\r]+/', $File->read(32))) {
				return 'text/plain';
			}
			return 'application/octet-stream';
		}

		if (count($magicMatch) === 1) {
			return MimeType::simplify(array_shift($magicMatch), $properties, $experimental);
		}

		if ($globMatch && $magicMatch) {
			$combinedMatch = array_intersect($globMatch, $magicMatch);

			if (count($combinedMatch) === 1) {
				return MimeType::simplify(array_shift($combinedMatch), $properties, $experimental);
			}
		}
		return null;
	}
/**
 * Simplifies a MIME type string
 *
 * @param string $mimeType
 * @param boolean If true removes properties
 * @param boolean If true removes experimental indicators
 * @return string
 */
	function simplify($mimeType, $properties = false, $experimental = false) {
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
 * Sets magic property
 *
 * @param array $config Configuration settings to take into account
 * @return void
 */
	function __loadMagic($config = array()) {
		$engine = $db = null;

		if (is_array($config)) {
			extract($config, EXTR_OVERWRITE);
		}

		if (($engine === 'fileinfo' || $engine === null) && extension_loaded('fileinfo')) {
			if (isset($db)) {
				$this->__magic = new finfo(FILEINFO_MIME, $db);
			} else {
				$this->__magic = new finfo(FILEINFO_MIME);
			}
		} elseif (($engine === 'mime_magic' || $engine === null) && extension_loaded('mime_magic')) {
			$this->__magic = 'mime_magic';
		} elseif ($engine === 'core' || $engine === null) {
			App::import('Vendor', 'Media.MimeMagic');

			if ($cached = Cache::read('mime_magic_db', '_cake_core_')) {
				$db = $cached;
			}

			if (!isset($db)) {
				$db = $this->__db('magic');
			}
			if (isset($db)) {
				$this->__magic = new MimeMagic($db);

				if (!$cached) {
					Cache::write('mime_magic_db', $this->__magic->toArray(), '_cake_core_');
				}
			}
		} else {
			$this->__magic = null;
		}
	}
/**
 * Sets glob property
 *
 * @param array $config Configuration settings to take into account
 * @return void
 */
	function __loadGlob($config = array()) {
		$engine = $db = null;

		if (is_array($config)) {
			extract($config, EXTR_OVERWRITE);
		}

		if ($engine === 'core' || $engine === null) {
			App::import('Vendor', 'Media.MimeGlob');

			if ($cached = Cache::read('mime_glob_db', '_cake_core_')) {
				$db = $cached;
			}

			if (!isset($db)) {
				$db = $this->__db('glob');
			}
			if (isset($db)) {
				$this->__glob = new MimeGlob($db);

				if (!$cached) {
					Cache::write('mime_glob_db', $this->__glob->toArray(), '_cake_core_');
				}
			}
		} else {
			$this->__glob = null;
		}
	}
/**
 * Finds the db file for given type
 *
 * @param string $type Either 'magic' or 'glob'
 * @access private
 * @return mixed If no file was found null otherwise the absolute path to the file
 */
	function __db($type) {
		$searchPaths = array(
			'mime_' . $type . '.php' => array(CONFIGS),
			'mime_' . $type . '.db' => array_merge(
				Configure::read('vendorPaths'),
				array(dirname(__FILE__) . DS)
			));

		foreach ($searchPaths as $basename => $paths) {
			foreach ($paths as $path) {
				if (is_readable($path . $basename)) {
					return $path . $basename;
				}
			}
		}
	}
}
?>
