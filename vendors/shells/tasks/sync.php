<?php
/**
 * Sync Task File
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
 * @subpackage media.shells.tasks
 * @copyright  2007-2009 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
/**
 * Sync Task Class
 *
 * @package    media
 * @subpackage media.shells.tasks
 */
class SyncTask extends ManageShell {
	var $modelName = null;
	var $uses = array();
	var $dbConnection = null;
	var $answer = 'n';

	function execute() {
		$this->_reset();
		$this->_config();

		if(isset($this->params['yes'])) {
			$this->answer = 'y';
		}

		if(!empty($this->args) && isset($this->params['connection'])) {
			$modelName = array_shift($this->args);
			$this->interactive = false;
		} else {
			$this->interactive = true;
		}

		if(isset($this->params['connection'])) {
			$dbConnection = $this->params['connection'];
		} else {
			$dbConnection = $this->_inDbConnection();
		}

		if(!isset($modelName)) {
			$modelName = $this->_inModelName($dbConnection);
		}

		$Model = ClassRegistry::init($modelName);

		// last chance to disable it
		if($this->interactive) {
			$input = strtoupper($this->in('Interactive?','y/n','y'));
			if($input == 'N') {
				$this->interactive = false;
			} else {
				$this->interactive = true;
			}
		}

		$this->out();
		$this->heading('Checking if files are in sync with records');

		list($fsMap, $dbMap) = $this->_generateMaps($Model);

		foreach($dbMap as $dbItem) {
			$this->out();
			$this->out($this->pad($this->shortPath($dbItem['file']), 60).' <<< '.$Model->name.'/'.$dbItem['id']);

			$File = new File($dbItem['file']);
			$alternativeFile = $this->_findByChecksum($dbItem['checksum'],$fsMap);
			if($this->_findByFile($alternativeFile,$dbMap)) {
				$alternativeFile = false;
			}

			if(!$File->readable() && $File->exists()) {
				$this->out("File exists but is not readable");

			} elseif(!$File->exists()) {
				$this->out("File does not exist");

				if($alternativeFile) {
					$this->out('This file has an identical checksum: '.$this->shortPath($alternativeFile));
					$input = $this->in("Select this file and update record?",'Y/N',$this->answer);
					if($input == 'y') {
						$data = array(
									'id' => $dbItem['id'],
									'dirname' => dirname(str_replace($Root->pwd(),'',$alternativeFile)),
									'basename' => basename($alternativeFile),
									);
						$Model->save($data);
						$this->out("Corrected dirname and basename");
						continue(1);
					}
				}

				$input = $this->in("Delete record?",'Y/N',$this->answer);
				if($input == 'y') {
					$Model->delete($dbItem['id']);
					$this->out("Record deleted");
					continue(1);
				}

			} elseif($dbItem['checksum'] != $File->md5()) {
				$this->out("Checksums mismatch");

				if($alternativeFile) {
					$this->out('This file has an identical checksum: '.$this->shortPath($alternativeFile));
					$input = $this->in("Select this file and update record?",'Y/N',$this->answer);
					if($input == 'y') {
						$data = array(
									'id' => $dbItem['id'],
									'dirname' => dirname(str_replace($Root->pwd(),'',$alternativeFile)),
									'basename' => basename($alternativeFile),
									);
						$Model->save($data);
						$this->out("Corrected dirname and basename");
						continue(1);
					}
				}

				$input = $this->in("Correct the checksum of the record?",'Y/N',$this->answer);
				if($input == 'y') {
					$data = array(
								'id' => $dbItem['id'],
								'checksum' => $File->md5(),
								);
					$Model->save($data);
					$this->out("Corrected checksum");
					continue(1);
				}

				$input = $this->in("Delete record?",'Y/N',$this->answer);
				if($input == 'y') {
					$Model->delete($dbItem['id']);
					$this->out("Record deleted");
					continue(1);
				}
			}
		}
		$this->out();
		$this->heading('Checking if records are in sync with files');
		// Refresh!
		list($fsMap,$dbMap) = $this->_generateMaps($Model);

		foreach($fsMap as $fsItem) {
			$this->out();
			$this->out($this->pad($this->shortPath($fsItem['file']), 60).' >>> '.$Model->name.'/??');

			$File = new File($fsItem['file']);

			if(!$this->_findByFile($fsItem['file'],$dbMap)) {
				$this->out("Orphaned");

				$input = $this->in("Delete file?",'Y/N',$this->answer);
				if($input == 'y') {
					$File->delete();
					$this->out("File deleted");
					continue(1);
				}

			}
		}
	}

	function _generateMaps(&$Model)
	{
		$Root = new Folder(MEDIA.'transfer');
		$fsFiles = $Root->findRecursive();

		foreach($fsFiles as $value) {
			$File = new File($value);
			$fsMap[] = array(
						'file' => $File->pwd(),
						'checksum' => $File->md5()
						);

		}

		$results = $Model->find('all');
		foreach($results as $result) {
			$dbMap[] = array(
							'id' => $result[$Model->name]['id'],
							'file' => MEDIA.$result[$Model->name]['dirname'].DS.$result[$Model->name]['basename'],
							'checksum' => $result[$Model->name]['checksum'],
							);


		}

		return array($fsMap,$dbMap);
	}

	function _findByChecksum($checksum,$map)
	{
		foreach($map as $item) {
			if($checksum == $item['checksum']) {
				return $item['file'];
			}
		}

		return false;
	}

	function _findByFile($file,$map)
	{
		foreach($map as $item) {
			if($file == $item['file']) {
				return $item['file'];
			}
		}

		return false;
	}

	function _inDbConnection()
	{
		$input = 'default';
		$connections = array_keys(get_class_vars('DATABASE_CONFIG'));

		if (count($connections) > 1) {
        	$input = $this->in(__('Use Database Connection', true) .':', $connections, 'default');
		}

		return $input;
	}

	function _config()
	{
		if (!is_dir(CONFIGS)) {
			$this->Project->execute();
		}

		if (!config('database')) {
			$this->err(__("Your database configuration was not found.", true));
			return false;
		}

		return true;

	}

	function _reset() {
		$this->dbConnection = null;
	}

	/**
	 * outputs the a list of possible models or controllers from database
	 *
	 * @param string $useDbConfig Database configuration name
	 * @access public
	 */
	function listAll($useDbConfig = 'default') {
		$this->_loadModels(); //

		$db =& ConnectionManager::getDataSource($useDbConfig);
		$usePrefix = empty($db->config['prefix']) ? '' : $db->config['prefix'];
		if ($usePrefix) {
			$tables = array();
			foreach ($db->listSources() as $table) {
				if (!strncmp($table, $usePrefix, strlen($usePrefix))) {
					$tables[] = substr($table, strlen($usePrefix));
				}
			}
		} else {
			$tables = $db->listSources();
		}
		if (empty($tables)) {
			$this->err(__('Your database does not have any tables.', true));
			$this->stop();
		}

		$this->__tables = $tables;
		$count = count($tables);
		$this->_modelNames = array();

		if ($this->interactive === true) {
			$this->out(__('Possible Models based on your current database:', true));
		}

		for ($i = 0; $i < $count; $i++) {
			$this->_modelNames[] = $this->_modelName($tables[$i]);
			if ($this->interactive === true) {
				$this->out($i + 1 . ". " . $this->_modelNames[$i]);
			}
		}

	}

	function _inModelName($useDbConfig = 'default')
	{
		$this->listAll($useDbConfig);

		$input = $this->in('Select Model',null,'q');

		if(isset($this->_modelNames[(intval($input) - 1)])) {
			return $this->_modelNames[(intval($input) - 1)];

		} elseif(strtoupper($input) == 'Q') {
			exit(0);

		} else {
			$this->out('Could not find Model '.$modelName.'.');
			$this->_inModelName();

		}
	}
}
?>