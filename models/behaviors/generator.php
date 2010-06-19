<?php
/**
 * Generator Behavior File
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
 * @subpackage media.models.behaviors
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Lib', 'Media_Process', array('file' => 'mm/src/Media/Process.php'));
App::import('Lib', 'Mime_Type', array('file' => 'mm/src/Mime/Type.php'));

/**
 * Generator Behavior Class
 *
 * @package    media
 * @subpackage media.models.behaviors
 */
class GeneratorBehavior extends ModelBehavior {

/**
 * Default settings
 *
 * baseDirectory
 *   An absolute path (with trailing slash) to a directory which will be stripped off the file path
 *
 * filterDirectory
 *   An absolute path (with trailing slash) to a directory to use for storing generated versions
 *
 * createDirectory
 *   false - Fail on missing directories
 *   true  - Recursively create missing directories
 *
 * createDirectoryMode
 *   Octal mode value to use when creating directories
 *
 * mode
 *   Octal mode value to use for resulting files of `make()`.
 *
 * overwrite
 *   false - Will fail if a version with the same already exists
 *   true - Overwrites existing versions with the same name
 *
 * guessExtension
 *   false - When making media use extension of source file regardless of MIME
 *           type of the destination file.
 *   true - Try to guess extension by looking at the MIME type of the resulting file.
 *
 * @var array
 */
	var $_defaultSettings = array(
		'baseDirectory'       => MEDIA_TRANSFER,
		'filterDirectory'     => MEDIA_FILTER,
		'createDirectory'     => true,
		'createDirectoryMode' => 0755,
		'mode'                => 0644,
		'overwrite'           => false,
		'guessExtension'      => true
	);

/**
 * Setup
 *
 * @param Model $Model
 * @param array $settings See defaultSettings for configuration options
 * @return void
 */
	function setup(&$Model, $settings = array()) {
		$settings = (array)$settings;
		$this->settings[$Model->alias] = array_merge($this->_defaultSettings, $settings);

		if (method_exists($Model, 'beforeMake')) {
			$message  = 'GeneratorBehavior::setup - ';
			$message .= 'The `beforeMake` callback has been deprecated ';
			$message .= 'in favor of the `makeVersion` method. ';
			$message .=  "There is no workaround in place.";
			trigger_error($message, E_USER_NOTICE);
		}
	}

/**
 * Callback
 *
 * Triggers `make()` if both `dirname` and `basename` fields are present.
 * Otherwise skips and returns `true` to continue the save operation.
 *
 * @param Model $Model
 * @param boolean $created
 * @return boolean
 */
	function afterSave(&$Model, $created) {
		$item = $Model->data[$Model->alias];

		if (isset($item['dirname'], $item['basename'])) {
			$file = $item['dirname'] . DS . $item['basename'];
		} elseif (isset($item['file'])) {
			$file = $item['file'];
		} else {
			return false;
		}
		return $this->make($Model, $file);
	}

/**
 * Parses instruction sets and invokes `makeVersion()` for each version on a file.
 * Also creates the destination directory if enabled by settings.
 *
 * If the `makeVersion()` method is implemented in the current model it'll be used
 * for generating a specifc version of the file (i.e. `s`, `m` or `l`) otherwise
 * the method within this behavior is going to be used.
 *
 * @param Model $Model
 * @param string $file Path to a file relative to `baseDirectory`  or an absolute path to a file
 * @return boolean
 */
	function make(&$Model, $file) {
		extract($this->settings[$Model->alias]);

		list($file, $relativeFile) = $this->_file($Model, $file);
		$relativeDirectory = DS . rtrim(dirname($relativeFile), '.');

		$filter = Configure::read('Media.filter.' . Mime_Type::guessName($file));
		$result = true;

		foreach ($filter as $version => $instructions) {
			$directory = Folder::slashTerm($filterDirectory . $version . $relativeDirectory);

			$Folder = new Folder($directory, $createDirectory, $createDirectoryMode);
			if (!$Folder->pwd()) {
				$message  = "GeneratorBehavior::generateVersion - Directory `{$directory}` ";
				$message .= "could not be created or is not writable. ";
				$message .= "Please check the permissions.";
				trigger_error($message, E_USER_WARNING);
				$result = false;
				continue;
			}

			if (!$Model->makeVersion($file, compact('version', 'directory', 'instructions'))) {
				$message  = "GeneratorBehavior::make - Failed to make version `{$version}` ";
				$message .= "of file `{$file}`. ";
				trigger_error($message, E_USER_WARNING);
				$result = false;
			}
		}
		return $result;
	}

/**
 * Generate a version of a file. If this method is reimplemented in the
 * model, than that one is used by `make()` instead of the implementation
 * below.
 *
 * $process an array with the following contents:
 *  - `directory`:  The destination directory (If this method was called
 *                  by `make()` the directory is already created)
 *  - `version`:  The version requested to be processed (e.g. `'l'`)
 *  - `instructions`: An array containing which names of methods to be called.
 *                 Possible instructions are:
 *                  - `array('name of method', 'name of other method')`
 *                  - `array('name of method' => array('arg1', 'arg2'))`
 * @param Model $Model
 * @param string $file Absolute path to the source file
 * @param array $process directory, version, instructions
 * @return boolean `true` if version for the file was successfully stored
 */
	function makeVersion(&$Model, $file, $process) {
		extract($this->settings[$Model->alias]);

		/* Process clone instruction */
		if (key($process['instructions']) == 'clone') {
			$action = current($args);

			if (!in_array($action, array('copy', 'link', 'symlink'))) {
				return false;
			}

			$destination = $this->_destinationFile($file, $process['directory'], null, $overwrite);

			if (!$destination) {
				return false;
			}
			return call_user_func($action, $file, $destination) && chmod($destination, $mode);
		}

		/* Process media transforms */
		$Media = Media_Process::factory(array('source' => $file));

		foreach ($process['instructions'] as $key => $value) {
			if (is_int($key)) {
				$method = $value;
				$args = null;
			} else {
				$method = $key;
				$args = (array) $value;
			}
			if (!method_exists($Media, $method)) {
				return false;
			}
			$result = call_user_func_array(array($Media, $method), $args);

			if ($result === false) {
				return false;
			} elseif (is_a($result, 'Media_Process')) {
				$Media = $result;
			}
		}

		/* Determine destination file */
		$extension = null;

		if ($guessExtension) {
			if (isset($process['instructions']['convert'])) {
				$extension = Mime_Type::guessExtension($process['instructions']['convert']);
			} else {
				$extension = Mime_Type::guessExtension($file);
			}
		}
		$destination = $this->_destinationFile($file, $process['directory'], $extension, $overwrite);

		if (!$destination) {
			return false;
		}
		return $Media->store($destination) && chmod($destination, $mode);
	}

	function _destinationFile($source, $directory, $extension = null, $overwrite = false) {
		$destination = $directory;

		if ($extension) {
			$destination .= pathinfo($source, PATHINFO_FILENAME) . '.' . $extension;
		} else {
			$destination .= basename($source);
		}
		if (file_exists($destination)) {
			if (!$overwrite) {
				return false;
			}
			unlink($destination);
		}
		return $destination;
	}

/**
 * Returns relative and absolute path to a file
 *
 * @param Model $Model
 * @param string $file
 * @return array
 */
	function _file(&$Model, $file) {
		extract($this->settings[$Model->alias]);
		$file = str_replace(array('\\', '/'), DS, $file);

		if (!is_file($file)) {
			$file = ltrim($file, DS);
			$relativeFile = $file;
			$file = $baseDirectory . $file;
		} else {
			$relativeFile = str_replace($baseDirectory, null, $file);
		}
		return array($file, $relativeFile);
	}
}

?>