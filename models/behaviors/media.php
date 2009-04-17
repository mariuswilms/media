<?php
/**
 * Media Behavior File
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
 * @subpackage media.models.behaviors
 * @copyright  2007-2009 David Persson <davidpersson@qeweurope.org>
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
 * createDirectory
 * 	false - Fail on missing directories
 * 	true  - Recursively create missing directories
 * makeVersions
 * 	false - Disable version generation
 * 	true  - Creates versions (configured in plugin's core.php) of files on create
 * metadataLevel
 * 	0 - (disabled) No retrieval of additional metadata
 *  1 - (basic) Adds mime_type and size fields
 *  2 - (detailed) Adds Multiple fields dependent on the type of the file e.g. artist, title
 * base
 * 	Needs trailing slash (You shouldn't need to change this, markers are NOT supported)
 *
 * @var array
 */
	var $_defaultSettings = array(
			'createDirectory' => true,
			'makeVersions'    => true,
			'metadataLevel'   => 1,
			'base'            => MEDIA,
		);
/**
 * Setup
 *
 * @param object $model
 * @param array $config See defaultSettings for configuration options
 * @return void
 */
	function setup(&$model, $config = null) {
		if (!is_array($config)) {
			$this->settings[$model->alias] = $this->_defaultSettings;
		} else {
			$this->settings[$model->alias] = array_merge($this->_defaultSettings, $config);
		}

		if ($model->useTable) {
			$fields = array('dirname', 'basename', 'checksum');
			foreach ($fields as $key => $field) {
				if ($model->hasField($field)) {
					unset($fields[$key]);
				}
			}
			if (!empty($fields)) {
				trigger_error('MediaBehavior::setup - The model \'' . $model->name . '\' lacks the ' . implode(', ', $fields) . ' field(s).', E_USER_WARNING);
			}
		}
	}
/**
 * Callback
 *
 * @param object $model
 * @return boolean
 */
	function beforeSave(&$model) {
		if (!$model->exists()) {
			/* Clear all data if we are going to create a record and the file field is missing */
			if (!isset($model->data[$model->alias]['file'])) {
				unset($model->data[$model->alias]);
				return true;
			}
		} else {
			/* Handle deletion request */
			if (isset($model->data[$model->alias]['delete']) && $model->data[$model->alias]['delete'] !== '0') {
				$model->delete();
				unset($model->data[$model->alias]);
				return true;
			}
		}

		/* Ensure that no bad fields sneaked into the to-be-saved data */
		$blacklist = array('dirname', 'basename', 'checksum', 'delete');
		$whitelist = array('id', 'file', 'model', 'foreign_key', 'created', 'modified', 'alternative');

		foreach ($model->data[$model->alias] as $key => $value) {
			if (in_array($key, $whitelist)) {
				continue(1);
			}
			if (in_array($key, $blacklist)) {
				unset($model->data[$model->alias][$key]);
			}
		}

		extract($this->settings[$model->alias]);

		if (isset($model->data[$model->alias]['file'])) {
			$File = new File($model->data[$model->alias]['file']);
			unset($model->data[$model->alias]['file']);

			/* Convert directory separators to / and remove trailing slash */
			$dirname = substr(
							str_replace(array($base, DS), array(null, '/'),
							Folder::slashTerm($File->Folder->pwd())),
							0,-1
							);

			$result = array(
						'checksum' => $File->md5(),
						'dirname'  => $dirname,
						'basename' => $File->name,
					);

			$model->data[$model->alias] = array_merge($model->data[$model->alias],$result);
		}

		return true;
	}
/**
 * Callback
 *
 * @param object $model
 * @param bool $created
 * @return bool
 */
	function afterSave(&$model, $created) {
		extract($this->settings[$model->alias]);

		if (!$created || !$makeVersions) {
			return true;
		}
		if (!isset($model->data[$model->alias]['dirname']) || !isset($model->data[$model->alias]['basename'])) {
			return true; /* Do not fail */
		}

		return $this->make($model, $model->data[$model->alias]['dirname'] . DS . $model->data[$model->alias]['basename']);
	}
/**
 * Adds metadata of each medium to results
 *
 * @param object $model
 * @param array $results
 * @param bool $primary
 * @return array
 */
	function afterFind(&$model, $results, $primary = false) {
		if (empty($results)) {
			return $results;
		}

		extract($this->settings[$model->alias]);

		foreach ($results as $key => &$result) {
			if (!isset($result[$model->alias]['dirname']) || !isset($result[$model->alias]['basename'])) {
				continue(1); /* Needed in certain situations like a pre-delete */
			}

			/* Retrieve metadata */
			$metadata = $this->metadata(
										$model,
										$result[$model->alias]['dirname']
										. DS . $result[$model->alias]['basename'],
										$metadataLevel
										);

			if ($metadata === false) {
				/*
				 * metadata() returns false on nonexistent/nonreadable files
				 * this means that this record is inconsistent
				 */
				unset($results[$key]);
				continue(1);
			}

			$result[$model->alias] = array_merge($result[$model->alias],$metadata);
		}

		return $results;
	}
/**
 * Deletes file corresponding to record
 *
 * @param object $model
 * @param bool $cascade
 * @return bool
 */
	function beforeDelete(&$model, $cascade = true) {
		extract($this->settings[$model->alias]);

		$result = $model->find(
							'first',
							array(
								'conditions' => array('id' => $model->id),
								'fields'     => array('dirname', 'basename'),
								'recursive'  => -1,
								)
							);

		if (empty($result)) {
			return false; /* Record did not pass verification? */
		}

		$File = new File($base
						 . $result[$model->alias]['dirname']
						 . DS . $result[$model->alias]['basename']);


		$Folder = new Folder($base . 'filter' . DS);
		list($versions, ) = $Folder->ls();

		foreach ($versions as $version) {
			$Folder->cd(
						$base
						. 'filter'
						. DS . $version
						. DS . $result[$model->alias]['dirname'] . DS
						);

			$basenames = $Folder->find($File->name() . '\..*');

			if (count($basenames) > 1) {
				trigger_error('MediaBehavior::beforeDelete - Ambigious filename ' . $File->name() . ' in ' . $Folder->pwd() . '.', E_USER_NOTICE);
				continue(1);
			} elseif (!isset($basenames[0])) {
				continue(1);
			}

			$FilterFile = new File($Folder->pwd() . $basenames[0]);
			$FilterFile->delete();
		}

		$File->delete();
		return true; /* Always delete record */
	}
/**
 * Parses instruction sets and invokes Medium::make for a file
 *
 * @param object $model
 * @param string $file Path to a file relative to MEDIA or an absolute path to a file
 * @return bool
 */
	function make(&$model, $file, $overwrite = false) {
		extract($this->settings[$model->alias]);

		$file = str_replace(array('/', '\\'), DS, is_file($file) ? $file : $base . $file);
		$File = new File($file);

		$name = strtolower(Medium::name($File->pwd()));

		foreach (Configure::read('Media.filter.' . $name) as $version => $instructions) {
			$directory = rtrim($base . 'filter' . DS . $version . DS . dirname(str_replace($base, '', $file)), '.');
			$Folder = new Folder($directory, $createDirectory);

			if (!$Folder->pwd()) {
				trigger_error("MediaBehavior::make - Directory '{$directory}' could not be created or is not writable. Please check your permissions.", E_USER_WARNING);
				continue(1);
			}
			if (method_exists($model, 'beforeMake')
			&& $model->beforeMake($file, compact('overwrite', 'directory', 'name', 'version', 'instructions'))) {
				continue(1);
			}
			if (!$Medium = Medium::make($File->pwd(), $instructions)) {
				trigger_error("MediaBehavior::make - Failed to make version {$version} of medium.", E_USER_WARNING);
				continue(1);
			}
			$Medium->store($Folder->pwd() . DS . $File->name, $overwrite);
		}
		return true;
	}
/**
 * Retrieve metadata of a file
 *
 * @param object $model
 * @param string $file Path to a file relative to MEDIA or an absolute path to a file
 * @param int $level level of amount of info to add, 0 disable, 1 for basic, 2 for detailed info
 * @return mixed Array with results or false if file is not readable
 */
	function metadata(&$model, $file, $level = 1) {
		if ($level < 1) {
			return array();
		}

		extract($this->settings[$model->alias]);

		if (is_file($file)) {
			$File = new File($file);
		} else {
			$File = new File($base . $file);
		}

		if (!$File->exists() || !$File->readable()) {
			return false;
		}

		$basic = array(
					'size'      => $File->size(),
					'mime_type' =>  MimeType::guessType($File->pwd()),
					);

		if ($level < 2) {
			return Set::filter($basic); /* return basic info */
		}

		$Medium = Medium::factory($File->pwd());

		if ($Medium->name === 'Audio') {
			$detailed = array(
							'artist'        => $Medium->artist(),
							'album'         => $Medium->album(),
							'title'         => $Medium->title(),
							'track'         => $Medium->track(),
							'year'          => $Medium->year(),
							'length'        => $Medium->duration(),
							'quality'       => $Medium->quality(),
							'sampling_rate' => $Medium->samplingRate(),
							'bitrate'       => $Medium->bitrate(),
							);
		} elseif ($Medium->name === 'Image') {
			$detailed = array(
							'width'     => $Medium->width(),
							'height'    => $Medium->height(),
							'ratio'     => $Medium->ratio(),
							'quality'   => $Medium->quality(),
							'megapixel' => $Medium->megapixel(),
							);
		} elseif ($Medium->name === 'Text') {
			$detailed = array(
							'characters'      => $Medium->characters(),
							'syllables'       => $Medium->syllables(),
							'sentences'       => $Medium->sentences(),
							'words'           => $Medium->words(),
							'flesch_score'    => $Medium->fleschScore(),
							'lexical_density' => $Medium->lexicalDensity(),
							);
		} elseif ($Medium->name === 'Video') {
			$detailed = array(
							'title'   => $Medium->title(),
							'year'    => $Medium->year(),
							'length'  => $Medium->duration(),
							'width'   => $Medium->width(),
							'height'  => $Medium->height(),
							'ratio'   => $Medium->ratio(),
							'quality' => $Medium->quality(),
							'bitrate' => $Medium->bitrate(),
							);
		} else {
			$detailed = array();
		}

		return Set::filter(array_merge($basic, $detailed)); /* return enhanced info */
	}
/**
 * Checks if an alternative text is given only if a file is submitted
 *
 * @param unknown_type $model
 * @param unknown_type $field
 * @return unknown
 */
	function checkRepresent(&$model,$field) {
		if (!isset($model->data[$model->alias]['file'])) {
			return true;
		}
		$value = current($field);
		return !empty($value);
		if (!empty($value)) {
			return true;
		}
		return false;
	}
}
?>
