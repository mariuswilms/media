<?php
/**
 * Sync Task File
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
 * @subpackage media.shells.tasks
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
/**
 * Sync Task Class
 *
 * @package    media
 * @subpackage media.shells.tasks
 */
class SyncTask extends MediaShell {
/**
 * model
 *
 * @var string
 * @access public
 */
	var $model;
/**
 * Directory
 *
 * @var string
 * @access public
 */
	var $directory;
/**
 * Default answer to use if prompted for input
 *
 * @var string
 * @access protected
 */
	var $_answer = 'n';
/**
 * Model
 *
 * @var Model
 * @access protected
 */
	var $_Model;
/**
 * baseDirectory from the model's media behavior settings
 *
 * @var string
 * @access protected
 */
	var $_baseDirectory;
/**
 * Folder to search
 *
 * @var Folder
 * @access protected
 */
	var $_Folder;
/**
 * Current item retrieved from the model
 *
 * @var array
 * @access private
 */
	var $__dbItem;
/**
 * Current item retrieved from the filesystem
 *
 * @var array
 * @access private
 */
	var $__fsItem;
/**
 * Current set of items retrieved from the model
 *
 * @var array
 * @access private
 */
	var $__dbMap;
/**
 * Current set of items retrieved from the filesystem
 *
 * @var array
 * @access private
 */
	var $__fsMap;
/**
 * Current file object
 *
 * @var File
 * @access private
 */
	var $__File;
/**
 * An alternative for the current file
 *
 * @var mixed
 * @access private
 */
	var $__alternativeFile;
/**
 * Main execution method
 *
 * @access public
 * @return boolean
 */
	function execute() {
		$this->_answer = isset($this->params['auto']) ? 'y' : 'n';
		$this->model = array_shift($this->args);
		$this->directory = array_shift($this->args);

		if (!isset($this->model)) {
			$this->model = $this->in('Name of model:', null, 'Media.Attachment');
		}
		if (!isset($this->directory)) {
			$this->directory = $this->in('Directory to search:', null, MEDIA_TRANSFER);
		}

		$this->_Model = ClassRegistry::init($this->model);

		if (!isset($this->_Model->Behaviors->Media)) {
			$this->err('MediaBehavior is not attached to Model');
			return false;
		}
		$this->_baseDirectory = $this->_Model->Behaviors->Media->settings[$this->_Model->alias]['baseDirectory'];
		$this->_Folder = new Folder($this->directory);
		$this->interactive = isset($this->model, $this->directory);

		if ($this->interactive) {
			$input = $this->in('Interactive?', 'y/n', 'y');

			if ($input == 'n') {
				$this->interactive = false;
			}
		}

		$this->out();
		$this->out(sprintf('%-25s: %s', 'Model', $this->_Model->name));
		$this->out(sprintf('%-25s: %s', 'Search directory', $this->shortPath($this->_Folder->pwd())));
		$this->out(sprintf('%-25s: %s', 'Automatic repair', $this->_answer == 'y' ? 'yes' : 'no'));

		if ($this->in('Looks OK?', 'y,n', 'y') == 'n') {
			return false;
		}
		$this->_Model->Behaviors->disable('Media');
		$this->_checkFilesWithRecords();
		$this->_checkRecordsWithFiles();
		$this->_Model->Behaviors->enable('Media');
		$this->out();
		return true;
	}
/**
 * Checks if files are in sync with records
 *
 * @access protected
 * @return void
 */
	function _checkFilesWithRecords() {
		$this->out();
		$this->out('Checking if files are in sync with records');
		$this->hr();

		list($this->__fsMap, $this->__dbMap) = $this->_generateMaps();

		foreach ($this->__dbMap as $dbItem) {
			$message = sprintf(
				'%-60s -> %s/%s',
				$this->shortPath($dbItem['file']),
				$this->_Model->name, $dbItem['id']
			);
			$this->out();
			$this->out($message);

			$this->__dbItem = $dbItem;
			$this->__File = new File($dbItem['file']);
			$this->__alternativeFile = $this->_findByChecksum($dbItem['checksum'], $this->__fsMap);

			if ($this->_findByFile($this->__alternativeFile, $this->__dbMap)) {
				$this->__alternativeFile = false;
			}

			if ($this->_handleNotReadable()) {
				continue;
			}
			if ($this->_handleOrphanedRecord()) {
				continue;
			}
			if ($this->_handleChecksumMismatch()) {
				continue;
			}
		}
	}
/**
 * Checks if records are in sync with files
 *
 * @access protected
 * @return void
 */
	function _checkRecordsWithFiles() {
		$this->out();
		$this->out('Checking if records are in sync with files');
		$this->hr();

		list($this->__fsMap, $this->__dbMap) = $this->_generateMaps();

		foreach ($this->__fsMap as $fsItem) {
			$message = sprintf(
				'%-60s <- %s/%s',
				$this->shortPath($fsItem['file']),
				$this->_Model->name,
				'?'
			);
			$this->out();
			$this->out($message);

			$this->__File = new File($fsItem['file']);
			$this->__fsItem = $fsItem;

			if ($this->_handleOrphanedFile()) {
				continue;
			}
		}
	}

	/* handle methods */

/**
 * Handles existent but not readable files
 *
 * @access protected
 * @return mixed
 */
	function _handleNotReadable() {
		if (!$this->__File->readable() && $this->__File->exists()) {
			$this->out('File exists but is not readable');
			return true;
		}
	}
/**
 * Handles orphaned records
 *
 * @access protected
 * @return mixed
 */
	function _handleOrphanedRecord() {
		if ($this->__File->exists()) {
			return;
		}
		$this->out('Orphaned');

		if ($this->_fixWithAlternative()) {
			return true;
		}
		if ($this->_fixDeleteRecord()) {
			return true;
		}
		return false;
	}
/**
 * Handles mismatching checksums
 *
 * @access protected
 * @return mixed
 */
	function _handleChecksumMismatch() {
		if ($this->__dbItem['checksum'] == $this->__File->md5(true)) {
			return;
		}
		$this->out('Checksums mismatch');

		if ($this->_fixWithAlternative()) {
			return true;
		}
		$input = $this->in('Correct the checksum of the record?', 'y,n', $this->_answer);

		if ($input == 'y') {
			$data = array(
				'id' => $this->__dbItem['id'],
				'checksum' => $this->__File->md5(true),
			);
			$this->_Model->save($data);
			$this->out('Corrected checksum');
			return true;
		}

		if ($this->_fixDeleteRecord()) {
			return true;
		}
	}
/**
 * Handles orphaned files
 *
 * @access protected
 * @return mixed
 */
	function _handleOrphanedFile() {
		if ($this->_findByFile($this->__fsItem['file'], $this->__dbMap)) {
			return;
		}
		$this->out('Orphaned');

		$input = $this->in('Delete file?', 'y,n', $this->_answer);

		if ($input == 'y') {
			$File->delete();
			$this->out('File deleted');
			return true;
		}
	}

	/* fix methods */

/**
 * Updates a record with an alternative file
 *
 * @access protected
 * @return boolean
 */
	function _fixWithAlternative() {
		if (!$this->__alternativeFile) {
			return false;
		}
		$message = sprintf(
			'This file has an identical checksum: %s',
			$this->shortPath($this->__alternativeFile)
		);
		$this->out($message);
		$input = $this->in('Select this file and update record?', 'y,n', $this->_answer);

		if ($input == 'n') {
			return false;
		}

		$data = array(
			'id' => $this->__dbItem['id'],
			'dirname' => dirname(str_replace($this->_baseDirectory, '', $this->__alternativeFile)),
			'basename' => basename($this->__alternativeFile),
		);
		$this->_Model->save($data);
		$this->out('Corrected dirname and basename');
		return true;
	}
/**
 * Deletes current record
 *
 * @access protected
 * @return booelan
 */
	function _fixDeleteRecord() {
		$input = $this->in('Delete record?', 'y,n', $this->_answer);

		if ($input == 'y') {
			$this->_Model->delete($this->__dbItem['id']);
			$this->out('Record deleted');
			return true;
		}
		return false;
	}

	/* map related methods */

/**
 * Generates filesystem and model maps
 *
 * @access protected
 * @return void
 */
	function _generateMaps() {
		$fsFiles = $this->_Folder->findRecursive();
		$results = $this->_Model->find('all');
		$fsMap = array();
		$dbMap = array();

		foreach ($fsFiles as $value) {
			$File = new File($value);
			$fsMap[] = array(
				'file' => $File->pwd(),
				'checksum' => $File->md5(true)
			);
		}
		foreach ($results as $result) {
			$dbMap[] = array(
				'id' => $result[$this->_Model->name]['id'],
				'file' => $this->_baseDirectory
						. $result[$this->_Model->name]['dirname']
						. DS . $result[$this->_Model->name]['basename'],
				'checksum' => $result[$this->_Model->name]['checksum'],
			);
		}
		return array($fsMap, $dbMap);
	}
/**
 * Finds an item's file by it's checksum
 *
 * @param string $checksum
 * @param array $map Map to use as a haystack
 * @access protected
 * @return mixed
 */
	function _findByChecksum($checksum, $map) {
		foreach ($map as $item) {
			if ($checksum == $item['checksum']) {
				return $item['file'];
			}
		}
		return false;
	}
/**
 * Finds an item's file by it's name
 *
 * @param string $file
 * @param array $map Map to use as a haystack
 * @access protected
 * @return mixed
 */
	function _findByFile($file, $map) {
		foreach ($map as $item) {
			if ($file == $item['file']) {
				return $item['file'];
			}
		}
		return false;
	}
}
?>