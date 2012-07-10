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
 * This adapter wraps the functions of the fileinfo extension.
 *
 * @link http://php.net/fileinfo
 */
class Mime_Type_Magic_Adapter_Fileinfo extends Mime_Type_Magic_Adapter {

	protected $_resource;

	public function __construct(array $config = array()) {
		if (isset($config['file'])) {
			$this->_resource = finfo_open(FILEINFO_NONE, $config['file']);
		} else {
			$this->_resource = finfo_open(FILEINFO_NONE);
		}
	}

	public function __destruct() {
		finfo_close($this->_resource);
	}

	public function analyze($handle) {
		$meta = stream_get_meta_data($handle);

		if (file_exists($meta['uri'])) {
			$type = 'file';
			$source = $meta['uri'];
		} else {
			$type = 'buffer';
			rewind($handle);
			$source = fread($handle, 1000000);
		}
		$result = call_user_func("finfo_{$type}", $this->_resource, $source, FILEINFO_MIME);

		if (strpos($result, 'application/ogg') === 0) {
			$full = call_user_func("finfo_{$type}", $this->_resource, $source);
			list($type, $attributes) = explode(';', $result, 2);

			if (strpos($full, 'video') !== false) {
				$type = 'video/ogg';
			} elseif (strpos($full, 'audio') !== false) {
				$type = 'audio/ogg';
			}
			return "{$type};{$attributes}";
		}
		if ($result != 'application/x-empty') {
			return $result;
		}
	}

	public function to($type) {
		throw new BadMethodCallException("Not supported");
	}

	public function register($item) {
		throw new BadMethodCallException("Not supported");
	}
}

?>