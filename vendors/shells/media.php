<?php
/**
 * Media Shell File
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
 * @subpackage media.shells
 * @copyright  2007-2009 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Core', array('ConnectionManager', 'Folder'));
require_once(APP . 'plugins' . DS . 'media'. DS . 'config' . DS . 'core.php');
Configure::write('Cache.disable', true);
/**
 * Media Shell Class
 *
 * @package    media
 * @subpackage media.shells
 */
class MediaShell extends Shell {
	var $tasks = array('Sync', 'Make', 'Collect');
	var $verbose = false;
	var $quiet = false;
/**
 * Width of shell in number of characters per line
 *
 * @var integer
 */
	var $width = 80;
/**
 * startup
 *
 * @access public
 * @return void
 */
	function startup() {
		$this->verbose = isset($this->params['verbose']);
		$this->quiet = isset($this->params['quiet']);
		parent::startup();
	}
/**
 * _welcome
 *
 * @access protected
 * @return void
 */
	function _welcome() {
		$this->clear();
		$this->hr();
		$this->out('Media Shell');
		$this->hr();
	}
/**
 * main
 *
 * @access public
 * @return void
 */
	function main() {
		$this->out('[I]nitialize Media Directory');
		$this->out('[P]rotect Transfer Directory');
		$this->out('[S]ynchronize');
		$this->out('[M]ake');
		$this->out('[C]ollect');
		$this->out('[H]elp');
		$this->out('[Q]uit');

		$action = strtoupper($this->in(__('What would you like to do?', true),
										array('I', 'P', 'S', 'M', 'C', 'H', 'Q'),'q'));

		switch ($action) {
			case 'I':
				$this->init();
			case 'P':
				$this->protect();
				break;
			case 'S':
				$this->Sync->execute();
				break;
			case 'M':
				$this->Make->execute();
				break;
			case 'C':
				$this->Collect->execute();
				break;
			case 'H':
				$this->help();
				break;
			case 'Q':
				$this->_stop();
		}
		$this->main();
	}

	function init() {
		if (is_dir(MEDIA)) {
			return true;
		}
		$this->out('The media root directory (' . $this->shortPath(MEDIA) . ') does not exist.');

		if ($this->in('Do you want to create initiate it now?', 'y,n', 'n') == 'n') {
			$this->out('Aborting.');
			return false;
		}
		$this->out('Initiating directory structure...');

		new Folder(MEDIA, true);

		foreach (array('transfer', 'filter', 'static') as $name) {
			$Folder = new Folder(MEDIA . $name, true);

			$this->out($this->shortPath($Folder->pwd()));

			foreach (Medium::short() as $subName) {
				$SubFolder = new Folder(MEDIA . $name . DS . $subName, true);
				$this->out($this->shortPath($SubFolder->pwd()));
			}
		}
		return true;
	}

	function protect() {
		if (is_file(MEDIA . 'transfer' . DS . '.htaccess')) {
			return true;
		}
		$this->out('Your transfer directory is missing a htaccess file to block requests.');

		if ($this->in('Do you want to create it now?', 'y,n', 'n') == 'n') {
			return false;
		}

		$File = new File(MEDIA . 'transfer' . DS . '.htaccess');
		$File->append("Order deny,allow\n");
		$File->append("Deny from all\n");
		$File->close();

		$this->out($this->shortPath($File->pwd()));
		return true;
	}
/**
 * Displays help contents
 *
 * @access public
 */
	function help() {
		// 63 chars ===============================================================
		$this->out("NAME");
		$this->out("\tmedia -- the 23rd shell");
		$this->out();
		$this->out("SYNOPSIS");
		$this->out("\tcake media.manage <params> <command> <args>");
		$this->out();
		$this->out("COMMANDS");
		$this->out("\tinit");
		$this->out("\t\tInitializes the media directory structure.");
		$this->out();
		$this->out("\tprotect");
		$this->out("\t\tCreates a htaccess file to protect the transfer dir.");
		$this->out();
		$this->out("\tcollect [-link] [-exclude name] [source]");
		$this->out("\t\tCollects files and copies them to the media dir.");
		$this->out();
		$this->out("\tsync [-yes] [-connection name] [modelname]");
		$this->out("\t\tChecks if records and files are in sync.");
		$this->out();
		$this->out("\t\t-connection name Database connection to use.");
		$this->out("\t\t-yes Always assumes 'y' as answer.");
		$this->out();
		$this->out("\tmake [-force] [-filter name] [source] [destination]");
		$this->out("\t\tProcess a file or directory according to filters.");
		$this->out();
		$this->out("\t\t-filter version Restrict command to a specfic filter version (e.g. xxl).");
		$this->out("\t\t-force Overwrite files if they exist.");
		$this->out();
		$this->out("\thelp");
		$this->out("\t\tShows this help message.");
		$this->out();
		$this->out("OPTIONS");
		$this->out("\t-verbose");
		$this->out("\t-quiet");
		$this->out();
	}

	/* Useful display methods */

/**
 * Outputs to the stdout filehandle.
 *
 * Overridden to enable quiet mode
 *
 * @param string $string String to output.
 * @param boolean $newline If true, the outputs gets an added newline.
 * @access public
 */
	function out($string = '', $newline = true) {
		if ($this->quiet) {
			return null;
		}
		$this->Dispatch->stdout($string, $newline);
	}
/**
 * clear
 *
 * @access public
 * @return void
 */
	function clear() {
		$this->out(chr(27).'[H'.chr(27).'[2J');
	}
/**
 * Returns a string padded to specified width
 *
 * @param string $string The string to pad
 * @param int $width Final length of string
 * @param string $character Character to be used for padding
 * @param string $align Alignment of $string. Either STR_PAD_LEFT, STR_PAD_BOTH or STR_PAD_RIGHT
 * @return string Padded string
 */
	function pad($string, $width, $character = ' ', $type = STR_PAD_RIGHT) {
		return str_pad($string, $width, $character, $type);
	}
/**
 * heading
 *
 * @param mixed $string
 * @param mixed $width
 * @param string $character
 * @access public
 * @return void
 */
	function heading($string, $width = null, $character = '=') {
		if (is_string($width)) {
			$character = $width;
			$width = null;
		}
		if ($width === null) {
			$width = $this->width;
		}
		$this->out($this->pad($string . ' ', $width, $character));
		$this->out();
	}
/**
 * Overridden
 *
 * @param string $character
 * @param mixed $width
 * @access public
 * @return void
 */
	function hr($character = '-', $width = null) {
		$this->out(str_repeat($character, $width === null ? $this->width : $width));
	}
/**
 * info
 *
 * @param mixed $message
 * @access public
 * @return void
 */
	function info($message) {
		if (!$this->verbose) {
			return null;
		}
		$this->out(sprintf(__('Notice: %s', true), $message), true);
	}
/**
 * warn
 *
 * @param mixed $message
 * @access public
 * @return void
 * @link /usr/lib/portage/bin/isolated-functions.sh
 */
	function warn($message) {
		/* Until Dispatcher does not prepend Error: */
		fwrite($this->Dispatch->stderr, sprintf(__('Warning: %s', true), $message)."\n");
	}
/**
 * Overridden
 *
 * Needed because ShellDispatcher prepends "Error:"
 *
 * @param mixed $message
 * @access public
 * @return void
 */
	function err($message) 	{
		fwrite($this->Dispatch->stderr, sprintf(__('Error: %s', true), $message)."\n");
		$this->_stop(1);
	}
/**
 * begin
 *
 * @param mixed $message
 * @access public
 * @return void
 */
	function begin($message) {
		$this->out(sprintf('%s ... ', $message), false);
	}
/**
 * end
 *
 * @param mixed $result
 * @access public
 * @return void
 */
	function end($result = null) {
		if ($result == true) {
			$message =  __('ok', true);
		} elseif (empty($result)) {
			$message = __('unknown', true);
		} else {
			$message = __('failed', true);
		}
		$this->out(sprintf('%s', $message));
		return $result;
	}
/**
 * progress
 *
 * Start with progress(target value)
 * Update with progress(current value, text)
 *
 * @param mixed $value
 * @param mixed $text
 * @access public
 * @return void
 */
	function progress($value, $text = null) {
		static $target = 0;

		if ($this->quiet) {
			return null;
		}

		if ($text === null) {
			$target = $value;
		} else {
			$out = sprintf('%\' 6.2f%% %s', ($value * 100) / $target, $text);
			$this->out($out);
		}
	}
/**
 * Overridden to allow Stop messages
 *
 * @param int $status
 * @access protected
 * @return void
 */
	function _stop($status = 0) {
		$this->out($status === 0 ? __('Quitting.', true) : __('Aborting.', true));
		parent::_stop($status);
	}
}
?>
