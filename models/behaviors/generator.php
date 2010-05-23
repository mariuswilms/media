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
App::import('Lib', 'Media.Media');

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
 * overwrite
 *   false - Will fail if a version with the same already exists
 *   true - Overwrites existing versions with the same name
 *
 * @var array
 */
	var $_defaultSettings = array(
		'baseDirectory'       => MEDIA,
		'filterDirectory'     => MEDIA_FILTER,
		'createDirectory'     => true,
		'createDirectoryMode' => 0755,
		'overwrite'           => false
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
		extract($this->settings[$Model->alias]);
		$item =& $Model->data[$Model->alias];

		if (isset($item['dirname'], $item['basename'])) {
			$file = $item['dirname'] . DS . $item['basename'];
		} elseif (isset($item['file'])) {
			$file = $item['file'];
		} else {
			return false;
		}
		return $this->make($Model, $file, $overwrite);
	}

/**
 * Parses instruction sets and invokes `makeVersion()` for each version on a file
 *
 * If the `makeVersion()` method is implemented in the current model it'll be used
 * for generating a specifc version of the file (i.e. `s`, `m` or `l`) otherwise
 * the method within this behavior is going to be used.
 *
 * @param Model $Model
 * @param string $file Path to a file relative to `baseDirectory`  or an absolute path to a file
 * @param boolean $overwrite Whether to overwrite existing versions with the same name or not
 * @return boolean
 */
	function make(&$Model, $file, $overwrite = false) {
		extract($this->settings[$Model->alias]);

		list($file, $relativeFile) = $this->_file($Model, $file);
		$relativeDirectory = DS . rtrim(dirname($relativeFile), '.');

		$name = Media::name($file);
		$filter = Configure::read('Media.filter.' . strtolower($name));

		foreach ($filter as $version => $instructions) {
			$directory = Folder::slashTerm($filterDirectory . $version . $relativeDirectory);

			$result = $Model->makeVersion($file, compact(
				'overwrite', 'directory', 'name', 'version', 'instructions'
			));
			if (!$result) {
				$message  = "GeneratorBehavior::make - Failed to make version `{$version}` ";
				$message .= "of file `{$file}`. ";
				trigger_error($message, E_USER_WARNING);
			}
		}
		return true;
	}

/**
 * Generate a version of a file
 *
 * $process an array with the following contents:
 *  overwrite - If the destination file should be overwritten if it exists
 *  directory - The destination directory (may not exist)
 *  name - Media name of $file (e.g. `'Image'`)
 *  version - The version requested to be processed (e.g. `l`)
 *  instructions - An array containing which names of methods to be called
 *
 * @param Model $Model
 * @param string $file Absolute path to the source file
 * @param array $process directory, version, name, instructions, overwrite
 * @return boolean `true` if version for the file was successfully stored
 */
	function makeVersion(&$Model, $file, $process) {
		extract($process);
		extract($this->settings[$Model->alias]);

		$Folder = new Folder($directory, $createDirectory, $createDirectoryMode);
		if (!$Folder->pwd()) {
			$message  = "GeneratorBehavior::generateVersion - Directory `{$directory}` ";
			$message .= "could not be created or is not writable. ";
			$message .= "Please check the permissions.";
			trigger_error($message, E_USER_WARNING);
			return false;
		}

		if (!$Media = Media::make($file, $instructions)) {
			return false;
		}
		return $Media->store($directory . basename($file), $overwrite);
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