<?php
/**
 * Manage Shell File
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
 * @copyright  2007-2009 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Core', array('ConnectionManager', 'Folder'));
require_once(APP . 'plugins' . DS . 'media'. DS . 'config' . DS . 'core.php');
Configure::write('Cache.disable', true);
/**
 * Manage Shell Class
 *
 * @package    media
 * @subpackage media.shells
 */
class ManageShell extends Shell {
	var $tasks = array('Sync', 'Make');
	var $verbose = false;
	var $quiet = false;
/**
 * Width of shell in number of characters per line
 *
 * @var integer
 */
	var $width = 80;
/**
 * _welcome
 *
 * @access protected
 * @return void
 */
	function _welcome() {
		$this->clear();
		$this->heading(__('Media Plugin Manage Shell', true), null, '~');
	}
/**
 * main
 *
 * @access public
 * @return void
 */
	function main() {
		if (isset($this->params['verbose'])) {
			$this->verbose = true;
		}
		if (isset($this->params['quiet'])) {
			$this->quiet = true;
		}

		$this->out('[S]ynchronize');
		$this->out('[M]ake');
		$this->out('[H]elp');
		$this->out('[Q]uit');

		$action = strtoupper($this->in(__('What would you like to do?', true), array('S', 'M', 'H', 'Q'),'q'));
		switch($action) {
			case 'S':
				$this->Sync->execute();
				break;
			case 'M':
				$this->Make->execute();
				break;
			case 'H':
				$this->help();
				break;
			case 'Q':
				$this->_stop();
		}
		$this->main();
	}
/**
 * Displays help contents
 *
 * @access public
 */
	function help() {
		// 63 chars ===============================================================
		$this->out('Checks if files in filesystem are in sync with records.');
		$this->hr();
		$this->out('Usage: cake <params> media.manage <command> <args>');
		$this->hr();
		$this->out('Params:');
		$this->out("\t-connection <name> Database connection to use.");
		$this->out("\t-yes Always assumes 'y' as answer.");
		$this->out("\t-filter <version> Restrict command to a specfic filter version (e.g. xxl).");
		$this->out("\t-force Overwrite files if they exist.");
		$this->out("\t-verbose");
		$this->out("\t-quiet");
		$this->out();
		$this->out('Commands:');
		$this->out("\n\thelp\n\t\tShows this help message.");
		$this->out("\n\tsynchron <model>\n\t\tChecks if records and files are in sync.");
		$this->out("\n\tmake <source> <destination>\n\t\tProcess a file or directory according to filters.");
		$this->out();
		$this->out('Args:');
		$this->out("\t<model> Name of the Model to use.");
		$this->out("\t<source> Absolute path to a file or directory.");
		$this->out("\t<destination> Absolute path to a directory.");
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
