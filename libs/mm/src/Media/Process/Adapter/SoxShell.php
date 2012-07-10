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

	protected $_sampleRate;

	protected $_channels;

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

		$map = array('ogv' => 'ogg', 'oga' => 'ogg');

		if (isset($map[$sourceType])) {
			$sourceType = $map[$sourceType];
		}
		if (isset($map[$targetType])) {
			$targetType = $map[$targetType];
		}
		$modify = null;

		if ($this->_sampleRate) {
			$modify .= " --rate {$this->_sampleRate}";
		}
		if ($this->_channels) {
			$modify .= " --channels {$this->_channels}";
		}

		rewind($this->_object);
		$error = fopen('php://temp', 'wrb+');
		$targetFile = tempnam(sys_get_temp_dir(), 'mm_');

		// Since SoX 14.3.0 multi threading is enabled which
		// paradoxically can cause huge slowdowns.
		$command  = "{$this->_command} -q --single-threaded";
		$command .= " -t {$sourceType} -{$modify} -t {$targetType} {$targetFile}";

		$descr = array(
			0 => $this->_object,
			1 => array('pipe', 'a'),
			2 => array('pipe', 'a')
		);
		$process = proc_open($command, $descr, $pipes);

		fclose($pipes[1]);
		fclose($pipes[2]);
		$return = proc_close($process);

		// Workaround for header based formats which require the output stream to be seekable.
		$target = fopen($targetFile, 'rb');
		$temporary = fopen('php://temp', 'wb+');
		stream_copy_to_stream($target, $temporary);
		fclose($target);
		unlink($targetFile);

		if ($return != 0) {
			rewind($error);
			//var_dump(stream_get_contents($temporary, -1, 0));
			// throw new RuntimeException("Command `{$command}` returned `{$return}`.");
			return false;
		}
		fclose($error);

		$this->_object = $temporary;
		return true;
	}

	public function passthru($key, $value) {
		throw new Exception("The adapter has no passthru support.");
	}

	public function channels($value) {
		$this->_channels = $value;
		return true;
	}

	public function sampleRate($value) {
		$this->_sampleRate = $value;
		return true;
	}
}

?>