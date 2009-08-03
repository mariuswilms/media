<?php
/**
 * Coupler Behavior File
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
 * @copyright  2007-2009 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor', 'Media.MimeType');
App::import('Vendor', 'Media.Media');
/**
 * Coupler Behavior Class
 *
 * @package    media
 * @subpackage media.models.behaviors
 */
class CouplerBehavior extends ModelBehavior {
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
 * @var array
 */
	var $_defaultSettings = array(
		'metadataLevel'   => 1,
		'baseDirectory'   => MEDIA
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
 * @param array $settings See defaultSettings for configuration options
 * @return void
 */
	function setup(&$Model, $settings = array()) {
		$settings = (array)$settings;

		if (isset($Model->Behaviors->Transfer)) {
			$transferSettings = $Model->Behaviors->Transfer->settings[$Model->alias];
			$settings['baseDirectory'] = dirname($transferSettings['baseDirectory']) . DS;
		}
		$this->settings[$Model->alias] = array_merge($this->_defaultSettings, $settings);

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

		$result = $Model->find('first', array(
			'conditions' => array($Model->primaryKey => $Model->id),
			'fields'     => array('dirname', 'basename'),
			'recursive'  => -1,
		));
		if (empty($result)) {
			return false;
		}

		$count = $Model->find('count', array(
			'conditions' => array(
				'dirname' => $result[$Model->alias]['dirname'],
				'basename' => $result[$Model->alias]['basename']
		)));
		if ($count > 1) {
			return false;
		}

		$file  = $baseDirectory;
		$file .= $result[$Model->alias]['dirname'];
		$file .= DS . $result[$Model->alias]['basename'];

		$File = new File($file);
		$File->delete();
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
			$Media = Media::factory($File->pwd());

			if ($Media->name === 'Audio') {
				$data[2] = array(
					'artist'        => $Media->artist(),
					'album'         => $Media->album(),
					'title'         => $Media->title(),
					'track'         => $Media->track(),
					'year'          => $Media->year(),
					'length'        => $Media->duration(),
					'quality'       => $Media->quality(),
					'sampling_rate' => $Media->samplingRate(),
					'bit_rate'       => $Media->bitRate(),
				);
			} elseif ($Media->name === 'Image') {
				$data[2] = array(
					'width'     => $Media->width(),
					'height'    => $Media->height(),
					'ratio'     => $Media->ratio(),
					'quality'   => $Media->quality(),
					'megapixel' => $Media->megapixel(),
				);
			} elseif ($Media->name === 'Text') {
				$data[2] = array(
					'characters'      => $Media->characters(),
					'syllables'       => $Media->syllables(),
					'sentences'       => $Media->sentences(),
					'words'           => $Media->words(),
					'flesch_score'    => $Media->fleschScore(),
					'lexical_density' => $Media->lexicalDensity(),
				);
			} elseif ($Media->name === 'Video') {
				$data[2] = array(
					'title'   => $Media->title(),
					'year'    => $Media->year(),
					'length'  => $Media->duration(),
					'width'   => $Media->width(),
					'height'  => $Media->height(),
					'ratio'   => $Media->ratio(),
					'quality' => $Media->quality(),
					'bit_rate' => $Media->bitRate(),
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
		$value = current($field); /* empty() limitat
		* ion */
		return !empty($value);
	}
/**
 * Returns relative and absolute path to a file
 *
 * @param Model $Model
 * @param string$file
 * @return array
 * @todo Duplicate Code @see GeneratorBehavior
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