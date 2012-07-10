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
 * The `Mime_Type_Glob_Adapter` class forms the base clase from which all mime type glob adatpers
 * must subclass.
 */
abstract class Mime_Type_Glob_Adapter {

	/**
	 * Items indexed by priority
	 *
	 * @var array
	 */
	protected $_items = array();

	/* All adapters must implement the following 3 methods. */

	/**
	 * Registers a glob item.
	 *
	 * This ia an example of a valid item:
	 * {{{
	 * 	array(
	 * 		'mime_type' => 'image/jpeg',
	 * 		'pattern' => 'jpg',
	 * 	)
	 * }}}
	 * or
	 * {{{
	 * 	array(
	 * 		'mime_type' => 'image/jpeg',
	 * 		'pattern' => array('jpg', 'jpeg'),
	 * 	)
	 * }}}
	 *
	 * @param array $item A valid glob item
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
	 * Analyzes a filename and determines the MIME type
	 *
	 * @param string $file Path to a file, basename of a file or in reverse mode a MIME type
	 * @param boolean $reverse Enable/disable reverse searching
	 * @return mixed A string containing the MIME type of the file or false if MIME type
	 *               could not be determined, in reverse mode the pattern corresponding to the
	 *               given MIME type
	 */
	abstract public function analyze($file, $reverse = false);

	/**
	 * Registers a glob item.
	 *
	 * @param array $item A valid glob item
	 * @return boolean True if item has successfully been registered, false if not
	 */
	protected function _register($item = array()) {
		foreach ((array) $item['pattern'] as $pattern) {
			if (isset($this->_items[$pattern])) {
				$this->_items[$pattern] = array_unique(array_merge(
					$this->_items[$pattern],
					array($item['mime_type'])
				));
			} else {
				$this->_items[$pattern] = array($item['mime_type']);
			}
		}
	}

	/**
	 * Exports current items.
	 *
	 * @param $type string Currently just `'array'` is supported.
	 * @return array
	 */
	protected function _to($type) {
		$result = array();

		foreach ($this->_items as $pattern => $mimeTypes) {
			foreach($mimeTypes as $mimeType) {
				$result[] = array('mime_type' => $mimeType, 'pattern' => $pattern);
			}
		}
		return $result;
	}

	/**
	 * Tests a file's contents against glob items.
	 *
	 * This method does not implement `fnmatch()`-style glob matching but uses
	 * a very simplisitc way in order to optimize speed and portabilty as `fnmatch()`
	 * was not available on Windows before PHP 5.3.
	 *
	 * @param string $name The basename of a file
	 * @param array $items
	 * @param boolean $caseSensitive
	 * @return array Matched MIME types keyed by patterns
	 */
	protected function _test($name, $items, $caseSensitive = true) {
		$basename = pathinfo($name, PATHINFO_BASENAME);
		$ext = pathinfo($name, PATHINFO_EXTENSION);

		$results = array();

		if (!$caseSensitive) {
			$basename = strtolower($basename);
			$ext = strtolower($ext);
		}

		if (isset($items[$ext])) {
			$results = $items[$ext];
		}
		if (isset($items[$basename])) {
			$results = array_merge($results, $items[$basename]);
		}
		return $results;
	}

	/**
	 * Does a reverse test against glob items.
	 *
	 * @param string $mimeType
	 * @param array $items
	 * @return array Matched patterns
	 */
	protected function _testReverse($mimeType, $items) {
		$results = array();

		foreach ($items as $pattern => $mimeTypes) {
			if (in_array($mimeType, $mimeTypes)) {
				$results[] = $pattern;
			}
		}
		return $results;
	}
}

?>