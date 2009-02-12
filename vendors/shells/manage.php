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
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  2007-2009 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Core',array('Folder'));
App::import('Core','ConnectionManager');
require_once(APP.'plugins'.DS.'media'.DS.'config'.DS.'core.php');
Configure::write('Cache.disable', true);
/**
 * Manage Shell Class
 *
 * @package    media
 * @subpackage media.shells
 */
class ManageShell extends Shell {
	var $tasks = array('Sync','Make');
	var $verbose = false;
	var $quiet = false;

	/**
	 * Width of shell in number of characters per line
	 *
	 * @var int
	 */
	var $width = 80;

	function _welcome() {
		$this->clear();
		$this->heading(sprintf(__('%s Shell', true), $this->name), null, '~');
	}

	function main() {
		if (isset($this->params['verbose'])) {
			$this->verbose = true;
		}
		if (isset($this->params['quiet'])) {
			$this->quiet = true;
		}

		$this->out('[S]ynchronize');
		$this->out('[M]ake');
		// $this->out('[C]lear');
		$this->out('[Q]uit');

		$action = strtoupper($this->in(__('What would you like to do?', true), array('S',/*'C',*/ 'M','Q'),'q'));
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
				exit(0);
				break;
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
		$this->out('Usage: cake <params> media.media <command> <arg>');
		$this->hr();
		$this->out('Params:');
		$this->out("\t-connection <name> Database connection to use.");
		$this->out("\t-yes Always assumes 'y' as answer.");
		$this->out('');
		$this->out('Commands:');
		$this->out("\n\tmedia help\n\t\tshows this help message.");
		$this->out("\n\tmedia synchron <model>\n\t\tChecks if records and files are in sync.");
		$this->out('');
		$this->out('Args:');
		$this->out("\t<model> Name of the Model to use.");

		$this->out("");

	}

	/**
	 * Outputs to the stdout filehandle.
	 *
	 * @param string $string String to output.
	 * @param boolean $newline If true, the outputs gets an added newline.
	 * @access public
	 */
	function out($string = '', $newline = true) {
		if($this->quiet) {
			return null;
		}
		$this->Dispatch->stdout($string, $newline);
	}

	function clear() {
		$this->out(chr(27).'[H'.chr(27).'[2J');
	}

	function _stop($status = 0) {
		if($status === 0) {
			$this->out(__('Quitting.', true));
		} else {
			$this->out(__('Aborting.', true));
		}
		parent::_stop($status);
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

	function heading($string, $width = null, $character = '=') {
		if(is_string($width)) {
			$character = $width;
			$width = null;
		}
		if($width === null) {
			$width = $this->width;
		}
		$this->out($this->pad($string.' ', $width, $character));
		$this->out();
	}

	function hr($character = '-', $width = null) {
		if($width === null) {
			$width = $this->width;
		}
		$this->out(str_repeat($character, $width));
	}

	function info($message) {
		if(!$this->verbose) {
			return null;
		}
		$this->out(sprintf(__('Notice: %s', true), $message), true);
	}

	/**
	 * @link /usr/lib/portage/bin/isolated-functions.sh
	 */
	function warn($message) {
		/* Until Dispatcher does not prepend Error: */
		fwrite($this->Dispatch->stderr, sprintf(__('Warning: %s', true), $message)."\n");
	}

	function err($message)
	{
		/* Until Dispatcher does not prepend Error: */
		fwrite($this->Dispatch->stderr, sprintf(__('Error: %s', true), $message)."\n");
		$this->_stop(1);
	}

	function begin($message) {
		$this->out(sprintf('%s ... ', $message), false);
	}

	function end($result = null) {
		if($result == true) {
			$message =  __('ok', true);
		} elseif(empty($result)) {
			$message = __('unknown', true);
		} else {
			$message = __('failed', true);
		}
		$this->out(sprintf('%s', $message));
		return $result;
	}

	function progress($value, $text = null) {
		static $target = 0;
		static $lastValue;
		static $eraseWidth;

		if($this->quiet) {
			return null;
		}

		if (isset($eraseWidth)) {
			echo "\x1b[u";
			echo str_repeat(' ', $eraseWidth);
		}
		if ($value === true) {
			echo "\x1b[s";
			$target = $text;
		} elseif ($value === false) {
			echo "\n";
		} else {
			echo "\x1b[u";
			if ($value === null) {
				$value = $lastValue;
			} else {
				$lastValue = $value;
			}
			$out = sprintf('%\' 6.2f%% %s', ($value * 100) / $target, $text);
			echo $out;
			$eraseWidth = strlen($out);
			echo "\x1b[u";
		}
	}

}
?>