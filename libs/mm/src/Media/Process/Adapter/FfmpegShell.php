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
 * @link http://ffmpeg.org
 */
class Media_Process_Adapter_FfmpegShell extends Media_Process_Adapter {

	protected $_compress;

	protected $_object;

	protected $_command;

	public function __construct($handle) {
		$this->_object = $handle;
		$this->_command = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? 'ffmpeg.exe' : 'ffmpeg';
	}

	public function store($handle) {
		rewind($handle);
		rewind($this->_object);

		return stream_copy_to_stream($this->_object, $handle);
	}

	public function convert($mimeType) {
		$sourceType = Mime_Type::guessExtension($this->_object);
		$targetType = Mime_Type::guessExtension($mimeType);

		$map = array('ogv' => 'ogg');

		if (isset($map[$sourceType])) {
			$sourceType = $map[$sourceType];
		}
		if (isset($map[$targetType])) {
			$targetType = $map[$targetType];
		}
		$command  = "{$this->_command} -f {$sourceType} -i - ";

		switch (Mime_Type::guessName($mimeType)) {
			case 'image':
				$command .= "-vcodec {$targetType} -vframes 1 -an -f rawvideo -";
				break;
			case 'video':
				$command .= "-f {$targetType} -";
				break;
			default:
				return true;
		}

		rewind($this->_object);
		$temporary = fopen('php://temp', 'w+b');
		$descr = array(
			0 => $this->_object,
			1 => $temporary,
			2 => array('pipe', 'a')
		);

		$process = proc_open($command, $descr, $pipes);
		fclose($pipes[2]);
		$return = proc_close($process);

		if ($return != 0) {
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