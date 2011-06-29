<?php
/**
 * Meta Behavior File
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
App::import('Core', 'Inflector');
App::import('Core', 'Cache');
require_once 'Mime/Type.php';
require_once 'Media/Info.php';

/**
 * Coupler Behavior Class
 *
 * If you set metadataLevel to a value greater then zero, you’ll get additional
 * metadata on each consecutive find operation.
 *
 * To manually get metadata or generate versions of a certain file just call the
 * metadata or make method on the model which the behavior is attached to. This
 * even works if you don’t use the above table schema.
 *
 * {{{
 *      $result = $this->Document->metadata('/tmp/cern.jpg', 2);
 * }}}
 *
 * @package    media
 * @subpackage media.models.behaviors
 */
class MetaBehavior extends ModelBehavior {

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
 *  0 - (disabled) No retrieval of additional metadata
 *  1 - (basic) Adds `mime_type` and `size` fields
 *  2 - (detailed) Queries an `Media_Info` object for all available fields
 *
 * @var array
 */
	var $_defaultSettings = array(
		'level' => 1
	);

/**
 * Holds cached metadata keyed by model alias
 *
 * @var array
 * @access private
 */
	var $__cached = array();

/**
 * Setup behavior settings and cached metadata for the current model
 *
 * @param Model $Model
 * @param array $settings See defaultSettings for configuration options
 * @return void
 */
	function setup(&$Model, $settings = array()) {
		$this->settings[$Model->alias] = array_merge($this->_defaultSettings, (array)$settings);
		$this->__cached[$Model->alias] = Cache::read('media_metadata_' . $Model->alias);
	}

/**
 * Write cached data on a per model basis
 *
 * @return void
 */
	function __destruct() {
		foreach ($this->__cached as $alias => $data) {
			if ($data) {
				Cache::write('media_metadata_' . $alias, $data);
			}
		}
	}

/**
 * Callback
 *
 * Adds metadata to be stored in table if a record is about to be created.
 *
 * @param Model $Model
 * @return boolean
 */
	function beforeSave(&$Model) {
		if ($Model->exists() || !isset($Model->data[$Model->alias]['file'])) {
			return true;
		}
		extract($this->settings[$Model->alias]);

		$Model->data[$Model->alias] += $this->metadata(
			$Model, $Model->data[$Model->alias]['file'], $level
		);
		return true;
	}

/**
 * Callback
 *
 * Adds metadata of corresponding file to each result.
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
			if (!isset($result[$Model->alias]['file'])) {
				continue;
			}
			$metadata = $this->metadata($Model, $result[$Model->alias]['file'], $level);

			if ($metadata) {
				$result[$Model->alias] = array_merge($result[$Model->alias], $metadata);
			}
		}
		return $results;
	}

/**
 * Retrieve (cached) metadata of a file
 *
 * @param Model $Model
 * @param string $file An absolute path to a file
 * @param integer $level level of amount of info to add, `0` disable, `1` for basic, `2` for detailed info
 * @return mixed Array with results or false if file is not readable
 */
	function metadata(&$Model, $file, $level = 1) {
		if ($level < 1) {
			return array();
		}
		extract($this->settings[$Model->alias]);
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
				'mime_type' => Mime_Type::guessType($File->pwd()),
				'checksum'  => $checksum,
			);
		}
		if ($level > 1 && !isset($data[2])) {
			$data[2] = array();

			try {
				$Info = Media_Info::factory(array('source' => $File->pwd()));

				foreach ($Info->all() as $key => $value) {
					$data[2][Inflector::underscore($key)] = $value;
				}
			} catch (Exception $E) {}
		}

		for ($i = $level, $result = array(); $i > 0; $i--) {
			$result = array_merge($result, $data[$i]);
		}
		$this->__cached[$Model->alias][$checksum] = $data;
		return $result;
	}
}

?>
