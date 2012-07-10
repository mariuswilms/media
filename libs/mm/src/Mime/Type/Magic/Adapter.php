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

/**
 * The `Mime_Type_Magic_Adapter class forms the base clase from which all mime type
 * magic adatpers must subclass.
 */
abstract class Mime_Type_Magic_Adapter {

	protected $_items = array();

	/* All adapters must implement the following 3 methods. */

	/**
	 * Register a magic item.
	 *
	 * @param array $item A valid magic item
	 * @return boolean True if item has successfully been registered, false if not
	 */
	abstract public function register($item);

	/**
	 * Exports current items.
	 *
	 * @param $type string I.e. `'array'`.
	 * @return mixed
	 */
	abstract public function to($type);

	/**
	 * Analyzes a files contents and determines the file's MIME type,
	 *
	 * @param resource $handle An open handle.
	 * @return string|void A string containing the MIME type of the file.
	 */
	abstract public function analyze($handle);

	/**
	 * Register a magic item.
	 *
	 * Supports a nesting level up to 3.
	 *
	 * @param array $item A valid magic item
	 * @param integer $indent The nesting depth of the item
	 * @param integer $priority A value between 0 and 100.
	 *                Low numbers should be used for more generic types and higher
	 *                values for specific subtypes.
	 * @return boolean True if item has successfully been registered, false if not
	 */
	protected function _register($item, $indent = 0, $priority = 50) {
		static $keys = array();

		if (!is_array($item)
		|| !isset($item['offset'], $item['value'], $item['range_length'], $item['value_length'])) {
			$message  = "Item is not an array or is missing values for `offset`, ";
			$message .= "`value`, `range_length` or `value_length`.`";
			throw new InvalidArgumentException($message);
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
	 * Exports current items.
	 *
	 * @param $type string Currently just `'array'` is supported.
	 * @return array
	 */
	protected function _to($type) {
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
	 * Tests a file's contents against magic items.
	 *
	 * @param resource $handle An open handle to read from.
	 * @param array $items
	 * @return mixed A string containing the MIME type of the file or false if no pattern matched.
	 */
	protected function _test($handle, $items) {
		foreach ($items as $item) {
			if ($result = $this->_testRecursive($handle, $item)) {
				return $result;
			}
		}
	}

	/**
	 * Recursively tests a file's contents against a magic item
	 *
	 * @param resource $handle An open handle to read from.
	 * @param array $item A magic item.
	 * @return mixed A string containing the MIME type of the file or false if no pattern matched.
	 */
	protected function _testRecursive($handle, $item) {
		if (isset($item['mask'])) {
			$item['value'] = $item['value'] & $item['mask'];
		}

		fseek($handle, $item['offset']);
		$string = fread($handle , $item['value_length'] + $item['range_length']);

		if (strpos($string, $item['value']) !== false) {
			if (isset($item['and'])) {
				foreach ($item['and'] as $andedItem) {
					if ($return = $this->_testRecursive($handle, $andedItem)) {
						return $return;
					}
				}
			} elseif (isset($item['mime_type'])) {
				return $item['mime_type'];
			}
		}
	}

	/**
	 * Format a value for testing.
	 *
	 * @param mixed $value Value to format
	 * @param mixed $type String containing the datatype of the value
	 * 	or an integer indicating the word size of the value
	 * @param boolean $binary Whether the value is a binary value or not
	 * @param boolean $unEscape If set to true and value is not binary strips slashes from string values
	 * @return mixed On success the formatted binary value or the input value
	 */
	protected function _formatValue($value, $type, $toBinary = false, $unEscape = true) {
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