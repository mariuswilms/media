<?php
/**
 * Make Task File
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
App::import('Vendor', 'Media.Medium');
/**
 * Make Task Class
 *
 * @package    media
 * @subpackage media.shells.tasks
 */
class MakeTask extends MediaShell {
/**
 * An absolute path to a file or directory
 *
 * @var string
 * @access public
 */
	var $source;
/**
 * An absolute path to a directory
 *
 * @var string
 * @access public
 */
	var $destination;
/**
 * Optionally holds the version string
 *
 * @var string
 * @access public
 */
	var $version;
/**
 * Force switch
 *
 * @var boolean
 * @access public
 */
	var $force;
/**
 * Overwrite existing files
 *
 * @var boolean
 * @access protected
 */
	var $_overwrite = false;
/**
 * Enable/disable creation of misssing directories
 *
 * @var boolean
 * @access protected
 */
	var $_createDirectories = false;
/**
 * Main execution methpd
 *
 * @access public
 * @return void
 */
	function execute() {
		$this->interactive = count($this->args) != 2;
		$this->force = isset($this->params['force']);
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

		if ($this->force) {
			$this->_overwrite = $this->_createDirectories = true;
		}
		if (isset($this->params['version'])) {
			$this->version = $this->params['version'];
		}

		$this->out();
		$this->out(sprintf('%-25s: %s', 'Source', $this->shortPath($this->source)));
		$this->out(sprintf('%-25s: %s', 'Destination', $this->shortPath($this->destination)));
		$this->out(sprintf('%-25s: %s', 'Overwrite existing', $this->_overwrite ? 'yes' : 'no'));
		$this->out(sprintf('%-25s: %s', 'Create directories', $this->_createDirectories ? 'yes' : 'no'));

		if ($this->in('Looks OK?', 'y,n', 'y') == 'n') {
			return false;
		}
		$this->out();
		$this->out('Making');
		$this->hr();

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
/**
 * "makes" a file
 *
 * @param string $file Absolute path to a file
 * @access protected
 * @return boolean
 */
	function _make($file) {
		$File = new File($file);
		$name = Medium::name($file);
		$subdir = array_pop(explode(DS, dirname($this->source)));

		if ($name === 'Icon' || strpos($file, 'ico' . DS) !== false) {
			return true;
		}

		if ($this->version) {
			$configString = 'Media.filter.' . strtolower($name) . '.' . $this->version;
			$filter = array(Configure::read($configString));
		} else {
			$configString = 'Media.filter.' . strtolower($name);
			$filter = Configure::read($configString);
		}

		foreach ($filter as $version => $instructions) {
			$directory = Folder::slashTerm(rtrim($this->destination . $version . DS . $subdir, '.'));
			$Folder = new Folder($directory, $this->_createDirectories);

			if (!$Folder->pwd()) {
				$this->err($directory . ' could not be created or is not writable.');
				$this->err('Please check your permissions.');
				return false;
			}

			$Medium = Medium::make($File->pwd(), $instructions);

			if (!$Medium) {
				$this->err('Failed to make version ' . $version . ' of medium.');
				return false;
			}
			$Medium->store($Folder->pwd() . $File->name, $this->_overwrite);
		}
		return true;
	}
}
?>