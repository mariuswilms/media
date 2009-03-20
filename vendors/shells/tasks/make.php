<?php
/**
 * Make Task File
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
App::import('Vendor','Media.Medium');
/**
 * Make Task Class
 *
 * @package    media
 * @subpackage media.shells.tasks
 */
class MakeTask extends ManageShell {
	var $overwrite = false;
	var $directory;

	function execute() {
		if (isset($this->params['directory'])) {
			$this->directory = $this->params['directory'];
		} else {
			$this->directory = $this->in('Source Directory', null, MEDIA . 'static' . DS);
		}
		if (isset($this->params['overwrite'])) {
			$this->overwrite = true;
		}

		$this->out();
		$this->out($this->pad('Source:', 25) . $this->shortPath($this->directory));
		$this->out($this->pad('Destination:', 25) . $this->shortPath(MEDIA . 'filter' . DS));
		$this->out($this->pad('Overwrite existing:', 25), false). $this->out($this->overwrite ? 'yes' : 'no');

		$input = $this->in('Looks OK?',array('y','n'),'n');
		if($input == 'n') {
			return $this->main();
		}

		$this->heading('Making');
		$Folder = new Folder($this->directory);
		$files = $Folder->findRecursive();

		$this->progress(true, count($files));
		foreach ($files as $key => $file) {
			$this->progress($key, $this->shortPath($file));
			$this->_make($file);
		}
		$this->progress(false);
	}

	function _make($file) {
		$Medium = Medium::factory($file);
		if($Medium->name === 'Icon' || strpos($file,'ico'.DS) !== false) {
			return true;
		}

		$filter = Configure::read('Media.filter.' . strtolower($Medium->name));

		/* compiles all versions */
		foreach($filter as $version => $instructions) {
			$File = new File($file);
			$Medium = Medium::make($File->pwd(), $instructions);

			if (!$Medium) {
				//$this->warn('Failed to make version ' . $version . ' of medium.');
				continue(1);
			}

			/* Create directory */
			$directory = MEDIA
						 . 'filter'
						 . DS . $version
						 . DS . str_replace(array('\\','/'), DS, dirname(str_replace(MEDIA, null, $file)));

			$Folder = new Folder($directory, true);

			if (!$Folder->pwd()) {
				$this->err('Directory \'' . $directory . '\' could not be created or is not writable. Please check your permissions.');
				continue(1);
			}

			$destinationFile = $Folder->pwd(). DS . basename($file);

			$Medium->store($destinationFile, $this->overwrite);
		}
	}
}
?>