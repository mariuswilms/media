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
App::import('Behavior', 'Media.Polymorphic');

/**
 * Coupler Behavior Class
 *
 * @package    media
 * @subpackage media.models.behaviors
 */
class CouplerBehavior extends PolymorphicBehavior {

/**
 * Settings keyed by model alias
 *
 * @var array
 */
	var $settings = array();

/**
 * Default settings
 *
 * baseDirectory
 * 	An absolute path (with trailing slash) to a directory which will be stripped off the file path
 *
 * @var array
 */
	var $_defaultSettings = array(
		'baseDirectory'   => MEDIA
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

		if (isset($Model->Behaviors->Transfer)) {
			$transferSettings = $Model->Behaviors->Transfer->settings[$Model->alias];
			$settings['baseDirectory'] = dirname($transferSettings['baseDirectory']) . DS;
		}
		$settings['classField'] = 'model';
		$settings['foreignKey'] = 'foreign_key';

		$this->settings[$Model->alias] = array_merge($this->_defaultSettings, $settings);
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
		$value = current($field);
		return !empty($value);
	}
}
?>