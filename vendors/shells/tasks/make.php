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
 * @copyright  2007-2009 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor', 'Media.Medium');
/**
 * Make Task Class
 *
 * @package    media
 * @subpackage media.shells.tasks
 */
class MakeTask extends ManageShell {
	var $overwrite = false;
	var $createDirectories = false;
	var $source;
	var $destination;
	var $filter;

	function execute() {
		if (count($this->args) == 2) {
			$this->interactive = false;
		}

		$this->source = array_shift($this->args);
		$this->destination = array_shift($this->args);

		if (!isset($this->source)) {
			$this->source = $this->in('Source File/Directory', null, MEDIA . 'static' . DS);
		}
		if (is_dir($this->source)) {
			$this->source = Folder::slashTerm($this->source);
		}
		if (!isset($this->destination)) {
			$this->destination = $this->in('Destination Directory', null, MEDIA . 'filter' . DS);
		}
		$this->destination = Folder::slashTerm($this->destination);

		if (isset($this->params['force'])) {
			$this->overwrite = $this->createDirectories = true;
		}

		if (isset($this->params['filter'])) {
			$this->filter = $this->params['filter'];
		}

		$this->out();
		$this->out($this->pad('Source:', 25) . $this->shortPath($this->source));
		$this->out($this->pad('Destination:', 25) . $this->shortPath($this->destination));
		$this->out($this->pad('Overwrite existing:', 25), false). $this->out($this->overwrite ? 'yes' : 'no');
		$this->out($this->pad('Create Directories:', 25), false). $this->out($this->createDirectories ? 'yes' : 'no');

		if ($this->in('Looks OK?', array('y','n'), 'y') == 'n') {
			return $this->main();
		}
		$this->heading('Making');

		if (is_file($this->source)) {
			$files = array($this->source);
		} else {
			$Folder = new Folder($this->source);
			$files = $Folder->findRecursive();
		}

		$this->progress(count($files));

		foreach ($files as $key => $file) {
			$this->progress($key, $this->shortPath($file));
			$this->_make($file);
		}
		$this->out();
	}

	function _make($file) {
		$File = new File($file);
		$name = Medium::name($file);
		$subdir = array_pop(explode(DS, dirname($this->source)));

		if ($name === 'Icon' || strpos($file, 'ico' . DS) !== false) {
			return true;
		}

		if (isset($this->filter)) {
			$filter = array(Configure::read('Media.filter.' . strtolower($name) . '.' . $this->version));
		} else {
			$filter = Configure::read('Media.filter.' . strtolower($name));
		}

		foreach ($filter as $version => $instructions) {
			$directory = Folder::slashTerm(rtrim($this->destination . $version . DS . $subdir, '.'));
			$Folder = new Folder($directory, $this->createDirectories);

			if (!$Folder->pwd()) {
				$this->warn('Directory \'' . $directory . '\' could not be created or is not writable. Please check your permissions.');
				return false;
			}

			$Medium = Medium::make($File->pwd(), $instructions);

			if (!$Medium) {
				$this->warn('Failed to make version ' . $version . ' of medium.');
				return false;
			}
			$Medium->store($Folder->pwd() . $File->name, $this->overwrite);
		}
		return true;
	}
}
?>
