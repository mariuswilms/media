<?php
/**
 * Generator Behavior File
 *
 * Copyright (c) 2007-2011 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.3
 *
 * @package    media
 * @subpackage media.models.behaviors
 * @copyright  2007-2011 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
require_once 'Media/Process.php';
require_once 'Mime/Type.php';

/**
 * Generator Behavior Class
 *
 * The generation of files is handled by the make method. It is either manually
 * triggered or when a to-be-saved record contains a file field with an absolute
 * path to a file. The model the behavior is attached to doesn’t necessarily need
 * to be bound to a table.
 *
 * To connect TransferBehavior and GeneratorBehavior with each other it is important
 * to specify TransferBehavior before GeneratorBehavior:
 * {{{
 *     var $actAs = array(
 *         'Media.Transfer',
 *         'Media.Generator'
 *     );
 * }}}
 *
 * Please note that this behavior *will not* delete generated versions
 * automatically. See docs/FAQ for an in depth explanation and docs/TUTORIAL
 * for a snippet you can use to implement this functionality.
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
 * If you already have generated versions of files and change the filter
 * configuration afterwards you may want to recreate those files with the new
 * settings.
 *
 * You can achieve that by removing already generated files first (optional), than
 * invoking the task from the shell:
 * $ cake media make
 *
 * For more information on options and arguments for the task call:
 * $ cake media help
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
				$message  = "GeneratorBehavior::make - Directory `{$directory}` ";
				$message .= "could not be created or is not writable. ";
				$message .= "Please check the permissions.";
				trigger_error($message, E_USER_WARNING);
				$result = false;
				continue;
			}

			try {
				$result = $Model->makeVersion($file, compact('version', 'directory', 'instructions'));
			} catch (Exception $E) {
				$message  = "GeneratorBehavior::make - While making version `{$version}` ";
				$message .= "of file `{$file}` an exception was thrown, the message provided ";
				$message .= 'was `' . $E->getMessage() . '`. Skipping version.';
				trigger_error($message, E_USER_WARNING);
				$result = false;
			}
			if (!$result) {
				$message  = "GeneratorBehavior::make - The method responsible for making version ";
				$message .= "`{$version}` of file `{$file}` returned `false`. Skipping version.";
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
 *  - `instructions`: An array specifying processing steps to execute on $file
 *                    in order to get to the desired transformed file.
 *
 *                    Each instruction is either a key/value pair where the key
 *                    can be thought of the method and the value the arguments
 *                    passed to that method. Whenever a value appears without a
 *                    corresponding string key it is used as the method instead.
 *
 *                    `array('name of method', 'name of other method')`
 *                    `array('name of method' => array('arg1', 'arg2'))`
 *
 *                    Most methods are made available through the `Media_Process_*`
 *                    classes. The class is chosen depending on the type of media
 *                    being processed. Since each one of those classes exposes
 *                    different methods the availaibility of those depends on the
 *                    type of media being processed.
 *
 *                    Please see the documentation for the mm library for further
 *                    information on the `Media_Process_*` classes mentioned above.
 *
 *                    However some methods are builtin and made available directly
 *                    through this method here. One of them being the `clone` method.
 *                    Cloning allows instructions which don't actually modify a file
 *                    but represent just a copy of it. Available clone types are `copy`,
 *                    `link` and `symlink`.
 *
 *                    `array('clone' => <type>)`
 *
 *                    In case an instruction method is neither builtin nor available
 *                    through one of the `Media_Proces_*` classes, the `passthru()`
 *                    method is invoked on that media object. The concrete implementation
 *                    of `passthru()` and therefore how it deals with the data passed
 *                    to it *highly* depends on the adapter in use.
 *
 * @link https://github.com/davidpersson/mm The PHP media library.
 * @param Model $Model
 * @param string $file Absolute path to the source file
 * @param array $process directory, version, instructions
 * @return boolean `true` if version for the file was successfully stored
 */
	function makeVersion(&$Model, $file, $process) {
		extract($this->settings[$Model->alias]);

		/* Process builtin instructions */
		if (isset($process['instructions']['clone'])) {
			$action = $process['instructions']['clone'];

			if (!in_array($action, array('copy', 'link', 'symlink'))) {
				return false;
			}

			$destination = $this->_destinationFile($file, $process['directory'], null, $overwrite);

			if (!$destination) {
				return false;
			}
			if (!call_user_func($action, $file, $destination)) {
				return false;
			}
			return $action == 'copy' ? chmod($destination, $mode) : true;
		}

		/* Process `Media_Process_*` instructions */
		$Media = Media_Process::factory(array('source' => $file));
		foreach ($process['instructions'] as $method => $args) {
			if (is_int($method)) {
				$method = $args;
				$args = null;
			}
			if (method_exists($Media, $method)) {
				$result = call_user_func_array(array($Media, $method), (array) $args);
			} else {
				$result = $Media->passthru($method, $args);
			}
			if ($result === false) {
				return false;
			} elseif (is_a($result, 'Media_Process_Generic')) {
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

/**
 * Helper method to determine path to destination file and delete
 * it if necessary.
 *
 * @param string $source Path to or name of source file.
 * @param string $directory Path to directory.
 * @param string $extension Optionally an extension to append to the final path.
 * @param boolean $overwrite If true will unlink destination if it exists, defaults to false.
 * @return string Path to destination file.
 */
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
