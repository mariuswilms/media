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
			$this->_resource = finfo_open(FILEINFO_MIME, $config['file']);
		} else {
			$this->_resource = finfo_open(FILEINFO_MIME);
		}
	}

	public function __destruct() {
		finfo_close($this->_resource);
	}

	public function analyze($handle) {
		$meta = stream_get_meta_data($handle);

		if (file_exists($meta['uri'])) {
			$result = finfo_file($this->_resource, $meta['uri']);
		} else {
			$result = finfo_buffer($this->_resource, fread($handle, 1000000));
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