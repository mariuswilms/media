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

require_once 'Mime/Type/Magic/Adapter.php';

/**
 * This adapter supports magic database files compiled into the Freedesktop format.
 * A precompiled glob database file comes with this library and is located in
 * `resources/magic.db`. You may also have a much more current version of such a file on
 * your system. Most often those files are located below `/usr/share/mime`.
 *
 * @link http://standards.freedesktop.org/shared-mime-info-spec/shared-mime-info-spec-0.13.html
 */
class Mime_Type_Magic_Adapter_Freedesktop extends Mime_Type_Magic_Adapter {

	public function __construct(array $config = array()) {
		if (!isset($config['file'])) {
			throw new InvalidArgumentException("Missing `file` configuration value.");
		}
		$this->_read($config['file']);
	}

	public function analyze($handle, $options = array()) {
		$filtered = array();

		$options += array('minPriority' => 0, 'maxPriority' => 100);
		extract($options, EXTR_SKIP);

		foreach ($this->_items as $priority => $items) {
			if ($priority < $minPriority || $priority > $maxPriority) {
				continue;
			}
			$filtered = array_merge($filtered, $items);
		}
		return $this->_test($handle, $filtered);
	}

	public function to($type) {
		return $this->_to($type);
	}

	public function register($item, $indent = 0, $priority = 50) {
		return $this->_register($item, $indent, $priority);
	}

	protected function _read($file) {
		$handle = fopen($file, 'rb');

		$sectionRegex = '^\[(\d{1,3}):([-\w.\+]+\/[-\w.\+]+)\]$';
		$itemRegex = '^(\d*)\>+(\d+)=+([^&~\+]{2})([^&~\+]+)&?([^~\+]*)~?(\d*)\+?(\d*).*$';

		if (fread($handle, 12) != "MIME-Magic\0\n") {
			throw new InvalidArgumentException("File `{$file}` has wrong format");
		}

		while (!feof($handle)) {
			$line = '';

			if (!isset($chars)) {
				$chars = array(0 => fread($handle, 1), 1 => fread($handle, 1));
			} else {
				$chars = array(0 => $chars[1], 1 => null);
			}

			while (
				!feof($handle) && !($chars[0] === "\n"
				&& (ctype_digit($chars[1]) || $chars[1] === '>' || $chars[1] === '['))
			) {
				$line .= $chars[0];
				$chars = array(0 => $chars[1], 1 => fread($handle, 1));
			}

			if (preg_match("/{$sectionRegex}/", $line, $matches)) {
				$section = array(
					'priority'  => $matches[1],
					'mime_type' => $matches[2]
				);
			} elseif (preg_match('/' . $itemRegex . '/', $line, $matches)) {
				$indent = empty($matches[1]) ? 0 : intval($matches[1]);
				$wordSize = empty($matches[6]) ? 1 : intval($matches[6]);
				$item = array(
					'offset'       => intval($matches[2]),
					'value_length' => current(unpack('n', $matches[3])),
					'value'        => $this->_formatValue($matches[4], $wordSize),
					/* default: all `one` bits */
					'mask'         => empty($matches[5]) ? null : $this->_formatValue($matches[5], $wordSize),
					'range_length' => empty($matches[7]) ? 1 : intval($matches[7]),
					'mime_type'    => $section['mime_type'],
				);
				$this->_register($item, $indent, $section['priority']);
			}
		}
		fclose($handle);
	}
}

?>