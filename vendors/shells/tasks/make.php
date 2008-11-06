<?php
/**
 * MakeTask file
 * 
 * Copyright (c) $CopyrightYear$ David Persson
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 * @author 			David Persson <davidpersson at qeweurope dot org>
 * @copyright 		David Persson <davidpersson at qeweurope dot org>
 * @link			http://cakeforge.org/projects/attm Attm Project
 * @package 		media
 * @subpackage  	media.shells.tasks
 * @since			media plugin 0.50
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 * @license 		http://www.opensource.org/licenses/mit-license.php The MIT License
 */
App::import('Vendor','Media.Medium');
/**
 * MakeTask
 * 
 * @package 		media
 * @subpackage  	media.shells.tasks
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