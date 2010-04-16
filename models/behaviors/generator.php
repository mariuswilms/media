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
App::import('Vendor', 'Media.Media');

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
 * 	An absolute path (with trailing slash) to a directory which will be stripped off the file path
 *
 * filterDirectory
 * 	An absolute path (with trailing slash) to a directory to use for storing generated versions
 *
 * createDirectory
 * 	false - Fail on missing directories
 * 	true  - Recursively create missing directories
 *
 * overwrite
 * 	false - Will fail if a version with the same already exists
 * 	true - Overwrites existing versions with the same name
 *
 * @var array
 */
	var $_defaultSettings = array(
		'baseDirectory'   => MEDIA,
		'filterDirectory' => MEDIA_FILTER,
		'createDirectory' => true,
		'overwrite' => false
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
 * Parses instruction sets and invokes `Media::make()` for a file
 *
 * @param Model $Model
 * @param string $file Path to a file relative to `baseDirectory`  or an absolute path to a file
 * @param boolean Whether to overwrite existing versions with the same name or not
 * @return boolean
 */
	function make(&$Model, $file, $overwrite = false) {
		extract($this->settings[$Model->alias]);

		list($file, $relativeFile) = $this->_file($Model, $file);

		$relativeDirectory = DS . rtrim(dirname($relativeFile), '.');

		$name = Media::name($file);
		$filter = Configure::read('Media.filter.' . strtolower($name));

		$hasCallback = method_exists($Model, 'beforeMake');

		foreach ($filter as $version => $instructions) {
			$directory = Folder::slashTerm($filterDirectory . $version . $relativeDirectory);
			$Folder = new Folder($directory, $createDirectory);

			if (!$Folder->pwd()) {
				$message  = "GeneratorBehavior::make - Directory `{$directory}` ";
				$message .= "could not be created or is not writable. ";
				$message .= "Please check the permissions.";
				trigger_error($message, E_USER_WARNING);
				continue;
			}

			if ($hasCallback) {
				$process = compact('overwrite', 'directory', 'name', 'version', 'instructions');

				if ($Model->beforeMake($file, $process)) {
					continue;
				}
			}
			if (!$Media = Media::make($file, $instructions)) {
				$message  = "GeneratorBehavior::make - Failed to make version `{$version}` ";
				$message .= "of file `{$file}`. ";
				trigger_error($message, E_USER_WARNING);
				continue;
			}
			$Media->store($directory . basename($file), $overwrite);
		}
		return true;
	}

/**
 * Returns relative and absolute path to a file
 *
 * @param Model $Model
 * @param string$file
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