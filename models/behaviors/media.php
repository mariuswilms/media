<?php
/**
 * Media Behavior File
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
 * @subpackage media.models.behaviors
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor', 'Media.MimeType');
App::import('Vendor', 'Media.Medium');
/**
 * Media Behavior Class
 *
 * @package    media
 * @subpackage media.models.behaviors
 */
class MediaBehavior extends ModelBehavior {
/**
 * Settings keyed by model alias
 *
 * @var array
 */
	var $settings = array();
/**
 * Default settings
 *
 * metadataLevel
 * 	0 - (disabled) No retrieval of additional metadata
 *  1 - (basic) Adds `mime_type` and `size` fields
 *  2 - (detailed) Adds Multiple fields dependent on the type of the file e.g. `artist`, `title`
 *
 * baseDirectory
 * 	An absolute path (with trailing slash) to a directory which will be stripped off the file path
 *
 * makeVersions
 * 	false - Disable version generation
 * 	true  - Creates versions (configured in plugin's `core.php`) of files on create
 *
 * filterDirectory
 * 	An absolute path (with trailing slash) to a directory to use for storing generated versions
 *
 * createDirectory
 * 	false - Fail on missing directories
 * 	true  - Recursively create missing directories
 *
 * @var array
 */
	var $_defaultSettings = array(
		'metadataLevel'   => 1,
		'baseDirectory'   => MEDIA,
		'makeVersions'    => true,
		'filterDirectory' => MEDIA_FILTER,
		'createDirectory' => true,
	);
/**
 * Holds cached metadata keyed by model alias
 *
 * @var array
 * @access private
 */
	var $__cached;
/**
 * Setup
 *
 * @param Model $Model
 * @param array $config See defaultSettings for configuration options
 * @return void
 */
	function setup(&$Model, $config = null) {
		if (!is_array($config)) {
			$config = array();
		}

		/* `base` config option deprecation */
		if (isset($config['base'])) {
			$message  = "MediaBehavior::setup - ";
			$message .= "The `base` option has been deprecated in favour of `baseDirectory`.";
			trigger_error($message, E_USER_NOTICE);

			$config['baseDirectory'] = $config['base'];
			unset($config['base']);
		}

		/* Interact with Transfer Behavior */
		if (isset($Model->Behaviors->Transfer)) {
			$transferSettings = $Model->Behaviors->Transfer->settings[$Model->alias];
			$config['baseDirectory'] = dirname($transferSettings['baseDirectory']) . DS;
			$config['createDirectory'] = $transferSettings['createDirectory'];
		}

		$this->settings[$Model->alias] = $config + $this->_defaultSettings;
		$this->__cached[$Model->alias] = Cache::read('media_metadata_' . $Model->alias, '_cake_core_');
	}
/**
 * Callback
 *
 * Requires `file` field to be present if a record is created.
 *
 * Handles deletion of a record and corresponding file if the `delete` field is
 * present and has not a value of either `null` or `'0'.`
 *
 * Prevents `dirname`, `basename`, `checksum` and `delete` fields to be written to
 * database.
 *
 * Parses contents of the `file` field if present and generates a normalized path
 * relative to the path set in the `baseDirectory` option.
 *
 * @param Model $Model
 * @return boolean
 */
	function beforeSave(&$Model) {
		if (!$Model->exists()) {
			if (!isset($Model->data[$Model->alias]['file'])) {
				unset($Model->data[$Model->alias]);
				return true;
			}
		} else {
			if (isset($Model->data[$Model->alias]['delete'])
			&& $Model->data[$Model->alias]['delete'] !== '0') {
				$Model->delete();
				unset($Model->data[$Model->alias]);
				return true;
			}
		}

		$blacklist = array(
			'dirname', 'basename', 'checksum', 'delete'
		);
		$whitelist = array(
			'id', 'file', 'model', 'foreign_key',
			'created', 'modified', 'alternative'
		);

		foreach ($Model->data[$Model->alias] as $key => $value) {
			if (in_array($key, $whitelist)) {
				continue;
			}
			if (in_array($key, $blacklist)) {
				unset($Model->data[$Model->alias][$key]);
			}
		}

		extract($this->settings[$Model->alias]);

		if (isset($Model->data[$Model->alias]['file'])) {
			$File = new File($Model->data[$Model->alias]['file']);
			unset($Model->data[$Model->alias]['file']);

			/* `baseDirectory` may equal the file's directory or use backslashes */
			$dirname = substr(str_replace(
				str_replace('\\', '/', $baseDirectory),
				null,
				str_replace('\\', '/', Folder::slashTerm($File->Folder->pwd()))
			), 0, -1);

			$result = array(
				'checksum' => $File->md5(true),
				'dirname'  => $dirname,
				'basename' => $File->name,
			);

			$Model->data[$Model->alias] = array_merge($Model->data[$Model->alias], $result);
		}
		return true;
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

		if (!$created || !$makeVersions) {
			return true;
		}
		$item =& $Model->data[$Model->alias];

		if (!isset($item['dirname'], $item['basename'])) {
			return true;
		}
		return $this->make($Model, $item['dirname'] . DS . $item['basename']);
	}
/**
 * Callback
 *
 * Adds metadata of corresponding file to each result.
 *
 * If the corresponding file of a result is not readable it is removed
 * from the results array, as it is inconsistent. This can be fixed
 * by calling `cake media sync` from the command line.
 *
 * @param Model $Model
 * @param array $results
 * @param boolean $primary
 * @return array
 */
	function afterFind(&$Model, $results, $primary = false) {
		if (empty($results)) {
			return $results;
		}
		extract($this->settings[$Model->alias]);

		foreach ($results as $key => &$result) {
			/* Needed during a pre deletion phase */
			if (!isset($result[$Model->alias]['dirname'], $result[$Model->alias]['basename'])) {
				continue;
			}
			$file = $result[$Model->alias]['dirname'] . DS . $result[$Model->alias]['basename'];

			$metadata = $this->metadata($Model, $file, $metadataLevel);

			/* `metadata()` checks if the file is readable */
			if ($metadata === false) {
				unset($results[$key]);
				continue;
			}
			$result[$Model->alias] = array_merge($result[$Model->alias], $metadata);
		}
		return $results;
	}
/**
 * Callback
 *
 * Deletes file corresponding to record as well as generated versions of that file.
 *
 * If the file couldn't be deleted the callback won't stop the
 * delete operation to continue to delete the record.
 *
 * @param Model $Model
 * @param boolean $cascade
 * @return boolean
 */
	function beforeDelete(&$Model, $cascade = true) {
		extract($this->settings[$Model->alias]);

		$query = array(
			'conditions' => array('id' => $Model->id),
			'fields'     => array('dirname', 'basename'),
			'recursive'  => -1,
		);
		$result = $Model->find('first', $query);

		if (empty($result)) {
			return false; /* Record did not pass verification? */
		}

		$file  = $baseDirectory;
		$file .= $result[$Model->alias]['dirname'];
		$file .= DS . $result[$Model->alias]['basename'];

		$File = new File($file);
		$Folder = new Folder($filterDirectory);

		list($versions, ) = $Folder->ls();

		foreach ($versions as $version) {
			$Folder->cd($filterDirectory . $version	. DS . $result[$Model->alias]['dirname'] . DS);
			$basenames = $Folder->find($File->name() . '\..*');

			if (count($basenames) > 1) {
				$message  = "MediaBehavior::beforeDelete - Ambiguous filename ";
				$message .= "`" . $File->name() . "` in `" . $Folder->pwd() . "`.";
				trigger_error($message, E_USER_NOTICE);
				continue;
			} elseif (!isset($basenames[0])) {
				continue;
			}

			$FilterFile = new File($Folder->pwd() . $basenames[0]);
			$FilterFile->delete();
		}
		$File->delete();
		return true;
	}
/**
 * Parses instruction sets and invokes `Medium::make()` for a file
 *
 * @param Model $Model
 * @param string $file Path to a file relative to `baseDirectory`  or an absolute path to a file
 * @return boolean
 */
	function make(&$Model, $file, $overwrite = false) {
		extract($this->settings[$Model->alias]);

		list($file, $relativeFile) = $this->_file($Model, $file);

		$relativeDirectory = DS . rtrim(dirname($relativeFile), '.');

		$name = Medium::name($file);
		$filter = Configure::read('Media.filter.' . strtolower($name));

		$hasCallback = method_exists($Model, 'beforeMake');

		foreach ($filter as $version => $instructions) {
			$directory = Folder::slashTerm($filterDirectory . $version . $relativeDirectory);
			$Folder = new Folder($directory, $createDirectory);

			if (!$Folder->pwd()) {
				$message  = "MediaBehavior::make - Directory `{$directory}` ";
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
			if (!$Medium = Medium::make($file, $instructions)) {
				$message  = "MediaBehavior::make - Failed to make version `{$version}` ";
				$message .= "of file `{$file}`. ";
				trigger_error($message, E_USER_WARNING);
				continue;
			}
			$Medium->store($directory . basename($file), $overwrite);
		}
		return true;
	}

/**
 * Retrieve (cached) metadata of a file
 *
 * @param Model $Model
 * @param string $file Path to a file relative to `baseDirectory` or an absolute path to a file
 * @param integer $level level of amount of info to add, `0` disable, `1` for basic, `2` for detailed info
 * @return mixed Array with results or false if file is not readable
 */
	function metadata(&$Model, $file, $level = 1) {
		if ($level < 1) {
			return array();
		}
		extract($this->settings[$Model->alias]);

		list($file,) = $this->_file($Model, $file);
		$File = new File($file);

		if (!$File->readable()) {
			return false;
		}
		$checksum = $File->md5(true);

		if (isset($this->__cached[$Model->alias][$checksum])) {
			$data = $this->__cached[$Model->alias][$checksum];
		}

		if ($level > 0 && !isset($data[1])) {
			$data[1] = array(
				'size'      => $File->size(),
				'mime_type' => MimeType::guessType($File->pwd()),
				'checksum'  => $checksum,
			);
		}
		if ($level > 1 && !isset($data[2])) {
			$Medium = Medium::factory($File->pwd());

			if ($Medium->name === 'Audio') {
				$data[2] = array(
					'artist'        => $Medium->artist(),
					'album'         => $Medium->album(),
					'title'         => $Medium->title(),
					'track'         => $Medium->track(),
					'year'          => $Medium->year(),
					'length'        => $Medium->duration(),
					'quality'       => $Medium->quality(),
					'sampling_rate' => $Medium->samplingRate(),
					'bit_rate'       => $Medium->bitRate(),
				);
			} elseif ($Medium->name === 'Image') {
				$data[2] = array(
					'width'     => $Medium->width(),
					'height'    => $Medium->height(),
					'ratio'     => $Medium->ratio(),
					'quality'   => $Medium->quality(),
					'megapixel' => $Medium->megapixel(),
				);
			} elseif ($Medium->name === 'Text') {
				$data[2] = array(
					'characters'      => $Medium->characters(),
					'syllables'       => $Medium->syllables(),
					'sentences'       => $Medium->sentences(),
					'words'           => $Medium->words(),
					'flesch_score'    => $Medium->fleschScore(),
					'lexical_density' => $Medium->lexicalDensity(),
				);
			} elseif ($Medium->name === 'Video') {
				$data[2] = array(
					'title'   => $Medium->title(),
					'year'    => $Medium->year(),
					'length'  => $Medium->duration(),
					'width'   => $Medium->width(),
					'height'  => $Medium->height(),
					'ratio'   => $Medium->ratio(),
					'quality' => $Medium->quality(),
					'bit_rate' => $Medium->bitRate(),
				);
			} else {
				$data[2] = array();
			}
		}

		for ($i = $level, $result = array(); $i > 0; $i--) {
			$result = array_merge($result, $data[$i]);
		}
		$this->__cached[$Model->alias][$checksum] = $data;
		return Set::filter($result);
	}
/**
 * Checks if an alternative text is given only if a file is submitted
 *
 * @param unknown_type $Model
 * @param unknown_type $field
 * @return unknown
 */
	function checkRepresent(&$Model, $field) {
		if (!isset($Model->data[$Model->alias]['file'])) {
			return true;
		}
		$value = current($field); /* empty() limitation */
		return !empty($value);
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