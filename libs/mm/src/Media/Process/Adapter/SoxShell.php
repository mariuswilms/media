<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2010 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright  2007-2010 David Persson <nperson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/mm
 */

require_once 'Media/Process/Adapter.php';
require_once 'Mime/Type.php';

/**
 * This media process adapter interfaces with the `sox` binary through the shell.
 *
 * @link http://sox.sourceforge.net
 */
class Media_Process_Adapter_SoxShell extends Media_Process_Adapter {

	protected $_compress;

	protected $_object;

	protected $_command;

	public function __construct($handle) {
		$this->_object = $handle;
		$this->_command = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? 'sox.exe' : 'sox';
	}

	public function store($handle) {
		rewind($handle);
		rewind($this->_object);

		return stream_copy_to_stream($this->_object, $handle);
	}

	public function convert($mimeType) {
		if (Mime_Type::guessName($mimeType) != 'audio') {
			return true; // others care about inter media type conversions
		}
		$sourceType = Mime_Type::guessExtension($this->_object);
		$targetType = Mime_Type::guessExtension($mimeType);

		if ($this->_compress) {
			// do stuff...
		}
		$command = "{$this->_command} -t {$sourceType} - -t {$targetType} -";

		rewind($this->_object);
		$temporary = fopen('php://temp', 'w');
		$descr = array(
			0 => $this->_object,
			1 => $temporary,
			2 => array('pipe', 'a')
		);

		$process = proc_open($command, $descr, $pipes);
		fclose($pipes[2]);
		$return = proc_close($process);

		if ($return != 0) {
//var_dump(stream_get_contents($temporary, -1, 0));
			// throw new RuntimeException("Command `{$command}` returned `{$return}`.");
			return false;
		}

		$this->_object = $temporary;
		return true;
	}

	public function compress($value) {
		$this->_compress = $value;
		return true;
	}

}

?>