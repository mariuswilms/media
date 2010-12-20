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
 * This media process adapter interfaces with the `ffmpeg` binary through the shell.
 *
 * @link http://ffmpeg.org
 */
class Media_Process_Adapter_FfmpegShell extends Media_Process_Adapter {

	protected $_compress;

	protected $_object;

	protected $_command;

	protected $_queued = array();
	protected $_queuedWidth;
	protected $_queuedHeight;

	public function __construct($handle) {
		$this->_object = $handle;
		$this->_command = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? 'ffmpeg.exe' : 'ffmpeg';
	}

	public function passthru($key, $value) {
		if (!$value || !is_scalar($value)) {
			throw new InvalidArgumentException("Value must be given and of type scalar");
		}
		$this->_queued[$key] = "-{$key} {$value}";
		return true;
	}

	public function store($handle) {
		rewind($handle);
		rewind($this->_object);

		if ($this->_queued && !$this->_process()) {
			return false;
		}
		return stream_copy_to_stream($this->_object, $handle);
	}

	public function convert($mimeType) {
		$targetType = Mime_Type::guessExtension($mimeType);
		$targetType = $this->_mapType($targetType);

		switch (Mime_Type::guessName($mimeType)) {
			case 'image':
				$command = "-vcodec {$targetType} -vframes 1 -an -f rawvideo -";
				break;
			case 'video':
				$command = "-f {$targetType} -";
				break;
			default:
				return true;
		}

		$this->_queued['target'] = $command;
		return true;
	}

	public function compress($value) {
		$this->_compress = $value;
		return true;
	}

	public function resize($width, $height) {
		$width  = (integer) $width;
		$height = (integer) $height;

		$this->_queued['resize'] = "-s {$width}x{$height}";
		$this->_queuedWidth = $width;
		$this->_queuedHeight = $height;
		return true;
	}

	public function width() {
		if ($this->_queuedWidth) {
			return $this->_queuedWidth;
		}
		preg_match('/Video\:.*,\s([0-9]+)x/', $i = $this->_info(), $matches);

		if (!isset($matches[1])) {
			throw new Exception('Could not parse width.');
		}
		return $matches[1];
	}

	public function height() {
		if ($this->_queuedHeight) {
			return $this->_queuedHeight;
		}
		preg_match('/Video\:.*,\s[0-9]+x([0-9]+)/', $this->_info(), $matches);

		if (!isset($matches[1])) {
			throw new Exception('Could not parse height.');
		}
		return $matches[1];
	}

	protected function _info() {
		$command = "{$this->_command} -i -";

		rewind($this->_object);
		$descr = array(
			0 => $this->_object,
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w')
		);

		/* There is no other way to get video information from
		   ffmpeg without exiting with an error condition because
		   it'll complain about a missing ouput file. */

		$process = proc_open($command, $descr, $pipes);

		/* Result is output to stderr. */
		$result = stream_get_contents($pipes[2]);

		fclose($pipes[1]);
		fclose($pipes[2]);
		proc_close($process);

		/* Intentionally not checking for return value. */
		return $result;
	}

	protected function _process() {
		if (!isset($this->_queued['source'])) {
			$sourceType = Mime_Type::guessExtension($this->_object);
			$sourceType = $this->_mapType($sourceType);

			$this->_queued['source'] = "-f {$sourceType} -i -";
		}
		if (!isset($this->_queued['target'])) {
			$targetType = Mime_Type::guessExtension($this->_object);
			$targetType = $this->_mapType($targetType);

			$this->_queued['target'] = "-f {$targetType} -";
		}
		$queued = $this->_queued;

		$source = $queued['source'];
		$target = $queued['target'];
		unset($queued['source'], $queued['target']);
		$targetOptions = $queued ? implode(' ', $queued) . ' ' : null;

		$command  = "{$this->_command} {$source} {$targetOptions}{$target}";

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
		$this->_queued = array();
		$this->_queuedWidth = $this->_queuedHeight = null;
		return true;
	}

	protected function _mapType($type) {
		$map = array(
			'ogv' => 'ogg',
			'oga' => 'ogg'
		);
		return isset($map[$type]) ? $map[$type] : $type;
	}
}

?>