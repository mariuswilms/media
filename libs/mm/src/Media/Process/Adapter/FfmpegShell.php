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

	protected $_object;

	protected $_command;

	protected $_options = array();

	protected $_width;
	protected $_height;

	protected $_source;
	protected $_target;

	protected $_cachedInfo;

	public function __construct($handle) {
		$this->_object = $handle;
		$this->_command = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? 'ffmpeg.exe' : 'ffmpeg';

		$this->_source = $this->_target = $this->_type(Mime_Type::guessType($this->_object));
	}

	public function passthru($key, $value = null) {
		if ($value === null) {
			$this->_options[$key] = "-{$key}";
		} elseif (is_array($value)) {
			$this->_options[$key] = "-{$key} " . implode(" -{$key} ", (array) $value);
		} else {
			$this->_options[$key] = "-{$key} {$value}";
		}
		return true;
	}

	public function store($handle) {
		if ($this->_target != $this->_source || $this->_options) {
			if (!$this->_process()) {
				return false;
			}
		}
		rewind($handle);
		rewind($this->_object);

		return stream_copy_to_stream($this->_object, $handle);
	}

	public function convert($mimeType) {
		switch (Mime_Type::guessName($mimeType)) {
			case 'image':
				$this->_options = array(
					'vcodec' => '-vcodec ' . $this->_type($mimeType),
					'vframes' => '-vframes 1',
					'seek' => '-ss 1',
					'noAudio' => '-an',
				) + $this->_options;

				$this->_target = 'rawvideo';
				break;
			case 'video':
				$this->_target = $this->_type($mimeType);
				break;
		}
		return true;
	}

	public function resize($width, $height) {
		$width  = (integer) $width;
		$height = (integer) $height;

		$this->_options['resize'] = "-s {$width}x{$height}";
		$this->_width = $width;
		$this->_height = $height;
		return true;
	}

	public function width() {
		if ($this->_width) {
			return $this->_width;
		}
		preg_match('/Video\:.*,\s([0-9]+)x/', $i = $this->_info(), $matches);

		if (!isset($matches[1])) {
			throw new Exception('Could not parse width.');
		}
		return $matches[1];
	}

	public function height() {
		if ($this->_height) {
			return $this->_height;
		}
		preg_match('/Video\:.*,\s[0-9]+x([0-9]+)/', $this->_info(), $matches);

		if (!isset($matches[1])) {
			throw new Exception('Could not parse height.');
		}
		return $matches[1];
	}

	protected function _info() {
		if ($this->_cachedInfo) {
			return $this->_cachedInfo;
		}
		$source = "-f {$this->_source} -i";

		$tempFile = null;
		$sourceHandle = $this->_object;

		rewind($sourceHandle);

		if ($this->_sourceRequiresFile()) {
			$tempFile = $this->_tempFile();
			$tempHandle = fopen($tempFile, 'w+b');

			stream_copy_to_stream($sourceHandle, $tempHandle);
			fclose($tempHandle);

			$source .= " {$tempFile}";
			$sourceDescr = array('pipe', 'r');
		} else {
			$source .= ' -';
			$sourceDescr = $sourceHandle;
		}
		$command  = "{$this->_command} {$source}";

		$descr = array(
			0 => $sourceDescr,
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

		if ($tempFile) {
			unlink($tempFile);
		}

		/* Intentionally not checking for return value. */
		return $this->_cachedInfo = $result;
	}

	protected function _process() {
		$source = "-f {$this->_source} -i";
		$target = "-f {$this->_target}";

		$tempSourceFile = null;
		$tempTargetFile = null;

		rewind($this->_object);

		$sourceHandle = $this->_object;
		$targetHandle = fopen('php://temp', 'w+b');

		if ($this->_sourceRequiresFile()) {
			/* In some situation (erroneous encoded source files) dimension
			   information cannot be retrieved. As we cannot hint the source
			   dimensions we use this workaround. */

			$tempSourceFile = $this->_tempFile();
			$tempSourceHandle = fopen($tempSourceFile, 'w+b');

			stream_copy_to_stream($sourceHandle, $tempSourceHandle);
			fclose($tempSourceHandle);

			$source .= " {$tempSourceFile}";
			$sourceDescr = array('pipe', 'r');
		} else {
			$source .= ' -';
			$sourceDescr = $sourceHandle;
		}
		if ($this->_targetRequiresFile()) {
			/* Some formats require the target to be seekable.
			   We workaround that by creating a file and deleting it later. */

			$tempTargetFile = $this->_tempFile();

			$target .= " {$tempTargetFile}";
			$targetDescr = array('pipe', 'w');
		} else {
			$target .= " -";
			$targetDescr = $targetHandle;
		}

		$options = $this->_options ? implode(' ', $this->_options) . ' ' : null;
		$command  = "{$this->_command} {$source} {$options}{$target}";

		$descr = array(
			0 => $sourceDescr,
			1 => $targetDescr,
			2 => array('pipe', 'a')
		);
		$process = proc_open($command, $descr, $pipes);
		fclose($pipes[2]);
		$return = proc_close($process);

		/* Clean/finish above workarounds. */

		if ($tempSourceFile) {
			unlink($tempSourceFile);
		}
		if ($tempTargetFile) {
			$tempTargetHandle = fopen($tempTargetFile, 'r+b');

			stream_copy_to_stream($tempTargetHandle, $targetHandle);

			fclose($tempTargetHandle);
			unlink($tempTargetFile);
		}

		if ($return != 0) {
			throw new RuntimeException("Command `{$command}` returned `{$return}`.");
			return false;
		}

		$this->_options = array();
		$this->_width = $this->_height = null;

		$this->_object = $targetHandle;
		return true;
	}

	protected function _type($object) {
		$type = Mime_Type::guessExtension($object);

		$map = array(
			'ogv' => 'ogg',
			'oga' => 'ogg'
		);
		return isset($map[$type]) ? $map[$type] : $type;
	}

	protected function _sourceRequiresFile() {
		$types = array(
			'mp4', 'mov'
		);
		return in_array($this->_source, $types);
	}

	protected function _targetRequiresFile() {
		$types = array(
			'mp4', 'ogg', 'mov'
		);
		return in_array($this->_target, $types);
	}

	protected function _tempFile() {
		return realpath(sys_get_temp_dir()) . '/' . uniqid('mm_');
	}
}

?>