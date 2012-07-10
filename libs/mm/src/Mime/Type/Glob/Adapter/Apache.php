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

require_once 'Mime/Type/Glob/Adapter.php';

/**
 * This adapter supports files like the ones that come with the
 * `mod_mime_magic` Apache Webserver module. Most often you'll find
 * such a file containing MIME type to extension mappings within
 * your apache2 configuration directory as `mime.types`.
 *
 * @link http://httpd.apache.org/docs/2.2/en/mod/mod_mime_magic.html
 */
class Mime_Type_Glob_Adapter_Apache extends Mime_Type_Glob_Adapter {

	public function __construct($config) {
		$this->_read($config['file']);
	}

	public function register($item) {
		return $this->_register($item);
	}

	public function to($type) {
		return $this->_to($type);
	}

	public function analyze($file, $reverse = false) {
		if ($reverse) {
			return $this->_testReverse($file, $this->_items);
		}
		if ($results = $this->_test($file, $this->_items, true)) {
			return $results;
		}
		return $this->_test($file, $this->_items, false);
	}

	protected function _read($file) {
		$handle = fopen($file, 'rb');

		$itemRegex = '^[-\w.+]*\/[-\w.+]+\s+[a-zA-Z0-9]*$';

		if (!preg_match("/{$itemRegex}/m", fread($handle, 4096))) {
			throw new InvalidArgumentException("File `{$file}` has wrong format");
		}
		fseek($handle, 0);

		while (!feof($handle)) {
			$line = trim(fgets($handle));

			if (empty($line) || $line{0} === '#') {
				continue;
			}
			$line = preg_split('/\s+/', $line);
			$this->_register(array(
				'mime_type' => array_shift($line),
				'pattern' => $line
			));
		}
		fclose($handle);
	}
}

?>