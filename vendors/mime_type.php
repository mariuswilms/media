<?php
/**
 * Mime Type File
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
 * @subpackage media.libs
 * @copyright  2007-2009 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
uses('file');
/**
 * Mime Type Class
 *
 * Detection of a file's MIME Type by it's contents and/or extension.
 * This is the main interface for MIME Type detection wrapping
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
 * @access public
 */
	var $__magic;
/**
 * Glob
 *
 * @var object An instance of the MimeGlob class
 * @access public
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
			$instance[0] =& new MimeType();
			$instance[0]->__loadMagic(Configure::read('Mime.magic'));
			$instance[0]->__loadGlob(Configure::read('Mime.glob'));
		}
		return $instance[0];
	}
/**
 * Change configuration during runtime
 *
 * @param string $property Either "magic" or "glob"
 * @param array $config Config specifying engine and db e.g. array('engine' => 'fileinfo', 'db' => '/etc/magic')
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
 * Guesses the extension (suffix) for an existing file or a mime type
 *
 * @param string $mimetype A mime type or an absolute path to file
 * @param array $options Currently not used
 * @return mixed A string with the first matching extension (w/o leading dot), false if nothing matched
 * @access public
 */
	function guessExtension($file, $options = array()) {
		$_this =& MimeType::getInstance();
		$globMatch = array();
		$preferred = array(
						'bz2', 'css', 'doc', 'html', 'jpg',
						'mpeg', 'mp3', 'ogg', 'php', 'ps',
						'rm', 'ra', 'rv', 'swf',
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
 * Guesses the mime type of the file
 *
 * Empty results are currently not handled:
 * 	application/x-empty
 * 	application/x-not-regular-file
 *
 * @param string $file
 * @param options $options Valid options are:
 *  - "simplify" If set to true experimental indicators are being removed from the mime type
 *  - "fast" If set to true Forces an suffix based lookup first
 * @return mixed string with mime type on success
 * @access public
 */
	function guessType($file, $options = array()) {
		$_this =& MimeType::getInstance();

		if (is_bool($options)) {
			$options = array('looseProperties' => $options, 'looseExperimental' => $options);
		}
		if (isset($options['simplify'])) {
			$options = array('looseProperties' => $options['simplify'], 'looseExperimental' => $options['simplify']);
		}
		$default = array('looseProperties' => true, 'looseExperimental' => false, 'paranoid' => false);
		extract(array_merge($default, $options), EXTR_SKIP);
		$magicMatch = $globMatch = array();

		if (!$paranoid) {
			if (is_a($_this->__glob, 'MimeGlob')) {
				$globMatch = $_this->__glob->analyze($file);
			}

			if (count($globMatch) === 1) {
				return MimeType::simplify(array_shift($globMatch), $looseProperties, $looseExperimental);
			}
		}

		if (is_a($_this->__magic, 'finfo')) {
			$magicMatch = $_this->__magic->file($file);
		} elseif ($_this->__magic === 'mime_magic') {
			$magicMatch = mime_content_type($file);
		} elseif (is_a($_this->__magic, 'MimeMagic')) {
			$magicMatch = $_this->__magic->analyze($file);
		}
		$magicMatch = !is_array($magicMatch) && empty($magicMatch) ? array() : array($magicMatch);

		if (empty($magicMatch)) {
			$File =& new File($file);

			if ($File->readable()) {
				if (preg_match('/[\t\n\r]+/', $File->read(32))) {
					return 'text/plain';
				}
				return 'application/octet-stream';
			}
			return null;
		}

		if (count($magicMatch) === 1) {
			return MimeType::simplify(array_shift($magicMatch), $looseProperties, $looseExperimental);
		}

		if ($globMatch && $magicMatch) {
			$combinedMatch = array_intersect($globMatch, $magicMatch);

			if (count($combinedMatch) === 1) {
				return MimeType::simplify(array_shift($combinedMatch), $looseProperties, $looseExperimental);
			}
		}

		return null;
	}
/**
 * Simplifies a mime type by removing all exprimental indicators
 * and attributes
 *
 * @param string $mimeType
 * @return string
 */
	function simplify($mimeType, $looseProperties = true, $looseExperimental = true) {
		if ($looseExperimental) {
			$mimeType = str_replace('x-', null, $mimeType);
		}

		if ($looseProperties) {
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
 * @return void
 */
	function __loadMagic($config = array()) {
		$engine = $db = null;

		if (is_array($config)) {
			extract($config, EXTR_OVERWRITE);
		}

		if (($engine === 'fileinfo' || $engine === null) && extension_loaded('fileinfo')) {
			if (isset($db)) {
				$this->__magic =& new finfo(FILEINFO_MIME, $db);
			} else {
				$this->__magic =& new finfo(FILEINFO_MIME);
			}
		} elseif (($engine === 'mime_magic' || $engine === null) && extension_loaded('mime_magic')) {
			$this->__magic = 'mime_magic';
		} elseif ($engine === 'core' || $engine === null) {
			App::import('Vendor', 'Media.MimeMagic');

			if ($cached = Cache::read('mime_magic_db')) {
				$db = $cached;
			}

			if (!isset($db)) {
				$commonFiles = array(
					APP . 'plugins' . 'vendors' . DS . 'magic.db',
					APP . 'plugins' . DS . 'media' . DS . 'vendors' . DS . 'magic.db',
					VENDORS . 'magic.db',
					);

				foreach($commonFiles as $commonFile) {
					if (is_readable($commonFile)) {
						$db = $commonFile;
						break(1);
					}
				}
			}
			if (isset($db)) {
				$this->__magic =& new MimeMagic($db);
				Cache::write('mime_magic_db', $this->__magic->toArray());
			}
		} else {
			$this->__magic = null;
		}
	}
/**
 * Sets glob property
 *
 * @return void
 */
	function __loadGlob($config = array()) {
		$engine = $db = null;

		if (is_array($config)) {
			extract($config, EXTR_OVERWRITE);
		}

		if ($engine === 'core' || $engine === null) {
			App::import('Vendor', 'Media.MimeGlob');

			if (!isset($db)) {
				$commonFiles = array(
					APP . 'config' . DS . 'mime_glob.php',
					APP . 'plugins' . DS . 'media' . DS . 'config' . DS . 'mime_glob.php',
					VENDORS . 'glob.db',
					APP . 'vendors' . DS . 'glob.db',
					APP . 'plugins' . DS . 'media' . DS . 'vendors' . DS . 'glob.db',
				);

				foreach($commonFiles as $commonFile) {
					if (is_readable($commonFile)) {
						$db = $commonFile;
						break(1);
					}
				}
			}
			if (isset($db)) {
				$this->__glob =& new MimeGlob($db);
			}
		} else {
			$this->__glob = null;
		}
	}
}
?>