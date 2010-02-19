<?php
/**
 * Mime Magic File
 *
 * Copyright (c) 2007-2010 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.libs
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
uses('file');
/**
 * Mime Magic Class
 *
 * Detection of a file's MIME type by it's contents.
 * An implementation of the MIME magic functionality in pure PHP
 * supporting several database formats.
 *
 * @package    media
 * @subpackage media.libs
 */
class MimeMagic extends Object {
/**
 * Items indexed by priority
 *
 * @var array
 * @access protected
 */
	var $_items = array();
/**
 * Constructor
 *
 * @param mixed $db
 * @access public
 */
	function __construct($db) {
		$this->__read($db);
	}
/**
 * Analyzes a files contents and determines the file's MIME type
 *
 * @param string $file An absolute path to a file
 * @param array $options An array holding options
 * @return mixed A string containing the MIME type of the file
 * 	or false if MIME type could not be determined
 * @access public
 */
	function analyze($file, $options = array()) {
		$filtered = array();

		$default = array('minPriority' => 0, 'maxPriority' => 100);
		$options = array_merge($default, $options);
		extract($options, EXTR_SKIP);

		foreach ($this->_items as $priority => $items) {
			if ($priority < $minPriority || $priority > $maxPriority) {
				continue;
			}
			$filtered = array_merge($filtered, $items);
		}
		return $this->__test($file, $filtered);
	}
/**
 * Determine the format of given database
 *
 * @param mixed $db
 */
	function format($db) {
		if (empty($db)) {
			return null;
		}
		if (is_array($db)) {
			return 'Array';
		}
		if (!is_string($db)) {
			return null;
		}
		$File = new File($db);

		if ($File->exists()) {
			if ($File->ext() === 'php') {
				return 'PHP';
			}

			$File->open('rb');
			$head = $File->read(4096);

			if (substr($head, 0, 12) === "MIME-Magic\0\n") {
				return 'Freedesktop Shared MIME-info Database';
			}
			if (preg_match('/^(\>*)(\d+)\t+(\S+)\t+([\S^\040]+)\t*([-\w.\+]+\/[-\w.\+]+)*\t*(\S*)$/m', $head)) {
				return 'Apache Module mod_mime_magic';
			}
		}
		return null;
	}
/**
 * Register a magic item
 *
 * Supports a nesting level up to 3
 *
 * @param array $item A valid magic item
 * @param integer $indent The nesting depth of the item
 * @param integer $priority A value between 0 and 100.
 * 	Low numbers should be used for more generic types and higher values for specific subtypes.
 * @return boolean True if item has successfully been registered, false if not
 * @access public
 */
	function register($item, $indent = 0, $priority = 50) {
		static $keys = array();

		if (!is_array($item)
		|| !isset($item['offset'], $item['value'], $item['range_length'], $item['value_length'])) {
			return false;
		}

		if (isset($item['priority'])) {
			$priority = $item['priority'];
			unset($item['priority']);
		}

		switch ($indent) {
			case 0:
				$this->_items[$priority][] = $item;
				end($this->_items[$priority]);
				$keys[0] = key($this->_items[$priority]);
				return true;
			case 1:
				$this->_items[$priority][$keys[0]]['and'][] = $item;
				end($this->_items[$priority][$keys[0]]['and']);
				$keys[1] = key($this->_items[$priority][$keys[0]]['and']);
				return true;
			case 2:
				$this->_items[$priority][$keys[0]]['and'][$keys[1]]['and'][] = $item;
				end($this->_items[$priority][$keys[0]]['and'][$keys[1]]['and']);
				$keys[2] = key($this->_items[$priority][$keys[0]]['and'][$keys[1]]['and']);
				return true;
			case 3:
				$this->_items[$priority][$keys[0]]['and'][$keys[1]]['and'][$keys[2]]['and'][] = $item;
				return true;
			default:
				return false;
		}
	}
/**
 * Exports current items as an array
 *
 * @return array
 * @access public
 */
	function toArray() {
		$results = array();

		foreach ($this->_items as $priority => $items) {
			foreach ($items as $item) {
				$item['priority'] = $priority;
				$results[] = $item;
			}
		}
		return $results;
	}
/**
 * Will load a file from various sources
 *
 * Supported formats:
 * - Freedesktop Shared MIME-info Database
 * - Apache Module mod_mime_magic
 * - PHP file containing variables formatted like: $data[0] = array(item, item, item, ...)
 *
 * @param mixed $file An absolute path to a magic file in apache, freedesktop
 * 	or a filename (without .php) of a file in the configs/ dir in CakePHP format
 * @return mixed A format string or null if format could not be determined
 * @access private
 * @link http://httpd.apache.org/docs/2.2/en/mod/mod_mime_magic.html
 * @link http://standards.freedesktop.org/shared-mime-info-spec/shared-mime-info-spec-0.13.html
 */
	function __read($db) {
		$format = MimeMagic::format($db);

		if ($format === 'Array') {
			foreach ($db as $item) {
				$this->register($item);
			}
		} elseif ($format === 'PHP') {
			include $db;
			foreach ($data as $item) {
				$this->register($item);
			}
		} elseif ($format === 'Freedesktop Shared MIME-info Database') {
			$sectionRegex = '^\[(\d{1,3}):([-\w.\+]+\/[-\w.\+]+)\]$';
			$itemRegex = '^(\d*)\>+(\d+)=+([^&~\+]{2})([^&~\+]+)&?([^~\+]*)~?(\d*)\+?(\d*).*$';

			$File = new File($db);
			$File->open('rb');
			$File->offset(12);

			while (!feof($File->handle)) {
				$line = '';

				if (!isset($chars)) {
					$chars = array(0 => $File->read(1), 1 => $File->read(1));
				} else {
					$chars = array(0 => $chars[1], 1 => null);
				}

				while (!feof($File->handle) && !($chars[0] === "\n"
				&& (ctype_digit($chars[1]) || $chars[1] === '>' || $chars[1] === '['))) {
					$line .= $chars[0];
					$chars = array(0 => $chars[1], 1 => $File->read(1));
				}

				if (preg_match('/' . $sectionRegex . '/', $line, $matches)) {
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
						'value'        => $this->__formatValue($matches[4], $wordSize),
						/* default: all `one` bits */
						'mask'         => empty($matches[5]) ? null : $this->__formatValue($matches[5], $wordSize),
						'range_length' => empty($matches[7]) ? 1 : intval($matches[7]),
						'mime_type'    => $section['mime_type'],
					);
					$this->register($item, $indent, $section['priority']);
				}
			}
		} elseif ($format === 'Apache Module mod_mime_magic') {
			$itemRegex = '^(\>*)(\d+)\t+(\S+)\t+([\S^\040]+)\t*([-\w.\+]+\/[-\w.\+]+)*\t*(\S*)$';

			$File = new File($db);
			$File->open('rb');

			while (!feof($File->handle)) {
				$line = trim(fgets($File->handle));

				if (empty($line) || $line{0} === '#') {
					continue;
				}

				$line = preg_replace('/(?!\B)\040+/', "\t", $line);

				if (!preg_match('/' . $itemRegex . '/', $line, $matches)) {
					continue;
				}

				$item = array(
					'offset'       => intval($matches[2]),
					'value'        => $this->__formatValue($matches[4], $matches[3], true),
					'mask'         => null,
					'range_length' => 0,
					'mime_type'    => empty($matches[5]) ? null : $matches[5],
					'encoding'     => empty($matches[6]) ? null : $matches[6],
				);
				$item['value_length'] = strlen($item['value']);
				$this->register($item, strlen($matches[1]), 80);
			}
		} else {
			trigger_error('MimeGlob::read - Unknown db format', E_USER_WARNING);
		}
	}
/**
 * Tests a file's contents against magic items
 *
 * @param string $file Absolute path to a file
 * @param array $items
 * @return mixed A string containing the MIME type of the file or false if no pattern matched
 * @access private
 */
	function __test($file, $items) {
		$File = new File($file);

		if (!$File->readable()) {
			return false;
		}

		$File->open('rb');

		foreach ($items as $item) {
			if ($result = $this->__testRecursive($File, $item)) {
				return $result;
			}
		}
		return false;
	}
/**
 * Recursively tests a file's contents against a magic item
 *
 * @param object $File An instance of the File class
 * @param array $item A magic item
 * @return mixed A string containing the MIME type of the file or false if no pattern matched
 * @access private
 */
	function __testRecursive(&$File, $item) {
		if (isset($item['mask'])) {
			$item['value'] = $item['value'] & $item['mask'];
		}

		$File->offset($item['offset']);

		if (strpos($File->read($item['value_length'] + $item['range_length']), $item['value']) !== false) {
			if (isset($item['and'])) {
				foreach ($item['and'] as $andedItem) {
					if ($return = $this->__testRecursive($File, $andedItem)) {
						return $return;
					}
				}
			} elseif (isset($item['mime_type'])) {
				return $item['mime_type'];
			}
		}
		return false;
	}
/**
 * Format a value for testing
 *
 * @param mixed $value Value to format
 * @param mixed $type String containing the datatype of the value
 * 	or an integer indicating the word size of the value
 * @param boolean $binary Whether the value is a binary value or not
 * @param boolean $unEscape If set to true and value is not binary strips slashes from string values
 * @return mixed On success the formatted binary value or the input value
 * @access private
 */
	function __formatValue($value, $type, $toBinary = false, $unEscape = true) {
		if (!$toBinary) {
			switch ($type) {
				case 2:
				case 'host16':
					return pack('S', current(unpack('n' , $value)));
				case 4:
				case 'host32':
					return pack('L', current(unpack('N' , $value)));
				default:
					return $value;
			}
		} else {
			if (decoct(octdec($value)) == $value) {
				$value = octdec($value);
			} elseif ($value[1] === 'x') {
				$value = hexdec($value);
			} elseif (is_numeric($value) && intval($value) == $value) {
				$value = intval($value);
			} elseif (is_numeric($value) && floatval($value) == $value) {
				$value = floatval($value);
			}

			switch ($type) {
				case 'byte':
					return pack('c', $value);
				case 'short':
					return pack('s', $value);
				case 'date':
				case 'long':
					return pack('l', $value);
				case 'float':
					return pack('f', $value);
				case 'double':
					return pack('d', $value);
				case 'string':
					if ($unEscape) {
						$value = strtr($value, array(
							'\ ' => ' ', '\<' => '<', '\>' => '>',
							'\\\r' => '\r', '\\\n' => '\n'
						));
					}
					return preg_replace('/\\\\([0-9]{1,3})/e', 'chr($1);', $value);
				case 'beshort':
					return pack('n', $value);
				case 'bedate':
				case 'belong':
					return pack('N', $value);
				case 'leshort':
					return pack('v', $value);
				case 'ledate':
				case 'lelong':
					return pack('V', $value);
				default:
					return $value;
			}
		}
	}
}
?>
