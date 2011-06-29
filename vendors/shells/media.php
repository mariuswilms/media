<?php
/**
 * Media Shell File
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
 * @subpackage media.shells
 * @copyright  2007-2011 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Core', array('ConnectionManager', 'Folder'));
require_once App::pluginPath('media') . 'config' . DS . 'core.php';
Configure::write('Cache.disable', true);

/**
 * Media Shell Class
 *
 * @package    media
 * @subpackage media.shells
 */
class MediaShell extends Shell {

/**
 * Tasks
 *
 * @var string
 * @access public
 */
	var $tasks = array('Sync', 'Make');

/**
 * Verbose mode
 *
 * @var boolean
 * @access public
 */
	var $verbose = false;

/**
 * Quiet mode
 *
 * @var boolean
 * @access public
 */
	var $quiet = false;

/**
 * Startup
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
 * Welcome
 *
 * @access protected
 * @return void
 */
	function _welcome() {
		$this->hr();
		$this->out('Media Shell');
		$this->hr();
	}

/**
 * Main
 *
 * @access public
 * @return void
 */
	 function main() {
		$this->out('[I]nitialize Media Directory');
		$this->out('[P]rotect Transfer Directory');
		$this->out('[S]ynchronize');
		$this->out('[M]ake');
		$this->out('[H]elp');
		$this->out('[Q]uit');

		$action = $this->in(
			__('What would you like to do?', true),
			array('I', 'P', 'S', 'M', 'H', 'Q'),
			'q'
		);

		$this->out();

		switch (strtoupper($action)) {
			case 'I':
				$this->init();
				break;
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

/**
 * Initializes directory structure
 *
 * @access public
 * @return void
 */
	function init() {
		$message = 'Do you want to create missing media directories now?';

		if ($this->in($message, 'y,n', 'n') == 'n') {
			return false;
		}

		$short = array('aud', 'doc', 'gen', 'img', 'vid');

		$dirs = array(
			MEDIA => array(),
			MEDIA_STATIC => $short,
			MEDIA_TRANSFER => $short,
			MEDIA_FILTER => array(),
		);

		foreach ($dirs as $dir => $subDirs) {
			if (is_dir($dir)) {
				$result = 'SKIP';
			} else {
				new Folder($dir, true);

				if (is_dir($dir)) {
					$result = 'OK';
				} else {
					$result = 'FAIL';
				}
			}
			$this->out(sprintf('%-50s [%-4s]', $this->shortPath($dir), $result));

			foreach ($subDirs as $subDir) {
				if (is_dir($dir . $subDir)) {
					$result = 'SKIP';
				} else {
					new Folder($dir . $subDir, true);

					if (is_dir($dir . $subDir)) {
						$result = 'OK';
					} else {
						$result = 'FAIL';
					}
				}
				$this->out(sprintf('%-50s [%-4s]', $this->shortPath($dir . $subDir), $result));
			}
		}

		$this->out();
		$this->protect();
		$this->out('Remember to set the correct permissions on transfer and filter directory.');
	}

/**
 * Protects the transfer directory
 *
 * @access public
 * @return void
 */
	function protect() {
		if (MEDIA_TRANSFER_URL === false) {
			$this->out('The content of the transfer directory is not served.');
			return true;
		}

		$file = MEDIA_TRANSFER . '.htaccess';

		if (is_file($file)) {
			$this->err($this->shortPath($file) . ' is already present.');
			return true;
		}
		if (strpos(MEDIA_TRANSFER, WWW_ROOT) === false) {
			$this->err($this->shortPath($file) . ' is not in your webroot.');
			return;
		}
		$this->out('Your transfer directory is missing a htaccess file to block requests.');

		if ($this->in('Do you want to create it now?', 'y,n', 'n') == 'n') {
			return false;
		}

		$File = new File($file);
		$File->append("Order deny,allow\n");
		$File->append("Deny from all\n");
		$File->close();

		$this->out($this->shortPath($File->pwd()) . ' created.');
		$this->out('');
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
		$this->out('');
		$this->out("SYNOPSIS");
		$this->out("\tcake media <params> <command> <args>");
		$this->out('');
		$this->out("COMMANDS");
		$this->out("\tinit");
		$this->out("\t\tInitializes the media directory structure.");
		$this->out('');
		$this->out("\tprotect");
		$this->out("\t\tCreates a htaccess file to protect the transfer dir.");
		$this->out('');
		$this->out("\tsync [-auto] [model] [searchdir]");
		$this->out("\t\tChecks if records are in sync with files and vice versa.");
		$this->out('');
		$this->out("\t\t-auto Automatically repair without asking for confirmation.");
		$this->out('');
		$this->out("\tmake [-force] [-version name] [sourcefile/sourcedir] [destinationdir]");
		$this->out("\t\tProcess a file or directory according to filters.");
		$this->out('');
		$this->out("\t\t-version name Restrict command to a specfic filter version (e.g. xxl).");
		$this->out("\t\t-force Overwrite files if they exist.");
		$this->out('');
		$this->out("\thelp");
		$this->out("\t\tShows this help message.");
		$this->out('');
		$this->out("OPTIONS");
		$this->out("\t-verbose");
		$this->out("\t-quiet");
		$this->out('');
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
}
?>
