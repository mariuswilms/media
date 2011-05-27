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
	protected $_objectTemp;
	protected $_objectInfo;
	protected $_objectType;

	protected $_width;
	protected $_height;

	protected $_command;
	protected $_options = array();

	protected $_targetType;

	public function __construct($handle) {
		$this->_command = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? 'ffmpeg.exe' : 'ffmpeg';
		$this->_load($handle);
	}

	public function __destruct() {
		if ($this->_objectTemp) {
			unlink($this->_objectTemp);
		}
	}

	protected function _load($handle) {
		rewind($handle);

		$this->_object = $handle;
		$this->_objectTemp = $this->_tempFile();

		file_put_contents($this->_objectTemp, $handle);

		$this->_objectType = $this->_type(Mime_Type::guessType($handle));
		$this->_targetType = $this->_objectType;

		$this->_info = $this->_info();

		return true;
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
		if ($this->_targetType != $this->_objectType || $this->_options) {
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
					'seek' => '-ss ' . intval($this->duration() / 4),
					'noAudio' => '-an',
				) + $this->_options;

				$this->_targetType = 'rawvideo';
				break;
			case 'video':
				$this->_targetType = $this->_type($mimeType);
				break;
		}
		return true;
	}

	public function resize($width, $height) {
		return (boolean) $this->_options['resize'] = array(
			(integer) $width,
			(integer) $height
		);
	}

	public function width() {
		if ($this->_width) {
			return $this->_width;
		}
		preg_match('/Video\:.*,\s([0-9]+)x/', $this->_info, $matches);

		if (!isset($matches[1])) {
			throw new Exception('Could not parse width.');
		}
		return $matches[1];
	}

	public function height() {
		if ($this->_height) {
			return $this->_height;
		}
		preg_match('/Video\:.*,\s[0-9]+x([0-9]+)/', $this->_info, $matches);

		if (!isset($matches[1])) {
			throw new Exception('Could not parse height.');
		}
		return $matches[1];
	}

	public function duration() {
		preg_match('/Duration\:\s([0-9]{2})\:([0-9]{2})\:([0-9]{2})/', $this->_info, $matches);

		if (!isset($matches[1], $matches[2], $matches[3])) {
			throw new Exception('Could not parse duration.');
		}

		$duration  = $matches[1] * 60 * 60; /* hours */
		$duration += $matches[2] * 60;      /* minutes */
		$duration += $matches[3];           /* seconds */
		/* We do not care about ms. */

		return $duration;
	}

	protected function _info() {
		$command  = "{$this->_command} -f {$this->_objectType} -i {$this->_objectTemp}";

		$descr = array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w')
		);

		/* There is no other way to get video information from
		   ffmpeg without exiting with an error condition because
		   it'll complain about a missing ouput file. */

		$process = proc_open($command, $descr, $pipes);

		/* Result is output to stderr. */
		$result = stream_get_contents($pipes[2]);

		fclose($pipes[0]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		proc_close($process);

		/* Intentionally not checking for return value. */
		return $result;
	}

	protected function _process() {
		$targetTemp = $this->_tempFile();

		$object = "-f {$this->_objectType} -i {$this->_objectTemp}";
		$target = "-f {$this->_targetType} {$targetTemp}";

		if (isset($this->_options['resize'])) {
			list($width, $height) = $this->_options['resize'];

			/* Fix for codecs require sizes to be even. */
			$requireEven = array('mp4');

			if (in_array($this->_targetType, $requireEven)) {
				$width = $width % 2 ? $width + 1 : $width;
				$height = $height % 2 ? $height + 1 : $height;
			}
			$this->_options['resize'] = "-s {$width}x{$height}";
		}
		$options = $this->_options ? implode(' ', $this->_options) . ' ' : null;
		$command  = "{$this->_command} {$object} {$options}{$target}";

		exec($command, $output, $return);

		if ($return != 0) {
			throw new RuntimeException("Command `{$command}` returned `{$return}`.");
			return false;
		}

		$target = fopen($targetTemp, 'r');
		$buffer = fopen('php://temp', 'w+');
		stream_copy_to_stream($target, $buffer);

		fclose($target);
		unlink($targetTemp);

		$this->_options = array();
		unlink($this->_objectTemp);

		$this->_load($buffer);
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

	protected function _tempFile() {
		return realpath(sys_get_temp_dir()) . '/' . uniqid('mm_');
	}
}

?>