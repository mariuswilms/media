<?php
/**
 * Coupler Behavior File
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

/**
 * Coupler Behavior Class
 *
 * Your model needs to be bound to a table. The table must have at least the
 * dirname, basename fields to make that work. Below you’ll find some example
 * SQL to alter an existent table.
 *
 * {{{
 *     ALTER TABLE `movies`
 *     ADD COLUMN `dirname` varchar(255) NOT NULL,
 *     ADD COLUMN `basename` varchar(255) NOT NULL,
 * }}}
 *
 * If you now save a record with a field named file which must contain an absolute
 * path to a file, is the path made relative (using the base path provided) and
 * then split into the dirname and basename parts which end up in the
 * corresponding fields. This way you won’t have any absolute paths in your
 * table which is more flexible (e.g. when relocating the folder with the media
 * files).
 *
 * Keeping files in sync with their records and vice versa can sometimes get
 * cumbersome. The SyncTask makes ensuring integrity easy. Just invoke it with the
 * following command from shell:
 * $cake media sync
 *
 * For more information on options and arguments for the task call:
 * $cake media help
 *
 * @see SyncTask
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
 * baseDirectory
 *   An absolute path (with trailing slash) to a directory which will be stripped off the file path
 *
 * @var array
 */
	var $_defaultSettings = array(
		'baseDirectory' => MEDIA_TRANSFER
	);

/**
 * Setup
 *
 * @param Model $Model
 * @param array $settings See defaultSettings for configuration options
 * @return void
 */
	function setup(&$Model, $settings = array()) {
		$this->settings[$Model->alias] = (array) $settings + $this->_defaultSettings;
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

			/* `baseDirectory` may equal the file's directory or use backslashes */
			$dirname = substr(str_replace(
				str_replace('\\', '/', $baseDirectory),
				null,
				str_replace('\\', '/', Folder::slashTerm($File->Folder->pwd()))
			), 0, -1);

			$result = array(
				'dirname'  => $dirname,
				'basename' => $File->name,
			);

			$Model->data[$Model->alias] = array_merge($Model->data[$Model->alias], $result);
		}
		return true;
	}

/**
 * Callback, deletes file (if there's one coupled) corresponding to record. If
 * the file couldn't be deleted the callback will stop the delete operation and
 * not continue to delete the record.
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
		if (!$result[$Model->alias]['dirname'] || !$result[$Model->alias]['basename']) {
			return true;
		}

		$file  = $baseDirectory;
		$file .= $result[$Model->alias]['dirname'];
		$file .= DS . $result[$Model->alias]['basename'];

		$File = new File($file);
		return $File->delete();
	}

/**
 * Callback, adds the `file` field to each result.
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
			if (!isset($result[$Model->alias]['dirname'], $result[$Model->alias]['basename'])) {
				continue;
			}
			$file  = $baseDirectory;
			$file .= $result[$Model->alias]['dirname'];
			$file .= DS . $result[$Model->alias]['basename'];
			$file = str_replace(array('\\', '/'), DS, $file);

			$result[$Model->alias]['file'] = $file;
		}
		return $results;
	}

/**
 * Checks if an alternative text is given only if a file is submitted
 *
 * @param Model $Model
 * @param array $field
 * @return boolean
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
