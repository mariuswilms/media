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

require_once 'Media/Info/Adapter.php';

/**
 * This adapter makes us of image related PHP functions which
 * act directly on a given stream or file. It aims to deliver
 * some few but most common values (i.e. width and height).
 */
class Media_Info_Adapter_ImageBasic extends Media_Info_Adapter {

	protected $_object;

	public function __construct($file) {
		$this->_object = $file;
	}

	public function all() {
		$data = getimagesize($this->_object);

		$result = array(
			'width' => $data[0],
			'height' => $data[1]
		);
		if (isset($data['channels'])) {
			$result['channels'] = $data['channels'];
		}
		if (isset($data['bits'])) {
			$result['bits'] = $data['bits'];
		}
		return $result;
	}

	public function get($name) {
		$data = $this->all();

		if (isset($data[$name])) {
			return $data[$name];
		}
	}
}

?>